@extends('layouts.app')
@section('boton')	
	<a href="{{route('transferencia.edit',$transferencia->nro)}}" class="btn btn-primary "><i class="fas fa-edit"></i>Editar</a>
    <a href="{{route('transferencia.imprimir',$transferencia->nro)}}" class="btn btn-outline-primary btn-sm "title="Imprimir" target="_blank"><i class="fas fa-print"></i> Imprimir</a>

	<a href="{{route('transferencia.index')}}" class="btn btn-link">Ir al registro de Transferencias</a>
@endsection	

@section('content')
<div class="row card-description">
	<div class="col-md-9">
		<div class="table-responsive">
			<table class="table table-striped table-bordered table-sm info">
				<tbody>
					<tr>
						<th width="20%">Fecha</th>
						<td>{{date('d-m-Y', strtotime($transferencia->fecha))}}</td>
					</tr>
					<tr>
						<th>Bodega de Origen</th>
						<td>{{$transferencia->bodega()->bodega}}</td>
					</tr>
					<tr>
						<th>Bodega de Destino</th>
						<td>{{$transferencia->bodega('destino')->bodega}}</td>
					</tr>
					<tr>
						<th>Observaciones</th>
						<td>{{$transferencia->observaciones}}</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>
 <div class="row card-description">
    <div class="col-md-12">
		<table class="table table-striped table-sm" width="100%">
			<thead class="thead-dark">
			<tr>
				<th>Ítem</th>
				<th class="text-center">Referencia</th>
				<th class="text-center">Cantidad transferida</th>
			</tr>
			</thead>
			<tbody>				
	          @foreach($trans as $item)
	          	<tr>
	          		<td><a href="{{route('inventario.show',$item->producto)}}">{{$item->producto()->producto}}</a></td>
	          		<td class="text-center">{{$item->producto()->ref}}</td>
	          		<td class="text-center">{{$item->nro}}</td>
	          	</tr>
	          @endforeach
			</tbody>
		</table>
    </div>
</div>
@endsection