@extends('layouts.app')

@section('style')
<style>
    .stopwatch .controls {
        font-size: 12px;
    }
    .stopwatch .controls button{
        padding: 5px 15px;
        background :#EEE;
        border: 3px solid #06C;
        border-radius: 5px
    }
    .stopwatch .time {
        font-size: 2em;
    }
    .bg-th {
        background: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}!important;
        color: #fff!important;
    }
    .table .thead-dark th {
        color: #fff;
        background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
        border-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
    }
    .btn-dark {
	    background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
	    border-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
	}
	.btn-dark:hover, .btn-dark:active {
	    background-color: #113951;
	    border-color: #113951;
	}
    .nav-tabs .nav-link {
        font-size: 1em;
    }
    .nav-tabs .nav-link.active, .nav-tabs .nav-item.show .nav-link {
        background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
        color: #fff!important;
    }
    .table .thead-light th {
        color: #fff!important;
        background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}!important;
        border-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}!important;
    }
    .nav-pills .nav-link.active, .nav-pills .show > .nav-link {
        color: #fff!important;
        background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}!important;
    }
    .nav-pills .nav-link {
        font-weight: 700!important;
    }
    .nav-pills .nav-link{
        color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}!important;
        background-color: #f9f9f9!important;
        margin: 2px;
        border: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
        transition: 0.4s;
    }
    .nav-pills .nav-link:hover {
        color: #fff!important;
        background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}!important;
    }
</style>
@endsection

@section('boton')
    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
	        <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
	    </div>
	@else
        @if(isset($_SESSION['permisos']['5']))
	        <a href="{{route('contactos.create')}}" class="btn btn-outline-info btn-sm"><i class="fas fa-plus"></i> Nuevo Cliente</a>
	    @endif
	    @if(isset($_SESSION['permisos']['411']))
	        <a href="{{route('contratos.create')}}" class="btn btn-outline-info btn-sm"><i class="fas fa-plus"></i> Nuevo Contrato</a>
	    @endif
	    @if(isset($_SESSION['permisos']['202']))
	        <a href="{{route('radicados.create')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nuevo Radicado</a>
	    @endif
    @endif
@endsection

