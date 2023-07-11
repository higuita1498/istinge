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
<a href="{{route('facturasp.createSoporte')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nuevo Documento Soporte</a>
@endif
@endsection

@section('content')

@if (session()->has('success'))
	<div class="alert alert-success alert-dismissible fade show" role="alert">
		{{ session()->get('success') }}
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
	@endif

    @if (session()->has('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session()->get('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

	@if(auth()->user()->empresaObj->estado_dian == 1 && auth()->user()->empresaObj->validateResp() == 0)
        <div class="alert alert-warning" role="alert">
        	<strong>Atención, segun la resolución 042 del 2020</strong> las empresas se deben clasificar en alguno de estos grupos de responsabilidades: O-13,O-15,O-23,O-47 y R-99-PN, revisa el <strong>RUT</strong> de tu empresa para saber a que grupo perteneces y actualiza tus responsabilidades haciendo <a href="{{route('configuracion.create')}}">click aquí</a>
        	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
        		<span aria-hidden="true">&times;</span>
        	</button>
        </div>
    @endif

    @if(Session::has('message_denied'))
	    <div class="alert alert-danger" role="alert">
	    	{{Session::get('message_denied')}}
	    	@if(Session::get('errorReason'))<br> <strong>Razon(es): <br></strong>
	    	    @if(is_string(Session::get('errorReason')))
	    	        {{Session::get('errorReason')}}
	    	    @elseif (count(Session::get('errorReason')) >= 1)
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
					<input type="text" placeholder="Nro" id="nro" class="form-control rounded">
				</div>
				<div class="col-md-1 pl-1 pt-1">
					<input type="text" placeholder="Factura" id="codigo" class="form-control rounded">
				</div>
				<div class="col-md-2 pl-1 pt-1">
					<select title="Proveedor" class="form-control rounded selectpicker" id="proveedor" data-size="5" data-live-search="true">
						@foreach ($proveedores as $proveedor)
						<option value="{{ $proveedor->id}}">{{ $proveedor->nombre}}</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-2 pl-1 pt-1">
					<select title="Comprador" class="form-control rounded selectpicker" id="comprador" data-size="5" data-live-search="true">
						@foreach ($compradores as $comprador)
						<option value="{{ $comprador->id}}">{{ $comprador->nombre}}</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-2 pl-1 pt-1">
					<select title="Creado por" class="form-control rounded selectpicker" id="created_by" data-size="5" data-live-search="true">
						@foreach ($usuarios as $usuario)
						<option value="{{ $usuario->id}}">{{ $usuario->nombres}}</option>
						@endforeach
					</select>
				</div>
				<div class="col-md-2 pl-1 pt-1">
					<input type="text" placeholder="Creación" id="creacion" name="creacion" class="form-control rounded creacion" autocomplete="off">
				</div>
				<div class="col-md-2 pl-1 pt-1">
					<input type="text" placeholder="Vencimiento" id="vencimiento" name="vencimiento" class="form-control rounded vencimiento" autocomplete="off">
				</div>
				<div class="col-md-1 pl-1 pr-md-0 pt-1">
					<select title="rango total" class="form-control rounded selectpicker" id="comparador" data-size="5" data-live-search="true">
						<option value="=">(=) Igual</option>
						<option value=">">(>) Mayor</option>
						<option value="<">(<) Menor</option>
					</select>
				</div>
				<div class="col-md-2 pl-md-0 pt-1">
					<input type="number" placeholder="Total" id="total" class="form-control rounded">
				</div>
				<div class="col-md-2 pl-1 pr-md-0 pt-1">
					<select title="Estado" class="form-control rounded selectpicker" id="estado" data-size="5" data-live-search="true">
						<option value="0">Cerrada</option>
						<option value="1">Abierta</option>
						<option value="2">Por pagar</option>
						<option value="3">Cerrada por Devolución</option>
						<option value="4">Cerrada con Devolución</option>
						<option value="5">Abierta con Devolución</option>
					</select>
				</div>

				{{-- @if ($estadoModulo)
				<div class="col-md-2 pl-1 pr-md-0 pt-1">
					<select name="etiqueta" id="etiqueta" class="form-control form-control-sm selectpicker" title="Etiquetas" data-size="5" data-live-search="true">
						@foreach ($etiquetas as $etiqueta)
						<option value="{{$etiqueta->id}}">{{$etiqueta->nombre}}</option>
						@endforeach
					</select>
				</div>
				@endif --}}


				<div class="col-md-1 pl-1 pt-2 text-center">
					<a href="javascript:cerrarFiltrador()" class="btn btn-icons ml-1 btn-outline-danger rounded btn-sm p-1 float-right" title="Limpiar parámetros de busqueda"><i class="fas fa-times"></i></a>
					<a href="javascript:void(0)" id="filtrar" class="btn btn-icons btn-outline-info rounded btn-sm p-1 float-right" title="Iniciar busqueda avanzada"><i class="fas fa-search"></i></a>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row card-description">
	<div class="col-md-12">
		<table class="table table-striped table-hover w-100 @if(env('APP_ENTORNO')==2 && auth()->user()->empresa == 160) mytable @endif" id="tabla-facturasp">
			<thead class="thead-dark">
				<tr>
					<th>Nro</th>
					<th>Factura</th>
					<th>Proveedor</th>
					<th>Comprador</th>
					<th>Creación</th>
					<th>Vencimiento</th>
					<th>Total</th>
					<th>Impuesto</th>
					<th>Pagado</th>
					<th>Por pagar</th>
					<th>Etiqueta</th>
					<th>Estado</th>
					<th>Acciones</th>
				</tr>
			</thead>
		</table>
	</div>
</div>

<!-- Modal notas -->
<div class="modal fade" id="modalObservaciones" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">

</div>
@endsection

@section('scripts')
<script>
	var tabla = null;
	window.addEventListener('load', function() {
		$('#tabla-facturasp').DataTable({
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
			"pageLength": 25,
			ajax: '{{url("empresa/facturasp/documentos_soporte")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
			columns: [{
					data: 'nro'
				},
				{
					data: 'codigo'
				},
				{
					data: 'proveedor'
				},
				{
					data: 'comprador'
				},
				{
					data: 'fecha'
				},
				{
					data: 'vencimiento'
				},
				{
					data: 'total'
				},
				{
					data: 'impuesto'
				},
				{
					data: 'pagado'
				},
				{
					data: 'pendiente'
				},
				{
					data: 'etiqueta'
				},
				{
					data: 'estatus'
				},
				{
					data: 'acciones'
				},
			]
		});

		tabla = $('#tabla-facturasp');

		tabla.on('preXhr.dt', function(e, settings, data) {
			data.nro = $('#nro').val();
			data.codigo = $('#codigo').val();
			data.proveedor = $('#proveedor').val();
			data.comprador = $('#comprador').val();
			data.creacion = $('#creacion').val();
			data.vencimiento = $('#vencimiento').val();
			data.comparador = $('#comparador').val();
			data.total = $('#total').val();
			data.estatus = $('#estado').val();
			data.etiqueta = $('#etiqueta').val();
			data.created_by = $('#created_by').val();
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
			format: 'yyyy-mm-dd',
		});

		$('.creacion').datepicker({
			locale: 'es-es',
			uiLibrary: 'bootstrap4',
			format: 'yyyy-mm-dd',
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
		$('#nro').val('');
		$('#codigo').val('');
		$('#proveedor').val('').selectpicker('refresh');
		$('#comprador').val('').selectpicker('refresh');
		$('#created_by').val('').selectpicker('refresh');
		$('#creacion').val('');
		$('#vencimiento').val('');
		$('#comparador').val('').selectpicker('refresh');
		$('#total').val('');
		$('#estado').val('').selectpicker('refresh');
		$('#etiqueta').val('').selectpicker('refresh');
		$('#form-filter').addClass('d-none');
		$('#boton-filtrar').html('<i class="fas fa-search"></i> Filtrar');
		getDataTable();
	}

	function modificarObservaciones(id) {
		var observaciones;
		$.ajax({
			url: `/empresa/facturasp/observacion/${id}`,
			method: 'GET',
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},

			success: function(response) {
				if (response.ok) {
					console.log(response.observacion);
					observaciones = response.observacion;

					id = parseInt(id);

					$('#modalObservaciones').html('');

					$('#modalObservaciones').append(`<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title" id="exampleModalLabel">Observaciones de la factura de venta</h5>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
								</button>
							</div>
							<div class="modal-body">
								<div class="form-group">
									<label for="observaciones">Ingrese las observaciones de la factura de venta</label>
									<textarea name="observaciones" id="observaciones-${id}" cols="30" rows="10" class="form-control">${observaciones}</textarea>
								</div>
								<div id="custom-target"></div>

							</div>
							<div class="modal-footer">
								<a  class="btn btn-secondary" data-dismiss="modal">Cerrar</a>
								<a  class="btn btn-primary text-white" onclick="guardarObservaciones(${id})">Guardar</a>
							</div>
						</div>
					</div>`);

					$('#modalObservaciones').modal('show');
				}
			}
		});
	}

	function guardarObservaciones(id) {
		$.ajax({
			url: `/empresa/facturasp/observacion`,
			method: 'POST',
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			data: {
				id: id,
				observacion: $('#observaciones-' + id).val()
			},
			success: function(response) {
				if (response.ok) {
					$(`#observacion-parrafo-${id}`).empty();
					$('#modalObservaciones').modal('hide');
					let observacion = response.observacion;
					$(`#observacion-parrafo-${id}`).append(observacion.substring(0, 24));
					$(`#observacion-parrafo-${id}`).attr('title', observacion);
					getDataTable();
				}
			}
		});
	}
</script>

<script>
	function cambiarEtiqueta(factura, etiqueta) {

		$.ajax({
			url: `/empresa/facturasp/etiqueta/${parseInt(factura)}/${parseInt(etiqueta)}`,
			method: 'GET',
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			success: function(response) {
				if (response.success) {
					let etiqueta = $(`#etiqueta-${factura}`);
					etiqueta.text(response.etiqueta.nombre);
					etiqueta.css('background-color', response.etiqueta.color.codigo);
				}
			}
		});
	}
</script>
@endsection