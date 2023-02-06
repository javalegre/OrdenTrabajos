<?php
namespace Ordenes\Controller;

use Ordenes\Controller\AppController;

/**
 * OrdenTrabajosInsumosEntregas Controller
 *
 * @property \App\Model\Table\OrdenTrabajosInsumosEntregasTable $OrdenTrabajosInsumosEntregas
 *
 * @method \App\Model\Entity\OrdenTrabajosInsumosEntrega[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class OrdenTrabajosInsumosEntregasController extends AppController
{
    public function initialize() {
        parent::initialize();
        
        $this->loadModel('Ordenes.OrdenTrabajosInsumos');
        $this->loadModel('Ordenes.OrdenTrabajos');
        
        $this->loadComponent('RequestHandler');
    }
    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['OrdenTrabajosInsumos', 'Productos', 'Unidades', 'Users']
        ];
        $ordenTrabajosInsumosEntregas = $this->paginate($this->OrdenTrabajosInsumosEntregas);

        $this->set(compact('ordenTrabajosInsumosEntregas'));
    }

    /**
     * View method
     *
     * @param string|null $id Orden Trabajos Insumos Entrega id.
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $ordenTrabajosInsumosEntrega = $this->OrdenTrabajosInsumosEntregas->get($id, [
            'contain' => ['OrdenTrabajosInsumos', 'Productos', 'Unidades', 'Users']
        ]);

        $this->set('ordenTrabajosInsumosEntrega', $ordenTrabajosInsumosEntrega);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $result = [];
        if ($this->request->data) {
            $data = $this->request->data;
            
            /* Verifico si la OT no está certificada */
            $ot = $this->OrdenTrabajosInsumosEntregas->OrdenTrabajosInsumos->get($data['distribucion-id-ins'], [
                'contain' => ['OrdenTrabajos']
            ]);
            
            if ( $ot->orden_trabajo->orden_trabajos_estado_id < 4 ) {            
                $fecha = str_replace('/','-', $data['fecha-entrega']);            

                $ordenTrabajosInsumosEntrega = $this->OrdenTrabajosInsumosEntregas->newEntity();
                $ordenTrabajosInsumosEntrega->fecha = date('Y-m-d H:i:s',strtotime($fecha));
                $ordenTrabajosInsumosEntrega->orden_trabajos_insumo_id = $data['distribucion-id-ins'];
                $ordenTrabajosInsumosEntrega->producto_id = $data['producto-id'];
                $ordenTrabajosInsumosEntrega->unidade_id = $data['unidade-id'];
                $ordenTrabajosInsumosEntrega->cantidad = $data['entregado-ins'];
                $ordenTrabajosInsumosEntrega->almacene_id = $data['almacen-entrega'];
                $ordenTrabajosInsumosEntrega->user_id = $this->request->session()->read('Auth.User.id');

                if ($this->OrdenTrabajosInsumosEntregas->save($ordenTrabajosInsumosEntrega)) {
                    /* Verifico si la OT es certificable, en cuyo caso, le cambio el estado a Cerrado */
                    $ordenTrabajo = $this->OrdenTrabajosInsumos->find('all',[
                        'conditions' => ['OrdenTrabajosInsumos.id' => $ordenTrabajosInsumosEntrega->orden_trabajos_insumo_id]
                    ])->first();
                    $id = $ordenTrabajo->orden_trabajo_id;
                    if ($this->OrdenTrabajos->find('Certificable', [ 'IdOrden' => $id ]) === 1){
                        $orden = $this->OrdenTrabajos->get($id);
                        $orden->orden_trabajos_estado_id = 3;
                        $this->OrdenTrabajos->save($orden);
                    }             
                    $id = $ordenTrabajosInsumosEntrega->id;
                    $result['status'] = 'success';
                    $result['message'] = 'Se guardó la entrega correctamente';
                    $result['id'] = $id;                    
                }
            } else {
                $result['status'] = 'error';
                $result['message'] = 'La OT se encuentra certificada o anulada.';
            }
        }
        $this->set(compact('result'));
        $this->set('_serialize', 'result');
        
        $this->RequestHandler->renderAs($this, 'json');        
    }

    /**
     * Edit method
     *
     * @param string|null $id Orden Trabajos Insumos Entrega id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $ordenTrabajosInsumosEntrega = $this->OrdenTrabajosInsumosEntregas->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $ordenTrabajosInsumosEntrega = $this->OrdenTrabajosInsumosEntregas->patchEntity($ordenTrabajosInsumosEntrega, $this->request->getData());
            if ($this->OrdenTrabajosInsumosEntregas->save($ordenTrabajosInsumosEntrega)) {
                $this->Flash->success(__('The orden trabajos insumos entrega has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The orden trabajos insumos entrega could not be saved. Please, try again.'));
        }
        $ordenTrabajosInsumos = $this->OrdenTrabajosInsumosEntregas->OrdenTrabajosInsumos->find('list', ['limit' => 200]);
        $productos = $this->OrdenTrabajosInsumosEntregas->Productos->find('list', ['limit' => 200]);
        $unidades = $this->OrdenTrabajosInsumosEntregas->Unidades->find('list', ['limit' => 200]);
        $users = $this->OrdenTrabajosInsumosEntregas->Users->find('list', ['limit' => 200]);
        $this->set(compact('ordenTrabajosInsumosEntrega', 'ordenTrabajosInsumos', 'productos', 'unidades', 'users'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Orden Trabajos Insumos Entrega id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $ordenTrabajosInsumosEntrega = $this->OrdenTrabajosInsumosEntregas->get($id);
        if ($this->OrdenTrabajosInsumosEntregas->delete($ordenTrabajosInsumosEntrega)) {
            $this->Flash->success(__('The orden trabajos insumos entrega has been deleted.'));
        } else {
            $this->Flash->error(__('The orden trabajos insumos entrega could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
