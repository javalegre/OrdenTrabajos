<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\OrdenTrabajosEstado $ordenTrabajosEstado
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $ordenTrabajosEstado->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $ordenTrabajosEstado->id)]
            )
        ?></li>
        <li><?= $this->Html->link(__('List Orden Trabajos Estados'), ['action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('List Orden Trabajos'), ['controller' => 'OrdenTrabajos', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Orden Trabajo'), ['controller' => 'OrdenTrabajos', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="ordenTrabajosEstados form large-9 medium-8 columns content">
    <?= $this->Form->create($ordenTrabajosEstado) ?>
    <fieldset>
        <legend><?= __('Edit Orden Trabajos Estado') ?></legend>
        <?php
            echo $this->Form->control('nombre');
            echo $this->Form->control('prioridad');
            echo $this->Form->control('observaciones');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
