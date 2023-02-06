<?php
    /**
     * @var \App\View\AppView $this
     * @var \App\Model\Entity\OrdenTrabajo $ordenTrabajo
     */
?>
<div class="row border-bottom white-bg page-heading">
    <div class="col-lg-8">
        <h2>Vale de Consumo</h2>
    </div>
    <div class="col-lg-4 text-right numero-ot">
        <span class="numero-ot">Vale Consumo Nº <?= $ordenTrabajo->id ?></span>
        <div class="btn-group pull-right dt-buttons no-margins no-padding">
            <?php
                //if ( $this->Acl->check(['controller' => 'OrdenTrabajos', 'action' => 'view']) ){
                    echo $this->Html->link('<i class="fa fa-list"></i>',['controller' => 'OrdenTrabajos', 'action' => 'view', $ordenTrabajo->id],['type' => 'button','title' => 'Volver a la OT', 'class'=>'btn btn-sm btn-default', 'escape' => false]);
                //}
            ?>
        </div>        
    </div>
</div>

<?= $this->Form->create($ordenTrabajo) ?>
<div class="ibox float-e-margins">
    <div class="ibox-content">
        <fieldset>
            <div class="col-md-12 no-margins no-padding">
                <div class="col-md-2 m-l-none">
                     <?= $this->Form->control('fecha',['type' => 'text','class' => 'form-control col-md-2', 'value' => date_format($ordenTrabajo->fecha, 'd/m/Y'), 'disabled','escape' => false]) ?>
                </div>
                <div class="col-md-5 no-margins no-padding">
                    <?= $this->Form->control('establecimiento',['type'=> 'text', 'value' => $ordenTrabajo->establecimiento['nombre'] , 'class' => 'form-control ', 'disabled']) ?>
                </div>
                <div class="col-md-5 m-r-none">
                     <?= $this->Form->control('proveedore',['type'=> 'text', 'label' => 'Proveedor','value' => $ordenTrabajo->proveedore['nombre'],'class' => 'form-control', 'disabled']) ?>
                </div>
            </div>
            <br><br>
            <div class="table-responsive col-sm-12 no-margins no-padding">
                <table id="dt_ordentrabajo" class="table table-bordered table-hover table-striped contractors-table dataTable no-footer" cellspacing="0" width="100%">
                    <thead><?= $this->Html->tableHeaders(['id','id-producto','Producto', 'unidade_id','U.M.','Dosis','Ordenado','Entregado','Devolución','Aplicado','Almacen','']) ?></thead>
                </table>
            </div>


            <div class="col-md-12"><br></div>
            <div class="col-md-12 no-margins no-padding">
                <?= $this->Form->control('observaciones', ['type' => 'textarea', 'class' => 'form-control summernote']) ?>
            </div>
        </fieldset>
    </div>
</div>

<?= $this->Form->control('distribucion', ['type' => 'hidden', 'class' => 'form-control', 'value' => $ordenTrabajo ]) ?>
<?= $this->Form->control('IdEstablecimiento', ['type' => 'hidden','value' => $ordenTrabajo->establecimiento_id ]) ?>
<?= $this->Form->control('id',['class'=>'form-control', 'value'=> $ordenTrabajo->id ]) ?>
<?= $this->Form->end() ?>

<?php
    echo '<script type="text/template" id="row-actions-insumos">
            <div class="btn-group">
                <a class="btn btn-xs btn-primary entrega" href="#" data-toggle ="tooltip" title = "Entrega de Insumos"><i class="fa fa-upload"></i></a>
                <a class="btn btn-xs btn-success devolucion" href="#" data-toggle ="tooltip" title = "Devolucion de Insumos"><i class="fa fa-download"></i></a>
            </div>
        </script>';
        '<div><span class="pull-right">
            <a href="#" data-id="${ row.id }" title="Cambiar articulo" type="button" class="btn btn-xs btn-white btn-orden-trabajo CambiarArticulo"><i class="fa fa-retweet"></i></a>
            <a href="#" data-id="${ row.id }" title="Desasociar BB" type="button" class="btn btn-xs btn-white btn-orden-trabajo ListaBigBags"><i class="fa fa-retweet"></i></a>        
        </span> ${data}</div>'
