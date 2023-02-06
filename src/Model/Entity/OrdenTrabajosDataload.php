<?php
namespace Ordenes\Model\Entity;

use Cake\ORM\Entity;

/**
 * OrdenTrabajosDataload Entity
 *
 * @property int $id
 * @property string $nombre
 * @property \Cake\I18n\Time $fecha
 * @property string $path
 * @property int $user_id
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\OrdenTrabajo[] $orden_trabajos
 */
class OrdenTrabajosDataload extends Entity
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
        'vca' => true,
        'fecha' => true,
        'path' => true,
        'user_id' => true,
        'created' => true,
        'modified' => true,
        'user' => true,
        'orden_trabajos' => true
    ];
}
