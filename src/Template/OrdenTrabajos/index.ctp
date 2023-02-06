<?php
/**
 * Ordenes de Trabajo
 *
 * Index de Ordenes de Trabajo, con datatables Server Side y filtros
 *
 * View OrdenTrabajos
 *
 * Desarrollado para Adecoagro SA.
 * 
 * @category CakePHP3
 *
 * @package Ordenes
 *
 * @author Javier Alegre <jalegre@adecoagro.com>
 * @copyright Copyright 2021, Adecoagro
 * @version 1.0.0 creado el 21/12/2021
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
        <h3>Ordenes de Trabajo &nbsp;&nbsp;<i class="fa fa-spin fa-refresh text-success" id="table-loader"></i></h3>
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
                        <?= $this->Form->control('proveedore_id',['type'=> 'select', 'label' => false, 'options' => [], 'class' => 'form-control select2', 'required']) ?>
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
    var EstablecimientoId = localStorage.getItem('OrdenTrabajosEstablecimientoId') ? localStorage.getItem('OrdenTrabajosEstablecimientoId') : '';
    
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
        localStorage.setItem('OrdenTrabajosEstablecimientoId',  $('#establecimiento-id').val());

    });
    
    /* 
     * Proveedores, server side
     * 
     * Si hay un proveedor pre seleccionado, hay que agregar ese item al select
     * 
     */
    var Proveedor = localStorage.getItem('OrdenTrabajosProveedor') ? localStorage.getItem('OrdenTrabajosProveedor') : '';
    $("#proveedore-id").select2({
        theme: "bootstrap",
        placeholder: "Filtrar por proveedor ...",
        width: '100%',
        allowClear: true,
        minimumInputLength: 3,
        ajax: {
            url: "/proveedores/search",
            dataType: 'json',
            data: function (params) {
                var query = {
                    q: params.term
                };
                return query;
            },
            processResults: function (data, params) {
                return { results: data.proveedores };
            }
        }
    }).val('').trigger('change').on('change.select2', function (e) {
        e.stopPropagation();
        
        /* Obtengo el array de establecimientos */
         $('#ordenes-de-trabajo').DataTable().ajax.reload();
         
        if ($('#proveedore-id').val() == null) {
            localStorage.setItem('OrdenTrabajosProveedor', '');
        } else {
            var data = {
                id: $('#proveedore-id').val(),   // ID
                text: $('#proveedore-id').text() // Nombre
            };
            /* Guardo el nuevo valor por defecto */
            localStorage.setItem('OrdenTrabajosProveedor', JSON.stringify(data));
        }
    });
    
    if (Proveedor.length > 0) {
        let ProveedorJson = JSON.parse(Proveedor);
        var newOption = new Option(ProveedorJson.text, ProveedorJson.id, false, false);
        $("#proveedore-id").append(newOption).trigger('change');
    }

    var CampaniaId = localStorage.getItem('OrdenTrabajosCampania') ? localStorage.getItem('OrdenTrabajosCampania') : '';
    $("#campania-id").select2({
        theme: "bootstrap",
        placeholder: "Filtrar por campaña ...",
        width: '100%',
        allowClear: true
       // data: campanias
    }).val(CampaniaId).trigger('change').on('change.select2', function (e) {
        e.stopPropagation();
        
        /* Obtengo el array de establecimientos */
        $('#ordenes-de-trabajo').DataTable().ajax.reload();
         
        localStorage.setItem('OrdenTrabajosCampania',  $('#campania-id').val());
    });
    
    /* Leo el estado seleccionado por defecto */
    var EstadoId = localStorage.getItem('OrdenTrabajosEstadoId') ? localStorage.getItem('OrdenTrabajosEstadoId') : '';
    $('#estado').val(EstadoId);
    
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
        let ActivoFiltroFechas = localStorage.getItem('OrdenTrabajosFiltroFechas') ? localStorage.getItem('OrdenTrabajosFiltroFechas') : 0;
        
        if (ActivoFiltroFechas == 0) {
            $("#chkRangoFechas").prop("checked", false);
            document.getElementById('rango-fechas').disabled = true;
        } else {
            $("#chkRangoFechas").prop("checked", true);
            document.getElementById('rango-fechas').disabled = false;
        }
        
        chkFechas.addEventListener('click', function() {
            if (chkFechas.checked) {
                localStorage.setItem('OrdenTrabajosFiltroFechas', 1);
                document.getElementById('rango-fechas').disabled = false;
            } else {
                localStorage.setItem('OrdenTrabajosFiltroFechas', 0);
                document.getElementById('rango-fechas').disabled = true;
            }
            $('#ordenes-de-trabajo').DataTable().ajax.reload();
        });
    };
    IniciarControlFechas();

    /* Proceso los estados de las OT */
    datatable_estados = function (data, type, full, meta)
    {
        if (type === 'display') {
            if (data) { 
                return `<div class="sphere" style="background-color:${data.color}" title="${data.nombre}"></div>`;
            }
            return `<div class="sphere" style="background-color:#cc0000" title="Inactivo"></div>`;            
        }
        return data ? data.id : '';
    };
    /* Proceso local los proveedores y labores */
    datatable_proveedores = function (data, type, full, meta)
    {
        if (type === 'display') {
            let distribuciones = full.orden_trabajos_distribuciones;
            const result = distribuciones.reduce((acc, item) => {
                let nombre_labor = item.proyectos_labore ? item.proyectos_labore.nombre : null;
                if (!acc.includes(nombre_labor) && nombre_labor !== null) {
                    acc.push(nombre_labor);
                }
                return acc;
            }, []);

            var cantidad_labores = full.orden_trabajos_distribuciones.length;

            if ( cantidad_labores === 0 ) {
                return full.proveedore ? full.proveedore.nombre + '' + '<br><span class="small">Sin labores</span>' : '';
            }
            return full.proveedore ? full.proveedore.nombre + '<span class="badge badge-success pull-right">' + (cantidad_labores > 1 ? cantidad_labores : '') + '</span>' + '<br><span class="small text-uppercase">' + result.join(", ") + '</span>' : '';
        }
        return data ? data.nombre : '';
    };
    /* 
    * Muestro el establecimiento y lotes de la OT
    */
    datatable_establecimientos = function (data, type, full, meta)
    {
        if (type === 'display') {
            let distribuciones = full.orden_trabajos_distribuciones;
            const result = distribuciones.reduce((acc, item) => {
                let nombre_lote = item.lote ? item.lote.nombre : null;
                if (!acc.includes(nombre_lote) && nombre_lote !== null) {
                    acc.push(nombre_lote);
                }
                return acc;
            }, []);

            /* Sumo las superficies ordenadas */
            var superficie = full.orden_trabajos_distribuciones.map(item => item.superficie).reduce((prev, curr) => prev + curr, 0);
            
            return data ? data.nombre + (superficie ? `<span class="small pull-right">(${superficie.toFixed(2)} has)</span>` : '') + '<br>' + '<span class="small">' + result.join(", ") + '</span>' : '';

        }
        return data ? data.nombre : '';
    };
    
    /**
    * Dibuja los botones que precisamos en el listado
    * 
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
                        '<a target="_blank" href="/orden-trabajos/view/' + data + '" type="button" title="Ver" class="btn btn-xs btn-white btn-orden-trabajo"><i class="fa fa-eye"></i></a>' +
                        '<a target="_blank" href="/orden-trabajos/edit/' + data + '" type="button" title="Editar" class="btn btn-xs btn-white btn-orden-trabajo"><i class="fa fa-pencil"></i></a>' +
                        '<a href="/orden-trabajos/view/' + data + '.pdf" type="button" title="Generar PDF" class="btn btn-xs btn-white btn-orden-trabajo"><i class="fa fa-print"></i></a>' +
                        '<a href="#" data-id="' + data + '" title="Anular OT" type="button" class="btn btn-xs btn-white btn-orden-trabajo EliminarOT"><i class="fa fa-trash"></i></a>' +
                    '</div>';		
        }
        return data;
    };
</script>

<div class="ibox">
    <div class="ibox-content">
        <div class="row">
            <!-- Columna Izquierda -->
            <div class="col-md-2 no-margins no-padding">
                <div class="mailbox-content m-l-none m-t-none">
                    <div class="file-manager">
                        <h5>Carpetas</h5>
                        <div class="space-25"></div>
                        <ul class="folder-list m-b-md" style="padding: 0">
                            <?php
                                echo '<li><a href="#" class="borrador"> <i class="fa fa-edit"></i> Borrador <span class="label label-warning pull-right" id="borrador"></span> </a></li>';
                                echo '<li><a href="#" class="abiertas"> <i class="fa fa-inbox"></i> Abiertas <span class="label label-warning-light pull-right" id="abiertas"></span> </a></li>';
                                echo '<li><a href="#" class="cerradas"> <i class="fa fa-check"></i> Cerradas <span class="label label-primary pull-right" id="cerradas"></span></a></li>';
                                echo '<li><a href="#" class="certificadas"> <i class="fa fa-certificate"></i> Certificadas <span class="label label-success pull-right" id="certificados"></span></a></li>';                                
                                echo '<li><a href="#" class="anuladas"> <i class="fa fa-trash-o"></i> Eliminadas <span class="label label-danger pull-right" id="anuladas"></span></a></li>';
                                echo '<li><a href="#" id="ver-todos"> <i class="fa fa-eye"></i> Ver todos</a></li>';
                            ?>
                        </ul>
                        <div class="clearfix"></div>
                    </div>                    
                </div>
            </div>
            <div class="col-md-10 no-margins no-padding">
                <div id="tabla_orden_trabajos">
                    <?php
                        $options = [
                                'ajax' => [ // current controller, action, params
                                        'url' => '/orden-trabajos/datatable',
                                        'data' => [
                                            'estado' => $this->DataTables->callback('ObtenerEstado'),
                                            'establecimiento_id' => $this->DataTables->callback('ObtenerEstablecimientoId'),
                                            'proveedore_id' => $this->DataTables->callback('ObtenerProveedorId'),
                                            'campania_id' => $this->DataTables->callback('ObtenerCampaniaId'),
                                            'desde' => $this->DataTables->callback('Desde'),
                                            'hasta' => $this->DataTables->callback('Hasta'),
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
                                                    'titleAttr' => 'Nueva Orden de Trabajo',
                                                    'className' => 'btn-monitoreo btn-icon-only btn-circle nueva-labor'
                                                ],[
                                                    'text' => '<i class="fa fa-file-excel-o"></i>',
                                                    'titleAttr' => 'Exportar listados de OT',
                                                    'className' => 'btn-monitoreo btn-icon-only btn-circle exportar-ot'
                                                ],[
                                                    'extend' => 'pdf',
                                                    'title' => 'ExampleFile',
                                                    'titleAtrr' => 'PDF',
                                                    'text' => '<i class="fa fa-file-pdf-o"></i>',
                                                    'className' => 'btn-monitoreo btn-icon-only btn-circle'
                                                ],[ 'text' => '<i class="fa fa-retweet"></i>',
                                                    'titleAttr' => 'Exportar listados para Ajustes',
                                                    'className' => 'btn-monitoreo btn-icon-only btn-circle exportar-ot-ajustes']
                                            ],
                                'columns' => [
                                        [
                                                'title' => __('OT'),
                                                'data' => 'id',
                                                'render' => $this->DataTables->callback('dt.render.ot')
                                        ],[
                                                'title' => __('Fecha'),
                                                'data' => 'fecha',
                                                'render' => $this->DataTables->callback('dt.render.fecha')
                                        ],[
                                                'title' => __('Establecimiento'),
                                                'data' => 'establecimiento',
                                                //'orderable' => false,
                                                'render' => $this->DataTables->callback('datatable_establecimientos')
                                        ],[
                                                'title' => __('Proveedor'),
                                                'data' => 'proveedore',
                                                //'orderable' => false,
                                                'className' => 'text-primary',
                                                'render' => $this->DataTables->callback('datatable_proveedores')
                                        ],[
                                                'title' => __('Creado por'),
                                                'data' => 'created',
                                                'className' => 'text-right',
                                                'render' => $this->DataTables->callback('dt.render.fechaCreacion')
                                        ],[
                                                'title' => __('Estado'),
                                                'data' => 'orden_trabajos_estado',
                                                'searchable' => true,
                                                'orderable' => true,
                                                'className' => 'text-center cell-double-action',
                                                'render' => $this->DataTables->callback('datatable_estados')
                                        ],[
                                                'title' => __(' '),
                                                'data' => 'id',
                                                'searchable' => false,
                                                'orderable' => false,
                                                'className' => 'cell-triple-action',
                                                'render' => $this->DataTables->callback('datatable_botones')
                                        ],[
                                                'title' => __(' '),
                                                'data' => 'proveedore_id',
                                                'visible' => false,
                                                'searchable' => true
                                        ]
                                ],
                                'order' => [0, 'desc'], // order by OT
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
<script>
    $(document).ready(function() {
        $("#oracle-loader").addClass('hidden');
        
        $('#desde').mask('00/00/0000');
        $('#hasta').mask('00/00/0000');
        
        $("body").toggleClass("mini-navbar");
        SmoothlyMenu();
        initEditor();

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

        $('.nueva-labor').on('click', function() {
            $(location).attr('href','/OrdenTrabajos/add');
        });
        
        /* Marcar para reprocesar una OT */
        $('#tabla_orden_trabajos').on('click', 'a.Reprocesar', function (e) {
            e.preventDefault();
            var id = e.currentTarget.getAttribute('data-id');
            fetch('/orden-trabajos/reprocesar/' + id )
                .then( res => res.json() )
                .then( data => {
                    if (data.status == 'success') {
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
                    //alert('error' + data.statusText);
                    //resultado = JSON.parse(data);
                }
            });                
        }); 

        /* Ejecuto el reporte de excel */
