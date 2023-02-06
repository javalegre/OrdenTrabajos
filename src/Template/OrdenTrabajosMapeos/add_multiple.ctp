<?php
/**
 * addMultiple
 * Permite Add las mismas caracteristicas de mapeos a varias OT 
 *   
 * Desarrollado para Adecoagro SA.
 * 
 * @category CakePHP3
 * @author Pablo Snaider <psnaider@adecoagro.com>
 * @copyright Copyright 2022, Adecoagro
 * @version 1.0.0 creado el 16/01/2022
 */
?>
<?= $this->Html->css(['Ordenes.style', 'plugins/daterangepicker/daterangepicker-3.14.1']) ?>
<?= $this->Html->script(['plugins/daterangepicker/daterangepicker-3.14.1.min']) ?>

<?= $this->Form->control('tipos', ['type' => 'hidden', 'value' => json_encode($mapeosCampaniasTipos)]) ?>
<?= $this->Form->control('calidades', ['type' => 'hidden', 'value' => json_encode($mapeosCalidades)]) ?>
<?= $this->Form->control('problemas', ['type' => 'hidden', 'value' => json_encode($mapeosProblemas)]) ?>
<?= $this->Form->control('users', ['type' => 'hidden', 'value' => json_encode($users)]) ?>
<?= $this->Form->control('requiere_comentario', ['type' => 'hidden', 'value' => 0]) ?>

