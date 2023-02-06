<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface[]|\Cake\Collection\CollectionInterface $ordenTrabajosMapeos
 */
?>
<?= $this->Html->css(['Ordenes.style', 'plugins/daterangepicker/daterangepicker-3.14.1']) ?>
<?= $this->Html->script(['plugins/daterangepicker/daterangepicker-3.14.1.min']) ?>

<?= $this->Form->control('establecimientos', ['type' => 'hidden', 'value' => json_encode($establecimientos)]) ?>
<?= $this->Form->control('sectores', ['type' => 'hidden', 'value' => json_encode($sectores)]) ?>
<?= $this->Form->control('proveedores', ['type' => 'hidden', 'value' => json_encode($proveedores)]) ?>
<?= $this->Form->control('cultivos', ['type' => 'hidden', 'value' => json_encode($cultivos)]) ?>
<?= $this->Form->control('campanias', ['type' => 'hidden', 'value' => json_encode($campanias)]) ?>
<?= $this->Form->control('desde', ['type' => 'hidden']) ?>
<?= $this->Form->control('hasta', ['type' => 'hidden']) ?>

<script>
    /**
     * Obtener Campaña Id
     * 
     * Devuelvo el ID de campania actual para utilizarlo
     * en los datatables server side
     * 
     * @returns {integer} id del Proyecto
     */
    const ObtenerCampaniaId = () => $('#campania-id').val() ? $('#campania-id').val() : '';

    /**
     * ObtenerEstablecimientoId
     * Devuelvo el ID del establecimiento seleccionado en el select para utilizarlo
     * en los datatables server side
     * 
     * @returns {integer} id del Establecimiento
     */
    const ObtenerEstablecimientoId = () => $('#establecimiento-id').val() ? $('#establecimiento-id').val() : '';

    /**
     * ObtenerSectoreId
     * Devuelvo el ID del sector seleccionado en el select para utilizarlo
     * en los datatables server side
     * 
     * @returns {integer} id del Establecimiento
     */
    const ObtenerSectoreId = () => $('#sectore-id').val() ? $('#sectore-id').val() : '';

    /**
     * Obtener Cultivo Id
     * Devuelvo el ID del cultivo actual para utilizarlo en los datatables server side
     * 
     * @returns {integer} id del Proyecto
     */
    const ObtenerCultivoId = () => $('#cultivo-id').val() ? $('#cultivo-id').val() : '';

    /**
     * ObtenerProveedorId
     * Devuelvo el ID del proveedor para utilizarlo en los datatables server side
     * 
     * @returns {integer} id del Establecimiento
     */
    const ObtenerProveedorId = () => $('#proveedore-id').val() ? $('#proveedore-id').val() : '';

    /**
     * Desde
     * Devuelvo la fecha desde para datatable
     * @return {date} fecha desde seleccionada
     */
    const Desde = () => {
        var chkFechas = document.getElementById('chkRangoFechas');
        if (chkFechas.checked) {
            return $('#desde').val();
        }    
        return '';
    }    

    /**
     * Hasta
     * Devuelvo la fecha Hasta para datatable
     * @return {date} fecha hasta seleccionada
     */
    const Hasta = () => {
        var chkFechas = document.getElementById('chkRangoFechas');
        if (chkFechas.checked) {
            return $('#hasta').val();
        }    
        return '';
    }  
</script>
<div class="row border-bottom white-bg page-heading">
    <div class="col-md-7 m-t-xs">
        <h3>Mapeo de Cosecha 
    </div>
    <div class="col-md-5 col-sm-6 col-xs-4 m-t-xs">
        <div class="btn-group pull-right dt-buttons no-margins no-padding">
            <?= $this->Html->link('<i class="fa fa-plus"></i>', 
                    ['controller' => 'OrdenTrabajosMapeos', 'action' => 'add_multiple'], 
                    ['type' => 'button', 'title' => 'Agregar Multiples', 'class'=>'btn btn-sm btn-default', 'escape' => false]) ?>
        </div>        
    </div>
