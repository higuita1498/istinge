@extends('layouts.app')
@section('content')
	<style>
    .readonly{ border: 0 !important; }
  </style>

    @if(Session::has('danger'))
		<div class="alert alert-danger" >
			{{Session::get('danger')}}
		</div>

		<script type="text/javascript">
			setTimeout(function(){
			    $('.alert').hide();
			    $('.active_table').attr('class', ' ');
			}, 5000);
		</script>
	@endif

    <form method="POST" action="{{ route('radicados.store') }}" style="padding: 2% 3%;" role="form" class="forms-sample" id="form-radicado" >
        {{ csrf_field() }}
        <div class="row">
            <div class="col-md-4 form-group">
                <label class="control-label">Nombre del Cliente</label>
                <div class="input-group" id="selectApi">
                    <select class="form-control selectpicker" name="cliente" id="cliente" required="" title="Seleccione" data-live-search="true" data-size="5" onchange="busqueda_detalles(this.value);">
                        @foreach($clientes as $client)
                        <option value="{{$client->id}}" {{$cliente== $client->id?'selected':''}}>{{$client->nombre}} {{$client->apellido1}} {{$client->apellido2}} - {{$client->nit}}</option>
                        @endforeach
                    </select>
                </div>
                <span class="help-block error">
                    <strong>{{ $errors->first('cliente') }}</strong>
                </span>
            </div>

            <div class="col-md-4">

                <ul id="list-contratos" class="list-group list-group-horizontal mt-4 mb-4" style="cursor:pointer">

                </ul>

            </div>

        </div>

        <div class="row" id="content" style="display: none;">
            <input type="hidden" class="form-control" id="id_cliente" name="id_cliente" readonly="" maxlength="20">
            <input type="hidden" class="form-control" id="nombre" name="nombre" readonly="">
            <div class="col-md-4 form-group">
                <label class="control-label">Identificación</label>
                <input type="text" class="form-control" id="ident" name="ident" readonly="" value="{{old('identificacion')}}" maxlength="20">
                <span class="help-block error">
                    <strong>{{ $errors->first('identificacion') }}</strong>
                </span>
            </div>
            <div class="col-md-4 form-group">
                <label class="control-label">N° Telefónico <span class="text-danger">*</span></label>
                <input type="text" class="form-control"  id="telefono" name="telefono" value="{{old('telefono')}}" maxlength="30" required>
                <span class="help-block error">
                    <strong>{{ $errors->first('telefono') }}</strong>
                </span>
            </div>
            <div class="col-md-4 form-group">
                <label class="control-label">Correo Electrónico</label>
                <input type="text" class="form-control"  id="correo" name="correo" value="{{old('correo')}}" maxlength="200">
                <span class="help-block error">
                    <strong>{{ $errors->first('correo') }}</strong>
                </span>
            </div>

            <div class="col-md-4 form-group contract">
                <label class="control-label">N° Contrato</label>
                <input type="text" class="form-control"  id="contrato" name="contrato" readonly="" value="{{old('contrato')}}" maxlength="200">
                <span class="help-block error">
                    <strong>{{ $errors->first('contrato') }}</strong>
                </span>
            </div>
            <div class="col-md-4 form-group d-none">
                <label class="control-label">Plan Contratado</label>
                <input type="text" class="form-control" id="plan" name="plan" readonly="" value="{{old('identificacion')}}" maxlength="20">
                <span class="help-block error">
                    <strong>{{ $errors->first('plan') }}</strong>
                </span>
            </div>
            <div class="col-md-4 form-group contract">
                <label class="control-label">Dirección IP</label>
                <input type="text" class="form-control" id="ip" name="ip" readonly="" value="{{old('ip')}}">
                <span class="help-block error">
                    <strong>{{ $errors->first('ip') }}</strong>
                </span>
            </div>
            <div class="col-md-4 form-group contract">
                <label class="control-label">Dirección MAC</label>
                <input type="text" class="form-control" id="mac_address" name="mac_address" readonly="" value="{{old('mac_address')}}">
                <span class="help-block error">
                    <strong>{{ $errors->first('mac_address') }}</strong>
                </span>
            </div>

            <div class="col-md-9 form-group">
                <label class="control-label">Dirección <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="direccion" name="direccion" value="{{old('direccion')}}" maxlength="200" required>
                <span class="help-block error">
                    <strong>{{ $errors->first('direccion') }}</strong>
                </span>
            </div>
            <div class="col-md-3 form-group">
                <label class="control-label">Barrio</label>
                <input type="text" class="form-control" id="barrio" name="barrio" value="{{old('barrio')}}" maxlength="200">
                <span class="help-block error">
                    <strong>{{ $errors->first('barrio') }}</strong>
                </span>
            </div>
            <div class="col-md-3 form-group">
                <label class="control-label">Fecha</label>
                <input type="text" class="form-control datepicker"  id="fecha" name="fecha" required="" value="{{date('d-m-Y')}}" >
                <span class="help-block error">
                    <strong>{{ $errors->first('fecha') }}</strong>
                </span>
            </div>
            <div class="col-md-3 form-group">
                <label class="control-label">Tipo de Servicio <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" name="servicio" id="servicio" required="" title="Seleccione">
                    @foreach($servicios as $servicio)
                    <option {{old('servicio')==$servicio->id?'selected':''}} value="{{$servicio->id}}">{{$servicio->nombre}}</option>
                    @endforeach
                </select>
                <span class="help-block error">
                    <strong>{{ $errors->first('servicio') }}</strong>
                </span>
            </div>
            <div class="col-md-3 form-group" id="div_valor" style="display:none;">
                <label class="control-label">Valor de la Instalación <span class="text-danger">*</span></label>
                <input type="number" class="form-control" name="valor" id=" ">
            </div>
            <div class="col-md-3 form-group" id="div_plan" style="display:none;">
                <label class="control-label">Elija Plan <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="plan" id=" ">
            </div>
            <div class="col-md-3 form-group">
                <label class="control-label">¿Escalar Caso? <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" name="estatus" id="estatus" required="" title="Seleccione" onchange="searchDV(this.value)">
                    <option value="0" selected>No</option>
                    <option value="2">Si</option>
                </select>
            </div>
            <div class="col-md-3 form-group" id="div_tecnico" style="display:none;">
                <label class="control-label">Técnico Asociado <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" name="tecnico" id="tecnico" title="Seleccione">
                    @foreach($tecnicos as $tecnico)
                    <option {{old('tecnico')==$tecnico->id?'selected':''}} value="{{$tecnico->id}}">{{$tecnico->nombres}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 form-group">
                <label class="control-label">Prioridad <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" name="prioridad" id="prioridad" required="" title="Seleccione">
                    <option value="1">Baja</option>
                    <option value="2">Media</option>
                    <option value="3">Alta</option>
                </select>
                <span class="help-block error">
                    <strong>{{ $errors->first('prioridad') }}</strong>
                </span>
            </div>
            @if(Auth::user()->empresa()->oficina)
            <div class="form-group col-md-3">
                <label class="control-label">Oficina Asociada <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" name="oficina" id="oficina" required="" title="Seleccione" data-live-search="true" data-size="5">
                    @foreach($oficinas as $oficina)
                      <option value="{{$oficina->id}}" {{ $oficina->id == auth()->user()->oficina ? 'selected' : '' }}>{{$oficina->nombre}}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-12 form-group">
                <label class="control-label">Observaciones</label>
                <textarea  class="form-control form-control-sm min_max_100" id="desconocido" required="" name="desconocido"></textarea>
                <span class="help-block error">
                    <strong>{{ $errors->first('desconocido') }}</strong>
                </span>
            </div>
        </div>
        <small>Los campos marcados con son obligatorios</small>
        <hr>
        <div class="row">
            <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
                <a href="{{route('radicados.index')}}" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
    <script type="text/javascript">
        @if($cliente)
            busqueda_detalles({{$cliente}});
        @endif
        document.addEventListener('DOMContentLoaded', obtenerValorSeleccionado);
        function obtenerValorSeleccionado() {
            var select = document.getElementById('servicio');
            var valorSeleccionado = select.value;

            if (valorSeleccionado === 2) {
                var elemento = document.getElementById('div_valor');
                elemento.style.display = 'block';

                var elemento = document.getElementById('div_plan');
                elemento.style.display = 'block';
                // Puedes realizar otras acciones aquí
            }

            // Mostrar el valor en una alerta
            // alert('Valor seleccionado al cargar la página: ' + valorSeleccionado);
        }

            // Compara el valor seleccionado con un valor específico

        function busqueda_detalles(cliente, contrato = null){

            if(contrato==null){
                if (window.location.pathname.split("/")[1] === "software") {
                var url='/software/api/getDetails/'+cliente;
                }else{
                    var url = '/api/getDetails/'+cliente;
                }
            }else{
                if (window.location.pathname.split("/")[1] === "software") {
                var url='/software/api/getDetails/'+cliente+'/'+contrato;
                }else{
                    var url = '/api/getDetails/'+cliente+'/'+contrato;;
                }
            }


            $.ajax({
                url: url,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                method: 'get',
                beforeSend: function(){
                    cargando(true);
                },
                success: function(data){
                    $("#content").removeAttr('style');
                    $("#id_cliente").val('').val(data.cliente.id);
                    $("#ident").val('').val(data.cliente.nit);
                    $("#telefono").val('').val(data.cliente.celular);
                    $("#correo").val('').val(data.cliente.email);
                    $("#direccion").val('').val(data.cliente.direccion);
                    $("#barrio").val('').val(data.cliente.barrio);
                    $("#nombre").val('').val(data.cliente.nombre);

                    if(data.cliente.apellido1){
                        $("#nombre").val('').val(data.cliente.nombre+' '+data.cliente.apellido1);
                    }
                    if(data.cliente.apellido2){
                        $("#nombre").val('').val(data.cliente.nombre+' '+data.cliente.apellido1+' '+data.cliente.apellido2);
                    }

                    if(data.contrato){
                        $(".contract").removeClass('d-none');
                        $("#contrato").val('').val(data.contrato.nro);
                        $("#ip").val('').val(data.contrato.ip);
                        $("#mac_address").val('').val(data.contrato.mac_address);
                        $('#servicio').find('[value=2]').prop('disabled', false);
                        $('#servicio').find('[value=5]').prop('disabled', false);
                        $('#servicio').find('[value=6]').prop('disabled', false);
                        $('#servicio').find('[value=7]').prop('disabled', false);
                        $("#servicio").selectpicker('val', '');
                    }else{
                        $(".contract").addClass('d-none');
                        $("#contrato").val('');
                        $("#ip").val('');
                        $("#mac_address").val('');

                        $('#servicio').find('[value=2]').prop('disabled', true);
                        $('#servicio').find('[value=5]').prop('disabled', true);
                        $('#servicio').find('[value=6]').prop('disabled', true);
                        $('#servicio').find('[value=7]').prop('disabled', true);
                        $("#servicio").selectpicker('val', '4');
                    }

                    $('#list-contratos').html('');

                    if(data.contratos.length > 0){
                        data.contratos.forEach(c => {
                            $('#list-contratos').append(`
                                <li class="list-group-item" style="padding: 7px; ${(data.contrato.id == c.id) ? 'color:green;' : ''}" onclick="busqueda_detalles(${c.client_id}, ${c.id})">(contrato: ${c.nro}) IP: ${c.ip} ${c.address_street ?? ''}</li>
                            `);

                            if((data.contrato.id == c.id)){
                                if(c.address_street){
                                    $("#direccion").val('').val(c.address_street);
                                }
                                if(c.address_number){
                                    $("#telefono").val('').val(c.address_number);
                                }
                            }

                        });

                    }

                },
                error: function(data){
                    Swal.fire({
                        position: 'center',
                        type: 'error',
                        title: 'Disculpe, estamos presentando problemas al tratar de enviar el formulario, intentelo mas tarde',
                        showConfirmButton: false,
                        timer: 2500
                    });
                }
            });
            cargando(false);
        }

        function searchDV(id){
            option = id;
            if (option == 2) {
                document.getElementById("div_tecnico").style.display = "block";
            }else{
                document.getElementById("div_tecnico").style.display = "none";
                $("#tecnico").val('').selectpicker('refresh');
            }
        }

        function serviceDV(id){
            if (id == 4) {
                document.getElementById("div_valor").style.display = "block";
                $("#valor").val('').prop('required', true);
            }else{
                document.getElementById("div_valor").style.display = "none";
                $("#valor").val('').prop('required', false);
            }
        }
    </script>
@endsection

