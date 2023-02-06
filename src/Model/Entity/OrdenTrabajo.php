<?php
namespace Ordenes\Model\Entity;

use Cake\ORM\Entity;

/**
 * OrdenTrabajo Entity
 *
 * @property int $id
 * @property \Cake\I18n\FrozenDate $fecha
 * @property int $orden_trabajos_estado_id
 * @property string $descripcion
 * @property float $velocidadviento
 * @property float $temperatura
 * @property float $consumogasoil
 * @property float $humedad
 * @property int $establecimiento_id
 * @property int $proveedore_id
 * @property string $observaciones
 * @property int $user_id
 * @property \Cake\I18n\FrozenTime $created
 * @property \Cake\I18n\FrozenTime $modified
 * @property \Cake\I18n\FrozenTime $deleted
 *
 * @property \App\Model\Entity\OrdenTrabajosEstado $orden_trabajos_estado
 * @property \App\Model\Entity\Establecimiento $establecimiento
 * @property \App\Model\Entity\Proveedore $proveedore
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\OrdenTrabajosCuadrilla[] $orden_trabajos_cuadrillas
 * @property \App\Model\Entity\OrdenTrabajosDistribucione[] $orden_trabajos_distribuciones
 * @property \App\Model\Entity\OrdenTrabajosInsumo[] $orden_trabajos_insumos
 * @property \App\Model\Entity\Labore[] $labores
 */
class OrdenTrabajo extends Entity
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
        'orden_trabajos_estado_id' => true,
        'descripcion' => true,
        'velocidadviento' => true,
        'temperatura' => true,
        'consumogasoil' => true,
        'humedad' => true,
        'establecimiento_id' => true,
        'proveedore_id' => true,
        'observaciones' => true,
        'orden_trabajos_dataload_id' => true,        
        'oc' => true,
        'user_id' => true,
        'creado_android' => true,
        'created' => true,
        'modified' => true,
        'deleted' => true,
        'oracle_oc_flag' => true,
        'interface_error' => true,
        'orden_trabajos_estado' => true,
        'establecimiento' => true,
        'proveedore' => true,
        'user' => true
    ];
}
