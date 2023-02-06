<?php
/**
 * Ordenes de Trabajo Reclasificaciones
 *
 * Listado de Reclasificaciones, con datatables Server Side y filtros
 *
 * Desarrollado para Adecoagro SA.
 * 
 * @category CakePHP3
 *
 * @package Ordenes
 *
 * @author Javier Alegre <jalegre@adecoagro.com>
 * @copyright Copyright 2021, Adecoagro
 * @version 1.0.0 creado el 02/08/2022
 */
?>
<script>
    /**
     * ObtenerEstablecimientoId
     * 
     * Devuelvo el ID del establecimiento seleccionado en el select para utilizarlo
     * en los datatables server side
     * 
     * @returns {integer} id del Establecimiento
     */
    const ObtenerEstablecimientoId = () => $('#establecimiento-id').val();
    
    /**
     * ObtenerCampaniaId
     * 
     * Devuelvo el ID de la campaña de monitoreo activa para utilizarlo
     * en los datatables server side
     * 
     * @returns {integer} id del Establecimiento
     */
    const ObtenerProveedorId = () => $('#proveedore-id').val();

    /**
     * Obtener Campaña Id
     * 
     * Devuelvo el ID de campania actual para utilizarlo
     * en los datatables server side
     * 
     * @returns {integer} id del Proyecto
     */
    const ObtenerCampaniaId = () => $('#campania-id').val();

    const Desde = () => {
        var chkFechas = document.getElementById('chkRangoFechas');
        if (chkFechas.checked) {
            return $('#desde').val();
        }
        return '';
    };
    const Hasta = () => {
        var chkFechas = document.getElementById('chkRangoFechas');
        if (chkFechas.checked) {
            return $('#hasta').val();
        }
        return '';
    };
    const ObtenerEstado = () => $('#estado').val();

</script>
<?php
    echo $this->Html->css(['Ordenes.style', 'plugins/daterangepicker/daterangepicker-3.14.1']);
    echo $this->Html->script(['plugins/daterangepicker/daterangepicker-3.14.1.min']);
    echo $this->Form->control('establecimientos', ['type' => 'hidden', 'value' => json_encode($establecimientos)]);
    echo $this->Form->control('desde', ['type' => 'hidden']);
    echo $this->Form->control('hasta', ['type' => 'hidden']);
    echo $this->Form->control('estado', ['type' => 'hidden', 'value' => '']);
?>

<div class="row border-bottom white-bg page-heading">
    <div class="col-md-6 m-t-xs">
        <h3>Reclasificaciones &nbsp;&nbsp;</h3>
    </div>
    <div class="col-md-6 numero-ot m-t-xs">
        <div class="btn-group pull-right dt-buttons no-margins no-padding">
            <?php
//                echo $this->Form->button('<i class="fa fa-cogs"></i>', ['type' => 'button','title' => 'Configuración', 'class'=>'btn btn-md btn-default right-sidebar-toggle', 'escape' => false]);
//                echo $this->Form->button('<i class="sicon sicon-double-check text-navy"></i>', ['type' => 'button','title' => 'Certificar', 'class'=>'btn btn-sm btn-default CertificarCompleto', 'escape' => false]);
//                echo $this->Form->button('<i class="fa fa-copy"></i>', ['type' => 'button','title' => 'Generar copia', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
//                echo $this->Form->button('<i class="fa fa-print"></i>', ['type' => 'button','title' => 'Imprimir', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
            ?>
        </div>        
    </div>
</div>
<?= $this->Form->control('grupo', ['type' => 'hidden', 'value' => $this->request->session()->read('Auth.User.group_id')]) ?>

<!-- Filtros -->
<div class="ibox float-e-margins m-b-xs">
    <div class="row">
        <div class="panel panel-default m-b-none">
            <div class="panel-body no-margins no-padding">
                <div class="col-md-12 m-b-xs m-t-xs no-padding">
                    <div class="col-md-4">
                        <?= $this->Form->control('establecimiento_id',['type'=> 'select', 'label' => false, 'options' => [], 'class' => 'form-control select2', 'required']) ?>
                    </div>
                    <div class="col-md-4 m-l-none m-r-none">
                        <!-- < ?= $this->Form->control('proveedore_id',['type'=> 'select', 'label' => false, 'options' => [], 'class' => 'form-control select2', 'required']) ?> -->
                    </div>
                    <div class="col-md-4">
                        <div class="ot-form-group">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <input type="checkbox" id="chkRangoFechas" checked="">
                                </span>
                                <input id="rango-fechas" type="text" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    
