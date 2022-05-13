@extends('layouts.app')

@section('boton')
	@if($servicio->nombre == 'WOMPI')
	    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#exampleModalCenter">
	    	<i class="far fa-question-circle"></i> Tutorial
	    </button>

	    <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
	    	<div class="modal-dialog modal-dialog-centered" role="document">
	    		<div class="modal-content">
	    			<div class="modal-header">
	    				<h5 class="modal-title" id="exampleModalLongTitle">TUTORIAL WOMPI</h5>
	    				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
	    					<span aria-hidden="true">&times;</span>
	    				</button>
	    			</div>
	    			<div class="modal-body">
	    				<p class="text-justify">Para obtener las credenciales necesarias, sólo debes registrarte en el <a href="https://comercios.wompi.co/" class="alert-link">Dashboard de Comercios</a>.</p>
	    				<p class="text-justify">- Ingresar a la sección de <strong>desarrolladores</strong>.</p>
	    				<p class="text-justify">- En la sección <strong>Seguimiento de transacciones</strong>, debe indicar en <i>URL de Eventos</i> lo siguiente: <strong>{{ Request::root() }}/software/api/pagos/wompi</strong></p>
	    				<center><img src="{{ asset('images/wompi_a.png') }}" class="img-fluid mb-3 border-dark border"></center>
	    				<p class="text-justify">- En la sección de <strong>Llaves del API para integración técnica</strong>, copiar la Llave pública y pegarlo acá en la configuración.</p>
	    				<center><img src="{{ asset('images/wompi_b.png') }}" class="img-fluid mb-3 border-dark border"></center>
	    				<p class="text-justify">- En la sección de <strong>Secretos para integración técnica</strong>, dar clic en mostrar y copiar Eventos y pegarlo acá en la configuración</p>
	    				<center><img src="{{ asset('images/wompi_c.png') }}" class="img-fluid mb-3 border-dark border"></center>
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

	        <div class="col-md-6 form-group {{ $servicio->nombre == 'WOMPI' ? '' : 'd-none'}}">
	            <label class="control-label">Eventos <span class="text-danger">*</span></label>
	            <input type="text" class="form-control" id="api_event" name="api_event"  required="" value="{{$servicio->api_event}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('api_event') }}</strong>
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
	        			<option value="1" {{$servicio->web==1?'selected':''}}>Si</option>
	        			<option value="0" {{$servicio->web==0?'selected':''}}>No</option>
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
	        			<option value="1" {{$servicio->app==1?'selected':''}}>Si</option>
	        			<option value="0" {{$servicio->app==0?'selected':''}}>No</option>
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