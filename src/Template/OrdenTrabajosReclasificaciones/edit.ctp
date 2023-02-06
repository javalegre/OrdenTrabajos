<?php
/**
 * Ordenes de Trabajo Reclasificaciones
 *
 * Editando una Reclasificaciones
 *
 * Desarrollado para Adecoagro SA.
 * 
 * @category CakePHP3
 *
 * @package Ordenes
 *
 * @author Javier Alegre <jalegre@adecoagro.com>
 * @copyright Copyright 2021, Adecoagro
 * @version 1.0.0 creado el 03/08/2022
 */
?>
<?php
    echo $this->Html->css(['Ordenes.style', 'plugins/daterangepicker/daterangepicker-3.14.1']);
    echo $this->Html->script(['plugins/daterangepicker/daterangepicker-3.14.1.min']);
?>
<div class="row border-bottom white-bg page-heading">
    <div class="col-md-6 m-t-xs">
        <h3>Editando una Reclasificación</h3>
    </div>
    <div class="col-md-6 m-t-xs">
        <div class="btn-group pull-right dt-buttons no-margins no-padding">
            <?php
                echo $this->Html->link('<i class="fa fa-home"></i>', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajosReclasificaciones', 'action' => 'index'], ['type' => 'button', 'title' => 'Volver a Reclasificaciones', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
                echo $this->Form->button('<i class="fa fa-save"></i>', ['type' => 'button','title' => 'Guardar', 'class'=>'btn btn-sm btn-default GuardarReclasificacion', 'escape' => false]);
                echo $this->Form->button('<i class="fa fa-file-excel-o"></i>', ['type' => 'button', 'title' => 'Generar dataloads', 'class' => 'btn btn-sm btn-default GenerarDataloads', 'escape' => false]);
                echo $this->Form->button('<i class="fa fa-trash-o"></i>', ['type' => 'button', 'title' => 'Eliminar reclasificación', 'data-id' => $ordenTrabajosReclasificacione->id, 'class' => 'btn btn-sm btn-default EliminarReclasificacion', 'escape' => false]);
                echo $this->Html->link('<i class="fa fa-print"></i>', ['controller' => 'OrdenTrabajosReclasificaciones', 'action' => 'view', $ordenTrabajosReclasificacione->id, '_ext' => 'pdf'], ['type' => 'button', 'title' => 'Generar PDF de la reclasificacion actual', 'class' => 'btn btn-sm btn-default', 'escape' => false]);                
            ?>
        </div>        
    </div>
</div>
<script>
    /**
     * ObtenerEstablecimientoId
     * 
     * Devuelvo el ID del establecimiento seleccionado en el select para utilizarlo
     * en los datatables server side
     * 
     * @returns {integer} id del Establecimiento
     */
    const ObtenerReclasificacionId = () => $('#id').val();
    
    datatable_fecha = function (data, type, full, meta)
    {
        if (type === 'display') {
            if (moment(data).isValid()) {
                return  moment(data).format('DD/MM/YYYY');
            }
            return '';
        }
        return data;
    };
    
    datatable_botones = function (data, type, full, meta)
    {
        if (type === 'display') {
            return '<div class="btn-group">' +
                        /* '<a target="_blank" href="/orden-trabajos/view/' + data + '" type="button" title="Ver" class="btn btn-xs btn-white btn-orden-trabajo"><i class="fa fa-eye"></i></a>' +
                        '<a target="_blank" href="/orden-trabajos/edit/' + data + '" type="button" title="Editar" class="btn btn-xs btn-white btn-orden-trabajo"><i class="fa fa-pencil"></i></a>' +
                        '<a href="/orden-trabajos/view/' + data + '.pdf" type="button" title="Generar PDF" class="btn btn-xs btn-white btn-orden-trabajo"><i class="fa fa-print"></i></a>' + */
                        '<a href="#" data-id="' + data + '" title="Anular reclasificacion" type="button" class="btn btn-xs btn-white btn-orden-trabajo EliminarReclasificacion"><i class="fa fa-trash"></i></a>' +
                    '</div>';		
        }
        return data;
    };    
</script>

<?= $this->Form->create($ordenTrabajosReclasificacione, ['id' => 'formReclasificaciones']) ?>
<div class="ibox float-e-margins">
    <div class="ibox-content">
        <fieldset>
            <?= $this->Form->control('id', ['type' => 'hidden', 'value' => $ordenTrabajosReclasificacione->id]) ?>
           <div class="col-md-12 no-margins no-padding">
               <div class="col-md-2 m-l-none">
                    <?= $this->Form->control('fecha',['type' => 'text','class' => 'form-control col-md-2 ', 'readonly' ,'value' => $ordenTrabajosReclasificacione->fecha->i18nFormat('dd/MM/yyyy'),'escape' => false]) ?>
               </div>
               <div class="col-md-5 no-margins no-padding">
                    <?= $this->Form->control('nombre',['type' => 'text', 'class' => 'form-control']) ?>
               </div>
               <div class="col-md-5 m-r-none">
                    <?= $this->Form->control('establecimiento_id',['type'=> 'select', 'options' => $establecimientos,'class' => 'form-control select2', 'disabled']) ?>
               </div>
               <?= $this->Form->control('user_id', ['type' => 'hidden', 'value' => $ordenTrabajosReclasificacione->user_id]) ?>
           </div>
            <br>&nbsp;<br>
            <div class="table-responsive col-sm-12 no-margins no-padding">
                <div id="tabla-reclasificaciones">
                    <?php
                        $options = [
                                'ajax' => [ // current controller, action, params
                                        'url' => '/orden-trabajos-reclasificaciones-detalles/index',
                                        'data' => [
                                            'orden_trabajos_reclasificacione_id' => $this->DataTables->callback('ObtenerReclasificacionId')
                                        ]
                                ],
                                'pageLength' => 20,
                                'iDisplayLength' => 20,
                                'autoWidth' => false,
                                'stateSave' => true,
                                'ordering' => false,
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
                                                ],[
                                                    'extend' => 'pdf',
                                                    'title' => 'ExampleFile',
                                                    'titleAtrr' => 'PDF',
                                                    'text' => '<i class="fa fa-file-pdf-o"></i>',
                                                    'className' => 'btn-monitoreo btn-icon-only btn-circle'
                                                ]
                                            ],
                                'columns' => [
                                        [
                                                'title' => __('OT'),
                                                'data' => 'orden_trabajos_distribucione.orden_trabajo_id'
                                        ],
                                        [
                                                'title' => __('Proyecto'),
                                                'data' => 'orden_trabajos_distribucione.proyecto.cultivo'
                                        ],[
                                                'title' => __('Labor'),
                                                'data' => 'orden_trabajos_distribucione.proyectos_labore.nombre'
                                        ],[
                                                'title' => __('Fecha'),
                                                'data' => 'created',
                                                'render' => $this->DataTables->callback('datatable_fecha')
                                        ], [
                                                'title' => __('Proyecto Origen'),
                                                'data' => 'proyecto.cultivo',
                                                'className' => 'text-primary'
                                        ],[
                                                'title' => __('Labor Origen'),
                                                'data' => 'proyectos_labore.nombre',
                                                'className' => 'text-primary'
                                        ],[
                                                'title' => __('Referencia'),
                                                'data' => 'referencia',
                                                'className' => 'text-primary'
                                        ], [
                                                'title' => __('Observaciones'),
                                                'data' => 'observaciones',
                                                'className' => 'text-primary'
                                        ], [
                                                'title' => __(' '),
                                                'data' => 'id',
                                                'searchable' => false,
                                                'orderable' => false,
                                                'className' => 'cell-double-action',
                                                'render' => $this->DataTables->callback('datatable_botones')
                                        ]
                                ]
                        ];
                        echo $this->DataTables->table('dt-reclasificaciones', $options, ['class' => 'table table-bordered table-hover table-striped sin-margen-superior']);
                    ?>
                </div>                
            </div>
            <br>&nbsp;<br>
            <div class="col-md-12 no-margins no-padding">
                <div class="row">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            Observaciones
                        </div>
                        <div class="panel-body">
                            <?= $this->Form->control('observaciones', ['type' => 'textarea','class' => 'form-control', 'label' => false]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
    </div>
</div>
<?= $this->Form->end() ?>

<!-- Modal para edición de labores -->
<div class="modal otmodal" id="reclasificaciones" tabindex="-1" role="dialog"  aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content animated fadeIn"></div>
    </div>
</div>

<script>
    /*
     * Creamos una reclasificacion nueva
     */
    $('.nueva-reclasificacion').on('click', function() {
        var orden_trabajos_reclasificacione_id = $('#id').val();
        $('#reclasificaciones .modal-content').load(`/orden-trabajos-reclasificaciones-detalles/add?orden_trabajos_reclasificacione_id=${orden_trabajos_reclasificacione_id}`, function() {
            $('#reclasificaciones').modal({show:true});
        });
    });
     
     /*
      * Guardamos los cambios de la reclasificacion
      */
    $('.GuardarReclasificacion').on('click', function() {
        const data = new FormData(document.getElementById('formReclasificaciones'));
        var id = $('#id').val();
        
        let url = new URL(`${window.location.protocol}//${window.location.host}/orden-trabajos-reclasificaciones/edit/${id}`);
        fetch(url, {
            method: 'POST',
            body: data
        })
        .then( res => res.json())
        .then( data => {
            if (data.status === 'success') {
                toastr.success(data.message);
                return;
            }
            toastr.error(data.message);
        });
    });
     
    /**
     * Se elimina la reclasificacion, siempre y cuando no tenga lineas de detalles.
     * 
     */ 
    $('.EliminarReclasificacion').on('click', function(e) {
        e.preventDefault();
        var id = e.currentTarget.getAttribute('data-id');
        
        fetch(`/orden-trabajos-reclasificaciones/delete/${ id }`, {
            method: 'DELETE'
            })
            .then( res => res.json())
            .then( data => {
                    if (data.status === 'error') {
                        toastr.error(data.message);
                        return;
                    }
                    window.location.href = `${window.location.protocol}//${window.location.host}/orden-trabajos-reclasificaciones`;
                })
            .catch(err => {
                        console.error(err);
                });
    });
     
    /**
     * 
     */
    $('.GenerarDataloads').on('click', function() {
        var orden_trabajos_reclasificacione_id = $('#id').val();
        console.log('Generando Archivo Excel segun id: ', orden_trabajos_reclasificacione_id);

        let url = new URL(`${window.location.protocol}//${window.location.host}/orden-trabajos-reclasificaciones/generar-excel/${orden_trabajos_reclasificacione_id}`);    
        fetch(url) 
            .then( res => res.json())
            .then( data => {
                if (data.status === 'success') {
                    $(location).attr('href', `/dataload/${data.archivo}`);
                    return;
                }
                toastr.error(data.message);
            });
    });
    
     $('#dt-reclasificaciones').on('click', 'a.EliminarReclasificacion', function (e) {
        e.preventDefault();
        var id = e.currentTarget.getAttribute('data-id');
        fetch(`/orden-trabajos-reclasificaciones-detalles/delete/${ id }`, {
            method: 'DELETE'
            }).then( res => res.json() )
              .then( data => {
                    if (data.status === 'success') {
                        $('#dt-reclasificaciones').DataTable().ajax.reload();
                        return;
                    } 
                    toastr.error(data.message); /* Se anuló correctamente */
                }).catch(err => {
                        console.error(err);
                });
    });
</script>