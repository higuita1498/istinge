@extends('layouts.app')

@section('style')
<style>
    .stopwatch .controls {
        font-size: 12px;
    }
    .stopwatch .controls button{
        padding: 5px 15px;
        background :#EEE;
        border: 3px solid #06C;
        border-radius: 5px
    }
    .stopwatch .time {
        font-size: 2em;
    }
    .bg-th {
        background: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}!important;
        color: #fff!important;
    }
    .table .thead-dark th {
        color: #fff;
        background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
        border-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
    }
    .btn-dark {
	    background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
	    border-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
	}
	.btn-dark:hover, .btn-dark:active {
	    background-color: #113951;
	    border-color: #113951;
	}
    .nav-tabs .nav-link {
        font-size: 1em;
    }
    .nav-tabs .nav-link.active, .nav-tabs .nav-item.show .nav-link {
        background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
        color: #fff!important;
    }
    .table .thead-light th {
        color: #fff!important;
        background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}!important;
        border-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}!important;
    }
    .nav-pills .nav-link.active, .nav-pills .show > .nav-link {
        color: #fff!important;
        background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}!important;
    }
    .nav-pills .nav-link {
        font-weight: 700!important;
    }
    .nav-pills .nav-link{
        color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}!important;
        background-color: #f9f9f9!important;
        margin: 2px;
        border: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
        transition: 0.4s;
    }
    .nav-pills .nav-link:hover {
        color: #fff!important;
        background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}!important;
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
    @if(isset($_SESSION['permisos']['411']))
        <a href="{{route('contratos.create')}}" class="btn btn-outline-info btn-sm"><i class="fas fa-plus"></i> Nuevo Contrato</a>
    @endif
    @if(isset($_SESSION['permisos']['201']))
        <a href="{{route('radicados.create')}}" class="btn btn-outline-info btn-sm"><i class="fas fa-plus"></i> Nuevo Radicado</a>
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
    
    <div class="container-fluid d-none" id="form-filter">
    	<div class="card shadow-sm border-0 mb-3" style="background: #ffffff00 !important;">
    		<div class="card-body py-0">
    			<div class="row">
    				<div class="col-md-2 offset-md-1 pl-0 pt-1">
    					<select title="Cliente" class="form-control rounded selectpicker" id="cliente" data-size="5" data-live-search="true">
							@foreach ($clientes as $cliente)
								<option value="{{ $cliente->id}}">{{$cliente->nombre}} {{$cliente->apellido1}} {{$cliente->apellido2}} - {{ $cliente->nit}}</option>
							@endforeach
						</select>
    				</div>
    				<div class="col-md-2 pl-0 pt-1">
    					<select title="Gestionado" class="form-control rounded selectpicker" id="created_by" data-size="5" data-live-search="true">
							@foreach ($usuarios as $usuario)
								<option value="{{ $usuario->id}}">{{ $usuario->nombres}}</option>
							@endforeach
						</select>
    				</div>
    				<div class="col-md-2 pl-1 pt-1">
    				    <select title="Servidor" class="form-control rounded selectpicker" id="servidor" data-size="5" data-live-search="true">
                			@foreach ($servidores as $servidor)
                			<option value="{{ $servidor->id}}">{{ $servidor->name}}</option>
                			@endforeach
                		</select>
                    </div>
                    <div class="col-md-2 pl-1 pt-1">
                        <select title="Grupo Corte" class="form-control rounded selectpicker" id="grupo_corte_q" data-size="5" data-live-search="true">
                			@foreach ($grupos_corte as $grupo_corte)
								<option value="{{ $grupo_corte->id}}">{{ $grupo_corte->nombre}}</option>
							@endforeach
                		</select>
                    </div>
    				<div class="col-md-2 pl-0 pt-1 d-none">
    					<select title="Factura" class="form-control rounded selectpicker" id="estatus" data-size="5" data-live-search="true">
							<option value="A">Cerrada (Pagada)</option>
							<option value="1">Abierta (Sin Pagar)</option>
						</select>
    				</div>
    				
    				{{--<div class="col-md-2 offset-md-1 pl-0 pt-1">--}}
    				<div class="col-md-2 pl-0 pt-1">
    					<select title="Estado" class="form-control rounded selectpicker" id="estado" data-size="5" data-live-search="true">
							{{--<option value="A">Sin Gestionar</option>--}}
							<option value="1">Gestionado</option>
							<option value="3">Gestionado/Sin Contestar</option>
							<option value="6">Gestionado / Nro Equivocado</option>
							<option value="2">Promesa Incumplida</option>
							<option value="4">Retirado</option>
							<option value="5">Retirado Total</option>
						</select>
    				</div>
    				
    				{{--<div class="col-md-2 pl-1 pt-1">--}}
    				<div class="col-md-2 offset-md-1 pl-0 pt-1">
    					<input type="text" class="form-control datepicker"  id="desde" value="{{$ini}}" name="fecha" required="" >
    				</div>
    				<div class="col-md-2 pl-1 pt-1">
    					<input type="text" class="form-control datepickerinput" id="hasta" value="{{$fin}}" name="hasta" required="">
    				</div>
    				<div class="col-md-2 pl-0 pt-1 text-left">
    					<a href="javascript:cerrarFiltrador()" class="btn btn-icons ml-1 btn-outline-danger rounded btn-sm p-1 float-right" title="Limpiar parámetros de busqueda"><i class="fas fa-times"></i></a>
    					<a href="javascript:void(0)" id="filtrar" class="btn btn-icons btn-outline-info rounded btn-sm p-1 float-right" title="Iniciar busqueda avanzada"><i class="fas fa-search"></i></a>
    					<a href="javascript:exportar()" class="btn btn-icons mr-1 btn-outline-success rounded btn-sm p-1 float-right" title="Exportar"><i class="fas fa-file-excel"></i></a>
    				</div>
    			</div>
    		</div>
    	</div>
    </div>
    
    <div class="row card-description">
    	<div class="col-md-12">        
			<div class="table-responsive mt-3">
			    <table class="table table-striped table-hover w-100" id="table_gestionado">
			        <thead class="thead-dark">
			            <tr>
			                <th>Nombre</th>
			                <th class="text-center">Identificación</th>
			                <th class="text-center">Teléfono</th>
			                <th class="text-center">Estado</th>
			                <th class="text-center">Gestionado por</th>
			                <th class="text-center">Gestionado el</th>
			                {{--<th class="text-center">Factura</th>--}}
			                <th class="text-center">Acciones</th>
			            </tr>
			        </thead>
			    </table>
		    </div>
    	</div>
    </div>

