@extends('layouts.app')
@section('boton')
	@if(Auth::user()->modo_lectura())
		<div class="alert alert-warning alert-dismissible fade show" role="alert">
			<a>Esta en Modo Lectura si desea seguir disfrutando de Nuestros Servicios Cancelar Alguno de Nuestros Planes <a class="text-black" href="{{route('PlanesPagina.index')}}"> <b>Click Aqui.</b></a></a>
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
	@else
	<a href="{{route('lista_precios.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nueva Lista de Precios</a>
	@endif
@endsection		
@section('content')
	@if(Session::has('success'))
		<div class="alert alert-success">
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
			<table class="table table-striped table-hover" id="table-general">
				<thead class="thead-dark">
					<tr>
		              <th>Nombre</th>
		              <th>Principal</th>
		              <th>Tipo</th>
		              <th class="text-center">Estatus</th>
		              <th class="text-center">Acciones</th>
		          </tr>                              
				</thead>
				<tbody>
					@foreach($listas as $lista)
						<tr @if($lista->nro==Session::get('lista_id')) class="active_table" @endif>
							<td>{{$lista->nombre}}</td>
							<td>{{$lista->principal()}}</td>
							<td>{{$lista->tipo()}}</td>
							<td class="text-center">{{$lista->status()}}</td>
							<td class="text-center">
							@if(Auth::user()->modo_lectura())
							@else
								<a href="{{route('lista_precios.edit',$lista->nro)}}" class="btn btn-outline-primary btn-icons"><i class="fas fa-edit"></i></a>
								@if($lista->nro!=1)
									<form action="{{ route('lista_precios.act_desc',$lista->nro) }}" method="POST" class="delete_form" style="margin:  0;display: inline-block;" id="act_desc-{{$lista->nro}}">
										{{ csrf_field() }}
									</form>
									@if($lista->status==1)
									  <button class="btn btn-outline-secondary  btn-icons" type="submit" title="Desactivar" onclick="confirmar('act_desc-{{$lista->nro}}', '¿Estas seguro que deseas desactivar esta lista?', 'No aparecera para seleccionar en la creación de productos');"><i class="fas fa-power-off"></i></button>
									@else
									  <button class="btn btn-outline-success  btn-icons" type="submit" title="Activar" onclick="confirmar('act_desc-{{$lista->nro}}', '¿Estas seguro que deseas activar esta lista?', 'Aparecera para seleccionar en la creación de productos');"><i class="fas fa-power-off"></i></button>
									@endif
									@if (!$lista->uso())
										<form action="{{ route('lista_precios.destroy',$lista->nro) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-lista{{$lista->nro}}">
											{{ csrf_field() }}
											<input name="_method" type="hidden" value="DELETE">
										</form>
										<button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-lista{{$lista->nro}}', '¿Estas seguro que deseas eliminar la lista?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
									@endif
								@endif
							@endif
    					</td>
						</tr> 
					@endforeach
				</tbody> 
			</table>
		</div>
	</div>
@endsection