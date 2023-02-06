<?php
/**
 * VCA
 *
 * Index de VCA, con datatables Server Side y filtros
 *
 * View OrdenTrabajosInsumos
 *
 * Desarrollado para Adecoagro SA.
 * 
 * @category CakePHP3
 *
 * @package Ordenes
 *
 * @author Cesar Gonzalez <cegonzalez@adecoagro.com>
 * @copyright Copyright 2021, Adecoagro
 */
?>
<script>
    /**
     * ObtenerEstablecimientoId
     * 
     * Devuelvo el ID del establecimiento seleccionado en el select para utilizarlo
     * en los datatables server side
     * 
     * @returns {integer} id del Establecimiento
     */
    const ObtenerEstablecimientoId = () => $('#establecimiento-id').val();
    
    /**
     * ObtenerCampaniaId
     * 
     * Devuelvo el ID de la campaña de monitoreo activa para utilizarlo
     * en los datatables server side
     * 
     * @returns {integer} id del Establecimiento
     */
    const ObtenerProveedorId = () => $('#proveedore-id').val();

    
    const Desde = () => {
        var chkFechas = document.getElementById('chkRangoFechas');
        if (chkFechas.checked) {
            return $('#desde').val();
        }
        return '';
    };
    const Hasta = () => {
        var chkFechas = document.getElementById('chkRangoFechas');
        if (chkFechas.checked) {
            return $('#hasta').val();
        }
        return '';
    };
</script>
<?php
    echo $this->Html->css(['Ordenes.style', 'plugins/daterangepicker/daterangepicker-3.14.1']);
    echo $this->Html->script(['plugins/daterangepicker/daterangepicker-3.14.1.min']);
    echo $this->Form->control('filtro_establecimientos', ['type' => 'hidden', 'value' => json_encode($filtro_establecimientos)]);
    echo $this->Form->control('desde', ['type' => 'hidden']);
    echo $this->Form->control('hasta', ['type' => 'hidden']);
    echo $this->Form->control('estado', ['type' => 'hidden', 'value' => '']);
?>

<div class="row border-bottom white-bg page-heading">
    <div class="col-md-6 m-t-xs">
        <h3>Vales de Consumo &nbsp;&nbsp;<i class="fa fa-spin fa-refresh text-success" id="table-loader"></i></h3>
    </div>
    <div class="col-md-6 numero-ot m-t-xs">
        <div class="btn-group pull-right dt-buttons no-margins no-padding">
            
        </div>        
    </div>
</div>
<!-- Filtros -->
<div class="ibox float-e-margins m-b-xs">
    <div class="row">
        <div class="panel panel-default m-b-none">
            <div class="panel-body no-margins no-padding">
                <div class="col-md-12 m-b-xs m-t-xs no-padding">
                    <div class="col-md-4">
                        <?= $this->Form->control('establecimiento_id',['type'=> 'select', 'label' => false, 'options' => [], 'class' => 'form-control select2', 'required']) ?>
                    </div>
                    <div class="col-md-4 m-l-none m-r-none">
                        <?= $this->Form->control('proveedore_id',['type'=> 'select', 'label' => false, 'options' => [], 'class' => 'form-control select2', 'required']) ?>
                    </div>
                    <div class="col-md-4">
                        <div class="ot-form-group">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <input type="checkbox" id="chkRangoFechas" checked="">
                                </span>
                                <input id="rango-fechas" type="text" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    
