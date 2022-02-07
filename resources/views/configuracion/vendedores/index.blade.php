 @extends('layouts.app')
@section('boton')	
		<a href="{{route('vendedores.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Vendedor</a>
	
	
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
	              <th>Identificación</th>
	              <th>Observaciones</th>
	              <th>Acciones</th>
	          </tr>
			</thead>
			<tbody>
				@foreach($vendedores as $vendedor)
					<tr @if($vendedor->id==Session::get('vendedor_id')) class="active_table" @endif>
						<td>{{$vendedor->nombre}}</td>
						<td>{{$vendedor->identificacion}}</td>
						<td>{{$vendedor->observaciones}}</td>
						<td>
							<form action="{{ route('vendedores.act_desc',$vendedor->id) }}" method="POST" class="delete_form" style="margin:  0;display: inline-block;" id="act_desc-vendedor">
			                    {{ csrf_field() }}
			                </form>
			                @if($vendedor->estado==1)
			                	<a href="{{route('vendedores.edit',$vendedor->id)}}" class="btn btn-outline-primary btn-icons"><i class="fas fa-edit"></i></a>
							@if($vendedor->usado()==0)
								<form action="{{ route('vendedores.destroy',$vendedor->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-vendedor">
        						{{ csrf_field() }}
								<input name="_method" type="hidden" value="DELETE">
    						</form>
    						<button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-vendedor', '¿Estas seguro que deseas eliminar el término de pago?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
							@endif
			                  <button class="btn btn-outline-secondary  btn-icons" type="submit" title="Desactivar" onclick="confirmar('act_desc-vendedor', '¿Estas seguro que deseas desactivar esta vendedor?', 'No aparecera para seleccionar en las facturas');"><i class="fas fa-power-off"></i></button>
			                @else
			                  <button class="btn btn-outline-success  btn-icons" type="submit" title="Activar" onclick="confirmar('act_desc-vendedor', '¿Estas seguro que deseas activar esta vendedor?', 'Aparecera para seleccionar en las facturas');"><i class="fas fa-power-off"></i></button>
			                @endif

						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
		</div>
	</div>
@endsection