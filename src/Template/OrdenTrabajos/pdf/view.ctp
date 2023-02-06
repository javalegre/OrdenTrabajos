<?php
    /**
     * @var \App\View\AppView $this
     * @var \App\Model\Entity\OrdenTrabajo $ordenTrabajo
     */
    include(WWW_ROOT.'phpqrcode'.DS.'qrlib.php');
?>
<div class="row no-margins">
        <?= $this->Html->image(WWW_ROOT.'img'.DS.'logoadeco1.jpg', ['width' => '210']) ?>
        <h3 class="pull-right">Orden de Trabajo Nº <?= $ordenTrabajo->id ?></h3>
        <?= $ordenTrabajo->oc ? '<br><span class="pull-right">OC: '.$ordenTrabajo->oc.'</span>' : '' ?>
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
<br>
Labores ordenadas
<div class="row">
    <table class="table table-bordered table-striped" cellspacing="0" width="100%">
        <thead><?= $this->Html->tableHeaders(['Labor', 'U. Medida','Centro de Costo','Lote','Sector','Has']) ?></thead>
        <tbody>
            <?php foreach ($ordenTrabajo['orden_trabajos_distribuciones'] as $distribucion): ?>
                <tr>
                    <td><?= h($distribucion->proyectos_labore['nombre']) ?></td>
                    <td><?= h($distribucion->unidade['nombre']) ?></td>
                    <td><?= h($distribucion->proyecto['nombre']) ?></td>
                    <td><?= h($distribucion->lote['nombre']) ?></td>
                    <td><?= $distribucion->lote->sectore ? h($distribucion->lote->sectore->direccion) : '' ?></td>
                    <td class="derecha"><?= h($distribucion['superficie']) ?></td>
                </tr>
            <?php endforeach; ?>                            
        </tbody>
    </table>
</div>

<?php if (count($ordenTrabajo['orden_trabajos_insumos']) > 0){ ?>
    Insumos a utilizar
    <table class="table table-bordered table-striped" cellspacing="0" width="100%">
        <thead class="blue-bg"><?= $this->Html->tableHeaders(['Producto', 'Lote', 'Un','Dosis','Cant.','Almacen']) ?></thead>
        <tbody>
            <?php foreach ($ordenTrabajo['orden_trabajos_insumos'] as $insumo): ?>
                <tr>
                    <td><?= h($insumo->producto['nombre']) ?></td>
                    <td><?= $insumo->has('productos_lote') ? h($insumo->productos_lote->nombre) : '' ?></td>
                    <td><?= h($insumo->unidade['nombre']) ?></td>
                    <td class="derecha"><?= h($insumo->dosis) ?></td>
                    <td class="derecha"><?= h($insumo->cantidad) ?></td>
                    <td><?= h($insumo->almacene['nombre']) ?></td>
                </tr>
            <?php endforeach; ?>                            
        </tbody>
    </table>
<?php } ?>
<table width="100%">
    <tr class="fondo">
        <td width="85%">
            <?= $this->Form->control('observaciones', ['type' => 'textarea', 'class' => 'form-control', 'style' => 'border:1px solid #333;' , 'value' => $ordenTrabajo->observaciones]) ?>
        </td>
        <td width="15%" class="text-center">
            <br>
            <?php
                $tempDir = TMP;
                $codeContents = $ordenTrabajo->id;
                $fileName = 'qr_code.png';
                $pngAbsoluteFilePath = $tempDir.$fileName;
                QRcode::png($codeContents, $pngAbsoluteFilePath);
                echo $this->Html->image($pngAbsoluteFilePath, ['class' => ['m-t-md'], 'width' => '150', 'heigh' => '150']);
            ?>
        </td>
    </tr>
</table>

Condiciones Meteorologicas
<table class="table table-bordered table-striped" cellspacing="0" width="100%">
    <thead class="blue-bg"><?= $this->Html->tableHeaders(['Fecha', 'Temp(º)','Humedad(%)','Viento(km/h)','Dirección']) ?></thead>
    <tbody>
        <?php $condicion = $ordenTrabajo['orden_trabajos_condiciones_meteorologica'] ? $ordenTrabajo['orden_trabajos_condiciones_meteorologica'] : ''; ?>
        <tr>
            <td class="text-center"><?= h($condicion ? $condicion->fecha->i18nFormat('dd/MM/yyyy HH:mm') : '') ?></td>
            <td class="text-center"><?= h($condicion ? $condicion->temperatura : '') ?></td>
            <td class="text-center"><?= h($condicion ? $condicion->humedad : '') ?></td>
            <td class="text-center"><?= h($condicion ? $condicion->viento : '') ?></td>
            <td class="text-center"><?= h($condicion ? $condicion->direccion: '') ?></td>
        </tr>

    </tbody>
</table>

<br><br><br><br><br><br><br>
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