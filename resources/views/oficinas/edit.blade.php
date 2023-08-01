@extends('layouts.app')

@section('content')
	<form method="POST" action="{{ route('oficinas.update', $oficina->id) }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-retencion" >
	   @csrf
	   <input name="_method" type="hidden" value="PATCH">
	   <div class="row">
	        <div class="col-md-4 form-group">
	            <label class="control-label">Nombre <span class="text-danger">*</span></label>
	            <input type="text" class="form-control"  id="nombre" name="nombre"  required="" value="{{$oficina->nombre}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('nombre') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-4 form-group">
	            <label class="control-label">Teléfono</label>
	            <input type="text" class="form-control" id="telefono" name="telefono"  value="{{$oficina->telefono}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('telefono') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-12 form-group">
	            <label class="control-label">Dirección</label>
	            <input type="text" class="form-control"  id="direccion" name="direccion"  value="{{$oficina->direccion}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('direccion') }}</strong>
	            </span>
	        </div>
	   </div>

	   <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
	   <hr>
	   <div class="row" >
	       <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
	           <a href="{{route('oficinas.index')}}" class="btn btn-outline-secondary">Cancelar</a>
	           <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
	       </div>
	   </div>
    </form>
@endsection

@section('scripts')
    <script>
    	$(document).on('keypress', '#telefono', function () {
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