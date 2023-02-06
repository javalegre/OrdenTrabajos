<?php
    /**
     * @var \App\View\AppView $this
     * @var \App\Model\Entity\OrdenTrabajo $ordenTrabajo
     */
     $total_superficie = 0;
?>
<div class="row no-margins">
    <?= $this->Html->image(WWW_ROOT.'img'.DS.'logoadeco1.jpg', ['width' => '210']) ?>
    <div class="pull-right">
        <h4 class="no-paddings m-b-n">Orden de Trabajo Nº <?= $ordenTrabajo->id ?></h4>
        <span class="pull-right no-paddings small"><?= $ordenTrabajo->oc ?  'OC '.$ordenTrabajo->oc : '' ?></span>
    </div>
</div>
<br>

<table width="100%">
    <tr class="fondo">
        <td width="15%"></td>
        <td width="65%"></td>
        <td width="20%" class="p-l-n"><span class="pull-right"><strong>Fecha: </strong><?= h(date_format($ordenTrabajo->fecha, 'd/m/Y')) ?></span></td>
    </tr>
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
        <thead><?= $this->Html->tableHeaders(['Labor', 'U. Medida','Centro de Costo','Lote','Sector','Has','Certif','Importe','Total']) ?></thead>
        <tbody>
            <?php foreach ($ordenTrabajo['orden_trabajos_distribuciones'] as $distribucion): ?>
                <tr>
                    <td><?= h($distribucion->proyectos_labore['nombre']) ?></td>
                    <td><?= h($distribucion->unidade['nombre']) ?></td>
                    <td><?= h($distribucion->proyecto['nombre']) ?></td>
                    <td><?= h($distribucion->lote['nombre']) ?></td>
                    <td><?= $distribucion->lote->sectore ? h($distribucion->lote->sectore->direccion) : '' ?></td>
                    <td class="derecha"><?= h($distribucion['superficie']) ?></td>
                    <?php
                        $certificadas = 0;
                        foreach ($distribucion['orden_trabajos_certificaciones'] as $certificacion):
                            $certificadas = $certificadas + $certificacion['has'];
                        endforeach;
                        $total_superficie = $total_superficie + $certificadas;
                    ?>
                    <td class="derecha"><?= h($certificadas) ?></td>
                    <td class="derecha"><?= h($this->Number->currency($distribucion['ImporteCertificado'])) ?></td>
                    <?php
                        /* Calculo el total en $ de la Orden de Trabajo */
                        $total = $certificadas * $distribucion['ImporteCertificado'];
                    ?>
                    <td class="derecha"><?= h($this->Number->currency($total)) ?></td>                    
                </tr>
            <?php endforeach; ?>                            
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6"></td>
                <td>( <?= $total_superficie ?> )</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</div>

<?php if (count($ordenTrabajo['orden_trabajos_insumos']) > 0){ ?>
    <br>
    Insumos a utilizar
    <table class="table table-bordered table-striped" cellspacing="0" width="100%">
        <thead class="blue-bg"><?= $this->Html->tableHeaders(['Producto', 'Lote', 'Un','Dosis','Ordenado','Aplicado','Almacen']) ?></thead>
        <tbody>
            <?php foreach ($ordenTrabajo['orden_trabajos_insumos'] as $insumo): ?>
                <tr>
                    <td><?= h($insumo->producto['nombre']) ?></td>
                    <td><?= h($insumo->has('productos_lote') ? $insumo->productos_lote->nombre : '') ?></td>
                    <td><?= h($insumo->unidade['nombre']) ?></td>
                    <td class="derecha"><?= h($insumo->dosis) ?></td>
                    <td class="derecha"><?= h($insumo->cantidad) ?></td>
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

<br>
<table class="table table-bordered table-striped" cellspacing="0" width="100%">
    <thead class="blue-bg"><?= $this->Html->tableHeaders(['Certificaciones']) ?></thead>
    <tbody>
        <?php foreach ($ordenTrabajo['orden_trabajos_certificaciones'] as $certificacion): ?>
            <tr>
                
                <td><?= h($certificacion->user['nombre']).' ha certificado <strong>'.h($certificacion->has).'  has</strong> el '.h(date_format($certificacion->fecha_final,'d/m/Y')).'.'?></td>
            </tr>
        <?php endforeach; ?>                            
    </tbody>
</table>
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