</div>
<!-- Filtros -->
<div class="ibox float-e-margins m-b-xs">
    <div class="row">
        <div class="panel panel-default m-b-none">
            <div class="panel-body no-margins no-padding">
                <div class="col-md-12 m-b-xs m-t-xs no-padding">
                    <div class="col-md-4">
                        <?= $this->Form->control('campania_id',['type'=> 'select', 'label' => false, 'options' => [], 'class' => 'form-control select2', 'required']) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $this->Form->control('establecimiento_id',['type'=> 'select', 'label' => false, 'options' => [], 'class' => 'form-control select2', 'required']) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $this->Form->control('sectore_id',['type'=> 'select', 'label' => false, 'options' => [], 'class' => 'form-control select2', 'required']) ?>
                    </div>
                </div>
                <div class="col-md-12 m-b-xs m-t-xs no-padding">
                    <div class="col-md-4">
                        <?= $this->Form->control('cultivo_id',['type'=> 'select', 'label' => false, 'options' => [], 'class' => 'form-control select2', 'required']) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $this->Form->control('proveedore_id',['type'=> 'select', 'label' => false, 'options' => [], 'class' => 'form-control select2', 'required']) ?>
                    </div>
                    <!-- control fecha -->
                    <div class="col-md-4">
                        <div class="ot-form-group">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <input type="checkbox" id="chkRangoFechas">
                                </span>
                                <input  type="text" id="rango-fechas" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- fin filtros -->
