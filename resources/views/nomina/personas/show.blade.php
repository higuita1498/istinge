@extends('layouts.app')

@section('content')

<style>
	#detalles>div>.card-persona,
	#tabla_detalles>div>.card-persona {
		border: 0px;
		box-shadow: 0 .5rem 1rem rgba(0, 0, 0, .15) !important;
		border-radius: 15px;
		padding: 15px 0 20px 0;
	}

	#detalles>div>div>div.card-header {
		border-bottom: 0px;
		background: #fff;
		font-weight: bold;
	}

	#detalles>div>div>ul>li {
		border: 0px;
	}

	#detalles>div>div>ul>li:hover {
		background-color: #fff;
	}

	#detalles>div>div>ul>li>i {
		font-size: 5em;
		margin-bottom: 10px;
		font-weight: bold;
	}

	#detalles>div>div>ul>li>span {
		font-size: 1.5em;
		font-weight: bold;
	}

	#detalles>div>.list-group-flush:last-child .list-group-item:last-child {
		border-bottom: 0;
		border-radius: 15px;
	}

	#detalles>div>.table td,
	.table th {
		padding: 13px 0px 13px 50px !important;
		width: 50%;
	}

	#detalles>div>.table>tbody>tr>td {
		padding-left: 0px !important;
		font-weight: bold;
	}

	#detalles>div>div>div.card-header>div>div>div>a {
		font-size: 13px;
	}

	th {
		font-weight: 400 !important;
	}

	.text-gestor {
		color: #d08f50;
	}

	#table-show-empleados>thead>tr>th {
		padding: 13px 10px !important;
		width: 16% !important;
	}

	.nav-tabs {
		border-bottom: 0px;
	}

	.tab-content {
		margin-top: 0;
	}

	.nav-tabs .nav-link.active,
	.nav-tabs .nav-item.show .nav-link {
		color: #333;
		background: #e9ecef;
	}

	.nav-link {
		padding: 0.75rem 1rem;
	}

	.color {
		color: #d08f50;
		background: #e9ecef;
		font-weight: bold;
		padding: 5px;
		border-radius: 5px;
		border: solid 1px #dbdbdb;
	}

	.color:hover {
		border: solid 1px #d08f50;
	}
</style>

@if(Session::has('error'))
<div class="alert alert-danger">
	{{Session::get('error')}}
</div>
<script type="text/javascript">
	setTimeout(function() {
		$('.alert').hide();
	}, 5000);
</script>
@endif

