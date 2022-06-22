@extends('layouts.app')

@section('boton')
    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
	        <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
	    </div>
	@else
    <a href="{{route('grupos-corte.index')}}" class="btn btn-outline-danger btn-sm"><i class="fas fa-backward"></i> Regresar</a>
    <a href="javascript:getDataTable();" class="btn btn-outline-success btn-sm"><i class="fas fa-sync"></i> Actualizar Listado</a>
    @endif
@endsection

@section('style')
<style>
    .card-header {
        background-color: #b00606;
        border-bottom: 1px solid #b00606;
    }
</style>
@endsection

@section('content')
    <div class="row card-description">
        <div class="col-md-12 mb-4">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-sm info">
                    <tbody>
                        <tr class="text-center">
                            <th class="bg-th text-center">Nombre</th>
                            <th class="bg-th text-center">Fecha factura</th>
                            <th class="bg-th text-center">Fecha pago</th>
                            <th class="bg-th text-center">Fecha Corte</th>
                            <th class="bg-th text-center">Fecha Suspensión</th>
                            <th class="bg-th text-center">Hora  Suspensión</th>
                            <th class="bg-th text-center">Estado</th>
                            <th class="bg-th text-center">Contratos Asociados</th>
                        </tr>
                        <tr>
                            <td class="text-center">{{$grupo->nombre}}</td>
                            <td class="text-center">{{$grupo->fecha_factura == 0 ? 'No Aplica' : $grupo->fecha_factura}}</td>
                            <td class="text-center">{{$grupo->fecha_pago == 0 ? 'No Aplica' : $grupo->fecha_pago}}</td>
                            <td class="text-center">{{$grupo->fecha_corte == 0 ? 'No Aplica' : $grupo->fecha_corte}}</td>
                            <td class="text-center">{{$grupo->fecha_suspension == 0 ? 'No Aplica' : $grupo->fecha_suspension}}</td>
                            <td class="text-center">{{date('g:i A', strtotime($grupo->hora_suspension))}}</td>
                            <td class="text-center"><span class="text-{{$grupo->status('true')}}"><b>{{$grupo->status()}}</b></span></td>
                            <td class="text-center">{{$contratos}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    	<div class="col-md-12">
    		<table class="table table-striped table-hover w-100" id="tabla-contratos">
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
			"pageLength": {{ Auth::user()->empresa()->pageLength }},
			ajax: '{{url("/contratos/0")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
			columns: [
			    @foreach($tabla as $campo)
                {data: '{{$campo->campo}}'},
                @endforeach
				{ data: 'acciones' },
			]
		});


        tabla = $('#tabla-contratos');

        tabla.on('preXhr.dt', function(e, settings, data) {
			data.grupo_corte = {{ $grupo->id }};
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
