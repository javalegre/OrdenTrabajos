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
    <div class="col-lg-3 col-md-3 m-t-xs pull-right">
        <div class="btn-group dt-buttons pull-right no-margins no-padding">
            <?php
                echo $this->Html->link('<i class="fa fa-plus"></i>',['controller' => 'OrdenTrabajos', 'action' => 'add'],['type' => 'button','title' => 'Crear nueva OT', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
                echo $this->Form->button('<i class="fa fa-save"></i>', ['type' => 'button','title' => 'Guardar', 'class'=>'btn btn-sm btn-default EjecutarOT', 'escape' => false]);
                echo $this->Form->button('<i class="fa fa-check"></i>', ['type' => 'button','title' => 'Finalizar la Certificación', 'class'=>'btn btn-sm btn-success certificar-ot', 'escape' => false]);
                echo $this->Form->button('<i class="fa fa-copy"></i>', ['type' => 'button','title' => 'Generar copia', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
                echo $this->Html->link('<i class="fa fa-list"></i>',['controller' => 'OrdenTrabajos', 'action' => 'index'],['type' => 'button','title' => 'Bandeja de Entrada', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
                echo $this->Form->button('<i class="fa fa-envelope-o"></i>', ['type' => 'button','title' => 'Enviar por Mail', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
                echo $this->Form->button('<i class="fa fa-print"></i>', ['type' => 'button','title' => 'Imprimir', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
            ?>
        </div>
    </div>
</div>
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
                <table id="dt_ordentrabajo" class="table table-bordered table-primary table-striped-b contractors-table dataTable no-footer" cellspacing="0" width="100%">
                    <thead><?= $this->Html->tableHeaders(['','Labor', 'U. Medida','Centro de Costo','Lote','Has Ord.','Has Certif.','Moneda','Importe Ord.','Importe Certif.','','']) ?></thead>
                </table>
            </div>
            <br><br>
            
            <div class="table-responsive col-sm-12 no-margins no-padding">
                <table id="dt_insumos" class="table-detalle-insumos no-footer hidden" cellspacing="0" width="100%">
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
                            <th class="hidden"></th>
                            <th class="hidden"></th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
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
                                                </td>
                                                <td>
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
                </div>
            </div> 

            <div class="col-md-12 no-margins no-padding">
                <div class="panel panel-default">
                    <div class="panel-body no-margins no-padding">
                        <div class="col-md-12 no-margins no-padding">
                            <div class="col-md-2 m-l-xs no-padding">
                                <?= $this->Form->control('orden_trabajos_estado_id',['type'=>'text','label' => false, 'class' => 'form-control', 'value'=> $ordenTrabajo['orden_trabajos_estado']['nombre'] ,'disabled']) ?>
                            </div>
                            <div class="col-md-4 no-margins pull-right">
                                <div class="col-md-6 no-margins">
                                    <?php echo $this->Form->button('<span class="bold">Distribuir Insumos</span>', ['id' => 'distribuir-insumos','type' => 'button', 'class' => 'btn btn-block btn-success m-t-xs m-l-xs hidden', 'escape' => false]); ?>
                                </div>
                                <div class="col-md-6 no-margins no-padding">
                                    <?php
                                        switch ($ordenTrabajo['orden_trabajos_estado_id']){
                                            case 3: /* Aprobado, muestro certificar */
                                                if($permiteFinalizar != 0){
                                                    echo $this->Form->button('<i class="fa fa-check"></i>&nbsp;&nbsp;<span class="bold">Finalizar OT</span>', ['id' => 'finalizar-ot','type' => 'button', 'title' => 'Certificar la OT actual', 'class' => 'btn btn-block btn-success certificar-ot m-t-xs m-l-xs', 'escape' => false]);
                                                } else {
                                                    echo $this->Form->button('<i class="fa fa-check"></i>&nbsp;&nbsp;<span class="bold">Finalizar OT</span>', ['id' => 'finalizar-ot','type' => 'button', 'title' => 'Certificar la OT actual', 'disabled' => 'disabled','class' => 'btn btn-block btn-success certificar-ot m-t-xs m-l-xs', 'escape' => false]);
                                                }
                                                break;
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>            
 
        </fieldset>
    </div>
</div>
<?= $this->Form->control('id',['class'=>'form-control', 'value'=> $ordenTrabajo->id, 'type' => 'hidden' ]) ?>
<?= $this->Form->control('siembra',['type' => 'hidden']) ?>
<?= $this->Form->control('monedas',['value'=> json_encode($monedas), 'type' => 'hidden' ]) ?>
<?= $this->Form->end() ?>

<script type="text/template" id="row-actions-insumos">
    <div class="btn-group">
        <a class="btn btn-xs btn-orden-trabajo text-success CertificarLabores" href="#"><i class="fa fa-check"></i></a>  
    </div>
</script>
<script type="text/template" id="row-actions-template-machine">
    <div class="btn-group">
        <a class="btn btn-xs btn-orden-trabajo font-s10 edit" href="#"><i class="fa fa-bars"></i></a>
    </div>
</script>
<script type="text/template" id="row-actions-template">
    <div class="btn-group">
        <a class="btn btn-xs btn-orden-trabajo btn-white edit" href="#"><i class="fa fa-pencil"></i></a>
        <a class="btn btn-xs btn-orden-trabajo btn-white delete-link delete" href="#"><i class="fa fa-times"></i></a>
    </div>
</script>
<script type="text/template" id="details-rows-template">
    <table class="table-detalle-insumos contractors-table-details<%= idContractor %>" style="width:95%">
        <thead>
        <tr>
            <th>Producto</th>
            <th>Lote</th>
            <th>Unidad</th>
            <th>Dosis</th>
            <th>Cantidad</th>
            <th>Entrega</th>
            <th>Devolucion</th>
            <th>Aplicado</th>
            <th>Almacen</th>
            <th></th>
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
<?= $this->Html->script(['plugins/datapicker/bootstrap-datepicker','Ordenes.ordentrabajocertificarsiembra']) ?>