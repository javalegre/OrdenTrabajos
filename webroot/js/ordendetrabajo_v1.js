/**
 * Ordenes de Trabajo
 *
 * Maneja la edición de edición de las OT.
 *
 * Desarrollado para Adecoagro SA.
 * 
  * @author Javier Alegre <jalegre@adecoagro.com>
 * @copyright Copyright 2021, Adecoagro
 * @version 1.0.0 creado el 21/12/2021
 *          1.0.1 14-02-2022 Se corrige el tema de la edición 
 */

var OrdenTrabajo = [];

var dataTableContractors = new Array();
var dataTableInsumos = new Array();
var DataContractors = new Array();
var labores = new Array();
var lotes = new Array();
var unidades = new Array();
var cc = new Array();
var productos_lotes = new Array();    
var productos = new Array();
var almacenes = new Array();
var monedas = new Array();
var tecnicas =  new Array();
    
$(document).ready(function() {
    $('#cm_fecha').mask('00/00/0000 00:00:00');
    $('#cm_temperatura').numeric();
    $('#cm_humedad').numeric();
    $('#cm_viento').numeric();

    const Iniciar = () => {
        /* Recupero los datos de la OT actual */
        let id = $('#id').val();
        fetch(`/orden-trabajos/edit/${ id }.json`)
            .then( res => res.json() )
            .then( data => {
                /* Data recibida de la OT */
                CargarTabla( data );
            });        
    };    
    Iniciar();
});

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
    
    /* 
     * Ahora paso todos los registros a los lotes
     * Ya vienen procesados desde el controlador
     * 15/02/2022 - Javier Alegre
     */
    lotes = data.lotes;
    
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
                id: row.proyectos_labore.id,
                nombre: row.proyectos_labore.nombre,
                insumos: row.proyectos_labore.insumos
            }
        );
    });
  
    OrdenTrabajo['distribucion'] = data.ordenTrabajo.orden_trabajos_distribuciones;
    OrdenTrabajo['insumos'] = data.ordenTrabajo.orden_trabajos_insumos;
            
    ContractorListEdit.init();
    
};

jQuery.validator.addMethod("validDate", function(value, element) {
        return this.optional(element) || moment(value,"DD/MM/YYYY HH:mm").isValid();
    }, "Formato fecha y hora erróneo (dd/mm/yyyy hh:mm)");
    
$("#ordenTrabajo").validate({
    ignore: null,
    rules: {
        'cm_fecha' : {validDate: true}
    },
    highlight: function ( element, errorClass, validClass ) {
            var elem = $(element);
            $( element ).addClass( "has-error" ).removeClass( "has-success" );
    },
    unhighlight: function (element, errorClass, validClass) {
            var elem = $(element);
            $( element ).addClass( "has-success" ).removeClass( "has-error" );
    }                
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
                return;
            } 
            toastr.info(data['message']); /* Se anuló correctamente */
            $(this).closest('tr').remove();
        })
        .catch( function(err) {
            console.log( err );
        });
});

$(".EjecutarOT").click(function(){
    /* Averiguo si habilito o no el boton de Ejecutar OT */
    let orden_trabajo_id = $('#id').val();
    /* ------------------------------------- */
    /* Funciona para enviar un formulario    */
    /* ------------------------------------- */
    const data = new FormData(document.getElementById('ordenTrabajo'));
    fetch(`/orden-trabajos/edit/${ orden_trabajo_id}.json`, {
        method: 'POST',
        body: data
    })
    .then( res => res.json())
    .then( data => {
        if (data.ordenTrabajo.orden_trabajos_estado_id > 1) {
            toastr.options.onHidden = function(){
                var ruta = "http://"+ document.domain +"/OrdenTrabajos/view/" + orden_trabajo_id;
                window.location.href = ruta;          
            };
            toastr.info('Se guardó el registro el registro correctamente.');
            return;
        }
        window.location.reload();
    });
});

$(".select2").select2({
     theme: "bootstrap",
     width: '100%'
});

