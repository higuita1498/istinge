@extends('layouts.app')
@section('content')
	<form method="POST" action="{{ route('integracion-sms.update', $servicio->id) }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-banco" >
	    @csrf
	    <input name="_method" type="hidden" value="PATCH">
	    <div class="row">
	        @if($servicio->nombre == 'Colombia RED' || $servicio->nombre == 'Hablame SMS' || $servicio->nombre == 'SmsEasySms')
	        <div class="col-md-4 form-group">
	            <label class="control-label">{{ $servicio->nombre == 'Hablame SMS' ? 'Account' : 'Nombre de Usuario'}} <span class="text-danger">*</span></label>
	            <input type="text" class="form-control" id="user" name="user"  required="" value="{{$servicio->user}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('user') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-4 form-group">
	            <label class="control-label">{{ $servicio->nombre == 'Hablame SMS' ? 'Token' : 'Clave de Usuario'}} <span class="text-danger">*</span></label>
	            <input type="text" class="form-control" id="pass" name="pass"  required="" value="{{$servicio->pass}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('pass') }}</strong>
	            </span>
	        </div>
	        @endif

	        @if($servicio->nombre == 'Hablame SMS')
	        <div class="col-md-4 form-group">
	            <label class="control-label">API Key <span class="text-danger">*</span></label>
	            <input type="text" class="form-control" id="api_key" name="api_key"  required="" value="{{$servicio->api_key}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('api_key') }}</strong>
	            </span>
	        </div>
	        @endif

	        <div class="col-md-4 form-group">
	            <label class="control-label">Nro Celular para pruebas <span class="text-danger">*</span> <a><i data-tippy-content="Indique el número de celular para enviar el SMS de prueba, de ser un destino en Colombia, indíquelo sin el código de país (3XXXXXXXXX)" class="icono far fa-question-circle"></i></a></label>
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
	            <a href="{{route('integracion-sms.index')}}" class="btn btn-outline-secondary">Cancelar</a>
	            <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
	        </div>
	    </div>
	</form>
@endsection