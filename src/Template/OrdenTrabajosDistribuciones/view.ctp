<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\OrdenTrabajosDistribucione $ordenTrabajosDistribucione
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Orden Trabajos Distribucione'), ['action' => 'edit', $ordenTrabajosDistribucione->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Orden Trabajos Distribucione'), ['action' => 'delete', $ordenTrabajosDistribucione->id], ['confirm' => __('Are you sure you want to delete # {0}?', $ordenTrabajosDistribucione->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Orden Trabajos Distribuciones'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Orden Trabajos Distribucione'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Lotes'), ['controller' => 'Lotes', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Lote'), ['controller' => 'Lotes', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Orden Trabajos'), ['controller' => 'OrdenTrabajos', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Orden Trabajo'), ['controller' => 'OrdenTrabajos', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Actividades'), ['controller' => 'Actividades', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Actividade'), ['controller' => 'Actividades', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Ambientes'), ['controller' => 'Ambientes', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Ambiente'), ['controller' => 'Ambientes', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="ordenTrabajosDistribuciones view large-9 medium-8 columns content">
    <h3><?= h($ordenTrabajosDistribucione->id) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Orden Trabajo') ?></th>
            <td><?= $ordenTrabajosDistribucione->has('orden_trabajo') ? $this->Html->link($ordenTrabajosDistribucione->orden_trabajo->id, ['controller' => 'OrdenTrabajos', 'action' => 'view', $ordenTrabajosDistribucione->orden_trabajo->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Lote') ?></th>
            <td><?= $ordenTrabajosDistribucione->has('lote') ? $this->Html->link($ordenTrabajosDistribucione->lote->nombre, ['controller' => 'Lotes', 'action' => 'view', $ordenTrabajosDistribucione->lote->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($ordenTrabajosDistribucione->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Labore Id') ?></th>
            <td><?= $this->Number->format($ordenTrabajosDistribucione->labore_id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Unidade Id') ?></th>
            <td><?= $this->Number->format($ordenTrabajosDistribucione->unidade_id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Campania Id') ?></th>
            <td><?= $this->Number->format($ordenTrabajosDistribucione->campania_id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Superficie') ?></th>
            <td><?= $this->Number->format($ordenTrabajosDistribucione->superficie) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($ordenTrabajosDistribucione->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($ordenTrabajosDistribucione->modified) ?></td>
        </tr>
    </table>
</div>
