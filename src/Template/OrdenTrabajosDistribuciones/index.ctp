<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\OrdenTrabajosDistribucione[]|\Cake\Collection\CollectionInterface $ordenTrabajosDistribuciones
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('New Orden Trabajos Distribucione'), ['action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Lotes'), ['controller' => 'Lotes', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Lote'), ['controller' => 'Lotes', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Orden Trabajos'), ['controller' => 'OrdenTrabajos', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Orden Trabajo'), ['controller' => 'OrdenTrabajos', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Actividades'), ['controller' => 'Actividades', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Actividade'), ['controller' => 'Actividades', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Ambientes'), ['controller' => 'Ambientes', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Ambiente'), ['controller' => 'Ambientes', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="ordenTrabajosDistribuciones index large-9 medium-8 columns content">
    <h3><?= __('Orden Trabajos Distribuciones') ?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('orden_trabajo_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('labore_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('unidade_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('campania_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('lote_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('superficie') ?></th>
                <th scope="col"><?= $this->Paginator->sort('created') ?></th>
                <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ordenTrabajosDistribuciones as $ordenTrabajosDistribucione): ?>
            <tr>
                <td><?= $this->Number->format($ordenTrabajosDistribucione->id) ?></td>
                <td><?= $ordenTrabajosDistribucione->has('orden_trabajo') ? $this->Html->link($ordenTrabajosDistribucione->orden_trabajo->id, ['controller' => 'OrdenTrabajos', 'action' => 'view', $ordenTrabajosDistribucione->orden_trabajo->id]) : '' ?></td>
                <td><?= $this->Number->format($ordenTrabajosDistribucione->labore_id) ?></td>
                <td><?= $this->Number->format($ordenTrabajosDistribucione->unidade_id) ?></td>
                <td><?= $this->Number->format($ordenTrabajosDistribucione->campania_id) ?></td>
                <td><?= $ordenTrabajosDistribucione->has('lote') ? $this->Html->link($ordenTrabajosDistribucione->lote->nombre, ['controller' => 'Lotes', 'action' => 'view', $ordenTrabajosDistribucione->lote->id]) : '' ?></td>
                <td><?= $this->Number->format($ordenTrabajosDistribucione->superficie) ?></td>
                <td><?= h($ordenTrabajosDistribucione->created) ?></td>
                <td><?= h($ordenTrabajosDistribucione->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $ordenTrabajosDistribucione->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $ordenTrabajosDistribucione->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $ordenTrabajosDistribucione->id], ['confirm' => __('Are you sure you want to delete # {0}?', $ordenTrabajosDistribucione->id)]) ?>
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
