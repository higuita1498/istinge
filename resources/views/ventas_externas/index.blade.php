@extends('layouts.app')

@section('styles')

@endsection

@section('boton')
    @if(auth()->user()->modo_lectura())
        <div class="alert alert-warning text-left" role="alert">
            <h4 class="alert-heading text-uppercase">Integra Colombia: Suscripción Vencida</h4>
           <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
        </div>
    @else
        @if(isset($_SESSION['permisos']['801']))
	        <a href="{{route('ventas-externas.create')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nueva Venta</a>
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

	@if(isset($_SESSION['permisos']['800']))
		<div class="container-fluid d-none" id="form-filter">
			<fieldset>
				<legend>Filtro de Búsqueda</legend>
				<div class="card shadow-sm border-0 mb-3" style="background: #ffffff00 !important;">
					<div class="card-body py-0">
						<div class="row">
							<div class="col-md-3 pl-1 pt-1">
								<input type="text" placeholder="Nombres/Apellidos" id="nombre" class="form-control rounded">
							</div>
							<div class="col-md-3 pl-1 pt-1">
								<input type="number" placeholder="Identificación" id="identificacion" class="form-control rounded">
							</div>
							<div class="col-md-3 pl-1 pt-1">
								<input type="number" placeholder="Teléfono" id="telefono" class="form-control rounded">
							</div>
							<div class="col-md-3 pl-1 pt-1">
								<input type="text" placeholder="Email" id="email" class="form-control rounded">
							</div>
							<div class="col-md-3 pl-1 pt-1">
								<input type="text" placeholder="Dirección" id="direccion" class="form-control rounded">
							</div>
							<div class="col-md-3 pl-1 pt-1">
								<input type="text" placeholder="Barrio" id="barrio" class="form-control rounded">
							</div>
							<div class="col-md-3 pl-1 pt-1">
								<select title="Estrato" class="form-control rounded selectpicker" id="estrato" data-size="5" data-live-search="true">
									<option value="1">1</option>
									<option value="2">2</option>
									<option value="3">3</option>
									<option value="4">4</option>
									<option value="5">5</option>
									<option value="6">6</option>
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

							<div class="col-md-1 pl-1 pt-1 text-left">
								<a href="javascript:cerrarFiltrador()" class="btn btn-icons ml-1 btn-outline-danger rounded btn-sm p-1 float-right" title="Limpiar parámetros de busqueda"><i class="fas fa-times"></i></a>
								<a href="javascript:void(0)" id="filtrar" class="btn btn-icons btn-outline-info rounded btn-sm p-1 float-right" title="Iniciar busqueda avanzada"><i class="fas fa-search"></i></a>
							</div>
						</div>
					</div>
				</div>
			</fieldset>
		</div>

		<div class="row card-description">
			@if(isset($_SESSION['permisos']['842']) || isset($_SESSION['permisos']['750']))
				<div class="col-md-12">
		    		<div class="container-filtercolumn form-inline">
		                @if(auth()->user()->modo_lectura())
		                @else
		                    <div class="dropdown mr-1">
		                    	@if(isset($_SESSION['permisos']['750']))
		                    	<a href="{{route('campos.organizar', 14)}}" class="btn btn-warning mr-1"><i class="fas fa-table"></i> Organizar Tabla</a>
		                    	@endif
		                    	<button class="btn btn-warning dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
		                    		Acciones en Lote
		                    	</button>
		                    	<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
		                    		<a class="dropdown-item" href="javascript:void(0)" id="btn_enabled"><i class="fas fa-fw fa-check" style="margin-left:4px; "></i> Aprobar Ventas Externas</a>
		                    		<a class="dropdown-item" href="javascript:void(0)" id="btn_destroy"><i class="fas fa-fw fa-times" style="margin-left:4px; "></i> Eliminar Ventas Externas</a>
		                    	</div>
		                    </div>
		                @endif
					</div>
				</div>
			@endif
			<div class="col-md-12">
				<table class="table table-striped table-hover w-100" id="tabla-ventas-externas">
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
    	tabla = $('#tabla-ventas-externas').DataTable({
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
			ajax: '{{url("/ventas-externas")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
			columns: [
			    @foreach($tabla as $campo)
                    {data: '{{$campo->campo}}'},
                @endforeach
				{data: 'acciones'},
			],
			@if(isset($_SESSION['permisos']['842']))
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
            data.nombre = $('#nombre').val();
            data.identificacion = $('#identificacion').val();
            data.telefono1 = $('#telefono').val();
            data.direccion = $('#direccion').val();
            data.barrio = $('#barrio').val();
            data.email = $('#email').val();
            data.estrato = $('#estrato').val();
            data.vendedor = $('#vendedor').val();
            data.canal = $('#canal').val();
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

        $('#btn_enabled').click( function () {
            aprobar();
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
		$('#identificacion').val('');
		$('#telefono').val('');
		$('#direccion').val('');
		$('#barrio').val('');
		$('#email').val('');;
		$('#estrato').val('').selectpicker('refresh');
		$('#vendedor').val('').selectpicker('refresh');
		$('#canal').val('').selectpicker('refresh');
		$('#form-filter').addClass('d-none');
		$('#boton-filtrar').html('<i class="fas fa-search"></i> Filtrar');
		getDataTable();
	}

	function aprobar(){
        var ventas = [];

        var table = $('#tabla-ventas-externas').DataTable();
        var nro = table.rows('.selected').data().length;

        if(nro<=1){
            swal({
                title: 'ERROR',
                html: 'Para ejecutar esta acción, debe al menos seleccionar dos ventas externas',
                type: 'error',
            });
            return false;
        }

        for (i = 0; i < nro; i++) {
            ventas.push(table.rows('.selected').data()[i]['id']);
        }

        var states = 'aprobar';

        swal({
            title: '¿Desea '+states+' '+nro+' ventas externas en lote?',
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
                    var url = `/software/empresa/ventas-externas/`+ventas+`/state_lote`;
                }else{
                    var url = `/empresa/ventas-externas/`+ventas+`/state_lote`;
                }

                $.ajax({
                    url: url,
                    method: 'GET',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(data) {
                        cargando(false);
                        swal({
                            title: 'PROCESO REALIZADO',
                            html: '<strong>'+data.correctos+' ventas externas '+states+'</strong><br><strong>'+data.fallidos+' ventas externas no '+states+'</strong>',
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
        var ventas = [];

        var table = $('#tabla-ventas-externas').DataTable();
        var nro = table.rows('.selected').data().length;

        if(nro<=1){
            swal({
                title: 'ERROR',
                html: 'Para ejecutar esta acción, debe al menos seleccionar dos ventas externas',
                type: 'error',
            });
            return false;
        }

        for (i = 0; i < nro; i++) {
            ventas.push(table.rows('.selected').data()[i]['id']);
        }

        swal({
            title: '¿Desea eliminar '+nro+' ventas externas en lote?',
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
                    var url = `/software/empresa/ventas-externas/`+ventas+`/destroy_lote`;
                }else{
                    var url = `/empresa/ventas-externas/`+ventas+`/destroy_lote`;
                }

                $.ajax({
                    url: url,
                    method: 'GET',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(data) {
                        cargando(false);
                        swal({
                            title: 'PROCESO REALIZADO',
                            html: '<strong>'+data.correctos+' ventas externas '+data.state+'</strong><br><strong>'+data.fallidos+' ventas externas no '+data.state+'</strong>',
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