<div class="row card-description" id="detalles">
	@if($modoLectura->success)
	<div class="alert alert-warning alert-dismissible fade show" role="alert">
		<a>{{ $modoLectura->message }}, si deseas seguir disfrutando de nuestros servicios adquiere alguno de nuestros planes <a class="text-black" href="{{route('nomina.planes')}}"> <b>Click Aquí.</b></a></a>
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
	@endif
	<div class="col-md-8">
		<div class="card-persona">
			<div class="card-header">
				<div class="row">
					<div class="col-md-6">
						<div class="text-left">Datos Básicos</div>
					</div>
					<div class="col-md-6">
						@if (!$modoLectura->success)
						@if(isset($_SESSION['permisos']['784']))
						<div class="text-right"><a href="{{route('personas.edit', $persona->id)}}" class=""><i class="fas fa-edit"></i> Editar</a></div>
						@endif
						@endif
					</div>
				</div>
			</div>
			<div class="table-responsive">
				<table class="table table-borderless">
					<tbody>
						<tr>
							<th scope="row">Nombre completo</th>
							<td>{{ $persona->nombre() }}</td>
						</tr>
						<tr>
							<th scope="row">{{ $persona->tipo_documento('completa') }}</th>
							<td>{{ $persona->nro_documento }}</td>
						</tr>
						<tr>
							<th scope="row">Correo el electrónico</th>
							<td>@if($persona->correo) {{ $persona->correo }} @else <span class="text-gestor font-weight-bold">Pendiente</span> @endif</td>
						</tr>
						<tr>
							<th scope="row">Tipo de contrato</th>
							<td>{{ $persona->nomina_tipo_contrato->nombre }}</td>
						</tr>
						<tr>
							<th scope="row">Término de contrato</th>
							<td>{{ $persona->terminoContrato->nombre }}</td>
						</tr>
						<tr>
							<th scope="row">Fecha de contratación</th>
							<td>{{ date('d-m-Y', strtotime($persona->fecha_contratacion)) }}</td>
						</tr>
						@if($persona->terminoContrato->id > 1)
						<tr>
							<th scope="row">Fecha de Finalización de Contrato</th>
							<td>{{ date('d-m-Y', strtotime($persona->fecha_finalizacion)) }}</td>
						</tr>
						@endif
						<tr>
							<th scope="row">Clase de riesgo (ARL)</th>
							<td>@if($persona->fk_clase_riesgo) {{ $persona->clase_riesgo() }} @else <span class="text-gestor font-weight-bold">Pendiente</span> @endif</td>
						</tr>
						<tr>
							<th scope="row">Días de descanso</th>
							<td>@if($persona->dias_descanso) {{ $persona->dias_descanso }} @else <span class="text-gestor font-weight-bold">Pendiente</span> @endif</td>
						</tr>
						<tr>
							<th scope="row">Vacaciones generadas</th>
							<td>{{$vacAcumuladas}} días</td>
						</tr>
						<tr>
							<th scope="row">¿Subsidio de transporte?</th>
							<td>@if($persona->subsidio == null) <span class="text-gestor font-weight-bold">Pendiente</span> @else {{ $persona->subsidio() }} @endif</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div class="card-persona mt-4">
			<div class="card-header">
				<div class="row">
					<div class="col-md-6">
						<div class="text-left">Datos Puesto de Trabajo</div>
					</div>
					<div class="col-md-6">
						@if (!$modoLectura->success)
						@if(isset($_SESSION['permisos']['784']))
						<div class="text-right"><a href="{{route('personas.edit', $persona->id)}}" class=""><i class="fas fa-edit"></i> Editar</a></div>
						@endif
						@endif
					</div>
				</div>
			</div>
			<div class="table-responsive">
				<table class="table table-borderless">
					<tbody>
						<tr>
							<th scope="row">Sede de trabajo</th>
							<td>{{ $persona->sede()->nombre }}</td>
						</tr>
						<tr>
							<th scope="row">Área</th>
							<td>{{ $persona->area()->nombre }}</td>
						</tr>
						<tr>
							<th scope="row">Cargo</th>
							<td>{{ $persona->cargo()->nombre }}</td>
						</tr>
						<tr>
							<th scope="row">Centro de costos</th>
							<td>{{ $persona->centro_costo()->nombre }}</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div class="card-persona mt-4">
			<div class="card-header">
				<div class="row">
					<div class="col-md-6">
						<div class="text-left">Datos Personales</div>
					</div>
					<div class="col-md-6">
						@if (!$modoLectura->success)
						@if(isset($_SESSION['permisos']['784']))
						<div class="text-right"><a href="{{route('personas.edit', $persona->id)}}" class=""><i class="fas fa-edit"></i> Editar</a></div>
						@endif
						@endif
					</div>
				</div>
			</div>
			<div class="table-responsive">
				<table class="table table-borderless">
					<tbody>
						<tr>
							<th scope="row">Fecha de nacimiento</th>
							<td>@if($persona->nacimiento) {{ date('d-m-Y', strtotime($persona->nacimiento)) }} @else <span class="text-gestor font-weight-bold">Pendiente</span> @endif</td>
						</tr>
						<tr>
							<th scope="row">Dirección hogar</th>
							<td>@if($persona->direccion) {{ $persona->direccion }} @else <span class="text-gestor font-weight-bold">Pendiente</span> @endif</td>
						</tr>
						<tr>
							<th scope="row">Número de teléfono</th>
							<td>@if($persona->nro_celular) {{ $persona->nro_celular }} @else <span class="text-gestor font-weight-bold">Pendiente</span> @endif</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div class="card-persona mt-4">
			<div class="card-header">
				<div class="row">
					<div class="col-md-6">
						<div class="text-left">Datos de Pago</div>
					</div>
					<div class="col-md-6">
						@if (!$modoLectura->success)
						@if(isset($_SESSION['permisos']['784']))
						<div class="text-right"><a href="{{route('personas.edit', $persona->id)}}" class=""><i class="fas fa-edit"></i> Editar</a></div>
						@endif
						@endif
					</div>
				</div>
			</div>
			<div class="table-responsive">
				<table class="table table-borderless">
					<tbody>
						<tr>
							<th scope="row">Medio de pago</th>
							<td>@if($persona->fk_metodo_pago) {{ $persona->metodo_pago() }} @else <span class="text-gestor font-weight-bold">Pendiente</span> @endif</td>
						</tr>
						@if($persona->fk_metodo_pago=='3')
						<tr>
							<th scope="row">Banco</th>
							<td>@if($persona->fk_banco) {{ $persona->banco() }} @else <span class="text-gestor font-weight-bold">Pendiente</span> @endif</td>
						</tr>
						<tr>
							<th scope="row">Tipo de cuenta</th>
							<td>@if($persona->tipo_cuenta) {{ $persona->tipo_cuenta() }} @else <span class="text-gestor font-weight-bold">Pendiente</span> @endif</td>
						</tr>
						<tr>
							<th scope="row">Número de cuenta</th>
							<td>@if($persona->nro_cuenta) {{ $persona->nro_cuenta }} @else <span class="text-gestor font-weight-bold">Pendiente</span> @endif</td>
						</tr>
						@endif
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<div class="col-md-4">
		<div class="card-persona">
			<div class="card-header text-center">

			</div>
			<ul class="list-group list-group-flush">
				<li class="list-group-item text-center"><i class="fas fa-umbrella-beach"></i><br>Vacaciones<br><span>{{$vacPendientes}} días pendientes</span</li>
			</ul>
		</div>
		<div class="card-persona mt-4">
			<div class="card-header">
				<div class="row">
					<div class="col-md-4">
						<div class="text-left">Salario</div>
					</div>
					<div class="col-md-4">
						<div class="text-left">{{ $moneda }} {{ App\Funcion::Parsear($persona->valor) }}</div>
					</div>
					<div class="col-md-4">
						@if (!$modoLectura->success)
						@if(isset($_SESSION['permisos']['784']))
						<div class="text-right"><a href="{{route('personas.edit', $persona->id)}}" class=""><i class="fas fa-edit"></i> Editar</a></div>
						@endif
						@endif
					</div>
				</div>
			</div>
			<div class="card-persona">
				<div class="card-header text-center">

				</div>
				@if(!$persona->is_liquidado)
				<ul class="list-group list-group-flush">
					<li class="list-group-item text-center"><i class="fas fa-door-closed"></i><a href="{{ route('nomina.liquidar.persona', $persona->id) }}" style="font-weight: bold;"><br>Liquidar persona<br></a><span></span></li>
				</ul>
				@else
				<ul class="list-group list-group-flush">
					<li class="list-group-item text-center"><i class="fas fa-door-open"></i><a href="{{ route('nomina.reincorporar.persona', $persona->id) }}" style="font-weight: bold;"><br>Reincorporar<br></a><span></span></li>
				</ul>
				@if($ultimaLiquidacion = $persona->ultimaLiquidacion())
				<div class="dropdown text-center">
					<button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-toggle="dropdown" aria-expanded="false">
						LIQUIDACION
					</button>
					<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
						<li><a class="dropdown-item" href="{{ route('nomina.liquidar.destroy', $ultimaLiquidacion) }}">Eliminar liquidacion</a></li>
						<li><a class="dropdown-item" href="{{ route('nomina.liquidar.edit', $ultimaLiquidacion) }}">Editar liquidacion</a></li>
						<li><a class="dropdown-item" href="{{ route('nomina.imprimir.comprobanteLiquidacion', $ultimaLiquidacion) }}">Imprimir liquidacion</a></li>
						<li><a class="dropdown-item" href="{{ route('nomina.imprimir.comprobanteLiquidacion', ['idContrato' => $ultimaLiquidacion, 'nomina' => 'si']) }}">Imprimir con nomina</a></li>
					</ul>
				</div>
				@endif

				@endif
			</div>
			@if($persona->contratos->count() > 0)
			<div class="card-persona">
				<div class="card-header text-center">

				</div>
				<ul class="list-group list-group-flush">
					<li class="list-group-item text-center"><i class="fas fa-file"></i><a href="#" style="font-weight: bold;" data-toggle="modal" data-target="#historial-contratos"><br>HISTORIAL DE CONTRATOS<br></a><span></span></li>
				</ul>


				<!-- Modal -->
				<div class="modal fade" id="historial-contratos" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
					<div class="modal-dialog">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title" id="exampleModalLabel">CONTRATOS ANTERIORES</h5>
								<button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
							</div>
							<div class="modal-body">
								<div class="card">
									<ul class="list-group list-group-flush">
										@foreach($persona->contratos as $contrato)
										<li class="list-group-item">
											<p style="margin-bottom:0.5px">Fecha inicio:{{$contrato->fecha_contratacion}} - Terminacion: {{$contrato->comprobanteLiquidacion->fecha_terminacion}}</p>
											<p style="margin-bottom:0.5px"><strong>Motivo liquidacion:</strong> {{$contrato->comprobanteLiquidacion->motivo}}</p>
											<p>Tipo contrato: {{$contrato->nomina_tipo_contrato->nombre ?? ''}}</p>
											<p>Clase riesgo: {{$contrato->clase_riesgo()}}</p>
											<p><a target="_blank" href="{{ route('nomina.imprimir.comprobanteLiquidacion', $contrato->id) }}">Imprimir liquidacion</a></p>
										</li>
										@endforeach
									</ul>
								</div>

							</div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
							</div>
						</div>
					</div>
				</div>

			</div>
			@endif
		</div>
	</div>
