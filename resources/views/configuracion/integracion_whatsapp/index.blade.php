@extends('layouts.app')

@section('boton')
    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
	       <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
	    </div>
	@else
    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#exampleModalCenter">
    	<i class="far fa-question-circle"></i> Tutorial
    </button>

    <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    	<div class="modal-dialog modal-dialog-centered" role="document">
    		<div class="modal-content">
    			<div class="modal-header">
    				<h5 class="modal-title" id="exampleModalLongTitle">TUTORIAL API KEY CALLMEBOT</h5>
    				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
    					<span aria-hidden="true">&times;</span>
    				</button>
    			</div>
    			<div class="modal-body">
    				<p class="text-justify">- Debe obtener la apikey del bot antes de usar la API:</p>
    				<p class="text-justify">- Agregue el número de teléfono <b>+34 644 20 47 56</b> en sus contactos telefónicos. (Nómbrelo como desee).</p>
    				<p class="text-justify">- Envíe este mensaje <b>"I allow callmebot to send me messages"</b> al nuevo contacto creado (utilizando WhatsApp, por supuesto).</p>
    				<p class="text-justify">- Espere hasta que reciba el mensaje <b>"API Activated for your phone number. Your APIKEY is 123123"</b> del bot</p>
    				<p class="text-justify"><b>Nota: Si no recibe la ApiKey en 2 minutos, intente nuevamente después de 24 horas.</b></p>
    				<p class="text-justify">- El mensaje de WhatsApp del bot contendrá la API Key necesaria para enviar mensajes usando la API. Puede enviar mensajes de texto utilizando la API después de recibir la confirmación.<br><br><b>Ejemplo:</b></p>
    				<center><img src="{{ asset('images/callmebot.jpg') }}" class="img-fluid mb-3 border-dark border"></center>
    				<p class="text-justify">- Una vez que se obtiene el API Key, se procede a configurarlo acá en Integra Colombia para disfrutar de la funcionalidad.</p>
    			</div>
    			<div class="modal-footer">
    				<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
    			</div>
    		</div>
    	</div>
    </div>
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
			<table class="table table-striped table-hover" id="example">
			<thead class="thead-dark">
				<tr>
	              <th>Nombre</th>
	              <th>Nro Asociado</th>
	              <th>API KEY</th>
	              <th>Estado</th>
	              <th>Acciones</th>
	          </tr>
			</thead>
			<tbody>
				@foreach($servicios as $servicio)
				<tr @if($servicio->id==Session::get('id')) class="active_table" @endif >
					<td>{{$servicio->nombre}}</td>
					<td>{{$servicio->numero}}</td>
					<td>{{$servicio->api_key}}</td>
					<td class="font-weight-bold text-{{$servicio->status('true')}}">{{$servicio->status()}}</td>
					<td>
						@if(auth()->user()->modo_lectura())
						@else
						<form action="{{ route('integracion-whatsapp.act_desc',$servicio->id) }}" method="POST" class="delete_form" style="margin:  0;display: inline-block;" id="act_desc{{$servicio->id}}">
							@csrf
		                </form>
						<a href="{{route('integracion-whatsapp.show',$servicio->id)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>
						<a href="{{route('integracion-whatsapp.edit',$servicio->id)}}" class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>

	                 	@if($servicio->status==1)
		                  <button class="btn btn-outline-danger btn-icons" type="submit" title="Deshabilitar" onclick="confirmar('act_desc{{$servicio->id}}', '¿Está seguro que desea deshabilitar este servicio?', '');"><i class="fas fa-power-off"></i></button>
		                @else
		                  <button class="btn btn-outline-success btn-icons" type="submit" title="Habilitar" onclick="confirmar('act_desc{{$servicio->id}}', '¿Está seguro que desea habilitar este servicio?', '');"><i class="fas fa-power-off"></i></button>
		                @endif
		                @if($servicio->api_key && $servicio->numero)
		                    <a href="{{route('integracion-whatsapp.envio_prueba',$servicio->id)}}" class="btn btn-outline-success btn-icons" title="Envío de SMS Prueba"><i class="far fa-comment-dots"></i></a>
		                @endif
		                @endif
					</td>
				</tr>
				@endforeach
			</tbody>
		</table>
		</div>
	</div>
@endsection