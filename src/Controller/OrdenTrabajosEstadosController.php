<?php
namespace Ordenes\Controller;

use Ordenes\Controller\AppController;

/**
 * OrdenTrabajosEstados Controller
 *
 * @property \App\Model\Table\OrdenTrabajosEstadosTable $OrdenTrabajosEstados
 *
 * @method \App\Model\Entity\OrdenTrabajosEstado[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class OrdenTrabajosEstadosController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
        $ordenTrabajosEstados = $this->paginate($this->OrdenTrabajosEstados);

        $this->set(compact('ordenTrabajosEstados'));
    }

    /**
     * View method
     *
     * @param string|null $id Orden Trabajos Estado id.
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $ordenTrabajosEstado = $this->OrdenTrabajosEstados->get($id, [
            'contain' => ['OrdenTrabajos']
        ]);

        $this->set('ordenTrabajosEstado', $ordenTrabajosEstado);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $ordenTrabajosEstado = $this->OrdenTrabajosEstados->newEntity();
        if ($this->request->is('post')) {
            $ordenTrabajosEstado = $this->OrdenTrabajosEstados->patchEntity($ordenTrabajosEstado, $this->request->getData());
            if ($this->OrdenTrabajosEstados->save($ordenTrabajosEstado)) {
                $this->Flash->success(__('The orden trabajos estado has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The orden trabajos estado could not be saved. Please, try again.'));
        }
        $this->set(compact('ordenTrabajosEstado'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Orden Trabajos Estado id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $ordenTrabajosEstado = $this->OrdenTrabajosEstados->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $ordenTrabajosEstado = $this->OrdenTrabajosEstados->patchEntity($ordenTrabajosEstado, $this->request->getData());
            if ($this->OrdenTrabajosEstados->save($ordenTrabajosEstado)) {
                $this->Flash->success(__('The orden trabajos estado has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The orden trabajos estado could not be saved. Please, try again.'));
        }
        $this->set(compact('ordenTrabajosEstado'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Orden Trabajos Estado id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $ordenTrabajosEstado = $this->OrdenTrabajosEstados->get($id);
        if ($this->OrdenTrabajosEstados->delete($ordenTrabajosEstado)) {
            $this->Flash->success(__('The orden trabajos estado has been deleted.'));
        } else {
            $this->Flash->error(__('The orden trabajos estado could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
