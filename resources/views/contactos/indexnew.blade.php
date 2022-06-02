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
        @if($tipo_usuario == 0)
            @if(isset($_SESSION['permisos']['411']))
                <a href="{{route('contratos.create')}}" class="btn btn-outline-info btn-sm"><i class="fas fa-plus"></i> Nuevo Contrato</a>
            @endif
            @if(isset($_SESSION['permisos']['202']))
                <a href="{{route('radicados.create')}}" class="btn btn-outline-info btn-sm"><i class="fas fa-plus"></i> Nuevo Radicado</a>
            @endif
        @endif
        @if(isset($_SESSION['permisos']['5']))
            @if($tipo_usuario == 0)
                <a href="{{route('contactos.create')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nuevo Cliente</a>
            @else
                <a href="{{route('contactos.createp')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nuevo Proveedor</a>
            @endif
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

    @if(isset($_SESSION['permisos']['814']))
        <div class="container-fluid">
        	<div class="row card-description" style="padding: 1% 1%; margin-bottom: 0;">
        		<div class="col-md-12 text-right">
        			<a href="{{route('contactos.importar')}}" class="btn btn-outline-success btn-sm"><i class="fas fa-file-upload"></i> Importar Contactos</a>
        		</div>
        	</div>
        </div>
    @endif

	@if($tipo_usuario == 1 && isset($_SESSION['permisos']['3']) || $tipo_usuario == 0 && isset($_SESSION['permisos']['2']))
	<div class="container-fluid d-none" id="form-filter">
		<fieldset>
			<legend>Filtro de Búsqueda</legend>
			<div class="card shadow-sm border-0 mb-3" style="background: #ffffff00 !important;">
				<div class="card-body py-0">
					<div class="row">
						@if($tipo_usuario == 0)
						<div class="col-md-3 pl-1 pt-1">
							<input type="text" placeholder="Serial ONU" id="serial_onu" class="form-control rounded">
						</div>
						@endif
						<div class="col-md-3 pl-1 pt-1">
							<input type="text" placeholder="Nombres" id="nombre" class="form-control rounded">
						</div>
						<div class="col-md-3 pl-1 pt-1">
							<input type="text" placeholder="Apellidos" id="apellido" class="form-control rounded">
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
						@if($tipo_usuario == 0)
						<div class="col-md-3 pl-1 pt-1">
							<input type="text" placeholder="Corregimiento/Vereda" id="vereda" class="form-control rounded">
						</div>
						<div class="col-md-3 pl-1 pt-1">
							<input type="text" placeholder="Barrio" id="barrio" class="form-control rounded">
						</div>
						<div class="col-md-3 pl-1 pt-1">
							<select title="Contratos" class="form-control rounded selectpicker" id="t_contrato" data-size="5" data-live-search="true">
								<option value="2" >Con contratos</option>
								<option value="1" >Sin contratos</option>
							</select>
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
						@endif
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
			<table class="table table-striped table-hover w-100" id="tabla-contactos">
				<thead class="thead-dark">
					<tr>
						@foreach($tabla as $campo)
						    @if($tipo_usuario == 1)
						        @if($campo->nombre != 'Contrato')
						            <th>{{$campo->nombre}}</th>
						        @endif
						    @else
						        <th>{{$campo->nombre}}</th>
						    @endif
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

		$('#tabla-contactos').DataTable({
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
			ajax: '{{url("/contactos/$tipo_usuario")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
			columns: [
			    @foreach($tabla as $campo)
			        @if($tipo_usuario == 1)
			            @if($campo->campo != 'contrato')
			                {data: '{{$campo->campo}}'},
			            @endif
                    @else
                        {data: '{{$campo->campo}}'},
                    @endif
                @endforeach
				{data: 'acciones'},
			]
		});


        tabla = $('#tabla-contactos');

        tabla.on('preXhr.dt', function(e, settings, data) {
            //data.serial_onu = $('#serial_onu').val();
            data.nombre = $('#nombre').val();
            data.apellido = $('#apellido').val();
            data.identificacion = $('#identificacion').val();
            data.telefono1 = $('#telefono').val();
            data.direccion = $('#direccion').val();
            data.barrio = $('#barrio').val();
            data.vereda = $('#vereda').val();
            data.email = $('#email').val();
            data.t_contrato = $('#t_contrato').val();
            data.serial_onu = $('#serial_onu').val();
            data.estrato = $('#estrato').val();
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
		//$('#serial_onu').val('');
		$('#nombre').val('');
		$('#apellido').val('');
		$('#identificacion').val('');
		$('#telefono').val('');
		$('#direccion').val('');
		$('#barrio').val('');
		$('#vereda').val('');
		$('#email').val('');
		$('#t_contrato').val('').selectpicker('refresh');
		$('#serial_onu').val('');
		$('#estrato').val('').selectpicker('refresh');
		$('#form-filter').addClass('d-none');
		$('#boton-filtrar').html('<i class="fas fa-search"></i> Filtrar');
		getDataTable();
	}
</script>
@endsection
