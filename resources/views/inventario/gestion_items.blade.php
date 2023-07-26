 @extends('layouts.app')

@section('content')
	<div class="row card-description configuracion">
		<div class="col-sm-3">
			<h4 class="card-title">Importar nuevos</h4>
			<p>Crea tus items desde un archivo de Excel.</p>
			<a href="{{route('inventario.importar')}}">Importar</a> <br>
		</div>

		<div class="col-sm-3">
			<h4 class="card-title">Actualización masiva</h4>
			<p>Actualiza de manera masiva tus items por medio de un archivo de Excel.</p>
			<a href="{{route('inventario.actualizar')}}">Actualizar</a> <br>
		</div>
		
		<div class="col-sm-3">
			<h4 class="card-title">Transferencia entre bodegas</h4>
			<p>Transfiere items entre bodegas.</p>
			<a href="{{route('transferencia.index')}}">Transferir</a> <br>
		</div>
	</div>
@endsection