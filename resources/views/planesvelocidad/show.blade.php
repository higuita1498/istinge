@extends('layouts.app')

@section('boton')
    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">Integra Colombia: Suscripción Vencida</h4>
	       <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
	    </div>
	@else
    @if($plan->status == 0)
    <form action="{{ route('planes-velocidad.destroy',$plan->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-mikrotik">
        {{ csrf_field() }}
        <input name="_method" type="hidden" value="DELETE">
    </form>
    <button class="btn btn-danger" type="submit" title="Eliminar" onclick="confirmar('eliminar-mikrotik', '¿Está seguro que deseas eliminar el Mikrotik?', 'Se borrará de forma permanente');"><i class="fas fa-times"></i> Eliminar</button>
    @endif
    <a href="{{route('planes-velocidad.edit',$plan->id)}}" class="btn btn-primary"><i class="fas fa-edit"></i> Editar</a>
    @endif
@endsection

@section('style')
	<style>
	    .card-header {
	        background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
	        border-bottom: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
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
                                DETALLES DEL PLAN - {{ $plan->name }}
                            </button>
                        </h5>
                    </div>
                    <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample">
                        <div class="card-body" style="border: solid 1px {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};padding: 0;">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-sm info">
									<tbody>
										<tr>
											<th width="20%">Mikrotik Asociada</th>
											<td><strong>{{ $plan->mikrotik()->nombre }}</strong></td>
										</tr>
										<tr>
											<th width="20%">Precio</th>
											<td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($plan->price)}}</td>
										</tr>
										<tr>
											<th width="20%">Vel. de Subida</th>
											<td>{{ $plan->upload }}</td>
										</tr>
										<tr>
											<th width="20%">Vel. de Bajada</th>
											<td>{{ $plan->download }}</td>
										</tr>
										<tr>
											<th width="20%">Tipo</th>
											<td><span class="font-weight-bold text-{{$plan->type('true')}}">{{ $plan->type() }}</span></td>
										</tr>
										<tr>
											<th width="20%">Tipo Plan</th>
											<td>{{ $plan->tipo() }}</td>
										</tr>
										<tr>
											<th width="20%">Estado</th>
											<td><span class="font-weight-bold text-{{$plan->status('true')}}">{{ $plan->status() }}</span></td>
										</tr>
										@if($plan->dhcp_server)
										<tr>
											<th width="20%">Servidor DHCP</th>
											<td>{{$plan->dhcp_server}}</td>
										</tr>
										@endif
										<tr>
											<th width="20%">Clientes Asociados</th>
											<td>
												<span class="badge badge-success">{{$plan->uso_state('enabled')}}</span> Habilitados<br>
											    <span class="badge badge-danger mt-1">{{$plan->uso_state('disabled')}}</span> Deshabilitados
											</td>
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
			<div class="table-responsive">

			</div>

			@if($plan->burst_limit_subida || $plan->burst_limit_bajada || $plan->burst_threshold_subida || $plan->burst_threshold_bajada || $plan->burst_time_subida || $plan->burst_time_bajada || $plan->queue_type_bajada || $plan->queue_type_bajada || $plan->parenta || $plan->prioridad)
			<div class="table-responsive mt-3">
				<table class="table table-striped table-bordered table-sm info">
					<tbody>
						<tr>
							<th colspan="2" class="text-center" style="font-size: 1em;">CONFIGURACIÓN AVANZADA</th>
						</tr>
						@if(strlen($plan->burst_limit_subida)>1)
						<tr>
							<th width="20%">Burst limit subida</th>
							<td>{{ $plan->burst_limit_subida }}</td>
						</tr>
						@endif
						@if(strlen($plan->burst_limit_bajada)>1)
						<tr>
							<th width="20%">Burst limit bajada</th>
							<td>{{ $plan->burst_limit_bajada }}</td>
						</tr>
						@endif
						@if(strlen($plan->burst_threshold_subida)>1)
						<tr>
							<th width="20%">Burst threshold subida</th>
							<td>{{ $plan->burst_threshold_subida }}</td>
						</tr>
						@endif
						@if(strlen($plan->burst_threshold_bajada)>1)
						<tr>
							<th width="20%">Burst threshold bajada</th>
							<td>{{ $plan->burst_threshold_bajada }}</td>
						</tr>
						@endif
						@if(strlen($plan->limit_at_subida)>1)
						<tr>
							<th width="20%">Limit-at Subida</th>
							<td>{{ $plan->limit_at_subida }}</td>
						</tr>
						@endif
						@if(strlen($plan->limit_at_bajada)>1)
						<tr>
							<th width="20%">Limit-at Bajada</th>
							<td>{{ $plan->limit_at_bajada }}</td>
						</tr>
						@endif
						@if($plan->burst_time_subida)
						<tr>
							<th width="20%">Burst time subida</th>
							<td>{{ $plan->burst_time_subida }}</td>
						</tr>
						@endif
						@if($plan->burst_time_bajada)
						<tr>
							<th width="20%">Burst time bajada</th>
							<td>{{ $plan->burst_time_bajada }}</td>
						</tr>
						@endif
						@if($plan->queue_type_subida)
						<tr>
							<th width="20%">Queue Type de subida</th>
							<td>{{ $plan->queue_type_subida }}</td>
						</tr>
						@endif
						@if($plan->queue_type_bajada)
						<tr>
							<th width="20%">Queue Type de bajada</th>
							<td>{{ $plan->queue_type_bajada }}</td>
						</tr>
						@endif
						@if($plan->parenta)
						<tr>
							<th width="20%">Parent</th>
							<td>{{ $plan->parenta }}</td>
						</tr>
						@endif
						@if($plan->prioridad)
						<tr>
							<th width="20%">Prioridad</th>
							<td>{{ $plan->prioridad }}</td>
						</tr>
						@endif
					</tbody>
				</table>
			</div>
			@endif
		</div>

		<div class="col-md-12">
			<table class="table table-striped table-hover w-100" id="tabla-plan">
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

			$('#tabla-plan').DataTable({
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


	        tabla = $('#tabla-plan');

	        tabla.on('preXhr.dt', function(e, settings, data) {
				data.plan = {{ $plan->id }};
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
