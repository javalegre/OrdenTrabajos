<?php
namespace Ordenes\Controller;

use Ordenes\Controller\AppController;

/**
 * OrdenTrabajosDistribuciones Controller
 *
 * @property \App\Model\Table\OrdenTrabajosDistribucionesTable $OrdenTrabajosDistribuciones
 *
 * @method \App\Model\Entity\OrdenTrabajosDistribucione[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class OrdenTrabajosDistribucionesController extends AppController
{
    public function initialize() {
        parent::initialize();
        $this->loadModel('Ordenes.OrdenTrabajosDistribucionesTarifarios');
    }
    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['Lotes', 'OrdenTrabajos', 'Actividades', 'Ambientes']
        ];
        $ordenTrabajosDistribuciones = $this->paginate($this->OrdenTrabajosDistribuciones);

        $this->set(compact('ordenTrabajosDistribuciones'));
    }

    /**
     * View method
     *
     * @param string|null $id Orden Trabajos Distribucione id.
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $ordenTrabajosDistribucione = $this->OrdenTrabajosDistribuciones->get($id, [
            'contain' => ['Lotes', 'OrdenTrabajos', 'Actividades', 'Ambientes']
        ]);

        $this->set('ordenTrabajosDistribucione', $ordenTrabajosDistribucione);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $result = [];
        
        /* Obtengo los datos que pasamos como parametros */
        $data = json_decode($this->request->getData('datos'));
        
        /* 
         * Con los cambios nuevos ingresados en el tarifario, verifico si encuentro
         * el separador, en caso de encontrarlo, separo y guardo el ID de labor.
         */
        $guion = strpos($data->labor,"-");
        if ($guion ) {
            $labor = explode("-", $data->labor);
            $data->labor = $labor[0];
        }
        
        if (!empty($data->id)) {
            /* Estoy editando, así que obtengo el objeto */
            $dist = $this->OrdenTrabajosDistribuciones->get($data->id);
        } else {
            /* Creo una entidad */
            $dist = $this->OrdenTrabajosDistribuciones->newEntity();
            $dist->orden_trabajo_id = $data->orden_trabajo_id;
        }
        $dist->proyectos_labore_id = $data->labor;
        $dist->unidade_id = $data->unmedida;
        $dist->proyecto_id = $data->cc;
        $dist->lote_id = $data->lote;
        $dist->superficie = $data->has;
        $dist->tecnicas_aplicacione_id = $data->tecnica;
        /* Si no seleccionó ningun lote, lo marco como lote 0 "Sin Lote */
        if (empty($dist->lote_id)) {
            $dist->lote_id = '0';
        }
        $dist->moneda_id = $data->moneda;
        $dist->importe = $data->importe;
         
        /* Guardo la entidad */
        if ($this->OrdenTrabajosDistribuciones->save($dist)) {
            /* Agrego la linea de distribución al tarifario */
            if ($data->tarifario) {
                $this->tarifario($data->tarifario, $dist);
            }
            
            $tarifario = $this->OrdenTrabajosDistribucionesTarifarios->find('all', [
                'contain' => ['OrdenTrabajosDistribuciones', 'ProyectosLaboresTarifarios'],
                'conditions' => ['OrdenTrabajosDistribuciones.orden_trabajo_id' => $data->orden_trabajo_id]
            ])->toArray();            
            
            $result = [ 'status'  => 'success',
                        'message' => 'Los cambios se guardaron correctamente.',
                        'tarifario' => $tarifario
                      ];            
        } else {
            $result = [ 'status'  => 'error',
                        'message' => 'No se pudo guardar correctamente.'];
        }
        
        $this->set(compact('result'));
        $this->set('_serialize', 'result');
        
        $this->RequestHandler->renderAs($this, 'json');
    }

    /**
     * Edit method
     *
     * @param string|null $id Orden Trabajos Distribucione id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        
        
        $ordenTrabajosDistribucione = $this->OrdenTrabajosDistribuciones->get($id, [
            'contain' => ['OrdenTrabajos' => ['Establecimientos' => ['EstablecimientosOrganizaciones' => ['Monedas']]],
                          'OrdenTrabajosCertificaciones' => ['Monedas'],
                          'OrdenTrabajosDistribucionesTarifarios' => ['Proveedores' => ['fields' => ['id', 'nombre']], 'ProyectosLaboresTarifarios'],
                          'ProyectosLabores' => ['fields' => ['id', 'nombre']],
                          'Monedas',
                          'Lotes' => ['fields' => ['id', 'nombre']]]
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $ordenTrabajosDistribucione = $this->OrdenTrabajosDistribuciones->patchEntity($ordenTrabajosDistribucione, $this->request->getData());
            if ($this->OrdenTrabajosDistribuciones->save($ordenTrabajosDistribucione)) {
                $this->Flash->success(__('The orden trabajos distribucione has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The orden trabajos distribucione could not be saved. Please, try again.'));
        }
        $this->set(compact('ordenTrabajosDistribucione'));
        $this->set('_serialize', ['ordenTrabajosDistribucione']);
    }

    /**
     * Delete method
     *
     * @param string|null $id Orden Trabajos Distribucione id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $ordenTrabajosDistribucione = $this->OrdenTrabajosDistribuciones->get($id);
        if ($this->OrdenTrabajosDistribuciones->delete($ordenTrabajosDistribucione)) {
            $this->Flash->success(__('The orden trabajos distribucione has been deleted.'));
        } else {
            $this->Flash->error(__('The orden trabajos distribucione could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
    
    /**
     * Tarifario method
     * 
     * Si la linea de distribución está con un tarifario, agrego la linea para
     * poder llevar el trackeo en la certificación y asociarlo a generar la asociacion
     * con la OT de alquiler
     */
    private function tarifario($id_tarifario = null, $distribucion = null) {
        $this->loadModel('ProyectosLaboresTarifarios');
        
        /* Busco si ya existe el tarifario de distribuciones*/
        $distribucion_tarifario = $this->OrdenTrabajosDistribucionesTarifarios->find('all', [
            'conditions' => ['orden_trabajos_distribucione_id' => $distribucion->id]
        ])->first();
        
        /* Si no existe, creo una entidad nueva */
        if (!$distribucion_tarifario) {
            $distribucion_tarifario = $this->OrdenTrabajosDistribucionesTarifarios->newEntity();
        }
        
        $tarifario = $this->ProyectosLaboresTarifarios->get($id_tarifario);
        
        $distribucion_tarifario->orden_trabajos_distribucione_id = $distribucion->id;
        $distribucion_tarifario->proyectos_labores_tarifario_id = $id_tarifario;
        $distribucion_tarifario->tarifa = $distribucion->importe;
        $distribucion_tarifario->alquiler = $tarifario->alquiler ? '1' : '0';
        $distribucion_tarifario->porcentaje = $tarifario->porcentaje;
        $distribucion_tarifario->proveedore_id = $tarifario->proveedore_id;
        
        $this->OrdenTrabajosDistribucionesTarifarios->save( $distribucion_tarifario );
        
        
    }
}
