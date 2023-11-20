 @extends('layouts.app')
@section('boton')
    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">Integra Colombia: Suscripción Vencida</h4>
	       <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
	    </div>
	@else
	<a href="{{route('tiposempresa.create')}}"  data-toggle="modal" data-target="#newtype" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Tipo de Contacto</a>
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
	
	 {{-- Modal contacto nuevo --}}
 <div class="modal fade" id="newtype" role="dialog">
 	<div class="modal-dialog modal-lg">
 		<div class="modal-content">
 			<div class="modal-header">
 				<button type="button" class="close" data-dismiss="modal">&times;</button>
 				<h4 class="modal-title"></h4>
 			</div>
 			<div class="modal-body">
 				@include('configuracion.tiposempresa.modal.create')
 			</div>
 			<div class="modal-footer">
 				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
 			</div>
 		</div>
 	</div>
 </div>
 {{-- /Modal contacto nuevo --}}
	
	<div class="row card-description">
		<div class="col-md-12">
			<table class="table table-striped table-hover" id="example">
			<thead class="thead-dark">
				<tr>
	              <th>Nombre</th>
	              <th>Descripción</th>
	              <th>Nro Contactos</th>
	              <th>Acciones</th>
	          </tr>
			</thead>
			<tbody>
				@foreach($tipos as $tipo)
					<tr @if($tipo->id==Session::get('tipo_id')) class="active_table" @endif>
						<td>{{$tipo->nombre}}</td>
						<td>{{$tipo->descripcion}}</td>
						<td>{{$tipo->usado()}}</td>
						<td>
							@if(auth()->user()->modo_lectura())
							@else
							<a href="{{route('tiposempresa.edit',$tipo->id)}}" class="btn btn-outline-primary btn-icons"><i class="fas fa-edit"></i></a>
							@if($tipo->usado()==0)
								<form action="{{ route('tiposempresa.destroy',$tipo->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-tipo">
        						{{ csrf_field() }}
								<input name="_method" type="hidden" value="DELETE">
    						</form>
    						<button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-tipo', '¿Estas seguro que deseas eliminar el Tipo de Empresa?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
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