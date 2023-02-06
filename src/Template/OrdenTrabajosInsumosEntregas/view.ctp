<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\OrdenTrabajosInsumosEntrega $ordenTrabajosInsumosEntrega
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Orden Trabajos Insumos Entrega'), ['action' => 'edit', $ordenTrabajosInsumosEntrega->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Orden Trabajos Insumos Entrega'), ['action' => 'delete', $ordenTrabajosInsumosEntrega->id], ['confirm' => __('Are you sure you want to delete # {0}?', $ordenTrabajosInsumosEntrega->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Orden Trabajos Insumos Entregas'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Orden Trabajos Insumos Entrega'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Orden Trabajos Insumos'), ['controller' => 'OrdenTrabajosInsumos', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Orden Trabajos Insumo'), ['controller' => 'OrdenTrabajosInsumos', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Productos'), ['controller' => 'Productos', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Producto'), ['controller' => 'Productos', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Unidades'), ['controller' => 'Unidades', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Unidade'), ['controller' => 'Unidades', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="ordenTrabajosInsumosEntregas view large-9 medium-8 columns content">
    <h3><?= h($ordenTrabajosInsumosEntrega->id) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Orden Trabajos Insumo') ?></th>
            <td><?= $ordenTrabajosInsumosEntrega->has('orden_trabajos_insumo') ? $this->Html->link($ordenTrabajosInsumosEntrega->orden_trabajos_insumo->id, ['controller' => 'OrdenTrabajosInsumos', 'action' => 'view', $ordenTrabajosInsumosEntrega->orden_trabajos_insumo->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Producto') ?></th>
            <td><?= $ordenTrabajosInsumosEntrega->has('producto') ? $this->Html->link($ordenTrabajosInsumosEntrega->producto->nombre, ['controller' => 'Productos', 'action' => 'view', $ordenTrabajosInsumosEntrega->producto->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Unidade') ?></th>
            <td><?= $ordenTrabajosInsumosEntrega->has('unidade') ? $this->Html->link($ordenTrabajosInsumosEntrega->unidade->id, ['controller' => 'Unidades', 'action' => 'view', $ordenTrabajosInsumosEntrega->unidade->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('User') ?></th>
            <td><?= $ordenTrabajosInsumosEntrega->has('user') ? $this->Html->link($ordenTrabajosInsumosEntrega->user->nombre, ['controller' => 'Users', 'action' => 'view', $ordenTrabajosInsumosEntrega->user->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($ordenTrabajosInsumosEntrega->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Cantidad') ?></th>
            <td><?= $this->Number->format($ordenTrabajosInsumosEntrega->cantidad) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($ordenTrabajosInsumosEntrega->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($ordenTrabajosInsumosEntrega->modified) ?></td>
        </tr>
    </table>
    <div class="row">
        <h4><?= __('Observaciones') ?></h4>
        <?= $this->Text->autoParagraph(h($ordenTrabajosInsumosEntrega->observaciones)); ?>
    </div>
</div>
