@extends('layouts.app')

@section('boton')
    @if(isset($_SESSION['permisos']['5']))
	    <a href="{{route('contactos.create')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nuevo Cliente</a>
    @endif
    @if(isset($_SESSION['permisos']['411']))
        <a href="{{route('contratos.create')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nuevo Contrato</a>
    @endif
    @if(isset($_SESSION['permisos']['201']))
        <a href="{{route('radicados.create')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nuevo Radicado</a>
    @endif
@endsection

@section('content')
	@if(Session::has('success'))
		<div class="alert alert-success" style="margin-left: 2%;margin-right: 2%;">
			{{Session::get('success')}}
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
	<div class="row card-description">
		<div class="col-md-12">
			<table class="table table-striped table-hover" id="example">
			<thead class="thead-dark">
				<tr>
	              <th>Nombre</th>
	              <th>Cédula</th>
	              <th>Nro Teléfono</th>
	              <th>Nro Teléfono</th>
	              <th>Email</th>
	              <th>Plan</th>
	              <th>Dirección</th>
	              <th>Fecha</th>
	              <th>Status</th>
	              <th>Acciones</th>
	          </tr>
			</thead>
			<tbody>
				@foreach($solicitudes as $solicitud)
					<tr @if($solicitud->id==Session::get('solicitud_id')) class="active" @endif>
						<td>{{$solicitud->nombre}}</td>
						<td>{{$solicitud->cedula}}</td>
						<td>{{$solicitud->nrouno}}</td>
						<td>{{$solicitud->nrodos}}</td>
						<td>{{$solicitud->email}}</td>
						<td>{{$solicitud->plan}}</td>
						<td>{{$solicitud->direccion}}</td>
						<td>{{date('d-m-Y', strtotime($solicitud->fecha))}}</td>
						<td><span class="font-weight-bold text-{{$solicitud->status('true')}}">{{$solicitud->status()}}</span></td>
						<td>
						    <form action="{{ route('solicitudes.status',$solicitud->id) }}" method="POST" class="delete_form" style="display: none;" id="status-{{$solicitud->id}}">
							    {{ csrf_field() }}
							</form>
							<a href="{{route('solicitudes.show',$solicitud->id)}}"  class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></i></a>
							<a href="#" onclick="confirmar('status-{{$solicitud->id}}', '¿Está seguro de que desea darle respuesta positiva a la solicitud de servicio?');" class="btn btn-outline-success btn-icons" title="Solucionar"><i class="fas fa-check"></i></a>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
		</div>
	</div>
@endsection