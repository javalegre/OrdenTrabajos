<?php
namespace Ordenes\Model\Entity;

use Cake\ORM\Entity;

/**
 * OrdenTrabajosDistribucionesTarifario Entity
 *
 * @property int $id
 * @property int|null $orden_trabajos_distribucione_id
 * @property int $proyectos_labores_tarifario_id
 * @property float|null $tarifa
 * @property int|null $alquiler
 * @property float|null $porcentaje
 * @property int|null $proveedore_id
 * @property int|null $orden_trabajo_alquiler_id
 * @property \Cake\I18n\Time|null $created
 * @property \Cake\I18n\Time|null $modified
 * @property \Cake\I18n\Time|null $deleted
 *
 * @property \App\Model\Entity\OrdenTrabajosDistribucione $orden_trabajos_distribucione
 * @property \App\Model\Entity\ProyectosLaboresTarifario $proyectos_labores_tarifario
 * @property \App\Model\Entity\Proveedore $proveedore
 * @property \App\Model\Entity\OrdenTrabajo $orden_trabajo
 */
class OrdenTrabajosDistribucionesTarifario extends Entity
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
        'proyectos_labores_tarifario_id' => true,
        'tarifa' => true,
        'alquiler' => true,
        'porcentaje' => true,
        'proveedore_id' => true,
        'orden_trabajo_alquiler_id' => true,
        'created' => true,
        'modified' => true,
        'deleted' => true,
        'orden_trabajos_distribucione' => true,
        'proyectos_labores_tarifario' => true,
        'proveedore' => true,
        'orden_trabajo' => true
    ];
}
