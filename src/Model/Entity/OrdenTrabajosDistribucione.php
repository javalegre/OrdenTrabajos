<?php
namespace Ordenes\Model\Entity;
use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

/**
 * OrdenTrabajosDistribucione Entity
 *
 * @property int $id
 * @property int $orden_trabajo_id
 * @property int $proyectos_labore_id
 * @property int $unidade_id
 * @property int $proyecto_id
 * @property int $lote_id
 * @property float $superficie
 * @property float $importe
 * @property int $moneda_id
 * @property \Cake\I18n\Date $created
 * @property \Cake\I18n\Date $modified
 *
 * @property \App\Model\Entity\OrdenTrabajo $orden_trabajo
 * @property \App\Model\Entity\Labore $labore
 * @property \App\Model\Entity\Unidade $unidade
 * @property \App\Model\Entity\Proyecto $proyecto
 * @property \App\Model\Entity\Lote $lote
 * @property \App\Model\Entity\Moneda $moneda
 * @property \App\Model\Entity\OrdenTrabajosCertificacione[] $orden_trabajos_certificaciones
 * @property \App\Model\Entity\OrdenTrabajosInsumo[] $orden_trabajos_insumos
 */
class OrdenTrabajosDistribucione extends Entity
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
    protected $_virtual = ['total', 'fecha_certificacion', 'total_certificado', 'fecha_aplicacion', 'importe_certificado'];
    
    protected $_accessible = [
        'orden_trabajo_id' => true,
        'proyectos_labore_id' => true,
        'unidade_id' => true,
        'proyecto_id' => true,
        'lote_id' => true,
        'tecnicas_aplicacione_id' => true,
        'superficie' => true,
        'importe' => true,
        'moneda_id' => true,
        'oracle_oc' => true,
        'created' => true,
        'modified' => true,
        'orden_trabajo' => true,
        'unidade' => true,
        'proyecto' => true,
        'lote' => true,
        'moneda' => true,
        'oc' => true
    ];
    
    /* Virtual Field */
    protected function _getTotal() {
        $total_calculado = $this->superficie * $this->importe;
        $total = round($total_calculado, 2);
        
        return $total;
    }
    
    /* Obtengo la ultima fecha de certificacion de esta linea */
    protected function _getFechaCertificacion() {
            $Certificaciones = TableRegistry::get('Ordenes.OrdenTrabajosCertificaciones');
            $certificacion = $Certificaciones->find('all', ['fields' => ['created']])->where(['orden_trabajos_distribucione_id' => $this->id])->order(['created' => 'desc'])->first();
            if ($certificacion) {
                return $certificacion->created;
            }
            return null;
    }

    /* Obtengo la fecha de aplicación/laboreo real */
    protected function _getFechaAplicacion() {
            $Certificaciones = TableRegistry::get('Ordenes.OrdenTrabajosCertificaciones');
            $certificacion = $Certificaciones->find('all', ['fields' => ['fecha_final']])->where(['orden_trabajos_distribucione_id' => $this->id])->order(['created' => 'desc'])->first();
            if ($certificacion) {
                return $certificacion->fecha_final;
            }
            return null;
    }    
    /* Obtengo la ultima fecha de certificacion de esta linea */
    protected function _getTotalCertificado() {
            $Certificaciones = TableRegistry::get('Ordenes.OrdenTrabajosCertificaciones');
            $certificacion = $Certificaciones->find('all')->where(['orden_trabajos_distribucione_id' => $this->id])->sumOf('has');
            if ($certificacion) {
                return $certificacion;
            }
            return null;
    }
    
    /* Obtengo la ultima fecha de certificacion de esta linea */
    protected function _getImporteCertificado() {
            $Certificaciones = TableRegistry::get('Ordenes.OrdenTrabajosCertificaciones');
            $certificacion = $Certificaciones->find('all', ['fields' => ['precio_final']])->where(['orden_trabajos_distribucione_id' => $this->id])->order(['created' => 'desc'])->first();
            if ($certificacion) {
                return $certificacion->precio_final;
            }
            return null;
    }
}