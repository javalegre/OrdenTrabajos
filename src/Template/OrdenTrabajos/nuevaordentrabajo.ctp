<?php
/**
 * Armo el array para el objeto DataTable
 * Tiene que ir en formato Object, para que pueda ser manipulado en forma
 * correcta.
 */

    $data = array();
    
    /* Armo todos los lotes */
    $Lote = array();
    foreach ($lotes as $lote){
        $Lote[] = $lote;
    }
    
    /* Unidades de Medida */
    $Unidades = array();
    foreach ($unidades as $unidad){
        $Unidades[] = $unidad;
    }
    
    /* Labores*/
    $Labores = array();
    foreach ($labores as $labor){
        $Labores[] = $labor;
    }
    
    /* Centros de Costos */
    $CentroCostos = array();
    foreach ($proyectos as $cc){
        $CentroCostos[] = $cc;
    }
    
    /* Monedas */
    $Monedas = array();
    foreach ($monedas as $moneda){
        $Monedas[] = $moneda;
    }

    /* Almacenes */
    $Almacenes = array();
    foreach ($almacenes as $almacen){
        $Almacenes[] = $almacen;
    }    

    /* Productos */
    $Productos = array();
    foreach ($productos as $producto){
        $Productos[] = $producto;
    }    
    /* Aplicaciones */
    $Tecnicas = array();
    foreach ($tecnicas as $tecnica){
        $Tecnicas[] = $tecnica;
    }    
    
    /* Distribucion de Orden de Trabajos */
    $distribucion = array();
    $distribucion = $ordenTrabajos;
    
    $data['lotes'] = $Lote;
    $data['unidades'] = $Unidades;
    $data['labores'] = $Labores;
    $data['distribucion'] = $distribucion;
    $data['cc'] = $CentroCostos;
    $data['monedas'] = $Monedas;
    $data['almacenes'] = $Almacenes;
    $data['productos'] = $Productos;
    $data['tecnicas'] = $Tecnicas;
    
//    die(debug($distribucion));
//    $data['insumos'] = $insumos;
    echo json_encode($data);