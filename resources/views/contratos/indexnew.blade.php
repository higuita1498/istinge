@extends('layouts.app')

@section('styles')
    <style>
        td .elipsis-short-325 {
            width: 325px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        @media all and (max-width: 768px) {
            td .elipsis-short-325 {
                width: 225px;
                overflow: hidden;
                white-space: nowrap;
                text-overflow: ellipsis;
            }
        }
    </style>
@endsection

@section('boton')
    @if(auth()->user()->modo_lectura())
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <a>Estas en modo lectura, si deseas seguir disfrutando de nuestros servicios adquiere alguno de nuestros planes <a class="text-black" href="{{route('PlanesPagina.index')}}"> <b>Click Aquí.</b></a></a>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @else
        @if(isset($_SESSION['permisos']['5']))
        <a href="{{route('contactos.create')}}" class="btn btn-outline-info btn-sm"><i class="fas fa-plus"></i> Nuevo Cliente</a>
        @endif
        @if(isset($_SESSION['permisos']['201']))
        <a href="{{route('radicados.create')}}" class="btn btn-outline-info btn-sm"><i class="fas fa-plus"></i> Nuevo Radicado</a>
        @endif

        @if(Auth::user()->id == 3)
            <a href="{{route('contratos.exportar')}}" class="btn btn-success btn-sm d-none" ><i class="fas fa-file-excel"></i> Exportar a Excel</a>
        @endif
        <a href="{{route('contratos.create')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nuevo Contrato</a>
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
    	<div class="card shadow-sm border-0">
    		<div class="card-body py-0">
    			<div class="row">
    				<div class="col-md-3 pl-1 pt-1">
    					<select title="Clientes" class="form-control selectpicker" id="client_id" data-size="5" data-live-search="true">
    						@foreach ($clientes as $cliente)
    						<option value="{{ $cliente->id }}">{{ $cliente->nombre }}</option>
    						@endforeach
    					</select>
    				</div>
    				<div class="col-md-3 pl-1 pt-1">
    					<select title="Planes" class="form-control selectpicker" id="plan" data-size="5" data-live-search="true">
    						@foreach ($planes as $plan)
    						<option value="{{ $plan->id }}">{{ $plan->name }}</option>
    						@endforeach
    					</select>
    				</div>
    				<div class="col-md-2 pl-1 pt-1">
    					<input type="text" class="form-control" id="ip" placeholder="Dirección IP">
    				</div>
    				<div class="col-md-2 pl-1 pt-1">
    					<input type="text" class="form-control" id="mac" placeholder="MAC">
    				</div>
    				<div class="col-md-2 pl-1 pt-1">
    					<select title="Estado" class="form-control selectpicker" id="state">
    						<option value="enabled">Habilitado</option>
    						<option value="disabled">Deshabilitado</option>
    					</select>
    				</div>
    				<div class="col-md-3 pl-1 pt-1">
    					<select title="Grupo de Corte" class="form-control selectpicker" id="grupo_cort">
    						@foreach ($grupos as $grupo)
    						<option value="{{ $grupo->id }}">{{ $grupo->nombre }}</option>
    						@endforeach
    					</select>
    				</div>
    				<div class="col-md-3 pl-1 pt-1">
    					<select title="Conexión" class="form-control selectpicker" id="conexion_s">
    						<option value="1">PPPOE</option>
    						<option value="2">DHCP</option>
    						<option value="3">IP Estática</option>
    						<option value="4">VLAN</option>
    					</select>
    				</div>
    				<div class="col-md-3 pl-1 pt-1">
    					<select title="Servidor" class="form-control selectpicker" id="server_configuration_id_s">
    						@foreach ($servidores as $servidor)
    						<option value="{{ $servidor->id }}">{{ $servidor->nombre }}</option>
    						@endforeach
    					</select>
    				</div>
    				{{-- <div class="col-md-3 pl-1 pt-1">
    					<select title="Interfaz" class="form-control selectpicker" id="interfaz_s">
    						@foreach ($interfaces as $interfaz)
    						<option value="{{ $interfaz->id }}">{{ $interfaz->nombre }}</option>
    						@endforeach
    					</select>
    				</div> --}}
    				<div class="col-md-3 pl-1 pt-1">
    					<select title="Nodo" class="form-control selectpicker" id="nodo_s">
    						@foreach ($nodos as $nodo)
    						<option value="{{ $nodo->id }}">{{ $nodo->nombre }}</option>
    						@endforeach
    					</select>
    				</div>
    				<div class="col-md-3 pl-1 pt-1">
    					<select title="Access Point" class="form-control selectpicker" id="ap_s">
    						@foreach ($aps as $ap)
    						<option value="{{ $ap->id }}">{{ $ap->nombre }}</option>
    						@endforeach
    					</select>
    				</div>
    				
    				<div class="col-md-2 pl-1 pt-1">
    					<a href="javascript:cerrarFiltrador()" class="btn btn-icons ml-1 btn-outline-danger rounded btn-sm p-1 float-right" title="Limpiar parámetros de busqueda"><i class="fas fa-times"></i></a>
    					<a href="javascript:void(0)" id="filtrar" class="btn btn-icons btn-outline-info rounded btn-sm p-1 float-right" title="Iniciar busqueda avanzada"><i class="fas fa-search"></i></a>
    					@if(Auth::user()->id == 3)
    					<a href="javascript:exportar()" class="btn btn-icons mr-1 btn-outline-success rounded btn-sm p-1 float-right" title="Exportar"><i class="fas fa-file-excel"></i></a>
    					@endif
    				</div>
    			</div>
    		</div>
    	</div>
    </div>
    
    <div class="row card-description">
    	<div class="col-md-12">
    		<table class="table table-striped table-hover w-100" id="tabla-contratos">
    			<thead class="thead-dark">
    				<tr>
    					{{-- <th>Nro</th>
    					<th>Cliente</th>
    					<th>Plan</th>
    					<th class="text-center">IP</th>
    					<th class="text-center">Estado</th>
    					<th class="text-center">Grupo de Corte</th> --}}
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
    window.addEventListener('load',
    function() {
		$('#tabla-contratos').DataTable({
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
			ajax: '{{url("/contratos/0")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
			columns: [
			    @foreach($tabla as $campo)
                {data: '{{$campo->campo}}'},
                @endforeach
			    /*{ data: 'nro' },
				{ data: 'client_id' },
				{ data: 'plan' },
				{ data: 'ip' },
				{ data: 'state' },
				{ data: 'grupo_corte' },*/
				{ data: 'acciones' },
			]
		});
		
		tabla = $('#tabla-contratos');
		
        tabla.on('preXhr.dt', function(e, settings, data) {
			data.cliente_id = $('#client_id').val();
            data.plan = $('#plan').val();
            data.state = $('#state').val();
			data.grupo_corte = $('#grupo_cort').val();
			data.ip = $('#ip').val();
			data.mac = $('#mac').val();
            data.conexion = $("#conexion_s").val();
            data.server_configuration_id = $("#server_configuration_id_s").val();
            data.interfaz = $("#interfaz_s").val();
            data.nodo = $("#nodo_s").val();
            data.ap = $("#ap_s").val();
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
		$('#client_id').val('').selectpicker('refresh');
		$('#plan').val('').selectpicker('refresh');
		$('#grupo_cort').val('').selectpicker('refresh');
		$('#state').val('').selectpicker('refresh');
		$('#ip').val('');
		$('#mac').val('');
        $("#conexion_s").val('').selectpicker('refresh');
        $("#server_configuration_id_s").val('').selectpicker('refresh');
        $("#interfaz_s").val('').selectpicker('refresh');
        $("#nodo_s").val('').selectpicker('refresh');
        $("#ap_s").val('').selectpicker('refresh');

		$('#form-filter').addClass('d-none');
		$('#boton-filtrar').html('<i class="fas fa-search"></i> Filtrar');
		getDataTable();
	}
	
	function exportar() {
	    window.location.href = '{{config('app.url')}}/empresa/contratos/exportar?client_id='+$('#client_id').val()+'&plan='+$('#plan').val()+'&ip='+$('#ip').val()+'&mac='+$('#mac').val()+'&state='+$('#state').val()+'&grupo_cort='+$('#grupo_cort').val();
	}

	@if($tipo)
	    $('#state').val('{{ $tipo }}').selectpicker('refresh');
	    abrirFiltrador();
	    getDataTable();
	@endif
</script>
@endsection
