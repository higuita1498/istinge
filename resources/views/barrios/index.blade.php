@extends('layouts.app')

@section('content')
    @if(Session::has('success'))
        <div class="alert alert-success">
            {{Session::get('success')}}
        </div>
        <script type="text/javascript">
            setTimeout(function() {
                $('.alert').hide();
                $('.active_table').attr('class', ' ');
            }, 5000);
        </script>
    @endif

    @if(Session::has('error'))
        <div class="alert alert-danger" >
            {{Session::get('error')}}
        </div>

        <script type="text/javascript">
            setTimeout(function(){
                $('.alert').hide();
                $('.active_table').attr('class', ' ');
            }, 8000);
        </script>
    @endif

    @if(Session::has('danger'))
        <div class="alert alert-danger">
            {{Session::get('danger')}}
        </div>
        <script type="text/javascript">
            setTimeout(function() {
                $('.alert').hide();
                $('.active_table').attr('class', ' ');
            }, 5000);
        </script>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(Session::has('message_success'))
        <div class="alert alert-success" role="alert">
            {{Session::get('message_success')}}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="row card-description">
        <div class="col-md-12">
            <table class="table table-striped table-hover" id="table-general">
                <thead class="thead-dark">
                <tr>
                    <th>Nombre</th>
                    <th># Clientes</th>
                    <th>Acciones</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach ($barrios as $barrio)
                @php $nroClientes = $barrio->nroClientes(); @endphp
                    <tr id="tr-{{ $barrio->id }}">
                        <td>{{$barrio->nombre}}</td>
                        <td>{{ $nroClientes }}</td>
                        <td>
                            <a href="javascript:void(0)" onclick="editBarrio({{ $barrio->id }})" class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
                            @if($nroClientes == 0)
                            <button class="btn btn-outline-danger btn-icons" onclick="deleteBarrio({{ $barrio->id }})" title="Eliminar">
                                <i class="fas fa-times"></i>
                            </button>
                            @endif
                        </td>
                        <td></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>


    <div class="modal fade" id="modalbarrio" role="dialog">
        <div class="modal-dialog modal-sm">
            <input type="hidden" id="trFila" value="0">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Editar Barrio</h4>
                      <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body" id="body-barrio">
                    <div class="row">
                        <input type="hidden" class="form-control"  id="barrio_id" name="barrio_id">
                        <div class="col-md-12 form-group">
                        <label class="control-label">Nombre <span class="text-danger">*</span></label>
                        <input type="text" class="form-control"  id="nombre" name="nombre"  required="" value="" maxlength="200" autocomplete='off'>
                        <span class="help-block error">
                            <strong>{{ $errors->first('nombre') }}</strong>
                        </span>
                        </div>

                    </div>
                    <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
                    <hr>
                        <div class="row" >
                        <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">

                        <button type="submit" id="updateBarrio" value="barrio" class="btn btn-success">Actualizar</button>
                        </div>
                        </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('scripts')
<script>
    function editBarrio(id){

        if (window.location.pathname.split("/")[1] === "software") {
        var url='/software/empresa/barrios/' + id + '/edit';
        }else{
        var url = '/empresa/barrios/' + id + '/edit';
        }

        var _token = { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') };
        cargando(true)

        $.ajax({
                url: url,
                method: 'GET',
                success: function(data) {
                    $("#modalbarrio").modal('show');
                    $("#nombre").val(data.nombre);
                    $("#barrio_id").val(data.id);
                    cargando(false);
                }
            });
    }

    $("#updateBarrio").click(function(){
        if (window.location.pathname.split("/")[1] === "software") {
        var url='/software/empresa/barrios/update/';
        }else{
        var url = '/empresa/barrios/update';
        }

        let barrio_id = $("#barrio_id").val();
        let nombre = $("#nombre").val();

        var _token = { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') };
        cargando(true)

        $.ajax({
            url: url,
            method: 'PUT',
            headers: _token,
            data: {
                nombre,
                barrio_id
            },
            success: function(data) {
                swal({
                    title: 'Actualizado',
                    html: 'Barrio actualizado correctamente!',
                    type: 'success',
                });
                $('#tr-' + barrio_id).find('td').first().text(nombre);
                $("#modalbarrio").modal('hide');

                cargando(false);
            }
        })
    });

    function deleteBarrio(barrio_id){

        if (window.location.pathname.split("/")[1] === "software") {
        var url='/software/empresa/delete-barrio/'+barrio_id;
        }else{
        var url = '/empresa/delete-barrio/'+barrio_id;
        }
        var _token = { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') };

        swal({
            title: "Eliminar Barrio?",
            text: "Los barrios que se pueden eliminar no están asociados a ningun cliente",
            type: 'question',
            showCancelButton: true,
            confirmButtonColor: '#00ce68',
            cancelButtonColor: '#d33',
            confirmButtonText: "Eliminar",
            cancelButtonText: 'No',
            }).then((result) => {
                if (result.value) {
                    cargando(true);
                    $.ajax({
                        url: url,
                        headers: _token,
                        type: 'post',
                        success: function(data) {
                            console.log(data)
                            if(data == 200){
                                swal({
                                    title: 'Eliminado',
                                    html: 'Barrio Eliminado correctamente!',
                                    type: 'success',
                                });
                                $('#tr-' + barrio_id).remove();
                                cargando(false)

                            }else{
                                alert("Error")
                                cargando(false)
                            }
                        }
                    });
                }
            });
    }
</script>
@endsection
