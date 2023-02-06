<?php 

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Cake\I18n\Time;


$tab = '\{TAB}';
$enter = '\{ENTER}';

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet()->setTitle('Ordenes de Trabajo');

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
foreach ($ordenTrabajos as $ot){
    echo $ot;
    
    $descripcion = 'OT:_'.$ot->id.'_'.$ot->proveedore['nombre'].'_'.$ot->orden_trabajos_distribuciones[0]['proyectos_labore']['nombre'].
            '_'.$ot->orden_trabajos_distribuciones[0]['unidade']['nombre'].'_'.$ot->orden_trabajos_distribuciones[0]['lote']['nombre'].
            '_Sup._'.$ot->orden_trabajos_distribuciones[0]['superficie'].'_Precio_'.$ot->orden_trabajos_distribuciones[0]['importe'].
            '__'.$ot->orden_trabajos_certificaciones[0]['user']['nombre'] .'__'.$ot->observaciones;
    
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
    $sheet->setCellValueByColumnAndRow(12, $linea, $descripcion);
    $sheet->setCellValueByColumnAndRow(13, $linea, $tab);
    $sheet->setCellValueByColumnAndRow(14, $linea, $ot->id);
    $sheet->setCellValueByColumnAndRow(15, $linea, $enter);
    $sheet->setCellValueByColumnAndRow(16, $linea, $tab);
    $sheet->setCellValueByColumnAndRow(17, $linea, 'Servicios');
    $sheet->setCellValueByColumnAndRow(18, $linea, $tab);
    $sheet->setCellValueByColumnAndRow(19, $linea, $tab);
    $sheet->setCellValueByColumnAndRow(20, $linea, $ot->orden_trabajos_distribucione[0]['proyectos_labore']['comodity']);
    $sheet->setCellValueByColumnAndRow(21, $linea, $tab);
    $sheet->setCellValueByColumnAndRow(22, $linea, $descripcion);
    $sheet->setCellValueByColumnAndRow(23, $linea, $tab);
    $sheet->setCellValueByColumnAndRow(24, $linea, $ot->orden_trabajos_distribuciones[0]['unidade']['nombre']);
    $sheet->setCellValueByColumnAndRow(25, $linea, $tab);
    $sheet->setCellValueByColumnAndRow(26, $linea, $ot->orden_trabajos_distribuciones[0]['superficie']);
    $sheet->setCellValueByColumnAndRow(27, $linea, $tab);
    $sheet->setCellValueByColumnAndRow(28, $linea, $ot->orden_trabajos_distribuciones[0]['importe']);
    $sheet->setCellValueByColumnAndRow(29, $linea, $tab);
    $sheet->setCellValueByColumnAndRow(30, $linea, $ot['fecha']);
    $sheet->setCellValueByColumnAndRow(31, $linea, $tab);
    $sheet->setCellValueByColumnAndRow(32, $linea, $ot['fecha']);
    $sheet->setCellValueByColumnAndRow(33, $linea, '*ML(767,662)');
    $sheet->setCellValueByColumnAndRow(34, $linea, $tab);
    $sheet->setCellValueByColumnAndRow(35, $linea, $ot->establecimiento['organizacion']);
    $sheet->setCellValueByColumnAndRow(36, $linea, $tab);
    $sheet->setCellValueByColumnAndRow(37, $linea, $enter);
    $sheet->setCellValueByColumnAndRow(38, $linea, '*ML(251,199)');
    $sheet->setCellValueByColumnAndRow(39, $linea, $ot->orden_trabajos_distribuciones[0]['proyecto']['segmento']);
    $sheet->setCellValueByColumnAndRow(40, $linea, $tab);
    $sheet->setCellValueByColumnAndRow(41, $linea, $ot->orden_trabajos_distribuciones[0]['proyectos_labore']['task_number']);
    $sheet->setCellValueByColumnAndRow(42, $linea, $tab);
    $sheet->setCellValueByColumnAndRow(43, $linea, $ot->orden_trabajos_distribuciones[0]['proyectos_labore']['expenditure_type']);
    $sheet->setCellValueByColumnAndRow(44, $linea, $tab);
    $sheet->setCellValueByColumnAndRow(45, $linea, $ot->establecimiento['organizacion2']);
    $sheet->setCellValueByColumnAndRow(46, $linea, $tab);
    $sheet->setCellValueByColumnAndRow(47, $linea, $ot['fecha']);
    $sheet->setCellValueByColumnAndRow(48, $linea, $tab);
    $sheet->setCellValueByColumnAndRow(49, $linea, '*sl(1)');
    $sheet->setCellValueByColumnAndRow(50, $linea, $tab);
    $sheet->setCellValueByColumnAndRow(51, $linea, '*sl(3)');
    $sheet->setCellValueByColumnAndRow(52, $linea, $tab);
    $sheet->setCellValueByColumnAndRow(53, $linea, '');
    $sheet->setCellValueByColumnAndRow(54, $linea, $tab);
    $sheet->setCellValueByColumnAndRow(55, $linea, $ot->orden_trabajos_distribuciones[0]['lote']['nombre']);
    $sheet->setCellValueByColumnAndRow(56, $linea, $enter);
    $sheet->setCellValueByColumnAndRow(57, $linea, '*ML(809,155)');
    $sheet->setCellValueByColumnAndRow(58, $linea, '*ML(751,126)');
    $sheet->setCellValueByColumnAndRow(59, $linea, '*sl(1)');
    $sheet->setCellValueByColumnAndRow(60, $linea, '\^S');
    $sheet->setCellValueByColumnAndRow(61, $linea, '*sl(1)');
    $sheet->setCellValueByColumnAndRow(62, $linea, $enter);
    $sheet->setCellValueByColumnAndRow(63, $linea, '*ML(55,310)');
    $sheet->setCellValueByColumnAndRow(64, $linea, '*sl(1)');
    $sheet->setCellValueByColumnAndRow(65, $linea, $tab);
    $sheet->setCellValueByColumnAndRow(66, $linea, $tab);
    $sheet->setCellValueByColumnAndRow(67, $linea, $tab);
    $sheet->setCellValueByColumnAndRow(68, $linea, $ot->user['nombre']);
    $sheet->setCellValueByColumnAndRow(69, $linea, $tab);
    $sheet->setCellValueByColumnAndRow(70, $linea, '*ML(525,605)');
    $sheet->setCellValueByColumnAndRow(71, $linea, '*sl(1)');
    $sheet->setCellValueByColumnAndRow(72, $linea, '*DN');
    
    $linea++;
}

$writer = new Xlsx($spreadsheet);

/* Armo la fecha */
$date = Time::now();

$file = 'dl-'.$date->format('Ymd');
$path = ROOT.DS.'dataload'.DS.$file.'.xlsx';

$writer->save($path);