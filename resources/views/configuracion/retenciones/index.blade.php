 @extends('layouts.app')
@section('boton')	
		<a href="{{route('retenciones.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Tipo de Retención</a>
	
	
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
	              <th>Retención (%)</th>
	              <th>Tipo</th>
	              <th>Descripción</th>
	              <th>Acciones</th>
	          </tr>
			</thead>
			<tbody>
				@foreach($retenciones as $retencion)
					<tr @if($retencion->id==Session::get('retencion_id')) class="active_table" @endif>
						<td>{{$retencion->nombre}}</td>
						<td>{{$retencion->porcentaje}}</td>
						<td>{{$retencion->tipo()}}</td>
						<td>{{$retencion->descripcion}}</td>
						<td>
							@if($retencion->empresa)

							<form action="{{ route('retenciones.act_desc',$retencion->id) }}" method="POST" class="delete_form" style="margin:  0;display: inline-block;" id="act_desc-retencion">
			                    {{ csrf_field() }}
			                </form>
			                	@if($retencion->estado==1)
			                		<a href="{{route('retenciones.edit',$retencion->id)}}" class="btn btn-outline-primary btn-icons"><i class="fas fa-edit"></i></a>
			                		@if($retencion->usado()==0)
										<form action="{{ route('retenciones.destroy',$retencion->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-retencion">
		        						{{ csrf_field() }}
										<input name="_method" type="hidden" value="DELETE">
		    						</form>
		    						<button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-retencion', '¿Estas seguro que deseas eliminar el tipo de retencion?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
									@endif
									 <button class="btn btn-outline-secondary  btn-icons" type="submit" title="Desactivar" onclick="confirmar('act_desc-retencion', '¿Estas seguro que deseas desactivar este tipo de retencion?', 'No aparecera para seleccionar en las facturas y/o inventario');"><i class="fas fa-power-off"></i></button>
			                	 @else
			                  	<button class="btn btn-outline-success  btn-icons" type="submit" title="Activar" onclick="confirmar('act_desc-retencion', '¿Estas seguro que deseas activar este tipo de retencion?', 'Aparecera para seleccionar en las facturas y/o inventario');"><i class="fas fa-power-off"></i></button>
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