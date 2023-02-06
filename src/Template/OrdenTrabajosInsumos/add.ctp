<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\OrdenTrabajosInsumo $ordenTrabajosInsumo
 */
?>
<nav class="large-3 medium-4 columns" id="actions-sidebar">
    <ul class="side-nav">
        <li class="heading"><?= __('Actions') ?></li>
        <li><?= $this->Html->link(__('List Orden Trabajos Insumos'), ['action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('List Orden Trabajos'), ['controller' => 'OrdenTrabajos', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Orden Trabajo'), ['controller' => 'OrdenTrabajos', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Orden Trabajos Distribuciones'), ['controller' => 'OrdenTrabajosDistribuciones', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Orden Trabajos Distribucione'), ['controller' => 'OrdenTrabajosDistribuciones', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Productos'), ['controller' => 'Productos', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Producto'), ['controller' => 'Productos', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Unidades'), ['controller' => 'Unidades', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Unidade'), ['controller' => 'Unidades', 'action' => 'add']) ?></li>
        <li><?= $this->Html->link(__('List Almacenes'), ['controller' => 'Almacenes', 'action' => 'index']) ?></li>
        <li><?= $this->Html->link(__('New Almacene'), ['controller' => 'Almacenes', 'action' => 'add']) ?></li>
    </ul>
</nav>
<div class="ordenTrabajosInsumos form large-9 medium-8 columns content">
    <?= $this->Form->create($ordenTrabajosInsumo) ?>
    <fieldset>
        <legend><?= __('Add Orden Trabajos Insumo') ?></legend>
        <?php
            echo $this->Form->control('orden_trabajo_id', ['options' => $ordenTrabajos]);
            echo $this->Form->control('orden_trabajos_distribucione_id', ['options' => $ordenTrabajosDistribuciones]);
            echo $this->Form->control('producto_id', ['options' => $productos]);
            echo $this->Form->control('dosis');
            echo $this->Form->control('cantidad');
            echo $this->Form->control('unidade_id', ['options' => $unidades]);
            echo $this->Form->control('cantidad_stock');
            echo $this->Form->control('almacene_id', ['options' => $almacenes]);
        ?>
    </fieldset>
    <?= $this->Form->button(__('Submit')) ?>
    <?= $this->Form->end() ?>
</div>
