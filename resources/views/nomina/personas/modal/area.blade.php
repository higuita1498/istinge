<form method="POST" action="{{ route('personas.ajax_area') }}" role="form" class="forms-sample p-0" novalidate id="formulario-area" onsubmit="event.preventDefault();">
    @csrf
    <div class="row">
        <div class="col-md-12 form-group">
            <label class="control-label">Nombre Área <span class="text-danger">*</span></label>
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
            <a id="cancelarArea" data-dismiss="modal" class="btn btn-outline-light">Cancelar</a>
            <a href="javascript:guardarArea();" class="btn btn-success">Guardar</a>
        </div>
    </div>

<script>
    function guardarArea(){
        $.post($("#formulario-area").attr('action'), $("#formulario-area").serialize(), function(dato) {
            if (dato['status'] == 'OK') {
                var select = $('#area');
                select.append('<option value=' + dato['id'] + ' selected>' + dato['area'] + '</option>');
                select.val(dato['id']);
                select.selectpicker('refresh');
                $("#area").trigger('change');
                $('#cancelarArea').click();
                $('#formulario-area').trigger("reset");
                swal("Registro Guardado", "Nueva Área Almacenada", "success");
            } else {
                swal('ERROR', dato['mensaje'], "error");
            }
        }, 'json');
    }
</script>