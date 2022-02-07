@extends('layouts.app')

@section('styles')

@endsection

@section('boton')
    <a href="javascript:abrirFiltrador()" class="btn btn-info btn-sm my-1" id="boton-filtrar"><i class="fas fa-search"></i>Filtrar</a>
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

<div class="container-fluid d-none" id="form-filter">
	<div class="card shadow-sm border-0 mb-3" style="background: #ffffff00 !important;">
		<div class="card-body py-0">
			<div class="row">
				<div class="col-md-3 pl-1 pt-1">
				    <select title="Cliente" class="form-control rounded selectpicker" id="id_cliente" data-size="5" data-live-search="true">
						@foreach ($clientes as $cliente)
							<option value="{{ $cliente->id}}">{{ $cliente->nombre}}</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-3 pl-1 pt-1">
				    <select title="Estado" class="form-control rounded selectpicker" id="status">
				        <option value="1">Pendiente</option>
				        <option value="0">Realizada</option>
						</select>
					</div>
				<div class="col-md-1 pl-1 pt-1 text-left">
					<a href="javascript:cerrarFiltrador()" class="btn btn-icons ml-1 btn-outline-danger rounded btn-sm p-1 float-right" title="Limpiar parámetros de busqueda"><i class="fas fa-times"></i></a>
					<a href="javascript:void(0)" id="filtrar" class="btn btn-icons btn-outline-info rounded btn-sm p-1 float-right" title="Iniciar busqueda avanzada"><i class="fas fa-search"></i></a>
				</div>
			</div>
		</div>
	</div>
</div>


<div class="row card-description">
	<div class="col-md-12">
		<table class="table table-striped table-hover w-100" id="tabla-wifi">
			<thead class="thead-dark">
				<tr>
					<th>Nro.</th>
					<th>Estatus</th>
					<th>Cliente</th>
					<th>Red Antigua</th>
					<th>Red Nueva</th>
					<th>Contraseña Antigua</th>
					<th>Contraseña Nueva</th>
					<th>Red Oculta</th>
					<th>IP</th>
					<th>MAC</th>
					<th>Ejecutado por</th>
					<th>Ejecutado el</th>
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

		$('#tabla-wifi').DataTable({
			responsive: true,
			serverSide: true,
			processing: true,
			searching: false,
			language: {
				'url': '/vendors/DataTables/es.json'
			},
			order: [
				[0, "DESC"]
			],
			"pageLength": 25,
			ajax: '{{url("/solicitudes")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
			columns: [
				{data: 'id'},
				{data: 'status'},
			    {data: 'id_cliente'},
				{data: 'red_antigua'},
				{data: 'red_nueva'},
				{data: 'pass_antigua'},
				{data: 'pass_nueva'},
				{data: 'oculto'},
				{data: 'ip'},
				{data: 'mac'},
				{data: 'created_by'},
				{data: 'updated_at'},
				{data: 'acciones'}
			]
		});


        tabla = $('#tabla-wifi');

        tabla.on('preXhr.dt', function(e, settings, data) {
            data.id_cliente = $('#id_cliente').val();
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
		$('#id_cliente').val('').selectpicker('refresh');
		$('#status').val('').selectpicker('refresh');
		$('#form-filter').addClass('d-none');
		$('#boton-filtrar').html('<i class="fas fa-search"></i> Filtrar');
		getDataTable();
	}
</script>
@endsection
