@extends('layouts.app')
@section('boton')	
	<a href="{{route('ajustes.edit',$ajuste->nro)}}" class="btn btn-primary "><i class="fas fa-edit"></i>Editar</a>
@endsection	

@section('content')
<div class="row card-description">
	<div class="col-md-9">
		<div class="table-responsive">
			<table class="table table-striped table-bordered table-sm info">
				<tbody>
					<tr>
						<th width="20%">Ítem</th>						
						<td>{{$ajuste->producto()->producto}}</td> 
					</tr>
					<tr>
						<th>Bodega</th>
						<td>{{$ajuste->bodega()->bodega}}</td>
					</tr>
					<tr>
						<th>Tipo de ajuste</th>
						<td>{{$ajuste->ajuste()}}</td>
					</tr>
					<tr>
						<th>Fecha</th>
						<td>{{date('d-m-Y', strtotime($ajuste->fecha))}}</td>
					</tr>
					<tr>
						<th>Costo Unitario</th>
						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($ajuste->costo_unitario*$ajuste->cant)}}</td>
					</tr>
					<tr>
						
						<th>Costo Total</th>
						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($ajuste->costo_unitario*$ajuste->cant)}}</td>
					</tr>
					<tr>
						<th>Observaciones</th>
						<td>{{$ajuste->observaciones}}</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
@endsection