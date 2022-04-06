function validateDianNomina(id, rutasuccess, codigo) {

    $titleswal = codigo + '<br> ¿Emitir Nomina a la Dian?';
    $textswal = "No podrás retroceder esta acción";
    $confirmswal = "Si, emitir";

    if (window.location.pathname.split("/")[1] === "software") {
        var url='/software/empresa';
    }else{
        var url = '/empresa';
    }

    Swal.fire({
        title: $titleswal,
        text: $textswal,
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: $confirmswal,
    }).then((result) => {
        if (result.value) {

            const Toast = Swal.mixin({
                toast: true,
                position: 'top-center',
                showConfirmButton: false,
                timer: 1000000000,
                timerProgressBar: true,
                onOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            })

            Toast.fire({
                type: 'success',
                title: 'Emitiendo nomina a la DIAN...',
            })


            // var btn = document.getElementsByClassName(".swal2-confirm.swal2-styled");
            // setTimeout(function () {
            //     btn.setAttribute('disabled', 'disabled');
            // }, 1);
            // setTimeout(function () {
            //     btn.removeAttribute('disabled');
            // }, 5000);


            $.ajax({
                url: url + '/nominadian/validatedian',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                method: 'get',
                data: { id: id, },
                success: function(response) {

                    // console.log(response);

                    if (response == 'nomina-vencida') {
                        $mensaje = "Para emitir a la Dian se debe tener un inicio en la numeración de la factura.";
                        $footer = "<a target='_blank' href='/empresa/configuracion/numeraciones_nomina_electronica/lista'>Configura tus numeraciones</a>";
                        $img = "gif-tuerca.gif";
                        messageValidateDian($mensaje, $footer, $img);
                    } else if (response == 'nomina-consecutivo-limite') {
                        $mensaje = "La numeración ha superado el limite de consecutivos";
                        $footer = "<a target='_blank' href='/empresa/configuracion/numeraciones_nomina_electronica/lista'>Configura tus numeraciones</a>";
                        $img = "gif-tuerca.gif";
                        messageValidateDian($mensaje, $footer, $img);
                    } else if (response == 'plazo-vencido') {
                        $mensaje = "El plazo de 10 días ha caducado para emitir nóminas electrónicas";
                        $footer = "";
                        $img = "gif-tuerca.gif";
                        messageValidateDian($mensaje, $footer, $img);
                    } else if (response == 'mucha-solicitud') {
                        $mensaje = "Hay demasiadas solicitudes en la Dian, por favor intentalo más tarde.";
                        $footer = "";
                        $img = "gif-tuerca.gif";
                        messageValidateDian($mensaje, $footer, $img);
                    } else if (response.statusCode == 409 || response.statusCode == 400 || response.statusCode == 500) {

                        motivo = "";
                        i = 1;
                        response.warnings.reverse().forEach(e => {
                            motivo += `<p style="color:red;font-size:16px;">${i}. ${e}</p> <br>`;
                            i++;
                        });

                        Swal.fire({
                            type: 'error',
                            title: 'Error',
                            html: "No se pudo emitir la nomina Documento con errores en campos mandatorios. <br>" + motivo,
                        })
                    } else if (response.statusCode == 504) {

                        if (response.errorMessage) {
                            msgError = response.errorMessage
                        } else {
                            msgError = "Error interno de la Dian, porfavor vuelve a intentarlo en unos minutos."
                        }

                        Swal.fire({
                            type: 'error',
                            title: 'Error',
                            html: msgError,
                        })

                    } else if (response == 'codigo-repetido') {
                        $mensaje = "Error al emitir nomina repetida, por favor intente nuevamente";
                        $footer = "";
                        $img = "gif-tuerca.gif";
                        messageValidateDian($mensaje, $footer, $img);
                    }

                    //-- /Validaciones para la factura --//
                    else {


                        const Toast = Swal.mixin({
                            toast: true,
                            position: 'top-center',
                            showConfirmButton: false,
                            timer: 1000000000,
                            timerProgressBar: true,
                            onOpen: (toast) => {
                                toast.addEventListener('mouseenter', Swal.stopTimer)
                                toast.addEventListener('mouseleave', Swal.resumeTimer)
                            }
                        })

                        Toast.fire({
                            type: 'success',
                            title: 'La nomina fue emitida satisfactoriamente',
                        })


                        window.location.href = rutasuccess;


                    }

                }
            })
        }
    })
}

function messageValidateDian($mensaje, $footer, $img) {

    var confirmButtonText = "OK";

    if ($img == "contrato.png") {
        confirmButtonText = '<a target="_blank" href="/empresa/configuracion" style="color:#fff">OK</a>';
    }
    Swal.fire({
        //type: 'error',
        title: 'Oops...',
        text: $mensaje,
        imageUrl: '/images/Documentacion/validaciones/' + $img,
        imageWidth: '25%',
        imageHeight: '100%',
        imageAlt: 'Custom image',
        confirmButtonText: confirmButtonText,
        footer: $footer
    })
}