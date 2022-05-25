@extends('layouts.app')

@section('boton')
    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
	        <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
	    </div>
	@else
    <form action="{{ route('integracion-pasarelas.act_desc',$servicio->id) }}" method="POST" class="delete_form" style="margin:  0;display: inline-block;" id="act_desc{{$servicio->id}}">
    	@csrf
    </form>
    <a href="{{route('integracion-pasarelas.index')}}" class="btn btn-outline-danger btn-sm"><i class="fas fa-backward"></i> Regresar</a>
    <a href="{{route('integracion-pasarelas.edit',$servicio->id)}}" class="btn btn-outline-primary btn-sm" title="Editar"><i class="fas fa-edit"></i> Editar</a>
    @if($servicio->status==1)
        <button class="btn btn-outline-danger btn-sm" type="submit" title="Deshablitar" onclick="confirmar('act_desc{{$servicio->id}}', '¿Está seguro que desea desactivar este servicio?', '');"><i class="fas fa-power-off"></i> Deshablitar</button>
    @else
        <button class="btn btn-outline-success btn-sm" type="submit" title="Hablitar" onclick="confirmar('act_desc{{$servicio->id}}', '¿Está seguro que desea activar este servicio?', '');"><i class="fas fa-power-off"></i> Hablitar</button>
	@endif

    @if($servicio->api_key && $servicio->user && $servicio->pass && $servicio->numero || $servicio->user && $servicio->pass && $servicio->numero)
        <a href="{{route('integracion-pasarelas.envio_prueba',$servicio->id)}}" class="btn btn-outline-success btn-sm" title="Editar"><i class="far fa-comment-dots"></i> SMS de Prueba</a>
    @endif
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
							<td class="font-weight-bold">{{$servicio->nombre}}</td>
						</tr>
						<tr>
							<td class="bg-th">Estado</td>
							<td class="font-weight-bold text-{{$servicio->status('true')}}">{{$servicio->status()}}</td>
						</tr>
						@if($servicio->api_key)
						<tr>
							<td class="bg-th">{{ $servicio->nombre == 'WOMPI' ? 'Llave pública' : 'API Key'}}</td>
							<td>{{$servicio->api_key}}</td>
						</tr>
						@endif
						@if($servicio->api_event)
						<tr>
							<td class="bg-th">API Eventos</td>
							<td>{{$servicio->api_event}}</td>
						</tr>
						@endif
						@if($servicio->user)
						<tr>
							<td class="bg-th">Usuario</td>
							<td>{{$servicio->user}}</td>
						</tr>
						@endif
						@if($servicio->pass)
						<tr>
							<td class="bg-th">Contraseña</td>
							<td>{{$servicio->pass}}</td>
						</tr>
						@endif
						@if($servicio->accountId)
						<tr>
							<td class="bg-th">{{ $servicio->nombre == 'ComboPay' ? 'ID de cliente':'merchantId'}}</td>
							<td>{{$servicio->accountId}}</td>
						</tr>
						@endif
						@if($servicio->merchantId)
						<tr>
							<td class="bg-th">{{ $servicio->nombre == 'ComboPay' ? 'Clave secreta':'accountId'}} </td>
							<td>{{$servicio->merchantId}}</td>
						</tr>
						@endif
						@if($servicio->p_cust_id_cliente)
						<tr>
							<td class="bg-th">p_cust_id_cliente</td>
							<td>{{$servicio->p_cust_id_cliente}}</td>
						</tr>
						@endif
						@if($servicio->p_key)
						<tr>
							<td class="bg-th">p_key</td>
							<td>{{$servicio->p_key}}</td>
						</tr>
						@endif
						@if($servicio->web)
						<tr>
							<td class="bg-th">Pagos desde WEB</td>
							<td>{{$servicio->web == 1?'SI':'NO'}}</td>
						</tr>
						@endif
						@if($servicio->app)
						<tr>
							<td class="bg-th">Pagos desde APP</td>
							<td>{{$servicio->app == 1?'SI':'NO'}}</td>
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