//        $('.exportar-ot').on('click', function() {
//            /* Muestro el dataload */
//            GenerarReporteExcel();
//        });

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

    /* Mostramos el modal para bajar reportes */
//    function GenerarReporteExcel (){
//        let Desde = '';
//        let Hasta = '';
//        
//        $("#oracle-loader").removeClass('hidden');
//        
//        // Reviso la fecha de inicio
//        var fecha = $('#desde').val();
//        if (fecha) {
//            if (fecha.length !== 10) {
//                toastr.error("El formato correcto es dd/mm/yyyy.", "Error de Formato");
//                $("#oracle-loader").addClass('hidden');
//                return;
//            }
//        }
//        if (moment(fecha, 'DD/MM/YYYY').isValid()) {
//            Desde = moment(fecha, 'DD/MM/YYYY').format('YYYY-MM-DD hh:mm:ss');
//        }
//        
//        // Reviso la fecha de final
//        var fecha = $('#hasta').val();
//        if (fecha) {
//            if (fecha.length !== 10) {
//                toastr.error("El formato correcto es dd/mm/yyyy.", "Error de Formato");
//                $("#oracle-loader").addClass('hidden');
//                return;
//            }
//        }
//        if (moment(fecha, 'DD/MM/YYYY').isValid()) {
//            Hasta = moment(fecha, 'DD/MM/YYYY').format('YYYY-MM-DD');
//        }
//        
//        /* Comienzo el reporte */
//        $("#table-loader").removeClass('hidden');
//        $(".oracle").addClass('disabled');
//        $(".oracle").prop("disabled", true);
//        
//        var Establecimiento_id = $('#establecimiento-id').val() ? $('#establecimiento-id').val() : '';
//        var Proveedore_id =      $('#proveedore-id').val()      ? $('#proveedore-id').val()      : '';
//        
//        let dataForm = new FormData();
//        dataForm.append('establecimiento_id', Establecimiento_id);
//        dataForm.append('proveedore_id', Proveedore_id);
//        dataForm.append('desde', Desde );
//        dataForm.append('hasta', Hasta );
//        
//        fetch(`/orden-trabajos/generarexcelot`, {
//                method: 'POST',
//                body: dataForm
//            }).then( res => res.json() )
//            .then( data => {
//                if (data['status'] === 'success'){
//                    /* Abro el archivo excel generado */
//                    $(location).attr('href','/dataload/' + data['archivo']);
//                } else {
//                    alert(data['message'] + ' - ' + data['data']);
//                }
//                $("#oracle-loader").addClass('hidden');
//                $(".oracle").removeClass('disabled');
//                $(".oracle").prop("disabled", false);
//                $('#OracleOt').modal('hide');
//            })
//            .catch( function(err) {
//                toastr.error(err);
//                $("#oracle-loader").addClass('hidden');
//                $(".oracle").removeClass('disabled');
//                $(".oracle").prop("disabled", false);
//            });
//    };

    /* Listado de Ordenes de Trabajos */
    var ListarOrdenesTrabajos = function() {
        var initData = function() {
           /* Esta rutina carga todos los datos al array, para poder usarlo, deberiamos
            * Generar una llamada Ajax con el array, o pasarlo desde el controller en el 
            * formato que deseamos.
            */ 
            recargarTotales();
        };
        
        /* Recupero los totales */
        const recargarTotales = () =>  {
            let url = new URL(`${window.location.protocol}//${window.location.host}/orden-trabajos/totales-ajax`);
            url.search = new URLSearchParams('proveedores', $("#proveedores").val() );
            fetch( url )
                .then( res => res.json() )
                .then( data => {
                    $('#borrador').text(data.borrador);
                    $('#abiertas').text(data.abiertas);
                    $('#certificados').text(data.certificados);
//                    $('#certificadosDL').text(data.certificadosConDL);                    
                    $('#anuladas').text(data.anuladas);
                    $('#cerradas').text(data.cerradas);
                    
                    $("#table-loader").addClass('hidden');
                });
        };
        
        /* Filtro solo las Borrador */
        $('.borrador').on('click', function() {
            AplicarFiltros (1);
        });
        /* Filtro solo las Abiertas */
        $('.abiertas').on('click', function() {
            AplicarFiltros (2);
        });
        $('.cerradas').on('click', function() {
            AplicarFiltros (3);
        });
        $('.certificadas').on('click', function() {
            AplicarFiltros (4);
        });
        $('.anuladas').on('click', function() {
            AplicarFiltros (5);
        });
        $('#ver-todos').on('click', function (e) {
            AplicarFiltros ('');
        });
        
        var AplicarFiltros = function(Estado){ 
            $("#table-loader").removeClass('hidden');
            
            $('#estado').val(Estado);
            localStorage.setItem('OrdenTrabajosEstadoId', $('#estado').val());
    
            $('#ordenes-de-trabajo').DataTable().ajax.reload();
            
            
            $("#table-loader").addClass('hidden');
        };
        
        return {
            init: function() {
                initData(); /* Cargo los datos */
           }
        };
    }();

    /* Iniciamos el listado de Ordenes de Trabajos */
    function initEditor() {
        ListarOrdenesTrabajos.init();
    }
</script>