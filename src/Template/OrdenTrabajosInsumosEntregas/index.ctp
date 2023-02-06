<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\OrdenTrabajosInsumosEntrega[]|\Cake\Collection\CollectionInterface $ordenTrabajosInsumosEntregas
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('New Orden Trabajos Insumos Entrega'), ['action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Orden Trabajos Insumos'), ['controller' => 'OrdenTrabajosInsumos', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Orden Trabajos Insumo'), ['controller' => 'OrdenTrabajosInsumos', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Productos'), ['controller' => 'Productos', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Producto'), ['controller' => 'Productos', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Unidades'), ['controller' => 'Unidades', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Unidade'), ['controller' => 'Unidades', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="ordenTrabajosInsumosEntregas index large-9 medium-8 columns content">
    <h3><?= __('Orden Trabajos Insumos Entregas') ?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('orden_trabajos_insumo_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('producto_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('unidade_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('cantidad') ?></th>
                <th scope="col"><?= $this->Paginator->sort('user_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('created') ?></th>
                <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ordenTrabajosInsumosEntregas as $ordenTrabajosInsumosEntrega): ?>
            <tr>
                <td><?= $this->Number->format($ordenTrabajosInsumosEntrega->id) ?></td>
                <td><?= $ordenTrabajosInsumosEntrega->has('orden_trabajos_insumo') ? $this->Html->link($ordenTrabajosInsumosEntrega->orden_trabajos_insumo->id, ['controller' => 'OrdenTrabajosInsumos', 'action' => 'view', $ordenTrabajosInsumosEntrega->orden_trabajos_insumo->id]) : '' ?></td>
                <td><?= $ordenTrabajosInsumosEntrega->has('producto') ? $this->Html->link($ordenTrabajosInsumosEntrega->producto->nombre, ['controller' => 'Productos', 'action' => 'view', $ordenTrabajosInsumosEntrega->producto->id]) : '' ?></td>
                <td><?= $ordenTrabajosInsumosEntrega->has('unidade') ? $this->Html->link($ordenTrabajosInsumosEntrega->unidade->id, ['controller' => 'Unidades', 'action' => 'view', $ordenTrabajosInsumosEntrega->unidade->id]) : '' ?></td>
                <td><?= $this->Number->format($ordenTrabajosInsumosEntrega->cantidad) ?></td>
                <td><?= $ordenTrabajosInsumosEntrega->has('user') ? $this->Html->link($ordenTrabajosInsumosEntrega->user->nombre, ['controller' => 'Users', 'action' => 'view', $ordenTrabajosInsumosEntrega->user->id]) : '' ?></td>
                <td><?= h($ordenTrabajosInsumosEntrega->created) ?></td>
                <td><?= h($ordenTrabajosInsumosEntrega->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $ordenTrabajosInsumosEntrega->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $ordenTrabajosInsumosEntrega->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $ordenTrabajosInsumosEntrega->id], ['confirm' => __('Are you sure you want to delete # {0}?', $ordenTrabajosInsumosEntrega->id)]) ?>
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
