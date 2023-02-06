<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\OrdenTrabajosCertificacione[]|\Cake\Collection\CollectionInterface $ordenTrabajosCertificaciones
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('New Orden Trabajos Certificacione'), ['action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Orden Trabajos Distribuciones'), ['controller' => 'OrdenTrabajosDistribuciones', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Orden Trabajos Distribucione'), ['controller' => 'OrdenTrabajosDistribuciones', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="ordenTrabajosCertificaciones index large-9 medium-8 columns content">
    <h3><?= __('Orden Trabajos Certificaciones') ?></h3>
    <table cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th scope="col"><?= $this->Paginator->sort('id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('orden_trabajos_distribucione_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('orden_trabajo_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('fecha_inicio') ?></th>
                <th scope="col"><?= $this->Paginator->sort('fecha_final') ?></th>
                <th scope="col"><?= $this->Paginator->sort('has') ?></th>
                <th scope="col"><?= $this->Paginator->sort('precio_final') ?></th>
                <th scope="col"><?= $this->Paginator->sort('imagenes') ?></th>
                <th scope="col"><?= $this->Paginator->sort('user_id') ?></th>
                <th scope="col"><?= $this->Paginator->sort('created') ?></th>
                <th scope="col"><?= $this->Paginator->sort('modified') ?></th>
                <th scope="col" class="actions"><?= __('Actions') ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ordenTrabajosCertificaciones as $ordenTrabajosCertificacione): ?>
            <tr>
                <td><?= $this->Number->format($ordenTrabajosCertificacione->id) ?></td>
                <td><?= $ordenTrabajosCertificacione->has('orden_trabajos_distribucione') ? $this->Html->link($ordenTrabajosCertificacione->orden_trabajos_distribucione->id, ['controller' => 'OrdenTrabajosDistribuciones', 'action' => 'view', $ordenTrabajosCertificacione->orden_trabajos_distribucione->id]) : '' ?></td>
                <td><?= $this->Number->format($ordenTrabajosCertificacione->orden_trabajo_id) ?></td>
                <td><?= h($ordenTrabajosCertificacione->fecha_inicio) ?></td>
                <td><?= h($ordenTrabajosCertificacione->fecha_final) ?></td>
                <td><?= $this->Number->format($ordenTrabajosCertificacione->has) ?></td>
                <td><?= $this->Number->format($ordenTrabajosCertificacione->precio_final) ?></td>
                <td><?= h($ordenTrabajosCertificacione->imagenes) ?></td>
                <td><?= $ordenTrabajosCertificacione->has('user') ? $this->Html->link($ordenTrabajosCertificacione->user->nombre, ['controller' => 'Users', 'action' => 'view', $ordenTrabajosCertificacione->user->id]) : '' ?></td>
                <td><?= h($ordenTrabajosCertificacione->created) ?></td>
                <td><?= h($ordenTrabajosCertificacione->modified) ?></td>
                <td class="actions">
                    <?= $this->Html->link(__('View'), ['action' => 'view', $ordenTrabajosCertificacione->id]) ?>
                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $ordenTrabajosCertificacione->id]) ?>
                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $ordenTrabajosCertificacione->id], ['confirm' => __('Are you sure you want to delete # {0}?', $ordenTrabajosCertificacione->id)]) ?>
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
