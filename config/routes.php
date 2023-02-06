<?php
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;

Router::plugin('Ordenes', ['path' => '/'], function (RouteBuilder $routes) {
        $routes->addExtensions(['pdf']);
        $routes->addExtensions(['json']);
		
        $routes->connect('/orden-trabajos/*', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajos', 'action' => 'index']);
        $routes->connect('/orden-trabajos/:action/*', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajos']);
        $routes->connect('/OrdenTrabajos/:action/*', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajos']);
        
        $routes->connect('/orden-trabajos-distribuciones/*', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajosDistribuciones', 'action' => 'index']);
        $routes->connect('/orden-trabajos-distribuciones/:action/*', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajosDistribuciones']);
        $routes->connect('/OrdenTrabajosDistribuciones/:action/*', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajosDistribuciones']);

        $routes->connect('/orden-trabajos-certificaciones/*', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajosCertificaciones', 'action' => 'index']);
        $routes->connect('/orden-trabajos-certificaciones/:action/*', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajosCertificaciones']);
        $routes->connect('/OrdenTrabajosCertificaciones/:action/*', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajosCertificaciones']);

        $routes->connect('/orden-trabajos-dataloads/*', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajosDataloads', 'action' => 'index']);
        $routes->connect('/orden-trabajos-dataloads/:action/*', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajosDataloads']);
        $routes->connect('/OrdenTrabajosDataloads/:action/*', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajosDataloads']);
        
        $routes->connect('/orden-trabajos-insumos/*', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajosInsumos', 'action' => 'index']);
        $routes->connect('/orden-trabajos-insumos/:action/*', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajosInsumos']);
        $routes->connect('/OrdenTrabajosInsumos/:action/*', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajosInsumos']);
        
        $routes->connect('/orden-trabajos-insumos-entregas/*', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajosInsumosEntregas', 'action' => 'index']);
        $routes->connect('/orden-trabajos-insumos-entregas/:action/*', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajosInsumosEntregas']);
        $routes->connect('/OrdenTrabajosInsumosEntregas/:action/*', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajosInsumosEntregas']);

        $routes->connect('/orden-trabajos-insumos-devoluciones/*', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajosInsumosDevoluciones', 'action' => 'index']);
        $routes->connect('/orden-trabajos-insumos-devoluciones/:action/*', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajosInsumosDevoluciones']);
        $routes->connect('/OrdenTrabajosInsumosDevoluciones/:action/*', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajosInsumosDevoluciones']);

        $routes->connect('/orden-trabajos-reclasificaciones/*', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajosReclasificaciones', 'action' => 'index']);
        $routes->connect('/orden-trabajos-reclasificaciones/:action/*', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajosReclasificaciones']);
        $routes->connect('/OrdenTrabajosReclasificaciones/:action/*', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajosReclasificaciones']);

        $routes->connect('/orden-trabajos-reclasificaciones-detalles/*', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajosReclasificacionesDetalles', 'action' => 'index']);
        $routes->connect('/orden-trabajos-reclasificaciones-detalles/:action/*', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajosReclasificacionesDetalles']);
        $routes->connect('/OrdenTrabajosReclasificaciones-detalles/:action/*', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajosReclasificacionesDetalles']);
        
        $routes->connect('/orden-trabajos-mapeos/*', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajosMapeos', 'action' => 'index']);
        $routes->connect('/orden-trabajos-mapeos/:action/*', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajosMapeos']);
        $routes->connect('/OrdenTrabajosMapeos/:action/*', ['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajosMapeos']);

        $routes->fallbacks(DashedRoute::class);
    }
);