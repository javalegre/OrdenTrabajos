/*  Ordenes de Trabajo
 *  version 1.0
 */
    var OrdenTrabajo = [];
    var dataTableContractors = new Array();
    var dataTableInsumos = new Array();
    var DataContractors = new Array();
    var labores = new Array();
    var lotes = new Array();
    var unidades = new Array();
    var cc = new Array();
    var productos = new Array();
    var productos_lotes = new Array();
    var almacenes = new Array();
    var monedas = new Array();
    var tecnicas =  new Array();
    var defaultValues = new Array();
    
$(document).ready(function() {
    /* Recupero los datos de la OT actual */
    RecuperarDatosSiembra( $('#id').val() );
});

/* Anular Orden de Trabajo */
$('.AnularOT').on('click', function (e) {
    e.preventDefault();
    var id = e.currentTarget.getAttribute('data-id');
    fetch('/orden-trabajos/delete/' + id )
        .then( res => res.json() )
        .then( data => {
            if (data['status'] === 'error') { /* Existe un error */
                for(var i=0;i<data['message'].length;i++){
                    toastr.error(data['message'][i]);
                }
            } else {
                toastr.info(data['message']); /* Se anuló correctamente */
                $(this).closest('tr').remove();
            }
        })
        .catch( function(err) {
            console.log( err.message );
            toastr.error(err.message );
        });
});

/**
 *  Recupero los datos de una OT
 * 
 * @param {number} orden_trabajo_id - Orden de trabajo a recuperar
 */
const RecuperarDatosSiembra = ( orden_trabajo_id ) => {
    fetch(`/orden-trabajos/siembra/${ orden_trabajo_id }.json`)
        .then( res => res.json() )
        .then( data => {
            /* Data recibida de la OT */
            CargarTabla( data );
        })
        .catch(function(err) {
            console.log( err );
        });
};

/* Inicio la carga de los datos a la tabla de OT */
const CargarTabla = ( data ) => {
    /* Proyectos */
    var centrocosto = data.proyectos;
    cc.push({id: 0, nombre: 'Seleccione un Proyecto' });
    $.each(centrocosto, function(index, row) {
        cc.push(
            {   id: row.id,
                nombre: row.nombre
            }
        );
    });
    /* Ahora paso todos los registros a los lotes */
    var lote = data.lotes;
    lotes.push({id: 0, nombre: 'Sin Lote', has: 0 });
    $.each(lote, function(index, row) {
        lotes.push(
            {
                id: row.lote_id,
                nombre: row.lote.nombre,
                has: row.lote.hectareas_reales,
                establecimiento: row.lote.establecimiento ? row.lote.establecimiento.nombre : '',
                sector: row.lote.sectore ? row.lote.sectore.nombre : ''
            }
        );
    });
    /* Datos de la planilla tecnica */
    var tecnica = data.tecnicas;
    $.each(tecnica, function(index, row) {
        tecnicas.push(
            {
                id: row.id,
                nombre: row.nombre
            }
        );
    });
    /* Lista de Unidades */
    var unidad = data.unidades;
    $.each(unidad, function(index, row) {
        unidades.push(
            {
                id: row.id,
                nombre: row.nombre
            }
        );
    });
    /* Monedas */
    var moneda = data.monedas;
    $.each(moneda, function(index, row) {
        monedas.push(
            {   id: row.id,
                nombre: row.simbolo
            }
        );
    });
    
    /* Productos */
    var producto = data.insumos;
    producto.push({id: 0, nombre: 'Seleccione producto', tarea: 0 });
    $.each(producto, function(index, row) {
        productos.push(
            {   id: row.id,
                nombre: row.nombre,
                tarea: row.tarea
            }
        );
    });
    
    /* Almacenes */
    var almacen = data.almacenes;
    $.each(almacen, function(index, row) {
        almacenes.push(
            {   id: row.id,
                nombre: row.nombre
            }
        );
    });
    
    var labor = data.labores;
    labores.push({id: 0, nombre: 'Seleccione una labor' });
    $.each(labor, function(index, row) {
        labores.push(
            {
                id: row.proyectos_labore_id,
                nombre: row.proyectos_labore ? row.proyectos_labore.nombre : '',
                insumos:  row.proyectos_labore ? row.proyectos_labore.insumos : ''
            }
        );
    });
    
    OrdenTrabajo['distribucion'] = data.ordenTrabajo.orden_trabajos_distribuciones;
    OrdenTrabajo['insumos'] = data.ordenTrabajo.orden_trabajos_insumos;
    ContractorListEdit.init();
};

$(".EjecutarOT").click(function(){
    /* Averiguo si habilito o no el boton de Ejecutar OT */
    let orden_trabajo_id = $('#id').val();

    /* ------------------------------------- */
    /* Funciona para enviar un formulario    */
    /* ------------------------------------- */
    $('#siembra').val(1);
    const data = new FormData(document.getElementById('ordenTrabajo'));
    
    fetch(`/orden-trabajos/siembra/${ orden_trabajo_id}.json`, {
        method: 'POST',
        body: data
    })
    .then( res => res.json())
    .then( data => {
        toastr.options.onHidden = function(){
            var ruta = "http://"+ document.domain +"/orden-trabajos/siembra/" + orden_trabajo_id;
            window.location.href = ruta;          
        };
        toastr.info('Se guardó el registro el registro correctamente.');
    });

});

$(".select2").select2({
     theme: "bootstrap",
     width: '100%'
});

/* Configuro el Select de Proveedores */
$("#proveedore-id").select2({
    theme: "bootstrap",
    placeholder: "Ingrese el proveedor ...",
    minimumInputLength: 5,
    ajax: {
        url: "/proveedores/search",
        dataType: 'json',
        data: function (params) {
            var query = {
                q: params.term,
                establecimiento: $('#establecimiento-id').val()
            };
            return query;
        },
        processResults: function (data, params) {
            return { results: data.proveedores };
        }
    }
});


/* -----------------------------------------------------------------------------
 * Duplicar la linea
 *----------------------------------------------------------------------------*/
$('#dt_ordentrabajo').on('click', 'a.duplicar', function (e) {
    e.preventDefault();
    
    var tr = $(this).closest('tr');
    var table = $('#dt_ordentrabajo').DataTable();
    var row = table.row(tr).data();
    
    /* Quito el lote de lo que duplico */
    lotes.map((lote, index) => {
        if (lote.id === row.lote.id) {
            lote.disabled = true;
        }
    });
    
    defaultValues = new Array();
    defaultValues = row;
    defaultValues.id = null;
    
    $('#tarifario').val(defaultValues.importe.tarifario ? defaultValues.importe.tarifario : '');
    
    $('.create-contractor').trigger('click');
});

