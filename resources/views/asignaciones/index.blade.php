@extends('layouts.app')

@section('boton')
    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
	       <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
	    </div>
	@else
	    @if(isset($_SESSION['permisos']['5']))
		    <a href="{{route('contactos.create')}}" class="btn btn-outline-info btn-sm"><i class="fas fa-plus"></i> Nuevo Cliente</a>
	    @endif
	    @if(isset($_SESSION['permisos']['411']))
	        <a href="{{route('contratos.create')}}" class="btn btn-outline-info btn-sm"><i class="fas fa-plus"></i> Nuevo Contrato</a>
	    @endif
	    @if(isset($_SESSION['permisos']['202']))
	        <a href="{{route('radicados.create')}}" class="btn btn-outline-info btn-sm"><i class="fas fa-plus"></i> Nuevo Radicado</a>
	    @endif
	    @if(isset($_SESSION['permisos']['402']))
		<a href="{{route('asignaciones.create')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nueva Asignación</a>
		@endif
	@endif
@endsection

@section('content')
	@if(Session::has('success'))
		<div class="alert alert-success" style="margin-left: 2%;margin-right: 2%;">
			{{Session::get('success')}}
		</div>
	@endif
	@if(Session::has('danger'))
		<div class="alert alert-danger" style="margin-left: 2%;margin-right: 2%;">
			{{Session::get('danger')}}
		</div>
	@endif
	@if(Session::has('message_denied'))
	    <div class="alert alert-danger" role="alert">
	    	{{Session::get('message_denied')}}
	    	@if(Session::get('errorReason'))<br> <strong>Razon(es): <br></strong>
	    	@if(count(Session::get('errorReason')) > 0)
	    	    @php $cont = 0 @endphp
	    	    @foreach(Session::get('errorReason') as $error)
	    	        @php $cont = $cont + 1; @endphp
	    	        {{$cont}} - {{$error}} <br>
	    	    @endforeach
	    	@else
	    	    {{ Session::get('errorReason') }}
	    	@endif
	    	@endif
	    	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
	    		<span aria-hidden="true">&times;</span>
	    	</button>
	    </div>
	@endif

	@if(isset($_SESSION['permisos']['410']))
	    <div class="row">

	    </div>

		<div class="row card-description">
			<div class="col-md-12 text-right mb-2 d-none">
	    		@if(isset($_SESSION['permisos']['751']))
	    		<a href="javascript:editCampos();" class="btn btn-info btn-sm"><i class="fas fa-edit"></i> Editar Configuración</a>
	    		@endif
	    	</div>
	    	<div class="col-md-12">
				<table class="table table-striped table-hover" id="example">
					<thead class="thead-dark">
						<tr>
							<th>Cliente</th>
							<th>Cédula</th>
							<th>Fecha de Firma</th>
							<th>Estado</th>
							<th class="text-center">Acciones</th>
			            </tr>
					</thead>
					<tbody>
						@foreach($contratos as $contrato)
							<tr>
								<td><a href="{{ route('contactos.show',$contrato->id )}}"  title="Ver">{{ $contrato->nombre }} {{ $contrato->apellido1 }} {{ $contrato->apellido2 }}</a></td>
								<td>{{ $contrato->nit }}</td>
								<td class="font-weight-bold text-{{ $contrato->asignacion('firma', true) }}">{{ $contrato->asignacion('firma', false) }}</td>
								<td>{{date('d-m-Y', strtotime($contrato->fecha_isp))}}</td>
								<td class="text-center">
									@if(auth()->user()->modo_lectura())
									@else
                                    @if(isset($_SESSION['permisos']['850']))
                                    <form action="{{ route('asignaciones.destroy', $contrato->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar{{$contrato->id}}">
                                        @csrf
                                        <input name="_method" type="hidden" value="DELETE">
                                    </form>
                                    @endif
									<a href="{{ route('contactos.show',$contrato->id )}}" class="btn btn-outline-info btn-icons" title="Ver Detalle"><i class="far fa-eye"></i></i></a>
									@if(isset($_SESSION['permisos']['817']))
									<a href="{{ route('asignaciones.imprimir',$contrato->id )}}" class="btn btn-outline-danger btn-icons" title="Imprimir Contrato Digital" target="_blank"><i class="fas fa-print"></i></a>
									@endif
									@if(isset($_SESSION['permisos']['818']))
									<a href="{{ route('asignaciones.enviar',$contrato->id )}}" onclick="cargando('true');" class="btn btn-outline-success btn-icons" title="Enviar Contrato Digital"><i class="fas fa-envelope"></i></a>
									@endif
									@if(isset($_SESSION['permisos']['844']))
									<a href="javascript:void(0);" onclick="generar_link({{ $contrato->id }});" class="btn btn-outline-warning btn-icons" title="Generar Link de Actualización de Firma"><i class="fas fa-fw fa-link"></i></a>
									@endif
									@if(isset($_SESSION['permisos']['846']))
									<a href="{{ route('asignaciones.edit',$contrato->id )}}" class="btn btn-outline-primary btn-icons" title="Cargar Documentos"><i class="fas fa-fw fa-upload"></i></a>
									@endif
                                    @if(isset($_SESSION['permisos']['850']))
                                    <button type="button" class="btn btn-outline-danger btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar{{$contrato->id}}', '¿Está seguro que desear eliminar esta asignación de contrato?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
                                    @endif
									@endif
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>

		<script>
		    function resetForm(){
		    	$("#name_1,#name_2,#name_3,#name_4,#name_5,#name_6").val('').selectpicker('refresh');
		    }
	    </script>
    @endif

    <div class="modal fade" id="modal_conf"  tabindex="-1" role="dialog">
        <div class="modal-dialog" style="max-width: 50%;">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Editar Campos Adjuntos</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="{{ route('asignaciones.campos_asignacion') }}" style="padding: 2% 3%;" role="form" class="forms-sample"  id="forma" >
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-6 offset-md-3">
                                <label class="control-label">Campo Principal</label>
                                <input type="text" class="form-control" name="campo_1" id="campo_1">
                                <span class="help-block error">
                                    <strong>{{ $errors->first('campo_1') }}</strong>
                                </span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-3">
                                <label class="control-label">Campo A</label>
                                <input type="text" class="form-control" name="campo_a" id="campo_a">
                                <span class="help-block error">
                                    <strong>{{ $errors->first('campo_a') }}</strong>
                                </span>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="control-label">Campo B</label>
                                <input type="text" class="form-control" name="campo_b" id="campo_b">
                                <span class="help-block error">
                                    <strong>{{ $errors->first('campo_b') }}</strong>
                                </span>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="control-label">Campo C</label>
                                <input type="text" class="form-control" name="campo_c" id="campo_c">
                                <span class="help-block error">
                                    <strong>{{ $errors->first('campo_c') }}</strong>
                                </span>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="control-label">Campo D</label>
                                <input type="text" class="form-control" name="campo_d" id="campo_d">
                                <span class="help-block error">
                                    <strong>{{ $errors->first('campo_d') }}</strong>
                                </span>
                            </div>

                            <div class="form-group col-md-3">
                                <label class="control-label">Campo E</label>
                                <input type="text" class="form-control" name="campo_e" id="campo_e">
                                <span class="help-block error">
                                    <strong>{{ $errors->first('campo_e') }}</strong>
                                </span>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="control-label">Campo F</label>
                                <input type="text" class="form-control" name="campo_f" id="campo_f">
                                <span class="help-block error">
                                    <strong>{{ $errors->first('campo_f') }}</strong>
                                </span>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="control-label">Campo G</label>
                                <input type="text" class="form-control" name="campo_g" id="campo_g">
                                <span class="help-block error">
                                    <strong>{{ $errors->first('campo_g') }}</strong>
                                </span>
                            </div>
                            <div class="form-group col-md-3">
                                <label class="control-label">Campo H</label>
                                <input type="text" class="form-control" name="campo_h" id="campo_h">
                                <span class="help-block error">
                                    <strong>{{ $errors->first('campo_h') }}</strong>
                                </span>
                            </div>
                            <div class="form-group col-md-12">
                                <label class="control-label">Contrato Digital</label>
                                <textarea class="form-control" name="contrato_digital" id="contrato_digital" rows="6" maxlength="2050"></textarea>
                                <span class="help-block error">
                                    <strong>{{ $errors->first('contrato_digital') }}</strong>
                                </span>
                            </div>
                        </div>
                        <div class="row" >
                            <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
                                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal" id="cancelar">Cancelar</button>
                                <a href="javascript:void(0);" class="btn btn-success" id="guardar">Guardar</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="//cdn.rawgit.com/zenorocha/clipboard.js/v1.5.3/dist/clipboard.min.js"></script>
    <script>
        $(document).ready(function () {
            $("#guardar").click(function (form) {
                $.post($("#forma").attr('action'), $("#forma").serialize(), function (data) {
                    console.log(data);
                    if(data.success == true){
                        $('#cancelar').click();
                        $('#forma').trigger("reset");
                        swal("Configuración Almacenada", "", "success");
                        $("#div_campo_a").text('').text(data.campo_a);
                        $("#div_campo_b").text('').text(data.campo_b);
                        $("#div_campo_c").text('').text(data.campo_c);
                        $("#div_campo_d").text('').text(data.campo_d);
                        $("#div_campo_e").text('').text(data.campo_e);
                        $("#div_campo_f").text('').text(data.campo_f);
                        $("#div_campo_g").text('').text(data.campo_g);
                        $("#div_campo_h").text('').text(data.campo_h);
                        $("#div_campo_1").text('').html(data.campo_1+' <span class="text-danger">*</span>');
                        $("#div_contrato_digital").text('').html(data.contrato_digital);
                    } else {
                        swal('ERROR', 'Intente nuevamente', "error");
                    }
                }, 'json');
            });
        });

        function editCampos(){
            var url = 'asignaciones/config_campos_asignacion';
            $.get(url,function(data){
                data = JSON. parse(data);
                $("#campo_a").val(data.campo_a);
                $("#campo_b").val(data.campo_b);
                $("#campo_c").val(data.campo_c);
                $("#campo_d").val(data.campo_d);
                $("#campo_e").val(data.campo_e);
                $("#campo_f").val(data.campo_f);
                $("#campo_g").val(data.campo_g);
                $("#campo_h").val(data.campo_h);
                $("#campo_1").val(data.campo_1);
                $("#contrato_digital").val(data.contrato_digital);
            });
            $('#modal_conf').modal("show");
        }

    	function generar_link(id) {
    		var clipboard = new Clipboard('.btn');
    		cargando(true);
    		if (window.location.pathname.split("/")[1] === "software") {
    			var url = `/software/empresa/asignaciones/`+id+`/generar_link`;
    		}else{
    			var url = `/empresa/asignaciones/`+id+`/generar_link`;
    		}

    		$.ajax({
    			url: url,
    			method: 'GET',
    			headers: {
    				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    			},
    			success: function(data) {
    				cargando(false);
    				swal({
    					title: 'LINK DE ACTUALIZACIÓN DE FIRMA',
    					html: data.text,
    					type: data.type,
    					showConfirmButton: false,
    					confirmButtonColor: '#1A59A1',
    					confirmButtonText: 'ACEPTAR',
    				});
    			}
    		});
    	}
    </script>
@endsection
