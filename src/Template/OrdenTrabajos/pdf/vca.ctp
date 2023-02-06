<?php
    /**
     * @var \App\View\AppView $this
     * @var \App\Model\Entity\OrdenTrabajo $ordenTrabajo
     */
?>
<div class="row no-margins">
        <?= $this->Html->image(WWW_ROOT.'img'.DS.'logoadeco1.jpg', ['width' => '210']) ?>
        <h2 class="pull-right">Vale de Consumo Nº <?= $ordenTrabajo->id ?></h2>
</div>
<br>

<table width="100%">
    <tr class="fondo">
        <td width="15%"></td>
        <td width="65%"></td>
        <td width="20%" class="p-l-n"><span class="pull-right"><strong>Fecha: </strong><?= h(date_format($ordenTrabajo->fecha, 'd/m/Y')) ?></span></td>
    </tr>
    <tr></tr>
    <tr>
        <td width="15%" class="fondo">Establecimiento: </td>
        <td colspan="2" class="fondo">&nbsp;<strong><?= h($ordenTrabajo->establecimiento['nombre']) ?></strong></td>
        
    </tr>
    <tr></tr>
    <tr class="fondo">
        <td width="15%">Proveedor:</td>
        <td colspan="2">&nbsp;<strong><?= h($ordenTrabajo->proveedore['nombre']) ?></strong></td>
    </tr>
</table>

<br><br>

Labores ordenadas
<div class="row">
    <table class="table table-bordered table-striped" cellspacing="0" width="100%">
        <thead><?= $this->Html->tableHeaders(['Labor', 'U. Medida','Centro de Costo','Lote','Has','Certif','Importe','Total']) ?></thead>
        <tbody>
            <?php foreach ($ordenTrabajo['orden_trabajos_distribuciones'] as $distribucion): ?>
                <tr>
                    <td><?= h($distribucion->labore['nombre']) ?></td>
                    <td><?= h($distribucion->unidade['nombre']) ?></td>
                    <td><?= h($distribucion->proyecto['nombre']) ?></td>
                    <td><?= h($distribucion->lote['nombre']) ?></td>
                    <td class="derecha"><?= h($distribucion['superficie']) ?></td>
                    <?php
                        $certificadas = 0;
                        foreach ($distribucion['orden_trabajos_certificaciones'] as $certificacion):
                            $certificadas = $certificadas + $certificacion['has'];
                        endforeach;
                    ?>
                    <td class="derecha"><?= h($certificadas) ?></td>
                    <td class="derecha"><?= h($this->Number->currency($distribucion['importe'])) ?></td>
                    <?php
                        /* Calculo el total en $ de la Orden de Trabajo */
                        $total = $certificadas * $distribucion['importe'];
                    ?>
                    <td class="derecha"><?= h($this->Number->currency($total)) ?></td>                    
                </tr>
            <?php endforeach; ?>                            
        </tbody>
    </table>
</div>

<?php if (count($ordenTrabajo['orden_trabajos_insumos']) > 0){ ?>
    <br><br>
    Insumos a utilizar
    <table class="table table-bordered table-striped" cellspacing="0" width="100%">
        <thead class="blue-bg"><?= $this->Html->tableHeaders(['Producto', 'Lote', 'Un', 'Dosis', 'Ordenado', 'Entr', 'Devol', 'Aplicado', 'Almacen']) ?></thead>
        <tbody>
            <?php foreach ($ordenTrabajo['orden_trabajos_insumos'] as $insumo): ?>
                <tr>
                    <td><?= h($insumo->producto['nombre']) ?></td>
                    <td><?= h($insumo->has('productos_lote') ? $insumo->productos_lote->nombre : '') ?></td>
                    <td><?= h($insumo->unidade['nombre']) ?></td>
                    <td class="derecha"><?= h($insumo->dosis) ?></td>
                    <td class="derecha"><?= h($insumo->cantidad) ?></td>
                    <td class="derecha"><?= h($insumo->entrega) ?></td>
                    <td class="derecha"><?= h($insumo->devolucion) ?></td>
                    <td class="derecha"><?= h($insumo->utilizado) ?></td>
                    <td><?= h($insumo->almacene['nombre']) ?></td>
                </tr>
            <?php endforeach; ?>                            
        </tbody>
    </table>
<?php } ?>
<br>
<?= $this->Form->control('observaciones', ['type' => 'textarea', 'class' => 'form-control', 'style' => 'border:1px solid #333;' , 'value' => $ordenTrabajo->observaciones]) ?>
<br>

<br><br><br>
<br><br><br>



<table width="100%">
    <tr class="fondo"></tr>
    <tr class="fondo"></tr>
    <tr>
        <td width="10%"></td>
        <td width="35%"></td>
        <td width="10%"></td>
        <td width="35%"></td>
        <td width="10%"></td>
    </tr>
    <tr>
        <td width="10%"></td>
        <td width="35%" class="centrado" style="border-top: 1px solid #ccc;">Firma Responsable Operativo</td>
        <td width="10%"></td>
        <td width="35%" class="centrado" style="border-top: 1px solid #ccc;">Firma Contratista</td>
        <td width="10%"></td>
    </tr>
    <tr>
        <td width="10%"></td>
        <td width="35%" class="centrado"><strong><?= h($ordenTrabajo->user['nombre']) ?></strong></td>
        <td width="10%"></td>
        <td width="35%" class="centrado"><strong><?= h($ordenTrabajo->proveedore['nombre']) ?></strong></td>
        <td width="10%"></td>
    </tr>    
</table>
 <footer>
     <small class="text-muted pull-right">www.elagronomo.com</small>
     <small class="text-muted">Orden de Trabajo generada por <?=  h($ordenTrabajo->user['nombre']) ?> el <?=  h(date_format($ordenTrabajo->created, 'd/m/Y H:i:s'))?></small>
</footer>