/* Esta funcion es la que permite la edicion del DataTable */
(function (factory) {
    if (typeof define === 'function' && define.amd) {
        // AMD
        define(['jquery', 'datatables.net'], function ($) {
            return factory($, window, document);
        });
    }
    else if (typeof exports === 'object') {
        // CommonJS
        module.exports = function (root, $) {
            if (!root) {
                root = window;
            }

            if (!$ || !$.fn.dataTable) {
                $ = require('datatables.net')(root, $).$;
            }

            return factory($, root, root.document);
        };
    }
    else {
        // Browser
        factory(jQuery, window, document);
    }
}(function ($, window, document, undefined) {
    'use strict';
    var DataTable = $.fn.dataTable;

    var _instance = 0;

    var altEditorTable = function (table, settings) {

        this.settings = settings;
        this.data = {
            /** @type {DataTable.Api} DataTables' API instance */
            table: new DataTable.Api(table),
            /** @type {String} Unique namespace for events attached to the document */
            namespace: '.altEditorTable' + (_instance++),
            instance: _instance
        };

        this.dom = {
            /** @type {jQuery} altEditor handle */
            modal: $('<div class="dt-altEditorTable-handle"/>')
        };

        /* Constructor logic */
        this._constructor();

    };

    $.extend(altEditorTable.prototype, {
        _constructor: function () {

            var isEditing = null;
            var actionOriginal = null;
            var that = this;
            var dt = this.data.table;
            var acc = dt; // (jqTds[that.settings.columnAction]).html();
            this._setup();

            dt.on('destroy.altEditorTable', function () {
                dt.off('.altEditorTable');
                $(dt.table().body()).off(that.data.namespace);
                $(document.body).off(that.data.namespace);
                $(dt.body()).off("click", "td");
            });
        },
        _setup: function () {
            
            var that = this;
            // Add nueva linea 
            $('.' + this.settings.createCssEvent).click(function (e) {
                e.preventDefault();
                if (that.isEditing !== null && that.isEditing !== nRow && (typeof that.isEditing !== 'undefined')) {
                    that._restoreRow(that.isEditing);
                    that.isEditing = null;
                }
                $(this).attr("disabled", true);
                var objectRow = new Object();
                if (typeof that.settings.temporalId !== 'undefined') {
                    objectRow[that.settings.temporalId] = Math.round(Math.random() * (500 - 0) + parseInt(0));
                }
                /* Valores por defecto */
                if (typeof that.settings.defaultValues !== 'undefined') {
                    /* Hardcoded - FIXIT */
                    objectRow['cc'] = defaultValues.cc;
                    objectRow['dosis'] = defaultValues.dosis;
                    objectRow['has'] = 0;
                    objectRow['importe'] = defaultValues.importe;
                    objectRow['insumos'] = defaultValues.insumos;
                    objectRow['labor'] = defaultValues.labor;
                    objectRow['labor_id'] = defaultValues.labor_id;
                    objectRow['lote'] = 0;
                    objectRow['moneda'] = defaultValues.moneda;
                    objectRow['tecnica'] = defaultValues.tecnica;
                    objectRow['unmedida'] = defaultValues.unmedida;
                    
                    //var lt_deshabilitados = {0:0, 1:1, 2:2, 3:3, 6:6, 7:7, 8: 8};
                    var lt_deshabilitados = {7:7, 8: 8};
                    that.settings.disabledFields = lt_deshabilitados;
                }
                
                var newRow = that.data.table.row.add(objectRow).draw(false);
                var nRow = that.data.table.row(newRow).node();
                that._editRow(nRow);
                that.isEditing = nRow;
                that.data.table.page('last').draw(false);
               // disabledFields
                if (that.settings.disabledFields !== 'undefined') {
                    var jqTds = $('>td', nRow);
                    that._disabledFields( jqTds, that.settings.disabledFields );
                }
            });

            //Delete Row
            this.data.table.on("click", 'a.delete', function (e) {
                e.preventDefault();

                var tr = $(this).closest('tr');
                var row = that.data.table.row(tr);
                var aData = that.data.table.row(row).data();
                var jqTds = $('>td', row);
                that.actionOriginal = $(jqTds[that.settings.columnAction]).html();
                $(jqTds[that.settings.columnAction]).html('<i class="fa fa-spin fa-refresh text-success"></i>');
                that.settings.onUpdate(that.data.table, aData, true, this.actionOriginal, $(jqTds[that.settings.columnAction]));
                that.data.table.row(tr).remove().draw();
                this.actionOriginal = null;
                
                /* Averiguo si habilito o no el boton de Ejecutar OT */
                var nFilas = $(".contractors-table tr").length;
                var nColumnas = $(".contractors-table tr:last td").length;
                if (nFilas > 2 || nColumnas > 1) {
                    $('.EjecutarOT').attr("disabled", false);
                } else {
                    $('.EjecutarOT').attr("disabled", true);
                }
                
            });

            //Cancel Editing or Adding a Row
            this.data.table.on("click", 'a.cancel', function (e) {
                e.preventDefault();

                var tr = $(this).closest('tr');
                var row = that.data.table.row(tr);
                var aData = that.data.table.row(row).data();
                
                if (that.settings.onCancel) {
                    that.settings.onCancel(that.data.table, aData);
                }
                
                that._restoreRow(that.isEditing);
                that.isEditing = null;
                
                $('.' + that.settings.createCssEvent).attr("disabled", false);
            });

            //Edit A Row
            this.data.table.on("click", 'a.edit', function (e) {
                e.preventDefault();

                var tr = $(this).closest('tr');
                var row = that.data.table.row(tr);
                
                if (that.isEditing !== null && that.isEditing != tr && (typeof that.isEditing !== 'undefined')) {
                    that._restoreRow(that.isEditing);
                    that._editRow(tr);
                    that.isEditing = tr;
                } else {
                    that._editRow(tr);
                    that.isEditing = tr;
                }
            });

            //Save an Editing Row
            this.data.table.on("click", 'a.save', function (e) {
                e.preventDefault();
                if (!that._saveRow(that.isEditing)) {
                    that.isEditing = null;
                }
                //Some Code to Highlight Updated Row
            });
        },
        _restoreRow: function (nRow) {
            var aData = this.data.table.row(nRow).data();
            var jqTds = $('>td', nRow);

            var that = this;
            if (typeof aData.id !== 'undefined') {
                var columnsDatatables = this.data.table.settings().init().columns;
                var columnIndex = 0;
                $.each(columnsDatatables, function (index, column) {
                    if ((typeof column.visible === 'undefined') || !column.visible === false) {
                        if ((typeof column.className === 'undefined') || column.className.indexOf('no-edit') === -1) {

                            var cell = that.data.table.row(nRow).cell(jqTds[columnIndex]);
                            try {
                                var fields = column.data.split('.');
                                if (typeof fields[1] === 'undefined') {
                                    if (typeof aData[column.data] === 'undefined') {
                                        cell.data(null);
                                    } else {
                                        cell.data(aData[column.data]);
                                    }
                                } else {
                                    if (typeof aData[fields[0]] !== 'undefined'){
                                        cell.data(aData[fields[0]][fields[1]]);
                                    } else {
                                        cell.data(null);
                                    }
                                }
                            } catch (e) {
                                cell.data(aData[column.data]);
                            }
                        }
                        columnIndex++;
                    }
                });

                $(jqTds[this.settings.columnAction]).html(this.actionOriginal);
                $(jqTds[this.settings.columnAction]).find('.tooltip').remove();
                this.actionOriginal = null;
                this.data.table.draw();
            } else {
                that.data.table.row(nRow).remove().draw();
            }
            
            /* Averiguo si habilito o no el boton de Ejecutar OT */
            var nFilas = $(".contractors-table tr").length;
            var nColumnas = $(".contractors-table tr:last td").length;
            if (nFilas > 2 || nColumnas > 1) {
                $('.EjecutarOT').attr("disabled", false);
            } else {
                $('.EjecutarOT').attr("disabled", true);
            }
        },
        // Edit Row
        _editRow: function (nRow) {
            var that = this;
            var jqTds = $('>td', nRow);
            var columnsDatatables = this.data.table.settings().init().columns;
            var columnIndex = 0;
            var first = true;
            if (typeof this.settings.onEdit !== 'undefined') {
                this.settings.onEdit(this.data.table, this.data.table.row(nRow).data());
            }
            
            /* Al editar una linea, deshabilito el Generar OT */
            $('.EjecutarOT').attr("disabled", true);
            
            $.each(columnsDatatables, function (index, column) {
                if ((typeof column.visible === 'undefined') || !column.visible === false) {
                    if ((typeof column.className === 'undefined') || column.className.indexOf('no-edit') === -1) {
                        var cell = that.data.table.row(nRow).cell(jqTds[columnIndex]);
                        // Show input
                        var input = that._getInputHtml(columnIndex, that.settings, cell.data(), column, jqTds, that.data.instance);
                        $(jqTds[columnIndex]).html(input.html);
                        if (first) {
                            first = false;
                        }
                    }
                    columnIndex++;
                }
            });

            if (that.settings.inputTypes) {
                $.each(that.settings.inputTypes, function (index, setting) {
                    if (typeof setting.disabledFields !== 'undefined') {
                        $("#select-" + that._getSelectorName(columnsDatatables[index].data) + that.data.instance).trigger("change");
                    }
                });
            }
            that.actionOriginal = $(jqTds[that.settings.columnAction]).html();
            $(jqTds[that.settings.columnAction]).html('<div class="btn-group"><a href="#" class="btn btn-success btn-xs save"><i class="fa fa-save"></i></a> <a href="#" class="btn btn-warning btn-xs cancel"><i class="fa fa-times"></i></a></div>');
            this.data.table.draw();
            try {
                $('.datepicker').datepicker({
                    language: 'es',
                    format: 'dd/mm/yyyy',
                    autoclose: true
                });

//                /* Meto el filtro especifico para insumos */
//                $(".select-inline-productos").select2({
//                    theme: "inline",
//                    placeholder: "Seleccione el insumo ...",
//                    minimumInputLength: 3,
//                    delay: 350,
//                    ajax: {
//                        url: "/productos/search",
//                        dataType: 'json',
//                        data: function ( params) {
//                            var query = {
//                                q: params.term,
//                                establecimiento: $('#establecimiento-id').val()
//                            };
//                            return query;
//                        }
////                        processResults: function (data, params) {
////                            return { results: data.productos };
////                        }                      
//                    },
//                    templateResult: function ( data ) {
//                        
//                        console.log('templateResult: ', data)
//                        
//                        if (data.loading) {
//                            return data.text;
//                        }
//                        let lote = '';
//                        let almacen = '';
//                        let existencia = '';
//                        let fecha = '';
//                        let unidad = '';
//                        
//                        if (data.cantidad !== null) {
//                            lote = data.lote ? data.lote : '';
//                            almacen = data.almacen ? data.almacen : '';
//                            existencia = data.cantidad ? Intl.NumberFormat().format(data.cantidad) : '';
//                            fecha = 'Stock al ' + moment( data.ultima_actualizacion).format("DD/MM/YYYY HH:mm");
//                            unidad = data.unidad ? data.unidad : '';
//                        }
//                        var $container = $(`<div><small class='pull-right'>${lote}</small>
//                                                  <strong>${data.text}</strong><br>
//                                                  <small class='pull-right'><strong> ${existencia} ${unidad}</strong></small><small>${almacen}</small><br>
//                                                  <small>${fecha}</small>
//                                            </div>`);
//                        return $container;    
//                    },
//                    templateSelection: function ( data ) {
//                        let texto = data.text ? data.text : data.text;
//                        let superficie = ''; /* data.has ? `(${data.has} has)` : ''; */
//                        let $container = $(`<div><small class='pull-right'>${superficie}</small>${texto}</div>`);
//                        return $container;
//                    } 
//                });
                 $(".select-inline-productos").select2({
                    theme: "inline",
                    placeholder: "Seleccione el insumo ...",
                    minimumInputLength: 3,
                    delay: 350,
                    ajax: {
                        url: "/productos/search",
                        dataType: 'json',
                        data: function ( params) {
                            var query = {
                                q: params.term,
                                establecimiento: $('#establecimiento-id').val()
                            };
                            return query;
                        }
//                        processResults: function (data, params) {
//                            console.log('processResults: ', data);
//                            return { results: data.productos };
//                        }                      
                    },
                    templateResult: function ( data ) {
                        
                        
                        if (data.loading) {
                            return data.text;
                        }
                        let lote = '';
                        let almacen = '';
                        let existencia = '';
                        let fecha = '';
                        let unidad = '';
                        
                        if (data.cantidad !== null) {
                            lote = data.lote ? data.lote : '';
                            almacen = data.almacen ? data.almacen : '';
                            existencia = data.cantidad ? Intl.NumberFormat().format(data.cantidad) : '';
                            fecha = 'Stock al ' + moment( data.ultima_actualizacion).format("DD/MM/YYYY HH:mm");
                            unidad = data.unidad ? data.unidad : '';
                        }
                         var $container = $(`<div><small class='pull-right'>${lote}</small>
                                                  <strong>${data.text}</strong><br>
                                                  <small class='pull-right'><strong> ${existencia} ${unidad}</strong></small><small>${almacen}</small><br>
                                                  <small>${fecha}</small>
                                            </div>`); 
                        return $container;                        
                    },
                    templateSelection: function ( data ) {
                        let texto = data.text ? data.text : data.text;
                        let superficie = ''; /* data.has ? `(${data.has} has)` : ''; */
                        let $container = $(`<div><small class='pull-right'>${superficie}</small>${texto}</div>`);
                        return $container;
                    }                      
                });
                $(".select-inline").select2({
                    theme: "inline"
                });
                
                $(".select-inline-lotes").select2({
                    theme: "inline",
                    data: lotes,
                    placeholder: "Seleccione un lote",
                    templateResult: function ( data ) {
                        if (data.loading) {
                            return data.nombre;
                        }
                        let superficie = data.has ? `(${data.has} has)` : '';
                        let sector = data.sector ? data.sector : '';
                        var $container = $(`<div><small class='pull-right'>${superficie}</small><strong>${data.nombre}</strong><br>
                                            <small>${sector}</small></div>`);
                        return $container;                        
                    },
                    templateSelection: function ( data ) {
                        let texto = data.nombre ? data.nombre : data.text;
                        let superficie = data.has ? `(${data.has} has)` : '';
                        let $container = $(`<div><small class='pull-right'>${superficie}</small>${texto}</div>`);
                        return $container;
                    }
                });
            } catch(e) {}
        },
        // Save Row
        _saveRow: function (nRow) {

            var that = this;
            var jqTds = $('>td', nRow);
            var l_error = false;
            var l_globalError = false;

            var columnsDatatables = this.data.table.settings().init().columns;
            var columnIndex = 0;
            $.each(columnsDatatables, function (index, column) {
                if ((typeof column.visible === 'undefined') || !column.visible === false) {
                    if ((typeof column.className === 'undefined') || column.className.indexOf('no-edit') === -1) {

                        var cell = that.data.table.row(nRow).cell(jqTds[columnIndex]);
                        var inputField = that._getInputField(jqTds[columnIndex]);
                        l_error = false;

                        if (that.settings.errorClass) {
                            $(inputField).removeClass(that.settings.errorClass);
                        } else {
                            $(inputField).removeClass('edit-input-error');
                        }
                        var l_parent = $(inputField).parent();
                        $(l_parent).find(".error-message").remove();

                        if (((that.settings.validations) && that.settings.validations !== true)) {
                            $.each(that.settings.validations, function (index, setting) {
                                if (setting.column === columnIndex) {
                                    if (_.has(setting, 'allowNull') && !setting.allowNull) {
                                        if (inputField.val() === '' || inputField.val() === "0" ) {
                                            that._validationError(inputField, setting.message);
                                            l_error = true;
                                        }
                                    }
                                    if (_.has(setting, 'method') && setting.method !== '' && !l_error && inputField.val() !== '') {
                                        if (!setting.method(inputField.val())) {
                                            that._validationError(inputField, setting.message);
                                            l_error = true;
                                        }
                                    }
                                    return true;
                                }
                            });

                            if (!l_error) {
                                if (inputField.prop('type') === 'checkbox') {
                                    if (inputField[0].checked) {
                                        cell.data(true);
                                    } else {
                                        cell.data(false);
                                    }
                                } else if (inputField.prop('type') === 'date') {
                                    var l_date = inputField.datepicker('getDate');
                                    cell.data({display: inputField.val(), timestamp: l_date.getTime()});
                                } else {
                                    cell.data(inputField.val());
                                }
                            } else {
                                l_globalError = l_error;
                            }
                        } else {
                            //_update(newValue);
                            if (inputField.prop('type') === 'checkbox') {
                                if (inputField[0].checked) {
                                    cell.data(true);
                                } else {
                                    cell.data(false);
                                }
                            } else {
                                cell.data(inputField.val());
                            }
                        }
                    }
                    columnIndex++;
                }
            });

            if (!l_globalError) {
                var cell = this.data.table.row(nRow).cell(jqTds[this.settings.columnAction]);
                $(jqTds[this.settings.columnAction]).html(this.actionOriginal);
                $(jqTds[this.settings.columnAction]).html('<i class="fa fa-spin fa-refresh text-success"></i>');
                var aData = this.data.table.row(nRow).data();
                this.settings.onUpdate(this.data.table, aData, false, this.actionOriginal, $(jqTds[this.settings.columnAction]), this.settings.temporalId, nRow);
                this.actionOriginal = null;
            }
            this.data.table.draw();
            
            /* Deshabilito el boton que creo la linea */
            $('.' + this.settings.createCssEvent).attr("disabled", false);
            
            return l_globalError;

        },
        _validationError: function (inputField, message) {

            if (this.settings.errorClass) {
                $(inputField).addClass(this.settings.errorClass);
            } else {
                $(inputField).addClass('edit-input-error');
            }
            $("<div class='animated fadeInDown text-danger dfn remove-margin-b error-message'>" + message + "</div>").insertAfter(inputField);

        },
        _getInputHtml: function (currentColumnIndex, settings, oldValue, column, jqTds, instance) {
            var inputSetting, inputType, input, inputCss, confirmCss, cancelCss, ColumnCss;
            input = {"focus": true, "html": null};
            
            if (settings.inputTypes) {
                $.each(settings.inputTypes, function (index, setting) {
                    if (setting.column == currentColumnIndex) {
                        inputSetting = setting;
                        inputType = inputSetting.type.toLowerCase();
                        ColumnCss = inputSetting.class;
                    }
                });
            }
            if (settings.inputCss) {
                inputCss = settings.inputCss;
                if (ColumnCss !== 'undefined') {
                    inputCss = inputCss + ' ' + ColumnCss;
                }
            }
            if (settings.confirmationButton) {
                confirmCss = settings.confirmationButton.confirmCss;
                cancelCss = settings.confirmationButton.cancelCss;
                inputType = inputType + "-confirm";
            }
            switch (inputType) {
                case "select":  /* Esto es para un select - HARDCODE - FIXIT */
                    input.html = "<select class=' " + inputCss + " select-" + column.data + instance + "' id='select-" + column.data + instance + "' value='" + oldValue + "'>";
                    if (oldValue == 'null') {
                        oldValue = null;
                    }
                    if (typeof oldValue === 'object') {
                        oldValue = oldValue.id;
                    }
                    $.each(labores, function (index, option) {
                        /* No hay ningun filtro */
                        input.html = input.html + "<option value='" + option.id + "'";
                        if (option.id == 'null') {
                            option.id = null;
                        }
                        if (oldValue == option.id) {
                            input.html = input.html + " selected ";
                        }
                        input.html = input.html + ">" + option.nombre + "</option>";
                    });
                    input.html = input.html + "</select>";
                    input.focus = false;
                    /*********************************************************************************
                     *  EVENTOS 
                     ********************************************************************************/
                    if (typeof inputSetting.callBack !== 'undefined'){
                        $(document).on('change', '#select-' + this._getSelectorName(column.data) + instance, {
                            domTD: jqTds, settings: inputSetting, columna: currentColumnIndex
                        }, function (events) {
                            events.data.settings.callBack(this.value, events );
                        });                        
                    }
                    break;
                case "list":
                    /* Filtro el listado de lotes, asi evito mostrar el que ya fue duplicado */
                    if (typeof inputSetting.precallBack !== 'undefined'){
                        var lista = inputSetting.precallBack;
                        var list = new Array();
                        lista.map((value) => {
                            if (typeof value !== 'undefined') {
                                if (typeof value.disabled !== 'undefined') {
                                    list.push({value: value.id, display: value.nombre, disabled: true});
                                } else {
                                    list.push({value: value.id, display: value.nombre});
                                }
                            }
                        });
                        inputSetting.options = list;
                    }
                    
                    input.html = "<select class=' " + inputCss + " select-" + column.data + instance + "' id='select-" + column.data + instance + "' value='" + oldValue + "'>";
                    if (oldValue == 'null') {
                        oldValue = null;
                    }
                    if (typeof oldValue === 'object') {
                        oldValue = oldValue.id;
                    }
                    $.each(inputSetting.options, function (index, option) {
                        /* No hay ningun filtro */
                        input.html = input.html + "<option value='" + option.value + "'";
                        if (option.value == 'null') {
                            option.value = null;
                        }
                        if (oldValue == option.value) {
                        //if (oldValue == option.display) {
                            input.html = input.html + " selected ";
                        }
                        if (typeof option.disabled !== 'undefined') {
                            input.html = input.html + " disabled = 'true' ";
                        }
                        input.html = input.html + ">" + option.display + "</option>";
                    });
                    input.html = input.html + "</select>";
                    input.focus = false;
                    /*********************************************************************************
                     *  EVENTOS 
                     * Capturo los eventos de cada objeto, para eso, defino en la columna un callBack,
                     * de esa manera puedo capturar un evento del obtejo.
                     ********************************************************************************/
                    if (typeof inputSetting.callBack !== 'undefined'){
                        $(document).on('change', '#select-' + this._getSelectorName(column.data) + instance, {
                            domTD: jqTds, settings: inputSetting, columna: currentColumnIndex
                        }, function (events) {
                            events.data.settings.callBack(this.value, events );
                        });                        
                    }
                    break;
                case "number": // text input w/ confirm
                    if (typeof oldValue === 'object') {
                        /* Hard Code - FIXIT */
                        oldValue = oldValue.importe;
                    }
                    input.html = "<input autofocus type='text' class='" + inputCss + " text-right " + column.data + instance + "' id='input-" + column.data + instance + "' value='" + oldValue + "'></input>";
                    /* Capturo los eventos de los inputs */
                    if (typeof inputSetting.callBack !== 'undefined'){
                        $(document).on('keyup', '#input-' + this._getSelectorName(column.data) + instance, {
                            domTD: jqTds, settings: inputSetting, columna: currentColumnIndex
                        }, function (events) {
                            events.data.settings.callBack(this.value, events, $(this).select2('data'));
                        });
                    };
                    break;
                case "checkbox": // text input w/ confirm
                    var l_check = '';
                    if (oldValue) {
                        l_check = 'checked';
                    }
                    input.html = "<input id='check" + instance + "_" + currentColumnIndex + "' type='checkbox' class='" + inputCss + "' " + l_check + "></input> <label for='check" + instance + "_" + currentColumnIndex + "'></label>";
                    break;
                case "text-confirm": // text input w/ confirm
                    //input.html = "<input id='ejbeatycelledit' class='" + inputCss + "' value='"+oldValue+"'></input>&nbsp;<a href='#' class='" + confirmCss + "' onclick='$(this).updateEditableCell(this)'>Confirm</a> <a href='#' class='" + cancelCss + "' onclick='$(this).cancelEditableCell(this)'>Cancel</a> ";
                    input.html = "<input autofocus class='" + inputCss + " " + column.data + instance + "' value='" + oldValue + "'></input>";
                    break;
                case "undefined-confirm": // text input w/ confirm
                    //input.html = "<input id='ejbeatycelledit' class='" + inputCss + "' value='" + oldValue + "'></input>&nbsp;<a href='#' class='" + confirmCss + "' onclick='$(this).updateEditableCell(this)'>Confirm</a> <a href='#' class='" + cancelCss + "' onclick='$(this).cancelEditableCell(this)'>Cancel</a> ";
                    input.html = "<input autofocus class='" + inputCss + "' value='" + oldValue + "'></input>";
                    break;
                case "date":
                    var data;
                    if (typeof column.data == 'object') {
                        data = column.data._;
                    } else {
                        data = column.data;
                    }
                    input.html = "<input type='text' id='" + data + instance + "'  name='" + data + instance + "'  required='required' class='" + inputCss + " form-control input-inline datepicker form-control valid' data-provide='datepicker' data-date-format='dd/mm/yyyy'  data-date-language='es' value='" + oldValue + "' aria-required='true' aria-invalid='false' >";
                    break;
                default: // text input
                    input.html = "<input autofocus class='" + inputCss + " " + column.data + instance + "' value='" + oldValue + "'></input>";
                    break;
            }
            return input;
        },
        _getInputField: function (callingElement) {
            // Update datatables cell value
            var inputField;
            switch ($(callingElement).prop('nodeName').toLowerCase()) {
                case 'td': // This means they're using confirmation buttons
                    if ($(callingElement).find('input').length > 0) {
                        inputField = $(callingElement).find('input');
                    }
                    if ($(callingElement).find('select').length > 0) {
                        inputField = $(callingElement).find('select');
                    }
                    if (typeof inputField == 'undefined') {
                        inputField = $(callingElement).find('input');
                    }
                    break;
                default:
                    inputField = $(callingElement).find('input');
            }
            return inputField;
        },
        _getSelectorName: function (name) {
            var fields = name.split('.');
            if (typeof fields[1] == 'undefined') {
                return name;
            } else {
                return fields[0] + '\\.' + fields[1];
            }
        },
        _disabledFields: function ( domTD, elementos ) {
            $.each(domTD, function (index, td) {
                var inputField;
                if ($(td).find('input').length > 0) {
                    inputField = $(td).find('input');
                }
                if ($(td).find('select').length > 0) {
                    inputField = $(td).find('select');
                }
                try {
                    if (typeof elementos[index] !== 'undefined') {
                        $(inputField).prop('disabled', true);
                       // $(inputField).val(null);
                    }
                } catch (e) {
                    $(inputField).prop('disabled', false);
                }
            });            
        }
    });

    // Alias for access
    DataTable.altEditorTable = altEditorTable;

    return altEditorTable;
}));

