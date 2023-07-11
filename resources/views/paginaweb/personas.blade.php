@extends('layouts.app')
@section('boton')	
{{--<a href="{{route('logistica.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Envio</a>--}}

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
<div class="row card-description">
	<div class="col-md-12">
		<table class="table table-striped table-hover" id="example">
			<thead class="thead-dark">
				<tr>
					<th>nro</th>
					<th>Nombre</th>
					<th>Apellido</th>
					<th>Email</th>
					<th>Telefono</th>
					<th>username</th>
					<th>fecha Registro</th>
					<th>estado</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				@foreach ($personas as $persona)
				<tr>
					<td>{{$persona->id}}</td>
					<td>{{$persona->nombre}}</td>
					<td>{{$persona->apellido}}</td>
					<td>{{$persona->email}}</td>
					<td>{{$persona->telefono}}</td>
					<td>{{$persona->username}}</td>
					<td>{{$persona->fecha_registro}}</td>
					<td>
						@if($persona->user_status == 1)
						<strong style="color:#4dc326;">Activo</strong>
						@endif
					</td>
					
					<td><a href="#" type="button" class="btn btn-success">Detalle</a></td>
				</tr>
				@endforeach

			</tbody>
		</table>
	</div>
</div>
@endsection