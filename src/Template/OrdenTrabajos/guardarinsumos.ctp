<?php
/**
 * Armo el array para el objeto DataTable
 * Tiene que ir en formato Object, para que pueda ser manipulado en forma
 * correcta.
 */
    $linea = array();
    
    $linea['producto'] = $registro['producto']['nombre'];
    $linea['unidad'] = $registro['unidade']['nombre'];
    $linea['almacen'] = $registro['almacene']['nombre'];
    $linea['id'] = $registro['id'];
    echo json_encode($linea);
?>
   