@section('content')
    @if(Session::has('success'))
        <div class="alert alert-success">
        	{{Session::get('success')}}
        </div>
        <script type="text/javascript">
        	setTimeout(function() {
        		$('.alert').hide();
        		$('.active_table').attr('class', ' ');
        	}, 5000);
        </script>
    @endif


    @if(Session::has('danger'))
        <div class="alert alert-danger">
        	{{Session::get('danger')}}
        </div>
        <script type="text/javascript">
        	setTimeout(function() {
        		$('.alert').hide();
        		$('.active_table').attr('class', ' ');
        	}, 5000);
        </script>
    @endif

    <div class="row card-description">
    	<div class="col-md-12">
    		<ul class="nav nav-pills" id="myTab" role="tablist">
    			<li class="nav-item">
    				<a class="nav-link active" id="sin_gestionar-tab" data-toggle="tab" href="#sin_gestionar" role="tab" aria-controls="sin_gestionar" aria-selected="true">PENDIENTES</a>
    			</li>
    			<li class="nav-item">
    				<a class="nav-link" id="gestionados-tab" data-toggle="tab" href="#gestionados" role="tab" aria-controls="gestionados" aria-selected="false">SOLVENTADOS</a>
    			</li>
    		</ul>
    		<hr style="border-top: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}; margin: .5rem 0rem;">
    		<div class="tab-content fact-table" id="myTabContent">
    			<div class="tab-pane fade show active" id="sin_gestionar" role="tabpanel" aria-labelledby="sin_gestionar-tab">
    			    <div class="text-right">
    			        <a href="javascript:getDataTable()" class="btn btn-success btn-sm my-1"><i class="fas fa-sync"></i>Actualizar</a>
    			        <a href="javascript:abrirFiltrador()" class="btn btn-info btn-sm my-1" id="boton-filtrar"><i class="fas fa-search"></i>Filtrar</a>
    			    </div>

    			    <div class="container-fluid d-none" id="form-filter">
    			    	<fieldset>
    			    		<legend>Filtro de Búsqueda</legend>
							<div class="card shadow-sm border-0">
								<div class="card-body pb-3 pt-2" style="background: #f9f9f9;">
									<div class="row">
										<div class="col-md-2 pl-1 pt-1">
											<input type="text" placeholder="Nro" id="codigo" class="form-control rounded">
										</div>
										<div class="col-md-2 pl-1 pt-1">
											<input type="text" placeholder="Fecha" id="fecha" name="fecha" class="form-control rounded creacion" autocomplete="off">
										</div>
										<div class="col-md-2 pl-1 pt-1">
											<input type="text" placeholder="Contrato" id="contrato" class="form-control rounded">
										</div>
										<div class="col-md-2 pl-1 pt-1">
											<select title="Cliente" class="form-control rounded selectpicker" id="cliente" data-size="5" data-live-search="true">
												@foreach ($clientes as $cliente)
													<option value="{{ $cliente->nombre}}">{{ $cliente->nombre}} {{$cliente->apellido1}} {{$cliente->apellido2}} - {{ $cliente->nit}}</option>
												@endforeach
											</select>
										</div>
										<div class="col-md-2 pl-1 pt-1">
											<input type="text" placeholder="Celular" id="telefono" class="form-control rounded">
										</div>
										<div class="col-md-2 pl-1 pt-1">
											<select title="Servicio" class="form-control rounded selectpicker" id="servicio" data-size="5" data-live-search="true">
												@foreach ($servicios as $servicio)
													<option value="{{ $servicio->id}}">{{ $servicio->nombre}}</option>
												@endforeach
											</select>
										</div>
										<div class="col-md-2 pl-1 pt-1">
											<select title="Estado" class="form-control rounded selectpicker" id="estatus">
												<option value="A">Pendiente</option>
												<option value="2">Escalado / Pendiente</option>
											</select>
										</div>
										<div class="col-md-2 pl-1 pt-1">
											<select title="Creado" class="form-control rounded selectpicker" id="creado">
												<option value="1">NetworkSoft</option>
												<option value="2">App</option>
											</select>
										</div>
										<div class="col-md-2 pl-1 pt-1">
											<select title="Prioridad" class="form-control rounded selectpicker" id="prioridad">
												<option value="1">Baja</option>
												<option value="2">Media</option>
												<option value="3">Alta</option>
											</select>
										</div>
										<div class="col-md-2 pl-1 pt-1">
											<select title="Técnico" class="form-control rounded selectpicker" id="tecnico" data-size="5" data-live-search="true">
												@foreach ($tecnicos as $tecnico)
													<option value="{{ $tecnico->id}}">{{ $tecnico->nombres}}</option>
												@endforeach
											</select>
										</div>
										<div class="col-md-2 pl-1 pt-1">
											<select title="Creado por" class="form-control rounded selectpicker" id="responsable" data-size="5" data-live-search="true">
												@foreach ($responsables as $responsable)
													<option value="{{ $responsable->id}}">{{ $responsable->nombres}}</option>
												@endforeach
											</select>
										</div>

										<div class="col-md-2 pl-1 pt-1">
											<a href="javascript:cerrarFiltrador()" class="btn btn-icons ml-1 btn-outline-danger rounded btn-sm p-1 float-right" title="Limpiar parámetros de busqueda"><i class="fas fa-times"></i></a>
											<a href="javascript:void(0)" id="filtrar" class="btn btn-icons btn-outline-info rounded btn-sm p-1 float-right" title="Iniciar busqueda avanzada"><i class="fas fa-search"></i></a>
										</div>
									</div>
								</div>
							</div>
						</fieldset>
					</div>

    				<div class="table-responsive mt-3">
    				    <table class="table table-striped table-hover w-100" id="table_sin_gestionar">
    				        <thead class="thead-dark">
								<tr>
									@foreach($tabla as $campo)
									    <th>{{$campo->nombre}}</th>
									@endforeach
									<th>Acciones</th>
								</tr>
							</thead>
    				    </table>
				    </div>
    			</div>
    			<div class="tab-pane fade" id="gestionados" role="tabpanel" aria-labelledby="gestionados-tab">
    			    <div class="text-right">
    			        <a href="javascript:getDataTableG()" class="btn btn-success btn-sm my-1"><i class="fas fa-sync"></i>Actualizar</a>
    			        <a href="javascript:abrirFiltradorG()" class="btn btn-info btn-sm my-1" id="boton-filtrarG"><i class="fas fa-search"></i>Filtrar</a>
    			    </div>

    			    <div class="container-fluid d-none" id="form-filterG">
    			    	<fieldset>
    			    		<legend>Filtro de Búsqueda</legend>
							<div class="card shadow-sm border-0">
								<div class="card-body pb-3 pt-2" style="background: #f9f9f9;">
									<div class="row">
										<div class="col-md-2 pl-1 pt-1">
											<input type="text" placeholder="Nro" id="codigoG" class="form-control rounded">
										</div>
										<div class="col-md-2 pl-1 pt-1">
											<input type="text" placeholder="Fecha" id="fechaG" name="fecha" class="form-control rounded creacion" autocomplete="off">
										</div>
										<div class="col-md-2 pl-1 pt-1">
											<input type="text" placeholder="Contrato" id="contratoG" class="form-control rounded">
										</div>
										<div class="col-md-2 pl-1 pt-1">
											<select title="Cliente" class="form-control rounded selectpicker" id="clienteG" data-size="5" data-live-search="true">
												@foreach ($clientes as $cliente)
													<option value="{{ $cliente->nombre}}">{{ $cliente->nombre}} {{$cliente->apellido1}} {{$cliente->apellido2}} - {{ $cliente->nit}}</option>
												@endforeach
											</select>
										</div>
										<div class="col-md-2 pl-1 pt-1">
											<input type="text" placeholder="Celular" id="telefonoG" class="form-control rounded">
										</div>
										<div class="col-md-2 pl-1 pt-1">
											<select title="Servicio" class="form-control rounded selectpicker" id="servicioG" data-size="5" data-live-search="true">
												@foreach ($servicios as $servicio)
													<option value="{{ $servicio->id}}">{{ $servicio->nombre}}</option>
												@endforeach
											</select>
										</div>
										<div class="col-md-2 pl-1 pt-1">
											<select title="Estado" class="form-control rounded selectpicker" id="estatusG">
												<option value="1">Solventado</option>
												<option value="3">Escalado / Solventado</option>
											</select>
										</div>
										<div class="col-md-2 pl-1 pt-1">
											<select title="Creado" class="form-control rounded selectpicker" id="creadoG">
												<option value="1">NetworkSoft</option>
												<option value="2">App</option>
											</select>
										</div>
										<div class="col-md-2 pl-1 pt-1">
											<select title="Prioridad" class="form-control rounded selectpicker" id="prioridadG">
												<option value="1">Baja</option>
												<option value="2">Media</option>
												<option value="3">Alta</option>
											</select>
										</div>
										<div class="col-md-2 pl-1 pt-1">
											<select title="Técnico" class="form-control rounded selectpicker" id="tecnicoG" data-size="5" data-live-search="true">
												@foreach ($tecnicos as $tecnico)
													<option value="{{ $tecnico->id}}">{{ $tecnico->nombres}}</option>
												@endforeach
											</select>
										</div>
										<div class="col-md-2 pl-1 pt-1">
											<select title="Creado por" class="form-control rounded selectpicker" id="responsableG" data-size="5" data-live-search="true">
												@foreach ($responsables as $responsable)
													<option value="{{ $responsable->id}}">{{ $responsable->nombres}}</option>
												@endforeach
											</select>
										</div>

										<div class="col-md-2 pl-1 pt-1">
											<a href="javascript:cerrarFiltradorG()" class="btn btn-icons ml-1 btn-outline-danger rounded btn-sm p-1 float-right" title="Limpiar parámetros de busqueda"><i class="fas fa-times"></i></a>
											<a href="javascript:void(0)" id="filtrarG" class="btn btn-icons btn-outline-info rounded btn-sm p-1 float-right" title="Iniciar busqueda avanzada"><i class="fas fa-search"></i></a>
										</div>
									</div>
								</div>
							</div>
						</fieldset>
					</div>

    				<div class="table-responsive mt-3">
    				    <table class="table table-striped table-hover w-100" id="table_sin_gestionarG">
    				        <thead class="thead-dark">
								<tr>
									@foreach($tabla as $campo)
									    <th>{{$campo->nombre}}</th>
									@endforeach
									<th>Acciones</th>
								</tr>
							</thead>
    				    </table>
				    </div>
    			</div>
			</div>
    	</div>
    </div>
