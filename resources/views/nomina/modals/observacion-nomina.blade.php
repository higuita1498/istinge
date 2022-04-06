<div class="modal fade bd-example" id="modal-agreg-observacion-nomina" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalLabel"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Agregar comentario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

            <textarea cols="57" rows="8" id="textarea">
            </textarea>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-light" data-dismiss="modal" id="cancelarModalObserv">
                    Cerrar
                </button>
                <a data-route="{{ route('nomina.agregar.observacion') }}" id="btn-guardar-obser-nomina"
                   data-nomina
                   href="javascript:guardarObservacion()"
                   class="btn btn-success">Guardar</a>
            </div>
        </div>
    </div>
</div>

<script>

    function guardarObservacion() {

        cargando(true);

        const btn = $('#btn-guardar-obser-nomina');
        const route = btn.data('route');
        const nomina = btn.data('nomina');
        const observ = $('#textarea').val();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        jQuery.ajax({
            method: "POST",
            data: {'nomina': nomina, 'observ': observ},
            url: route,
            success: function (data) {
                cargando(false);
                swal("Registro Actualizado", "Observación agregada exitosamente", "success");
                $('#cancelarModalObserv').click();
                $('.btn-comentario').val(data.nota)

            },
            error: function (error) {
                swal('ERROR', 'No se ha podido agregar la observación', "error");
            }
        })

    }


</script>
