<?php
namespace Ordenes\Controller;

use Ordenes\Controller\AppController;
use Cake\I18n\Date;
use Cake\I18n\Time;
use Cake\Log\Log;
/**
 * OrdenTrabajosCertificaciones Controller
 *
 * @property \App\Model\Table\OrdenTrabajosCertificacionesTable $OrdenTrabajosCertificaciones
 *
 * @method \App\Model\Entity\OrdenTrabajosCertificacione[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class OrdenTrabajosCertificacionesController extends AppController
{

    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
        $this->paginate = [
            'contain' => ['OrdenTrabajosDistribuciones', 'Users']
        ];
        $ordenTrabajosCertificaciones = $this->paginate($this->OrdenTrabajosCertificaciones);

        $this->set(compact('ordenTrabajosCertificaciones'));
    }

    /**
     * View method
     *
     * @param string|null $id Orden Trabajos Certificacione id.
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $ordenTrabajosCertificacione = $this->OrdenTrabajosCertificaciones->get($id, [
            'contain' => ['OrdenTrabajosDistribuciones', 'Users']
        ]);

        $this->set('ordenTrabajosCertificacione', $ordenTrabajosCertificacione);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $ordenTrabajosCertificacione = $this->OrdenTrabajosCertificaciones->newEntity();
        if ($this->request->is('json')) {
            if ($this->request->is(['patch', 'post', 'put'])) {
                $ordenTrabajosCertificacione = $this->OrdenTrabajosCertificaciones->patchEntity($ordenTrabajosCertificacione, $this->request->getData());
                /* Verificar si tiene partes asociados */
                $VerificarPartes = $this->verificarPartesAsociados($ordenTrabajosCertificacione->orden_trabajos_distribucione_id);
                if ($VerificarPartes) {
                    
                    $fecha_final = new Date($this->request->getData(['fecha_final']));
                    $ordenTrabajosCertificacione->fecha_final = $fecha_final;

                    $fecha_inicio = new Date($this->request->getData(['fecha_inicio']));
                    $ordenTrabajosCertificacione->fecha_inicio = $fecha_inicio;

                    $ordenTrabajosCertificacione->user_id =  $this->request->session()->read('Auth.User.id');

                    if ($this->OrdenTrabajosCertificaciones->save($ordenTrabajosCertificacione)) {
                        /* Devuelvo el array para dibujar el historico de certificaciones */
                        $ordenTrabajos = $this->OrdenTrabajosCertificaciones->OrdenTrabajos->get($ordenTrabajosCertificacione->orden_trabajo_id, [
                            'contain' => (['OrdenTrabajosDistribuciones' => ['OrdenTrabajosCertificaciones'],
                                           'OrdenTrabajosCertificaciones' => ['Users' => ['fields' => ['id', 'nombre', 'ruta_imagen']]]
                                           ])
                        ]);

                        $certificacion = $this->OrdenTrabajosCertificaciones->get($ordenTrabajosCertificacione->id, [
                            'contain' => ['Monedas']
                        ]);
                        
                        $resultado = [
                            'status' => 'success',
                            'message' => 'El registro se actualizó correctamente.',
                            'certificaciones' => $ordenTrabajos,
                            'certificacion' => $certificacion
                            ];
                    } else {
                        $resultado = [
                            'status' => 'error',
                            'message' => 'No se pudo actualizar el registro.',
                            'archivo' => $ordenTrabajosCertificacione->errors()
                            ];                    
                    }            
                } else {
                    $ordenTrabajos = $this->OrdenTrabajosCertificaciones->OrdenTrabajos->get($ordenTrabajosCertificacione->orden_trabajo_id, [
                            'contain' => (['OrdenTrabajosDistribuciones' => ['OrdenTrabajosCertificaciones'],
                                           'OrdenTrabajosCertificaciones' => ['Users' => ['fields' => ['id', 'nombre', 'ruta_imagen']]]
                                           ])
                        ]);
                    $resultado = [
                            'status' => 'error',
                            'message' => 'Ya tiene partes electrónicos asociados. La certificación se hará en forma automática.',
                            'certificaciones' => $ordenTrabajos
                            ];
                }
            }
        } else {
            if ($this->request->is('post')) {
                $ordenTrabajosCertificacione = $this->OrdenTrabajosCertificaciones->patchEntity($ordenTrabajosCertificacione, $this->request->getData());
                if ($this->OrdenTrabajosCertificaciones->save($ordenTrabajosCertificacione)) {
                    $this->Flash->success(__('The orden trabajos certificacione has been saved.'));

                    return $this->redirect(['action' => 'index']);
                }
                $this->Flash->error(__('The orden trabajos certificacione could not be saved. Please, try again.'));
            }
        }
        $ordenTrabajosDistribuciones = $this->OrdenTrabajosCertificaciones->OrdenTrabajosDistribuciones->find('list', ['limit' => 200]);
        $users = $this->OrdenTrabajosCertificaciones->Users->find('list', ['limit' => 200]);
        $this->set(compact('ordenTrabajosCertificacione', 'ordenTrabajosDistribuciones', 'users', 'resultado'));
        
        $this->set('_serialize', 'resultado');
    }
    
    /*
     * Verifico si tiene partes electrónicos asociados.
     */
    private function verificarPartesAsociados($id = null) {
        /* Pongo en stand by la verificacion de partes asociados */
//        $this->loadModel('CertPartesElectronicos');
//        
//        $partes = $this->CertPartesElectronicos->find('all', [
//            'conditions' => ['orden_trabajos_distribucione_id' => $id]
//        ])->toArray();
//
//        if ($partes) {
//            return false;
//        }
        return true;
    }
    /**
     * Edit method
     *
     * @param string|null $id Orden Trabajos Certificacione id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $ordenTrabajosCertificacione = $this->OrdenTrabajosCertificaciones->get($id);
        
        $response = [];
        
        if ($this->request->is('json')) {
            if ($this->request->is(['patch', 'post', 'put'])) {
                $ordenTrabajosCertificacione = $this->OrdenTrabajosCertificaciones->patchEntity($ordenTrabajosCertificacione, $this->request->getData());
                
                $date = new Date($this->request->getData(['fecha_final']));
                $ordenTrabajosCertificacione->fecha_final = $date;
                
                if ($this->OrdenTrabajosCertificaciones->save($ordenTrabajosCertificacione)) {
                    /* Devuelvo el array para dibujar el historico de certificaciones */
                    $ordenTrabajos = $this->OrdenTrabajosCertificaciones->OrdenTrabajos->get($ordenTrabajosCertificacione->orden_trabajo_id, [
                        'contain' => (['OrdenTrabajosDistribuciones' => ['OrdenTrabajosCertificaciones' => ['Monedas']],
                                       'OrdenTrabajosCertificaciones' => ['Monedas', 'Users' => ['fields' => ['id', 'nombre', 'ruta_imagen']]]
                                       ])
                    ]);
                    
                    $certificacion = $this->OrdenTrabajosCertificaciones->get($id, [
                        'contain' => ['Monedas']
                    ]);
                    
                    $resultado = [
                        'status' => 'success',
                        'message' => 'El registro se actualizó correctamente.',
                        'certificaciones' => $ordenTrabajos,
                        'certificacion' => $certificacion
                        ];
                } else {
                    $resultado = [
                        'status' => 'error',
                        'message' => 'No se pudo actualizar el registro.',
                        'errores' => $ordenTrabajosCertificacione->errors()
                        ];                    
                }
            }
        } else {
            if ($this->request->is(['patch', 'post', 'put'])) {
                $ordenTrabajosCertificacione = $this->OrdenTrabajosCertificaciones->patchEntity($ordenTrabajosCertificacione, $this->request->getData());

                if ($this->OrdenTrabajosCertificaciones->save($ordenTrabajosCertificacione)) {
                    $this->Flash->success(__('The orden trabajos certificacione has been saved.'));

                    return $this->redirect(['action' => 'index']);
                }
                
                die(debug( $ordenTrabajosCertificacione ));
                $this->Flash->error(__('The orden trabajos certificacione could not be saved. Please, try again.'));
            }            
        }
        
        $ordenTrabajosDistribuciones = $this->OrdenTrabajosCertificaciones->OrdenTrabajosDistribuciones->find('list', ['limit' => 200]);
        $users = $this->OrdenTrabajosCertificaciones->Users->find('list', ['limit' => 200]);
        $this->set(compact('ordenTrabajosCertificacione', 'ordenTrabajosDistribuciones', 'users', 'resultado'));
        
        $this->set('_serialize', 'resultado');
    }

    /**
     * Delete method
     *
     * @param string|null $id Orden Trabajos Certificacione id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $ordenTrabajosCertificacione = $this->OrdenTrabajosCertificaciones->get($id, [
            'contain' => ['OrdenTrabajosDistribuciones' => ['OrdenTrabajos']]
        ]);

        $orden_trabajo_id = $ordenTrabajosCertificacione->orden_trabajos_distribucione->orden_trabajo->id;
        
        $ordenTrabajos = $this->OrdenTrabajosCertificaciones->OrdenTrabajos->get($orden_trabajo_id, [
                                           'contain' => (['OrdenTrabajosDistribuciones' => ['OrdenTrabajosCertificaciones'],
                                                          'OrdenTrabajosCertificaciones' => ['Users' => ['fields' => ['id', 'nombre', 'ruta_imagen']]]
                                                          ])
                           ]);         
 
        /* Chequeo que la OT esté en estado != 4 */
        if ( $ordenTrabajosCertificacione ) {
            /* Verifico que el usuario que intenta sacar la certificacion, sea el mismo que confeccionó la OT */
            $user = $this->request->session()->read('Auth.User.id');
            if ( $ordenTrabajosCertificacione->orden_trabajos_distribucione->orden_trabajo->user_id !== $user ) {
                $result['status'] = 'error';
                $result['message'] = 'No puede quitar la certificación de una OT que no haya confeccionado.';
            } else {
                if ($ordenTrabajosCertificacione->oracle_flag) {
                    $distribucione_id = $ordenTrabajosCertificacione->orden_trabajos_distribucione->id;
                    $ordenTrabajosDistribucione = $this->OrdenTrabajosCertificaciones->OrdenTrabajosDistribuciones->get($distribucione_id, [
                        'contain' => ['OrdenTrabajos' => ['Establecimientos' => ['EstablecimientosOrganizaciones' => ['Monedas']]],
                                      'OrdenTrabajosCertificaciones',
                                      'ProyectosLabores' => ['fields' => ['id', 'nombre']],
                                      'Monedas',
                                      'Lotes' => ['fields' => ['id', 'nombre']]]
                    ]);                    
                    $result = [
                            'status' => 'error',
                            'message' => 'La certificación ya se subió a oracle. Modifique la tarifa.',
                            'certificaciones' => $ordenTrabajosDistribucione
                        ];                        
                } else {
                    if ( $ordenTrabajosCertificacione->orden_trabajos_distribucione->orden_trabajo->orden_trabajos_estado_id > 4 ) {
                        $result['status'] = 'error';
                        $result['message'] = 'La orden de trabajo '.$ordenTrabajosCertificacione->orden_trabajos_distribucione->orden_trabajo->id.' ya se encuentra finalizada.';
                    } else {
                        if ($this->OrdenTrabajosCertificaciones->delete($ordenTrabajosCertificacione)) {

                            $ordenTrabajos = $this->OrdenTrabajosCertificaciones->OrdenTrabajos->get($orden_trabajo_id, [
                                           'contain' => (['OrdenTrabajosDistribuciones' => ['OrdenTrabajosCertificaciones'],
                                                          'OrdenTrabajosCertificaciones' => ['Users' => ['fields' => ['id', 'nombre', 'ruta_imagen']]]
                                                          ])
                           ]);

                            $result = [
                                'status' => 'success',
                                'message' => 'La certificación se ha quitado correctamente.',
                                'certificaciones' => $ordenTrabajos
                            ];
                        } else {
                            $result['status'] = 'error';
                            $result['message'] = 'No se pudo quitar la certificación.';
                        }
                    }            
                }
            }
        } else {
            $result['status'] = 'error';
            $result['message'] = 'No hay certificaciones.';
        }

        $this->set(compact('result'));
        $this->set('_serialize', 'result');
        
        $this->RequestHandler->renderAs($this, 'json');
    }
}
