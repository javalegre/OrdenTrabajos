<?php
/**
 * Ordenes de Trabajo
 *
 * Maneja la generación de OT, edicion y otros
 *
 * Controller OrdenTrabajos
 *
 * Desarrollado para Adecoagro SA.
 * 
 * @category CakePHP3
 *
 * @package Ordenes
 *
 * @author Javier Alegre <jalegre@adecoagro.com>
 * @copyright Copyright 2021, Adecoagro
 * @version 1.0.0 creado el 21/12/2021
 */
namespace Ordenes\Controller;

use Ordenes\Controller\AppController;
use Cake\Event\Event;
use Cake\I18n\Time;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Cake\Datasource\ConnectionManager;
use Cake\Log\Log;
use Cake\Utility\Hash;

/**
 * OrdenTrabajos Controller
 *
 * @property \App\Model\Table\OrdenTrabajosTable $OrdenTrabajos
 *
 * @method \App\Model\Entity\OrdenTrabajo[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class OrdenTrabajosController extends AppController {

    public function initialize() {
        parent::initialize();
        $this->loadModel('Configuraciones');
        $this->loadModel('Ordenes.OrdenTrabajosInsumos');
        $this->loadModel('Ordenes.OrdenTrabajosInsumosEntregas');
        $this->loadModel('Ordenes.OrdenTrabajosInsumosDevoluciones');
        $this->loadModel('Ordenes.OrdenTrabajosDistribuciones');
        $this->loadModel('Ordenes.OrdenTrabajosCertificaciones');
        $this->loadModel('Ordenes.OrdenTrabajosDistribucionesTarifarios');
        $this->loadModel('Ordenes.OrdenTrabajosDataloads');
        $this->loadModel('ProyectosLaboresTarifarios');
        $this->loadModel('ProyectosLabores');
        $this->loadModel('Lotes');
        $this->loadModel('Unidades');
        $this->loadModel('Proyectos');
        $this->loadModel('Proveedores');
        $this->loadModel('Monedas');
        $this->loadModel('Almacenes');
        $this->loadModel('Productos');
        $this->loadModel('TecnicasAplicaciones');
        $this->loadModel('Tecnicaresponsables');
        $this->loadModel('ProductosExistencias');
        $this->loadModel('CampaniaMonitoreos');
        $this->loadModel('Campanias');
        $this->loadModel('Queue.QueuedJobs');
        $this->loadComponent('RequestHandler');
    }

    /**
     * Index method
     *
     * Filtrado por usuarios que tienen acceso.
     * 
     * @return \Cake\Http\Response|void
     */
    public function index() {
        
        $filtros = [];
        $grupo_id = $this->request->session()->read('Auth.User.group_id');
        
        /* Filtramos solo los administrativos e ingenieros */
        if (in_array($grupo_id, [2, 3])) {
            $filtros[] = ['id IN' => $this->request->session()->read('Auth.User.establecimientos')];
        }

        $establecimientos = $this->OrdenTrabajos->Establecimientos->find('all')->select(['id', 'nombre', 'organizacion'])->where([$filtros])->toArray();
        $campanias = $this->Campanias->find('list')->order(['id' => 'DESC']);
        
        $lista_establecimientos = $this->OrdenTrabajos->Establecimientos->find('list', [
            'conditions' => ['id IN' => $this->request->session()->read('Auth.User.establecimientos')] 
        ]);
        
        $lista_proveedores = $this->OrdenTrabajos->Proveedores->find('list');
        
        $this->set(compact('establecimientos', 'campanias', 'lista_establecimientos','lista_proveedores'));
    }
    
    /**
     * datatable
     * 
     * Muestra el datatable de Ordenes de trabajo
     * 
     */
    public function datatable() {

        $columns = [[
                        'field' => 'OrdenTrabajos.id',
                        'data' => 'id',
                        'orderable' => true,
                        'searchable' => true
                    ], [
                        'field' => 'OrdenTrabajos.fecha',
                        'data' => 'fecha'
                    ], [
                        'field' => 'Establecimientos.nombre',
                        'data' => 'establecimiento'
                    ], [
                        'field' => 'Proveedores.nombre',
                        'data' => 'proveedore'
                    ], [
                        'field' => 'Users.nombre',
                        'data' => 'nombre'
                    ], [
                        'field' => 'OrdenTrabajos.orden_trabajos_estado_id',
                        'data' => 'OrdenTrabajos.orden_trabajos_estado',
                        'searchable' => true
                    ], [
                        'field' => 'Users.nombre',
                        'data' => 're'
                    ], [
                        'field' => 'OrdenTrabajos.proveedore_id',
                        'data' => 'proveedore_id'
                    ]];  
        
        /* ****************************************************************** */
        /* Filtros                                                            */
        /* ****************************************************************** */
        $filtros = [];
        $grupo_id = $this->request->session()->read('Auth.User.group_id');
        $user_id = $this->request->session()->read('Auth.User.id');
//        if ($grupo_id == 3) { // Administrativos - Ven OT's de los establecimientos
//            $filtros[] = "OrdenTrabajos.establecimiento_id IN (".implode(",", $this->request->session()->read('Auth.User.establecimientos')).")";
//        }

        
        switch ($this->request->session()->read('Auth.User.group_id')) {
            case 1: /* Developers */
                break;
            case 3: /* Administrativos - Ven OT's de los establecimientos */
                $filtros[] = "OrdenTrabajos.establecimiento_id IN (".implode(",", $this->request->session()->read('Auth.User.establecimientos')).")";
                break;
            default: /* Aqui deberian llegar los ingenieros */
                $rol = $this->request->session()->read('Auth.User.role_id');
                if ($rol == 3 || $rol == 6) {
                    /* Obtengo los usuarios del sector */
                    $filtros[] = "OrdenTrabajos.establecimiento_id IN (".implode(",", $this->request->session()->read('Auth.User.establecimientos')).")";
                    break;
                } else {
                    /* Obtengo los usuarios del sector */
                    $usuarios = $this->NotificarA($this->request->session()->read('Auth.User.id'));
                    $establecimientos = $this->request->session()->read('Auth.User.establecimientos');
                    
                    if ($establecimientos) {
                        $filtros[] = "OrdenTrabajos.establecimiento_id IN (".implode(",", $this->request->session()->read('Auth.User.establecimientos')).")";
                    }
                    
//                    if (count($usuarios) > 0) {
//                        //$filtros[] = ["OrdenTrabajos.user_id IN (".implode(",", $usuarios).")"];
//                    }
                    break;
                }
        }
        
        /* ****************************************************************** */
        /* Filtros                                                            */
        /* ****************************************************************** */
        $estado = $this->request->getQuery('estado');
        if ($estado) {
            $filtros[] = "OrdenTrabajos.orden_trabajos_estado_id = '".$estado."'";
        }
        $establecimientos = $this->request->getQuery('establecimiento_id');
        if ($establecimientos) {
            $filtros[] = "OrdenTrabajos.establecimiento_id = '".$establecimientos."'";
        }
        $proveedor = $this->request->getQuery('proveedore_id');
        if ($proveedor) {
            $filtros[] = "OrdenTrabajos.proveedore_id = '".$proveedor."'";
        }
//        $campania = $this->request->getQuery('campania_id');
//        if ($campania) {
//            $filtros[] = "Proyectos.campania_monitoreo_id = '".$campania."'";
//        }
        $desde = $this->request->getQuery('desde');
        if ($desde) {
            $filtros[] = "OrdenTrabajos.fecha >= '".$desde."'";
        }
        $hasta = $this->request->getQuery('hasta');
        if ($hasta) {
            $filtros[] = "OrdenTrabajos.fecha <= '".$hasta."'";
        }
        /* ****************************************************************** */

        $data = $this->DataTables->find('Ordenes.OrdenTrabajos','all', [
            'contain' => ['Establecimientos' => ['fields' => ['id', 'nombre']],
                          'Proveedores' => ['fields' => ['id', 'nombre']],
                          'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']],
                          'OrdenTrabajosEstados',
                          'OrdenTrabajosDistribuciones' => [
                              'Lotes' => ['fields' => ['id', 'nombre']] ,
                            /*  'Proyectos',*/
                              'ProyectosLabores' => ['fields' => ['id', 'nombre']]
                              ]
                           ],
            'conditions' => [$filtros]
            // 'order' => ['id' => 'DESC']
        ], $columns);         
        
