@extends('layouts.app')

@section('style')
    <style>

    </style>
@endsection

@section('boton')
    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
	       <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
	    </div>
	@else
	    <a href="javascript:abrirAcciones()" class="btn btn-dark btn-sm my-1" id="boton-acciones">Acciones de Oficina&nbsp;&nbsp;<i class="fas fa-caret-down"></i></a>
	@endif
@endsection

@section('content')
	<div class="container-fluid d-none" id="form-acciones">
		<fieldset>
			<legend>Acciones de Oficina</legend>
			<div class="card shadow-sm border-0">
				<div class="card-body pb-3 pt-2" style="background: #f9f9f9;">
					<div class="row">
						<div class="col-md-12 text-center">
						    <form action="{{route('oficinas.status',$oficina->id)}}" method="get" class="delete_form" style="margin:  0;display: inline-block;" id="status-{{$oficina->id}}">
						    	@csrf
						    </form>
						    <form action="{{ route('oficinas.destroy',$oficina->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-{{$oficina->id}}">
						    	@csrf
						    	<input name="_method" type="hidden" value="DELETE">
						    </form>

						    @if(isset($_SESSION['permisos']['811']))
						    <a title="Editar" href="{{route('oficinas.edit',$oficina->id)}}" class="btn btn-outline-primary btn-sm"><i class="fas fa-edit"></i> Editar</a>
						    @endif
						    @if(isset($_SESSION['permisos']['813']))
						        @if($oficina->status == 0)
						        <button title="Habilitar oficina" class="btn btn-outline-success btn-sm" type="submit" onclick="confirmar('status-{{$oficina->id}}', '¿Está seguro que desea habilitar la oficina {{$oficina->nombre}}?', '');"><i class="fas fa-power-off"></i> Habilitar</button>
						        @else
						        <button title="Deshabilitar oficina" class="btn btn-outline-danger btn-sm" type="submit" onclick="confirmar('status-{{$oficina->id}}', '¿Está seguro que desea deshabilitar la oficina {{$oficina->nombre}}?', '');"><i class="fas fa-power-off"></i> Deshabilitar</button>
						        @endif
						    @endif
						    @if(isset($_SESSION['permisos']['812']))
						        @if($oficina->uso == 0)
						        <button class="btn btn-outline-danger btn-sm" type="submit" title="Eliminar" onclick="confirmar('eliminar-{{$oficina->id}}', '¿Está seguro que deseas eliminar la oficina {{$oficina->nombre}}?', 'Se borrará de forma permanente');"><i class="fas fa-times"></i> Eliminar</button>
						        @endif
						    @endif
						</div>
					</div>
				</div>
			</div>
		</fieldset>
	</div>

	<div class="row card-description">
		<div class="col-md-12">
			<div class="table-responsive">
				<table class="table table-striped table-bordered table-sm info">
					<tbody>
						<tr>
							<th width="15%">DATOS GENERALES</th>
							<th></th>
						</tr>
						<tr>
							<th>Nombre</th>
							<td>{{ $oficina->nombre }}</td>
						</tr>
						<tr>
							<th>Teléfono</th>
							<td>{{ $oficina->telefono }}</td>
						</tr>
						<tr>
							<th>Dirección</th>
							<td>{{ $oficina->direccion }}</td>
						</tr>
						@if($oficina->created_by)
						<tr>
							<th>Registrado por</th>
							<td>{{ $oficina->created_by()->nombres }}</td>
						</tr>
						@endif
						@if($oficina->updated_by)
						<tr>
							<th>Actualizado por</th>
							<td>{{ $oficina->updated_by()->nombres }}</td>
						</tr>
						@endif
					</tbody>
				</table>
			</div>
		</div>
	</div>
@endsection

@section('scripts')
	<script>
		function abrirAcciones() {
			if ($('#form-acciones').hasClass('d-none')) {
				$('#boton-acciones').html('Acciones de Oficina&nbsp;&nbsp;<i class="fas fa-caret-up"></i>');
				$('#form-acciones').removeClass('d-none');
			} else {
				$('#boton-acciones').html('Acciones de Oficina&nbsp;&nbsp;<i class="fas fa-caret-down"></i>');
				cerrarFiltrador();
			}
		}

		function cerrarFiltrador() {
			$('#form-acciones').addClass('d-none');
			$('#boton-acciones').html('Acciones de Oficina&nbsp;&nbsp;<i class="fas fa-caret-down"></i>');
		}
	</script>
@endsection