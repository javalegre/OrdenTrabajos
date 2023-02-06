<?php
namespace Ordenes\Controller;

use Ordenes\Controller\AppController;
use Cake\Log\Log;
use Cake\Datasource\ConnectionManager;

/**
 * OrdenTrabajosInsumos Controller
 *
 * @property \App\Model\Table\OrdenTrabajosInsumosTable $OrdenTrabajosInsumos
 *
 * @method \App\Model\Entity\OrdenTrabajosInsumo[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class OrdenTrabajosInsumosController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index($id = null)
    {
        $connection = ConnectionManager::get('default');
        
        $strsql = "SELECT * from semillas_bolsas WHERE orden_trabajos_insumo_id = '" . $id."'";

        $semillas_bolsas =  $connection->execute($strsql)->fetchAll('assoc');
        
        $insumos = $this->OrdenTrabajosInsumos->get($id, [
            'contain' => ['Productos', 'ProductosLotes']
        ]);
        
        $this->set(compact('insumos', 'semillas_bolsas'));
        
        /* Para el modal */
       // $this->render('Ordenes.OrdenTrabajosInsumos/index', 'ajax');
    }

    /**
     * View method
     *
     * @param string|null $id Orden Trabajos Insumo id.
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $ordenTrabajosInsumo = $this->OrdenTrabajosInsumos->get($id, [
            'contain' => ['OrdenTrabajos', 'OrdenTrabajosDistribuciones', 'Productos', 'Unidades', 'Almacenes']
        ]);

        $this->set('ordenTrabajosInsumo', $ordenTrabajosInsumo);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $response = [];
        if ($this->request->is('post')) {
            $data = json_decode($this->request->getData('datos'), true);
            if (isset($data['id'])) {
                $ordenTrabajosInsumo = $this->OrdenTrabajosInsumos->get( $data['id']);
            } else {
                $ordenTrabajosInsumo = $this->OrdenTrabajosInsumos->newEntity();
            }
            
            $ordenTrabajosInsumo = $this->OrdenTrabajosInsumos->patchEntity($ordenTrabajosInsumo, $data);
            $ordenTrabajosInsumo->producto_id = $data['producto'];
            $ordenTrabajosInsumo->productos_lote_id = $data['lote'];
            $ordenTrabajosInsumo->unidade_id = $data['unidad'];
            $ordenTrabajosInsumo->almacene_id = $data['almacen'];
            $ordenTrabajosInsumo->cantidad_stock = 1;
            
            if ($this->OrdenTrabajosInsumos->save($ordenTrabajosInsumo)) {
                $response['status'] = 'success';
                $response['data'] = $this->OrdenTrabajosInsumos->get($ordenTrabajosInsumo->id, [
                    'contain' => ['Almacenes', 'Unidades', 'Productos']
                ]);
            } else {
                $response['status'] = 'error';
            }
        }
        
        $this->set(compact('response'));
        $this->set('_serialize', 'response');
        $this->RequestHandler->renderAs($this, 'json');
    }

    /**
     * Edit method
     *
     * @param string|null $id Orden Trabajos Insumo id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $ordenTrabajosInsumo = $this->OrdenTrabajosInsumos->get($id);
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            if ($this->request->getData('producto-linea')) {
                
                
                /* Chequeo que el usuario tenga permisos para hacer los cambios */
                $grupo = $this->request->session()->read('Auth.User.group_id');
                $rol = $this->request->session()->read('Auth.User.role_id');
                
                if ( $grupo != '3' || !in_array($rol, ['8', '9'])) {
                    
                    /* No esta en el grupo Administrativos ni tiene rol Encargado de Almacen, Responsable Administrativo */
                    $respuesta = [
                        'status' => 'error',
                        'message' => 'No tiene autorización para realizar estos cambios.'
                    ];
                    
                    $this->set(compact('respuesta'));
                    $this->set('_serialize', 'respuesta');

                    $this->RequestHandler->renderAs($this, 'json');
                    
                    return;
                }
                
  //              die(debug( 'Holis'));
                /* Este es un cambio de producto, así que lo paso a la table */
                
