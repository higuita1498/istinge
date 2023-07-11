@extends('layouts.app')

@section('boton')
    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#exampleModalCenter">
    	<i class="far fa-question-circle" style="margin: 2px;"></i> Tutorial
    </button>

    <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    	<div class="modal-dialog modal-dialog-centered" role="document">
    		<div class="modal-content">
    			<div class="modal-header">
    				<h5 class="modal-title" id="exampleModalLongTitle">TUTORIAL API MONITOR BLACKLIST</h5>
    				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
    					<span aria-hidden="true">&times;</span>
    				</button>
    			</div>
    			<div class="modal-body">
                    <p class="text-justify">- Registrarse en <a href="https://hetrixtools.com" target="_blank">https://hetrixtools.com/</a></p>

    				<p class="text-justify">- Luego vamos a requerir el Token y la ID de la lista de contactos, para ello nos logueamos en <a href="https://hetrixtools.com" target="_blank">https://hetrixtools.com/</a></p>

    				<p class="text-justify">- Luego en el menú de <b>API > API Keys</b></p>
    				<center><img src="{{ asset('images/Blacklist1.jpeg') }}" class="img-fluid mb-3 border-dark border"></center>
    				<p class="text-justify">- Esa es el API Keys que deben colocar en el sistema en el campo <b>Token API</b></p>
    				<center><img src="{{ asset('images/Blacklist2.jpeg') }}" class="img-fluid mb-3 border-dark border"></center>
    				<p class="text-justify">- Para obtener el ID de la lista de contactos, van al menú <b>API > API Explorer</b></p>
    				<center><img src="{{ asset('images/Blacklist3.jpeg') }}" class="img-fluid mb-3 border-dark border"></center>
    				<p class="text-justify">- En el select ubicar y seleccionar la opción <b>v1 List Contact Lists</b></p>
    				<center><img src="{{ asset('images/Blacklist4.jpeg') }}" class="img-fluid mb-3 border-dark border"></center>
    				<p class="text-justify">- Luego presionar en <b>GO</b></p>
    				<center><img src="{{ asset('images/Blacklist5.jpeg') }}" class="img-fluid mb-3 border-dark border"></center>
    				<p class="text-justify">- En la parte inferior cargará un Name y un ID</p>
    				<center><img src="{{ asset('images/Blacklist6.jpeg') }}" class="img-fluid mb-3 border-dark border"></center>
    				<p class="text-justify">- Ese ID es el que va en el NetworkSoft en el campo <b>ID Lista de contacto</b></p>
    				<center><img src="{{ asset('images/Blacklist7.jpeg') }}" class="img-fluid mb-3 border-dark border"></center>
    				<p class="text-justify">- Una vez configurado el Token API y el ID Lista de contacto podrá utilizar las opciones del Monitor Blacklist y así monitoriar sus servidores.</p>
    			</div>
    			<div class="modal-footer">
    				<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
    			</div>
    		</div>
    	</div>
    </div>
@endsection

@section('content')
    @if(Session::has('danger'))
        <div class="alert alert-danger" style="margin-left: 2%;margin-right: 2%;">
	    {{Session::get('danger')}}
        </div>
    @endif

	<form method="POST" action="{{ route('monitor-blacklist.store_api') }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-banco">
	    @csrf
	    <div class="row">
	        <div class="col-md-3 form-group">
	            <label class="control-label">Token API <span class="text-danger">*</span></label>
	            <input type="text" class="form-control" id="api_key_hetrixtools" name="api_key_hetrixtools"  required="" value="{{$empresa->api_key_hetrixtools}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('api_key_hetrixtools') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-3 form-group">
	            <label class="control-label">ID Lista de contacto <span class="text-danger">*</span></label>
	            <input type="text" class="form-control" id="id_contacto_hetrixtools" name="id_contacto_hetrixtools"  required="" value="{{$empresa->id_contacto_hetrixtools}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('id_contacto_hetrixtools') }}</strong>
	            </span>
	        </div>
	    </div>
	    <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
	    <hr>
	    <div class="row" >
	        <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
	            <a href="{{route('monitor-blacklist.index')}}" class="btn btn-outline-secondary">Cancelar</a>
	            <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
	        </div>
	    </div>
	</form>
@endsection