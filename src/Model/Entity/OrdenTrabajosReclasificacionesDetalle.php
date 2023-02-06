<?php
namespace Ordenes\Model\Entity;

use Cake\ORM\Entity;

/**
 * OrdenTrabajosReclasificacionesDetalle Entity
 *
 * @property int $id
 * @property int|null $orden_trabajos_reclasificacione_id
 * @property int|null $orden_trabajo_id
 * @property int|null $orden_trabajo_distribucione_id
 * @property int|null $proyecto_id
 * @property int|null $proyectos_labore_id
 * @property string|null $referencia
 * @property string|null $observaciones
 * @property \Cake\I18n\Time|null $created
 * @property \Cake\I18n\Time|null $modified
 * @property \Cake\I18n\Time|null $deleted
 *
 * @property \Ordenes\Model\Entity\OrdenTrabajosReclasificacione $orden_trabajos_reclasificacione
 * @property \Ordenes\Model\Entity\OrdenTrabajo $orden_trabajo
 * @property \Ordenes\Model\Entity\OrdenTrabajoDistribucione $orden_trabajo_distribucione
 * @property \App\Model\Entity\Proyecto $proyecto
 * @property \App\Model\Entity\ProyectosLabore $proyectos_labore
 */
class OrdenTrabajosReclasificacionesDetalle extends Entity
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
        'orden_trabajos_reclasificacione_id' => true,
        'orden_trabajo_id' => true,
        'orden_trabajos_distribucione_id' => true,
        'proyecto_id' => true,
        'proyectos_labore_id' => true,
        'referencia' => true,
        'observaciones' => true,
        'created' => true,
        'modified' => true,
        'deleted' => true,
        'orden_trabajos_reclasificacione' => true,
        'orden_trabajo' => true,
        'orden_trabajos_distribucione' => true,
        'proyecto' => true,
        'proyectos_labore' => true,
    ];
}