//        die(debug( $data->toArray() ));
        /* ************************************************************ */
        /* Datatables Server Side Processing                            */
        /* ************************************************************ */
        $this->set('columns', $columns);
        $this->set('data', $data);
        $this->set('_serialize', array_merge($this->viewVars['_serialize'], ['data']));
    }
    
    /*
     * Totales Ajax
     * Devuelve
     * - Total Certificadas
     * - Total Aprobadas
     * - Total Borrador
     * - Total Certificables
     */
    public function totalesAjax() {
        /* Filtro por los proveedores */
        $filtros = ' 1 = 1 ';
        
        $proveedores = $this->request->getQuery('proveedores');
        if ( $proveedores ) {
            $filtros = ' proveedore_id IN (' . implode(", ", $proveedores).')';
        }
        
        $totales = [];
        switch ($this->request->session()->read('Auth.User.group_id')) {
            case 1: /* Los Developers ven todo */
                $totales['certificados'] = $this->OrdenTrabajos->find('CantidadCertificadasSinDL');
                $totales['certificadosConDL'] = $this->OrdenTrabajos->find('CantidadCertificadasConDL');
                $totales['abiertas'] =  $this->OrdenTrabajos->find('CantidadAbiertas');
                $totales['cerradas'] =  $this->OrdenTrabajos->find('CantidadCerradas');
                $totales['anuladas'] =  $this->OrdenTrabajos->find('CantidadAnuladas');
                $totales['borrador'] =  $this->OrdenTrabajos->find('CantidadBorrador');
                break;
            case 3: /* Administrativos - Ven OT's de los establecimientos */
                $totales['certificados'] = $this->OrdenTrabajos->find('CantidadCertificadasSinDL', ['conditions' => ['establecimiento_id IN' => $this->request->session()->read('Auth.User.establecimientos')]]);
                $totales['certificadosConDL'] = $this->OrdenTrabajos->find('CantidadCertificadasConDL', ['conditions' => ['establecimiento_id IN' => $this->request->session()->read('Auth.User.establecimientos')]]);
                $totales['abiertas'] =  $this->OrdenTrabajos->find('CantidadAbiertas', ['conditions' => ['establecimiento_id IN' => $this->request->session()->read('Auth.User.establecimientos')]]);
                $totales['cerradas'] =  $this->OrdenTrabajos->find('CantidadCerradas', ['conditions' => ['establecimiento_id IN' => $this->request->session()->read('Auth.User.establecimientos')]]);
                $totales['anuladas'] =  $this->OrdenTrabajos->find('CantidadAnuladas', ['conditions' => ['establecimiento_id IN' => $this->request->session()->read('Auth.User.establecimientos')]]);
                $totales['borrador'] =  $this->OrdenTrabajos->find('CantidadBorrador', ['conditions' => ['establecimiento_id IN' => $this->request->session()->read('Auth.User.establecimientos')]]);
                break;
            case 6: /* Auditores */
                $totales['certificados'] = $this->OrdenTrabajos->find('CantidadCertificadasSinDL');
                $totales['certificadosConDL'] = $this->OrdenTrabajos->find('CantidadCertificadasConDL');
                $totales['abiertas'] =  $this->OrdenTrabajos->find('CantidadAbiertas');
                $totales['cerradas'] =  $this->OrdenTrabajos->find('CantidadCerradas');
                $totales['anuladas'] =  $this->OrdenTrabajos->find('CantidadAnuladas');
                $totales['borrador'] =  $this->OrdenTrabajos->find('CantidadBorrador');
                break;
            default: /* Aqui deberian llegar los ingenieros */
                /* Obtengo los usuarios del sector */
                $usuarios = $this->NotificarA($this->request->session()->read('Auth.User.id'));
                $totales['certificados'] = $this->OrdenTrabajos->find('CantidadCertificadas', ['conditions' => ['user_id IN' => $usuarios, $filtros]]);
                $totales['abiertas'] =  $this->OrdenTrabajos->find('CantidadAbiertas', ['conditions' => ['user_id IN' => $usuarios, $filtros]]);
                $totales['cerradas'] =  $this->OrdenTrabajos->find('CantidadCerradas', ['conditions' => ['user_id IN' => $usuarios, $filtros]]);
                $totales['anuladas'] =  $this->OrdenTrabajos->find('CantidadAnuladas', ['conditions' => ['user_id IN' => $usuarios, $filtros]]);
                $totales['borrador'] =  $this->OrdenTrabajos->find('CantidadBorrador', ['conditions' => ['user_id IN' => $usuarios, $filtros]]);
                break;
        }        
        $this->set(compact('totales'));
        $this->set('_serialize', 'totales');
        
        $this->RequestHandler->renderAs($this, 'json');
    }
    
    public function aplicarfiltros ($modulo = null, $accion = null){
        /* Leo la configuracion del usuario actual */
        $configuraciones = $this->Configuraciones->find('all',[
            'conditions' => ['user_id' => $this->request->session()->read('Auth.User.id'), 'modulo' => $modulo, 'action' => $accion]
        ]);
        $filtros = [];
        $filtro = '';
        
        /* Primero, los filtros por usuario */
        switch ($this->request->session()->read('Auth.User.group_id')) {
            case 3: /* Administrativos - Ven OT's de los establecimientos */
                $filtro = 'OrdenTrabajos.establecimiento_id IN (' . implode(', ', $this->request->session()->read('Auth.User.establecimientos')).')';
                break;
            default: /* Aqui deberian llegar los ingenieros */
                $usuarios = $this->NotificarA($this->request->session()->read('Auth.User.id'));
                if (!empty($usuarios)){
                    $filtro = 'OrdenTrabajos.user_id IN (' . implode(', ', $usuarios).')';
                }
                break;
        }        
        $filtros[] = $filtro;
        foreach($configuraciones as $configuracion){
            $filtro = '';
            switch ($configuracion->clave){
                case 'FiltrarCertificados':
                    if ($configuracion->value === '1'){
                        $filtro = 'OrdenTrabajos.orden_trabajos_estado_id != 4';
                    }
                    break;
                default:
                    break;
            }
            if(!empty($filtro)){
                $filtros[] = $filtro;
            }
            
        }
        $filtro_a_aplicar = implode(' and ', $filtros);

        return $filtro_a_aplicar;
    }
    /**
     * Datatable server side VCA
     * Dibujo la tabla de OrdenTrabajosInsumos, asociadas a OrdenTrabajos y OrdenTrabajosDistribuciones
     * @param getQuery $parametros para filtrar datatable
     *
     * Parametros:
     *  - Desde  - (formato dd/MM/yyyy) Ej. 08/09/2022
     *           - en ambas fechas, toma el dato de la tabla OrdenTrabajos para filtrar. 
     *  - Hasta  - (formato dd/MM/yyyy) Ej. 08/09/2022
     *  - Establecimiento - (int)id del establecimiento a filtrar que viene del index-vca,
     *    compara el establecimiento_id de OrdenTrabajos para filtrar.
     *  - Proveedor  - (int) id de proveedor a filtrar que viene del index-vca,
     *    compara el proveedore_id de OrdenTrabajos para filtrar.
     */
     public function datatableVca() {
         $columns = [[
                        'field' => 'OrdenTrabajos.id',
                        'data' => 'orden_trabajo.id',
                        'orderable' => true,
                        'searchable' => true
                    ], [
                        'field' => 'OrdenTrabajos.fecha',
                        'data' => 'orden_trabajo.fecha'
                    ], [
                        'field' => 'OrdenTrabajos.Establecimientos.nombre',
                        'data' => 'orden_trabajo.establecimiento.nombre'
                    ], [
                        'field' => 'OrdenTrabajos.Proveedores.nombre',
                        'data' => 'orden_trabajo.proveedore.nombre'
                    ], [
                        'field' => 'Productos.nombre',
                        'data' => 'producto.nombre'
                    ], [
                        'field' => 'OrdenTrabajosDistribuciones.Lotes.nombre',
                        'data' => 'orden_trabajos_distribucione.lote.nombre'
                    ], [
                        'field' => 'OrdenTrabajos.id',
                            'data' => 'orden_trabajo.id'
                    ]]; 
        
        /* ****************************************************************** */
        /* Filtros                                                            */
        /* ****************************************************************** */
        $filtros = [];
        
        $establecimientos = $this->request->getQuery('establecimiento_id');
        if ($establecimientos) {
            $filtros[] = "OrdenTrabajos.establecimiento_id = '".$establecimientos."'";
        }
        $proveedor = $this->request->getQuery('proveedore_id');
        if ($proveedor) {
            $filtros[] = "OrdenTrabajos.proveedore_id = '".$proveedor."'";
        }
        $desde = $this->request->getQuery('desde');
        if ($desde) {
            $filtros[] = "OrdenTrabajos.fecha >= '".$desde."'";
        }
        $hasta = $this->request->getQuery('hasta');
        if ($hasta) {
            $filtros[] = "OrdenTrabajos.fecha <= '".$hasta."'";
        }
        /*
         *  Los de rol 4,5 y 10, se filtra por establecimiento asignado al usuario.
         */
        if ( $this->request->session()->read('Auth.User.role_id') == 4|5|10) {
            $filtros[] = ['OrdenTrabajos.establecimiento_id IN' => $this->request->session()->read('Auth.User.establecimientos')];
        }
        
        $data = $this->DataTables->find('Ordenes.OrdenTrabajosInsumos','all', [
            'contain' => ['OrdenTrabajos' => ['Establecimientos' => ['fields' => ['id', 'nombre']],
                                              'Proveedores' => ['fields' => ['id', 'nombre']],
                                              'OrdenTrabajosEstados'],
                          'OrdenTrabajosDistribuciones' => ['Lotes' => ['fields' => ['id', 'nombre']]],
                          'Productos'=> ['fields' => ['id', 'nombre']]
                        ],
            'conditions' => [$filtros,
                             'OrdenTrabajos.orden_trabajos_estado_id < 4'], /* Solo los aprobados */
            'sort' => ['OrdenTrabajos.fecha' => 'DESC'],
            'group' => ['OrdenTrabajosInsumos.orden_Trabajo_id']
        ], $columns); 
    
        /* ************************************************************ */
        /* Datatables Server Side Processing                            */
        /* ************************************************************ */
        $this->set('columns', $columns);
        $this->set('data', $data);
        $this->set('_serialize', array_merge($this->viewVars['_serialize'], ['data']));
         
    }
    /**
     * Index de VCA
     * @param array $parametros para filtrar datos en datatableVca, estos parametros son los que mando a
     * a la funcion del datatable y para poder ejecutar el reporte. 
     */
    public function indexVca() {
        
        $filtros = [];
        $grupo_id = $this->request->session()->read('Auth.User.group_id');
        
        /* Filtramos solo los administrativos e ingenieros */
        if (in_array($grupo_id, [2, 3])) {
            $filtros[] = ['id IN' => $this->request->session()->read('Auth.User.establecimientos')];
        }

        $filtro_establecimientos = $this->OrdenTrabajos->Establecimientos->find('all')->select(['id', 'nombre', 'organizacion'])->where([$filtros])->toArray();

        
        /* Si existe algo en organizaciones, es porque la petición es json */
        if ($this->request->is('json')) {
            $campania = $this->request->getData('campania');
            $establecimientos = $this->request->getData('establecimientos');
            
            $proyectos = $this->Proyectos->find('all', [
                'fields' => ['id', 'text' => 'nombre']
            ]);
            
            if ($campania) {
                $proyectos->where(['campania_monitoreo_id' => $campania]);
            }
            if ($establecimientos) {
                $proyectos->where(['establecimiento_id IN' => explode(',', str_replace(' ', '', $establecimientos))]);
            }
            $this->set(['respuesta' => ['status' => 'success', 'message' => 'Los datos se recibieron correctamente.', 'establecimientos' => json_encode($proyectos->toArray())],
                        '_serialize' => 'respuesta'
                ]);
            $this->RequestHandler->renderAs($this, 'json');
            return;
        }
        
        $establecimientos = $this->OrdenTrabajos->Establecimientos->find('list');
        
        /*
         *  Todos los que NO son developers, van filtrados por establecimientos 
         */
        if ( $this->request->session()->read('Auth.User.role_id') == 4|5|10) {
            $establecimientos = $this->OrdenTrabajos->Establecimientos->find('list', [
                'conditions' => ['id IN' => $this->request->session()->read('Auth.User.establecimientos')] 
            ]);
        }
        
        $campanias = $this->CampaniaMonitoreos->find('list', [
            'conditions' => ['activa' => '1'],
            'order' => ['id' => 'DESC']
        ]);
        
        $id_campania = $this->CampaniaMonitoreos->find('all', ['conditions' => ['activa' => '1'], 'order' => ['id' => 'DESC']])->first();
        
        /* Muestro por defecto los proyectos de la ultima campaña */
        $proyectos = $this->Proyectos->find('list', [
            'conditions' => ['campania_monitoreo_id' => $id_campania->id]
        ]);
        
        $this->set(compact('campanias', 'proyectos','establecimientos','filtro_establecimientos'));
    }
    
    public function indexVcaInterfaz() 
    {
        
        $ordenTrabajos = $this->OrdenTrabajos->find('all', [
            'contain' => [
                            'Establecimientos',
                            'Users' => ['fields' => ['id', 'nombre']],
                            'Proveedores' => ['fields' => ['id', 'nombre']],
                            'OrdenTrabajosEstados',
                            'OrdenTrabajosDistribuciones' => ['Lotes', 'ProyectosLabores']
                        ],
            'conditions' => ['OrdenTrabajos.orden_trabajos_estado_id < 4', 'Establecimientos.id IN' => $this->request->session()->read('Auth.User.establecimientos')], /* Solo los aprobados */
            'sort' => ['OrdenTrabajos.fecha' => 'ASC']
        ]);
        
        if ($this->request->is('json')) {
            $ordenTrabajos = $this->OrdenTrabajosInsumosEntregas->find('all', [
                    'contain' => [
                                    'Almacenes',
                                    'Productos',
                                    'Unidades',
                                    'OrdenTrabajosInsumos' => ['OrdenTrabajos' => ['Establecimientos']]
                                ]
                ]);
                
            $filtros = $this->request->getData();
            /* Aplico los filtros de Fecha y Establecimientos */
            if ($filtros['desde']) {
                $ordenTrabajos->where(['OrdenTrabajosInsumosEntregas.fecha >=' => $filtros['desde']]);
            }
            if ($filtros['hasta']) {
                $ordenTrabajos->where(['OrdenTrabajos.fecha <=' => $filtros['hasta']]);
            }
            if ($filtros['establecimientos']) {
                $lista_establecimientos = explode(',', str_replace(' ', '', $filtros['establecimientos']));
                $ordenTrabajos->where(['OrdenTrabajos.establecimiento_id IN' => $lista_establecimientos]);
            }
        }
        
        $establecimientos = $this->OrdenTrabajos->Establecimientos->find('list', [
            'conditions' => ['id IN' => $this->request->session()->read('Auth.User.establecimientos')] 
        ]);
        
        $this->set(compact('ordenTrabajos', 'establecimientos'));
        $this->set('_serialize', 'ordenTrabajos');
    }
    /**
     * View method
     *
     * @param string|null $id Orden Trabajo id.
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null) {
        
        $ordenTrabajo = $this->OrdenTrabajos->get($id, [
            'contain' => (['Proveedores' => ['fields' => ['id', 'nombre', 'email']],
                           'Establecimientos' => ['fields' => ['id', 'nombre']],
                           'OrdenTrabajosEstados' => ['fields' => ['id', 'nombre']],
                           'OrdenTrabajosCondicionesMeteorologicas',
                           'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']],
                           'OrdenTrabajosCertificaciones' => ['Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']]],
                           'OrdenTrabajosDistribuciones' => ['ProyectosLabores',
                                                             'Lotes' => ['Sectores'], 
                                                             'Unidades',
                                                             'Proyectos',
                                                             'Monedas',
                                                             'OrdenTrabajosCertificaciones',
                                                             'OrdenTrabajosDistribucionesTarifarios'],
                           'OrdenTrabajosInsumos' => ['Productos', 'Unidades', 'Almacenes', 'ProductosLotes']])
        ]);
        
        /* Verifico si la OT es de Siembra */
        $verificar_siembra = $this->OrdenTrabajos->get($id, [
            'contain' => ['OrdenTrabajosInsumos' => ['conditions' => ['orden_trabajos_distribucione_id' => 0 ]]]
        ]);
        if ( $verificar_siembra->orden_trabajos_insumos  ) {
            return $this->redirect(['action' => 'siembra', $ordenTrabajo->id]);
        }
        
        $alquila_implementos = '';
        
        // SE CALCULA EL MONTO TOTAL CERTIFICADO
        $distribuciones = $ordenTrabajo->orden_trabajos_distribuciones;
        foreach ($distribuciones as $distribucion){
            if ($distribucion->orden_trabajos_distribuciones_tarifario) {
                if (!$alquila_implementos) {
                    $alquila_implementos = $distribucion->orden_trabajos_distribuciones_tarifario->orden_trabajo_alquiler_id ? $distribucion->orden_trabajos_distribuciones_tarifario->orden_trabajo_alquiler_id : '';
                }
            }
        }
        
        $neighbors = $this->OrdenTrabajos->find('neighbors', ['id' => $id, 'user_id' => $this->request->session()->read('Auth.User.id')]);
        
        /* Configuro el nombre del archivo */
        $this->viewBuilder()->options([
            'pdfConfig' => [
                'orientation' => 'portrait',
                'filename' => 'OT_' . $id . '.pdf'
            ]
        ]);
        $this->set(compact(['ordenTrabajo', 'neighbors', 'alquila_implementos']));
        $this->set('_serialize', ['ordenTrabajo']);
        
    }

    public function viewCertificada($id = null) {
        $ordenTrabajo = $this->OrdenTrabajos->get($id, [
            'contain' => ['Proveedores',
                          'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']],
                          'OrdenTrabajosCondicionesMeteorologicas',
                          'Establecimientos', 'OrdenTrabajosEstados', 'OrdenTrabajosCertificaciones' => ['Users' => ['fields' => ['id', 'nombre']]] , 'OrdenTrabajosDistribuciones' => ['ProyectosLabores', 'OrdenTrabajosCertificaciones', 'Unidades', 'Proyectos', 'Lotes' => ['Sectores'], 'Monedas'],
                'OrdenTrabajosInsumos' => ['Productos', 'ProductosLotes', 'Unidades', 'Almacenes', 'OrdenTrabajosInsumosEntregas', 'OrdenTrabajosInsumosDevoluciones'], 'OrdenTrabajosCertificacionesImagenes']
        ]);
        /* Busco el Promedio de Importe Certificados */
        foreach($ordenTrabajo->orden_trabajos_distribuciones as $distribucion){
            $certificado  = $this->OrdenTrabajosDistribuciones->find('ImporteCertificado', ['IdDistribucion' => $distribucion->id]);
            $distribucion->ImporteCertificado = $certificado->importe;
        }
        /* */
        foreach($ordenTrabajo->orden_trabajos_insumos as $insumo){
            /* Busco las cantidades utilizadas */
            $utilizado = 0;
            foreach($insumo->orden_trabajos_insumos_entregas as $entregas){
                $utilizado += $entregas->cantidad;
            }
            /* Busco las cantidades devueltas */
            $devueltos = 0; 
            foreach($insumo->orden_trabajos_insumos_devoluciones as $devoluciones){
                $devueltos += $devoluciones->cantidad;
            }
            $insumo->utilizado = $utilizado - $devueltos;
        }
        $establecimientos = $this->OrdenTrabajos->Establecimientos->find('list');
        $proveedores = $this->OrdenTrabajos->Proveedores->find('list', ['limit' => 200]);
        $estados = $this->OrdenTrabajos->OrdenTrabajosEstados->find('list');

        $this->viewBuilder()->options([
            'pdfConfig' => [
                'orientation' => 'portrait',
                'filename' => 'OT_' . $id . '.pdf'
            ]
        ]);

        $this->set(compact('ordenTrabajo', 'proveedores', 'establecimientos', 'labores', 'productos', 'lote', 'estados'));
    }

    /**
     * Reprocesar
     * 
     * Marco la OT para reprocesar en la interfaz de OC
     * 
     * @param string|null $id Orden Trabajo id.
     * @return \Cake\Http\Response|void
     */
    public function reprocesar($id = null) {
        $ordenTrabajo = $this->OrdenTrabajos->get($id);
        if ($ordenTrabajo->oracle_oc_flag == 'E') {
            $ordenTrabajo->oracle_oc_flag = 'R';
            $ordenTrabajo->interface_error = '';
            if ($this->OrdenTrabajos->save($ordenTrabajo)) {
                $this->OrdenTrabajosCertificaciones->updateAll(
                    ['oracle_flag' => 'R','interface_error' => ''],
                    ['orden_trabajo_id' => $id]);
                $data = ['status' => 'success', 'message' => 'Se marcó para reprocesar.'];
            }
        } else {
            $data = ['status' => 'error', 'message' => 'Ocurrió un error al reprocesar'];
        }
        $this->set(compact('data'));
        $this->set('_serialize', 'data');
        $this->RequestHandler->renderAs($this, 'json');        
    }
    
    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add($lote = null) {
        $ordenTrabajo = $this->OrdenTrabajos->newEntity();
        
        if ($this->request->is('post')) {
            $ordenTrabajo = $this->OrdenTrabajos->patchEntity($ordenTrabajo, $this->request->getData());
            
            if ($this->OrdenTrabajos->save($ordenTrabajo)) {
                if ($this->request->getData('siembra')) {
                    return $this->redirect(['action' => 'siembra', $ordenTrabajo->id, $lote]);
                }
                /* Ya tengo guardado el ID generado */
                return $this->redirect(['action' => 'edit', $ordenTrabajo->id, $lote]);
            }
            $this->Flash->error(__('The orden trabajo could not be saved. Please, try again.'));
        }
        if (is_array($this->request->session()->read('Auth.User.establecimientos'))) {
            $establecimientos = $this->OrdenTrabajos->Establecimientos->find('list', [
                'conditions' => ['Establecimientos.id IN' => $this->request->session()->read('Auth.User.establecimientos')]]);
        } else {
            $establecimientos = $this->OrdenTrabajos->Establecimientos->find('list');
        }
        $this->set(compact('ordenTrabajo', 'establecimientos'));
    }

    /* Pasa la OT a estado cerrado, si no está incluido en algun dataload */
    public function quitarCertificacion( $id = null ) {
        $data = [];
        $mensajes = [];
        
        $ordenTrabajo = $this->OrdenTrabajos->get($id, [
            'contain' => ['OrdenTrabajosDataloads',
                          'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']]]
        ]);
        
        if ($ordenTrabajo->orden_trabajos_dataload) {
            $mensajes[] = 'La OT ya fué incluida en el dataload '.$ordenTrabajo->orden_trabajos_dataload->id.' el '.$ordenTrabajo->orden_trabajos_dataload->fecha->i18nFormat('dd/MM/yyyy HH:mm');
            $data['status'] = 'error';
        }
        if ($ordenTrabajo->oracle_oc_flag) {
            $mensajes[] = 'La OT ya fué subida a oracle.';
            $data['status'] = 'error';
        }
        if ( !$mensajes ) {
            if ($ordenTrabajo->user_id != $this->request->session()->read('Auth.User.id')) {
                $mensajes[] = 'Solo el usuario '. $ordenTrabajo->user->nombre.' puede quitar la certificación';
                $data['status'] = 'error';
            }
        }
        
        /* Si no hay errores, saco la certificacion */
        if ( !$mensajes ) {
            $ordenTrabajo->orden_trabajos_estado_id = 3;
            if ($this->OrdenTrabajos->save( $ordenTrabajo)) {
                $mensajes[] = 'La OT '.$id.' pasó a estado cerrado correctamente.';
                $data['status'] = 'success';
            }
        }
        
        $data['message'] = $mensajes;
        
        $this->set(compact('data'));
        $this->set('_serialize', 'data');
        
        $this->RequestHandler->renderAs($this, 'json');
    }
    
    /* Cargo una certificacion parcial */
    public function certificacion() {
        
        /* -----------------------------------------------------
         * Recibo los datos
         * -----------------------------------------------------
         * realizada - cantidad efectuada
         * fecha_modal
         * distribucion-id
         * fecha-inicio - Fecha de la OT
         * orden-trabajo
         * observaciones
         * precio_final - precio con el que certifican la labor
         * 
         */
        
        $data = $this->request->data;
        
        $status = 'success';
        $mensajes = [];
        $datos= [];
        
        $this->loadModel('OrdenTrabajosCertificaciones');
        $this->loadModel('OrdenTrabajosDistribuciones');

        $consultas = $this->OrdenTrabajosDistribuciones->get($data['distribucion-id'], [
                    'contain' => ['OrdenTrabajosCertificaciones', 'ProyectosLabores', 'OrdenTrabajos']
                ]);
        
        $id_orden_trabajo = $consultas->orden_trabajo->id;
        
        /* Sumo las cantidades certificadas */
        $certificado = 0;
        foreach ($consultas->orden_trabajos_certificaciones as $certificacion):
            $certificado += $certificacion['has'];
        endforeach;
        
        /* Valido que tenga certificaciones */
        if ($data['precio_final'] == 0){
            $status = 'error';
            $mensajes[] = 'No se puede certificar una labor con importe 0.';
        }
        /* Valido que no sea una certificación de 0 (cero) */
        if ($data['realizada'] == 0){
            $status = 'error';
            $mensajes[] = 'No puede certificar con 0 (cero) unidades.';
        }
        /* Valido la cantidad */
        if (($certificado + $data['realizada']) > $consultas->superficie){
            $status = 'error';
            $mensajes[] = 'No puede certificar una superficie mayor a la ordenada. Su máximo a certificar es de <b> '.($consultas->superficie - $certificado).'</b>';
        }
        /* Valido que la fecha de ejecucion no sea anterior a la fecha de ordenada */
//        if (strtotime($data['fecha_modal']) < strtotime($data['fecha-inicio'])){
//            $status = 'error';
//            $mensajes[] = 'La fecha de certificación no puede ser anterior a la fecha de ordenado.';
//        }
        
        /* Si no hay errores, guardo el registro */
        if (count($mensajes) == 0){
            $certificacion = $this->OrdenTrabajosCertificaciones->newEntity();
            
            $fechaInicio = str_replace('/', '-', $data['fecha-inicio']);
            $fechaFinal = str_replace('/', '-', $data['fecha_modal']);

            $certificacion->orden_trabajos_distribucione_id = $data['distribucion-id'];
            $certificacion->orden_trabajo_id = $data['orden-trabajo'];
            $certificacion->fecha_inicio = date('Y-m-d H:i:s', strtotime($fechaInicio));
            $certificacion->fecha_final = date('Y-m-d H:i:s', strtotime($fechaFinal));
            $certificacion->observaciones = $data['observaciones'];
            $certificacion->has = $data['realizada'];
            $certificacion->tipo_cambio = $data['cotizacion'];
            $certificacion->precio_final = round($data['precio_final'], 2);
            $certificacion->user_id = $this->request->session()->read('Auth.User.id');
            $certificacion->moneda_id = $data['moneda'] ? $data['moneda'] : 1;
            
            if ($this->OrdenTrabajosCertificaciones->save($certificacion)){
                $mensajes[] = 'La certificación se guardó correctamente.';
                $ordenTrabajos = $this->OrdenTrabajos->get($id_orden_trabajo, [
                    'contain' => (['OrdenTrabajosCertificaciones' => ['Users' => ['fields' => ['id', 'nombre', 'ruta_imagen']]],
                                   'OrdenTrabajosDistribuciones'])
                ]);
                $datos['certificaciones'] = $ordenTrabajos;
            }
        }
        
        $datos['status'] = $status;
        $datos['message'] = $mensajes;

        $this->set(compact('datos'));
        $this->set('_serialize', 'datos');
        
        $this->RequestHandler->renderAs($this, 'json');
    }
    
    /* Guardo todos los mapas generados para su impresion */

    public function subirmapas() {

        /* Recupero los datos      */
        $data = $this->request->data;

        /* Establezco la ruta donde estan los mapas de las OT's */
        $ruta = WWW_ROOT . 'img' . DS . 'OrdenTrabajoMapas' . DS;
        /* Si no está creado el directorio, lo creo */
        $this->create_folder($ruta);

        $ot = $data['ot'];
        $imglote = $data['imagen_lote'];
//        $imgkml = $data['imagen_kml'];

        /* Primero guardo la imagen del lote */
        $fileName = $ruta . 'OT-' . $ot . '-' . 'mapa.png';
        $this->guardar_archivo($imglote, $fileName);

        /* Ahora guardo la segunda imagen */
//        $fileName = $ruta.'OT-'.$ot.'-'.'kml.png';
//        $this->guardar_archivo($imgkml, $fileName);        

        $this->set('fileName', $fileName);
    }

    function guardar_archivo($archivo = false, $nombre = false) {
        /* Ahora guardo la segunda imagen */
        $archivo = str_replace('data:image/png;base64,', '', $archivo);
        $archivo = str_replace(' ', '+', $archivo);
        $fileData = base64_decode($archivo);
        file_put_contents($nombre, $fileData);
    }

    function create_folder($path = false) {
        if ($path && !file_exists($path)) {
            mkdir($path, 0777);
        }
    }

    /* Guardo todas las certificaciones de los insumos */

    public function certificacioninsumos() {
        $this->loadModel('OrdenTrabajosInsumos');
        $data = $this->request->data;
        
        $datos= [];
        
        if ($this->request->is('ajax')) {


            $insumos = $this->OrdenTrabajosInsumos->get($data['distribucion-id-ins']);

            $insumos->utilizado = $data['utilizado-ins'];

            /* Recalculo la dosis real aplicada */

        
            if ($this->OrdenTrabajosInsumos->save($insumos)) {
                $status = 'success';
                $mensajes = 'Se certificó el insumo correctamente.';
            } else {
                $status = 'error';
                $mensajes = 'Ocurrió un error al certificar el insumo';
            }
        }
        
        $datos['status'] = $status;
        $datos['message'] = $mensajes;
        
        $this->set(compact('datos')); // Pass $data to the view
    }

    /**
     * Edit method
     *
     * @param string|null $id Orden Trabajo id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null, $lote = null) {
        $status = [];
        
        $ordenTrabajo = $this->OrdenTrabajos->get($id, [
            'contain' => ([ 'Proveedores',
                            'OrdenTrabajosDistribuciones' => ['ProyectosLabores',
                                                             'Lotes' => ['Sectores'],
                                                             'Unidades',
                                                             'Proyectos',
                                                             'Monedas',
                                                             'OrdenTrabajosCertificaciones',
                                                             'TecnicasAplicaciones',
                                                             'OrdenTrabajosInsumos',
                                                             'OrdenTrabajosDistribucionesTarifarios'
                                                            ],
                            'OrdenTrabajosInsumos' => ['Productos',
                                                       'Unidades',
                                                       'Almacenes',
                                                       'OrdenTrabajosInsumosEntregas',
                                                       'OrdenTrabajosInsumosDevoluciones',
                                                       'ProductosLotes'
                                                       ],
                            'OrdenTrabajosCondicionesMeteorologicas'
                         ])
        ]);
        
        $ordenTrabajo->certificable = $this->OrdenTrabajos->find('Certificable', ['IdOrden' => $id]);
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            
            $data = $this->request->getData();
            if ($data['cm_fecha']) {
                /* hay una fecha en Condiciones Meteorologicas */
                $resultado = $this->guardarCondiciones( $data );
            }
            $ordenTrabajo = $this->OrdenTrabajos->patchEntity($ordenTrabajo, $this->request->getData());
            
            if ($ordenTrabajo->orden_trabajos_distribuciones) {
                $ordenTrabajo->orden_trabajos_estado_id = 2;
            }
            
            if ( $ordenTrabajo->certificable === 1) {
                $ordenTrabajo->orden_trabajos_estado_id = 3;
            }
            
            if ($this->OrdenTrabajos->save($ordenTrabajo)) {
                if ($this->request->is('ajax')) {
                    $status['guardado'] = 'success';
                }
            } else {
                if ($this->request->is('ajax')) {
                    $status['guardado'] = 'error';
                }
            }
        }
        
        /* Verifico si la OT es de Siembra */
        $verificar_siembra = $this->OrdenTrabajos->get($id, [
            'contain' => ['OrdenTrabajosInsumos' => ['conditions' => ['orden_trabajos_distribucione_id' => 0 ]]]
        ]);
        if ( $verificar_siembra->orden_trabajos_insumos  ) {
            return $this->redirect(['action' => 'siembra', $ordenTrabajo->id]);
        }
        
        /* Si ya está aprobada, lo mando a la vista */
        if ($ordenTrabajo->orden_trabajos_estado_id >= 4) {
            return $this->redirect(['action' => 'view', $ordenTrabajo->id]);
        }        
        if (is_array($this->request->session()->read('Auth.User.establecimientos'))) {
            $establecimientos = $this->OrdenTrabajos->Establecimientos->find('list', [
                'conditions' => ['Establecimientos.id IN' => $this->request->session()->read('Auth.User.establecimientos')]]);
        } else {
            $establecimientos = $this->OrdenTrabajos->Establecimientos->find('list');
        }
        
        $proyectos = $this->Proyectos->find('all', [
            'fields' => ['id', 'nombre'],
            'conditions' => ['Proyectos.establecimiento_id' => $ordenTrabajo->establecimiento_id,
                             'Proyectos.activa' => '1',
                             'Proyectos.id IN' => $this->request->session()->read('Auth.User.proyectos')
                            ]
        ]);
        
        $proveedores = $this->OrdenTrabajos->Proveedores->find('list', [
            'conditions' => ['id' => $ordenTrabajo->proveedore_id]
        ]);
        
        /* Pendiente */
        $insumos = $this->OrdenTrabajosInsumos->find('all', [
            'fields' => ['id' => 'Productos.id', 'nombre' => 'Productos.nombre'],
            'conditions' => ['orden_trabajo_id' => $id],
            'contain' => ['Productos']
        ]);
        
        $unidades = $this->Unidades->find('all',[
            'fields' => ['id', 'nombre']
        ]);
        
        $tecnicas = $this->TecnicasAplicaciones->find('all', [
            'fields' => ['id', 'nombre']
        ]);
         
        /* Pongo los lotes */
        $lotes = $this->VerificarExistenciaLote($ordenTrabajo);
        
        $labores = $this->OrdenTrabajosDistribuciones->find('all', [
            'conditions' => ['orden_trabajo_id' => $id],
            'contain' => ['ProyectosLabores']
        ]);
        
        $monedas = $this->Monedas->find('all', [
            'fields' => ['id', 'simbolo']
        ]);
        
        $almacenes = $this->Almacenes->find('all', [
            'conditions' => ['establecimiento_id' => $ordenTrabajo->establecimiento_id]
        ]);
        
        $tarifario = $this->OrdenTrabajosDistribucionesTarifarios->find('all', [
            'contain' => ['OrdenTrabajosDistribuciones', 'ProyectosLaboresTarifarios'],
            'conditions' => ['OrdenTrabajosDistribuciones.orden_trabajo_id' => $id]
        ])->toArray();
        
        $estados = $this->OrdenTrabajos->OrdenTrabajosEstados->find('list');

        $this->set(compact('ordenTrabajo', 'establecimientos', 'proveedores', 'estados', 'proyectos', 'lotes', 'tecnicas', 'unidades', 'monedas', 'lote', 'insumos','almacenes', 'labores', 'tarifario'));
        
        $this->set('_serialize', ['ordenTrabajo', 'proyectos', 'lotes', 'tecnicas', 'unidades', 'monedas', 'insumos', 'almacenes','labores']);

        //$this->RequestHandler->renderAs($this, 'json');
    }
    
    /**
     * Metodo Duplicar
     * @param type $id
     * @return OT duplicada con Distribucion e insumos
     */
    public function duplicar($id = null) {
        
        $original = $this->OrdenTrabajos->get($id, [
            'contain' => ['OrdenTrabajosDistribuciones']
        ]);
        
        /* Genero la copia de la OT */
        $copia = $this->OrdenTrabajos->duplicate( $id );
        
        /* Genero los duplicados de cada linea */
        foreach ($original->orden_trabajos_distribuciones as $distribucion) {
            $copia_distribucion = $this->OrdenTrabajosDistribuciones->duplicate($distribucion->id);
            
            /* Tengo la copia, ahora le asigno el ID de la OT */
            if ($copia_distribucion->orden_trabajos_insumos) {
                foreach ($copia_distribucion->orden_trabajos_insumos as $insumo) {
                    $insumo_nuevo = $this->OrdenTrabajosInsumos->get($insumo->id);
                    $insumo_nuevo->orden_trabajo_id = $copia->id;
                    $this->OrdenTrabajosInsumos->save($insumo_nuevo);
                }
            }
            $linea_nueva = $this->OrdenTrabajosDistribuciones->get($copia_distribucion->id);
            $linea_nueva->orden_trabajo_id = $copia->id;
            $this->OrdenTrabajosDistribuciones->save($linea_nueva);
        }
        
        $result['status'] = 'success';
        $result['data'] = $copia;
        
        $this->set(compact('result'));
        $this->set('_serialize', 'result');
        
        $this->RequestHandler->renderAs($this, 'json');
        
        
    }
    
    public function siembra($id = null, $lote = null) {
        $status = [];
        
        $ordenTrabajo = $this->OrdenTrabajos->get($id, [
            'contain' => ([ 'Proveedores',
                                'Establecimientos',
                                'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']],
                                'OrdenTrabajosDistribuciones' => ['ProyectosLabores',
                                                                 'Lotes' => ['Sectores'],
                                                                 'Unidades',
                                                                 'Proyectos',
                                                                 'Monedas',
                                                                 'OrdenTrabajosCertificaciones',
                                                                 'TecnicasAplicaciones',
                                                                 'OrdenTrabajosDistribucionesTarifarios'
                                                                ],
                                'OrdenTrabajosInsumos' => ['Productos',
                                                           'Unidades',
                                                           'Almacenes',
                                                           'OrdenTrabajosInsumosEntregas',
                                                           'OrdenTrabajosInsumosDevoluciones',
                                                           'ProductosLotes'
                                                           ],
                                'OrdenTrabajosCondicionesMeteorologicas'
                             ])
            ]);
        
        
        $ordenTrabajo->certificable = $this->OrdenTrabajos->find('Certificable', ['IdOrden' => $id]);
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            
            $data = $this->request->getData();
            
            if ($data['cm_fecha']) {
                /* hay una fecha en Condiciones Meteorologicas */
                $resultado = $this->guardarCondiciones( $data );
            }
            
            $ordenTrabajo = $this->OrdenTrabajos->patchEntity($ordenTrabajo, $this->request->getData());
            
            if ($ordenTrabajo->orden_trabajos_distribuciones) {
                $ordenTrabajo->orden_trabajos_estado_id = 2;
            }
            
            if ( $ordenTrabajo->certificable === 1) {
                $ordenTrabajo->orden_trabajos_estado_id = 3;
            }            
            
            if ($this->OrdenTrabajos->save($ordenTrabajo)) {
                if ($this->request->is('ajax')) {
                    $status['guardado'] = 'success';
                }
            } else {
                if ($this->request->is('ajax')) {
                    $status['guardado'] = 'error';
                }
            }
        }
        
        /* Si ya está aprobada, lo mando a la vista */
        if ($ordenTrabajo->orden_trabajos_estado_id >= 4) {
            return $this->redirect(['action' => 'view', $ordenTrabajo->id]);
        }        
        
        if (is_array($this->request->session()->read('Auth.User.establecimientos'))) {
            $establecimientos = $this->OrdenTrabajos->Establecimientos->find('list', [
                'conditions' => ['Establecimientos.id IN' => $this->request->session()->read('Auth.User.establecimientos')]]);
        } else {
            $establecimientos = $this->OrdenTrabajos->Establecimientos->find('list');
        }
        
        /*  */
        if (is_array($this->request->session()->read('Auth.User.proyectos'))) {
            $proyectos = $this->OrdenTrabajos->OrdenTrabajosDistribuciones->Proyectos->find('all', [
                'fields' => ['id', 'nombre'],
                'conditions' => ['Proyectos.id IN' => $this->request->session()->read('Auth.User.proyectos'),
                                 'Proyectos.establecimiento_id' => $ordenTrabajo->establecimiento_id]
            ]);
        } else {
            $proyectos = $this->OrdenTrabajos->OrdenTrabajosDistribuciones->Proyectos->find('all', [
                'limit' => 1
            ]);
        }
        $proveedores = $this->OrdenTrabajos->Proveedores->find('list', [
            'conditions' => ['id' => $ordenTrabajo->proveedore_id]
        ]);
       
        /* Pendiente */
        $insumos = $this->OrdenTrabajosInsumos->find('all', [
            'fields' => ['id' => 'Productos.id', 'nombre' => 'Productos.nombre'],
            'conditions' => ['orden_trabajo_id' => $id],
            'contain' => ['Productos']
        ]);        
        
        $unidades = $this->Unidades->find('all',[
            'fields' => ['id', 'nombre']
        ]);
        
        $tecnicas = $this->TecnicasAplicaciones->find('all', [
            'fields' => ['id', 'nombre']
        ]);
         
        $lotes = $this->Tecnicaresponsables->find('all', [
            'fields' => ['Tecnicaresponsables.user_id', 'Tecnicaresponsables.lote_id', 'Lotes.nombre', 'Lotes.hectareas_reales', 'Sectores.nombre', 'Establecimientos.nombre'],
            'contain' => ['Lotes' => ['Sectores', 'Establecimientos']],
            'conditions' => ['user_id' => $this->request->session()->read('Auth.User.id'),
                             'Lotes.establecimiento_id' => $ordenTrabajo->establecimiento_id],
            'sort' => ['TecnicaResponsables.lote_id' => 'ASC']
        ]);
           
        $monedas = $this->Monedas->find('all', [
            'fields' => ['id', 'simbolo']
        ]);
        
        $almacenes = $this->Almacenes->find('all', [
            'conditions' => ['establecimiento_id' => $ordenTrabajo->establecimiento_id]
        ]);
        
        if ( $ordenTrabajo->orden_trabajos_insumos ) {
            foreach ($ordenTrabajo->orden_trabajos_insumos as $linea_insumo) {
                $entregado = 0;
                foreach ($linea_insumo->orden_trabajos_insumos_entregas as $entrega) {
                    $entregado += $entrega->cantidad;
               }
                $devolucion = 0;
                foreach ($linea_insumo->orden_trabajos_insumos_devoluciones as $devoluciones) {
                    $devolucion += $devoluciones->cantidad;
                }
                $linea_insumo->utilizado = round($entregado - $devolucion, 3); /* Este valor es el utilizado real */
                $linea_insumo->entrega = round($entregado, 3);
                $linea_insumo->devolucion = round($devolucion, 3);
            }
        }
        
        /* Filtro las labores incluidas en las lineas de distribucion */
        $labores = $this->OrdenTrabajosDistribuciones->findByOrdenTrabajoId($id)->contain('ProyectosLabores')->group('proyectos_labore_id');
        
        $estados = $this->OrdenTrabajos->OrdenTrabajosEstados->find('list');

        $this->set(compact('ordenTrabajo', 'establecimientos', 'proveedores', 'estados', 'proyectos', 'lotes', 'tecnicas', 'unidades', 'labores', 'monedas', 'lote', 'insumos','almacenes'));
        
        $this->set('_serialize', ['ordenTrabajo', 'proyectos', 'lotes', 'tecnicas', 'unidades', 'monedas', 'insumos', 'almacenes', 'labores']);

        //$this->RequestHandler->renderAs($this, 'json');
    }
    
    /**
     *  Guardo las condiciones climaticas de la OT especificada
     * 
     */
    private function guardarCondiciones( $data = null ) {
        
        $registro = $this->OrdenTrabajos->OrdenTrabajosCondicionesMeteorologicas->find('all', [
            'conditions' => ['orden_trabajo_id' => $data['id']]
        ])->first();
        
        if ( !$registro ) {
            $registro = $this->OrdenTrabajos->OrdenTrabajosCondicionesMeteorologicas->newEntity();
            $registro->orden_trabajo_id = $data['id'];
            $registro->user_id = $this->request->session()->read('Auth.User.id');
        }
        
        $fecha = Time::createFromFormat('d/m/Y H:i', $data['cm_fecha']);
        
        $registro->fecha = $fecha;
        $registro->temperatura = $data['cm_temperatura'];
        $registro->humedad = $data['cm_humedad'];
        $registro->viento = $data['cm_viento'];
        $registro->direccion = $data['cm_direccion'];
        
        if ($this->OrdenTrabajos->OrdenTrabajosCondicionesMeteorologicas->save($registro)) {
            return $registro;
        }
        return false;
    }
    
    /**
     * VCA
     *
     */
    public function vca($id = null) {

        /* Si ya está certificada, lo mando a la vista */
        if ($this->request->session()->read('Auth.User.role_id') == '4') {
            return $this->redirect(['controller' => 'Inicios', 'action' => 'inicio']);
        }
        if ($this->request->session()->read('Auth.User.role_id') == '5') {
            return $this->redirect(['controller' => 'Inicios', 'action' => 'inicio']);
        }
        
        
        
        $ordenTrabajo = $this->OrdenTrabajos->get( $id,[
            'contain' => ['Proveedores',
                          'OrdenTrabajosEstados',
                          'Establecimientos',
                          'OrdenTrabajosDistribuciones' => ['ProyectosLabores', 'Lotes', 'Unidades', 'Proyectos', 'Monedas', 'OrdenTrabajosCertificaciones'],
                          'OrdenTrabajosInsumos' => ['Productos',
                                                     'Unidades',
                                                     'Almacenes',
                                                     'ProductosLotes',
                                               'OrdenTrabajosInsumosEntregas' => ['Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']],
                                                                                  'Productos',
                                                                                  'Almacenes'],
                                               'OrdenTrabajosInsumosDevoluciones'  => ['Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']],
                                                                                  'Productos',
                                                                                  'Almacenes'] ]],
            'withDeleted' => 'withDeleted'
        ]);
        
        if ($ordenTrabajo->orden_trabajos_estados_id >= 4) {
            return $this->redirect(['action' => 'view', $ordenTrabajo->id]);
        }        
        /* Sumo todos los entregados/utilizados */
        foreach ($ordenTrabajo->orden_trabajos_insumos as $insumos) {
            $entregado = 0;
            foreach ($insumos->orden_trabajos_insumos_entregas as $entrega) {
                $entregado += $entrega->cantidad;
            }
            $devolucion = 0;
            foreach ($insumos->orden_trabajos_insumos_devoluciones as $devoluciones) {
                $devolucion += $devoluciones->cantidad;
            }
            $insumos->utilizado = round($entregado - $devolucion, 3); /* Este valor es el utilizado real */
            $insumos->entrega = round($entregado, 3);
            $insumos->devolucion = round($devolucion, 3);
        }
        
        $almacenes = $this->Almacenes->find('all', [
            'fields' => ['id', 'nombre', 'sub_inventario', 'localizacion'],
            'conditions' => ['establecimiento_id' => $ordenTrabajo->establecimiento_id]
        ]);
        
        $estados = $this->OrdenTrabajos->OrdenTrabajosEstados->find('list');

        /* Configuro el nombre del archivo */
        $this->viewBuilder()->options([
            'pdfConfig' => [
                'orientation' => 'portrait',
                'filename' => 'VCA_' . $id . '.pdf'
            ]
        ]);

        $this->set(compact('ordenTrabajo', 'establecimientos', 'productos', 'estados', 'almacenes'));
        $this->set('_serialize', 'almacenes');
        
        /* Si ya está certificada, lo mando a la vista */
        if ($ordenTrabajo->orden_trabajos_estados_id >= 4) {
            return $this->redirect(['action' => 'view', $ordenTrabajo->id]);
        }
    }

    public function nuevaot($id = null) {
        $ordenTrabajo = $this->OrdenTrabajos->newEntity();
        if ($this->request->is('post')) {

            $ordenTrabajo = $this->OrdenTrabajos->patchEntity($ordenTrabajo, $this->request->getData());
            $ordenTrabajo->user_id = $this->request->session()->read('Auth.User.id');
            if ($this->OrdenTrabajos->save($ordenTrabajo)) {
                $this->Flash->success(__('The orden trabajo has been saved.'));
                /* Ya tengo guardado el ID generado */
                return $this->redirect(['action' => 'edit', $ordenTrabajo->id]);
            }
            $this->Flash->error(__('The orden trabajo could not be saved. Please, try again.'));
        }
        $establecimientos = $this->OrdenTrabajos->Establecimientos->find('list', ['limit' => 200]);
        $proveedores = $this->OrdenTrabajos->Proveedores->find('list', ['limit' => 200]);
        $labores = $this->OrdenTrabajos->ProyectosLabores->find('list', ['limit' => 200]);
        $this->set(compact('ordenTrabajo', 'establecimientos', 'proveedores', 'labores'));
    }

    /* Ejecuto la OT 
     * Pasa de estar de Borrador al Circuito de Aprobacion
     * 
     * Si no hay plan, pasa a Aprobada directamente.
     */

    public function ejecutarot() {
        $datos = $this->request->data();

        
        $id = $datos['id'];

        $ordenTrabajo = $this->OrdenTrabajos->get($id, [
            'contain' => ['OrdenTrabajosDistribuciones']
        ]);
        /* Pasa a estar Abierta */
        $ordenTrabajo->orden_trabajos_estado_id = 2;
        if ($this->OrdenTrabajos->find('Certificable', ['IdOrden' => $id]) === 1){
            /* Pasa a estar Cerrada si tiene todas las entregas de productos */
            $ordenTrabajo->orden_trabajos_estado_id = 3;
        }
        $ordenTrabajo->establecimiento_id = $datos['establecimiento_id'];
        $ordenTrabajo->proveedore_id = $datos['proveedore_id'];
        $ordenTrabajo->observaciones = $datos['observaciones'];
        $fecha = Time::createFromFormat(
                        'd/m/Y',
                        $datos['fecha'],
                        'America/Buenos_Aires'
        );

        /* Aplico el formato correcto */
        $ordenTrabajo->fecha = $fecha->i18nFormat('yyyy-MM-dd HH:mm:ss');

        $data = [
            'content' => $id,
            'status' => 'error',
            'message' => 'Ocurrió un error al actualizar.'
        ];

        if ($this->OrdenTrabajos->save($ordenTrabajo)) {
            $data = [
                'content' => $id,
                'status' => 'success',
                'message' => 'Se actualizó correctamente.'
            ];
            /* --------------------------------------------------------------------------------------
             * 
             *  Genero el evento para enviar las notificaciones.
             *  Este evento se maneja desde App\Event\OrdenTrabajoListener
             *  
             * -------------------------------------------------------------------------------------- */
            /* Obtengo el ultimo registro completo */
            $orden = $this->OrdenTrabajos->get($ordenTrabajo->id, ['contain' => ['Proveedores', 'Establecimientos' => 'Almacenes', 'OrdenTrabajosInsumos']]);
            $orden->toArray();
            $event = new Event('Model.OrdenTrabajos.Nuevo', $this, json_encode($orden));
            $this->eventManager()->dispatch($event);
            /* -------------------------------------------------------------------------------------- */
        }

        $this->set(compact('data'));
    }
    
    /* Certificacion de una OT de Siembra */
    public function certificarSiembra($id = null) {
        $has_certificadas = 0;
        
        
//        $ordenTrabajo = $this->OrdenTrabajos->get($id, [
//            'contain' => (['OrdenTrabajosDistribuciones' => ['ProyectosLabores',
//                                                             'Lotes',
//                                                             'Unidades',
//                                                             'Proyectos',
//                                                             'Monedas',
//                                                             'OrdenTrabajosCertificaciones',
//                                                             'OrdenTrabajosDistribucionesTarifarios' => ['ProyectosLaboresTarifarios']
//                                                            ],
//                            'Establecimientos',
//                            'Proveedores',
//                            'OrdenTrabajosEstados',
//                            'OrdenTrabajosCertificaciones' => ['Users', 'Monedas'],
//                            'OrdenTrabajosCondicionesMeteorologicas',
//                            'OrdenTrabajosInsumos' => ['Productos', 'Unidades', 'Almacenes', 'OrdenTrabajosInsumosEntregas', 'OrdenTrabajosInsumosDevoluciones']
//                        ])
//        ]); 
        
        $ordenTrabajo = $this->OrdenTrabajos->get($id, [
            'contain' => ['OrdenTrabajosDistribuciones' => ['ProyectosLabores',
                                                            'Lotes',
                                                            'Unidades',
                                                            'Proyectos',
                                                            'Monedas',
                                                            'OrdenTrabajosCertificaciones',                                                            
                                                            'OrdenTrabajosDistribucionesTarifarios' => ['ProyectosLaboresTarifarios']
                                                            ],
                'Establecimientos',
                          'Proveedores',
                          'OrdenTrabajosEstados',
                          'OrdenTrabajosCondicionesMeteorologicas',
                          'OrdenTrabajosCertificaciones' => 'Users',
                          
                          'OrdenTrabajosInsumos' => ['Productos', 'Almacenes', 'Unidades','OrdenTrabajosInsumosEntregas', 'OrdenTrabajosInsumosDevoluciones', 'ProductosLotes']
                ]
        ]);
        
        /* Si ya está certificado / Anulado muestro la vista */
        if ($ordenTrabajo->orden_trabajos_estado_id > 3) {
            return $this->redirect(['action' => 'view', $id]);
        }
        
        if ($this->request->is(['patch', 'post', 'put'])) {

            $ordenTrabajo = $this->OrdenTrabajos->patchEntity($ordenTrabajo, $this->request->getData());
            
            $ordenTrabajo->orden_trabajos_estado_id = 4;

            if ($this->OrdenTrabajos->save($ordenTrabajo)) {
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The orden trabajo could not be saved. Please, try again.'));
        }
        
        //SE CHEQUEA QUE HAYA UNA CERTIFICACION POR LINEA Y EL PRECIO SEA MAYOR A 0
        $permiteFinalizar = true;
//        foreach ($ordenTrabajo->orden_trabajos_distribuciones as $distribucion) {
//            $monto = 0; $has = 0;
//            foreach ($distribucion->orden_trabajos_certificaciones as $certificacion){
//                $monto += $certificacion->has * $certificacion->precio_final;
//                $has += $certificacion->has;
//            }
//            $distribucion->total_certificado = $monto;
//            $distribucion->hascertificadas = $has;
//            
//            $distribucion->importe_certificado = 0;
//            if ($has !== 0){
//                $certificado = $monto / $has;
//                $distribucion->importe_certificado = round( $certificado, 2 );
//            }
//            $has_certificadas += $has; 
//            
//            if(empty($d->orden_trabajos_certificaciones)) {
//                $permiteFinalizar = 0;
//            } else {
//                $monto = 0;
//                foreach ($d->orden_trabajos_certificaciones as $l):
//                    $monto += $l->has * $l->precio_final;
//                endforeach;
//                /* if($monto == 0) $permiteFinalizar = 0; */ /* Permitimos la certificacion sin importes */
//            }
//        }
//
//        /* Sumo todos los entregados/utilizados */
//        foreach ($ordenTrabajo->orden_trabajos_insumos as $insumos) {
//            
//            $entregado = 0;
//            $entregado = $this->OrdenTrabajosInsumos->find('entregas', ['IdInsumos' => $insumos->id])->entregas;
//
//            $devolucion = 0;
//            $devolucion = $this->OrdenTrabajosInsumos->find('devoluciones', ['IdInsumos' => $insumos->id])->devoluciones;            
//            
//            $insumos->utilizado = round($entregado - $devolucion, 3); /* Este valor es el utilizado real */
//            $insumos->entrega = round($entregado, 3);
//            $insumos->devolucion = round($devolucion, 3);
//        }
        
        
        //SE CALCULA EL MONTO TOTAL CERTIFICADO
        $distribuciones = $ordenTrabajo->orden_trabajos_distribuciones;
        foreach ($distribuciones as $distribucion){
            $monto = 0; $has = 0;
            
            if(!$distribucion->orden_trabajos_certificaciones) {
                $permiteFinalizar = false;
            }
            
            foreach ($distribucion->orden_trabajos_certificaciones as $certificacion){
                $monto += $certificacion->has * $certificacion->precio_final;
                $has += $certificacion->has;
            }
            $distribucion->total_certificado = $monto;
            $distribucion->hascertificadas = $has;
            
            $distribucion->importe_certificado = 0;
            if ($has !== 0){
                $certificado = $monto / $has;
                $distribucion->importe_certificado = round( $certificado, 2 );
            }
            $has_certificadas += $has;
            
            foreach ($ordenTrabajo->orden_trabajos_insumos as $insumo) {
                if ($distribucion->id == $insumo->orden_trabajos_distribucione_id) {
                    $entregado = 0;
                    $entregado = $this->OrdenTrabajosInsumos->find('entregas', ['IdInsumos' => $insumo->id])->entregas;

                    $devolucion = 0;
                    $devolucion = $this->OrdenTrabajosInsumos->find('devoluciones', ['IdInsumos' => $insumo->id])->devoluciones;

                    $insumo->utilizado = round($entregado - $devolucion, 3); /* Este valor es el utilizado real */
                    $insumo->entrega = round($entregado, 3);
                    $insumo->devolucion = round($devolucion, 3);
                    
                    $insumo->dosis_aplicada = 0;
                    
                    if($distribucion->hascertificadas != 0){
                        $insumo->dosis_aplicada = round($insumo->utilizado / $distribucion->hascertificadas, 3);
                    }
                }
            }
        }        
        
        /*
         * Agrego el control de Tipo de Usuario
         * 
         * Solo pueden terminar una certificacion los usuarios que se cumplan con los siguientes requerimientos:
         * - Grupo: Ingenieros
         * - Rol: Encargado de Sector / Encargado de Campo
         * 
         */
        if ( $this->request->session()->read('Auth.User.group_id') === 2 ) { /* Solo los Ingenieros pueden aprobar */
            switch ($this->request->session()->read('Auth.User.role_id')) {
                case 5: /* Encargado de Sector */
                    break;
                case 6: /* Encargado de Campo */
                    break;
                case 10: /* Encargado de Agricultura */
                    break;
                default:
                    $permiteFinalizar = false;
                    break;
            }
        } else {
            $permiteFinalizar = false;
        }
        
        $cotizacion = $this->obtenercotizacion();
        
        // SE BORRA LOS DATOS QUE NO SE UTILIZAN PARA OPTIMIZAR LA VISTA
        $InsumosCertificar = $this->insumosACertificar( $id ); 
        $monedas = $this->Monedas->find('all')->toArray();
        
        $this->set(compact('permiteFinalizar','ordenTrabajo', 'cotizacion', 'InsumosCertificar', 'monedas'));
        $this->set('_serialize', ['permiteFinalizar','ordenTrabajo', 'cotizacion', 'InsumosCertificar', 'monedas']);

    }    

    
    /* CERTIFICACION
     * Pasa de estar de Borrador al Circuito de Aprobacion
     * 
     * Si no hay plan, pasa a Aprobada directamente.
     */

    public function certificarot($id = null) {
        $has_certificadas = 0;
        
        $ordenTrabajo = $this->OrdenTrabajos->get($id, [
            'contain' => (['OrdenTrabajosDistribuciones' => ['ProyectosLabores',
                                                             'Lotes',
                                                             'Unidades',
                                                             'Proyectos',
                                                             'Monedas',
                                                             'OrdenTrabajosCertificaciones',
                                                             'OrdenTrabajosDistribucionesTarifarios' => ['ProyectosLaboresTarifarios']
                                                            ],
                            'Establecimientos',
                            'Proveedores',
                            'OrdenTrabajosEstados',
                            'OrdenTrabajosCertificaciones' => ['Users', 'Monedas'],
                            'OrdenTrabajosCondicionesMeteorologicas',
                            'OrdenTrabajosInsumos' => ['Productos', 'Unidades', 'Almacenes', 'OrdenTrabajosInsumosEntregas', 'OrdenTrabajosInsumosDevoluciones']
                        ])
        ]);        
        

        /* Si ya está certificado / Anulado muestro la vista */
        if ($ordenTrabajo->orden_trabajos_estado_id > 3) {
            return $this->redirect(['action' => 'view', $id]);
        }

        /* Verifico si es una OT de Siembra */
        $verificar = $this->OrdenTrabajosInsumos->find('all', [
            'conditions' => ['orden_trabajos_distribucione_id' => 0,
                             'orden_trabajo_id' => $id]
        ])->first();
        if ( $verificar ) {
            return $this->redirect(['action' => 'certificar-siembra', $id]);
        }
        
        
        if ($this->request->is(['patch', 'post', 'put'])) {

            $ordenTrabajo = $this->OrdenTrabajos->patchEntity($ordenTrabajo, $this->request->getData());
            
            $ordenTrabajo->orden_trabajos_estado_id = 4;

            if ($this->OrdenTrabajos->save($ordenTrabajo)) {
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The orden trabajo could not be saved. Please, try again.'));
        }
        
        //SE CHEQUEA QUE HAYA UNA CERTIFICACION POR LINEA Y EL PRECIO SEA MAYOR A 0
        $permiteFinalizar = true;
        foreach ($ordenTrabajo->orden_trabajos_distribuciones as $d):
            if(empty($d->orden_trabajos_certificaciones)) $permiteFinalizar = 0;
            else{
                $monto = 0;
                foreach ($d->orden_trabajos_certificaciones as $l):
                    $monto += $l->has * $l->precio_final;
                endforeach;
                /* if($monto == 0) $permiteFinalizar = 0; */ /* Permitimos la certificacion sin importes */
            }
        endforeach;

        /* Sumo todos los entregados/utilizados */
        foreach ($ordenTrabajo->orden_trabajos_insumos as $insumos) {
            $entregado = 0;
            foreach ($insumos->orden_trabajos_insumos_entregas as $entrega) {
                $entregado += $entrega->cantidad;
            }
            $devolucion = 0;
            foreach ($insumos->orden_trabajos_insumos_devoluciones as $devoluciones) {
                $devolucion += $devoluciones->cantidad;
            }
            $insumos->utilizado = round($entregado - $devolucion, 3); /* Este valor es el utilizado real */
            $insumos->entrega = round($entregado, 3);
            $insumos->devolucion = round($devolucion, 3);
        }
     
        
        //SE CALCULA EL MONTO TOTAL CERTIFICADO
        $distribuciones = $ordenTrabajo->orden_trabajos_distribuciones;
        foreach ($distribuciones as $distribucion){
            $monto = 0; $has = 0;
            foreach ($distribucion->orden_trabajos_certificaciones as $certificacion){
                $monto += $certificacion->has * $certificacion->precio_final;
                $has += $certificacion->has;
            }
            $distribucion->total_certificado = $monto;
            $distribucion->hascertificadas = $has;
            
            $distribucion->importe_certificado = 0;
            if ($has !== 0){
                $certificado = $monto / $has;
                $distribucion->importe_certificado = round( $certificado, 2 );
            }
            $has_certificadas += $has;
            
            foreach ($ordenTrabajo->orden_trabajos_insumos as $insumo) {
                if ($distribucion->id == $insumo->orden_trabajos_distribucione_id) {
                    $entregado = 0;
                    $entregado = $this->OrdenTrabajosInsumos->find('entregas', ['IdInsumos' => $insumo->id])->entregas;

                    $devolucion = 0;
                    $devolucion = $this->OrdenTrabajosInsumos->find('devoluciones', ['IdInsumos' => $insumo->id])->devoluciones;

                    $insumo->utilizado = round($entregado - $devolucion, 3); /* Este valor es el utilizado real */
                    $insumo->entrega = round($entregado, 3);
                    $insumo->devolucion = round($devolucion, 3);

                    $insumo->dosis_aplicada = 0;
                    if($has != 0){
                        $insumo->dosis_aplicada = round($insumo->utilizado / $has, 3);
                    }
                }
            }
            
        }
        
        /*
         * Agrego el control de Tipo de Usuario
         * 
         * Solo pueden terminar una certificacion los usuarios que se cumplan con los siguientes requerimientos:
         * - Grupo: Ingenieros
         * - Rol: Encargado de Sector / Encargado de Campo
         * 
         */
        if ( $this->request->session()->read('Auth.User.group_id') === 2 ) { /* Solo los Ingenieros pueden aprobar */
            switch ($this->request->session()->read('Auth.User.role_id')) {
                case 5: /* Encargado de Sector */
                    break;
                case 6: /* Encargado de Campo */
                    break;
                case 10: /* Encargado de Agricultura */
                    break;
                default:
                    $permiteFinalizar = false;
                    break;
            }
        } else {
            $permiteFinalizar = false;
        }
                
        $cotizacion = $this->obtenercotizacion();
        
        // SE BORRA LOS DATOS QUE NO SE UTILIZAN PARA OPTIMIZAR LA VISTA
        //unset($ordenTrabajo['orden_trabajos_insumos']);
        
        $monedas = $this->Monedas->find('all')->toArray();
        
        $this->set(compact('permiteFinalizar','ordenTrabajo', 'cotizacion', 'monedas'));
        $this->set('_serialize', ['permiteFinalizar','ordenTrabajo', 'cotizacion', 'monedas']);
        
    }

    /*
     *  Finalizo la certificacion
     * 
     *  Lineas de Distribucion deben tener una certificación hecha.
     *  
     *  Todas las lineas de Insumos deben tener al menos:
     *  - una entrega de producto.
     *  - certificación de aplicación.
     *
     *  */

    public function finalizarot( $id = null ) {
        
        $data = $this->request->getData();
        
        $id = $data['id'];
        $observaciones = $data['observaciones'];

        $status = 'success';
        $mensajes = [];
        $datos = [];
        
        /* Verifico si existe una OT de Alquiler */
        $existeAlquiler = $this->verificarExistenciaAlquiler($id);
        if (is_bool($existeAlquiler) && $existeAlquiler) {
            $this->set(['respuesta' => ['status' => 'error', 'message' => 'Existen OT de alquiler asociadas con status de Certificada. <br>Comuniquese con Sistemas.'],
                            '_serialize' => 'respuesta']);
            $this->RequestHandler->renderAs($this, 'json');
            return;
        }
        
        $ordenTrabajo = $this->OrdenTrabajos->get($id, [
            'contain' => ['OrdenTrabajosDistribuciones' => ['ProyectosLabores',
                                                            'OrdenTrabajosDistribucionesTarifarios',
                                                            'Unidades',
                                                            'OrdenTrabajosCertificaciones',
                                                            'OrdenTrabajosInsumos' => ['OrdenTrabajosInsumosEntregas', 'Productos']],
                          'OrdenTrabajosInsumos' => [ 'conditions' => ['orden_trabajos_distribucione_id' => 0] ,'OrdenTrabajosInsumosEntregas', 'OrdenTrabajosInsumosDevoluciones', 'Productos'],
                          'OrdenTrabajosCertificaciones' => ['finder' => ['all' => ['withDeleted']]]
                         ]
        ]);
        
        /* 
         * Verificamos si el establecimiento de la OT está entre los autorizados para el usuario actual 
         */
        if (!in_array($ordenTrabajo->establecimiento_id, $this->request->session()->read('Auth.User.establecimientos'))) {
            $this->set(['respuesta' => ['status' => 'error', 'message' => 'No puede finalizar una OT de un establecimiento que no tiene habilitado.'],
                            '_serialize' => 'respuesta']);
            $this->RequestHandler->renderAs($this, 'json');
            return;            
        }
        
        if ($this->request->is(['patch', 'post', 'put'])) {
            $variable = isset($data['cm_fecha']) ? $data['cm_fecha'] : null;
            if ( $variable ) {
                /* hay una fecha en Condiciones Meteorologicas */
                $resultado = $this->guardarCondiciones( $data );
            }
        }
        
        /* Verifico que todas las lineas estén certificadas */
        $distribuciones = $ordenTrabajo->orden_trabajos_distribuciones;
        foreach($distribuciones as $distribucion){
            /* Verifico que todas las lineas tengan una certificacion */
            if ( !$distribucion->orden_trabajos_certificaciones ){
                $status = 'error';
                $mensajes[] = 'La labor '.$distribucion->proyectos_labore->nombre.' ('.$distribucion->superficie.' '.$distribucion->unidade->codigo.') aún no se ha certificado.';
            }
            /* Ahora verifico los insumos */
            $insumos = $distribucion->orden_trabajos_insumos;
            foreach($insumos as $insumo){
                /* Verico que el insumo tenga al menos una entrega */
                if ( !$insumo->orden_trabajos_insumos_entregas ) {
                    $status = 'error';
                    $mensajes[] = 'El insumo '.$insumo->producto->nombre.' no registra entregas.';
                }
            }
        }
        /* Verifico si es una OT de Siembra */
        $verificar = $this->OrdenTrabajosInsumos->find('all', [
            'conditions' => ['orden_trabajos_distribucione_id' => 0,
                             'orden_trabajo_id' => $id]
        ])->first();
        if ( $verificar ) {
            $status = 'error';
            $mensajes[] = 'La OT aún tiene Insumos sin distribuir.';
        }
        
        $user_id = $this->request->session()->read('Auth.User.id');
        
        /* Verifico que el usuario sea ingeniero y rol encargado de sector / campo */
        if ( $this->request->session()->read('Auth.User.group_id') === 2 ) { /* Solo los Ingenieros pueden aprobar */
            switch ($this->request->session()->read('Auth.User.role_id')) {
                case 5: /* Encargado de Sector */
                        /* Verifico que todas las certificaciones parciales realizadas sean del usuario */
                        $certificaciones = $ordenTrabajo->orden_trabajos_certificaciones;
                        foreach($certificaciones as $certificacion){
                            $certificacion->user_id = $user_id;
                            $this->OrdenTrabajosCertificaciones->save($certificacion);
                        }                    
                    break;
                case 6: /* Encargado de Campo */
                        /* Verifico que todas las certificaciones parciales realizadas sean del usuario */
                        $certificaciones = $ordenTrabajo->orden_trabajos_certificaciones;
                        foreach($certificaciones as $certificacion){
                            $certificacion->user_id = $user_id;
                            $this->OrdenTrabajosCertificaciones->save($certificacion);
                        }                    
                    break;
                case 10: /* Encargado de Agricultura */
                        /* Verifico que todas las certificaciones parciales realizadas sean del usuario */
                        $certificaciones = $ordenTrabajo->orden_trabajos_certificaciones;
                        foreach($certificaciones as $certificacion){
                            $certificacion->user_id = $user_id;
                            $this->OrdenTrabajosCertificaciones->save($certificacion);
                        }                    
                    break;
                default:
                    $status = 'error';
                    $mensajes[] = 'El rol que tiene no le permite finalizar una certificación.';
                    break;
            }
        } else {
            $status = 'error';
            $mensajes[] = 'El usuario debe ser del Grupo INGENIEROS para poder finalizar una certificación.';
        }

        /* Si todo funcionó correctamente, guardo la certificación */
        if ( !$mensajes ) {
            
            /* Busco en las tecnicas, en los casos de siembra */
            $this->cargatecnica( $ordenTrabajo->id );
            
            $ordenTrabajo = $this->OrdenTrabajos->get($id);
            $ordenTrabajo->orden_trabajos_estado_id = 4; /* FIX IT */
            $ordenTrabajo->observaciones = $observaciones;
            
            if ($ordenTrabajo->oracle_oc_flag == 'Y') {
                $ordenTrabajo->oracle_oc_flag = 'R';
                
                /* Marco las Certificaciones para reprocesar, en el caso de que ya estén marcadas como subidas */
                $this->OrdenTrabajosCertificaciones->updateAll(
                    ['oracle_flag' => 'R'],
                    ['orden_trabajo_id' => $ordenTrabajo->id, 'oracle_flag IN' => ['Y', 'E']]
                );
            }
            
            if ($this->OrdenTrabajos->save($ordenTrabajo)) {
                $mensajes[] = 'Se finalizó la certificación correctamente.';
                
                /* Marco el proyecto a la tabla tecnicaresponsables. */
                $this->agregarProyecto($id);
                
                /* Busco las OT de alquileres */
                $this->procesarAlquileres($ordenTrabajo, $user_id);
            }
            
        }
        
        $datos['status'] = $status;
        $datos['message'] = $mensajes;
        
        $this->set(compact('datos'));
        $this->set('_serialize', 'datos');
        
        $this->RequestHandler->renderAs($this, 'json');
    }
    
    /*
     *  Agrego el proyecto a la tabla tecnicaresponsables
     */
    private function agregarProyecto( $id = null ) {

        $ordenTrabajos = $this->OrdenTrabajosDistribuciones->find('all', [
                            'conditions' => ['orden_trabajo_id' => $id]
                        ]);
        foreach ($ordenTrabajos as $ot) {
            if ($ot->lote_id != 0 ) {
                $responsables = $this->Tecnicaresponsables->find('all', [
                    'conditions' => ['lote_id' => $ot->lote_id]
                ]);

                /* Marco la tecnica responsables */
                foreach ($responsables as $responsable) {
                    $marcar = $this->Tecnicaresponsables->get($responsable->id);
                    if ($marcar) {
                        $marcar->proyecto_id = $ot->proyecto_id;
                        $this->Tecnicaresponsables->save($marcar);
                    }
                }
            }
        }
    }
    
    /**
     * Delete method
     *
     * @param string|null $id Orden Trabajo id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */    
    public function delete($id = null)
    {
        $mensajes = [];

        $orden_trabajo = $this->OrdenTrabajos->get($id, [
            'contain' => [
                'OrdenTrabajosDistribuciones' => ['ProyectosLabores', 'Unidades', 'OrdenTrabajosCertificaciones'],
                'OrdenTrabajosInsumos' => ['Productos' => ['fields' => ['id', 'nombre']],'OrdenTrabajosInsumosEntregas', 'OrdenTrabajosInsumosDevoluciones']]
        ]);

        /* Verifico que todos los insumos registren la devolución completa de los mismos */
        foreach ($orden_trabajo->orden_trabajos_insumos as $insumos) {
            $entregado = 0;
            foreach ($insumos->orden_trabajos_insumos_entregas as $entrega) {
                $entregado += $entrega->cantidad;
            }
            $devolucion = 0;
            foreach ($insumos->orden_trabajos_insumos_devoluciones as $devoluciones) {
                $devolucion += $devoluciones->cantidad;
            }
            $insumos->utilizado = round($entregado - $devolucion, 3); /* Este valor es el utilizado real */
            if ( (int)$insumos->utilizado !== 0 ) {
                $status = 'error';
                $mensajes[] = 'El insumo '.$insumos->producto['nombre'].' registra entregas sin devolución.'.$insumos->utilizado;
            }
        }
//        /* Reviso que no haya certificaciones */
//        foreach($orden_trabajo->orden_trabajos_distribuciones as $distribucion){
//            /* Verifico que todas la linea NO tengan una certificacion */
//            if ( $distribucion->orden_trabajos_certificaciones ){
//                $status = 'error';
//                $mensajes[] = 'La labor '.$distribucion->proyectos_labore->nombre.' ('.$distribucion->superficie. ' ' .$distribucion->unidade->codigo.') tiene una certificación.';
//            }
//        }

        if ( $orden_trabajo->orden_trabajos_estado_id > 3) {            
            unset($mensajes);
            $status = 'error';
            $mensajes[] = 'No puede anular una Orden de Trabajo en este estado. Contactese con un administrador para eliminarla.';
        }

        if ( !$mensajes ) {
            $orden_trabajo->orden_trabajos_estado_id = 5;
            if ($this->OrdenTrabajos->save($orden_trabajo)) {
                $status = 'success';
                $mensajes[] = 'Se ha anulado correctamente la OT solicitada';
            } else {
                $status = 'error';
                $mensajes[] = 'No se pudo anular la OT solicitada';
            }
        }
        $datos['status'] = $status;
        $datos['message'] = $mensajes;

        $this->set('datos', $datos);
        $this->set('_serialize', 'datos');

        $this->RequestHandler->renderAs($this, 'json');
    }
    
    private function insumosentregados( $id = null )
    {
        $entregado = 0;
        $entregas = $this->OrdenTrabajosInsumos->find('Entregas', ['IdInsumos' => $id]);
        if (!$entregas->entregas){
            $entregado = $entregas->entregas;
        }
        
        $devuelto = 0;
        $devoluciones = $this->OrdenTrabajosInsumos->find('Devoluciones', ['IdInsumos' => $id]);        
        if (!$devoluciones->devoluciones){
            $devuelto = $devoluciones->devoluciones;
        }
        
        $aplicado = $entregado - $devuelto;

        return $aplicado;
    }
    
    /* Orden de Trabajo
     * Envio todos los datos para que se pueda cargar una nueva Orden de Trabajo
     */

    public function nuevaordentrabajo() {
        $data = $this->request->data;
        $id = $data['id'];

        $unidades = $this->Unidades->find('all',[
            'fields' => ['id', 'nombre']
        ]);

        /* Busco todos los lotes asignados a este usuario */
        $lotes = $this->Lotes->find();
        $lotes->contain(['Tecnicaresponsables' => ['Users' => ['fields' => ['id', 'nombre']]], 'Establecimientos']);
        $lotes->matching('Tecnicaresponsables', function ($q) {
            return $q->where(['Tecnicaresponsables.user_id' => $this->request->session()->read('Auth.User.id')]);
        });
        
        /* Filtro las campañas de acuerdo al usuario */
        $proyectos = $this->Proyectos->find('all', [
            'conditions' => ['activa ' => 1, 'establecimiento_id IN' => $this->request->session()->read('Auth.User.establecimientos')]
        ]);

        $proyecto = $this->request->session()->read('Auth.User.proyectos');
        if (is_array($proyecto)) {
            $proyectos = $this->Proyectos->find('all',
                    ['conditions' => ['id IN' => $proyecto]]);
        }

        $monedas = $this->Monedas->find('all');

        $productos = $this->Productos->find('all');
        
        $ot = $this->OrdenTrabajos->get($id, [
            'contain' => (['OrdenTrabajosDistribuciones' => ['ProyectosLabores', 'Lotes', 'Unidades', 'Proyectos', 'Monedas', 'OrdenTrabajosCertificaciones', 'TecnicasAplicaciones'],
        'OrdenTrabajosInsumos' => ['Productos', 'Unidades', 'Almacenes', 'OrdenTrabajosInsumosEntregas', 'OrdenTrabajosInsumosDevoluciones']])
        ]);
        
        $ordenTrabajos = $ot->toArray();

        /* Envio solo los almacenes del campo seleccionado */
        $almacenes = $this->Almacenes->find('all', [
            'conditions' => ['Almacenes.establecimiento_id' => $ordenTrabajos['establecimiento_id']]
        ]);

        $lista_proyectos = [];
        
        $tecnicas = $this->TecnicasAplicaciones->find('all');
        
        $distribuciones = $ordenTrabajos['orden_trabajos_distribuciones'];

        if (count($distribuciones) > 0) {
            foreach ($distribuciones as $distribucione) {
                $lista_proyectos[] = $distribucione['proyecto_id'];
            }
            $labores = $this->ProyectosLabores->find('all', [
                'conditions' => ['ProyectosLabores.proyecto_id IN' => $lista_proyectos]
            ]);
            
        } else {
            $labores = $this->ProyectosLabores->find('all', [
                'limit' => 0
            ]);
        }

        $this->set(compact('labores', 'lotes', 'unidades', 'proyectos', 'ordenTrabajos', 'monedas', 'almacenes', 'productos', 'tecnicas'));
    }

//    public function certificacionajax( $id = null ) {
//        
//        $has_certificadas = 0;
//        
//        $ordenTrabajos = $this->OrdenTrabajos->get($id, [
//            'contain' => (['OrdenTrabajosDistribuciones' => ['ProyectosLabores',
//                                                             'Lotes',
//                                                             'Unidades',
//                                                             'Proyectos',
//                                                             'Monedas',
//                                                             'OrdenTrabajosCertificaciones'
//                                                            ],
//                           'OrdenTrabajosInsumos' => ['Productos',
//                                                        'Unidades',
//                                                        'Almacenes']
//                        ])
//        ]);
//
//        //SE CALCULA EL MONTO TOTAL CERTIFICADO
//        $distribuciones = $ordenTrabajos->orden_trabajos_distribuciones;
//        foreach ($distribuciones as $distribucion){
//            $monto = 0; $has = 0;
//            foreach ($distribucion->orden_trabajos_certificaciones as $certificacion){
//                $monto += $certificacion->has * $certificacion->precio_final;
//                $has += $certificacion->has;
//            }
//            $distribucion->total_certificado = $monto;
//            $distribucion->hascertificadas = $has;
//            
//            $distribucion->importe_certificado = 0;
//            if ($has !== 0){
//                $certificado = $monto / $has;
//                $distribucion->importe_certificado = round( $certificado, 2 );
//            }
//            $has_certificadas += $has;
//            
//            foreach ($ordenTrabajos->orden_trabajos_insumos as $insumo) {
//                if ($distribucion->id == $insumo->orden_trabajos_distribucione_id) {
//                    $entregado = 0;
//                    $entregado = $this->OrdenTrabajosInsumos->find('entregas', ['IdInsumos' => $insumo->id])->entregas;
//
//                    $devolucion = 0;
//                    $devolucion = $this->OrdenTrabajosInsumos->find('devoluciones', ['IdInsumos' => $insumo->id])->devoluciones;
//
//                    $insumo->utilizado = round($entregado - $devolucion, 3); /* Este valor es el utilizado real */
//                    $insumo->entrega = round($entregado, 3);
//                    $insumo->devolucion = round($devolucion, 3);
//
//                    $insumo->dosis_aplicada = 0;
//                    if($has != 0){
//                        $insumo->dosis_aplicada = round($insumo->utilizado / $has, 3);
//                    }
//                }
//            }
//            
//        }
//        
//        $this->set(compact(['ordenTrabajos']));
//        $this->set('_serialize', ['ordenTrabajos']);
//        
//        $this->RequestHandler->renderAs($this,'json');
//    }
   
    /* Elimino la linea de distribucion   */
    public function eliminarordentrabajo() {
        /* Obtengo los datos que pasamos como parametros */
        $data = $this->request->data;

        $id = $data['id'];

        if (!empty($id)) {
            /* Estoy editando, así que obtengo el objeto */
            $dist = $this->OrdenTrabajos->OrdenTrabajosDistribuciones->get($id, [
                'contain' => ['OrdenTrabajosInsumos', 'OrdenTrabajosCertificaciones']
            ]);
            
            /* Este objeto es el que me permite eliminar la distribucion */
            $distribucion = $this->OrdenTrabajos->OrdenTrabajosDistribuciones->get($id);
            
            /* Reviso si tiene linea de insumos */
            if (!empty($dist->orden_trabajos_insumos)) {
                $data = [
                    'status' => 'error',
                    'message' => 'La labor tiene insumos asignados.'
                ];
            }
            /* Reviso si tiene alguna linea certificada */
            if (!empty($dist->orden_trabajos_certificaciones)) {
                $data = [
                    'status' => 'error',
                    'message' => 'La labor tiene certificaciones realizadas.'
                ];
            }

            /* Si no tiene ninguno de ellos, lo elimino */
            if (empty($dist->orden_trabajos_insumos) && empty($dist->orden_trabajos_certificaciones)) {
                if ($result = $this->OrdenTrabajos->OrdenTrabajosDistribuciones->delete($distribucion)) {
                    $data = [
                        'status' => 'success',
                        'message' => 'Se eliminó la labor correctamente.'
                    ];
                }
            }
        }
        $this->set('data', $data);
    }

    /* Guardo todos los insumos que se utilizaran en la OT
     * 
     * Recibo los datos del Modal y varios mas.
     * A guardar en Orden_trabajos_insumos
     * 
     */

    public function guardarinsumos() {
        /* Obtengo los datos que pasamos como parametros */
        $data = $this->request->data;
        
        if (!empty($data['id'])) {
            /* Estoy editando, así que obtengo el objeto */
            $insumos = $this->OrdenTrabajosInsumos->get($data['id']);
        } else {
            /* Creo una entidad */
            $insumos = $this->OrdenTrabajosInsumos->newEntity();
        }
        $insumos->orden_trabajo_id = $data['orden_trabajo_id'];
        $insumos->producto_id = $data['producto'];
        $insumos->productos_lote_id = $data['lote'] ? $data['lote'] : '';
        $insumos->orden_trabajos_distribucione_id = $data['orden_trabajos_distribucione_id'];
        $insumos->dosis = $data['dosis'];
        $insumos->cantidad = $data['cantidad'];
        $insumos->unidade_id = $data['unidad'];
        $insumos->cantidad_stock = 1;
        $insumos->almacene_id = $data['almacen'];
        /* Guardo la entidad */
        if ($this->OrdenTrabajosInsumos->save($insumos)) {
            /* Ya tengo guardado el ID generado */
            $registro = $this->OrdenTrabajosInsumos->get($insumos->id, [
                'contain' => ['Productos', 'Unidades', 'Almacenes', 'ProductosLotes']
            ]);
        } else {
            die(debug( $insumos ));
        }
        
        
        $this->set(compact('registro'));
        $this->set('_serialize', 'registro');
        
        $this->RequestHandler->renderAs($this, 'json');
    }

    public function drop() {
        $data = $this->request->data;
        if ($this->request->is(array('post', 'put'))) {
            if (!empty($_FILES)) {

                $fileName = $_FILES['file']['name']; //Get the image
                $file_full = $this->crear_ruta($data, $fileName);     //Image storage path

                $file = basename($fileName);
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                $file_temp_name = $_FILES['file']['tmp_name'];
                $new_file_name = 'OT-' . $data['ot'] . '-' . $data['distribucion'] . '-' . time() . '.' . $ext;

                if (move_uploaded_file($file_temp_name, $file_full . $new_file_name)) {
                    /* Ya esta subido el archivo, lo guardo en la tabla */
                    $this->loadModel('OrdenTrabajosCertificacionesImagenes');
                    $archivo = $this->OrdenTrabajosCertificacionesImagenes->newEntity();

                    $exif = $this->leer_exif($file_full . $new_file_name);

                    $archivo->orden_trabajo_id = $data['ot'];
                    $archivo->nombre = $new_file_name;
                    $archivo->descripcion = 'Certificacion OT - ' . $data['ot'];
                    $archivo->ruta = $file_full;
                    if (is_array($exif)) {
                        $archivo->latitud = $exif['latitud'];
                        $archivo->longitud = $exif['longitud'];
                    }
//                    $archivo->fecha = time();
                    $archivo->user_id = $this->request->session()->read('Auth.User.id');

//                    die(debug($archivo));
                    if ($this->OrdenTrabajosCertificacionesImagenes->save($archivo)) {
                        echo "File Uploaded successfully";
                        die;
                    }
                } else {
                    echo "Error";
                    die;
                }
            }
        }
    }

    /* Obtengo el ID de OT y la linea de la distribucion, asi se como guardarlos.
     * El nombre del archivo es:
     * OT-id OT-id distribucion-count(imagenes certificaciones) + 1
     * 
     */

    function crear_ruta() {
        /* Verifico en primer medida que el directorio este creado */
        $file_full = WWW_ROOT . 'OrdenTrabajosCertificaciones/';     //Image storage path
        // Si no encuentro la carpeta, la creo
        if (!file_exists($file_full)) {
            mkdir($file_full, 0755, true);
        }
        return $file_full;
    }

    /* Todas las OTs que están certificadas aun no generados para dataload */

    public function dataload() {
        $ordenTrabajos = $this->OrdenTrabajos->find('all', [
            'contain' => ['Establecimientos', 'Users', 'Proveedores', 'OrdenTrabajosEstados', 'OrdenTrabajosDistribuciones' => 'Lotes'],
            'conditions' => ['OrdenTrabajos.orden_trabajos_estado_id ' => 4]
        ]);

        $this->set(compact('ordenTrabajos'));
    }

    /* Genero el dataload */

    public function generardataload() {
        $data = $this->request->data;

        $ordenes = $data['valores'];

        if (is_array($ordenes)) {
            $ordenTrabajos = $this->OrdenTrabajos->find('all', [
                'contain' => ['Establecimientos', 'Users', 'Proveedores', 'OrdenTrabajosEstados', 'OrdenTrabajosDistribuciones' => ['ProyectosLabores', 'Unidades', 'Lotes', 'Proyectos'],
                    'OrdenTrabajosCertificaciones' => 'Users'
                ],
                'conditions' => ['OrdenTrabajos.id IN' => $ordenes]
            ]);

            /* Ya tengo los datos, ahora a escribir el excel */
            $this->set(compact('ordenTrabajos'));
        }







//        if (is_array($this->request->session()->read('Auth.User.establecimientos'))){
//            $establecimientos = $this->OrdenTrabajos->Establecimientos->find('list', [
//                'conditions' => ['Establecimientos.id IN' => $this->request->session()->read('Auth.User.establecimientos')]]);
//        } else {
//            $establecimientos = $this->OrdenTrabajos->Establecimientos->find('list');
//        }        
    }

    /* Leo los datos exif del archivo y devuelvo sus coordenadas GPS */

    function leer_exif($file) {
        //$file = "/var/www/html/milfs/images/gps.jpg";
        $exif = exif_read_data($file);
        if (!empty($exif['GPSLongitude']) && !empty($exif['GPSLatitude'])) {
            $d = (double) $exif['GPSLongitude'][0];
            $m = exif_float($exif['GPSLongitude'][1]);
            $s = exif_float($exif['GPSLongitude'][2]);
            $gps_longitude = (double) $d + $m / 60 + $s / 3600;
            if ($exif['GPSLongitudeRef'] == 'W') {
                $gps_longitude = -$gps_longitude;
            }
            $d = $exif['GPSLatitude'][0];
            $m = exif_float($exif['GPSLatitude'][1]);
            $s = exif_float($exif['GPSLatitude'][2]);
            $gps_latitude = (double) $d + $m / 60 + $s / 3600;
            if ($exif['GPSLatitudeRef'] == 'S') {
                $gps_latitude = -$gps_latitude;
            }
            if ($gps_latitude != '') {
                //$resultado = "{$_SESSION['url']}mapero.php?lon={$gps_latitude}&lat={$gps_longitude}&zoom=18";
                $resultado['longitud'] = $gps_longitude;
                $resultado['latitud'] = $gps_latitude;
            } else {
                $resultado = "";
            }
            return $resultado;
        }
    }

    /*
     * Busco todos los usuarios que compartan lotes
     * 
     * Devuelvo un array
     */

    function NotificarA($user_id) {
        /* Lista de Usuarios a notificar */
        $usuarios = [];
        array_push( $usuarios, $user_id );
        
        /* Cargo la tabla Tecnica Responsables para averiguar a quien tengo que
         * enviar las notificaciones. En principio, a todos los que esten asignados
         * a los mismos lotes que el que genera el mensaje
         */
        $this->loadModel('Tecnicaresponsables');
        $this->loadModel('Lotes');

        /* Busco los sectores asignados al usuario actual                     */
        $mislotes = $this->Tecnicaresponsables->find('all')
                ->where(['Tecnicaresponsables.user_id ' => $user_id]);
        /* Tengo los sectores asociados al usuario actual, ahora busco todos los
         * usuarios asociados a estos lotes                                   */
        foreach ($mislotes as $lote) {
            $misusuarios = $this->Tecnicaresponsables->find('all', [
                'conditions' => ['lote_id' => $lote->lote_id, 'user_id !=' => $lote->user_id]
            ]);
            foreach ($misusuarios as $usuario) {
                /* Si el usuario aun no esta en el array y no es el mismo que genera, lo agrego */
                if (!in_array($usuario->user_id, $usuarios)) {
                    array_push($usuarios, $usuario->user_id);
                }
            }
        }

        return $usuarios;
    }

    function ListaLotes($user_id) {
        $lotes = [];
        array_push($lotes, 0);
        /* Cargo la tabla Tecnica Responsables para averiguar a quien tengo que
         * enviar las notificaciones. En principio, a todos los que esten asignados
         * a los mismos lotes que el que genera el mensaje
         */
        $this->loadModel('Tecnicaresponsables');

        /* Busco los sectores asignados al usuario actual                     */
        $mislotes = $this->Tecnicaresponsables->find('all')
                ->where(['Tecnicaresponsables.user_id ' => $user_id]);
        foreach ($mislotes as $lote) {
            if (!in_array($lote->lote_id, $lotes)) {
                array_push($lotes, $lote->lote_id);
            }
        }
        return $lotes;
    }
    
    /*
     * Generar Excel OT
     * 
     * Devuelve un listado completo de las OT generadas en el sistema.
     * Para analizarlo fuera del sistema.
     * 
     */
//    public function generarexcelot() 
//    {
//        
//    }
    
    
    public function generarexcelot() {
        
        /* Arreglo temporal al error de memoria al generar un excel tan grande */
        ini_set('memory_limit', '-1');
        set_time_limit(900);
        
        $filtros = $this->request->getData();
        
//        die(debug( $filtros));
        
        $desde = $filtros['desde'] ? $filtros['desde'] : '';
        $hasta = $filtros['hasta'] ? $filtros['hasta'] : '';
        
        $id = $this->request->session()->read('Auth.User.id');
         
        switch ($this->request->session()->read('Auth.User.group_id')) {
            case 1: /* Los Developers ven todo */
                //$establecimientos = $this->request->session()->read('Auth.User.establecimientos');
                $ordenTrabajos = $this->OrdenTrabajos->find('all', [
                    'contain' => [
                                    'Establecimientos' => ['fields' => ['id','nombre', 'organizacion']],
                                    'OrdenTrabajosDataloads' => ['Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']]],
                                    'Proveedores' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']],
                                    'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']],
                                    'OrdenTrabajosEstados' => ['fields' => ['id', 'nombre']],
                                    'OrdenTrabajosDistribuciones' => ['Lotes' => ['fields' => ['id', 'nombre'], 'Sectores'],
                                                                      'ProyectosLabores' => ['fields' => ['id', 'nombre', 'proyecto_id','expenditure_type']],
                                                                      'Proyectos' => ['fields' => ['id', 'segmento', 'cultivo']],
                                                                      'Monedas' => ['fields' => ['id', 'simbolo']],
                                                                      'Unidades' => ['fields' => ['id', 'nombre']],
                                                                      'OrdenTrabajosCertificaciones'
                                                                    ],
                                    'OrdenTrabajosOracles' => ['fields' => ['id', 'lote', 'orden_trabajo_id', 'oc', 'fecha_oc', 'status','tc', 'cantidad', 'precio', 'labor'],
                                                               ],
                                    'OrdenTrabajosCondicionesMeteorologicas'
                                ]
                ]);
               
                /* Aplico los filtros de Fecha y Establecimientos */
                if ($desde) {
                    $ordenTrabajos->where(['OrdenTrabajos.fecha >=' => $desde]);
                }
                if ($hasta) {
                    $ordenTrabajos->where(['OrdenTrabajos.fecha <=' => $hasta]);
                }
                
                if ($filtros['establecimientos']) {
                    $establecimientos = explode(',', str_replace(' ', '', $filtros['establecimientos']));
                    $ordenTrabajos->where(['OrdenTrabajos.establecimiento_id IN' => $establecimientos]);
                }   
                
                if ($filtros['proveedores']) {
                    $proveedores = explode(',', str_replace(' ', '', $filtros['proveedores']));
                    $ordenTrabajos->where(['OrdenTrabajos.proveedore_id IN' => $proveedores]);
                }   
                           
                break;
            case 3: /* Administrativos - Ven OT's de los establecimientos */
                $ordenTrabajos = $this->OrdenTrabajos->find('all', [
                    'contain' => [
                                    'Establecimientos' => ['fields' => ['id','nombre', 'organizacion']],
                                    'OrdenTrabajosDataloads' => ['Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']]],
                                    'Proveedores' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']],
                                    'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']],
                                    'OrdenTrabajosEstados' => ['fields' => ['id', 'nombre']],
                                    'OrdenTrabajosDistribuciones' => ['Lotes' => ['fields' => ['id', 'nombre'], 'Sectores'],
                                                                      'ProyectosLabores' => ['fields' => ['id', 'nombre', 'proyecto_id','expenditure_type']],
                                                                      'Proyectos' => ['fields' => ['id', 'segmento', 'cultivo']],
                                                                      'Monedas' => ['fields' => ['id', 'simbolo']],
                                                                      'Unidades' => ['fields' => ['id', 'nombre']],
                                                                      'OrdenTrabajosCertificaciones' /* => ['Users' => ['finder' =>['all' => ['withDeleted']], 'fields' => ['id', 'nombre']]] */
                                                                      ],
                                    'OrdenTrabajosOracles' => ['fields' => ['id', 'lote', 'orden_trabajo_id', 'oc', 'fecha_oc', 'status','tc', 'cantidad', 'precio', 'labor']]
                                                               # 'conditions' => ['aprobado' => 'Y']]
                                ],
                    'conditions' => ['Establecimientos.id IN' => $this->request->session()->read('Auth.User.establecimientos')]
                ]);
                
                /* Aplico los filtros de Fecha y Establecimientos */
                if ($desde) {
                    $ordenTrabajos->where(['OrdenTrabajos.fecha >=' => $desde]);
                }
                if ($hasta) {
                    $ordenTrabajos->where(['OrdenTrabajos.fecha <=' => $hasta]);
                }
                if ($filtros['establecimientos']) {
                    $establecimientos = explode(',', str_replace(' ', '', $filtros['establecimientos']));
                    $ordenTrabajos->where(['OrdenTrabajos.establecimiento_id IN' => $establecimientos]);
                } 
                if ($filtros['proveedores']) {
                    $proveedores = explode(',', str_replace(' ', '', $filtros['proveedores']));
                    $ordenTrabajos->where(['OrdenTrabajos.proveedore_id IN' => $proveedores]);
                }   
                
                break;
            case 6: /* Auditores */
                $ordenTrabajos = $this->OrdenTrabajos->find('all', [
                    'contain' => [
                                    'Establecimientos' => ['fields' => ['id','nombre', 'organizacion']],
                                    'OrdenTrabajosDataloads' => ['Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']]],
                                    'Proveedores' => ['fields' => ['id', 'nombre']],
                                    'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']],
                                    'OrdenTrabajosEstados' => ['fields' => ['id', 'nombre']],
                                    'OrdenTrabajosDistribuciones' => ['Lotes' => ['fields' => ['id', 'nombre'], 'Sectores'],
                                                                      'ProyectosLabores' => ['fields' => ['id', 'nombre', 'proyecto_id','expenditure_type']],
                                                                      'Proyectos' => ['fields' => ['id', 'segmento', 'cultivo']],
                                                                      'Monedas' => ['fields' => ['id', 'simbolo']],
                                                                      'Unidades' => ['fields' => ['id', 'nombre']],
                                                                      'OrdenTrabajosCertificaciones'
                                                                    ],
                                    'OrdenTrabajosOracles' => ['fields' => ['id', 'lote', 'orden_trabajo_id', 'oc', 'fecha_oc', 'status','tc', 'cantidad', 'precio', 'labor'],
                                                               ],
                                                               //'conditions' => ['aprobado' => 'Y']],
                                    'OrdenTrabajosCondicionesMeteorologicas'
                                ]

                ]);
                break;
            default: /* Aqui deberian llegar los ingenieros */
                $rol = $this->request->session()->read('Auth.User.role_id');
                if ($rol == 3 || $rol == 10) {
                    /* Obtengo los usuarios del sector */
                    $establecimientos = $this->request->session()->read('Auth.User.establecimientos');
                    $ordenTrabajos = $this->OrdenTrabajos->find('all', [
                        'contain' => [
                                        'Establecimientos' => ['fields' => ['id','nombre', 'organizacion']],
                                        'OrdenTrabajosDataloads' => ['Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']]],
                                        'Proveedores' => ['fields' => ['id', 'nombre']],
                                        'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']],
                                        'OrdenTrabajosEstados' => ['fields' => ['id', 'nombre']],
                                        'OrdenTrabajosDistribuciones' => ['Lotes' => ['fields' => ['id', 'nombre'], 'Sectores'],
                                                                          'ProyectosLabores' => ['fields' => ['id', 'nombre', 'proyecto_id','expenditure_type']],
                                                                          'Proyectos' => ['fields' => ['id', 'segmento', 'cultivo']],
                                                                          'Monedas' => ['fields' => ['id', 'simbolo']],
                                                                          'Unidades' => ['fields' => ['id', 'nombre']],
                                                                          'OrdenTrabajosCertificaciones' /* => ['Users' => ['finder' =>['all' => ['withDeleted']], 'fields' => ['id', 'nombre']]] */
                                                                          ],
                                        'OrdenTrabajosOracles' => ['fields' => ['id', 'lote', 'orden_trabajo_id', 'oc', 'fecha_oc', 'status','tc', 'cantidad', 'precio', 'labor']],
                                                                   # 'conditions' => ['aprobado' => 'Y']],
                                        'OrdenTrabajosCondicionesMeteorologicas'
                                    ],
                        'conditions' => ['Establecimientos.id IN' => $establecimientos]
                    ]);
                    /* Aplico los filtros de Fecha y Establecimientos */
                    if ($desde) {
                        $ordenTrabajos->where(['OrdenTrabajos.fecha >=' => $desde]);
                    }
                    if ($hasta) {
                        $ordenTrabajos->where(['OrdenTrabajos.fecha <=' => $hasta]);
                    }
                    if ($filtros['establecimientos']) {
                        $establecimientos = explode(',', str_replace(' ', '', $filtros['establecimientos']));
                        $ordenTrabajos->where(['OrdenTrabajos.establecimiento_id IN' => $establecimientos]);
                    } 
                    if ($filtros['proveedores']) {
                    $proveedores = explode(',', str_replace(' ', '', $filtros['proveedores']));
                    $ordenTrabajos->where(['OrdenTrabajos.proveedore_id IN' => $proveedores]);
                }   
 
                    break;
                } else {
                    /* Obtengo los usuarios del sector */
                    $usuarios = $this->NotificarA($id);
                    $ordenTrabajos = $this->OrdenTrabajos->find('all', [
                        'contain' => [
                                        'Establecimientos' => ['fields' => ['id','nombre', 'organizacion']],
                                        'OrdenTrabajosDataloads' => ['Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']]],
                                        'Proveedores' => ['fields' => ['id', 'nombre']],
                                        'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']],
                                        'OrdenTrabajosEstados' => ['fields' => ['id', 'nombre']],
                                        'OrdenTrabajosDistribuciones' => ['Lotes' => ['fields' => ['id', 'nombre'], 'Sectores'],
                                                                          'ProyectosLabores' => ['fields' => ['id', 'nombre', 'proyecto_id','expenditure_type']],
                                                                          'Proyectos' => ['fields' => ['id', 'segmento', 'cultivo']],
                                                                          'Monedas' => ['fields' => ['id', 'simbolo']],
                                                                          'Unidades' => ['fields' => ['id', 'nombre']],
                                                                          'OrdenTrabajosCertificaciones' => ['Users' => ['finder' =>['all' => ['withDeleted']], 'fields' => ['id', 'nombre']]]
                                                                          ],
                                        'OrdenTrabajosOracles' => ['fields' => ['id', 'lote', 'orden_trabajo_id', 'oc', 'fecha_oc', 'status','tc', 'cantidad', 'precio', 'labor']],
                                                                   # 'conditions' => ['aprobado' => 'Y']],
                                        'OrdenTrabajosCondicionesMeteorologicas'
                                    ],
                        'conditions' => ['Users.id IN' => $usuarios]
                    ]);
                    /* Aplico los filtros de Fecha y Establecimientos */
                    if ($desde) {
                        $ordenTrabajos->where(['OrdenTrabajos.fecha >=' => $desde]);
                    }
                    if ($hasta) {
                        $ordenTrabajos->where(['OrdenTrabajos.fecha <=' => $hasta]);
                    }
                    if ($filtros['establecimientos']) {
                        $establecimientos = explode(',', str_replace(' ', '', $filtros['establecimientos']));
                        $ordenTrabajos->where(['OrdenTrabajos.establecimiento_id IN' => $establecimientos]);
                    }
                    if ($filtros['proveedores']) {
                        $proveedores = explode(',', str_replace(' ', '', $filtros['proveedores']));
                        $ordenTrabajos->where(['OrdenTrabajos.proveedore_id IN' => $proveedores]);
                    }   
                    break;
                }
        }
        
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Ordenes de Trabajos');

        $writer = new Xlsx($spreadsheet);        
        
        /* Creamos el encabezado y le damos estilos */
        $sheet->mergeCells('A1:A3'); /* Inicio Logo  */
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('El Agronomo');
        $drawing->setPath(WWW_ROOT . 'img' . DS . 'logo_elagronomo_print.png');
        $drawing->setCoordinates('A1');
        $drawing->setHeight(58);
        $drawing->setWorksheet($spreadsheet->getActiveSheet());

        /* Ahora el nombre del sistema */
        $sheet->mergeCells('B1:D3');
        $sheet->setCellValue('B1', 'El Agronomo');
        $styleArray = [
            'font' => [
                'size' => 36
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            ]
        ];
        $sheet->getStyle('B1:D3')->applyFromArray($styleArray);

        /* Ahora pongo todo el encabezado en fondo blanco */
        $styleArray = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FFFFFF',
                ],
            ]
        ];
        $sheet->getStyle('A1:AK3')->applyFromArray($styleArray);
        
        /* Ordenado */
        $sheet->mergeCells('K3:L3');
        $sheet->setCellValue('K3', 'Ordenado');
        /* Certificado */
        $sheet->mergeCells('N3:O3');
        $sheet->setCellValue('N3', 'Certificado');
        /* Valorizado */
        $sheet->mergeCells('P3:Q3');
        $sheet->setCellValue('P3', 'Valorizado');
        /* Oracle */
        $sheet->mergeCells('V3:AC3');
        $sheet->setCellValue('V3', 'Oracle');
        /* Condiciones Meteorologicas */
        $sheet->mergeCells('AG3:AK3');
        $sheet->setCellValue('AG3', 'Condiciones Meteorologicas');
        
        $styleArray = [
            'font' => [
                'size' => 11,
                'color' => [
                    'startColor' => [
                        'argb' => '94995F',
                    ]
                ]
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ]
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'D8E4BC',
                ],
            ]
        ];
        $sheet->getStyle('K3:L3')->applyFromArray($styleArray);
        $sheet->getStyle('N3:R3')->applyFromArray($styleArray);
        $sheet->getStyle('V3:AC3')->applyFromArray($styleArray);
        $sheet->getStyle('AG3:AK3')->applyFromArray($styleArray);
        
        /* Escribo los encabezados */
        $sheet->setCellValue('A4', 'Nº OT');
        $sheet->setCellValue('B4', 'Proveedor');
        $sheet->setCellValue('C4', 'ORG');
        $sheet->setCellValue('D4', 'Establecimiento');
        $sheet->setCellValue('E4', 'Proyecto');
        $sheet->setCellValue('F4', 'Cultivo');
        $sheet->setCellValue('G4', 'Labor');
        $sheet->setCellValue('H4', 'Lote');
        $sheet->setCellValue('I4', 'Sector');
        $sheet->setCellValue('J4', 'UM');
        $sheet->setCellValue('K4', 'Fecha'); /* Ordenado */
        $sheet->setCellValue('L4', 'Superficie');  /* Ordenado */
        $sheet->setCellValue('M4', 'Aplicado');  /* Certificado */
        $sheet->setCellValue('N4', 'Fecha');  /* Certificado */
        $sheet->setCellValue('O4', 'Superficie'); /* Certificado */
        $sheet->setCellValue('P4', 'Moneda'); /* Valorizado */
        $sheet->setCellValue('Q4', 'Tarifa');
        $sheet->setCellValue('R4', 'Importe');/* Fin del Valorizado */
        $sheet->setCellValue('S4', 'Creado por');
        $sheet->setCellValue('T4', 'Aprobado por');
        $sheet->setCellValue('U4', 'Estado');
        $sheet->setCellValue('V4', 'OC'); /* Oracle */
        $sheet->setCellValue('W4', 'Fecha');
        $sheet->setCellValue('X4', 'TC');
        $sheet->setCellValue('Y4', 'Status');
        $sheet->setCellValue('Z4', 'Cant.');
        $sheet->setCellValue('AA4', 'Precio');
        $sheet->setCellValue('AB4', 'Total');
        $sheet->setCellValue('AC4', 'Lote'); /* Oracle */
        $sheet->setCellValue('AD4', 'Dataload');
        $sheet->setCellValue('AE4', 'Generado por');
        $sheet->setCellValue('AF4', 'Observaciones');
        $sheet->setCellValue('AG4', 'Fecha'); /* Condiciones Meteorologicas */
        $sheet->setCellValue('AH4', 'Temp.'); /* Condiciones Meteorologicas */
        $sheet->setCellValue('AI4', 'Humedad'); /* Condiciones Meteorologicas */
        $sheet->setCellValue('AJ4', 'Viento'); /* Condiciones Meteorologicas */
        $sheet->setCellValue('AK4', 'Direccion'); /* Condiciones Meteorologicas */
        
        /* Le agrego estilos al color del encabezado de las columnas */
        $styleArray = [
            'font' => [
                'size' => 11,
                'color' => [
                    'startColor' => [
                        'argb' => '94995F',
                    ]
                ]
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ]
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'D8E4BC',
                ],
            ]
        ];
        $sheet->getStyle('A4:AK4')->applyFromArray($styleArray);
        
        $styleError = [
            'font' => [
                'size' => 11,
                'color' => [
                    'startColor' => [
                        'argb' => '676a6c',
                    ]
                ]
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ]
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FFAAAA',
                ],
            ]
        ];
        
        /* Pongo la Fecha en que fue generada */
        $styleArray = [
            'font' => ['size' => 8],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]
        ];        
        $spreadsheet->getActiveSheet()->getStyle('AK1:AK2')->applyFromArray($styleArray);
        $now = Time::now();
        $fecha ='Generado el '. $now->i18nFormat('dd/MM/yyyy HH:mm');
        $sheet->setCellValue('AK1', $fecha);
        
        /* Ahora pongo el usuario */
        $generado = 'Generado por ' . $this->request->session()->read('Auth.User.nombre');
        $sheet->setCellValue('AK2', $generado);
        
        $linea = 5;
        $matched = false;
        
        foreach ($ordenTrabajos as $ordenTrabajo) {
            $distribuciones = $ordenTrabajo->orden_trabajos_distribuciones;
            $oracles = $ordenTrabajo->orden_trabajos_oracles;
            
            /* Si no hay distribuciones, no se crea ninguna linea, asi que genero
             * una linea con los datos de la OT al menos */
            if (!$distribuciones) {
                $sheet->setCellValueByColumnAndRow(1, $linea, $ordenTrabajo->id);
                $sheet->setCellValueByColumnAndRow(2, $linea, $ordenTrabajo->proveedore['nombre']);
                $sheet->setCellValueByColumnAndRow(3, $linea, $ordenTrabajo->establecimiento['organizacion']);
                $sheet->setCellValueByColumnAndRow(4, $linea, $ordenTrabajo->establecimiento['nombre']);
                /* Ahora agrego las cantidades ordenadas */
                if ( $ordenTrabajo['fecha'] ) {
                    $sheet->setCellValueByColumnAndRow(11, $linea, Date::PHPToExcel($ordenTrabajo['fecha']));
                    $sheet->getStyleByColumnAndRow(11, $linea)->getNumberFormat()->setFormatCode('dd/MM/yyyy');
                }
                if (!empty($ordenTrabajo->user)){
                    $sheet->setCellValueByColumnAndRow(19, $linea, $ordenTrabajo->user['nombre']);                    
                }
                $sheet->setCellValueByColumnAndRow(21, $linea, $ordenTrabajo->orden_trabajos_estado['nombre']);
                $sheet->setCellValueByColumnAndRow(31, $linea, h($ordenTrabajo->observaciones));
                $linea++;                
            }
            
            /* Si hay algo, entro a las distribuciones */
            foreach ($distribuciones as $distribucion) {
                $certificador = $this->OrdenTrabajosDistribuciones->find('Certificador', ['IdDistribucion' => $distribucion->id]);
                
                $sheet->setCellValueByColumnAndRow(1, $linea, $ordenTrabajo->id);
                $sheet->setCellValueByColumnAndRow(2, $linea, $ordenTrabajo->proveedore['nombre']);
                $sheet->setCellValueByColumnAndRow(3, $linea, $ordenTrabajo->establecimiento['organizacion']);
                $sheet->setCellValueByColumnAndRow(4, $linea, $ordenTrabajo->establecimiento['nombre']);
                $sheet->setCellValueByColumnAndRow(5, $linea, $distribucion->proyecto['segmento']);
                $sheet->setCellValueByColumnAndRow(6, $linea, $distribucion->proyecto['cultivo']);
                $sheet->setCellValueByColumnAndRow(7, $linea, $distribucion->proyectos_labore['nombre']);
                $sheet->setCellValueByColumnAndRow(8, $linea, $distribucion->lote['nombre']);
                if ($distribucion->lote->sectore) {
                    if ($distribucion->lote->sectore->direccion) {
                        $sheet->setCellValueByColumnAndRow(9, $linea, $distribucion->lote->sectore['direccion']);
                    } else {
                        $sheet->setCellValueByColumnAndRow(9, $linea, $distribucion->lote->sectore['nombre']);
                    }
                } else {
                    $sheet->setCellValueByColumnAndRow(9, $linea, '');
                }
                $sheet->setCellValueByColumnAndRow(10, $linea, $distribucion->unidade['nombre']);
                /* Fecha de la OT, en format fecha */
                $sheet->setCellValueByColumnAndRow(11, $linea, Date::PHPToExcel($ordenTrabajo['fecha']));
                $sheet->getStyleByColumnAndRow(11, $linea)->getNumberFormat()->setFormatCode('dd/MM/yyyy');
                
                $sheet->setCellValueByColumnAndRow(12, $linea, $distribucion['superficie']); /* Ordenadas */
                /* Ahora agrego las cantidades certificadas */
                $precio = 0;
                /* Fecha Aplicacion - Iria en caso de que esté certificada */
                $certificadas = 0;
                if (count($distribucion->orden_trabajos_certificaciones) > 0) {
                    $certificaciones = $distribucion->orden_trabajos_certificaciones;
                    foreach ($certificaciones as $certificacion) {
                        $certificadas += $certificacion['has'];
                        $fecha = $certificacion['fecha_final'];
                        $precio = $certificacion['precio_final'];
                        $fecha_real_certificacion = $certificacion->created;
                    }

                     /* Fecha real de aplicacion */
                    $sheet->setCellValueByColumnAndRow(13, $linea, Date::PHPToExcel($fecha));
                    $sheet->getStyleByColumnAndRow(13, $linea)->getNumberFormat()->setFormatCode('dd/MM/yyyy');
                    
                    /* Pongo las cantidades certificadas y la ultima fecha de certificacion */
                    $sheet->setCellValueByColumnAndRow(14, $linea,  Date::PHPToExcel($fecha_real_certificacion)); /* Fecha real de aplicacion */
                    $sheet->getStyleByColumnAndRow(14, $linea)->getNumberFormat()->setFormatCode('dd/MM/yyyy');
                    
                    $sheet->setCellValueByColumnAndRow(15, $linea, $certificadas); /* Certificadas */
                }
                /* Valorizado */
                $sheet->setCellValueByColumnAndRow(16, $linea, $distribucion->moneda->simbolo);
                $sheet->setCellValueByColumnAndRow(17, $linea, $precio);
                
                $total = round($certificadas * $precio, 2);
                
                $sheet->setCellValueByColumnAndRow(18, $linea, $total);
                
                if (!empty($ordenTrabajo->user)){
                    $sheet->setCellValueByColumnAndRow(19, $linea, $ordenTrabajo->user['nombre']);                    
                }
                if (!empty($certificador)){
                    $sheet->setCellValueByColumnAndRow(20, $linea, $certificador->user->nombre);
                }
                $sheet->setCellValueByColumnAndRow(21, $linea, $ordenTrabajo->orden_trabajos_estado['nombre']);
                
                if ( $oracles ) {
                    if (sizeof($oracles) == 1 ) {
                        foreach ($oracles as $k => $oracle) {
                            /* Solo las OC aprobadas */
                            $matched = true;
                            /* Si hay un solo oracle, significa que es una sola linea */
                            $sheet->setCellValueByColumnAndRow(22, $linea, $oracle->oc);
                            $sheet->setCellValueByColumnAndRow(23, $linea, $oracle->fecha_oc->i18nFormat('dd/MM/yyyy'));
                            $sheet->setCellValueByColumnAndRow(24, $linea, $oracle->tc);
                            $sheet->setCellValueByColumnAndRow(25, $linea, $oracle->status);
                            $sheet->setCellValueByColumnAndRow(26, $linea, $oracle->cantidad);
                            $sheet->setCellValueByColumnAndRow(27, $linea, $oracle->precio);
                            $sheet->setCellValueByColumnAndRow(28, $linea, $oracle->total);

                            $sheet->setCellValueByColumnAndRow(29, $linea, $oracle->lote);
                            if ( $total != $oracle->total ) {
                                /* Hay una diferencia entre el total certificado y el total en oracle */
                                /* Marco la celda con un color distinto */
                                $spreadsheet->getActiveSheet()->getStyleByColumnAndRow(18, $linea)->applyFromArray($styleError); 
                                $spreadsheet->getActiveSheet()->getStyleByColumnAndRow(28, $linea)->applyFromArray($styleError);
                            }
                            if ( $certificadas != $oracle->cantidad ) { /* Hay diferencias en las cantidades */
                                $spreadsheet->getActiveSheet()->getStyleByColumnAndRow(15, $linea)->applyFromArray($styleError);
                                $spreadsheet->getActiveSheet()->getStyleByColumnAndRow(26, $linea)->applyFromArray($styleError);
                            }
                            $oracle->estado = 'MATCHED';
                            $oracles[$k] = $oracle;
                        }
                    } else {
                        /* Tiene varias lineas, asi que tengo que matchear las lineas de oracle, con las lineas de la OT */
                        foreach ($oracles as $k => $oracle)  {
                            /* Verifico si tiene Lote - Labor - Superficie */
                            if (strtoupper($oracle->lote) == strtoupper($distribucion->lote['nombre']) && $oracle->cantidad == $certificadas && strtoupper($oracle->labor) == strtoupper($distribucion->proyectos_labore['expenditure_type']) && $oracle->precio == $precio ){
                                $sheet->setCellValueByColumnAndRow(22, $linea, $oracle->oc);
                                $sheet->setCellValueByColumnAndRow(23, $linea, $oracle->fecha_oc->i18nFormat('dd/MM/yyyy'));
                                $sheet->setCellValueByColumnAndRow(24, $linea, $oracle->tc);
                                $sheet->setCellValueByColumnAndRow(25, $linea, $oracle->status);
                                $sheet->setCellValueByColumnAndRow(26, $linea, $oracle->cantidad);
                                $sheet->setCellValueByColumnAndRow(27, $linea, $oracle->precio);
                                $sheet->setCellValueByColumnAndRow(28, $linea, $oracle->total);
                                $sheet->setCellValueByColumnAndRow(29, $linea, $oracle->lote);
                                $matched = true;
                                $oracle->estado = 'MATCHED';
                                $oracles[$k] = $oracle;
                            }
                        }
                        /* No paso el primer emparejamiento, busco las diferencias */
                        foreach ($oracles as $k => $oracle) {
                            if ($oracle->estado !== 'MATCHED') {
                                /* Verifico si tiene Lote - Labor */
                                if ( strtoupper($oracle->labor) == strtoupper($distribucion->proyectos_labore['expenditure_type']) && $oracle->cantidad == $certificadas && $oracle->precio == $precio ) { 
                                /* if ( strtoupper($oracle->lote) == strtoupper($distribucion->lote['nombre']) && strtoupper($oracle->labor) == strtoupper($distribucion->proyectos_labore['nombre']) && $oracle->cantidad == $certificadas ) {  */
                                    $sheet->setCellValueByColumnAndRow(22, $linea, $oracle->oc);
                                    $sheet->setCellValueByColumnAndRow(23, $linea, $oracle->fecha_oc->i18nFormat('dd/MM/yyyy'));
                                    $sheet->setCellValueByColumnAndRow(24, $linea, $oracle->tc);
                                    $sheet->setCellValueByColumnAndRow(25, $linea, $oracle->status);
                                    $sheet->setCellValueByColumnAndRow(26, $linea, $oracle->cantidad);
                                    $sheet->setCellValueByColumnAndRow(27, $linea, $oracle->precio);
                                    $sheet->setCellValueByColumnAndRow(28, $linea, $oracle->total);
                                    $sheet->setCellValueByColumnAndRow(29, $linea, $oracle->lote);
                                    if ( strtoupper($oracle->lote) != strtoupper($distribucion->lote['nombre']) ) {
                                        /* Hay una diferencia entre el total certificado y el total en oracle */
                                        /* Marco la celda con un color distinto */
                                        $spreadsheet->getActiveSheet()->getStyleByColumnAndRow(8, $linea)->applyFromArray($styleError); 
                                        $spreadsheet->getActiveSheet()->getStyleByColumnAndRow(29, $linea)->applyFromArray($styleError);
                                    }
                                    $matched = true;
                                    $oracle->estado = 'MATCHED';
                                    $oracles[$k] = $oracle;
                                }                                    
                            }
                        }
                    }
                }
                
                $sheet->setCellValueByColumnAndRow(30, $linea, $ordenTrabajo->orden_trabajos_dataload_id);
                
                if ( $ordenTrabajo->orden_trabajos_dataload ) {
                    if ( $ordenTrabajo->orden_trabajos_dataload->user ) {
                        $sheet->setCellValueByColumnAndRow(31, $linea, $ordenTrabajo->orden_trabajos_dataload->user->nombre );
                    }
                }
                $sheet->setCellValueByColumnAndRow(32, $linea, h($ordenTrabajo->observaciones));
                $linea++;
            }
            
            /* ------------------------------------------------ */
            /* Verifico que se hayan colocado todos los Matched */
            /* ------------------------------------------------ */
            $total_orden = 0;
            foreach ($oracles as $k => $oracle) {
                if ($oracle->estado !== 'MATCHED') {
                    /* Verifico si tiene Lote - Labor */
                    $total_orden += $oracle->total;

                    $sheet->setCellValueByColumnAndRow(22, $linea-1, $oracle->oc);
                    $sheet->setCellValueByColumnAndRow(23, $linea-1, $oracle->fecha_oc->i18nFormat('dd/MM/yyyy'));
                    $sheet->setCellValueByColumnAndRow(24, $linea-1, $oracle->tc);
                    $sheet->setCellValueByColumnAndRow(25, $linea-1, $oracle->status);
                    $sheet->setCellValueByColumnAndRow(26, $linea-1, $oracle->cantidad);
                    $sheet->setCellValueByColumnAndRow(27, $linea-1, $oracle->precio);
                    $sheet->setCellValueByColumnAndRow(28, $linea-1, $total_orden);
                    $sheet->setCellValueByColumnAndRow(29, $linea-1, $oracle->lote);
//                    if ( $total_orden != $total ) {
//                        /* Hay una diferencia entre el total certificado y el total en oracle */
//                        $spreadsheet->getActiveSheet()->getStyleByColumnAndRow(18, $linea-1)->applyFromArray($styleError); 
//                        $spreadsheet->getActiveSheet()->getStyleByColumnAndRow(28, $linea-1)->applyFromArray($styleError);
//                    }
                    $matched = true;
                    $oracle->estado = 'MATCHED';
                    $oracles[$k] = $oracle;

                    /* Armo los comentarios */
                    $comentario = $sheet->getCommentByColumnAndRow(22, $linea-1)
                                        ->setAuthor('El Agronomo');
                    $comentario = $sheet->getCommentByColumnAndRow(22, $linea-1)
                                        ->getText()->createTextRun('OC: ');
                    $sheet->getCommentByColumnAndRow(22, $linea-1)
                        ->getText()->createTextRun($oracle->oc." - $ ".$oracle->total);
                    $sheet->getCommentByColumnAndRow(22, $linea-1)
                        ->getText()->createTextRun("\r\n");
                    $spreadsheet->getActiveSheet()->getStyleByColumnAndRow(22, $linea-1)->applyFromArray($styleError); 
                }
            }            
            /* ------------------------------------------------ */
            if ($this->request->session()->read('Auth.User.group_id') == 2 && $ordenTrabajo->orden_trabajos_condiciones_meteorologica) {
                $condicion = $ordenTrabajo->orden_trabajos_condiciones_meteorologica;
                $sheet->setCellValueByColumnAndRow(33, $linea, $condicion->fecha->i18nFormat('dd/MM/yyyy'));
                $sheet->setCellValueByColumnAndRow(34, $linea, $condicion->temperatura);
                $sheet->setCellValueByColumnAndRow(35, $linea, $condicion->humedad);
                $sheet->setCellValueByColumnAndRow(36, $linea, $condicion->viento);
                $sheet->setCellValueByColumnAndRow(37, $linea, $condicion->direccion);

            }
        }
        /* RE dimensiono las columnas */
        foreach (range('C', 'Z') as $columnID) {
          $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        //$sheet->getColumnDimension('AC')->setAutoSize(true);
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('AE')->setWidth(25);
        $sheet->getColumnDimension('AC')->setWidth(14);
       
        /* Armo el nombre del Archivo
         * El formato es:
         * Listado OT - Año-mes-dia
         */

        $time = Time::now();
        $fecha_actual = $time->i18nFormat('yyyy_MM_dd_HHmm');
        $nombre = 'Listado_OT_' . $fecha_actual . '.xlsx';

        ob_end_clean();

        $dir = ROOT.DS.'dataload';
        $path = $dir.DS.$nombre;
         
        $writer->save($path);
        
        $resultado = [
            'status' => 'success',
            'message' => 'El archivo se creó correctamente.',
            'archivo' => $nombre
        ];

        $this->set('resultado', $resultado);

        $this->set('_serialize', 'resultado');
        $this->RequestHandler->renderAs($this, 'json');        
    }
    
    public function generarexcelotajustes() {
        
        /* Arreglo temporal al error de memoria al generar un excel tan grande */
        ini_set('memory_limit', '-1');
        set_time_limit(900);

        switch ($this->request->session()->read('Auth.User.group_id')) {
            case 1: /* Los Developers ven todo */
                $ordenTrabajos = $this->OrdenTrabajos->find('all', [
                    'contain' => [
                                    'Establecimientos' => ['fields' => ['id','nombre', 'organizacion']],
                                    'OrdenTrabajosDataloads' => ['Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']]],
                                    'Proveedores' => ['fields' => ['id', 'nombre']],
                                    'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']],
                                    'OrdenTrabajosEstados' => ['fields' => ['id', 'nombre']],
                                    'OrdenTrabajosDistribuciones' => ['Lotes' => ['fields' => ['id', 'nombre'], 'Sectores'],
                                                                      'ProyectosLabores' => ['fields' => ['id', 'nombre', 'proyecto_id','expenditure_type']],
                                                                      'Proyectos' => ['fields' => ['id', 'segmento', 'cultivo']],
                                                                      'Monedas' => ['fields' => ['id', 'simbolo']],
                                                                      'Unidades' => ['fields' => ['id', 'nombre']],
                                                                      'OrdenTrabajosCertificaciones'
                                                                    ],
                                    'OrdenTrabajosOracles' => ['fields' => ['id', 'lote', 'orden_trabajo_id', 'oc', 'fecha_oc', 'status','tc', 'cantidad', 'precio', 'labor'],
                                                               ],
                                    'OrdenTrabajosCondicionesMeteorologicas'
                                ]

                ]);
                break;
            default: /* Aqui deberian llegar los ingenieros */
                break;
        }
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Ordenes de Trabajos');

        $writer = new Xlsx($spreadsheet);        
        
        /* Creamos el encabezado y le damos estilos */
        $sheet->mergeCells('A1:A3'); /* Inicio Logo  */
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('El Agronomo');
        $drawing->setPath(WWW_ROOT . 'img' . DS . 'logo_elagronomo_print.png');
        $drawing->setCoordinates('A1');
        $drawing->setHeight(58);
        $drawing->setWorksheet($spreadsheet->getActiveSheet());

        /* Ahora el nombre del sistema */
        $sheet->mergeCells('B1:D3');
        $sheet->setCellValue('B1', 'El Agronomo');
        $styleArray = [
            'font' => [
                'size' => 36
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            ]
        ];
        $sheet->getStyle('B1:D3')->applyFromArray($styleArray);

        /* Ahora pongo todo el encabezado en fondo blanco */
        $styleArray = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FFFFFF',
                ],
            ]
        ];
        $sheet->getStyle('A1:AK3')->applyFromArray($styleArray);
        
        /* Ordenado */
        $sheet->mergeCells('K3:L3');
        $sheet->setCellValue('K3', 'Ordenado');
        /* Certificado */
        $sheet->mergeCells('N3:O3');
        $sheet->setCellValue('N3', 'Certificado');
        /* Valorizado */
        $sheet->mergeCells('P3:Q3');
        $sheet->setCellValue('P3', 'Valorizado');
        /* Oracle */
        $sheet->mergeCells('V3:AC3');
        $sheet->setCellValue('V3', 'Oracle');
        /* ID's Ajustes */
        $sheet->mergeCells('AG3:AK3');
        $sheet->setCellValue('AG3', 'Ajustes');
        
        $styleArray = [
            'font' => [
                'size' => 11,
                'color' => [
                    'startColor' => [
                        'argb' => '94995F',
                    ]
                ]
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ]
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'D8E4BC',
                ],
            ]
        ];
        $sheet->getStyle('K3:L3')->applyFromArray($styleArray);
        $sheet->getStyle('N3:R3')->applyFromArray($styleArray);
        $sheet->getStyle('V3:AC3')->applyFromArray($styleArray);
        $sheet->getStyle('AG3:AK3')->applyFromArray($styleArray);
        
        /* Escribo los encabezados */
        $sheet->setCellValue('A4', 'Nº OT');
        $sheet->setCellValue('B4', 'Proveedor');
        $sheet->setCellValue('C4', 'ORG');
        $sheet->setCellValue('D4', 'Establecimiento');
        $sheet->setCellValue('E4', 'Proyecto');
        $sheet->setCellValue('F4', 'Cultivo');
        $sheet->setCellValue('G4', 'Labor');
        $sheet->setCellValue('H4', 'Lote');
        $sheet->setCellValue('I4', 'Sector');
        $sheet->setCellValue('J4', 'UM');
        $sheet->setCellValue('K4', 'Fecha'); /* Ordenado */
        $sheet->setCellValue('L4', 'Superficie');  /* Ordenado */
        $sheet->setCellValue('M4', 'Aplicado');  /* Certificado */
        $sheet->setCellValue('N4', 'Fecha');  /* Certificado */
        $sheet->setCellValue('O4', 'Superficie'); /* Certificado */
        $sheet->setCellValue('P4', 'Moneda'); /* Valorizado */
        $sheet->setCellValue('Q4', 'Tarifa');
        $sheet->setCellValue('R4', 'Importe');/* Fin del Valorizado */
        $sheet->setCellValue('S4', 'Creado por');
        $sheet->setCellValue('T4', 'Aprobado por');
        $sheet->setCellValue('U4', 'Estado');
        $sheet->setCellValue('V4', 'OC'); /* Oracle */
        $sheet->setCellValue('W4', 'Fecha');
        $sheet->setCellValue('X4', 'TC');
        $sheet->setCellValue('Y4', 'Status');
        $sheet->setCellValue('Z4', 'Cant.');
        $sheet->setCellValue('AA4', 'Precio');
        $sheet->setCellValue('AB4', 'Total');
        $sheet->setCellValue('AC4', 'Lote'); /* Oracle */
        $sheet->setCellValue('AD4', 'Dataload');
        $sheet->setCellValue('AE4', 'Generado por');
        $sheet->setCellValue('AF4', 'Observaciones');
        $sheet->setCellValue('AG4', 'Proyecto'); /* Ajuste */
        $sheet->setCellValue('AH4', 'Labor'); /* Ajuste */
        $sheet->setCellValue('AI4', 'Unidad'); /* Ajuste */
        $sheet->setCellValue('AJ4', 'Lote'); /* Ajuste */
        $sheet->setCellValue('AK4', 'Usuario'); /* Ajuste */
        
        /* Le agrego estilos al color del encabezado de las columnas */
        $styleArray = [
            'font' => [
                'size' => 11,
                'color' => [
                    'startColor' => [
                        'argb' => '94995F',
                    ]
                ]
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ]
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'D8E4BC',
                ],
            ]
        ];
        $sheet->getStyle('A4:AK4')->applyFromArray($styleArray);
        
        $styleError = [
            'font' => [
                'size' => 11,
                'color' => [
                    'startColor' => [
                        'argb' => '676a6c',
                    ]
                ]
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ]
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FFAAAA',
                ],
            ]
        ];
        
        /* Pongo la Fecha en que fue generada */
        $styleArray = [
            'font' => ['size' => 8],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]
        ];        
        $spreadsheet->getActiveSheet()->getStyle('AK1:AK2')->applyFromArray($styleArray);
        $now = Time::now();
        $fecha ='Generado el '. $now->i18nFormat('dd/MM/yyyy HH:mm');
        $sheet->setCellValue('AK1', $fecha);
        
        /* Ahora pongo el usuario */
        $generado = 'Generado por ' . $this->request->session()->read('Auth.User.nombre');
        $sheet->setCellValue('AK2', $generado);
        
        $linea = 5;
        $matched = false;
        
        foreach ($ordenTrabajos as $ordenTrabajo) {
            $distribuciones = $ordenTrabajo->orden_trabajos_distribuciones;
            $oracles = $ordenTrabajo->orden_trabajos_oracles;
            
            /* Si no hay distribuciones, no se crea ninguna linea, asi que genero
             * una linea con los datos de la OT al menos */
            if (empty($distribuciones)) {
                $sheet->setCellValueByColumnAndRow(1, $linea, $ordenTrabajo->id);
                $sheet->setCellValueByColumnAndRow(2, $linea, $ordenTrabajo->proveedore['nombre']);
                $sheet->setCellValueByColumnAndRow(3, $linea, $ordenTrabajo->establecimiento['organizacion']);
                $sheet->setCellValueByColumnAndRow(4, $linea, $ordenTrabajo->establecimiento['nombre']);
                /* Ahora agrego las cantidades ordenadas */
                if ( $ordenTrabajo['fecha'] ) {
                    $sheet->setCellValueByColumnAndRow(11, $linea, $ordenTrabajo['fecha']->i18nFormat('dd/MM/yyyy'));
                }
                if (!empty($ordenTrabajo->user)){
                    $sheet->setCellValueByColumnAndRow(19, $linea, $ordenTrabajo->user['nombre']);                    
                }
                $sheet->setCellValueByColumnAndRow(21, $linea, $ordenTrabajo->orden_trabajos_estado['nombre']);
                $sheet->setCellValueByColumnAndRow(31, $linea, wordwrap($ordenTrabajo->observaciones, 100));
                $linea++;                
            }
            
            /* Si hay algo, entro a las distribuciones */
            foreach ($distribuciones as $distribucion) {
                $certificador = $this->OrdenTrabajosDistribuciones->find('Certificador', ['IdDistribucion' => $distribucion->id]);
                
                $sheet->setCellValueByColumnAndRow(1, $linea, $ordenTrabajo->id);
                $sheet->setCellValueByColumnAndRow(2, $linea, $ordenTrabajo->proveedore['nombre']);
                $sheet->setCellValueByColumnAndRow(3, $linea, $ordenTrabajo->establecimiento['organizacion']);
                $sheet->setCellValueByColumnAndRow(4, $linea, $ordenTrabajo->establecimiento['nombre']);
                $sheet->setCellValueByColumnAndRow(5, $linea, $distribucion->proyecto['segmento']);
                $sheet->setCellValueByColumnAndRow(6, $linea, $distribucion->proyecto['cultivo']);
                $sheet->setCellValueByColumnAndRow(7, $linea, $distribucion->proyectos_labore['nombre']);
                $sheet->setCellValueByColumnAndRow(8, $linea, $distribucion->lote['nombre']);
                if ($distribucion->lote->sectore) {
                    if ($distribucion->lote->sectore->direccion) {
                        $sheet->setCellValueByColumnAndRow(9, $linea, $distribucion->lote->sectore['direccion']);
                    } else {
                        $sheet->setCellValueByColumnAndRow(9, $linea, $distribucion->lote->sectore['nombre']);
                    }
                } else {
                    $sheet->setCellValueByColumnAndRow(9, $linea, '');
                }
                $sheet->setCellValueByColumnAndRow(10, $linea, $distribucion->unidade['nombre']);
                /* Ahora agrego las cantidades ordenadas */
                $sheet->setCellValueByColumnAndRow(11, $linea, $ordenTrabajo['fecha']->i18nFormat('dd/MM/yyyy'));
                $sheet->setCellValueByColumnAndRow(12, $linea, $distribucion['superficie']); /* Ordenadas */
                /* Ahora agrego las cantidades certificadas */
                $precio = 0;
                /* Fecha Aplicacion - Iria en caso de que esté certificada */
                $certificadas = 0;
                if (count($distribucion->orden_trabajos_certificaciones) > 0) {
                    $certificaciones = $distribucion->orden_trabajos_certificaciones;
                    foreach ($certificaciones as $certificacion) {
                        $certificadas += $certificacion['has'];
                        $fecha = $certificacion['fecha_final'];
                        $precio = $certificacion['precio_final'];
                        $fecha_real_certificacion = $certificacion->created;
                    }
                    
                    $sheet->setCellValueByColumnAndRow(13, $linea, $fecha->i18nFormat('dd/MM/yyyy')); /* Fecha real de aplicacion */
                    
                    /* Pongo las cantidades certificadas y la ultima fecha de certificacion */
                    $sheet->setCellValueByColumnAndRow(14, $linea, $fecha_real_certificacion->i18nFormat('dd/MM/yyyy')); /* Fecha real de aplicacion */
                    $sheet->setCellValueByColumnAndRow(15, $linea, $certificadas); /* Certificadas */
                }
                /* Valorizado */
                $sheet->setCellValueByColumnAndRow(16, $linea, $distribucion->moneda->simbolo);
                $sheet->setCellValueByColumnAndRow(17, $linea, $precio);
                
                $total = round($certificadas * $precio, 2);
                
                $sheet->setCellValueByColumnAndRow(18, $linea, $total);
                
                if (!empty($ordenTrabajo->user)){
                    $sheet->setCellValueByColumnAndRow(19, $linea, $ordenTrabajo->user['nombre']);                    
                }
                if (!empty($certificador)){
                    $sheet->setCellValueByColumnAndRow(20, $linea, $certificador->user->nombre);
                }
                $sheet->setCellValueByColumnAndRow(21, $linea, $ordenTrabajo->orden_trabajos_estado['nombre']);
                
                if ( $oracles ) {
                    if (sizeof($oracles) == 1 ) {
                        foreach ($oracles as $k => $oracle) {
                            /* Solo las OC aprobadas */
                            $matched = true;
                            /* Si hay un solo oracle, significa que es una sola linea */
                            $sheet->setCellValueByColumnAndRow(22, $linea, $oracle->oc);
                            $sheet->setCellValueByColumnAndRow(23, $linea, $oracle->fecha_oc->i18nFormat('dd/MM/yyyy'));
                            $sheet->setCellValueByColumnAndRow(24, $linea, $oracle->tc);
                            $sheet->setCellValueByColumnAndRow(25, $linea, $oracle->status);
                            $sheet->setCellValueByColumnAndRow(26, $linea, $oracle->cantidad);
                            $sheet->setCellValueByColumnAndRow(27, $linea, $oracle->precio);
                            $sheet->setCellValueByColumnAndRow(28, $linea, $oracle->total);

                            $sheet->setCellValueByColumnAndRow(29, $linea, $oracle->lote);
                            if ( $total != $oracle->total ) {
                                /* Hay una diferencia entre el total certificado y el total en oracle */
                                /* Marco la celda con un color distinto */
                                $spreadsheet->getActiveSheet()->getStyleByColumnAndRow(18, $linea)->applyFromArray($styleError); 
                                $spreadsheet->getActiveSheet()->getStyleByColumnAndRow(28, $linea)->applyFromArray($styleError);
                            }
                            if ( $certificadas != $oracle->cantidad ) { /* Hay diferencias en las cantidades */
                                $spreadsheet->getActiveSheet()->getStyleByColumnAndRow(15, $linea)->applyFromArray($styleError);
                                $spreadsheet->getActiveSheet()->getStyleByColumnAndRow(26, $linea)->applyFromArray($styleError);
                            }
                            $oracle->estado = 'MATCHED';
                            $oracles[$k] = $oracle;
                        }
                    } else {
                        /* Tiene varias lineas, asi que tengo que matchear las lineas de oracle, con las lineas de la OT */
                        foreach ($oracles as $k => $oracle)  {
                            /* Verifico si tiene Lote - Labor - Superficie */
                            if (strtoupper($oracle->lote) == strtoupper($distribucion->lote['nombre']) && $oracle->cantidad == $certificadas && strtoupper($oracle->labor) == strtoupper($distribucion->proyectos_labore['expenditure_type']) && $oracle->precio == $precio ){
                                $sheet->setCellValueByColumnAndRow(22, $linea, $oracle->oc);
                                $sheet->setCellValueByColumnAndRow(23, $linea, $oracle->fecha_oc->i18nFormat('dd/MM/yyyy'));
                                $sheet->setCellValueByColumnAndRow(24, $linea, $oracle->tc);
                                $sheet->setCellValueByColumnAndRow(25, $linea, $oracle->status);
                                $sheet->setCellValueByColumnAndRow(26, $linea, $oracle->cantidad);
                                $sheet->setCellValueByColumnAndRow(27, $linea, $oracle->precio);
                                $sheet->setCellValueByColumnAndRow(28, $linea, $oracle->total);
                                $sheet->setCellValueByColumnAndRow(29, $linea, $oracle->lote);
                                $matched = true;
                                $oracle->estado = 'MATCHED';
                                $oracles[$k] = $oracle;
                            }
                        }
                        /* No paso el primer emparejamiento, busco las diferencias */
                        foreach ($oracles as $k => $oracle) {
                            if ($oracle->estado !== 'MATCHED') {
                                /* Verifico si tiene Lote - Labor */
                                if ( strtoupper($oracle->labor) == strtoupper($distribucion->proyectos_labore['expenditure_type']) && $oracle->cantidad == $certificadas && $oracle->precio == $precio ) { 
                                /* if ( strtoupper($oracle->lote) == strtoupper($distribucion->lote['nombre']) && strtoupper($oracle->labor) == strtoupper($distribucion->proyectos_labore['nombre']) && $oracle->cantidad == $certificadas ) {  */
                                    $sheet->setCellValueByColumnAndRow(22, $linea, $oracle->oc);
                                    $sheet->setCellValueByColumnAndRow(23, $linea, $oracle->fecha_oc->i18nFormat('dd/MM/yyyy'));
                                    $sheet->setCellValueByColumnAndRow(24, $linea, $oracle->tc);
                                    $sheet->setCellValueByColumnAndRow(25, $linea, $oracle->status);
                                    $sheet->setCellValueByColumnAndRow(26, $linea, $oracle->cantidad);
                                    $sheet->setCellValueByColumnAndRow(27, $linea, $oracle->precio);
                                    $sheet->setCellValueByColumnAndRow(28, $linea, $oracle->total);
                                    $sheet->setCellValueByColumnAndRow(29, $linea, $oracle->lote);
                                    if ( strtoupper($oracle->lote) != strtoupper($distribucion->lote['nombre']) ) {
                                        /* Hay una diferencia entre el total certificado y el total en oracle */
                                        /* Marco la celda con un color distinto */
                                        $spreadsheet->getActiveSheet()->getStyleByColumnAndRow(8, $linea)->applyFromArray($styleError); 
                                        $spreadsheet->getActiveSheet()->getStyleByColumnAndRow(29, $linea)->applyFromArray($styleError);
                                    }
                                    $matched = true;
                                    $oracle->estado = 'MATCHED';
                                    $oracles[$k] = $oracle;
                                }                                    
                            }
                        }
                    }
                }
                
                
                $sheet->setCellValueByColumnAndRow(30, $linea, $ordenTrabajo->orden_trabajos_dataload_id);
                
                if ( $ordenTrabajo->orden_trabajos_dataload ) {
                    if ( $ordenTrabajo->orden_trabajos_dataload->user ) {
                        $sheet->setCellValueByColumnAndRow(31, $linea, $ordenTrabajo->orden_trabajos_dataload->user->nombre );
                    }
                }
                $sheet->setCellValueByColumnAndRow(32, $linea, wordwrap($ordenTrabajo->observaciones, 100));
                
                /* ------------------------------------------------------------------------------ */
                /* Agrego los datos para ingresar los ajustes */
                /* ------------------------------------------------------------------------------ */
                $sheet->setCellValueByColumnAndRow(33, $linea, $distribucion->proyecto_id);
                $sheet->setCellValueByColumnAndRow(34, $linea, $distribucion->proyectos_labore_id);
                $sheet->setCellValueByColumnAndRow(35, $linea, $distribucion->unidade_id);
                $sheet->setCellValueByColumnAndRow(36, $linea, $distribucion->lote_id);
                /* $sheet->setCellValueByColumnAndRow(37, $linea, $certificador->user->id);       */
                /* ------------------------------------------------------------------------------ */
                
                $linea++;
            }
            
            /* ------------------------------------------------ */
            /* Verifico que se hayan colocado todos los Matched */
            /* ------------------------------------------------ */
            $total_orden = 0;
            foreach ($oracles as $k => $oracle) {
                if ($oracle->estado !== 'MATCHED') {
                    /* Verifico si tiene Lote - Labor */
                    $total_orden += $oracle->total;

                    $sheet->setCellValueByColumnAndRow(22, $linea-1, $oracle->oc);
                    $sheet->setCellValueByColumnAndRow(23, $linea-1, $oracle->fecha_oc->i18nFormat('dd/MM/yyyy'));
                    $sheet->setCellValueByColumnAndRow(24, $linea-1, $oracle->tc);
                    $sheet->setCellValueByColumnAndRow(25, $linea-1, $oracle->status);
                    $sheet->setCellValueByColumnAndRow(26, $linea-1, $oracle->cantidad);
                    $sheet->setCellValueByColumnAndRow(27, $linea-1, $oracle->precio);
                    $sheet->setCellValueByColumnAndRow(28, $linea-1, $total_orden);
                    $sheet->setCellValueByColumnAndRow(29, $linea-1, $oracle->lote);

                    $matched = true;
                    $oracle->estado = 'MATCHED';
                    $oracles[$k] = $oracle;

                    /* Armo los comentarios */
                    $comentario = $sheet->getCommentByColumnAndRow(22, $linea-1)
                                        ->setAuthor('El Agronomo');
                    $comentario = $sheet->getCommentByColumnAndRow(22, $linea-1)
                                        ->getText()->createTextRun('OC: ');
                    $sheet->getCommentByColumnAndRow(22, $linea-1)
                        ->getText()->createTextRun($oracle->oc." - $ ".$oracle->total);
                    $sheet->getCommentByColumnAndRow(22, $linea-1)
                        ->getText()->createTextRun("\r\n");
                    $spreadsheet->getActiveSheet()->getStyleByColumnAndRow(22, $linea-1)->applyFromArray($styleError); 
                }
            }            
            /* ------------------------------------------------ */
            /* if ($this->request->session()->read('Auth.User.group_id') == 2 && $ordenTrabajo->orden_trabajos_condiciones_meteorologica) { */
//                $sheet->setCellValueByColumnAndRow(33, $linea, $distribucion->proyecto_id);
//                $sheet->setCellValueByColumnAndRow(34, $linea, $distribucion->proyectos_labore_id);
//                $sheet->setCellValueByColumnAndRow(35, $linea, $distribucion->unidade_id);
//                $sheet->setCellValueByColumnAndRow(36, $linea, $distribucion->lote_id);
//                $sheet->setCellValueByColumnAndRow(37, $linea, $certificador->user->id);

            /* } */
        }
        /* RE dimensiono las columnas */
        foreach (range('C', 'Z') as $columnID) {
          $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        //$sheet->getColumnDimension('AC')->setAutoSize(true);
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(40);
        $sheet->getColumnDimension('AE')->setWidth(25);
        $sheet->getColumnDimension('AC')->setWidth(14);
       
        /* Armo el nombre del Archivo
         * El formato es:
         * Listado OT - Año-mes-dia
         */

        $time = Time::now();
        $fecha_actual = $time->i18nFormat('yyyy_MM_dd_HHmm');
        $nombre = 'Ajuste_' . $fecha_actual . '.xlsx';

        ob_end_clean();

        $dir = ROOT.DS.'dataload';
        $path = $dir.DS.$nombre;
         
        $writer->save($path);
        
        $data = [
            'status' => 'success',
            'message' => 'El archivo se creó correctamente.',
            'archivo' => $nombre
        ];

        $this->set('resultado', $data);
        $this->set('_serialize', 'resultado');
        
        $this->RequestHandler->renderAs($this, 'json');
        
    }
    
    /* 
     * Si encuentra una certificacion vacia, pero hay un valor en oracle
     * actualizo el agronomo con ese valor de oracle
     * 
     */
    private function ChequearOracle( $distribucion = null, $oracle = null ) {
        $this->loadModel('OrdenTrabajosCertificaciones');
        if ( count($distribucion->orden_trabajos_certificaciones) == 1 ) {
            $precio_final = $distribucion->orden_trabajos_certificaciones[0]->precio_final;
            if ( $precio_final === null ) {
                /* Tengo el precio de oracle y una sola certificacion */
                $certificacion = $this->OrdenTrabajosCertificaciones->get( $distribucion->orden_trabajos_certificaciones[0]->id );
                $certificacion->precio_final = $oracle->precio;
                $certificacion->observaciones = $certificacion->observaciones . " - Fixed";
                
                $this->OrdenTrabajosCertificaciones->save ( $certificacion );
                
            }
        }
    }
    /*
     * Obtengo la cotización del dolar
     * 
     * Lo obtengo de geeklab
     */

    public function obtenercotizacion() {
        $data_in = "http://ws.geeklab.com.ar/dolar/get-dolar-json.php";
        $data_json = @file_get_contents($data_in);

        $cotizacion = 0;
        if (strlen($data_json) > 0) {
            $data_out = json_decode($data_json, true);

            if (is_array($data_out)) {
                if (isset($data_out['libre'])) {
                    $cotizacion = $data_out['libre'];
                }
            }
        }
        return $cotizacion;
    }
    
    public function generarexcelvca($tipo = null) {
        $resultado = [
            'status' => 'error',
            'message' => 'Ocurrió un error al intentar bajar el reporte.'
        ];
        
        // Busco los parametros de filtros y los envio a la funcion
        $query = $this->request->getData();
        
        if ($tipo === 'vca') {
            $resultado = $this->generarexcelvca_general( $query );
        }
        if ($tipo === 'oracle') {
            $resultado = $this->generarexcelvca_oracle( $query );
        }
        if ($tipo === 'agrupado') {
            $this->generarexcelvca_oracle_agrupado();
        }
        
        $this->set('resultado', $resultado);
        $this->set('_serialize', 'resultado');
        $this->RequestHandler->renderAs($this, 'json');
    }
    /*
     * Generar Excel VCA General
     * 
     * Devuelve un listado completo de los VCA del establecimiento solicitado para analizarlo fuera del sistema.
     * Este reporte se genera en background y está en src/Shell/Task/QueueGenerarExcelVcaTask.php
     * 
     */
    private function generarexcelvca_general($filtros = null) {
        
        $user_id = $this->request->session()->read('Auth.User.id');
        $filtros['user_id'] = $user_id;
        
        $reporte = $this->QueuedJobs->createJob('GenerarExcelVca', $filtros, [] ,$user_id);
        
        $resultado = [
            'status' => 'success',
            'message' => 'El reporte se puso en cola correctamente.'
        ];
        
        return $resultado;    
    }
    
    private function generarexcelvca_oracle($filtros = null) {
        
        ini_set('memory_limit', '-1');
        set_time_limit(900);
        
        $desde = $filtros['desde'] ? $filtros['desde'] : '';
        $hasta = $filtros['hasta'] ? $filtros['hasta'] : '';
        
        /* Conexion local */
        //$el_agronomo = ConnectionManager::get('default');
        
        $id = $this->request->session()->read('Auth.User.id');
        switch ($this->request->session()->read('Auth.User.group_id')) {
            case 1: /* Los Developers ven todo */
                $ordenTrabajos = $this->OrdenTrabajos->find( 'all', 
                        [
                        'contain' => [
                                'Establecimientos' => ['fields' => ['id', 'organizacion', 'nombre', 'id_organizacion']],
                                'Proveedores' => ['fields' => ['id', 'nombre']],
                                'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']],
                                'OrdenTrabajosEstados'
                                ],
                        'conditions' => ['OrdenTrabajos.orden_trabajos_estado_id <' => 5, $desde, $hasta]
                ]);

                /* Aplico los filtros de Fecha y Establecimientos */
                if ($desde) {
                    $ordenTrabajos->where(['OrdenTrabajos.fecha >=' => $desde]);
                }
                if ($hasta) {
                    $ordenTrabajos->where(['OrdenTrabajos.fecha <=' => $hasta]);
                }

                if ($filtros['establecimientos']) {
                    $establecimientos = explode(',', str_replace(' ', '', $filtros['establecimientos']));
                    $ordenTrabajos->whereInList('OrdenTrabajos.establecimiento_id', $establecimientos);
                }
                
                break;
            case 3: /* Administrativos - Ven OT's de los establecimientos */
                // Filtro de establecimientos
                $ordenTrabajos = $this->OrdenTrabajos->find( 'all', 
                        [
                        'contain' => [
                                'Establecimientos' => ['fields' => ['id', 'organizacion', 'nombre', 'id_organizacion']],
                                'Proveedores' => ['fields' => ['id', 'nombre']],
                                'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']],
                                'OrdenTrabajosEstados'
                                ],
                        'conditions' => ['OrdenTrabajos.orden_trabajos_estado_id <' => 5, $desde, $hasta]
                ]);

                /* Aplico los filtros de Fecha y Establecimientos */
                if ($desde) {
                    $ordenTrabajos->where(['OrdenTrabajos.fecha >=' => $desde]);
                }
                if ($hasta) {
                    $ordenTrabajos->where(['OrdenTrabajos.fecha <=' => $hasta]);
                }

                if ($filtros['establecimientos']) {
                    $establecimientos = explode(',', str_replace(' ', '', $filtros['establecimientos']));
                } else {
                    $establecimientos = explode(',', str_replace(' ', '', $this->request->session()->read('Auth.User.establecimientos')));
                }
                $ordenTrabajos->whereInList('OrdenTrabajos.establecimiento_id', $establecimientos);
                
                break;
            default: /* Aqui deberian llegar los ingenieros */
                /* Obtengo los usuarios del sector */
                $usuarios = $this->NotificarA($id);
                $ordenTrabajos = $this->OrdenTrabajos->find('all', [
                    'contain' => ['Users'],
                    'conditions' => ['Users.id IN' => $usuarios] /* Solo los aprobados */
                ]);
                break;
        }
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('VCAs');
        
        $writer = new Xlsx($spreadsheet);   
        
        /* Creamos el encabezado y le damos estilos */
        $sheet->mergeCells('A1:A3'); /* Inicio Logo  */
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('El Agronomo');
        $drawing->setPath(WWW_ROOT . 'img' . DS . 'logo_elagronomo_print.png');
        $drawing->setCoordinates('A1');
        $drawing->setHeight(58);
        $drawing->setWorksheet($spreadsheet->getActiveSheet());

        /* Ahora el nombre del sistema */
        $sheet->mergeCells('B1:D3');
        $sheet->setCellValue('B1', 'El Agronomo');
        $styleArray = [
            'font' => [
                'size' => 36
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            ]
        ];
        $sheet->getStyle('B1:D3')->applyFromArray($styleArray);

        /* Ahora pongo todo el encabezado en fondo blanco */
        $styleArray = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FFFFFF',
                ],
            ]
        ];
        $sheet->getStyle('A1:R3')->applyFromArray($styleArray);

        $styleError = [
            'font' => [
                'size' => 11,
                'color' => [
                    'startColor' => [
                        'argb' => '676a6c',
                    ]
                ]
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FFAAAA',
                ],
            ]
        ];

        /* Escribo los encabezados */
        $sheet->setCellValue('A4', 'VCA');
        $sheet->setCellValue('B4', 'Estado');
        $sheet->setCellValue('C4', 'Fecha');
        $sheet->setCellValue('D4', 'Proveedor');
        $sheet->setCellValue('E4', 'ORG');
        $sheet->setCellValue('F4', 'Establecimiento');
        $sheet->setCellValue('G4', 'Codigo');
        $sheet->setCellValue('H4', 'Producto');
        $sheet->setCellValue('I4', 'UM');
        
        /* El Agronomo */
        $sheet->setCellValue('J4', 'Cantidad');
        $sheet->setCellValue('K4', 'Oracle');
        $sheet->setCellValue('L4', 'Observaciones');
        
        /* Oracle */
        $sheet->setCellValue('M4', 'Estado');
        
        /* Le agrego estilos al color del encabezado de las columnas */
        $styleArray = [
            'font' => [
                'size' => 11,
                'color' => [
                    'startColor' => [
                        'argb' => '94995F',
                    ]
                ]
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ]
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'D8E4BC',
                ],
            ]
        ];
        $sheet->getStyle('A4:M4')->applyFromArray($styleArray);

        $linea = 5;
        
        /* Armo los listados para utilizarlos en las querys */
        $listado_ordenes_trabajos = $this->listaOrdenesTrabajos($ordenTrabajos);
        $listado_organizaciones = $this->listaEstablecimientosOrdenesTrabajos($ordenTrabajos);
        
        /* Ejecuto las querys y trabajo solo sobre los datos completos */
        $data_agronomo = $this->buscar_datos_agronomo($listado_ordenes_trabajos);
        $data_oracle = $this->buscar_datos_oracle($listado_ordenes_trabajos, $listado_organizaciones);
        
        foreach ($ordenTrabajos as $ordenTrabajo) {
            
            $insumos = $this->orden_trabajos_filtrar_insumos_($ordenTrabajo->id, $data_agronomo);
            $insumos_oracles = $this->orden_trabajos_filtrar_insumos_($ordenTrabajo->id, $data_oracle);
            
            /* Las lineas de distribucion estan en El Agronomo */
            /* Comienzo a cargar las lineas */
            foreach($insumos as $k => $insumo) {
                    $entregado = 0;
                    $devuelto = 0;

                    $sheet->setCellValueByColumnAndRow(1, $linea, $ordenTrabajo->id);
                    $sheet->setCellValueByColumnAndRow(2, $linea, $ordenTrabajo->orden_trabajos_estado->nombre);
                    $sheet->setCellValueByColumnAndRow(3, $linea, $ordenTrabajo->fecha->i18nFormat('dd/MM/yyyy'));
                    $sheet->setCellValueByColumnAndRow(4, $linea, $ordenTrabajo->proveedore['nombre']);
                    $sheet->setCellValueByColumnAndRow(5, $linea, $ordenTrabajo->establecimiento['organizacion']);
                    $sheet->setCellValueByColumnAndRow(6, $linea, $ordenTrabajo->establecimiento['nombre']);

                    $sheet->setCellValueByColumnAndRow(7, $linea, $insumo['codigooracle']);
                    $sheet->setCellValueByColumnAndRow(8, $linea, $insumo['nombre']);
                    $sheet->setCellValueByColumnAndRow(9, $linea, $insumo['codigo']);

                    $cantidad = round($insumo['cantidad'], 2);
                    $sheet->setCellValueByColumnAndRow(10, $linea, $cantidad);

                    /* Ahora verifico si existe en oracle */
                    $encontrado = 0;
                    foreach($insumos_oracles as $k => $insumo_oracle) {
                        if ($insumo_oracle['INVENTORY_ITEM_ID'] == $insumo['inventory_item']) {
                            
                            if ($insumo_oracle['CANTIDAD'] > 0) {
                                /* Exceso de devoluciones */
                                $cantidad_oracle = 0 - (round($insumo_oracle['CANTIDAD'], 2));
                            } else {
                                $cantidad_oracle = round(abs($insumo_oracle['CANTIDAD']), 2);
                            }
                            
                            $sheet->setCellValueByColumnAndRow(11, $linea, $cantidad_oracle);
                            $insumo_oracle['INVENTORY_ITEM_ID'] = '0';
                            $insumos_oracles[$k] = $insumo_oracle;

                            $encontrado = 1;

                            /* Comparo las cantidades y si hay diferencias, lo marco */
                            if ( $cantidad_oracle != $cantidad ) { /* Hay diferencias en las cantidades */
                                $spreadsheet->getActiveSheet()->getStyleByColumnAndRow(11, $linea)->applyFromArray($styleError);
                                $sheet->setCellValueByColumnAndRow(12, $linea, 'No coinciden las cantidades');
                                $spreadsheet->getActiveSheet()->getStyleByColumnAndRow(13, $linea)->applyFromArray($styleError);
                            } 
                            
                            break;
                        }
                    }
                    if ($encontrado === 0) {
                        $spreadsheet->getActiveSheet()->getStyleByColumnAndRow(11, $linea)->applyFromArray($styleError);
                        $sheet->setCellValueByColumnAndRow(12, $linea, 'No está ingresado en oracle');
                        $spreadsheet->getActiveSheet()->getStyleByColumnAndRow(13, $linea)->applyFromArray($styleError);
                    }
                    $linea++;
                }
                
            if ($insumos_oracles) {
                /* Ahora reviso lo que tenia en oracle */
                foreach ($insumos_oracles as $insumo_oracle) {
                    if ($insumo_oracle['INVENTORY_ITEM_ID'] != 0) {
                        /* Hay un producto que ESTA en oracle pero NO en el agronomo */
                        $sheet->setCellValueByColumnAndRow(1, $linea, $ordenTrabajo->id);
                        $sheet->setCellValueByColumnAndRow(2, $linea, $ordenTrabajo->orden_trabajos_estado->nombre);
                        $sheet->setCellValueByColumnAndRow(3, $linea, $ordenTrabajo->fecha->i18nFormat('dd/MM/yyyy'));
                        $sheet->setCellValueByColumnAndRow(4, $linea, $ordenTrabajo->proveedore['nombre']);
                        $sheet->setCellValueByColumnAndRow(5, $linea, $ordenTrabajo->establecimiento['organizacion']);
                        $sheet->setCellValueByColumnAndRow(6, $linea, $ordenTrabajo->establecimiento['nombre']);

                        $sheet->setCellValueByColumnAndRow(7, $linea, $insumo_oracle['INVENTORY_ITEM_ID']);
                        
                        $producto_item = $this->Productos->findByInventoryItem($insumo_oracle['INVENTORY_ITEM_ID'])->contain('Unidades')->first();
                        if ($producto_item) {
                            $sheet->setCellValueByColumnAndRow(8, $linea, $producto_item->nombre);
                            $sheet->setCellValueByColumnAndRow(9, $linea, $producto_item->unidade->nombre);
                        }

                        /* Marco los errores */
                        $spreadsheet->getActiveSheet()->getStyleByColumnAndRow(10, $linea)->applyFromArray($styleError);

                        $sheet->setCellValueByColumnAndRow(11, $linea, abs($insumo_oracle['CANTIDAD']));
                        $sheet->setCellValueByColumnAndRow(12, $linea, 'Está ingresado en Oracle pero no en El Agronomo');

                        $spreadsheet->getActiveSheet()->getStyleByColumnAndRow(13, $linea)->applyFromArray($styleError);

                        $linea++;
                    }
                }
            }

        }

        foreach (range('B', 'N') as $columnID) {
            //autodimensionar las columnas
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('M')->setWidth(7);

        ob_end_clean();

        $time = Time::now();
        $fecha_actual = $time->i18nFormat('yyyy_MM_dd HHmm');
        
        $nombre = 'Listado_VCA_ORACLE_' . $fecha_actual . '.xlsx';
        
        $dir = ROOT.DS.'dataload';
        
        $path = $dir.DS.$nombre;

        $writer->save($path);

        $resultado = [
            'status' => 'success',
            'message' => 'El archivo se creó correctamente.',
            'archivo' => $nombre
        ];
        
        return $resultado;
    }
    
    /**
     * Recibo un array de ordenes de trabajo y armo el listado para que puedan
     * ser usados en los filtros
     * 
     * Ejemplo: ('62580', '659500', ... )
     * 
     * @param type $ordenes Array de Ordenes de Trabajo
     */
    private function listaOrdenesTrabajos ($ordenes = null) {
        $resultados = [];
        /* Armo un array con los numeros de OT que quiero filtrar */
        foreach ($ordenes as $orden) {
            if (!in_array("'".$orden->id."'", $resultados)) {
                array_push($resultados, "'".$orden->id."'");
            }
        }
        return $resultados;
    }

    /**
     * Genero un array de id_organizacion con los valores en oracle para filtrar
     * 
     * @param type $ordenes
     * @return array
     */
    private function listaEstablecimientosOrdenesTrabajos ($ordenes = null) {
        $resultados = [];
        /* Armo un array con los numeros de OT que quiero filtrar */
        foreach ($ordenes as $orden) {
            if (!in_array("'".$orden->establecimiento->id_organizacion."'", $resultados)) {
                
                array_push($resultados, "'".$orden->establecimiento->id_organizacion."'");
            }
        }
        return $resultados;
    }
    
    /**
     * Busco los movimientos de insumos en oracle
     * 
     * Utilizo la tabla mtl_material_transactions, con los filtros de un array de OT
     * y un array con las organizaciones. Tambien aplico el reason_id = 1910, que me
     * filtra todas los movimientos que son desde el agronomo.
     * 
     * @param type $ordenes Array de Numeros de OT
     * @param type $listado_organizaciones Array de id_organizacion (viene de establecimientos)
     * @return type
     */
    private function buscar_datos_oracle($ordenes = null, $listado_organizaciones = null) {
        $connection = ConnectionManager::get('oracle');
        $strsql = "select sum (tra.transaction_quantity) Cantidad,
                          tra.inventory_item_id,
                          tra.attribute3 OT
                   from apps.mtl_material_transactions tra
                    where 1=1
                         AND tra.attribute3 IN (".implode(', ',$ordenes).")
                         AND tra.organization_id IN (". implode(', ', $listado_organizaciones).")
                       
                         GROUP BY tra.inventory_item_id, tra.attribute3";
        $results =  $connection->execute($strsql)->fetchAll('assoc');
          /* AND tra.REASON_ID = '1910'*/
        return $results;
    }
    
    /**
     * Tomo el listado de OT a incluir y ejecuto un select, de esta forma devuelve los datos en forma mas rapida
     * 
     * @param type $ordenes Array de Numeros de OT
     * @return type Array
     */
    private function buscar_datos_agronomo($ordenes = null) {
        $connection = ConnectionManager::get('default');
        $strsql = "SELECT pro.codigooracle, pro.inventory_item, pro.nombre, um.codigo, ot.orden_trabajo_id as OT, sum(movimientos.cantidad) AS cantidad
                    FROM `orden_trabajos_insumos` ot,
                                   `productos` pro,
                                   `unidades` um,
                                   (SELECT orden_trabajos_insumo_id, id, almacene_id, oracle_flag, DATE(fecha) fecha, cantidad, 'ENTREGAS' mov_tipo
                                        FROM orden_trabajos_insumos_entregas WHERE 1=1 AND deleted is null AND cantidad <> 0
                                    UNION
                                    SELECT orden_trabajos_insumo_id, id, almacene_id, oracle_flag, DATE(fecha) fecha, (0 - cantidad), 'DEVOLUCIONES' mov_tipo
                                        FROM orden_trabajos_insumos_devoluciones WHERE 1=1 AND deleted is null AND cantidad <> 0
                                    ) movimientos
                    WHERE 1=1 
                                 AND ot.producto_id = pro.id 
                                 AND ot.id = movimientos.orden_trabajos_insumo_id
                                 AND ot.unidade_id = um.id 
                                 AND ot.deleted IS NULL
                       AND ot.orden_trabajo_id IN (". implode(', ',$ordenes).")
                    GROUP BY ot.orden_trabajo_id, pro.codigooracle, pro.nombre, um.codigo";
        $results =  $connection->execute($strsql)->fetchAll('assoc');
        return $results;
    }
    
    /**
     * Filtro el listado de resultados de acuerdo a la enviada como parametro
     * 
     * @param type $ot   Orden de Trabajo
     * @param type $data Listado de Ordenes
     * @return type Array con los datos de esa OT
     */
    private function orden_trabajos_filtrar_insumos_( $ot = null, $data = null) {
        $results = array_filter($data, function($orden_trabajo) use ($ot) {
            return $orden_trabajo['OT'] == $ot;
        });
        return $results;
    }
    
    private function generarexcelvca_oracle_agrupado() {
        
        ini_set('memory_limit', '-1');
        set_time_limit(900);
         
        $id = $this->request->session()->read('Auth.User.id');
        switch ($this->request->session()->read('Auth.User.group_id')) {
            case 1: /* Los Developers ven todo */
                $ordenTrabajos = $this->OrdenTrabajos->find( 'all', 
                    [
                    'contain' => [
                        'Establecimientos' => ['fields' => ['id', 'organizacion', 'nombre']],
                        'Proveedores' => ['fields' => ['id', 'nombre']],
                        'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']],
                        'OrdenTrabajosEstados' => ['fields' => ['id', 'nombre']],
                        'OrdenTrabajosDistribuciones' => [
                            'Lotes' => ['fields' => ['id', 'nombre'], 'Sectores'],
                            'ProyectosLabores' => ['fields' => ['id', 'nombre']],
                            'Proyectos' => ['fields' => ['id', 'nombre', 'segmento', 'cultivo']],
                            'Unidades' => ['fields' => ['id', 'nombre']],
                            'OrdenTrabajosCertificaciones',
                            'OrdenTrabajosInsumos' => [
                                                        'Productos' => [ 'fields' => ['id', 'nombre'], 'Unidades' => ['fields' => ['nombre']]],
                                                        'OrdenTrabajosInsumosEntregas' => ['fields' => ['orden_trabajos_insumo_id','cantidad']],
                                                        'OrdenTrabajosInsumosDevoluciones' => ['fields' => ['orden_trabajos_insumo_id','cantidad']],
                                                        'Almacenes' => ['fields' => ['id', 'nombre']]]
                                                    ],
                        'OrdenTrabajosInsumos' => [ 'conditions' => ['orden_trabajos_distribucione_id' => 0],
                                                        'Productos' => [ 'fields' => ['id', 'nombre'], 'Unidades' => ['fields' => ['nombre']]],
                                                        'OrdenTrabajosInsumosEntregas' => ['fields' => ['orden_trabajos_insumo_id','cantidad']],
                                                        'OrdenTrabajosInsumosDevoluciones' => ['fields' => ['orden_trabajos_insumo_id','cantidad']],
                                                        'Almacenes' => ['fields' => ['id', 'nombre']]]
                        ],
                    'conditions' => ['OrdenTrabajos.orden_trabajos_estado_id <' => 5, 'OrdenTrabajos.orden_trabajos_estado_id >' => 1 ]
                ]);
                break;
            case 2: /* Ingenieros */
                $rol = $this->request->session()->read('Auth.User.role_id');
                if ($rol == 3 || $rol == 6) {
                    $establecimientos = $this->request->session()->read('Auth.User.establecimientos');
                    $ordenTrabajos = $this->OrdenTrabajos->find( 'all', 
                        [
                        'contain' => [
                            'Establecimientos' => ['fields' => ['id', 'organizacion', 'nombre']],
                            'Proveedores' => ['fields' => ['id', 'nombre']],
                            'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']],
                            'OrdenTrabajosEstados' => ['fields' => ['id', 'nombre']],
                            'OrdenTrabajosDistribuciones' => [
                                'Lotes' => ['fields' => ['id', 'nombre'], 'Sectores'],
                                'ProyectosLabores' => ['fields' => ['id', 'nombre']],
                                'Proyectos' => ['fields' => ['id', 'nombre', 'segmento', 'cultivo']],
                                'Unidades' => ['fields' => ['id', 'nombre']],
                                'OrdenTrabajosCertificaciones',
                                'OrdenTrabajosInsumos' => [
                                                            'Productos' => [ 'fields' => ['id', 'nombre'], 'Unidades' => ['fields' => ['nombre']]],
                                                            'OrdenTrabajosInsumosEntregas',
                                                            'OrdenTrabajosInsumosDevoluciones',
                                                            'Almacenes' => ['fields' => ['id', 'nombre']]]
                                                        ]
                            ],
                        'conditions' => ['Establecimientos.id IN ' => $establecimientos ]
                    ]);
                    break;                    
                } else {
                    $usuarios = $this->NotificarA($id);
                    $ordenTrabajos = $this->OrdenTrabajos->find( 'all', 
                        [
                        'contain' => [
                            'Establecimientos' => ['fields' => ['id', 'organizacion', 'nombre']],
                            'Proveedores' => ['fields' => ['id', 'nombre']],
                            'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']],
                            'OrdenTrabajosEstados' => ['fields' => ['id', 'nombre']],
                            'OrdenTrabajosDistribuciones' => [
                                'Lotes' => ['fields' => ['id', 'nombre'], 'Sectores'],
                                'ProyectosLabores' => ['fields' => ['id', 'nombre']],
                                'Proyectos' => ['fields' => ['id', 'nombre', 'segmento', 'cultivo']],
                                'Unidades' => ['fields' => ['id', 'nombre']],
                                'OrdenTrabajosCertificaciones',
                                'OrdenTrabajosInsumos' => [
                                                            'Productos' => [ 'fields' => ['id', 'nombre'], 'Unidades' => ['fields' => ['nombre']]],
                                                            'OrdenTrabajosInsumosEntregas',
                                                            'OrdenTrabajosInsumosDevoluciones',
                                                            'Almacenes' => ['fields' => ['id', 'nombre']]]
                                                        ]
                            ],
                        'conditions' => ['OrdenTrabajos.user_id IN ' => $usuarios ]
                    ]);
                    break;
                }
            case 3: /* Administrativos - Ven OT's de los establecimientos */
                $ordenTrabajos = $this->OrdenTrabajos->find( 'all', 
                    [
                    'contain' => [
                        'Establecimientos' => ['fields' => ['id', 'organizacion', 'nombre']],
                        'Proveedores' => ['fields' => ['id', 'nombre']],
                        'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']],
                        'OrdenTrabajosEstados' => ['fields' => ['id', 'nombre']],
                        'OrdenTrabajosDistribuciones' => [
                            'Lotes' => ['fields' => ['id', 'nombre'], 'Sectores'],
                            'ProyectosLabores' => ['fields' => ['id', 'nombre']],
                            'Proyectos' => ['fields' => ['id', 'nombre', 'segmento', 'cultivo']],
                            'Unidades' => ['fields' => ['id', 'nombre']],
                            'OrdenTrabajosCertificaciones',
                            'OrdenTrabajosInsumos' => [
                                                        'Productos' => [ 'fields' => ['id', 'nombre'], 'Unidades' => ['fields' => ['nombre']]],
                                                        'OrdenTrabajosInsumosEntregas' => ['fields' => ['orden_trabajos_insumo_id','cantidad']],
                                                        'OrdenTrabajosInsumosDevoluciones' => ['fields' => ['orden_trabajos_insumo_id','cantidad']],
                                                        'Almacenes' => ['fields' => ['id', 'nombre']]]
                                                    ],
                        'OrdenTrabajosInsumos' => [ 'conditions' => ['orden_trabajos_distribucione_id' => 0],
                                                        'Productos' => [ 'fields' => ['id', 'nombre'], 'Unidades' => ['fields' => ['nombre']]],
                                                        'OrdenTrabajosInsumosEntregas' => ['fields' => ['orden_trabajos_insumo_id','cantidad']],
                                                        'OrdenTrabajosInsumosDevoluciones' => ['fields' => ['orden_trabajos_insumo_id','cantidad']],
                                                        'Almacenes' => ['fields' => ['id', 'nombre']]]
                        ],
                    'conditions' => ['OrdenTrabajos.orden_trabajos_estado_id <' => 5, 'OrdenTrabajos.orden_trabajos_estado_id >' => 1,
                                     'Establecimientos.id IN' => $this->request->session()->read('Auth.User.establecimientos'),
                                     'OrdenTrabajos.id >' => '70000']
