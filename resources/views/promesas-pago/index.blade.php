@extends('layouts.app')

@section('boton')
    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">Integra Colombia: Suscripción Vencida</h4>
	       <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
	    </div>
	@else
    <a href="javascript:abrirFiltrador()" class="btn btn-info btn-sm my-1" id="boton-filtrar"><i class="fas fa-search"></i>Filtrar</a>
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

	<div class="alert alert-info alert-dismissible fade show" role="alert">
	   <p>Revisa las promesas de pago de tus facturas que aún siguen abiertas.</p>
	   <button type="button" class="close" data-dismiss="alert" aria-label="Close">
		<span aria-hidden="true">&times;</span>
	</button>

	</div>

	<div class="container-fluid d-none" id="form-filter">
		<fieldset>
            <legend>Filtro de Búsqueda</legend>
			<div class="card shadow-sm border-0">
				<div class="card-body pb-3 pt-2" style="background: #f9f9f9;">
					<div class="row">
						<div class="col-md-3 pl-1 pt-1">
							<select title="Cliente" class="form-control rounded selectpicker" id="cliente" data-size="5" data-live-search="true">
								@foreach ($clientes as $cliente)
									<option value="{{ $cliente->id}}">{{ $cliente->nombre}} {{ $cliente->apellido1}} {{ $cliente->apellido2}} - {{ $cliente->nit }}</option>
								@endforeach
							</select>
						</div>
						<div class="col-md-3 pl-1 pt-1">
							<select title="Creado por" class="form-control rounded selectpicker" id="created_by" data-size="5" data-live-search="true">
								@foreach ($usuarios as $usuario)
									<option value="{{ $usuario->id}}">{{ $usuario->nombres}}</option>
								@endforeach
							</select>
						</div>

						<div class="col-md-1 pl-1 pt-1">
							<a href="javascript:cerrarFiltrador()" class="btn btn-icons ml-1 btn-outline-danger rounded btn-sm p-1 float-right" title="Limpiar parámetros de busqueda"><i class="fas fa-times"></i></a>
							<a href="javascript:void(0)" id="filtrar" class="btn btn-icons btn-outline-info rounded btn-sm p-1 float-right" title="Iniciar busqueda avanzada"><i class="fas fa-search"></i></a>
						</div>
					</div>
				</div>
			</div>
		</fieldset>
	</div>

	<div class="container-fluid">
    	<div class="row card-description" style="padding: 1% 1%; margin-bottom: 0;">
    		<div class="col-md-12 text-right">
    			@if(isset($_SESSION['permisos']['750']))
    			<a href="{{route('campos.organizar', 11)}}" class="btn btn-warning btn-sm my-1"><i class="fas fa-table"></i> Organizar Tabla</a>
    			@endif
	    	</div>
	    </div>
	</div>

	<div class="row card-description">
		<div class="col-md-12">
			<table class="table table-striped table-hover w-100" id="tabla-promesas">
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
	window.addEventListener('load', function() {
		$('#tabla-promesas').DataTable({
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
			ajax: '{{url("promesas")}}',
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

		tabla = $('#tabla-promesas');

		tabla.on('preXhr.dt', function(e, settings, data) {
			data.created_by = $('#created_by').val();
			data.cliente = $('#cliente').val();
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

		$('#cliente, #created_by').on('change',function() {
            getDataTable();
            return false;
        });

		$('.vencimiento').datepicker({
			locale: 'es-es',
      		uiLibrary: 'bootstrap4',
			format: 'yyyy-mm-dd' ,
		});

		$('.creacion').datepicker({
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
        $('#created_by').val('').selectpicker('refresh');
        $('#cliente').val('').selectpicker('refresh');
		$('#form-filter').addClass('d-none');
		$('#boton-filtrar').html('<i class="fas fa-search"></i> Filtrar');
		getDataTable();
	}
</script>
@endsection