/* Configuro el Select de Proveedores */
$("#proveedore-id").select2({
    theme: "bootstrap",
    width: '100%',
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

            });

            //Cancel Editing or Adding a Row
            this.data.table.on("click", 'a.cancel', function (e) {
                e.preventDefault();
                that._restoreRow(that.isEditing);
                that.isEditing = null;
                $('.' + that.settings.createCssEvent).attr("disabled", false);
                $('.alert').removeClass('hidden').addClass('hidden');
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
            $('.EjecutarOT').attr("disabled", false);
        },
        // Edit Row
        _editRow: function (nRow) {
            var that = this;
            var jqTds = $('>td', nRow);
            var columnsDatatables = this.data.table.settings().init().columns;
            var columnIndex = 0;
            var first = true;
            
            /* Al editar una linea, deshabilito el Generar OT */
            $('.EjecutarOT').attr("disabled", true);
            $.each(columnsDatatables, function (index, column) {
                if ((typeof column.visible === 'undefined') || !column.visible === false) {
                    if ((typeof column.className === 'undefined') || column.className.indexOf('no-edit') === -1) {
                        var cell = that.data.table.row(nRow).cell(jqTds[columnIndex]);
                        // Show input
                        var input = that._getInputHtml(columnIndex, that.settings, cell.data(), column, jqTds, that.data.instance);
                        $(jqTds[columnIndex]).html(input.html);
                        
                            try {
                                $("#select2-" + column.data + that.data.instance).select2({
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
                            } catch(e) {
                                console.log('error', e);
                            }

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
            
            /* Ejecuto el evento */
            if (typeof this.settings.onEdit !== 'undefined') {
                this.settings.onEdit(jqTds, this.data.table.row(nRow).data());
            }
            
            /* Estos son los datos que están en la línea */
            var rowData = this.data.table.row(nRow).data();
            
            that.actionOriginal = $(jqTds[that.settings.columnAction]).html();
            $(jqTds[that.settings.columnAction]).html('<div class="btn-group"><a href="#" class="btn btn-success btn-xs save"><i class="fa fa-save"></i></a> <a href="#" class="btn btn-warning btn-xs cancel"><i class="fa fa-times"></i></a></div>');
            this.data.table.draw();
            try {
                $('.datepicker').datepicker({
                    language: 'es',
                    format: 'dd/mm/yyyy',
                    autoclose: true
                });

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
////                            console.log('processResults: ', data);
////                            return { results: data.productos };
////                        }                      
//                    },
//                    templateResult: function ( data ) {
//                        
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
//                         var $container = $(`<div><small class='pull-right'>${lote}</small>
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
               
                $(".select-inline").select2({
                    theme: "inline",
                    width: '100%'
                });

                /* verifico si el lote está en la lista */
                var LoteSeleccionado = rowData.lote ? rowData.lote.id : '';
                $(".select-inline-lotes").select2({
                    theme: "inline",
                    data: lotes,
                    width: '100%',
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
                }).val(LoteSeleccionado).trigger('change');
                
                $(".select-inline-vacio").select2({
                    theme: "inline",
                    width: '100%',
                    allowClear: true,
                    placeholder: {
                        value: "0",
                        text:"Sin Lote",
                        selected:'selected'
                    },
                    templateResult: function ( data ) {
                        return data.text;
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
                this.settings.onUpdate(this.data.table, aData, false, this.actionOriginal, $(jqTds[this.settings.columnAction]), this.settings.temporalId);
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
                case "select2":
                    input.html = "<select class=' " + inputCss + " select-" + column.data + instance + "' id='select2-" + column.data + instance + "' value='" + oldValue + "'>";
                    input.html = input.html + "</select>";
                    input.focus = false;
                    /*********************************************************************************
                     *  EVENTOS 
                     * Capturo los eventos de cada objeto, para eso, defino en la columna un callBack,
                     * de esa manera puedo capturar un evento del obtejo.
                     *
                     ********************************************************************************/
                    if (typeof inputSetting.callBack !== 'undefined'){
                        console.log('Eventos select2');
                        $(document).on('change', '#select2-' + this._getSelectorName(column.data) + instance, {
                            domTD: jqTds, settings: inputSetting, columna: currentColumnIndex, tabla: this
                        }, function (events) {
                            console.log('data: ', $(this).select2('data'));
                            events.data.settings.callBack(this.value, events, $(this).select2('data'));
                        }); 
                    }
                    break;
                case "list":
                    input.html = "<select class=' " + inputCss + " select-" + column.data + instance + "' id='select-" + column.data + instance + "' value='" + oldValue + "'>";
                    if (oldValue == 'null') {
                        oldValue = null;
                    }
                    $.each(inputSetting.options, function (index, option) {
                        /* No hay ningun filtro */
                        input.html = input.html + "<option value='" + option.value + "'";
                        if (option.value == 'null') {
                            option.value = null;
                        }
                        /* Si encuentro el lote, lo filtro */
                        if(inputSetting.lote !== 'undefined'){
                            if(inputSetting.lote == option.value){
                                input.html = input.html + " selected ";
                            }
                        }
                        if (oldValue == option.display) {
                            input.html = input.html + " selected ";
                        }
                        input.html = input.html + ">" + option.display + "</option>";
                    });
                    input.html = input.html + "</select>";
                    input.focus = false;
                    /*********************************************************************************
                     *  EVENTOS 
                     * Capturo los eventos de cada objeto, para eso, defino en la columna un callBack,
                     * de esa manera puedo capturar un evento del obtejo.
                     *
                     ********************************************************************************/
                    if (typeof inputSetting.callBack !== 'undefined'){
                        $(document).on('change', '#select-' + this._getSelectorName(column.data) + instance, {
                            domTD: jqTds, settings: inputSetting, columna: currentColumnIndex, tabla: this
                        }, function (events) {
                            events.data.settings.callBack(this.value, events, $(this).select2('data'));
                        });                        
                    }
                    break;
                case "number": // text input w/ confirm
                    input.html = "<input autofocus type='text' class='" + inputCss + " text-right " + column.data + instance + "' id='input-" + column.data + instance + "' value='" + oldValue + "'></input>";
                    /* Capturo los eventos de los inputs */
                    if (typeof inputSetting.callBack !== 'undefined'){
                        $(document).on('keyup', '#input-' + this._getSelectorName(column.data) + instance, {
                            domTD: jqTds, settings: inputSetting, columna: currentColumnIndex, tabla: this
                        }, function (events) {
                            events.data.settings.callBack(this.value, events );
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
                        $(inputField).val(null);
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

/* Lista y edicion de Contratistas.
 * @type {{init}}
 */
var ContractorListEdit = function() {

    var $table;
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
                    lote: insumos[i].productos_lote ? insumos[i].productos_lote : '',
                    almacen: insumos[i]['almacene'].nombre,
                    id: insumos[i].id,
                    id_distribuciones: insumos[i].orden_trabajos_distribucione_id,
                    entregas: insumos[i]['orden_trabajos_insumos_entregas'].length
                }
            );
        }            

        distribucion = new Array;
        distribucion = OrdenTrabajo['distribucion'];
        
        var tecnica;
        for (var i = 0; i< distribucion.length; i++){
            tecnica = '-';
            if (distribucion[i]['tecnicas_aplicacione'] !== null) {
                tecnica = distribucion[i]['tecnicas_aplicacione'].nombre;
            }
            
            dataTableContractors.push(
                {
                    labor: distribucion[i]['proyectos_labore'].nombre,
                    unmedida: distribucion[i]['unidade'].nombre,
                    cc: distribucion[i]['proyecto'].nombre,
                    lote: distribucion[i]['lote'],
                    tecnica: tecnica,
                    has: distribucion[i]['superficie'],
                    dosis: '1',
                    total: distribucion[i]['superficie'],
                    moneda: distribucion[i]['moneda'] ? distribucion[i]['moneda'].simbolo : '',
                    importe: distribucion[i].importe,
                    insumos: distribucion[i]['proyectos_labore'].insumos,
                    labor_id: distribucion[i]['proyectos_labore'].id,
                    id: distribucion[i]['id'],
                    tarifario: distribucion[i]['orden_trabajos_distribuciones_tarifario']
                }
            );
        }
    };
    
    var mapOptionsList = function(values, filtro) {
        /* Recibe un array y carga todos los datos en un list */
        var list = new Array();
        $.each(values, function(index, value) {
            if (typeof value !== 'undefined') {
                list.push({value: value.id, display: value.nombre});
            }
        });
        return list;
    };
    
    var reloadDataLabores = function() {
        /* Recarga los datos */

        $("#table-loader").addClass('hidden');
        
        /* Recupero los datos de la OT actual */
        let id = $('#id').val();
        fetch(`/orden-trabajos/edit/${ id }.json`)
            .then( res => res.json() )
            .then( data => {
                
                OrdenTrabajo['distribucion'] = data.ordenTrabajo.orden_trabajos_distribuciones;
                OrdenTrabajo['insumos'] = data.ordenTrabajo.orden_trabajos_insumos;                
                initDataContractors();
                
                $table.clear();
                $table.rows.add(dataTableContractors);
                $table.draw();
                
                $table.rows(':not(.parent)').nodes().to$().find('td:first-child').trigger('click');
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
            buttons: [{/* Agregar Item a la Orden de Trabajo */
                            "text": "<i class='fa fa-plus'></i>",
                            "titleAttr": "Agregar Labor",
                            "className": "btn create-contractor"
                        }],
            autoWidth: false,
            columns: [{
                    className: 'details-control-contractors',
                    data: 'cc',
                    sortable: false,
                    responsivePriority: 1,
                    defaultContent: '',
                    width: '20%'
                },{ data: 'labor',
                    responsivePriority: 2,
                    defaultContent: '0',
                    width: '20%',
                    render: function(data, type, row) {
                        if (type === 'display' || type === 'type') {
                            if (row.tarifario !== null &&  typeof(row.tarifario) === 'object') {
                                return `<span title="Tarifario id: ${row.tarifario.proyectos_labores_tarifario_id}">${data}</span>`;
                                //return `<span class="badge badge-success pull-right" title="Tarifario ${row.tarifario.proyectos_labores_tarifario_id}"><i class='fa fa-info'></i></span>${data}`;
                            }
                            return data;
                        }
                        return data;
                    }                    
                },{
                    data: 'unmedida',
                    responsivePriority: 2,
                    defaultContent: '',
                    width: '10%'
                },{
                    data: 'tecnica',
                    responsivePriority: 1,
                    defaultContent: '',
                    render: function(data, type, row) {
                        if (type == 'display' || type == 'type') {
                            $.each(tecnicas, function(index, value) {
                                if (data === value.id) {
                                    return value.nombre;
                                }
                            });                            
                        }
                        return data;
                    }
                },{
                    data: 'lote',
                    responsivePriority: 1,
                    defaultContent: '',
                    width: '10%',
                    render: function(data, type, row) {
                        if (type == 'display' || type == 'type') {
                            return data ? data.nombre : '';
                        }
                        return data ? data.id : '';
                    }
                },{
                    data: 'has',
                    class:'text-right',
                    responsivePriority: 1,
                    defaultContent: '',
                    width: '5%'
                },{
                    data: 'moneda',
                    responsivePriority: 1,
                    render: function(data, type, row) {
                        if (type == 'display' || type == 'type') {
                            $.each(monedas, function(index, value) {
                                if (data === value.id) {
                                    return value.simbolo;
                                }
                            });                            
                        }
                        return data;
                    },
                    defaultContent: ''
                },{
                    data: 'importe',
                    class:'text-right',                    
                    responsivePriority: 1,
                    defaultContent: '0',
                    width: '10%'
                },{
                    data: 'tarifario',
                    className: 'save',
                    defaultContent: '0',
                    visible: false
                },{
                    width: '28px',
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
                    data: 'insumos',
                    defaultContent: '0',
                    visible: false
                },{
                    data: 'labor_id',
                    defaultContent: '0',
                    visible: false
                }],
                ordering: false
        });
        
        // Add event listener for opening and closing details
        $table.on('click', 'td.details-control-contractors, .expand_machine', function() {
            var tr = $(this).closest('tr');
            var row = $table.row(tr);
            var dataInsumos = new Array();
            var data = row.data();
            var actionsTemplateMachine = _.template($("#row-actions-template-machine").text());
            
            if (data.insumos) {
                    // Open this row
                    row.child(formatDetails(data), 'background-white background-child');
                    row.child.show();
                    
                    /* Todos los insumos relacionados con esta OT están en dataTableInsumos
                     * pero solo debo recuperar los que están relacionados a esta labor en 
                     * particular, a traves del campo orden_trabajos_distribucione_id */
                    $.each(dataTableInsumos, function(index, row) {
                        if (row.id_distribuciones === data.id) {
                                dataInsumos.push(row);
                            return true;
                        }
                    });
                    
                    $subTable = $(".contractors-table-details" + row.data().id).DataTable({
                        pageLength: 25,
                        data: dataInsumos,
                        autoWidth: false,
                        deferRender: false,
                        dom: 'rt',
                        columns: [
                            {
                                data: 'producto',
                                defaultContent: '',
                                width: '25%'
                            },
                            {
                                data: 'lote',
                                defaultContent: '',
                                width: '15%',
                                render: function(data, type, row) {
                                    if (type == 'display' || type == 'type') {
                                        return data ? data.nombre : '';
                                    }
                                    return data ? data.id : '';
                                }
                            },
                            {
                                data: 'unidad',
                                defaultContent: '',
                                width: '15%'
                            },
                            {
                                data: 'dosis',
                                class: 'text-right',
                                defaultContent: '',
                                extraData: row.data().id
                            },
                            {
                                data: 'cantidad',
                                class: 'text-right',
                                defaultContent: ''
                            },
                            {
                                data: 'almacen',
                                defaultContent: '',
                                width: '20%'
                            },
                            {
                                data: 'id',
                                className: 'text-center cell-double-action no-custo no-edit',
                                render: function(data, type, row) {
                                    if (type === 'display' || type === 'filter'){
                                        /* Si tiene entregas (=1) no muestro las opciones de editar/eliminar linea */
                                        return actionsTemplateMachine({});
                                    }
                                },
                                responsivePriority: 1,
                                defaultContent: ''
                            },{
                                data: 'distribucionId',
                                visible: false,
                                defaultContent: row.data().id
                            },{
                                data: 'temporalId',
                                defaultContent: 0,
                                visible: false
                            }
                        ],
                        ordering: false
                    });
                    
                    /* Si se permite la edicion, habilito las tablas editables */
                    var createEvent = 'insumos' + row.data().id;
                    var lt_disabled = {1: 1, 2: 2, 3: 3, 4: 4, 5: 5, 6:6};
                    var subTableEdit = new $.fn.dataTable.altEditorTable($subTable, {
                        columnAction: 6,
                        temporalId: 'temporalId',
                        onUpdate: callbackEditTableInsumos,
                        inputCss: 'edit-input-inline',
                        createCssEvent: createEvent,
                        errorClass: 'edit-input-error',
                        disabledFields: lt_disabled,
                        columns: [0, 1, 2, 3, 4, 5],
                        inputTypes: [
                            {
                                column: 0,
                                // class: "select-inline-productos",
                                 type: "select2",
                                 callBack: callBackProductos,
                                 //options: mapOptionsList(productos),
                                 width: '35%'
                            },
                            {
                                column: 1,
                                class: "select-inline",
                                type: "list",
                                options: mapOptionsList(productos_lotes),
                                width: '25%'
                            },                             
                            {
                                column: 2,
                                type: "list",
                                class: "select-inline",
                                options: mapOptionsList(unidades)
                            },
                            { /* Dosis */
                                column: 3,
                                type: "number",
                                callBack: callBackDosisInsumos,
                                extraData: row.data().id
                            },
                            { /* Cantidad - Sale de la dosis x has */
                                column: 4,
                                type: "number"
                            },
                            {
                                column: 5,
                                type: "list",
                                class: "select-inline",
                                options: mapOptionsList(almacenes)
                            }
                        ]
                    });
                }
        });
        
        var lt_disabled = {1: 1, 2: 2, 3: 3, 4: 4, 5: 5, 6: 6, 7: 7};
        /* Si se permite la edicion, habilito las tablas editables */
        var editTable = new $.fn.dataTable.altEditorTable($table, {
            columnAction: 8,
            onUpdate: callbackEditTableContractors,
            inputCss: 'edit-input-inline',
            onEdit: callbackEditLabores,
            createCssEvent: 'create-contractor',
            errorClass: 'edit-input-error',
            disabledFields: lt_disabled,
            columns: [1, 2, 3, 4, 5, 6, 7, 8],
            validations: [{
                    column: 1,
                    allowNull: false,
                    message: 'Unidad de Medida'
                },
                {   column: 2,
                    allowNull: false,
                    message: 'Centro de Costo'
                },{
                    column: 4,
                    allowNull: true,
                    message: 'Lote'
                },{
                    column: 5,
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
                },{ /* Labores */
                    column: 1,
                    type: "list",
                    class: "select-inline disabled",
                    options: mapOptionsList(labores),
                    callBack: callBackProyectosLabores
                },{ /* Unidades de Medida */
                    column: 2,
                    type: "list",
                    class: "select-inline disabled",
                    //callBack: 'unidades',
                   options: mapOptionsList(unidades)
                },{ /* Tecnica */
                    column: 3,
                    type: "list",
                    class: "select-inline disabled",
                   options: mapOptionsList(tecnicas)
                },{ /* Lotes */
                    column: 4,
                    class: "select-inline-lotes",
                    type: "list",
                    options: mapOptionsList(lotes),
                    callBack: callBackLotes
                },{ /* Cantidad de Hectareas */
                    column: 5,
                    type: "number",
                    callBack: callBackSuperficie
                },{ /* Tipo de Moneda */
                    column: 6,
                    type: "list",
                    class: "select-inline disabled",
                    options: mapOptionsList(monedas)
                },{ /* Importe de Moneda */
                    column: 7,
                    type: "number"
                },{
                    column: 8,
                    type: "number"
                }]
        });
        
        /* Expando todas las lineas de Insumos */
        $table.rows(':not(.parent)').nodes().to$().find('td:first-child').trigger('click');
    };

    /* Listado de Proyectos: busco todas las tareas relacionadas a este proyecto */
    const callBackProyectos = (value, event) => {
        if (value == 0) {
            return;
        }
        $.each(event.data.domTD, function (index, td) {
            if (index === 1 ) { /* Ahora listo los proyectos por labores */
                var inputField;
                if ($(td).find('select').length > 0) {
                     inputField = $(td).find('select');
                }
                
                let dataForm = new FormData();
                dataForm.append('establecimiento', $('#establecimiento-id').val() );
                dataForm.append('proveedor', $('#proveedore-id').val() );
                
                /* Marco deshabilitado el select */
                $(inputField).prop('disabled', true);
                fetch(`/proyectos/listarlabores/${value}.json`, {
                    method: 'POST',
                    body: dataForm
                  }).then( res => res.json() )
                    .then( data => {
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

    /**
     * Marco los campos como deshabilitados, en caso de que no se pueda modificar
     * la tarifa.
     * 
     * @param tabla Tabla que se edita
     * @param disabledFields Campos a marcar como deshabilitados
     */
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
    
    const callBackDosisInsumos = ( value, event ) => {
        if (value == 0) {
            return;
        }
        
        var IdDistribucion = event.data.settings.extraData;
        $.each(event.data.domTD, function (index, td) {
            if (index === 4 ) { /* Ahora listo los proyectos por labores */
                var inputField;
                if ($(td).find('input').length > 0) {
                     inputField = $(td).find('input');
                }
                let TotalHas = 0;
                $.each(dataTableContractors, function(index, row) {
                    if (IdDistribucion == row.id) {
                        TotalHas += dataTableContractors[index].has;
                    }
                });
                let total = TotalHas * value;
                $(inputField).val(total.toFixed(2));
            }
        });        
    };
    
    var formatDetails = function(callback) {
        var templateDetails = _.template($("#details-rows-template").text());
        var lt_dataTableMachines = new Array();

        return templateDetails({
            id: callback.id,
            machines: lt_dataTableMachines
        });
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
        
        var Proyecto = '';
        var Labor = '';
        
        $.each(event.data.domTD, function (index, td) {
            if (index === 0 ) { /* Proyecto */
                var inputField;
                if ($(td).find('select').length > 0) {
                    inputField = $(td).find('select');
                }
                Proyecto = $(inputField).val();
            }
            if (index === 1 ) { /* Labor */
                var inputField;
                if ($(td).find('select').length > 0) {
                    inputField = $(td).find('select');
                }
                Labor = $(inputField).val();
            }
            if (index === 5 ) {
                var inputField;
                if ($(td).find('input').length > 0) {
                    inputField = $(td).find('input');
                }
                $(inputField).val(superficie);
            }
            if (index === 6 ) {
//                var inputField;
//                if ($(td).find('select').length > 0) {
//                    inputField = $(td).find('select');
//                }
//                $(inputField).val(1);
            }
            if (index === 7 ) {
                var inputField;
                if ($(td).find('input').length > 0) {
                    inputField = $(td).find('input');
                }
                if (isNaN($(inputField).val())) {
                    $(inputField).val(0);
                }
            }
        });
        
        RevisarLaboreos (Proyecto, Labor, value);
        
        var tr = $(event.data.domTD).closest('tr');
        var row = $table.row(tr).data();
        
        if ($table.row(tr).child) {
            var SubTabla = $(`.contractors-table-details${row.id}`).DataTable();
            SubTabla.rows().every(function (index, element) {
                var data = this.data(); /* this shows correct row data, but not whether first column checkbox is checked by user */
                var cantidad = data.dosis * superficie;
                var local = 'es-ES';
                var options = { maximumFractionDigits: 10 };
                var numberFormat = new Intl.NumberFormat(local, options);

                data.cantidad = numberFormat.format(cantidad);
                this.data( data );
            });            
            SubTabla.draw();
        }
    };
    
    /**
     * Reviso si no se realizó una labor para el proyecto y lote especificado.
     */
    const RevisarLaboreos = (Proyecto, Labor, Lote) => {
        let url = new URL(`${window.location.protocol}//${window.location.host}/proyectos-labores/chequear-labores-realizadas`);
        urlParams = new URLSearchParams();
        urlParams.append('proyecto', Proyecto );
        urlParams.append('labor', Labor );
        urlParams.append('lote', Lote );
        url.search = urlParams;
        fetch( url )           
            .then( res => res.json() )
            .then( data => {
                if (data.status == 'success') {
                    if (data.data) {
                        $('#chequeos-labores').empty();
                        $.each(data.data, function(index, value) {
                            $('#chequeos-labores').append(
                                `<div class="alert alert-danger alert-dismissable hidden m-t-xs">
                                    <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                                    <span>${value.orden_trabajo.user.nombre} orden&oacute ${value.superficie} has el ${moment(value.orden_trabajo.fecha).format("DD/MM/YYYY")} en la OT ${value.orden_trabajo.id}</span>
                                </div>`);
                        });
                        $('.alert').removeClass('hidden');
                    }
                }
            });
        
    };
    
    const callBackSuperficie = (value, event) => {
        if (value == 0) {
            return;
        }
        var superficie = value;
      
        var tr = $(event.data.domTD).closest('tr');
        var row = $table.row(tr).data();
        
        if ($table.row(tr).child) {
            var SubTabla = $(`.contractors-table-details${row.id}`).DataTable();
            SubTabla.rows().every(function (index, element) {
                var data = this.data(); /* this shows correct row data, but not whether first column checkbox is checked by user */
                var cantidad = data.dosis * superficie;
                var local = 'es-ES';
                var options = { maximumFractionDigits: 10 };
                var numberFormat = new Intl.NumberFormat(local, options);

                data.cantidad = numberFormat.format(cantidad);
                this.data( data );
            });            
            SubTabla.draw();
        }        
    };
    
    var formatButtonDetails = function(callback) {
        var templateDetails = _.template($("#row-actions-insumos").text());
        
        return templateDetails({
            id: callback.id
        });
    }; 

    /* Lista Labores: busco las unidades de medida y las tecnicas asociadas */    
    const callBackProyectosLabores = (value, event ) => {
        if (value == 0) {
            return;
        }
        var lt_disabled = {
            8: 8
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
                datos['labor'] = $(inputField).val();
            };
            
            if (index === 2 ) { /* Unidades de Medida */
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
                            theme: 'inline',
                            width: '100%'
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
            /* Tipo de Moneda */
            if (index === 6 ) { 
                var inputField;
                if ($(td).find('select').length > 0) {
                    inputField = $(td).find('select');
                }
                if (DatosCertificacion) {
                    var Moneda = 1;
                    if (typeof DatosCertificacion[0].tarifa.moneda_id !== 'undefined' && DatosCertificacion[0].tarifa.moneda_id !== null) {
                        Moneda = DatosCertificacion[0].tarifa.moneda_id;
                    }
                    $(inputField).val(Moneda).trigger('change');
                    /* Reviso si el tarifario está como editable la tarifa o no */
                    if (!$.isEmptyObject(DatosCertificacion[0].tarifa)) {
                        if (!DatosCertificacion[0].tarifa.editable) {
                            $(inputField).prop('disabled', true);
                        }
                    }
                } else {
                    $(inputField).val(1).trigger('change');
                }
            }
            
            /* Precio de la labor, si tiene definida, lo pongo */
            if (index === 7 ) {
                var inputField;
                if ($(td).find('input').length > 0) {
                    inputField = $(td).find('input');
                }
                if (DatosCertificacion) {
                    var ValorTarifa =  DatosCertificacion[0].tarifa ? DatosCertificacion[0].tarifa.tarifa : 0;
                    var ValorUta = DatosCertificacion[0].tarifa ? DatosCertificacion[0].tarifa.uta : 0;
                    
                    /* Si la tarifa es = 0, miro los rangos de HP y UTA, para calcular una tarifa */
                    if (ValorTarifa == 0 && ValorUta == 0) {
                        if (DatosCertificacion[0].tarifa.importe_rango_hp && DatosCertificacion[0].tarifa.valor_uta) {
                            ValorTarifa = DatosCertificacion[0].tarifa.importe_rango_hp * DatosCertificacion[0].tarifa.valor_uta.valor_uta;
                        }
                    }
                        
                    $(inputField).val(ValorTarifa.toFixed(2));
                    /*$(inputField).val(total.toFixed(2));*/
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
     * Esto se ejecuta ANTES de editar una linea, por lo que es genial para
     * especificar los bloqueos.
     */
    const callbackEditLabores = ( table, aData ) => {
        try {
            /* Muestro el tarifario */
            var Tarifario = JSON.parse($('#tarifario-data').val());
            /* Reviso si encuentro el tarifario */
            Tarifario.map((tarifario) => {
                if (tarifario.orden_trabajos_distribucione_id == aData.id) {
                    var lt_disabled = {6: 6, 7: 7, 8: 8};
                    marcarBloqueos(table, lt_disabled);
                }
            });
        } catch(e) {
            console.error('callbackEditLabores: ', e);
        }
    };
    
    const callbackEditInsumos = ( table, aData ) => {
        console.log('callbackEditInsumos: ', aData );
    };
    
    var callbackEditTableContractors = function(table, aData, flagDelete, actionOriginal, cellAction, temporalIdField) {
        var RowContractor = new Object;
        
        $('.alert').removeClass('hidden').addClass('hidden');
        
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
            RowContractor = updateDataContractors(RowContractor);
            
            var RowContractorPost = jQuery.extend({}, RowContractor);
            RowContractorPost['orden_trabajo_id'] = $("#id").val();
            
            /* TODO Tengo que pasar el listado con el tarifario */
            RowContractorPost['tarifario'] = $('#tarifario').val();
            
            $("#table-loader").removeClass('hidden');
            
            const data = new FormData();
            data.append('datos', JSON.stringify(RowContractorPost));
            fetch(`/orden-trabajos-distribuciones/add`, {
                method: 'POST',
                body: data
            })
            .then( res => res.json())
            .then( data => {
                if (data.status = 'success') {
                    /* Se guardó correctamente al parecer */
                    reloadDataLabores();
                    $('.create-contractor').attr("disabled", false);
                    $(cellAction).html(actionOriginal);
                    $(cellAction).find('.tooltip').remove();

                    $('#EjecutarOT').attr("disabled", false);                        
                    
                    if (data.tarifario && data.tarifario.length > 0) {
                        $('#tarifario-data').val(JSON.stringify(data.tarifario));
                    }
                    
                    return;
                }
                toastr.error(data.message);
            });
        }
    };
    
    var callbackEditTableInsumos = function(table, aData, flagDelete, actionOriginal, cellAction, temporalIdField) {
        var distribucionId;
        /* Obtengo el Id de la linea de Distribuciones */
        var columns = table.settings().init().columns;
        $.each(columns, function(index, column) {
            if (column.data === 'distribucionId') {
                distribucionId = column.defaultContent;
                return true;
            }
        });
        if (flagDelete) {
            $("#table-loader").removeClass('hidden');
            fetch(`/orden-trabajos-insumos/delete/${ aData.id }`, {
                method: 'DELETE'
                }).then( res => res.json() )
                  .then(data => {
                    if (data.status == 'error') {
                        toastr.error(data.message);
                        reloadDataLabores();
                    }
                    $(cellAction).html(actionOriginal);
                    $(cellAction).find('.tooltip').remove();
                    $("#table-loader").addClass('hidden'); 
            
                }).catch(err => {
                    console.error(err);
                });
        } else {
            RowInsumos = aData;
            
            var RowInsumosPost = jQuery.extend({}, RowInsumos);
            RowInsumosPost['orden_trabajos_distribucione_id'] = distribucionId;
            RowInsumosPost['orden_trabajo_id'] = $("#id").val();

            $("#table-loader").removeClass('hidden');
            /* Ahora guardo el dato generado mediante AJAX */
            $.ajax({
                type: 'POST',
                url: '/orden-trabajos/guardarinsumos',
                data: RowInsumosPost,
                success: function(response) {
                    /* Limpio la SubTabla */
                    reloadDataLabores();
                    $('.create-contractor').attr("disabled", false);
                    $(cellAction).html(actionOriginal);
                    $(cellAction).find('.tooltip').remove();
                    $('#EjecutarOT').attr("disabled", false);
                }
            });
        }
    };
    
 /* Listado de Productos: busco la unidad de medida */
    const callBackProductos = (value, event, extraData) => {
        if (value == 0) {
            return;
        }
        
        var lt_disabled = {4: 4};
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
                            width: '100%' 
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
                            width: '100%' 
                        }).trigger('change');
                        $(inputField).prop('disabled', false);
                    })
                    .catch( function(err) {
                        console.log( err );
                    });                    
            }            
            if ( index === 5 ) { /* Pongo el almacen como predeterminado */
                var inputField;
                if ($(td).find('select').length > 0) {
                     inputField = $(td).find('select');
                }
                $(inputField).val( almacenes[0].id ).trigger('change');
            }
        });
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