<?php
namespace Ordenes\Model\Entity;

use Cake\ORM\Entity;

/**
 * OrdenTrabajosCondicionesMeteorologica Entity
 *
 * @property int $id
 * @property int $orden_trabajo_id
 * @property \Cake\I18n\Time|null $fecha
 * @property int|null $temperatura
 * @property int|null $humedad
 * @property string|null $viento
 * @property string|null $direccion
 * @property int $user_id
 * @property \Cake\I18n\Time|null $created
 * @property \Cake\I18n\Time|null $modified
 *
 * @property \App\Model\Entity\OrdenTrabajo $orden_trabajo
 * @property \App\Model\Entity\User $user
 */
class OrdenTrabajosCondicionesMeteorologica extends Entity
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
        'fecha' => true,
        'temperatura' => true,
        'humedad' => true,
        'viento' => true,
        'direccion' => true,
        'user_id' => true,
        'created' => true,
        'modified' => true,
        'orden_trabajo' => true,
        'user' => true
    ];
}
