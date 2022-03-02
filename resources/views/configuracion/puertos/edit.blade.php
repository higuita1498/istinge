@extends('layouts.app')
@section('content')
	<form method="POST" action="{{ route('puertos-conexion.update', $puerto->id) }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-banco" >
	    @csrf
	    <input name="_method" type="hidden" value="PATCH">
	    <div class="row">
	        <div class="col-md-4 form-group">
	            <label class="control-label">Puerto <span class="text-danger">*</span></label>
	            <input type="number" class="form-control"  id="nombre" name="nombre"  required="" value="{{$puerto->nombre}}" maxlength="200" min="0">
	            <span class="help-block error">
	                <strong>{{ $errors->first('nombre') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-4 form-group">
	            <label class="control-label">Estado <span class="text-danger">*</span></label>
	            <select class="form-control selectpicker" name="estado" id="estado" title="Seleccione" required="">
	                <option value="1" {{ ($puerto->estado == 1) ? 'selected' : '' }}>Habilitado</option>
	                <option value="0" {{ ($puerto->estado == 0) ? 'selected' : '' }}>Deshabilitado</option>
	            </select>
	            <span class="help-block error">
	                <strong>{{ $errors->first('estado') }}</strong>
	            </span>
	        </div>
	    </div>
	    <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
	    <hr>
	    <div class="row" >
	        <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
	            <a href="{{route('puertos-conexion.index')}}" class="btn btn-outline-secondary">Cancelar</a>
	            <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
	        </div>
	    </div>
	</form>
@endsection