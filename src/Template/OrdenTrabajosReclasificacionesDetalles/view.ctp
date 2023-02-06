<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $ordenTrabajosReclasificacionesDetalle
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Orden Trabajos Reclasificaciones Detalle'), ['action' => 'edit', $ordenTrabajosReclasificacionesDetalle->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Orden Trabajos Reclasificaciones Detalle'), ['action' => 'delete', $ordenTrabajosReclasificacionesDetalle->id], ['confirm' => __('Are you sure you want to delete # {0}?', $ordenTrabajosReclasificacionesDetalle->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Orden Trabajos Reclasificaciones Detalles'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Orden Trabajos Reclasificaciones Detalle'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Orden Trabajos Reclasificaciones'), ['controller' => 'OrdenTrabajosReclasificaciones', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Orden Trabajos Reclasificacione'), ['controller' => 'OrdenTrabajosReclasificaciones', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Orden Trabajos'), ['controller' => 'OrdenTrabajos', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Orden Trabajo'), ['controller' => 'OrdenTrabajos', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Proyectos'), ['controller' => 'Proyectos', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Proyecto'), ['controller' => 'Proyectos', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="ordenTrabajosReclasificacionesDetalles view large-9 medium-8 columns content">
    <h3><?= h($ordenTrabajosReclasificacionesDetalle->id) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Orden Trabajos Reclasificacione') ?></th>
            <td><?= $ordenTrabajosReclasificacionesDetalle->has('orden_trabajos_reclasificacione') ? $this->Html->link($ordenTrabajosReclasificacionesDetalle->orden_trabajos_reclasificacione->id, ['controller' => 'OrdenTrabajosReclasificaciones', 'action' => 'view', $ordenTrabajosReclasificacionesDetalle->orden_trabajos_reclasificacione->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Orden Trabajo') ?></th>
            <td><?= $ordenTrabajosReclasificacionesDetalle->has('orden_trabajo') ? $this->Html->link($ordenTrabajosReclasificacionesDetalle->orden_trabajo->id, ['controller' => 'OrdenTrabajos', 'action' => 'view', $ordenTrabajosReclasificacionesDetalle->orden_trabajo->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Proyecto') ?></th>
            <td><?= $ordenTrabajosReclasificacionesDetalle->has('proyecto') ? $this->Html->link($ordenTrabajosReclasificacionesDetalle->proyecto->id, ['controller' => 'Proyectos', 'action' => 'view', $ordenTrabajosReclasificacionesDetalle->proyecto->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($ordenTrabajosReclasificacionesDetalle->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Orden Trabajo Distribucione Id') ?></th>
            <td><?= $this->Number->format($ordenTrabajosReclasificacionesDetalle->orden_trabajo_distribucione_id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($ordenTrabajosReclasificacionesDetalle->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($ordenTrabajosReclasificacionesDetalle->modified) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Deleted') ?></th>
            <td><?= h($ordenTrabajosReclasificacionesDetalle->deleted) ?></td>
        </tr>
    </table>
</div>
