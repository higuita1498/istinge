<form method="POST" action="{{ route('ctacontable.update') }}" role="form" class="forms-sample p-0" novalidate id="edit-ctacontable" onsubmit="event.preventDefault();">
    @csrf
    <input type="hidden" name="id" id="cta_id">
    <div class="row">
        <div class="col-md-12 form-group">
            <label class="control-label">Código Cuenta Contable <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="codigo" id="cta_codigo" required="" maxlength="200">
            <span class="help-block error">
                <strong>{{ $errors->first('codigo') }}</strong>
            </span>
        </div>
    </div>
    <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
    <hr>
</form>
    <div class="row" style="text-align: right;">
        <div class="col-md-12">
            <a id="cancelarCtacontable" data-dismiss="modal" class="btn btn-outline-light">Cancelar</a>
            <a href="javascript:editCtaContable()" class="btn btn-success">Guardar</a>
        </div>
    </div>

<script>
    function editCtaContable(){
        $.post($("#edit-ctacontable").attr('action'), $("#edit-ctacontable").serialize(), function(dato) {
            if (dato['status'] == 'OK') {
                $('#cancelarCtacontable').click();
                $('#modalCtaEdit').modal("hide");
                $('#edit-ctacontable').trigger("reset");
                swal("Registro Actualizado", "Actualización de Cuenta Contable Satisfactoria", "success");
                $('#'+dato['id']).html('<td>'+dato['nombre']+'</td><td>'+dato['codigo']+'</td><td></td><td><button class="btn btn-outline-primary btn-icons mr-1" idCta="'+dato['id']+'" onclick="editTableCta('+dato['id']+')" title="Editar Cta. Contable"><i class="fas fa-edit"></i></button></td>');
            } else {
                swal('ERROR', dato['mensaje'], "error");
            }
        }, 'json');
    }
</script>