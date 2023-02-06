<?php
/**
 * GenerarExcel VCA
 *
 * Genera un archivo de tipo excel con los movimientos valorizados de insumos.
 *
 * Class Queue
 *
 * Desarrollado para Adecoagro SA.
 * 
 * @package Queue
 *
 * @author Javier Alegre <jalegre@adecoagro.com>
 * @copyright Copyright 2021, Adecoagro
 * @version 1.0.0 creado el 02/08/2021
 */

namespace Ordenes\Shell\Task;
use Queue\Shell\Task\QueueTask;
use Cake\I18n\Time;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Cake\Datasource\ConnectionManager;

/**
 * A Simple QueueTask example.
 */
class QueueGenerarExcelVcaTask extends QueueTask {

    /**
     * Timeout for run, after which the Task is reassigned to a new worker.
     *
     * @var int
     */
    public $timeout = 120;

    /**
     * Number of times a failed instance of this task should be restarted before giving up.
     *
     * @var int
     */
    public $retries = 5;

    /**
     * Stores any failure messages triggered during run()
     *
     * @var string
     */
    public $failureMessage = 'Unable to collect information.';

    /**
     * Example add functionality.
     * Will create one example job in the queue, which later will be executed using run();
     *
     * @return void
     */
    public function add() {
        $this->out('Fetch evekey api data.');
        $this->hr();
        $this->out('You can find the sourcecode of this task in: ');
        $this->out(__FILE__);
        $this->out(' ');
        /*
         * Adding a task of type 'example' with no additionally passed data
         */

    }

