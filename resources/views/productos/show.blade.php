@extends('layouts.app')

@section('boton')
    @if(auth()->user()->modo_lectura())
        <div class="alert alert-warning text-left" role="alert">
            <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
            <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
        </div>
    @else
        @if($producto->tipo == 1)
            <a href="{{route('productos.index_asignacion')}}" class="btn btn-outline-danger btn-sm"><i class="fas fa-backward"></i> Regresar</a>
        @else
            <a href="{{route('productos.index_devolucion')}}" class="btn btn-outline-danger btn-sm"><i class="fas fa-backward"></i> Regresar</a>
        @endif
	@endif
	<div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
		<button type="button" class="close" data-dismiss="alert">×</button>
		<strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
	</div>
@endsection

@section('content')
	<div class="row card-description">
		<div class="col-md-12">
			<table class="table table-striped table-bordered table-sm info">
				<tbody>
					<tr>
						<th class="bg-th text-center" colspan="2" style="font-size: 1em;"><strong>DATOS GENERALES</strong></th>
					</tr>
					<tr>
						<th width="15%">Nro</th>
						<td>{{$producto->nro}}</td>
					</tr>
					<tr>
						<th width="15%">Estado</th>
						<td class="font-weight-bold text-{{$producto->status(true)}}">{{$producto->status()}}</td>
					</tr>
					<tr>
						<th width="15%">Producto</th>
						<td>{{$producto->producto()->producto}} ({{$producto->producto()->ref}})</td>
					</tr>
					@if($producto->precio && $producto->status != 3)
					<tr>
						<th width="15%">Precio Total</th>
						<td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($producto->precio())}} @if($producto->cuotas) | {{ $producto->cuotas }} cuotas de {{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($producto->precio()/$producto->cuotas) }} @endif </td>
					</tr>
					@endif
					@if($producto->cuotas)
					<tr>
						<th width="15%">Cuotas</th>
						<td>{{ $producto->cuotas }} cuotas de {{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($producto->precio()/$producto->cuotas) }}</td>
					</tr>
					@endif
					@if($producto->cuotas_pendientes)
					<tr>
						<th width="15%">Cuotas Pendientes</th>
						<td>{{ $producto->cuotas_pendientes }} de {{ $producto->cuotas }}</td>
					</tr>
					@endif
					<tr>
						<th width="15%">Cliente</th>
						<td><a href="{{ route('contactos.show', $producto->contrato()->cliente()->id)}}"><strong>{{$producto->contrato()->cliente()->nombre}} {{$producto->contrato()->cliente()->apellidos()}}</strong></a>
					</tr>
					<tr>
						<th width="15%">Contrato</th>
						<td><a href="{{ route('contratos.show', $producto->contrato )}}"><strong>{{$producto->contrato()->nro}}</strong></a>
					</tr>
					<tr>
						<th width="15%">Creado por</th>
						<td>{{$producto->created_by()->nombres}}</td>
					</tr>
					<tr>
						<th width="15%">Creado el</th>
						<td>{{date('d-m-Y g:i:s A', strtotime($producto->created_at))}}</td>
					</tr>
					@if($producto->updated_by)
					<tr>
						<th width="15%">Actualizado por</th>
						<td>{{$producto->updated_by()->nombres}}</td>
					</tr>
					<tr>
						<th width="15%">Actualizado el</th>
						<td>{{$producto->updated_at}}</td>
					</tr>
					@endif
				</tbody>
			</table>
		</div>
	</div>
@endsection