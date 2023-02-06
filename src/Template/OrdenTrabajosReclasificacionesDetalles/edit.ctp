<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $ordenTrabajosReclasificacionesDetalle
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $ordenTrabajosReclasificacionesDetalle->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $ordenTrabajosReclasificacionesDetalle->id)]
            )
        ?></li>
        <li><?= $this->Html->link(__('List Orden Trabajos Reclasificaciones Detalles'), ['action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('List Orden Trabajos Reclasificaciones'), ['controller' => 'OrdenTrabajosReclasificaciones', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Orden Trabajos Reclasificacione'), ['controller' => 'OrdenTrabajosReclasificaciones', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Orden Trabajos'), ['controller' => 'OrdenTrabajos', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Orden Trabajo'), ['controller' => 'OrdenTrabajos', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Proyectos'), ['controller' => 'Proyectos', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Proyecto'), ['controller' => 'Proyectos', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="ordenTrabajosReclasificacionesDetalles form large-9 medium-8 columns content">
    <?= $this->Form->create($ordenTrabajosReclasificacionesDetalle) ?>
    <fieldset>
        <legend><?= __('Edit Orden Trabajos Reclasificaciones Detalle') ?></legend>
        <?php
            echo $this->Form->control('orden_trabajos_reclasificacione_id', ['options' => $ordenTrabajosReclasificaciones, 'empty' => true]);
            echo $this->Form->control('orden_trabajo_id', ['options' => $ordenTrabajos, 'empty' => true]);
            echo $this->Form->control('orden_trabajo_distribucione_id');
            echo $this->Form->control('proyecto_id', ['options' => $proyectos, 'empty' => true]);
            echo $this->Form->control('deleted', ['empty' => true]);
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
