<?php 
    /**
     * @var \App\View\AppView $this
     * @var \App\Model\Entity\OrdenTrabajo $ordenTrabajo
     */
    $condicion = $ordenTrabajo->orden_trabajos_condiciones_meteorologica;
    echo $this->Form->control('distribucion', ['type' => 'hidden', 'value' => json_encode($ordenTrabajo)]);
    echo $this->Html->css(['Ordenes.style']);
    
?>
<?= $this->Form->control('email_proveedor', ['type' => 'hidden', 'value' =>  $ordenTrabajo->proveedore->email]) ?>

<div class="row border-bottom white-bg page-heading">
    <div class="col-lg-7 col-md-7 m-t-xs">
        <h3 class="m-t-xs">Orden de Trabajo Nº <?= $ordenTrabajo->id ?><?= $ordenTrabajo->oc ? '<small id="numero-oc">&nbsp;&nbsp;&nbsp; OC: '.$ordenTrabajo->oc.'</small>' : '<small id="numero-oc"></small>' ?></h3>
    </div>
    <div class="col-lg-5 col-md-5 m-t-xs">
        <div class="btn-group pull-right dt-buttons no-margins no-padding">
            <?php
                if ($neighbors['prev']['id']) {
                    echo $this->Html->link('<i class="fa fa-chevron-left"></i>',['action' => 'view', $neighbors['prev']['id']],['type' => 'button','title' => 'Anterior', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
                }
                if ($neighbors['next']['id']) {
                    echo $this->Html->link('<i class="fa fa-chevron-right"></i>',['action' => 'view', $neighbors['next']['id']],['type' => 'button','title' => 'Siguiente', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
                }
                echo $this->Html->link('<i class="fa fa-plus"></i>',['controller' => 'OrdenTrabajos', 'action' => 'add'],['type' => 'button','title' => 'Crear nueva OT', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
                echo $this->Form->button('<i class="fa fa-copy"></i>',['id' => 'duplicar','type' => 'button','title' => 'Duplicar OT', 'data-id' => $ordenTrabajo->id, 'class'=>'btn btn-sm btn-default', 'escape' => false]);
                
                if ($this->Acl->check(['plugin' => 'Ordenes', 'controller' => 'OrdenTrabajos', 'action' => 'vca'])) {
                    echo $this->Html->link('<i class="fa fa-flask"></i>',['controller' => 'OrdenTrabajos', 'action' => 'vca', $ordenTrabajo->id],['type' => 'button','title' => 'Entregar Insumos', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
                }
                echo $this->Form->button('<i class="fa fa-trash"></i>', ['type' => 'button','title' => 'Anular OT', 'data-id' => $ordenTrabajo->id,'class'=>'btn btn-sm btn-default AnularOT', 'escape' => false]);
                if ($ordenTrabajo->orden_trabajos_estado_id == 4 ) {
                    echo $this->Form->button('<i class="fa fa-unlock"></i>', 
                    ['type' => 'button','title' => 'Quitar Certificación', 'data-id' => $ordenTrabajo->id,'class'=>'btn btn-sm btn-default QuitarCertificacion', 'escape' => false]);
                }
                if ( $ordenTrabajo->orden_trabajos_estado_id < 4 ) {
                    echo $this->Html->link('<i class="fa fa-check"></i>',['controller' => 'OrdenTrabajos', 'action' => 'certificarot', $ordenTrabajo->id],['type' => 'button','title' => 'Certificar la OT actual', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
                }
                echo $this->Html->link('<i class="fa fa-list"></i>',['controller' => 'OrdenTrabajos', 'action' => 'index'],['type' => 'button','title' => 'Bandeja de Entrada', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
                echo $this->Form->button('<i class="fa fa-database"></i>', ['type' => 'button', 'title' => 'Ver Historico Oracle', 'id' => 'historico' ,'class'=>'btn btn-sm btn-default', 'escape' => false]);
                
                /* Enviar por mail - TODO: Pablo Snaider */
                if ( $ordenTrabajo->orden_trabajos_estado_id > 1 && $ordenTrabajo->orden_trabajos_estado_id < 5) {
                    echo $this->Form->button('<i class="fa fa-envelope-o"></i>', ['type' => 'button','title' => 'Enviar por Mail', 'id' => 'enviar-email', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
                }    
                if ($ordenTrabajo->orden_trabajos_estado_id < 4){
                    echo $this->Html->link('<i class="fa fa-print"></i>',['controller' => 'OrdenTrabajos', 'action' => 'view', $ordenTrabajo->id, '_ext' => 'pdf'],['type' => 'button','title' => 'Imprimir la OT actual', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
                } else {
                    /* Ya está certificada */
                    echo $this->Html->link('<i class="fa fa-print"></i>',['controller' => 'OrdenTrabajos', 'action' => 'viewCertificada', $ordenTrabajo->id, '_ext' => 'pdf'],['type' => 'button','title' => 'Imprimir la OT actual', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
                }                
            ?>
        </div>        
    </div>
</div>

<div class="ibox float-e-margins">
    <div class="ibox-content">
        <fieldset>
           <div class="col-md-12 no-margins no-padding">
               <div class="col-md-2 m-l-none">
                    <?= $this->Form->control('fecha',['type' => 'text','class' => 'form-control', 'value' => date_format($ordenTrabajo->fecha, 'd/m/Y'), 'disabled','escape' => false]) ?>
               </div>
               <div class="col-md-5 no-margins no-padding">
                    <?= $this->Form->control('establecimiento_id',['type'=> 'text', 'class' => 'form-control', 'readonly' => 'readonly', 'value' => $ordenTrabajo->establecimiento->nombre]) ?>
               </div>
               <div class="col-md-5 m-r-none">
                    <?= $this->Form->control('proveedore_id',['type'=> 'text', 'label' => 'Proveedor', 'class' => 'form-control', 'readonly' => 'readonly', 'value' => $ordenTrabajo->proveedore->nombre ]) ?>
               </div>
           </div>
            <br><br>
            <div class="table-responsive col-sm-12 no-margins no-padding">
                <table id="dt_ordentrabajo" class="table table-bordered table-hover table-striped dataTable contractors-table no-footer" cellspacing="0" width="100%">
                    <thead><?= $this->Html->tableHeaders(['','Proyecto', 'Labor', 'Sector', 'Lote','Has Ord.','Has Certif.','Moneda','Importe Ord.','Importe Certif.','']) ?></thead>
                </table>
            </div>
            <div class="col-md-12 no-margins no-padding">
                <div class="row">
                    <div class="col-md-5 m-l-none m-r-none">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Observaciones
                            </div>
                            <div class="panel-body p-xs">
                                <?= $this->Form->control('observaciones', ['type' => 'textarea', 'class' => 'form-control', 'label' => false ,'value' => $ordenTrabajo->observaciones, 'readonly' => 'readonly']) ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 m-r-none">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Certificaciones
                                <span class="label label-warning-light pull-right"><?= count($ordenTrabajo['orden_trabajos_certificaciones']) ?></span>
                            </div>
                            <div class="panel-body">
                                <?php 
                                    if ($ordenTrabajo->orden_trabajos_estado_id !== 4)
                                        {
                                            echo '<div class="ibox-content ibox-heading">';
                                            $solicitadas = 0;
                                            foreach($ordenTrabajo['orden_trabajos_distribuciones'] as $labores){
                                                $solicitadas = $solicitadas + $labores['superficie'];
                                            }
                                            $certificadas = 0;
                                            foreach ($ordenTrabajo['orden_trabajos_certificaciones'] as $certificacion):
                                                $certificadas = $certificadas + $certificacion['has'];
                                            endforeach;
                                            $pendientes = $solicitadas - $certificadas;
                                            echo '<h3>Tiene '. $pendientes . ' hectareas sin certificar!</h3>';
                                            echo '</div>';
                                        }
                                ?> 
                                <div class="table-responsive">
                                    <table class="table-certificaciones small">
                                        <tbody>
                                            <?php foreach ($ordenTrabajo['orden_trabajos_certificaciones'] as $certificacion): ?>
                                            <tr>
                                                <td class="client-avatar">
                                                    <a href="#" class="pull-left">
                                                        <?= $this->Html->image($certificacion->user['ruta_imagen'], ['alt' => 'user.jpg','title'=>$certificacion->user['nombre']]) ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <strong><?= $certificacion->user['nombre'] ?></strong> ha certificado <?= round($certificacion['has'],2) ?> has. <br>
                                                    <small class="text-muted"><?= $certificacion['observaciones'] ?></small>
                                                </td>
                                                <td>
                                                    <small class="pull-right"><?= date_format($certificacion['fecha_final'],'d/m/Y') ?></small>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>                        
                    </div>
                    <div class="col-md-3 m-r-none">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                Condiciones Meteorológicas
                            </div>
                            <div class="panel-body p-xs">
                                <div class="col-md-12 no-margins no-padding">
                                    <div class="row m-b-xs">
                                        <div class="col-md-6 no-margins no-padding">
                                            <label class="titulo-condiciones">Fecha / Hora</label>
                                        </div>
                                        <div class="col-md-6 no-margins no-padding">
                                            <input type="text" class="form-control edit-input-inline" name="cm_fecha" readonly="" value="<?= $condicion ? $condicion->fecha->i18nFormat('dd/MM/yyyy HH:mm') : ''  ?>">
                                        </div>
                                    </div>
                                    <div class="row m-b-xs">
                                        <div class="col-md-6 no-margins no-padding">
                                            <label class="titulo-condiciones">Temp. (º)</label>
                                        </div>
                                        <div class="col-md-6 no-margins no-padding">
                                            <input type="text" class="form-control edit-input-inline" name="cm_temperatura" id="cm_temperatura" readonly="readonly" value="<?= $condicion ? $condicion->temperatura : ''  ?>">
                                        </div>
                                    </div>                        
                                    <div class="row m-b-xs">
                                        <div class="col-md-6 no-margins no-padding">
                                            <label class="titulo-condiciones">Humedad (%)</label>
                                        </div>
                                        <div class="col-md-6 no-margins no-padding">
                                            <input type="text" class="form-control edit-input-inline" name="cm_humedad" id="cm_humedad" readonly="readonly" value="<?= $condicion ? $condicion->humedad : ''  ?>">
                                        </div>
                                    </div>
                                    <div class="row m-b-xs">
                                        <div class="col-md-6 no-margins no-padding">
                                            <label class="titulo-condiciones">Viento (km/h)</label>
                                        </div>
                                        <div class="col-md-6 no-margins no-padding">
                                            <input type="text" class="form-control edit-input-inline" name="cm_viento" id="cm_viento" readonly="readonly" value="<?= $condicion ? $condicion->viento : ''  ?>">
                                        </div>
                                    </div>
                                    <div class="row m-b-xs">
                                        <div class="col-md-6 no-margins no-padding">
                                            <label class="titulo-condiciones">Dirección</label>
                                        </div>
                                        <div class="col-md-6 no-margins no-padding">
                                            <input type="text" class="form-control edit-input-inline" name="cm_direccion" id="cm_direccion" readonly="readonly" value="<?= $condicion ? $condicion->direccion : ''  ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>                    

                    </div>
                </div>
            </div>            
            <div class="col-md-12 no-margins no-padding">
                <div class="panel panel-default m-b-xs">
                    <div class="panel-body no-margins no-padding">
                        <div class="col-md-12 no-margins no-padding">
                            <div class="col-md-2 m-l-xs no-padding">
                                <?= $this->Form->control('orden_trabajos_estado_id',['type'=>'text','label' => false, 'class' => 'form-control', 'value'=> $ordenTrabajo->orden_trabajos_estado->nombre ,'disabled']) ?>
                            </div>
                            <div class="col-md-5 no-margins no-padding">
                                <div class="m-t">
                                    <span class="m-t-md m-l-md">
                                        <?php
                                            if ( $ordenTrabajo->orden_trabajos_dataload_id ) {
                                                echo 'Incluido en el Dataload <strong>'. $ordenTrabajo->orden_trabajos_dataload_id .'</strong>.';
                                            }
                                            /* Aviso aqui si tiene alquiler de OT */
                                            if ($alquila_implementos) {
                                                echo 'Tiene <strong>'.$this->Html->link('OT '.$alquila_implementos, ['action' => 'view', $alquila_implementos],['class' => 'button', 'target' => '_blank']).'</strong> de alquiler de implementos.';
                                            }
                                        ?>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-1"></div>
                            <div class="col-md-2 pull-right">
                                <?php
                                    if ($ordenTrabajo->orden_trabajos_estado_id < 4){
                                        echo $this->Html->link('<i class="fa fa-check"></i>&nbsp;&nbsp;<span class="bold">Certificar OT</span>', ['controller' => 'OrdenTrabajos', 'action' => 'certificarot', $ordenTrabajo->id],['type'=>'button', 'data-toogle' => 'tooltip', 'title' => 'Certificar la OT actual.', 'class'=>'btn btn-block btn-success m-t-xs m-l-xs','escape' => false]);
                                    }
                                ?>                                
                            </div>
                            <div class="col-md-2 pull-right">
                                <?php 
                                    if ($ordenTrabajo->orden_trabajos_estado_id < 4){
                                        echo $this->Html->link('<i class="fa fa-file-pdf-o"></i>&nbsp;&nbsp;<span class="bold">Generar PDF</span>', ['controller' => 'OrdenTrabajos', 'action' => 'view', $ordenTrabajo->id, '_ext' => 'pdf'],['type'=>'button', 'title' => 'Generar PDF de la OT actual.', 'class'=>'btn btn-block btn-success m-t-xs m-r-xs','escape' => false]);
                                    } else {
                                        /* Ya está certificada */
                                        echo $this->Html->link('<i class="fa fa-file-pdf-o"></i>&nbsp;&nbsp;<span class="bold">Generar PDF</span>', ['controller' => 'OrdenTrabajos', 'action' => 'viewCertificada', $ordenTrabajo->id, '_ext' => 'pdf'],['type'=>'button', 'title' => 'Generar PDF de la OT actual.', 'class'=>'btn btn-block btn-success m-t-xs m-r-xs','escape' => false]);
                                    }
                                ?>
                            </div>                            
                        </div>
                    </div>
                </div>
            </div>
            <?php
                if ($ordenTrabajo->user) {
                    echo '<small>Orden de Trabajo creada por '.$ordenTrabajo->user->nombre.' el '.$ordenTrabajo->created->i18nFormat('dd/MM/yyyy HH:mm');
                }
            ?>
        </fieldset>
    </div>
</div>
<div class="modal otmodal" id="ModalOracle" tabindex="-1" role="dialog"  aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content animated fadeIn">
            <div class="modal-header text-left">
                <div class="pull-right">
                    <?= $this->Form->button('<i class="fa fa-times"></i>', ['class' => 'btn-monitoreo btn-icon-only btn-circle' , 'data-dismiss' => "modal", 'title' => 'Cerrar','type' => 'button', 'escape' => false]) ?>
                </div>
                <h4>Hist&oacute;rico de Oracle</h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="historico_oracle"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para ingreso de correo electronico -->
<div class="modal otmodal" id="ingresar_email" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header text-left">
                <div class="pull-right">
                    <?= $this->Form->button('<i class="fa fa-times"></i>', ['class' => 'btn-monitoreo btn-icon-only btn-circle' , 'data-dismiss' => "modal", 'title' => 'Cancelar','type' => 'button', 'escape' => false]) ?>
                </div>
                <h3>Enviar OT</h3>
            </div>
            <div class="modal-body">
                <?= $this->Form->create('formulario', ['id' => 'formulario']) ?>
                <fieldset>
                    <div class="row">
                        <div class="col-md-11 m-l-md">
                            <div class="m-b-md">
                                <?= $this->Form->control('email', ['class' => 'form-control', 'label' => 'Ingrese Email', 'placeholder'=>'correo@tudominio.com', 'type' => 'email', 'required']) ?>
                                <span class="m-l-xs">El correo ingresado quedará registrado para futuros envios.</span>
                            </div>
                        </div>
                        <div class="col-md-11 m-l-md">
                            <div class="m-b-md">
                                <?= $this->Form->button('Enviar OT', ['class' => "btn btn-w-m btn-primary Enviar", 'type' =>'button']) ?>                         
                            </div>
                        </div>
                    </div>
                </fieldset>
                <?= $this->Form->control('proveedor', ['type' => 'hidden', 'value' =>  $ordenTrabajo->proveedore->id]) ?>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>
<!-- fin del modal -->

<?= $this->Form->control('id',['class'=>'form-control', 'value'=> $ordenTrabajo->id, 'type' => 'hidden' ]) ?>
<?= $this->Html->script(['Ordenes.ordenTrabajoEmailProveedor.js']) ?>
<script type="text/template" id="details-rows-template">
    <table class="table table-bordered table-hover contractors-table contractors-table-details<%= idContractor %>" style="width:100%">
        <thead>
        <tr>
            <th>Producto</th>
            <th>Lote</th>
            <th>Dosis</th>
            <th>Ordenado</th>
            <th>Entrega</th>
            <th>Devolución</th>
            <th>Aplicado</th>
            <th>Dosis Real</th>
            <th>Almacen</th>
        </tr>
        </thead><tbody></tbody>
    </table>
</script>

<script>
    
    /**
    * Lista y edicion de Contratistas.
    *
    * @type {{init}}
    */
    var OrdenesDeTrabajo = function() {

        var $table;
        var dataInsumos = [];
        var dataDistribuciones = [];

        var InicializarDatos = function() {
           /* Esta rutina carga todos los datos al array, para poder usarlo, deberiamos
            * Generar una llamada Ajax con el array, o pasarlo desde el controller en el 
            * formato que deseamos.
            */ 
            dataDistribuciones = [];
            dataInsumos = [];
            
            var datos = JSON.parse($('#distribucion').val());
            /* Ahora paso todos los registros a los lotes */
            if (datos.orden_trabajos_insumos) {
                dataInsumos = datos.orden_trabajos_insumos.map((insumo) => {
                    return {
                            producto: insumo.producto.nombre,
                            unidad: insumo.unidade,
                            lote: insumo.productos_lote ? insumo.productos_lote.nombre : '',
                            dosis: insumo.dosis,
                            cantidad: insumo.cantidad,
                            entrega: insumo.entregas,
                            devolucion: insumo.devoluciones,
                            almacen: insumo.almacene.nombre,
                            utilizado: insumo.entregas - insumo.devoluciones,
                            dosis_aplicada: insumo.dosis_aplicada_real,
                            id: insumo.id,
                            id_distribuciones: insumo.orden_trabajos_distribucione_id 
                        };
                });
            }
            dataDistribuciones = datos.orden_trabajos_distribuciones.map((distribucion) => {
                return {
                        labor: distribucion.proyectos_labore.nombre,
                        unmedida: distribucion.unidade,
                        cc: distribucion.proyecto.nombre,
                        lote: distribucion.lote.nombre,
                        sector: distribucion.lote.sectore ? distribucion.lote.sectore.nombre : '',
                        has: distribucion.superficie,
                        certificadas: distribucion.total_certificado,
                        total: distribucion.superficie,
                        moneda: distribucion.moneda.simbolo,
                        importe: distribucion.importe,
                        insumos: distribucion.proyectos_labore.insumos,
                        labor_id: distribucion.proyectos_labore.id,
                        importe_certificado: distribucion.importe_certificado,
                        id: distribucion.id,
                        oc: distribucion.oracle_oc
                    };
            });
        };

        var IniciarlizarTablas = function() {
             $table = $(".contractors-table").DataTable({
                 pageLength: 20,
                 destroy: true,
                 deferRender: false,
                 data: dataDistribuciones,
                 dom: "<'row'<'col-sm-12'tr>>",
                 ordering: false,
                 buttons: [{ 
                                /* Exportar a Excel */
                                extend:    'excelHtml5',
                                text:      '<i class="fa fa-file-excel-o"></i>',
                                titleAttr: 'Excel'
                            },{
                                /* Exportar CSV */
                                extend: 'csvHtml5',
                                text: '<i class="fa fa-file-text-o"></i>',
                                titleAttr: 'CSV'
                            },{
                                /* Exportar a PDF */
                                extend:    'pdfHtml5',
                                text:      '<i class="fa fa-file-pdf-o"></i>',
                                titleAttr: 'PDF'
                            }],
                 columns: [{
                         className: 'details-control-contractors no-custo no-edit',
                         sortable: false,
                         data: null,
                         defaultContent: '<i class="icon_expand glyphicon glyphicon-menu-down" data-toggle="tooltip" data-placement="top" data-original-title="Insumos utilizados"></i>'                    
                     }, {
                         className: 'no-custo no-edit',
                         data: 'cc',
                         sortable: false,
                         responsivePriority: 1,
                         defaultContent: ''
                     },{
                         data: 'labor',
                         sortable: false,
                         responsivePriority: 2,
                         defaultContent: '',
                         render: function(data, type, full, meta) {
                            if (!data) {
                                 return '';
                            }
                            return `<strong title="${full.unmedida ? full.unmedida.nombre : ''}"><small class="pull-right">(${ full.unmedida ? full.unmedida.codigo : ''})</small></strong>${data}`;
                        }
                     },{
                         data: 'sector',
                         sortable: false,
                         responsivePriority: 2,
                         defaultContent: ''
                     },{
                         data: 'lote',
                         sortable: false,
                         responsivePriority: 1,
                         defaultContent: ''
                     },{
                         data: 'has',
                         class:'text-center',
                         sortable: false,
                         responsivePriority: 1,
                         defaultContent: ''
                     },{
                        data: 'certificadas',
                        class: 'text-center',
                        sortable: false,
                        responsivePriority: 1,
                        defaultContent: '',
                        render: function(data, type, row) {
                            if (!data) {
                                 return '';
                            }
                            return '<span class="badge badge-success">&nbsp;&nbsp;' + data.toFixed(2) + '&nbsp;&nbsp;</span>';
                        }
                     },{
                         data: 'moneda',
                         sortable: false,
                         responsivePriority: 1,
                         defaultContent: ''
                     },{
                         data: 'importe',
                         class:'text-right',                    
                         sortable: false,
                         responsivePriority: 1,
                         defaultContent: '0'                    
                     },{
                         data: 'importe_certificado',
                         class:'text-right',                    
                         sortable: false,
                         responsivePriority: 1,
                         defaultContent: '0',
                         render: function(data, type, row) {
                            if (!data) {
                                return '';
                            }
                            return '<span class="badge badge-success">&nbsp;&nbsp;' + data + '&nbsp;&nbsp;</span>';
                         }
                     },{
                         data: 'oc',
                         className: 'text-center cell-single-action no-custo no-edit',
                         sortable: false,
                         render: function(data, type, row) {
                             if (type === 'display' || type === 'filter'){
                                 if (data) {
                                     return `<div class="btn-group">
                                                       <a href="#" data-id="${data}" title="Historico en oracle de OC ${data}" type="button" class="btn btn-xs btn-white btn-orden-trabajo oracle"><i class="fa fa-database"></i></a>
                                                   </div>`;
                                 }
                             }
                         },                    
                         responsivePriority: 1,
                         defaultContent: '',
                         visible: false

                     },{
                         data: 'insumos',
                         sortable: false,
                         responsivePriority: 1,
                         defaultContent: '',
                         visible: false
                     },{
                         data: 'id',
                         defaultContent: '0',
                         visible: false
                     },{
                         data: 'labor_id',
                         defaultContent: '0',
                         visible: false
                     }]
            });
            
             // Add event listener for opening and closing details
            $table.on('click', 'td.details-control-contractors, .expand_machine', function() {
                var tr = $(this).closest('tr');
                var td = $(this).closest('td');
                 
                var row = $table.row(tr);
                var datatableInsumos = new Array();
                var data = row.data();

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                    $(tr).find(".icon_expand").removeClass('glyphicon-menu-right');
                    $(tr).find(".icon_expand").addClass('glyphicon-menu-down');
                } else {
                     // Open this row
                    $.each(dataInsumos, function(index, row) {
                        if (row.id_distribuciones === data.id) {
                                var dosis_real = row.utilizado / data.has;
                                row.dosis_aplicada = dosis_real.toFixed(4);
                                datatableInsumos.push(row);
                            return true;
                        }
                    });
                    
                    /* Si la linea de distribucion no tiene insumos, oculto la primer columna */
                    if (datatableInsumos.length === 0) {
                        $table.column(td).visible(false);
                        return false;
                    }
                     
                     row.child(formatDetails(data), 'background-white background-child');
                     row.child.show();

                     /* Todos los insumos relacionados con esta OT están en dataTableInsumos
                      * pero solo debo recuperar los que están relacionados a esta labor en 
                      * particular, a traves del campo orden_trabajos_distribucione_id */
                     $subTable = $(".contractors-table-details" + row.data().id).DataTable({
                         pageLength: 25,
                         data: datatableInsumos,
                         autoWidth: false,
                         deferRender: false,
                         dom: 'rt',
                         columns: [{
                                 data: 'producto',
                                 defaultContent: '',
                                 sortable: false,
                                 render: function(data, type, full, meta) {
                                    if (!data) {
                                         return '';
                                    }
                                    return `<strong title="${full.unidad ? full.unidad.nombre : ''}"><small class="pull-right">(${ full.unidad ? full.unidad.codigo : ''})</small></strong>${data}`;
                                }
                             },
//                             {
//                                 data: 'unidad',
//                                 sortable: false,
//                                 defaultContent: ''
//                             },
                             {
                                 data: 'lote',
                                 sortable: false,
                                 defaultContent: ''
                             }, {
                                 data: 'dosis',
                                 defaultContent: '',
                                 sortable: false,
                                 class: 'text-center'
                             },{
                                 data: 'cantidad',
                                 defaultContent: '',
                                 sortable: false,
                                 class: 'text-center'
                             },{
                                 data: 'entrega',
                                 sortable: false,
                                 defaultContent: '',
                                 class: 'text-center'
                             },{
                                 data: 'devolucion',
                                 sortable: false,
                                 defaultContent: '',
                                 class: 'text-center'
                             },{
                                 data: 'utilizado',
                                 sortable: false,
                                 defaultContent: '',
                                 class: 'text-center'
                             },{
                                 data: 'dosis_aplicada',
                                 sortable: false,
                                 defaultContent: '',
                                 class: 'text-center',
                                 render: function(data, type, row) {
                                     if (data > (row.dosis * 1.05) || data < (row.dosis / 1.05) && (data !== 0)){
                                         return '<span class="badge badge-danger">&nbsp;&nbsp;' + data + '&nbsp;&nbsp;</span>';
                                     } else {
                                         return data;
                                     }
                                 }
                             },{
                                 data: 'almacen',
                                 sortable: false,
                                 class: 'text-right',
                                 defaultContent: ''                 
                             },{
                                 data: 'id',
                                 sortable: false,
                                 visible: false,
                                 responsivePriority: 1,
                                 defaultContent: ''
                             },{
                                 data: 'distribucionId',
                                 sortable: false,
                                 visible: false,
                                 defaultContent: row.data().id
                             },{
                                 data: 'temporalId',
                                 defaultContent: 0,
                                 visible: false
                             }]
                     });

                     tr.addClass('shown');
                     $(tr).find(".icon_expand").removeClass('glyphicon-menu-down');
                     $(tr).find(".icon_expand").addClass('glyphicon-menu-right');              
                 }
             });
            /* Expando todas las lineas de Insumos */
            $table.rows(':not(.parent)').nodes().to$().find('td:first-child').trigger('click');

         };

        var formatDetails = function(callback) {
             var templateDetails = _.template($("#details-rows-template").text());
             var lt_dataTableMachines = new Array();

             return templateDetails({
                 idContractor: callback.id,
                 machines: lt_dataTableMachines
             });

         };
         
        const init = function () {
            $("#table-loader").removeClass('hidden');
            InicializarDatos();
            IniciarlizarTablas();
        }();
    }();
    
    $('.AnularOT').on('click', function (e) {
        e.preventDefault();
        var id = e.currentTarget.getAttribute('data-id');
        
        fetch('/orden-trabajos/delete/' + id )
            .then( res => res.json() )
            .then( data => {
                if (data['status'] === 'error') { /* Existe un error */
                    for(var i=0;i<data['message'].length;i++){
                        toastr.error(data['message'][i]);
                    }
                } else {
                    toastr.info(data['message']); /* Se anuló correctamente */
                    $(this).closest('tr').remove();
                }
            })
            .catch( function(err) {
                console.log( err );
            });
    });
    
    $('#duplicar').on('click', function (e) {
        e.preventDefault();
        var id = e.currentTarget.getAttribute('data-id');
        fetch('/orden-trabajos/duplicar/' + id )
            .then( res => res.json() )
            .then( data => {
                if (data['status'] === 'success') { /* Existe un error */
                    var ruta = "http://"+ document.domain +"/OrdenTrabajos/edit/" + data.data.id;
                    window.location.href = ruta;
                }
            })
            .catch( function(err) {
                console.log( err );
            });        
    });
    
    /* Consulto historicos en oracle y si no está la OC, lo asigna a la OT */
    $('#historico').on('click', function (e) {
        var id = $('#id').val();
        e.preventDefault();
        fetch('/orden-trabajos/consultar-historico-oracle/' + id )
            .then( res => res.json() )
            .then( data => {
                if (data.length > 0) {
                        var tabla = '';
                        var NumeroOC = null;
                        tabla = '<table class="table table-bordered table-hover table-striped dataTable no-footer"><thead>' +
                                '<tr class="text-center"><th>Sec</th><th>Fecha</th><th>Accion</th><th>Realizado por</th><th>Nota</th></tr></thead><tbody>';
                        $.each(data, function ( index, problema) {
                            var Nombre = problema.EMPLOYEE_NAME ? problema.EMPLOYEE_NAME : problema.NOMBRE;
                            if (NumeroOC == null) {
                                NumeroOC = problema.OC;
                            }
                            tabla = tabla + '<tr>' +
                                                '<td><span class="text-capitalize">' + problema.SEQUENCE_NUM + '</span></td>' +
                                                '<td class="text-right"><span>' + problema.ACTION_DATE + '</span></td>' + 
                                                '<td><span>' + problema.ACTION_CODE_DSP + '</span></td>' + 
                                                '<td><span>' + Nombre + '</span></td>' +
                                                '<td><span>' + (problema.NOTE ? problema.NOTE : '') + '</span></td>' + 
                                            '</tr>';
                        });
                        tabla = tabla + '</tbody></table>';
                        $('#historico_oracle').html( tabla );
                        $('#numero-oc').html('&nbsp;&nbsp;&nbsp;OC: ' + NumeroOC);
                    } else  {
                        $('#historico_oracle').html('');
                    }            
                    $('#ModalOracle').modal('show');
            })
            .catch( function(err) {
                console.log( err );
            });            
    });
        
    $('.QuitarCertificacion').on('click', function (e) {
        e.preventDefault();
        var id = e.currentTarget.getAttribute('data-id');
        fetch(`/orden-trabajos/quitar-certificacion/${id}`)
            .then( res => res.json() )
            .then( data => {
                if (data['status'] === 'error') { /* Existe un error */
                    toastr.error(data['message']);
                } else {
                    toastr.options.onHidden = function(){
                        var ruta = "http://"+ document.domain +"/OrdenTrabajos/view/" + id;
                        window.location.href = ruta;
                    };
                    toastr.info('Se quitó la finalización de la OT correctamente.');
                }
            })
            .catch( function(err) {
                console.log( err );
            });
    });  
    
</script>