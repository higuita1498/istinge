@extends('layouts.app')

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
	              <th>Estado</th>
	              <th>Acciones</th>
	          </tr>
			</thead>
			<tbody>
				@foreach($servicios as $servicio)
				<tr @if($servicio->id==Session::get('id')) class="active_table" @endif >
					<td>{{$servicio->nombre}}</td>
					<td class="font-weight-bold text-{{$servicio->status('true')}}">{{$servicio->status()}}</td>
					<td>
						<form action="{{ route('integracion-sms.act_desc',$servicio->id) }}" method="POST" class="delete_form" style="margin:  0;display: inline-block;" id="act_desc{{$servicio->id}}">
							@csrf
		                </form>
						<a href="{{route('integracion-sms.show',$servicio->id)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>
						<a href="{{route('integracion-sms.edit',$servicio->id)}}" class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>

	                 	@if($servicio->status==1)
		                  <button class="btn btn-outline-danger btn-icons" type="submit" title="Desactivar" onclick="confirmar('act_desc{{$servicio->id}}', '¿Está seguro que desea desactivar este servicio?', '');"><i class="fas fa-power-off"></i></button>
		                @else
		                  <button class="btn btn-outline-success btn-icons" type="submit" title="Activar" onclick="confirmar('act_desc{{$servicio->id}}', '¿Está seguro que desea activar este servicio?', '');"><i class="fas fa-power-off"></i></button>
		                @endif
		                @if($servicio->api_key && $servicio->user && $servicio->pass)
		                    <a href="{{route('integracion-sms.envio_prueba',$servicio->id)}}" class="btn btn-outline-success btn-icons" title="Envío de SMS Prueba"><i class="far fa-comment-dots"></i></a>
		                @endif
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
		</div>
	</div>
@endsection