<?= $this->Form->control('establecimientos', ['type' => 'hidden', 'value' => json_encode($establecimientos)]) ?>
<?= $this->Form->control('sectores', ['type' => 'hidden', 'value' => json_encode($sectores)]) ?>
<?= $this->Form->control('desde', ['type' => 'hidden']) ?>
<?= $this->Form->control('hasta', ['type' => 'hidden']) ?>
<?= $this->Form->control('ot', ['type' => 'hidden']) ?>

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
    

    /* 
     * Muestro el Establecimiento, Sector, Lotes y Superficie de la OT
     */
    const dt_establecimiento_sector = function (data, type, full, meta) {
        if (type === 'display') {
            
            var organizacion =  full.orden_trabajo.establecimiento.nombre;
            var sector = full.lote.sectore.nombre;
            var lote = full.lote.nombre;
            var superficie = full.superficie;
            
            return `<div>${organizacion}</div> <span class="small pull-right lote">${lote}</span> <br>` + 
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
            return data + `<br><span class="small pull-right cultivo">${cultivo}</span>`;
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
            var mapeo = full.orden_trabajos_mapeos[0];
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
            var mapeo = full.orden_trabajos_mapeos[0];
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
            var mapeo = full.orden_trabajos_mapeos[0];
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
            var mapeo = full.orden_trabajos_mapeos[0];
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
            var mapeo = full.orden_trabajos_mapeos[0];
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
            var mapeo = full.orden_trabajos_mapeos[0];
            if (mapeo) {
                return mapeo.comentario;
            }
            return '';    
        }
        return '';
    }


    /**
     * datatable_seleccion 
     * Dibuja los select y los pone como checket si fueron seleccionados.
     * @param data The data for the cell (based on columns.data)
     * @param type 'filter', 'display', 'type' or 'sort'
     * @param full The full data source for the row
     * @param meta Object containing additional information about the cell
     * @returns Manipulated cell data
     */
    const datatable_seleccion = (data, type, full, meta) =>
    {
        if (type === 'display') {
            
            var checked = '';
            if (ot != '') {
                var existe = ot.findIndex(element => element == full.id);
                if (existe != -1) {
                    checked = "checked";    
                }
            }
            
            return `<div class="form-check">
                        <input class="form-check-input AgregarOt" data-id="${full.id}" type="checkbox" ${checked} >
                    </div>`;
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
            
            if (! data) {
                return '';
            }

            var image = full.orden_trabajos_mapeos[0].user.img_base64;
            var nombre = full.orden_trabajos_mapeos[0].user.nombre;
            if (image) { 
                return '<img title="' + nombre + '" class="img-circle m-r-xs" width="20" src="data:image/jpg;base64,' + image + '">';
            } else {
                return '<img title="' + nombre + '" class="img-circle m-r-xs" width="20" src="/img/users/user.jpg">';
            }
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
</script>
<div class="row border-bottom white-bg page-heading">
    <div class="col-md-7 m-t-xs">
        <h3>Agregar Multiples
    </div>
    <div class="col-md-5 col-sm-6 col-xs-4 m-t-xs">
        <div class="btn-group pull-right dt-buttons no-margins no-padding">
            <?= $this->Html->link('<i class="fa fa-home"></i>', 
                    ['controller' => 'OrdenTrabajosMapeos', 'action' => 'index'], 
                    ['type' => 'button', 'title' => 'Volver al Listado', 'class'=>'btn btn-sm btn-default', 'escape' => false]) ?>
        </div>        
    </div>
</div>
<div class="ibox float-e-margins m-b-xs">
   
</div>
<div class="ibox float-e-margins">
    <div class="ibox-content p-xs">
        <div class="panel-options">
            <ul class="nav nav-tabs">
                <li class="active">
                    <a href="#datos-mapeos" data-toggle="tab">
                        1. Datos de Mapeo
                    </a>
                </li>
                <li>
                    <a href="#seleccionar-ots" data-toggle="tab">
                        2. Seleccionar Ots
                        <span class="label label-warning eye-catching cantidad"></span>
                    </a>
                </li>
                <li class=""><a href="#agregar-multiple" data-toggle="tab">3. Controlar y Guardar </a></li>
            </ul>
        </div>
        <div class="panel-body blank-panel">
            <div class="tab-content">
                
                <!-- datos de mapeo -->
                <div class="tab-pane active" id="datos-mapeos">    
                    <?= $this->Form->create('ot', ['id' => 'formulario']) ?>
                    <div class="row white-bg m-t-xs m-b-xs ">
                        <div class="col-md-12 m-b-xs m-t-xs no-padding">
                            <div class="col-md-4">
                                <?= $this->Form->control('mapeos_campanias_tipo_id', ['type' => 'select', 'label' => false, 'class' => 'form-control select2', 'options' => [], 'required']) ?>
                            </div>
                            <div class="col-md-4">
                                <?= $this->Form->control('mapeos_calidade_id', ['type' => 'select', 'label' => false, 'class' => 'form-control select2', 'options' => [], 'required' ]) ?>
                            </div>
                            <div class="col-md-4">
                                <?= $this->Form->control('mapeos_problema_id', ['type' => 'select', 'label' => false, 'class' => 'form-control select2', 'options' => [], 'required' ]) ?>
                            </div>
                        </div>
                        <div class="col-md-12 m-b-xs m-t-xs no-padding">
                            <div class="col-md-4">
                                <?= $this->Form->control('comentario', ['class' => 'form-control', 'type' => 'text', 'label' => false, 'placeholder' => 'Ingrese un Comentario', 'required']) ?>
                            </div>
                            <div class="col-md-4">
                                <?= $this->Form->control('user_id', ['type' => 'select', 'label' => false, 'class' => 'form-control select2', 'options' => [], 'required' ]) ?>
                            </div>
                            <div class="col-md-4">
                                <div class="col-md-12 m-t-xs">
                                    <div class="col-md-4 m-t-xs">
                                        <?= $this->Form->control('sms',['label' => '  SMS', 'type' => 'checkbox', 'class' => 'm-t-md' ,'checked' => '', 'required' ]) ?>
                                    </div>
                                    <div class="col-md-4 m-t-xs">
                                        <?= $this->Form->control('pdf',['label' => '  PDF', 'type' => 'checkbox', 'class' => 'm-t-md' ,'checked' => '', 'required' ]) ?>
                                    </div>    
                                </div>     
                            </div>
                        </div>
                    </div>
                    <?= $this->Form->control('data', ['type' => 'hidden']) ?>    
                    <?= $this->Form->end() ?>
                </div>

                <!-- seleccionar Ots -->
                <div class="tab-pane " id="seleccionar-ots"> 
                    <div class="row white-bg m-t-xs m-b-xs ">    
                        <div class="col-md-12 m-b-xs m-t-xs no-padding">
                            <div class="col-md-4">
                                <?= $this->Form->control('establecimiento_id',['type'=> 'select', 'label' => false, 'options' => [], 'class' => 'form-control select2', 'required']) ?>
                            </div>
                            <div class="col-md-4">
                                <?= $this->Form->control('sectore_id',['type'=> 'select', 'label' => false, 'options' => [], 'class' => 'form-control select2', 'required']) ?>
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

                    <!-- Inicio datatble  -->
                    <div id="tabla_ot" class="table-responsive m-t-md">
                        <?php
                            $options = [
                                'ajax' => [ 'url' => '/orden-trabajos-mapeos/datatable-multiple',
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
                                                [  'title' => __('OT'),
                                                    'data' => 'orden_trabajo.id', 
                                                    'className' => 'text-center', 
                                                ],[  
                                                    'title' => __('OT Dist.'),
                                                    'data' => 'id', 
                                                    'className' => 'text-center', 
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
                                                    'className' => 'text-center',
                                                    'render' => $this->DataTables->callback('ObtenerImagenesUsersCertificador')
                                                ],[
                                                    'title' => __(''),
                                                    'data' => 'id',
                                                    'searchable' => false,
                                                    'className' => 'cell-triple-action',
                                                    'render' => $this->DataTables->callback('datatable_seleccion')
                                                ]
                                            ]
                            ];
                            echo $this->DataTables->table('mapeos', $options, ['class' => 'table table-bordered table-hover table-striped sin-margen-superior']);
                        ?>

                    </div>
                </div>

                <!-- controlar y guardar -->
                <div class="tab-pane " id="agregar-multiple">
                    <div class="row ">
                        <div class="col-lg-10">
                            <div class="ibox no-padding">
                                <div class="ibox-title">
                                    <h5>OTs Seleccionadas</h5>
                                    <div class="ibox-tools">
                                        <a class="collapse-link">
                                            <i class="fa fa-chevron-up"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="ibox-content no-padding">
                                    <table id="Tabla" class="table table-bordered table-hover table-striped"> 
                                        
                                        <thead><?= $this->Html->tableHeaders(['OT','OT Dist.','Fecha','Establecimiento','Lote','Cultivo','']) ?></thead>
                                        <tbody id="otSeleccionadas">
                                            
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="row no-padding">                
                        <div class="col-md-6 m-b-xs no-padding">
                            <div class="col-md-12 m-t-md">    
                                <?= $this->Form->button('Guardar', ['class' => "btn btn-success generarQr ladda-button ladda-button-demo", 'data-style'=>'zoom-in', 'type' => 'button', 'escape' => false]) ?>
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
     * Inicializo Select2 MapeosCampaniasTipos
     */
    var data = JSON.parse($('#tipos').val());
    $('#mapeos-campanias-tipo-id').select2({
        theme: "bootstrap",
        placeholder: 'Seleccionar un Tipo Campaña...',
        width: '100%',
        allowClear: true,
        data:data
    }).val('').trigger('change').on('change.select2', function (e) {
        e.stopPropagation();

        if (!$("#formulario").valid()) {
            return;
        }
    });

    
    /**
     * Inicializo Select2 MapeosCalidades
     */
    var data = JSON.parse($('#calidades').val());
    $('#mapeos-calidade-id').select2({
        theme: "bootstrap",
        placeholder: 'Seleccionar un Tipo Calidad...',
        width: '100%',
        allowClear: true,
        data:data
    }).val('').trigger('change').on('change.select2', function (e) {
        e.stopPropagation();
        
        if (!$("#formulario").valid()) {
            return;
        }
    });

    /**
     * Inicializo Select2 Users
     */
    var data = JSON.parse($('#users').val());
    $('#user-id').select2({
        theme: "bootstrap",
        placeholder: 'Seleccionar un Usuario...',
        width: '100%',
        allowClear: true,
        data:data
    }).val('').trigger('change').on('change.select2', function (e) {
        e.stopPropagation();
        
        if (!$("#formulario").valid()) {
            return;
        }
    });

    /**
     * Inicializo Select2 MapeosProblemas
     */
    var comentario_obligatorio = "";
    var data = JSON.parse($('#problemas').val());
    $("#mapeos-problema-id").select2({
        theme: "bootstrap",
        width: '100%',
        placeholder: "Seleccionar un Problema...",
        allowClear: true,
        data: data,
        matcher: function (params, data) {
                // If there are no search terms, return all of the data
                if ($.trim(params.term) === '')  return data;

                // `params.term` should be the term that is used for searching
                // `data.text` is the text that is displayed for the data object
                if (data.text.toUpperCase().indexOf(params.term.toUpperCase()) > -1) {
                  var modifiedData = $.extend({}, data, true);
                  //  modifiedData.text += ' (matched)';

                  // You can return modified objects from here
                  // This includes matching the `children` how you want in nested data sets
                  return modifiedData;
                }
                return null;

        },
        templateResult: function ( data ) {
                    if (data.loading) {
                        return data.text;
                        console.log('otro');
                    }
                                      
                    let $container = $(`<div>${data.text}</div>`);
                    return $container;
                },
        templateSelection: function ( data ) {
            if (data.text) {
                
                comentario_obligatorio = data.requiere_comentario;
                return data.text;   
            }
            let $container = $(`<div>${data.text}</div>`);
            return $container;
            
        }
    }).val('').trigger('change').on('change.select2', function (e) {
        e.stopPropagation();
        $('#requiere-comentario').val(comentario_obligatorio);
        
        if (!$("#formulario").valid()) {
            return;
        }
    });

    /* valida el resto de las fechas */
    jQuery.validator.addMethod("validarComentario", function(value, element, parametro ) {
        var comentario = $(parametro[0]).val();
        if (comentario == 1 && value == '') {
            return false;
        } else {
            return true;
        }

        }, jQuery.validator.format ("Ingrese Comentario para este Problema.") 
    );
    
    /**
     * Validar el formulario 
     */
    $('#formulario').validate({
        rules: {
            mapeos_campanias_tipo_id: {
                required:true, 
            },
            mapeos_calidade_id: {
                required: true
            },
            mapeos_problema_id: {
                required: true
            },
            comentario: {
                required:false,
                validarComentario: ["#requiere-comentario"]
            },
            user_id: {
                required: true
            }
        },
        messages: {
            mapeos_campanias_tipo_id: { 
                required: "Ingrese Tipo de Campaña."
            },
            mapeos_calidade_id: {
                required: "Ingrese Calidad"
            },
            mapeos_problema_id: {
                required: "Ingrese un Problema"
            },
            user_id: {
                required: "Ingrese un Usuario"
            }
        },
        ignore: ["input[type=checkbox]"]
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
    
    /**
     * AgregarOt
     * Cada ot seleccionada de la tabla, se almacena en un array para generar el add multiple.
     * Event on Change
     */

    /* vble global para almacenar las orden_trabajos_distribucione_id */
     var ot = [];

    $('#mapeos').on('change', '.AgregarOt', function (e) {
        e.preventDefault();

        if ( $(this).is(":checked") ) {

            /* valido que no exista el id en la tabla se seleccionados */
            var existe = ot.findIndex(element => element == $(this).attr('data-id'));
            if (existe != -1 && ot.length > 0) {
                return false;    
            } 
            
            ot.push($(this).attr('data-id'));
            var suma = $('#ot').val() ? Number($('#ot').val()) + Number(1) : 1;

            agregarFilaTablaSeleccionados($(this));
            $('#data').val(ot);

        } else {
            var newArray = ot.filter((item) => item !== $(this).attr('data-id'));
            ot = newArray;
            var suma = $('#ot').val() - 1;

            borrarFilaTablaSeleccionados( $(this).attr('data-id'));
            $('#data').val(ot);
        } 

        $('#ot').val(suma);
        $('.cantidad').text($('#ot').val());
    });

    /**
     * agregarFilaTablaSeleccionados
     * Agrego Semillas (filas) a la tabla de semillas seleccionadas para mostrar al usuario
     * @param | Objet 
     * @return | DOM
     * 
     */
    function agregarFilaTablaSeleccionados(element, tabla) {
        
        if (!element){
            return false;
        }

        var ele = element.parent().parent().parent();
        var ot = ele.find("td").eq(0).text();
        var otDistribuciones = ele.find("td").eq(1).text();
        var fecha = ele.find("td").eq(2).text();
        var establecimiento = ele.find("td").eq(3).find("div").text();
        var lote = ele.find("td").eq(3).find(".lote").text();
        var cultivo = ele.find("td").eq(4).find(".cultivo").text();
        
            
        var Linea = `<tr>
                            <td class="text-center">${ot}</td>
                            <td class="text-center">${otDistribuciones}</td>
                            <td class="text-center">${fecha}</td>
                            <td class="text-center">${establecimiento}</td>
                            <td class="text-center">${lote}</td>
                            <td class="text-center">${cultivo}</td>
                            <td class="cell-single-action">
                                <a href="#" data-id="${otDistribuciones}" title="Eliminar OT" type="button" class="btn btn-xs btn-white btn-orden-trabajo EliminarOt"><i class="fa fa-trash"></i></a>
                            </td>    
                    </tr>`;
        document.getElementById("otSeleccionadas").insertRow(-1).innerHTML = Linea;
    }


    /**
     * borrarFilaTablaSeleccionados
     * Borra la ot (fila) de la tabla ot seleciconadas
     * @param | integer
     * @return | DOM
     * 
     */
    function borrarFilaTablaSeleccionados(id) {
        if (!id){
            return false;
        }

        $("#Tabla tbody tr").each(function (index) {
            if ($(this).find("td").eq(1).text() == id) {
                $(this).closest('tr').remove();
            }
        });
    }

    /**
     * Eliminar Registro 
     * @param {object} e Objeto con atributo data-id con el ID 
     * @return {object} Devuelve el resultado en JSON
     */
    $('#Tabla').on('click', 'a.EliminarOt', function (e) {
        e.preventDefault();
    
        var id = e.currentTarget.getAttribute('data-id');
        $(this).parent().parent().remove();

        var newArray = ot.filter((item) => item !== $(this).attr('data-id'));
        ot = newArray;
        $('#data').val(ot);
        
        var suma = $('#ot').val() - 1;
        $('#ot').val(suma);
        $('.cantidad').text($('#ot').val());
        
        /*desactivo el check el la tabla */
        $("#mapeos").find('input[type=checkbox]').each(function() {
            
            if (id == $(this).attr('data-id')) {
                if ($(this).is(':checked')) {
                    $(this).prop('checked', false);
                }
            }           
        });
    });

    /**
     * generarQr
     * Genera los QR para los insumos seleccionados.
     * Event on click
     * 
     */
    $('.generarQr').on('click', function() {

        /** valido si hay insumos selecciondos */
        if (ot.length === 0) {
            toastr.error("Error, debe seleccionar OTs");
            return false;
        }

        if (!$("#formulario").valid()) {
            toastr.error("Error, Ingrese los Datos de Mapeo");
            return;
        }

        toastr.warning('Agregando las OT Seleccionadas.');

        /** genero el formulario para enviar por POST */
        $('#data').val(ot);
        const data = new FormData(document.getElementById('formulario')); 
        let url = new URL(`${window.location.protocol}//${window.location.host}/orden-trabajos-mapeos/add-multiple`);

        fetch(url, {
            method: 'POST',
            body: data
        })
        .then( res => res.json())
        .then( data => {
            if (data.response.status == 'success') {
                toastr.success(data.response.message);

                let url = new URL(`${window.location.protocol}//${window.location.host}/orden-trabajos-mapeos/index`);
                setTimeout(function() {window.location.href = url}, 1000);
                return;
            }
            toastr.error(data.response.message);
        });
    });


    /** desactivo el evento enter del formulario */
    $(document).ready(function() {
        $("form").keypress(function(e) {
            if (e.which == 13) {
                return false;
            }
        });
    });
</script>   