    /**
     * Example run function.
     * This function is executed, when a worker is executing a task.
     * The return parameter will determine, if the task will be marked completed, or be requeued.
     *
     * @param array $data The array passed to QueuedTask->createJob()
     * @param int|null $id The id of the QueuedTask
     * @return bool Success
     */
    public function run(array $data, $job_id = null) {

        $this->loadModel('Users');
        $this->loadModel('Reportes');
        
        $connection = ConnectionManager::get('default');
        
        /* Soluciona unos temas de memoria de PHP */
        ini_set('memory_limit', '-1');
        set_time_limit(900);        
        
        $desde = $data['desde'] ? $data['desde'] : '';
        $hasta = $data['hasta'] ? $data['hasta'] : '';
        
        $inicio_reporte = Time::now();
        
        /* 
         * Esto lo separo, porque al poner estos campos de valorizado, le performance de la consulta 
         * cae estrepitosamente
         */
        $valorizado_campo = '';
        $valorizado_tabla = '';
        if ($data['valorizado'] == 1) {
            $valorizado_campo = "costo.precio_unitario        'Costo',";
            $valorizado_tabla = "LEFT JOIN orden_trabajos_insumos_costos costo ON costo.orden_trabajos_insumo_id = oti.id";    
        }
        
        /* Obtengo el usuario del reporte */
        $user = $this->Users->find('all')->where(['id' => $data['user_id']])->first();
        
        $strsql = "SELECT   oti.orden_trabajo_id             'OT',
                            oti.orden_trabajo_id         'VCA',
                            prov.nombre			 'Proveedor',
                            est.organizacion             'ORG',
                            est.nombre			 'Establecimiento',
                            proy.nombre			 'Proyecto',
                            proy.cultivo		 'Cultivo',
                            sec.nombre			 'Sector',
                            lab.nombre			 'Labor',
                            lot.nombre                   'Lote',
                            prod.nombre			 'Producto',
                            uni.nombre                   'UM',
                            ot.fecha			 'Fecha Ord',
                            otd.superficie               'Sup. ORD',
                            oti.dosis			 'Dosis Ord',
                            oti.cantidad 		 'Cantidad Ord',
                            certif.fecha_certificacion   'Fecha Cer',
                            certif.sup_certificada       'Superficie',
                            ROUND(((IFNULL(entr.entregado, 0) - IFNULL(devol.devuelto, 0)) / certif.sup_certificada), 4) 'Dosis',
                            (IFNULL(entr.entregado, 0) - IFNULL(devol.devuelto, 0)) 'Cantidad',
                            IFNULL(entr.entregado, 0)    'Entregas',
                            IFNULL(devol.devuelto, 0)    'Devoluciones',
                            alm.nombre                   'Almacen',
                            esta.nombre			 'Estado',
                            usr.nombre			 'Ordenado por',
                            ".$valorizado_campo."
                            est.organizacion             'Organizacion',
                            prod.codigooracle            'CodigoOracle'
                    FROM orden_trabajos_insumos oti
                            JOIN orden_trabajos   ot   		ON oti.orden_trabajo_id = ot.id
                            JOIN establecimientos est  		ON ot.establecimiento_id = est.id
                            JOIN productos        prod 		ON oti.producto_id = prod.id
                            JOIN proveedores      prov 		ON ot.proveedore_id = prov.id
                            JOIN unidades         uni  		ON oti.unidade_id = uni.id
                            JOIN almacenes        alm  	   ON oti.almacene_id = alm.id
                            JOIN orden_trabajos_estados esta ON ot.orden_trabajos_estado_id = esta.id
                            JOIN users usr ON ot.user_id = usr.id
                            LEFT JOIN orden_trabajos_distribuciones otd ON oti.orden_trabajos_distribucione_id = otd.id and otd.deleted IS NULL
                            LEFT JOIN proyectos proy ON otd.proyecto_id = proy.id
                            LEFT JOIN lotes lot      ON otd.lote_id = lot.id
                            LEFT JOIN sectores sec        ON lot.sectore_id = sec.id
                            LEFT JOIN proyectos_labores lab ON otd.proyectos_labore_id = lab.id
                            LEFT JOIN (SELECT orden_trabajos_insumo_id, SUM(cantidad) AS entregado FROM orden_trabajos_insumos_entregas WHERE deleted IS NULL GROUP BY orden_trabajos_insumo_id) entr ON entr.orden_trabajos_insumo_id = oti.id
                            LEFT JOIN (SELECT orden_trabajos_insumo_id, SUM(cantidad) AS devuelto FROM orden_trabajos_insumos_devoluciones WHERE deleted IS NULL GROUP BY orden_trabajos_insumo_id) devol ON devol.orden_trabajos_insumo_id = oti.id
                            LEFT JOIN (SELECT orden_trabajos_distribucione_id, MAX(created) as fecha_certificacion, SUM(has) AS sup_certificada FROM orden_trabajos_certificaciones WHERE deleted IS NULL GROUP BY orden_trabajos_distribucione_id) certif ON certif.orden_trabajos_distribucione_id = otd.id
                            ".$valorizado_tabla."
                    WHERE 1=1
                            AND oti.deleted IS NULL";
        
        /* Aplico los filtros de Fecha y Establecimientos */
        if ($desde) {
            $strsql = $strsql." AND ot.fecha >= '".$desde."'";
        }
        if ($hasta) {
            $strsql = $strsql." AND ot.fecha <= '".$hasta."'";
        }
        if ($data['establecimientos']) {
            $strsql = $strsql." AND est.id IN (".$data['establecimientos'].")";
        }
        if ($data['proyectos']) {
            $strsql = $strsql." AND proy.id IN (".$data['proyectos'].")";
        }
        
        $insumos =  $connection->execute($strsql)->fetchAll('assoc');
        
        $total_registros = count($insumos);
        
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
        $styleArray = ['font' => ['size' => 36], 'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT]];
        $sheet->getStyle('B1:D3')->applyFromArray($styleArray);

        /* Ahora pongo todo el encabezado en fondo blanco */
        $styleArray = ['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFF']]];
        $sheet->getStyle('A1:R3')->applyFromArray($styleArray);

