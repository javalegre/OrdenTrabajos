<?php
namespace Ordenes\Controller;

use Ordenes\Controller\AppController;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Cake\I18n\Time;

/**
 * OrdenTrabajosReclasificaciones Controller
 *
 * @property \Ordenes\Model\Table\OrdenTrabajosReclasificacionesTable $OrdenTrabajosReclasificaciones
 *
 * @method \Ordenes\Model\Entity\OrdenTrabajosReclasificacione[]|\Cake\Datasource\ResultSetInterface paginate($object = null, array $settings = [])
 */
class OrdenTrabajosReclasificacionesController extends AppController
{
     public function initialize() {
        parent::initialize();
        $this->loadModel('Establecimientos');
        $this->loadModel('Ordenes.OrdenTrabajosReclasificaciones');
        $this->loadModel('Ordenes.OrdenTrabajosReclasificacionesDetalles');
        $this->loadModel('Ordenes.OrdenTrabajosDistribuciones');
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
        $establecimientos = $this->Establecimientos->find('all')->select(['id', 'nombre', 'organizacion'])->toArray();
        
        $columns = [[
                        'field' => 'fecha',
                        'data' => 'fecha'
                    ], [
                        'field' => 'OrdenTrabajosReclasificaciones.nombre',
                        'data' => 'lote'
                    ], [
                        'field' => 'Establecimientos',
                        'data' => 'establecimiento.nombre'
                    ], [
                        'field' => 'observaciones',
                        'data' => 'observaciones'
                    ], [
                        'field' => 'Users.nombre',
                        'data' => 'nombre'
                    ], [
                        'field' => 'created',
                        'data' => 'created'
                    ], [
                        'field' => 'OrdenTrabajosReclasificaciones.id',
                        'data' => 'id'
                    ]];  
        
        /* ****************************************************************** */
        /* Filtros                                                            */
        /* ****************************************************************** */
        $filtros = [];
        $establecimiento_id = $this->request->getQuery('establecimiento_id');
        if ($establecimiento_id) {
            $filtros[] = "OrdenTrabajosReclasificaciones.establecimiento_id = '".$establecimiento_id."'";
        }
        $desde = $this->request->getQuery('desde');
        if ($desde) {
            $filtros[] = "OrdenTrabajosReclasificaciones.fecha >= '".$desde."'";
        }
        $hasta = $this->request->getQuery('hasta');
        if ($hasta) {
            $filtros[] = "OrdenTrabajosReclasificaciones.fecha <= '".$hasta."'";
        }
        /* ****************************************************************** */
        /* Filtros                                                            */
        /* ****************************************************************** */
        
        $data = $this->DataTables->find('Ordenes.OrdenTrabajosReclasificaciones','all', [
            'contain' => ['Establecimientos',
                          'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']]
                           ],
            'conditions' => [$filtros]
        ], $columns);         
        
        $this->set(compact('establecimientos'));
        
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
     * @param string|null $id Orden Trabajos Reclasificacione id.
     * @return \Cake\Http\Response|null
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $ordenTrabajosReclasificacione = $this->OrdenTrabajosReclasificaciones->get($id, [
            'contain' => ['Establecimientos', 
                          'Users',
                          'OrdenTrabajosReclasificacionesDetalles' => ['OrdenTrabajos',
                                                                       'Proyectos', 
                                                                       'ProyectosLabores', 
                                                                       'OrdenTrabajosDistribuciones' => [
                                                                           'Proyectos',
                                                                           'ProyectosLabores'
                                                                       ]
                              
                          ]],
        ]);

        $establecimientos = $this->OrdenTrabajosReclasificaciones->Establecimientos->find('list', ['limit' => 200]);
        
        /* Configuro el nombre del archivo */
        $this->viewBuilder()->options([
            'pdfConfig' => [
                'orientation' => 'portrait',
                'filename' => 'reclasificacion_' . $id.'_'.$ordenTrabajosReclasificacione->establecimiento->organizacion.'_'.$ordenTrabajosReclasificacione->fecha->i18nFormat('yyyy_MM_dd').'.pdf'
            ]
        ]);
        
        $this->set(compact('ordenTrabajosReclasificacione', 'establecimientos'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $ordenTrabajosReclasificacione = $this->OrdenTrabajosReclasificaciones->newEntity();
        if ($this->request->is('post')) {
            $ordenTrabajosReclasificacione = $this->OrdenTrabajosReclasificaciones->patchEntity($ordenTrabajosReclasificacione, $this->request->getData());
            $ordenTrabajosReclasificacione->fecha = date_format(date_create(), 'Y-m-d');
            
            if ($this->OrdenTrabajosReclasificaciones->save($ordenTrabajosReclasificacione)) {
                $this->set(['respuesta' => ['status' => 'success', 'message' => 'Se guardó el dato correctamente.', 'data' => $ordenTrabajosReclasificacione->id],
                                            '_serialize' => 'respuesta']);
            } else {
                $this->set(['respuesta' => ['status' => 'error', 'message' => 'Ocurrió un error al guardar la reclasificacion.'],
                                            '_serialize' => 'respuesta']);
            }
            $this->RequestHandler->renderAs($this, 'json');
        }
        $establecimientos = $this->Establecimientos->find('list');
        $this->set(compact('ordenTrabajosReclasificacione', 'establecimientos'));
        
        /* Para el modal */
        $this->render('add', 'ajax');
    }

    /**
     * Edit method
     *
     * @param string|null $id Orden Trabajos Reclasificacione id.
     * @return \Cake\Http\Response|null Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $ordenTrabajosReclasificacione = $this->OrdenTrabajosReclasificaciones->get($id, [
            'contain' => ['Establecimientos'],
        ]);
        
        /* Si la reclasificacion ya fue procesada, es decir, se generó el excel, lo pasamos a la vista */
        if ($ordenTrabajosReclasificacione->procesado === 1) {
            return $this->redirect(['action' => 'view', $ordenTrabajosReclasificacione->id]);
        }
        if ($this->request->is(['patch', 'post', 'put'])) {
            $ordenTrabajosReclasificacione = $this->OrdenTrabajosReclasificaciones->patchEntity($ordenTrabajosReclasificacione, $this->request->getData());
            $ordenTrabajosReclasificacione->fecha = Time::createFromFormat('d/m/Y', $this->request->getData('fecha'));
            
            if ($this->OrdenTrabajosReclasificaciones->save($ordenTrabajosReclasificacione)) {
                $this->set(['respuesta' => ['status' => 'success', 'message' => 'Los cambios se guardaron correctamente.', 'data' => $ordenTrabajosReclasificacione->id],
                                            '_serialize' => 'respuesta']);
            } else {
                $this->set(['respuesta' => ['status' => 'error', 'message' => 'Ocurrió un error al guardar los cambios en la reclasificacion.'],
                                            '_serialize' => 'respuesta']);
            }
            $this->RequestHandler->renderAs($this, 'json');
        }
        $establecimientos = $this->OrdenTrabajosReclasificaciones->Establecimientos->find('list', ['limit' => 200]);
        $users = $this->OrdenTrabajosReclasificaciones->Users->find('list', ['limit' => 200]);
        $this->set(compact('ordenTrabajosReclasificacione', 'establecimientos', 'users'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Orden Trabajos Reclasificacione id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $ordenTrabajosReclasificacione = $this->OrdenTrabajosReclasificaciones->get($id, [
            'contain' => ['OrdenTrabajosReclasificacionesDetalles']
        ]);
        
        if (count($ordenTrabajosReclasificacione->orden_trabajos_reclasificaciones_detalles) > 0) {
            $this->set(['respuesta' => ['status' => 'error', 'message' => 'Existen lineas de reclasificación. Eliminelas antes e intentelo nuevamente.'],
                                            '_serialize' => 'respuesta']);
            $this->RequestHandler->renderAs($this, 'json');
            return;
        }
        
        if ($this->OrdenTrabajosReclasificaciones->delete($ordenTrabajosReclasificacione)) {
            $this->set(['respuesta' => ['status' => 'success', 'message' => 'Eliminado correctamente.'],
                                            '_serialize' => 'respuesta']);
            $this->RequestHandler->renderAs($this, 'json');
            return;
        }
        $this->set(['respuesta' => ['status' => 'error', 'message' => 'No se pudo eliminar correctamente.'],
                                            '_serialize' => 'respuesta']);
        $this->RequestHandler->renderAs($this, 'json');
        return;
    }

    /**
     * generarExcel
     * 
     * Creo archivo excel de Reclasificacion con 3 hojas:
     * Hoja 1: WebADI
     *  - Son las reclasificaciones de las labores
     * Hoja 2: APRO
     * Hoja 3: CPRO
     * @param 
     * @return json | nombre del archivo
     * 
     */
    public function generarExcel($orden_trabajos_reclasificacione_id  = null) {
       
        /*busco los datos */
        $ordenTrabajosReclasificacionesDetalles = $this->OrdenTrabajosReclasificacionesDetalles->find('all', [
            'contain' => [  'OrdenTrabajosReclasificaciones',
                            'Proyectos', 
                            'ProyectosLabores',
                            'OrdenTrabajos',
                            'OrdenTrabajosDistribuciones' => [
                                    'Proyectos', 
                                    'ProyectosLabores',
                                    'OrdenTrabajosInsumos'=> ['Productos', 'Unidades', 'Almacenes' ],
                                    'Lotes'
                            ]
                        ],
            'conditions' => ['orden_trabajos_reclasificacione_id =' => $orden_trabajos_reclasificacione_id]                    
        ]);
       
        $tab = '\{TAB}';
        $enter = '\{ENTER}';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet()->setTitle('WebADI');
        $writer = new Xlsx($spreadsheet);
        
        /* Defino los encabezados WebADI */
        $sheet->setCellValue('A1', 'Base de Datos');
        $sheet->setCellValue('A2', 'Unidad Operativa');
        $sheet->setCellValue('A3', 'Origen de Transacción');
        $sheet->setCellValue('A4', 'Nombre de Lote');
        $sheet->setCellValue('A5', 'ORG_ID');
        
        /* Defino las columnas de la tabla WebADI */
        $sheet->setCellValue('A7', 'Upl');
        $sheet->setCellValue('B7', 'Número de Proyecto');
        $sheet->setCellValue('C7', 'Número de Tarea');
        $sheet->setCellValue('D7', 'Fecha Contable');
        $sheet->setCellValue('E7', 'Fecha Final de Erogación');
        $sheet->setCellValue('F7', 'Fecha de Articulo de Erogación');
        $sheet->setCellValue('G7', 'Tipo de Erogación');
        $sheet->setCellValue('H7', 'Referencia Transaccional Oiriginal');
        $sheet->setCellValue('I7', 'Costo Bruto Funcional');
        $sheet->setCellValue('J7', 'Costo Bruto Transacción');
        $sheet->setCellValue('K7', 'Cantidad');
        $sheet->setCellValue('L7', 'Indicador Transacción Negativa');
        $sheet->setCellValue('M7', 'Comentario de Erogación');
        $sheet->setCellValue('N7', 'Nombre de Organización');
        $sheet->setCellValue('O7', 'Divisa de Transacción');
        $sheet->setCellValue('P7', 'Categoría DFF');
        $sheet->setCellValue('Q7', '');
        $sheet->setCellValue('R7', '');
        $sheet->setCellValue('S7', 'Mensajes');

        /* Defino los Datos de WebADI */
        $linea = 8;
        foreach ($ordenTrabajosReclasificacionesDetalles as $detalle){
            
            $fecha_contable = $detalle->orden_trabajos_reclasificacione->fecha->modify('last sunday')->i18nFormat('dd-MM-yyyy');
            
            /* La primer linea la usamos para dar de baja la labor */
            $sheet->setCellValueByColumnAndRow(1, $linea, '');
            $sheet->setCellValueByColumnAndRow(2, $linea, $detalle->proyecto->segmento);
            $sheet->setCellValueByColumnAndRow(3, $linea, $detalle->proyectos_labore->task_number);
            $sheet->setCellValueByColumnAndRow(4, $linea, $fecha_contable);      //fecha contable
            $sheet->setCellValueByColumnAndRow(5, $linea, $fecha_contable);      //fecha final erogacion
            $sheet->setCellValueByColumnAndRow(6, $linea, $fecha_contable);      //fecha articulo
            $sheet->setCellValueByColumnAndRow(7, $linea, $detalle->proyectos_labore->expenditure_type);
            $sheet->setCellValueByColumnAndRow(8, $linea, $detalle->referencia);
            $sheet->setCellValueByColumnAndRow(9, $linea, '');      //costo bruto funcional
            $sheet->setCellValueByColumnAndRow(10, $linea, '');     //costo bruto transaccional 
           
            $cantidad = (-1) * ($detalle->orden_trabajos_distribucione->total_certificado * $detalle->orden_trabajos_distribucione->importe_certificado);
            $sheet->setCellValueByColumnAndRow(11, $linea, $cantidad); 
            $sheet->setCellValueByColumnAndRow(12, $linea, 'Y');    //indicador transaccional negativo
            $sheet->setCellValueByColumnAndRow(13, $linea, $detalle->referencia);
            $sheet->setCellValueByColumnAndRow(14, $linea, '');     //Nombre de Organizacion 
            $sheet->setCellValueByColumnAndRow(15, $linea, '');     //Divisa de Transaccion
            $sheet->setCellValueByColumnAndRow(16, $linea, '');     //Categoria DFF           
            $sheet->setCellValueByColumnAndRow(17, $linea, '');     // sin datos       
            $sheet->setCellValueByColumnAndRow(18, $linea, '');     // sin datos
            $sheet->setCellValueByColumnAndRow(19, $linea, '');     // Mensajes           
            $linea++;

            /* Ahora damos de alta la nueva labor reclasificada */
            $sheet->setCellValueByColumnAndRow(1, $linea, '');
            $sheet->setCellValueByColumnAndRow(2, $linea, $detalle->orden_trabajos_distribucione->proyecto->segmento);
            $sheet->setCellValueByColumnAndRow(3, $linea, $detalle->orden_trabajos_distribucione->proyectos_labore->task_number);
            $sheet->setCellValueByColumnAndRow(4, $linea, $fecha_contable);      //fecha contable
            $sheet->setCellValueByColumnAndRow(5, $linea, $fecha_contable);      //fecha final erogacion
            $sheet->setCellValueByColumnAndRow(6, $linea, $fecha_contable);      //fecha articulo
            $sheet->setCellValueByColumnAndRow(7, $linea, $detalle->orden_trabajos_distribucione->proyectos_labore->expenditure_type);
            $sheet->setCellValueByColumnAndRow(8, $linea, $detalle->referencia);
            $sheet->setCellValueByColumnAndRow(9, $linea, '');      //costo bruto funcional
            $sheet->setCellValueByColumnAndRow(10, $linea, '');     //costo bruto transaccional 
           
            $cantidad = ($detalle->orden_trabajos_distribucione->total_certificado * $detalle->orden_trabajos_distribucione->importe_certificado);
            $sheet->setCellValueByColumnAndRow(11, $linea, $cantidad); 
            $sheet->setCellValueByColumnAndRow(12, $linea, '');    //indicador transaccional negativo
            $sheet->setCellValueByColumnAndRow(13, $linea, $detalle->referencia);
            $sheet->setCellValueByColumnAndRow(14, $linea, '');     //Nombre de Organizacion 
            $sheet->setCellValueByColumnAndRow(15, $linea, '');     //Divisa de Transaccion
            $sheet->setCellValueByColumnAndRow(16, $linea, '');     //Categoria DFF           
            $sheet->setCellValueByColumnAndRow(17, $linea, '');     // sin datos       
            $sheet->setCellValueByColumnAndRow(18, $linea, '');     // sin datos
            $sheet->setCellValueByColumnAndRow(19, $linea, '');     // Mensajes           
            $linea++;
        }


        /* Hoja APRO */
        $myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'APRO');
        $spreadsheet->addSheet($myWorkSheet);
        $sheet = $spreadsheet->setActiveSheetIndex(1);

        /* Escribo los encabezados APRO */
        $sheet->setCellValue('A1', 'Articulo - OCULTAR');
        $sheet->setCellValue('B1', 'Articulo');
        $sheet->setCellValue('C1', 'X');
        $sheet->setCellValue('D1', 'Sub Inventario');
        $sheet->setCellValue('E1', 'X');
        $sheet->setCellValue('F1', 'Localizador');
        $sheet->setCellValue('G1', 'X');
        $sheet->setCellValue('H1', 'X');
        $sheet->setCellValue('I1', 'Cantidad');
        $sheet->setCellValue('J1', 'X');
        $sheet->setCellValue('K1', 'Motivo');
        $sheet->setCellValue('L1', 'X');
        $sheet->setCellValue('M1', 'Referencia');
        $sheet->setCellValue('N1', 'X');
        $sheet->setCellValue('O1', 'Proyecto');
        $sheet->setCellValue('P1', 'X');

        $sheet->setCellValue('Q1', 'X');
        $sheet->setCellValue('R1', 'Tarea Origen');
        $sheet->setCellValue('S1', 'X');
        $sheet->setCellValue('T1', 'X');
        $sheet->setCellValue('U1', 'Tipo Erogacion');
        $sheet->setCellValue('V1', 'X');
        $sheet->setCellValue('W1', 'Direccion');
        $sheet->setCellValue('X1', 'X');
        $sheet->setCellValue('Y1', 'Flexflied');
        $sheet->setCellValue('Z1', 'X');
        $sheet->setCellValue('AA1', 'OC');
        $sheet->setCellValue('AB1', 'X');
        $sheet->setCellValue('AC1', 'Distribucion de OC');
        $sheet->setCellValue('AD1', 'X');
        $sheet->setCellValue('AE1', 'N° OT');
        $sheet->setCellValue('AF1', 'X');
        $sheet->setCellValue('AG1', 'Sup. Aplicada');
        $sheet->setCellValue('AH1', 'X');
        $sheet->setCellValue('AI1', 'VCA');
        $sheet->setCellValue('AJ1', 'X');
        $sheet->setCellValue('AK1', 'Lote');
        $sheet->setCellValue('AL1', 'Aceptar');
        $sheet->setCellValue('AM1', 'Nuevo');
        $sheet->setCellValue('AN1', 'X');
        $sheet->setCellValue('AO1', 'X');
        /* fin titulos APRO */
        
        /*
         * en el APRO, damos de baja el articulo anterior, es usando el proyecto y proyecto_labores
         * de la linea de reclasificacion
         */
        $linea = 2;
        foreach ($ordenTrabajosReclasificacionesDetalles as $otrDetalles){
            $insumos = $otrDetalles->orden_trabajos_distribucione->orden_trabajos_insumos;
            foreach ($insumos as $insumo) {
                $sheet->setCellValueByColumnAndRow(1, $linea, $insumo->producto['nombre']);
                $sheet->setCellValueByColumnAndRow(2, $linea, $insumo->producto['codigooracle']);
                $sheet->setCellValueByColumnAndRow(3, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(4, $linea, $insumo->almacene['sub_inventario']);
                $sheet->setCellValueByColumnAndRow(5, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(6, $linea, $insumo->almacene['localizacion']);
                $sheet->setCellValueByColumnAndRow(7, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(8, $linea, '*SL(3)');
                
                $cantidad_aplicada = $insumo->entregas - $insumo->devoluciones;
                
                $sheet->setCellValueByColumnAndRow(9, $linea, $cantidad_aplicada);
                $sheet->setCellValueByColumnAndRow(10, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(11, $linea, 'APRO'); //Motivo
                $sheet->setCellValueByColumnAndRow(12, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(13, $linea, $otrDetalles->referencia);
                $sheet->setCellValueByColumnAndRow(14, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(15, $linea, $otrDetalles->proyecto->segmento);
                $sheet->setCellValueByColumnAndRow(16, $linea, $tab);                
                $sheet->setCellValueByColumnAndRow(17, $linea, '*SL(1)');

                $tarea = $otrDetalles->proyecto->cultivo.$insumo->producto->tarea;
                $sheet->setCellValueByColumnAndRow(18, $linea, $tarea); // tarea Origen ????
                $sheet->setCellValueByColumnAndRow(19, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(20, $linea, '*SL(1)');
                $sheet->setCellValueByColumnAndRow(21, $linea, $insumo->producto['tipo_erogacion']); 
                $sheet->setCellValueByColumnAndRow(22, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(23, $linea, '');     // direccion ???
                $sheet->setCellValueByColumnAndRow(24, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(25, $linea, $tab);   //flexfield
                $sheet->setCellValueByColumnAndRow(26, $linea, '*SL(3)');
                $sheet->setCellValueByColumnAndRow(27, $linea, $otrDetalles->orden_trabajo['oc']);  //orden de compra
                $sheet->setCellValueByColumnAndRow(28, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(29, $linea, $otrDetalles->id);  /// Distribucion OC ???
                $sheet->setCellValueByColumnAndRow(30, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(31, $linea, $otrDetalles->orden_trabajo['id']);
                $sheet->setCellValueByColumnAndRow(32, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(33, $linea, '');  // superficie Aplicada.
                $sheet->setCellValueByColumnAndRow(34, $linea, $tab); 
                $sheet->setCellValueByColumnAndRow(35, $linea, $otrDetalles->orden_trabajo['id']); // vca 
                $sheet->setCellValueByColumnAndRow(36, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(37, $linea, $otrDetalles->orden_trabajos_distribucione->lote->nombre); // lote 
                $sheet->setCellValueByColumnAndRow(38, $linea, $enter);  
                $sheet->setCellValueByColumnAndRow(39, $linea, '\{DOWN}'); 
                $sheet->setCellValueByColumnAndRow(40, $linea, '*SL(1)');
                $sheet->setCellValueByColumnAndRow(41, $linea, '*DN');
                
                $linea++;
            }
        }
         
        /* Hoja CPRO */
        $myWorkSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'CPRO');
        $spreadsheet->addSheet($myWorkSheet);
        $sheet = $spreadsheet->setActiveSheetIndex(2);
       
        /* Escribo los encabezados CPRO */
        $sheet->setCellValue('A1', 'Articulo - OCULTAR');
        $sheet->setCellValue('B1', 'Articulo');
        $sheet->setCellValue('C1', 'X');
        $sheet->setCellValue('D1', 'Sub Inventario');
        $sheet->setCellValue('E1', 'X');
        $sheet->setCellValue('F1', 'Localizador');
        $sheet->setCellValue('G1', 'X');
        $sheet->setCellValue('H1', 'X');
        $sheet->setCellValue('I1', 'Cantidad');
        $sheet->setCellValue('J1', 'X');
        $sheet->setCellValue('K1', 'Motivo');
        $sheet->setCellValue('L1', 'X');
        $sheet->setCellValue('M1', 'Referencia');
        $sheet->setCellValue('N1', 'X');
        $sheet->setCellValue('O1', 'Proyecto');
        $sheet->setCellValue('P1', 'X');
        $sheet->setCellValue('Q1', 'X');
        $sheet->setCellValue('R1', 'Tarea Origen');
        $sheet->setCellValue('S1', 'X');
        $sheet->setCellValue('T1', 'X');
        $sheet->setCellValue('U1', 'Tipo Erogacion');
        $sheet->setCellValue('V1', 'X');
        $sheet->setCellValue('W1', 'Direccion');
        $sheet->setCellValue('X1', 'X');
        $sheet->setCellValue('Y1', 'Flexflied');
        $sheet->setCellValue('Z1', 'X');
        $sheet->setCellValue('AA1', 'OC');
        $sheet->setCellValue('AB1', 'X');
        $sheet->setCellValue('AC1', 'Distribucion de OC');
        $sheet->setCellValue('AD1', 'X');
        $sheet->setCellValue('AE1', 'N° OT');
        $sheet->setCellValue('AF1', 'X');
        $sheet->setCellValue('AG1', 'Sup. Aplicada');
        $sheet->setCellValue('AH1', 'X');
        $sheet->setCellValue('AI1', 'Fecha de Aplicacion');
        $sheet->setCellValue('AJ1', 'X');
        $sheet->setCellValue('AK1', 'VCA');
        $sheet->setCellValue('AL1', 'X');
        $sheet->setCellValue('AM1', 'Lote');
        $sheet->setCellValue('AN1', 'Aceptar');
        $sheet->setCellValue('AO1', 'Nuevo');
        $sheet->setCellValue('AP1', 'X');
        $sheet->setCellValue('AQ1', 'X');
         
        /* Datos en columnas de CPRO */
        $linea = 2;
        foreach ($ordenTrabajosReclasificacionesDetalles as $otrDetalles){
            $insumos = $otrDetalles->orden_trabajos_distribucione->orden_trabajos_insumos;
            foreach ($insumos as $insumo) {    

                $sheet->setCellValueByColumnAndRow(1, $linea, $insumo->producto['nombre']);
                $sheet->setCellValueByColumnAndRow(2, $linea, $insumo->producto['codigooracle']);
                $sheet->setCellValueByColumnAndRow(3, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(4, $linea, $insumo->almacene['sub_inventario']);
                $sheet->setCellValueByColumnAndRow(5, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(6, $linea, $insumo->almacene['localizacion']);
                $sheet->setCellValueByColumnAndRow(7, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(8, $linea, '*SL(3)');
                
                $cantidad_aplicada = $insumo->entregas - $insumo->devoluciones;
                
                $sheet->setCellValueByColumnAndRow(9, $linea, $cantidad_aplicada);
                $sheet->setCellValueByColumnAndRow(10, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(11, $linea, 'CPRO'); //Motivo
                $sheet->setCellValueByColumnAndRow(12, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(13, $linea, $otrDetalles->referencia);
                $sheet->setCellValueByColumnAndRow(14, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(15, $linea, $otrDetalles->orden_trabajos_distribucione['proyecto']['segmento']);
                $sheet->setCellValueByColumnAndRow(16, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(17, $linea, '*SL(1)');
                $tarea = $otrDetalles->orden_trabajos_distribucione['proyecto']['cultivo'].$insumo->producto['tarea'];              
                $sheet->setCellValueByColumnAndRow(18, $linea, $tarea); // tarea Origen ????
                $sheet->setCellValueByColumnAndRow(19, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(20, $linea, '*SL(1)');
                $sheet->setCellValueByColumnAndRow(21, $linea, $insumo->producto['tipo_erogacion']); 
                $sheet->setCellValueByColumnAndRow(22, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(23, $linea, '');     // direccion ???
                $sheet->setCellValueByColumnAndRow(24, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(25, $linea, $tab);   //flexfield
                $sheet->setCellValueByColumnAndRow(26, $linea, '*SL(3)');
                $sheet->setCellValueByColumnAndRow(27, $linea, $otrDetalles->orden_trabajo['oc']);  //orden de compra
                $sheet->setCellValueByColumnAndRow(28, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(29, $linea, $otrDetalles->id);  /// Distribucion OC ???
                $sheet->setCellValueByColumnAndRow(30, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(31, $linea, $otrDetalles->orden_trabajo['id']);
                $sheet->setCellValueByColumnAndRow(32, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(33, $linea, '');  // superficie Aplicada.
                $sheet->setCellValueByColumnAndRow(34, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(35, $linea, $otrDetalles->orden_trabajo->fecha->i18nFormat('dd/MM/yyyy'));  // fecha
                $sheet->setCellValueByColumnAndRow(36, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(37, $linea, $otrDetalles->orden_trabajo['id']); // vca 
                $sheet->setCellValueByColumnAndRow(38, $linea, $tab);
                $sheet->setCellValueByColumnAndRow(39, $linea, $otrDetalles->orden_trabajos_distribucione->lote->nombre); // lote 
                $sheet->setCellValueByColumnAndRow(40, $linea, $enter);  
                $sheet->setCellValueByColumnAndRow(41, $linea, '\{DOWN}'); 
                $sheet->setCellValueByColumnAndRow(42, $linea, '*SL(1)');
                $sheet->setCellValueByColumnAndRow(43, $linea, '*DN');
                
                $linea++;
            }
            
        }

        $sheet = $spreadsheet->setActiveSheetIndex(0);
        
        /*  Defino nombre, path y genero el archivo  */
        $date = Time::now();
        $fecha = $date->format('Ymd');
        $archivo = 'reclasificaciones_'.$orden_trabajos_reclasificacione_id.'_'.$fecha.'.xlsx';
        $path = ROOT.DS.'dataload'.DS.$archivo;
        $writer->save($path);    
        
        /* Marco la reclasificacion como ya procesada */
        $reclasificacion = $this->OrdenTrabajosReclasificaciones->get($orden_trabajos_reclasificacione_id);
        $reclasificacion->procesado = 1;
        $this->OrdenTrabajosReclasificaciones->save($reclasificacion);
        
        /** returno to front */
        $this->set(['respuesta' => ['status' => 'success', 'message' => 'Archivo encontrado!', 'archivo'=> $archivo],
                        '_serialize' => 'respuesta']);

        $this->RequestHandler->renderAs($this, 'json');

    }
}
