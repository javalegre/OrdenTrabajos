<?php
    /**
     * @var \App\View\AppView $this
     * @var \App\Model\Entity\OrdenTrabajo $ordenTrabajo
     */

    $condicion = $ordenTrabajo->orden_trabajos_condiciones_meteorologica;
?>
<?= $this->Form->control('email_proveedor', ['type' => 'hidden', 'value' =>  $ordenTrabajo->proveedore->email]) ?>

<div class="row border-bottom white-bg page-heading">
    <div class="col-lg-9 col-md-9 m-t-xs">
        <h3>Orden de Trabajo Nº <?= $ordenTrabajo->id ?></h3>
    </div>
    <div class="col-lg-3 col-md-3 m-t-xs">
        <div class="btn-group pull-right dt-buttons no-margins no-padding">
            <?php
                echo $this->Html->link('<i class="fa fa-plus"></i>',['controller' => 'OrdenTrabajos', 'action' => 'add'],['type' => 'button','title' => 'Crear nueva OT', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
                echo $this->Form->button('<i class="fa fa-save"></i>', ['type' => 'button','title' => 'Guardar', 'class'=>'btn btn-sm btn-default EjecutarOT', 'escape' => false]);
                echo $this->Html->link('<i class="fa fa-list"></i>',['controller' => 'OrdenTrabajos', 'action' => 'index'],['type' => 'button','title' => 'Bandeja de Entrada', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
//                echo $this->Form->button('<i class="sicon sicon-weather"></i>', ['type' => 'button','title' => 'Condiciones Agro Meteorológicas', 'class'=>'btn btn-sm btn-default Condiciones', 'escape' => false]);
                if ( $ordenTrabajo->certificable === 1 && $this->Acl->check(['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajos', 'action' => 'certificarot']) ){
                    echo $this->Html->link('<i class="fa fa-check text-navy"></i>', ['controller' => 'OrdenTrabajos', 'action' => 'Certificarot', $ordenTrabajo->id] ,['type' => 'button','title' => 'Certificar', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
                }
                echo $this->Form->button('<i class="fa fa-trash"></i>', ['type' => 'button','title' => 'Anular OT', 'data-id' => $ordenTrabajo->id,'class'=>'btn btn-sm btn-default AnularOT', 'escape' => false]);
                //echo $this->Form->button('<i class="fa fa-copy"></i>', ['type' => 'button','title' => 'Generar copia', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
                
                /* Enviar por mail - TODO: Pablo Snaider */
                if ( $ordenTrabajo->orden_trabajos_estado_id > 1 &&  $ordenTrabajo->orden_trabajos_estado_id < 5) {
                    echo $this->Form->button('<i class="fa fa-envelope-o"></i>', ['type' => 'button','title' => 'Enviar por Mail', 'id' => 'enviar-email', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
                }    
                echo $this->Html->link('<i class="fa fa-print"></i>',['controller' => 'OrdenTrabajos', 'action' => 'view', $ordenTrabajo->id, '_ext' => 'pdf'],['type' => 'button','title' => 'Imprimir', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
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
                    <?= $this->Form->control('fecha',['type' => 'text','class' => 'form-control col-md-2 ', 'readonly' ,'value' => $ordenTrabajo->fecha->i18nFormat('dd/MM/yyyy'),'escape' => false]) ?>
               </div>
               <div class="col-md-5 no-margins no-padding">
                    <?php
                        if ($tarifario) {
                            echo $this->Form->control('establecimiento_id',['type'=> 'select', 'options' => $establecimientos,'class' => 'form-control select2', 'disabled']);
                        } else {
                            echo $this->Form->control('establecimiento_id',['type'=> 'select', 'options' => $establecimientos,'class' => 'form-control select2']);
                        }
                    ?>
               </div>
               <div class="col-md-5 m-r-none">
                    <?php
                        if ($tarifario) {
                            echo $this->Form->control('proveedore_id',['type'=> 'select', 'label' => 'Proveedor','options' => $proveedores,'class' => 'form-control select2', 'disabled']);
                        } else {
                            echo $this->Form->control('proveedore_id',['type'=> 'select', 'label' => 'Proveedor','options' => $proveedores,'class' => 'form-control select2']);
                        }
                    ?>
               </div>
           </div>
            <br><br>
            <div class="table-responsive col-sm-12 no-margins no-padding">
                <table id="dt_ordentrabajo" class="table table-bordered table-hover table-striped contractors-table dataTable no-footer" cellspacing="0" width="100%">
                    <thead><?= $this->Html->tableHeaders(['Proyecto','Labor','Unidad','Planilla Tecnica', 'Lote','Cantidad','Moneda','Importe','','']) ?></thead>
                </table>
                <div id="chequeos-labores"></div>
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
<!-- Modal para ingreso de correo electronico -->
<div class="modal otmodal" id="ingresar_email" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header text-left">
                <div class="pull-right">
                    <?= $this->Form->button('<i class="fa fa-times"></i>', ['class' => 'btn-monitoreo btn-icon-only btn-circle' , 'data-dismiss' => "modal", 'title' => 'Cancelar','type' => 'button', 'escape' => false]) ?>
                </div>
                <h3>Enviar OT</h3>
            </div>
            <div class="modal-body">
                <?= $this->Form->create('formulario', ['id' => 'formulario']) ?>
                <fieldset>
                    <div class="row">
                        <div class="col-md-11 m-l-md">
                            <div class="m-b-md">
                                <?= $this->Form->control('email', ['class' => 'form-control', 'label' => 'Ingrese Email', 'placeholder'=>'correo@tudominio.com', 'type' => 'email', 'required']) ?>
                                <span class="m-l-xs">El correo ingresado quedará registrado para futuros envios.</span>
                            </div>
                        </div>
                        <div class="col-md-11 m-l-md">
                            <div class="m-b-md">
                                <?= $this->Form->button('Enviar OT', ['class' => "btn btn-w-m btn-primary Enviar", 'type' =>'button']) ?>                         
                            </div>
                        </div>
                    </div>
                </fieldset>
                <?= $this->Form->control('proveedor', ['type' => 'hidden', 'value' =>  $ordenTrabajo->proveedore->id]) ?>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>
<!-- fin del modal -->
<?= $lote ? $this->Form->control('lote', ['type' => 'hidden', 'value' => $lote]) : '' ?>
<?= $this->Form->control('tarifario', ['type' => 'hidden']) ?>
<?= $this->Form->control('tarifario_data', ['type' => 'hidden', 'value' => json_encode($tarifario)]) ?>
<?= $this->Form->control('id', ['class' => 'form-control', 'type' => 'hidden', 'value' => $ordenTrabajo->id ]) ?>
<?= $this->Form->end() ?>
<?php 
    echo '<script type="text/template" id="row-actions-template">
                <div class="btn-group">
                    <a class="btn btn-xs btn-info edit" href="#"><i class="fa fa-pencil"></i></a>
                    <a class="btn btn-xs btn-danger delete-link delete" href="#"><i class="fa fa-times"></i></a>
                </div>
            </script>';
    echo '<script type="text/template" id="row-actions-template-machine">
            <div class="btn-group">
                <a class="btn btn-xs btn-orden-trabajo-real btn-info font-s10 edit" href="#"><i class="fa fa-pencil"></i></a>
                <a class="btn btn-xs btn-orden-trabajo-real btn-danger delete-link font-s10 delete"  href="#"><i class="fa fa-times"></i></a>
            </div>
        </script>';
    echo '<script type="text/template" id="row-actions-insumos">
            <div class="btn-group">
                <a class="btn btn-xs btn-orden-trabajo-real btn-info edit" href="#"><i class="fa fa-pencil"></i></a>
                <a href="#" type="button" title="Agregar insumos" class="btn btn-xs btn-success btn-orden-trabajo-real agregar-insumo insumos<%= id %>"><i class="fa fa-flask"></i></a>
                <a class="btn btn-xs btn-orden-trabajo-real btn-danger delete-link delete" href="#"><i class="fa fa-times"></i></a>
            </div>
        </script>';
?>
<script type="text/template" id="details-rows-template">
    <table class="table table-bordered table-hover contractors-table-details<%= id %>" style="width:100%">
        <thead>
        <tr class="background-gray">
            <th>Producto</th>
            <th>Lote</th>
            <th>Unidad</th>
            <th>Dosis</th>
            <th>Cantidad</th>
            <th>Almacen</th>
            <th><div class="btn-group">
                    <a href="#" type="button" title="Agregar insumos" class="btn btn-xs btn-success btn-orden-trabajo-real agregar-insumo insumos<%= id %>"><i class="fa fa-flask"></i></a>
                </div>
            </th>
            <th class="hidden"></th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</script>
<?= $this->Html->script(['Ordenes.ordendetrabajo_v1.js?='.$version]) ?>
<?= $this->Html->script(['Ordenes.ordenTrabajoEmailProveedor.js']) ?>