<script>
    /**
     * Asigno los valores por defecto que tienen los establecimientos y campañas
     * 
     * Lo ubico en este lugar para que tome los cambios ANTES que los datatables lo utilicen
     */
    var data = JSON.parse($('#establecimientos').val());
    var EstablecimientoId = localStorage.getItem('OrdenTrabajosReclasificacionesEstablecimientoId') ? localStorage.getItem('OrdenTrabajosReclasificacionesEstablecimientoId') : '';
    
    /* 
     * Establecimientos
     */
    $("#establecimiento-id").select2({
        theme: "bootstrap",
        placeholder: 'Filtrar por establecimientos ...',
        width: '100%',
        allowClear: true,
        data: data,
        templateSelection: function ( data ) {
            if (data.id) {
                let $container = $(`<small class="pull-right">${data.organizacion}</small><div>${data.nombre}</div>`);
                return $container;
            }
            return data.text;
        },
        templateResult: function ( data ) {
            if (data.loading) {
                return data.nombre;
            }
            var $container = $(`<small class="pull-right">${data.organizacion}</small><div>${data.nombre}</div>`);
            return $container;
        },
        matcher: function (params, data) {
                if ($.trim(params.term) === '')  return data; // If there are no search terms, return all of the data
                if (data.nombre.toUpperCase().indexOf(params.term.toUpperCase()) > -1) {
                    var modifiedData = $.extend({}, data, true);
                    return modifiedData;
                }
                return null;
        }
    }).val(EstablecimientoId).trigger('change').on('change.select2', function (e) {
        e.stopPropagation();
        /* Recargamos las tablas */
        $('#ordenes-de-trabajo').DataTable().ajax.reload();
        /* Guardo el nuevo valor por defecto */
        localStorage.setItem('OrdenTrabajosReclasificacionesEstablecimientoId',  $('#establecimiento-id').val());

    });
    
    /* 
     * Configuro las fechas 
     */
    $(function() {
        var start = moment().subtract(29, 'days');
        var end = moment();

        function cb(start, end) {
            $('#rango-fechas span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        }

        $('#rango-fechas').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
               'Hoy': [moment(), moment()],
               'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
               'Ultima semana': [moment().subtract(6, 'days'), moment()],
               'Ultimos 30 días': [moment().subtract(29, 'days'), moment()],
               'Este mes': [moment().startOf('month'), moment().endOf('month')],
               'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, cb);
        
        $('#rango-fechas').on('apply.daterangepicker', function(ev, picker) {
            $('#desde').val(picker.startDate.format('YYYY-MM-DD'));
            $('#hasta').val(picker.endDate.format('YYYY-MM-DD'));
            
            $('#ordenes-de-trabajo').DataTable().ajax.reload();
        });
        cb(start, end);
    });
    
    /* Inicio el filtro de fechas */
    const IniciarControlFechas = () => {
        var chkFechas = document.getElementById('chkRangoFechas');
        let ActivoFiltroFechas = localStorage.getItem('OrdenTrabajosReclasificacionesFiltroFechas') ? localStorage.getItem('OrdenTrabajosReclasificacionesFiltroFechas') : 0;
        
        if (ActivoFiltroFechas == 0) {
            $("#chkRangoFechas").prop("checked", false);
            document.getElementById('rango-fechas').disabled = true;
        } else {
            $("#chkRangoFechas").prop("checked", true);
            document.getElementById('rango-fechas').disabled = false;
        }
        
        chkFechas.addEventListener('click', function() {
            if (chkFechas.checked) {
                localStorage.setItem('OrdenTrabajosReclasificacionesFiltroFechas', 1);
                document.getElementById('rango-fechas').disabled = false;
            } else {
                localStorage.setItem('OrdenTrabajosReclasificacionesFiltroFechas', 0);
                document.getElementById('rango-fechas').disabled = true;
            }
            $('#ordenes-de-trabajo').DataTable().ajax.reload();
        });
    };
    IniciarControlFechas();
   
    /**
    * Dibuja los botones que precisamos en el listado
    * @param data The data for the cell (based on columns.data)
    * @param type 'filter', 'display', 'type' or 'sort'
    * @param full The full data source for the row
    * @param meta Object containing additional information about the cell
    * @returns Manipulated cell data
    */
    datatable_botones = function (data, type, full, meta)
    {
        if (type === 'display') {
            return '<div class="btn-group">' +
                        '<a target="_blank" href="/orden-trabajos-reclasificaciones/view/' + data + '" type="button" title="Ver" class="btn btn-xs btn-white btn-orden-trabajo"><i class="fa fa-eye"></i></a>' +
                        '<a target="_blank" href="/orden-trabajos-reclasificaciones/edit/' + data + '" type="button" title="Editar" class="btn btn-xs btn-white btn-orden-trabajo"><i class="fa fa-pencil"></i></a>' +
                        '<a href="/orden-trabajos-reclasificaciones/view/' + data + '.pdf" type="button" title="Generar PDF" class="btn btn-xs btn-white btn-orden-trabajo"><i class="fa fa-print"></i></a>' +
                        /* '<a href="#" data-id="' + data + '" title="Anular OT" type="button" class="btn btn-xs btn-white btn-orden-trabajo EliminarOT"><i class="fa fa-trash"></i></a>' + */
                    '</div>';		
        }
        return data;
    };

    datatable_fecha = function (data, type, full, meta)
    {
        if (type === 'display') {
            if (moment(data).isValid()) {
                return  moment.utc(data).format('DD/MM/YYYY');
            }
            return '';
        }
        return data;
    };
    datatable_fecha_creacion = function (data, type, full, meta)
    {
        if (type === 'display') {
            return '<span class="small">' + full.user ? full.user.nombre : ''  + '</span><br>' + 
                       '<span class="small">' + moment(full.created).format('DD/MM/YYYY HH:mm') + '</span>';
        }
        return data;
    };
    
    datatable_procesado = function (data, type, full, meta)
    {
        if (type === 'display') {
            if (data == '1') {
                return '<div class="btn-group">' +
                        '<a href="#" data-id="' + data + '" title="Tiene reporte" type="button" class="btn btn-xs btn-white btn-orden-trabajo text-success"><i class="fa fa-check"></i></a>' + 
                    '</div>';
            }
            return `<div class="btn-group"></div>`;
        }
        return data;
    };
</script>

<div class="ibox">
    <div class="ibox-content">
        <div class="row">
            <div class="col-md-12 no-margins no-padding">
                <div id="tabla_orden_trabajos">
                    <?php
                        $options = [
                                'ajax' => [ // current controller, action, params
                                        'url' => $this->Url->build(),
                                        'data' => [
                                            'establecimiento_id' => $this->DataTables->callback('ObtenerEstablecimientoId'),
                                            'desde' => $this->DataTables->callback('Desde'),
                                            'hasta' => $this->DataTables->callback('Hasta')
                                        ]
                                ],
                                'pageLength' => 20,
                                'iDisplayLength' => 20,
                                'autoWidth' => false,
                                'stateSave' => true,
                                'prefixSearch' => true,
                                'language' => [
                                    'emptyTable' => 'No hay datos en la tabla.',
                                    'processing' => 'Buscando ...',
                                    'search' => 'Buscar',
                                    'info' => 'Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros',
                                    'paginate' => [
                                        'first' => __d('data_tables', 'Primero'),
                                        'last' => __d('data_tables', 'Ultimo'),
                                        'next' => __d('data_tables', 'Siguiente'),
                                        'previous' => __d('data_tables', 'Anterior'),
                                    ],
                                    'infoEmpty' => __d('data_tables', 'No hay registros para mostrar'),
                                    'infoFiltered' => __d('data_tables', '(Se encontraron _MAX_ registros)'),
                                    'lengthMenu' => __d('data_tables', 'Show _MENU_ entries'),
                                    'zeroRecords' => __d('data_tables', 'No hay registros'),
                                    'aria' => [
                                        'sortAscending' => __d('data_tables', ': activar para ordenar en forma Ascendente'),
                                        'sortDescending' => __d('data_tables', ': activar para ordenar en forma Descendente'),
                                    ],                
                                ],
                                'dom' => '<"row"<"col-sm-6 no-padding"f><"col-sm-6 no-padding botones-tabla-top-right"B>><"row"<"col-sm-12 no-margin no-padding"tr>><"row"<"col-sm-6"i><"col-sm-6"p>>',
                                'buttons' => [
                                                [   'text' => '<i class="fa fa-plus"></i>',
                                                    'titleAttr' => 'Crear una reclasificación',
                                                    'className' => 'btn-monitoreo btn-icon-only btn-circle nueva-reclasificacion'
                                                ]
                                            /*  ,[
                                                    'text' => '<i class="fa fa-file-excel-o"></i>',
                                                    'titleAttr' => 'Exportar listados de OT',
                                                    'className' => 'btn-monitoreo btn-icon-only btn-circle exportar-ot'
                                                ]*/
                                            ],
                                'columns' => [
                                        [
                                                'title' => __('Fecha'),
                                                'data' => 'fecha',
                                                'render' => $this->DataTables->callback('datatable_fecha')
                                        ],[
                                                'title' => __('Nombre'),
                                                'data' => 'nombre'
                                        ],[
                                                'title' => __('Establecimiento'),
                                                'data' => 'establecimiento.nombre'
                                        ],[
                                                'title' => __('Observaciones'),
                                                'data' => 'observaciones',
                                                'className' => 'text-primary'
                                        ],[
                                                'title' => __('Reporte'),
                                                'data' => 'procesado',
                                                'className' => 'text-center',
                                                'render' => $this->DataTables->callback('datatable_procesado')
                                        ],[
                                                'title' => __('Creado por'),
                                                'data' => 'created',
                                                'className' => 'text-right',
                                                'render' => $this->DataTables->callback('datatable_fecha_creacion')
                                        ],[
                                                'title' => __(' '),
                                                'data' => 'id',
                                                'searchable' => false,
                                                'orderable' => false,
                                                'className' => 'cell-triple-action',
                                                'render' => $this->DataTables->callback('datatable_botones')
                                        ]
                                ],
                                'order' => [0, 'desc']
                        ];
                        echo $this->DataTables->table('ordenes-de-trabajo', $options, ['class' => 'table table-bordered table-hover table-striped sin-margen-superior']);
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Labor -->
<form id="oracleot">
    <div class="modal otmodal" id="OracleOt" tabindex="-1" role="dialog"  aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content animated fadeIn">
                <div class="modal-header text-left">
                    <h3 class="modal-title"><span id="titulo-modal"></span> <small class="pull-right" style="font-size: 45%;" id="sub-titulo-modal"></small></h3>
                    <div>
                        <h3>Seleccionar Organizacion<i class="fa fa-spin fa-refresh text-success pull-right" id="oracle-loader"></i></h3>
                    </div>                    
                </div>
                <div class="modal-body">
                    <fieldset>
                        <div class="col-md-12">
                            <div class="row col-md-12 m-l-none m-r-none">
                                <?= $this->Form->control('filtro_establecimientos', ['type' => 'select', 'class' => 'form-control oracle', 'options' => $lista_establecimientos, 'label' => 'Organizaciones', 'multiple' => 'multiple']) ?>
                                <?= $this->Form->control('filtro_proveedores', ['type' => 'select', 'class' => 'form-control oracle', 'options' => $lista_proveedores, 'label' => 'Proveedores', 'multiple' => 'multiple']) ?>
                            </div>
                            <br>
                            <div class="row col-md-12 m-l-none m-r-none">
                                <div class="row col-md-6 m-l-none">
                                    <?= $this->Form->control('filtro_desde', ['label' => false, 'class' => 'form-control oracle', 'label' => 'Desde']) ?>
                                </div>
                                <div class="row col-md-6 m-r-none">
                                    <?= $this->Form->control('filtro_hasta', ['label' => false, 'class' => 'form-control oracle', 'label' => 'Hasta']) ?>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="modal-footer">
                    <div class="row col-md-12 no-margins no-padding">
                        <div class="col-md-5 no-margins no-padding">
                            <button type="button" class="btn btn-default btn-block oracle" data-dismiss="modal">Cancelar</button>
                        </div>
                        <div class="col-md-2"></div>
                        <div class="col-md-5 no-margins no-padding">
                            <button type="button" class="btn btn-primary btn-block oracle" onclick="GenerarReporteExcel()">Generar Reporte</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Modal para edición de labores -->
<div class="modal otmodal" id="reclasificaciones" tabindex="-1" role="dialog"  aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated fadeIn"></div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $("#oracle-loader").addClass('hidden');
        
        $('#desde').mask('00/00/0000');
        $('#hasta').mask('00/00/0000');
        
        $("body").toggleClass("mini-navbar");
        SmoothlyMenu();

        $("#filtro-establecimientos").select2({
            theme: "bootstrap",
            width: '100%',
            dropdownParent: $('#OracleOt')
        });
        
        $("#filtro-proveedores").select2({
            theme: "bootstrap",
            width: '100%',
            dropdownParent: $('#OracleOt')
        });

        /**
         * Creamos una reclasificacion nueva
         */
        $('.nueva-reclasificacion').on('click', function() {
            $('#reclasificaciones .modal-content').load(`/orden-trabajos-reclasificaciones/add`, function() {
                $('#reclasificaciones').modal({show:true});
            });
        });
        
        /* Marcar para reprocesar una OT */
        $('#tabla_orden_trabajos').on('click', 'a.Reprocesar', function (e) {
            e.preventDefault();
            var id = e.currentTarget.getAttribute('data-id');
            fetch('/orden-trabajos/reprocesar/' + id )
                .then( res => res.json() )
                .then( data => {
                    if (data.status === 'success') {
                        $('#ordenes-de-trabajo').DataTable().draw(false);
                    }
                })
                .catch( function(err) {
                    console.log( err );
                });
        });
        
        $('#tabla_orden_trabajos').on('click', 'a.EliminarOT', function (e) {
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
                    console.log( err );
                });
        });

        $('.exportar-ot-ajustes').on('click', function() {

            /* Realizo el reporte con Ajax */
            $("#table-loader").removeClass('hidden');
            $.ajax({
                type:"POST", 
                async:true,
                url:"/orden-trabajos/generarexcelotajustes",    /* Pagina que procesa la peticion   */
                success:function (data){
                    if (data['status'] == 'success'){
                        /* Abro el archivo excel generado */
                        $(location).attr('href','/dataload/' + data['archivo']);
                    } else {
                        alert(data['message'] + ' - ' + data['data']);
                    }
                    $("#table-loader").addClass('hidden');
                },
                error: function (data) {
                    console.log(data);

                }
            });                
        }); 

        $('.exportar-ot').on('click', function() {
            /* Muestro el dataload */
            $('#OracleOt').modal('show');
        }); 
    });        

    function GenerarReporteExcel (){
        let Desde = '';
        let Hasta = '';
        
        $("#oracle-loader").removeClass('hidden');
        
        // Reviso la fecha de inicio
        var fecha = $('#filtro-desde').val();
        if (fecha) {
            if (fecha.length !== 10) {
                toastr.error("El formato correcto es dd/mm/yyyy.", "Error de Formato");
                $("#oracle-loader").addClass('hidden');
                return;
            }
        }
        if (moment(fecha, 'DD/MM/YYYY').isValid()) {
            Desde = moment(fecha, 'DD/MM/YYYY').format('YYYY-MM-DD hh:mm:ss');
        }
        
        // Reviso la fecha de final
        var fecha = $('#filtro-hasta').val();
        if (fecha) {
            if (fecha.length !== 10) {
                toastr.error("El formato correcto es dd/mm/yyyy.", "Error de Formato");
                $("#oracle-loader").addClass('hidden');
                return;
            }
        }
        if (moment(fecha, 'DD/MM/YYYY').isValid()) {
            Hasta = moment(fecha, 'DD/MM/YYYY').format('YYYY-MM-DD');
        }
        
        /* Comienzo el reporte */
        $("#table-loader").removeClass('hidden');
        $(".oracle").addClass('disabled');
        $(".oracle").prop("disabled", true);
        
        let dataForm = new FormData();
        dataForm.append('establecimientos', $('#filtro-establecimientos').val() );
        dataForm.append('proveedores', $('#filtro-proveedores').val() );
        dataForm.append('desde', Desde );
        dataForm.append('hasta', Hasta );
        
        fetch(`/orden-trabajos/generarexcelot`, {
                method: 'POST',
                body: dataForm
            }).then( res => res.json() )
            .then( data => {
                if (data['status'] === 'success'){
                    /* Abro el archivo excel generado */
                    $(location).attr('href','/dataload/' + data['archivo']);
                } else {
                    alert(data['message'] + ' - ' + data['data']);
                }
                $("#oracle-loader").addClass('hidden');
                $(".oracle").removeClass('disabled');
                $(".oracle").prop("disabled", false);
                $('#OracleOt').modal('hide');
            })
            .catch( function(err) {
                toastr.error(err);
                $("#oracle-loader").addClass('hidden');
                $(".oracle").removeClass('disabled');
                $(".oracle").prop("disabled", false);
            });
    };
</script>