<?php
namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * OrdenTrabajosOraclesRechazo Entity
 *
 * @property int $id
 * @property int|null $orden_trabajo_id
 * @property int|null $orden_compra
 * @property \Cake\I18n\Time|null $fecha
 * @property string|null $rechazado_por
 * @property string|null $evento
 * @property string|null $status
 * @property string|null $motivo
 * @property int|null $procesado
 * @property \Cake\I18n\Time|null $created
 * @property \Cake\I18n\Time|null $modified
 * @property \Cake\I18n\Time|null $deleted
 *
 * @property \App\Model\Entity\OrdenTrabajo $orden_trabajo
 */
class OrdenTrabajosOraclesRechazo extends Entity
{

    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array
     */
    protected $_accessible = [
        'orden_trabajo_id' => true,
        'orden_compra' => true,
        'fecha' => true,
        'rechazado_por' => true,
        'evento' => true,
        'status' => true,
        'motivo' => true,
        'procesado' => true,
        'created' => true,
        'modified' => true,
        'deleted' => true,
        'orden_trabajo' => true
    ];
}
