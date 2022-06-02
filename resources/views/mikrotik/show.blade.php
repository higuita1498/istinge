@extends('layouts.app')

@section('style')
	<style>
		.scroll-y {
			height: 200px;
			overflow-y: scroll;
			position:absolute;
			width: 130vh;
		}
		.scroll-y::-webkit-scrollbar{
	        width: 8px;
	        height: 8px;
	    }
	    .scroll-y::-webkit-scrollbar-track{
	        background: #f1f1f1;
	        border-radius: 20px;
	    }
	    .scroll-y::-webkit-scrollbar-thumb{
	        background: #888;
	        border-radius: 20px;
	    }
	    .scroll-y::-webkit-scrollbar-thumb:hover{
	        background: #555;
	    }
	    @media all and (max-width: 768px) {
			.scroll-y {
				width: 28vh;
			}
		}
		.card-header {
	        background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
	        border-bottom: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
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
	    <a href="javascript:abrirAcciones()" class="btn btn-dark btn-sm my-1" id="boton-acciones">Acciones de Mikrotik&nbsp;&nbsp;<i class="fas fa-caret-down"></i></a>
	@endif
@endsection

@section('content')
	<div class="container-fluid d-none" id="form-acciones">
		<fieldset>
			<legend>Acciones de Mikrotik</legend>
			<div class="card shadow-sm border-0">
				<div class="card-body pb-3 pt-2" style="background: #f9f9f9;">
					<div class="row">
						<div class="col-md-12 text-center">
						    @if($mikrotik->status == 0)
					        <form action="{{route('mikrotik.conectar',$mikrotik->id)}}" method="get" class="delete_form" style="margin:  0;display: inline-block;" id="conectar-{{$mikrotik->id}}">
					            @csrf
					        </form>
					        <form action="{{ route('mikrotik.destroy',$mikrotik->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-mikrotik">
					            @csrf
					            <input name="_method" type="hidden" value="DELETE">
					        </form>
					        @endif
						    @if($mikrotik->status == 1)
						    <form action="{{route('mikrotik.reglas',$mikrotik->id)}}" method="get" class="delete_form" style="margin:  0;display: inline-block;" id="regla-{{$mikrotik->id}}">
					            @csrf
					        </form>
					        <form action="{{route('mikrotik.importar',$mikrotik->id)}}" method="get" class="delete_form" style="margin:  0;display: inline-block;" id="importar-{{$mikrotik->id}}">
					            @csrf
					        </form>
					        <form action="{{route('mikrotik.reiniciar',$mikrotik->id)}}" method="get" class="delete_form" style="margin:  0;display: inline-block;" id="reiniciar-{{$mikrotik->id}}">
					            @csrf
					        </form>
					        @endif
					        <form action="{{route('mikrotik.conectar',$mikrotik->id)}}" method="get" class="delete_form" style="margin:  0;display: inline-block;" id="conectar-{{$mikrotik->id}}">
				                @csrf
				            </form>

						    <a title="Editar" href="{{route('mikrotik.edit',$mikrotik->id)}}" class="btn mt-1 btn-outline-primary"><i class="fas fa-edit"></i> Editar</a>
						    @if($mikrotik->status == 1)
						    <a title="Log" href="{{route('mikrotik.log',$mikrotik->id)}}" class="btn mt-1 btn-outline-success"><i class="fas fa-clipboard-check"></i> Ver Log</a>
						    <a title="Gráfica de Consumo" href="{{route('mikrotik.grafica',$mikrotik->id)}}" class="btn mt-1 btn-outline-info"><i class="fas fa-chart-area"></i> Ver Gráfica de Consumo</a>
						    @endif
						    @if($mikrotik->status == 1)
						    <button title="Aplicar Reglas" class="btn mt-1 btn-outline-dark" type="submit" onclick="confirmar('regla-{{$mikrotik->id}}', '¿Está seguro que desea aplicar las reglas a esta Mikrotik?', '');"><i class="fas fa-plus"></i> Aplicar Reglas</button>
						    {{--<button title="Importar Contratos" class="btn mt-1 btn-outline-info" type="submit" onclick="confirmar('importar-{{$mikrotik->id}}', '¿Está seguro que desea importar todos los contratos desde {{$mikrotik->nombre}}?', '');"><i class="fas fa-sync"></i> Importar Contratos</button>--}}
						    <button title="Reiniciar" class="btn mt-1 btn-outline-danger" type="submit" onclick="confirmar('reiniciar-{{$mikrotik->id}}', '¿Está seguro que desea reiniciar el mikrotik {{$mikrotik->nombre}}?', '');"><i class="fas fa-power-off"></i> Reiniciar </button>
						    @endif
						    @if($mikrotik->status == 0)
						    <button title="Eliminar" class="btn mt-1 btn-outline-danger" type="submit" onclick="confirmar('eliminar-mikrotik', '¿Está seguro que deseas eliminar el Mikrotik?', 'Se borrará de forma permanente');"><i class="fas fa-times"></i> Eliminar</button>
						    @endif
						    @if($mikrotik->status == 0)
						        <button title="Conectar Mikrotik" class="btn mt-1 btn-outline-success" type="submit" onclick="confirmar('conectar-{{$mikrotik->id}}', '¿Está seguro que desea conectar la Mikrotik {{$mikrotik->nombre}}?', '');"><i class="fas fa-plug"></i> Conectar</button>
						        @else
						        <button title="Desconectar Mikrotik" class="btn mt-1 btn-outline-danger" type="submit" onclick="confirmar('conectar-{{$mikrotik->id}}', '¿Está seguro que desea desconectar la Mikrotik {{$mikrotik->nombre}}?', '');"><i class="fas fa-plug"></i> Desconectar</button>
						    @endif
						    @if($mikrotik->status == 1)
						    <a title="IP's Autorizadas" href="{{ route('mikrotik.ips-autorizadas',$mikrotik->id )}}" class="btn mt-1 btn-outline-warning"><i class="fas fa-project-diagram"></i> IP's Autorizadas</a>
						    @endif
						</div>
					</div>
				</div>
			</div>
		</fieldset>
	</div>

	<div class="row card-description">
		<div class="col-md-12 mb-4">
            <div class="accordion" id="accordionExample">
                <div class="card">
                    <div class="card-header" id="headingOne">
                        <h5 class="mb-0">
                            <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne" style="color: #fff!important;font-weight: bold;">
                                DETALLES DEL MIKROTIK - {{ $mikrotik->nombre }}
                            </button>
                        </h5>
                    </div>
                    <div id="collapseOne" class="collapse" aria-labelledby="headingOne" data-parent="#accordionExample">
                        <div class="card-body" style="border: solid 1px {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};padding: 0;">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-sm info">
									<tbody>
										<tr>
											<th width="15%">IP</th>
											<td>{{ $mikrotik->ip }}</td>
										</tr>
										<tr>
											<th width="15%">Puerto API</th>
											<td>{{ $mikrotik->puerto_api }}</td>
										</tr>
										<tr>
											<th width="15%">Puerto WINBOX</th>
											<td>{{ $mikrotik->puerto_winbox }}</td>
										</tr>
										<tr>
											<th width="15%">Puerto WEB</th>
											<td>{{ $mikrotik->puerto_web }}</td>
										</tr>
										<tr>
											<th width="15%">Interfaz WAN</th>
											<td>{{ $mikrotik->interfaz }}</td>
										</tr>
										<tr>
											<th width="15%">Interfaz LAN</th>
											<td>{{ $mikrotik->interfaz_lan }}</td>
										</tr>
										<tr>
											<th width="15%">Estado</th>
											<td><span class="font-weight-bold text-{{$mikrotik->status('true')}}">{{ $mikrotik->status() }}</span></td>
										</tr>
										<tr style="height: 200px;">
											<th width="15%">Segmentos de IP</th>
											<td class="scroll-y">
											    @foreach($segmentos as $segmento)
											        {{$segmento->segmento}}<br>
											    @endforeach
											    {{-- <table class="table table-striped text-center">
														<tr>
															@php $i = 0; @endphp
															@foreach($segmentos as $segmento)
															    @if($i <= 6)
															    <td style="font-size: 1em;">{{$segmento->segmento}}</td>
															    @php $i++; @endphp
															    @else
															    <tr></tr>
															    @php $i = 0; @endphp
															    @endif
															@endforeach
													    </tr>
													</table>--}}
											</td>
										</tr>
										<tr>
											<th width="15%">Amarre MAC</th>
											<td class="font-weight-bold text-{{ $mikrotik->amarre_mac('true') }}">{{ $mikrotik->amarre_mac() }}</td>
										</tr>
										@if($mikrotik->uptime)
										<tr>
											<th width="15%">Uptime</th>
											<td>{{ $mikrotik->uptime }}</td>
										</tr>
										@endif
										@if($mikrotik->version)
										<tr>
											<th width="15%">Versión</th>
											<td>{{ $mikrotik->version }}</td>
										</tr>
										@endif
										@if($mikrotik->buildtime)
										<tr>
											<th width="15%">Build Time</th>
											<td>{{ $mikrotik->buildtime }}</td>
										</tr>
										@endif
										@if($mikrotik->freememory)
										<tr>
											<th width="15%">Free Memory</th>
											<td>{{ $mikrotik->freememory }}</td>
										</tr>
										@endif
										@if($mikrotik->totalmemory)
										<tr>
											<th width="15%">Total Memory</th>
											<td>{{ $mikrotik->totalmemory }}</td>
										</tr>
										@endif
										@if($mikrotik->cpu)
										<tr>
											<th width="15%">CPU</th>
											<td>{{ $mikrotik->cpu }}</td>
										</tr>
										@endif
										@if($mikrotik->cpucount)
										<tr>
											<th width="15%">CPU Count</th>
											<td>{{ $mikrotik->cpucount }}</td>
										</tr>
										@endif
										@if($mikrotik->cpufrequency)
										<tr>
											<th width="15%">CPU Frequency</th>
											<td>{{ $mikrotik->cpufrequency }}</td>
										</tr>
										@endif
										@if($mikrotik->cpuload)
										<tr>
											<th width="15%">CPU Load</th>
											<td>{{ $mikrotik->cpuload }}</td>
										</tr>
										@endif
										@if($mikrotik->freehddspace)
										<tr>
											<th width="15%">Free HDD Space</th>
											<td>{{ $mikrotik->freehddspace }}</td>
										</tr>
										@endif
										@if($mikrotik->totalhddspace)
										<tr>
											<th width="15%">Total HDD Space</th>
											<td>{{ $mikrotik->totalhddspace }}</td>
										</tr>
										@endif
										@if($mikrotik->architecturename)
										<tr>
											<th width="15%">Architecture Name</th>
											<td>{{ $mikrotik->architecturename }}</td>
										</tr>
										@endif
										@if($mikrotik->board)
										<tr>
											<th width="15%">Board</th>
											<td>{{ $mikrotik->board }}</td>
										</tr>
										@endif
										@if($mikrotik->platform)
										<tr>
											<th width="15%">Platform</th>
											<td>{{ $mikrotik->platform }}</td>
										</tr>
										@endif
										@if($mikrotik->created_by)
										<tr>
											<th width="15%">Registrado por</th>
											<td>{{ $mikrotik->created_by()->nombres }}</td>
										</tr>
										@endif
										@if($mikrotik->updated_by)
										<tr>
											<th width="15%">Actualizado por</th>
											<td>{{ $mikrotik->updated_by()->nombres }}</td>
										</tr>
										@endif
									</tbody>
								</table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    	<div class="col-md-12">
    		<table class="table table-striped table-hover w-100" id="tabla-mikrotik">
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

			$('#tabla-mikrotik').DataTable({
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


	        tabla = $('#tabla-mikrotik');

	        tabla.on('preXhr.dt', function(e, settings, data) {
				data.server_configuration_id = {{ $mikrotik->id }};
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

		function abrirAcciones() {
			if ($('#form-acciones').hasClass('d-none')) {
				$('#boton-acciones').html('Acciones de Mikrotik&nbsp;&nbsp;<i class="fas fa-caret-up"></i>');
				$('#form-acciones').removeClass('d-none');
			} else {
				$('#boton-acciones').html('Acciones de Mikrotik&nbsp;&nbsp;<i class="fas fa-caret-down"></i>');
				cerrarFiltrador();
			}
		}

		function cerrarFiltrador() {
			$('#form-acciones').addClass('d-none');
			$('#boton-acciones').html('Acciones de Mikrotik&nbsp;&nbsp;<i class="fas fa-caret-down"></i>');
		}
	</script>
@endsection