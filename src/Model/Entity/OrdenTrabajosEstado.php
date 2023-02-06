<?php
namespace Ordenes\Model\Entity;

use Cake\ORM\Entity;

/**
 * OrdenTrabajosEstado Entity
 *
 * @property int $id
 * @property string $nombre
 * @property int $prioridad
 * @property string $observaciones
 *
 * @property \App\Model\Entity\OrdenTrabajo[] $orden_trabajos
 */
class OrdenTrabajosEstado extends Entity
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
        'nombre' => true,
        'prioridad' => true,
        'observaciones' => true,
        'color' => true,
        'orden_trabajos' => true
    ];
}
