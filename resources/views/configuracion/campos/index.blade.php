 @extends('layouts.app')
@section('boton')	
	{{--<a href="{{route('personalizar_inventario.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Campo</a>--}}
	<a href="{{route('personalizar_inventario.organizar')}}" class="btn btn-outline-light btn-sm" >Organizar Tabla</a>
	
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
			<table class="table table-striped table-hover" id="example">
			<thead class="thead-dark">
				<tr>
	              <th>Nombre</th>
	              <th>Descripción</th>
	              <th>Tamaño Campo</th>
	              <th>Requerido</th>
	              <th>Tabla Inventario</th>
	              <th>Estatus</th>
	              <th>Acciones</th>
	          </tr>
			</thead>
			<tbody>
				@foreach($campos as $campo)
				<tr @if($campo->id==Session::get('campo_id')) class="active_table" @endif >
					<td>{{$campo->nombre}}</td>
					<td>{{$campo->descripcion}}</td>
					<td>{{$campo->varchar}}</td>
					<td>{{$campo->tipo()}}</td>
					<td>{{$campo->tabla()}}</td>
					<td>{{$campo->status()}}</td>
					<td>
						<a href="{{route('personalizar_inventario.show',$campo->id)}}"   class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></i></a>						
						<a href="{{route('personalizar_inventario.edit',$campo->id)}}"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
						<form action="{{ route('personalizar_inventario.act_desc',$campo->id) }}" method="POST" class="delete_form" style="margin:  0;display: inline-block;" id="act_desc-campo{{$campo->id}}">
		                    {{ csrf_field() }}
		                </form>
	                 	@if($campo->status==1)
		                  <button class="btn btn-outline-secondary  btn-icons" type="submit" title="Desactivar" onclick="confirmar('act_desc-campo{{$campo->id}}', '¿Estas seguro que deseas desactivar este campo?', 'No aparecera en el Inventario');"><i class="fas fa-power-off"></i></button>
		                @else
		                  <button class="btn btn-outline-success  btn-icons" type="submit" title="Activar" onclick="confirmar('act_desc-campo{{$campo->id}}', '¿Estas seguro que deseas activar este campo?', 'Aparecera en el Inventario');"><i class="fas fa-power-off"></i></button>
		                @endif

		                @if($campo->usado()==0)
							<form action="{{ route('personalizar_inventario.destroy',$campo->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-campo{{$campo->id}}">
	    						{{ csrf_field() }}
								<input name="_method" type="hidden" value="DELETE">
							</form>
							<button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-campo{{$campo->id}}', '¿Estas seguro que deseas eliminar este campo?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
						@endif

					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
		</div>
	</div>
@endsection 