<script>
    /**
     * Asigno los valores por defecto que tienen los establecimientos y campañas
     * Lo ubico en este lugar para que tome los cambios ANTES que los datatables lo utilicen
     */
    
    /* 
     * Campanias select2
     */
    var data = JSON.parse($('#campanias').val());
    var CampaniaId = localStorage.getItem('OrdenTrabajosMapeosCampaniaId') ? localStorage.getItem('OrdenTrabajosMapeosCampaniaId') : '';
    $("#campania-id").select2({
        theme: "bootstrap",
        placeholder: 'Seleccionar un Campaña...',
        width: '100%',
        allowClear: true,
        data: data
    }).val(CampaniaId).trigger('change').on('change.select2', function (e) {
        e.stopPropagation();
        /* Recargamos las tablas */
        $('#mapeos').DataTable().ajax.reload();
        /* Guardo el nuevo valor por defecto */
        localStorage.setItem('OrdenTrabajosMapeosCampaniaId',  $('#campania-id').val());
    });


     /* 
     * Establecimientos select2
     */
    var data = JSON.parse($('#establecimientos').val());
    var EstablecimientoId = localStorage.getItem('OrdenTrabajosMapeosEstablecimientoId') ? localStorage.getItem('OrdenTrabajosMapeosEstablecimientoId') : '';
    $("#establecimiento-id").select2({
        theme: "bootstrap",
        placeholder: 'Seleccionar un Establecimiento...',
        width: '100%',
        allowClear: true,
        data: data
    }).val(EstablecimientoId).trigger('change').on('change.select2', function (e) {
        e.stopPropagation();
        /* Recargamos las tablas */
        $('#mapeos').DataTable().ajax.reload();
        /* Guardo el nuevo valor por defecto */
        localStorage.setItem('OrdenTrabajosMapeosEstablecimientoId',  $('#establecimiento-id').val());
    });
    

    /**
     * Sectores select2
     */
    var data_sectores = JSON.parse($('#sectores').val());
    $("#sectore-id").select2({
        theme: "bootstrap",
        width: '100%',
        allowClear: true,
        data: data_sectores,
        placeholder: "Seleccionar un Sector...",
        matcher: function (params, data) {
                // If there are no search terms, return all of the data
                if ($.trim(params.term) === '')  return data;

                // `params.term` should be the term that is used for searching
                // `data.text` is the text that is displayed for the data object
                if (data.nombre.toUpperCase().indexOf(params.term.toUpperCase()) > -1) {
                  var modifiedData = $.extend({}, data, true);
                  modifiedData.text += ' (matched)';

                  // You can return modified objects from here
                  // This includes matching the `children` how you want in nested data sets
                  return modifiedData;
                }
                return null;

        },
        templateResult: function ( data ) {
                    if (data.loading) {
                        return data.text;
                    }
                    var Establecimiento = $('#establecimiento-id').val();
                    
                    if (Establecimiento) {
                        if (data.establecimiento_id == Establecimiento) {
                            let $container = $(`<div>${data.nombre}</div>`);
                            return $container;
                        }
                        return;
                    }
                    let $container = $(`<div>${data.nombre}</div>`);
                    return $container;
                },
        templateSelection: function ( data ) {
            if (data.text) {
                return data.text;
            }
            let $container = $(`<div>${data.nombre}</div>`);
            return $container;
        }
    }).val('').trigger('change').on('change.select2', function (e) {
        e.stopPropagation();
        /* Recargamos las tablas */
        $('#mapeos').DataTable().ajax.reload();
    });


    /* 
     * Proveedores select2
     */
    var data = JSON.parse($('#proveedores').val());
    var ProveedoresId = localStorage.getItem('OrdenTrabajosMapeosProveedoresId') ? localStorage.getItem('OrdenTrabajosMapeosProveedoresId') : '';
    $("#proveedore-id").select2({
        theme: "bootstrap",
        placeholder: 'Seleccionar un Proveedor...',
        width: '100%',
        allowClear: true,
        data: data
    }).val(ProveedoresId).trigger('change').on('change.select2', function (e) {
        e.stopPropagation();
        /* Recargamos las tablas */
        $('#mapeos').DataTable().ajax.reload();
        /* Guardo el nuevo valor por defecto */
        localStorage.setItem('OrdenTrabajosMapeosProveedoresId',  $('#proveedore-id').val());
    });


    /* 
     * Cultivos select2
     */
    var data = JSON.parse($('#cultivos').val());
    var CultivosId = localStorage.getItem('OrdenTrabajosMapeosCultivosId') ? localStorage.getItem('OrdenTrabajosMapeosCultivosId') : '';
    $("#cultivo-id").select2({
        theme: "bootstrap",
        placeholder: 'Seleccionar un Cultivo...',
        width: '100%',
        allowClear: true,
        data: data
    }).val(CultivosId).trigger('change').on('change.select2', function (e) {
        e.stopPropagation();
        /* Recargamos las tablas */
        $('#mapeos').DataTable().ajax.reload();
        /* Guardo el nuevo valor por defecto */
        localStorage.setItem('OrdenTrabajosMapeosCultivosId',  $('#cultivo-id').val());
    }); 

   
    /* 
    * Muestro el Establecimiento, Sector, Lotes y Superficie de la OT
    */
    const dt_establecimiento_sector = function (data, type, full, meta) {
        if (type === 'display') {
            
            var organizacion =  full.orden_trabajo.establecimiento.nombre;
            var sector = full.lote.sectore.nombre;
            var lote = full.lote.nombre;
            var superficie = full.superficie;
            
            return organizacion + `<span class="small pull-right">${lote}</span> <br>` + 
                                  `<span class="small">${sector}</span>` +
                                  '<span class="small pull-right">('+ superficie.toFixed(2) +') has </span>';
        }
        return '';
    };

    /* 
    * Muestro el id de la OT y id de ot distribuciones
    */
    const dt_render_ot = function (data, type, full, meta) {
        if (type === 'display') {
            return data +  ` <br> <span class="small pull-right"> (${full.id})</span>`;
        }
        return '';
    }

    /* 
    * Muestro la campaña y el cultivo 
    */
    const dt_campania_cultivo = function (data, type, full, meta) {
        if (type === 'display') {
            var cultivo = full.proyecto.cultivo;
            return data + `<br><span class="small pull-right">${cultivo}</span>`;
        }
        return '';
    }
    
    /* 
    * Muestro el proveedor y la labor 
    */
    const dt_proveedor_labor = function (data, type, full, meta) {
        if (type === 'display') {
            var labor = full.proyectos_labore.nombre;
            if (data) {
                return data + `<br><span class="small pull-right">${labor}</span>`;
            }
            return 'Sin Proveedor' + `<br><span class="small pull-right">${labor}</span>`;
        }
        return '';
    }
    
    /* 
    * Muestro la campania si exite
    */
    const dt_campanias = function (data, type, full, meta) {
        if (type === 'display') {
            return data ? data : '';
        }
        return '';
    }

    /* 
    * Muestro tipo de campaña
    */
    const dt_tipo = function (data, type, full, meta) {
        if (type === 'display') {
            var mapeo = full.orden_trabajos_mapeo;
            if (mapeo) {
                return mapeo.mapeos_campanias_tipo.nombre;
            }
            return '';    
        }
        return '';
    }

    /* 
    * Muestro calidad de  mapeo
    */
    const dt_calidad = function (data, type, full, meta) {
        if (type === 'display') {
            var mapeo = full.orden_trabajos_mapeo;
            if (mapeo) {
                return mapeo.mapeos_calidade.nombre;
            }
            return '';    
        }
        return '';
    }

    /* 
    * Muestro problemas del mapeo
    */
    const dt_problema = function (data, type, full, meta) {
        if (type === 'display') {
            var mapeo = full.orden_trabajos_mapeo;
            if (mapeo) {
                return mapeo.mapeos_problema.nombre;
            }
            return '';    
        }
        return '';
    }

    /* 
    * Muestro la SI o vacio segun data SMS
    */
    const dt_sms = function (data, type, full, meta) {
        if (type === 'display') {
            var mapeo = full.orden_trabajos_mapeo;
            if (mapeo) {
                if (mapeo.sms == 1) {
                    return 'Si';
                }
                return '';
            }
            return '';  
        }
        return '';
    }

    /* 
    * Muestro la SI o vacio segun data
    */
    const dt_pdf = function (data, type, full, meta) {
        if (type === 'display') {
            var mapeo = full.orden_trabajos_mapeo;
            if (mapeo) {
                if (mapeo.pdf == 1) {
                    return 'Si';
                }
                return '';
            }
            return '';  
        }
        return '';
    }

    /* 
     * Muestro comentario del mapeo
     */
    const dt_comentario = function (data, type, full, meta) {
        if (type === 'display') {
            var mapeo = full.orden_trabajos_mapeo;
            if (mapeo) {
                return mapeo.comentario;
            }
            return '';    
        }
        return '';
    }


    /**
     * Dibuja los botones 
     * @param data The data for the cell (based on columns.data)
     * @param type 'filter', 'display', 'type' or 'sort'
     * @param full The full data source for the row
     * @param meta Object containing additional information about the cell
     * @returns Manipulated cell data
     */
    const datatable_botones = (data, type, full, meta) =>
    {
        if (type === 'display') {
            var mapeo = full.orden_trabajos_mapeo;
            if (mapeo) { 
                return '<div class="btn-group">' +
                            '<a href="#" data-id="' + mapeo.id + '" type="button" title="Ver" class="btn btn-xs btn-white Ver"><i class="fa fa-eye"></i></a>' +            
                            '<a href="#" data-id="' + mapeo.id + '" type="button" title="Editar" class="btn btn-xs btn-white Editar"><i class="fa fa-pencil"></i></a>' +                    
                        '</div>';
            } else {
                return '<div class="btn-group">' +
                            '<a href="#" data-id="' + full.id + '" type="button" title="Agregar Mapeo" class="btn btn-xs btn-white Agregar"><i class="fa fa-plus"></i></a>' +                                
                        '</div>';
            }		
        }
        return '';
    };

     /**
     * Formatear la Fecha 
     * @param data The data for the cell (based on columns.data)
     * @param type 'filter', 'display', 'type' or 'sort'
     * @param full The full data source for the row
     * @param meta Object containing additional information about the cell
     * @returns Manipulated cell data
     */
    const obtenerFecha = (data, type, full, meta) =>  {
        if (type === 'display') {
            if (data){
                return moment.utc(data).startOf('day').format('DD/MM/YYYY');
            } 
            return "";
        }
        return data;
    }

    /**
     * Inserto las imagenes de los usuarios
     * @param data The data for the cell (based on columns.data)
     * @param type 'filter', 'display', 'type' or 'sort'
     * @param full The full data source for the row
     * @param meta Object containing additional information about the cell
     * @returns Manipulated cell data 
     */
    const ObtenerImagenesUsers = (data, type, full, meta) => {
        
        if (type === 'display') {
            var mapeo = full.orden_trabajos_mapeo;
            if (mapeo) { 
                var image = full.orden_trabajos_mapeo.user.img_base64;
                var nombre = full.orden_trabajos_mapeo.user.nombre;
                if (image) { 
                    return '<img title="' + nombre + '" class="img-circle m-r-xs" width="20" src="data:image/jpg;base64,' + image + '">';
                } 
                return '<img title="' + nombre + '" class="img-circle m-r-xs" width="20" src="/img/users/user.jpg">';
            }
            return '';
        }
        return '';    
    }

    const ObtenerImagenesUsersCertificador = (data, type, full, meta) => {
        
        if (type === 'display') {

            var image = full.orden_trabajo.user.img_base64;
    
            if (image) { 
                return '<img title="' + data + '" class="img-circle m-r-xs" width="20" src="data:image/jpg;base64,' + image + '">';
            } else {
                return '<img title="' + data + '" class="img-circle m-r-xs" width="20" src="/img/users/user.jpg">';
            }
        }
        return '';    
    }

    /** 
     * Configuro las fechas del DateRangePiker
     */
    $(function() {
        var start = moment().subtract(29, 'days');
        var end = moment();

        function cb(start, end) {
            $('#rango-fechas span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
            
            $('#desde').val(start.format('YYYY-MM-DD'));
            $('#hasta').val(end.format('YYYY-MM-DD'));

            $('#mapeos').DataTable().ajax.reload();

        }

        $('#rango-fechas').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
               'Hoy': [moment(), moment()],
               'Ultima semana': [moment().subtract(6, 'days'), moment()],
               'Ultimos 30 días': [moment().subtract(29, 'days'), moment()],
               'Este mes': [moment().startOf('month'), moment().endOf('month')],
               'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
               'Ultimos 3 Meses': [moment().subtract(2, 'month').startOf('month'), moment().endOf('month')]
            }
        }, cb);
        
        $('#rango-fechas').on('apply.daterangepicker', function(ev, picker) {
            $('#desde').val(picker.startDate.format('YYYY-MM-DD'));
            $('#hasta').val(picker.endDate.format('YYYY-MM-DD'));

            $('#mapeos').DataTable().ajax.reload();

        });
        cb(start, end);
    });

    /**
     *  Inicio del filtro fechas 
     */  
    const IniciarControlFechas = () => {
            $('#chkRangoFechas').prop('checked', true);
            $('#rango-fechas').prop('disabled', false);
    };

    /**
     * Evento on change del checkbox para activar el filtro fecha
     */
    $('#chkRangoFechas').on('change', function() {
        if (this.checked) {
            $('#rango-fechas').prop('disabled',false);
            
            $('#desde').val( $('#rango-fechas').data('daterangepicker').startDate.format('YYYY-MM-DD') ); 
            $('#hasta').val( $('#rango-fechas').data('daterangepicker').endDate.format('YYYY-MM-DD') ); 
            
        } else {
            $('#rango-fechas').prop('disabled',true);
            $('#desde').val(''); 
            $('#hasta').val('');
        }

        $('#mapeos').DataTable().ajax.reload();

    });

    IniciarControlFechas();
