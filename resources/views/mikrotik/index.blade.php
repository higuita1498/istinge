@extends('layouts.app')
@section('boton')
    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">Integra Colombia: Suscripción Vencida</h4>
	       <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
	    </div>
	@else
    @if(isset($_SESSION['permisos']['435']))
        <a href="{{route('planes-velocidad.create')}}" class="btn btn-outline-info btn-sm" ><i class="fas fa-plus"></i> Nuevo Plan</a>
    @endif
    @if(isset($_SESSION['permisos']['432']))
    <a href="{{route('mikrotik.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nueva Mikrotik</a>
    @endif
    <a href="javascript:abrirFiltrador()" class="btn btn-info btn-sm my-1" id="boton-filtrar"><i class="fas fa-search"></i>Filtrar</a>
    @endif
@endsection

@section('content')
@if(isset($_SESSION['permisos']['429']))
	@if(Session::has('success'))
		<div class="alert alert-success" >
			{{Session::get('success')}}
		</div>

		<script type="text/javascript">
			setTimeout(function(){
			    $('.alert').hide();
			    $('.active_table').attr('class', ' ');
			}, 50000);
		</script>
	@endif
	
	@if(Session::has('danger'))
		<div class="alert alert-danger" >
			{{Session::get('danger')}}
		</div>

		<script type="text/javascript">
			setTimeout(function(){
			    $('.alert').hide();
			    $('.active_table').attr('class', ' ');
			}, 50000);
		</script>
	@endif

	<div class="container-fluid d-none" id="form-filter">
		<fieldset>
			<legend>Filtro de Búsqueda</legend>
			<div class="card shadow-sm border-0">
				<div class="card-body pb-3 pt-2" style="background: #f9f9f9;">
					<div class="row">
						<div class="col-md-3 pl-1 pt-1">
							<input type="text" placeholder="Nombre" id="nombre" class="form-control rounded">
						</div>
						<div class="col-md-3 pl-1 pt-1">
							<input type="text" placeholder="IP" id="ip" class="form-control rounded">
						</div>
						<div class="col-md-3 pl-1 pt-1">
							<input type="text" placeholder="Puerto WEB" id="puerto_web" class="form-control rounded">
						</div>
						<div class="col-md-3 pl-1 pt-1">
							<input type="text" placeholder="Puerto API" id="puerto_api" class="form-control rounded">
						</div>
						<div class="col-md-3 pl-1 pt-1">
							<input type="text" placeholder="Puerto WINBOX" id="puerto_winbox" class="form-control rounded">
						</div>
						<div class="col-md-3 pl-1 pt-1">
							<input type="text" placeholder="Interfaz" id="interfaz" class="form-control rounded">
						</div>
						<div class="col-md-3 pl-1 pt-1">
							<input type="text" placeholder="Interfaz LAN" id="interfaz_lan" class="form-control rounded">
						</div>
						<div class="col-md-3 pl-1 pt-1">
						    <select title="Estado" class="form-control rounded selectpicker" id="status">
						        <option value="1">Conectada</option>
								<option value="A">Desconectada</option>
							</select>
						</div>
						<div class="col-md-12 pl-1 pt-2 text-center">
							<a href="javascript:cerrarFiltrador()" class="btn btn-icons ml-1 btn-outline-danger rounded btn-sm p-1" title="Limpiar parámetros de busqueda"><i class="fas fa-times"></i></a>
							<a href="javascript:void(0)" id="filtrar" class="btn btn-icons btn-outline-info rounded btn-sm p-1" title="Iniciar busqueda avanzada"><i class="fas fa-search"></i></a>
						</div>
					</div>
				</div>
			</div>
		</fieldset>
	</div>

	<div class="row card-description">
		@if(isset($_SESSION['permisos']['835']))
		<div class="col-md-12">
    		<div class="container-filtercolumn form-inline">
                @if(auth()->user()->modo_lectura())
                @else
                    <div class="dropdown mr-1">
                    	@if(isset($_SESSION['permisos']['750']))
                    	<a href="{{route('campos.organizar', 15)}}" class="btn btn-warning mr-1"><i class="fas fa-table"></i> Organizar Tabla</a>
                    	@endif
                    	<button class="btn btn-warning dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    		Acciones en Lote
                    	</button>
                    	<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    		<a class="dropdown-item" href="javascript:void(0)" id="btn_enabled"><i class="fas fa-fw fa-power-off" style="margin-left:4px; "></i> Conectar Mikrotiks</a>
                    		<a class="dropdown-item" href="javascript:void(0)" id="btn_disabled"><i class="fas fa-fw fa-power-off" style="margin-left:4px; "></i> Desconectar Mikrotiks</a>
                    		<a class="dropdown-item" href="javascript:void(0)" id="btn_destroy"><i class="fas fa-fw fa-times" style="margin-left:4px; "></i> Eliminar Mikrotiks</a>
                    	</div>
                    </div>
                @endif
			</div>
		</div>
		@endif
		<div class="col-md-12">
			<table class="table table-striped table-hover w-100" id="tabla-mikrotiks">
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
    var tabla = null;
    window.addEventListener('load',
    function() {
		tabla = $('#tabla-mikrotiks').DataTable({
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
			ajax: '{{url("/lmikrotik")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
			columns: [
			    @foreach($tabla as $campo)
                    {data: '{{$campo->campo}}'},
                @endforeach
				{data: 'acciones'},
			],
			@if(isset($_SESSION['permisos']['835']))
			select: true,
            select: {
                style: 'multi',
            },
			dom: 'Blfrtip',
            buttons: [{
            	text: '<i class="fas fa-check"></i> Seleccionar todos',
            	action: function() {
            		tabla.rows({
            			page: 'current'
            		}).select();
            	}
            },
            {
            	text: '<i class="fas fa-times"></i> Deseleccionar todos',
            	action: function() {
            		tabla.rows({
            			page: 'current'
            		}).deselect();
            	}
            }]
            @endif
		});

        tabla.on('preXhr.dt', function(e, settings, data) {
			data.nombre        = $('#nombre').val();
			data.ip            = $('#ip').val();
			data.puerto_web    = $('#puerto_web').val();
			data.puerto_api    = $('#puerto_api').val();
			data.puerto_winbox = $('#puerto_winbox').val();
			data.interfaz      = $('#interfaz').val();
			data.interfaz_lan  = $('#interfaz_lan').val();
			data.status        = $('#status').val();
			data.filtro        = true;
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

        $('#nombre, #ip, #puerto_web, #puerto_api, #puerto_winbox, #interfaz, #interfaz_lan, #status').on('keyup',function(e) {
        	if(e.which > 32 || e.which == 8) {
        		getDataTable();
        		return false;
        	}
        });

        $('#status').on('change',function() {
        	getDataTable();
        	return false;
        });

        $('#btn_enabled').click( function () {
            states('on');
        });

        $('#btn_disabled').click( function () {
            states('off');
        });

        $('#btn_destroy').click( function () {
            destroy();
        });
    });

	function getDataTable() {
		tabla.ajax.reload();
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
		$('#nombre').val('');
		$('#ip').val('');
		$('#puerto_web').val('');
		$('#puerto_api').val('');
		$('#puerto_winbox').val('');
		$('#interfaz').val('');
		$('#interfaz_lan').val('');
		$('#status').val('').selectpicker('refresh');
		$('#form-filter').addClass('d-none');
		$('#boton-filtrar').html('<i class="fas fa-search"></i> Filtrar');
		getDataTable();
	}

	function states(state){
        var mikrotiks = [];

        var table = $('#tabla-mikrotiks').DataTable();
        var nro = table.rows('.selected').data().length;

        if(nro<=1){
            swal({
                title: 'ERROR',
                html: 'Para ejecutar esta acción, debe al menos seleccionar dos mikrotiks',
                type: 'error',
            });
            return false;
        }

        for (i = 0; i < nro; i++) {
            mikrotiks.push(table.rows('.selected').data()[i]['id']);
        }

        if(state === 'on'){
            var states = 'conectar';
        }else{
            var states = 'desconectar';
        }

        swal({
            title: '¿Desea '+states+' '+nro+' mikrotiks en lote?',
            html: 'Al Aceptar, no podrá cancelar el proceso.<br><span class="text-danger font-weight-bold">PUEDE DEMORAR UNOS MINUTOS</span>',
            type: 'question',
            showCancelButton: true,
            confirmButtonColor: '#00ce68',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Aceptar',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (result.value) {
                cargando(true);

                if (window.location.pathname.split("/")[1] === "software") {
                    var url = `/software/empresa/mikrotik/`+mikrotiks+`/`+state+`/state_lote`;
                }else{
                    var url = `/empresa/mikrotik/`+mikrotiks+`/`+state+`/state_lote`;
                }

                $.ajax({
                    url: url,
                    method: 'GET',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(data) {
                        cargando(false);
                        if(data.state === 'on'){
                        	var states = 'conectada(s)';
                        }else{
                        	var states = 'desconectada(s)';

                        }
                        swal({
                            title: 'PROCESO REALIZADO',
                            html: '<strong>'+data.correctos+' mikrotiks '+states+'</strong><br><strong>'+data.fallidos+' mikrotiks no '+states+'</strong>',
                            type: 'success',
                            showConfirmButton: true,
                            confirmButtonColor: '#1A59A1',
                            confirmButtonText: 'ACEPTAR',
                        });
                        getDataTable();
                    }
                })
            }
        })
    }

    function destroy(){
        var mikrotiks = [];

        var table = $('#tabla-mikrotiks').DataTable();
        var nro = table.rows('.selected').data().length;

        if(nro<=1){
            swal({
                title: 'ERROR',
                html: 'Para ejecutar esta acción, debe al menos seleccionar dos mikrotiks',
                type: 'error',
            });
            return false;
        }

        for (i = 0; i < nro; i++) {
            mikrotiks.push(table.rows('.selected').data()[i]['id']);
        }

        swal({
            title: '¿Desea eliminar '+nro+' mikrotiks en lote?',
            text: 'Al Aceptar, no podrá cancelar el proceso',
            type: 'question',
            showCancelButton: true,
            confirmButtonColor: '#00ce68',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Aceptar',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (result.value) {
                cargando(true);

                if (window.location.pathname.split("/")[1] === "software") {
                    var url = `/software/empresa/mikrotik/`+mikrotiks+`/destroy_lote`;
                }else{
                    var url = `/empresa/mikrotik/`+mikrotiks+`/destroy_lote`;
                }

                $.ajax({
                    url: url,
                    method: 'GET',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(data) {
                        cargando(false);
                        swal({
                            title: 'PROCESO REALIZADO',
                            html: '<strong>'+data.correctos+' mikrotiks '+data.state+'</strong><br><strong>'+data.fallidos+' mikrotiks no '+data.state+' por estar en uso</strong>',
                            type: 'success',
                            showConfirmButton: true,
                            confirmButtonColor: '#1A59A1',
                            confirmButtonText: 'ACEPTAR',
                        });
                        getDataTable();
                    }
                })
            }
        })
    }
</script>
@endsection