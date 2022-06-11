@extends('layouts.app')

@section('styles')

@endsection

@section('boton')
    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
	        <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
	    </div>
	@else
    <a href="javascript:abrirFiltrador()" class="btn btn-info btn-sm my-1" id="boton-filtrar"><i class="fas fa-search"></i>Filtrar</a>
    <?php if (isset($_SESSION['permisos']['725'])) { ?>
        <a href="{{route('grupos-corte.create')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nuevo Grupo de Corte</a>
    <?php } ?>
    @endif
@endsection

@section('content')

    @if(Session::has('success'))
        <div class="alert alert-success" style="margin-left: 2%;margin-right: 2%;">
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
        <div class="alert alert-danger" style="margin-left: 2%;margin-right: 2%;">
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
					<div class="col-md-3 pl-1 pt-1">
						<input type="text" placeholder="Nombre" id="nombre" class="form-control rounded">
					</div>
					<div class="col-md-2 pl-1 pt-1">
						<input type="text" placeholder="Fecha Corte" id="fecha_corte" class="form-control rounded">
					</div>
					<div class="col-md-2 pl-1 pt-1">
						<input type="text" placeholder="Fecha Suspensión" id="fecha_suspension" class="form-control rounded">
					</div>
					<div class="col-md-3 pl-1 pt-1">
					    <select title="Estado" class="form-control rounded selectpicker" id="status">
					        <option value="1">Habilitado</option>
							<option value="A">Deshabilitado</option>
						</select>
					</div>
					<div class="col-md-1 pl-1 pt-1 text-left">
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
		<table class="table table-striped table-hover w-100" id="tabla-grupo">
			<thead class="thead-dark">
				<tr>
				    <th>Nro</th>
				    <th>Nombre</th>
				    <th>Fecha Factura</th>
				    <th>Fecha Pago</th>
				    <th>Fecha Corte</th>
				    <th>Fecha Suspensión</th>
					<th>Estado</th>
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

		$('#tabla-grupo').DataTable({
			responsive: true,
			serverSide: true,
			processing: true,
			language: {
				'url': '/vendors/DataTables/es.json'
			},
			order: [
				[0, "desc"]
			],
			"pageLength": {{ Auth::user()->empresa()->pageLength }},
			ajax: '{{url("/grupos")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
			columns: [
			    {data: 'id'},
			    {data: 'nombre'},
			    {data: 'fecha_factura'},
			    {data: 'fecha_pago'},
			    {data: 'fecha_corte'},
			    {data: 'fecha_suspension'},
				{data: 'status'},
				{data: 'acciones'},
			]
		});


        tabla = $('#tabla-grupo');

        tabla.on('preXhr.dt', function(e, settings, data) {
            data.nombre = $('#nombre').val();
            data.fecha_corte = $('#fecha_corte').val();
            data.fecha_suspension = $('#fecha_suspension').val();
            data.status = $('#status').val();
            data.filtro = true;
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

        $('#nombre, #fecha_corte, #fecha_suspension').on('keyup',function(e) {
        	if(e.which > 32 || e.which == 8) {
        		getDataTable();
        		return false;
        	}
        });

        $('#status').on('change',function() {
        	getDataTable();
        	return false;
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
		$('#nombre').val('');
		$('#fecha_corte').val('').selectpicker('refresh');
		$('#fecha_suspension').val('').selectpicker('refresh');
		$('#status').val('').selectpicker('refresh');
		$('#form-filter').addClass('d-none');
		$('#boton-filtrar').html('<i class="fas fa-search"></i> Filtrar');
		getDataTable();
	}
</script>
@endsection