@endsection

@section('scripts')
<script>
	var tabla = null;
	window.addEventListener('load', function() {
		$('#table_sin_gestionar').DataTable({
			responsive: true,
			serverSide: true,
			processing: true,
			searching: false,
			language: {
				'url': '/vendors/DataTables/es.json'
			},
			order: [
				[1, "desc"]
			],
			"pageLength": {{ Auth::user()->empresa()->pageLength }},
			ajax: '{{url("/radicados/0")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
			columns: [
				@foreach($tabla as $campo)
                {data: '{{$campo->campo}}'},
                @endforeach
				{data: 'acciones'}
			]
		});

		tabla = $('#table_sin_gestionar');

		tabla.on('preXhr.dt', function(e, settings, data) {
			data.codigo      = $('#codigo').val();
			data.fecha       = $('#fecha').val();
			data.contrato    = $('#contrato').val();
			data.cliente     = $('#cliente').val();
			data.telefono    = $('#telefono').val();
			data.servicio    = $('#servicio').val();
			data.direccion   = $('#direccion').val();
			data.estatus     = $('#estatus').val();
			data.creado      = $('#creado').val();
			data.prioridad   = $('#prioridad').val();
			data.tecnico     = $('#tecnico').val();
			data.responsable = $('#responsable').val();
			data.filtro      = true;
		});

		$('#filtrar').on('click', function(e) {
			getDataTable();
			return false;
		});

		$('#form-filter').on('keypress', function(e) {
			if (e.which == 13) {
				getDataTable();
				return false;
			}
		});

		$('.fecha').datepicker({
			locale: 'es-es',
      		uiLibrary: 'bootstrap4',
			format: 'yyyy-mm-dd' ,
		});

		///////////////////////////////////////////

		$('#table_sin_gestionarG').DataTable({
			responsive: true,
			serverSide: true,
			processing: true,
			searching: false,
			language: {
				'url': '/vendors/DataTables/es.json'
			},
			order: [
				[1, "desc"]
			],
			"pageLength": {{ Auth::user()->empresa()->pageLength }},
			ajax: '{{url("/radicados/1")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
			columns: [
				@foreach($tabla as $campo)
                {data: '{{$campo->campo}}'},
                @endforeach
				{data: 'acciones'}
			]
		});

		tablaG = $('#table_sin_gestionarG');

		tablaG.on('preXhr.dt', function(e, settings, data) {
			data.codigo      = $('#codigoG').val();
			data.fecha       = $('#fechaG').val();
			data.contrato    = $('#contratoG').val();
			data.cliente     = $('#clienteG').val();
			data.telefono    = $('#telefonoG').val();
			data.servicio    = $('#servicioG').val();
			data.direccion   = $('#direccionG').val();
			data.estatus     = $('#estatusG').val();
			data.creado      = $('#creadoG').val();
			data.prioridad   = $('#prioridadG').val();
			data.tecnico     = $('#tecnicoG').val();
			data.responsable = $('#responsableG').val();
			data.filtro      = true;
		});

		$('#filtrarG').on('click', function(e) {
			getDataTableG();
			return false;
		});

		$('#form-filterG').on('keypress', function(e) {
			if (e.which == 13) {
				getDataTableG();
				return false;
			}
		});

		$('.fecha').datepicker({
			locale: 'es-es',
      		uiLibrary: 'bootstrap4',
			format: 'yyyy-mm-dd' ,
		});
	});

	function getDataTable() {
		tabla.DataTable().ajax.reload();
	}

	function abrirFiltrador() {
		if ($('#form-filter').hasClass('d-none')) {
			$('#boton-filtrar').html('<i class="fas fa-times"></i> Cerrar');
			$('#form-filter').removeClass('d-none');
		} else {
			$('#boton-filtrar').html('<i class="fas fa-search"></i> Filtrar');
			cerrarFiltrador();
		}
	}

	function cerrarFiltrador() {
		$('#codigo').val('');
		$('#fecha').val('');
		$('#contrato').val('');
		$('#cliente').val('').selectpicker('refresh');
		$('#telefono').val('');
		$('#servicio').val('').selectpicker('refresh');
		$('#direccion').val('');
		$('#estatus').val('').selectpicker('refresh');
		$('#creado').val('').selectpicker('refresh');
		$('#prioridad').val('').selectpicker('refresh');
		$('#tecnico').val('').selectpicker('refresh');
		$('#responsable').val('').selectpicker('refresh');
		$('#form-filter').addClass('d-none');
		$('#boton-filtrar').html('<i class="fas fa-search"></i> Filtrar');
		getDataTable();
	}

	///////////////////////

	function getDataTableG() {
		tablaG.DataTable().ajax.reload();
	}

	function abrirFiltradorG() {
		if ($('#form-filterG').hasClass('d-none')) {
			$('#boton-filtrarG').html('<i class="fas fa-times"></i> Cerrar');
			$('#form-filterG').removeClass('d-none');
		} else {
			$('#boton-filtrarG').html('<i class="fas fa-search"></i> Filtrar');
			cerrarFiltradorG();
		}
	}

	function cerrarFiltradorG() {
		$('#codigoG').val('');
		$('#fechaG').val('');
		$('#contratoG').val('');
		$('#clienteG').val('').selectpicker('refresh');
		$('#telefonoG').val('');
		$('#servicioG').val('').selectpicker('refresh');
		$('#direccionG').val('');
		$('#estatusG').val('').selectpicker('refresh');
		$('#creadoG').val('').selectpicker('refresh');
		$('#prioridadG').val('').selectpicker('refresh');
		$('#tecnicoG').val('').selectpicker('refresh');
		$('#responsableG').val('').selectpicker('refresh');
		$('#form-filterG').addClass('d-none');
		$('#boton-filtrarG').html('<i class="fas fa-search"></i> Filtrar');
		getDataTableG();
	}
</script>
@endsection