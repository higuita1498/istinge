<form method="POST" action="{{ route('permisos.guardar') }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form" >
    {{ csrf_field() }}
    <input type="hidden" name="idUsuario" value="{{$idUsuario}}">
    <div class="row">
        <div class="col-md-12">
            <label class="control-label">Seleccionar todos</label>
            <input type="checkbox" id="selectall">
            <br>
            @foreach($modulos as $modulo)
                <label class="control-label text-uppercase font-weight-bold">{{$modulo->nombre_modulo}}</label><br>
                @foreach($permisosUsuario as $key => $permiso)
                    @if($modulo->id == $permiso->id_modulo)
                        @php
                            $check = '';
                            if($permisosUsuario[$key]->id_usuario!=NULL) {
                                $check = 'checked';
                            }
                        @endphp
                        <div class="form-check form-check-flat ml-4 mt-1">
                            <label class="form-check-label">
                            <input type="checkbox" class="form-check-input checks casec" name="permiso[{{$permiso->id}}]" {{$check}} value="1"> {{$permiso->nombre_permiso}}<i class="input-helper"></i></label>
                        </div>
                    @endif
                @endforeach
            @endforeach
            <br>
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
    
    $("#selectall").on("click", function() {
        $(".casec").attr("checked", this.checked);
    });

    $(document).ready(function () {
        $("#guardar").click(function (form) {
            $.post($("#form").attr('action'), $("#form").serialize(), function (dato) {
                if(dato['status']=='OK'){
                    $('#cancelar').click();
                    $('#form').trigger("reset");
                    swal("Registro Guardado", "Se ha Actualizado los permisos Para el Usuario!!!", "success");
                } else {
                    swal('Info!!', dato['mensaje'], "error");
                    //alert(dato['mensaje']);
                }
            }, 'json');
        });

        $('#cancelar').click(function () {
            $('#form').trigger("reset");
        });
    });
</script>
