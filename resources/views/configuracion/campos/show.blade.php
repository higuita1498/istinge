@extends('layouts.app')

@section('boton')	
	<a href="{{route('personalizar_inventario.edit',$campo->id)}}" class="btn btn-primary btn-xs"><i class="fas fa-edit"></i>Editar</a>
@endsection	
@section('content')
<div class="row card-description">
	<div class="col-md-12">
		<div class="table-responsive">
			<table class="table table-striped table-bordered table-sm info">
				<tbody>
					<tr>
						<th width="25%">Campo</th>
						<th></th>
					</tr>
					<tr>
						<th>Nombre</th>
						<td>{{$campo->nombre}}</td>
					</tr>
					<tr>
						<th>Campo</th>
						<td>{{$campo->campo}}</td>
					</tr>
					<tr>
						<th>Descripción</th>
						<td>{{$campo->descripcion}}</td>
					</tr>
					<tr>
						<th>Tamaño del Campo</th>
						<td>{{$campo->varchar}}</td>
					</tr>
					<tr>
						<th>¿Es Requerido?</th>
						<td>{{$campo->tipo()}}</td>
					</tr>
					<tr>
						<th>Valor por Defecto</th>
						<td>{{$campo->default}}</td>
					</tr>
					<tr>
						<th>Autocompletar</th>
						<td>{{$campo->autocompletar()}}</td>
					</tr>
					<tr>
						<th>Estatus</th>
						<td>{{$campo->status()}}</td>
					</tr>
					<tr>
						<th>Posición en la tabla de <a href="{{route('inventario.index')}}" target="_blanck">Inventario</a></th>
						<td>{{$campo->tabla()}}</td>
					</tr>
					
				</tbody>
			</table>
		</div>
		
	</div>
</div>

@endsection