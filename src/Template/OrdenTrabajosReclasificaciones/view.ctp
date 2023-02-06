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
        <h3>Reclasificación Nº <?= $ordenTrabajosReclasificacione->id ?></h3>
    </div>
    <div class="col-md-6 m-t-xs">
        <div class="btn-group pull-right dt-buttons no-margins no-padding">
            <?php
                echo $this->Html->link('<i class="fa fa-home"></i>', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajosReclasificaciones', 'action' => 'index'], ['type' => 'button', 'title' => 'Volver a Reclasificaciones', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
                echo $this->Form->button('<i class="fa fa-file-excel-o"></i>', ['type' => 'button', 'title' => 'Generar dataloads', 'class' => 'btn btn-sm btn-default GenerarDataloads', 'escape' => false]);
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
</script>

<?= $this->Form->create($ordenTrabajosReclasificacione, ['id' => 'Reclasificaciones']) ?>
<div class="ibox float-e-margins">
    <div class="ibox-content">
        <fieldset>
            <?= $this->Form->control('id', ['type' => 'hidden', 'value' => $ordenTrabajosReclasificacione->id]) ?>
           <div class="col-md-12 no-margins no-padding">
               <div class="col-md-2 m-l-none">
                    <?= $this->Form->control('fecha',['type' => 'text','class' => 'form-control col-md-2 ', 'readonly', 'value' => $ordenTrabajosReclasificacione->fecha->i18nFormat('dd/MM/yyyy'),'escape' => false]) ?>
               </div>
               <div class="col-md-5 no-margins no-padding">
                    <?= $this->Form->control('nombre',['type' => 'text', 'class' => 'form-control', 'readonly']) ?>
               </div>
               <div class="col-md-5 m-r-none">
                    <?= $this->Form->control('establecimiento_id',['type'=> 'select', 'options' => $establecimientos,'class' => 'form-control select2', 'disabled' => 'disabled']) ?>
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
                                'width' => '100%',
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
                                'dom' => '<"row"<"col-sm-6 no-padding"f><"col-sm-6 no-padding botones-tabla-top-right">><"row"<"col-sm-12 no-margin no-padding"tr>><"row"<"col-sm-6"i><"col-sm-6"p>>',
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
                            <?= $this->Form->control('observaciones', ['type' => 'textarea','class' => 'form-control', 'label' => false, 'readonly']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
    </div>
</div>
<?= $this->Form->end() ?>
<script>
     
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
</script>