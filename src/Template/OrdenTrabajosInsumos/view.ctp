<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\OrdenTrabajosInsumo $ordenTrabajosInsumo
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Orden Trabajos Insumo'), ['action' => 'edit', $ordenTrabajosInsumo->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Orden Trabajos Insumo'), ['action' => 'delete', $ordenTrabajosInsumo->id], ['confirm' => __('Are you sure you want to delete # {0}?', $ordenTrabajosInsumo->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Orden Trabajos Insumos'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Orden Trabajos Insumo'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Orden Trabajos'), ['controller' => 'OrdenTrabajos', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Orden Trabajo'), ['controller' => 'OrdenTrabajos', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Orden Trabajos Distribuciones'), ['controller' => 'OrdenTrabajosDistribuciones', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Orden Trabajos Distribucione'), ['controller' => 'OrdenTrabajosDistribuciones', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Productos'), ['controller' => 'Productos', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Producto'), ['controller' => 'Productos', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Unidades'), ['controller' => 'Unidades', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Unidade'), ['controller' => 'Unidades', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Almacenes'), ['controller' => 'Almacenes', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Almacene'), ['controller' => 'Almacenes', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="ordenTrabajosInsumos view large-9 medium-8 columns content">
    <h3><?= h($ordenTrabajosInsumo->id) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Orden Trabajo') ?></th>
            <td><?= $ordenTrabajosInsumo->has('orden_trabajo') ? $this->Html->link($ordenTrabajosInsumo->orden_trabajo->id, ['controller' => 'OrdenTrabajos', 'action' => 'view', $ordenTrabajosInsumo->orden_trabajo->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Orden Trabajos Distribucione') ?></th>
            <td><?= $ordenTrabajosInsumo->has('orden_trabajos_distribucione') ? $this->Html->link($ordenTrabajosInsumo->orden_trabajos_distribucione->id, ['controller' => 'OrdenTrabajosDistribuciones', 'action' => 'view', $ordenTrabajosInsumo->orden_trabajos_distribucione->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Producto') ?></th>
            <td><?= $ordenTrabajosInsumo->has('producto') ? $this->Html->link($ordenTrabajosInsumo->producto->id, ['controller' => 'Productos', 'action' => 'view', $ordenTrabajosInsumo->producto->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Unidade') ?></th>
            <td><?= $ordenTrabajosInsumo->has('unidade') ? $this->Html->link($ordenTrabajosInsumo->unidade->id, ['controller' => 'Unidades', 'action' => 'view', $ordenTrabajosInsumo->unidade->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Almacene') ?></th>
            <td><?= $ordenTrabajosInsumo->has('almacene') ? $this->Html->link($ordenTrabajosInsumo->almacene->id, ['controller' => 'Almacenes', 'action' => 'view', $ordenTrabajosInsumo->almacene->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($ordenTrabajosInsumo->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Dosis') ?></th>
            <td><?= $this->Number->format($ordenTrabajosInsumo->dosis) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Cantidad') ?></th>
            <td><?= $this->Number->format($ordenTrabajosInsumo->cantidad) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Cantidad Stock') ?></th>
            <td><?= $this->Number->format($ordenTrabajosInsumo->cantidad_stock) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($ordenTrabajosInsumo->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($ordenTrabajosInsumo->modified) ?></td>
        </tr>
    </table>
</div>
