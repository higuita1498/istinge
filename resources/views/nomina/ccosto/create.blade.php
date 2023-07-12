<form method="POST" action="{{ route('ccosto.store') }}" role="form" class="forms-sample p-0" novalidate id="store-ccosto" onsubmit="event.preventDefault();">
    @csrf
    <div class="row">
        <div class="col-md-12 form-group">
            <label class="control-label">Nombre Centro de Costos <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="nombre" required="" value="{{old('nombre')}}" maxlength="200">
            <span class="help-block error">
                <strong>{{ $errors->first('nombre') }}</strong>
            </span>
        </div>
        <div class="col-md-6 form-group">
            <label class="control-label">Prefijo Cuenta Contable</label>
            <input type="text" class="form-control" name="prefijo" value="{{old('prefijo')}}" maxlength="200">
            <span class="help-block error">
                <strong>{{ $errors->first('prefijo') }}</strong>
            </span>
        </div>
        <div class="col-md-6 form-group">
            <label class="control-label">Código Cuenta Contable</label>
            <input type="text" class="form-control" name="codigo" value="{{old('codigo')}}" maxlength="200">
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
            <a id="cancelarCcosto" data-dismiss="modal" class="btn btn-outline-light">Cancelar</a>
            <a href="javascript:store()" class="btn btn-success">Guardar</a>
        </div>
    </div>

<script>
    function store(){
        $.post($("#store-ccosto").attr('action'), $("#store-ccosto").serialize(), function(dato) {
            if (dato['status'] == 'OK') {
                $('#cancelarCcosto').click();
                $('#store-ccosto').trigger("reset");
                swal("Registro Guardado", "Nuevo Centro de Costo Almacenado", "success");
                if(dato['prefijo']==null){ var prefijo = ''; }else{ var prefijo = dato['prefijo']; }
                if(dato['codigo']==null){ var codigo = ''; }else{ var codigo = dato['codigo']; }
                $("#tbody").append('<tr id="'+dato['id']+'"><td>'+dato['nombre']+'</td><td>'+prefijo+'</td><td>'+codigo+'</td><td></td><td><button class="btn btn-outline-primary btn-icons mr-1 editCC" idCC="'+dato['id']+'" onclick="editTable('+dato['id']+')" title="Editar Centro de Costo"><i class="fas fa-edit"></i></button><button class="btn btn-outline-danger btn-icons destroyCC" idCC="'+dato['id']+'" onclick="destroyTable('+dato['id']+')" title="Eliminar Centro de Costo"><i class="fas fa-times"></i></button></td></tr>');
            } else {
                swal('ERROR', dato['mensaje'], "error");
            }
        }, 'json');
    }
</script>