@extends('layouts.app')

@section('style')
<style>
    .nav-tabs .nav-link {
        font-size: 1em;
    }
    .nav-tabs .nav-link.active, .nav-tabs .nav-item.show .nav-link {
        background-color: #b00606;
        color: #fff!important;
    }
    .nav-pills .nav-link.active, .nav-pills .show > .nav-link {
        color: #fff!important;
        background-color: #b00606!important;
    }
    .nav-pills .nav-link {
        font-weight: 700!important;
    }
    .nav-pills .nav-link{
        color: #b00606!important;
        background-color: #f9f9f9!important;
        margin: 2px;
        border: 1px solid #b00606;
        transition: 0.4s;
    }
    .nav-pills .nav-link:hover {
        color: #fff!important;
        background-color: #b00606!important;
    }
</style>
@endsection

@section('boton')
@if(isset($_SESSION['permisos']['202']))
	<a href="{{route('radicados.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Caso</a>
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

	<style>

	</style>

	<div class="row card-description">
		<div class="col-md-12">
			<ul class="nav nav-pills" id="myTab" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" id="pendientes-tab" data-toggle="tab" href="#pendientes" role="tab" aria-controls="pendientes" aria-selected="true">Pendientes</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" id="solventados-tab" data-toggle="tab" href="#solventados" role="tab" aria-controls="solventados" aria-selected="false">Solventados</a>
				</li>
			</ul>
			<hr style="border-top: 1px solid #b00606; margin: .5rem 0rem;">
			<div class="tab-content fact-table" id="myTabContent">
				<div class="tab-pane fade show active" id="pendientes" role="tabpanel" aria-labelledby="pendientes-tab">
					<div class="table-responsive">
						<table class="table table-striped table-hover" id="example0" style="width: 100%; border: 1px solid #e9ecef;">
							<thead class="thead-dark">
								<tr>
					              <th>N° Radicado</th>
					              <th>Fecha</th>
					              <th>N° Contrato</th>
					              <th>Cliente</th>
					              <th>N° Telefónico</th>
					              <th>Tipo Servicio</th>
					              <th>Dirección</th>
					              <th>Estatus</th>
					              <th>Acciones</th>
					          </tr>
							</thead>
							<tbody>
								@foreach($radicados as $radicado)
								    @if($radicado->estatus == 0 || $radicado->estatus == 2 )
										<tr @if($radicado->id==Session::get('radicado_id')) class="active_table" @endif>
											<td><a href="{{route('radicados.show',$radicado->id)}}">{{$radicado->codigo}}</a></td>
											<td>{{date('d-m-Y', strtotime($radicado->fecha))}}</td>
											<td>{{$radicado->contrato}}</td>
											<td>{{$radicado->nombre}}</td>
											<td>{{$radicado->telefono}}</td>
											<td>{{$radicado->nombre_servicio}}</td>
											<td>{{$radicado->direccion}}</td>
											<td>@if ($radicado->estatus == 0)
											    <span class="text-danger font-weight-bold">Pendiente</span>
											@endif
											@if ($radicado->estatus == 1)
											    <span class="text-success font-weight-bold">Resuelto</span>
											@endif
											@if ($radicado->estatus == 2)
											    <span class="text-danger font-weight-bold">Escalado / Pendiente</span>
											@endif
											@if ($radicado->estatus == 3)
											    <span class="text-success font-weight-bold">Escalado / Resuelto</span>
											@endif</td>
											<td>
												<a href="{{route('radicados.show',$radicado->id)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>
												@if(isset($_SESSION['permisos']['203']))
													<a href="{{route('radicados.edit',$radicado->id)}}" class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
												@endif
												@if($radicado->estatus==0)
													@if(isset($_SESSION['permisos']['204']))
														<form action="{{ route('radicados.destroy',$radicado->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-{{$radicado->id}}">
															{{ csrf_field() }}
															<input name="_method" type="hidden" value="DELETE">
														</form>
														<button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-{{$radicado->id}}', '¿Estas seguro que deseas eliminar el radicado?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
													@endif
													{{-- @if(isset($_SESSION['permisos']['205']))
														<form action="{{ route('radicados.escalar',$radicado->id) }}" method="POST" class="delete_form" style="display: none;" id="escalar-{{$radicado->id}}">
															{{ csrf_field() }}
														</form>
														<a href="#" onclick="confirmar('escalar-{{$radicado->id}}', '¿Está seguro de que desea escalar el caso?');" class="btn btn-outline-warning btn-icons" title="Escalar"><i class="fas fa-angle-double-right"></i></a>
													@endif --}}
												@endif

												@if($radicado->firma || $radicado->estatus==0)
												    @if(isset($_SESSION['permisos']['207']))
												        <form action="{{ route('radicados.solventar',$radicado->id) }}" method="POST" class="delete_form" style="display: none;" id="solventar-{{$radicado->id}}">
														{{ csrf_field() }}
													    </form>
													    <a href="#" onclick="confirmar('solventar-{{$radicado->id}}', '¿Está seguro de que desea solventar el caso?');" class="btn btn-outline-success btn-icons" title="Solventar"><i class="fas fa-check-double"></i></a>
													@endif
												@endif

												<a href="{{route('radicados.imprimir', ['id' => $radicado->id, 'name'=> 'Caso Radicado No. '.$radicado->codigo.'.pdf'])}}"  class="btn btn-outline-primary btn-icons" title="Imprimir" target="_blank"><i class="fas fa-print"></i></a>

												@if($radicado->estatus==2 && !$radicado->firma)
												    @if(isset($_SESSION['permisos']['209']))
													    <a href="{{route('radicados.firmar', $radicado->id)}}"  class="btn btn-outline-success btn-icons" title="Firmar" target="_blank"><i class="fas fa-file-signature"></i></a>
												    @endif
												@endif

												@if($radicado->tiempo_ini)
												    <form action="{{ route('radicados.proceder',$radicado->id) }}" method="POST" class="delete_form" style="display: none;" id="proceder{{$radicado->id}}">
												        {{ csrf_field() }}
												    </form>
												    <a href="#" onclick="confirmar('proceder{{$radicado->id}}', '¿Está seguro de que desea @if($radicado->tiempo_ini == null) iniciar @else finalizar @endif  el radicado?');" class="btn btn-outline-success btn-icons" title="@if($radicado->tiempo_ini == null) Iniciar @else Finalizar @endif Radicado"><i class="fas fa-check"></i></a>
												@endif
											</td>
										</tr>
									@endif
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
				<div class="tab-pane fade show" id="solventados" role="tabpanel" aria-labelledby="solventados-tab">
					<div class="table-responsive">
						<table class="table table-striped table-hover" id="example" style="width: 100%; border: 1px solid #e9ecef;">
							<thead class="thead-dark">
								<tr>
					              <th>N° Radicado</th>
					              <th>Fecha</th>
					              <th>N° Contrato</th>
					              <th>Cliente</th>
					              <th>N° Telefónico</th>
					              <th>Tipo Servicio</th>
					              <th>Dirección</th>
					              <th>Estatus</th>
					              <th>Acciones</th>
					          </tr>
							</thead>
							<tbody>
								@foreach($radicados as $radicado)
									@if($radicado->estatus == 1 || $radicado->estatus == 3 )
										<tr @if($radicado->id==Session::get('radicado_id')) class="active_table" @endif>
											<td><a href="{{route('radicados.show',$radicado->id)}}">{{$radicado->codigo}}</a></td>
											<td>{{date('d-m-Y', strtotime($radicado->fecha))}}</td>
											<td>{{$radicado->contrato}}</td>
											<td>{{$radicado->nombre}}</td>
											<td>{{$radicado->telefono}}</td>
											<td>{{$radicado->nombre_servicio}}</td>
											<td>{{$radicado->direccion}}</td>
											<td>@if ($radicado->estatus == 0)
											    <span class="text-danger font-weight-bold">Pendiente</span>
											@endif
											@if ($radicado->estatus == 1)
											    <span class="text-success font-weight-bold">Resuelto</span>
											@endif
											@if ($radicado->estatus == 2)
											    <span class="text-danger font-weight-bold">Escalado / Pendiente</span>
											@endif
											@if ($radicado->estatus == 3)
											    <span class="text-success font-weight-bold">Escalado / Resuelto</span>
											@endif</td>
											<td>
												<a href="{{route('radicados.show',$radicado->id)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>
												@if($radicado->estatus==0)
													@if(isset($_SESSION['permisos']['203']))
													<a href="{{route('radicados.edit',$radicado->id)}}" class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
													@endif
													@if(isset($_SESSION['permisos']['204']))
														<form action="{{ route('radicados.destroy',$radicado->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-{{$radicado->id}}">
															{{ csrf_field() }}
															<input name="_method" type="hidden" value="DELETE">
														</form>
														<button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-{{$radicado->id}}', '¿Estas seguro que deseas eliminar el radicado?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
													@endif
													@if(isset($_SESSION['permisos']['205']))
														<form action="{{ route('radicados.escalar',$radicado->id) }}" method="POST" class="delete_form" style="display: none;" id="escalar-{{$radicado->id}}">
															{{ csrf_field() }}
														</form>
														<a href="#" onclick="confirmar('escalar-{{$radicado->id}}', '¿Está seguro de que desea escalar el caso?');" class="btn btn-outline-warning btn-icons" title="Escalar"><i class="fas fa-angle-double-right"></i></a>
													@endif
												@endif

												@if($radicado->estatus==0 || $radicado->estatus==2)
												    @if(isset($_SESSION['permisos']['207']))
												        <form action="{{ route('radicados.solventar',$radicado->id) }}" method="POST" class="delete_form" style="display: none;" id="solventar-{{$radicado->id}}">
														{{ csrf_field() }}
													    </form>
													    <a href="#" onclick="confirmar('solventar-{{$radicado->id}}', '¿Está seguro de que desea solventar el caso?');" class="btn btn-outline-success btn-icons" title="Solventar"><i class="fas fa-check-double"></i></a>
													@endif
												@endif

												<a href="{{route('radicados.imprimir', ['id' => $radicado->id, 'name'=> 'Caso Radicado No. '.$radicado->codigo.'.pdf'])}}"  class="btn btn-outline-primary btn-icons" title="Imprimir" target="_blank"><i class="fas fa-print"></i></a>

												@if($radicado->estatus==2 && !$radicado->firma)
												    @if(isset($_SESSION['permisos']['209']))
													    <a href="{{route('radicados.firmar', $radicado->id)}}"  class="btn btn-outline-success btn-icons" title="Firmar" target="_blank"><i class="fas fa-file-signature"></i></a>
												    @endif
												@endif
											</td>
										</tr>
									@endif
								@endforeach
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<script>
		$(document).ready(function() {
		// Inicializa tu DataTable
		/*$('#example').DataTable({
			// Configura tus opciones de DataTables aquí
		});*/

		// Intercepta los mensajes de advertencia de DataTables
		$.fn.dataTable.ext.errMode = 'none';

		// Opcional: Maneja el evento 'error' de la tabla para realizar acciones adicionales
		/*$('#example').on('error.dt', function(e, settings, techNote, message) {
			// Aquí puedes registrar el error en la consola o manejarlo de otra forma
			console.log('DataTables error:', message);
			// Opcionalmente puedes ocultar cualquier mensaje de error visual que ya se haya mostrado
			$('.dataTables_wrapper .dataTables_info').hide();
		});*/
	});
	</script>
@endsection
