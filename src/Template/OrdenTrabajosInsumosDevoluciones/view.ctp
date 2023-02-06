<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\OrdenTrabajosInsumosDevolucione $ordenTrabajosInsumosDevolucione
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Orden Trabajos Insumos Devolucione'), ['action' => 'edit', $ordenTrabajosInsumosDevolucione->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Orden Trabajos Insumos Devolucione'), ['action' => 'delete', $ordenTrabajosInsumosDevolucione->id], ['confirm' => __('Are you sure you want to delete # {0}?', $ordenTrabajosInsumosDevolucione->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Orden Trabajos Insumos Devoluciones'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Orden Trabajos Insumos Devolucione'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Orden Trabajos Insumos'), ['controller' => 'OrdenTrabajosInsumos', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Orden Trabajos Insumo'), ['controller' => 'OrdenTrabajosInsumos', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Productos'), ['controller' => 'Productos', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Producto'), ['controller' => 'Productos', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="ordenTrabajosInsumosDevoluciones view large-9 medium-8 columns content">
    <h3><?= h($ordenTrabajosInsumosDevolucione->id) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Orden Trabajos Insumo') ?></th>
            <td><?= $ordenTrabajosInsumosDevolucione->has('orden_trabajos_insumo') ? $this->Html->link($ordenTrabajosInsumosDevolucione->orden_trabajos_insumo->id, ['controller' => 'OrdenTrabajosInsumos', 'action' => 'view', $ordenTrabajosInsumosDevolucione->orden_trabajos_insumo->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Producto') ?></th>
            <td><?= $ordenTrabajosInsumosDevolucione->has('producto') ? $this->Html->link($ordenTrabajosInsumosDevolucione->producto->nombre, ['controller' => 'Productos', 'action' => 'view', $ordenTrabajosInsumosDevolucione->producto->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('User') ?></th>
            <td><?= $ordenTrabajosInsumosDevolucione->has('user') ? $this->Html->link($ordenTrabajosInsumosDevolucione->user->nombre, ['controller' => 'Users', 'action' => 'view', $ordenTrabajosInsumosDevolucione->user->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($ordenTrabajosInsumosDevolucione->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Cantidad') ?></th>
            <td><?= $this->Number->format($ordenTrabajosInsumosDevolucione->cantidad) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Fecha') ?></th>
            <td><?= h($ordenTrabajosInsumosDevolucione->fecha) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($ordenTrabajosInsumosDevolucione->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($ordenTrabajosInsumosDevolucione->modified) ?></td>
        </tr>
    </table>
    <div class="row">
        <h4><?= __('Observaciones') ?></h4>
        <?= $this->Text->autoParagraph(h($ordenTrabajosInsumosDevolucione->observaciones)); ?>
    </div>
</div>
