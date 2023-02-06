<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\OrdenTrabajo $ordenTrabajo
 */
    /* Uso los objetos de la fecha de cake */
    $date = date_create();
    $cadena_fecha_actual = date_format($date, 'd/m/Y');  /* Ej: 22/01/2018 13:40 */
?>
<div class="row border-bottom white-bg page-heading">
    <div class="col-lg-9 col-md-9 m-t-xs">
        <h3>Orden de Trabajo</h3>
    </div>
    <div class="col-lg-3 m-t-xs"></div>
</div>

<?= $this->Form->create($ordenTrabajo, ['id' => 'OrdenTrabajo']) ?>
    <div class="ibox float-e-margins">
        <div class="ibox-title">
            <div class="ibox-tools">
                <?= $this->Form->button('<i class="sicon-siembra"></i>  OT Siembra', ['id' => 'btn_siembra','type' => 'button','class'=>'btn btn-success btn-sm']) ?>
                <?= $this->Form->button('<i class="fa fa-save"></i> Generar Orden de Trabajo', ['id' => 'guardar','type' => 'submit','class'=>'btn btn-primary btn-sm']) ?>
            </div>
        </div>
        <div class="ibox-content">
            <fieldset>
               <div class="col-md-12 no-margin no-padding">
                   <div class="col-md-2">
                       <?= $this->Form->control('fecha',['type' => 'text','class' => 'form-control datepicker', 'value' => $cadena_fecha_actual, 'escape' => false]) ?>
                   </div>
                   <div class="col-md-5">
                       <?= $this->Form->control('establecimiento_id',['type'=> 'select', 'options' => $establecimientos,'class' => 'form-control select2']) ?>
                   </div>
                   <div class="col-md-5">
                       <?= $this->Form->control('proveedore_id',['type'=> 'select', 'label' => 'Proveedor','options' => [],'class' => 'form-control select2', 'required']) ?>
                   </div>
                    <div class="col-md-12">
                        <hr class="hr-line-solid">
                    </div>
                    <div class="col-md-12 m-l-none m-r-none">
                        <?= $this->Form->control('observaciones', ['type' => 'textarea', 'class' => 'form-control']) ?>
                    </div>
               </div>
                <br/>                                
                <?php
                    echo $this->Form->control('estado', ['value' => 0, 'type'=>'hidden']);
                    echo $this->Form->control('descripcion', ['value' => 0, 'type'=>'hidden']);
                    echo $this->Form->control('velocidadviento', ['value' => 0, 'type'=>'hidden']);
                    echo $this->Form->control('temperatura', ['value' => 0, 'type'=>'hidden']);
                    echo $this->Form->control('consumogasoil', ['value' => 0, 'type'=>'hidden']);
                    echo $this->Form->control('humedad', ['value' => 0, 'type'=>'hidden']);
                    echo $this->Form->control('siembra', ['type'=>'hidden']);
                    echo $this->Form->control('user_id', ['type'=>'hidden', 'value' => $this->request->session()->read('Auth.User.id')]);
                ?>
            </fieldset>
        </div>
    </div>
<?= $this->Form->end() ?>
<script>
    
    $("#establecimiento-id").select2({
        theme: "bootstrap",
        width: '100%'
    });
    $('.datepicker').datepicker({
        language: 'es',
        format: 'dd/mm/yyyy',
        autoclose: true
    });
    
    /* Configuro el Select de Proveedores */
    $("#proveedore-id").select2({
        theme: "bootstrap",
        width: '100%',
        placeholder: "Ingrese el proveedor ...",
        minimumInputLength: 3,
        ajax: {
            url: "/proveedores/search",
            dataType: 'json',
            data: function (params) {
                var query = {
                    q: params.term,
                    establecimiento: $('#establecimiento-id').val()
                };
                return query;
            },
            processResults: function (data, params) {
                return { results: data.proveedores };
            }
        }
    });

    $('#btn_siembra').on('click', function () {
        $('#siembra').val(1);
        $( "#OrdenTrabajo" ).submit();
    });
    $('#OrdenTrabajo')
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
                'proveedore_id': {
                    required: true
                }
            },
            messages: {
                'proveedore_id': {
                    required: 'Seleccione el proveedor'
                }
            }
        });
 </script>