<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\OrdenTrabajosEstado $ordenTrabajosEstado
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Orden Trabajos Estado'), ['action' => 'edit', $ordenTrabajosEstado->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Orden Trabajos Estado'), ['action' => 'delete', $ordenTrabajosEstado->id], ['confirm' => __('Are you sure you want to delete # {0}?', $ordenTrabajosEstado->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Orden Trabajos Estados'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Orden Trabajos Estado'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Orden Trabajos'), ['controller' => 'OrdenTrabajos', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Orden Trabajo'), ['controller' => 'OrdenTrabajos', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="ordenTrabajosEstados view large-9 medium-8 columns content">
    <h3><?= h($ordenTrabajosEstado->id) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Nombre') ?></th>
            <td><?= h($ordenTrabajosEstado->nombre) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($ordenTrabajosEstado->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Prioridad') ?></th>
            <td><?= $this->Number->format($ordenTrabajosEstado->prioridad) ?></td>
        </tr>
    </table>
    <div class="row">
        <h4><?= __('Observaciones') ?></h4>
        <?= $this->Text->autoParagraph(h($ordenTrabajosEstado->observaciones)); ?>
    </div>
    <div class="related">
        <h4><?= __('Related Orden Trabajos') ?></h4>
        <?php if (!empty($ordenTrabajosEstado->orden_trabajos)): ?>
        <table cellpadding="0" cellspacing="0">
            <tr>
                <th scope="col"><?= __('Id') ?></th>
                <th scope="col"><?= __('Fecha') ?></th>
                <th scope="col"><?= __('Orden Trabajos Estado Id') ?></th>
                <th scope="col"><?= __('Descripcion') ?></th>
                <th scope="col"><?= __('Velocidadviento') ?></th>
                <th scope="col"><?= __('Temperatura') ?></th>
                <th scope="col"><?= __('Consumogasoil') ?></th>
                <th scope="col"><?= __('Humedad') ?></th>
                <th scope="col"><?= __('Establecimiento Id') ?></th>
                <th scope="col"><?= __('Proveedore Id') ?></th>
                <th scope="col"><?= __('User Id') ?></th>
                <th scope="col"><?= __('Created') ?></th>
                <th scope="col"><?= __('Modified') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
            <?php foreach ($ordenTrabajosEstado->orden_trabajos as $ordenTrabajos): ?>
            <tr>
                <td><?= h($ordenTrabajos->id) ?></td>
                <td><?= h($ordenTrabajos->fecha) ?></td>
                <td><?= h($ordenTrabajos->orden_trabajos_estado_id) ?></td>
                <td><?= h($ordenTrabajos->descripcion) ?></td>
                <td><?= h($ordenTrabajos->velocidadviento) ?></td>
                <td><?= h($ordenTrabajos->temperatura) ?></td>
                <td><?= h($ordenTrabajos->consumogasoil) ?></td>
                <td><?= h($ordenTrabajos->humedad) ?></td>
                <td><?= h($ordenTrabajos->establecimiento_id) ?></td>
                <td><?= h($ordenTrabajos->proveedore_id) ?></td>
                <td><?= h($ordenTrabajos->user_id) ?></td>
                <td><?= h($ordenTrabajos->created) ?></td>
                <td><?= h($ordenTrabajos->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['controller' => 'OrdenTrabajos', 'action' => 'view', $ordenTrabajos->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['controller' => 'OrdenTrabajos', 'action' => 'edit', $ordenTrabajos->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'OrdenTrabajos', 'action' => 'delete', $ordenTrabajos->id], ['confirm' => __('Are you sure you want to delete # {0}?', $ordenTrabajos->id)]) ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
</div>
