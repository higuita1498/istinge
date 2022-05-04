@extends('layouts.app')

@section('boton')
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
    				<p class="text-justify">- Una vez que se obtiene el API Key, se procede a configurarlo acá en Network Soft para disfrutar de la funcionalidad.</p>
    			</div>
    			<div class="modal-footer">
    				<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
    			</div>
    		</div>
    	</div>
    </div>
@endsection

@section('content')
	<form method="POST" action="{{ route('integracion-whatsapp.update', $servicio->id) }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-banco" >
	    @csrf
	    <input name="_method" type="hidden" value="PATCH">
	    <div class="row">
	        @if($servicio->nombre == 'CallMEBot')
	        <div class="col-md-4 form-group">
	            <label class="control-label">API Key <span class="text-danger">*</span></label>
	            <input type="text" class="form-control" id="api_key" name="api_key"  required="" value="{{$servicio->api_key}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('api_key') }}</strong>
	            </span>
	        </div>
	        @endif

	        <div class="col-md-4 form-group">
	            <label class="control-label">Nro Celular Asociado <span class="text-danger">*</span> <a><i data-tippy-content="Indique el número de celular, de ser un destino en Colombia, indíquelo sin el código de país (3XXXXXXXXX)" class="icono far fa-question-circle"></i></a></label>
	            <input type="number" class="form-control" id="numero" name="numero"  required="" value="{{$servicio->numero}}" maxlength="200" min="0">
	            <span class="help-block error">
	                <strong>{{ $errors->first('numero') }}</strong>
	            </span>
	        </div>
	    </div>
	    <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
	    <hr>
	    <div class="row" >
	        <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
	            <a href="{{route('integracion-whatsapp.index')}}" class="btn btn-outline-secondary">Cancelar</a>
	            <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
	        </div>
	    </div>
	</form>
@endsection