@extends('layouts.app')

@section('boton')
	@if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
	        <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
	    </div>
	@else
		<a href="{{route('bodegas.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nueva Bodega</a>
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
		              <th>Dirección</th>
		              <th>Observaciones</th>
		              <th class="text-center">Estatus</th>
		              <th class="text-center">Acciones</th>
		          </tr>                              
				</thead>
				<tbody>
					@foreach($bodegas as $bodega)
						<tr @if($bodega->nro==Session::get('bodega_id')) class="active_table" @endif>
							<td>{{$bodega->bodega}}</td>
							<td>{{$bodega->direccion}}</td>
							<td>{{$bodega->observaciones}}</td>
							<td class="text-center">{{$bodega->status()}}</td>
							<td class="text-center">
								@if(Auth::user()->modo_lectura())
								@else
									<a href="{{route('bodegas.edit',$bodega->nro)}}" class="btn btn-outline-primary btn-icons"><i class="fas fa-edit"></i></a>
								@endif
							<form action="{{ route('bodegas.act_desc',$bodega->nro) }}" method="POST" class="delete_form" style="margin:  0;display: inline-block;" id="act_desc-{{$bodega->nro}}">
			                    {{ csrf_field() }}
			                </form>
							@if($bodega->status==1)
			                  <button class="btn btn-outline-secondary  btn-icons" type="submit" title="Desactivar" onclick="confirmar('act_desc-{{$bodega->nro}}', '¿Estas seguro que deseas desactivar esta bodega?', 'No aparecera para seleccionar en la creación de productos');"><i class="fas fa-power-off"></i></button>
			                @else
			                  <button class="btn btn-outline-success  btn-icons" type="submit" title="Activar" onclick="confirmar('act_desc-{{$bodega->nro}}', '¿Estas seguro que deseas activar esta bodega?', 'Aparecera para seleccionar en la creación de productos');"><i class="fas fa-power-off"></i></button>
			                @endif				
			                @if (!$bodega->uso())
								<form action="{{ route('bodegas.destroy',$bodega->nro) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-bodega{{$bodega->nro}}">
	        						{{ csrf_field() }}
									<input name="_method" type="hidden" value="DELETE">
	    						</form>
	    						<button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-bodega{{$bodega->nro}}', '¿Estas seguro que deseas eliminar la bodega?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
    						@endif		
    					</td>
						</tr> 
					@endforeach
				</tbody> 
			</table>
		</div>
	</div>
@endsection