<?php
namespace Ordenes\Model\Entity;

use Cake\ORM\Entity;

/**
 * OrdenTrabajosOracle Entity
 *
 * @property int $id
 * @property int $orden_trabajo_id
 * @property float $oc
 * @property \Cake\I18n\Date $fecha_oc
 * @property string|null $status
 * @property string|null $aprobado
 * @property \Cake\I18n\Date|null $fecha_aprobacion
 * @property string|null $lote
 * @property int|null $proyecto
 * @property string|null $labor
 * @property float|null $tc
 * @property float|null $cantidad
 * @property float|null $precio
 * @property \Cake\I18n\Time $created
 * @property \Cake\I18n\Time $modified
 *
 * @property \App\Model\Entity\OrdenTrabajo $orden_trabajo
 */
class OrdenTrabajosOracle extends Entity
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
        'oc' => true,
        'fecha_oc' => true,
        'status' => true,
        'aprobado' => true,
        'fecha_aprobacion' => true,
        'lote' => true,
        'proyecto' => true,
        'labor' => true,
        'tc' => true,
        'cantidad' => true,
        'precio' => true,
        'po_distribution' => true,
        'created' => true,
        'modified' => true,
        'orden_trabajo' => true
    ];
    protected $_virtual = [
        'total'
    ];
    
    /* Virtual Field */
    protected function _getTotal() {
        $total_calculado = $this->cantidad * $this->precio;
        $total = round($total_calculado, 2);
        
        return $total;
    }    
}
