@extends('layouts.app')
@section('boton')
    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
	        <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
	    </div>
	@else
        <a href="javascript:abrirFiltrador()" class="btn btn-info btn-sm my-1" id="boton-filtrar"><i class="fas fa-search"></i>Filtrar</a>
        <a href="{{route('ingresos.create')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nuevo Ingreso</a>
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

	<div class="container-fluid d-none" id="form-filter">
		<fieldset>
            <legend>Filtro de Búsqueda</legend>
			<div class="card shadow-sm border-0">
				<div class="card-body pb-3 pt-2" style="background: #f9f9f9;">
					<div class="row">
						<div class="col-md-2 pl-1 pt-1">
							<input type="text" placeholder="Nro" id="numero" class="form-control rounded">
						</div>
						<div class="col-md-2 pl-1 pt-1">
							<input type="text" placeholder="Comprobante Pago" id="comprobante_pago" class="form-control rounded">
						</div>
						<div class="col-md-2 pl-1 pt-1">
							<select class="form-control rounded selectpicker" id="cliente" title="Cliente" data-size="5" data-live-search="true">
								@foreach ($clientes as $cliente)
								<option value="{{ $cliente->id }}">{{ $cliente->nombre }} {{$cliente->apellido1}} {{$cliente->apellido2}} - {{ $cliente->nit }}</option>
								@endforeach
							</select>
						</div>
						<div class="col-md-2 pl-1 pt-1">
							<input type="text" placeholder="Fecha" id="fecha-pago" class="form-control rounded">
						</div>
						<div class="col-md-2 pl-1 pt-1">
							<select id="banco" class="form-control rounded selectpicker m-0 p-0" title="Cuenta" data-width="150px" data-size="5" data-live-search="true">
								@php $tipos_cuentas=\App\Banco::tipos();@endphp
								@foreach($tipos_cuentas as $tipo_cuenta)
									<optgroup label="{{$tipo_cuenta['nombre']}}">
										@foreach($bancos as $cuenta)
										    @if($cuenta->tipo_cta==$tipo_cuenta['nro'])
										        <option value="{{$cuenta->id}}">{{$cuenta->nombre}}</option>
										    @endif
										@endforeach
									</optgroup>
								@endforeach
							</select>
						</div>
						<div class="col-md-2 pl-1 pt-1">
							<select class="form-control rounded selectpicker" id="metodo" title="Método de Pago" data-size="5" data-live-search="true">
								@foreach($metodos as $metodo)
								    <option value="{{$metodo->id}}">{{$metodo->metodo}}</option>
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

	<div class="row card-description">
		<div class="col-md-12">
    		<div class="container-filtercolumn">
    			@if(isset($_SESSION['permisos']['750']))
    			<a href="{{route('campos.organizar', 5)}}" class="btn btn-warning btn-sm mr-1"><i class="fas fa-table"></i> Organizar Tabla</a>
    			@endif
    			@if(Auth::user()->empresa()->efecty == 1)
    			<a href="{{route('ingresos.efecty')}}" class="btn btn-warning btn-sm" style="background: #938B16; border: solid #938B16 1px;"><i class="fas fa-upload"></i> Cargar Archivo Efecty TXT</a>
    			<a href="{{route('ingresos.efecty_xlsx')}}" class="btn btn-success btn-sm d-none"><i class="fas fa-upload"></i> Cargar Archivo Efecty XLXS</a>
    			@endif
			</div>
		</div>
		<div class="col-md-12">
			<table class="table table-striped table-hover nowrap w-100" id="tabla-ingresos">
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

@endsection

@section('scripts')
	<script>
		window.addEventListener('load',
			function() {

				$('#tabla-ingresos').DataTable({
					responsive: true,
					serverSide: true,
					processing: true,
					searching: false,
					language: {
						'url': '/vendors/DataTables/es.json'
					},
					order: [
						[0, "desc"]
					],
					"pageLength": {{ Auth::user()->empresa()->pageLength }},
					ajax: '{{url("/ingresos")}}',
					headers: {
						'X-CSRF-TOKEN': '{{csrf_token()}}'
					},
					columns: [
						@foreach($tabla as $campo)
						{data: '{{$campo->campo}}'},
						@endforeach
						{data: 'acciones'},
					]
				});

				tabla = $('#tabla-ingresos');

				tabla.on('preXhr.dt', function(e, settings, data) {
					data.numero = $('#numero').val();
					data.comprobante_pago = $('#comprobante_pago').val();
					data.cliente = $('#cliente').val();
					data.fecha = $('#fecha-pago').val();
					data.estado = $('#estado').val();
					data.banco = $('#banco').val();
					data.metodo = $('#metodo').val();
					data.filtro = true;
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

				$('#numero').on('keyup',function(e) {
		            if(e.which > 32 || e.which == 8) {
		                getDataTable();
		                return false;
		            }
		        });

		        $('#cliente, #banco, #metodo, #fecha-pago').on('change',function() {
		            getDataTable();
		            return false;
		        });

				$('#fecha-pago').datepicker({
					locale: 'es-es',
					uiLibrary: 'bootstrap4',
					format: 'yyyy-mm-dd',
					iconsLibrary: 'fontawesome',
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
		    $('#numero').val('');
		    $('#comprobante_pago').val('');
			$('#cliente').val('').selectpicker('refresh');
			$('#fecha-pago').val('');
			$('#estado').val('').selectpicker('refresh');
			$('#banco').val('').selectpicker('refresh');
			$('#metodo').val('').selectpicker('refresh');
			$('#form-filter').addClass('d-none');
			$('#boton-filtrar').html('<i class="fas fa-search"></i> Filtrar');
			getDataTable();
		}
	</script>
@endsection