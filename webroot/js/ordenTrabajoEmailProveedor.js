 /**
 * Ordenes de Trabajo
 *
 * Actualiza email de Proveedor y enviar Orden de Trabajo por email.
 *
 * Desarrollado para Adecoagro SA.
 * 
 * @author Pablo Snaider <psnaider@adecoagro.com>
 * @copyright Copyright 2022, Adecoagro
 * @version 1.0.0 creado el 11/10/2022
 */

 /**
     * Enviar email
     * verifico si el proveedor tiene email, sino habilito el modal para que ingrese un correo
     * para enviar la OT
     */
  $('#enviar-email').on('click', function (e) {
    if ($('#email-proveedor').val() == '') {
        $('#email').val('');
        $('#ingresar_email').modal({show:true});
    } else {
        EnviarEmailOrdenTrabajo();
    }
});

/**
 * Enviar                                               
 * Envio para guardar el correo ingresado y enviar la OT
 *
 */
$('.Enviar').on('click', function() {
    
    $("#formulario").validate({
        rules: {
            email: {
                required: true,
                email: true
            }
        },
        messages: {
            email: {
                required: "Debe ingresar un dirección de correo.",
                email: "Su dirección de correo debe tener el formato name@domain.com"
            }
        }
    });

    const data = new FormData();
    data.append('email', $('#email').val());
    data.append('id', $('#proveedor').val()); 
    
    let url = new URL(`${window.location.protocol}//${window.location.host}/proveedores/actualizar-email`);
    fetch(url, {
        method: 'POST',
        body: data
    })
    .then( res => res.json())
    .then( data => {
        $('#ingresar_email').modal('hide');
        
        if (data.response.status == 'success') {
            toastr.info(data.response.message);
            $('#email-proveedor').val( $('#email').val() );
            EnviarEmailOrdenTrabajo();
        } else {
            toastr.error(data.response.message);
        }
    });
    
});


/**
 * EnviarEmailOrdenTrabajo
 * Envia correo electronico de la OT al correo registrado
 * 
 */
function EnviarEmailOrdenTrabajo() {

    let id = $('#id').val();
    let url = new URL(`${window.location.protocol}//${window.location.host}/orden-trabajos/notificar-ot-proveedor/${id}`);
    fetch(url)
        .then( res => res.json())
        .then( data => {
            
            if (data.status == 'success') {
                toastr.info(data.message);
            } else {
                toastr.error(data.message);
            }
        });        
}