@endsection

@section('scripts')
<script>
    var tabla = null;
    window.addEventListener('load',
    function() {
		$('#table_gestionado').DataTable({
			responsive: true,
			serverSide: true,
			processing: true,
			searching: false,
			paging:   true,
			language: {
				'url': '/vendors/DataTables/es.json'
			},
			order: [
				[0, "asc"]
			],
			"pageLength": {{ Auth::user()->empresa()->pageLength }},
			ajax: '{{url("/reporte")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
			columns: [
			    {data: 'nombre'},
			    {data: 'nit'},
			    {data: 'celular'},
				{data: 'estado'},
				{data: 'created_by'},
				{data: 'updated_at'},
				//{data: 'estatus'},
				{data: 'acciones'},
			]
		});
        tabla = $('#table_gestionado');
        tabla.on('preXhr.dt', function(e, settings, data) {
            data.cliente = $('#cliente').val();
            data.estado = $('#estado').val();
            data.created_by = $('#created_by').val();
            data.desde = $('#desde').val();
            data.hasta = $('#hasta').val();
            data.estatus = $('#estatus').val();
            data.estado = $('#estado').val();
            data.servidor = $('#servidor').val();
            data.grupo_corte = $('#grupo_corte_q').val();
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
		$('#cliente').val('');
		$('#estado').val('').selectpicker('refresh');
		$('#created_by').val('').selectpicker('refresh');
		$('#updated_at').val('');
		$('#estatus').val('').selectpicker('refresh');
		$('#servidor').val('').selectpicker('refresh');
		$('#grupo_corte_q').val('').selectpicker('refresh');
		$('#form-filter').addClass('d-none');
		$('#boton-filtrar').html('<i class="fas fa-search"></i> Filtrar');
		getDataTable();
	}
	
	function exportar() {
	    //window.location.href = 'https://intercarnet.com/software/empresa/crm/exportar?desde='+$('#desde').val()+'&hasta='+$('#hasta').val()+'&cliente='+$('#cliente').val()+'&created_by='+$('#created_by').val()+'&estatus='+$('#estatus').val()+'&servidor='+$('#servidor').val()+'&grupo_corte='+$('#grupo_corte').val()+'&estado='+$('#estado').val();
	    window.location.href = '{{config('app.url')}}/empresa/crm/exportar?desde='+$('#desde').val()+'&hasta='+$('#hasta').val()+'&cliente='+$('#cliente').val()+'&created_by='+$('#created_by').val()+'&servidor='+$('#servidor').val()+'&grupo_corte='+$('#grupo_corte_q').val()+'&estado='+$('#estado').val();
	}
</script>
@endsection
