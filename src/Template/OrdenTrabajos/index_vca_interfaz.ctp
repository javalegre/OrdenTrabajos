<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\OrdenTrabajo[]|\Cake\Collection\CollectionInterface $ordenTrabajos
 */
?>
<div class="row border-bottom white-bg page-heading">
    <div class="col-lg-9 col-md-9 m-t-xs">
        <h3>Reporte de Interfaz de Ajustes &nbsp;&nbsp;<i class="fa fa-spin fa-refresh text-success" id="table-loader"></i></h3>
    </div>
    <div class="col-lg-3 col-md-3 m-t-xs">
        <div class="btn-group pull-right dt-buttons no-margins no-padding">
            <?php
                echo $this->Form->button('<i class="fa fa-search"></i>', ['type' => 'button','title' => 'Buscar registros', 'class'=>'btn btn-sm btn-default BuscarRegistros', 'escape' => false]);
//                echo $this->Html->link('<i class="fa fa-plus"></i>',['controller' => 'OrdenTrabajos', 'action' => 'add'],['type' => 'button','title' => 'Crear nueva OT', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
//                echo $this->Form->button('<i class="fa fa-save"></i>', ['type' => 'button','title' => 'Guardar', 'class'=>'btn btn-sm btn-default EjecutarOT', 'escape' => false]);
//                echo $this->Html->link('<i class="fa fa-list"></i>',['controller' => 'OrdenTrabajos', 'action' => 'index'],['type' => 'button','title' => 'Bandeja de Entrada', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
////                echo $this->Form->button('<i class="sicon sicon-weather"></i>', ['type' => 'button','title' => 'Condiciones Agro Meteorológicas', 'class'=>'btn btn-sm btn-default Condiciones', 'escape' => false]);
//                if ( $ordenTrabajo->certificable === 1 && $this->Acl->check(['controller' => 'OrdenTrabajos/certificarot']) ){
//                    echo $this->Html->link('<i class="fa fa-check text-navy"></i>', ['controller' => 'OrdenTrabajos', 'action' => 'Certificarot', $ordenTrabajo->id] ,['type' => 'button','title' => 'Certificar', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
//                }
//                echo $this->Form->button('<i class="fa fa-trash"></i>', ['type' => 'button','title' => 'Anular OT', 'data-id' => $ordenTrabajo->id,'class'=>'btn btn-sm btn-default AnularOT', 'escape' => false]);
//                //echo $this->Form->button('<i class="fa fa-copy"></i>', ['type' => 'button','title' => 'Generar copia', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
//                echo $this->Html->link('<i class="fa fa-print"></i>',['controller' => 'OrdenTrabajos', 'action' => 'view', $ordenTrabajo->id, '_ext' => 'pdf'],['type' => 'button','title' => 'Imprimir', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
//            ?>
        </div>
    </div>
