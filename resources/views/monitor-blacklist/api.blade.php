@extends('layouts.app')
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