</script>

<div class="ibox float-e-margins">
    <div class="ibox-content">
        <div id="tabla_mapeos" class="table-responsive no-padding">
            <?php
                $options = [
                    'ajax' => [ 'url' => '/orden-trabajos-mapeos/datatable',
                                'data' => [
                                    'campania_id' => $this->DataTables->callback('ObtenerCampaniaId'),
                                    'establecimiento_id' => $this->DataTables->callback('ObtenerEstablecimientoId'),
                                    'sectore_id' => $this->DataTables->callback('ObtenerSectoreId'),
                                    'cultivo_id' => $this->DataTables->callback('ObtenerCultivoId'),
                                    'proveedore_id' => $this->DataTables->callback('ObtenerProveedorId'),
                                    'desde' => $this->DataTables->callback('Desde'),
                                    'hasta' => $this->DataTables->callback('Hasta')
                                ]],
                    'pageLength' => 10,
                    'length' => 10,
                    'iDisplayLength' => 10,
                    'autoWidth' => false,
                    'ordering' => false,
                    'stateSave' => true,
                    'prefixSearch' => false,
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
                    'dom' => '<"row" <"col-sm-6 no-padding"f><"col-sm-6 no-padding botones-tabla-top-right"B>>
                                <"row" <"col-sm-12 no-margin no-padding"tr>>
                                <"row" <"col-sm-6"i><"col-sm-6"p>>',
                    'buttons' => [[
                                    'text' => '<i class="fa fa-file-excel-o"></i>',
                                    'titleAttr' => 'Exportar Mapeos',
                                    'className' => 'exportar-mapeos'
                                ]
                                ],
                    'columns' => [
                                    [  'title' => __('OT / Dist.'),
                                        'data' => 'orden_trabajo.id', 
                                        'className' => 'text-center', 
                                        'render' => $this->DataTables->callback('dt_render_ot')
                                    ],[
                                        'title' => __('Fecha'),
                                        'data' => 'orden_trabajo.fecha', 
                                        'className' => 'text-center',
                                        'render' => $this->DataTables->callback('obtenerFecha')
                                    ],[  
                                        'title' => __('Establecimiento'),
                                        'data' => 'orden_trabajo.establecimiento.nombre', 
                                        'render' => $this->DataTables->callback('dt_establecimiento_sector')
                                    ],[
                                        'title' => __('Campaña/Cultivo'),
                                        'data' => 'proyecto.campania_monitoreo.nombre', 
                                        'className' => 'text-center',
                                        'render' => $this->DataTables->callback('dt_campania_cultivo')
                                    ],[
                                        'title' => __('Proveedor/Labor'),
                                        'data' => 'orden_trabajo.proveedore.nombre',
                                        'className' => 'text-center',
                                        'render' => $this->DataTables->callback('dt_proveedor_labor')
                                    ],[
                                        'title' => __('Certificado Por'),
                                        'data' => 'orden_trabajo.user.nombre',
                                        'searchable' => false,
                                        'className' => 'text-center',
                                        'render' => $this->DataTables->callback('ObtenerImagenesUsersCertificador')
                                    ],[
                                        'title' => __('Tipo'),
                                        'data' => 'tipo',
                                        'searchable' => false,
                                        'className' => 'text-center',
                                        'render' => $this->DataTables->callback('dt_tipo')
                                    ],[
                                        'title' => __('Calidad'),
                                        'data' => 'calidad',
                                        'searchable' => false,
                                        'className' => 'text-center',
                                        'render' => $this->DataTables->callback('dt_calidad')
                                    ],[
                                        'title' => __('SMS'),
                                        'data' => 'sms',
                                        'searchable' => false,
                                        'className' => 'text-center',
                                        'render' => $this->DataTables->callback('dt_sms')
                                    ],[
                                        'title' => __('PDF'),
                                        'data' => 'pdf',
                                        'searchable' => false,
                                        'className' => 'text-center',
                                        'render' => $this->DataTables->callback('dt_pdf')
                                    ],[
                                        'title' => __('Problema'),
                                        'data' => 'problema',
                                        'searchable' => false,
                                        'className' => 'text-center',
                                        'render' => $this->DataTables->callback('dt_problema')
                                    ],[
                                        'title' => __('Comentario'),
                                        'data' => 'comentario',
                                        'searchable' => false,
                                        'render' => $this->DataTables->callback('dt_comentario')
                                    ],[
                                        'title' => __('Procesado Por'),
                                        'data' => 'user.nombre',
                                        'searchable' => false,
                                        'className' => 'text-center',
                                        'render' => $this->DataTables->callback('ObtenerImagenesUsers')
                                    ],[
                                        'title' => __(''),
                                        'data' => 'id',
                                        'searchable' => false,
                                        'className' => 'cell-triple-action',
                                        'render' => $this->DataTables->callback('datatable_botones')
                                    ]
                                ]
                ];
                echo $this->DataTables->table('mapeos', $options, ['class' => 'table table-bordered table-hover table-striped sin-margen-superior']);
            ?>
        </div>
    </div>