</div>
<div class="ibox">
    <div class="ibox-content">
        <div class="row">
            <div class="col-md-12 no-margins no-padding">
                <div class="col-md-8 m-l-none">
                    <?= $this->Form->control('establecimiento', ['class' => 'form-control select2', 'label' => 'Organizaciones', 'multiple' => 'multiple']) ?>
                </div>
               <div class="col-md-2 no-margins no-padding">
                    <?= $this->Form->control('desde', ['label' => false, 'class' => 'form-control datepicker', 'label' => 'Desde']) ?>
                </div>
                <div class="col-md-2 m-r-none">
                    <?= $this->Form->control('hasta', ['label' => false, 'class' => 'form-control datepicker', 'label' => 'Hasta']) ?>
                </div>
            </div>
        </div>
        <hr class="m-t-none m-b-sm">
        <div class="row">
            <div class="col-md-12 no-margins no-padding">
                <table class="table table-bordered table-hover table-striped dataTable sin-margen-superior">
                    <thead><?= $this->Html->tableHeaders(['Org','OT','Fecha', 'Almacen', 'Insumo','Cantidad','Unidad','Status','Mensaje','']) ?></thead>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    var DatosInterfaz = new Array();
    
    /* Traigo todos los datos necesarios */
    const ObtenerDatos = () => {
        console.log('Traigo los datos necesarios');
        
        let Desde = null;
        let Hasta = null;
        
        $("#table-loader").removeClass('hidden');
        
        // Reviso la fecha de inicio
        var fecha = $('#desde').val();
        if (fecha) {
            if (fecha.length !== 10) {
                toastr.error("El formato correcto es dd/mm/yyyy.", "Error de Formato");
                $("#table-loader").addClass('hidden');
                return;
            }
        }
        if (moment(fecha, 'DD/MM/YYYY').isValid()) {
            Desde = moment(fecha, 'DD/MM/YYYY').format('YYYY-MM-DD');
        }
        
        // Reviso la fecha de final
        var fecha = $('#hasta').val();
        if (fecha) {
            if (fecha.length !== 10) {
                toastr.error("El formato correcto es dd/mm/yyyy.", "Error de Formato");
                $("#table-loader").addClass('hidden');
                return;
            }
        }
        if (moment(fecha, 'DD/MM/YYYY').isValid()) {
            Hasta = moment(fecha, 'DD/MM/YYYY').format('YYYY-MM-DD');
        }
        
        /* Comienzo el reporte */
        $("#table-loader").removeClass('hidden');
        
        let dataForm = new FormData();
        dataForm.append('establecimientos', $('#establecimiento').val() );
        dataForm.append('desde', Desde );
        dataForm.append('hasta', Hasta );

        fetch(`/OrdenTrabajos/index_vca_interfaz.json`, {
            method: 'POST',
            body: dataForm
          }).then( res => res.json() )
            .then( data => {
               console.log('Data: ', data);
                
                /* Inicializo la tabla */
                ListaCertificaciones( data ).initData();
                
                $("#table-loader").addClass('hidden');
            })
            .catch( function(err) {
                console.log( err );
            });         
        
    };
    
    $(document).ready(function () {
        $("#table-loader").addClass('hidden');
        
        $.fn.dataTable.moment('DD/MM/YYYY');

//        $('.dataTable').DataTable({
//            pageLength: 20,
//            responsive: true,
//            autoWidth: false,
//            dom: "<'row'<'col-sm-6 no-padding'f><'col-sm-6 no-padding botones-tabla-top-right'B>>" +
//                        "<'row'<'col-sm-12 no-margin no-padding'tr>>" +
//                        "<'row'<'col-sm-6'i><'col-sm-6'p>>",
//            buttons: [
//                {  /* Exporto todas las OT a Excel para analisis */
//                    text: "<i class='fa fa-file-excel-o'></i>",
//                    titleAttr: "Listado VCA's",
//                    className: "btn",
//                    action: function () {
//                            $("#table-loader").removeClass('hidden');
//                            $.ajax({
//                                type:"POST", 
//                                async:true,
//                                timeout: 0,
//                                url:"/orden-trabajos/generarexcelvca/vca",    /* Pagina que procesa la peticion   */
//                                success:function (data){
//                                    /* data = JSON.parse(data);*/
//                                    if (data['status'] == 'success'){
//                                        /* Abro el archivo excel generado */
//                                        $(location).attr('href','/dataload/' + data['archivo']);
//                                    } else {
//                                        alert(data['message'] + ' - ' + data['data']);
//                                    }
//                                    $("#table-loader").addClass('hidden');
//                                },
//                                error: function (data) {
//                                    console.log(data);
//                                }
//                            });                        
//
//                    }
//                },
//                {  /* Exporto todas las Entregas/Devoluciones en Excel */
//                    text: "<i class='fa fa-database'></i>",
//                    titleAttr: "VCA's en Oracle",
//                    className: "btn",
//                    action: function () {
//                            $('#OracleVca').modal('show');
//                        }
//                },
//                {  /* voy a una ventana para mostrar errores en la interfaz de ajustes */
//                    text: "<i class='fa fa-search'></i>",
//                    titleAttr: "VCA con errores de Interfaz",
//                    className: "btn",
//                    action: function () {
//                            //location.href = "http://example.com";
//                            //
//                             //$(location).attr('href','/dataload/' + data['archivo']);
//                             
//                             console.log($(location).attr('href','/orden-trabajos/'));
//                             
//                             console.log( $(location) );
//                             
//                             console.log(location);
//                             console.log(window.location.href)
//                        }
//                },                        
//
//                {extend: 'pdf', title: 'ExampleFile', titleAtrr: "PDF", text:"<i class='fa fa-file-pdf-o'></i>"},
//                {extend: 'print',
//                    customize: function (win) {
//                        $(win.document.body).addClass('white-bg');
//                        $(win.document.body).css('font-size', '8px');
//                        $(win.document.body).find('table')
//                            .addClass('compact')
//                            .css('font-size', 'inherit');
//                    }
//                }
//            ]         
//        });
        
        $('.datepicker').datepicker({
            language: 'es',
            format: 'dd/mm/yyyy',
            autoclose: true
        });
        
        $(".select2").select2({
            theme: "bootstrap"
        });
        
        /* Inicio la table */
        ListaCertificaciones().init();
    });
    
    $('.BuscarRegistros').on('click', function() {
        ObtenerDatos();
    });
    
    
    /**
 * Lista y edicion de Certificaciones.
 *
 * @param data Array con los datos de certificaciones.
 */
