<form method="POST" action="{{ route('recarga.usuario') }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-recarga">
    {{ csrf_field() }}
    <input type="hidden" name="id" value="{{$usuario->id}}">
    <div class="row">
        <div class="col-md-6 form-group">
            <label class="control-label">Saldo Disponible</label>
            <input type="text" class="form-control" id="saldo" name="saldo" readonly disabled value="{{$usuario->saldo}}">
        </div>
        
        <div class="col-md-6 form-group">
            <label class="control-label">Saldo a Recargar</label>
            <input type="number" class="form-control" id="recarga" name="recarga" required="" min="1" value="1">
        </div>
    </div>
    
    <div class="row" >
        <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal" id="cancelar">Cancelar</button>
            <button type="button" id="guardar" class="btn btn-success">Guardar</button>
        </div>
    </div>
</form>

<script>
    $("#form").submit(function () {
        return false;
    });

    $(document).ready(function () {
        $("#guardar").click(function (form) {
            cargando(true);
            $.post($("#form-recarga").attr('action'), $("#form-recarga").serialize(), function (dato) {
                cargando(false);
                if(dato['success']==true){
                    $('#cancelar').click();
                    $('#form-recarga').trigger("reset");
                    swal("Registro Guardado", dato['mensaje'], "success");
                    var a = document.createElement("a");
                    a.href = window.location.pathname;
                    a.click();
                } else {
                    swal('Info!!', dato['mensaje'], "error");
                }
            }, 'json');
        });

        $('#cancelar').click(function () {
            $('#form-recarga').trigger("reset");
        });
    });
</script>
