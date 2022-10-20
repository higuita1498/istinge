<form method="POST" action="{{ route('personas.ajax_cargo') }}" role="form" class="forms-sample p-0" novalidate id="formulario-cargo" onsubmit="event.preventDefault();">
    @csrf
    <div class="row">
        <div class="col-md-12 form-group">
            <label class="control-label">Nombre Cargo <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="nombre" required="" value="{{old('nombre')}}" maxlength="200">
            <span class="help-block error">
                <strong>{{ $errors->first('nombre') }}</strong>
            </span>
        </div>
    </div>
    <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
    <hr>
</form>
    <div class="row" style="text-align: right;">
        <div class="col-md-12">
            <a id="cancelarCargo" data-dismiss="modal" class="btn btn-outline-light">Cancelar</a>
            <a href="javascript:guardarCargo()" class="btn btn-success">Guardar</a>
        </div>
    </div>

<script>
    function guardarCargo(){
        $.post($("#formulario-cargo").attr('action'), $("#formulario-cargo").serialize(), function(dato) {
            if (dato['status'] == 'OK') {
                var select = $('#cargo');
                select.append('<option value=' + dato['id'] + ' selected>' + dato['cargo'] + '</option>');
                select.val(dato['id']);
                select.selectpicker('refresh');
                $("#cargo").trigger('change');
                $('#cancelarCargo').click();
                $('#formulario-cargo').trigger("reset");
                swal("Registro Guardado", "Nuevo Cargo Almacenado", "success");
            } else {
                swal('ERROR', dato['mensaje'], "error");
            }
        }, 'json');
    }
</script>