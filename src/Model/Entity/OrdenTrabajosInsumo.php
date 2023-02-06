<?php
namespace Ordenes\Model\Entity;

use Cake\ORM\Entity;
use Cake\ORM\TableRegistry;

/**
 * OrdenTrabajosInsumo Entity
 *
 * @property int $id
 * @property int $orden_trabajo_id
 * @property int $producto_id
 * @property int $orden_trabajos_distribucione_id
 * @property float $dosis
 * @property float $cantidad
 * @property int $unidade_id
 * @property float $cantidad_stock
 * @property float $utilizado
 * @property float $dosis_aplicada
 * @property int $almacene_id
 * @property \Cake\I18n\Date $created
 * @property \Cake\I18n\Date $modified
 *
 * @property \App\Model\Entity\OrdenTrabajo $orden_trabajo
 * @property \App\Model\Entity\Producto $producto
 * @property \App\Model\Entity\OrdenTrabajosDistribucione $orden_trabajos_distribucione
 * @property \App\Model\Entity\Unidade $unidade
 * @property \App\Model\Entity\Almacene $almacene
 * @property \App\Model\Entity\OrdenTrabajosInsumosDevolucione[] $orden_trabajos_insumos_devoluciones
 * @property \App\Model\Entity\OrdenTrabajosInsumosEntrega[] $orden_trabajos_insumos_entregas
 */
class OrdenTrabajosInsumo extends Entity
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
    protected $_virtual = ['dosis_aplicada_real', 'entregas', 'devoluciones'];
    
    protected $_accessible = [
        'orden_trabajo_id' => true,
        'producto_id' => true,
        'orden_trabajos_distribucione_id' => true,
        'proyectos_labore_id' => true,
        'dosis' => true,
        'cantidad' => true,
        'unidade_id' => true,
        'cantidad_stock' => true,
        'utilizado' => true,
        'dosis_aplicada' => true,
        'almacene_id' => true,
        'created' => true,
        'modified' => true,
        'orden_trabajo' => true,
        'producto' => true,
        'orden_trabajos_distribucione' => true,
        'unidade' => true,
        'almacene' => true,
        'orden_trabajos_insumos_devoluciones' => true,
        'orden_trabajos_insumos_entregas' => true
    ];
    
    /**
     * Devuelvo el total entregado a este insumo
     */
    protected function _getEntregas() {
        $entregas = TableRegistry::get('Ordenes.OrdenTrabajosInsumosEntregas');
        $entregado = $entregas->find('all')->where(['orden_trabajos_insumo_id' => $this->id, 'deleted IS NULL'])->sumOf('cantidad');
        
        return $entregado ? $entregado : 0;
    }
    
    /**
     *  Devuelvo el total devuelto a este insumo
     */
    protected function _getDevoluciones() {
        $devoluciones = TableRegistry::get('Ordenes.OrdenTrabajosInsumosDevoluciones');
        $devuelto = $devoluciones->find('all')->where(['orden_trabajos_insumo_id' => $this->id, 'deleted IS NULL'])->sumOf('cantidad');
        
        return $devuelto ? $devuelto : 0;
    } 
    
    /**
     * Dosis Real Aplicada
     * 
     * Sumo todas las cantidades entregadas y devueltas, y las divido por las has certificadas.
     * 
     */
    protected function _getDosisAplicadaReal() {
        $distribuciones = TableRegistry::get('Ordenes.OrdenTrabajosDistribuciones');
        
        $total_entregado = $this->entregas ? $this->entregas : 0;
        $total_devuelto = $this->devoluciones ? $this->devoluciones : 0;
        
        $distribucion = $distribuciones->find('all')->where(['id' => $this->orden_trabajos_distribucione_id])->first();
        
        if ($distribucion && $distribucion->total_certificado) {
            $dosis_calculada = ($total_entregado - $total_devuelto) / $distribucion->total_certificado;
            return round($dosis_calculada, 4);
        }
        return '0';
    }
}
