<form method="POST" action="{{ route('ganancia.usuario') }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-ganancia">
    {{ csrf_field() }}
    <input type="hidden" name="id" value="{{$usuario->id}}">
    <div class="row">
        @if($usuario->ganancia>0)
        <div class="col-md-6 form-group">
            <label class="control-label">Monto Disponible (Ganancia)</label>
            <input type="text" class="form-control" id="saldo" name="saldo" readonly disabled value="{{$usuario->ganancia}}">
        </div>
        
        <div class="col-md-6 form-group">
            <label class="control-label">Monto a Intercambiar (Saldo)</label>
            <input type="number" class="form-control" id="recarga" name="recarga" required="" min="1" value="1" max="{{$usuario->ganancia}}">
        </div>
        @else
        <div class="alert alert-danger" role="alert">
            <h4 class="alert-heading">ERROR</h4>
            <p>Disculpe, Ud. no posee saldo de ganancia disponible para realzar intercambio</p>
            <hr>
            <p class="mb-0">Lo invitamos a recargar saldo, usando la plataforma WOMPI haciendo <a href="{{route('empresa')}}" class="alert-link">click aqui</a>.</p>
        </div>
        @endif
    </div>
    
    <div class="row" >
        <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
            @if($usuario->ganancia>0)
            <button type="button" class="btn btn-outline-secondary" data-dismiss="modal" id="cancelar">Cancelar</button>
            <button type="button" id="guardar" class="btn btn-success">Guardar</button>
            @endif
        </div>
    </div>
</form>

<script>
    $("#form").submit(function () {
        return false;
    });

    $(document).ready(function () {
        $("#guardar").click(function (form) {
            cargando('true');
            $.post($("#form-ganancia").attr('action'), $("#form-ganancia").serialize(), function (dato) {
                $('#cancelar').click();
                $('#form-ganancia').trigger("reset");
                if(dato['ganancia'] == 0){
                    $('#div_ganancia').attr('style', 'background: #fc2919;padding: 10px 20px;border-radius: 15px;');
                }else{
                    $('#div_ganancia').attr('style', 'background: #55de4c;padding: 10px 20px;border-radius: 15px;');
                }
                
                $('#div_ganancia').text('GANANCIA: '+dato['ganancia']);
                
                if(dato['saldo'] == 0){
                    $('#div_saldo').attr('style', 'background: #fc2919;padding: 10px 20px;border-radius: 15px;');
                }else{
                    $('#div_saldo').attr('style', 'background: #55de4c;padding: 10px 20px;border-radius: 15px;');
                }
                $('#div_saldo').text('SALDO: '+dato['saldo']);
                $('.loader').removeAttr('style').attr('style','display:none');
                swal(dato['title'], dato['mensaje'], dato['type']);
            }, 'json');
        });

        $('#cancelar').click(function () {
            $('#form-ganancia').trigger("reset");
        });
    });
</script>
