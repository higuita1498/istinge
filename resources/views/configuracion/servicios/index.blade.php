@extends('layouts.app')
@section('boton')
    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">Integra Colombia: Suscripción Vencida</h4>
	       <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
	    </div>
	@else
	<a href="{{route('servicio.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Servicio</a>
	@endif
@endsection
@section('content')
	@if(Session::has('success'))
		<div class="alert alert-success" >
			{{Session::get('success')}}
		</div>

		<script type="text/javascript">
			setTimeout(function(){
			    $('.alert').hide();
			    $('.active_table').attr('class', ' ');
			}, 5000);
		</script>
	@endif

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
	<div class="row card-description">
		<div class="col-md-12">
			<table class="table table-striped table-hover" id="example">
			<thead class="thead-dark">
				<tr>
	              <th>Nombre</th>
	              <th>Tiempo</th>
	              <th>Estatus</th>
	              <th>Acciones</th>
	          </tr>
			</thead>
			<tbody>
				@foreach($servicios as $servicio)
					<tr @if($servicio->id==Session::get('servicio_id')) class="active_table" @endif>
						<td>{{$servicio->nombre}}</td>
						<td>{{$servicio->tiempo}} minutos</td>
						<td>{{$servicio->estatus()}}</td>
						<td>
							@if(auth()->user()->modo_lectura())
							@else
							<a href="{{route('servicio.edit',$servicio->id)}}" class="btn btn-outline-primary btn-icons"><i class="fas fa-edit"></i></a>
							@if($servicio->usado()==0)
								<form action="{{ route('servicio.destroy',$servicio->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-servicio">
        						{{ csrf_field() }}
								<input name="_method" type="hidden" value="DELETE">
    						</form>
    						<button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-servicio', '¿Estas seguro que deseas eliminar el servicio?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
							@endif
							@endif
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
		</div>
	</div>
@endsection