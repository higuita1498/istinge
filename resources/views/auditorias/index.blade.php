@extends('layouts.app')

@section('boton')
    <a href="javascript:getDataTable()" class="btn btn-success btn-sm my-1"><i class="fas fa-sync-alt"></i> Actualizar</a>
    <a href="javascript:abrirFiltrador()" class="btn btn-info btn-sm my-1" id="boton-filtrar"><i class="fas fa-search"></i>Filtrar</a>
@endsection

@section('content')
    @if(Session::has('danger'))
		<div class="alert alert-danger" >
			{{Session::get('danger')}}
		</div>

		<script type="text/javascript">
			setTimeout(function(){
			    $('.alert').hide();
			    $('.active_table').attr('class', ' ');
			}, 10000);
		</script>
	@endif

	<div class="container-fluid d-none mb-3" id="form-filter">
        <fieldset>
            <legend>Filtro de Búsqueda</legend>
        	<div class="card shadow-sm border-0">
        		<div class="card-body py-3" style="background: #f9f9f9;">
        			<div class="row">
        				<div class="col-md-3 pl-1 pt-1">
        					<input type="text" class="form-control" id="contrato" placeholder="Nro Contrato">
        				</div>
                        <div class="col-md-3 pl-1 pt-1">
                            <select title="Clientes" class="form-control selectpicker" id="client_id" data-size="5" data-live-search="true">
                                @foreach ($clientes as $cliente)
                                <option value="{{ $cliente->id }}">{{ $cliente->nombre }} {{ $cliente->apellido1 }} {{ $cliente->apellido2 }} - {{ $cliente->nit }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 pl-1 pt-1">
        					<input type="text" class="form-control" id="ip" placeholder="Dirección IP">
        				</div>
        				<div class="col-md-3 pl-1 pt-1">
                            <select title="Ejecutado por" class="form-control selectpicker" id="created_by" data-size="5" data-live-search="true">
                                @foreach ($usuarios as $usuario)
                                <option value="{{ $usuario->id }}">{{ $usuario->nombres}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 pl-1 pt-1">
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

	<div class="row card-description">
    	<div class="col-md-12">
    		<table class="table table-striped table-hover w-100" id="tabla-log">
    			<thead class="thead-dark">
    				<tr>
    					<th>Fecha</th>
    					<th>Contrato</th>
    					<th>Cliente</th>
    					<th>IP</th>
    					<th>Ejecutado por</th>
    					<th>Cambios Realizados</th>
    				</tr>
    			</thead>
    		</table>
    	</div>
    </div>
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

		$('#tabla-log').DataTable({
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
            columnDefs:[{
            	"targets": [2],
            	"orderable": false
            }],
			pageLength: {{ Auth::user()->empresa()->pageLength }},
			ajax: '{{url("/auditoria_contratos")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
			columns: [
			    { data: 'created_at' },
			    { data: 'contrato' },
			    { data: 'cliente' },
			    { data: 'ip' },
			    { data: 'created_by' },
				{ data: 'descripcion' }
			]
		});

        tabla = $('#tabla-log');

        tabla.on('preXhr.dt', function(e, settings, data) {
			data.contrato   = $("#contrato").val();
			data.client_id  = $("#client_id").val();
			data.ip         = $("#ip").val();
			data.created_by = $("#created_by").val();
			data.desde      = $("#desde").val();
			data.hasta      = $("#hasta").val();
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

        $('#contrato, #ip').on('keyup',function(e) {
            if(e.which > 32 || e.which == 8) {
                getDataTable();
                return false;
            }
        });

        $('#client_id, #created_by, #desde, #hasta').on('change',function() {
            getDataTable();
            return false;
        });
    });

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
		$("#contrato").val('');
		$("#client_id").val('').selectpicker('refresh');
		$("#ip").val('');
		$("#created_by").val('').selectpicker('refresh');
		$("#desde").val('');
		$("#hasta").val('');
		$('#form-filter').addClass('d-none');
		$('#boton-filtrar').html('<i class="fas fa-search"></i> Filtrar');
		getDataTable();
	}

	function getDataTable() {
		tabla.DataTable().ajax.reload();
	}
</script>
@endsection