<script>
    /**
     * Asigno los valores por defecto que tienen los establecimientos y campañas
     * 
     * Lo ubico en este lugar para que tome los cambios ANTES que los datatables lo utilicen
     */
    var data = JSON.parse($('#filtro-establecimientos').val());
    var EstablecimientoId = localStorage.getItem('VcaEstablecimientoId') ? localStorage.getItem('VcaEstablecimientoId') : '';
    
    /* 
     * Establecimientos
     */
    $("#establecimiento-id").select2({
        theme: "bootstrap",
        placeholder: 'Filtrar por establecimientos ...',
        width: '100%',
        allowClear: true,
        data: data,
        templateSelection: function ( data ) {
            if (data.id) {
                let $container = $(`<small class="pull-right">${data.organizacion}</small><div>${data.nombre}</div>`);
                return $container;
            }
            return data.text;
        },
        templateResult: function ( data ) {
            if (data.loading) {
                return data.nombre;
            }
            var $container = $(`<small class="pull-right">${data.organizacion}</small><div>${data.nombre}</div>`);
            return $container;
        },
        matcher: function (params, data) {
                if ($.trim(params.term) === '')  return data; // If there are no search terms, return all of the data
                if (data.nombre.toUpperCase().indexOf(params.term.toUpperCase()) > -1) {
                    var modifiedData = $.extend({}, data, true);
                    return modifiedData;
                }
                return null;
        }
    }).val(EstablecimientoId).trigger('change').on('change.select2', function (e) {
        e.stopPropagation();
        /* Recargamos las tablas */
        $('#vca').DataTable().ajax.reload();
        /* Guardo el nuevo valor por defecto */
        localStorage.setItem('VcaEstablecimientoId',  $('#establecimiento-id').val());

    });
    
    /* 
     * Proveedores, server side
     * 
     * Si hay un proveedor pre seleccionado, hay que agregar ese item al select
     * 
     */
    var Proveedor = localStorage.getItem('VcaProveedor') ? localStorage.getItem('VcaProveedor') : '';
    $("#proveedore-id").select2({
        theme: "bootstrap",
        placeholder: "Filtrar por proveedor ...",
        width: '100%',
        allowClear: true,
        minimumInputLength: 3,
        ajax: {
            url: "/proveedores/search",
            dataType: 'json',
            data: function (params) {
                var query = {
                    q: params.term
                };
                return query;
            },
            processResults: function (data, params) {
                return { results: data.proveedores };
            }
        }
    }).val('').trigger('change').on('change.select2', function (e) {
        e.stopPropagation();
        
        /* Obtengo el array de establecimientos */
         $('#vca').DataTable().ajax.reload();
         
        if ($('#proveedore-id').val() == null) {
            localStorage.setItem('VcaProveedor', '');
        } else {
            var data = {
                id: $('#proveedore-id').val(),   // ID
                text: $('#proveedore-id').text() // Nombre
            };
            /* Guardo el nuevo valor por defecto */
            localStorage.setItem('VcaProveedor', JSON.stringify(data));
        }
    });
    
    if (Proveedor.length > 0) {
        let ProveedorJson = JSON.parse(Proveedor);
        var newOption = new Option(ProveedorJson.text, ProveedorJson.id, false, false);
        $("#proveedore-id").append(newOption).trigger('change');
    }

    /* 
     * Configuro las fechas 
     */
    $(function() {
        var start = moment().subtract(29, 'days');
        var end = moment();

        function cb(start, end) {
            $('#rango-fechas span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
        }

        $('#rango-fechas').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
               'Hoy': [moment(), moment()],
               'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
               'Ultima semana': [moment().subtract(6, 'days'), moment()],
               'Ultimos 30 días': [moment().subtract(29, 'days'), moment()],
               'Este mes': [moment().startOf('month'), moment().endOf('month')],
               'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, cb);
        
        $('#rango-fechas').on('apply.daterangepicker', function(ev, picker) {
            $('#desde').val(picker.startDate.format('YYYY-MM-DD'));
            $('#hasta').val(picker.endDate.format('YYYY-MM-DD'));
            
            $('#vca').DataTable().ajax.reload();
        });
        cb(start, end);
    });
    
    /* Inicio el filtro de fechas */
    const IniciarControlFechas = () => {
        var chkFechas = document.getElementById('chkRangoFechas');
        let ActivoFiltroFechas = localStorage.getItem('OrdenTrabajosFiltroFechas') ? localStorage.getItem('OrdenTrabajosFiltroFechas') : 0;
        
        if (ActivoFiltroFechas == 0) {
            $("#chkRangoFechas").prop("checked", false);
            document.getElementById('rango-fechas').disabled = true;
        } else {
            $("#chkRangoFechas").prop("checked", true);
            document.getElementById('rango-fechas').disabled = false;
        }
        
        chkFechas.addEventListener('click', function() {
            if (chkFechas.checked) {
                localStorage.setItem('OrdenTrabajosFiltroFechas', 1);
                document.getElementById('rango-fechas').disabled = false;
            } else {
                localStorage.setItem('OrdenTrabajosFiltroFechas', 0);
                document.getElementById('rango-fechas').disabled = true;
            }
            $('#vca').DataTable().ajax.reload();
        });
    };
    IniciarControlFechas();
    
    
    datatable_lotes = function (data, type, full, meta)
    {  
        return data ? data : '';
    };
   
    datatable_establecimientos = function (data, type, full, meta)
    {
        return data ? data : '';
    };
    
    datatable_proveedores = function (data, type, full, meta)
    {
        return data ? data : '';
    };
    
     datatable_fecha = function (data, type, full, meta)
    {
        return data ? moment.utc(data).format('DD/MM/YYYY') : '';
    };
    /**
    * Dibuja los botones que precisamos en el listado
    * @param data The data for the cell (based on columns.data)
    * @param type 'filter', 'display', 'type' or 'sort'
    * @param full The full data source for the row
    * @param meta Object containing additional information about the cell
    * @returns Manipulated cell data
    */
    datatable_botones = function (data, type, full, meta)
    {
        if (type === 'display') {
            return '<div class="btn-group">' +
                        '<a target="_blank" href="/orden-trabajos/vca/' + data + '" type="button" title="Ver" class="btn btn-xs btn-white btn-orden-trabajo"><i class="fa fa-eye"></i></a>' +
                        '<a href="/orden-trabajos/vca/' + data + '.pdf" type="button" title="Generar PDF" class="btn btn-xs btn-white btn-orden-trabajo"><i class="fa fa-print"></i></a>' +
                    '</div>';		
        }
        return data;
    };
</script>
<div class="ibox">
    <div class="ibox-content">
        <div class="row">
            <div class="col-md-12 no-margins no-padding">
                <div id="tabla_vca">
                    <?php
                        $options = [
                                'ajax' => [
                                        'url' => '/orden-trabajos/datatable-vca',
                                        'data' => [

                                            'establecimiento_id' => $this->DataTables->callback('ObtenerEstablecimientoId'),
                                            'proveedore_id' => $this->DataTables->callback('ObtenerProveedorId'),
                                            'desde' => $this->DataTables->callback('Desde'),
                                            'hasta' => $this->DataTables->callback('Hasta'),
                                        ]
                                ],
                                'pageLength' => 20,
                                'iDisplayLength' => 20,
                                'autoWidth' => false,
                                'stateSave' => true,
                                'prefixSearch' => true,
                                'language' => [
                                    'emptyTable' => 'No hay datos en la tabla.',
                                    'processing' => 'Buscando ...',
                                    'search' => 'Buscar',
                                    'info' => 'Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros',
                                    'paginate' => [
                                        'first' => __d('data_tables', 'Primero'),
                                        'last' => __d('data_tables', 'Ultimo'),
                                        'next' => __d('data_tables', 'Siguiente'),
                                        'previous' => __d('data_tables', 'Anterior'),
                                    ],
                                    'infoEmpty' => __d('data_tables', 'No hay registros para mostrar'),
                                    'infoFiltered' => __d('data_tables', '(Se encontraron _MAX_ registros)'),
                                    'lengthMenu' => __d('data_tables', 'Show _MENU_ entries'),
                                    'zeroRecords' => __d('data_tables', 'No hay registros'),
                                    'aria' => [
                                        'sortAscending' => __d('data_tables', ': activar para ordenar en forma Ascendente'),
                                        'sortDescending' => __d('data_tables', ': activar para ordenar en forma Descendente'),
                                    ],                
                                ],
                                'dom' => '<"row"<"col-sm-6 no-padding"f><"col-sm-6 no-padding botones-tabla-top-right"B>><"row"<"col-sm-12 no-margin no-padding"tr>><"row"<"col-sm-6"i><"col-sm-6"p>>',
                                'buttons' => [
                                                [   /* Exporto todas las OT a Excel para analisis */
                                                    'text' => '<i class="fa fa-file-excel-o"></i>',
                                                    'titleAttr' => 'Listado VCAs',
                                                    'className' => 'btn-monitoreo btn-icon-only btn-circle exportar-vca',
                                                ],[ /* Exporto todas las Entregas/Devoluciones en Excel */
                                                    'text' => "<i class='fa fa-database'></i>",
                                                    'titleAttr' => "VCA's en Oracle",
                                                    'className' => 'btn-monitoreo btn-icon-only btn-circle oracle-vca',
                                                ],[ /* voy a una ventana para mostrar errores en la interfaz de ajustes */
                                                    'text' => "<i class='fa fa-search'></i>",
                                                    'titleAttr' => "VCA con errores de Interfaz",
                                                    'className' => 'btn-monitoreo btn-icon-only btn-circle vca-interfaz',
                                                ]
                                            ],
                                'columns' => [
                                        [
                                                'title' => __('VCA'),
                                                'data' => 'orden_trabajo.id',
                                                'render' => $this->DataTables->callback('dt.render.ot')
                                        ],[
                                                'title' => __('Fecha'),
                                                'data' => 'orden_trabajo.fecha',
                                                'render' => $this->DataTables->callback('datatable_fecha')
                                        ],[
                                                'title' => __('Establecimiento'),
                                                'data' => 'orden_trabajo.establecimiento.nombre',
                                                'className' => 'text-primary',
                                                'render' => $this->DataTables->callback('datatable_establecimientos')
                                        ],[
                                                'title' => __('Proveedor'),
                                                'data' => 'orden_trabajo.proveedore.nombre',
                                                'className' => 'text-primary',
                                                'render' => $this->DataTables->callback('datatable_proveedores')
                                        ],[
                                                'title' => __('Producto'),
                                                'data' => 'producto.nombre',
                                                'className' => 'text-primary'
                                        ],[
                                                'title' => __('Lotes'),
                                                'data' => 'orden_trabajos_distribucione.lote.nombre',
                                                'className' => 'text-primary',
                                                'render' => $this->DataTables->callback('datatable_lotes')
                                        ],[
                                                'title' => __(' '),
                                                'data' => 'orden_trabajo.id',
                                                'searchable' => false,
                                                'orderable' => false,
                                                'className' => 'cell-double-action',
                                                'render' => $this->DataTables->callback('datatable_botones')
                                        ]
                                ]
                        ];
                        echo $this->DataTables->table('vca', $options, ['class' => 'table table-bordered table-hover table-striped sin-margen-superior']);
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Labor -->
<form id="oraclevca">
    <div class="modal otmodal" id="OracleVca" tabindex="-1" role="dialog"  aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content animated fadeIn">
                <div class="modal-header text-left">
                    <h3 class="modal-title"><span id="titulo-modal"></span> <small class="pull-right" style="font-size: 45%;" id="sub-titulo-modal"></small></h3>
                    <div>
                        <h3>Seleccionar Organizacion<i class="fa fa-spin fa-refresh text-success pull-right" id="oracle-loader"></i></h3>
                    </div>                    
                </div>
                <div class="modal-body">
                    <fieldset>
                        <div class="row col-md-12">
                            <?= $this->Form->control('establecimientos', ['type' => 'select', 'class' => 'form-control select2 oracle', 'label' => 'Organizaciones', 'multiple' => 'multiple']) ?>
                        </div>
                        <br>
                        <div class="row col-md-12">
                            <div class="row col-md-6 m-l-none">
                                <?= $this->Form->control('oracle_desde', ['label' => false, 'class' => 'form-control oracle', 'label' => 'Desde']) ?>
                            </div>
                            <div class="row col-md-6 m-r-none">
                                <?= $this->Form->control('oracle_hasta', ['label' => false, 'class' => 'form-control oracle', 'label' => 'Hasta']) ?>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="modal-footer">
                    <div class="row col-md-12 no-margins no-padding">
                        <div class="col-md-5 no-margins no-padding">
                            <button type="button" class="btn btn-default btn-block oracle" data-dismiss="modal">Cancelar</button>
                        </div>
                        <div class="col-md-2"></div>
                        <div class="col-md-5 no-margins no-padding">
                            <button type="button" class="btn btn-primary btn-block oracle" onclick="CertificarLabor()">Generar Reporte</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Modal Listado de Vca -->
<form id="excelvca">
    <div class="modal otmodal" id="ExcelVca" tabindex="-1" role="dialog"  aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content animated fadeIn">
                <div class="modal-header text-left">
                    <h3 class="modal-title"><span id="titulo-modal"></span> <small class="pull-right" style="font-size: 45%;" id="sub-titulo-modal"></small></h3>
                    <div><h3>Seleccionar Organizacion<i class="fa fa-spin fa-refresh text-success pull-right" id="oracle-loader"></i></h3></div>
                </div>
                <div class="modal-body">
                    <fieldset>
                        <div class="row col-md-12">
                            <?= $this->Form->control('establecimientos_vca', ['type' => 'select', 'class' => 'form-control select2 oracle ExcelVca', 'label' => 'Organizaciones', 'multiple' => 'multiple', 'options' => $establecimientos ]) ?>
                        </div>
                        <br>
                        <div class="row col-md-12">
                            <?= $this->Form->control('campania_id', ['type' => 'select', 'class' => 'form-control select2 oracle ExcelVca', 'label' => 'Campaña', 'options' => $campanias ]) ?>
                        </div>
                        <br>
                        <div class="row col-md-12">
                            <?= $this->Form->control('lista_proyectos', ['type' => 'select', 'class' => 'form-control select2 oracle ExcelVca', 'label' => 'Proyecto', 'multiple' => 'multiple']) ?>
                        </div>
                        <br>
                        <div class="row col-md-12">
                            <div class="row col-md-6 m-l-none">
                                <?= $this->Form->control('desde_vca', ['label' => false, 'class' => 'form-control oracle', 'label' => 'Desde']) ?>
                            </div>
                            <div class="row col-md-6 m-r-none">
                                <?= $this->Form->control('hasta_vca', ['label' => false, 'class' => 'form-control oracle', 'label' => 'Hasta']) ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="col-md-6">
                                    <div class="m-l-xs">
                                        <div class="checkbox checkbox-primary">
                                            <input id="chkValorizar" type="checkbox">
                                            <label for="chkValorizar">
                                                Valorizar el reporte
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                </div>
                <div class="modal-footer">
                    <div class="row col-md-12 no-margins no-padding">
                        <div class="col-md-5 no-margins no-padding">
                            <button type="button" class="btn btn-default btn-block oracle" data-dismiss="modal">Cancelar</button>
                        </div>
                        <div class="col-md-2"></div>
                        <div class="col-md-5 no-margins no-padding">
                            <button type="button" class="btn btn-primary btn-block oracle" onclick="ExcelVCA()">Generar Reporte</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<?= $this->Html->script(['plugins/dataTables/datetime-moment']) ?>
<script>
    $(document).ready(function () {
        
        $("#table-loader").addClass('hidden');
        $("#oracle-loader").addClass('hidden');
        
        $(".ExcelVca").select2({
            theme: "bootstrap",
            width: '100%',
            dropdownParent: $('#ExcelVca')
        });
        
        $("#establecimientos").select2({
            theme: "bootstrap",
            width: '100%',
            dropdownParent: $('#OracleVca')
        });
        
        $('.exportar-vca').on('click', function() {
           /* Muestro el dataload */
           $('#ExcelVca').modal('show');
       });
       
       $('.oracle-vca').on('click', function() {
           /* Muestro el dataload */
           $('#OracleVca').modal('show');
       });
       
//       $('.vca-interfaz').on('click', function() {
//            console.log($(location).attr('href','/orden-trabajos/index-vca-interfaz'));
//                             
//            console.log( $(location) );
//                             
//            console.log(location);
//            console.log(window.location.href);
//       });
       
        
       $('#desde').mask('00/00/0000');
       $('#hasta').mask('00/00/0000'); 
        
       /* Filtro los proyectos cuando se selecciona un establecimiento */
        $('#establecimientos-vca').on('select2:select', function (e) {
            e.stopPropagation();

            var Establecimientos = $('#establecimientos-vca').val();
            var Campanias = $('#campania-id').val();
            FiltrarProyectos(Establecimientos, Campanias);
        });
        
        /* Filtro los proyectos cuando se selecciona un establecimiento */
        $('#campania-id').on('select2:select', function (e) {
            e.stopPropagation();

            var Establecimientos = $('#establecimientos-vca').val();
            var Campanias = $('#campania-id').val();
            FiltrarProyectos(Establecimientos, Campanias);
        });  
        
    });
    
    /**
    * Filtro los proyectos asociados a los establecimientos pasados
    * 
    * @param {array} establecimientos
    * @returns {array} Lista de proyectos activos
    */
    const FiltrarProyectos = (establecimientos = null, campania = null) => {
        let dataForm = new FormData();
        dataForm.append('establecimientos', establecimientos );
        dataForm.append('campania', campania );
        
        fetch(`/orden-trabajos/index-vca.json`, {
                method: 'POST',
                body: dataForm
            }).then( res => res.json() )
            .then( data => {
                var ProyectosFiltrados = JSON.parse(data.establecimientos);
                $("#lista-proyectos").empty().select2({
                    theme: "bootstrap",
                    width: '100%',
                    data: ProyectosFiltrados,
                    dropdownParent: $("#ExcelVca")
                }).trigger("change");
            })
            .catch( function(err) {
                toastr.error(err);
            });
    };
    
    /* Aplico filtros y genero informe de los VCA */
    function ExcelVCA() {
        let Desde = '';
        let Hasta = '';
        
        $("#oracle-loader").removeClass('hidden');
        
        // Reviso la fecha de inicio
        var fecha = $('#desde-vca').val();
        if (fecha) {
            if (fecha.length !== 10) {
                toastr.error("El formato correcto es dd/mm/yyyy.", "Error de Formato");
                $("#oracle-loader").addClass('hidden');
                return;
            }
        }
        if (moment(fecha, 'DD/MM/YYYY').isValid()) {
            Desde = moment(fecha, 'DD/MM/YYYY').format('YYYY-MM-DD');
        }
        
        // Reviso la fecha de final
        var fecha = $('#hasta-vca').val();
        if (fecha) {
            if (fecha.length !== 10) {
                toastr.error("El formato correcto es dd/mm/yyyy.", "Error de Formato");
                $("#oracle-loader").addClass('hidden');
                return;
            }
        }
        if (moment(fecha, 'DD/MM/YYYY').isValid()) {
            Hasta = moment(fecha, 'DD/MM/YYYY').format('YYYY-MM-DD');
        }
        
        /* Comienzo el reporte */
        $("#table-loader").removeClass('hidden');
        $(".oracle").addClass('disabled');
        $(".oracle").prop("disabled", true);
        
        let dataForm = new FormData();
        dataForm.append('establecimientos', $('#establecimientos-vca').val() );
        dataForm.append('desde', Desde );
        dataForm.append('hasta', Hasta );
        dataForm.append('proyectos', $('#lista-proyectos').val() );
        dataForm.append('campania', $('#campania-id').val());
        dataForm.append('valorizado', $('#chkValorizar')[0].checked ? 1 : 0);
        
        fetch(`/orden-trabajos/generarexcelvca/vca`, {
                method: 'POST',
                body: dataForm
            }).then( res => res.json() )
            .then( data => {
                console.log('Datos OK: ', data);
                toastr.info(data.message);
                
                $("#oracle-loader").addClass('hidden');
                $(".oracle").removeClass('disabled');
                $(".oracle").prop("disabled", false);
                $('#ExcelVca').modal('hide');
            })
            .catch( function(err) {
                toastr.error(err);
                $("#oracle-loader").addClass('hidden');
                $(".oracle").removeClass('disabled');
                $(".oracle").prop("disabled", false);
            });
    }
    
    function CertificarLabor (){
        let Desde = null;
        let Hasta = null;
        
        $("#oracle-loader").removeClass('hidden');
        
        // Reviso la fecha de inicio
        var fecha = $('#oracle-desde').val();
        if (fecha) {
            if (fecha.length !== 10) {
                toastr.error("El formato correcto es dd/mm/yyyy.", "Error de Formato");
                $("#oracle-loader").addClass('hidden');
                return;
            }
        }
        if (moment(fecha, 'DD/MM/YYYY').isValid()) {
            Desde = moment(fecha, 'DD/MM/YYYY').format('YYYY-MM-DD');
        }
        
        // Reviso la fecha de final
        var fecha = $('#oracle-hasta').val();
        if (fecha) {
            if (fecha.length !== 10) {
                toastr.error("El formato correcto es dd/mm/yyyy.", "Error de Formato");
                $("#oracle-loader").addClass('hidden');
                return;
            }
        }
        if (moment(fecha, 'DD/MM/YYYY').isValid()) {
            Hasta = moment(fecha, 'DD/MM/YYYY').format('YYYY-MM-DD');
        }
        
        /* Comienzo el reporte */
        $("#table-loader").removeClass('hidden');
        $(".oracle").addClass('disabled');
        $(".oracle").prop("disabled", true);
        
        
        let dataForm = new FormData();
        dataForm.append('establecimientos', $('#establecimientos').val() );
        dataForm.append('desde', Desde ? Desde : '' );
        dataForm.append('hasta', Hasta ? Hasta : '');
        
        fetch(`/orden-trabajos/generarexcelvca/oracle`, {
                method: 'POST',
                body: dataForm
            }).then( res => res.json() )
            .then( data => {
                if (data['status'] == 'success'){
                    /* Abro el archivo excel generado */
                    $(location).attr('href','/dataload/' + data['archivo']);
                } else {
                    alert(data['message'] + ' - ' + data['data']);
                }
                $("#oracle-loader").addClass('hidden');
                $(".oracle").removeClass('disabled');
                $(".oracle").prop("disabled", false);
                $('#OracleVca').modal('hide');
            })
            .catch( function(err) {
                toastr.error(err);
                $("#oracle-loader").addClass('hidden');
                $(".oracle").removeClass('disabled');
                $(".oracle").prop("disabled", false);
            });
    };
</script>