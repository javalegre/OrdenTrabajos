<?php
   /**
    * Agregar Reclasificacion
    * 
    * Buscamos una OT con la linea a reclasificar
    * 
    * 
    * Desarrollado para Adecoagro SA.
    * 
    * @category CakePHP3
    *
    * @package Ordenes
    *
    * @author Javier Alegre <jalegre@adecoagro.com>
    * @copyright Copyright 2021, Adecoagro
    * @version 1.0.0 creado el 22/08/2022
    */
?>
<style>
    .select2-container--bootstrap .select2-selection--single {
        height: 54px !important;
    }
    .wizard-big.wizard > .content {
        min-height: 200px !important;
    }
</style>
<?= $this->Html->script(['plugins/steps/jquery.steps.min']) ?>
<?= $this->Html->css(['plugins/steps/jquery.steps']) ?>
<div class="modal-header text-left">
    <div class="pull-right">
        <?= $this->Form->button('<i class="fa fa-save"></i>', ['class' => 'btn-monitoreo btn-icon-only btn-circle GuardarRegistro' ,'title' => 'Guardar Registro','type' => 'button', 'escape' => false]) ?>
        <?= $this->Form->button('<i class="fa fa-times"></i>', ['class' => 'btn-monitoreo btn-icon-only btn-circle' , 'data-dismiss' => "modal", 'title' => 'Cancelar','type' => 'button', 'escape' => false]) ?>
    </div>
    <h3 class="modal-title">Reclasificar ...</h3>
</div>


<div class="modal-body">
    <div class="row no-margins no-padding">
        <?= $this->Form->create($ordenTrabajosReclasificacionesDetalle, ['id' => 'formReclasificacion']) ?>
        <fieldset>
            <?= $this->Form->control('orden_trabajos_reclasificacione_id', ['type' => 'hidden', 'value' => $orden_trabajos_reclasificacione_id]) ?>
            <?= $this->Form->control('proyecto_id', ['type' => 'hidden', 'value' => '']) ?>
            <?= $this->Form->control('proyectos_labore_id', ['type' => 'hidden', 'value' => '']) ?>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="ot-form-group">
                        <div class="input-group">
                            <input type="text" name="orden_trabajo_id" class="form-control" required="required" maxlength="11" id="orden-trabajo-id" value="" placeholder="N&uacute;mero de Orden">
                            <span class="input-group-btn">
                                <button type="button" class="btn btn-primary BuscarOrdenes"><i class="fa fa-search"></i></button>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="m-t-xs">
                        <small>Seleccione la OT que quiere reclasificar y ENTER para buscar los proyectos y labores</small>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div id="wizard" class="m-t-sm wizard-big">
                    <h1>A reclasificar</h1>
                    <div class="step-content">
                        <div>
                            <?= $this->Form->control('orden_trabajos_distribucione_id',['type'=> 'select', 'class' => 'form-control select2', 'label' => 'Lineas a reclasificar', 'disabled' => 'disabled']) ?>
                        </div>
                    </div>
                    <h1>Reclasificado</h1>
                    <div class="step-content">
                        <?= $this->Form->control('proyecto_seleccionado', ['type' => 'select', 'class' => 'form-control', 'label' => 'Proyecto']) ?>
                        <?= $this->Form->control('labor_seleccionada', ['type' => 'select', 'class' => 'form-control', 'label' => 'Labor', 'disabled' => 'disabled']) ?>
                    </div>
                </div>
            </div>
        </fieldset>
        <?= $this->Form->end() ?>
    </div>
