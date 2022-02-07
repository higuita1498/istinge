@extends('layouts.app')

@section('boton')
    @if(isset($_SESSION['permisos']['423']))
	    <a href="{{route('notificaciones.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nueva Notificación</a>
    @endif
@endsection


@section('content')
	@if(Session::has('success'))
		<div class="alert alert-success" style="margin-left: 2%;margin-right: 2%;">
			{{Session::get('success')}}
		</div>
	@endif
	
	<div class="row card-description">
		<div class="col-md-12">
			<table class="table table-striped table-hover" id="example">
			<thead class="thead-dark">
				<tr>
	              <th class="text-center">Tipo</th>
	              <th class="text-center">Desde</th>
	              <th class="text-center">Hasta</th>
	              <th>Mensaje</th>
	              <th class="text-center">Status</th>
	              <th class="text-center">Acciones</th>
	          </tr>
			</thead>
			<tbody>
				@foreach($notificaciones as $notificacion)
					<tr @if($notificacion->id==Session::get('solicitud_id')) class="active" @endif>
						<td class="text-center">{{$notificacion->tipo()}}</td>
						<td class="text-center">{{date('d-m-Y', strtotime($notificacion->desde))}}</td>
						<td class="text-center">{{date('d-m-Y', strtotime($notificacion->hasta))}}</td>
						<td>{{$notificacion->mensaje}}</td>
						<td class="text-center font-weight-bold {{$notificacion->status(true)}} ">{{$notificacion->status()}}</td>
						<td class="text-center">
						    @if(isset($_SESSION['permisos']['425']))
							<form action="{{ route('notificaciones.destroy',$notificacion->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-{{$notificacion->id}}">
							    {{ csrf_field() }}
							    <input name="_method" type="hidden" value="DELETE">
							</form>
							@endif
							<a href="{{route('notificaciones.show',$notificacion->id)}}" class="btn btn-outline-info btn-icons d-none" title="Ver"><i class="far fa-eye"></i></i></a>
							@if(isset($_SESSION['permisos']['424']))
							<a href="{{route('notificaciones.edit',$notificacion->id)}}" class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
							@endif
							@if(isset($_SESSION['permisos']['425']))
							<button class="btn btn-outline-danger btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-{{$notificacion->id}}', '¿Está seguro que desea eliminar la notificación?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
							@endif
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
		</div>
	</div>
@endsection