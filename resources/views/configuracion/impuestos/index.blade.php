 @extends('layouts.app')
@section('boton')
    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
	        <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
	    </div>
	@else
	<a href="{{route('impuestos.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Tipo de Impuesto</a>
	@endif
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
	              <th>Porcentaje (%)</th>
	              <th>Tipo</th>
	              <th>Descripción</th>
	              <th>Acciones</th>
	          </tr>
			</thead>
			<tbody>
				@foreach($impuestos as $impuesto)
					<tr @if($impuesto->id==Session::get('impuesto_id') && $impuesto->empresa) class="active_table" @endif>
						<td>{{$impuesto->nombre}} </td>
						<td>{{$impuesto->porcentaje}}</td>
						<td>{{$impuesto->tipo()}}</td>
						<td>{{$impuesto->descripcion}}</td>
						<td>
							@if(auth()->user()->modo_lectura())@else
							@if($impuesto->empresa)

							<form action="{{ route('impuestos.act_desc',$impuesto->id) }}" method="POST" class="delete_form" style="margin:  0;display: inline-block;" id="act_desc-impuesto">
			                    {{ csrf_field() }}
			                </form>
			                	@if($impuesto->estado==1)
			                		<a href="{{route('impuestos.edit',$impuesto->id)}}" class="btn btn-outline-primary btn-icons"><i class="fas fa-edit"></i></a>
			                		@if($impuesto->usado()==0)
										<form action="{{ route('impuestos.destroy',$impuesto->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-impuesto">
		        						{{ csrf_field() }}
										<input name="_method" type="hidden" value="DELETE">
		    						</form>
		    						<button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-impuesto', '¿Estas seguro que deseas eliminar el tipo de impuesto?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
									@endif
									 <button class="btn btn-outline-secondary  btn-icons" type="submit" title="Desactivar" onclick="confirmar('act_desc-impuesto', '¿Estas seguro que deseas desactivar este tipo de impuesto?', 'No aparecera para seleccionar en las facturas y/o inventario');"><i class="fas fa-power-off"></i></button>
			                	 @else
			                  	<button class="btn btn-outline-success  btn-icons" type="submit" title="Activar" onclick="confirmar('act_desc-impuesto', '¿Estas seguro que deseas activar este tipo de impuesto?', 'Aparecera para seleccionar en las facturas y/o inventario');"><i class="fas fa-power-off"></i></button>
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