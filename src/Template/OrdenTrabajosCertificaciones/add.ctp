<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\OrdenTrabajosCertificacione $ordenTrabajosCertificacione
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('List Orden Trabajos Certificaciones'), ['action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('List Orden Trabajos Distribuciones'), ['controller' => 'OrdenTrabajosDistribuciones', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Orden Trabajos Distribucione'), ['controller' => 'OrdenTrabajosDistribuciones', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="ordenTrabajosCertificaciones form large-9 medium-8 columns content">
    <?= $this->Form->create($ordenTrabajosCertificacione) ?>
    <fieldset>
        <legend><?= __('Add Orden Trabajos Certificacione') ?></legend>
        <?php
            echo $this->Form->control('orden_trabajos_distribucione_id', ['options' => $ordenTrabajosDistribuciones]);
            echo $this->Form->control('orden_trabajo_id');
            echo $this->Form->control('fecha_inicio', ['empty' => true]);
            echo $this->Form->control('fecha_final', ['empty' => true]);
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
