@extends('layouts.app')
@section('boton')	
		<a href="{{route('recurrentes.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nueva Factura Recurrente</a>
	
	
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
			<table class="table table-striped table-hover " id="example">
			<thead class="thead-dark">
				<tr>
	              <th>Nro</th>
	              <th>Cliente</th>
	              <th>Fecha de Incio</th>
	              <th>Fecha de Finalización</th>
	              <th>Total</th>
	              <th>Frecuencia (meses)</th>
	              <th>Termino (días)</th>
	              <th>Acciones</th>
	          </tr>                              
			</thead>
			<tbody >
				@foreach($facturas as $factura)
					<tr @if($factura->id==Session::get('factura')) class="active_table" @endif>
						<td><a href="{{route('recurrentes.show',$factura->nro)}}" >{{$factura->nro}}</a> </td>
						<td><a href="{{route('contactos.show',$factura->cliente)}}" target="_blanck">{{$factura->cliente()->nombre}}</a></td>
						<td>{{date('d-m-Y', strtotime($factura->fecha))}}</td>
						<td>@if($factura->vencimiento){{date('d-m-Y', strtotime($factura->vencimiento))}} @endif</td>
						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->total()->total)}}</td> 
						<td>{{$factura->frecuencia}}</td>
						<td>{{$factura->plazo()}}</td>
						<td>
							<a href="{{route('recurrentes.show',$factura->nro)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a> 
							<a href="{{route('recurrentes.edit',$factura->nro)}}"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>

							<form action="{{ route('recurrentes.destroy',$factura->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-recurrentes{{$factura->id}}">
    						{{ csrf_field() }}
							<input name="_method" type="hidden" value="DELETE">
							</form>
							<button class="btn btn-outline-danger  btn-icons negative_paging" type="submit" title="Eliminar" onclick="confirmar('eliminar-recurrentes{{$factura->id}}', '¿Estas seguro que deseas eliminar la factura recurrente?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>								
						</td> 
					</tr> 
				@endforeach
			</tbody>
		</table>
		</div>
	</div>
@endsection