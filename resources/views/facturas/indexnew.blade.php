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
        <a href="{{route('facturas.create')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nueva Factura de Venta</a>
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
						<select title="Cliente" class="form-control rounded selectpicker" id="cliente" data-size="5" data-live-search="true">
							@foreach ($clientes as $cliente)
								<option value="{{ $cliente->id}}">{{ $cliente->nombre}} - {{ $cliente->nit}}</option>
							@endforeach
						</select>
					</div>
					<div class="col-md-2 pl-1 pt-1">
						<select title="Fecha Corte" class="form-control rounded selectpicker" id="corte" data-size="5" data-live-search="true">
							<option value="15" >Día 15</option>
							<option value="30" >Día 30</option>
						</select>
					</div>
					<div class="col-md-2 pl-1 pt-1">
						<input type="text" placeholder="Creación" id="creacion" name="creacion" class="form-control rounded creacion" autocomplete="off">
					</div>
					<div class="col-md-2 pl-1 pt-1">
						<input type="text" placeholder="Vencimiento" id="vencimiento" name="vencimiento" class="form-control rounded vencimiento" autocomplete="off">
					</div>
					<div class="col-md-2 pl-1 pt-1">
						<select title="Estado" class="form-control rounded selectpicker" id="estado">
							<option value="1">Abiertas</option>
							<option value="A">Cerradas</option>
							<option value="2">Anuladas</option>
						</select>
					</div>
					<div class="col-md-2 pl-1 pt-1">
						<select title="Enviada a Correo" class="form-control rounded selectpicker" id="correo">
							<option value="1">Si</option>
							<option value="A">No</option>
						</select>
					</div>
					

					<div class="col-md-1 pl-1 pt-1">
						<a href="javascript:limpiarFiltrador()" class="btn btn-icons ml-1 btn-outline-danger rounded btn-sm p-1 float-right" title="Limpiar parámetros de busqueda"><i class="fas fa-times"></i></a>
						<a href="javascript:void(0)" id="filtrar" class="btn btn-icons btn-outline-info rounded btn-sm p-1 float-right" title="Iniciar busqueda avanzada"><i class="fas fa-search"></i></a>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div class="row card-description">
		<div class="col-md-12">
			<table class="table table-striped table-hover w-100" id="tabla-facturas">
				<thead class="thead-dark">
					<tr>
						{{-- <th>Número</th>
						<th>Cliente</th>
						<th>Creación</th>
						<th>Vencimiento</th>
						<th>Total</th>
						<th>IVA</th>
						<th>Pagado</th>
						<th>Por Pagar</th>
						<th>Estado</th> --}}
						@foreach($tabla as $campo)
    					    <th>{{$campo->nombre}}</th>
    					@endforeach
						<th>Acciones</th>
					</tr>
				</thead>
			</table>
		</div>
	</div>
	
	<div class="modal fade" id="promesaPago" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">GENERAR PROMESA DE PAGO</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div id="div_promesa"></div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
	var tabla = null;
	window.addEventListener('load', function() {
		$('#tabla-facturas').DataTable({
			responsive: true,
			serverSide: true,
			processing: true,
			searching: false,
			language: {
				'url': '/vendors/DataTables/es.json'
			},
			order: [
				[2, "DESC"],[0, "DESC"]
			],
			"pageLength": 25,
			ajax: '{{url("/facturas")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
			columns: [
			    @foreach($tabla as $campo)
                {data: '{{$campo->campo}}'},
                @endforeach
				/*{data: 'codigo'},
				{data: 'cliente'},
				{data: 'fecha'},
				{data: 'vencimiento'},
				{data: 'total'},
				{data: 'impuesto'},
				{data: 'pagado'},
				{data: 'pendiente'},
				{data: 'estado'},*/
				{data: 'acciones'},
			]
		});

		tabla = $('#tabla-facturas');

		tabla.on('preXhr.dt', function(e, settings, data) {
			data.codigo = $('#codigo').val();
			data.corte = $('#corte').val();
			data.cliente = $('#cliente').val();
			data.vendedor = $('#vendedor').val();
			data.creacion = $('#creacion').val();
			data.vencimiento = $('#vencimiento').val();
			data.comparador = $('#comparador').val();
			data.total = $('#total').val();
			data.estado = $('#estado').val();
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
		$('#codigo').val('');
		$('#corte').val('').selectpicker('refresh');
		$('#cliente').val('').selectpicker('refresh');
		$('#vendedor').val('').selectpicker('refresh');
		$('#creacion').val('');
		$('#vencimiento').val('');
		$('#comparador').val('').selectpicker('refresh');
		$('#total').val('');
		$('#estado').val('').selectpicker('refresh');
		$('#form-filter').addClass('d-none');
		$('#boton-filtrar').html('<i class="fas fa-search"></i> Filtrar');
		getDataTable();
	}

	@if($tipo)
	    $('#estado').val('{{ $tipo }}').selectpicker('refresh');
	    abrirFiltrador();
	    getDataTable();
	@endif
</script>
@endsection