//                 die(debug( print_r( $this->request->getData()) ));
                
                $data = json_decode($this->request->getData('producto-linea'));
                $producto = json_decode($this->request->getData('productos'));
                
                
                
                $producto = [
                                'valor' => $this->request->getData('productos'),
                                'observaciones' => $this->request->getData('productos-observaciones')
                            ];
                $user = $this->request->session()->read('Auth.User.id');
                
                $respuesta = $this->OrdenTrabajosInsumos->cambiarProductos($id, $producto , $user);
                
                
                
                //die(debug( $respuesta ));
                $this->set(compact('respuesta'));
                $this->set('_serialize', 'respuesta');

                $this->RequestHandler->renderAs($this, 'json');
                
                return;
                
            } else {
                $ordenTrabajosInsumo = $this->OrdenTrabajosInsumos->patchEntity($ordenTrabajosInsumo, $this->request->getData());
                if ($this->OrdenTrabajosInsumos->save($ordenTrabajosInsumo)) {
                    $this->Flash->success(__('The orden trabajos insumo has been saved.'));

                    return $this->redirect(['action' => 'index']);
                }
                $this->Flash->error(__('The orden trabajos insumo could not be saved. Please, try again.'));
            }
        }
        
        $ordenTrabajos = $this->OrdenTrabajosInsumos->OrdenTrabajos->find('list', ['limit' => 200]);
        $ordenTrabajosDistribuciones = $this->OrdenTrabajosInsumos->OrdenTrabajosDistribuciones->find('list', ['limit' => 200]);
        $productos = $this->OrdenTrabajosInsumos->Productos->find('list', ['limit' => 200]);
        $unidades = $this->OrdenTrabajosInsumos->Unidades->find('list', ['limit' => 200]);
        $almacenes = $this->OrdenTrabajosInsumos->Almacenes->find('list', ['limit' => 200]);
        $this->set(compact('ordenTrabajosInsumo', 'ordenTrabajos', 'ordenTrabajosDistribuciones', 'productos', 'unidades', 'almacenes', 'respuesta'));
        

    }

    /**
     * Delete method
     *
     * @param string|null $id Orden Trabajos Insumo id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $data = [];
        $this->request->allowMethod(['post', 'delete']);
        $ordenTrabajo = $this->OrdenTrabajosInsumos->get($id);
        
        $entregas = 0;
        $entregas = $this->OrdenTrabajosInsumos->find('entregas', ['IdInsumos' => $id])->entregas;

        $devoluciones = 0;
        $devoluciones = $this->OrdenTrabajosInsumos->find('devoluciones', ['IdInsumos' => $id])->devoluciones;                

        $cantidad = $entregas - $devoluciones;

        if ($cantidad === 0) { /* Está listo para ser eliminado. */
            
            
            /* Anulo todas las entregas */
            $this->eliminarEntregas($id);

            /* Anulo todas las entregas */
            $this->eliminarDevoluciones($id);

            /* Ahora marco como eliminado el insumos */
            if ($this->OrdenTrabajosInsumos->delete($ordenTrabajo)) {
               $data['status'] = 'success';
                $data['message'] = 'El insumo se ha eliminado correctamente.';
            } else {
                $data['status'] = 'error';
                $data['message'] = $ordenTrabajo->errors(); /* Esto nos devuelve el error para poder mostrarlo en la vista */
            }
        } else {
            $data = [
                'content' => $data,
                'status' => 'error',
                'message' => 'El insumo contiene entregas sin devoluciones.'
            ];            
        }
        $this->set([
            'response' => $data,
            '_serialize' => 'response',
        ]);
        $this->RequestHandler->renderAs($this, 'json');
    }
    
    /**
     * Desasociar bolsas
     * 
     * Quito la asociacion de la tabla semillas_bolsas y elimino la entrega
     * 
     * @param type $id
     */
    public function desasociar($id = null)
    {
        
        $this->request->allowMethod(['post', 'delete']);
        
        $connection = ConnectionManager::get('default');
        
        $strsql = "SELECT * from semillas_bolsas WHERE id = '" . $id."'";

        $semillas_bolsas =  $connection->execute($strsql)->fetchAll('assoc')[0];
        
        /**
         *  Revisamos si existe el ID del bb 
         */
        if (!$semillas_bolsas) {
            $this->set([
                'response' => ['status' => 'error', 'message' => 'El código del big bag no se encuentra en la base de datos.'],
                '_serialize' => 'response'
            ]);
            $this->RequestHandler->renderAs($this, 'json');
            return;
        }
        
        /**
         * Intento buscar una entrega que coincida con lo más relevante del bb
         */
        $entrega = $this->OrdenTrabajosInsumos->OrdenTrabajosInsumosEntregas->find('all', [
                            'conditions' => ['orden_trabajos_insumo_id' => $semillas_bolsas['orden_trabajos_insumo_id'],
                                             'dispositivo_id > 0',
                                             'oracle_flag IS NULL',
                                             'cantidad' => $semillas_bolsas['peso']]
                    ])->first();
        
        if (!$entrega) {
            $this->set([
                'response' => ['status' => 'error', 'message' => 'No hay una entrega que coincida con los datos relevantes del big bag.'],
                '_serialize' => 'response'
            ]);
            
            $this->RequestHandler->renderAs($this, 'json');
            return;
        }
        
        $entrega_id = $entrega->id;
        $insumo_id = $entrega->orden_trabajos_insumo_id;

        /**
         *  Esto falla xq no está definido en cake como clave doble 02/09/2021
         *  $this->OrdenTrabajosInsumos->OrdenTrabajosInsumosEntregas->delete($entrega);
         */

        /* Marcamos la eliminacion con una query */
        $strsql = "UPDATE orden_trabajos_insumos_entregas SET observaciones='semillas_bolsas.id: ".$id.", user_id: ".$this->request->session()->read('Auth.User.id')."', deleted=now(), oracle_flag='Y' where id=".$entrega->id." AND dispositivo_id=".$entrega->dispositivo_id;
        $connection->execute($strsql);
        
        $strsql = "UPDATE semillas_bolsas SET orden_trabajos_insumo_id=NULL WHERE id=".$id."";
        $this->log($strsql);
        $connection->execute($strsql);
        
        $data = ['status' => 'success', 'message' => 'Se ha desasociado el BB correctamente', 'entrega_id' => $entrega_id, 'insumo_id' => $insumo_id];
        
        $this->set([
            'response' => $data,
            '_serialize' => 'response'
        ]);
        $this->RequestHandler->renderAs($this, 'json');
    }
    
    /**
     * AplicarCambios method
     *
     * @param string|null $token Token con la información para efectuar un cambio.
     */
    public function aplicarCambios() {
        
        if ($this->request->getQuery('accion') == 'rechazar') {

            /* Rechazo la accion */
            $token = urldecode($this->request->getQuery('token'));
            $user_id = $this->request->getQuery('user_id');
            
            /* Saco los espacios del token - TODO verificar porque sale este error */
            $token = str_replace(" ", "", $token);
            $rechazar = $this->OrdenTrabajosInsumos->rechazarCambioProductos ($token, $user_id );
            
            $id = $rechazar['registro'];
            
            return $this->redirect(['controller' => 'orden-trabajos-auditorias', 'action' => 'notificar', $id]);
        } else {
            /* La accion fue approbada así que lo guardo */
            
            /* Rechazo la accion */
            $token = urldecode($this->request->getQuery('token'));
            $user_id = $this->request->getQuery('user_id');
            
            /* Saco los espacios del token - TODO verificar porque sale este error */
            $token = str_replace(" ", "", $token);
            /* Busco el registro */
            
            $rechazar = $this->OrdenTrabajosInsumos->aplicarCambioProductos($token, $user_id);
            
            $id = $rechazar['registro'];
            
            return $this->redirect(['controller' => 'orden-trabajos-auditorias', 'action' => 'notificar', $id]);            
        }
    }
    
    /**
     * Anulo todas las devoluciones
     * 
     * @param type $id OrdenTrabajosInsumos
     * @return boolean
     */
    private function eliminarDevoluciones ($id = null) {
        $devoluciones = $this->OrdenTrabajosInsumos->OrdenTrabajosInsumosDevoluciones->find('all', [
            'conditions' => ['orden_trabajos_insumo_id' => $id]
        ]);
        foreach ($devoluciones as $devolucion) {
            $anula = $devolucion;
            unset ($anula->id);
            unset ($anula->oracle_flag);
            unset ($anula->interface_error);
            
            $anula->cantidad = -1 * abs($devolucion->cantidad);
            $anula->isNew(true);
            
            $this->OrdenTrabajosInsumos->OrdenTrabajosInsumosDevoluciones->save($anula);
        }
        /* Ahora que estan anulados, borro todos los registros */
        $this->OrdenTrabajosInsumos->OrdenTrabajosInsumosDevoluciones->deleteAll(['orden_trabajos_insumo_id' => $id]);
        
        return true;
    }

    /**
     * Anulo todas las entregas
     * 
     * @param type $id OrdenTrabajosInsumos
     * @return boolean
     */
    private function eliminarEntregas ($id = null) {
        $entregas = $this->OrdenTrabajosInsumos->OrdenTrabajosInsumosEntregas->find('all', [
            'conditions' => ['orden_trabajos_insumo_id' => $id]
        ]);
        foreach ($entregas as $entrega) {
            $anula = $entrega;
            unset ($anula->id);
            unset ($anula->oracle_flag);
            unset ($anula->interface_error);
            
            $anula->cantidad = -1 * abs($entrega->cantidad);
            $anula->isNew(true);
            
            $this->OrdenTrabajosInsumos->OrdenTrabajosInsumosEntregas->save($anula);
        }
        /* Ahora que estan anulados, borro todos los registros */
        $this->OrdenTrabajosInsumos->OrdenTrabajosInsumosEntregas->deleteAll(['orden_trabajos_insumo_id' => $id]);
        
        return true;
    }
    
    /**
     * EliminarInsumo
     *
     * @param string|null $id Orden Trabajos Insumo id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    
    public function eliminarinsumo()
    {
        if ($this->request->is('ajax')) {        
            /* Obtengo los datos que pasamos como parametros */
            $data = $this->request->data;

            /* Leo la tabla */
            if ($data['id']) {
                /* Estoy editando, así que obtengo el objeto */
                $dist = $this->OrdenTrabajosInsumos->get($data['id']);
                $entregas = 0;
                $entregas = $this->OrdenTrabajosInsumos->find('entregas', ['IdInsumos' => $data['id']])->entregas;

                $devoluciones = 0;
                $devoluciones = $this->OrdenTrabajosInsumos->find('devoluciones', ['IdInsumos' => $data['id']])->devoluciones;                
                
                $cantidad = $entregas - $devoluciones;
                
                if ($cantidad === 0) {
                    if ($result = $this->OrdenTrabajosInsumos->delete($dist)){
                        $data = [
                            'content' => $data,
                            'status' => 'success',
                            'message' => 'Se quitó el insumo correctamente.'
                        ];                    
                    } else {
                        $data = [
                            'content' => $data,
                            'status' => 'error',
                            'message' => 'Ocurrió un error al intentar quitar el insumo.'
                        ];
                    }                    
                } else {
                    $data = [
                        'content' => $data,
                        'status' => 'error',
                        'message' => 'El insumo contiene entregas sin devoluciones.'
                    ];
                    
                }
            }
            $this->set(compact('data')); // Pass $data to the view
            $this->set('_serialize', 'data'); // Let the JsonView class know what variable to use                
        }
    }
}
