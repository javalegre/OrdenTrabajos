<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\OrdenTrabajosCertificacione $ordenTrabajosCertificacione
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('Edit Orden Trabajos Certificacione'), ['action' => 'edit', $ordenTrabajosCertificacione->id]) ?> </li>
        <li><?= $this->Form->postLink(__('Delete Orden Trabajos Certificacione'), ['action' => 'delete', $ordenTrabajosCertificacione->id], ['confirm' => __('Are you sure you want to delete # {0}?', $ordenTrabajosCertificacione->id)]) ?> </li>
        <li><?= $this->Html->link(__('List Orden Trabajos Certificaciones'), ['action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Orden Trabajos Certificacione'), ['action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Orden Trabajos Distribuciones'), ['controller' => 'OrdenTrabajosDistribuciones', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New Orden Trabajos Distribucione'), ['controller' => 'OrdenTrabajosDistribuciones', 'action' => 'add']) ?> </li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?> </li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?> </li>
    </ul>
</nav>
<div class="ordenTrabajosCertificaciones view large-9 medium-8 columns content">
    <h3><?= h($ordenTrabajosCertificacione->id) ?></h3>
    <table class="vertical-table">
        <tr>
            <th scope="row"><?= __('Orden Trabajos Distribucione') ?></th>
            <td><?= $ordenTrabajosCertificacione->has('orden_trabajos_distribucione') ? $this->Html->link($ordenTrabajosCertificacione->orden_trabajos_distribucione->id, ['controller' => 'OrdenTrabajosDistribuciones', 'action' => 'view', $ordenTrabajosCertificacione->orden_trabajos_distribucione->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Imagenes') ?></th>
            <td><?= h($ordenTrabajosCertificacione->imagenes) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('User') ?></th>
            <td><?= $ordenTrabajosCertificacione->has('user') ? $this->Html->link($ordenTrabajosCertificacione->user->nombre, ['controller' => 'Users', 'action' => 'view', $ordenTrabajosCertificacione->user->id]) : '' ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Id') ?></th>
            <td><?= $this->Number->format($ordenTrabajosCertificacione->id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Orden Trabajo Id') ?></th>
            <td><?= $this->Number->format($ordenTrabajosCertificacione->orden_trabajo_id) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Has') ?></th>
            <td><?= $this->Number->format($ordenTrabajosCertificacione->has) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Precio Final') ?></th>
            <td><?= $this->Number->format($ordenTrabajosCertificacione->precio_final) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Fecha Inicio') ?></th>
            <td><?= h($ordenTrabajosCertificacione->fecha_inicio) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Fecha Final') ?></th>
            <td><?= h($ordenTrabajosCertificacione->fecha_final) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Created') ?></th>
            <td><?= h($ordenTrabajosCertificacione->created) ?></td>
        </tr>
        <tr>
            <th scope="row"><?= __('Modified') ?></th>
            <td><?= h($ordenTrabajosCertificacione->modified) ?></td>
        </tr>
    </table>
    <div class="row">
        <h4><?= __('Observaciones') ?></h4>
        <?= $this->Text->autoParagraph(h($ordenTrabajosCertificacione->observaciones)); ?>
    </div>
</div>
