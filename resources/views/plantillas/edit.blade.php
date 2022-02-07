@extends('layouts.app')
@section('content')
	<form method="POST" action="{{ route('plantillas.update', $plantilla->id) }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-retencion" >
	    @csrf
	    <input name="_method" type="hidden" value="PATCH">
	    <div class="row">
	        <div class="col-md-6 form-group">
        	    <label class="control-label">Título <span class="text-danger">*</span></label>
        	    <input type="text" class="form-control"  id="title" name="title"  required="" value="{{$plantilla->title}}" maxlength="200">
        	    <span class="help-block error">
        	        <strong>{{ $errors->first('title') }}</strong>
        	    </span>
        	</div>
        	
	        <div class="col-md-3 form-group">
	            <label class="control-label">Tipo <span class="text-danger">*</span></label>
        	    <select name="tipo" id="tipo" class="form-control selectpicker " title="Seleccione" data-live-search="true" data-size="5" required>
        	        <option value="0" {{$plantilla->tipo==0?'selected':''}} >SMS</option>
        	        <option value="1" {{$plantilla->tipo==1?'selected':''}} >EMAIL</option>
        	    </select>
        	    <span class="help-block error">
        	        <strong>{{ $errors->first('tipo') }}</strong>
        	    </span>
        	</div>
        	
        	<div class="col-md-3 form-group">
	            <label class="control-label">Clasificación <span class="text-danger">*</span></label>
        	    <select name="clasificacion" id="clasificacion" class="form-control selectpicker " title="Seleccione" data-live-search="true" data-size="5" required>
        	        <option value="0" {{$plantilla->clasificacion==0?'selected':''}} >CLASIFICACIÓN A</option>
        	        <option value="1" {{$plantilla->clasificacion==1?'selected':''}} >CLASIFICACIÓN B</option>
        	    </select>
        	    <span class="help-block error">
        	        <strong>{{ $errors->first('clasificacion') }}</strong>
        	    </span>
        	</div>
        	
        	<div class="col-md-12 form-group">
        	    <label class="control-label">Contenido <span class="text-danger">*</span></label>
        	    <textarea class="form-control ckeditor" name="contenido" id="contenido" rows="4">{{$plantilla->contenido}}</textarea>
        	    <span class="help-block error">
        	        <strong>{{ $errors->first('contenido') }}</strong>
        	    </span>
        	</div>
        </div>
	    
	   <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
	   
	   <hr>
	   
	   <div class="row" >
	       <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
	           <a href="{{route('plantillas.index')}}" class="btn btn-outline-secondary">Cancelar</a>
	           <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
	       </div>
	   </div>
    </form>
@endsection

@section('scripts')
<script type="text/javascript">
    
</script>
@endsection