<?php
/**
 * @var \App\View\AppView $this
 * @var \Cake\Datasource\EntityInterface $ordenTrabajosMapeo
 */
?>
<?= $this->Form->control('tipos', ['type' => 'hidden', 'value' => json_encode($mapeosCampaniasTipos)]) ?>
<?= $this->Form->control('calidades', ['type' => 'hidden', 'value' => json_encode($mapeosCalidades)]) ?>
<?= $this->Form->control('problemas', ['type' => 'hidden', 'value' => json_encode($mapeosProblemas)]) ?>
<?= $this->Form->control('users', ['type' => 'hidden', 'value' => json_encode($users)]) ?>
<?= $this->Form->control('requiere_comentario', ['type' => 'hidden', 'value' => 0]) ?>
<?= $this->Form->control('id', ['type' => 'hidden', 'value' => $ordenTrabajosDistribuciones->id ]) ?>

<div class="modal-header text-left">
    <div class="pull-right">
        <?= $this->Form->button('<i class="fa fa-times"></i>', ['class' => 'btn-icon-only btn-circle' , 'data-dismiss' => "modal", 'title' => 'Cancelar','type' => 'button', 'escape' => false]) ?>
    </div>
    <h3 class="">Agregar Mapeo</h3>
</div>
<div class="row m-l-sm m-r-sm m-t-xs ">
    <table class="table table-bordered table-hover table-striped dataTable no-footer">
        <thead><?= $this->Html->tableHeaders(['OT', 'Campaña', 'Establecimiento', 'Lote', 'Cultivo']) ?></thead>
        <tbody>
            <tr>
                <td class="text-center"><?= $ordenTrabajosDistribuciones->orden_trabajo_id ?> </td>
                <td class="text-center"> <?= $ordenTrabajosDistribuciones->proyecto->campania_monitoreo->nombre ?> </td> 
                <td class="text-center"><?= $ordenTrabajosDistribuciones->orden_trabajo->establecimiento->nombre ?> </td> 
                <td class="text-center"><?= $ordenTrabajosDistribuciones->lote->nombre ?> </td>
                <td class="text-center"><?= $ordenTrabajosDistribuciones->proyecto->cultivo ?> </td>
            </tr>
        </tbody>
    </table>
</div>
<div class="modal-body-users p-xs">   
    <fieldset class="ps-3 pe-3">
        <?= $this->Form->create($ordenTrabajosMapeo, ['id'=>'formulario']) ?>
        <div class="col-md-12 ">
            <?= $this->Form->control('mapeos_campanias_tipo_id', ['type' => 'select', 'label' => 'Tipo Campaña', 'class' => 'form-control select2', 'options' => [], 'required']) ?>
        </div>
        <div class="col-md-12 m-t-xs">
            <?= $this->Form->control('mapeos_calidade_id', ['type' => 'select', 'label' => 'Calidad Mapeo', 'class' => 'form-control select2', 'options' => [], 'required' ]) ?>
        </div>
        <div class="col-md-12 m-t-xs">
            <?= $this->Form->control('mapeos_problema_id', ['type' => 'select', 'label' => 'Problema', 'class' => 'form-control select2', 'options' => [], 'required' ]) ?>
        </div>
        <div class="col-md-12 m-t-xs">
            <?= $this->Form->control('comentario', ['class' => 'form-control', 'type' => 'text', 'label' => 'Comentario', 'required']) ?>
        </div>
        <div class="col-md-12 m-t-xs">
            <?= $this->Form->control('user_id', ['type' => 'select', 'label' => 'Procesado Por', 'class' => 'form-control select2', 'options' => [], 'required' ]) ?>
        </div>
        
        <div class="col-md-12 m-t-xs">
            <div class="col-md-4 m-t-xs">
                <?= $this->Form->control('sms',['label' => '  SMS', 'type' => 'checkbox', 'class' => 'm-t-md' ,'checked' => '', 'required' ]) ?>
            </div>
            <div class="col-md-4 m-t-xs">
                <?= $this->Form->control('pdf',['label' => '  PDF', 'type' => 'checkbox', 'class' => 'm-t-md' ,'checked' => '', 'required' ]) ?>
            </div>    
            <div class="col-md-4 m-t-xs text-center">
                <?= $this->Form->button('Guardar', ['class' => "btn btn-success GuardarRegistro", 'type' => 'button', 'escape' => false]) ?>    
            </div>            
        </div>     
        <?= $this->Form->end() ?>
    </fieldset>
</div>

