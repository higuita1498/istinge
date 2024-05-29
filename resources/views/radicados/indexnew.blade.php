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
    .btn-group {
    	border: 0;
    	border-radius: 0;
    }
</style>
@endsection

@section('boton')
    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">Integra Colombia: Suscripción Vencida</h4>
	       <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
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
    			    	@if(isset($_SESSION['permisos']['841']))
    			    	@if(auth()->user()->modo_lectura())
    			    	@else
	    			    <div class="btn-group dropdown">
	    			    	@if(isset($_SESSION['permisos']['750']))
	    			    	<a href="{{route('campos.organizar', 12)}}" class="btn btn-warning mr-1"><i class="fas fa-table"></i> Organizar Tabla</a>
	    			    	@endif
	    			    	<button class="btn btn-warning dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	    			    		Acciones en Lote
	    			    	</button>
	    			    	<div class="dropdown-menu">
	    			    		<a class="dropdown-item" href="javascript:void(0)" id="btn_solventar"><i class="fas fa-check-double fa-fw" style="margin-left:4px; "></i> Solventar Casos</a>
	    			    		<a class="dropdown-item" href="javascript:void(0)" id="btn_destroy"><i class="fas fa-times fa-fw" style="margin-left:4px; "></i> Eliminar Casos</a>
	    			    	</div>
	    			    </div>
	    			    @endif
	    			    @endif

    			    	<div class="btn-group">
    			    		<a href="javascript:getDataTable()" class="btn btn-success btn-sm my-1"><i class="fas fa-sync"></i>Actualizar</a>
    			    		<a href="javascript:abrirFiltrador()" class="btn btn-info btn-sm my-1" id="boton-filtrar"><i class="fas fa-search"></i>Filtrar</a>
    			    	</div>
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
													<option value="{{ $cliente->nit }}">{{ $cliente->nombre}} {{$cliente->apellido1}} {{$cliente->apellido2}} - {{ $cliente->nit}}</option>
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
											<input type="text" placeholder="Fecha Fin" id="tiempo_fin" name="tiempo_fin" class="form-control rounded creacion" autocomplete="off">
										</div>
										<div class="col-md-3 pl-1 pt-1">
											<input type="text" placeholder="Direccion" id="direccion" name="direccion" class="form-control rounded" autocomplete="off">
										</div>
									</div>
									<div class="row">
										<div class="col-md-12 pl-1 pt-1 text-center">
											<a href="javascript:cerrarFiltrador()" class="btn btn-icons ml-1 btn-outline-danger rounded btn-sm p-1" title="Limpiar parámetros de busqueda"><i class="fas fa-times"></i></a>
											<a href="javascript:void(0)" id="filtrar" class="btn btn-icons btn-outline-info rounded btn-sm p-1" title="Iniciar busqueda avanzada"><i class="fas fa-search"></i></a>
											@if(isset($_SESSION['permisos']['831']))
											<a href="javascript:exportar('0');" class="btn btn-icons mr-1 btn-outline-success rounded btn-sm p-1" title="Exportar"><i class="fas fa-file-excel"></i></a>
											@endif
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
    			    	@if(isset($_SESSION['permisos']['841']))
    			    	@if(auth()->user()->modo_lectura())
    			    	@else
	    			    <div class="btn-group dropdown">
	    			    	<button class="btn btn-warning dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	    			    		Acciones en Lote
	    			    	</button>
	    			    	<div class="dropdown-menu">
	    			    		<a class="dropdown-item" href="javascript:void(0)" id="btn_reabrir"><i class="fas fa-check-double fa-fw" style="margin-left:4px; "></i> Reabrir Casos</a>
	    			    	</div>
	    			    </div>
	    			    @endif
	    			    @endif
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
													<option value="{{ $cliente->nit }}">{{ $cliente->nombre}} {{$cliente->apellido1}} {{$cliente->apellido2}} - {{ $cliente->nit}}</option>
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
											<input type="text" placeholder="Fecha Fin" id="tiempo_finG" name="tiempo_finG" class="form-control rounded creacion" autocomplete="off">
										</div>
										<div class="col-md-3 pl-1 pt-1">
											<input type="text" placeholder="Direccion" id="direccionG" name="direccionG" class="form-control rounded" autocomplete="off">
										</div>
									</div>
									<div class="row">
										<div class="col-md-12 pl-1 pt-1 text-center">
											<a href="javascript:cerrarFiltradorG()" class="btn btn-icons ml-1 btn-outline-danger rounded btn-sm p-1" title="Limpiar parámetros de busqueda"><i class="fas fa-times"></i></a>
											<a href="javascript:void(0)" id="filtrarG" class="btn btn-icons btn-outline-info rounded btn-sm p-1" title="Iniciar busqueda avanzada"><i class="fas fa-search"></i></a>
											@if(isset($_SESSION['permisos']['831']))
											<a href="javascript:exportar('1');" class="btn btn-icons mr-1 btn-outline-success rounded btn-sm p-1" title="Exportar"><i class="fas fa-file-excel"></i></a>
											@endif
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
	var tablaG = null;

		$(document).ready(function() {
		// Inicializa tu DataTable
		/*$('#example').DataTable({
			// Configura tus opciones de DataTables aquí
		});*/

		// Intercepta los mensajes de advertencia de DataTables


		// Opcional: Maneja el evento 'error' de la tabla para realizar acciones adicionales
		/*$('#example').on('error.dt', function(e, settings, techNote, message) {
			// Aquí puedes registrar el error en la consola o manejarlo de otra forma
			console.log('DataTables error:', message);
			// Opcionalmente puedes ocultar cualquier mensaje de error visual que ya se haya mostrado
			$('.dataTables_wrapper .dataTables_info').hide();
		});*/
	});

	window.addEventListener('load', function() {
		tabla = $('#table_sin_gestionar').DataTable({
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
			],
			@if(isset($_SESSION['permisos']['841']))
			select: true,
            select: {
                style: 'multi',
            },
			dom: 'Blfrtip',
            buttons: [{
            	text: '<i class="fas fa-check"></i> Seleccionar todos',
            	action: function() {
            		tabla.rows({
            			page: 'current'
            		}).select();
            	}
            },
            {
            	text: '<i class="fas fa-times"></i> Deseleccionar todos',
            	action: function() {
            		tabla.rows({
            			page: 'current'
            		}).deselect();
            	}
            }]
            @endif
		});

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
			data.tiempo_fin  = $('#tiempo_fin').val();
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

		$('#fecha').datepicker({
			locale: 'es-es',
      		uiLibrary: 'bootstrap4',
			format: 'yyyy-mm-dd' ,
		});

		$('#fechaG').datepicker({
			locale: 'es-es',
      		uiLibrary: 'bootstrap4',
			format: 'yyyy-mm-dd' ,
		});

		$('#tiempo_fin').datepicker({
			locale: 'es-es',
      		uiLibrary: 'bootstrap4',
			format: 'yyyy-mm-dd' ,
		});

		$('#tiempo_finG').datepicker({
			locale: 'es-es',
      		uiLibrary: 'bootstrap4',
			format: 'yyyy-mm-dd' ,
		});

		$('#codigoG, #contratoG, #telefonoG').on('keyup',function(e) {
        	if(e.which > 32 || e.which == 8) {
        		getDataTableG();
        		return false;
        	}
        });

        $('#fechaG, #clienteG, #servicioG, #estatusG, #creadoG, #prioridadG, #tecnicoG, #responsableG, #tiempo_finG').on('change',function() {
        	getDataTableG();
        	return false;
        });

		///////////////////////////////////////////

		tablaG = $('#table_sin_gestionarG').DataTable({
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
			],
			@if(isset($_SESSION['permisos']['841']))
			select: true,
            select: {
                style: 'multi',
            },
			dom: 'Blfrtip',
            buttons: [{
            	text: '<i class="fas fa-check"></i> Seleccionar todos',
            	action: function() {
            		tablaG.rows({
            			page: 'current'
            		}).select();
            	}
            },
            {
            	text: '<i class="fas fa-times"></i> Deseleccionar todos',
            	action: function() {
            		tablaG.rows({
            			page: 'current'
            		}).deselect();
            	}
            }]
            @endif
		});

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
			data.tiempo_fin  = $('#tiempo_finG').val();
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

		$('#btn_solventar').click( function () {
            states('solventar');
        });

        $('#btn_reabrir').click( function () {
            states('reabrir');
        });

        $('#btn_destroy').click( function () {
            destroy();
        });

        $('#codigo, #contrato, #telefono').on('keyup',function(e) {
        	if(e.which > 32 || e.which == 8) {
        		getDataTable();
        		return false;
        	}
        });

        $('#fecha, #cliente, #servicio, #estatus, #creado, #prioridad, #tecnico, #responsable, #tiempo_fin').on('change',function() {
        	getDataTable();
        	return false;
        });
	});

	function getDataTable() {
		tabla.ajax.reload();
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
		$('#tiempo_fin').val('');
		$('#form-filter').addClass('d-none');
		$('#boton-filtrar').html('<i class="fas fa-search"></i> Filtrar');
		getDataTable();
	}

	///////////////////////

	function getDataTableG() {
		tablaG.ajax.reload();
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
		$('#tiempo_finG').val('');
		$('#form-filterG').addClass('d-none');
		$('#boton-filtrarG').html('<i class="fas fa-search"></i> Filtrar');
		getDataTableG();
	}

	function exportar(otp) {
		if(otp == 0){
			window.location.href = window.location.pathname+'/exportar?codigo='+$('#codigo').val()+'&fecha='+$('#fecha').val()+'&contrato='+$('#contrato').val()+'&cliente='+$('#cliente').val()+'&telefono='+$('#telefono').val()+'&servicio='+$('#servicio').val()+'&estatus='+$('#estatus').val()+'&prioridad='+$('#prioridad').val()+'&tecnico='+$('#tecnico').val()+'&tiempo_fin='+$('#tiempo_fin').val()+'&otp='+otp;
		}else{
			window.location.href = window.location.pathname+'/exportar?codigo='+$('#codigoG').val()+'&fecha='+$('#fechaG').val()+'&contrato='+$('#contratoG').val()+'&cliente='+$('#clienteG').val()+'&telefono='+$('#telefonoG').val()+'&servicio='+$('#servicioG').val()+'&estatus='+$('#estatusG').val()+'&prioridad='+$('#prioridadG').val()+'&tecnico='+$('#tecnicoG').val()+'&tiempo_fin='+$('#tiempo_finG').val()+'&otp='+otp;
		}
	}

	function states(state){
		if(state == 'solventar'){
            var table = $('#table_sin_gestionar').DataTable();
            var nro = table.rows('.selected').data().length;
        }else{
            var table = $('#table_sin_gestionarG').DataTable();
            var nro = table.rows('.selected').data().length;
        }

        var radicados = [];
        if(nro<=1){
            swal({
                title: 'ERROR',
                html: 'Para ejecutar esta acción, debe al menos seleccionar dos radicados',
                type: 'error',
            });
            return false;
        }

        for (i = 0; i < nro; i++) {
            radicados.push(table.rows('.selected').data()[i]['id']);
        }

        swal({
            title: '¿Desea '+state+' '+nro+' radicados en lote?',
            text: 'Al Aceptar, no podrá cancelar el proceso',
            type: 'question',
            showCancelButton: true,
            confirmButtonColor: '#00ce68',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Aceptar',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (result.value) {
                cargando(true);
                if (window.location.pathname.split("/")[1] == "software") {
                    var url = `/software/empresa/radicados/`+radicados+`/`+state+`/state_lote`;
                }else{
                    var url = `/empresa/radicados/`+radicados+`/`+state+`/state_lote`;
                }

                $.ajax({
                    url: url,
                    method: 'GET',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(data) {
                        cargando(false);
                        if(data.state == 'solventar'){
                        	var state = 'solventados';
                        }else{
                        	var state = 'reabiertos';
                        }
                        swal({
                            title: 'PROCESO REALIZADO',
                            html: '<strong>'+data.correctos+' radicados '+state+'</strong><br><strong>'+data.fallidos+' radicados no '+state+'</strong>',
                            type: 'success',
                            showConfirmButton: true,
                            confirmButtonColor: '#1A59A1',
                            confirmButtonText: 'ACEPTAR',
                        });
                        getDataTable();
                        getDataTableG();
                    }
                })
            }
        })
    }

    function destroy(){
        var radicados = [];

        var table = $('#table_sin_gestionar').DataTable();
        var nro = table.rows('.selected').data().length;

        if(nro<=1){
            swal({
                title: 'ERROR',
                html: 'Para ejecutar esta acción, debe al menos seleccionar dos radicados',
                type: 'error',
            });
            return false;
        }

        for (i = 0; i < nro; i++) {
            radicados.push(table.rows('.selected').data()[i]['id']);
        }

        swal({
            title: '¿Desea eliminar '+nro+' radicados en lote?',
            text: 'Al Aceptar, no podrá cancelar el proceso',
            type: 'question',
            showCancelButton: true,
            confirmButtonColor: '#00ce68',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Aceptar',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (result.value) {
                cargando(true);

                if (window.location.pathname.split("/")[1] == "software") {
                    var url = `/software/empresa/radicados/`+radicados+`/destroy_lote`;
                }else{
                    var url = `/empresa/radicados/`+radicados+`/destroy_lote`;
                }

                $.ajax({
                    url: url,
                    method: 'GET',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(data) {
                        cargando(false);
                        swal({
                            title: 'PROCESO REALIZADO',
                            html: '<strong>'+data.correctos+' radicados '+data.state+'</strong><br><strong>'+data.fallidos+' radicados no '+data.state+'</strong>',
                            type: 'success',
                            showConfirmButton: true,
                            confirmButtonColor: '#1A59A1',
                            confirmButtonText: 'ACEPTAR',
                        });
                        getDataTable();
                        getDataTableG();
                    }
                })
            }
        })
    }
</script>
@endsection
