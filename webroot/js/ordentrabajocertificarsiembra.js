/*
 *
 *   Ordenes de Trabajo
 *   version 1.0
 *
 */
    var dataTableContractors = new Array();
    var dataTableInsumos = new Array();
    var DataContractors = new Array();
    var InsumosParaCertificar = new Array();
    var datos = new Array();
    var defaultValues = new Array();
    var monedas = new Array();

    var moneda = JSON.parse($('#monedas').val());
    $.each(moneda, function(index, row) {
        monedas.push(
            {   id: row.id,
                nombre: row.simbolo_oracle
            }
        );
    });
    
$(document).ready(function() {
    /* Inicio el show */
    $(".select2").select2({
            theme: "bootstrap"
    });

    /* Barra lateral derecha para los historicos */
    $('.full-height-scroll-certificacion').slimscroll({
        height: '400px',
        distance: 0
    });        

    $('#dt_ordentrabajo').on('click', 'a.CertificarLabores', function (e) {
        e.preventDefault();

        var tr = this.closest('tr');
        var aData =  $(".contractors-table").DataTable().row( tr ).data();

        const resultado = ConsultarInfoCertificacion( aData )
        .then(res => {
            
                let Distribucion = res.distribucion.ordenTrabajosDistribucione;

                var Titulo = 'OT ' + $('#id').val();
                $('#titulo-modal').html(Titulo);
                var SubTitulo = `${ $('#fecha').val() } - ${ $('#establecimiento-id').val() } - ${ $('#proveedore-id').val() } <br><br><strong>${Distribucion.proyectos_labore.nombre}</strong>`;
                $('#sub-titulo-modal').html(SubTitulo);
                
                var Labor = 'Labor: ' + aData.labor;

                $('#ordenado').val(aData.has);

                var PrecioFinal = aData.importe * $('#cotizacion').val();
                $('#precio-final').val(parseFloat(PrecioFinal).toFixed(2));
                $('#distribucion-id').val(aData.id);

                let mensaje = `Cantidad ordenada para <strong> ${ Distribucion.proyectos_labore.nombre }</strong> en el lote <strong>${ Distribucion.lote.nombre }</strong>`;
                $('#mensaje-labor').html(mensaje);

                /* Inicializo la tabla */
                ListaCertificaciones( Distribucion ).initData();

                /* Armo la barra lateral de historicos */
//                ArmarHistoricos( Historicos );

                $('#CertificarLabor').modal('show');

        });            
    });

    /* Traigo todos los datos necesarios */
    const IniciarCertificacion = () => {
        let id = $('#id').val();
        fetch(`/orden-trabajos/certificar-siembra/${ id }.json`)
            .then( res => res.json() )
            .then( data => {
                
                /* Data recibida de la OT */
                datos = data;
                
                /* Marco los botones de Finalizar OT */
                if (data.permiteFinalizar) {
                    $('.certificar-ot'). attr("disabled", false);
                } else {
                    $('.certificar-ot'). attr("disabled", true);
                }
                
                ContractorListEdit.init();
            });            
    };
    IniciarCertificacion();
    ListaCertificaciones().init();
});

/* ---------------------------------------------------------------------- */
/**
 * Dibujo el listado de Certificaciones
 */
const RefreshTotalesCertificados = ( data ) => {
    /* Calculo la cantidad a certificar */
    var certificar = 0;
    $.each(data.orden_trabajos_distribuciones, function(index, value) {
        certificar += value.superficie;
    });
    $.each(data.orden_trabajos_certificaciones, function(index, value) {
        certificar -= value.has;
    });
    certificar = Intl.NumberFormat().format(certificar);
    if ( certificar !== 0 ) {
        var certificaciones = `<div class="ibox-content ibox-heading"><h3>Tiene ${ certificar } hectareas sin certificar!</h3></div>`;
        $('#cantidad-certificaciones').html( certificaciones );
    }
    /* Agrego las certificaciones */
    if (data.orden_trabajos_certificaciones.length > 0 ) {
        var tabla = '<div class="table-responsive"><table class="table-certificaciones small"><tbody>';
        var linea = '';
        $.each(data.orden_trabajos_certificaciones, function(index, value) {
            linea = linea + `<tr>
                                <td class="client-avatar">
                                    <a href="#" class="pull-left">
                                    <img src="/img/${ value.user.ruta_imagen}" alt="user.jpg" title="${value.user.nombre}">                                                    </a>
                                </td>
                                <td>
                                    <strong>${value.user.nombre}</strong> ha certificado ${value.has} has. <br>
                                    ${ value.observaciones ? '<small class=""text-muted">' + value.observaciones + '</small>' : ''}
                                </td>
                                <td>
                                    <small class="pull-right">${moment( value.fecha_final ).format("DD/MM/YYYY")}</small>
                                </td>
                            </tr>`;
        });
        var fin_tabla = '</tbody></table></div>';
        var certificaciones_a_mostrar = tabla + linea + fin_tabla;
        $('#cantidad-certificaciones').append( certificaciones_a_mostrar );
        $('.certificar-ot').attr("disabled", false);
    }
};

/**
 * Se reparten los insumos entre las lineas certificadas.
 */
$('#distribuir-insumos').on('click', function (e) {
    e.preventDefault;

    /* Recupero los datos de la OT actual */
    let id = $('#id').val();
    fetch(`/orden-trabajos/siembra-repartir-insumos/${ id }.json`)
        .then( res => res.json() )
        .then( data => {

            /* Data recibida de la OT */
            
            if(data['status']=='success'){
                location.reload();
            }else{
                /* Hay errores, asi que los muestro */
                for(var i=0;i<data['message'].length;i++){
                    toastr.error(data['message'][i]);
                }
            };
        });

});

const ConsultarInfoCertificacion = async (data) => {
    try {
        const response = await fetch(`/orden-trabajos-distribuciones/edit/${ data.id }.json`);
        const Distribucion = await response.json();
        const Certificacion = await  ObtenerTarifarios( Distribucion );

        let respuesta = {
            certificacion: Certificacion,
            distribucion: Distribucion,
            historicos: Certificacion.historicos
        };
        return respuesta;
    } catch (error ) {
        console.log('Ocurrió un error:', error );
    }
};

const ObtenerTarifarios = async ( distribucion ) => {
    let linea_distribucion = distribucion.ordenTrabajosDistribucione;

    let data = {
        labor: linea_distribucion.proyectos_labore_id,
        unidad: linea_distribucion.unidade_id,
        establecimiento: linea_distribucion.orden_trabajo.establecimiento_id,
        proveedor: linea_distribucion.orden_trabajo.proveedore_id
    };

    // Enviar datos de un objeto JSON /
    let dataForm = new FormData();
    dataForm.append("json", JSON.stringify( data ));
    const respuesta = await fetch(`/proyectos-labores-tarifarios/consultar-tarifario/${ linea_distribucion.proyectos_labore_id }`, {
            method: 'POST',
             body: dataForm
        })
         .then( res => res.json())
         .then( data => {
            return data;
         });
    return respuesta;
};

 /* 
  * Finalizar Certificacion
  * 
  * Efectúo los chequeos antes de terminar la certificacion.
  * Todas las lineas de insumos deben tener el aplicado real.
  * 
  */
 $('.certificar-ot').click(function() {
    
    let orden_trabajo_id = $('#id').val();
    $('#siembra').val(1);
     
    const data = new FormData(document.getElementById('ordenTrabajo'));
    fetch(`/orden-trabajos/finalizarot/${ orden_trabajo_id}.json`, {
        method: 'POST',
        body: data
    })
    .then( res => res.json())
    .then( data => {
        
        console.log('Resultado de finalizar la ot: ', data );
        
        if(data['status']=='success') {
            toastr.options.onHidden = function(){
                var ruta = "http://"+ document.domain +"/OrdenTrabajos/siembra/" + orden_trabajo_id;
                window.location.href = ruta;          
            };    
            toastr.info(data['message']);
        }else{
            /* Hay errores, asi que los muestro */
            for(var i=0;i<data['message'].length;i++){
                toastr.error(data['message'][i]);
            }
        }
    });     
     
 });

