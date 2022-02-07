@extends('layouts.app')

@section('boton')
    <a href="{{route('access-point.index')}}" class="btn btn-outline-danger btn-sm"><i class="fas fa-backward"></i> Regresar</a>
    <a href="javascript:getDataTable();" class="btn btn-outline-success btn-sm"><i class="fas fa-sync"></i> Actualizar Listado</a>
@endsection

@section('style')
<style>
    .card-header {
        background-color: rgb(49 126 191);
        border-bottom: 1px solid rgb(49 126 191);
    }
</style>
@endsection

@section('content')
    <div class="row card-description">
        <div class="col-md-12 mb-4">
            <div class="accordion" id="accordionExample">
                <div class="card">
                    <div class="card-header" id="headingOne">
                        <h5 class="mb-0">
                            <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne" style="color: #fff!important;font-weight: bold;">
                                DETALLES DEL ACCESS POINT
                            </button>
                        </h5>
                    </div>
                    <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample">
                        <div class="card-body" style="border: solid 1px #317ebf;padding: 0;">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-sm info">
                                    <tbody>
                                        <tr>
                                            <th class="bg-th" width="15%">DATOS GENERALES</th>
                                            <th class="bg-th"></th>
                                        </tr>
                                        <tr>
                                            <th>Nombre</th>
                                            <td>{{$ap->nombre}}</td>
                                        </tr>
                                        <tr>
                                            <th>Password</th>
                                            <td>{{$ap->password}}</td>
                                        </tr>
                                        <tr>
                                            <th>Modo de Red</th>
                                            <td>{{$ap->modo_red()}}</td>
                                        </tr>
                                        <tr>
                                            <th>Nodo</th>
                                            <td>{{$ap->nodo()->nombre}}</td>
                                        </tr>
                                        @if($ap->descripcion)
                                        <tr>
                                            <th>Descripción</th>
                                            <td>{{$ap->descripcion}}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <th>Contratos Asociados</th>
                                            <td>{{$ap->uso}}</td>
                                        </tr>
                                        <tr>
                                            <th>Estado</th>
                                            <td><span class="text-{{$ap->status('true')}}"><b>{{$ap->status()}}</b></span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    	<div class="col-md-12">
    		<table class="table table-striped table-hover w-100" id="tabla-contratos">
    			<thead class="thead-dark">
    				<tr>
    					<th>Nro</th>
    					<th>Cliente</th>
    					<th>Identificación</th>
    					<th>Teléfono</th>
    					<th>Correo</th>
    					<th>Barrio</th>
    					<th>Plan</th>
    					<th>MAC</th>
    					<th>IP</th>
    					<th>Estado</th>
    					<th>Fecha Corte</th>
    					<th>Último Pago</th>
    					<th>Cancelación del Servicio</th>
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

		$('#tabla-contratos').DataTable({
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
			"pageLength": 25,
			ajax: '{{url("/contratos/a-$ap->id")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
			columns: [
			    { data: 'nro' },
				{ data: 'client_id' },
				{ data: 'nit' },
				{ data: 'telefono' },
				{ data: 'email' },
				{ data: 'barrio' },
				{ data: 'plan' },
				{ data: 'mac' },
				{ data: 'ip' },
				{ data: 'state' },
				{ data: 'corte' },
				{ data: 'pago' },
				{ data: 'servicio' },
				{ data: 'acciones' },
			]
		});


        tabla = $('#tabla-contratos');

        tabla.on('preXhr.dt', function(e, settings, data) {
			data.cliente_id = $('#client_id').val();
            data.plan = $('#plan').val();
            data.state = $('#state').val();
			data.corte = $('#corte').val();
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
		$('#client_id').val('').selectpicker('refresh');
		$('#plan').val('').selectpicker('refresh');
		$('#corte').val('').selectpicker('refresh');
		$('#state').val('').selectpicker('refresh');
		$('#form-filter').addClass('d-none');
		$('#boton-filtrar').html('<i class="fas fa-search"></i> Filtrar');
		getDataTable();
	}
</script>
@endsection
