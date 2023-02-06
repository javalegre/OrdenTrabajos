<?php
namespace Ordenes\Model\Entity;

use Cake\ORM\Entity;

/**
 * OrdenTrabajosMapeo Entity
 *
 * @property int $id
 * @property int|null $orden_trabajo_id
 * @property int|null $orden_trabajos_distribucione_id
 * @property int|null $orden_trabajos_certificacione_id
 * @property int|null $lote_id
 * @property float|null $superficie
 * @property int|null $proyectos_labore_id
 * @property int|null $campanias_tipo_id
 * @property int|null $calidad_mapeo_id
 * @property int|null $sms
 * @property int|null $pdf
 * @property int|null $problema
 * @property string|null $comentario
 * @property int|null $user_id
 * @property \Cake\I18n\Time|null $created
 * @property \Cake\I18n\Time|null $modified
 *
 * @property \Ordenes\Model\Entity\OrdenTrabajo $orden_trabajo
 * @property \Ordenes\Model\Entity\OrdenTrabajosDistribucione $orden_trabajos_distribucione
 * @property \Ordenes\Model\Entity\OrdenTrabajosCertificacione $orden_trabajos_certificacione
 * @property \Ordenes\Model\Entity\Lote $lote
 * @property \Ordenes\Model\Entity\ProyectosLabore $proyectos_labore
 * @property \Ordenes\Model\Entity\MapeosCampaniasTipo $mapeos_campanias_tipo
 * @property \Ordenes\Model\Entity\MapeosCalidade $mapeos_calidade
 * @property \Ordenes\Model\Entity\MapeosProblema $mapeos_problema
 * @property \Ordenes\Model\Entity\User $user
 */
class OrdenTrabajosMapeo extends Entity
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
        'orden_trabajos_distribucione_id' => true,
        'orden_trabajos_certificacione_id' => true,
        'lote_id' => true,
        'superficie' => true,
        'proyectos_labore_id' => true,
        'mapeos_campanias_tipo_id' => true,
        'mapeos_calidade_id' => true,
        'sms' => true,
        'pdf' => true,
        'mapeos_problema_id' => true,
        'comentario' => true,
        'user_id' => true,
        'created' => true,
        'modified' => true,
        'orden_trabajo' => true,
        'orden_trabajos_distribucione' => true,
        'orden_trabajos_certificacione' => true,
        'lote' => true,
        'proyectos_labore' => true,
        'campanias_tipo' => true,
        'calidad_mapeo' => true,
        'user' => true,
    ];
}
