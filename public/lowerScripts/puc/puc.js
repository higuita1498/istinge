function updatePuc(){

    // Capturamnos el boton de envío
    var btnEnviar = $("#btnUpdate");
    $.ajax({
        type: $("#form-categoria").attr("method"),
        url: $("#form-categoria").attr("action"),
        data:$("#form-categoria").serialize(),
        success: function(data){
        /*
        * Se ejecuta cuando termina la petición y esta ha sido
        * correcta
        * */
       swal({
           title: 'Puc',
           html: 'Categoría del puc actualizada correctamente.',
           type: 'success',
           showConfirmButton: true,
           confirmButtonColor: '#1A59A1',
           confirmButtonText: 'ACEPTAR',
        });
        $("#modal-small").modal("hide");
        },
        error: function(data){
            /*
            * Se ejecuta si la peticón ha sido erronea
            * */
            alert("Problemas al tratar de enviar el formulario");
            $("#modal-small").modal("hide");
        }
    });
    // Nos permite cancelar el envio del formulario
    return false;
}

function guardarPuc(codigo){
        // Capturamnos el boton de envío
    var btnEnviar = $("#btnStore");
    var promise = $.ajax({
        type: $("#form-categoria-store").attr("method"),
        url: $("#form-categoria-store").attr("action"),
        data:$("#form-categoria-store").serialize(),
        success: function(data){
        /*
        * Se ejecuta cuando termina la petición y esta ha sido
        * correcta
        * */
        
        $("#collapse"+codigo).collapse('hide');
        $("#collapse"+codigo).html('');
        $("#collapse"+codigo).attr('estado',0);
        
       swal({
           title: 'Puc',
           html: 'Categoría del puc creada correctamente.',
           type: 'success',
           showConfirmButton: true,
           confirmButtonColor: '#1A59A1',
           confirmButtonText: 'ACEPTAR',
        });
        $("#modal-small").modal("hide");
        
        },
        error: function(data){
            /*
            * Se ejecuta si la peticón ha sido erronea
            * */
            alert("Problemas al tratar de enviar el formulario");
            $("#modal-small").modal("hide");
        }
    });
    promise.then(function(){
        showCategory(codigo);
    });
    // Nos permite cancelar el envio del formulario
    return false;
}