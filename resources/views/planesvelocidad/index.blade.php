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
	        <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
	        <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
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
							<input type="text" placeholder="Nombre" id="name" class="form-control rounded">
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
							<a href="javascript:void(0)" id="filtrar" class="btn btn-icons btn-outline-info rounded btn-sm p-1 float-right" title="Iniciar busqueda avanzada"><i class="fas fa-search"></i></a>
						</div>
					</div>
				</div>
			</div>
		</fieldset>
	</div>

	<div class="row card-description">
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
    window.addEventListener('load',
    function() {
		$('#tabla-planes').DataTable({
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
			]
		});


        tabla = $('#tabla-planes');

        tabla.on('preXhr.dt', function(e, settings, data) {
            //data.serial_onu = $('#serial_onu').val();
            data.name = $('#name').val();
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

        $('#name, #price, #download, #upload').on('keyup',function(e) {
        	if(e.which > 32 || e.which == 8) {
        		getDataTable();
        		return false;
        	}
        });

        $('#type, #mikrotik_s, #status, #tipo_plan').on('change',function() {
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
		$('#name').val('');
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
</script>
@endsection
