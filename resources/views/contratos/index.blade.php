@extends('layouts.app')

@section('boton')
	<a href="{{route('contratos.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Contrato</a>
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
		<div class="col-md-2 offset-md-1">
			<div class="alert alert-primary text-center" role="alert">
				<h5 class="alert-heading" style="font-size: 0.8rem;">Registrados</h5>
				<h5 class="alert-heading">{{ $numcontratos }}</h5>
			</div>
		</div>
		<div class="col-md-2">
			<div class="alert alert-success text-center" role="alert">
				<h5 class="alert-heading" style="font-size: 0.8rem;">Habilitados</h5>
				<h5 class="alert-heading">{{ $habilitados }}</h5>
			</div>
		</div>
		<div class="col-md-2">
			<div class="alert alert-danger text-center" role="alert">
				<h5 class="alert-heading" style="font-size: 0.8rem;">Deshabilitados</h5>
				<h5 class="alert-heading">{{ $deshabilitados }}</h5>
			</div>
		</div>
		<div class="col-md-2">
			<div class="alert alert-dark text-center" role="alert">
				<h5 class="alert-heading" style="font-size: 0.8rem;">Corte del 15</h5>
				<h5 class="alert-heading">{{ $corte15 }}</h5>
			</div>
		</div>
		<div class="col-md-2">
			<div class="alert alert-dark text-center" role="alert">
				<h5 class="alert-heading" style="font-size: 0.8rem;">Corte del 30</h5>
				<h5 class="alert-heading">{{ $corte30 }}</h5>
			</div>
		</div>
		<div class="col-md-10 offset-md-1 text-center mt-3">
		    @if($numpagina == 1000 || $pageleft >= 0)
		        <div class="alert alert-warning text-center" role="alert">
				    <h5 class="alert-heading" style="font-size: 0.8rem;margin: 0;">La vista está mostrado los contratos en lotes de {{ $take }}, si desea ver más lotes, haga clic en los botones inferiores.</h5>
			    </div>
		    @endif
		    @if($pageleft >= 0)
		         <a href="{{route('contratos.corte',$corte.'-'.$pageleft)}}" class="btn btn-success">Lote Anterior</a>
		    @endif
		    @if($numpagina == 1000)
		         <a href="{{route('contratos.corte',$corte.'-'.$page)}}" class="btn btn-success">Próximo Lote</a>
		    @endif
		</div>
	</div>

	<div class="row card-description">
		<div class="col-md-12">
			<table class="table table-striped table-hover" id="example">
				<thead class="thead-dark">
					<tr>
						<th>Nro.</th>
						<th>Cliente</th>
						<th class="text-center">Cédula</th>
						<th class="text-center">Plan</th>
					    {{-- <th class="text-center">Fecha Corte</th> --}}
						<th class="text-center">Suspension</th>
						<th class="text-center">Estado</th>
						<th class="text-center">Acciones</th>
		            </tr>
				</thead>
				<tbody>
					@foreach($contratos as $contrato)
						<tr>
							<td><a href="{{ route('contratos.show',$contrato->public_id )}}"  title="Ver">{{ $contrato->public_id }}</a></td>
							<td>{{ $contrato->nombre }}</td>
							<td class="text-center">{{ $contrato->nit }}</td>
							<td class="text-center">{{ $contrato->plan }}</td>
							{{-- <td class="text-center">
							    @if($contrato->fecha_corte == 15 || $contrato->fecha_corte == 30) {{ $contrato->fecha_corte }} de cada mes @endif
						        @if($contrato->fecha_corte == 50) Plan Gratis @endif 
						        @if($contrato->fecha_corte == 0) No Asignada @endif
						    </td> --}}
						    <td class="text-center">
							    @if($contrato->fecha_suspension > 0) {{ $contrato->fecha_suspension }} días @else NO @endif
						    </td>
							<td class="text-center">
								@if($contrato->status() == 'Habilitado')
								   <span class="text-success font-weight-bold">{{ $contrato->status() }}</span>
								@else
							       <span class="text-danger font-weight-bold">{{ $contrato->status() }}</span>
							    @endif
							</td>
							<td>
								<a href="{{ route('contratos.show',$contrato->public_id )}}"  class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></i></a>
								<?php if(isset($_SESSION['permisos']['406'])){ ?>
								<a href="{{ route('contratos.edit',$contrato->public_id )}}"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></i></a>
								<?php } ?>
								<?php if(isset($_SESSION['permisos']['407'])){ ?>
								    <form action="{{ route('contratos.state',$contrato->public_id) }}" method="post" class="delete_form" style="margin:0;display: inline-block;" id="cambiar-state{{$contrato->public_id}}">
								    	{{ csrf_field() }}
									</form>
									<button @if($contrato->state == 'enabled') class="btn btn-outline-danger btn-icons" title="Deshabilitar" @else  class="btn btn-outline-success btn-icons" title="Habilitar" @endif type="submit" onclick="confirmar('cambiar-state{{$contrato->public_id}}', '¿Estas seguro que deseas cambiar el estatus del contrato?', '');"><i class="fas fa-file-signature"></i></button>
								<?php } ?>
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