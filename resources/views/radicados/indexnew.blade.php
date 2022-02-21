@extends('layouts.app')


@section('boton')
    @if(Auth::user()->modo_lectura())
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
        	<a>Estas en modo lectura, si deseas seguir disfrutando de nuestros servicios adquiere alguno de nuestros planes <a class="text-black" href="{{route('PlanesPagina.index')}}"> <b>Click Aquí.</b></a></a>
        	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
        		<span aria-hidden="true">&times;</span>
        	</button>
        </div>
    @else
        <a href="javascript:abrirFiltrador()" class="btn btn-info btn-sm my-1" id="boton-filtrar"><i class="fas fa-search"></i>Filtrar</a>
        @if(isset($_SESSION['permisos']['201']))
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
					<div class="col-md-1 pl-1 pt-1">
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
								<option value="{{ $cliente->nombre}}">{{ $cliente->nombre}} - {{ $cliente->nit}}</option>
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
							<option value="1">Solventado</option>
							<option value="2">Escalado / Pendiente</option>
							<option value="3">Escalado / Solventado</option>
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
			<table class="table table-striped table-hover w-100" id="tabla-radicados">
				<thead class="thead-dark">
					<tr>
						<th>Nro Radicado</th>
						<th>Fecha</th>
						<th>Contrato</th>
						<th>Cliente</th>
						<th>Nro Celular</th>
						<th>Servicio</th>
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
	window.addEventListener('load', function() {
		$('#tabla-radicados').DataTable({
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
			"pageLength": 25,
			ajax: '{{url("/radicados")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
			columns: [
				{data: 'codigo'},
				{data: 'fecha'},
				{data: 'contrato'},
				{data: 'cliente'},
				{data: 'telefono'},
				{data: 'servicio'},
				//{data: 'direccion'},
				{data: 'estatus'},
				{data: 'acciones'}
			]
		});

		tabla = $('#tabla-radicados');

		tabla.on('preXhr.dt', function(e, settings, data) {
			data.codigo    = $('#codigo').val();
			data.fecha     = $('#fecha').val();
			data.contrato  = $('#contrato').val();
			data.cliente   = $('#cliente').val();
			data.telefono  = $('#telefono').val();
			data.servicio  = $('#servicio').val();
			data.direccion = $('#direccion').val();
			data.estatus   = $('#estatus').val();
			data.filtro    = true;
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
		$('#form-filter').addClass('d-none');
		$('#boton-filtrar').html('<i class="fas fa-search"></i> Filtrar');
		getDataTable();
	}
</script>
@endsection