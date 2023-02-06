<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface[]|\Cake\Collection\CollectionInterface $ordenTrabajosReclasificacionesDetalles
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('New Orden Trabajos Reclasificaciones Detalle'), ['action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Orden Trabajos Reclasificaciones'), ['controller' => 'OrdenTrabajosReclasificaciones', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Orden Trabajos Reclasificacione'), ['controller' => 'OrdenTrabajosReclasificaciones', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Orden Trabajos'), ['controller' => 'OrdenTrabajos', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Orden Trabajo'), ['controller' => 'OrdenTrabajos', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Proyectos'), ['controller' => 'Proyectos', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Proyecto'), ['controller' => 'Proyectos', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="ordenTrabajosReclasificacionesDetalles index large-9 medium-8 columns content">
    <h3><?= __('Orden Trabajos Reclasificaciones Detalles') ?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('orden_trabajos_reclasificacione_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('orden_trabajo_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('orden_trabajo_distribucione_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('proyecto_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('created') ?></th>
                <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
                <th scope="col"><?= $this->Paginator->sort('deleted') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ordenTrabajosReclasificacionesDetalles as $ordenTrabajosReclasificacionesDetalle): ?>
            <tr>
                <td><?= $this->Number->format($ordenTrabajosReclasificacionesDetalle->id) ?></td>
                <td><?= $ordenTrabajosReclasificacionesDetalle->has('orden_trabajos_reclasificacione') ? $this->Html->link($ordenTrabajosReclasificacionesDetalle->orden_trabajos_reclasificacione->id, ['controller' => 'OrdenTrabajosReclasificaciones', 'action' => 'view', $ordenTrabajosReclasificacionesDetalle->orden_trabajos_reclasificacione->id]) : '' ?></td>
                <td><?= $ordenTrabajosReclasificacionesDetalle->has('orden_trabajo') ? $this->Html->link($ordenTrabajosReclasificacionesDetalle->orden_trabajo->id, ['controller' => 'OrdenTrabajos', 'action' => 'view', $ordenTrabajosReclasificacionesDetalle->orden_trabajo->id]) : '' ?></td>
                <td><?= $this->Number->format($ordenTrabajosReclasificacionesDetalle->orden_trabajo_distribucione_id) ?></td>
                <td><?= $ordenTrabajosReclasificacionesDetalle->has('proyecto') ? $this->Html->link($ordenTrabajosReclasificacionesDetalle->proyecto->id, ['controller' => 'Proyectos', 'action' => 'view', $ordenTrabajosReclasificacionesDetalle->proyecto->id]) : '' ?></td>
                <td><?= h($ordenTrabajosReclasificacionesDetalle->created) ?></td>
                <td><?= h($ordenTrabajosReclasificacionesDetalle->modified) ?></td>
                <td><?= h($ordenTrabajosReclasificacionesDetalle->deleted) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $ordenTrabajosReclasificacionesDetalle->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $ordenTrabajosReclasificacionesDetalle->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $ordenTrabajosReclasificacionesDetalle->id], ['confirm' => __('Are you sure you want to delete # {0}?', $ordenTrabajosReclasificacionesDetalle->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(['format' => __('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')]) ?></p>
    </div>
</div>
