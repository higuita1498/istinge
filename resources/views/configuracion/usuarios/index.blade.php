@extends('layouts.app')
@section('boton')
@if(Auth::user()->rol >= 2)
    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
	       <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
	    </div>
	@else
    @if(Auth::user()->rol >= 2 && $recarga == 0 )
		<a href="{{route('usuarios.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Usuario</a>
	@endif
	@if($recarga == 1 )
	    @if(isset($_SESSION['permisos']['426']))
		<a href="{{route('reportes.recargas')}}" class="btn btn-success btn-sm" ><i class="fas fa-file-excel"></i>Ver Reporte de Recargas</a>
		@endif
	@endif
	@endif
@endif
@endsection

@section('content')
	@if(Session::has('success'))
		<div class="alert alert-success">
			{{Session::get('success')}}
		</div>

		<script type="text/javascript">
			setTimeout(function(){
			    $('.alert').hide();
			    $('.active_table').attr('class', ' ');
			}, 5000);
		</script>
	@endif

	<div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
		<button type="button" class="close" data-dismiss="alert">x</button>
		<strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
	</div>

	<div class="row card-description">
		<div class="col-md-12 fact-table">
			<div class="table-responsive">
				<table class="table table-striped table-hover">
					<thead class="thead-dark text-center">
						<tr>
			              <th >Nombre y Apellido</th>
			              <th >Correo electrónico</th>
			              <th >Nombre de Usuario</th>
			              @if($recarga == 0)
			              <th >Perfil</th>
			              @if(Auth::user()->rol > 1)
			              <th >Estado</th>
			              @endif
			              @endif
			              @if($recarga == 1)
			              <th >Saldo</th>
			              @endif
			              @if(Auth::user()->rol == 1)
			              <th>Empresa</th>
			              @endif
			              <th >Acciones</th>
			          </tr>
					</thead>
					<tbody class="text-center">
						@foreach($usuarios as $usuario)
							<tr @if($usuario->id==Session::get('usuario_id')) class="active_table" @endif>
								<td>{{$usuario->nombres}}</td>
								<td>{{$usuario->email}}</td>
								<td>{{$usuario->username}}</td>
								@if($recarga == 0)
								<td class="font-weight-bold {{$usuario->rol(true)}}">{{$usuario->rol()}}</td>
								@if(Auth::user()->rol > 1)
								<td class="{{$usuario->estatus(true)}}">{{$usuario->estatus()}}</td>
								@endif
								@endif
								@if($recarga == 1)
								<td class="font-weight-bold text-{{($usuario->saldo == 0)?'danger':'success'}}">{{Auth::user()->empresa()->moneda}} {{ App\Funcion::Parsear($usuario->saldo) }}</td>
								@endif
								@if(Auth::user()->rol == 1)
								<td>{{$usuario->empresa()?$usuario->empresa()->nombre:'MASTER'}}</td>
								@endif
								<td class="text-center">
								    @if(Auth::user()->rol > 1 && $recarga == 0)
									<a href="{{route('usuarios.show',$usuario->id)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>
									@endif
									@if(Auth::user()->rol >= 2 && $recarga == 0)
									    @if(isset($_SESSION['permisos']['765']) || Auth::user()->rol == 3)
									    <a href="{{route('usuarios.edit',$usuario->id)}}" class="btn btn-outline-primary btn-icons"><i class="fas fa-edit"></i></a>
									    @endif
									    @if(Auth::user()->id!=$usuario->id)
									        <form action="{{ route('usuarios.act_desc',$usuario->id) }}" method="POST" class="delete_form" style="margin:  0;display: inline-block;" id="act_desc-usuario{{$usuario->id}}">
									            {{ csrf_field() }}
									        </form>
									        @if($usuario->user_status==1)
									            @if($usuario->usado() == 0)
									                @if(isset($_SESSION['permisos']['766']) || Auth::user()->rol == 3)
				    						        <button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-usuario{{$usuario->id}}', '¿Estas seguro que deseas eliminar el usuario?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
				    						        @endif
				    						    @endif
				    						    @if(isset($_SESSION['permisos']['767']) || Auth::user()->rol == 3)
											    <button class="btn btn-outline-secondary  btn-icons" type="submit" title="Desactivar" onclick="confirmar('act_desc-usuario{{$usuario->id}}', '¿Estas seguro que deseas desactivar este usuario?');"><i class="fas fa-power-off"></i></button>
											    @endif
											    @if($usuario->usado() == 0)
											        <form action="{{ route('usuarios.destroy',$usuario->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-usuario{{$usuario->id}}">
				        						        {{ csrf_field() }}
												        <input name="_method" type="hidden" value="DELETE">
				    						        </form>
				    						    @endif
					                	    @else
					                	        @if(isset($_SESSION['permisos']['767']) || Auth::user()->rol == 3)
					                  	        <button class="btn btn-outline-success  btn-icons" type="submit" title="Activar" onclick="confirmar('act_desc-usuario{{$usuario->id}}', '¿Estas seguro que deseas activar este usuario?');"><i class="fas fa-power-off"></i></button>
					                  	        @endif
					                	    @endif
					                    @endif
					                @endif
					                @if($recarga == 0)
					                    @if(Auth::user()->rol > 1)
					                        @if(isset($_SESSION['permisos']['768']) || Auth::user()->rol == 3)
					                        <button class="btn btn-outline-success  btn-icons permisos" idUsuario="{{$usuario->id}}" title="Permisos del Sistema"><i class="fas fa-lock"></i></button>
					                        @endif
					                        @if(Auth::user()->rol == 3)
					                        <a title="Ingresar" class="btn btn-outline-danger btn-icons" href="{{route('usuario.ingresarR', $usuario->email)}}"><i class="fas fa-sign-in-alt"></i></a>
					                        @endif
					                    @endif
					                @if(Auth::user()->rol == 1)
					                    <a title="Ingresar" class="btn btn-outline-danger btn-icons" href="{{route('usuario.ingresar', $usuario->email)}}"><i class="fas fa-sign-in-alt"></i></a>
					                @endif
					                @endif
					                @if(isset($_SESSION['permisos']['426']))
					                    @if($recarga == 1)
					                        <button class="btn btn-outline-info btn-icons saldo" idUser="{{$usuario->id}}" title="Recarga de Saldo"><i class="fas fa-dollar-sign"></i></button>
					                    @endif
					                @endif
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="modal fade" id="modal"  tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="modal-title"></h4>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>
				<div class="modal-body" id="modal-body">
				    
				</div>
			</div>
		</div>
	</div>
@endsection

@section('scripts')
	<script>
		$(document).ready(function () {
	        $('.permisos').click(function(){
	            var url = 'permisosUsuario';
	            var _token =   $('meta[name="csrf-token"]').attr('content');
	            $("#modal-title").html($(this).attr('title'));
	            $.post(url,{ idUsuario : $(this).attr('idUsuario'), _token : _token },function(resul){
	                $("#modal-body").html(resul);
	            });
	            $('#modal').modal("show");
	        });

	        $('.saldo').click(function(){
	            $('#form-recarga').trigger("reset");
	            var url = 'saldoUsuario';
	            var _token =   $('meta[name="csrf-token"]').attr('content');
	            $("#modal-title").html($(this).attr('title'));
	            $.post(url,{ id : $(this).attr('idUser'), _token : _token },function(resul){
	                $("#modal-body").html(resul);
	            });
	            $('#modal').modal("show");
	        });
	    });
	</script>
@endsection