/* Lista y edicion de Contratistas */
var ContractorListEdit = function() {

    //var dataTableContractors = new Array();
    var $table;
    var $subTable;
    
    var initDataContractors = function() {
       /* Esta rutina carga todos los datos al array, para poder usarlo, deberiamos
        * Generar una llamada Ajax con el array, o pasarlo desde el controller en el 
        * formato que deseamos.
        */ 
        dataTableContractors = new Array();
        dataTableInsumos = new Array();
        
        /* Ahora paso todos los registros a los lotes */
        var insumos = OrdenTrabajo['insumos'];
        for (var i = 0; i< insumos.length; i++){
            dataTableInsumos.push(
                {
                    producto: insumos[i]['producto'].nombre,
                    unidad: insumos[i]['unidade'].nombre,
                    dosis: insumos[i].dosis,
                    cantidad: insumos[i].cantidad,
                    almacen: insumos[i]['almacene'].nombre,
                    lote: insumos[i]['productos_lote'] ? insumos[i]['productos_lote'].nombre : '',
                    id: insumos[i].id,
                    id_distribuciones: insumos[i].orden_trabajos_distribucione_id,
                    entregas: insumos[i]['orden_trabajos_insumos_entregas'].length,
                    entrega: insumos[i].entrega,
                    devolucion: insumos[i].devolucion,
                    dosis_aplicada: insumos[i].utilizado
                }
            );
        }            

        distribucion = new Array;
        distribucion = OrdenTrabajo['distribucion'];
        
        var tecnica;
        for (var i = 0; i< distribucion.length; i++){
            tecnica = '-';
            if (distribucion[i]['tecnicas_aplicacione'] !== null) {
                tecnica = {
                    id: distribucion[i]['tecnicas_aplicacione'].id,
                    nombre: distribucion[i]['tecnicas_aplicacione'].nombre
                };
            }
            dataTableContractors.push(
                {
                    labor: {id: distribucion[i]['proyectos_labore'].id, nombre: distribucion[i]['proyectos_labore'].nombre},
                    unmedida: {id: distribucion[i]['unidade'].id, nombre: distribucion[i]['unidade'].nombre},
                    cc: {id: distribucion[i]['proyecto'].id, nombre: distribucion[i]['proyecto'].nombre},
                    lote: {id: distribucion[i]['lote'].id, nombre: distribucion[i]['lote'].nombre},
                    tecnica: tecnica,
                    has: distribucion[i]['superficie'],
                    dosis: '1',
                    total: distribucion[i]['superficie'],
                    moneda: {id: distribucion[i]['moneda'].id, nombre: distribucion[i]['moneda'].simbolo},
                    importe: {importe: distribucion[i].importe, tarifario: distribucion[i].orden_trabajos_distribuciones_tarifario ? distribucion[i].orden_trabajos_distribuciones_tarifario.proyectos_labores_tarifario_id : ''},
                    insumos: distribucion[i]['proyectos_labore'].insumos,
                    labor_id: distribucion[i]['proyectos_labore'].id,
                    id: distribucion[i]['id']
                }
            );
        }
    };
    
    var mapOptionsList = function(values) {
        /* Recibe un array y carga todos los datos en un list */
        var list = new Array();
        $.each(values, function(index, value) {
            if (typeof value !== 'undefined') {
                list.push({value: value.id, display: value.nombre});
            }
        });
        return list;
    };
        
    var mapOptionSelect2 = function (values) {
        /* Recibe un array y carga todos los datos en un list */
        var list = new Array();
        $.each(values, function(index, value) {
            if (typeof value !== 'undefined') {
                list.push({id: value.id, text: value.nombre});
            }
        });
        return list;
    };
    
    var reloadDataLabores = function() {
        /* Recarga los datos */
        $("#table-loader").addClass('hidden');
        /* Recupero los datos de la OT actual */
        let id = $('#id').val();
        fetch(`/orden-trabajos/siembra/${ id }.json`)
            .then( res => res.json() )
            .then( data => {
                OrdenTrabajo['distribucion'] = data.ordenTrabajo.orden_trabajos_distribuciones;

                var labor = data.labores;
                labores.push({id: 0, nombre: 'Seleccione una labor' });
                $.each(labor, function(index, row) {
                    labores.push(
                        {
                            id: row.proyectos_labore_id,
                            nombre: row.proyectos_labore ? row.proyectos_labore.nombre : '',
                            insumos:  row.proyectos_labore ? row.proyectos_labore.insumos : ''
                        }
                    );
                });
                
                OrdenTrabajo['insumos'] = data.ordenTrabajo.orden_trabajos_insumos;
                initDataContractors();
                
                $table.clear();
                $table.rows.add(dataTableContractors);
                $table.draw();
                
                $subTable.clear();
                $subTable.rows.add(dataTableInsumos);
                $subTable.draw();
                
            });        

    };
    
    var updateDataContractors = function(RowContractor) {
        var l_index = 0;
        var l_new = true;
        var l_DataNewContractors = new Array();
        $.each(DataContractors, function(index, row) {
            if (row.id == RowContractor.id) {
                DataContractors[index].labor = RowContractor.labor;
                DataContractors[index].unmedida = RowContractor.unmedida;
                DataContractors[index].cc = RowContractor.cc;
                DataContractors[index].has = RowContractor.has;
                DataContractors[index].dosis = RowContractor.dosis;
                DataContractors[index].total = RowContractor.total;
                DataContractors[index].insumos = RowContractor.insumos;
                DataContractors[index].moneda = RowContractor.moneda;
                DataContractors[index].importe = RowContractor.importe;
                DataContractors[index].labor_id = RowContractor.labor_id;
                DataContractors[index].tecnica = RowContractor.tecnica;
                DataContractors[index].id = RowContractor.id;
                
                /* Calculo el total de hectareas */
                l_index = index;
                l_new = false;
                return true;
            }
        });
        if (l_new) {
            l_DataNewContractors.push(
                    {
                        labor: RowContractor.labor,
                        unmedida: RowContractor.unmedida,
                        cc: RowContractor.cc,
                        lote: RowContractor.lote,
                        has: RowContractor.has,
                        dosis: RowContractor.dosis,
                        total: RowContractor.total,
                        insumos: RowContractor.insumos,
                        moneda: RowContractor.moneda,
                        importe: RowContractor.importe,
                        labor_id: RowContractor.labor_id,
                        tecnica: RowContractor.tecnica,
                        id: RowContractor.id
                    });
            return l_DataNewContractors[l_DataNewContractors.length - 1];
        } else {
            return DataContractors[l_index];
        }
    };

    var initTable = function() {
        var actionsTemplate = _.template($("#row-actions-template").text());
        $table = $(".contractors-table").DataTable({
            pageLength: 20,
            destroy: true,
            deferRender: false,
            data: dataTableContractors,
            dom: "<'row'<'col-sm-offset-10 botones-tabla-top-right'B>>" +
                            "<'row'<'col-sm-12'tr>>",
            buttons: [ {
                            /* Agregar Item a la Orden de Trabajo */
                            "text": "<i class='fa fa-plus'></i>",
                            "titleAttr": "Agregar lina",
                            "className": "btn create-contractor"
                        }],
            columns: [{
                    className: 'details-control-contractors',
                    data: 'cc',
                    sortable: false,
                    responsivePriority: 1,
                    defaultContent: '',
                    width: '25%',
                    render: function(data, type, row) {
                        if (!data) {
                               return '';
                        }
                        if (type == 'display' || type == 'type') {
                            return data.nombre;
                            
                        }
                        return data.nombre;
                    }
                },{
                    data: 'labor',
                    sortable: false,
                    responsivePriority: 2,
                    defaultContent: '',
                    render: function(data, type, row) {
                        if (!data) {
                                return '';
                            }
                        if (type == 'display' || type == 'type') {
                            $.each(labores, function(index, value) {
                                if (data.id == value.id) {
                                    return value.nombre;
                                }
                            });                            
                        }
                        return data.nombre;
                    }                    
                },{
                    data: 'unmedida',
                    sortable: false,
                    responsivePriority: 2,
                    defaultContent: '',
                    render: function(data, type, row) {
                        if (!data) {
                            return '';
                        }
                        if (type == 'display' || type == 'type') {
                            return data.nombre;
                        }
                        return data.nombre;
                    }
                },{
                    data: 'tecnica',
                    sortable: false,
                    responsivePriority: 1,
                    defaultContent: '',
                    render: function(data, type, row) {
                        if (!data) {
                                return '';
                            }
                        if (type == 'display' || type == 'type') {
                            $.each(tecnicas, function(index, value) {
                                if (data === value.id) {
                                    return value.nombre;
                                }
                            });                            
                        }
                        return data.nombre;
                    }
                },{
                    data: 'lote',
                    sortable: false,
                    responsivePriority: 1,
                    defaultContent: '',
                    render: function(data, type, row) {
                        if (!data) {
                                return '';
                            }
                        if (type == 'display' || type == 'type') {
                            return data.nombre;
                        }
                        return data;
                    }
                },{
                    data: 'has',
                    class:'text-right',
                    sortable: false,
                    responsivePriority: 1,
                    defaultContent: ''
                },{
                    data: 'moneda',
                    sortable: false,
                    responsivePriority: 1,
                    render: function(data, type, row) {
                         if (!data) {
                                return '';
                            }
                        if (type == 'display' || type == 'type') {
                            $.each(monedas, function(index, value) {
                                if (data.id === value.id) {
                                    return value.simbolo;
                                }
                            });                            
                        }
                        return data.nombre;
                    },
                    defaultContent: ''
                },{
                    data: 'importe',
                    class:'text-right',                    
                    sortable: false,
                    responsivePriority: 1,
                    defaultContent: '0',
                    render: function(data, type, row) {
                         if (!data) {
                                return '';
                            }
                        if (type == 'display' || type == 'type') {
                            return data.importe ? data.importe : '';
                        }
                        return data.importe ? data.importe : '0';
                    }
                },{
                    data: 'insumos',
                    sortable: false,
                    responsivePriority: 1,
                    defaultContent: '',
                    visible: false
                },{
                    data: 'labor_id',
                    sortable: false,
                    className: 'cell-triple-action no-custo no-edit',
                    render: function(data, type, row) {
                        if(row.insumos){
                            return formatButtonDetails(row);
                        } else {
                            return actionsTemplate({});
                        }
                        return data;
                    },
                    responsivePriority: 1                   
                },{
                    data: 'id',
                    defaultContent: '0',
                    visible: false
                },{
                    data: 'temporalId',
                    defaultContent: '0',
                    visible: false
                }],
                ordering: false,
                footerCallback: function ( row, data, start, end, display ) {
                    var api = this.api(), data;
                    // Remove the formatting to get integer data for summation
                    var intVal = function ( i ) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '')*1 :
                            typeof i === 'number' ?
                                i : 0;
                    };

                    /* Calculo la superficie */
                    var total = api
                               .column( 5 )
                               .data()
                               .reduce( function (a, b) {
                                    return intVal(a) + intVal(b);
                               },0);
  
                    /* Actualizo el footer */
                    $(api.column(5).footer()).html(`( ${total.toFixed(2)} )`);
                }
        });
        
        var actionsTemplateMachine = _.template($("#row-actions-template-machine").text());   
        $subTable = $(".insumos-table").DataTable({
            pageLength: 25,
            data: dataTableInsumos,
            ordering: false,
            deferRender: false,
            dom: "<'row'<'col-sm-offset-10 botones-tabla-top-right'B>>" +
                            "<'row'<'col-sm-12'tr>>",
            buttons: [{
                            /* Agregar Item a la Orden de Trabajo */
                            "text": "<i class='fa fa-plus'></i>",
                            "titleAttr": "Agregar Insumos",
                            "className": "btn AddInsumos"
                      }],
            columns: [{
                    data: 'producto',
                    defaultContent: '',
                    sortable: false,
                    width: '20%'
                },{
                    data: 'lote',
                    defaultContent: '',
                    sortable: false,
                    width: '10%'
                },{
                    data: 'unidad',
                    sortable: false,
                    defaultContent: '',
                    width: '10%'
                },{
                    data: 'dosis',
                    defaultContent: '',
                    sortable: false,
                    width: '10%'
                },{
                    data: 'cantidad',
                    defaultContent: '',
                    sortable: false,
                    width: '10%'
                },{
                    data: 'entrega',
                    sortable: false,
                    defaultContent: '',
                    class: 'text-center'
                },{
                    data: 'devolucion',
                    sortable: false,
                    defaultContent: '',
                    class: 'text-center'
                },{
                    data: 'dosis_aplicada',
                    sortable: false,
                    defaultContent: '0',
                    class: 'text-center'
                },{
                    data: 'almacen',
                    sortable: false,
                    defaultContent: '0',
                    width: '15%'
                },{
                    data: 'id',
                    sortable: false,
                    className: 'text-center cell-double-action no-custo no-edit',
                    render: function(data, type, row) {
                        if (type === 'display' || type === 'filter'){
                            return actionsTemplateMachine({});
                        }
                    },
                    responsivePriority: 1,
                    defaultContent: ''
                },{
                    data: 'distribucionId',
                    sortable: false,
                    visible: false,
                    defaultContent: 0
                },{
                    data: 'temporalId',
                    defaultContent: 0,
                    visible: false
                }]
        });
        
        var lt_disabled = {1: 1, 2: 2, 3: 3, 4: 4, 5: 5, 6: 6, 7: 7, 8:8};
        var subTableEdit = new $.fn.dataTable.altEditorTable($subTable, {
            columnAction: 9,
            temporalId: 'temporalId',
            onEdit: callbackEditInsumos,
            onUpdate: callbackEditTableInsumos,
            inputCss: 'edit-input-inline',
            createCssEvent: 'AddInsumos',
            errorClass: 'edit-input-error',
            disabledFields: lt_disabled,
            columns: [0, 1, 2, 3, 4, 5, 6, 7, 8],
            inputTypes: [
                {
                    column: 0,
                    class: "select-inline-productos",
                    type: "list",
                    callBack: callBackProductos,
                    options: mapOptionsList(productos),
                    width: '35%'
                }, {
                    column: 1,
                    class: "select-inline-productos",
                    type: "list",
                    options: mapOptionsList(productos_lotes),
                    width: '15%'
                }, {
                    column: 2,
                    type: "list",
                    class: "select-inline",
                    options: mapOptionsList(unidades),
                    width: '15%'
                }, { /* Dosis */
                    column: 3,
                    type: "number",
                    callBack: callBackDosisInsumos
                }, { /* Cantidad - Sale de la dosis x has */
                    column: 4,
                    type: "number"
                }, { /* Entregado */
                    column: 5,
                    type: "number"
                }, { /* Devolucion */
                    column: 6,
                    type: "number"
                }, { /* Aplicado */
                    column: 7,
                    type: 'number'
                }, {
                    column: 8,
                    width: '10%',
                    type: "list",
                    class: "select-inline",
                    options: mapOptionsList(almacenes)
                }
            ]
        });
        
        var lt_disabled = {1: 1, 2: 2, 3: 3, 4: 4, 5: 5, 6: 6, 7: 7};
        /* Si se permite la edicion, habilito las tablas editables */
        var editTable = new $.fn.dataTable.altEditorTable($table, {
            columnAction: 8,
            onUpdate: callbackEditTableContractors,
            onEdit: callbackEditLabores,
            inputCss: 'edit-input-inline',
            createCssEvent: 'create-contractor',
            errorClass: 'edit-input-error',
            defaultValues: defaultValues,
            disabledFields: lt_disabled,
            columns: [1, 2, 3, 4, 5, 6, 7, 8],
            validations: [{
                    column: 1,
                    allowNull: false,
                    message: 'Unidad de Medida'
                },{
                    column: 2,
                    allowNull: false,
                    message: 'Centro de Costo'
                },{
                    column: 3,
                    allowNull: false,
                    message: 'Lote'
                },{
                    column: 4,
                    allowNull: false,
                    message: 'Has'
                },{
                    column: 6,
                    allowNull: false,
                    message: 'Moneda'
                }],
            inputTypes: [
                { /* Proyectos */
                    column: 0,
                    type: "list",
                    class: "select-inline",
                    options: mapOptionsList(cc),
                    callBack: callBackProyectos
                    
                }, { /* Labores */
                    column: 1,
                    type: "select",
                    class: "select-inline disabled",
                    options: mapOptionsList(labores),
                    callBack: callBackProyectosLabores
                }, { /* Unidades de Medida */
                    column: 2,
                    type: "list",
                    class: "select-inline disabled",
                    /* callBack: 'unidades', */
                    options: mapOptionsList(unidades)
                }, { /* Tecnica */
                    column: 3,
                    type: "list",
                    class: "select-inline disabled",
                    //callBack: 'unidades',
                   options: mapOptionsList(tecnicas)
                }, { /* Lotes */
                    column: 4,
                    type: "list",
                    class: "select-inline-lotes",
                    precallBack: RefrescarLotes(),
                    options: mapOptionsList(lotes),
                    callBack: callBackLotes
                }, { /* Cantidad de Hectareas */
                    column: 5,
                    type: "number",
                    hectareas: $('#lote').val()
                }, { /* Tipo de Moneda */
                    column: 6,
                    type: "list",
                    class: "",
                    options: mapOptionsList(monedas)
                }, { /* Importe de Moneda */
                    column: 7,
                    type: "number"
                }
            ]
        });
    };
    
    var formatButtonDetails = function(callback) {
        var templateDetails = _.template($("#row-actions-insumos").text());
        
        return templateDetails({
            id: callback.id
        });
    }; 
    
    var callbackEditTableContractors = function(table, aData, flagDelete, actionOriginal, cellAction, temporalIdField, nRow) {
        var RowContractor = new Object;

        /* Se ejecuta al editar una tabla */
        if (flagDelete) {
            /* Elimino el registro actual */
            $("#table-loader").removeClass('hidden');
            $.ajax({
                type: 'POST',
                url: '/OrdenTrabajos/eliminarordentrabajo',
                data: {id: aData.id},
                success: function(response) {
                    /* Se guardó correctamente al parecer */
                    var data = JSON.parse(response);
                    
                    if (data['status'] === 'error') { /* Existe un error */
                        for(var i=0;i<data['message'].length;i++){
                            toastr.error(data['message'][i]);
                        }
                    } else {
                        toastr.info(data['message']); /* Se anuló correctamente */
                    }
                
                    reloadDataLabores();
                    $('.create-contractor').attr("disabled", false);
                    $(cellAction).html(actionOriginal);
                    $(cellAction).find('.tooltip').remove();
                }
            });            
        } else {
            /* Añadir un nuevo registro */
            RowContractor = aData;
            
            /* --------------------------------------------------------------- */
            /* Saco el guion de la tabla                                       */
            /* --------------------------------------------------------------- */
            var MisDatos = table.row(nRow).data();
            var labor = MisDatos.labor.split("-", 1);
            MisDatos.labor = labor[0];
            table.row(nRow).data( MisDatos ).draw();;
            console.log('RowContractor: ', RowContractor);
            
            RowContractor = updateDataContractors(RowContractor);
            var RowContractorPost = jQuery.extend({}, RowContractor);
            RowContractorPost['orden_trabajo_id'] = $("#id").val();
            RowContractorPost['tarifario'] = $('#tarifario').val();
            
            $("#table-loader").removeClass('hidden');
            /* Ahora guardo el dato generado mediante AJAX */
            const data = new FormData();
            data.append('datos', JSON.stringify(RowContractorPost));
            fetch(`/orden-trabajos-distribuciones/add`, {
                method: 'POST',
                body: data
            })
            .then( res => res.json())
            .then( data => {
                if (data.status = 'success') {
//                    /* Reviso si agrego la labor al array de labores */
//                    var encontrado = false;
//                    labores.map((value) => {
//                        console.log('Value: ', value);
//                        
//                    });
                    
                    /* Se guardó correctamente al parecer */
                    reloadDataLabores();
                    $('.create-contractor').attr("disabled", false);
                    $(cellAction).html(actionOriginal);
                    $(cellAction).find('.tooltip').remove();
                    $('#EjecutarOT').attr("disabled", false);
                    
                    $('#tarifario').val('');
                    return;
                }
                toastr.error(data.message);
            });            
        }
    };
    
    const callBackDosisInsumos = ( value, event ) => {
        if (value == 0) {
            return;
        }
        console.log('Event: ', event);
        
        $.each(event.data.domTD, function (index, td) {
            if (index === 4 ) { /* Ahora listo los proyectos por labores */
                var inputField;
                if ($(td).find('input').length > 0) {
                     inputField = $(td).find('input');
                }
                let TotalHas = 0;
                $.each(dataTableContractors, function(index, row) {
                    console.log('Linea de Contractors: ', row);
                    TotalHas += dataTableContractors[index].has;
                });
                let total = TotalHas * value;
                $(inputField).val(total.toFixed(2));
            }
        });        
    };
    
    /* Listado de Proyectos: busco todas las tareas relacionadas a este proyecto */
    const callBackProyectos = (value, event) => {
        if (value == 0) {
            return;
        }
        console.log('CallBackProyectos: ', event);
        
        $.each(event.data.domTD, function (index, td) {
            if (index === 1 ) { /* Ahora listo los proyectos por labores */
                var inputField;
                if ($(td).find('select').length > 0) {
                     inputField = $(td).find('select');
                }
                /* Marco deshabilitado el select */
                $(inputField).prop('disabled', true);
                
                let dataForm = new FormData();
                dataForm.append('establecimiento', $('#establecimiento-id').val() );
                dataForm.append('proveedor', $('#proveedore-id').val() );
                
                fetch(`/proyectos/listarlabores/${value}.json`, {
                    method: 'POST',
                    body: dataForm
                }).then( res => res.json() )
                  .then( data => {
                      
                      console.log('Listado de Labores: ', data);
                      
                        $(inputField).html('').select2({
                            placeholder: "Seleccione una labor",
                            data: data,
                            theme: 'inline',
                            width: 'resolve',
                            templateResult: function ( data, container ) {
                                if (data.loading) {
                                    return data.nombre;
                                }
                                let aclaracion = '';
                                if (data.tarifa) {
                                    $(container).css("border-left", "3px solid #1c84c6");
                                    aclaracion = data.tarifa.aclaracion_tarifa ? `<br><span class="small">${data.tarifa.aclaracion_tarifa}</span>` : '';
                                }
                                
                                var $container = data.tarifa ? $(`<div>
                                                                    <span class='pull-right'>${data.tarifa.moneda ? data.tarifa.moneda.simbolo : ''} ${data.tarifa.tarifa ? data.tarifa.tarifa : ''}</span>
                                                                    <strong>${data.text}</strong>${aclaracion}
                                                                  </div>`) : data.text;
                                return $container;
                            }
                        }).val([' ']).trigger('change');
                        
                        $(inputField).prop('disabled', false);                      
                    })
                    .catch( function(err) {
                        console.log( err );
                    });                    
            }
        });
    };
    
    /* Listado de Productos: busco la unidad de medida */
    const callBackProductos = (value, event, extraData) => {
        if (value == 0) {
            return;
        }
        var lt_disabled = {4: 4, 5: 5, 6: 6, 7: 7};
        /* Bloqueo todo lo que no sea necesario */
        marcarBloqueos ( event.data.domTD, lt_disabled );
        $.each(event.data.domTD, function (index, td) {
            /* Recupero los lotes de clasificacion asociadas al producto */
            if (index === 1 ) {
                var inputField;
                if ($(td).find('select').length > 0) {
                     inputField = $(td).find('select');
                }
                /* Marco deshabilitado el select */
                $(inputField).prop('disabled', true);
                fetch(`/productos/lotes/${value}.json`)
                    .then( res => res.json() )
                    .then( data => {
                        let lote_seleccionado = extraData[0].lote_id !== '' ? extraData[0].lote_id : '';
                        $(inputField).html('').select2({
                            data: data,
                            theme: 'inline',
                            width: 'resolve' 
                        }).val(lote_seleccionado).trigger('change');
                        $(inputField).prop('disabled', false);
                    })
                    .catch( function(err) {
                        console.log( err );
                    });                    
            }
            /* Recupero las unidades asociadas al producto */
            if (index === 2 ) {
                var inputField;
                if ($(td).find('select').length > 0) {
                     inputField = $(td).find('select');
                }
                /* Marco deshabilitado el select */
                $(inputField).prop('disabled', true);
                fetch(`/productos/unidades/${value}.json`)
                    .then( res => res.json() )
                    .then( data => {
                        $(inputField).html('').select2({
                            data: data,
                            theme: 'inline',
                            width: 'resolve' 
                        }).trigger('change');
                        $(inputField).prop('disabled', false);
                    })
                    .catch( function(err) {
                        console.log( err );
                    });                    
            }            
            if ( index === 8 ) { /* Pongo el almacen como predeterminado */
                var inputField;
                if ($(td).find('select').length > 0) {
                     inputField = $(td).find('select');
                }
                $(inputField).val( almacenes[0].id ).trigger('change');
            }
        });
    };
    
    /* Lista Labores: busco las unidades de medida y las tecnicas asociadas */    
    const callBackProyectosLabores = (value, event ) => {
        if (value == 0) {
            return;
        }
        var lt_disabled = {
            7: 7
        };

        var datos = [];
        var DatosCertificacion = [];
        
        /* Bloqueo todo lo que no sea necesario */
        marcarBloqueos ( event.data.domTD, lt_disabled ); 
        
        $.each(event.data.domTD, function (index, td) {
            /* Ahora listo las unidades */
            if (index === 1) {
                var inputField;
                if ($(td).find('select').length > 0) {
                    inputField = $(td).find('select');
                }
                DatosCertificacion = $(inputField).select2('data');
                
                /* Reviso si no esta la labor ya en el listado */
                DatosCertificacion.map((valor) => {
                    var existe = false;
                    labores.map((labor) => {
                        if (valor.labor_id === labor.id) {
                            existe = true;
                        }
                    });
                    if (!existe) {
                        labores.push({id: valor.labor_id, nombre: valor.text });
                    }
                });
            };
            
            /* Ahora listo las unidades */
            if (index === 2 ) {
                var inputField;
                if ($(td).find('select').length > 0) {
                     inputField = $(td).find('select');
                }
                /* Marco deshabilitado el select */
                $(inputField).prop('disabled', true);
                fetch(`/proyectos-labores/listarunidades/${value}.json`)
                    .then( res => res.json() )
                    .then( data => {
                        $(inputField).html('').select2({
                            data: data,
                            theme: 'inline'
                        });
                        datos['unidad'] = $(inputField).val();
                        
                        /* Miro si hay una unidad de medida en el tarifario */
                        if (DatosCertificacion[0].tarifa) {
                            let Unidad = DatosCertificacion[0].tarifa.unidades[0];
                            if ($(inputField).find("option[value='" + Unidad + "']").length) {
                                $(inputField).val(Unidad).trigger('change');
                                $(inputField).prop('disabled', true);
                            }
                            datos['unidad'] = Unidad;
                        } else {
                            $(inputField).prop('disabled', false);
                        }
                    })
                    .catch( function(err) {
                        console.log( err );
                    });
                if (!DatosCertificacion) {
                    datos['unidad'] = $(inputField).val();
                    datos['proveedor'] = $('#proveedore-id').val();
                    datos['establecimiento'] = $('#establecimiento-id').val();
                }
            }
            /* Ahora listo las tecnicas */
            if (index === 3 ) {
                var inputField;
                if ($(td).find('select').length > 0) {
                     inputField = $(td).find('select');
                }
                /* Marco deshabilitado el select */
                $(inputField).prop('disabled', true);
                fetch(`/proyectos-labores/tecnicas/${value}.json`)
                    .then( res => res.json() )
                    .then( data => {
                        $(inputField).html('').select2({
                            data: data,
                            theme: 'inline'
                        });
                        $(inputField).prop('disabled', false);
                    })
                    .catch( function(err) {
                        console.log( err );
                    });
            }
            if (index === 5 ) {
                var inputField;
                if ($(td).find('input').length > 0) {
                     inputField = $(td).find('input');
                }
                $(inputField).val(0);
            }
            if (index === 6 ) {
                var inputField;
                if ($(td).find('select').length > 0) {
                     inputField = $(td).find('select');
                }
                $(inputField).val(1);
            }
            if (index === 7 ) {
                var inputField;
                if ($(td).find('input').length > 0) {
                     inputField = $(td).find('input');
                }
                if (DatosCertificacion) {
                    $(inputField).val(DatosCertificacion[0].tarifa ? DatosCertificacion[0].tarifa.tarifa : 0);
                    $('#tarifario').val(DatosCertificacion[0].tarifa ? DatosCertificacion[0].tarifa.id : 0);
                    if (!$.isEmptyObject(DatosCertificacion[0].tarifa)) {
                        if (!DatosCertificacion[0].tarifa.editable) {
                            $(inputField).attr("disabled", true);
                        }
                    }
                } else {
                    $(inputField).val(0);
                    $('#tarifario').val(0);
                }
            }            
        });
    }; 
    
    /**
     * Tiro los lotes previos a su re procesamiento
     */
    const RefrescarLotes = () => {
        console.log('Refresco los lotes', lotes);
            /* Recibe un array y carga todos los datos en un list */
//            var list = new Array();
//            lotes.map((lote) => {
//                if (typeof lote !== 'undefined') {
//                    if (typeof lote.disabled !== 'undefined') {
//                        list.push({value: lote.id, display: lote.nombre, disabled: true});
//                    } else {
//                        list.push({value: lote.id, display: lote.nombre});
//                    }
//                }
//            });
//            
//            console.log('List: ', list);
            return lotes;
    };
    
    /* Selecciono el lote y traigo la superficie */    
    const callBackLotes = (value, event ) => {
        if (value == 0) {
            return;
        }
        var superficie = 0;
        $.each(lotes, function (index, lote) {
            if (lote.id == value) {
                superficie = lote.has;
            }
        });
        console.log('Callback Lotes');
        $.each(event.data.domTD, function (index, td) {
            if (index === 5 ) {
                var inputField;
                if ($(td).find('input').length > 0) {
                    inputField = $(td).find('input');
                }
                $(inputField).val(superficie);
            }
            if (index === 6 ) {
                var inputField;
                if ($(td).find('select').length > 0) {
                    inputField = $(td).find('select');
                }
                $(inputField).val(1);
            }
//            if (index === 7 ) {
//                var inputField;
//                if ($(td).find('input').length > 0) {
//                    inputField = $(td).find('input');
//                }
//                $(inputField).val(0);
//            }
        });
        
    };
        
    const marcarBloqueos = ( tabla, disabledFields ) => {
        $.each(tabla, function (index, td) {
            var inputField;
            if ($(td).find('input').length > 0) {
                inputField = $(td).find('input');
            }
            if ($(td).find('select').length > 0) {
                inputField = $(td).find('select');
            }
            var l_value = $(inputField).val();
            try {
                if (typeof disabledFields[index] !== 'undefined') {
                    if ( l_value != 0 ) {
                        $(inputField).prop('disabled', true);
                    } else {
                        $(inputField).prop('disabled', true);
                        $(inputField).val(null);
                    }                                        
                } else {
                    $(inputField).prop('disabled', false);
                }
            } catch (e) {
                $(inputField).prop('disabled', false);
            }
        });
    };
    
    const callbackEditLabores = ( table, aData ) => {
        console.log('callbackEditLabores: ', aData );
    };
    
    const callbackEditInsumos = ( table, aData ) => {
        console.log( aData );
    };
    var callbackEditTableInsumos = function(table, aData, flagDelete, actionOriginal, cellAction, temporalIdField, nRow) {
        if (flagDelete) {
            $("#table-loader").removeClass('hidden');
            $.ajax({
                type: 'POST',
                url: '/orden-trabajos-insumos/eliminarinsumo',
                data: {id: aData.id},
                success: function(response) {
                    
                    $(cellAction).html(actionOriginal);
                    $(cellAction).find('.tooltip').remove();
                    $("#table-loader").addClass('hidden');                    
                }
            });            

        } else {
            RowInsumos = aData;
            var RowInsumosPost = jQuery.extend({}, RowInsumos);
            RowInsumosPost['orden_trabajos_distribucione_id'] = 0;
            RowInsumosPost['orden_trabajo_id'] = $("#id").val();
            
            $("#table-loader").removeClass('hidden');

            /* Ahora guardo el dato generado mediante AJAX */
            const data = new FormData();
            data.append('datos', JSON.stringify(RowInsumosPost));
            fetch(`/orden-trabajos-insumos/add`, {
                method: 'POST',
                body: data
            })
            .then( res => res.json())
            .then( data => {
                console.log('Datos recibidos: ', data );
                if (data.status = 'success') {
                    /* Se guardó correctamente al parecer */
                    reloadDataLabores();
                    
                    $('.create-contractor').attr("disabled", false);
                    $(cellAction).html(actionOriginal);
                    $(cellAction).find('.tooltip').remove();
                    $('#EjecutarOT').attr("disabled", false);
                    return;
                }
                toastr.error(data.message);
            });            
        }
    };
    
    return {
        init: function() {
            initDataContractors(); /* Cargo los datos */
            initTable();           /* Inicio la tabla */
           
            $("#table-loader").addClass('hidden');
            $("#data-contractors").removeClass('hidden');
            $("#page-loader").addClass('hidden');
        }
    };
}();