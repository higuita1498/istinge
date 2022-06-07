@extends('layouts.app')

@section('boton')
    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
	        <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
	    </div>
	@else
	    @if(isset($_SESSION['permisos']['57']))
	        <a href="{{route('cotizaciones.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nueva Cotización</a>
	    @endif
	    <a href="javascript:abrirFiltrador()" class="btn btn-info btn-sm my-1" id="boton-filtrar"><i class="fas fa-search"></i>Filtrar</a>
    @endif
@endsection

@section('content')
	@if(Session::has('success'))
		<div class="alert alert-success" style="margin-left: 2%;margin-right: 2%;">
			<button type="button" class="close" data-dismiss="alert">×</button>
			{{Session::get('success')}}
		</div>
	@endif 

	@if(Session::has('error'))
		<div class="alert alert-danger" style="margin-left: 2%;margin-right: 2%;">
			<button type="button" class="close" data-dismiss="alert">×</button>
			{{Session::get('error')}}
		</div>
	@endif

	<div class="container-fluid d-none" id="form-filter">
		<fieldset>
			<legend>Filtro de Búsqueda</legend>
			<div class="card shadow-sm border-0">
				<div class="card-body pb-3 pt-2" style="background: #f9f9f9;">
					<div class="row">
						<div class="col-md-3 pl-1 pt-1">
							<input type="text" placeholder="Nro" id="cot_nro" class="form-control rounded">
						</div>
						<div class="col-md-3 pl-1 pt-1">
							<input type="text" placeholder="Cliente" id="nombre" class="form-control rounded">
						</div>
						<div class="col-md-3 pl-1 pt-1">
							<input type="text" placeholder="Fecha" id="fecha" class="form-control rounded">
						</div>
						<div class="col-md-3 pl-1 pt-1">
						    <select title="Estado" class="form-control rounded selectpicker" id="estatus">
						        <option value="1">Todas</option>
								<option value="2">Por Cotizar</option>
							</select>
						</div>
						<div class="col-md-12 pl-1 pt-2 text-center">
							<a href="javascript:cerrarFiltrador()" class="btn btn-icons ml-1 btn-outline-danger rounded btn-sm p-1" title="Limpiar parámetros de busqueda"><i class="fas fa-times"></i></a>
							<a href="javascript:void(0)" id="filtrar" class="btn btn-icons btn-outline-info rounded btn-sm p-1" title="Iniciar busqueda avanzada"><i class="fas fa-search"></i></a>
						</div>
					</div>
				</div>
			</div>
		</fieldset>
	</div>

	<div class="row card-description">
		<div class="col-md-12">
			<table class="table table-striped table-hover w-100" id="tabla-cotizaciones">
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
		var tabla = null;
	    window.addEventListener('load',
	    function() {
			$('#tabla-cotizaciones').DataTable({
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
				ajax: '{{url("lcotizaciones")}}',
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

	        tabla = $('#tabla-cotizaciones');

	        tabla.on('preXhr.dt', function(e, settings, data) {
				data.cot_nro     = $('#cot_nro').val();
				data.nombre  = $('#nombre').val();
				data.fecha   = $('#fecha').val();
				data.estatus = $('#estatus').val();
				data.filtro  = true;
	        });

	        $('#filtrar').on('click', function(e) {
	            getDataTable();
	            return false;
	        });

	        $('#form-filter').on('keypress',function(e) {
	            if(e.which == 13) {
	                getDataTable();
	                return false;
	            }
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
			$('#cot_nro').val('');
			$('#nombre').val('');
			$('#fecha').val('');
			$('#estatus').val('').selectpicker('refresh');
			$('#form-filter').addClass('d-none');
			$('#boton-filtrar').html('<i class="fas fa-search"></i> Filtrar');
			getDataTable();
		}
	</script>
@endsection