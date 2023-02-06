<?php
namespace Ordenes\Model\Entity;

use Cake\ORM\Entity;

/**
 * OrdenTrabajosInsumosCosto Entity
 *
 * @property int $id
 * @property int|null $orden_trabajos_insumo_id
 * @property int|null $orden_trabajo_id
 * @property int|null $producto_id
 * @property int|null $almacene_id
 * @property float|null $cantidad_utilizada
 * @property \Cake\I18n\Date|null $fecha
 * @property float|null $precio_unitario
 * @property float|null $tipo_cambio
 * @property float|null $precio_unitario_dolar
 * @property float|null $superficie
 * @property int|null $estado
 * @property int|null $periodo
 * @property \Cake\I18n\Time|null $created
 * @property \Cake\I18n\Time|null $modified
 *
 * @property \Ordenes\Model\Entity\OrdenTrabajosInsumo $orden_trabajos_insumo
 * @property \Ordenes\Model\Entity\OrdenTrabajo $orden_trabajo
 * @property \Ordenes\Model\Entity\Producto $producto
 * @property \Ordenes\Model\Entity\Almacene $almacene
 */
class OrdenTrabajosInsumosCosto extends Entity
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
        'orden_trabajos_insumo_id' => true,
        'orden_trabajo_id' => true,
        'producto_id' => true,
        'almacene_id' => true,
        'cantidad_utilizada' => true,
        'fecha' => true,
        'precio_unitario' => true,
        'tipo_cambio' => true,
        'precio_unitario_dolar' => true,
        'superficie' => true,
        'estado' => true,
        'periodo' => true,
        'created' => true,
        'modified' => true,
        'orden_trabajos_insumo' => true,
        'orden_trabajo' => true,
        'producto' => true,
        'almacene' => true
    ];
}
