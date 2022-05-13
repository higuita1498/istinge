@extends('layouts.app')

@section('boton')
    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
	        <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
	    </div>
	@else
        <a href="javascript:abrirFiltrador()" class="btn btn-info btn-sm my-1" id="boton-filtrar"><i class="fas fa-search"></i>Filtrar</a>
        @if(isset($_SESSION['permisos']['253']))
            <a href="{{route('pagos.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Pago</a>
        @endif
    @endif
@endsection

@section('style')
    <style>
    	td .elipsis-short {
	    	width: 250px;
	    	overflow: hidden;
	    	white-space: nowrap;
	    	text-overflow: ellipsis;
	    }
	    @media all and (max-width: 768px){
	    	td .elipsis-short {
	    		width: 150px;
	    		overflow: hidden;
	    		white-space: nowrap;
	    		text-overflow: ellipsis;
	    	}
	    }
    </style>
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
					<div class="col-md-2 pl-1 pt-1">
						<input type="text" placeholder="Nro" id="nro" class="form-control rounded">
					</div>
					<div class="col-md-3 pl-1 pt-1">
						<select title="Beneficiario" class="form-control rounded selectpicker" id="beneficiario" data-size="5" data-live-search="true">
							@foreach ($beneficiarios as $beneficiario)
								<option value="{{ $beneficiario->id}}">{{ $beneficiario->nombre}} {{$beneficiario->apellido1}} {{$beneficiario->apellido2}} - {{ $beneficiario->nit}}</option>
							@endforeach
						</select>
					</div>
					<div class="col-md-2 pl-1 pt-1">
						<input type="text" placeholder="Fecha" id="creacion" class="form-control rounded creacion" autocomplete="off">
					</div>
					<div class="col-md-2 pl-1 pt-1">
						<select title="Cuenta" class="form-control rounded selectpicker" id="cuenta" data-size="5" data-live-search="true">
							@foreach ($cuentas as $cuenta)
								<option value="{{ $cuenta->id}}">{{ $cuenta->nombre}}</option>
							@endforeach
						</select>
					</div>
					<div class="col-md-2 pl-1 pt-1">
						<select title="Estado" class="form-control rounded selectpicker" id="estatus">
							<option value="1">Anulado</option>
							<option value="2">Consolidado</option>
							<option value="A">No consolidado</option>
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
			<table class="table table-striped table-hover w-100" id="tabla-pagos">
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
		$('#tabla-pagos').DataTable({
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
			"pageLength": {{ Auth::user()->empresa()->pageLength }},
			ajax: '{{url("/pagos")}}',
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

		tabla = $('#tabla-pagos');

		tabla.on('preXhr.dt', function(e, settings, data) {
			data.nro = $('#nro').val();
			data.beneficiario = $('#beneficiario').val();
			data.creacion = $('#creacion').val();
			data.estatus = $('#estatus').val();
			data.cuenta = $('#cuenta').val();
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
		$('#nro').val('');
		$('#beneficiario').val('').selectpicker('refresh');
		$('#creacion').val('');
		$('#estatus').val('').selectpicker('refresh');
		$('#cuenta').val('').selectpicker('refresh');
		$('#form-filter').addClass('d-none');
		$('#boton-filtrar').html('<i class="fas fa-search"></i> Filtrar');
		getDataTable();
	}
</script>
@endsection