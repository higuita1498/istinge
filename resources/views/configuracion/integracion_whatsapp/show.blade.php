@extends('layouts.app')

@section('boton')
    <form action="{{ route('integracion-whatsapp.act_desc',$servicio->id) }}" method="POST" class="delete_form" style="margin:  0;display: inline-block;" id="act_desc{{$servicio->id}}">
    	@csrf
    </form>
    <a href="{{route('integracion-whatsapp.index')}}" class="btn btn-outline-danger btn-sm"><i class="fas fa-backward"></i> Regresar</a>
    <a href="{{route('integracion-whatsapp.edit',$servicio->id)}}" class="btn btn-outline-primary btn-sm" title="Editar"><i class="fas fa-edit"></i> Editar</a>
    @if($servicio->status==1)
        <button class="btn btn-outline-danger btn-sm" type="submit" title="Deshablitar" onclick="confirmar('act_desc{{$servicio->id}}', '¿Está seguro que desea desactivar este servicio?', '');"><i class="fas fa-power-off"></i> Deshablitar</button>
    @else
        <button class="btn btn-outline-success btn-sm" type="submit" title="Hablitar" onclick="confirmar('act_desc{{$servicio->id}}', '¿Está seguro que desea activar este servicio?', '');"><i class="fas fa-power-off"></i> Hablitar</button>
	@endif

    @if($servicio->api_key && $servicio->numero)
        <a href="{{route('integracion-whatsapp.envio_prueba',$servicio->id)}}" class="btn btn-outline-success btn-sm" title="Editar"><i class="far fa-comment-dots"></i> SMS de Prueba</a>
    @endif
@endsection

@section('content')
    @if(Session::has('success'))
		<div class="alert alert-success" >
			{{Session::get('success')}}
		</div>

		<script type="text/javascript">
			setTimeout(function(){
			    $('.alert').hide();
			    $('.active_table').attr('class', ' ');
			}, 5000);
		</script>
	@endif

	@if(Session::has('danger'))
		<div class="alert alert-danger" >
			{{Session::get('danger')}}
		</div>

		<script type="text/javascript">
			setTimeout(function(){
			    $('.alert').hide();
			    $('.active_table').attr('class', ' ');
			}, 5000);
		</script>
	@endif

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
						@if($servicio->api_key)
						<tr>
							<td class="bg-th">API Key</td>
							<td>{{$servicio->api_key}}</td>
						</tr>
						@endif
						@if($servicio->numero)
						<tr>
							<td class="bg-th">Nro Asociado</td>
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
