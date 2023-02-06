    /*
 *
 *   Ordenes de Trabajo
 *   version 1.0
 *
 */
    var dataTableContractors = new Array();
    var dataTableInsumos = new Array();
    var DataContractors = new Array();
    var OrdenTrabajo = new Array();
    var datos = new Array();
    
$(document).ready(function() {
    
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
    
    /* Busco los procesos historicos en oracle */
    $('.oracle').on('click', function (e) {
        console.log('Busco en oracle');
    });
    
    /* Consulto historicos en oracle */
    $('#historico').on('click', function (e) {
		
		console.log('Busco el historico');
		
		var id = $('#id').val();
		console.log('Orden Trabajo: ', id);
		
            e.preventDefault();
            
            fetch('/orden-trabajos/consultar-historico-oracle/' + id )
                .then( res => res.json() )
                .then( data => {
                    if (data.length > 0) {
                            var tabla = '';
                            tabla = '<table class="table-tecnica margin bottom m-t-none"><thead>' +
                                    '<tr class="text-center small"><th>Sec</th><th>Fecha</th><th>Accion</th><th>Realizado por</th><th>Nota</th></tr></thead><tbody>';
                            $.each(data, function ( index, problema) {
                                var Nombre = problema.EMPLOYEE_NAME ? problema.EMPLOYEE_NAME : problema.NOMBRE;
                                tabla = tabla + '<tr>' +
                                                    '<td><span class="text-capitalize small">' + problema.SEQUENCE_NUM + '</span></td>' +
                                                    '<td class="text-right small"><span>' + problema.ACTION_DATE + '</span></td>' + 
                                                    '<td class="small"><span>' + problema.ACTION_CODE_DSP + '</span></td>' + 
                                                    '<td class="small"><span>' + Nombre + '</span></td>' +
                                                    '<td class="small"><span>' + (problema.NOTE ? problema.NOTE : '') + '</span></td>' + 
                                                '</tr>';
                            });
                            tabla = tabla + '</tbody></table>';
                            $('#historico_oracle').html( tabla );
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
        fetch('/orden-trabajos/quitar-certificacion/' + id )
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
    
    const Iniciar = () => {
        /* Inicio el show */
        let id = $('#id').val();
        fetch(`/orden-trabajos/view/${ id }.json`)
            .then( res => res.json() )
            .then( data => {
                /* Data recibida de la OT */
                //CargarTabla( data );
                datos = data;
                ContractorListEdit.init();
            })
            .catch(error => console.error(error));
    };
    Iniciar();
});


/**
 * Lista y edicion de Contratistas.
 *
 * @type {{init}}
 */
var ContractorListEdit = function() {

    //var dataTableContractors = new Array();
    var $table;
    
    var initDataContractors = function() {
       /* Esta rutina carga todos los datos al array, para poder usarlo, deberiamos
        * Generar una llamada Ajax con el array, o pasarlo desde el controller en el 
        * formato que deseamos.
        */ 
        dataTableContractors = new Array();
        dataTableInsumos = new Array();

        /* Ahora paso todos los registros a los lotes */
        insumos = new Array();
        
        if (datos.ordenTrabajo.orden_trabajos_insumos) {
            insumos = datos.ordenTrabajo.orden_trabajos_insumos;  //[0]['orden_trabajos_insumos'];
            insumos.map((insumo) => {
                dataTableInsumos.push(
                    {
                        producto: insumo.producto.nombre,
                        unidad: insumo.unidade.nombre,
                        dosis: insumo.dosis,
                        cantidad: insumo.cantidad,
                        entrega: insumo.entregas,
                        devolucion: insumo.devoluciones,
                        almacen: insumo.almacene.nombre,
                        utilizado: insumo.entregas - insumo.devoluciones,
                        dosis_aplicada: insumo.dosis_aplicada_real,
                        id: insumo.id,
                        id_distribuciones: insumo.orden_trabajos_distribucione_id 
                    }
                );
            });
        }
        
        distribucion = new Array;
        distribuciones = datos.ordenTrabajo.orden_trabajos_distribuciones;
        console.log(distribuciones);
        distribuciones.map((distribucion) => {
            dataTableContractors.push(
                {
                    labor: distribucion.proyectos_labore.nombre,
                    unmedida: distribucion.unidade.nombre,
                    cc: distribucion.proyecto.nombre,
                    lote: distribucion.lote.nombre,
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
                }
            );
        });
    };
    
    var updateDataContractors = function(RowContractor) {
        var l_index = 0;
        var l_new = true;
        var l_DataNewContractors = new Array();
        $.each(DataContractors, function(index, row) {
            if (row.id == RowContractor.id) {
                DataContractors[index].labor = RowContractor.labor;
                DataContractors[index].unmedida = RowContractor.unmedida;
                DataContractors[index].cc = RowContractor.cc;
                DataContractors[index].has = RowContractor.has;
                DataContractors[index].dosis = RowContractor.dosis;
                DataContractors[index].total = RowContractor.total;
                DataContractors[index].insumos = RowContractor.insumos;
                DataContractors[index].moneda = RowContractor.moneda;
                DataContractors[index].importe = RowContractor.importe;
                DataContractors[index].labor_id = RowContractor.labor_id;
                DataContractors[index].id = RowContractor.id;
                
                console.log(RowContractor.importe);
                
                l_index = index;
                l_new = false;
                return true;
            }
        });
        if (l_new) {
            l_DataNewContractors.push(
                    {
                        labor: RowContractor.labor,
                        unmedida: RowContractor.unmedida,
                        cc: RowContractor.cc,
                        lote: RowContractor.lote,
                        has: RowContractor.has,
                        dosis: RowContractor.dosis,
                        total: RowContractor.total,
                        insumos: RowContractor.insumos,
                        moneda: RowContractor.moneda,
                        importe: RowContractor.importe,
                        labor_id: RowContractor.labor_id,
                        id: RowContractor.id
                    });
            return l_DataNewContractors[l_DataNewContractors.length - 1];
        } else {
            return DataContractors[l_index];
        }
    };

    var initTable = function() {
        $table = $(".contractors-table").DataTable({
            pageLength: 20,
            destroy: true,
            deferRender: false,
            data: dataTableContractors,
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
                    className: 'details-control-contractors no-custo no-edit',
                    data: 'labor',
                    sortable: false,
                    responsivePriority: 1,
                    defaultContent: ''
                },{
                    data: 'unmedida',
                    sortable: false,
                    responsivePriority: 2,
                    defaultContent: ''
                },{
                    data: 'cc',
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
                    defaultContent: ''
                    
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
            var row = $table.row(tr);
            var dataInsumos = new Array();
            var data = row.data();
            
            if (row.child.isShown()) {
                row.child.hide();
                tr.removeClass('shown');
                $(tr).find(".icon_expand").removeClass('glyphicon-menu-right');
                $(tr).find(".icon_expand").addClass('glyphicon-menu-down');                
            }
            else {
                // Open this row
                $.each(dataTableInsumos, function(index, row) {
                    if (row.id_distribuciones === data.id) {
                            var dosis_real = row.utilizado / data.has;
                            row.dosis_aplicada = dosis_real.toFixed(4);
                            dataInsumos.push(row);
                        return true;
                    }
                });
                
                row.child(formatDetails(data), 'background-white background-child');
                row.child.show();

                /* Todos los insumos relacionados con esta OT están en dataTableInsumos
                 * pero solo debo recuperar los que están relacionados a esta labor en 
                 * particular, a traves del campo orden_trabajos_distribucione_id */


                $subTable = $(".contractors-table-details" + row.data().id).DataTable({
                    pageLength: 25,
                    data: dataInsumos,
                    autoWidth: false,
                    deferRender: false,
                    dom: 'rt',
                    columns: [{
                            data: 'producto',
                            defaultContent: '',
                            sortable: false
                        },{
                            data: 'unidad',
                            sortable: false,
                            defaultContent: ''
                        },{
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
            /* Expando todas las lineas de Insumos */
            /* $table.rows(':not(.parent)').nodes().to$().find('td:first-child').trigger('click'); */
        });

    };
    
    var formatDetails = function(callback) {
        var templateDetails = _.template($("#details-rows-template").text());
        var lt_dataTableMachines = new Array();

        return templateDetails({
            idContractor: callback.id,
            machines: lt_dataTableMachines
        });

    };
    
    return {
        init: function() {
            
            initDataContractors(); /* Cargo los datos */
            initTable();           /* Inicio la tabla */
            
            $("#table-loader").addClass('hidden');
            $("#data-contractors").removeClass('hidden');
            $("#page-loader").addClass('hidden');
        }
    };
}();