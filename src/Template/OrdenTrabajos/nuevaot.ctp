<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\OrdenTrabajo $ordenTrabajo
 */
?>
    <?= $this->Form->create($ordenTrabajo) ?>
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="tabs-container" style="padding-top: 15px;">
                <ul class="nav nav-tabs">
                     <li class="active"><a data-toggle="tab"> Orden de Trabajo</a></li>
                 </ul>
                 <div class="tab-content">
                     <div id="tab-11" class="tab-pane active">
                         <div class="panel-body">
                             <!-- Botonera -->
                            <div class="ibox-tools">
                                <?= $this->Form->button('<i class="fa fa-save"></i> Generar Orden de Trabajo',['class'=>'btn btn-primary btn-sm']) ?>
                            </div>
                             <fieldset class="form-horizontal">
                                 <div class="form-group">
                                     <label class="col-sm-2 control-label">Fecha:</label>
                                     <div class="col-sm-3">
                                         <div class="input-group">
                                             <span class="input-group-btn">
                                                 <button class="btn btn-primary" type="button"><i class="fa fa-calendar"></i></button>
                                             </span>
                                             <input id="fecha" name="fecha" type="text" class="form-control">
                                         </div>
                                     </div>
                                 </div>
                                 <div class="form-group"><label class="col-sm-2 control-label">Establecimiento:</label>
                                     <div class="col-sm-10">
                                         <div class="input-group">
                                             <span class="input-group-btn">
                                                 <button class="btn btn-primary" type="button"><i class="fa fa-search"></i></button>
                                             </span>
                                             <?= $this->Form->control('establecimiento_id', ['options' => $establecimientos,'label'=>false,'class'=>'form-control select2']) ?>
                                         </div>
                                     </div>
                                 </div>
                                 <div class="form-group"><label class="col-sm-2 control-label">Proveedor:</label>
                                     <div class="col-sm-10">
                                         <div class="input-group">
                                             <span class="input-group-btn">
                                                 <button class="btn btn-primary" type="button"><i class="fa fa-search"></i></button>
                                             </span>
                                             <?= $this->Form->control('proveedore_id', ['options' => $proveedores,'label'=>false,'class'=>'form-control select2']) ?>
                                         </div>
                                     </div>
                                 </div>
                                 <br/>                                

                                 <?php
                                     echo $this->Form->control('estado', ['value' => 0, 'type'=>'hidden']);
                                     echo $this->Form->control('descripcion', ['value' => 0, 'type'=>'hidden']);
                                     echo $this->Form->control('velocidadviento', ['value' => 0, 'type'=>'hidden']);
                                     echo $this->Form->control('temperatura', ['value' => 0, 'type'=>'hidden']);
                                     echo $this->Form->control('consumogasoil', ['value' => 0, 'type'=>'hidden']);
                                     echo $this->Form->control('humedad', ['value' => 0, 'type'=>'hidden']);
                         //            echo $this->Form->control('establecimiento_id', ['options' => $establecimientos, 'empty' => true],'value'=>0);
                         //            echo $this->Form->control('proveedore_id', ['options' => $proveedores, 'empty' => true]);
                                     echo $this->Form->control('labores._ids', ['options' => $labores, 'value' => 0,'type'=>'hidden']);
                                 ?>
                             </fieldset>
                         </div>
                     </div>
                 </div>
            </div>
        </div>
    </div>
    <?= $this->Form->end() ?>

<?= $this->Html->script(['plugins/datapicker/bootstrap-datepicker']) ?>
<script>
    $(document).ready(function() {
        
       /* Armo todas las fechas */
        $("#fecha").val(moment().format('DD/MM/YYYY'));
        $(".select2").select2();
       
    });     
    
</script>