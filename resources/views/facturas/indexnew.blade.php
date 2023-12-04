@extends('layouts.app')


@section('boton')
    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">Integra Colombia: Suscripción Vencida</h4>
	       <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
	    </div>
	@else
        <a href="javascript:abrirFiltrador()" class="btn btn-info btn-sm my-1" id="boton-filtrar"><i class="fas fa-search"></i>Filtrar</a>
        @if(!isset($_SESSION['permisos']['42']))
                 <a href="{{route('facturas.create')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nueva Factura de Venta</a>
        @endif
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

	@if(Session::has('error'))
		<div class="alert alert-danger" >
			{{Session::get('error')}}
		</div>

		<script type="text/javascript">
			setTimeout(function(){
			    $('.alert').hide();
			    $('.active_table').attr('class', ' ');
			}, 8000);
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
		<fieldset>
            <legend>Filtro de Búsqueda</legend>
			<div class="card shadow-sm border-0">
        		<div class="card-body pt-1 pb-3" style="background: #f9f9f9;">
					<div class="row">
						<div class="col-md-1 pl-1 pt-1">
							<input type="text" placeholder="Nro" id="codigo" class="form-control rounded">
						</div>
						<div class="col-md-2 pl-1 pt-1">
							<select title="Cliente" class="form-control rounded selectpicker" id="cliente" data-size="5" data-live-search="true">
								@foreach ($clientes as $cliente)
									<option value="{{ $cliente->id}}">{{ $cliente->nombre}} {{$cliente->apellido1}} {{$cliente->apellido2}} - {{ $cliente->nit}}</option>
								@endforeach
							</select>
						</div>
						<div class="col-md-2 pl-1 pt-1">
							<select title="Municipio" class="form-control rounded selectpicker" id="municipio" data-size="5" data-live-search="true">
								@foreach ($municipios as $municipio)
									<option value="{{ $municipio->id}}">{{ $municipio->nombre}}</option>
								@endforeach
							</select>
						</div>
						<div class="col-md-2 pl-1 pt-1">
							<input type="text" placeholder="Creación" id="creacion" name="creacion" class="form-control rounded creacion" autocomplete="off">
						</div>
						<div class="col-md-2 pl-1 pt-1">
							<input type="text" placeholder="Vencimiento" id="vencimiento" name="vencimiento" class="form-control rounded vencimiento" autocomplete="off">
						</div>
						<div class="col-md-2 pl-1 pt-1">
							<select title="Servidor" class="form-control rounded selectpicker" id="servidor">
								@foreach ($servidores as $servidor)
								<option value="{{ $servidor->id}}">{{ $servidor->nombre}}</option>
								@endforeach
							</select>
						</div>
						<div class="col-md-2 pl-1 pt-1">
							<select title="Estado" class="form-control rounded selectpicker" id="estado">
								<option value="1" selected="">Abiertas</option>
								<option value="A">Cerradas</option>
								<option value="2">Anuladas</option>
							</select>
						</div>
						<div class="col-md-2 pl-1 pt-1 d-none">
							<select title="Enviada a Correo" class="form-control rounded selectpicker" id="correo">
								<option value="1">Si</option>
								<option value="A">No</option>
							</select>
						</div>
					</div>
					<div class="row">
						<div class="col-md-12 pl-1 pt-1 text-center">
							<a href="javascript:limpiarFiltrador()" class="btn btn-icons ml-1 btn-outline-danger rounded btn-sm p-1" title="Limpiar parámetros de busqueda"><i class="fas fa-times"></i></a>
							<a href="javascript:void(0)" id="filtrar" class="btn btn-icons btn-outline-info rounded btn-sm p-1" title="Iniciar busqueda avanzada"><i class="fas fa-search"></i></a>
							<a href="javascript:exportar()" class="btn btn-icons mr-1 btn-outline-success rounded btn-sm p-1" title="Exportar"><i class="fas fa-file-excel"></i></a>
						</div>
					</div>
				</div>
			</div>
		</fieldset>
	</div>

	<div class="row card-description">
		<div class="col-md-12">
    		<div class="container-filtercolumn form-inline">
    			@if(Auth::user()->empresa()->efecty == 1)
    			<a href="{{route('facturas.downloadefecty')}}" class="btn btn-warning btn-sm" style="background: #938B16; border: solid #938B16 1px;"><i class="fas fa-cloud-download-alt"></i> Descargar Archivo Efecty</a>
    			@endif
				{{-- @if(isset($_SESSION['permisos']['830']))
    			<a class="btn btn-outline-success btn-sm disabled mr-1 d-none" href="javascript:void(0)" id="btn_emitir"><i class="fas fa-sitemap"></i> Convertir a facturas electrónicas en Lote</a>
    			@endif --}}
    			@if(isset($_SESSION['permisos']['750']))
    			<a href="{{route('campos.organizar', 4)}}" class="btn btn-warning btn-sm mr-1"><i class="fas fa-table"></i> Organizar Tabla</a>
    			@endif
                @if(isset($_SESSION['permisos']['774']))
                <a href="{{route('promesas-pago.index')}}" class="btn btn-outline-danger btn-sm mr-1"><i class="fas fa-calendar"></i> Ver Promesas de Pago</a>
                @endif
                <div class="dropdown mr-1">
                    <button class="btn btn-warning dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Acciones en Lote
                    </button>
                    @if(isset($_SESSION['permisos']['774']))
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item" href="javascript:void(0)" id="btn_emitir"><i class="fas fa-server"></i> Convertir a facturas electrónicas en Lote</a>
                    </div>
                    @endif
                </div>
			</div>
		</div>
		<div class="col-md-12">
			<table class="table table-striped table-hover w-100" id="tabla-facturas">
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
	var tabla = $('#tabla-facturas');
	window.addEventListener('load', function() {
		var tabla= $('#tabla-facturas').DataTable({
			responsive: true,
			serverSide: true,
			processing: true,
			searching: false,
			@if(isset($_SESSION['permisos']['830']))
			select: true,
			@endif
			language: {
				'url': '/vendors/DataTables/es.json'
			},
			order: [
				[2, "DESC"],[0, "DESC"]
			],
			"pageLength": {{ Auth::user()->empresa()->pageLength }},
			ajax: '{{url("/facturas")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
			@if(isset($_SESSION['permisos']['830']))
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
            }],
            @endif
			columns: [
			    @foreach($tabla as $campo)
                {data: '{{$campo->campo}}'},
                @endforeach
				{data: 'acciones'},
			]
		});

		tabla.on('preXhr.dt', function(e, settings, data) {
			data.codigo = $('#codigo').val();
			data.corte = $('#corte').val();
			data.cliente = $('#cliente').val();
			data.municipio = $('#municipio').val();
			data.vendedor = $('#vendedor').val();
			data.creacion = $('#creacion').val();
			data.vencimiento = $('#vencimiento').val();
			data.comparador = $('#comparador').val();
			data.total = $('#total').val();
			data.servidor = $('#servidor').val();
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

		$('#codigo').on('keyup',function(e) {
            if(e.which > 32 || e.which == 8) {
                getDataTable();
                return false;
            }
        });

        $('#cliente, #municipio, #estado, #correo, #creacion, #vencimiento').on('change',function() {
            getDataTable();
            return false;
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

		$('#tabla-facturas tbody').on('click', 'tr', function () {
			var table = $('#tabla-facturas').DataTable();
			var nro = table.rows('.selected').data().length;

			if(table.rows('.selected').data().length >= 0){
				$("#btn_emitir").removeClass('disabled d-none');
			}else{
				$("#btn_emitir").addClass('disabled d-none');
			}
        });

		$('#btn_emitir').on('click', function(e) {
		var table = $('#tabla-facturas').DataTable();
		var nro = table.rows('.selected').data().length;

		if(nro <= 0){
			swal({
				title: 'ERROR',
				html: 'Para ejecutar esta acción, debe al menos seleccionar una factura.',
				type: 'error',
			});
			return false;
		}

		var facturas = [];
		for (i = 0; i < nro; i++) {
			facturas.push(table.rows('.selected').data()[i]['id']);
		}

		swal({
			title: '¿Desea convertir '+nro+' facturas estandar a facturas electrónicas?',
			text: 'Esto puede demorar unos minutos. Al Aceptar, no podrá cancelar el proceso',
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
					var url = `/software/empresa/facturas/conversionmasiva/`+facturas;
				}else{
					var url = `/empresa/facturas/conversionmasiva/`+facturas;
				}

				$.ajax({
					url: url,
					method: 'GET',
					headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
					success: function(data) {
						cargando(false);
						swal({
							title: 'PROCESO REALIZADO',
							html: data.text,
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
		$('#municipio').val('').selectpicker('refresh');
		$('#vendedor').val('').selectpicker('refresh');
		$('#creacion').val('');
		$('#vencimiento').val('');
		$('#comparador').val('').selectpicker('refresh');
		$('#total').val('');
		$('#estado').val('').selectpicker('refresh');
		$('#servidor').val('').selectpicker('refresh');
		$('#form-filter').addClass('d-none');
		$('#boton-filtrar').html('<i class="fas fa-search"></i> Filtrar');
		getDataTable();
	}

	function exportar() {
		$("#estado").selectpicker('refresh');
        window.location.href = window.location.pathname+'/exportar?codigo='+$('#codigo').val()+'&cliente='+$('#cliente').val()+'&municipio='+$('#municipio').val()+'&creacion='+$('#creacion').val()+'&vencimiento='+$('#vencimiento').val()+'&estado='+$('#estado').val()+'&tipo=1';
	}

	@if($tipo)
	    $('#estado').val('{{ $tipo }}').selectpicker('refresh');
	    abrirFiltrador();
	    getDataTable();
	@endif
</script>
@endsection
