@extends('layouts.app')
@section('boton')
    
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
			<table class="table table-striped table-hover" id="table-facturas">
			<thead class="thead-dark">
				<tr>
	                <th>Cliente</th>
	                <th class="text-center">Contrato</th>
	                <th class="text-center">Acción</th>
	                <th class="text-center">Responsable</th>
	                <th class="text-center">Fecha/Hora</th>
	            </tr>
			</thead>
			<tbody>
				@foreach($auditorias as $auditoria)
					<tr> 
						<td><a href="{{route('contactos.show',$auditoria->id_cliente)}}">{{$auditoria->cliente()->nombre}}</a></td>
						<td class="text-center"><a href="{{route('contratos.show',$auditoria->id_contrato)}}">{{$auditoria->id_contrato}}</a></td>
						<td class="text-center"><span class="font-weight-bold text-{{$auditoria->accion(true)}}">{{$auditoria->accion()}}</span></td>
						<td class="text-center">{{$auditoria->responsable()->nombres}}</td>
						<td class="text-center">{{$auditoria->created_at}}</td>
					</tr>
				@endforeach
			</tbody>
		</table>
		</div>
	</div>
@endsection