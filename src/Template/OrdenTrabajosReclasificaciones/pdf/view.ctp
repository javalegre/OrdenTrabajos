<?php
/**
 * Ordenes de Trabajo Reclasificaciones
 *
 * Comprobante de una reclasificacion realizada, en formato PDF
 *
 * Desarrollado para Adecoagro SA.
 * 
 * @category CakePHP3
 *
 * @package Ordenes
 *
 * @author Javier Alegre <jalegre@adecoagro.com>
 * @copyright Copyright 2021, Adecoagro
 * @version 1.0.0 creado el 29/09/2022
 */
?>
<div class="row no-margins">
        <?= $this->Html->image(WWW_ROOT.'img'.DS.'logoadeco1.jpg', ['width' => '210']) ?>
    <h3 class="pull-right">Reclasificaci&oacute;n Nº <?= $ordenTrabajosReclasificacione->id ?></h3>
</div>
<br>
<table width="100%">
    <tr class="fondo">
        <td width="15%"></td>
        <td width="65%"></td>
        <td width="20%" class="p-l-n"><span class="pull-right"><strong>Fecha: </strong><?= h(date_format($ordenTrabajosReclasificacione->fecha, 'd/m/Y')) ?></span></td>
    </tr>
    <tr></tr>
    <tr>
        <td width="15%" class="fondo">Establecimiento: </td>
        <td colspan="2" class="fondo">&nbsp;<strong><?= h($ordenTrabajosReclasificacione->establecimiento->nombre) ?></strong></td>
        
    </tr>
    <tr></tr>
    <tr class="fondo">
        <td width="15%">Descripci&oacute;n:</td>
        <td colspan="2">&nbsp;<strong><?= h($ordenTrabajosReclasificacione->nombre) ?></strong></td>
    </tr>
</table>
<br>

Lineas reclasificadas
<div class="row">
    <table class="table table-bordered table-striped" cellspacing="0" width="100%">
        <thead><?= $this->Html->tableHeaders(['OT', 'Proyecto', 'Labor', 'Fecha', 'Proyecto Origen', 'Labor Origen', 'Referencia']) ?></thead>
        <tbody>
            <?php foreach ($ordenTrabajosReclasificacione->orden_trabajos_reclasificaciones_detalles as $distribucion): ?>
                <tr>
                    <td><?= h($distribucion->orden_trabajo_id) ?></td>
                    <td><?= $distribucion->orden_trabajos_distribucione->proyecto ? $distribucion->orden_trabajos_distribucione->proyecto->cultivo : '' ?></td>
                    <td><?= $distribucion->orden_trabajos_distribucione->proyectos_labore ? $distribucion->orden_trabajos_distribucione->proyectos_labore->nombre : '' ?></td>
                    <td><?= h($distribucion->created->i18nFormat('dd/MM/yyyy')) ?></td>
                    <td><?= h($distribucion->proyecto->cultivo) ?></td>
                    <td><?= h($distribucion->proyectos_labore->nombre) ?></td>
                    <td><?= h($distribucion->referencia) ?></td>
                </tr>
            <?php endforeach; ?>                            
        </tbody>
    </table>
</div>

<table width="100%">
    <tr class="fondo">
        <td width="100%">
            <?= $this->Form->control('observaciones', ['type' => 'textarea', 'class' => 'form-control', 'style' => 'border:1px solid #333;' , 'value' => $ordenTrabajosReclasificacione->observaciones]) ?>
        </td>
    </tr>
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
        <td width="35%" class="centrado" style="border-top: 1px solid #ccc;">Solicitado por</td>
        <td width="10%"></td>
        <td width="35%" class="centrado" style="border-top: 1px solid #ccc;">Autorizado por</td>
        <td width="10%"></td>
    </tr>
    <tr>
        <td width="10%"></td>
        <td width="35%" class="centrado"><strong></strong></td>
        <td width="10%"></td>
        <td width="35%" class="centrado"><strong></strong></td>
        <td width="10%"></td>
    </tr>    
</table>
 <footer>
     <small class="text-muted pull-right">www.elagronomo.com</small>
     <small class="text-muted">Reclasificaci&oacute;n generada por <?=  h($ordenTrabajosReclasificacione->has('user') ? $ordenTrabajosReclasificacione->user->nombre : '') ?> el <?=  h(date_format($ordenTrabajosReclasificacione->created, 'd/m/Y H:i:s'))?></small>
</footer>