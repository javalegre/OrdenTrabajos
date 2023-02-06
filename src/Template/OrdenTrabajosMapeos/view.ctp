<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $ordenTrabajosMapeo
 */
?>
<div class="modal-header text-left">
    <div class="pull-right">
        <?= $this->Form->button('<i class="fa fa-times"></i>', ['class' => 'btn-icon-only btn-circle' , 'data-dismiss' => "modal", 'title' => 'Cancelar','type' => 'button', 'escape' => false]) ?>
    </div>
    <h3 class="">Ver Mapeo </h3>
</div>
<div class="row m-l-sm m-r-sm m-t-xs ">
    <table class="table table-bordered table-hover table-striped dataTable no-footer">
        <thead><?= $this->Html->tableHeaders(['OT', 'Campaña', 'Establecimiento', 'Lote', 'Cultivo']) ?></thead>
        <tbody>
            <tr>
                <td class="text-center"><?= $ordenTrabajosMapeo->orden_trabajo->id ?> </td>
                <td class="text-center"><?= $ordenTrabajosMapeo->orden_trabajos_distribucione->proyecto->campania_monitoreo->nombre ?> </td> 
                <td class="text-center"><?= $ordenTrabajosMapeo->orden_trabajo->establecimiento->nombre ?> </td> 
                <td class="text-center"><?= $ordenTrabajosMapeo->lote->nombre ?> </td>
                <td class="text-center"><?= $ordenTrabajosMapeo->orden_trabajos_distribucione->proyecto->cultivo ?> </td>
            </tr>
        </tbody>
    </table>
</div>
<div class="modal-body-users p-xs">   
    <fieldset class="ps-3 pe-3">
        <div class="col-md-12 ">
            <?= $this->Form->control('mapeos_campanias_tipo_id', ['type' => 'text', 'label' => 'Tipo Campaña', 'class' => 'form-control ', 'readonly' => 'readonly', 'value' => $ordenTrabajosMapeo->mapeos_campanias_tipo->nombre ]) ?>
        </div>
        <div class="col-md-12 m-t-xs">
            <?= $this->Form->control('mapeos_calidade_id', ['type' => 'text', 'label' => 'Calidad Mapeo', 'class' => 'form-control', 'readonly' => 'readonly', 'value' => $ordenTrabajosMapeo->mapeos_calidade->nombre ]) ?>
        </div>
        <div class="col-md-12 m-t-xs">
            <?= $this->Form->control('mapeos_problema_id', ['type' => 'text', 'label' => 'Problema', 'class' => 'form-control ', 'readonly' => 'readonly', 'value' => $ordenTrabajosMapeo->mapeos_problema->nombre ]) ?>
        </div>
        <div class="col-md-12 m-t-xs">
            <?= $this->Form->control('comentario', ['class' => 'form-control', 'type' => 'text', 'label' => 'Comentario', 'readonly' => 'readonly', 'value' => $ordenTrabajosMapeo->comentario]) ?>
        </div>
        <div class="col-md-12 m-t-xs">
            <?= $this->Form->control('user_id', ['type' => 'text', 'label' => 'Procesado Por', 'class' => 'form-control', 'readonly' => 'readonly', 'value' => $ordenTrabajosMapeo->user->nombre ]) ?>
        </div>
        
        <div class="col-md-12 m-t-xs">
            <div class="col-md-4 m-t-xs">
                <?= $this->Form->control('sms',['label' => '  SMS', 'type' => 'checkbox', 'class' => 'm-t-md' , 'checked' => $ordenTrabajosMapeo->sms ]) ?>
            </div>
            <div class="col-md-4 m-t-xs">
                <?= $this->Form->control('pdf',['label' => '  PDF', 'type' => 'checkbox', 'class' => 'm-t-md' , 'checked' => $ordenTrabajosMapeo->pdf ]) ?>
            </div>    
        </div>     
    </fieldset>
</div>