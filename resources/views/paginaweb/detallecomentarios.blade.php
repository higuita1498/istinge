@extends('layouts.app')

@section('boton')
@if(Auth::user()->modo_lectura() || Auth::user()->facturasHechas() || Auth::user()->ingresosMaximos())
<div class="alert alert-warning alert-dismissible fade show" role="alert">
	<a>Esta en Modo Lectura si desea seguir disfrutando de Nuestros Servicios Cancelar Alguno de Nuestros Planes <a class="text-black" href="{{route('PlanesPagina.index')}}"> <b>Click Aqui.</b></a></a>
	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
		<span aria-hidden="true">&times;</span>
	</button>
</div>
@else
<a href="{{route('factura.create_item',$inventario->id)}}" class="btn btn-outline-primary btn-sm "><i class="fas fa-plus"></i>Facturar este ítem</a>
<a href="{{route('facturasp.create_item',$inventario->id)}}" class="btn btn-outline-primary btn-sm "><i class="fas fa-plus"></i>Comprar este ítem</a>
<a href="{{route('inventario.edit',$inventario->id)}}" class="btn btn-outline-primary btn-sm "><i class="fas fa-edit"></i>Editar</a>
@endif
@endsection
@section('content')
<div class="row card-description">
	<div class="col-md-9">
		<div class="table-responsive">
			<table class="table table-striped table-bordered table-sm info">
				<tbody>
					<tr>
						<th width="20%">Código</th>
						<td><b>{{$inventario->id}}</b></td>
					</tr>
					<tr>
						<th >Referencia</th>
						<td>{{$inventario->ref}}</td>
					</tr>
					<tr>
						<th>Nombre</th>
						<td>{{$inventario->producto}}</td>
					</tr>
					<tr>
						<th>Precio</th>
						<td style="padding: 0 1%  !important; " border="0">
							<table class="precios_table">
								<tr>
									<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($inventario->precio)}}</td>
									<td><span class="text-muted">{{$inventario->precio()}}</span></td>
								</tr>
								@foreach($inventario->precios() as $precio)
								<tr>
									<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($precio->precio)}}</td>
									<td><span class="text-muted">{{$precio->lista()->nombre()}}</span></td>
								</tr>


								@endforeach
							</table>
						</td>
					</tr>
					<tr>
						<th>Impuesto</th>
						<td>{{$inventario->impuesto()}}</td>
					</tr>
					<tr>
						<th>Descripción</th>
						<td>{{$inventario->descripcion}}</td>
					</tr>
					<tr>
						<th>Categoría</th>
						<td>{{$inventario->categoria()}}</td>
					</tr>
					@if($inventario->tipo_producto==1)
					<tr>
						<th>Unidad de medida</th>
						<td>{{$inventario->unidad()}}</td>
					</tr>
					<tr>
						<th>Cantidad inicial</th>
						<td>{{$inventario->inventario('inicial')}}</td>
					</tr>
					<tr>
						<th>Inventario</th>
						<td>{{$inventario->inventario()}}</td>
					</tr>
					<tr>
						<th>Bodegas</th>
						<td>
							<table class="precios_table">
								@foreach($inventario->bodegas() as $bodega)
								<tr>
									<td>{{$bodega->nro}}</td>
									<td><span class="text-muted">{{$bodega->bodega()->bodega}}</span></td>
								</tr>
								@endforeach
							</table>
						</td>
					</tr>
					@endif
					<tr>
						<th>Lista</th>
						<td>
							{{$inventario->lista()}}
						</td>
					</tr>
					@if(count($extras)>0)
					<tr>
						<th><b>Campos Extras</b></th>
					</tr>
					@foreach($extras as $campo)
					<tr>
						<th>{{$campo->nombre}} @if($campo->descripcion)<br><small>{{$campo->descripcion}}</small>@endif</th>
						<td>{{$inventario->campoExt($campo->campo)}}</td>
					</tr>

					@endforeach

					@endif
					
				</tbody>


			</table>
		</div>
	</div>
	<div class="col-md-3" style="text-align: center;">
		@if($inventario->imagen)
		<img class="img-responsive" src="{{asset('images/Empresas/Empresa'.$inventario->empresa.'/inventario/'.$inventario->imagen)}}" alt="" style="    width: 100%;" onerror="this.onerror=null; this.src='@if(Auth::user()->empresa()->img_default) {{asset("images/Empresas/Empresa".Auth::user()->empresa."/".Auth::user()->empresa()->img_default)}} @else {{asset('images/producto-sin-imagen.png')}} @endif ';" 

		>
		@else
		<img class="img-responsive" src="@if(Auth::user()->empresa()->img_default) {{asset("images/Empresas/Empresa".Auth::user()->empresa."/".Auth::user()->empresa()->img_default)}} @else {{asset('images/producto-sin-imagen.png')}} @endif" alt="" style="    width: 100%;">

		@endif
		
	</div>
</div>

<div class="row card-description">
	<h1>Comentarios</h1>
	<!-- Desgloce -->
	<div class="row" style="padding: 2% 6%;">
		<div class="col-md-12 fact-table">
			<table class="table table-striped table-sm desgloce"  width="100%">
				<thead >
					<tr>
						<th>nro</th>
						<th width="7%">Nombre</th>
						<th width="43%">Comentario</th>
						<th width="12%">Calificacion</th>
						<th width="12%">Email</th>
						<th width="13%">Teléfono</th>
						<th width="13%">Fecha del comentario</th>
					</tr>
				</thead>
				<tbody>
					@foreach($comentarios as $comentario)
					<tr>
						<td>{{$comentario->id}}</td>
						<td>{{$comentario->nombre}}</td>
						<td>{{$comentario->comentario}}</td>
						<td>{{$comentario->calificacion}}</td>
						<td>{{$comentario->email}}</td>
						<td>{{$comentario->telefono}}</td>
						<td>{{$comentario->created_at}}</td>
					</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
	<a clas="btn btn-warning" href="{{route('PaginaWeb.comentarios')}}">Regresar</a>
</div>
@endsection
