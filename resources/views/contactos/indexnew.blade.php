@extends('layouts.app')

@section('styles')

@endsection

@section('boton')
    <a href="javascript:abrirFiltrador()" class="btn btn-info btn-sm my-1" id="boton-filtrar"><i class="fas fa-search"></i>Filtrar</a>
    @if($tipo_usuario == 0)
    <a href="{{route('contactos.create')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nuevo Cliente</a>
    @else
    <a href="{{route('contactos.createp')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nuevo Proveedor</a>
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
				{{--<div class="col-md-2 pl-1 pt-1">
					<input type="text" placeholder="Serial ONU" id="serial_onu" class="form-control rounded">
				</div>--}}
				<div class="col-md-3 pl-1 pt-1 offset-md-1">
					<input type="text" placeholder="Nombre" id="nombre" class="form-control rounded">
				</div>
				<div class="col-md-3 pl-1 pt-1">
					<input type="number" placeholder="Identificación" id="identificacion" class="form-control rounded">
				</div>
				<div class="col-md-3 pl-1 pt-1">
					<input type="number" placeholder="Teléfono" id="telefono" class="form-control rounded">
				</div>
				<div class="col-md-1 pl-1 pt-1 text-left">
					<a href="javascript:cerrarFiltrador()" class="btn btn-icons ml-1 btn-outline-danger rounded btn-sm p-1 float-right" title="Limpiar parámetros de busqueda"><i class="fas fa-times"></i></a>
					<a href="javascript:void(0)" id="filtrar" class="btn btn-icons btn-outline-info rounded btn-sm p-1 float-right" title="Iniciar busqueda avanzada"><i class="fas fa-search"></i></a>
				</div>
			</div>
		</div>
	</div>
</div>


<div class="row card-description">
	<div class="col-md-12">
		<table class="table table-striped table-hover w-100" id="tabla-contactos">
			<thead class="thead-dark">
				<tr>
					{{--<th>Serial ONU</th>--}}
					<th>Nombre</th>
					<th>Identificación</th>
					<th>Teléfono</th>
					<th>Email</th>
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

		$('#tabla-contactos').DataTable({
			responsive: true,
			serverSide: true,
			processing: true,
			language: {
				'url': '/vendors/DataTables/es.json'
			},
			order: [
				[0, "asc"]
			],
			"pageLength": 25,
			//ajax: '{{url("/contactos")}}',
			ajax: '{{url("/contactos/$tipo_usuario")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
			columns: [
			    //{data: 'serial_onu'},
			    {data: 'nombre'},
				{data: 'nit'},
				{data: 'telefono1'},
				{data: 'email'},
				{data: 'acciones'},
			]
		});


        tabla = $('#tabla-contactos');

        tabla.on('preXhr.dt', function(e, settings, data) {
            //data.serial_onu = $('#serial_onu').val();
            data.nombre = $('#nombre').val();
            data.identificacion = $('#identificacion').val();
            data.telefono1 = $('#telefono').val();
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
		$('#identificacion').val('');
		$('#telefono').val('');
		$('#form-filter').addClass('d-none');
		$('#boton-filtrar').html('<i class="fas fa-search"></i> Filtrar');
		getDataTable();
	}
</script>
@endsection
