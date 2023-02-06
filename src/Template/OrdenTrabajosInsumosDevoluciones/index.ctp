<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\OrdenTrabajosInsumosDevolucione[]|\Cake\Collection\CollectionInterface $ordenTrabajosInsumosDevoluciones
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('New Orden Trabajos Insumos Devolucione'), ['action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Orden Trabajos Insumos'), ['controller' => 'OrdenTrabajosInsumos', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Orden Trabajos Insumo'), ['controller' => 'OrdenTrabajosInsumos', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Productos'), ['controller' => 'Productos', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Producto'), ['controller' => 'Productos', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="ordenTrabajosInsumosDevoluciones index large-9 medium-8 columns content">
    <h3><?= __('Orden Trabajos Insumos Devoluciones') ?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('fecha') ?></th>
                <th scope="col"><?= $this->Paginator->sort('orden_trabajos_insumo_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('producto_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('cantidad') ?></th>
                <th scope="col"><?= $this->Paginator->sort('user_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('created') ?></th>
                <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ordenTrabajosInsumosDevoluciones as $ordenTrabajosInsumosDevolucione): ?>
            <tr>
                <td><?= $this->Number->format($ordenTrabajosInsumosDevolucione->id) ?></td>
                <td><?= h($ordenTrabajosInsumosDevolucione->fecha) ?></td>
                <td><?= $ordenTrabajosInsumosDevolucione->has('orden_trabajos_insumo') ? $this->Html->link($ordenTrabajosInsumosDevolucione->orden_trabajos_insumo->id, ['controller' => 'OrdenTrabajosInsumos', 'action' => 'view', $ordenTrabajosInsumosDevolucione->orden_trabajos_insumo->id]) : '' ?></td>
                <td><?= $ordenTrabajosInsumosDevolucione->has('producto') ? $this->Html->link($ordenTrabajosInsumosDevolucione->producto->nombre, ['controller' => 'Productos', 'action' => 'view', $ordenTrabajosInsumosDevolucione->producto->id]) : '' ?></td>
                <td><?= $this->Number->format($ordenTrabajosInsumosDevolucione->cantidad) ?></td>
                <td><?= $ordenTrabajosInsumosDevolucione->has('user') ? $this->Html->link($ordenTrabajosInsumosDevolucione->user->nombre, ['controller' => 'Users', 'action' => 'view', $ordenTrabajosInsumosDevolucione->user->id]) : '' ?></td>
                <td><?= h($ordenTrabajosInsumosDevolucione->created) ?></td>
                <td><?= h($ordenTrabajosInsumosDevolucione->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $ordenTrabajosInsumosDevolucione->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $ordenTrabajosInsumosDevolucione->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $ordenTrabajosInsumosDevolucione->id], ['confirm' => __('Are you sure you want to delete # {0}?', $ordenTrabajosInsumosDevolucione->id)]) ?>
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
