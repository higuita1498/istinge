@extends('layouts.app')

@section('styles')
    <style>

    </style>
@endsection

@section('boton')
    @if(auth()->user()->modo_lectura())
        <div class="alert alert-warning text-left" role="alert">
            <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
           <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
        </div>
    @else
        @if(isset($_SESSION['permisos']['820']))
        <a href="{{route('productos.create_asignacion')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nueva Asignación</a>
        @endif
        <a href="javascript:abrirFiltrador()" class="btn btn-info btn-sm my-1" id="boton-filtrar"><i class="fas fa-search"></i>Filtrar</a>
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

    <div class="container-fluid d-none mb-3" id="form-filter">
        <fieldset>
            <legend>Filtro de Búsqueda</legend>
        	<div class="card shadow-sm border-0">
        		<div class="card-body py-3" style="background: #f9f9f9;">
        			<div class="row">
        				<div class="col-md-2 pl-1 pt-1">
        					<input type="text" class="form-control" id="nro" placeholder="Nro">
        				</div>
                        <div class="col-md-3 pl-1 pt-1">
                            <select title="Cliente" class="form-control selectpicker" id="cliente" data-size="5" data-live-search="true">
                                @foreach ($clientes as $cliente)
                                <option value="{{ $cliente->id }}">{{ $cliente->nombre }} {{ $cliente->apellido1 }} {{ $cliente->apellido2 }} - {{ $cliente->nit }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 pl-1 pt-1">
                            <select title="Venta" class="form-control selectpicker" id="producto_venta">
                                <option value="1">Si</option>
                                <option value="0">No</option>
                            </select>
                        </div>
                        <div class="col-md-3 pl-1 pt-1">
                            <select title="Estado" class="form-control selectpicker" id="status">
                                <option value="1">Pagado y Asignado</option>
                                <option value="2">Pendiente de Pago</option>
                                <option value="3">Asignado</option>
                            </select>
                        </div>
                        <div class="col-md-4 pl-1 pt-1">
                            <div class="row">
                                <div class="col-md-6 pr-1">
                                    <input type="text" class="form-control" id="desde" name="fecha" placeholder="desde">
                                </div>
                                <div class="col-md-6 pl-1">
                                    <input type="text" class="form-control" id="hasta" name="hasta" placeholder="hasta">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
        				<div class="col-md-12 pl-1 pt-1 text-center">
        					<a href="javascript:cerrarFiltrador()" class="btn btn-icons ml-1 btn-outline-danger rounded btn-sm p-1" title="Limpiar parámetros de busqueda"><i class="fas fa-times"></i></a>
        					<a href="javascript:void(0)" id="filtrar" class="btn btn-icons btn-outline-info rounded btn-sm p-1" title="Iniciar busqueda avanzada"><i class="fas fa-search"></i></a>
        				</div>
        			</div>
        		</div>
        	</div>
        </fieldset>
    </div>

    @if(isset($_SESSION['permisos']['819']))
    <div class="row card-description">
    	<div class="col-md-12">
    		<table class="table table-striped table-hover w-100" id="tabla-asigproductos">
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
    @endif
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#desde').datepicker({
            uiLibrary: 'bootstrap4',
            iconsLibrary: 'fontawesome',
            locale: 'es-es',
            uiLibrary: 'bootstrap4',
            format: 'dd-mm-yyyy',
            maxDate: function () {
                return $('#hasta').val();
            }
        });
        $('#hasta').datepicker({
            uiLibrary: 'bootstrap4',
            iconsLibrary: 'fontawesome',
            locale: 'es-es',
            uiLibrary: 'bootstrap4',
            format: 'dd-mm-yyyy',
            minDate: function () {
                return $('#desde').val();
            }
        });
    });

    var tabla = null;
    window.addEventListener('load',
    function() {
		var tbl = $('#tabla-asigproductos').DataTable({
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
			ajax: '{{url("/lproductos")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
			columns: [
			    @foreach($tabla as $campo)
                {data: '{{$campo->campo}}'},
                @endforeach
				{ data: 'acciones' },
			]
		});

		tabla = $('#tabla-asigproductos');

        tabla.on('preXhr.dt', function(e, settings, data) {
            data.nro        = $('#nro').val();
            data.cliente    = $('#cliente').val();
            data.desde      = $('#desde').val();
            data.hasta      = $('#hasta').val();
            data.created_by = $('#created_by').val();
            data.producto_venta = $("#producto_venta").val();
            data.status = $("#status").val();
            data.tipo       = 1;
            data.filtro     = true;
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

        $('#nro').on('keyup',function(e) {
            if(e.which > 32 || e.which == 8) {
                getDataTable();
                return false;
            }
        });

        $('#cliente, #desde, #hasta, #producto_venta, #status').on('change',function() {
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
		$('#nro').val('');
        $('#cliente').val('').selectpicker('refresh');
        $('#status').val('').selectpicker('refresh');
        $('#producto_venta').val('').selectpicker('refresh');
        $('#created_by').val('').selectpicker('refresh');
        $("#desde").val('');
        $("#hasta").val('');

		$('#form-filter').addClass('d-none');
		$('#boton-filtrar').html('<i class="fas fa-search"></i> Filtrar');
		getDataTable();
	}
</script>
@endsection