$("#cotizacion").keyup(function() {
    var PrecioFinal = $('#PrecioOrdenado').val() * this.value;
    $('#precio-final').val(parseFloat(PrecioFinal).toFixed(2));
});

/* Certifico la labor realizada */
function CertificarInsumos (){
    var resultado;
    $.ajax({
        type:"POST", 
        async:true,
        data: $('#ModalInsumos').serialize(),
        url:"/orden-trabajos/certificacioninsumos",    /* Pagina que procesa la peticion   */
        success:function (data){
            datos = JSON.parse(data);
            if(datos['status']=='success'){
                toastr.info(datos['message']);
            }else{
                /* Hay errores, asi que los muestro */
                for(var i=0;i<datos['message'].length;i++){
                    toastr.error(datos['message'][i]);
                }
            }            
        },
        error: function (data) {
            alert('error' + data.statusText);
            resultado = JSON.parse(data);
        }
    });
    return resultado;
}

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
                
                if (typeof that.settings.defaultValues !== 'undefined') {
                    /* Hardcoded - Fix it*/
                    objectRow['importe'] = defaultValues.importe;
                    objectRow['tarifa'] = defaultValues.tarifa;
                    objectRow['cantidad'] = defaultValues.superficie_ordenada;
                    objectRow['tipo_cambio'] = defaultValues.tipo_cambio !== null ? defaultValues.tipo_cambio : '1';
                    objectRow['fecha'] = moment().format("DD/MM/YYYY");
                    objectRow['orden_trabajos_distribucione_id'] = defaultValues.orden_trabajos_distribucione_id;
                    objectRow['moneda_id'] = defaultValues.moneda_id;
                    objectRow['organizacion_moneda'] = defaultValues.organizacion_moneda;
                    objectRow['moneda'] = defaultValues.organizacion_moneda;
                    objectRow['tarifario'] = defaultValues.tarifario;
                    objectRow['moneda_ordenada'] = defaultValues.moneda_ordenada;
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

            //Certificacion
            this.data.table.on("click", 'a.quitar-certificacion', function (e) {
                e.preventDefault();
                
//                var tr = $(this).closest('tr');
//                var row = that.data.table.row(tr);
//                var aData = that.data.table.row(row).data();
//                
//                var jqTds = $('>td', row);
//                that.actionOriginal = $(jqTds[that.settings.columnAction]).html();
//                
//                that.settings.onUpdate(that.data.table, aData, true, row);
               
            });

            //Cancel Editing or Adding a Row
            this.data.table.on("click", 'a.cancel', function (e) {
                e.preventDefault();
                that._restoreRow(that.isEditing);
                that.isEditing = null;
                $('.' + that.settings.createCssEvent).attr("disabled", false);
                $('.certificacion-notificacion').removeClass('hidden').addClass('hidden');
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

            if (this.settings.nombre === 'TablaLabores') {
                this.data.table.on("click", 'a.CertificarLabores', function (e) {
                    /* Cargo todos los valores de la linea */
                    var tr = $(this).closest('tr');
                    var row = that.data.table.row(tr);
                    var aData = that.data.table.row(row).data();
                    
                    const resultado = ConsultarInfoCertificacion( aData )
                        .then(res => {
                                let Historicos = res.certificacion.historico;
                                let Distribucion = res.distribucion.ordenTrabajosDistribucione;
                                
                                /* tr.css('background-color', '#d9edf7'); */
                                var Titulo = 'OT ' + $('#id').val();
                                $('#titulo-modal').html(Titulo);
                                var SubTitulo = `${ $('#fecha').val() } - ${ $('#establecimiento-id').val() } - ${ $('#proveedore-id').val() } <br><br><strong>${Distribucion.proyectos_labore.nombre}</strong>`;
                                $('#sub-titulo-modal').html(SubTitulo);
//                                if (aData.moneda === Tarifario.alquiler.simbolo){
//                                    $('#verCotizacion').addClass('hidden');
//                                    $('#cotizacion').val(1);
//                                    $('#label-precio-final').text('Importe (En ' + Tarifario.alquiler.moneda + ')');
//                                    $('#moneda').val(Tarifario.alquiler.moneda_id);
//                                }
                                
                                var Labor = 'Labor: ' + aData.labor;
                                
                                $('#ordenado').val(aData.has);
                                
                                var PrecioFinal = aData.importe * $('#cotizacion').val();
                                $('#precio-final').val(parseFloat(PrecioFinal).toFixed(2));
                                $('#distribucion-id').val(aData.id);
                                
                                let mensaje = `Cantidad ordenada para <strong> ${ Distribucion.proyectos_labore.nombre }</strong> en el lote <strong>${ Distribucion.lote.nombre }</strong>`;
                                $('#mensaje-labor').html(mensaje);
                                
                                /* Inicializo la tabla */
                                ListaCertificaciones( Distribucion ).initData();
                                
                                /* Armo la barra lateral de historicos */
//                                ArmarHistoricos( Historicos );
                                
                                $('#CertificarLabor').modal('show');
                        });
                });
            }            
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

        },
        // Edit Row
        _editRow: function (nRow) {
            var that = this;
            var jqTds = $('>td', nRow);
            var columnsDatatables = this.data.table.settings().init().columns;
            var columnIndex = 0;
            var first = true;
            
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
            
            /* Ejecuto el evento */
            if (typeof this.settings.onEdit !== 'undefined') {
                this.settings.onEdit(jqTds, this.data.table.row(nRow).data());
            }
            
            that.actionOriginal = $(jqTds[that.settings.columnAction]).html();
            $(jqTds[that.settings.columnAction]).html('<div class="btn-group"><a href="#" class="btn btn-orden-trabajo btn-white btn-xs save"><i class="fa fa-save"></i></a> <a href="#" class="btn btn-orden-trabajo text-danger btn-white btn-xs cancel"><i class="fa fa-times"></i></a></div>');
            this.data.table.draw();
            try {
                $('.datepicker').datepicker({
                    language: 'es',
                    format: 'dd/mm/yyyy',
                    autoclose: true
                });
                $(".select-inline").select2({
                    theme: "inline",
                    width: '100%'
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
                        var CellData = that.data.table.row(nRow).data();
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
                                    if (_.has(setting, 'method') && setting.method !== '' && !l_error && inputField.val() !== '' && typeof inputField.val() !== 'undefined') {
                                        let validacion = setting.method(inputField.val(), CellData);
                                        if (validacion.response === false) {
                                            that._validationError(inputField, validacion.message);
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
            return l_globalError;
            
        },
        _validationError: function (inputField, message) {
            if (this.settings.errorClass) {
                $(inputField).addClass(this.settings.errorClass);
            } else {
                $(inputField).addClass('edit-input-error');
            }
            if (message) {
                $("<div class='animated fadeInDown text-danger dfn remove-margin-b error-message'>" + message + "</div>").insertAfter(inputField);
            }
        },
        _getInputHtml: function (currentColumnIndex, settings, oldValue, column, jqTds, instance) {
            var inputSetting, inputType, input, inputCss, confirmCss, cancelCss;
            input = {"focus": true, "html": null};

            if (settings.inputTypes) {
                $.each(settings.inputTypes, function (index, setting) {
                    if (setting.column == currentColumnIndex) {
                        inputSetting = setting;
                        inputType = inputSetting.type.toLowerCase();
                    }
                });
            }
            if (settings.inputCss) {
                inputCss = settings.inputCss;
            }
            if (settings.confirmationButton) {
                confirmCss = settings.confirmationButton.confirmCss;
                cancelCss = settings.confirmationButton.cancelCss;
                inputType = inputType + "-confirm";
            }
            
            switch (inputType) {
                case "list":
                    if (inputSetting.class) {
                        inputCss = inputSetting.class;
                    }
                    
                    console.log('Input data: ', column.data);
                    
                    input.html = "<select class=' " + inputCss + " select-" + column.data + instance + "' id='select-" + column.data + instance + "' value='" + oldValue + "'>";
                    if (oldValue == 'null') {
                        oldValue = null;
                    }
                    $.each(inputSetting.options, function (index, option) {
                            input.html = input.html + "<option value='" + option.value + "'";
                            if (option.value == 'null') {
                                option.value = null;
                            }
                            if (oldValue == option.value) {
                                input.html = input.html + " selected ";
                            }
                            input.html = input.html + ">" + option.display + "</option>";
                    });
                    input.html = input.html + "</select>";
                    input.focus = false;
                    
                    /* Capturo los eventos de los inputs */
                    if (typeof inputSetting.callBack !== 'undefined'){
                        $(document).on('select2:select', '#select-' + this._getSelectorName(column.data) + instance, {
                            domTD: jqTds, settings: inputSetting, columna: currentColumnIndex
                        }, function (events) {
                            events.data.settings.callBack(this.value, events );
                        });
                    };

                    break;
                case "number": // text input w/ confirm
                   
                    input.html = "<input autofocus type='text' class='" + inputCss + " text-right " + column.data + instance + "' id='input-" + column.data + instance + "' value='" + oldValue + "'></input>";
                    
                    /* Capturo los eventos de los inputs */
                    if (typeof inputSetting.callBack !== 'undefined'){
                        $(document).on('keyup', '#input-' + this._getSelectorName(column.data) + instance, {
                            domTD: jqTds, settings: inputSetting, columna: currentColumnIndex
                        }, function (events) {
                            events.data.settings.callBack(this.value, events );
                        });
                    };                    
                    break;
                case "checkbox": // text input w/ confirm
                    //input.html = "<input id='ejbeatycelledit' class='" + inputCss + "' value='"+oldValue+"'></input>&nbsp;<a href='#' class='" + confirmCss + "' onclick='$(this).updateEditableCell(this)'>Confirm</a> <a href='#' class='" + cancelCss + "' onclick='$(this).cancelEditableCell(this)'>Cancel</a> ";
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
                    /* Capturo los eventos del datepicker */
                    if (typeof inputSetting.callBack !== 'undefined'){
                        $(document).on('change', '#' + data + instance, {
                            domTD: jqTds, settings: inputSetting, columna: currentColumnIndex
                        }, function (events) {
                            events.data.settings.callBack(this.value, events );
                        });
                    };                    
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
        _disabledFields: function ( domTD, elementos, editar ) {
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
                        if ( editar === 'undefined') {
                            $(inputField).val(null);
                        }
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


/**
 * Lista y edicion de Contratistas.
 *
 * @type {{init}}
 */
var ContractorListEdit = function() {

    //var dataTableContractors = new Array();
    var $table;
    
    var initDataContractors = function() {
       /* Esta rutina carga todos los datos al array, para poder usarlo, deberiamos
        * Generar una llamada Ajax con el array, o pasarlo desde el controller en el 
        * formato que deseamos.
        */ 
        dataTableContractors = new Array();
        dataTableInsumos = new Array();
        InsumosParaCertificar = new Array();
        
        /* Ahora paso todos los registros a los lotes */
        insumos = new Array();
        insumos = datos.ordenTrabajo['orden_trabajos_insumos'];  //[0]['orden_trabajos_insumos'];
        for (var i = 0; i< insumos.length; i++){
            dataTableInsumos.push(
                {
                    producto: insumos[i]['producto'].nombre,
                    unidad: insumos[i]['unidade'].nombre,
                    dosis: insumos[i].dosis,
                    cantidad: insumos[i].cantidad,
                    entrega: insumos[i].entrega,
                    lote: insumos[i]['productos_lote'] ? insumos[i]['productos_lote'].nombre : '',
                    devolucion: insumos[i].devolucion,
                    almacen: insumos[i]['almacene'].nombre,
                    utilizado: insumos[i].utilizado,
                    dosis_aplicada: insumos[i].dosis_aplicada,
                    id: insumos[i].id,
                    id_distribuciones: insumos[i].orden_trabajos_distribucione_id 
                }
            );
        }

        distribucion = new Array();
        distribucion = datos.ordenTrabajo['orden_trabajos_distribuciones'];
        for (var i = 0; i< distribucion.length; i++){
            dataTableContractors.push(
                {
                    labor: distribucion[i]['proyectos_labore'].nombre,
                    unmedida: distribucion[i]['unidade'].nombre,
                    cc: distribucion[i]['proyecto'].nombre,
                    lote: distribucion[i]['lote'].nombre,
                    has: distribucion[i]['superficie'],
                    certificadas: distribucion[i].hascertificadas,
                    total: distribucion[i]['superficie'],
                    moneda: distribucion[i]['moneda'].simbolo_oracle,
                    importe: distribucion[i].importe,
                    insumos: distribucion[i]['proyectos_labore'].insumos,
                    labor_id: distribucion[i]['proyectos_labore'].id,
                    importe_certificado: distribucion[i]['importe_certificado'] ? distribucion[i]['importe_certificado'] : 0,
                    id: distribucion[i]['id']
                }
            );
        }
        
        certificar = new Array();
        
        certificar = datos.InsumosCertificar['orden_trabajos_insumos'];
        for (var i = 0; i< certificar.length; i++){
            InsumosParaCertificar.push(
                {
                    producto: certificar[i]['producto'].nombre,
                    unidad: certificar[i]['unidade'].nombre,
                    dosis: certificar[i].dosis,
                    cantidad: certificar[i].cantidad,
                    entrega: certificar[i].entrega,
                    lote: certificar[i]['productos_lote'] ? certificar[i]['productos_lote'].certificar : '',
                    devolucion: certificar[i].devolucion,
                    almacen: certificar[i]['almacene'].nombre,
                    utilizado: certificar[i].utilizado,
                    dosis_aplicada: certificar[i].dosis_aplicada,
                    id: certificar[i].id,
                    id_distribuciones: certificar[i].orden_trabajos_distribucione_id 
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
    
    var reloadDataContractors = function() {
        /* Recarga los datos */
        $table.clear();
        $("#table-loader").addClass('hidden');
        $.ajax({
            type:"POST", 
            async:true,
            data: $('#id').serialize(),
            url:"/OrdenTrabajos/nuevaordentrabajo",    /* Pagina que procesa la peticion   */
            success:function (data){   
                datos = JSON.parse(data);
                
                initDataContractors();
                $table.rows.add(dataTableContractors);
                $table.draw();
            }
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
                DataContractors[index].id = RowContractor.id;
                
                console.log(RowContractor.importe);
                
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
                    id: RowContractor.id
                });
            return l_DataNewContractors[l_DataNewContractors.length - 1];
        } else {
            return DataContractors[l_index];
        }
    };
    
    var initTable = function() {
        //var actionsTemplate = _.template($("#row-actions-template").text());
        var actionsTemplate = _.template($("#row-actions-insumos").text());
        
        if (InsumosParaCertificar.length > 0) {

            $('#distribuir-insumos').removeClass('hidden');
            $("#dt_insumos").removeClass('hidden');
            $('.certificar-ot').attr("disabled", true);
            
            $TableCertificar = $(".table-detalle-insumos").DataTable({
                pageLength: 30,
                data: InsumosParaCertificar,
                autoWidth: false,
                deferRender: false,
                dom: 'rt',
                columns: [{
                        data: 'producto',
                        defaultContent: '',
                        width: '30%'
                    },{
                        data: 'lote',
                        defaultContent: '',
                        width: '15%'
                    },{
                        data: 'unidad',
                        defaultContent: ''
                    },{
                        data: 'dosis',
                        defaultContent: '',
                        className: 'text-center'
                    },{
                        data: 'cantidad',
                        defaultContent: '',
                        className: 'text-center'
                    },{
                        data: 'entrega',
                        defaultContent: '',
                        className: 'text-center'
                    },{
                        data: 'devolucion',
                        defaultContent: '',
                        className: 'text-center'
                    },{
                        data: 'dosis_aplicada',
                        defaultContent: '',
                        className: 'text-center'
                    },{
                        data: 'almacen',
                        defaultContent: ''
                    },{
                        data: 'id',
                        //width: '5%',
                        visible: false,
                        className: 'text-center cell-double-action no-custo no-edit',
                        render: function(data, type, row) {
                            if (type === 'display' || type === 'filter'){
                                /* Si tiene entregas (=1) no muestro las opciones de editar/eliminar linea */
/*                                         if (row.entregas == '0'){ */
                                    //return actionsTemplateMachine({});
/*                                        } */
                            }
                            /* return data; */
                        },
                        responsivePriority: 1,
                        defaultContent: ''
                    },{
                        data: 'distribucionId',
                        sortable: false,
                        visible: false,
                        defaultContent: ''
                    }
                ],
                ordering: false
            });
        }
        else {
            $("#dt_insumos").addClass('hidden');
            $('#distribuir-insumos').addClass('hidden');
            $('.certificar-ot').attr("disabled", false);
        }
        
        $table = $(".contractors-table").DataTable({
            pageLength: 30,
            destroy: true,
            deferRender: false,
            data: dataTableContractors,
            dom: "<'row'<'col-sm-12'tr>>",
            columns: [{
                    className: 'details-control-contractors no-custo no-edit',
                    sortable: false,
                    data: null,
                    defaultContent: '<i class="icon_expand glyphicon glyphicon-menu-down" data-toggle="tooltip" data-placement="top" data-original-title="Insumos utilizados"></i>'                    
                },{
                    className: 'details-control-contractors no-custo no-edit',
                    data: 'labor',
                    sortable: false,
                    responsivePriority: 1,
                    defaultContent: ''   
                },{
                    data: 'unmedida',
                    sortable: false,
                    responsivePriority: 2,
                    defaultContent: ''
                },{
                    data: 'cc',
                    sortable: false,
                    responsivePriority: 2,
                    defaultContent: ''
                },{
                    data: 'lote',
                    sortable: false,
                    responsivePriority: 1,
                    defaultContent: ''
                },{
                    data: 'has',
                    class:'text-center',
                    sortable: false,
                    responsivePriority: 1,
                    defaultContent: ''
                },{
                    data: 'certificadas',
                    class: 'text-center',
                    sortable: false,
                    responsivePriority: 1,
                    defaultContent: '',
                    render: function(data, type, row) {
                        return '<span class="badge badge-success">&nbsp;&nbsp;' + data + '&nbsp;&nbsp;</span>';
                    }
                },{
                    data: 'moneda',
                    sortable: false,
                    responsivePriority: 1,
                    defaultContent: ''
                },{
                    data: 'importe',
                    class:'text-right',                    
                    sortable: false,
                    responsivePriority: 1,
                    defaultContent: '0'                    
                },{
                    data: 'importe_certificado',
                    class:'text-right',                    
                    sortable: false,
                    responsivePriority: 1,
                    defaultContent: '0',
                    render: function(data, type, row) {
                        return '<span class="badge badge-success">&nbsp;&nbsp;' + data + '&nbsp;&nbsp;</span>';
                    }
                },{
                    data: 'insumos',
                    sortable: false,
                    responsivePriority: 1,
                    defaultContent: '',
                    visible: false
                },{
                    data: '',
                    defaultContent: '',
                    sortable: false,
                    className: 'cell-single-action no-custo no-edit',
                    render: function(data, type, row) {
                        return actionsTemplate({});
                    },
                    responsivePriority: 1                   
                },{
                    data: 'id',
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
            
            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
                $(tr).find(".icon_expand").removeClass('glyphicon-menu-right');
                $(tr).find(".icon_expand").addClass('glyphicon-menu-down');                
            }
            else {
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
                    pageLength: 30,
                    data: dataInsumos,
                    autoWidth: false,
                    deferRender: false,
                    dom: 'rt',
                    columns: [
                        {
                            data: 'producto',
                            defaultContent: '',
                            width: '30%'
                        },{
                            data: 'lote',
                            defaultContent: '',
                            width: '15%'
                        },{
                            data: 'unidad',
                            defaultContent: ''
                        },{
                            data: 'dosis',
                            defaultContent: '',
                            className: 'text-center'
                        },{
                            data: 'cantidad',
                            defaultContent: '',
                            width: '10%',
                            className: 'text-center'
                        },{
                            data: 'entrega',
                            defaultContent: '',
                            width: '10%',
                            className: 'text-center'
                        },{
                            data: 'devolucion',
                            defaultContent: '',
                            width: '10%',
                            className: 'text-center'
                        },{
                            data: 'dosis_aplicada',
                            defaultContent: '',
                            width: '10%',
                            className: 'text-center'
                        },{
                            data: 'almacen',
                            defaultContent: '',
                            width: '15%'
                        },{
                            data: 'id',
                            //width: '5%',
                            className: 'text-center cell-double-action no-custo no-edit',
                            render: function(data, type, row) {
                                if (type === 'display' || type === 'filter'){
                                    /* Si tiene entregas (=1) no muestro las opciones de editar/eliminar linea */
    /*                                         if (row.entregas == '0'){ */
                                        return actionsTemplateMachine({});
    /*                                        } */
                                }
                                /* return data; */
                            },
                            responsivePriority: 1,
                            defaultContent: ''
                        },{
                            data: 'distribucionId',
                            sortable: false,
                            visible: false,
                            defaultContent: row.data().id
                        }
                    ],
                    ordering: false
                });
                var lt_disabled = {0:0, 1: 1, 2: 2, 5: 5, 6: 6, 7: 7, 8:8};      
                var subTableEdit = new $.fn.dataTable.altEditorTable($subTable, {
                    columnAction: 6,
                    temporalId: 'id',
                    onUpdate: callbackEditTableInsumos,
                    inputCss: 'edit-input-inline',
                    errorClass: 'edit-input-error',
                    columns: [ 3 ],
                    disabledFields: lt_disabled,
                    inputTypes: [
                        { /* Cantidad - Sale de la dosis x has */
                            column: 3,
                            type: "number"
                        },{
                            column: 4,
                            type: "number"
                        }
                    ]
                });

                tr.addClass('shown');
                $(tr).find(".icon_expand").removeClass('glyphicon-menu-down');
                $(tr).find(".icon_expand").addClass('glyphicon-menu-right');            
            }
        });
    };
    

    var formatDetails = function(callback) {
        var templateDetails = _.template($("#details-rows-template").text());
        var lt_dataTableMachines = new Array();

        return templateDetails({
            idContractor: callback.id,
            machines: lt_dataTableMachines
        });
    };

    var callbackEditTableInsumos = function(table, aData, flagDelete, actionOriginal, cellAction, temporalIdField) {
        var RowMachine = new Object;
        var RowMachineUpdate = new Object;
        var distribucionId;

        /* Obtengo el Id de la linea de Distribuciones */
//        var columns = table.settings().init().columns;
//        $.each(columns, function(index, column) {
//            if (column.data === 'distribucionId') {
//                distribucionId = column.defaultContent;
//                return true;
//            }
//        });
        
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
//            $.ajax({
//                type: 'POST',
//                url: '/orden-trabajos/guardarinsumos',
//                data: RowInsumosPost,
//                success: function(response) {
//                    
//                    console.log('Guardar Insumos: ', response );
//                    
//                    /* Se guardó correctamente al parecer */
//                    var phpdata = JSON.parse(response);
//                    
//                    /* Limpio la SubTabla */
//                    table.rows().eq(0).each( function ( index ) {
//                        var row = table.row( index );
//                        var data = row.data();
//                        console.log('Data: ', data );
//                        if(data.temporalId && data.temporalId !== 0) {
//                            /* Esta es la linea que se estuvo editando */
//                            data.producto = phpdata.producto;
//                            data.almacen = phpdata.almacen;
//                            data.unidad = phpdata.unidad;
//                            data.temporalId = 0;
//                            data.entregas = 0;
//                            row.data(data).draw();
//                        } else {
//                            if (data.id == phpdata.id){
//                                data.producto = phpdata.producto;
//                                data.almacen = phpdata.almacen;
//                                data.unidad = phpdata.unidad;
//                                row.data(data).draw();
//                            }
//                        }
//                    } ); 
//            
//                    $('.create-contractor').attr("disabled", false);
//                    $(cellAction).html(actionOriginal);
//                    $(cellAction).find('.tooltip').remove();
//                    
//                    $('#EjecutarOT').attr("disabled", false);
//                }
//            });
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

var ListaCertificaciones = function(data = null) {
    var $table;
    
    var dataTableCertificaciones = new Array();
    
    var initDataCertificaciones = async function() {
       /* Esta rutina carga todos los datos al array, para poder usarlo, deberiamos
        * Generar una llamada Ajax con el array, o pasarlo desde el controller en el 
        * formato que deseamos.
        */ 
        dataTableCertificaciones = new Array();

        let Cotizacion = 1;
        let MonedaOrigen = '';
        let MonedaDestino = '';
        
        if (data.orden_trabajo.establecimiento.establecimientos_organizacione.moneda_id != data.moneda_id) {
            if (data.orden_trabajo.orden_trabajos_estado_id == 3) {
                let Mensaje = `La organización ${ data.orden_trabajo.establecimiento.establecimientos_organizacione.nombre } utiliza
                       ${data.orden_trabajo.establecimiento.establecimientos_organizacione.moneda.nombre} como moneda y usted ha definido ${data.moneda.nombre} para la labor.
                       Debe convertir la moneda especificando el tipo de cambio correcto.`;
                $('#certificacion').text(Mensaje);
                $('.certificacion-notificacion').removeClass('hidden');

                let Hoy = moment().format("YYYY-MM-DD");
                MonedaDestino = data.orden_trabajo.establecimiento.establecimientos_organizacione.moneda.simbolo_oracle;
                MonedaOrigen = data.moneda.simbolo_oracle;

                Cotizacion = await ObtenerCotizacion(Hoy, MonedaOrigen, MonedaDestino );
            }
        }
        
        /* Si alquila implemento */
        if (data.orden_trabajos_distribuciones_tarifario && data.orden_trabajos_distribuciones_tarifario.alquiler != '0' ) {
            let Mensaje = '';
            if (data.orden_trabajos_distribuciones_tarifario.orden_trabajo_alquiler_id !==  null) {
                Mensaje = `Hay una OT ${ data.orden_trabajos_distribuciones_tarifario.orden_trabajo_alquiler_id } de alquiler de implementos generada a nombre de ${ data.orden_trabajos_distribuciones_tarifario.proveedore.nombre }.`;
            } else {
                Mensaje = `Al FINALIZAR la certificación, se va a generar una OT por alquiler de implemento a nombre de ${ data.orden_trabajos_distribuciones_tarifario.proveedore ? data.orden_trabajos_distribuciones_tarifario.proveedore.nombre : '' } por el 
                    ${data.orden_trabajos_distribuciones_tarifario.porcentaje } %.`;    
            }
            $('#alquiler').text(Mensaje);
            $('.certificacion-alquiler').removeClass('hidden');
        }
        
        /* Valores por defecto a cargarse cuando se agrega una linea */
        let Importe = data.importe ? data.importe : 0;
        Importe = Importe * Cotizacion;
        
        defaultValues = new Array();
        defaultValues = {
                            tarifa: data.importe,
                            importe: Importe.toFixed(2),
                            superficie_ordenada: data.superficie,
                            orden_trabajos_distribucione_id : data.id,
                            tipo_cambio: Cotizacion,
                            moneda_id: data.moneda_id,
                            organizacion_moneda_id: data.orden_trabajo.establecimiento.establecimientos_organizacione.moneda_id,
                            organizacion_moneda: MonedaDestino, 
                            moneda: MonedaOrigen,
                            moneda_ordenada: data.moneda.simbolo_oracle,
                            tarifario: data.orden_trabajos_distribuciones_tarifario ? data.orden_trabajos_distribuciones_tarifario : ''
                        };

        /* 
         * Si la zona del establecimiento es 1 (Arroz) ponemos el tipo de moneda
         * predefinida como pesos - HARDCODE - FIXIT
         */
        if (data.orden_trabajo.establecimiento.zona_id == '1' ) {
            defaultValues.moneda_id = 1;
         //   defaultValues.moneda_ordenada = 'ARS';
        }
        
        let certificaciones = data.orden_trabajos_certificaciones;
        /* Ahora paso todos los registros a los lotes */
        for (var i = 0; i< certificaciones.length; i++){
            dataTableCertificaciones.push(
                {
                    id: certificaciones[i].id,
                    fecha: moment(certificaciones[i].fecha_final).format("DD/MM/YYYY"),
                    tarifa: data.importe ? data.importe : '0',
                    importe: certificaciones[i].precio_final,
                    observaciones: certificaciones[i].observaciones,
                    cantidad: certificaciones[i].has,
                    orden_trabajos_distribucione_id: certificaciones[i].orden_trabajos_distribucione_id,
                    fecha_ordenado: certificaciones[i].has,
                    superficie_ordenada: data.superficie,
                    moneda_id: certificaciones[i].moneda_id,
                    tipo_cambio: certificaciones[i].tipo_cambio ? certificaciones[i].tipo_cambio : '1',
                    organizacion_moneda: MonedaDestino, 
                    moneda: certificaciones[i].moneda ? certificaciones[i].moneda.simbolo_oracle : MonedaOrigen,
                    tarifario: data.orden_trabajos_distribuciones_tarifario ? data.orden_trabajos_distribuciones_tarifario : '',
                    moneda_ordenada: data.moneda.simbolo_oracle
                }
            );
        }
        
        $table = $(".certificacion-table").DataTable();
        $("#table-loader").addClass('hidden');
        
        $table.clear();
        $table.rows.add(dataTableCertificaciones);
        $table.draw();
        
    };
    
    var reloadDataContractors = function() {
        /* Recarga los datos */
//        $table = $(".certificacion-table").DataTable();
//        $("#table-loader").addClass('hidden');
        
        initDataCertificaciones();

    };
    
    var initTable = function() {
        var actionsTemplate = _.template($("#row-actions-template").text());
        $table = $(".certificacion-table").DataTable({
            pageLength: 20,
            destroy: true,
            deferRender: false,
            data: dataTableCertificaciones,
            dom: "<'row'<'col-sm-12'tr>>",
            ordering: false,
            autoWidth: false,
            columns: [
                {
                    data: 'fecha',
                    sortable: false,
                    responsivePriority: 2,
                    defaultContent: '',
                    class: 'text-center',
                    width: '10%',
                    render: function(data, type, row) {
                        return data;
                    }
                }, {
                    data: 'cantidad',
                    sortable: false,
                    responsivePriority: 2,
                    defaultContent: '',
                    class: 'text-right',
                    width: '10%'
                },{
                    data: 'tarifa',
                    sortable: false,
                    responsivePriority: 2,
                    defaultContent: '',
                    class: 'text-right',
//                    width: '15%'
                    render: function (data, type, row) {
                        return data;
                    }
                },{
                    data: 'moneda_id',
                    sortable: false,
                    responsivePriority: 2,
                    defaultContent: '',
                    class: 'text-right',
                    render: function(data, type, row) {
                        return row.moneda;
                    }
                } , {
                    data: 'tipo_cambio',
                    sortable: false,
                    responsivePriority: 2,
                    defaultContent: '',
                    class: 'text-right',
                    width: '10%'
                },{
                    data: 'importe',
                    sortable: false,
                    responsivePriority: 2,
                    defaultContent: '',
                    class: 'text-right',
                    width: '15%'
                }, {
                    data: 'observaciones',
                    sortable: false,
                    responsivePriority: 1,
                    defaultContent: ''
                }, {
                    data: '',
                    defaultContent: '',
                    sortable: false,
                    width: '28px',
                    className: 'no-custo no-edit',
                    render: function(data, type, row) {
                        return actionsTemplate({});
                    },
                    responsivePriority: 1                   
                }, {
                    data: 'id',
                    defaultContent: '0',
                    visible: false
                }, {
                    data: 'orden_trabajos_distribucione_id',
                    defaultContent: '0',
                    visible: false
                }, {
                    data: 'superficie_ordenada',
                    defaultContent: '0',
                    visible: false
                }, {
                    data: 'organizacion_moneda',
                    defaultContent: '0',
                    visible: false
                },{
                    data: 'moneda',
                    defaultContent: '0',
                    visible: false
                }, {
                    data: 'tarifario',
                    defaultContent: '0',
                    visible: false
                }
            ]
        });
        
        var lt_disabled = {};
       
        /* Si se permite la edicion, habilito las tablas editables */
        var editTable = new $.fn.dataTable.altEditorTable($table, {
            columnAction: 7,
            inputCss: 'input-small form-control edit-input-inline',
            createCssEvent: 'crear-certificacion',
            onUpdate: callbackEditTableCertificacion,
            onEdit: callbackEditandoTabla,
            errorClass: 'edit-input-error',
            nombre: 'TablaCertificaciones',
            defaultValues: defaultValues,
            disabledFields: lt_disabled,
            columns: [ 0, 1, 2, 3, 4, 5, 6, 7, 8],
            validations: [
                {   column: 0,
                    allowNull: false,
                    message: 'Fecha de Labor',
                    method: methodFecha
                },{
                    column: 1,
                    allowNull: false,
                    message: 'Falta la cantidad',
                    method: methodCantidad
                }, {
                    column: 2,
                    allowNull: false,
                    message: 'Falta la tarifa',
                    method: methodNumber
                },                
                {
                    column: 4,
                    allowNull: false,
                    message: 'Falta el tipo de cambio',
                    method: methodNumber
                },{
                    column: 5,
                    allowNull: false,
                    message: 'Falta el importe final',
                    method: methodNumber
                }
            ],
            inputTypes: [
                { /* Fecha de Labor */
                    column: 0,
                    type: "date",
                    callBack: callBackFechaCotizacion,
                    class: "input-small edit-input-inline"
                }, { /* Cantidad */
                    column: 1,
                    type: "number",
                    class: "input-small edit-input-inline"
                }, { /* Tarifa */
                    column: 2,
                    type: "number",
                    callBack: callBackTarifas,
                    class: "input-small edit-input-inline"
                }, { /* Moneda */
                    column: 3,
                    type: "list",
                    callBack: callBackMonedas, 
                    class: "select-inline",
                    options: mapOptionsList(monedas)
                }, { /* Tipo de cambio */
                    column: 4,
                    type: "number",
                    callBack: callBackTarifas,
                    class: "input-small edit-input-inline"
                }, { /* Precio Final */
                    column: 5,
                    type: "number",
                    class: "input-small edit-input-inline"
                }, { /* Observaciones */
                    column: 6,
                    type: "text",
                    class: "input-small edit-input-inline"
                }
            ]
        });
    };

    /**
     * Esta función recibe todos los datos de una fila una vez que se pasan las validaciones.
     */
    var callbackEditTableCertificacion = function(table, aData, flagDelete, actionOriginal, cellAction, temporalIdField, nRow) {
        $("#table-loader").removeClass('hidden');
        
        var fecha = aData.fecha;
        if (moment(fecha, 'DD/MM/YYYY').isValid()) {
            fecha = moment(fecha, 'DD/MM/YYYY').format('YYYY-MM-DD HH:mm:ss');
        }
        
        if (flagDelete) {
            EliminarCertificacion (aData);
            return true;
        }
        
        let dataForm = new FormData();
        dataForm.append('fecha_final', fecha);
        dataForm.append('precio_final', aData.importe);
        dataForm.append('has', aData.cantidad);
        dataForm.append('observaciones', aData.observaciones);
        dataForm.append('tipo_cambio', aData.tipo_cambio);
        dataForm.append('moneda_id', aData.moneda_id);
        
        /* Si ya existe el registro */
        if (aData.id) {
            fetch(`/orden-trabajos-certificaciones/edit/${aData.id}.json`, {
                method: 'POST',
                body: dataForm
            })
            .then( res => res.json())
            .then( data => {
                
                if (data.status == 'success') {
                    $("#table-loader").addClass('hidden');
                    $('.create-contractor').attr("disabled", false);
                    $(cellAction).html(actionOriginal);
                    $(cellAction).find('.tooltip').remove();
                    $('.certificacion-notificacion').removeClass('hidden').addClass('hidden');
                    
                    /* Redibujo el historico de certificaciones */
                    if (data.certificaciones) {
                        RefreshTotalesCertificados (data.certificaciones);
                        
                        /* Redibujo la fila de distribucion */
                        ActualizarLineaDistribucion (data.certificaciones, aData);
                        
                        /* actualizo la linea de distribucion */
                        aData.moneda = data.certificacion.moneda.simbolo_oracle;
                        table.row(nRow).data(aData).draw();
                        
                    }
                } else {
                    toastr.error(data.message);
                    $("#table-loader").addClass('hidden');
                }
            });
        } else {
            /* Agrego los datos que faltaban */
            dataForm.append('orden_trabajos_distribucione_id', $('#distribucion-id').val());
            fecha = moment($('#fecha-inicio').val(), 'DD/MM/YYYY').format('YYYY-MM-DD HH:mm:ss');
            dataForm.append('fecha_inicio', fecha);
            dataForm.append('orden_trabajo_id', $('#orden-trabajo').val());
            
            fetch(`/orden-trabajos-certificaciones/add.json`, {
                method: 'POST',
                body: dataForm
            })
            .then( res => res.json())
            .then( data => {
                if (data.status == 'success') {
                    
                    reloadTable (data.certificacion);
                    
                    $("#table-loader").addClass('hidden');
                    $('.create-contractor').attr("disabled", false);
                    $(cellAction).html(actionOriginal);
                    $(cellAction).find('.tooltip').remove();
                    $('.certificacion-notificacion').removeClass('hidden').addClass('hidden');
                    
                    /* Redibujo el historico de certificaciones */
                    if (data.certificaciones) {
                        RefreshTotalesCertificados (data.certificaciones);
                        
                        /* Redibujo la fila de distribucion */
                        ActualizarLineaDistribucion (data.certificaciones, aData);
                        
                        aData.moneda = data.certificacion.moneda.simbolo_oracle;
                        table.row(nRow).data(aData).draw();
                    }
                    
                } else {
                    toastr.error(data.message);
                    
//                    reloadTable (data.certificacion);
//                    
//                    $("#table-loader").addClass('hidden');
//                    $('.create-contractor').attr("disabled", false);
//                    $(cellAction).html(actionOriginal);
//                    $(cellAction).find('.tooltip').remove();
//                    $('.certificacion-notificacion').removeClass('hidden').addClass('hidden');
                }
            });
        }
    };
    
    /**
     * Elimino una certificacion realizada, siempre que no haya sido subida a una interfaz.
     * 
     * @param aData Datos de la linea a eliminar
     */
    const EliminarCertificacion = (aData) => {
        fetch(`/orden-trabajos-certificaciones/delete/${aData.id}`, {
            method: 'DELETE'
        })
        .then( res => res.json())
        .then( data => {
            if(data['status']=='success') {
                /* Redibujo los Totales Certificados */
                RefreshTotalesCertificados (data.certificaciones);
                /* Redibujo la fila de distribucion */
                ActualizarLineaDistribucion (data.certificaciones, aData); 
                
            } else {
                toastr.error(data['message']);
                /* Redibujo las certificaciones */
                ListaCertificaciones( data.certificaciones ).initData();
            }
            $("#table-loader").addClass('hidden');
            $('.create-contractor').attr("disabled", false);
            $('.certificacion-notificacion').removeClass('hidden').addClass('hidden');            
            
        }); 
    };
    
    /**
     * Pongo el ID de linea en la linea recien creada
     */
    const reloadTable = (certificacion) => {
        let certificaciones = $(".certificacion-table").DataTable().rows().data();
        $.each(certificaciones, function(index, value) {
            if (!value.id) { /* Es una linea nueva */
                value.id = certificacion.id;
            }
        });
    };
    
    /**
     * Actualizo la linea de distribucion que fue procesada.
     * 
     * $param certificaciones Lista de Certificaciones
     * $param linea Linea de distribucion actual
     */
    const ActualizarLineaDistribucion = (certificaciones, linea) => {
        var table = $('#dt_ordentrabajo').DataTable();
        table.rows().every( function (rowIdx, tableLoop, rowLoop) {
            let linea_actual = this.data();
            let superficie = 0;
            let importe = 0;            
            /* Esta es la misma linea de distribucion */
            if (linea_actual.id == linea.orden_trabajos_distribucione_id) {
                /**
                 * Recorro todas las certificaciones de esta linea y sumo las has
                 * Tomo el valor y multiplico x la cantidad hecha, al final, pongo
                 * el precio promedio certificado.
                 */
                $.each(certificaciones.orden_trabajos_certificaciones, function(index, value) { 
                    if (value.orden_trabajos_distribucione_id == linea_actual.id) {
                        superficie += value.has;
                        importe = importe + (value.has * value.precio_final);
                    }
                });
                linea_actual.certificadas = superficie;
                if (superficie !== '0') {
                    let ImporteCertificado = parseFloat( importe / superficie );
                    linea_actual.importe_certificado = Number.isNaN(ImporteCertificado) ? 0 : ImporteCertificado;
                }
                table.row(rowIdx).data( linea_actual );
            }
        });
        table.draw();
    };
    
    /**
     * Verifico si recibo un valor numerico
     * 
     * @param data valor a verificar
     * @return array Validacion con response y message
     */  
    const methodNumber = (data) => {
        if (isNaN(data)) {
            return respuesta = { response: false,
                                    message: 'No es un n&uacutemero v&aacutelido'
                                  };
        }
        if (data < 0) {
            return respuesta = { response: false,
                                    message: 'No puede poner un importe negativo'
                                  };
        }
        return respuesta = { response: true };
    };
    
     /**
     * Verifico si recibo un valor numerico y si la cantidad no es mayor a lo 
     * ordenado
     * 
     * @param data valor a verificar
     * @return array Validacion con response y message
     */  
    const methodCantidad = (data, celda) => {
        $('.certificacion-notificacion').addClass('hidden');
        if (isNaN(data)) {
            return respuesta = { response: false,
                                     message: 'N&uacutemero no v&aacutelido'
                                   };
        }
        if (data < 0) {
             return respuesta = { response: false,
                                    message: 'Importe negativo'};
         }
        
        let superficie_ordenada = $('#ordenado').val();
        let certificaciones = $(".certificacion-table").DataTable().rows().data();
        
        var superficie_certificada = parseFloat(data);
        $.each(certificaciones, function(index, value) {
            if (value.id) {
                if (value.id != celda.id) {
                    superficie_certificada += parseFloat(value.cantidad);
                }                
            }
            
        });
        
        /* Evito que certifiquen mas de lo ordenado */
        if (superficie_certificada > superficie_ordenada) {
            $('#certificacion').text('La superficie certificada no puede ser mayor a la ordenada.');
            $('.certificacion-notificacion').removeClass('hidden');
            return respuesta = { response: false};
        }
         return respuesta = { response: true };
    };
    
    /**
     * Calculo el importe a pagar, multiplicando la tarifa por el Tipo de Cambio
     */
    const callBackTarifas = (value, event) => {
        if (value == 0) {
            return;
        }
        if (isNaN(value)) {
            return;
        }
        let Tarifa = 0;
        let TipoCambio = 0;
        let Importe = 0;
        $.each(event.data.domTD, function (index, td) {
            if (index === 2 ) { /* Tarifa */
                var inputField;
                if ($(td).find('input').length > 0) {
                    inputField = $(td).find('input');
                }
                Tarifa = $(inputField).val();
            }
            if (index === 4 ) { /* Tipo Cambio */
                var inputField;
                if ($(td).find('input').length > 0) {
                    inputField = $(td).find('input');
                }
                TipoCambio = $(inputField).val();
            }
            if (index === 5 ) { /* Importe */
                var inputField;
                if ($(td).find('input').length > 0) {
                    inputField = $(td).find('input');
                }
                $(inputField).prop('disabled', true);
                
                Importe = Tarifa * TipoCambio;
                $(inputField).val(Importe.toFixed(2));
            }
        });
    };

    /**
     * Callback Monedas
     * 
     * Reviso si la moneda es USD, en ese caso, el tipo de cambio es 1
     * y bloqueo el tipo de cambio
     * TODO: FIXIT
     */
    const callBackMonedas = async (value, event) => {
        
        var tr = $(event.data.domTD).closest('tr');
        var row = $table.row(tr).data();
        
        var fecha_cotizacion = moment(row.fecha, "DD/MM/YYYY").format("YYYY-MM-DD");;
        
        let Cotizacion = await ObtenerCotizacion(fecha_cotizacion, row.moneda_ordenada, row.moneda );
        
        $.each(event.data.domTD, function (index, td) {
            if (index === 2 ) { /* Tarifa */
                var inputField;
                if ($(td).find('input').length > 0) {
                    inputField = $(td).find('input');
                }
                Tarifa = $(inputField).val();
            }
            if (index === 3 ) { /* Tipo Moneda */
                var inputField;
                if ($(td).find('select').length > 0) {
                    inputField = $(td).find('select');
                }
                TipoMoneda = $(inputField).val();
            }
            if (index === 4 ) { /* Tipo Cambio */
                var inputField;
                if ($(td).find('input').length > 0) {
                    inputField = $(td).find('input');
                }
                if (TipoMoneda === '2') { /* Si la OT es en USD, tipo de cambio = 1 */
                    TipoCambio = 1;
                    $(inputField).val(TipoCambio);
                    $(inputField).prop('disabled', true);
                } else {
                    $(inputField).val(Cotizacion);
                    TipoCambio = Cotizacion;
                    $(inputField).prop('disabled', false);
                }
            }
            if (index === 5 ) { /* Importe */
                var inputField;
                if ($(td).find('input').length > 0) {
                    inputField = $(td).find('input');
                }
                $(inputField).prop('disabled', true);
                Importe = Tarifa * TipoCambio;
                $(inputField).val(Importe.toFixed(2));
            }
        });
    };

    /**
     * Obtengo la cotizacion de una moneda para una fecha especifica
     * 
     * @param fecha Fecha a buscar
     * @param moneda Moneda a buscar
     * 
     * @return array Validacion con response y message
     */
    const callBackFechaCotizacion = async (value, event) => {
        if (value == 0) {
            return;
        }

        var tr = $(event.data.domTD).closest('tr');
        var row = $table.row(tr).data();
        var fecha_cotizacion = moment(value, "DD/MM/YYYY").format("YYYY-MM-DD");
        
        console.log('Data: ', row);
        console.log('Moneda ordenada: ', row.moneda_ordenada);
        console.log('Moneda: ', row.moneda);
        
        let Cotizacion = await ObtenerCotizacion(fecha_cotizacion, row.moneda_ordenada, row.moneda );
        
        $.each(event.data.domTD, function (index, td) {
            if (index === 2 ) { /* Tarifa */
                var inputField;
                if ($(td).find('input').length > 0) {
                    inputField = $(td).find('input');
                }
                Tarifa = $(inputField).val();
            }
            if (index === 3 ) { /* Tipo Moneda */
                var inputField;
                if ($(td).find('select').length > 0) {
                    inputField = $(td).find('select');
                }
                TipoMoneda = $(inputField).val();
            }
            if (index === 4 ) { /* Tipo Cambio */
                var inputField;
                if ($(td).find('input').length > 0) {
                    inputField = $(td).find('input');
                }
                if (TipoMoneda === '2') { /* Si la OT es en USD, tipo de cambio = 1 */
                    TipoCambio = 1;
                    $(inputField).val(TipoCambio);
                    $(inputField).prop('disabled', true);
                } else {
                    $(inputField).val(Cotizacion);
                    TipoCambio = Cotizacion;
                    $(inputField).prop('disabled', false);
                }
            }
            if (index === 5 ) { /* Importe */
                var inputField;
                if ($(td).find('input').length > 0) {
                    inputField = $(td).find('input');
                }
                $(inputField).prop('disabled', true);
                Importe = Tarifa * TipoCambio;
                $(inputField).val(Importe.toFixed(2));
            }
        });
       
    };    
    
    /**
     * Obtengo la cotizacion de una moneda para una fecha especifica
     * 
     * @param fecha Fecha a buscar
     * @param moneda Moneda a buscar
     * 
     * @return array Validacion con response y message
     */
    const ObtenerCotizacion = async ( fecha, moneda_origen, moneda_destino ) => {
        /* Enviar datos de busqueda como un Post */
        let dataForm = new FormData();
        dataForm.append("fecha", fecha);
        dataForm.append("origen", moneda_origen);
        dataForm.append("destino", moneda_destino);
        
        try {  
          const respuesta = await fetch('/monedas/consultar-cotizacion.json', {
                  method: 'POST',
                   body: dataForm
              });
          const datos = await respuesta.json();
          
          console.log('ObtenerCotizacion: ', datos);
          
          return datos;
        } catch (error) {
            console.log('Ocurrió un error:', error );
        }
    };
    
    /**
     * Recibe un valor y realiza verificaciones de fecha.
     * 1 - Si la certificacion es < Fecha OT
     * 2 - Si es una fecha futura
     * 3 - Formato dd/mm/yyyy
     * 
     * @param data Fecha en formato dd/mm/yyyy
     * @return array Validacion con response y message
     */
    const methodFecha = (data, celda) => {
        var fecha = data;
        /* Chequeo si es una fecha en el formato valido */
        if (fecha) {
            if (fecha.length !== 10) {
                return respuesta = { response: false,
                                     message: 'Formato Incorrecto, use dd/mm/yyyy'
                                   };
            }
        }
        var fecha = moment(fecha, 'DD/MM/YYYY');
        /* Verifico que la fecha de certificacion NO sea menor a lo ordenado */
        var fecha_ot = moment($('#fecha').val(), 'DD/MM/YYYY');
        if (fecha < fecha_ot) {
            return respuesta = { response: false,
                                 message: 'Fecha menor a la ordenada'
                               };
        }
        /* Verifico que no es una fecha futura */
        var fecha_actual = moment();
        if (fecha > fecha_actual) {
            return respuesta = { response: false,
                                 message: 'Fecha futura'
                               };
        }
        return respuesta = { response: true };
    };
    
    /**
     * Este evento se lanza al iniciar la edición de la tabla
     * @param table Tabla que se edita
     */
    var callbackEditandoTabla = function (table) {
        
        var lt_disabled = {5: 5};
        var columnsDatatables = table;
        
        var tr = $(columnsDatatables).closest('tr');
        var row = $table.row(tr).data();
        
        /* Si tiene un tarifario y el mismo no está habilitado para editar */
        if (row.tarifario && !row.tarifario.proyectos_labores_tarifario.editable) {
            var lt_disabled = { 2: 2, 5: 5};
        }
        marcarBloqueos(columnsDatatables, lt_disabled);
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
    
    return {
        init: function() {
            initTable();           /* Inicio la tabla */
            
            $("#table-loader").addClass('hidden');
            $("#data-contractors").removeClass('hidden');
            $("#page-loader").addClass('hidden');
        },
        initData: function() {
            reloadDataContractors();
        }
    };
};