?>
<script type="text/template" id="details-rows-template">
    <table class="table table-detalle table-sm contractors-table contractors-table-details<%= idContractor %>" style="width:100%">
        <thead>
        <tr>
            <th>Fecha</th>
            <th>Producto</th>
            <th>Entrega</th>
            <th>Devolucion</th>
            <th>Almacen</th>
            <th>Transaccion</th>
            <th>Entregado por</th>
        </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</script>

<!-- Modal Labor -->
<form id="ModalEntrega">
    <div class="modal otmodal" id="EntregarInsumo" tabindex="-1" role="dialog"  aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content animated fadeIn">
                <div class="modal-header">
                    <h3><span id="titulo-modal-ins"></span></h3>
                    <small class="font-bold" id="sub-titulo-modal-ins"></small>
                </div>
                <div class="modal-body">
                    <fieldset>
                        <div>&nbsp;</div>
                        <div class="row col-md-12 m-l-none m-r-none">
                            <div class="form-group">
                                <label class="col-md-3 control-label m-t-xs">Fecha</label>
                                <div class="col-md-7">
                                    <?= $this->Form->control('fecha-entrega', ['label' => false, 'class' => 'form-control']) ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label m-t-xs">Ordenado</label>
                                <div class="col-md-7">
                                    <?= $this->Form->control('ordenado-ins', ['label' => false, 'class' => 'form-control', 'readonly']) ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label m-t-xs">Entregado</label>
                                <div class="col-md-7">
                                    <?= $this->Form->control('entregado-ins', ['label' => false, 'class' => 'form-control required number']) ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label m-t-xs">Almacén</label>
                                <div class="col-md-7">
                                    <?= $this->Form->control('almacen-entrega', ['type' => 'select', 'class' => 'form-control select2', 'label' => false]) ?>
                                </div>
                            </div>                            
                            <?= $this->Form->control('distribucion-id-ins', ['type' => 'hidden', 'class' => 'form-control']) ?>
                            <?= $this->Form->control('producto-id', ['type' => 'hidden', 'class' => 'form-control']) ?>
                            <?= $this->Form->control('unidade-id', ['type' => 'hidden', 'class' => 'form-control']) ?>
                            
                        </div>
                        <div>&nbsp;</div>
                    </fieldset>
                </div>
                <div class="modal-footer">
                    <div class="row col-md-12 no-margins no-padding">
                        <div class="col-md-5 no-margins no-padding">
                            <button type="button" class="btn btn-default btn-block" data-dismiss="modal">Cancelar</button>
                        </div>
                        <div class="col-md-2"></div>
                        <div class="col-md-5 no-margins no-padding">
                            <?= $this->Form->button('<i class="fa fa-upload"></i>&nbsp;&nbsp;&nbsp;&nbsp;<span class="bold">Entregar</span>', ['type' => 'button', 'class'=>'btn btn-primary entregar btn-block', 'escape' => false]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<form id="ModalDevolucion">
    <div class="modal otmodal" id="DevolverInsumo" tabindex="-1" role="dialog"  aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content animated fadeIn">
                <div class="modal-header">
                    <h3><span id="titulo-modal-dev"></span></h3>
                </div>
                <div class="modal-body">
                    <fieldset>
                        <div>&nbsp;</div>
                        <div class="row col-md-12 m-l-none m-r-none">
                            <div class="form-group">
                                <label class="col-md-3 control-label m-t-xs">Fecha</label>
                                <div class="col-md-7">
                                    <?= $this->Form->control('fecha-devolucion', ['label' => false, 'class' => 'form-control']) ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label m-t-xs">Entregado</label>
                                <div class="col-md-7">
                                    <?= $this->Form->control('entregado-dev', ['label' => false, 'class' => 'form-control', 'readonly']) ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label m-t-xs">Devuelve</label>
                                <div class="col-md-7">
                                    <?= $this->Form->control('devuelto-dev', ['label' => false, 'class' => 'form-control required number']) ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-3 control-label m-t-xs">Almacén</label>
                                <div class="col-md-7">
                                    <?= $this->Form->control('almacen-devolucion', ['type' => 'select', 'class' => 'form-control select2', 'label' => false]) ?>
                                </div>
                            </div>                            
                            <?= $this->Form->control('distribucion-id-dev', ['type' => 'hidden', 'class' => 'form-control']) ?>
                            <?= $this->Form->control('producto-id-dev', ['type' => 'hidden', 'class' => 'form-control']) ?>
                        </div>
                        <div>&nbsp;</div>
                    </fieldset>
                </div>
                <div class="modal-footer">
                    <div class="row col-md-12 no-margins no-padding">
                        <div class="col-md-5 no-margins no-padding">
                            <button type="button" class="btn btn-default btn-block" data-dismiss="modal">Cancelar</button>
                        </div>
                        <div class="col-md-2"></div>
                        <div class="col-md-5 no-margins no-padding">
                            <?= $this->Form->button('<i class="fa fa-upload"></i>&nbsp;&nbsp;&nbsp;&nbsp;<span class="bold">Devolver</span>', ['type' => 'button', 'class'=>'btn btn-primary devolver btn-block', 'escape' => false]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Modal cambio articulo -->
<form id="ModalCambioArticulo">
    <div class="modal otmodal" id="CambiarArticulo" tabindex="-1" role="dialog"  aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content animated fadeIn">
                <div class="modal-header">
                    <h3>Cambio de Articulo</h3>
                </div>
                <div class="modal-body">
                    <fieldset>
                        <div class="row col-md-12 m-l-none m-r-none">
                            <div class="form-group">
                                <div class="col-md-12">
                                    <?= $this->Form->control('productos', ['type' => 'select', 'class' => 'form-control select2', 'label' => 'Producto']) ?>
                                    <?= $this->Form->control('productos-observaciones', ['type' => 'textarea', 'class' => 'form-control', 'label' => 'Motivo del cambio']) ?>
                                    <?= $this->Form->control('producto-linea',['type' => 'hidden']) ?>
                                </div>
                            </div>                            
                        </div>
                    </fieldset>
                </div>
                <div class="modal-footer">
                    <div class="row col-md-12 no-margins no-padding">
                        <div class="col-md-5 no-margins no-padding">
                            <button type="button" class="btn btn-default btn-block" data-dismiss="modal">Cancelar</button>
                        </div>
                        <div class="col-md-2"></div>
                        <div class="col-md-5 no-margins no-padding">
                            <?= $this->Form->button('<i class="fa fa-retweet"></i>&nbsp;&nbsp;&nbsp;&nbsp;<span class="bold">Aplicar cambio</span>', ['type' => 'button', 'class'=>'btn btn-primary cambiarArticulo btn-block', 'escape' => false]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Modal para mostrar los big bags -->
<div class="modal otmodal" id="modalBigBags" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        
        let id = $('#id').val();
        fetch(`/orden-trabajos/vca/${ id }.json`)
            .then( res => res.json() )
            .then( data => {
                /* Data recibida de la OT */
                ListadoAlmacenes (data);
            }); 
        
        const ListadoAlmacenes = ( almacenes ) => {
            $("#almacen-devolucion").select2({
                theme: "bootstrap",
                data: almacenes,
                width: '100%',
                dropdownParent: $('#DevolverInsumo'),
                templateSelection: function ( data ) {
                    let $container = $(`<div><span class='pull-right'>${data.localizacion}</span> ${data.nombre}</div>`);
                    return $container;
                },
                templateResult: function ( data ) {
                    let $container = $(`<div><span class='pull-right'>${data.localizacion}</span> ${data.nombre}</div>`);
                    return $container;
               }
            });
            $("#almacen-entrega").select2({
                theme: "bootstrap",
                data: almacenes,
                width: '100%',
                dropdownParent: $('#EntregarInsumo'),
                templateSelection: function ( data ) {
                    let $container = $(`<div><span class='pull-right'>${data.localizacion}</span> ${data.nombre}</div>`);
                    return $container;
                },
                templateResult: function ( data ) {
                    let $container = $(`<div><span class='pull-right'>${data.localizacion}</span> ${data.nombre}</div>`);
                    return $container;
               }
            });
        };
        
        $("#fecha-entrega").val(moment().format('DD/MM/YYYY'));
        $("#fecha-devolucion").val(moment().format('DD/MM/YYYY'));
        
        $('#entregado-ins').numeric();
        
        /* Inicio el dataTable */
        initTable();
        
        $('#EntregarInsumo').on('shown.bs.modal', function() {
            /* Le doy el foco */
            $('#entregado-ins').focus();
        });
        
        $('#DevolverInsumo').on('shown.bs.modal', function() {
            /* Le doy el foco */
            $('#devuelto-dev').focus();
        });

        /* Realizo el cambio solicitado */
        $('.cambiarArticulo').click(function() {
            
            if (!$('#productos').val()) {
                toastr.error("No se eligió el producto.");
                return;
            }
            
            /* Envío el cambio solicitado al controlador */
            let linea = JSON.parse($('#producto-linea').val());
            const data = new FormData(document.getElementById('ModalCambioArticulo'));
            
            fetch('/orden-trabajos-insumos/edit/' + linea.id + '.json', {
                    method: 'POST',
                    body: data
                })
                .then( res => res.json() )
                .then( data => {
                    if (data['status'] === 'error') { /* Existe un error */
                        toastr.error(data['message']);
                    } else {
                        toastr.info(data['message']); /* Se anuló correctamente */
                    }
                })
                .catch( function(err) {
                    console.log( err );
                });
                $('#CambiarArticulo').modal('hide');
        });

        /* Realizo el cambio solicitado */
        $('.cambiarArticulo').click(function() {
            
            if (!$('#productos').val()) {
                toastr.error("No se eligió el producto.");
                return;
            }
            
            /* Envío el cambio solicitado al controlador */
            let linea = JSON.parse($('#producto-linea').val());
            const data = new FormData(document.getElementById('ModalCambioArticulo'));
            
            fetch('/orden-trabajos-insumos/edit/' + linea.id + '.json', {
                    method: 'POST',
                    body: data
                })
                .then( res => res.json() )
                .then( data => {
                    if (data['status'] === 'error') { /* Existe un error */
                        toastr.error(data['message']);
                    } else {
                        toastr.info(data['message']); /* Se anuló correctamente */
                    }
                })
                .catch( function(err) {
                    console.log( err );
                });
                $('#CambiarArticulo').modal('hide');
        });
        
        $('.entregar').click(function(){
            /* Verifico que todos los datos esten bien */
            jQuery.validator.messages.required = 'Esta campo es obligatorio.';
            jQuery.validator.messages.number = 'Esta campo debe ser num&eacute;rico.'; 
            jQuery.validator.messages.date = 'No es una fecha v&aacute;lida.'; 

            var validado = $("#ModalEntrega").valid();
            if (validado){
               /* El formulario esta correcto, asi que ahora lo guardo */
                const data = new FormData(document.getElementById('ModalEntrega'));
                fetch(`/orden-trabajos-insumos-entregas/add`, {
                    method: 'POST',
                    body: data
                }).then( res => res.json())
                .then( data => {
                    if (data.status == 'success') {
                        location.reload(); 
                        return;
                    }
                });               
               $('#EntregarInsumo').modal('hide');
            }            
        });
        
        $('.devolver').click(function(){
            /* Verifico que todos los datos esten bien */
            jQuery.validator.messages.required = 'Esta campo es obligatorio.';
            jQuery.validator.messages.number = 'Esta campo debe ser num&eacute;rico.'; 
            jQuery.validator.messages.date = 'No es una fecha v&aacute;lida.'; 
                
            var validado = $("#ModalDevolucion").valid();
            if(validado){
                console.log('No entrego el producto');
               /* El formulario esta correcto, asi que ahora lo guardo */
                $.ajax({
                    type:"POST", 
                    async:true,
                    data: $('#ModalDevolucion').serialize(),
                    url:"/OrdenTrabajosInsumosDevoluciones/add",    /* Pagina que procesa la peticion   */
                    success:function (data){
                        if ( data ) {
                            if (data.status === 'success') {
                                /* Es horrible usar esta funcion, pero es temporal */
                               location.reload();
                               return;
                            }
                            toastr.error(data.message);
                        }
                    }, error:function (data){
                        console.log('Ocurrió un error');
                    }
                });
               
               $('#DevolverInsumo').modal('hide');
            }            
        });        
    });
    
    var initTable = function() {
        
        var distribucion = JSON.parse($('#distribucion').val());
        
        var detalle = initDataContractors(distribucion['orden_trabajos_insumos']);
        
        var dataInsumos = detalle['Insumos'];
        var dataMovimientos = detalle['Movimientos'];
        
        var actionsTemplate = _.template($("#row-actions-insumos").text());        
        var table = $('.dataTable').DataTable({
            pageLength: 20,
            responsive: true,
            data: dataInsumos,
            dom: "<'row'<'col-sm-12 no-margin no-padding'tr>><'row'<'col-sm-6'i><'col-sm-6'p'>>",
            columnDefs: [{
                targets: [ 0, 1, 3 ],
                visible: false,
                searchable: false
            }],
            buttons: [
                {  /* Agregar una nueva labor */
                   text: "<i class='fa fa-plus'></i>",
                   titleAttr: "Nueva Orden de Trabajo",
                   className: "btn nueva-labor",
                   action: function () {
                    $(location).attr('href','/OrdenTrabajos/add');}
                },
                {text:"<i class='fa fa-file-excel-o'></i>", titleAtrr: "Excel", extend: 'excel' },
                {extend: 'pdf', title: 'ExampleFile', titleAtrr: "PDF", text:"<i class='fa fa-file-pdf-o'></i>"},
                {extend: 'print',
                    customize: function (win) {
                        $(win.document.body).addClass('white-bg');
                        $(win.document.body).css('font-size', '8px');
                        $(win.document.body).find('table')
                            .addClass('compact')
                            .css('font-size', 'inherit');
                    }
                }
            ],
            columns: [
                {
                    data: 'id',
                    visible: false,
                    responsivePriority: 1,
                    defaultContent: ''   
                },{
                    data: 'idproducto',
                    visible: false,
                    responsivePriority: 2,
                    defaultContent: ''
                },{
                    className: 'details-control-contractors no-custo no-edit',                    
                    data: 'producto',
                    sortable: false,
                    responsivePriority: 2,
                    defaultContent: '',
                    render: function(data, type, row) {
                            if (data) {
                                return `<div><span class='pull-right'>
                                            <a href="#" data-id="${ row.id }" title="Cambiar articulo" type="button" class="btn btn-xs btn-white btn-orden-trabajo CambiarArticulo"><i class="fa fa-retweet"></i></a>
                                            <a href="#" data-id="${ row.id }" title="Desasociar Big Bags" type="button" class="btn btn-xs btn-white btn-orden-trabajo ListaBigBags"><i class="fa fa-qrcode"></i></a>
                                        </span> ${data}</div>`;
                            }
                        }
                },{
                    data: 'idunidad',
                    visible: false,
                    responsivePriority: 1,
                    defaultContent: ''
                },{
                    data: 'unidad',
                    sortable: false,
                    responsivePriority: 1,
                    defaultContent: ''
                },{
                    data: 'dosis',
                    class: 'text-right',
                    sortable: false,
                    responsivePriority: 1,
                    defaultContent: ''
                },{
                    data: 'cantidad',
                    class:'text-right',
                    sortable: false,
                    responsivePriority: 1,
                    defaultContent: ''
                },{
                    data: 'entrega',
                    class:'text-right',
                    sortable: false,
                    responsivePriority: 1,
                    defaultContent: ''
                },{
                    data: 'devolucion',
                    class:'text-right',                    
                    sortable: false,
                    responsivePriority: 1,
                    defaultContent: '0'                    
                },{
                    data: 'utilizado',
                    sortable: false,
                    responsivePriority: 1,
                    defaultContent: ''
                },{
                    data: 'almacen',
                    sortable: false,
                    responsivePriority: 1,
                    defaultContent: ''
                },{
                    data: '',
                    defaultContent: '',
                    sortable: false,
                    className: 'cell-double-action no-custo no-edit',
                    render: function(data, type, row) {
                        return actionsTemplate({});
                    },
                    responsivePriority: 1                   
                },{
                    data: 'id_distribuciones',
                    defaultContent: '0',
                    visible: false
                }]
        });        
        
        /* Evento Entrega */
        $(document).on("click", ".entrega", function(){	
            var tr = $(this).closest('tr');
            var row = table.row( tr ).data();
            
            $('#distribucion-id-ins').val(row['id']);
            $('#producto-id').val(row['idproducto']);
            $('#unidade-id').val(row['idunidad']);
            
            $('#titulo-modal-ins').text(row['producto']);
            $('#ordenado-ins').val(row['cantidad']); /* Ordenado */
            
            $('#EntregarInsumo').modal('show');
        });
        
        /* Evento Devolucion */
        $(document).on("click", ".devolucion", function(){	
            var tr = $(this).closest('tr');
            var row = table.row( tr ).data();
            console.log('Devolución: ', row);
            $('#distribucion-id-dev').val(row['id']);
            $('#producto-id-dev').val(row['idproducto']);
            
            $('#titulo-modal-dev').text(row['producto']);
            $('#ordenado-dev').val(row['cantidad']); /* Ordenado */
            $('#entregado-dev').val(row['entrega']); /* Producto entregado */
            
            $('#DevolverInsumo').modal('show');
        });        
        
        /* Muestro el listado de Big Bags asociados */
        $(document).on("click", ".ListaBigBags", function(e) {
            e.preventDefault();
            e.stopPropagation();
            var id = e.currentTarget.getAttribute('data-id');
            
            $('#modalBigBags .modal-content').load(`/orden-trabajos-insumos/index/${id}`, function(){
                $('#modalBigBags').modal({show:true});
            });
        });
        
        // Add event listener for opening and closing details
        table.on('click', 'td.details-control-contractors, .expand_machine', function() {
            var tr = $(this).closest('tr');
            var row = table.row(tr);
            
            var movimientos = new Array();
            var data = row.data();
            
            // Open this row
            row.child(formatDetails(data), 'background-white background-child');
            row.child.show();
            
            /* Todos los insumos relacionados con esta OT están en dataTableInsumos
             * pero solo debo recuperar los que están relacionados a esta labor en 
             * particular, a traves del campo orden_trabajos_distribucione_id */
            $.each(dataMovimientos, function(index, row) {
                if (row.insumo_id === data.id) {
                        movimientos.push(row);
                    return true;
                }
            });
            var $subTable = $(".contractors-table-details" + row.data().id).DataTable({
                paging: false,
                data: movimientos,
                autoWidth: false,
                deferRender: false,
                dom: 'rt',
                columns: [
                    {
                        data: 'fecha',
                        defaultContent: '',
                        sortable: false
                    },{
                        data: 'producto',
                        sortable: false,
                        defaultContent: ''
                    },{
                        data: 'entrega',
                        defaultContent: '',
                        sortable: false
                    },{
                        data: 'salida',
                        defaultContent: '',
                        sortable: false
                    },{
                        data: 'almacen',
                        sortable: false,
                        defaultContent: '',
                        render: function(data, type, row) {
                            if (data) {
                                return `<div><span class='pull-right'><strong>${data.localizacion}</strong></span> ${data.nombre}</div>`;
                            }
                        }
                    },{
                        data: 'transaccion',
                        class:'text-center',
                        defaultContent: '',
                        sortable: false
                    },{
                        data: 'responsable',
                        sortable: false,
                        defaultContent: ''
                    },{
                        data: 'id',
                        defaultContent: '0',
                        visible: false
                    }
                ]
            });
        });
    };
    
    var initDataContractors = function( distribucion ) {
        /* Esta rutina carga todos los datos al array, para poder usarlo, deberiamos
         * Generar una llamada Ajax con el array, o pasarlo desde el controller en el 
         * formato que deseamos.
         */ 
        var data = new Array();
        
        var dataInsumos = new Array();
        var dataMovimientos = new Array();

        /* Ahora paso todos los registros a los lotes */
        var insumos = new Array();
        var entradas = new Array();
        var salidas =new Array();
        
        insumos = distribucion;
        
        for (var i = 0; i< insumos.length; i++){
            dataInsumos.push(
                {
                    id: insumos[i].id,
                    idproducto: insumos[i]['producto'].id,
                    producto: insumos[i]['producto'].nombre,
                    idunidad: insumos[i]['unidade'].id,
                    unidad: insumos[i]['unidade'].nombre,
                    dosis: insumos[i].dosis,
                    cantidad: insumos[i].cantidad,
                    entrega: insumos[i].entrega,
                    devolucion: insumos[i].devolucion,
                    utilizado: insumos[i].utilizado,
                    almacen: insumos[i]['almacene'].nombre,
                    id_distribuciones: insumos[i].orden_trabajos_distribucione_id 
                }
            );
            entradas = insumos[i]['orden_trabajos_insumos_entregas'];
            for (var e = 0; e < entradas.length; e++){
                dataMovimientos.push(
                    {
                        fecha: moment(entradas[e].fecha).format('DD/MM/YYYY'),
                        responsable: entradas[e]['user']['nombre'],
                        almacen: entradas[e]['almacene'] ? entradas[e]['almacene'] : '',
                        entrega: entradas[e]['cantidad'],
                        producto: entradas[e]['producto']['nombre'],
                        transaccion: entradas[e]['transaccion'],
                        insumo_id: entradas[e].orden_trabajos_insumo_id,
                        id: entradas[e]['id']                        
                    }
                );
            }
            salidas = insumos[i]['orden_trabajos_insumos_devoluciones'];
            for (var e = 0; e < salidas.length; e++){
                dataMovimientos.push(
                    {
                        fecha: moment(salidas[e].fecha).format('DD/MM/YYYY'),
                        responsable: salidas[e]['user']['nombre'],
                        almacen: salidas[e]['almacene'] ? salidas[e]['almacene'] : '',
                        salida: salidas[e]['cantidad'],
                        producto: salidas[e]['producto']['nombre'],
                        transaccion: salidas[e]['transaccion'],
                        insumo_id: salidas[e].orden_trabajos_insumo_id,
                        id: salidas[e]['id']
                    }
                );
            }
        };
        
        /* Devuelvo los datos */
        data['Insumos'] = dataInsumos;
        data['Movimientos'] = dataMovimientos;

        return data;        
//        console.log('Movimientos: ', dataMovimientos);
    };

    $("#productos").select2({
        theme: "bootstrap",
        placeholder: "Seleccione el producto ...",
        minimumInputLength: 3,
        dropdownParent: $('#CambiarArticulo'),
        width: '100%',
        delay: 350,
        ajax: {
            url: "/productos/search",
            dataType: 'json',
            data: function ( params) {
                var query = {
                    q: params.term,
                    establecimiento: $('#idestablecimiento').val()
                };
                return query;
            },
            processResults: function (data, params) {
                return { results: data.productos };
            }                      
        },
        templateResult: function ( data ) {
            if (data.loading) {
                return data.text;
            }
            let lote = '';
            let almacen = '';
            let existencia = '';
            let fecha = '';
            let unidad = '';
            if (typeof data.existencias !== 'undefined') {
                lote = data.existencias[0].productos_lote ? data.existencias[0].productos_lote.nombre : '';
                almacen = data.existencias[0].almacene ? data.existencias[0].almacene.nombre : '';
                existencia = data.existencias[0].cantidad ? Intl.NumberFormat().format(data.existencias[0].cantidad) : '';
                fecha = 'Stock al ' + moment( data.existencias[0].fecha).format("DD/MM/YYYY HH:mm");
                unidad = data.unidades ? data.unidades.descripcion : '';
            }
             var $container = $(`<div><small class='pull-right'>${lote}</small>
                                      <strong>${data.text}</strong><br>
                                      <small class='pull-right'><strong> ${existencia} ${unidad}</strong></small><small>${almacen}</small><br>
                                      <small>${fecha}</small>
                                </div>`); 

//                        var $container = $(`<div><strong>${data.text}</strong></div>`);

            return $container;                        
        },
        templateSelection: function ( data ) {
            let texto = data.text ? data.text : data.text;
            let superficie = ''; /* data.has ? `(${data.has} has)` : ''; */
            let $container = $(`<div><small class='pull-right'>${superficie}</small>${texto}</div>`);
            return $container;
        }                      
    });
    
    var formatDetails = function(callback) {
        var templateDetails = _.template($("#details-rows-template").text());
        return templateDetails({
            idContractor: callback.id
        });
    };
</script>