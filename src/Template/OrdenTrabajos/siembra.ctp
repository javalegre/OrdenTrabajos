<?php
    /**
     * @var \App\View\AppView $this
     * @var \App\Model\Entity\OrdenTrabajo $ordenTrabajo
     */
    $condicion = $ordenTrabajo->orden_trabajos_condiciones_meteorologica;
?>
<div class="row border-bottom white-bg page-heading">
    <div class="col-lg-9 col-md-9 m-t-xs">
        <h3>Orden de Trabajo de Siembra Nº <?= $ordenTrabajo->id ?></h3>
    </div>
    <div class="col-lg-3 col-md-3 m-t-xs">
        <div class="btn-group pull-right dt-buttons no-margins no-padding">
            <?php

                echo $this->Html->link('<i class="fa fa-plus"></i>',['controller' => 'OrdenTrabajos', 'action' => 'add'],['type' => 'button','title' => 'Crear nueva OT', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
                echo $this->Form->button('<i class="fa fa-save"></i>', ['type' => 'button','title' => 'Guardar', 'class'=>'btn btn-sm btn-default EjecutarOT', 'escape' => false]);
                // if ($this->Acl->check(['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajos', 'action' => 'vca'])) {
                    echo $this->Html->link('<i class="fa fa-flask"></i>',['controller' => 'OrdenTrabajos', 'action' => 'vca', $ordenTrabajo->id],['type' => 'button','title' => 'Entregar Insumos', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
                //}                
                echo $this->Html->link('<i class="fa fa-list"></i>',['controller' => 'OrdenTrabajos', 'action' => 'index'],['type' => 'button','title' => 'Bandeja de Entrada', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
                if ( $ordenTrabajo->certificable === 1) { // && $this->Acl->check(['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajos', 'action' => 'certificarot']) ){
                    echo $this->Html->link('<i class="fa fa-check text-navy"></i>', ['controller' => 'OrdenTrabajos', 'action' => 'certificar-siembra', $ordenTrabajo->id] ,['type' => 'button','title' => 'Certificar', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
                }
                echo $this->Html->link('<i class="fa fa-print"></i>',['controller' => 'OrdenTrabajos', 'action' => 'siembra', $ordenTrabajo->id, '_ext' => 'pdf'],['type' => 'button','title' => 'Imprimir', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
            ?>
        </div>
    </div>
</div>
<?= $this->Form->create($ordenTrabajo, ['id' => 'ordenTrabajo']) ?>
<div class="ibox float-e-margins">
    <div class="ibox-content">
        <fieldset>
            <?= $this->Form->hidden($ordenTrabajo->id) ?>
           <div class="col-md-12 no-margins no-padding">
               <div class="col-md-2 m-l-none">
                    <?= $this->Form->control('fecha',['type' => 'text','class' => 'form-control col-md-2', 'readonly' ,'value' => date_format($ordenTrabajo->fecha, 'd/m/Y'),'escape' => false]) ?>
               </div>
               <div class="col-md-5 no-margins no-padding">
                    <?= $this->Form->control('establecimiento_id',['type'=> 'select', 'options' => $establecimientos,'class' => 'form-control select2']) ?>
               </div>
               <div class="col-md-5 m-r-none">
                    <?= $this->Form->control('proveedore_id',['type'=> 'select', 'label' => 'Proveedor','options' => $proveedores,'class' => 'form-control select2']) ?>
               </div>
           </div>
            <br><br>
            <div class="table-responsive col-sm-12 no-margins no-padding">
                <table id="dt_ordentrabajo" class="table table-bordered table-hover table-striped contractors-table dataTable no-footer" cellspacing="0" width="100%">
                    <thead>
                        <?= $this->Html->tableHeaders(['Proyectos Activos','Labor Solicitada','UM','Tecnica','Nombre Lote','Has','Moneda','Importe','','']) ?></thead>
                    <tfoot>
                        <tr>
                            <th colspan="5"></th>
                            <th colspan="1"></th>
                            <th colspan="4"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <br><br>
            <div class="table-responsive col-sm-12 no-margins no-padding">
                <table id="dt_insumos" class="table table-bordered table-hover table-striped insumos-table dataTable no-footer" cellspacing="0" width="100%">
                    <thead>
                        <tr class="background-gray">
                            <th>Producto</th>
                            <th>Lote</th>
                            <th>Unidad</th>
                            <th>Dosis</th>
                            <th>Cantidad</th>
                            <th>Entrega</th>
                            <th>Devolucion</th>
                            <th>Aplicado</th>
                            <th>Almacen</th>
                            <th>&nbsp;</th>
                            <th class="hidden"></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>              
            </div>
            <div class="col-md-12 no-margins no-padding">
                <div class="row">
                    <div class="col-md-8 m-l-none m-r-none">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Observaciones
                            </div>
                            <div class="panel-body">
                                <?= $this->Form->control('observaciones', ['type' => 'textarea','class' => 'form-control', 'label' => false]) ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 m-r-none">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Condiciones Meteorológicas
                            </div>
                            <div class="panel-body p-xs">
                                <div class="col-md-12 no-margins no-padding">
                                    <div class="row m-b-xs">
                                        <div class="col-md-4 no-margins no-padding">
                                            <label class="titulo-condiciones">Fecha / Hora</label>
                                        </div>
                                        <div class="col-md-8 no-margins no-padding">
                                            <input type="text" class="form-control edit-input-inline" data-mask="99/99/9999 99:99" name="cm_fecha" placeholder="" value="<?= $condicion ? $condicion->fecha->i18nFormat('dd/MM/yyyy HH:mm') : ''  ?>">
                                            <!--<input type="text" class="form-control edit-input-inline" name="cm_fecha" id="cm_fecha" data-mask="99/99/9999" placeholder="">-->
                                        </div>
                                    </div>
                                    <div class="row m-b-xs">
                                        <div class="col-md-4 no-margins no-padding">
                                            <label class="titulo-condiciones">Temp. (º)</label>
                                        </div>
                                        <div class="col-md-8 no-margins no-padding">
                                            <input type="text" class="form-control edit-input-inline" name="cm_temperatura" id="cm_temperatura" value="<?= $condicion ? $condicion->temperatura : ''  ?>">
                                        </div>
                                    </div>                        
                                    <div class="row m-b-xs">
                                        <div class="col-md-4 no-margins no-padding">
                                            <label class="titulo-condiciones">Humedad (%)</label>
                                        </div>
                                        <div class="col-md-8 no-margins no-padding">
                                            <input type="text" class="edit-input-inline" name="cm_humedad" id="cm_humedad" value="<?= $condicion ? $condicion->humedad : '' ?>">
                                        </div>
                                    </div>
                                    <div class="row m-b-xs">
                                        <div class="col-md-4 no-margins no-padding">
                                            <label class="titulo-condiciones">Viento (km/h)</label>
                                        </div>
                                        <div class="col-md-8 no-margins no-padding">
                                            <input type="text" class="edit-input-inline" name="cm_viento" id="cm_viento" value="<?= $condicion ? $condicion->viento : '' ?>">
                                        </div>
                                    </div>
                                    <div class="row m-b-xs">
                                        <div class="col-md-4 no-margins no-padding">
                                            <label class="titulo-condiciones">Dirección</label>
                                        </div>
                                        <div class="col-md-8 no-margins no-padding">
                                            <input type="text" class="edit-input-inline" name="cm_direccion" id="cm_direccion" value="<?= $condicion ? $condicion->direccion : '' ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>                    

                    </div>
                </div>
            </div>
            <div class="col-md-12 no-margins no-padding">
                <div class="panel panel-default">
                    <div class="panel-body no-margins no-padding">
                        <div class="col-md-12 no-margins no-padding">
                            <div class="col-md-2 m-l-xs no-padding">
                                <?= $this->Form->control('orden_trabajos_estado_id',['type'=> 'select', 'label' => false, 'options' => $estados,'class' => 'form-control select2', 'disabled']) ?>
                            </div>
                            <div class="col-md-2 no-margins pull-right">
                                <?= $this->Form->button('<i class="fa fa-save"></i>&nbsp;&nbsp;<span class="bold">Guardar OT</span>', ['type' => 'button','title' => 'Guardar cambios', 'class'=>'btn btn-block btn-primary EjecutarOT m-t-xs m-l-xs','id' => 'EjecutarOT', 'escape' => false]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
    </div>
</div>

<?= $lote ? $this->Form->control('lote', ['type' => 'hidden', 'value' => $lote]) : '' ?>
<?= $this->Form->control('siembra', ['type' => 'hidden']) ?>
<?= $this->Form->control('id',['class'=>'form-control', 'value'=> $ordenTrabajo->id ]) ?>
<?= $this->Form->control('cm_fecha',['class'=>'form-control', 'type'=> 'hidden', 'id' => 'cm_fecha']) ?>
<?= $this->Form->control('tarifario', ['type' => 'hidden']) ?>

<?= $this->Form->end() ?>

<?php 
    echo '<script type="text/template" id="row-actions-template">
                <div class="btn-group">
                    <a class="btn btn-xs btn-info edit" href="#"><i class="fa fa-pencil"></i></a>
                    <a class="btn btn-xs btn-success duplicar" href="#" title="Duplicar esta linea"><i class="fa fa-copy"></i></a>
                    <a class="btn btn-xs btn-danger delete-link delete" href="#"><i class="fa fa-times"></i></a>
                </div>
            </script>';
    echo '<script type="text/template" id="row-actions-template-machine">
            <div class="btn-group">
                <a class="btn btn-xs btn-info font-s10 edit" href="#"><i class="fa fa-pencil"></i></a>
                <a class="btn btn-xs btn-danger delete-link font-s10 delete"  href="#"><i class="fa fa-times"></i></a>
            </div>
        </script>';
    echo '<script type="text/template" id="row-actions-insumos">
            <div class="btn-group">
                <a class="btn btn-xs btn-info edit" href="#"><i class="fa fa-pencil"></i></a>
                <a class="btn btn-xs btn-success duplicar" href="#" title="Duplicar esta linea"><i class="fa fa-copy"></i></a>
                <a class="btn btn-xs btn-danger delete-link delete" href="#"><i class="fa fa-times"></i></a>
            </div>
        </script>';
?>
<?= $this->Html->script(['Ordenes.OrdenTrabajoSiembra.js?ver='.$version]) ?>