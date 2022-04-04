@extends('layouts.app')
@section('content')
	<form method="POST" action="{{ route('mikrotik.update', $mikrotik->id) }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-retencion" >
	    @csrf
	    <input name="_method" type="hidden" value="PATCH">
	    <div class="row">
	        <div class="col-md-3 form-group">
	            <label class="control-label">Nombre <span class="text-danger">*</span></label>
	            <input type="text" class="form-control"  id="nombre" name="nombre"  required="" value="{{$mikrotik->nombre}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('nombre') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-3 form-group">
	            <label class="control-label">IP <span class="text-danger">*</span></label>
	            <input type="text" class="form-control"  id="ip" name="ip"  required="" value="{{$mikrotik->ip}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('ip') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-3 form-group">
	            <label class="control-label">Puerto WEB</label>
	            <input type="text" class="form-control"  id="puerto_web" name="puerto_web"  value="{{$mikrotik->puerto_web}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('puerto_web') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-3 form-group">
	            <label class="control-label">Puerto API <span class="text-danger">*</span></label>
	            <input type="text" class="form-control"  id="puerto_api" name="puerto_api" required="" value="{{$mikrotik->puerto_api}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('puerto_api') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-3 form-group">
	            <label class="control-label">Puerto WINBOX <span class="text-danger">*</span></label>
	            <input type="text" class="form-control"  id="puerto_winbox" name="puerto_winbox" required="" value="{{$mikrotik->puerto_winbox}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('puerto_winbox') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-3 form-group">
	            <label class="control-label">Interfaz WAN <span class="text-danger">*</span></label>
	            <input type="text" class="form-control"  id="interfaz" name="interfaz" required="" value="{{$mikrotik->interfaz}}" maxlength="200" required="">
	            <span class="help-block error">
	                <strong>{{ $errors->first('interfaz') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-3 form-group">
	            <label class="control-label">Interfaz LAN <span class="text-danger">*</span></label>
	            <input type="text" class="form-control"  id="interfaz_lan" name="interfaz_lan" required="" value="{{$mikrotik->interfaz_lan}}" maxlength="200" required="">
	            <span class="help-block error">
	                <strong>{{ $errors->first('interfaz_lan') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-3 form-group">
	            <label class="control-label">Usuario <span class="text-danger">*</span></label>
	            <input type="text" class="form-control"  id="usuario" name="usuario"  required="" value="{{$mikrotik->usuario}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('usuario') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-3 form-group">
	            <label class="control-label">Contraseña <span class="text-danger">*</span></label>
	            <input type="text" class="form-control"  id="clave" name="clave"  required="" value="{{$mikrotik->clave}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('clave') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-3 form-group">
	            <label class="control-label">Amarre MAC <span class="text-danger">*</span></label>
	            <select class="form-control selectpicker" name="amarre_mac" id="amarre_mac" title="Seleccione" required="">
	                <option value="1" {{ $mikrotik->amarre_mac ==1?'selected':'' }}>Habilitado</option>
	                <option value="0" {{ $mikrotik->amarre_mac ==0?'selected':'' }}>Deshabilitado</option>
	            </select>
	            <span class="help-block error">
	                <strong>{{ $errors->first('amarre_mac') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-12 form-group">
	            <label class="control-label">Segmentos <a><i data-tippy-content="Escriba los segmentos separados por espacios" class="icono far fa-question-circle"></i></a> <span class="text-danger">*</span></label>
	            <select class="form-control" name="segmento_ip[]" id="segmento_ip" style="width: 100%;" required multiple="multiple">
	            	@foreach($segmentos as $segmento)
	            	<option value="{{$segmento->segmento}}" selected>{{$segmento->segmento}}</option>
	            	@endforeach
	            </select>
	            <span class="help-block error">
	                <strong>{{ $errors->first('segmento_ip') }}</strong>
	            </span>
	        </div>
	   </div>

	   <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
	   <hr>
	   <div class="row" >
	       <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
	           <a href="{{route('mikrotik.index')}}" class="btn btn-outline-secondary">Cancelar</a>
	           <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
	       </div>
	   </div>
    </form>
@endsection

@section('scripts')
    <script>
    	$(document).on('keypress', '.select2-search__field', function () {
    		if ((event.which < 32 || event.which > 57)) {
    			event.preventDefault();
    		}
    	});

    	$(document).ready(function() {
    		$("#segmento_ip").select2({
    			tags: true,
    			tokenSeparators: [' '],
    			allowClear: true
    		})
    	});
    </script>
@endsection