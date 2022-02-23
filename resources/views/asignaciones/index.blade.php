@extends('layouts.app')

@section('boton')
    @if(isset($_SESSION['permisos']['5']))
	    <a href="{{route('contactos.create')}}" class="btn btn-outline-info btn-sm"><i class="fas fa-plus"></i> Nuevo Cliente</a>
    @endif
    @if(isset($_SESSION['permisos']['411']))
        <a href="{{route('contratos.create')}}" class="btn btn-outline-info btn-sm"><i class="fas fa-plus"></i> Nuevo Contrato</a>
    @endif
    @if(isset($_SESSION['permisos']['201']))
        <a href="{{route('radicados.create')}}" class="btn btn-outline-info btn-sm"><i class="fas fa-plus"></i> Nuevo Radicado</a>
    @endif
    @if(isset($_SESSION['permisos']['411']))
	<a href="{{route('asignaciones.create')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nueva Asignación</a>
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
						<th>Cliente</th>
						<th>Cédula</th>
						<th>Fecha de Firma</th>
						<th class="text-center">Acciones</th>
		            </tr>
				</thead>
				<tbody>
					@foreach($contratos as $contrato)
						<tr>
							<td><a href="{{ route('contactos.show',$contrato->id )}}"  title="Ver">{{ $contrato->nombre }}</a></td>
							<td>{{ $contrato->nit }}</td>
							<td>{{date('d-m-Y', strtotime($contrato->fecha_isp))}}</td>
							<td class="text-center">
								<a href="{{ route('contactos.show',$contrato->id )}}"  class="btn btn-outline-info btn-icons" title="Ver Detalle"><i class="far fa-eye"></i></i></a>
								<a href="{{ route('asignaciones.imprimir',$contrato->id )}}"  class="btn btn-outline-danger btn-icons" title="Imprimir Contrato Digital" target="_blank"><i class="fas fa-print"></i></i></a>
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


@endsection