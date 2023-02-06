<?php
    /**
     * @var \App\View\AppView $this
     * @var \App\Model\Entity\OrdenTrabajo $ordenTrabajo
     */
    $condicion = $ordenTrabajo->orden_trabajos_condiciones_meteorologica;
?>
<div class="row border-bottom white-bg page-heading">
    <div class="col-lg-9 col-md-9 m-t-xs">
        <h3>Orden de Trabajo Nº <?= $ordenTrabajo->id ?>&nbsp;&nbsp;<i class="fa fa-spin fa-refresh text-success" id="table-loader"></i></h3>
    </div>
    <div class="col-lg-3 col-md-3 m-t-xs">
        <div class="btn-group pull-right dt-buttons no-margins no-padding">
            <?php
                echo $this->Html->link('<i class="fa fa-plus"></i>',['controller' => 'OrdenTrabajos', 'action' => 'add'],['type' => 'button','title' => 'Crear nueva OT', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
                echo $this->Form->button('<i class="fa fa-save"></i>', ['type' => 'button','title' => 'Guardar', 'class'=>'btn btn-sm btn-default EjecutarOT', 'escape' => false]);
                echo $this->Html->link('<i class="fa fa-pencil"></i>',['controller' => 'OrdenTrabajos', 'action' => 'edit', $ordenTrabajo->id],['type' => 'button','title' => 'Editar la OT actual', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
                echo $this->Form->button('<i class="fa fa-check"></i>', ['type' => 'button','title' => 'Finalizar OT', 'class'=>'btn btn-sm btn-success certificar-ot', 'escape' => false]);
                echo $this->Html->link('<i class="fa fa-list"></i>',['controller' => 'OrdenTrabajos', 'action' => 'index'],['type' => 'button','title' => 'Bandeja de Entrada', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
                /* Enviar por mail - TODO: Pablo Snaider */
                echo $this->Form->button('<i class="fa fa-envelope-o"></i>', ['type' => 'button','title' => 'Enviar por Mail', 'id' => 'enviar-email', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
                echo $this->Html->link('<i class="fa fa-print"></i>',['controller' => 'OrdenTrabajos', 'action' => 'view', $ordenTrabajo->id, '_ext' => 'pdf'],['type' => 'button','title' => 'Imprimir la OT actual', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
            ?>
        </div>
    </div>
</div>
<?= $this->Form->control('email_proveedor', ['type' => 'hidden', 'value' =>  $ordenTrabajo->proveedore->email]) ?>
<?= $this->Form->create($ordenTrabajo, ['id' => 'ordenTrabajo']) ?>
<div class="ibox float-e-margins">
    <div class="ibox-content">
        <fieldset>
           <div class="col-md-12 no-margins no-padding">
               <div class="col-md-2 m-l-none">
                    <?php if ($ordenTrabajo['orden_trabajos_estado_id'] !== 1){
                        echo $this->Form->control('fecha',['type' => 'text','class' => 'form-control', 'value' => date_format($ordenTrabajo->fecha, 'd/m/Y'), 'disabled','escape' => false]);
                    } else {
                        echo $this->Form->control('fecha',['type' => 'text','class' => 'form-control', 'value' => date_format($ordenTrabajo->fecha, 'd/m/Y'),'escape' => false]);
                    }            
                    ?>
               </div>
               <div class="col-md-5 no-margins no-padding">
                    <?= $this->Form->control('establecimiento_id',['type'=> 'text', 'class' => 'form-control', 'readonly' => 'readonly', 'value' => $ordenTrabajo->establecimiento->nombre]) ?>
               </div>
               <div class="col-md-5 m-r-none">
                    <?= $this->Form->control('proveedore_id',['type'=> 'text', 'label' => 'Proveedor', 'class' => 'form-control', 'readonly' => 'readonly', 'value' => $ordenTrabajo->proveedore->nombre ]) ?>
               </div>
           </div>
            <br><br>
            <div class="table-responsive col-sm-12 no-margins no-padding">
                <table id="dt_ordentrabajo" class="table table-bordered table-hover table-striped contractors-table dataTable no-footer" cellspacing="0" width="100%">
                    <thead><?= $this->Html->tableHeaders(['Labor', 'U. Medida','Centro de Costo','Lote','Has Ord.','Has Certif.','Moneda','Importe Ord.','Importe Certif.','','']) ?></thead>
                </table>
            </div>
            <div class="col-md-12 no-margins no-padding">
                <div class="row">
                    <div class="col-md-5 m-l-none m-r-none">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Observaciones
                            </div>
                            <div class="panel-body p-xs">
                                <?= $this->Form->control('observaciones', ['type' => 'textarea', 'class' => 'form-control', 'label' => false ,'value' => $ordenTrabajo->observaciones, 'readonly' => 'readonly']) ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 m-r-none">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Certificaciones
                                <span class="label label-warning-light pull-right"><?= count($ordenTrabajo['orden_trabajos_certificaciones']) ?></span>
                            </div>
                            <div class="panel-body" id="cantidad-certificaciones">
                                <?php 
                                    if ($ordenTrabajo['orden_trabajos_estado_id'] !== 4)
                                        {
                                            $solicitadas = 0;
                                            foreach($ordenTrabajo['orden_trabajos_distribuciones'] as $labores){
                                                $solicitadas = $solicitadas + $labores['superficie'];
                                            }
                                            $certificadas = 0;
                                            foreach ($ordenTrabajo['orden_trabajos_certificaciones'] as $certificacion):
                                                $certificadas = $certificadas + $certificacion['has'];
                                            endforeach;
                                            $pendientes = $solicitadas - $certificadas;
                                            
                                            if ($pendientes > 0 ) {
                                                echo '<div class="ibox-content ibox-heading">';
                                                echo '<h3>Tiene '. $pendientes . ' hectareas sin certificar!</h3>';
                                                echo '</div>';
                                            }
                                        }
                                ?> 
                                <div class="table-responsive">
                                    <table class="table-certificaciones small">
                                        <tbody>
                                            <?php foreach ($ordenTrabajo['orden_trabajos_certificaciones'] as $certificacion): ?>
                                            <tr>
                                                <td class="client-avatar">
                                                    <a href="#" class="pull-left">
                                                        <?= $this->Html->image($certificacion->user['ruta_imagen'], ['alt' => 'user.jpg','title'=>$certificacion->user['nombre']]) ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <strong><?= $certificacion->user['nombre'] ?></strong> ha certificado <?= $certificacion['has'] ?> has. <br>
                                                    <small class="text-muted"><?= $certificacion['observaciones'] ?></small>
                                                </td>
                                                <td>
                                                    <small class="pull-right"><?= date_format($certificacion['fecha_final'],'d/m/Y') ?></small>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>                        
                    </div>
                    <div class="col-md-3 m-r-none">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Condiciones Meteorológicas
                            </div>
                            <div class="panel-body p-xs">
                                <div class="col-md-12 no-margins no-padding">
                                    <div class="row m-b-xs">
                                        <div class="col-md-6 no-margins no-padding">
                                            <label class="titulo-condiciones">Fecha / Hora</label>
                                        </div>
                                        <div class="col-md-6 no-margins no-padding">
                                            <input type="text" class="form-control edit-input-inline" data-mask="99/99/9999 99:99" name="cm_fecha" placeholder="" value="<?= $condicion ? $condicion->fecha->i18nFormat('dd/MM/yyyy HH:mm') : ''  ?>">
                                        </div>
                                    </div>
                                    <div class="row m-b-xs">
                                        <div class="col-md-6 no-margins no-padding">
                                            <label class="titulo-condiciones">Temp. (º)</label>
                                        </div>
                                        <div class="col-md-6 no-margins no-padding">
                                            <input type="text" class="form-control edit-input-inline" name="cm_temperatura" id="cm_temperatura" value="<?= $condicion ? $condicion->temperatura : ''  ?>">
                                        </div>
                                    </div>                        
                                    <div class="row m-b-xs">
                                        <div class="col-md-6 no-margins no-padding">
                                            <label class="titulo-condiciones">Humedad (%)</label>
                                        </div>
                                        <div class="col-md-6 no-margins no-padding">
                                            <input type="text" class="form-control edit-input-inline" name="cm_humedad" id="cm_humedad" value="<?= $condicion ? $condicion->humedad : ''  ?>">
                                        </div>
                                    </div>
                                    <div class="row m-b-xs">
                                        <div class="col-md-6 no-margins no-padding">
                                            <label class="titulo-condiciones">Viento (km/h)</label>
                                        </div>
                                        <div class="col-md-6 no-margins no-padding">
                                            <input type="text" class="form-control edit-input-inline" name="cm_viento" id="cm_viento" value="<?= $condicion ? $condicion->viento : ''  ?>">
                                        </div>
                                    </div>
                                    <div class="row m-b-xs">
                                        <div class="col-md-6 no-margins no-padding">
                                            <label class="titulo-condiciones">Dirección</label>
                                        </div>
                                        <div class="col-md-6 no-margins no-padding">
                                            <input type="text" class="form-control edit-input-inline" name="cm_direccion" id="cm_direccion" value="<?= $condicion ? $condicion->direccion : ''  ?>">
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
                                <?= $this->Form->control('orden_trabajos_estado_id',['type'=>'text','label' => false, 'class' => 'form-control', 'value'=> $ordenTrabajo['orden_trabajos_estado']['nombre'] ,'disabled']) ?>
                            </div>
                            <div class="col-md-2 no-margins pull-right">
                                <?php
                                    if ($ordenTrabajo['orden_trabajos_estado_id'] == '3') {
                                        if($permiteFinalizar != 0){
                                            echo $this->Form->button('<i class="fa fa-check"></i>&nbsp;&nbsp;<span class="bold">Finalizar OT</span>', ['id' => 'finalizar-ot','type' => 'button', 'title' => 'Certificar la OT actual', 'class' => 'btn btn-block btn-success certificar-ot m-t-xs m-l-xs', 'escape' => false]);
                                        } else {
                                            echo $this->Form->button('<i class="fa fa-check"></i>&nbsp;&nbsp;<span class="bold">Finalizar OT</span>', ['id' => 'finalizar-ot','type' => 'button', 'title' => 'Certificar la OT actual', 'disabled' => 'disabled','class' => 'btn btn-block btn-success certificar-ot m-t-xs m-l-xs', 'escape' => false]);
                                        }                                                
                                    }
                                ?>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>            
 
        </fieldset>
    </div>
</div>
<?= $this->Form->control('id',['class'=>'form-control', 'value'=> $ordenTrabajo->id, 'type' => 'hidden' ]) ?>
<?= $this->Form->control('monedas',['value'=> json_encode($monedas), 'type' => 'hidden' ]) ?>
<?= $this->Form->end() ?>

<script type="text/template" id="row-actions-insumos">
    <div class="btn-group">
        <a class="btn btn-xs btn-orden-trabajo btn-white text-success CertificarLabores" href="#"><i class="fa fa-check" title="Editar la Certificación"></i></a>
    </div>
</script>

<script type="text/template" id="row-actions-template">
    <div class="btn-group">
        <a class="btn btn-xs btn-orden-trabajo btn-white edit" href="#"><i class="fa fa-pencil"></i></a>
        <a class="btn btn-xs btn-orden-trabajo btn-white delete-link delete" href="#"><i class="fa fa-times"></i></a>
    </div>
</script>

<script type="text/template" id="details-rows-template">
    <table class="table table-bordered table-hover contractors-table contractors-table-details<%= idContractor %>" style="width:100%">
        <thead>
        <tr>
            <th>Producto</th>
            <th>Unidad</th>
            <th>Dosis</th>
            <th>Ordenado</th>
            <th>Entrega</th>
            <th>Devolución</th>
            <th>Aplicado</th>
            <th>Dosis Real</th>
            <th>Almacen</th>
            <th class="hidden"></th>
            <th class="hidden"></th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</script>

<!-- Modal Labor -->
<form id="ModalLabores">
    <div class="modal otmodal" id="CertificarLabor" tabindex="-1" role="dialog"  aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content animated fadeIn">
                <div class="modal-header text-left">
                    <h3 class="modal-title"><span id="titulo-modal"></span> <small class="pull-right" style="font-size: 45%;" id="sub-titulo-modal"></small></h3>
                    <div>
                        <h3><span id="labor"></span></h3>
                    </div>                    
                </div>
                <div class="modal-body h-300">
                    <fieldset>
                        <div class="row col-md-12 m-t-xs">
                            <div class="row col-md-12">
                                <span class="pull-right"><a class="btn btn-xs btn-orden-trabajo btn-white crear-certificacion" href="#"><i class="fa fa-plus"></i></a></span>
                                <span>Certificaciones</span>
                            </div>
                            <div class="table-responsive col-sm-12">
                                <table id="dt_certificaciones" class="table table-bordered table-hover table-striped certificacion-table dataTable no-footer" cellspacing="0" width="100%">
                                    <thead><?= $this->Html->tableHeaders(['Fecha labor', 'Cantidad','Tarifa','Moneda','TC','Precio Final','Observaciones', '']) ?></thead>
                                </table>
                            </div>
                            <?= $this->Form->control('distribucion-id', ['type' => 'hidden', 'label' => 'Ordenado', 'class' => 'form-control']) ?>
                            <?= $this->Form->control('fecha-inicio', ['type' => 'hidden', 'label' => 'Ordenado', 'class' => 'form-control', 'value' => date_format($ordenTrabajo->fecha, 'd/m/Y')]) ?>
                            <?= $this->Form->control('orden-trabajo', ['type' => 'hidden', 'label' => 'Ordenado', 'class' => 'form-control', 'value' => $ordenTrabajo->id]) ?>
                            <?= $this->Form->control('ordenado', ['type' => 'hidden']) ?>
                        </div>
                    </fieldset>
                    <div class="alert certificacion-alquiler hidden">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                        <span id="alquiler"></span>
                    </div>
                    <div class="alert certificacion-notificacion hidden">
                        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
                        <span id="certificacion"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="row col-md-12 no-margins no-padding">
                        <div class="col-md-5 no-margins no-padding">
                            <button type="button" class="btn btn-primary btn-block" data-dismiss="modal">Cerrar</button>
                        </div>
                        <div class="col-md-2"></div>
                        <div class="col-md-5 no-margins no-padding">
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

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

<?= $this->Html->script(['plugins/jasny/jasny-bootstrap.min', 'plugins/datapicker/bootstrap-datepicker', 'Ordenes.ordentrabajocertificar_V1.js?='.$version]) ?>
<?= $this->Html->script(['Ordenes.ordenTrabajoEmailProveedor.js']) ?>
