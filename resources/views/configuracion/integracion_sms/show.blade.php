@extends('layouts.app')

@section('boton')
    <form action="{{ route('integracion-sms.act_desc',$servicio->id) }}" method="POST" class="delete_form" style="margin:  0;display: inline-block;" id="act_desc{{$servicio->id}}">
    	@csrf
    </form>
    <a href="{{route('integracion-sms.index')}}" class="btn btn-outline-danger btn-sm"><i class="fas fa-backward"></i> Regresar</a>
    <a href="{{route('integracion-sms.edit',$servicio->id)}}" class="btn btn-outline-primary btn-sm" title="Editar"><i class="fas fa-edit"></i> Editar</a>
    @if($servicio->status==1)
        <button class="btn btn-outline-danger btn-sm" type="submit" title="Deshablitar" onclick="confirmar('act_desc{{$servicio->id}}', '¿Está seguro que desea desactivar este servicio?', '');"><i class="fas fa-power-off"></i> Deshablitar</button>
    @else
        <button class="btn btn-outline-success btn-sm" type="submit" title="Hablitar" onclick="confirmar('act_desc{{$servicio->id}}', '¿Está seguro que desea activar este servicio?', '');"><i class="fas fa-power-off"></i> Hablitar</button>
	@endif

    @if($servicio->api_key && $servicio->user && $servicio->pass && $servicio->numero || $servicio->user && $servicio->pass && $servicio->numero)
        <a href="{{route('integracion-sms.envio_prueba',$servicio->id)}}" class="btn btn-outline-success btn-sm" title="Editar"><i class="far fa-comment-dots"></i> SMS de Prueba</a>
    @endif
@endsection

@section('content')
    <div class="row card-description">
		<div class="col-md-12">
			<div class="table-responsive">
				<table class="table table-striped table-bordered table-sm">
					<tbody>
						<tr>
							<th class="bg-th text-center" colspan="2" style="font-size: 1em;"><strong>DATOS GENERALES</strong></th>
						</tr>
						<tr>
							<td class="bg-th" width="20%">Servicio</td>
							<td>{{$servicio->nombre}}</td>
						</tr>
						<tr>
							<td class="bg-th">Estado</td>
							<td class="font-weight-bold text-{{$servicio->status('true')}}">{{$servicio->status()}}</td>
						</tr>
						@if($servicio->api_key && $servicio->nombre == 'Hablame SMS')
						<tr>
							<td class="bg-th">API Key</td>
							<td>{{$servicio->api_key}}</td>
						</tr>
						@endif
						@if($servicio->user)
						<tr>
							<td class="bg-th">{{ $servicio->nombre == 'Hablame SMS' ? 'Account' : 'Nombre de Usuario'}}</td>
							<td>{{$servicio->user}}</td>
						</tr>
						@endif
						@if($servicio->pass)
						<tr>
							<td class="bg-th">{{ $servicio->nombre == 'Hablame SMS' ? 'Token' : 'Clave de Usuario'}}</td>
							<td>{{$servicio->pass}}</td>
						</tr>
						@endif
						@if($servicio->numero)
						<tr>
							<td class="bg-th">Nro de Celular para pruebas</td>
							<td>{{$servicio->numero}}</td>
						</tr>
						@endif
						@if($servicio->updated_by)
						<tr>
							<td class="bg-th">Actualizado por</td>
							<td>{{$servicio->updated_by()->nombres}}</td>
						</tr>
						@endif
					</tbody>
				</table>
			</div>
		</div>
	</div>
@endsection
