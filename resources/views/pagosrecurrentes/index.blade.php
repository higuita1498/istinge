@extends('layouts.app')

@section('boton')
    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">Integra Colombia: Suscripción Vencida</h4>
	       <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
	    </div>
	@else
    @if(isset($_SESSION['permisos']['263']))
	    <a href="{{route('pagosrecurrentes.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Pago Recurrente</a>
	@endif
	@endif
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
						<td><a href="{{route('pagosrecurrentes.show',$gasto->nro)}}">{{$gasto->nro}}</a></td>
						<td>
							<div class="elipsis-short">
								@if($gasto->tipo())<a href="{{route('contactos.show',$gasto->tipo()->id)}}" target="_blank">{{$gasto->tipo()->nombre}}@endif</a>
							</div>
						</td>
						<td><div class="elipsis-short-inpc">{{$gasto->observaciones}}</div></td>
						<td>{{$gasto->cuenta()->nombre}} </td>
						<td>{{date('d-m-Y', strtotime($gasto->proxima))}}</td>
						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($gasto->total()->total)}} </td>
						<td class="font-weight-bold text-{{$gasto->estado('true')}}">{{ $gasto->estado() }}</td>
						<td>
							@if(auth()->user()->modo_lectura())
							@else
							@if(isset($_SESSION['permisos']['262']))
							<a  href="{{route('pagosrecurrentes.show',$gasto->nro)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></i></a>
							@endif
							@if(isset($_SESSION['permisos']['264']))
							<a href="{{route('pagosrecurrentes.edit',$gasto->nro)}}"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
							@endif
							@if($gasto->estado == 1)
							<a  href="{{route('pagosR.ingreso', $gasto->id)}}" class="btn btn-outline-primary btn-icons" title="Agregar pago"><i class="fas fa-money-bill"></i></a>
							@endif
							@if(isset($_SESSION['permisos']['743']))
							<form action="{{ route('pagosrecurrentes.act_des',$gasto->id) }}" method="get" class="delete_form" style="margin:  0;display: inline-block;" id="aprobar-gasto{{$gasto->id}}">
						  		{{ csrf_field() }}
							</form>
							<button class="btn {{ ($gasto->estado==0) ? 'btn-outline-success' : 'btn-outline-danger' }} btn-icons negative_paging" type="submit" title="{{ ($gasto->estado==0) ? 'Aprobar/Activar' : 'Desactivar' }}" onclick="confirmar('aprobar-gasto{{$gasto->id}}', '¿Está seguro que deseas {{ ($gasto->estado==0) ? 'aprobar/activar' : 'desactivar' }} el pago pago recurrente?', '');"><i class="fas fa-{{ ($gasto->estado==0) ? 'check' : 'power-off' }}"></i></button>
							@endif
							@if(isset($_SESSION['permisos']['265']) && $gasto->uso() == 0)
						  	<form action="{{ route('pagosrecurrentes.destroy',$gasto->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-gasto{{$gasto->id}}">
						  		{{ csrf_field() }}
						  		<input name="_method" type="hidden" value="DELETE">
							</form>
							<button class="btn btn-outline-danger  btn-icons negative_paging" type="submit" title="Eliminar" onclick="confirmar('eliminar-gasto{{$gasto->id}}', 'Estas seguro que deseas eliminar el pago recurrente?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
							@endif
							@endif
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
