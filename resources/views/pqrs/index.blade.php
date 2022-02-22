@extends('layouts.app')

@section('boton')
    @if(isset($_SESSION['permisos']['5']))
	    <a href="{{route('contactos.create')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nuevo Cliente</a>
    @endif
    @if(isset($_SESSION['permisos']['411']))
        <a href="{{route('contratos.create')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nuevo Contrato</a>
    @endif
    @if(isset($_SESSION['permisos']['201']))
        <a href="{{route('radicados.create')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nuevo Radicado</a>
    @endif
    <a href="javascript:abrirFiltrador()" class="btn btn-info btn-sm my-1" id="boton-filtrar"><i class="fas fa-search"></i>Filtrar</a>
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

    @if(Session::has('message_denied'))
	    <div class="alert alert-danger" role="alert">
	    	{{Session::get('message_denied')}}
	    	@if(Session::get('errorReason'))<br> <strong>Razon(es): <br></strong>
	    	    @if(count(Session::get('errorReason')) > 1)
	    	        @php $cont = 0 @endphp
	    	        @foreach(Session::get('errorReason') as $error)
	    	            @php $cont = $cont + 1; @endphp
	    	            {{$cont}} - {{$error}} <br>
	    	        @endforeach
	    	    @endif
	    	@endif
	    	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
	    		<span aria-hidden="true">&times;</span>
	    	</button>
	    </div>
	@endif

	@if(Session::has('message_success'))
	    <div class="alert alert-success" role="alert">
	    	{{Session::get('message_success')}}
	    	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
	    		<span aria-hidden="true">&times;</span>
	    	</button>
	    </div>
	@endif

	<div class="container-fluid d-none" id="form-filter">
		<div class="card shadow-sm border-0">
			<div class="card-body py-0">
				<div class="row">
					<div class="col-md-3 pl-1 pt-1">
						<select title="Usuarios" class="form-control rounded selectpicker" id="updated_by" data-size="5" data-live-search="true">
							@foreach ($usuarios as $usuario)
								<option value="{{ $usuario->id}}">{{ $usuario->nombres}}</option>
							@endforeach
						</select>
					</div>
					<div class="col-md-3 pl-1 pt-1">
						<select title="Solicitud" class="form-control rounded selectpicker" id="solicitud" data-size="5" data-live-search="true">
							<option value="Peticiones">Peticiones</option>
							<option value="Quejas">Quejas</option>
							<option value="Reclamos">Reclamos</option>
							<option value="Sugerencias">Sugerencias</option>
						</select>
					</div>
					<div class="col-md-3 pl-1 pt-1">
						<input type="text" placeholder="Fecha" id="creacion" name="creacion" class="form-control rounded creacion" autocomplete="off">
					</div>
					<div class="col-md-2 pl-1 pt-1">
						<select title="Estado" class="form-control rounded selectpicker" id="estatus">
							<option value="1">Por Atender</option>
							<option value="0">Atendido</option>
						</select>
					</div>

					<div class="col-md-1 pl-1 pt-1">
						<a href="javascript:cerrarFiltrador()" class="btn btn-icons ml-1 btn-outline-danger rounded btn-sm p-1 float-right" title="Limpiar parámetros de busqueda"><i class="fas fa-times"></i></a>
						<a href="javascript:void(0)" id="filtrar" class="btn btn-icons btn-outline-info rounded btn-sm p-1 float-right" title="Iniciar busqueda avanzada"><i class="fas fa-search"></i></a>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row card-description">
		<div class="col-md-12">
			<table class="table table-striped table-hover w-100" id="tabla-pqrs">
				<thead class="thead-dark">
					<tr>
					    <th>Solicitud</th>
					    <th>Nombres</th>
					    <th>Email</th>
					    <th>Fecha</th>
					    <th>Estatus</th>
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
		$('#tabla-pqrs').DataTable({
			responsive: true,
			serverSide: true,
			processing: true,
			language: {
				'url': '/vendors/DataTables/es.json'
			},
			order: [
				[0, "desc"]
			],
			"pageLength": 25,
			ajax: '{{url("/pqrs")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
			columns: [
				{data: 'solicitud'},
				{data: 'nombres'},
				{data: 'email'},
				{data: 'fecha'},
				{data: 'estatus'},
				{data: 'acciones'},
			]
		});

		tabla = $('#tabla-pqrs');

		tabla.on('preXhr.dt', function(e, settings, data) {
			data.updated_by = $('#updated_by').val();
			data.solicitud = $('#solicitud').val();
			data.creacion = $('#creacion').val();
			data.estatus = $('#estatus').val();
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
        $('#updated_by').val('').selectpicker('refresh');
        $('#solicitud').val('').selectpicker('refresh');
        $('#creacion').val('');
        $('#estatus').val('').selectpicker('refresh');
		$('#form-filter').addClass('d-none');
		$('#boton-filtrar').html('<i class="fas fa-search"></i> Filtrar');
		getDataTable();
	}
</script>
@endsection