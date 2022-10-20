@extends('layouts.app')

@section('boton')
    @if(Auth::user()->modo_lectura())
		<div class="alert alert-warning alert-dismissible fade show" role="alert">
			<a>Esta en Modo Lectura si desea seguir disfrutando de Nuestros Servicios Cancelar Alguno de Nuestros Planes <a class="text-black" href="{{route('PlanesPagina.index')}}"> <b>Click Aqui.</b></a></a>
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
	@else
		<a href="{{route('mensajeria.create')}}" class="btn btn-primary btn-sm disabled" disabled><i class="fas fa-plus"></i> Nuevo Mensaje</a>
		<a href="{{route('mensajeria.enviar')}}" class="btn btn-success btn-sm disabled" disabled><i class="fas fa-envelope-open-text"></i> Enviar Mensaje Prueba</a>
    @endif
@endsection

@section('content')
	@if(Session::has('success'))
		<div class="alert alert-success" >
			{{Session::get('success')}}
		</div>
	@endif
	
	@if(Session::has('danger'))
		<div class="alert alert-danger" >
			{{Session::get('danger')}}
		</div>
	@endif
	
	<div class="row card-description">
		<div class="col-md-12">
			<table class="table table-striped table-hover" id="example">
			<thead class="thead-dark">
				<tr>
	              <th>Nro</th>
	              <th>Contenido</th>
	              <th>Tipo</th>
	              <th>Fecha</th>
	              <th>Hora</th>
	              <th>Acciones</th>
	          </tr>
			</thead>
			<tbody>
				@foreach($mensajes as $mensaje)
					<tr @if($mensaje->id==Session::get('id')) class="active_table" @endif>
						<td><a href="{{route('mensajeria.show',$mensaje->id)}}">{{$mensaje->id}}</a></td>
						<td>{{$mensaje->contenido}}</td>
						<td>{{$mensaje->tipo}}</td>
						<td>{{$mensaje->fecha}}</td>
						<td>{{$mensaje->hora}}</td>
						<td>
							<a href="{{route('mensajeria.show',$mensaje->id)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>
							@if($mensaje->lectura != 1)
								@if(Auth::user()->modo_lectura())
								@else
									<a href="{{route('mensajeria.edit',$mensaje->id)}}" class="btn btn-outline-primary btn-icons"><i class="fas fa-edit"></i></a>
									@if(!$mensaje->uso())
										<form action="{{ route('mensajeria.destroy',$mensaje->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-banco">
											{{ csrf_field() }}
											<input name="_method" type="hidden" value="DELETE">
										</form>
									<button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-banco', '¿Estas seguro que deseas eliminar el banco?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
									@endif
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
