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
        <div class="alert alert-warning text-left" role="alert">
            <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
            <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
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
    						<option value="{{ $cliente->id }}">{{ $cliente->nombre }} - {{ $cliente->nit }}</option>
    						@endforeach
    					</select>
    				</div>
    				<div class="col-md-2 pl-1 pt-1">
    					<input type="text" class="form-control" id="celular" placeholder="Celular">
    				</div>
    				<div class="col-md-2 pl-1 pt-1">
    					<input type="text" class="form-control" id="email" placeholder="Email">
    				</div>
    				<div class="col-md-2 pl-1 pt-1">
    					<input type="text" class="form-control" id="direccion" placeholder="Dirección">
    				</div>
    				<div class="col-md-2 pl-1 pt-1">
    					<input type="text" class="form-control" id="barrio" placeholder="Barrio">
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
                    <div class="col-md-3 pl-1 pt-1">
                        <select title="Vendedor" class="form-control selectpicker" id="vendedor">
                            @foreach ($vendedores as $vendedor)
                            <option value="{{ $vendedor->id }}">{{ $vendedor->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 pl-1 pt-1">
                        <select title="Canal de Venta" class="form-control selectpicker" id="canal">
                            @foreach ($canales as $canal)
                            <option value="{{ $canal->id }}">{{ $canal->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 pl-1 pt-1">
                        <select title="Tipo de Tecnología" class="form-control selectpicker" id="tecnologia_s">
                            <option value="1">Fibra</option>
                            <option value="2">Inalámbrica</option>
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
    		<div class="container-filtercolumn">
                <a href="{{ route('pings.index') }}" class="btn btn-danger">Ver Pings Fallidos <i class="fa fa-plug"></i></a>
    			<a  onclick="filterOptions()" class="btn btn-secondary" value="0" id="buttonfilter">Filtrar  Campos<i class="fas fa-filter" style="margin-left:4px; "></i></a>
    			<ul class="options-search-columns"  id="columnOptions">
    				@foreach($tabla as $campo)
    				    <li><input type="button" class="btn btn-success btn-sm boton_ocultar_mostrar" value="{{$campo->nombre}}"></li>
    				@endforeach
				</ul>
			</div>
		</div>
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
		var tbl = $('#tabla-contratos').DataTable({
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
            data.c_barrio = $("#barrio").val();
            data.c_direccion = $("#direccion").val();
            data.c_celular = $("#celular").val();
            data.c_email = $("#email").val();
            data.vendedor = $("#vendedor").val();
            data.canal = $("#canal").val();
            data.tecnologia = $("#tecnologia_s").val();
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

        $(".boton_ocultar_mostrar").on('click', function(){
        	var indice = $(this).index(".boton_ocultar_mostrar");
        	$(".boton_ocultar_mostrar").eq(indice).toggleClass("btn-danger");
        	var columna = tbl.column(indice);
        	columna.visible(!columna.visible());
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
        $('#barrio').val('');
        $('#direccion').val('');
        $('#celular').val('');
        $('#email').val('');
        $("#vendedor").val('').selectpicker('refresh');
        $("#canal").val('').selectpicker('refresh');
        $("#tecnologia_s").val('').selectpicker('refresh');

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
