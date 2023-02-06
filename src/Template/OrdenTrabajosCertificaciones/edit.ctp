<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\OrdenTrabajosCertificacione $ordenTrabajosCertificacione
 */
?>
<div class="ibox float-e-margins">
    <div class="ibox-content">
        <?= $this->Form->create($ordenTrabajosCertificacione) ?>
        <fieldset>
            <legend><?= __('Editar certificacion') ?></legend>
            <?php
                echo $this->Form->control('orden_trabajos_distribucione_id', ['options' => $ordenTrabajosDistribuciones]);
                echo $this->Form->control('orden_trabajo_id');
                echo $this->Form->control('fecha_inicio', ['type' => 'text', 'class' => 'form-control']);
                echo $this->Form->control('fecha_final', ['type' => 'text', 'class' => 'form-control']);
                echo $this->Form->control('has');
                echo $this->Form->control('precio_final');
                echo $this->Form->control('observaciones');
                echo $this->Form->control('imagenes');
                echo $this->Form->control('user_id', ['options' => $users]);
            ?>
        </fieldset>
        <?= $this->Form->button(__('Submit')) ?>
        <?= $this->Form->end() ?>        
    </div>
</div>

