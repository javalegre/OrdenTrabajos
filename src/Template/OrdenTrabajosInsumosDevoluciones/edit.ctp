<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\OrdenTrabajosInsumosDevolucione $ordenTrabajosInsumosDevolucione
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $ordenTrabajosInsumosDevolucione->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $ordenTrabajosInsumosDevolucione->id)]
            )
        ?></li>
        <li><?= $this->Html->link(__('List Orden Trabajos Insumos Devoluciones'), ['action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('List Orden Trabajos Insumos'), ['controller' => 'OrdenTrabajosInsumos', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Orden Trabajos Insumo'), ['controller' => 'OrdenTrabajosInsumos', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Productos'), ['controller' => 'Productos', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Producto'), ['controller' => 'Productos', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Users'), ['controller' => 'Users', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New User'), ['controller' => 'Users', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="ordenTrabajosInsumosDevoluciones form large-9 medium-8 columns content">
    <?= $this->Form->create($ordenTrabajosInsumosDevolucione) ?>
    <fieldset>
        <legend><?= __('Edit Orden Trabajos Insumos Devolucione') ?></legend>
        <?php
            echo $this->Form->control('fecha', ['empty' => true]);
            echo $this->Form->control('orden_trabajos_insumo_id', ['options' => $ordenTrabajosInsumos]);
            echo $this->Form->control('producto_id', ['options' => $productos]);
            echo $this->Form->control('cantidad');
            echo $this->Form->control('observaciones');
            echo $this->Form->control('user_id', ['options' => $users]);
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
