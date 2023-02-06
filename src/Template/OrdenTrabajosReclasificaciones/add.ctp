<?php
   /**
    * Add Reclasificaciones 
    * 
    * Permitimos crear una reclasificacion nueva. 
    * 
    * Desarrollado para Adecoagro SA.
    * 
    * @category CakePHP3
    *
    * @package Ordenes
    *
    * @author Javier Alegre <jalegre@adecoagro.com>
    * @copyright Copyright 2021, Adecoagro
    * @version 1.0.0 creado el 02/08/2022
    */
?>
<div class="modal-header text-left">
    <div class="pull-right">
        <?= $this->Form->button('<i class="fa fa-save"></i>', ['class' => 'btn-monitoreo btn-icon-only btn-circle GuardarRegistro' ,'title' => 'Guardar Registro','type' => 'button', 'escape' => false]) ?>
        <?= $this->Form->button('<i class="fa fa-times"></i>', ['class' => 'btn-monitoreo btn-icon-only btn-circle' , 'data-dismiss' => "modal", 'title' => 'Cancelar','type' => 'button', 'escape' => false]) ?>
    </div>
    <h5 class="modal-title">Creando una reclasificaci&oacute;n</h5>
</div>
<div class="modal-body">
    <?= $this->Form->create($ordenTrabajosReclasificacione, ['id' => 'form-reclasificaciones']) ?>
    <div class="row no-margins no-padding">
        <fieldset>
            <div class="col-md-12 ">
                <?= $this->Form->control('nombre', ['class' => 'form-control', 'type' => 'text', 'label' => 'Lote de Reclasificación', 'required']) ?>
            </div>
            <div class="col-md-12">
                <?= $this->Form->control('establecimiento_id', ['type' => 'select', 'class' => 'form-control select2', 'options' => $establecimientos, 'label' => 'Establecimiento', 'required']) ?>
            </div>
            <div class="col-md-12">
                <?= $this->Form->control('observaciones', ['type' => 'textarea', 'class' => 'form-control']) ?>
            </div>
            <?= $this->Form->control('user_id', ['type' => 'hidden', 'value' => $this->request->session()->read('Auth.User.id')]) ?>
        </fieldset>
    </div>
    <?= $this->Form->end() ?>
</div>
<script>
    
    $(".select2").select2({
        theme: "bootstrap",
        width: "100%",
        placeholder: 'Seleccione un establecimiento ...',
        dropdownParent: $("#reclasificaciones")
    }).val('').trigger('change');
        
    /* --------------------------------------------------------------------------- */
    /* Validación del formulario                                                   */
    /* --------------------------------------------------------------------------- */
        $('#form-reclasificaciones')
           .find('.select2-select')
           .select2({
               theme: "bootstrap"
           })
           // Revalidate your field when it is changed
           .change(function(e) {
               $(this).valid();
           })
           .end()
           .validate({
               ignore: ["input[type=hidden]"],
               errorClass: 'help-block animation-pullUp',
               errorElement: 'div',
               errorPlacement: function(error, e) {
                   e.parents('.ot-form-group').append(error);
               },
               highlight: function(e) {
                   $(e).closest('.ot-form-group').removeClass('has-success has-error')
                       .addClass('has-error');
                   $(e).closest('.help-block').remove();
               },
               success: function(e) {
                   e.closest('.ot-form-group').removeClass('has-success has-error');
                   e.closest('.help-block').remove();
               },
               rules: {
                   nombre: {required:true},
                   establecimiento_id: {required: true}
               },
               messages: {
                   nombre: {
                       required: 'Falta el nombre del lote de reclasificación'
                   },
                   establecimiento_id: {
                       required: 'Falta el establecimiento'
                   }
               }
           });
    /* --------------------------------------------------------------------------- */
    
    $('.GuardarRegistro').on('click', function() {
        
        var FormLabores = $('#form-reclasificaciones');
        FormLabores.validate();
        
        if (FormLabores.valid()) {
            const data = new FormData(document.getElementById('form-reclasificaciones'));
            let url = new URL(`${window.location.protocol}//${window.location.host}/orden-trabajos-reclasificaciones/add`);
            
            fetch(url, {
                method: 'POST',
                body: data
            })
            .then( res => res.json())
            .then( data => {
                if (data.status === 'success') {
                    $('#reclasificaciones').modal('hide');
                    let url = new URL(`${window.location.protocol}//${window.location.host}/orden-trabajos-reclasificaciones/edit/${data.data}`);
                    window.location.href = url;
                    return;
                }
                toastr.error(data.message);
            });
        }
    });
</script>