var ListaCertificaciones = function(data = null) {

    //var dataTableContractors = new Array();
    var $table;
    
    var dataTableCertificaciones = new Array();
    
    var initDataCertificaciones = function() {
       /* Esta rutina carga todos los datos al array, para poder usarlo, deberiamos
        * Generar una llamada Ajax con el array, o pasarlo desde el controller en el 
        * formato que deseamos.
        */ 
        dataTableCertificaciones = new Array();
        let certificaciones = data;
        /* Ahora paso todos los registros a los lotes */
        $.each(certificaciones, function (index, data) {
            dataTableCertificaciones.push(
                {
                    id: data.id,
                    fecha: moment(data.fecha).format("DD/MM/YYYY"),
                    organizacion: data.orden_trabajos_insumo.orden_trabajo.establecimiento.organizacion,
                    ot: data.orden_trabajos_insumo.orden_trabajo.id,
                    almacen: data.almacene.localizacion,
                    insumo: data.producto.nombre,
                    cantidad: data.cantidad,
                    unidad: data.unidade.nombre,
                    status: data.oracle_flag,
                    mensaje: data.interface_error
                }
            );
        });
        
        $table = $(".dataTable").DataTable();
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
        $table = $(".dataTable").DataTable({
            pageLength: 20,
            destroy: true,
            deferRender: false,
            data: dataTableCertificaciones,
/*            dom: "<'row'<'col-sm-12'tr>>",*/
            dom: "<'row'<'col-sm-6 no-padding'f><'col-sm-6 no-padding'>>" +
                        "<'row'<'col-sm-12 no-margin no-padding'tr>>" +
                        "<'row'<'col-sm-6'i><'col-sm-6'p>>",
            ordering: false,
            autoWidth: false,
            columns: [
                {
                    data: 'organizacion',
                    sortable: false,
                    responsivePriority: 2,
                    defaultContent: '',
                    render: function(data, type, row) {
                        return data;
                    }
                }, {
                    data: 'ot',
                    sortable: false,
                    responsivePriority: 2,
                    defaultContent: ''
                },{
                    data: 'fecha',
                    sortable: false,
                    responsivePriority: 2,
                    defaultContent: '',
                    render: function (data, type, row) {
                        return data;
                    }
                },{
                    data: 'almacen',
                    sortable: false,
                    responsivePriority: 2,
                    defaultContent: ''
                },{
                    data: 'insumo',
                    sortable: false,
                    responsivePriority: 2,
                    defaultContent: ''
                }, {
                    data: 'cantidad',
                    sortable: false,
                    responsivePriority: 1,
                    defaultContent: ''
                }, {
                    data: 'unidad',
                    sortable: false,
                    responsivePriority: 1,
                    defaultContent: ''
                },  {
                    data: 'status',
                    sortable: false,
                    responsivePriority: 1,
                    defaultContent: '',
                    render: function(data, type, row) {
                        if (data == 'E') {
                            return `<span class="badge badge-danger">${data}</span>`;
                        } else {
                            return data;
                        }
                    }
                }, {
                    data: 'mensaje',
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
                }
            ]
        });
        
        /* Si se permite la edicion, habilito las tablas editables */
//        var editTable = new $.fn.dataTable.altEditorTable($table, {
//            columnAction: 6,
//            inputCss: 'input-small edit-input-inline',
//            createCssEvent: 'crear-certificacion',
//            onUpdate: callbackEditTableCertificacion,
//            errorClass: 'edit-input-error',
//            nombre: 'TablaCertificaciones',
//            defaultValues: defaultValues,
//            columns: [ 0, 1, 2, 3, 4, 5, 6, 7, 8],
//            validations: [
//                {   column: 0,
//                    allowNull: false,
//                    message: 'Fecha de Labor',
//                    method: methodFecha
//                },{
//                    column: 1,
//                    allowNull: false,
//                    message: 'Falta la cantidad',
//                    method: methodCantidad
//                }, {
//                    column: 2,
//                    allowNull: false,
//                    message: 'Falta el tipo de cambio',
//                    method: methodNumber
//                },                
//                {
//                    column: 3,
//                    allowNull: false,
//                    message: 'Falta el tipo de cambio',
//                    method: methodNumber
//                },{
//                    column: 4,
//                    allowNull: false,
//                    message: 'Falta el importe final',
//                    method: methodNumber
//                }
//            ],
//            inputTypes: [
//                { /* Fecha de Labor */
//                    column: 0,
//                    type: "date",
//                    callBack: callBackFechaCotizacion,
//                    class: "input-small edit-input-inline"
//                }, { /* Cantidad */
//                    column: 1,
//                    type: "number",
//                    class: "input-small edit-input-inline"
//                }, { /* Tarifa */
//                    column: 2,
//                    type: "number",
//                    callBack: callBackTarifas,
//                    class: "input-small edit-input-inline"
//                }, { /* Tipo de cambio */
//                    column: 3,
//                    type: "number",
//                    callBack: callBackTarifas,
//                    class: "input-small edit-input-inline"
//                }, { /* Precio Final */
//                    column: 4,
//                    type: "number",
//                    class: "input-small edit-input-inline"
//                }, { /* Observaciones */
//                    column: 5,
//                    type: "text",
//                    class: "input-small edit-input-inline"
//                }
//            ]
//        });
    };

    /**
     * Esta función recibe todos los datos de una fila una vez que se pasan las validaciones.
     */
    var callbackEditTableCertificacion = function(table, aData, flagDelete, actionOriginal, cellAction, temporalIdField) {
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
                    }
                    
                } else {
                    toastr.error(data.message);
                    $("#table-loader").addClass('hidden');
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
        
        console.log('Certificacion: ', certificacion);
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
            if (index === 3 ) { /* Tipo Cambio */
                var inputField;
                if ($(td).find('input').length > 0) {
                    inputField = $(td).find('input');
                }
                TipoCambio = $(inputField).val();
            }
            if (index === 4 ) { /* Importe */
                var inputField;
                if ($(td).find('input').length > 0) {
                    inputField = $(td).find('input');
                }
                
                //$(inputField).prop('disabled', true);
                
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
        
        console.log('Evento: ', event);
        
        console.log('Fecha cotizacion: ', value);

        var tr = $(event.data.domTD).closest('tr');
        var row = $table.row(tr).data();
        
        console.log('Row Fecha: ', row);
        
        var m = moment(value, "DD/MM/YYYY").format("YYYY-MM-DD");;

        let Cotizacion = await ObtenerCotizacion(m, row.moneda, row.organizacion_moneda );

        $.each(event.data.domTD, function (index, td) {
            if (index === 2 ) { /* Tarifa */
                var inputField;
                if ($(td).find('input').length > 0) {
                    inputField = $(td).find('input');
                }
                Tarifa = $(inputField).val();
            }
            if (index === 3 ) { /* Tipo Cambio */
                var inputField;
                if ($(td).find('input').length > 0) {
                    inputField = $(td).find('input');
                }
                $(inputField).val(Cotizacion);
                TipoCambio = Cotizacion;
            }
            if (index === 4 ) { /* Importe */
                var inputField;
                if ($(td).find('input').length > 0) {
                    inputField = $(td).find('input');
                }
                
                //$(inputField).prop('disabled', true);
                
                Importe = Tarifa * TipoCambio;
                $(inputField).val(Importe.toFixed(2));
            }
        });

        console.log('Cotizacion: ', Cotizacion);
        console.log('Fecha actual: ', m );
       
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
</script>