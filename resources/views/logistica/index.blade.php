@extends('layouts.app')
@section('boton')	
		<a href="{{route('logistica.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Envio</a>
	
	
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
				  <th>Número de guía</th>
	              <th>Factura Asociada</th>
	              <th>Cliente</th>
	              <th>Dirección</th>
	              <th>Logístico encargado</th>
	              <th>Estatus</th>
	              <th>Acciones</th>
	          </tr>
			</thead>
			<tbody>
			</tbody>
		</table>
		</div>
	</div>
@endsection