<script>
    /**
     * Inicializo Select2 MapeosCampaniasTipos
     */
    var data = JSON.parse($('#tipos').val());
    $('#mapeos-campanias-tipo-id').select2({
        theme: "bootstrap",
        placeholder: 'Seleccionar un Tipo Campaña...',
        width: '100%',
        allowClear: true,
        data:data,
        dropdownParent: $('#modal')
    }).val('').trigger('change');

    
    /**
     * Inicializo Select2 MapeosCalidades
     */
    var data = JSON.parse($('#calidades').val());
    $('#mapeos-calidade-id').select2({
        theme: "bootstrap",
        placeholder: 'Seleccionar un Tipo Calidad...',
        width: '100%',
        allowClear: true,
        data:data,
        dropdownParent: $('#modal')
    }).val('').trigger('change');

    /**
     * Inicializo Select2 Users
     */
    var data = JSON.parse($('#users').val());
    $('#user-id').select2({
        theme: "bootstrap",
        placeholder: 'Seleccionar un Usuario...',
        width: '100%',
        allowClear: true,
        data:data,
        dropdownParent: $('#modal')
    }).val('').trigger('change');

    /**
     * Inicializo Select2 MapeosProblemas
     */
    var comentario_obligatorio = "";
    var data = JSON.parse($('#problemas').val());
    $("#mapeos-problema-id").select2({
        theme: "bootstrap",
        width: '100%',
        placeholder: "Seleccionar un Problema...",
        allowClear: true,
        data: data,
        dropdownParent: $('#modal'),
        matcher: function (params, data) {
                // If there are no search terms, return all of the data
                if ($.trim(params.term) === '')  return data;

                // `params.term` should be the term that is used for searching
                // `data.text` is the text that is displayed for the data object
                if (data.text.toUpperCase().indexOf(params.term.toUpperCase()) > -1) {
                  var modifiedData = $.extend({}, data, true);
                  //  modifiedData.text += ' (matched)';

                  // You can return modified objects from here
                  // This includes matching the `children` how you want in nested data sets
                  return modifiedData;
                }
                return null;

        },
        templateResult: function ( data ) {
                    if (data.loading) {
                        return data.text;
                        console.log('otro');
                    }
                                      
                    let $container = $(`<div>${data.text}</div>`);
                    return $container;
                },
        templateSelection: function ( data ) {
            if (data.text) {
                
                comentario_obligatorio = data.requiere_comentario;
                return data.text;   
            }
            let $container = $(`<div>${data.text}</div>`);
            return $container;
            
        }
    }).val('').trigger('change').on('change.select2', function (e) {
        e.stopPropagation();
        $('#requiere-comentario').val(comentario_obligatorio);
    });

    /**
     * Guardar Registro
     */
    $('.GuardarRegistro').on('click', function() {

        if (!$("#formulario").valid()) {
            return;
        }
        $('.GuardarRegistro').attr('disabled', true);   

        var id = $('#id').val();
        const data = new FormData(document.getElementById('formulario'));       
        let url = new URL(`${window.location.protocol}//${window.location.host}/orden-trabajos-mapeos/add/${id}`);
        fetch(url, {
            method: 'POST',
            body: data
        })
        .then( res => res.json())
        .then( data => {
            $('#modal').modal('hide');
            $('#mapeos').DataTable().ajax.reload();
            if (data.response.status == 'success') {
                toastr.success(data.response.message);
                return;
            }
            toastr.error(data.response.message);
        });
    });

    /* valida el resto de las fechas */
    jQuery.validator.addMethod("validarComentario", function(value, element, parametro ) {
        var comentario = $(parametro[0]).val();
        if (comentario == 1 && value == '') {
            return false;
        } else {
            return true;
        }

        }, jQuery.validator.format ("Ingrese Comentario para este Problema.") 
    );
    
    /**
     * Validar el formulario 
     */
    $('#formulario').validate({
        rules: {
            mapeos_campanias_tipo_id: {
                required:true, 
            },
            mapeos_calidade_id: {
                required: true
            },
            mapeos_problema_id: {
                required: true
            },
            comentario: {
                required:false,
                validarComentario: ["#requiere-comentario"]
            },
            user_id: {
                required: true
            }
        },
        messages: {
            mapeos_campanias_tipo_id: { 
                required: "Ingrese Tipo de Campaña."
            },
            mapeos_calidade_id: {
                required: "Ingrese Calidad"
            },
            mapeos_problema_id: {
                required: "Ingrese un Problema"
            },
            user_id: {
                required: "Ingrese un Usuario"
            }
        },
        ignore: ["input[type=checkbox]"]
    });
 
</script>
