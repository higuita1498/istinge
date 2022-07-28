@extends('layouts.app')

@section('styles')

@endsection

@section('boton')
    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
	        <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
	    </div>
	@else
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
			<div class="card shadow-sm border-0 mb-3" style="background: #ffffff00 !important;">
				<div class="card-body pb-3 pt-2" style="background: #f9f9f9;">
					<div class="row">
						<div class="col-md-2 pl-1 pt-1">
							<input type="text" placeholder="Factura" id="factura" class="form-control rounded">
						</div>
						<div class="col-md-2 pl-1 pt-1">
						    <select id="cliente" class="form-control rounded selectpicker" title="Cliente" data-live-search="true" data-size="5">
		                        @foreach($clientes as $cliente)
		                            <option value="{{$cliente->id}}">{{$cliente->nombre}} {{$cliente->apellido1}} {{$cliente->apellido2}} - {{$cliente->nit}}</option>
		                        @endforeach
		                    </select>
						</div>
						<div class="col-md-2 pl-1 pt-1">
						    <select title="Estado" class="form-control rounded selectpicker" id="estado">
						        <option value="1">Aprobado</option>
								<option value="2">Sin Aprobar</option>
							</select>
						</div>
						<div class="col-md-2 pl-1 pt-1">
						    <select id="created_by" class="form-control rounded selectpicker" title="Creado por" data-live-search="true" data-size="5">
		                        @foreach($usuarios as $usuario)
		                            <option value="{{$usuario->id}}">{{$usuario->nombres}}</option>
		                        @endforeach
		                    </select>
						</div>
						<div class="col-md-2 pl-1 pt-1">
						    <select id="updated_by" class="form-control rounded selectpicker" title="Aprobado por" data-live-search="true" data-size="5">
		                        @foreach($usuarios as $usuario)
		                            <option value="{{$usuario->id}}">{{$usuario->nombres}}</option>
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
		<div class="col-md-12">
			<table class="table table-striped table-hover w-100" id="tabla-descuento">
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
	    window.addEventListener('load',
	    function() {

			$('#tabla-descuento').DataTable({
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
				ajax: '{{url("descuentos")}}',
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

	        tabla = $('#tabla-descuento');

	        tabla.on('preXhr.dt', function(e, settings, data) {
				data.id         = $('#id').val();
				data.cliente    = $('#cliente').val();
				data.factura    = $('#factura').val();
				data.descuento  = $('#descuento').val();
				data.estado     = $('#estado').val();
				data.created_by = $('#created_by').val();
				data.updated_by = $('#updated_by').val();
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

	        $('#factura').on('keyup',function(e) {
	            if(e.which > 32 || e.which == 8) {
	                getDataTable();
	                return false;
	            }
	        });

	        $('#cliente, #estado, #created_by, #updated_by').on('change',function() {
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
			$('#id').val('');
			$('#cliente').val('').selectpicker('refresh');
			$('#factura').val('');
			$('#descuento').val('');
			$('#estado').val('').selectpicker('refresh');
			$('#created_by').val('');
			$('#updated_by').val('');
			$('#form-filter').addClass('d-none');
			$('#boton-filtrar').html('<i class="fas fa-search"></i> Filtrar');
			getDataTable();
		}

		function aprobarDescuento(id) {
			swal({
				title: '¿Está seguro que desea aprobar el descuento a la factura?',
				text: 'Esta acción no se puede revertir',
				type: 'question',
				showCancelButton: true,
				confirmButtonColor: '#00ce68',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Aceptar',
				cancelButtonText: 'Cancelar',
			}).then((result) => {
				if (result.value) {
					cargando(true);
					$.ajax({
						url: `{{ route('descuentos.aprobar') }}`,
						method: 'POST',
						headers: {
							'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
						},
						data: {
							id: id,
						},
						success: function(data) {
							cargando(false);
							if (data.success) {
								Swal.fire({
									type: data.icon,
									title: data.title,
									text: data.text,
									showConfirmButton: false,
									timer: 5000
								})
								getDataTable();
							}
						}
					});
				}
			})
		}

		function noaprobarDescuento(id) {
			swal({
				title: '¿Está seguro que desea no aprobar el descuento a la factura?',
				text: 'Esta acción no se puede revertir',
				type: 'question',
				showCancelButton: true,
				confirmButtonColor: '#00ce68',
				cancelButtonColor: '#d33',
				confirmButtonText: 'Aceptar',
				cancelButtonText: 'Cancelar',
			}).then((result) => {
				if (result.value) {
					cargando(true);
					$.ajax({
						url: `{{ route('descuentos.noaprobar') }}`,
						method: 'POST',
						headers: {
							'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
						},
						data: {
							id: id,
						},
						success: function(data) {
							cargando(false);
							if (data.success) {
								Swal.fire({
									type: data.icon,
									title: data.title,
									text: data.text,
									showConfirmButton: false,
									timer: 5000
								})
								getDataTable();
							}
						}
					});
				}
			})
		}
	</script>
@endsection
