<?php
namespace Ordenes\Controller;

use Ordenes\Controller\AppController;
use Cake\I18n\Time;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

/**
 * OrdenTrabajosMapeos Controller
 *
 * @property \Ordenes\Model\Table\OrdenTrabajosMapeosTable $OrdenTrabajosMapeos
 *
 * @method \Ordenes\Model\Entity\OrdenTrabajosMapeo[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class OrdenTrabajosMapeosController extends AppController {

    public function initialize() {
        parent::initialize();
        
        $this->loadModel('Ordenes.OrdenTrabajosDistribuciones');
        $this->loadModel('Ordenes.OrdenTrabajosCertificaciones');
        $this->loadModel('ProyectosLabores');
        $this->loadModel('Lotes');
        $this->loadModel('Proyectos');
        $this->loadModel('Proveedores');
        $this->loadModel('CampaniaMonitoreos');
        $this->loadModel('Establecimientos');
        $this->loadModel('Cultivos');
        $this->loadModel('Sectores');
        $this->loadModel('Configuraciones');

        $this->loadComponent('RequestHandler');

        // $this->loadModel('Unidades');
        // $this->loadModel('Ordenes.OrdenTrabajosDistribucionesTarifarios');
        // $this->loadModel('Ordenes.OrdenTrabajosDataloads');
        // $this->loadModel('ProyectosLaboresTarifarios');
        // $this->loadModel('Configuraciones');
        // $this->loadModel('Ordenes.OrdenTrabajosInsumos');
        // $this->loadModel('Ordenes.OrdenTrabajosInsumosEntregas');
        // $this->loadModel('Ordenes.OrdenTrabajosInsumosDevoluciones');
        // $this->loadModel('Monedas');
        // $this->loadModel('Almacenes');
        // $this->loadModel('Productos');
        // $this->loadModel('TecnicasAplicaciones');
        // $this->loadModel('Tecnicaresponsables');
        // $this->loadModel('ProductosExistencias');
        // $this->loadModel('Campanias');
        // $this->loadModel('Queue.QueuedJobs');
        
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null
     */
    public function index() {
        
        $establecimientos = $this->Establecimientos->find('all')->select(['id', 'text' => 'nombre']);
        $proveedores = $this->Proveedores->find('all')->select(['id', 'text' => 'nombre'])->where(['activo = 0', 'deleted IS NULL']);
        $cultivos = $this->Cultivos->find('all')->select(['id', 'text' => 'nombre']);
        $campanias = $this->CampaniaMonitoreos->find('all')->select(['id', 'text' => 'nombre'])->where(['activa = 1']);
        $sectores = $this->Sectores->find('all');

        if ( $this->request->session()->read('Auth.User.establecimientos')) {
            $establecimientos->where(['id IN ' => $this->request->session()->read('Auth.User.establecimientos') ]);
            $sectores->where(['establecimiento_id IN' => $this->request->session()->read('Auth.User.establecimientos')]);
        }

        $this->set(compact('establecimientos', 'proveedores', 'cultivos', 'campanias','sectores'));
    }

    /**
     * View method
     *
     * @param string|null $id Orden Trabajos Mapeo id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null) {

        $ordenTrabajosMapeo = $this->OrdenTrabajosMapeos->get($id, [
            'contain' => [  'OrdenTrabajos' => ['Establecimientos' => ['Zonas' => ['fields' => ['id', 'nombre']]] ], 
                            'OrdenTrabajosDistribuciones' => [ 'Proyectos' => ['CampaniaMonitoreos' => ['fields' => ['id', 'nombre']] ] ],  
                            'OrdenTrabajosCertificaciones', 
                            'Lotes', 
                            'ProyectosLabores', 
                            'MapeosCampaniasTipos', 
                            'MapeosCalidades',
                            'MapeosProblemas',
                            'Users'
                        ]
        ]);

        $this->set('ordenTrabajosMapeo', $ordenTrabajosMapeo);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add($id = null) {

        if ($id) { 
            $ordenTrabajosDistribuciones = $this->OrdenTrabajosDistribuciones->get($id, [ 
                'contain' => [ 'OrdenTrabajos' => ['Establecimientos' => ['Zonas' => ['fields' => ['id', 'nombre']]] ],
                                'OrdenTrabajosCertificaciones',
                                'Lotes' => ['fields' => ['id', 'nombre'] ],
                                'Proyectos' => [ 'CampaniaMonitoreos' => ['fields' => ['id', 'nombre']] ]
                             ]
            ]);
        }
        
        $ordenTrabajosMapeo = $this->OrdenTrabajosMapeos->newEntity();
        if ($this->request->is('post')) {

            $ordenTrabajosMapeo = $this->OrdenTrabajosMapeos->patchEntity($ordenTrabajosMapeo, $this->request->getData());
            $ordenTrabajosMapeo->orden_trabajo_id = $ordenTrabajosDistribuciones->orden_trabajo_id;
            $ordenTrabajosMapeo->orden_trabajos_distribucione_id = $ordenTrabajosDistribuciones->id;
            $ordenTrabajosMapeo->orden_trabajos_certificacione_id = $ordenTrabajosDistribuciones->orden_trabajos_certificaciones ? $ordenTrabajosDistribuciones->orden_trabajos_certificaciones[0]->id : null;
            $ordenTrabajosMapeo->lote_id = $ordenTrabajosDistribuciones->lote_id;
            $ordenTrabajosMapeo->superficie = $ordenTrabajosDistribuciones->superficie;
            $ordenTrabajosMapeo->proyectos_labore_id = $ordenTrabajosDistribuciones->proyectos_labore_id;

            if ($this->OrdenTrabajosMapeos->save($ordenTrabajosMapeo)) {
                $response = [
                    'status'     => 'success',
                    'message'    => 'El Mapeo se guardo correctamente.'
                ];
            } else {
                $response = [
                    'status'     => 'error',
                    'message'    => 'Error al guardar el registro.'
                ];
            }

            $this->set(compact('response'));
            $this->set('_serialize',['response']);
            $this->RequestHandler->renderAs($this, 'json');
            return;
        }

        $mapeosCampaniasTipos = $this->OrdenTrabajosMapeos->MapeosCampaniasTipos->find('all')->select(['id', 'text' => 'nombre']);
        $mapeosCalidades = $this->OrdenTrabajosMapeos->MapeosCalidades->find('all')->select(['id', 'text' => 'nombre']);
        $mapeosProblemas = $this->OrdenTrabajosMapeos->MapeosProblemas->find('all')->select(['id', 'text' => 'nombre', 'requiere_comentario']);
        $users = $this->OrdenTrabajosMapeos->users->find('all')->select(['id', 'text' => 'nombre'])->where('1=1 and deleted IS NULL');

        $this->set(compact('ordenTrabajosMapeo','mapeosCampaniasTipos', 'mapeosCalidades', 'mapeosProblemas', 'users', 'ordenTrabajosDistribuciones'));
    }

    /**
     * add_multiple
     * Permite agregar los mismos datos de mapeos a variass OT de distribuciones.
     *  
     */
    public function addMultiple() {

        $mapeosCampaniasTipos = $this->OrdenTrabajosMapeos->MapeosCampaniasTipos->find('all')->select(['id', 'text' => 'nombre']);
        $mapeosCalidades = $this->OrdenTrabajosMapeos->MapeosCalidades->find('all')->select(['id', 'text' => 'nombre']);
        $mapeosProblemas = $this->OrdenTrabajosMapeos->MapeosProblemas->find('all')->select(['id', 'text' => 'nombre', 'requiere_comentario']);
        $users = $this->OrdenTrabajosMapeos->users->find('all')->select(['id', 'text' => 'nombre'])->where('1=1 and deleted IS NULL');

        $establecimientos = $this->Establecimientos->find('all')->select(['id', 'text' => 'nombre']);
        $sectores = $this->Sectores->find('all');

        if ( $this->request->session()->read('Auth.User.establecimientos')) {
            $establecimientos->where(['id IN ' => $this->request->session()->read('Auth.User.establecimientos') ]);
            $sectores->where(['establecimiento_id IN' => $this->request->session()->read('Auth.User.establecimientos')]);
        }

        
        if ($this->request->is('post')) {

            $data = $this->request->getData();
            
            $ordenTrabajosDistribuciones = explode(',',$data['data']);
            
            foreach ($ordenTrabajosDistribuciones as $id) {
                $ordenTrabajosDistribucione = $this->OrdenTrabajosDistribuciones->get($id, [ 
                    'contain' => [ 'OrdenTrabajos' => ['Establecimientos' => ['Zonas' => ['fields' => ['id', 'nombre']]] ],
                                    'OrdenTrabajosCertificaciones',
                                    'Lotes' => ['fields' => ['id', 'nombre'] ],
                                    'Proyectos' => [ 'CampaniaMonitoreos' => ['fields' => ['id', 'nombre']] ]
                                 ]
                ]);

                $ordenTrabajosMapeo = $this->OrdenTrabajosMapeos->newEntity();
                $ordenTrabajosMapeo = $this->OrdenTrabajosMapeos->patchEntity($ordenTrabajosMapeo, $this->request->getData());
                
                $ordenTrabajosMapeo->orden_trabajo_id = $ordenTrabajosDistribucione->orden_trabajo_id;
                $ordenTrabajosMapeo->orden_trabajos_distribucione_id = $ordenTrabajosDistribucione->id;
                $ordenTrabajosMapeo->orden_trabajos_certificacione_id = $ordenTrabajosDistribucione->orden_trabajos_certificaciones ? $ordenTrabajosDistribucione->orden_trabajos_certificaciones[0]->id : '';
                $ordenTrabajosMapeo->lote_id = $ordenTrabajosDistribucione->lote_id;
                $ordenTrabajosMapeo->superficie = $ordenTrabajosDistribucione->superficie;
                $ordenTrabajosMapeo->proyectos_labore_id = $ordenTrabajosDistribucione->proyectos_labore_id;
        
                if ($this->OrdenTrabajosMapeos->save($ordenTrabajosMapeo)) {
                    $response = [
                        'status'     => 'success',
                        'message'    => 'Mapeo guardado correctamente.'
                    ];
                }

            }

            $this->set(compact('response'));
            $this->set('_serialize',['response']);
            $this->RequestHandler->renderAs($this, 'json');
            return;
        }


        $this->set(compact('mapeosCampaniasTipos', 'mapeosCalidades', 'mapeosProblemas', 'users', 'establecimientos', 'sectores'));

    }


    /**
     * Edit method
     *
     * @param string|null $id Orden Trabajos Mapeo id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null) {

        $ordenTrabajosMapeo = $this->OrdenTrabajosMapeos->get($id, [
            'contain' => [  'OrdenTrabajos' => ['Establecimientos' => ['Zonas' => ['fields' => ['id', 'nombre']]] ], 
                            'OrdenTrabajosDistribuciones' => [ 'Proyectos' => ['CampaniaMonitoreos' => ['fields' => ['id', 'nombre']] ] ],  
                            'OrdenTrabajosCertificaciones', 
                            'Lotes', 
                            'ProyectosLabores', 
                            'MapeosCampaniasTipos', 
                            'MapeosCalidades',
                            'MapeosProblemas',
                            'Users'
                        ]
        ]);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $ordenTrabajosMapeo = $this->OrdenTrabajosMapeos->patchEntity($ordenTrabajosMapeo, $this->request->getData());
            
            if ($this->OrdenTrabajosMapeos->save($ordenTrabajosMapeo)) {
                $response = [
                    'status'     => 'success',
                    'message'    => 'El Mapeo se guardo correctamente.'
                ];
            } else {
                $response = [
                    'status'     => 'error',
                    'message'    => 'Error al guardar el registro.'
                ];
            }

            $this->set(compact('response'));
            $this->set('_serialize',['response']);
            $this->RequestHandler->renderAs($this, 'json');
            return;
        }
        
        $mapeosCampaniasTipos = $this->OrdenTrabajosMapeos->MapeosCampaniasTipos->find('all')->select(['id', 'text' => 'nombre']);
        $mapeosCalidades = $this->OrdenTrabajosMapeos->MapeosCalidades->find('all')->select(['id', 'text' => 'nombre']);
        $mapeosProblemas = $this->OrdenTrabajosMapeos->MapeosProblemas->find('all')->select(['id', 'text' => 'nombre', 'requiere_comentario']);
        $users = $this->OrdenTrabajosMapeos->users->find('all')->select(['id', 'text' => 'nombre'])->where('1=1 and deleted IS NULL');

        $this->set(compact('ordenTrabajosMapeo','mapeosCampaniasTipos', 'mapeosCalidades', 'mapeosProblemas', 'users'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Orden Trabajos Mapeo id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $ordenTrabajosMapeo = $this->OrdenTrabajosMapeos->get($id);
        if ($this->OrdenTrabajosMapeos->delete($ordenTrabajosMapeo)) {
            $this->Flash->success(__('The orden trabajos mapeo has been deleted.'));
        } else {
            $this->Flash->error(__('The orden trabajos mapeo could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Datatable Index
     * 
     */
    public function datatable () {
        $columns = [
            [
                'field' => 'OrdenTrabajos.id',
                'data' => 'orden_trabajo.id'
            ],[
                'field' => 'OrdenTrabajos.fecha',
                'data' => 'orden_trabajo.fecha'
            ],[
                'field' => 'Establecimientos.id',
                'data' => 'orden_trabajo.establecimiento.nombre'
            ],[
                'field' => 'CampaniaMonitoreos.nombre',
                'data' => 'proyecto.campania_monitoreo.nombre'
            ],[
                'field' => 'Proveedores.nombre',
                'data' => 'orden_trabajo.proveedore.nombre'
            ],[
                'field' => 'Users.nombre',
                'data' => 'user.nombre',
                'searchable' => false
            ],[
                'field' => 'MapeosCampaniasTipos.nombre',
                'data' => 'tipo',
                'searchable' => false
            ],[
                'field' => 'MapeosCalidades.nombre',
                'data' => 'calidad',
                'searchable' => false
            ],[
                'field' => 'OrdenTrabajosMapeos.sms',
                'data' => 'sms',
                'searchable' => false
            ],[
                'field' => 'OrdenTrabajosMapeos.pdf',
                'data' => 'pdf',
                'searchable' => false
            ],[
                'field' => 'MapeosProblemas.nombre',
                'data' => 'problema',
                'searchable' => false
            ],[
                'field' => 'OrdenTrabajosMapeos.comentario',
                'data' => 'comentario',
                'searchable' => false
            ],[
                'field' => 'Users.nombre',
                'data' => 'user.nombre',
                'searchable' => false
            ],[
                'field' => 'OrdenTrabajosMapeos.id',
                'data' => 'id',
                'searchable' => false
            ]

        ];

        $filtros = [];
        
        /**  Fitramos por Establecimientos */
        $establecimiento_id = $this->request->getQuery('establecimiento_id');
        if ($establecimiento_id) {
            $filtros[] = "Establecimientos.id = '".$establecimiento_id."'";
        }

        /**  Fitramos por Sectores */
        $sectore_id = $this->request->getQuery('sectore_id');
        if ($sectore_id) {
            $filtros[] = "Sectores.id = '".$sectore_id."'";
        } 

        /**  Fitramos por Campañas */
        $filtros[] = ["CampaniaMonitoreos.activa = '1'"];
        $campania_id = $this->request->getQuery('campania_id');
        if ($campania_id) {
            $filtros[] = "CampaniaMonitoreos.id = '".$campania_id."'";
        }
        
        /**  Fitramos por Cultivo */
        $cultivo_id = $this->request->getQuery('cultivo_id');
        if ($cultivo_id) {
            $filtros[] = "Cultivos.id = '".$cultivo_id."'";
        }
        
        /**  Fitramos por Proveedor */
        $proveedore_id = $this->request->getQuery('proveedore_id');
        if ($proveedore_id) {
            $filtros[] = "Proveedores.id = '".$proveedore_id."'";
        }

        /** Filtramos por fecha desde*/
        $desde = $this->request->getQuery('desde');
        if ($desde) {
            $filtros[] = "OrdenTrabajos.fecha >= '".$desde."'";
        }
      
        /** Filtramos por fecha Hasta*/
        $hasta = $this->request->getQuery('hasta');
        if ($hasta) {
            $filtros[] = "OrdenTrabajos.fecha <= '".$hasta."'";
        }

        /* filtros de cosecha */
        $filtros[] = $this->filtrarOtCosechas();
        
        $data = $this->DataTables->find('Ordenes.OrdenTrabajosDistribuciones','all', [
            'contain' => [ 
                            'OrdenTrabajos' => [
                                                    'Establecimientos' => ['Zonas' => ['fields' => ['id', 'nombre']]],
                                                    'Proveedores' => ['fields' => ['id', 'nombre']],
                                                    'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre', 'img_base64']],
                                                ],
                            'OrdenTrabajosCertificaciones' => [
                                                    'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre', 'img_base64']]
                                                ],
                            'Lotes' => [ 'Sectores' ], 
                            'Proyectos' => [
                                                    'Cultivos' => ['fields' => ['id', 'nombre']] , 
                                                    'CampaniaMonitoreos' => ['fields' => ['id', 'nombre']]
                                            ], 
                            'ProyectosLabores' => ['fields' => ['id', 'nombre']],
                            'OrdenTrabajosMapeos' => [
                                                        'MapeosCampaniasTipos',
                                                        'MapeosCalidades',
                                                        'MapeosProblemas',
                                                        'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre', 'img_base64']]
                                                    ]
                        ],
            'conditions' => [$filtros]
        ], $columns);

        /* Datatables Server Side Processing */
        $this->set('columns', $columns);
        $this->set('data', $data);
        $this->set('_serialize', array_merge($this->viewVars['_serialize'], ['data']));

    }

    /**
     * Datatable Index
     * 
     */
    public function datatableMultiple () {
        $columns = [
            [
                'field' => 'OrdenTrabajos.id',
                'data' => 'orden_trabajo.id'
            ], [
                'field' => 'OrdenTrabajosDistribuciones.id',
                'data' => 'id'
            ],[
                'field' => 'OrdenTrabajos.fecha',
                'data' => 'orden_trabajo.fecha'
            ],[
                'field' => 'Establecimientos.id',
                'data' => 'orden_trabajo.establecimiento.nombre'
            ],[
                'field' => 'CampaniaMonitoreos.nombre',
                'data' => 'proyecto.campania_monitoreo.nombre'
            ],[
                'field' => 'Proveedores.nombre',
                'data' => 'orden_trabajo.proveedore.nombre'
            ],[
                'field' => 'Users.nombre',
                'data' => 'user.nombre'
            ],[
                'field' => 'Users.nombre',
                'data' => 'orden_trabajo.user.nombre'
            ],[
                'field' => 'id',
                'data' => 'id',
                'searchable' => false
            ]

        ];

        $filtros = [];
        
        /**  Fitramos por Establecimientos */
        $establecimiento_id = $this->request->getQuery('establecimiento_id');
        if ($establecimiento_id) {
            $filtros[] = "Establecimientos.id = '".$establecimiento_id."'";
        }

        /**  Fitramos por Sectores */
        $sectore_id = $this->request->getQuery('sectore_id');
        if ($sectore_id) {
            $filtros[] = "Sectores.id = '".$sectore_id."'";
        } 

        /**  Fitramos por Campañas */
        $filtros[] = ["CampaniaMonitoreos.activa = '1'"];

        /** Filtramos por fecha desde*/
        $desde = $this->request->getQuery('desde');
        if ($desde) {
            $filtros[] = "OrdenTrabajos.fecha >= '".$desde."'";
        }
      
        /** Filtramos por fecha Hasta*/
        $hasta = $this->request->getQuery('hasta');
        if ($hasta) {
            $filtros[] = "OrdenTrabajos.fecha <= '".$hasta."'";
        }

        /* Filtramos los que no tiene Mapeos cargados  */
        $filtros[] = "(SELECT COUNT(*) FROM orden_trabajos_mapeos otm WHERE otm.id = OrdenTrabajosMapeos.id) = 0 ";

        /* filtros de cosecha */
        $filtros[] = $this->filtrarOtCosechas();

        $data = $this->DataTables->find('Ordenes.OrdenTrabajosDistribuciones','all', [
            'contain' => [ 
                            'OrdenTrabajos' => [
                                                    'Establecimientos' => ['Zonas' => ['fields' => ['id', 'nombre']]],
                                                    'Proveedores' => ['fields' => ['id', 'nombre']],
                                                    'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre', 'img_base64']],
                                                ],
                            'OrdenTrabajosCertificaciones' => [
                                                    'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre', 'img_base64']]
                                                ],
                            'Lotes' => [ 'Sectores' ], 
                            'Proyectos' => [
                                                    'Cultivos' => ['fields' => ['id', 'nombre']] , 
                                                    'CampaniaMonitoreos' => ['fields' => ['id', 'nombre']]
                                            ], 
                            'ProyectosLabores' => ['fields' => ['id', 'nombre']],
                            'OrdenTrabajosMapeos' => [
                                                        'MapeosCampaniasTipos',
                                                        'MapeosCalidades',
                                                        'MapeosProblemas',
                                                        'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre', 'img_base64']]
                                                    ]
                        ],
            'conditions' => [$filtros]
        ], $columns);

        /* Datatables Server Side Processing */
        $this->set('columns', $columns);
        $this->set('data', $data);
        $this->set('_serialize', array_merge($this->viewVars['_serialize'], ['data']));

    }

    /**
     * exportar
     * exporta archivo excel la informacion del datatable filtrado.
     * 
     */
    public function exportar() {

        /* Arreglo temporal al error de memoria al generar un excel tan grande */
        ini_set('memory_limit', '-1');
        set_time_limit(900);
        
        $filtros = [];
        
        /**  Fitramos por Establecimientos */
        $establecimiento_id = $this->request->getQuery('establecimiento_id');
        if ($establecimiento_id) {
            $filtros[] = "Establecimientos.id = '".$establecimiento_id."'";
        }

        /**  Fitramos por Sectores */
        $sectore_id = $this->request->getQuery('sectore_id');
        if ($sectore_id) {
            $filtros[] = "Sectores.id = '".$sectore_id."'";
        } 

        /**  Fitramos por Campañas */
        $filtros[] = ["CampaniaMonitoreos.activa = '1'"];
        $campania_id = $this->request->getQuery('campania_id');
        if ($campania_id) {
            $filtros[] = "CampaniaMonitoreos.id = '".$campania_id."'";
        }
        
        /**  Fitramos por Cultivo */
        $cultivo_id = $this->request->getQuery('cultivo_id');
        if ($cultivo_id) {
            $filtros[] = "Cultivos.id = '".$cultivo_id."'";
        }
        
        /**  Fitramos por Proveedor */
        $proveedore_id = $this->request->getQuery('proveedore_id');
        if ($proveedore_id) {
            $filtros[] = "Proveedores.id = '".$proveedore_id."'";
        }

        /** Filtramos por fecha desde*/
        $desde = $this->request->getQuery('desde');
        if ($desde) {
            $filtros[] = "OrdenTrabajos.fecha >= '".$desde."'";
        }
      
        /** Filtramos por fecha Hasta*/
        $hasta = $this->request->getQuery('hasta');
        if ($hasta) {
            $filtros[] = "OrdenTrabajos.fecha <= '".$hasta."'";
        }

        /* filtros de cosecha */
        $filtros[] = $this->filtrarOtCosechas();

        $ordenTrabajosDistribuciones = $this->OrdenTrabajosDistribuciones->find('all', [
            'contain' => [ 
                            'OrdenTrabajos' => [
                                                    'Establecimientos' => ['Zonas' => ['fields' => ['id', 'nombre']]],
                                                    'Proveedores' => ['fields' => ['id', 'nombre']],
                                                    'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre', 'img_base64']],
                                                ],
                            'OrdenTrabajosCertificaciones' => [
                                                    'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre', 'img_base64']]
                                                ],
                            'Lotes' => [ 'Sectores' ], 
                            'Proyectos' => [
                                                    'Cultivos' => ['fields' => ['id', 'nombre']] , 
                                                    'CampaniaMonitoreos' => ['fields' => ['id', 'nombre']]
                                            ], 
                            'ProyectosLabores' => ['fields' => ['id', 'nombre']],
                            'OrdenTrabajosMapeos' => [
                                                        'MapeosCampaniasTipos',
                                                        'MapeosCalidades',
                                                        'MapeosProblemas',
                                                        'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre', 'img_base64']]
                                                    ]
                        ],
            'conditions' => [$filtros]
        ]);
       
        /* Phpexcel   Genero archivo excel */
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Listado de Mapeos');

        $writer = new Xlsx($spreadsheet);

        /* Determino las columnas de la planilla */
        $sheet->setCellValue('A4', 'OT');
        $sheet->setCellValue('B4', 'OT Distribuciones');
        $sheet->setCellValue('C4', 'Fecha');
        $sheet->setCellValue('D4', 'Campaña');
        $sheet->setCellValue('E4', 'Establecimiento');
        $sheet->setCellValue('F4', 'Sector');
        $sheet->setCellValue('G4', 'Lote');
        $sheet->setCellValue('H4', 'Labor');
        $sheet->setCellValue('I4', 'Cultivo');
        $sheet->setCellValue('J4', 'Superficie');
        $sheet->setCellValue('K4', 'Proveedor');
        $sheet->setCellValue('L4', 'Certificada Por');
        $sheet->setCellValue('M4', 'Tipo Campaña');
        $sheet->setCellValue('N4', 'Calidad');
        $sheet->setCellValue('O4', 'SMS');
        $sheet->setCellValue('P4', 'PDF');
        $sheet->setCellValue('Q4', 'Problema');
        $sheet->setCellValue('R4', 'Comentario');
        $sheet->setCellValue('S4', 'Procesado Por');
                
        /* Encabezado */
        $sheet->mergeCells('A1:A3'); 
        /* Inicio Logo  */
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('El Agronomo');
        $drawing->setPath(WWW_ROOT . 'img' . DS . 'logo_elagronomo_print.png');
        $drawing->setCoordinates('A1');
        $drawing->setHeight(58);
        $drawing->setWorksheet($spreadsheet->getActiveSheet());

        /* Nombre del Sistema */
        $sheet->mergeCells('B1:D3'); /* Uno el encabezado */
        $sheet->setCellValue('B1', 'El Agronomo');

        $styleArray = [ 'font' => ['bold' => true,'size' => 36],
                        'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT ]
                      ];

        $sheet->getStyle('B1')->applyFromArray($styleArray);
        
        $sheet->setCellValue('H1', 'Listado de Mapeos');

        // if (isset($desde)) {
        //     $now = Time::parse($desde);
        //     $desde = $now->i18nFormat('dd/MM/yyyy');

        //     $now = Time::parse($hasta);
        //     $hasta = $now->i18nFormat('dd/MM/yyyy');   

        //     $sheet->setCellValue('E2', 'Desde: '.$desde);
        //     $sheet->setCellValue('E3', 'Hasta: '.$hasta);
        // }

        $styleArray = ['font' => ['bold' => false, 'size' => 11] ];

        $sheet->getStyle('E1:F3')->applyFromArray($styleArray);
        
        /* Pinto de Blanco el fondo del encabezado */
        $styleArray = [
            'borders' => [
                'outline' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,                ],
            ],
            'fill' => [ 
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFFFFF',],
            ],
        ];         
        $sheet->getStyle('A1:S3')->applyFromArray($styleArray);
        
        /* Color al encabezado */
        $styleArray = [
            'font' => [
                'bold' => false,
                'color' => ['argb' => '000000',],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'D8E4BC',],
            ],
        ];        
        $spreadsheet->getActiveSheet()->getStyle('A4:S4')->applyFromArray($styleArray);
        
        /* Pongo la Fecha en que fue generada */
        $styleArray = [
            'font' => ['size' => 8],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]
        ];        
        $spreadsheet->getActiveSheet()->getStyle('G1:G3')->applyFromArray($styleArray);
        $now = Time::now();
        $fecha ='Generado el '. $now->i18nFormat('dd/MM/yyyy HH:mm');
        $sheet->setCellValue('S1', $fecha);
        
        /* Pongo el usuario que lo genero */
        $generado = 'Generado por ' . $this->request->session()->read('Auth.User.nombre');
        $sheet->setCellValue('S2', $generado);

        $estilo_linea_proyecto_activo = ['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => '07C219']]];

        $styleArray = ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER] ];
        
        $linea = 5;
       
        /* Cargo la tabla */
        foreach ($ordenTrabajosDistribuciones as $ordenTrabajosDistribucione) {
           
            $sheet->setCellValueByColumnAndRow(1, $linea, $ordenTrabajosDistribucione->orden_trabajo_id);
            $sheet->setCellValueByColumnAndRow(2, $linea, $ordenTrabajosDistribucione->id);
            $sheet->setCellValueByColumnAndRow(3, $linea, $ordenTrabajosDistribucione->orden_trabajo->fecha ? $ordenTrabajosDistribucione->orden_trabajo->fecha->i18nFormat('dd/MM/yyyy') : '');
            $sheet->setCellValueByColumnAndRow(4, $linea, $ordenTrabajosDistribucione->proyecto->campania_monitoreo->nombre);
            $sheet->setCellValueByColumnAndRow(5, $linea, $ordenTrabajosDistribucione->orden_trabajo->establecimiento->nombre);
            $sheet->setCellValueByColumnAndRow(6, $linea, $ordenTrabajosDistribucione->lote->sectore->nombre);
            $sheet->setCellValueByColumnAndRow(7, $linea, $ordenTrabajosDistribucione->lote->nombre);
            $sheet->setCellValueByColumnAndRow(8, $linea, $ordenTrabajosDistribucione->proyectos_labore->nombre);
            $sheet->setCellValueByColumnAndRow(9, $linea, $ordenTrabajosDistribucione->proyecto->cultivo);
            $sheet->setCellValueByColumnAndRow(10, $linea, $ordenTrabajosDistribucione->superficie);
            $sheet->setCellValueByColumnAndRow(11, $linea, $ordenTrabajosDistribucione->orden_trabajo->proveedore ? $ordenTrabajosDistribucione->orden_trabajo->proveedore->nombre : '');
            $sheet->setCellValueByColumnAndRow(12, $linea, $ordenTrabajosDistribucione->orden_trabajos_certificaciones ? $ordenTrabajosDistribucione->orden_trabajos_certificaciones[0]->user->nombre : '');
            
            $tipo = $ordenTrabajosDistribucione->orden_trabajos_mapeo ? $ordenTrabajosDistribucione->orden_trabajos_mapeo->mapeos_campanias_tipo->nombre : '';
            $calidad = $ordenTrabajosDistribucione->orden_trabajos_mapeo ? $ordenTrabajosDistribucione->orden_trabajos_mapeo->mapeos_calidade->nombre : '';
            $sms = $ordenTrabajosDistribucione->orden_trabajos_mapeo ? ($ordenTrabajosDistribucione->orden_trabajos_mapeo->sms == 1 ? "SI" : "NO") : '';
            $pdf = $ordenTrabajosDistribucione->orden_trabajos_mapeo ? ($ordenTrabajosDistribucione->orden_trabajos_mapeo->pdf == 1 ? "SI" : "NO") : '';
            $problema = $ordenTrabajosDistribucione->orden_trabajos_mapeo ? $ordenTrabajosDistribucione->orden_trabajos_mapeo->mapeos_problema->nombre : '';
            $comentario = $ordenTrabajosDistribucione->orden_trabajos_mapeo ? $ordenTrabajosDistribucione->orden_trabajos_mapeo->comentario : '';
            $procesado_por = $ordenTrabajosDistribucione->orden_trabajos_mapeo ? $ordenTrabajosDistribucione->orden_trabajos_mapeo->user->nombre : '';

            $sheet->setCellValueByColumnAndRow(13, $linea, $tipo);
            $sheet->setCellValueByColumnAndRow(14, $linea, $calidad);
            $sheet->setCellValueByColumnAndRow(15, $linea, $sms);
            $sheet->setCellValueByColumnAndRow(16, $linea, $pdf);
            $sheet->setCellValueByColumnAndRow(17, $linea, $problema);
            $sheet->setCellValueByColumnAndRow(18, $linea, $comentario);
            $sheet->setCellValueByColumnAndRow(19, $linea, $procesado_por);
            
            $linea++;
        }
        
        /* autodimensionar las columnas */
        foreach (range('A', 'S') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $time = Time::now();
        $fecha_actual = $time->i18nFormat('yyyy_MM_dd_HHmm');
        $nombre_archivo = 'Listado_Mapeos_'.$fecha_actual.'.xlsx';

        // $archivo_a_guardar = ROOT.DS.'dataload'.DS.$nombre_archivo;        
        // $writer->save($archivo_a_guardar);
        // /* Fin generar archivo Excel  */
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $nombre_archivo . '"');
        header('Cache-Control: max-age=0');
        
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx', 'Excel2007');
        ob_end_clean();
        $writer->save('php://output');
        exit;                                        
    }

    /**
     * filtros de cosecha 
     */    
    private function filtrarOtCosechas() {
        
        /* busco los ot de cosecha */
        $configuraciones = $this->Configuraciones->find('all', [
            'conditions' => ["modulo" => "OrdenTrabajosMapeos", "action" => "index", "clave" => "Filtros"]
        ])->first();

        $patrones = $configuraciones->value;

        return "ProyectosLabores.nombre IN (".$patrones.")";
    }
}