</div> 
  <!-- Modal  -->
<div class="modal otmodal" id="modal" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
        </div>
    </div>
</div>    
<script>
/* Ocultar modal */
    $('#modal').on('hide.bs.modal', function() {
        $(this).data('modal', null);
    });

    /***
     * CRUD Mapeos
     */
    /* Agregar mapeo (Modal) */
    $('#mapeos').on('click', '.Agregar', function (e) {
       
        e.preventDefault();
        var id = e.currentTarget.getAttribute('data-id');
      
        $('#modal .modal-content').load(`/orden-trabajos-mapeos/add/${id}`,function(){
            $('#modal').modal({show:true});
        });
    });

    /* Ver Campania (Modal)  */
    $('#mapeos').on('click', '.Ver', function (e) {
        e.preventDefault();
        var id = e.currentTarget.getAttribute('data-id');
        
        $('#modal .modal-content').load(`/orden-trabajos-mapeos/view/${id}`,function() {
            $('#modal').modal({show:true});
        });
    });
    
    /* Editar Campania (Modal) */
    $('#mapeos').on('click', '.Editar', function (e) {
        e.preventDefault();
        var id = e.currentTarget.getAttribute('data-id');
        
        $('#modal .modal-content').load(`/orden-trabajos-mapeos/edit/${id}`,function() {
            $('#modal').modal({show:true});
        });
    });

     /**
     * Exportar Datatable
     * Detecto el click y genero el archivo y lo descargo directamente.
     * @returns nombre del archivo para luego descargarlo
     */
    $('.exportar-mapeos').on('click', function (e) {

        var desde = '';
        var hasta = '';

        if ($('#chkRangoFechas').prop('checked')) {
            var desde = Desde();
            var hasta = Hasta();
        } 

        /* Esto permite descargar un excel SIN GUARDARLO en el servidor */
        var filtros =`campania_id=${ObtenerCampaniaId()}&establecimiento_id=${ObtenerEstablecimientoId()}&sectore_id=${ObtenerSectoreId()}`;
        filtros += `&cultivo_id=${ObtenerCultivoId()}&proveedore_id=${ObtenerProveedorId()}&desde=${desde}&hasta=${hasta}`; 

        window.open(`/orden-trabajos-mapeos/exportar?${filtros}`, "_self");
        toastr["warning"]("Aguarde, su reporte esta siendo procesado.");
    });
</script>