//                    'limit' => ['500']            
                ]);                
                break;
            case 6: /* Auditores */
                $ordenTrabajos = $this->OrdenTrabajos->find( 'all', 
                    [
                    'contain' => [
                        'Establecimientos' => ['fields' => ['id', 'organizacion', 'nombre']],
                        'Proveedores' => ['fields' => ['id', 'nombre']],
                        'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']],
                        'OrdenTrabajosEstados' => ['fields' => ['id', 'nombre']],
                        'OrdenTrabajosDistribuciones' => [
                            'Lotes' => ['fields' => ['id', 'nombre'], 'Sectores'],
                            'ProyectosLabores' => ['fields' => ['id', 'nombre']],
                            'Proyectos' => ['fields' => ['id', 'nombre', 'segmento', 'cultivo']],
                            'Unidades' => ['fields' => ['id', 'nombre']],
                            'OrdenTrabajosCertificaciones',
                            'OrdenTrabajosInsumos' => [
                                                        'Productos' => [ 'fields' => ['id', 'nombre'], 'Unidades' => ['fields' => ['nombre']]],
                                                        'OrdenTrabajosInsumosEntregas' => ['fields' => ['orden_trabajos_insumo_id','cantidad']],
                                                        'OrdenTrabajosInsumosDevoluciones' => ['fields' => ['orden_trabajos_insumo_id','cantidad']],
                                                        'Almacenes' => ['fields' => ['id', 'nombre']]]
                                                    ],
                        'OrdenTrabajosInsumos' => [ 'conditions' => ['orden_trabajos_distribucione_id' => 0],
                                                        'Productos' => [ 'fields' => ['id', 'nombre'], 'Unidades' => ['fields' => ['nombre']]],
                                                        'OrdenTrabajosInsumosEntregas' => ['fields' => ['orden_trabajos_insumo_id','cantidad']],
                                                        'OrdenTrabajosInsumosDevoluciones' => ['fields' => ['orden_trabajos_insumo_id','cantidad']],
                                                        'Almacenes' => ['fields' => ['id', 'nombre']]]
                        ],
                    'conditions' => ['OrdenTrabajos.orden_trabajos_estado_id <' => 5, 'OrdenTrabajos.orden_trabajos_estado_id >' => 1 ],
                    'limit' => [500]
                ]);
                break;
            default: /* Aqui deberian llegar los ingenieros */
                /* Obtengo los usuarios del sector */
                $usuarios = $this->NotificarA($id);
                $ordenTrabajos = $this->OrdenTrabajos->find('all', [
                    'contain' => ['Users'],
                    'conditions' => ['Users.id IN' => $usuarios] /* Solo los aprobados */
                ]);
                break;
        }
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('VCAs');
        
        $writer = new Xlsx($spreadsheet);   
        
        /* Creamos el encabezado y le damos estilos */
        $sheet->mergeCells('A1:A3'); /* Inicio Logo  */
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('El Agronomo');
        $drawing->setPath(WWW_ROOT . 'img' . DS . 'logo_elagronomo_print.png');
        $drawing->setCoordinates('A1');
        $drawing->setHeight(58);
        $drawing->setWorksheet($spreadsheet->getActiveSheet());

        /* Ahora el nombre del sistema */
        $sheet->mergeCells('B1:D3');
        $sheet->setCellValue('B1', 'El Agronomo');
        $styleArray = [
            'font' => [
                'size' => 36
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT,
            ]
        ];
        $sheet->getStyle('B1:D3')->applyFromArray($styleArray);

        /* Ahora pongo todo el encabezado en fondo blanco */
        $styleArray = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FFFFFF',
                ],
            ]
        ];
        $sheet->getStyle('A1:R3')->applyFromArray($styleArray);

        
        $styleSiembra = [
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ]
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'D8E4BC',
                ],
            ]
        ];        
        
        /* Unifico el Ordenado */
        $sheet->mergeCells('M3:P3');
        $sheet->setCellValue('M3', 'ORDENADO');
        
        /* Unifico el Certificado */
        $sheet->mergeCells('Q3:T3');
        $sheet->setCellValue('Q3', 'CERTIFICADO');        
        $styleArray = [
            'font' => [
                'size' => 11,
                'color' => [
                    'startColor' => [
                        'argb' => '94995F',
                    ]
                ]
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ]
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'D8E4BC',
                ],
            ]
        ];        
        $sheet->getStyle('M3:T3')->applyFromArray($styleArray);

        /* Escribo los encabezados */
        $sheet->setCellValue('A4', 'OT');
        $sheet->setCellValue('B4', 'VCA');
        $sheet->setCellValue('C4', 'Proveedor');
        $sheet->setCellValue('D4', 'ORG');
        $sheet->setCellValue('E4', 'Establecimiento');
        $sheet->setCellValue('F4', 'Proyecto');
        $sheet->setCellValue('G4', 'Cultivo');
        $sheet->setCellValue('H4', 'Sector');
        $sheet->setCellValue('I4', 'Labor');
        $sheet->setCellValue('J4', 'Lote');
        $sheet->setCellValue('K4', 'Producto');
        $sheet->setCellValue('L4', 'UM');
        
        /* Ordenado */
        $sheet->setCellValue('M4', 'Fecha');
        $sheet->setCellValue('N4', 'Superficie');
        $sheet->setCellValue('O4', 'Dosis');
        $sheet->setCellValue('P4', 'Cantidad');
        
        /* Certificado */
        $sheet->setCellValue('Q4', 'Fecha');
        $sheet->setCellValue('R4', 'Superficie');
        $sheet->setCellValue('S4', 'Dosis');
        $sheet->setCellValue('T4', 'Cantidad');
        
        
        $sheet->setCellValue('U4', 'Entregas');
        $sheet->setCellValue('V4', 'Devoluciones');
        $sheet->setCellValue('W4', 'Almacen');
        $sheet->setCellValue('X4', 'Estado');
        $sheet->setCellValue('Y4', 'Ordenado por');
        $sheet->setCellValue('Z4', 'Exist. Oracle');
        
        /* Le agrego estilos al color del encabezado de las columnas */
        $styleArray = [
            'font' => [
                'size' => 11,
                'color' => [
                    'startColor' => [
                        'argb' => '94995F',
                    ]
                ]
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ]
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'D8E4BC',
                ],
            ]
        ];
        $sheet->getStyle('A4:Z4')->applyFromArray($styleArray);

        $linea = 5;
        
        foreach ($ordenTrabajos as $ordenTrabajo) {
            $distribuciones = $ordenTrabajo->orden_trabajos_distribuciones;
            if ( $distribuciones ) {
                foreach ($distribuciones as $distribucion) {
                    $insumos = $distribucion->orden_trabajos_insumos;
                        foreach ($insumos as $insumo) {
                            $sheet->setCellValueByColumnAndRow(1, $linea, $ordenTrabajo->id);
                            $sheet->setCellValueByColumnAndRow(2, $linea, $ordenTrabajo->id);
                            $sheet->setCellValueByColumnAndRow(3, $linea, $ordenTrabajo->proveedore['nombre']);
                            $sheet->setCellValueByColumnAndRow(4, $linea, $ordenTrabajo->establecimiento['organizacion']);
                            $sheet->setCellValueByColumnAndRow(5, $linea, $ordenTrabajo->establecimiento['nombre']);
                            $sheet->setCellValueByColumnAndRow(6, $linea, $distribucion->proyecto['segmento']);
                            $sheet->setCellValueByColumnAndRow(7, $linea, $distribucion->proyecto['cultivo']); /* Cultivo */
                            $sheet->setCellValueByColumnAndRow(8, $linea, $distribucion->lote->sectore ? $distribucion->lote->sectore['direccion'] : ''); /* Sector / Establecimiento */
                            $sheet->setCellValueByColumnAndRow(9, $linea, $distribucion->proyectos_labore['nombre']);
                            $sheet->setCellValueByColumnAndRow(10, $linea, $distribucion->lote['nombre']);

                            /* Datos del Insumo */
                            $sheet->setCellValueByColumnAndRow(11, $linea, $insumo->producto['nombre']);
                            $sheet->setCellValueByColumnAndRow(12, $linea, $insumo->producto->unidade['codigo']);

                            /* Ordenado */
                            $sheet->setCellValueByColumnAndRow(13, $linea, $ordenTrabajo['fecha']->i18nFormat('dd/MM/yyyy'));
                            $sheet->setCellValueByColumnAndRow(14, $linea, $distribucion['superficie']);
                            $sheet->setCellValueByColumnAndRow(15, $linea, $insumo['dosis']);
                            $sheet->setCellValueByColumnAndRow(16, $linea, $insumo['cantidad']);

                            /* Reviso si está certificado */
                                if(!empty($distribucion->orden_trabajos_certificaciones)){
                                    $has_certificadas = 0; /* Sumo todas las has certificadas */
                                    foreach($distribucion->orden_trabajos_certificaciones as $certificacion){
                                        $has_certificadas += $certificacion['has'];
                                        $fecha_certificacion = $certificacion['fecha_final'];
                                    }
                                    $sheet->setCellValueByColumnAndRow(17, $linea, $fecha_certificacion->i18nFormat('dd/MM/yyyy'));
                                    $sheet->setCellValueByColumnAndRow(18, $linea, $has_certificadas);
                                    /* Agrego como comentario */
        /*                            $sheet->getComment('P'.$linea)
                                        ->getText()->createTextRun('Total amount on the current invoice, excluding VAT.');                            */

                                    $data = $this->recalcular_dosis_aplicada($distribucion->id, $insumo->producto_id);
                                    $sheet->setCellValueByColumnAndRow(19, $linea, round($data['dosis_aplicada'], 3));
                                    $sheet->setCellValueByColumnAndRow(20, $linea, round($data['aplicado'],3));
                                }

                            $total_entregas = 0;
                            /* Sumo todas las entregas */
                            $entregas = $insumo->orden_trabajos_insumos_entregas;
                            foreach ($entregas as $entrega) {
                                $total_entregas = $total_entregas + $entrega['cantidad'];
                            }
                            $sheet->setCellValueByColumnAndRow(21, $linea, $total_entregas);
                            /* ------------------------------------------------------------- */

                            $total_devoluciones = 0;
                            /* Sumo todas las devoluciones */
                            $devoluciones = $insumo->orden_trabajos_insumos_devoluciones;
                            foreach ($devoluciones as $devolucione) {
                                $total_devoluciones = $total_devoluciones + $devolucione['cantidad'];
                            }
                            $sheet->setCellValueByColumnAndRow(22, $linea, $total_devoluciones);
                            /* ------------------------------------------------------------- */
                            $sheet->setCellValueByColumnAndRow(23, $linea, $insumo->almacene['nombre']);
                            $sheet->setCellValueByColumnAndRow(24, $linea, $ordenTrabajo->orden_trabajos_estado['nombre']);
                            $sheet->setCellValueByColumnAndRow(25, $linea, $ordenTrabajo->user['nombre']);
                            
                            $linea++;
                        }

                }
            }
            
            
            /* Ahora agrego todas las lineas de Siembra */
            if ( $ordenTrabajo->orden_trabajos_insumos ) {
                $insumos = $distribucion->orden_trabajos_insumos;
                $superficie_a_sembrar = 0;
                $lotes_a_sembrar = '';
                $proyecto = '';
                $proyecto_a_sembrar = '';
                
                foreach($ordenTrabajo->orden_trabajos_distribuciones as $distribucion) {
                    $superficie_a_sembrar += $distribucion->superficie;
                    $lotes_a_sembrar = $lotes_a_sembrar.' / '.$distribucion->lote['nombre'];
                    $proyecto_a_sembrar = $distribucion->proyecto['segmento'];
                }
                
                
                
                foreach ($ordenTrabajo->orden_trabajos_insumos as $insumo) {
                    $sheet->setCellValueByColumnAndRow(1, $linea, $ordenTrabajo->id);
                    $sheet->setCellValueByColumnAndRow(2, $linea, $ordenTrabajo->id);
                    $sheet->setCellValueByColumnAndRow(3, $linea, $ordenTrabajo->proveedore['nombre']);
                    $sheet->setCellValueByColumnAndRow(4, $linea, $ordenTrabajo->establecimiento['organizacion']);
                    $sheet->setCellValueByColumnAndRow(5, $linea, $ordenTrabajo->establecimiento['nombre']);
                    $sheet->setCellValueByColumnAndRow(6, $linea, $proyecto_a_sembrar);
                    $sheet->setCellValueByColumnAndRow(7, $linea, $distribucion->proyecto['cultivo']); /* Cultivo */
                    $sheet->setCellValueByColumnAndRow(8, $linea, $distribucion->lote->sectore ? $distribucion->lote->sectore['nombre'] : ''); /* Sector / Establecimiento */
                    $sheet->setCellValueByColumnAndRow(9, $linea, 'OT DE SIEMBRA');
                    
                    $spreadsheet->getActiveSheet()->getStyleByColumnAndRow(7, $linea)->applyFromArray($styleSiembra);
                    
                    $sheet->setCellValueByColumnAndRow(10, $linea, $lotes_a_sembrar);

                    /* Datos del Insumo */
                    $sheet->setCellValueByColumnAndRow(11, $linea, $insumo->producto['nombre']);
                    $sheet->setCellValueByColumnAndRow(12, $linea, $insumo->producto->unidade['codigo']);

                    /* Ordenado */
                    $sheet->setCellValueByColumnAndRow(13, $linea, $ordenTrabajo['fecha']->i18nFormat('dd/MM/yyyy'));
                    $sheet->setCellValueByColumnAndRow(14, $linea, $superficie_a_sembrar);
                    $sheet->setCellValueByColumnAndRow(15, $linea, $insumo['dosis']);
                    $sheet->setCellValueByColumnAndRow(16, $linea, $insumo['cantidad']);

                    /* Reviso si está certificado */
//                        if(!empty($distribucion->orden_trabajos_certificaciones)){
//                            $has_certificadas = 0; /* Sumo todas las has certificadas */
//                            foreach($distribucion->orden_trabajos_certificaciones as $certificacion){
//                                $has_certificadas += $certificacion['has'];
//                                $fecha_certificacion = $certificacion['fecha_final'];
//                            }
//                            $sheet->setCellValueByColumnAndRow(15, $linea, $fecha_certificacion->i18nFormat('dd/MM/yyyy'));
//                            $sheet->setCellValueByColumnAndRow(16, $linea, $has_certificadas);
//                            /* Agrego como comentario */
///*                            $sheet->getComment('P'.$linea)
//                                ->getText()->createTextRun('Total amount on the current invoice, excluding VAT.');                            */
//
//                            $data = $this->recalcular_dosis_aplicada($distribucion->id, $insumo->producto_id);
//                            $sheet->setCellValueByColumnAndRow(17, $linea, round($data['dosis_aplicada'], 3));
//                            $sheet->setCellValueByColumnAndRow(18, $linea, round($data['aplicado'],3));
//                        }

                    $total_entregas = 0;
                    /* Sumo todas las entregas */
                    $entregas = $insumo->orden_trabajos_insumos_entregas;
                    foreach ($entregas as $entrega) {
                        $total_entregas = $total_entregas + $entrega['cantidad'];
                    }
                    $sheet->setCellValueByColumnAndRow(21, $linea, $total_entregas);
                    /* ------------------------------------------------------------- */

                    $total_devoluciones = 0;
                    /* Sumo todas las devoluciones */
                    $devoluciones = $insumo->orden_trabajos_insumos_devoluciones;
                    foreach ($devoluciones as $devolucione) {
                        $total_devoluciones = $total_devoluciones + $devolucione['cantidad'];
                    }
                    $sheet->setCellValueByColumnAndRow(22, $linea, $total_devoluciones);
                    /* ------------------------------------------------------------- */
                    $sheet->setCellValueByColumnAndRow(23, $linea, $insumo->almacene['nombre']);
                    $sheet->setCellValueByColumnAndRow(24, $linea, $ordenTrabajo->orden_trabajos_estado['nombre']);
                    $sheet->setCellValueByColumnAndRow(25, $linea, $ordenTrabajo->user['nombre']); 
                    $linea++;
                }
            }
        }

        foreach (range('C', 'Z') as $columnID) {
            //autodimensionar las columnas
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(8);

        ob_end_clean();

        $time = Time::now();
        $fecha_actual = $time->i18nFormat('yyyy_MM_dd HHmm');
        
        $nombre = 'Listado_VCA_' . $fecha_actual . '.xlsx';
        
        $dir = ROOT.DS.'dataload';
        
        $path = $dir.DS.$nombre;

        $writer->save($path);

        $resultado = [
            'status' => 'success',
            'message' => 'El archivo se creó correctamente.',
            'archivo' => $nombre
        ];
        $this->set('resultado', $resultado);
        
        $this->set('_serialize', 'resultado');
        
        $this->RequestHandler->renderAs($this, 'json');        
    }
    
    /* Recalcular Dosis Aplicada
     * 
     * Toma como parametro y devuelve la dosis real aplicada
     * 
     */
    public function recalcular_dosis_aplicada($id_distribucion = null, $insumo_id = null) {
       
        $ordenTrabajo = $this->OrdenTrabajos->OrdenTrabajosDistribuciones->get($id_distribucion, [
            'contain' => ['OrdenTrabajosInsumos' => ['OrdenTrabajosInsumosEntregas', 'OrdenTrabajosInsumosDevoluciones'], 'OrdenTrabajosCertificaciones']
        ]);
        $has_certificadas = 0;
        $certificaciones = $ordenTrabajo->orden_trabajos_certificaciones;
        foreach($certificaciones as $certificacione){
            $has_certificadas += $certificacione['has'];
        }
        
        $aplicado = 0;
        $insumos = $ordenTrabajo->orden_trabajos_insumos;
        foreach($insumos as $insumo){
            if($insumo->producto_id == $insumo_id){
                /* Sumo todas las entregas */
                foreach($insumo->orden_trabajos_insumos_entregas as $entrega){
                    $aplicado += $entrega->cantidad;
                }
                /*Quito las devoluciones */
                foreach($insumo->orden_trabajos_insumos_devoluciones as $devolucion){
                    $aplicado -= $devolucion->cantidad;
                }
            }
        }
        
        $dosis_aplicada = 0;
        /* Si hay has certificas y hay entregas de productos */
        if (($has_certificadas != 0) && ($aplicado != 0)) {
            $dosis_aplicada = $aplicado / $has_certificadas;
        }
        
        $resultado['aplicado'] = $aplicado;
        $resultado['dosis_aplicada'] = $dosis_aplicada;
        
        /* Devuelvo la dosis calculada real */
        return $resultado;
    }

    /* Carga Tecnica
     * 
     * Si la labor es una siembra, inserta una planilla tecnica en caso de no
     * existir, busca los datos en la tabla ArrozVariedadesEstimaciones y
     * calcula las fechas probables de emergencia e inicio de riego.
     * También busca la semilla para obtener la variedad sembrada y el curado
     * correspondiente.
     */
	
    private function cargatecnica( $id ) {
        
        $this->LoadModel("Tecnicas");
        $this->LoadModel("ArrozVariedadesOracles");
        $this->LoadModel("ArrozVariedadesEstimaciones");
        
        $ordenTrabajo = $this->OrdenTrabajos->get($id, [
            'contain' => ['OrdenTrabajosDistribuciones' => ['ProyectosLabores', 'OrdenTrabajosCertificaciones' ,'Unidades', 'OrdenTrabajosInsumos' => ['OrdenTrabajosInsumosEntregas', 'Productos']]]
        ]);
        
        foreach( $ordenTrabajo->orden_trabajos_distribuciones as $distribucion ) {
            if ( $distribucion->proyectos_labore ) {
                if (in_array($distribucion->proyectos_labore->proyectos_tarea_id, [24, 25])) {
                    $tecnicas = $this->Tecnicas->find('all', [
                        'conditions' => ['lote_id' => $distribucion->lote_id,
                                         'proyecto_id' => $distribucion->proyecto_id]
                        ])->toArray();
                    //SI NO HAY NINGUNA TECNICA PARA ESTE LOTE Y ESTE PROYECTO INSERTA UNA NUEVA
                    if (!$tecnicas) {

                        $tecnica = $this->Tecnicas->newEntity();
                        $tecnica->lote_id = $distribucion->lote_id;
                        $tecnica->proyecto_id = $distribucion->proyecto_id;
                        $tecnica->hectareas_sembradas = $distribucion->superficie;
                        $tecnica->establecimiento_id = $ordenTrabajo->establecimiento_id;

                        $tecnica->fecha_siembra = $distribucion->orden_trabajos_certificaciones[0]->fecha_final;

                        $fechas_estimadas = $this->ArrozVariedadesEstimaciones->find('all', [
                            'conditions' => ['establecimiento_id' => $ordenTrabajo->establecimiento_id, 'mes' => $distribucion->orden_trabajos_certificaciones[0]->fecha_final->month],
                            'fields' => ['emergencia', 'riego']
                        ])->first();
                        
                        if($fechas_estimadas){
                            //ESTIMADO DE EMERGENCIA
                            $emergencia = new Time();
                            $emergencia->setDate($tecnica->fecha_siembra->year,$tecnica->fecha_siembra->month,$tecnica->fecha_siembra->day);
                            $tecnica->fecha_emergencia_estimada = $emergencia->modify('+' . $fechas_estimadas->emergencia . ' days');
                            //ESTIMADO DE INICIO RIEGO
                            $riego = new Time();
                            $riego->setDate($tecnica->fecha_siembra->year,$tecnica->fecha_siembra->month,$tecnica->fecha_siembra->day);
                            $tecnica->fecha_inicioriego_estimada = $riego->modify('+' . $fechas_estimadas->riego . ' days');
                        }
                        //BUSCA EL INSUMO SEMILLA ARROZ PARA INSERTAR VARIEDAD Y CURADO
                        foreach ($distribucion->orden_trabajos_insumos as $insumo) {
							if ($insumo->producto->familia == 'Semillas' && $insumo->producto->subfamilia == 'Arroz') {
                                $variedad = $this->ArrozVariedadesOracles->find('all', [
                                    'conditions' => ['producto_id' => $insumo->producto->id], 'fields' => ['arroz_variedade_id', 'arroz_tipo_curado_id']
                                ])->first();
								
                                $tecnica->arroz_variedade_id = $variedad->arroz_variedade_id;
                                $tecnica->arroz_tipo_curado_id = $variedad->arroz_tipo_curado_id;
                                
								$tecnica->densidad_siembra = $insumo->dosis;
								
                            }
                        }
                        /* Se pone null esta linea por pedido de la gente de campo, para que completen manuelamente la fecha de siembra */
						$tecnica->fecha_siembra = null;
                        $this->Tecnicas->save($tecnica);
						
                    }                    
                    
                }
            }            
        }
    }
    
    /* Una OT de siembra, al certificarse, reparte todos los insumos en partes proporcionales entre
     * las distintas labores.
     * El total de (insumos / hectareas totales) * has certificadas
     */
    public function siembraRepartirInsumos( $id ) {
        
        $ordenTrabajo = $this->OrdenTrabajos->get($id, [
            'contain' => ['OrdenTrabajosDistribuciones' => ['ProyectosLabores', 'OrdenTrabajosCertificaciones', 'Unidades', 'OrdenTrabajosInsumos' => ['OrdenTrabajosInsumosEntregas', 'Productos']],
                          'OrdenTrabajosInsumos' => [ 'conditions' => ['orden_trabajos_distribucione_id' => 0] ,'OrdenTrabajosInsumosEntregas', 'OrdenTrabajosInsumosDevoluciones', 'Productos'],
                          'OrdenTrabajosCertificaciones'
                         ]
        ]);
        $mensajes = [];
        
        /* Verifico que todas las lineas estén certificadas */
        $distribuciones = $ordenTrabajo->orden_trabajos_distribuciones;
        foreach($distribuciones as $distribucion){
            /* Verifico que todas las lineas tengan una certificacion */
            if ( !$distribucion->orden_trabajos_certificaciones ){
                $status = 'error';
                $mensajes[] = 'La labor '.$distribucion->proyectos_labore->nombre.'( '.$distribucion->superficie.' '.$distribucion->unidade->codigo.' ) aún no se ha certificado.';
            }
            /* Ahora verifico los insumos */
            $insumos = $ordenTrabajo->orden_trabajos_insumos;
            if ( !$insumos ) {
                $status = 'error';
                $mensajes[] = 'No hay insumos a distribuir.';
            }
            foreach($insumos as $insumo){
                /* Verico que el insumo tenga al menos una entrega */
                if ( !$insumo->orden_trabajos_insumos_entregas ) {
                    $status = 'error';
                    $mensajes[] = 'El insumo '.$insumo->producto->nombre.' no registra entregas.';
                }
            }
        }
        
        if ( !$mensajes ) {
            $total_hectareas = 0;
            
            /* 
             * Si la OT tiene una sola linea de distribucion, solo tengo que poner 
             * el id de distribucion en las lineas de insumos
             */
            if (count($ordenTrabajo->orden_trabajos_distribuciones) === 1) {
                foreach ( $ordenTrabajo->orden_trabajos_distribuciones as $labor) {
                    $this->OrdenTrabajosInsumos->updateAll( 
                            ['orden_trabajos_distribucione_id' => $labor->id],
                            ['orden_trabajo_id' => $labor->orden_trabajo_id,
                             'orden_trabajos_distribucione_id' => 0]);
                }
            } else {
                /* Tiene muchas lineas de distribucion */
                foreach ( $ordenTrabajo->orden_trabajos_distribuciones as $labor ) {
                    foreach( $labor->orden_trabajos_certificaciones as $certificado ) {
                        $total_hectareas += $certificado->has;
                    }
                }

                foreach ( $ordenTrabajo->orden_trabajos_distribuciones as $labor ) {
                    $cantidad_certificada = 0;
                    foreach( $labor->orden_trabajos_certificaciones as $certificado ) {
                        $cantidad_certificada += $certificado->has;
                    }
                    /* Tengo la cantidad certificada en has y tengo el total certificado */
                    foreach( $ordenTrabajo->orden_trabajos_insumos as $insumo ) {
                        $aplicado = $this->calcularAplicado( $insumo );

                        if ($cantidad_certificada !== 0 ) {
                            $dosis_calculada = ($aplicado / $total_hectareas);
                            $dosis_aplicada = $dosis_calculada * $cantidad_certificada;
                            $dosis = round( $dosis_aplicada, 2);
                            $dosis_ordenada = $insumo->dosis * $labor->superficie;



                            $insumo_nuevo = $this->OrdenTrabajosInsumos->newEntity();

                            $insumo_nuevo->orden_trabajo_id = $insumo->orden_trabajo_id;
                            $insumo_nuevo->productos_lote_id = $insumo->productos_lote_id;
                            $insumo_nuevo->producto_id = $insumo->producto_id;
                            $insumo_nuevo->orden_trabajos_distribucione_id = $labor->id;
                            $insumo_nuevo->dosis = $insumo->dosis;
                            $insumo_nuevo->cantidad = round($dosis_ordenada, 2);
                            $insumo_nuevo->unidade_id = $insumo->unidade_id;
                            $insumo_nuevo->cantidad_stock = 1;
                            $insumo_nuevo->utilizado = 0;
                            $insumo_nuevo->dosis_aplicada = $dosis;
                            $insumo_nuevo->almacene_id = $insumo->almacene_id;

                            if ( $this->OrdenTrabajosInsumos->save( $insumo_nuevo ) ) {
                                /* Ya tengo el insumo guardado */
                                $entrega = $this->OrdenTrabajosInsumosEntregas->newEntity();
                                $entrega->fecha = $insumo_nuevo->created;
                                $entrega->orden_trabajos_insumo_id = $insumo_nuevo->id;
                                $entrega->producto_id = $insumo_nuevo->producto_id;
                                $entrega->unidade_id = $insumo_nuevo->unidade_id;
                                $entrega->cantidad = $insumo_nuevo->dosis_aplicada;
                                $entrega->almacene_id = $insumo_nuevo->almacene_id;
                                $entrega->observaciones = "OT Siembra " . $ordenTrabajo->id;
                                $entrega->user_id = $ordenTrabajo->user_id;

                                $this->OrdenTrabajosInsumosEntregas->save ( $entrega );
                            }
                        }
                    }
                }

                /* Ahora elimino la linea de entregas de siembra */
                foreach( $ordenTrabajo->orden_trabajos_insumos as $insumo ) {
                    $this->OrdenTrabajosInsumos->EliminarInsumo( $insumo->id );
                }
            }
            $status = 'success';
        }
            
        $datos['status'] = $status;
        $datos['message'] = $mensajes;

        $this->set(compact('datos'));
        $this->set('_serialize', 'datos');
        
        $this->RequestHandler->renderAs($this, 'json');
    }
    
    /* Se le pasa un array de OrdenTrabajoInsumos y devuelve lo aplicado */
    private function calcularAplicado ( $insumo ) {
        $aplicado = 0;
        /* Sumo las entregas */
        foreach($insumo->orden_trabajos_insumos_entregas as $entrega){
            $aplicado += $entrega->cantidad;
        }
        /* Quito las devoluciones */
        foreach($insumo->orden_trabajos_insumos_devoluciones as $devolucion){
            $aplicado -= $devolucion->cantidad;
        }
        return $aplicado;
    }
    
    private function insumosACertificar( $id ) {
        
        $has_certificadas = 0;
        $ordenTrabajos = $this->OrdenTrabajos->get($id, [
            'contain' => (['OrdenTrabajosDistribuciones' => ['ProyectosLabores',
                                                             'Lotes',
                                                             'Unidades',
                                                             'Proyectos',
                                                             'Monedas',
                                                             'OrdenTrabajosCertificaciones'
                                                            ],
                           'OrdenTrabajosInsumos' => ['conditions' => ['orden_trabajos_distribucione_id' => 0], //'finder' => ['all' => ['withDeleted']], 
                                                      'Productos',
                                                      'ProductosLotes',
                                                      'Unidades',
                                                      'Almacenes']
                        ])
        ]);        
        //SE CALCULA EL MONTO TOTAL CERTIFICADO
        $distribuciones = $ordenTrabajos->orden_trabajos_distribuciones;
        foreach ($distribuciones as $distribucion){
            $monto = 0; $has = 0;
            foreach ($distribucion->orden_trabajos_certificaciones as $certificacion){
                $monto += $certificacion->has * $certificacion->precio_final;
                $has += $certificacion->has;
            }
            $distribucion->total_certificado = $monto;
            $distribucion->hascertificadas = $has;
            
            $distribucion->importe_certificado = 0;
            if ($has !== 0){
                $certificado = $monto / $has;
                $distribucion->importe_certificado = round( $certificado, 2 );
            }
            $has_certificadas += $has;
        }
        foreach ($ordenTrabajos->orden_trabajos_insumos as $insumo) {
           // if ( $insumo->orden_trabajos_distribucione_id = $distribucion->id ) {
                $entregado = 0;
                $entregado = $this->OrdenTrabajosInsumos->find('entregas', ['IdInsumos' => $insumo->id])->entregas;

                $devolucion = 0;
                $devolucion = $this->OrdenTrabajosInsumos->find('devoluciones', ['IdInsumos' => $insumo->id])->devoluciones;


                $insumo->utilizado = round($entregado - $devolucion, 3); /* Este valor es el utilizado real */
                $insumo->entrega = round($entregado, 3);
                $insumo->devolucion = round($devolucion, 3);
                $insumo->dosis_aplicada = 0;
                if($has_certificadas != 0){
                    $insumo->dosis_aplicada = round($insumo->utilizado / $has_certificadas, 3);
                }
           // }
        }
        return $ordenTrabajos;
    }
    
    /**
     * Consulto el histórico en oracle de la OT
     * Si encuentro la orden de compra y no está asociada a la ot, lo asocio
     * 
     * @param type $orden_trabajo
     * return array Historico
     */
    public function consultarHistoricoOracle( $orden_trabajo = null ) {
        $data = [];
        $orden_compra = '';
        
        $connection = ConnectionManager::get('oracle');
        
        $strsql = " SELECT his.sequence_num,
                        his.action_date,
                        his.action_code_dsp,
                        his.employee_name,
                        his.note,
                        po.segment1 OC,
                        po.attribute1 OT
                 FROM apps.PO_ACTION_HISTORY_V his,
                      apps.po_headers_all po
                 WHERE 1=1
                    AND his.object_id = po.po_header_id
                    AND his.object_id IN (
                                  SELECT PO.PO_HEADER_ID
                                  FROM apps.po_headers_all po
                                  where 1=1
                                     AND po.cancel_flag != 'Y'
                                     AND po.attribute1 = '".$orden_trabajo."'
                                 )
                 AND his.object_type_code = 'PO'
                 order by his.sequence_num";
        
        $results =  $connection->execute($strsql);
        foreach ($results->fetchAll('assoc') as $result){
            if( $result ) {
                /* Guardo los datos en la tabla */
                $data[] = $result;
                $orden_compra = $result['OC'];
            }
        }
        
        /* Hay una orden de compra asociada */
        if ($orden_compra) {
            /* Reviso si la OT tiene completo este dato */
            $ordenTrabajo = $this->OrdenTrabajos->get($orden_trabajo);
            if (!$ordenTrabajo->oc) {
                /* No tiene la orden de compra asociada, así que completo el dato */
                $ordenTrabajo->oc = $orden_compra;
                
                $this->OrdenTrabajos->save($ordenTrabajo);
            }
        }
        
        $this->set('data', $data);
        $this->set('_serialize', 'data');
        $this->RequestHandler->renderAs($this, 'json');
    }
    
    
   /*
    *  Agrego las OT al alquiler
    */
    private function procesarAlquileres( $ordenTrabajo = null, $user_id = null ) {
        $distribuciones = $this->OrdenTrabajosDistribuciones->find('all', [
            'contain' => ['OrdenTrabajosDistribucionesTarifarios', 'OrdenTrabajosCertificaciones'],
            'conditions' => ['OrdenTrabajosDistribuciones.orden_trabajo_id' => $ordenTrabajo->id]
        ]);
        
        /* Reviso si tengo que crear una OT de alquiler */
        if ($this->verificarOrdenTrabajoAlquiler($ordenTrabajo)) {
            
            $ordenTrabajo_copia = $this->verificarExistenciaAlquiler($ordenTrabajo->id);
            
            /* revisamos el estado de la OT de alquiler */
            if ($ordenTrabajo_copia) {
                /* La OT de alquiler existe, así que antes de actualizarla, la marco como ya certificada */
                $ordenTrabajo_copia->orden_trabajos_estado_id = '4';
            } else {
                /* Genero una OT nueva de alquiler */
                $ordenTrabajo_copia = $this->OrdenTrabajos->duplicate($ordenTrabajo->id);
                $ordenTrabajo_copia->observaciones = 'Alquiler de Implemento de la OT '. $ordenTrabajo->id;
                $ordenTrabajo_copia->proveedore_id = $this->proveedorOrdenTrabajoAlquiler($ordenTrabajo);
                $ordenTrabajo_copia->user_id = $user_id;
            }
            $this->OrdenTrabajos->save($ordenTrabajo_copia);
            
            /* Ahora copio todas las lineas de distribucion */
            foreach ($distribuciones as $distribucion) {
                if ($distribucion->orden_trabajos_distribuciones_tarifario) {
                    if ($distribucion->orden_trabajos_distribuciones_tarifario->alquiler == '1' && $distribucion->orden_trabajos_distribuciones_tarifario->porcentaje && $distribucion->orden_trabajos_distribuciones_tarifario->proveedore_id) {
                        
                        /* Calculo el porcentaje a aplicar */
                        $alquiler_labor = $distribucion->proyectos_labore_id;
                        $alquiler_establecimiento = $ordenTrabajo->establecimiento_id;
                        $alquiler_proveedor = $ordenTrabajo_copia->proveedore_id;
                        $alquiler_unidad = $distribucion->unidade_id;
                        
                        $tarifa_para_alquiler = $this->ProyectosLaboresTarifarios->ConsultarTarifa($alquiler_labor, $alquiler_establecimiento, $alquiler_proveedor, $alquiler_unidad );
                        
                        $importe_tarifa = '0';
                        
                        /* Busco una linea de distribucion de la copia, filtrando la linea por Proyecto / Labor / Lote */
                        $distribucion_copia = $this->OrdenTrabajosDistribuciones->find('all', [
                            'conditions' => ['orden_trabajo_id' => $ordenTrabajo_copia->id,
                                             'proyectos_labore_id' => $distribucion->proyectos_labore_id,
                                             'proyecto_id' => $distribucion->proyecto_id,
                                             'lote_id' => $distribucion->lote_id]
                        ])->first();
                        
                        /* Si NO existe una linea de distribucion, agrego una */
                        if (!$distribucion_copia) {
                            $distribucion_copia = $this->OrdenTrabajosDistribuciones->duplicate($distribucion->id);
                            $distribucion_copia->orden_trabajo_id = $ordenTrabajo_copia->id;
                            $distribucion_copia->unidade_id = '73'; /* Unidad */
                            $distribucion_copia->superficie = '1';                            
                        }
                        
                        if ($tarifa_para_alquiler) {
                            /* Si la tarifa es en UTAs y hay una UTA definida */
                            if ($tarifa_para_alquiler[0]['uta'] && $tarifa_para_alquiler[0]['valor_uta']) {
                                $importe_tarifa_pesos = $tarifa_para_alquiler[0]['uta'] * $tarifa_para_alquiler[0]['valor_uta']['valor_uta'];
                                
                                /* Calculo el porcentaje */
                                try {
                                    $importe_tarifa = ($importe_tarifa_pesos / 100) * $distribucion->orden_trabajos_distribuciones_tarifario->porcentaje;
                                } catch (Exception $e) {
                                    $importe_tarifa = '0';
                                }                                
                                
                            } elseif ($tarifa_para_alquiler[0]['tarifa']) {
                                /* Si la tarifa es un importe en pesos */
                                $importe_tarifa_pesos = $tarifa_para_alquiler[0]['tarifa'];
                                /* Calculo el porcentaje */
                                try {
                                    $importe_tarifa = ($importe_tarifa_pesos / 100) * $distribucion->orden_trabajos_distribuciones_tarifario->porcentaje;
                                } catch (Exception $e) {
                                    $importe_tarifa = '0';
                                }    
                            } else {
                                /* No hay ningun importe definido */
                                $importe_tarifa = '0';
                            }
                            
                            /* Si no hay valor UTA ni Tarifa pero hay ImporteRangoHp */
                            if (!$tarifa_para_alquiler[0]['uta'] && !$tarifa_para_alquiler[0]['tarifa'] && $tarifa_para_alquiler[0]['importe_rango_hp']) {
                                $importe_tarifa_pesos = $tarifa_para_alquiler[0]['importe_rango_hp'] * $tarifa_para_alquiler[0]['valor_uta']['valor_uta'];
                                /* Calculo el porcentaje */
                                try {
                                    $importe_tarifa = ($importe_tarifa_pesos / 100) * $distribucion->orden_trabajos_distribuciones_tarifario->porcentaje;
                                } catch (Exception $e) {
                                    $importe_tarifa = '0';
                                }                                
                            }
                            $importe_total = $importe_tarifa * $distribucion->total_certificado;
                        } else {
                            $importe_total = '0';
                        }
                        
                        $distribucion_copia->importe = round($importe_total, 2);
                        
                        $this->OrdenTrabajosDistribuciones->save($distribucion_copia);
                        
                        /* Busco una certificacion para esta distribucion */
                        $ordenTrabajosCertificacione = $this->OrdenTrabajosCertificaciones->find('all', [
                            'conditions' => ['orden_trabajo_id' => $ordenTrabajo_copia->id,
                                             'orden_trabajos_distribucione_id' => $distribucion_copia->id]
                        ])->first();
                        
                        if (!$ordenTrabajosCertificacione) {
                            /* Creo una certificacion a hoy del alquiler */
                            $ordenTrabajosCertificacione = $this->OrdenTrabajosCertificaciones->newEntity();
                            $ordenTrabajosCertificacione->orden_trabajos_distribucione_id = $distribucion_copia->id;
                            $ordenTrabajosCertificacione->orden_trabajo_id = $ordenTrabajo_copia->id;
                            $ordenTrabajosCertificacione->fecha_inicio = $distribucion_copia->created;
                            $ordenTrabajosCertificacione->fecha_final = $distribucion_copia->created;
                            $ordenTrabajosCertificacione->has = '1';
                            $ordenTrabajosCertificacione->moneda_id = $distribucion_copia->moneda_id;
                            $ordenTrabajosCertificacione->tipo_cambio = '1';
                            $ordenTrabajosCertificacione->observaciones = 'Alquiler de Implemento de la OT '. $ordenTrabajo->id;
                            $ordenTrabajosCertificacione->user_id = $user_id;
                        }
                        
                        /* Si ya existe, solo modifico el importe */
                        $ordenTrabajosCertificacione->precio_final = $distribucion_copia->importe;                        
                        
                        if ($this->OrdenTrabajosCertificaciones->save($ordenTrabajosCertificacione)) {
                            /* Busco la certificacion de la linea de distribucion y le saco el porcentaje */

                        } else {
                            die(debug( $ordenTrabajosCertificacione ));
                        }
                       
                        /* Completo el numero de OT en la linea de OrdenTrabajosDistribucionesTarifarios */
                        $distribucion_tarifario = $this->OrdenTrabajosDistribucionesTarifarios->get($distribucion->orden_trabajos_distribuciones_tarifario->id);
                        $distribucion_tarifario->orden_trabajo_alquiler_id = $ordenTrabajo_copia->id;
                        $this->OrdenTrabajosDistribucionesTarifarios->save($distribucion_tarifario);
                        
                    }
                }
            }
            
            /* Marcamos para reprocesar las OT */
            if ($ordenTrabajo_copia->oracle_oc_flag == 'Y') {
                $ordenTrabajo_copia->oracle_oc_flag = 'R';
                
                /* Marco las Certificaciones para reprocesar */
                $this->OrdenTrabajosCertificaciones->updateAll(
                    ['oracle_flag' => 'R'],
                    ['orden_trabajo_id' => $ordenTrabajo_copia->id]
                );
            }
        }

    }
    
    /*
     * Verifico si tengo que generar una OT de alquiler de Implemento
     * 
     * Devuelvo true si tengo que crear
     */
    private function verificarOrdenTrabajoAlquiler ($ordenTrabajo = null) {
        
        $distribuciones = $this->OrdenTrabajosDistribuciones->find('all', [
            'contain' => ['OrdenTrabajosDistribucionesTarifarios'],
            'conditions' => ['OrdenTrabajosDistribuciones.orden_trabajo_id' => $ordenTrabajo->id
                ]
        ]);
        foreach ($distribuciones as $distribucion) {
            if ($distribucion->orden_trabajos_distribuciones_tarifario) {
                if ($distribucion->orden_trabajos_distribuciones_tarifario->alquiler == '1' && $distribucion->orden_trabajos_distribuciones_tarifario->porcentaje && $distribucion->orden_trabajos_distribuciones_tarifario->proveedore_id) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /*
     * Averiguo el Proveedor de la Orden de Trabajo de alquiler
     */
    private function proveedorOrdenTrabajoAlquiler ($ordenTrabajo = null) {
        $distribuciones = $this->OrdenTrabajosDistribuciones->find('all', [
            'contain' => ['OrdenTrabajosDistribucionesTarifarios'],
            'conditions' => ['OrdenTrabajosDistribuciones.orden_trabajo_id' => $ordenTrabajo->id
                ]
        ]);
        foreach ($distribuciones as $distribucion) {
            if ($distribucion->orden_trabajos_distribuciones_tarifario) {
                if ($distribucion->orden_trabajos_distribuciones_tarifario->alquiler == '1' && $distribucion->orden_trabajos_distribuciones_tarifario->porcentaje && $distribucion->orden_trabajos_distribuciones_tarifario->proveedore_id) {
                    return $distribucion->orden_trabajos_distribuciones_tarifario->proveedore_id;
                }
            }
        }
        return false;
    }
    
    /**
     * Verifico si existe una OT de alquiler en estado Certificado
     * 
     * Si existe OT de alquiler:
     *    - Si esta en estado 4 (Certificado), devuelve false (no permite edicion)
     *    - Si está en otro estado, permite la edicion
     * 
     * @param type $value
     * @return type boolean Devuelve si existe una OT de alquiler
     */
    private function verificarExistenciaAlquiler ($orden_trabajo_id = null) {
        $distribuciones = $this->OrdenTrabajosDistribuciones->find('all', [
            'contain' => ['OrdenTrabajosDistribucionesTarifarios'],
            'conditions' => ['OrdenTrabajosDistribucionesTarifarios.orden_trabajo_alquiler_id IS NOT NULL', 'orden_trabajo_id' => $orden_trabajo_id]
        ]);
        foreach ($distribuciones as $distribucion) {
            /* Primero chequeo las certificadas*/
            $orden = $this->OrdenTrabajos->find('all', [
                'conditions' => ['id' => $distribucion->orden_trabajos_distribuciones_tarifario->orden_trabajo_alquiler_id,
                                 'orden_trabajos_estado_id' => '4']
            ])->toArray();
            
            /* Si existe una OT de alquiler en estado Certificado, salgo */
            if ($orden) {
                return true;
            }
            
            /* Ahora chequeo las que estan en otro estado distinto a la anulada */
            $orden = $this->OrdenTrabajos->find('all', [
                'conditions' => ['id' => $distribucion->orden_trabajos_distribuciones_tarifario->orden_trabajo_alquiler_id,
                                 'orden_trabajos_estado_id <>' => '5']
            ])->first();
            if ($orden) {
                return $orden;
            }
        }
        return false;
    }
    
    /**
     * Devuelvo el costo del insumo en la fecha especificada.
     * 
     * Se conecta al datawarehouse, y pasando los parametros de organizacion, producto y fecha
     * devuelve el costo.
     * 
     * @param type $organizacion
     * @param type $producto
     * @param type $fecha Formato yyyymm Ejemplo: 202012
     * @param type $intento Si es 0 busca en la fecha pasada, sino, busca en el mes anterior
     * @return real Devuelve el costo valorizado para esa fecha
     */
    private function ValorizarProducto($organizacion = null, $producto = null, $fecha = null, $intento = 0) {
        $connection = ConnectionManager::get('datawarehouse');
        
        $fecha_movimiento = $fecha->i18nFormat('yyyyMM');
        
        $strsql = "SELECT SUM(ComponenteCostoReal) as costo FROM Info.Costos"
                . " WHERE ProductoCodigo  = '" . $producto . "'"
                . "   and OrganizacionCodigo = '" . $organizacion . "'"
                . "   and Convert(CHAR(6),CostoPeriodo,112) = '" . $fecha_movimiento ."'";

        $results =  $connection->execute($strsql)->fetchAll('assoc');
        
        /* Si tengo el costo, lo devuelvo */
        if ($results[0]['costo']) {
            return $results[0]['costo'];
        }
        
        /* No encontró la fecha en el periodo, asi que busco la fecha del periodo anterior  */
        if ($intento > 0 ) {
            /* Ya estoy buscando la fecha anterior y no encontré nada, asi que salgo */
            return false;
        }
        
        /* Saco un mes a la fecha pasada */
        $fecha_anterior = $fecha->subMonths(1);
        
        /* Utilizo en forma recursiva la funcion */
        return $this->ValorizarProducto($organizacion, $producto, $fecha_anterior, 1);
    }
    
    /**
     * Busco los lotes asignados al usuario.
     * 
     * Si no está en la lista, agrego los lotes de la OT y devuelvo un array
     * con los lotes con el siguiente esquema.
     * 
     * id, nombre, has, establecimiento, establecimiento_id, sector
     * 
     * Con eso lo dibujamos en la vista y evitamos el error de cambio de lote al
     * editar una OT.
     * 
     * @param type $ordenTrabajo
     */
    private function VerificarExistenciaLote($ordenTrabajo = null)
    {
        $tecnica_responsables = $this->Tecnicaresponsables->find('all', [
            'fields' => ['lote_id'],
            'contain' => ['Lotes'],
            'conditions' => ['user_id' => $this->request->session()->read('Auth.User.id'),
                             'Lotes.establecimiento_id' => $ordenTrabajo->establecimiento_id],
            'sort' => ['TecnicaResponsables.lote_id' => 'ASC']
        ]);
        
        $lotes_disponibles = Hash::extract($tecnica_responsables->toArray(), '{n}.lote_id');
        
        /* Ahora reviso los lotes que estan en la distribución, si NO están, los agrego */
        foreach ($ordenTrabajo->orden_trabajos_distribuciones as $distribucion) {
            if (!in_array($distribucion->lote_id, $lotes_disponibles)) {
                $lotes_disponibles[] = $distribucion->lote_id;
            }
        }
        
        /* Si el lote "Sin Lote" no se encuentra, lo agrego */
        if (!in_array(0, $lotes_disponibles)) {
            $lotes_disponibles[] = '0';
        }
        
        /*  Ahora devuelvo los lotes, los que tenía asignado el usuario + los lotes
            de las lineas de distribucion, o sea, las que estaban en la OT */
        $lotes = $this->Lotes->find('all', [
            'fields' => ['Lotes.id', 'Lotes.nombre', 'has' => 'Lotes.hectareas_reales', 'establecimiento' => 'Establecimientos.nombre', 'establecimiento_id' => 'Establecimientos.id',
                         'sector' => 'Sectores.nombre'],
            'contain' => ['Sectores' => 'Establecimientos'],
            'conditions' => ['Lotes.id IN' => $lotes_disponibles]
        ]);
        
        return $lotes;
    }
    
    /**
     * NotificarOtProveedor
     * Envio correo electronico con archivo pdf adjunto al correo registrado del proveedor
     * 
     */
    public function NotificarOtProveedor($orden_trabajo_id = null) {
        
        $respuesta = $this->OrdenTrabajos->EnviarEmailProveedor($orden_trabajo_id);
        
        $this->set([
            'response' => $respuesta,
            '_serialize' => 'response',
        ]);

        return $this->RequestHandler->renderAs($this, 'json');
    }


}

// Convertir un string "1/123" a su representación float
function exif_float($value) {
    $pos = strpos($value, '/');
    if ($pos === false)
        return (float) $value;
    $a = (float) substr($value, 0, $pos);
    $b = (float) substr($value, $pos + 1);
    return ($b == 0) ? ($a) : ($a / $b);
}

