@extends('layouts.app')

@section('boton')	
	<a href="{{route('pagosrecurrentes.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Pago Recurrente</a>
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
			<div class="row mb-4" style="display: flex; justify-content: space-between;">
				<div class="col-md-6">
					<div class="row d-none">
						<div class="col-md-8 text-left" style="padding: 2%; font-weight: bold; color:#808080 ">Total a pagar pendiente</div>
						<div class="col-md-4 text-left text-danger" style="padding: 2%; font-weight: bold;">{{$total_pendiente}}</div>
					</div>
				</div>
				<div class="col-md-5">
					<div class="container-filtercolumn" >
						<a  onclick="filterOptions()" class="btn btn-secondary" value="0" id="buttonfilter">Filtrar  Campos<i class="fas fa-filter" style="margin-left:4px; "></i></a>
						<ul class="options-search-columns"  id="columnOptions">
							<li><input type="button" class="btn btn-success btn-sm boton_ocultar_mostrar" value="Código"></li>
							<li><input type="button" class="btn btn-success btn-sm boton_ocultar_mostrar" value="Tipo"></li>
							<li><input type="button" class="btn btn-success btn-sm boton_ocultar_mostrar" value="Obsevraciones"></li>
							<li><input type="button" class="btn btn-success btn-sm boton_ocultar_mostrar" value="Cuenta"></li>
							<li><input type="button" class="btn btn-success btn-sm boton_ocultar_mostrar" value="Próximo pago"></li>
							<li><input type="button" class="btn btn-success btn-sm boton_ocultar_mostrar" value="Monto"></li>
							<li><input type="button" class="btn btn-success btn-sm boton_ocultar_mostrar" value="Estatus"></li>
						</ul>
					</div>
				</div>
			</div>

			<table class="table table-striped table-hover" id="example">
				<thead class="thead-dark">
					<tr>
						<th>Código</th>
						<th>Tipo</th>
						<th>Observaciones</th>
						<th>Cuenta</th>
						<th>Proximo Pago</th>
						<th>Monto</th>
						<th>Estatus</th>
						<th>Acciones</th>
					</tr>
				</thead>
				<tbody>
					@foreach($gastos as $gasto)
					<tr @if($gasto->nro==Session::get('gasto_id')) class="active_table" @endif>
						<td><a href="{{route('pagosrecurrentes.show',$gasto->nro)}}">{{$gasto->nro}}</a> </td>
						<td><div class="elipsis-short">@if($gasto->tipo())<a href="{{route('contactos.show',$gasto->tipo()->id)}}" target="_blank">{{$gasto->tipo()->nombre}}@endif</a></div></td>
						<td><div class="elipsis-short-inpc">{{$gasto->observaciones}}</div></td>
						<td>{{$gasto->cuenta()->nombre}} </td>
						<td>{{date('d-m-Y', strtotime($gasto->proxima))}}</td>
						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($gasto->total()->total)}} </td>
						<td class="font-weight-bold text-{{$gasto->estado('true')}}">{{ $gasto->estado() }}</td>
						<td>
							<a  href="{{route('pagosrecurrentes.show',$gasto->nro)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></i></a>
							<a href="{{route('pagosrecurrentes.edit',$gasto->nro)}}"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
							<a  href="{{route('pagosR.ingreso', $gasto->id)}}" class="btn btn-outline-primary btn-icons" title="Agregar pago"><i class="fas fa-money-bill"></i></a>
						  	<form action="{{ route('pagosrecurrentes.destroy',$gasto->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-gasto{{$gasto->id}}">
						  		{{ csrf_field() }}
						  		<input name="_method" type="hidden" value="DELETE">
							</form>
							<button class="btn btn-outline-danger  btn-icons negative_paging" type="submit" title="Eliminar" onclick="confirmar('eliminar-gasto{{$gasto->id}}', 'Estas seguro que deseas eliminar el pago recurrente?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
						</td>
					</tr> 
					@endforeach
				</tbody>
			</table>
		</div>
	</div>

	<div class="modal fade" id="modalobservacion" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div id="contenido-observacion"></div>
		</div>
	</div>
@endsection