        $styleSiembra = ['borders' => ['outline' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
                         'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'D8E4BC']]];        
        
        /* Unifico el Ordenado */
        $sheet->mergeCells('M3:P3');
        $sheet->setCellValue('M3', 'ORDENADO');
        
        /* Unifico el Certificado */
        $sheet->mergeCells('Q3:T3');
        $sheet->setCellValue('Q3', 'CERTIFICADO');        
        $styleArray = ['font' => ['size' => 11, 'color' => ['startColor' => ['argb' => '94995F']]],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'D8E4BC']]
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
        $sheet->setCellValue('AA4', 'Valorizado');
        
        /* Le agrego estilos al color del encabezado de las columnas */
        $styleArray = [
            'font' => ['size' => 11, 'color' => ['startColor' => ['argb' => '94995F']]],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
            'borders' => ['outline' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['argb' => 'D8E4BC']]
        ];
        $sheet->getStyle('A4:AA4')->applyFromArray($styleArray);

        $linea = 5;
        $registro_actual = 0;
        foreach ($insumos as $insumo) {
            $sheet->setCellValueByColumnAndRow(1, $linea, $insumo['OT']);
            $sheet->setCellValueByColumnAndRow(2, $linea, $insumo['VCA']);
            $sheet->setCellValueByColumnAndRow(3, $linea, $insumo['Proveedor']);
            $sheet->setCellValueByColumnAndRow(4, $linea, $insumo['ORG']);
            $sheet->setCellValueByColumnAndRow(5, $linea, $insumo['Establecimiento']);
            $sheet->setCellValueByColumnAndRow(6, $linea, $insumo['Proyecto']);
            $sheet->setCellValueByColumnAndRow(7, $linea, $insumo['Cultivo']); /* Cultivo */            
            $sheet->setCellValueByColumnAndRow(8, $linea, $insumo['Sector']); /* Sector / Establecimiento */
            $sheet->setCellValueByColumnAndRow(9, $linea, $insumo['Labor']);
            $sheet->setCellValueByColumnAndRow(10, $linea, $insumo['Lote']);

            /* Datos del Insumo */
            $sheet->setCellValueByColumnAndRow(11, $linea, $insumo['Producto']);
            $sheet->setCellValueByColumnAndRow(12, $linea, $insumo['UM']);
                        
            /* Ordenado */
            $fecha_ordenado = Time::createFromFormat('Y-m-d', $insumo['Fecha Ord']);
            $sheet->setCellValueByColumnAndRow(13, $linea, $fecha_ordenado->i18nFormat('dd/MM/yyyy'));
            $sheet->setCellValueByColumnAndRow(14, $linea, $insumo['Sup. ORD']);
            $sheet->setCellValueByColumnAndRow(15, $linea, $insumo['Dosis Ord']);
            $sheet->setCellValueByColumnAndRow(16, $linea, $insumo['Cantidad Ord']);
            $fecha_certificacion = '';
            if ($insumo['Fecha Cer']) {
                $fecha_certificacion = Time::createFromFormat('Y-m-d H:i:s', $insumo['Fecha Cer'])->i18nFormat('dd/MM/yyyy');
            }
            $sheet->setCellValueByColumnAndRow(17, $linea, $fecha_certificacion);
            $sheet->setCellValueByColumnAndRow(18, $linea, $insumo['Superficie']);
            
            $sheet->setCellValueByColumnAndRow(19, $linea, $insumo['Dosis']);
            $sheet->setCellValueByColumnAndRow(20, $linea, $insumo['Cantidad']);
                        
            /* Reviso si está certificado */
            $sheet->setCellValueByColumnAndRow(21, $linea, $insumo['Entregas']);
            $sheet->setCellValueByColumnAndRow(22, $linea, $insumo['Devoluciones']);
            /* ------------------------------------------------------------- */
            $sheet->setCellValueByColumnAndRow(23, $linea, $insumo['Almacen']);
            $sheet->setCellValueByColumnAndRow(24, $linea, $insumo['Estado']);
            $sheet->setCellValueByColumnAndRow(25, $linea, $insumo['Ordenado por']);
            
            if ($data['valorizado'] == 1) {
                if ($insumo['Costo']) {
                    $sheet->setCellValueByColumnAndRow(27, $linea, $insumo['Costo']);
                } else {
                    if ($fecha_certificacion && $insumo['Cantidad']) {
                        $fecha_certificacion = $now = new Time($insumo['Fecha Cer']);
                        $valorizado = $this->ValorizarProducto($insumo['Organizacion'], $insumo['CodigoOracle'], $fecha_certificacion, '0');
                        $sheet->setCellValueByColumnAndRow(27, $linea, $valorizado);
                    }
                }
            }
            $linea++;

            /* Actualizo el progreso */
            $registro_actual++;
            $this->QueuedJobs->updateProgress($job_id, ($registro_actual) / $total_registros, 'Registros procesados:' . ($registro_actual));
        }
        foreach (range('C', 'Z') as $columnID) {
            //autodimensionar las columnas
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
        $sheet->getColumnDimension('A')->setWidth(8);
        $sheet->getColumnDimension('B')->setWidth(8);

        $time = Time::now();
        $fecha_actual = $time->i18nFormat('yyyy_MM_dd HHmm');
        
        $nombre = 'Listado_VCA_' . $fecha_actual . '.xlsx';
        
        $dir = ROOT.DS.'dataload';
        
        $path = $dir.DS.$nombre;

        $writer->save($path);
        
        /* Ahora lo agrego en la tabla de reportes */
        $reporte = $this->Reportes->newEntity();
        $reporte->reportes_tipo_id = 1;
        $reporte->nombre = $nombre;
        $reporte->path = $path;
        $reporte->user_id = $user->id;
        $reporte->descargado = null;
        $reporte->inicio = $inicio_reporte;
        $reporte->final = $time;
        $reporte->registros = $registro_actual;
        
        $this->Reportes->save($reporte);
        
        return true;
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
        
        /* No hay fecha */
        if (!$fecha) {
            return false;
        }
        
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
}
