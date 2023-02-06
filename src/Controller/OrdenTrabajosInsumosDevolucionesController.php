<?php
namespace Ordenes\Controller;

use Ordenes\Controller\AppController;

/**
 * OrdenTrabajosInsumosDevoluciones Controller
 *
 * @property \App\Model\Table\OrdenTrabajosInsumosDevolucionesTable $OrdenTrabajosInsumosDevoluciones
 *
 * @method \App\Model\Entity\OrdenTrabajosInsumosDevolucione[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class OrdenTrabajosInsumosDevolucionesController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['OrdenTrabajosInsumos', 'Productos', 'Users']
        ];
        $ordenTrabajosInsumosDevoluciones = $this->paginate($this->OrdenTrabajosInsumosDevoluciones);

        $this->set(compact('ordenTrabajosInsumosDevoluciones'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $result = [];
        if (!empty($this->request->data)) {
            $data = $this->request->data;
            
            /* Verifico si la OT no está certificada */
            $ot = $this->OrdenTrabajosInsumosDevoluciones->OrdenTrabajosInsumos->get($data['distribucion-id-dev'], [
                'contain' => ['OrdenTrabajos']
            ]);
            
            if ( $ot->orden_trabajo->orden_trabajos_estado_id < 4 ) {
                $fecha = str_replace('/','-', $data['fecha-devolucion']);            

                $ordenTrabajosInsumosDevolucione = $this->OrdenTrabajosInsumosDevoluciones->newEntity();
                $ordenTrabajosInsumosDevolucione->fecha = date('Y-m-d H:i:s',strtotime($fecha));
                $ordenTrabajosInsumosDevolucione->orden_trabajos_insumo_id = $data['distribucion-id-dev'];
                $ordenTrabajosInsumosDevolucione->producto_id = $data['producto-id-dev'];
                $ordenTrabajosInsumosDevolucione->cantidad = $data['devuelto-dev'];
                $ordenTrabajosInsumosDevolucione->almacene_id = $data['almacen-devolucion'];
                $ordenTrabajosInsumosDevolucione->dispositivo_id = '0';
                $ordenTrabajosInsumosDevolucione->user_id = $this->request->session()->read('Auth.User.id');

                if ($this->OrdenTrabajosInsumosDevoluciones->save($ordenTrabajosInsumosDevolucione)) {
                    $id = $ordenTrabajosInsumosDevolucione->id;
                    $result['status'] = 'success';
                    $result['message'] = 'Se guardó la devolución correctamente';
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
     * @param string|null $id Orden Trabajos Insumos Devolucione id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $ordenTrabajosInsumosDevolucione = $this->OrdenTrabajosInsumosDevoluciones->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $ordenTrabajosInsumosDevolucione = $this->OrdenTrabajosInsumosDevoluciones->patchEntity($ordenTrabajosInsumosDevolucione, $this->request->getData());
            if ($this->OrdenTrabajosInsumosDevoluciones->save($ordenTrabajosInsumosDevolucione)) {
                $this->Flash->success(__('The orden trabajos insumos devolucione has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The orden trabajos insumos devolucione could not be saved. Please, try again.'));
        }
        $ordenTrabajosInsumos = $this->OrdenTrabajosInsumosDevoluciones->OrdenTrabajosInsumos->find('list', ['limit' => 200]);
        $productos = $this->OrdenTrabajosInsumosDevoluciones->Productos->find('list', ['limit' => 200]);
        $users = $this->OrdenTrabajosInsumosDevoluciones->Users->find('list', ['limit' => 200]);
        $this->set(compact('ordenTrabajosInsumosDevolucione', 'ordenTrabajosInsumos', 'productos', 'users'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Orden Trabajos Insumos Devolucione id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $ordenTrabajosInsumosDevolucione = $this->OrdenTrabajosInsumosDevoluciones->get($id);
        if ($this->OrdenTrabajosInsumosDevoluciones->delete($ordenTrabajosInsumosDevolucione)) {
            $this->Flash->success(__('The orden trabajos insumos devolucione has been deleted.'));
        } else {
            $this->Flash->error(__('The orden trabajos insumos devolucione could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
