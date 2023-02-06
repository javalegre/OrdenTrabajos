<?php
namespace Ordenes\Model\Entity;

use Cake\ORM\Entity;

/**
 * OrdenTrabajosCertificacione Entity
 *
 * @property int $id
 * @property int $orden_trabajos_distribucione_id
 * @property int $orden_trabajo_id
 * @property \Cake\I18n\Time|null $fecha_inicio
 * @property \Cake\I18n\Time|null $fecha_final
 * @property float|null $has
 * @property int|null $moneda_id
 * @property float|null $tipo_cambio
 * @property float|null $precio_final
 * @property string|null $observaciones
 * @property array|null $imagenes
 * @property int $user_id
 * @property \Cake\I18n\Time|null $created
 * @property \Cake\I18n\Time|null $modified
 * @property \Cake\I18n\Time|null $deleted
 * @property string|null $oracle_flag
 * @property string|null $interface_error
 *
 * @property \App\Model\Entity\OrdenTrabajosDistribucione $orden_trabajos_distribucione
 * @property \App\Model\Entity\OrdenTrabajo $orden_trabajo
 * @property \App\Model\Entity\Moneda $moneda
 * @property \App\Model\Entity\User $user
 */
class OrdenTrabajosCertificacione extends Entity
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
        'orden_trabajos_distribucione_id' => true,
        'orden_trabajo_id' => true,
        'fecha_inicio' => true,
        'fecha_final' => true,
        'has' => true,
        'moneda_id' => true,
        'tipo_cambio' => true,
        'precio_final' => true,
        'observaciones' => true,
        'imagenes' => true,
        'user_id' => true,
        'created' => true,
        'modified' => true,
        'deleted' => true,
        'oracle_flag' => true,
        'interface_error' => true,
        'cotizacion' => true,
        'orden_trabajos_distribucione' => true,
        'orden_trabajo' => true,
        'moneda' => true,
        'user' => true
    ];
}
