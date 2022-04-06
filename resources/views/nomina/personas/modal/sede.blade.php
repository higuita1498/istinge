<form method="POST" action="{{ route('personas.ajax_sede') }}" role="form" class="forms-sample p-0" novalidate id="formulario-sede" onsubmit="event.preventDefault();">
    @csrf
    <div class="row">
        <div class="col-md-12 form-group">
            <label class="control-label">Nombre Sede <span class="text-danger">*</span></label>
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
            <a id="cancelarSede" data-dismiss="modal" class="btn btn-outline-light">Cancelar</a>
            <a href="javascript:guardarSede()" class="btn btn-success">Guardar</a>
        </div>
    </div>

<script>
    function guardarSede(){
        $.post($("#formulario-sede").attr('action'), $("#formulario-sede").serialize(), function(dato) {
            if (dato['status'] == 'OK') {
                var select = $('#sede');
                select.append('<option value=' + dato['id'] + ' selected>' + dato['sede'] + '</option>');
                select.val(dato['id']);
                select.selectpicker('refresh');
                $("#sede").trigger('change');
                $('#cancelarSede').click();
                $('#formulario-sede').trigger("reset");
                swal("Registro Guardado", "Nueva Sede Almacenada", "success");
            } else {
                swal('ERROR', dato['mensaje'], "error");
            }
        }, 'json');
    }
</script>