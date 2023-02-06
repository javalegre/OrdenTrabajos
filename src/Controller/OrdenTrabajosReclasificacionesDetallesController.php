<?php
namespace Ordenes\Controller;

use Ordenes\Controller\AppController;

/**
 * OrdenTrabajosReclasificacionesDetalles Controller
 *
 * @property \Ordenes\Model\Table\OrdenTrabajosReclasificacionesDetallesTable $OrdenTrabajosReclasificacionesDetalles
 *
 * @method \Ordenes\Model\Entity\OrdenTrabajosReclasificacionesDetalle[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class OrdenTrabajosReclasificacionesDetallesController extends AppController
{
    public function initialize() {
        parent::initialize();
        $this->loadModel('Establecimientos');
        $this->loadModel('Ordenes.OrdenTrabajosReclasificaciones');
        $this->loadModel('Ordenes.OrdenTrabajosDistribuciones');
        $this->loadModel('Ordenes.OrdenTrabajosInsumos');
        $this->loadModel('Proyectos');
        $this->loadModel('ProyectosLabores');
        
        $this->loadComponent('RequestHandler');
    }
    
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index()
    {
        $columns = [[
                        'field' => 'OrdenTrabajosDistribuciones',
                        'data' => 'orden_trabajos_distribucione.orden_trabajo_id',
                        'orderable' => false,
                        'searchable' => false
                    ], [
                        'field' => 'OrdenTrabajosDistribuciones.Proyectos',
                        'data' => 'orden_trabajos_distribucione.proyecto.nombre',
                        'orderable' => false,
                        'searchable' => false
                    ], [
                        'field' => 'OrdenTrabajosDistribuciones.ProyectosLabores',
                        'data' => 'orden_trabajos_distribucione.proyectos_labore.nombre'
                    ], [
                        'field' => 'created',
                        'data' => 'created'
                    ], [
                        'field' => 'Proyectos',
                        'data' => 'proyecto.nombre'
                    ], [
                        'field' => 'ProyectosLabores',
                        'data' => 'proyectos_labore.nombre'
                    ], [
                        'field' => 'referencia',
                        'data' => 'referencia'
                    ], [
                        'field' => 'OrdenTrabajosReclasificaciones.id',
                        'data' => 'id'
                    ]];  
        
        /* ****************************************************************** */
        /* Filtros                                                            */
        /* ****************************************************************** */
        $filtros = [];
        
        $orden_trabajos_reclasificacione_id = $this->request->getQuery('orden_trabajos_reclasificacione_id');
        if ($orden_trabajos_reclasificacione_id) {
            $filtros[] = "OrdenTrabajosReclasificacionesDetalles.orden_trabajos_reclasificacione_id = '".$orden_trabajos_reclasificacione_id."'";
        }
        /* ****************************************************************** */

        $data = $this->DataTables->find('Ordenes.OrdenTrabajosReclasificacionesDetalles','all', [
            'contain' => ['OrdenTrabajosDistribuciones' => ['Proyectos', 'ProyectosLabores'],
                          'Proyectos', 'ProyectosLabores'],
            'conditions' => [$filtros]
        ], $columns);         
        
       // $this->set(compact('establecimientos'));
        
        /* ************************************************************ */
        /* Datatables Server Side Processing                            */
        /* ************************************************************ */
        $this->set('columns', $columns);
        $this->set('data', $data);
        $this->set('_serialize', array_merge($this->viewVars['_serialize'], ['data']));
    }
    
    /**
     * View method
     *
     * @param string|null $id Orden Trabajos Reclasificaciones Detalle id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $ordenTrabajosReclasificacionesDetalle = $this->OrdenTrabajosReclasificacionesDetalles->get($id, [
            'contain' => ['OrdenTrabajosReclasificaciones', 'OrdenTrabajos', 'OrdenTrabajoDistribuciones', 'Proyectos'],
        ]);

        $this->set('ordenTrabajosReclasificacionesDetalle', $ordenTrabajosReclasificacionesDetalle);
    }

    /**
     * Add method
     *
     * Agrega una linea nueva a la reclasificacion.
     *  - Modifica la linea de orden_trabajos_distribuciones, colocando allí los nuevos datos de proyecto y labor.
     *  - Guarda la linea y genera la leyenda correspondiente.
     */
    public function add()
    {
        $orden_trabajos_reclasificacione_id = $this->request->getQuery('orden_trabajos_reclasificacione_id');
        $ordenTrabajosReclasificacionesDetalle = $this->OrdenTrabajosReclasificacionesDetalles->newEntity();
        if ($this->request->is('post')) {
            /*
             *  Modificamos la linea de distribucion a reclasificar con los datos nuevos 
             */
            $distribucion = $this->OrdenTrabajosDistribuciones->get($this->request->getData('orden_trabajos_distribucione_id'));
            $distribucion->proyecto_id = $this->request->getData('proyecto_seleccionado');
            $distribucion->proyectos_labore_id = $this->request->getData('labor_seleccionada');
            $this->OrdenTrabajosDistribuciones->save($distribucion);
            
            /**
             * Ahora actualizo el proyecto_id en la tabla de insumos
             */
            $this->OrdenTrabajosInsumos->updateAll([
                'proyectos_labore_id' => $this->request->getData('labor_seleccionada')
            ], [
                'orden_trabajos_distribucione_id' => $this->request->getData('orden_trabajos_distribucione_id')
            ]);
            
            /*
             * Ahora guardamos los datos nuevos de la reclasificacion
             */
            $ordenTrabajosReclasificacionesDetalle = $this->OrdenTrabajosReclasificacionesDetalles->patchEntity($ordenTrabajosReclasificacionesDetalle, $this->request->getData());
            $ordenTrabajosReclasificacionesDetalle->referencia = $this->armarReferenciaReclasificacion($ordenTrabajosReclasificacionesDetalle);
            
            if ($this->OrdenTrabajosReclasificacionesDetalles->save($ordenTrabajosReclasificacionesDetalle)) {
                $this->set(['respuesta' => ['status' => 'success', 'message' => 'Se agregó la reclasificación correctamente.'],
                            '_serialize' => 'respuesta']);
            } else {
                $this->set(['respuesta' => ['status' => 'error', 'message' => 'Ocurrió un error al guardar la reclasificación.'],
                    '_serialize' => 'respuesta']);
            }
            $this->RequestHandler->renderAs($this, 'json');    
        }

        $this->set(compact('ordenTrabajosReclasificacionesDetalle', 'orden_trabajos_reclasificacione_id'));
        
        /* Para el modal */
        $this->render('add', 'ajax');
    }

    /**
     * Edit method
     *
     * @param string|null $id Orden Trabajos Reclasificaciones Detalle id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $ordenTrabajosReclasificacionesDetalle = $this->OrdenTrabajosReclasificacionesDetalles->get($id, [
            'contain' => [],
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $ordenTrabajosReclasificacionesDetalle = $this->OrdenTrabajosReclasificacionesDetalles->patchEntity($ordenTrabajosReclasificacionesDetalle, $this->request->getData());
            if ($this->OrdenTrabajosReclasificacionesDetalles->save($ordenTrabajosReclasificacionesDetalle)) {
                $this->Flash->success(__('The orden trabajos reclasificaciones detalle has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The orden trabajos reclasificaciones detalle could not be saved. Please, try again.'));
        }
        $ordenTrabajosReclasificaciones = $this->OrdenTrabajosReclasificacionesDetalles->OrdenTrabajosReclasificaciones->find('list', ['limit' => 200]);
        $ordenTrabajos = $this->OrdenTrabajosReclasificacionesDetalles->OrdenTrabajos->find('list', ['limit' => 200]);
        $ordenTrabajoDistribuciones = $this->OrdenTrabajosReclasificacionesDetalles->OrdenTrabajoDistribuciones->find('list', ['limit' => 200]);
        $proyectos = $this->OrdenTrabajosReclasificacionesDetalles->Proyectos->find('list', ['limit' => 200]);
        $this->set(compact('ordenTrabajosReclasificacionesDetalle', 'ordenTrabajosReclasificaciones', 'ordenTrabajos', 'ordenTrabajoDistribuciones', 'proyectos'));
    }

    /**
     * Delete method
     *
     * Antes de eliminar la linea, modificamos la linea de distribucion de la orden de trabajo
     * 
     * @param string|null $id Orden Trabajos Reclasificaciones Detalle id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $ordenTrabajosReclasificacionesDetalle = $this->OrdenTrabajosReclasificacionesDetalles->get($id);
        
        /* Dejamos la linea de distribucion de la orden de trabajo como estaba antes. */
        $ordenTrabajosDistribucione = $this->OrdenTrabajosDistribuciones->get($ordenTrabajosReclasificacionesDetalle->orden_trabajos_distribucione_id);
        $ordenTrabajosDistribucione->proyecto_id = $ordenTrabajosReclasificacionesDetalle->proyecto_id;
        $ordenTrabajosDistribucione->proyectos_labore_id = $ordenTrabajosReclasificacionesDetalle->proyectos_labore_id;
        
        
        if (!$this->OrdenTrabajosDistribuciones->save($ordenTrabajosDistribucione)) {
            $this->set(['respuesta' => ['status' => 'error', 'message' => 'No se pudo volver a actualizar la Orden de Trabajo.'],
                        '_serialize' => 'respuesta']);
            $this->RequestHandler->renderAs($this, 'json');
            return;
        }
        
        if ($this->OrdenTrabajosReclasificacionesDetalles->delete($ordenTrabajosReclasificacionesDetalle)) {
            $this->set(['respuesta' => ['status' => 'success', 'message' => 'Se eliminó la linea correctamente.'],
                        '_serialize' => 'respuesta']);
            $this->RequestHandler->renderAs($this, 'json');
            return;
        } 
        
        $this->set(['respuesta' => ['status' => 'error', 'message' => 'No se pudo volver eliminar la linea.'],
                    '_serialize' => 'respuesta']);
        $this->RequestHandler->renderAs($this, 'json');
        
    }
    
    /**
     * BuscarOrdenes method
     *
     * Busca todas las ordenes de trabajo de un establecimiento que se pueden reclasificar.
     * 
     * Devuelve un array con los datos de una Orden de Trabajo especificada, con los datos
     * del proyecto y labor asociados.
     * 
     * Recibe los siguientes parametros por POST
     * @param string $orden_trabajo_id Numero de Orden de Trabajo
     * @param string $orden_trabajos_reclasificacione_id Numero de Reclasificacion, lo usamos para verificar que sea de la misma organizacion
     * @return array Array con las Ordenes que se pueden reclasificar.
     */
    public function buscarOrdenes()
    {
        
        /* Busco la reclasificacion para obtener el ID del establecimiento */
        $orden_trabajos_reclasificacione = $this->OrdenTrabajosReclasificaciones->get($this->request->getData('orden_trabajos_reclasificacione_id'));
        
        /* Ahora busco las lineas de distribuciones */
        $distribuciones = $this->OrdenTrabajosDistribuciones->find('all', [
            'contain' => ['Proyectos' => ['CampaniaMonitoreos', 'Cultivos'], 
                          'ProyectosLabores',
                          'Lotes' => ['Sectores'],
                          'OrdenTrabajos'],
            'conditions' => ['OrdenTrabajosDistribuciones.orden_trabajo_id' => $this->request->getData('orden_trabajo_id'),
                             'OrdenTrabajos.establecimiento_id' => $orden_trabajos_reclasificacione->establecimiento_id]
        ]);
        
        /* 
         * Reviso si se encuentran los datos de la OT en el establecimiento seleccionado para reclasificar.
         */
        if (!$distribuciones) {
            $this->set(['respuesta' => ['status' => 'error', 'message' => 'No se encontró la OT o la misma no corresponde a este establecimiento.'],
                        '_serialize' => 'respuesta']);
            $this->RequestHandler->renderAs($this, 'json');
            return false;
        }
        
        /* 
         * Devuelvo los datos encontrados.
         */
        $this->set(['respuesta' => ['status' => 'success', 'message' => 'Los datos fueron encontrados correctamente.', 'data' => $distribuciones],
                    '_serialize' => 'respuesta']);
        $this->RequestHandler->renderAs($this, 'json');
    }
    
    /**
     * BuscarProyectosDestinos method
     *
     * Busca todos los proyectos existentes en una organizacion 
     * 
     * Devuelve un array con los proyectos existentes para una organizacion dada, filtrando por establecimiento
     * 
     * Recibe los siguientes parametros por POST
     * @param string $reclasificacion_id Id de la reclasificacion, lo uso para ver el establecimiento_id
     * @param string $search Texto para filtrar los proyectos
     * @return array Array con los proyectos posibles para reclasificar
     */
    public function buscarProyectosDestinos()
    {
 
        $term = $this->request->query['q'];
        
        $establecimiento_id = $this->OrdenTrabajosReclasificaciones->get($this->request->query['reclasificacion_id'])->establecimiento_id;
        
        $results = $this->Proyectos->find('all', [
            'conditions' => ['Proyectos.nombre LIKE' => '%'.$term.'%',
                             'Proyectos.establecimiento_id' => $establecimiento_id],
            'contain' => ['CampaniaMonitoreos', 'Cultivos']
        ]);
        
        $this->set(compact('results'));
        $this->set('_serialize', ['results']);
    }
    
    /**
     * BuscarProyectosLaboresDestinos method
     *
     * Busca todos las labores existentes en el proyecto especificado 
     * 
     * Recibe los siguientes parametros por POST
     * @param string $proyecto_id Id del proyecto seleccionado
     * @param string $search Texto para filtrar las labores
     * @return array Array con las labores de un proyecto
     */
    public function buscarProyectosLaboresDestinos()
    {
 
        $term = $this->request->query['q'];
        
        $results = $this->ProyectosLabores->find('all', [
            'conditions' => ['ProyectosLabores.nombre LIKE' => '%'.$term.'%',
                             'ProyectosLabores.proyecto_id' => $this->request->query['proyecto_id']],
            'contain' => ['ProyectosGastosCategorias']
        ]);
        
        $this->set(compact('results'));
        $this->set('_serialize', ['results']);
    }
    
    /**
     * Armar Referencia Reclasificacion
     * 
     * Toma los datos de una linea de reclasificacion y arma la referencia con el siguiente esquema
     * 
     * Recla a CultivoDestino_OT:NumeroOt_CodigoEstablecimiento_LaborDestino_Lote
     * 
     * Ej: Recla a SJ1NOGMO_OT:113899_SLU_PULV. TER_SLU-31
     * 
     * @param type $ordenTrabajosReclasificacionesDetalle
     */
    private function armarReferenciaReclasificacion($ordenTrabajosReclasificacionesDetalle = null) {
        
        $distribucion = $this->OrdenTrabajosDistribuciones->get($ordenTrabajosReclasificacionesDetalle->orden_trabajos_distribucione_id, [
                            'contain' => ['Proyectos' => ['Establecimientos'], 'ProyectosLabores', 'Lotes']
                        ]);
        
        $referencia = "Recla a ".$distribucion->proyecto->cultivo."_OT:".$distribucion->orden_trabajo_id."_".$distribucion->proyecto->establecimiento->organizacion."_".
                      $distribucion->proyectos_labore->nombre."_".$distribucion->lote->nombre;
        
        return $referencia;
    }
}
