@extends('layouts.app')
@section('content')
	@if($servicio->nombre == 'WOMPI')
	    <div class="alert alert-info" role="alert">
	    	Para obtener tu <i>Llave pública</i> de WOMPI, sólo debes registrarte en el <a href="https://comercios.wompi.co/" class="alert-link">dashboard de Comercios</a> e ingresar a la sección de <strong>desarrolladores</strong>.
	    </div>
	@endif

	<form method="POST" action="{{ route('integracion-pasarelas.update', $servicio->id) }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-banco" >
	    @csrf
	    <input name="_method" type="hidden" value="PATCH">
	    <div class="row">
	        <div class="col-md-{{ $servicio->nombre == 'WOMPI' ? '6' : '4'}} form-group">
	            <label class="control-label">{{ $servicio->nombre == 'WOMPI' ? 'Llave pública' : 'API Key'}} <span class="text-danger">*</span></label>
	            <input type="text" class="form-control" id="api_key" name="api_key"  required="" value="{{$servicio->api_key}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('api_key') }}</strong>
	            </span>
	        </div>

	        <div class="col-md-4 form-group {{ $servicio->nombre == 'WOMPI' ? 'd-none' : ''}}">
	            <label class="control-label">merchantId <span class="text-danger">*</span></label>
	            <input type="text" class="form-control" id="merchantId" name="merchantId"  required="" value="{{$servicio->merchantId}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('merchantId') }}</strong>
	            </span>
	        </div>

	        <div class="col-md-4 form-group {{ $servicio->nombre == 'WOMPI' ? 'd-none' : ''}}">
	            <label class="control-label">accountId <span class="text-danger">*</span></label>
	            <input type="text" class="form-control" id="accountId" name="accountId"  required="" value="{{$servicio->accountId}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('accountId') }}</strong>
	            </span>
	        </div>

	        <div class="col-md-{{ $servicio->nombre == 'WOMPI' ? '3' : '4'}} form-group">
	        	<label class="control-label">Permitir Pago desde la WEB <span class="text-danger">*</span></label>
	        	<div class="input-group">
	        		<select class="form-control selectpicker" name="web" id="web" required="" title="Seleccione" data-live-search="true" data-size="5">
	        			<option value="1" {{old('web')==1?'selected':''}}>Si</option>
	        			<option value="0" {{old('web')==0?'selected':''}}>No</option>
	        		</select>
	        	</div>
	        	<span class="help-block error">
	        		<strong>{{ $errors->first('web') }}</strong>
	        	</span>
	        </div>
	        <div class="col-md-{{ $servicio->nombre == 'WOMPI' ? '3' : '4'}} form-group">
	        	<label class="control-label">Permitir Pago desde la APP <span class="text-danger">*</span></label>
	        	<div class="input-group">
	        		<select class="form-control selectpicker" name="app" id="app" required="" title="Seleccione" data-live-search="true" data-size="5">
	        			<option value="1" {{old('app')==1?'selected':''}}>Si</option>
	        			<option value="0" {{old('app')==0?'selected':''}}>No</option>
	        		</select>
	        	</div>
	        	<span class="help-block error">
	        		<strong>{{ $errors->first('app') }}</strong>
	        	</span>
	        </div>
	    </div>
	    <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
	    <hr>
	    <div class="row" >
	        <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
	            <a href="{{route('integracion-pasarelas.index')}}" class="btn btn-outline-secondary">Cancelar</a>
	            <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
	        </div>
	    </div>
	</form>
@endsection