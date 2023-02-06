<?php
namespace Ordenes\Controller;

use Ordenes\Controller\AppController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\Time;
use Cake\Filesystem\Folder;
use Cake\Filesystem\File;

/**
 * OrdenTrabajosDataloads Controller
 *
 * @property \App\Model\Table\OrdenTrabajosDataloadsTable $OrdenTrabajosDataloads
 *
 * @method \App\Model\Entity\OrdenTrabajosDataload[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class OrdenTrabajosDataloadsController extends AppController
{
    public function initialize() {
        parent::initialize();

        $this->loadModel('Ordenes.OrdenTrabajos');
        $this->loadModel('Ordenes.OrdenTrabajosDistribuciones');
        $this->loadModel('Ordenes.OrdenTrabajosInsumos');
        $this->loadModel('Ordenes.OrdenTrabajosInsumosEntregas');
        $this->loadModel('Ordenes.OrdenTrabajosInsumosDevoluciones');
        $this->loadModel('ProductosExistencias');
    }
    /**
     * Index method
     *
     * @return \Cake\Http\Response|void
     */
    public function index()
    {
        switch ($this->request->session()->read('Auth.User.group_id')) {
            case 1: /* Los Developers ven todo */
                $Dataloads = $this->OrdenTrabajosDataloads->find('all',[
                    'contain' => ['Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']]],
                    'limit' => 200,
                    'order' => ['OrdenTrabajosDataloads.id' => 'DESC']
                ] );
                break;
            case 3: /* Administrativos - Ven sus dataloads */
                $Dataloads = $this->OrdenTrabajosDataloads->find('all',[
                    'contain' => ['Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']]],
                    'limit' => 200,
                    'conditions' => ['OrdenTrabajosDataloads.user_id' => $this->request->session()->read('Auth.User.id')],
                    'order' => ['OrdenTrabajosDataloads.id' => 'DESC']
                ] );
                break;
            default: /* Aqui deberian llegar los ingenieros */
                /* Verifico si es un Encargado de Agricultura */
                if ($this->request->session()->read('Auth.User.role_id') == 10) {
                    $Dataloads = $this->OrdenTrabajosDataloads->find('all',[
                        'contain' => ['Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']]],
                        'limit' => 200,
                        'conditions' => ['OrdenTrabajosDataloads.user_id' => $this->request->session()->read('Auth.User.id')],
                        'order' => ['OrdenTrabajosDataloads.id' => 'DESC']
                    ] );
                }
                break;
        }                

        $this->set(compact('Dataloads'));
    }

    /**
     * View method
     *
     * @param string|null $id Orden Trabajos Dataload id.
     * @return \Cake\Http\Response|void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $ordenTrabajosDataload = $this->OrdenTrabajosDataloads->get($id, [
            'contain' => ['Users', 'OrdenTrabajos' => ['Establecimientos', 'Proveedores', 'Users']]
        ]);
        
        $this->set('ordenTrabajosDataload', $ordenTrabajosDataload);
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $this->loadModel('OrdenTrabajos');
        $ordenTrabajos = $this->OrdenTrabajos->find('all', [
            'contain' => ['Establecimientos',
                          'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['nombre']],
                          'Proveedores', 'OrdenTrabajosEstados', 'OrdenTrabajosDistribuciones' => 'Lotes'],
            'conditions' => ['OrdenTrabajos.orden_trabajos_estado_id ' => 4, 'Establecimientos.id IN' => $this->request->session()->read('Auth.User.establecimientos'),
                    'OrdenTrabajos.orden_trabajos_dataload_id is NULL', 'OrdenTrabajos.oracle_oc_flag is NULL']
        ]);
        
        $this->set(compact('ordenTrabajos'));
        
    }
    public function generardataload(){
        
        $this->loadModel('OrdenTrabajos');
        
        $data = $this->request->data;
        
        $ordenes = $data['valores'];
        $certificaciones = [];
        
        if (is_array($ordenes) && count($ordenes) > 0){
            $ordenTrabajos = $this->OrdenTrabajos->find('all', [
                'contain' => ['Establecimientos',
                              'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['nombre']],
                              'Proveedores' => ['fields' => ['nombre']],
                              'OrdenTrabajosEstados' => ['fields' => ['nombre']],
                              'OrdenTrabajosDistribuciones' => [
                                                                    'ProyectosLabores' => 'ProyectosTareas',
                                                                    'Unidades' => ['fields' => ['nombre']],
                                                                    'Lotes' => ['Sectores'],
                                                                    'Proyectos'
                                                                ],
                              'OrdenTrabajosInsumos'=> [
                                                            'Productos',
                                                            'Unidades'=> ['fields' => ['nombre']],
                                                            'Almacenes' => ['fields' => ['nombre']],
                                                            'OrdenTrabajosInsumosDevoluciones',
                                                            'OrdenTrabajosInsumosEntregas'
                                                        ],
                              'OrdenTrabajosCertificaciones' => ['Users' => ['fields' => ['nombre']]]
                    ],
                'conditions' => ['OrdenTrabajos.id IN ' => $ordenes] //, 'OrdenTrabajos.orden_trabajos_dataload_id IS NULL'
            ]);
            
            /* Verifico que estén todos bien certificados */
            foreach($ordenTrabajos as $ordenTrabajo){
                if (empty($ordenTrabajo->orden_trabajos_certificaciones)){
                    $certificaciones[] = $ordenTrabajo->id;
                }
            }
            
            if( sizeof($certificaciones) == 0 ) { 
                if (isset($data['dataload'])) {
                    $id = $data['dataload'];
                    
                    /* Busco el archivo */
                    $dataload = $this->OrdenTrabajosDataloads->get($id);
                    
                    $archivo = $dataload->nombre;
                    //$path = $dataload->path;

                    $dir = ROOT.DS.'dataload';

                    /* Si la carpeta no existe, lo creamos */
                    if (!file_exists($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    /* $dir = new Folder(ROOT.DS.'dataload', true, 0755); */
                    $path = $dir.DS.$archivo.'.xlsx';
                    
                } else {
                    $date = Time::now();
                    $fecha = $date->format('Ymd');

                    $ordenTrabajosDataload = $this->OrdenTrabajosDataloads->newEntity();
                    $ordenTrabajosDataload->fecha = $date;
                    $ordenTrabajosDataload->nombre = '';
                    $ordenTrabajosDataload->path = '';
                    $ordenTrabajosDataload->user_id =$this->request->session()->read('Auth.User.id');;

                    if ($this->OrdenTrabajosDataloads->save($ordenTrabajosDataload)) {
                        /* Ya he guardado el dataload, asi que averiguo el ID */
                        $id = $ordenTrabajosDataload->id;

                        /* Armo el nombre y el path */
                        $nombre = 'dataload_'.$id.'_'.$fecha;
                        $dir = ROOT.DS.'dataload';

                        /* Si la carpeta no existe, lo creamos */
                        if (!file_exists($dir)) {
                            mkdir($dir, 0755, true);
                        }

                        /* $dir = new Folder(ROOT.DS.'dataload', true, 0755); */
                        $path = $dir.DS.$nombre.'.xlsx';

                        $archivo = $nombre.'.xlsx';

                        /* Guardo el nombre y la ruta */
                        $ordenTrabajosDataload->nombre = $archivo;
                        $ordenTrabajosDataload->path = $path;

                        $this->OrdenTrabajosDataloads->save($ordenTrabajosDataload);

                        /* Marco todas las OT a este dataload */
                        $dataload_guardado = $this->marcar_ot_dataload($ordenes, $id);
                    }
                }
                    
                //die(debug($archivo));
                    /* Ahora, envio los datos para crear el excel */
                    $resultado = $this->generar_excel($ordenTrabajos, $id, $path, $archivo);

                    $this->set('resultado', $resultado);

            } else {
                $data = [
                    'status' => 'error',
                    'message' => 'Faltan certificaciones.',
                    'data' => $certificaciones
                ];                
                $this->set('resultado', $data);
            }
        }
       }
    /**
     * Edit method
     *
     * @param string|null $id Orden Trabajos Dataload id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Network\Exception\NotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $ordenTrabajosDataload = $this->OrdenTrabajosDataloads->get($id, [
            'contain' => []
        ]);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $ordenTrabajosDataload = $this->OrdenTrabajosDataloads->patchEntity($ordenTrabajosDataload, $this->request->getData());
            if ($this->OrdenTrabajosDataloads->save($ordenTrabajosDataload)) {
                $this->Flash->success(__('The orden trabajos dataload has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The orden trabajos dataload could not be saved. Please, try again.'));
        }
        $users = $this->OrdenTrabajosDataloads->Users->find('list', ['limit' => 200]);
        $this->set(compact('ordenTrabajosDataload', 'users'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Orden Trabajos Dataload id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $ordenTrabajosDataload = $this->OrdenTrabajosDataloads->get($id);
        if ($this->OrdenTrabajosDataloads->delete($ordenTrabajosDataload)) {
            $this->Flash->success(__('The orden trabajos dataload has been deleted.'));
        } else {
            $this->Flash->error(__('The orden trabajos dataload could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
    /*
     * Marco todas las OT,s con le Id del dataload
     */
    public function marcar_ot_dataload($lista, $id){
         $query = $this->OrdenTrabajos->updateAll(
                 ['orden_trabajos_dataload_id' => $id],
                 ['id IN' => $lista]);
    }

    /* 
     * Consulto todas las OT del dataload para traer la OC
     * 
     */
    public function consultaroracle($ordenTrabajos = null){
        
        $this->loadModel('OrdenTrabajos');
        
        $request = $this->request->data;
        $datos = $request['valores'];

        $connection = ConnectionManager::get('oracle');

        /* Recorro todas las OT */
        foreach($datos as $dato){
            
            $ordenTrabajo = $this->OrdenTrabajos->get($dato);
            
            /* Busco la OC */
            $strsql = "SELECT poh.SEGMENT1 as oc FROM apps.po_headers_all poh WHERE poh.attribute1 = '". $dato. "'";

            $results =  $connection->execute($strsql);
          
            foreach ($results->fetchAll('assoc') as $result){
                if(!empty($result)){
                    /* Si encuentro la OC en Oracle, lo guardo en la OT de ElAgronomo */
                    $ordenTrabajo->oc = $result['OC'];
                    $this->OrdenTrabajos->save($ordenTrabajo);
                }
            }
        }
        $data = [
            'status' => 'success',
            'message' => 'Se han consultado los datos en Oracle.',
            'data' => ''
        ];                

        $this->set('resultado', $data);        
    }
    
    public function generar_excel($ordenTrabajos = null, $id = null, $path = null, $archivo = null){
        $tab = '\{TAB}';
        $enter = '\{ENTER}';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Ordenes de Trabajo');

        $writer = new Xlsx($spreadsheet);
        
        /* Escribo los encabezados */
        $sheet->setCellValue('A1', 'UNID.OP.');
        $sheet->setCellValue('B1', 'TIPO');
        $sheet->setCellValue('C1', 'PROVEEDOR');
        $sheet->setCellValue('D1', 'X');
        $sheet->setCellValue('E1', 'X');
        $sheet->setCellValue('F1', 'X');
        $sheet->setCellValue('G1', 'ENVIO');
        $sheet->setCellValue('H1', 'X');
        $sheet->setCellValue('I1', 'FACTURACION');
        $sheet->setCellValue('J1', 'X');
        $sheet->setCellValue('K1', 'X');
        $sheet->setCellValue('L1', 'DESCRIPCION');
        $sheet->setCellValue('M1', 'X');
        $sheet->setCellValue('N1', 'Nº OT');
        $sheet->setCellValue('O1', 'X');
        $sheet->setCellValue('P1', 'X');
        $sheet->setCellValue('Q1', 'TIPO');
        $sheet->setCellValue('R1', 'X');
        $sheet->setCellValue('S1', 'X');
        $sheet->setCellValue('T1', 'CATEGORIA');
        $sheet->setCellValue('U1', 'X');
        $sheet->setCellValue('V1', 'DESCRIPCION');
        $sheet->setCellValue('W1', 'X');
        $sheet->setCellValue('X1', 'UDM');
        $sheet->setCellValue('Y1', 'X');
        $sheet->setCellValue('Z1', 'CANTIDAD');
        $sheet->setCellValue('AA1', 'X');
        $sheet->setCellValue('AB1', 'PRECIO');
        $sheet->setCellValue('AC1', 'X');
        $sheet->setCellValue('AD1', 'PACTADO');
        $sheet->setCellValue('AE1', 'X');
        $sheet->setCellValue('AF1', 'NECESARIO');
        $sheet->setCellValue('AG1', 'ENVIO');
        $sheet->setCellValue('AH1', 'X');
        $sheet->setCellValue('AI1', 'ORG');
        $sheet->setCellValue('AJ1', 'X');
        $sheet->setCellValue('AK1', 'DISTRIBUCIONES');
        $sheet->setCellValue('AL1', 'IR PROYECTO');
        $sheet->setCellValue('AM1', 'PROYECTO');
        $sheet->setCellValue('AN1', 'X');
        $sheet->setCellValue('AO1', 'TAREA');
        $sheet->setCellValue('AP1', 'X');
        $sheet->setCellValue('AQ1', 'TIPO EROGACION');
        $sheet->setCellValue('AR1', 'X');
        $sheet->setCellValue('AS1', 'ORGANIZACION');
        $sheet->setCellValue('AT1', 'X');
        $sheet->setCellValue('AU1', 'FECHA');
        $sheet->setCellValue('AV1', 'X');
        $sheet->setCellValue('AW1', 'X');
        $sheet->setCellValue('AX1', 'X');
        $sheet->setCellValue('AY1', 'X');
        $sheet->setCellValue('AZ1', 'X');
        $sheet->setCellValue('BA1', 'ESTABLECIMIENTO');
        $sheet->setCellValue('BB1', 'X');
        $sheet->setCellValue('BC1', 'LOTE');
        $sheet->setCellValue('BD1', 'X');
        $sheet->setCellValue('BE1', 'CERRAR');
        $sheet->setCellValue('BF1', 'CERRAR');
        $sheet->setCellValue('BG1', 'X');
        $sheet->setCellValue('BH1', 'GUARDAR');
        $sheet->setCellValue('BI1', 'X');
        $sheet->setCellValue('BJ1', 'APROBAR');
        $sheet->setCellValue('BK1', 'REENVIAR');
        $sheet->setCellValue('BL1', 'X');
        $sheet->setCellValue('BM1', 'X');
        $sheet->setCellValue('BN1', 'X');
        $sheet->setCellValue('BO1', 'X');
        $sheet->setCellValue('BP1', 'APROBADOR');
        $sheet->setCellValue('BQ1', 'X');
        $sheet->setCellValue('BR1', 'ACEPTAR');
        $sheet->setCellValue('BS1', 'X');
        $sheet->setCellValue('BT1', 'X');

        $linea = 2;
        $lineaot = 2;
        
        foreach ($ordenTrabajos as $ot){
            /*
             *  Cada línea de distribucion es una linea nueva en el dataload
             * 
             */

            /* Escribo el Encabezado */
            $header = $this->listaLabores( $ot);
            $descripcion_header = 'OT:_'.$ot->id.'_'.$ot->establecimiento->nombre.$header.$ot->proveedore['nombre'];
            $descripcion = 'OT:_'.$ot->id.'_'.$ot->establecimiento->nombre;
            
            $lineaot = $linea;

            $sheet->setCellValueByColumnAndRow(1, $linea, $tab);
            $sheet->setCellValueByColumnAndRow(2, $linea, $tab);
            $sheet->setCellValueByColumnAndRow(3, $linea, $ot->proveedore['nombre']);
            $sheet->setCellValueByColumnAndRow(4, $linea, $tab);
            $sheet->setCellValueByColumnAndRow(5, $linea, $tab);
            $sheet->setCellValueByColumnAndRow(6, $linea, $tab);
            $sheet->setCellValueByColumnAndRow(7, $linea, $ot->establecimiento['envio']);
            $sheet->setCellValueByColumnAndRow(8, $linea, $tab);
            $sheet->setCellValueByColumnAndRow(9, $linea, $ot->establecimiento['facturacion']);
            $sheet->setCellValueByColumnAndRow(10, $linea, $tab);
            $sheet->setCellValueByColumnAndRow(11, $linea, $tab);
                
            $sheet->setCellValueByColumnAndRow(12, $lineaot, $descripcion_header);
            
            $sheet->setCellValueByColumnAndRow(13, $linea, $tab);
            $sheet->setCellValueExplicitByColumnAndRow(14, $linea, $ot->id, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValueByColumnAndRow(15, $linea, $enter);
            
            
            $distribuciones = $ot->orden_trabajos_distribuciones;
            foreach($distribuciones as $distribucion){
                /* Fecha de Ordenado */
                /* $fechaot = $ot->fecha->i18nFormat('dd/MM/yyyy'); */
            
                /* Cambios 13/06/2019 - La fecha es de la ultima certificacion */
                $fecha_certificacion = $this->OrdenTrabajosDistribuciones->find('FechaCertificacion', ['IdDistribucion' => $distribucion['id']]);
                $fechaot = $fecha_certificacion->fecha_final->i18nFormat('dd/MM/yyyy');
                /*  ----------------------------------------------- */
                /*  Comienzan las lineas de Distribucion            */
                /*  ----------------------------------------------- */
                /* Averiguo el nombre del certificador */
                $certificador = $this->OrdenTrabajosDistribuciones->find('Certificador', ['IdDistribucion' => $distribucion['id']]);

                /* Averiguo el importe certificado, viene como promedio */
                $importe_certificado = $this->OrdenTrabajosDistribuciones->find('ImporteCertificado', ['IdDistribucion' => $distribucion['id']]);
                $certificado = number_format($importe_certificado->importe, 2, ",", "");
                
                $superficie_certificada = $this->OrdenTrabajosDistribuciones->find('SuperficieCertificada', ['IdDistribucion' => $distribucion['id']]);
                $superficie =  number_format($superficie_certificada->superficie, 2, ",", "");

                if ( $this->request->session()->read('Auth.User.grupo_negocio') == 2) {
                    $descripcion = 'OT:_'.$ot->id.'_'.$ot->establecimiento->nombre;
                    if ($distribucion->lote->sectore) {
                        
                        $descripcion = 'OT:_'.$ot->id.'_'.$distribucion->lote->sectore->nombre;
                        $descripcion_header = 'OT:_'.$ot->id.'_'.$distribucion->lote->sectore->nombre.$header.$ot->proveedore['nombre'];
                        /* Si es la primera linea, cambio la descripcion */
                        if ($distribucione == reset($distribuciones)) {
                            
                            $sheet->setCellValueByColumnAndRow(12, $linea, $descripcion_header);
                        }
                        
                    }
                }
                
                $descripcion_linea = $descripcion.'_'.$distribucion->proyectos_labore['nombre'].
                        '_'.$distribucion->unidade['nombre'].'_'.$distribucion->lote['nombre'].
                        '_Sup._'.$superficie.'_Precio_'.$certificado.'_'.$ot->observaciones;
                
                
                
                
                
                if( $certificador ) {
                    $descripcion_linea = $descripcion_linea.'_'.$certificador->user->nombre;
                }
                $sheet->setCellValueByColumnAndRow(16, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(17, $linea, 'Servicios');
                $sheet->setCellValueByColumnAndRow(18, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(19, $linea, $tab);
                /* --------------------------------------------------------------------------------------------------- */
                $categoria = $distribucion->proyectos_labore['proyectos_tarea']['categoria'];
                $sheet->setCellValueByColumnAndRow(20, $linea, $categoria );
                /* --------------------------------------------------------------------------------------------------- */
                $sheet->setCellValueByColumnAndRow(21, $linea, $tab);
                
                /* Si es Agricultura, simplifico la 2a observacion */
                if ( $this->request->session()->read('Auth.User.grupo_negocio') == 2) {
                    $sheet->setCellValueByColumnAndRow(22, $linea, $distribucion->proyectos_labore['nombre']);
                    
                    $sheet->setCellValueByColumnAndRow(12, $lineaot, $descripcion_header);
                    
                } else {
                    $sheet->setCellValueByColumnAndRow(22, $linea, $descripcion_linea);
                }
                $sheet->setCellValueByColumnAndRow(23, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(24, $linea, $distribucion['unidade']['nombre']);
                $sheet->setCellValueByColumnAndRow(25, $linea, $tab);
                /* --------------------------------------------------------------------------------------------------- */
                $sheet->setCellValueExplicitByColumnAndRow(26, $linea, $superficie, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                /* --------------------------------------------------------------------------------------------------- */
                $sheet->setCellValueByColumnAndRow(27, $linea, $tab);
                /* --------------------------------------------------------------------------------------------------- */
                $sheet->setCellValueExplicitByColumnAndRow(28, $linea, $certificado, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                /* --------------------------------------------------------------------------------------------------- */
                $sheet->setCellValueByColumnAndRow(29, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(30, $linea, $fechaot);
                $sheet->setCellValueByColumnAndRow(31, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(32, $linea, $fechaot);
                $sheet->setCellValueByColumnAndRow(33, $linea, '*ML(767,662)');
                $sheet->setCellValueByColumnAndRow(34, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(35, $linea, $ot->establecimiento['organizacion']);
                $sheet->setCellValueByColumnAndRow(36, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(37, $linea, $enter);
                $sheet->setCellValueByColumnAndRow(38, $linea, '*ML(251,199)');
                $sheet->setCellValueByColumnAndRow(39, $linea, $distribucion['proyecto']['segmento']);
                $sheet->setCellValueByColumnAndRow(40, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(41, $linea, $distribucion['proyectos_labore']['task_number']);
                $sheet->setCellValueByColumnAndRow(42, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(43, $linea, $distribucion['proyectos_labore']['expenditure_type']);
                $sheet->setCellValueByColumnAndRow(44, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(45, $linea, $ot->establecimiento['organizacion2']);
                $sheet->setCellValueByColumnAndRow(46, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(47, $linea, $fechaot);
                $sheet->setCellValueByColumnAndRow(48, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(49, $linea, '*sl(1)');
                $sheet->setCellValueByColumnAndRow(50, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(51, $linea, '*sl(3)');
                $sheet->setCellValueByColumnAndRow(52, $linea, $tab);
                if ( $this->request->session()->read('Auth.User.grupo_negocio') == 2) {
                    $campo = $ot->establecimiento['nombre'];
                    if ($distribucion->lote->sectore) {
                        $campo = $distribucion->lote->sectore->nombre;
                    }
                    $sheet->setCellValueByColumnAndRow(53, $linea, $campo);
                } else {
                    $sheet->setCellValueByColumnAndRow(53, $linea, '');
                }
                $sheet->setCellValueByColumnAndRow(54, $linea, $tab);
                $sheet->setCellValueExplicitByColumnAndRow(55, $linea, $distribucion['lote']['nombre'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                $sheet->setCellValueByColumnAndRow(56, $linea, $enter);
                $sheet->setCellValueByColumnAndRow(57, $linea, '*ML(809,155)');
                $sheet->setCellValueByColumnAndRow(58, $linea, '*ML(751,126)');
                $sheet->setCellValueByColumnAndRow(59, $linea, '*sl(1)');
                $sheet->setCellValueByColumnAndRow(60, $linea, '\^S');
                
                if ($distribucion !== end($distribuciones)) {
                    $sheet->setCellValueByColumnAndRow(61, $linea, '*DN');
                    $linea++;
                } else {
                    $sheet->setCellValueByColumnAndRow(61, $linea, '*sl(1)');    
                }
            }
            /* Escribo el final de la OT */
            $sheet->setCellValueByColumnAndRow(62, $linea, $enter);
            $sheet->setCellValueByColumnAndRow(63, $linea, '*ML(55,310)');
            $sheet->setCellValueByColumnAndRow(64, $linea, '*sl(1)');
            $sheet->setCellValueByColumnAndRow(65, $linea, $tab);
            $sheet->setCellValueByColumnAndRow(66, $linea, $tab);
            $sheet->setCellValueByColumnAndRow(67, $linea, $tab);
            $sheet->setCellValueByColumnAndRow(68, $linea, $certificador->user->nombre);
            $sheet->setCellValueByColumnAndRow(69, $linea, $tab);
            $sheet->setCellValueByColumnAndRow(70, $linea, '*ML(525,605)');
            $sheet->setCellValueByColumnAndRow(71, $linea, '*sl(1)');
            $sheet->setCellValueByColumnAndRow(72, $linea, '*DN'); 
            $linea++;
        }
        
        $writer->save($path);

        $data = [
            'status' => 'success',
            'message' => 'El archivo se creó correctamente.',
            'archivo' => $archivo
        ];
        
        return $data;
    }
    
    private function listaLabores($ot = null) {
        $cultivos = [];
        $labores = [];
        $campañas = [];
        
        $header = '_';
        foreach ($ot->orden_trabajos_distribuciones as $distribucion) {
            if ($distribucion->proyecto) {
                if (!in_array($distribucion->proyecto->cultivo, $cultivos)) {
                    $cultivos[] = $distribucion->proyecto->cultivo;
                }
                $temp = explode('-', $distribucion->proyecto->segmento);
                if (!in_array($temp[0], $campañas)) {
                    $campañas[] = $temp[0];
                }
                if (!in_array($distribucion->proyectos_labore->nombre, $labores)) {
                    $labores[] = $distribucion->proyectos_labore->nombre;
                }
            }
        }
        if ($cultivos) {
            $header = $header. $this->transformarArray($cultivos).'_';
        }
        if ($campañas) {
            $header = $header. $this->transformarArray($campañas).'_';
        }
        if ($labores) {
            $header = $header. $this->transformarArray($labores).'_';
        }
        return $header;
    }
    /* Recibo un array y devuelvo un texto.
     * Ejemplo: 
     * Recibo: [MAIZ, TRIGO]
     * Devuelvo: (MAIZ-TRIGO)
     * Si solo tiene un item, lo devuelvo tal cual vino
     */
    private function transformarArray($listas = null) {
        $texto = '';
        if ($listas) {
            $texto = count($listas) > 1 ? $texto = '(' : $texto = '';
            foreach ($listas as $lista) {
                $texto = $texto.$lista;
                if ($lista !== end($listas)) {
                    $texto = $texto.'-';
                }
            }
            $texto = count($listas) > 1 ? $texto = $texto.')' : $texto;
        }
        return $texto;
    }
    
    public function generarvca(){
    /* Bloqueo el Generar VCA ya que se hará todo en forma automática
     * Solicitado por Fernanda Barca el 07/01/2020
     */    
        
        $this->loadModel('OrdenTrabajos');
        
        $data = $this->request->data;
        
        $ordenes = $data['valores'];
        
        $certificaciones = [];
        
        if (is_array($ordenes) && count($ordenes) > 0){
            $ordenTrabajos = $this->OrdenTrabajos->find('all', [
                'contain' => ['Establecimientos', 'Users' ,'Proveedores', 'OrdenTrabajosEstados', 'OrdenTrabajosDistribuciones' => ['ProyectosLabores' => 'ProyectosTareas', 'Unidades', 'Lotes' => ['Sectores'], 'Proyectos',
                            'OrdenTrabajosInsumos'=> ['Productos','Unidades','Almacenes' ,'OrdenTrabajosInsumosDevoluciones', 'OrdenTrabajosInsumosEntregas'],
                             'OrdenTrabajosCertificaciones' => 'Users']
                    ],
                'conditions' => ['OrdenTrabajos.id IN' => $ordenes]
            ]);
            $ordenTrabajos->toArray();
            
            $id = $data['id'];

            if (!$certificaciones) {
                
                $ordenTrabajosDataload = $this->OrdenTrabajosDataloads->get($id);
                
                $date = Time::now();
                $fecha = $date->format('Ymd');

                /* Armo el nombre y el path */
                $nombre = 'vca_'.$id.'_'.$fecha;
                $dir = ROOT.DS.'dataload';

                /* Si la carpeta no existe, lo creamos */
                if (!file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }

                $path = $dir.DS.$nombre.'.xlsx';

                $archivo = $nombre.'.xlsx';

                /* Guardo el nombre y la ruta */
                $ordenTrabajosDataload->vca = $archivo;

                $this->OrdenTrabajosDataloads->save($ordenTrabajosDataload);

                /* Ahora, envio los datos para crear el excel */
                if ( $this->request->session()->read('Auth.User.grupo_negocio') == 2) {
                    $resultado = $this->generar_excel_vca_agricultura($ordenTrabajos, $id, $path, $archivo);
                } else {
                    $resultado = $this->generar_excel_vca($ordenTrabajos, $id, $path, $archivo);
                }
                $this->set('resultado', $resultado);

            } else {
                $data = [
                    'status' => 'error',
                    'message' => 'Faltan OC.',
                    'data' => $certificaciones
                ];                
                
                $this->set('resultado', $data);
            }
       }        
    }
    /*
     * Genero el archivo Excel para el datalodad del VCA
     * 
     */
    public function generar_excel_vca($ordenTrabajos = null, $id = null, $path = null, $archivo = null){
        
        $tab = '\{TAB}';
        $enter = '\{ENTER}';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Consumos');
        
        $writer = new Xlsx($spreadsheet);
        
        $linea = 2;
        
        /* Escribo los encabezados */
        $sheet->setCellValue('A1', 'Nº Articulo');
        $sheet->setCellValue('B1', 'X');
        $sheet->setCellValue('C1', 'Sub Inventario');
        $sheet->setCellValue('D1', 'X');
        $sheet->setCellValue('E1', 'Localizacion');
        $sheet->setCellValue('F1', 'X');
        $sheet->setCellValue('G1', 'UM');
        $sheet->setCellValue('H1', 'X');
        $sheet->setCellValue('I1', 'Cantidad');
        $sheet->setCellValue('J1', 'X');
        $sheet->setCellValue('K1', 'X');
        $sheet->setCellValue('L1', 'X');
        $sheet->setCellValue('M1', 'Motivo');
        $sheet->setCellValue('N1', 'X');
        $sheet->setCellValue('O1', 'Referencia');
        $sheet->setCellValue('P1', 'X');
        $sheet->setCellValue('Q1', 'Proyecto');
        $sheet->setCellValue('R1', 'X');
        $sheet->setCellValue('S1', 'Tarea Origen');
        $sheet->setCellValue('T1', 'X');
        $sheet->setCellValue('U1', 'Tipo Erogacion');
        $sheet->setCellValue('V1', 'X');
        $sheet->setCellValue('W1', 'Org Erogacion');
        $sheet->setCellValue('X1', 'X');
        $sheet->setCellValue('Y1', 'X');
        $sheet->setCellValue('Z1', 'OC');
        $sheet->setCellValue('AA1', 'X');
        $sheet->setCellValue('AB1', 'Lote');
        $sheet->setCellValue('AC1', 'X');
        $sheet->setCellValue('AD1', 'OT');
        $sheet->setCellValue('AE1', 'X');
        $sheet->setCellValue('AF1', 'X');
        $sheet->setCellValue('AG1', 'Fecha');
        $sheet->setCellValue('AH1', 'X');
        $sheet->setCellValue('AI1', 'VCA');
        $sheet->setCellValue('AJ1', 'X');
        $sheet->setCellValue('AK1', 'X');
        $sheet->setCellValue('AL1', 'Enter');
        $sheet->setCellValue('AM1', 'ML');
        $sheet->setCellValue('AN1', 'X');
        $sheet->setCellValue('AO1', 'Cargado a Oracle');
        $sheet->setCellValue('AP1', 'Existencias');
        
        foreach ($ordenTrabajos as $ot){
            $distribuciones = $ot->orden_trabajos_distribuciones;
            foreach($distribuciones as $distribucion){

                $superficie_certificada = $this->OrdenTrabajosDistribuciones->find('SuperficieCertificada', ['IdDistribucion' => $distribucion['id']]); 
                $superficie =  number_format($superficie_certificada->superficie, 2, ",", "");

                $insumos = $distribucion->orden_trabajos_insumos;
                foreach ($insumos as $insumo) {
                    
                    $InsumosAplicados = $this->insumosaplicados($insumo->id);
                    $insumos_aplicados = number_format($InsumosAplicados, 2, ",", "");

                    $Dosis = $InsumosAplicados / $superficie_certificada->superficie;

                    $dosis_aplicada = number_format($Dosis, 3, ",", "");
                    
                    $descripcion = 'OT:_'.$ot->id.'_'.$ot->proveedore['nombre'].'_'.$insumo['producto']['nombre'].
                        '_'.$insumo['unidade']['codigo'].'_'.$distribucion['lote']['nombre'].
                            '_Sup._'.$superficie.'_Cant_'.$insumos_aplicados.
                            '_Dosis_'.$dosis_aplicada.'_';
                    
                    /* Busco el nombre del ultimo que certifico */
                    $certificador = $this->OrdenTrabajosDistribuciones->find('Certificador', ['IdDistribucion' => $distribucion->id]);
                    
                    if( $certificador ) {
                        $descripcion = $descripcion.$certificador->user->nombre.'_VCA_'.$ot->id;
                    }
                    $sheet->setCellValueByColumnAndRow(1, $linea, $insumo->producto['codigooracle']);
                    $sheet->setCellValueByColumnAndRow(2, $linea, $tab);
                    $sheet->setCellValueByColumnAndRow(3, $linea, $insumo->almacene['sub_inventario']);
                    $sheet->setCellValueByColumnAndRow(4, $linea, $tab);
                    $sheet->setCellValueByColumnAndRow(5, $linea, $insumo->almacene['localizacion']);
                    $sheet->setCellValueByColumnAndRow(6, $linea, $tab);
                    $sheet->setCellValueByColumnAndRow(7, $linea, $insumo->unidade['codigo']);
                    $sheet->setCellValueByColumnAndRow(8, $linea, $tab);
                    /* ----------------------------------------------------------- */
                    $sheet->setCellValueExplicitByColumnAndRow(9, $linea, $insumos_aplicados, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);                
                    /* ----------------------------------------------------------- */
                    $sheet->setCellValueByColumnAndRow(10, $linea, $tab);
                    $sheet->setCellValueByColumnAndRow(11, $linea, $tab);
                    $sheet->setCellValueByColumnAndRow(12, $linea, $tab);
                    $sheet->setCellValueByColumnAndRow(13, $linea, 'CPRO');
                    $sheet->setCellValueByColumnAndRow(14, $linea, $tab);
                    $sheet->setCellValueByColumnAndRow(15, $linea, $descripcion);
                    $sheet->setCellValueByColumnAndRow(16, $linea, $tab);
                    $sheet->setCellValueByColumnAndRow(17, $linea, $distribucion['proyecto']['segmento'] );
                    $sheet->setCellValueByColumnAndRow(18, $linea, $tab);

                    $tarea = $distribucion['proyecto']['cultivo'].$insumo->producto['tarea'];

                    $sheet->setCellValueByColumnAndRow(19, $linea, $tarea);
                    $sheet->setCellValueByColumnAndRow(20, $linea, $tab);
                    $sheet->setCellValueByColumnAndRow(21, $linea, $insumo->producto['tipo_erogacion']);
                    $sheet->setCellValueByColumnAndRow(22, $linea, $tab);
                    $sheet->setCellValueByColumnAndRow(23, $linea, $ot->establecimiento['organizacion']);
                    $sheet->setCellValueByColumnAndRow(24, $linea, $tab);
                    $sheet->setCellValueByColumnAndRow(25, $linea, $tab);
                    $sheet->setCellValueByColumnAndRow(26, $linea, $ot->oc);
                    $sheet->setCellValueByColumnAndRow(27, $linea, $tab);
                    $sheet->setCellValueExplicitByColumnAndRow(28, $linea, $distribucion['lote']['nombre'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $sheet->setCellValueByColumnAndRow(29, $linea, $tab);
                    $sheet->setCellValueExplicitByColumnAndRow(30, $linea, $ot->id, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $sheet->setCellValueByColumnAndRow(31, $linea, $tab);
                    $sheet->setCellValueByColumnAndRow(32, $linea, $tab);
                    /* ---------------------------------------------------------------*/
                    $fechaot = $ot->fecha->i18nFormat('dd/MM/yyyy');
                    /* ---------------------------------------------------------------*/
                    $sheet->setCellValueByColumnAndRow(33, $linea, $fechaot);
                    $sheet->setCellValueByColumnAndRow(34, $linea, $tab);
                    $sheet->setCellValueExplicitByColumnAndRow(35, $linea, $ot->id, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $sheet->setCellValueByColumnAndRow(36, $linea, $tab);
                    $sheet->setCellValueByColumnAndRow(37, $linea, $tab);
                    $sheet->setCellValueByColumnAndRow(38, $linea, $enter);

                    if(end($insumos)){
                        $sheet->setCellValueByColumnAndRow(39, $linea, '*ML(110,67)');
                        $sheet->setCellValueByColumnAndRow(40, $linea, $tab);
                    }
                    
                    /* Marco el registro como ya subido a la interfaz */
                    $this->marcar_entregas_oracle($insumo);
                    
                    $linea++;
                }
            }
        }
        
        $writer->save($path);
        
        $data = [
            'status' => 'success',
            'message' => 'El archivo se creó correctamente.',
            'archivo' => $archivo
        ];
        
        return $data;        
    }
    
    /* Marco los insumos como ya subidos a la interfaz de oracle*/
    private function marcar_entregas_oracle ($linea = null) {
        if ($linea) {
            $insumos = $this->OrdenTrabajosInsumos->find('all', [
                'contain' => ['OrdenTrabajosInsumosEntregas','OrdenTrabajosInsumosDevoluciones'],
                'conditions' => ['OrdenTrabajosInsumos.id' => $linea->id]
            ]);
            foreach ($insumos as $insumo) {
                /* Marco las Entregas como subidas a la interfaz */
                foreach ($insumo->orden_trabajos_insumos_entregas as $entrega) {
                    $entrega->oracle_flag = 'Y';
                    $entrega->interface_error = 'Dataload';
                    $this->OrdenTrabajosInsumosEntregas->save($entrega);
                }
                /* Marco las devoluciones como subidas a la interfaz */
                foreach ($insumo->orden_trabajos_insumos_devoluciones as $devolucion) {
                    $devolucion->oracle_flag = 'Y';
                    $devolucion->interface_error = 'Dataload';
                    $this->OrdenTrabajosInsumosDevoluciones->save($devolucion);
                }
            }
        }
        return;
    }
    
    public function generar_excel_vca_agricultura($ordenTrabajos = null, $id = null, $path = null, $archivo = null){
        
        $tab = '\{TAB}';
        $enter = '\{ENTER}';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('Consumos');
        
        $writer = new Xlsx($spreadsheet);
        
        $linea = 2;
        
        /* Escribo los encabezados */
        $sheet->setCellValue('A1', 'Articulo');
        $sheet->setCellValue('B1', 'Nº Articulo');
        $sheet->setCellValue('C1', 'X');
        $sheet->setCellValue('D1', 'Sub Inventario');
        $sheet->setCellValue('E1', 'X');
        $sheet->setCellValue('F1', 'Localizacion');
        $sheet->setCellValue('G1', 'X');
        $sheet->setCellValue('H1', 'X');
        $sheet->setCellValue('I1', 'Cantidad');
        $sheet->setCellValue('J1', 'X');
        $sheet->setCellValue('K1', 'X');
        $sheet->setCellValue('L1', 'Motivo');
        $sheet->setCellValue('M1', 'X');
        $sheet->setCellValue('N1', 'Referencia');
        $sheet->setCellValue('O1', 'X');
        $sheet->setCellValue('P1', 'Proyecto');
        $sheet->setCellValue('Q1', 'X');
        $sheet->setCellValue('R1', 'X');
        $sheet->setCellValue('S1', 'Tarea Origen');
        $sheet->setCellValue('T1', 'X');
        $sheet->setCellValue('U1', 'X');
        $sheet->setCellValue('V1', 'Tipo Erogacion');
        $sheet->setCellValue('W1', 'X');
        $sheet->setCellValue('X1', 'Direccion');
        $sheet->setCellValue('Y1', 'X');
        $sheet->setCellValue('Z1', 'X');
        $sheet->setCellValue('AA1', 'X');
        $sheet->setCellValue('AB1', 'OC');
        $sheet->setCellValue('AC1', 'X');
        $sheet->setCellValue('AD1', 'Lote');
        $sheet->setCellValue('AE1', 'X');
        $sheet->setCellValue('AF1', 'OT');
        $sheet->setCellValue('AG1', 'X');
        $sheet->setCellValue('AH1', 'Superficie Aplicada');
        $sheet->setCellValue('AI1', 'X');
        $sheet->setCellValue('AJ1', 'Fecha');
        $sheet->setCellValue('AK1', 'X');
        $sheet->setCellValue('AL1', 'VCA');
        $sheet->setCellValue('AM1', 'X');
        $sheet->setCellValue('AN1', 'Lote');
        $sheet->setCellValue('AO1', 'Enter');
        $sheet->setCellValue('AP1', 'Down');
        $sheet->setCellValue('AQ1', 'X');
        $sheet->setCellValue('AR1', 'X');
        $sheet->setCellValue('AS1', 'Cargado a Oracle');
        $sheet->setCellValue('AT1', 'Existencia');
        
        foreach ($ordenTrabajos as $ot){
            $distribuciones = $ot->orden_trabajos_distribuciones;
            foreach($distribuciones as $distribucion){

                $superficie_certificada = $this->OrdenTrabajosDistribuciones->find('SuperficieCertificada', ['IdDistribucion' => $distribucion['id']]); 
                $superficie =  number_format($superficie_certificada->superficie, 2, ",", "");

                $insumos = $distribucion->orden_trabajos_insumos;
                foreach ($insumos as $insumo) {
                    
                    $InsumosAplicados = $this->insumosaplicados($insumo->id);
                    $insumos_aplicados = number_format($InsumosAplicados, 2, ",", "");

                    $Dosis = $InsumosAplicados / $superficie_certificada->superficie;

                    $dosis_aplicada = number_format($Dosis, 3, ",", "");
                    
                    $descripcion = 'OT:_'.$ot->id.'_'.$ot->proveedore['nombre'].'_'.$insumo['producto']['nombre'].
                        '_'.$insumo['unidade']['codigo'].'_'.$distribucion['lote']['nombre'].
                            '_Sup._'.$superficie.'_Cant_'.$insumos_aplicados.
                            '_Dosis_'.$dosis_aplicada.'_';
                    
                    /* Busco el nombre del ultimo que certifico */
                    $certificador = $this->OrdenTrabajosDistribuciones->find('Certificador', ['IdDistribucion' => $distribucion->id]);
                    if( $certificador ) {
                        $descripcion = $descripcion.$certificador->user->nombre.'_VCA_'.$ot->id;
                    }                    
                    $sheet->setCellValueByColumnAndRow(1, $linea, $insumo->producto['nombre']);
                    $sheet->setCellValueByColumnAndRow(2, $linea, $insumo->producto['codigooracle']);
                    $sheet->setCellValueByColumnAndRow(3, $linea, $tab);
                    $sheet->setCellValueByColumnAndRow(4, $linea, $insumo->almacene['sub_inventario']);
                    $sheet->setCellValueByColumnAndRow(5, $linea, $tab);
                    $sheet->setCellValueByColumnAndRow(6, $linea, $insumo->almacene['localizacion']);
                    $sheet->setCellValueByColumnAndRow(7, $linea, $tab);
                    $sheet->setCellValueByColumnAndRow(8, $linea, '*SL(3)'); /* Espera */
                    /* ----------------------------------------------------------- */
                    $sheet->setCellValueExplicitByColumnAndRow(9, $linea, $insumos_aplicados, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);                
                    /* ----------------------------------------------------------- */
                    $sheet->setCellValueByColumnAndRow(10, $linea, $tab);
                    $sheet->setCellValueByColumnAndRow(11, $linea, $tab);
                    $sheet->setCellValueByColumnAndRow(12, $linea, 'CPRO');
                    $sheet->setCellValueByColumnAndRow(13, $linea, $tab);
                    $sheet->setCellValueByColumnAndRow(14, $linea, $descripcion);
                    $sheet->setCellValueByColumnAndRow(15, $linea, $tab);
                    $sheet->setCellValueByColumnAndRow(16, $linea, $distribucion['proyecto']['segmento'] );
                    $sheet->setCellValueByColumnAndRow(17, $linea, $tab);
                    $sheet->setCellValueByColumnAndRow(18, $linea, '*SL(1)');
                    $tarea = $distribucion['proyecto']['cultivo'].$insumo->producto['tarea'];
                    $sheet->setCellValueByColumnAndRow(19, $linea, $tarea);
                    $sheet->setCellValueByColumnAndRow(20, $linea, $tab);
                    $sheet->setCellValueByColumnAndRow(21, $linea, '*SL(1)');
                    $sheet->setCellValueByColumnAndRow(22, $linea, $insumo->producto['tipo_erogacion']);
                    $sheet->setCellValueByColumnAndRow(23, $linea, $tab);
                    $sheet->setCellValueByColumnAndRow(24, $linea, $ot->establecimiento['direccion']);
                    if ($distribucion->lote->sectore) {
                        $sheet->setCellValueByColumnAndRow(24, $linea, $distribucion->lote->sectore->direccion);
                    }
                    $sheet->setCellValueByColumnAndRow(25, $linea, $tab);
                    $sheet->setCellValueByColumnAndRow(26, $linea, $tab);
                    $sheet->setCellValueByColumnAndRow(27, $linea, '*SL(3)');
                    $sheet->setCellValueByColumnAndRow(28, $linea, $ot->oc);
                    $sheet->setCellValueByColumnAndRow(29, $linea, $tab);
                    $sheet->setCellValueExplicitByColumnAndRow(30, $linea, $distribucion['lote']['nombre'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $sheet->setCellValueByColumnAndRow(31, $linea, $tab);
                    $sheet->setCellValueExplicitByColumnAndRow(32, $linea, $ot->id, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $sheet->setCellValueByColumnAndRow(33, $linea, $tab);
                    $sheet->setCellValueByColumnAndRow(34, $linea, number_format($superficie_certificada->superficie,2,",",""));
                    $sheet->setCellValueByColumnAndRow(35, $linea, $tab);
                    /* ---------------------------------------------------------------*/
                    $fechaot = $ot->fecha->i18nFormat('dd/MM/yyyy');
                    /* ---------------------------------------------------------------*/
                    $sheet->setCellValueByColumnAndRow(36, $linea, $fechaot);
                    $sheet->setCellValueByColumnAndRow(37, $linea, $tab);
                    $sheet->setCellValueExplicitByColumnAndRow(38, $linea, $ot->id, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $sheet->setCellValueByColumnAndRow(39, $linea, $tab);
                    $sheet->setCellValueExplicitByColumnAndRow(40, $linea, $distribucion['lote']['nombre'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $sheet->setCellValueByColumnAndRow(41, $linea, $enter);
                    $sheet->setCellValueByColumnAndRow(42, $linea, '\{DOWN}');
                    $sheet->setCellValueByColumnAndRow(43, $linea, '*SL(1)');
                    $sheet->setCellValueByColumnAndRow(44, $linea, '*DN');

                    $stock = $this->ProductosExistencias->find('all', [
                        'conditions' => ['producto_id' =>  $insumo->producto->id, 'almacene_id' => $insumo->almacene->id]
                    ])->first();
                    if ( $stock ) {
                        $sheet->setCellValueByColumnAndRow(45, $linea, $stock->cantidad);
                        $sheet->setCellValueByColumnAndRow(46, $linea, $stock->fecha->i18nFormat('dd/MM/yyyy H:m'));
                    }                    
                    
                    
//                    if(end($insumos)){
//                        $sheet->setCellValueByColumnAndRow(39, $linea, '*ML(110,67)');
//                        $sheet->setCellValueByColumnAndRow(40, $linea, $tab);
//                    }
                   
                    $this->marcar_entregas_oracle($insumo);
                    
                    $linea++;
                }
            }
        }
        
        $writer->save($path);
        
        $data = [
            'status' => 'success',
            'message' => 'El archivo se creó correctamente.',
            'archivo' => $archivo
        ];
        
        return $data;        
    }
    
    
    
    public function insumosaplicados($id = null)
    {
        $entregado = 0;
        $entregas = $this->OrdenTrabajosInsumos->find('Entregas', ['IdInsumos' => $id]);
        if (!empty($entregas->entregas)){
            $entregado = $entregas->entregas;
        }
        
        $devuelto = 0;
        $devoluciones = $this->OrdenTrabajosInsumos->find('Devoluciones', ['IdInsumos' => $id]);        
        if (!empty($devoluciones->devoluciones)){
            $devuelto = $devoluciones->devoluciones;
        }
        
        $aplicado = $entregado - $devuelto;

        return $aplicado;
    }    
}