</div>
<script>
    
    /**
     * Inicializamos los pasos para cargar en etapas
     */
    $("#wizard").steps({
        labels: {
            cancel: "Cancelar",
            current: "Siguiente step:",
            pagination: "Pagination",
            finish: "Guardar",
            next: "Siguiente",
            previous: "Anterior",
            loading: "Leyendo ..."
        },
        onFinished: function (event, currentIndex)
                {
                    GuardarReclasificacion();
                }
    });
    
    /**
     * Lista de proyectos disponibles para el establecimiento seleccionado.
     * 
     * El filtro se obtiene buscando el establecimiento_id de la reclasificacion
     * que se pasa como parámetro
     */
    $("#proyecto-seleccionado").select2({
        theme: "bootstrap",
        width: '100%',
        placeholder: "Seleccione un proyecto",
        dropdownParent: $("#reclasificaciones"),
        minimumInputLength: 3,
        ajax: {
            url: "/orden-trabajos-reclasificaciones-detalles/buscar-proyectos-destinos",
            dataType: 'json',
            data: function (params) {
                return {q: params.term,
                        reclasificacion_id: $('#orden-trabajos-reclasificacione-id').val()
                };
            }
        },
        templateResult: function ( data ) {
            if (data.loading) {
                return data.text;
            }
            var $container = $(`<div><small class='pull-right'>${data.campania_monitoreo ? data.campania_monitoreo.nombre : ''}</small>
                                    <strong>${data.nombre}</strong><br>
                                <small>${data.Cultivos ? data.Cultivos.nombre : ''}</small></div>`);
            return $container;                        
        },
        templateSelection: function ( data ) {
            if (!data.id) {
                return data.text;
            }
            var $container = $(`<div><small class='pull-right'>${data.campania_monitoreo ? data.campania_monitoreo.nombre : ''}</small>
                                    <strong>${data.nombre}</strong><br>
                                    <small>${data.Cultivos ? data.Cultivos.nombre : ''}</small></div>`);
            return $container;
        }
    }).val('').trigger('change').on('select2:select', function (e) {
        var data = e.params.data;
        console.log('Item Selected: ', data);
        
        $("#labor-seleccionada").prop("disabled", false);
    });
    
    /**
     * Lista las labores disponibles para el proyecto seleccionado.
     * 
     * El filtro se obtiene pasando el proyecto seleccionado
     */
    $("#labor-seleccionada").select2({
        theme: "bootstrap",
        width: '100%',
        placeholder: "Seleccione una labor",
        dropdownParent: $("#reclasificaciones"),
        minimumInputLength: 2,
        ajax: {
            url: "/orden-trabajos-reclasificaciones-detalles/buscar-proyectos-labores-destinos",
            dataType: 'json',
            data: function (params) {
                return {
                    q: params.term,
                    proyecto_id: $('#proyecto-seleccionado').val()
                };
            }
        },
        templateResult: function ( data ) {
            if (data.loading) {
                return data.text;
            }
            return  $(`<div><strong>${data.nombre}</strong><br><small>${data.proyectos_gastos_categoria ? data.proyectos_gastos_categoria.nombre : ''}</small></div>`);
        },
        templateSelection: function ( data ) {
            if (!data.id) {
                return data.text;
            }
            return  $(`<div><strong>${data.nombre}</strong><br><small>${data.proyectos_gastos_categoria ? data.proyectos_gastos_categoria.nombre : ''}</small></div>`);
        }
    }).val('').trigger('change');

    /**
     * Guardamos los datos de la reclasificacion enviando los datos por POST
     * 
     */
    function GuardarReclasificacion() {
        const data = new FormData(document.getElementById('formReclasificacion'));
        let url = new URL(`${window.location.protocol}//${window.location.host}/orden-trabajos-reclasificaciones-detalles/add`);
        fetch(url, {
                method: 'POST',
                body: data
            })
            .then( res => res.json())
            .then( data => {
                if (data.status === 'success') {
                    $('#reclasificaciones').modal('hide');
                    $('#dt-reclasificaciones').DataTable().ajax.reload();
                    return;
                }
                toastr.error(data.message);
            });
    }

    $('.BuscarOrdenes').on('click', function() {
        
        const data = new FormData();
        data.append("orden_trabajo_id", $('#orden-trabajo-id').val());
        data.append("orden_trabajos_reclasificacione_id", $('#orden-trabajos-reclasificacione-id').val());
        
        let url = new URL(`${window.location.protocol}//${window.location.host}/orden-trabajos-reclasificaciones-detalles/buscar-ordenes`);
        fetch(url, {
            method: 'POST',
            body: data
        })
        .then( res => res.json())
        .then( data => {
            if (data.status === 'success') {
                $("#orden-trabajos-distribucione-id").select2({
                    theme: "bootstrap",
                    data: data.data,
                    width: '100%',
                    placeholder: "Seleccione linea a reclasificar",
                    dropdownParent: $("#reclasificaciones"),
                    disabled: false,
                    templateResult: function ( data ) {
                        if (data.loading) {
                            return data.text;
                        }

                        var $container = $(`<div><small class='pull-right'>${data.proyecto.campania_monitoreo ? data.proyecto.campania_monitoreo.nombre : ''}</small>
                                                <strong>${data.proyecto.nombre}</strong><br>
                                                <small class='pull-right'>${data.lote.sectore ? data.lote.sectore.nombre : ''} - ${data.lote ? data.lote.nombre : ''}</small>
                                                <small>${data.proyectos_labore ? data.proyectos_labore.nombre : ''}</small>
                                            </div>`);
                        return $container;                        
                    },
                    templateSelection: function ( data ) {
                        if (!data.id) {
                            return data.text;
                        }
                        var $container = $(`<div><small class='pull-right'>${data.proyecto.campania_monitoreo ? data.proyecto.campania_monitoreo.nombre : ''}</small>
                                                <strong>${data.proyecto.nombre}</strong><br>
                                                <small class='pull-right'>${data.lote.sectore ? data.lote.sectore.nombre : ''} - ${data.lote ? data.lote.nombre : ''}</small>
                                                <small>${data.proyectos_labore ? data.proyectos_labore.nombre : ''}</small>
                                            </div>`);
                        return $container;
                    }
                }).val('').trigger('change').on('select2:select', function (e) {
                    var data = e.params.data;

                    /* Asigno los valores por defecto */
                    $('#proyecto-id').val(data.proyecto_id);
                    $('#proyectos-labore-id').val(data.proyectos_labore_id);

                });
                
                return;
            }
            toastr.error(data.message);
       });
    });
</script>
