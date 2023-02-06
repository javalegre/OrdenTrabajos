<?php
namespace Ordenes\Model\Entity;

use Cake\ORM\Entity;

/**
 * OrdenTrabajosInsumosEntrega Entity
 *
 * @property int $id
 * @property \Cake\I18n\Time $fecha
 * @property int $orden_trabajos_insumo_id
 * @property int $producto_id
 * @property int $unidade_id
 * @property float $cantidad
 * @property string $observaciones
 * @property int $user_id
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\OrdenTrabajosInsumo $orden_trabajos_insumo
 * @property \App\Model\Entity\Producto $producto
 * @property \App\Model\Entity\Unidade $unidade
 * @property \App\Model\Entity\User $user
 */
class OrdenTrabajosInsumosEntrega extends Entity
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
        'fecha' => true,
        'orden_trabajos_insumo_id' => true,
        'producto_id' => true,
        'unidade_id' => true,
        'cantidad' => true,
        'observaciones' => true,
        'almacene_id' => true,
        'transaccion' => true,
        'user_id' => true,
        'created' => true,
        'modified' => true,
        'deleted' => true,
        'oracle_flag' => true,
        'interface_error' => true,
        'orden_trabajos_insumo' => true,
        'producto' => true,
        'unidade' => true,
        'user' => true,
        'almacene' => true,
    ];
}
