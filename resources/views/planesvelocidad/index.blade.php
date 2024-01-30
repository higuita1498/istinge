@extends('layouts.app')

@section('style')
    <style>
		td .elipsis-short-300 {
			width: 300px;
			overflow: hidden;
			white-space: nowrap;
			text-overflow: ellipsis;
			display: inline-block;
		}
		@media all and (max-width: 768px){
			td .elipsis-short-300 {
				width: 200px;
				overflow: hidden;
				white-space: nowrap;
				text-overflow: ellipsis;
				display: inline-block;
			}
		}
	</style>
@endsection

@section('boton')
    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">Integra Colombia: Suscripción Vencida</h4>
	       <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
	    </div>
	@else
        @if(isset($_SESSION['permisos']['435']))
            <a href="{{route('planes-velocidad.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Plan</a>
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
		<fieldset>
			<legend>Filtro de Búsqueda</legend>
			<div class="card shadow-sm border-0">
				<div class="card-body pb-3 pt-2" style="background: #f9f9f9;">
					<div class="row">
                        <div class="col-md-3 pl-1 pt-1">
							<input type="text" placeholder="Nombre" id="nombre" class="form-control rounded">
						</div>
						<div class="col-md-3 pl-1 pt-1">
							{{-- <input type="text" placeholder="Nombre" id="name" class="form-control rounded"> --}}
                            <select title="Planes" class="form-control rounded selectpicker" id="name" data-size="5" data-live-search="true">
								@foreach($planes_velocidad as $plan)
								<option value="{{$plan->name}}">{{$plan->name}}</option>
								@endforeach
							</select>
						</div>
						<div class="col-md-3 pl-1 pt-1">
							<input type="number" placeholder="Precio" id="price" class="form-control rounded">
						</div>
						<div class="col-md-3 pl-1 pt-1">
							<input type="number" placeholder="Vel. Descarga" id="download" class="form-control rounded">
						</div>
						<div class="col-md-3 pl-1 pt-1">
							<input type="number" placeholder="Vel. Subida" id="upload" class="form-control rounded">
						</div>
						<div class="col-md-3 pl-1 pt-1">
							<select title="Tipo" class="form-control rounded selectpicker" id="type" data-size="5" data-live-search="true">
								<option value="A">Queue Simple</option>
	        	                <option value="1">PCQ</option>
							</select>
						</div>
						<div class="col-md-3 pl-1 pt-1">
							<select title="Mikrotik" class="form-control rounded selectpicker" id="mikrotik_s" data-size="5" data-live-search="true">
								@foreach($mikrotiks as $mikrotik)
								<option value="{{$mikrotik->id}}">{{$mikrotik->nombre}}</option>
								@endforeach
							</select>
						</div>
						<div class="col-md-3 pl-1 pt-1">
							<select title="Estado" class="form-control rounded selectpicker" id="status" data-size="5" data-live-search="true">
								<option value="A">Deshabilitado</option>
	        	                <option value="1">Habilitado</option>
							</select>
						</div>
						<div class="col-md-3 pl-1 pt-1">
							<select title="Tipo Plan" class="form-control rounded selectpicker" id="tipo_plan" data-size="5" data-live-search="true">
								<option value="1">Residencial</option>
	        	                <option value="2">Corporativo</option>
							</select>
						</div>

						<div class="col-md-1 pl-1 pt-1 text-left">
							<a href="javascript:cerrarFiltrador()" class="btn btn-icons ml-1 btn-outline-danger rounded btn-sm p-1 float-right" title="Limpiar parámetros de busqueda"><i class="fas fa-times"></i></a>
                            @if(!isset($_SESSION['permisos']['858']))
                                <a href="javascript:void(0)" id="filtrar" class="btn btn-icons btn-outline-info rounded btn-sm p-1 float-right" title="Iniciar busqueda avanzada"><i class="fas fa-search"></i></a>
                            @else
                                <a href="javascript:alerta()" id="filtrar" class="btn btn-icons btn-outline-info rounded btn-sm p-1 float-right" title="Iniciar busqueda avanzada"><i class="fas fa-search"></i></a>
                            @endif
                        </div>
					</div>
				</div>
			</div>
		</fieldset>
	</div>

	<div class="row card-description">
		@if(isset($_SESSION['permisos']['834']))
		<div class="col-md-12">
    		<div class="container-filtercolumn form-inline">
                @if(auth()->user()->modo_lectura())
                @else
                    <div class="dropdown mr-1">
                        @if(isset($_SESSION['permisos']['750']))
                <a href="{{route('campos.organizar', 10)}}" class="btn btn-warning my-1"><i class="fas fa-table"></i> Organizar Tabla</a>
                @endif
                    	<button class="btn btn-warning dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    		Acciones en Lote
                    	</button>
                    	<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    		<a class="dropdown-item" href="javascript:void(0)" id="btn_enabled"><i class="fas fa-fw fa-power-off" style="margin-left:4px; "></i> Habilitar Planes</a>
                    		<a class="dropdown-item" href="javascript:void(0)" id="btn_disabled"><i class="fas fa-fw fa-power-off" style="margin-left:4px; "></i> Deshabilitar Planes</a>
                    		<a class="dropdown-item" href="javascript:void(0)" id="btn_destroy"><i class="fas fa-fw fa-times" style="margin-left:4px; "></i> Eliminar Planes</a>
                    	</div>
                    </div>
                @endif
			</div>
		</div>
		@endif

		<div class="col-md-12">
			<table class="table table-striped table-hover w-100" id="tabla-planes">
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
    function alerta(){
        alert("estas consultando");
    }
    // Evento que escucha cuando se hace clic en el botón específico
     document.getElementById('filtrar').addEventListener('click', function() {
    //     // Llama a la función de inicialización cuando se hace clic en el botón
         inicializarDataTable();
     });
     function getDataTable() {
         tabla.ajax.reload();
     }

    @if(!isset($_SESSION['permisos']['858']))
    window.addEventListener('load',
    function() {
		tabla = $('#tabla-planes').DataTable({
			responsive: true,
			serverSide: true,
			processing: true,
			searching: false,
			language: {
				'url': '/vendors/DataTables/es.json'
			},
			order: [
				[0, "asc"]
			],
			"pageLength": {{ Auth::user()->empresa()->pageLength }},
			ajax: '{{url("/planes")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
            columns: [
                    @foreach($tabla as $campo)
                    {data: '{{$campo->campo}}'},
                    @endforeach
                    {data: 'acciones'},
            ],

			@if(isset($_SESSION['permisos']['834']))
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
            //data.serial_onu = $('#serial_onu').val();
            data.name = $('#name').val();
            data.nombre = $('#nombre').val();
            data.price = $('#price').val();
            data.download = $('#download').val();
            data.upload = $('#upload').val();
            data.type = $('#type').val();
            data.mikrotik_s = $('#mikrotik_s').val();
            data.status = $('#status').val();
            data.tipo_plan = $('#tipo_plan').val();
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

        $('#name, #price, #download, #upload, #nombre').on('keyup',function(e) {
        	if(e.which > 32 || e.which == 8) {
        		getDataTable();
        		return false;
        	}
        });

        $('#type, #mikrotik_s, #status, #tipo_plan').on('change',function() {
        	getDataTable();
        	return false;
        });

        $('#btn_enabled').click( function () {
            states('enabled');
        });

        $('#btn_disabled').click( function () {
            states('disabled');
        });

        $('#btn_destroy').click( function () {
            destroy();
        });
    });
    @endif
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
		$('#name').val('');
        $('#nombre').val('');
		$('#price').val('');
		$('#download').val('');
		$('#upload').val('');
		$('#type').val('').selectpicker('refresh');
		$('#mikrotik_s').val('').selectpicker('refresh');
		$('#status').val('').selectpicker('refresh');
		$('#tipo_plan').val('').selectpicker('refresh');
		$('#form-filter').addClass('d-none');
		$('#boton-filtrar').html('<i class="fas fa-search"></i> Filtrar');
		getDataTable();
	}

	function states(state){
        var planes = [];

        var table = $('#tabla-planes').DataTable();
        var nro = table.rows('.selected').data().length;

        if(nro<=1){
            swal({
                title: 'ERROR',
                html: 'Para ejecutar esta acción, debe al menos seleccionar dos planes',
                type: 'error',
            });
            return false;
        }

        for (i = 0; i < nro; i++) {
            planes.push(table.rows('.selected').data()[i]['id']);
        }

        if(state === 'enabled'){
            var states = 'habilitar';
        }else{
            var states = 'deshabilitar';
        }

        swal({
            title: '¿Desea '+states+' '+nro+' planes en lote?',
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
                    var url = `/software/empresa/planes-velocidad/`+planes+`/`+state+`/state_lote`;
                }else{
                    var url = `/empresa/planes-velocidad/`+planes+`/`+state+`/state_lote`;
                }

                $.ajax({
                    url: url,
                    method: 'GET',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(data) {
                        cargando(false);
                        if(data.state === 'enabled'){
                        	var states = 'habilitados';
                        }else{
                        	var states = 'deshabilitados';

                        }
                        swal({
                            title: 'PROCESO REALIZADO',
                            html: '<strong>'+data.correctos+' planes '+states+'</strong><br><strong>'+data.fallidos+' planes no '+states+'</strong>',
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
        var planes = [];

        var table = $('#tabla-planes').DataTable();
        var nro = table.rows('.selected').data().length;

        if(nro<=1){
            swal({
                title: 'ERROR',
                html: 'Para ejecutar esta acción, debe al menos seleccionar dos planes',
                type: 'error',
            });
            return false;
        }

        for (i = 0; i < nro; i++) {
            planes.push(table.rows('.selected').data()[i]['id']);
        }

        swal({
            title: '¿Desea eliminar '+nro+' planes en lote?',
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
                    var url = `/software/empresa/planes-velocidad/`+planes+`/destroy_lote`;
                }else{
                    var url = `/empresa/planes-velocidad/`+planes+`/destroy_lote`;
                }

                $.ajax({
                    url: url,
                    method: 'GET',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(data) {
                        cargando(false);
                        swal({
                            title: 'PROCESO REALIZADO',
                            html: '<strong>'+data.correctos+' planes '+data.state+'</strong><br><strong>'+data.fallidos+' planes no '+data.state+' por estar en uso</strong>',
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

    //metodo
    function inicializarDataTable() {
        var tabla = $('#tabla-planes').DataTable({
            responsive: true,
            serverSide: true,
            processing: true,
            searching: false,
            language: {
                'url': '/vendors/DataTables/es.json'
            },
            order: [
                [0, "asc"]
            ],
            "pageLength": {{ Auth::user()->empresa()->pageLength }},
            ajax: '{{url("/planes")}}',
            headers: {
                'X-CSRF-TOKEN': '{{csrf_token()}}'
            },
            columns: [
                @foreach($tabla as $campo)
                {data: '{{$campo->campo}}'},
                @endforeach
                {data: 'acciones'},
            ],

            @if(isset($_SESSION['permisos']['834']))
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
    }
</script>
@endsection
