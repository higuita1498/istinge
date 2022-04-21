@extends('layouts.app')

@section('boton')

    @if($plan->status == 0)
    <form action="{{ route('planes-velocidad.destroy',$plan->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-mikrotik">
        {{ csrf_field() }}
        <input name="_method" type="hidden" value="DELETE">
    </form>
    <button class="btn btn-danger" type="submit" title="Eliminar" onclick="confirmar('eliminar-mikrotik', '¿Está seguro que deseas eliminar el Mikrotik?', 'Se borrará de forma permanente');"><i class="fas fa-times"></i> Eliminar</button>
    @endif
    <a href="{{route('planes-velocidad.edit',$plan->id)}}" class="btn btn-primary"><i class="fas fa-edit"></i> Editar</a>

@endsection

@section('content')

<div class="row card-description">
	<div class="col-md-12">
		<div class="table-responsive">
			<table class="table table-striped table-bordered table-sm info">
				<tbody>
					<tr>
						<th colspan="2" class="text-center" style="font-size: 1em;">DATOS GENERALES</th>
					</tr>
					<tr>
						<th width="20%">Nombre</th>
						<td>{{ $plan->name }}</td>
					</tr>
					<tr>
						<th>Mikrotik Asociada</th>
						<td><strong>{{ $plan->mikrotik()->nombre }}</strong></td>
					</tr>
					<tr>
						<th>Precio</th>
						<td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($plan->price)}}</td>
					</tr>
					<tr>
						<th>Vel. de Subida</th>
						<td>{{ $plan->upload }}</td>
					</tr>
					<tr>
						<th>Vel. de Bajada</th>
						<td>{{ $plan->download }}</td>
					</tr>
					<tr>
						<th>Tipo</th>
						<td><span class="font-weight-bold text-{{$plan->type('true')}}">{{ $plan->type() }}</span></td>
					</tr>
					<tr>
						<th>Tipo Plan</th>
						<td>{{ $plan->tipo() }}</td>
					</tr>
					<tr>
						<th>Estado</th>
						<td><span class="font-weight-bold text-{{$plan->status('true')}}">{{ $plan->status() }}</span></td>
					</tr>
					@if($plan->dhcp_server)
					<tr>
						<th>Servidor DHCP</th>
						<td>{{$plan->dhcp_server}}</td>
					</tr>
					@endif
					<tr>
						<th>Clientes Asociados</th>
						<td>
							<span class="badge badge-success">{{$plan->uso_state('enabled')}}</span> Habilitados<br>
						    <span class="badge badge-danger mt-1">{{$plan->uso_state('disabled')}}</span> Deshabilitados
						</td>
					</tr>
				</tbody>
			</table>
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
</div>

@endsection
