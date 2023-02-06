<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\OrdenTrabajosDistribucione $ordenTrabajosDistribucione
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $ordenTrabajosDistribucione->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $ordenTrabajosDistribucione->id)]
            )
        ?></li>
        <li><?= $this->Html->link(__('List Orden Trabajos Distribuciones'), ['action' => 'index']) ?></li>
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
<div class="ordenTrabajosDistribuciones form large-9 medium-8 columns content">
    <?= $this->Form->create($ordenTrabajosDistribucione) ?>
    <fieldset>
        <legend><?= __('Edit Orden Trabajos Distribucione') ?></legend>
        <?php
            echo $this->Form->control('orden_trabajo_id', ['options' => $ordenTrabajos]);
            echo $this->Form->control('labore_id');
            echo $this->Form->control('unidade_id');
            echo $this->Form->control('campania_id');
            echo $this->Form->control('lote_id', ['options' => $lotes]);
            echo $this->Form->control('superficie');
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