</div>

<div class="row">

	<div class="col-12 px-5 py-3">
		<div class="row card-description">
			<div class="col-md-12">
				<ul class="nav nav-tabs" id="myTab" role="tablist">
					<li class="nav-item" role="presentation">
						<a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Nóminas Electrónicas</a>
					</li>
					<li class="nav-item" role="presentation">
						<a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Historial de pagos</a>
					</li>
				</ul>
				<div class="tab-content" id="myTabContent">
					<div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
						<div class="table-responsive">
							<table class="table table-light table-striped table-hover" id="table-show-empleados" style="width: 100%; border: 1px solid #e9ecef;">
								<thead class="thead-light">
									<tr>
										<th class="align-middle font-weight-bold">Periodos</th>
										<th class="font-weight-bold">Horas extras y<br>recargos</th>
										<th class="font-weight-bold">Vacaciones,<br>Incap y Lic</th>
										<th class="font-weight-bold">Ingresos<br>adicionales</th>
										<th class="font-weight-bold">Deducc, prést y<br>ReteFuen</th>
										<th class="font-weight-bold">Pago<br>empleado</th>
										<th class="align-middle font-weight-bold">Estado</th>
										<th class="align-middle font-weight-bold">Acciones</th>
									</tr>
								</thead>
								<tbody>
									@php $i = 0; @endphp
									@foreach ($detalles as $detalle)
									<tr>
										<td>{{ $detalles[$i]['periodo'] }}</td>
										<td>{{ $detalles[$i]['extras'] }}</td>
										<td>{{ $detalles[$i]['vacaciones'] }}</td>
										<td>{{ $moneda }} {{ App\Funcion::Parsear($detalles[$i]['ingresos']) }}</td>
										<td>{{ $moneda }} {{ App\Funcion::Parsear($detalles[$i]['deducciones']) }}</td>
										<td>{{ $moneda }} {{ App\Funcion::Parsear($detalles[$i]['total']) }}</td>
										<td><span class="text-{{ $detalles[$i]['text'] }}">{{ $detalles[$i]['estado'] }}</span></td>
										<td>
											@if ($detalles[$i]['estado'] === 'No emitida')
											<a href="javascript:void(0)" title="La nómina aún no ha sido emitida"><i class="far fa-print"></i></a>
											@else
											<a href="{{ route('nominaCompleta.pdf', $detalles[$i]['nomina_id']) }}" target="_blank" title="Imprimir"><i class="far fa-print color"></i></a>
											@endif
											<a href="{{route('nomina.calculosCompleto',$detalles[$i]['nomina_id'])}} title="Ver"><i class="far fa-eye color"></i></a>
										</td>
									</tr>
									@php $i++; @endphp
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
					<div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
						<div class="table-responsive">
							<table class="table table-light table-striped table-hover" id="table-show-empleados" style="width: 100%; border: 1px solid #e9ecef;">
								<thead class="thead-light">
									<tr>
										<th class="align-middle font-weight-bold">Periodo</th>
										<th class="font-weight-bold">Salarios</th>
										<th class="font-weight-bold">Otros Ingresos</th>
										<th class="font-weight-bold">Deducciones y <br> Retenciones</th>
										<th class="font-weight-bold">Pago Neto</th>
									</tr>
								</thead>
								<tbody>
									@foreach ($nominas as $nomina)

									@forelse ($nomina->nominaperiodos as $periodo)
									@if ($nomina->isPagado && $periodo->isPagado)
									<tr>
										<td>
											<a href="{{ route('nomina.liquidar', ['periodo' => $nomina->periodo, 'year'=> $nomina->year, 'editar' => true, 'tipo' => $periodo->periodo]) }}" target="_blank">
												{{ $periodo->fecha_desde->format('d') }}-{{ $periodo->fecha_hasta->format('d') }}/{{ ucfirst(App\Model\Nomina\Nomina::monthName($periodo->fecha_desde)) }}/{{ $periodo->fecha_desde->format('Y') }}
											</a>
										</td>
										@php
										$totalidad = $periodo->resumenTotal();
										@endphp
										<td>{{ $moneda }} {{ number_format($totalidad['pago']['salario']) ?? 0 }}</td>
										<td>{{ $moneda }} {{ number_format($totalidad['pago']['ingresosAdicionales']) ?? 0 }}</td>
										<td>{{ $moneda }} {{ number_format($totalidad['pago']['retencionesDeducciones']) ?? 0 }}</td>
										<td>{{ $moneda }} {{ number_format($totalidad['pago']['total']) ?? 0 }}</td>
									</tr>
									@endif

									@empty
									@endforelse

									@endforeach
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>


@endsection

@section('scripts')
<script>
	$(document).ready(function() {
		$('#table-show-empleados').DataTable({
			"language": {
				"zeroRecords": "Disculpe, No existen registros",
				"info": "",
				"infoEmpty": " ",
				"infoFiltered": "(Filtrado de _MAX_ total entradas)",
				"infoPostFix": "",
				"decimal": ",",
				"thousands": ".",
				"lengthMenu": "",
				"loadingRecords": "Cargando...",
				"processing": "Procesando...",
				"search": "Buscar:",
				"zeroRecords": "Sin resultados encontrados",
			},
			"paging": false,
			"searching": true,
			"order": [
				[0, "desc"]
			],
		});
	});
</script>
@endsection
