@extends('layouts.app')
@section('content')
	<form method="POST" action="{{ route('tiposempresa.update', $tipo->id) }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-termino" >
   {{ csrf_field() }} 
      <input name="_method" type="hidden" value="PATCH">
  <div class="row">
    <div class="col-md-4 form-group">
      <label class="control-label">Nombre <span class="text-danger">*</span></label>
      <input type="text" class="form-control"  id="nombre" name="nombre"  required="" value="{{$tipo->nombre}}" maxlength="200">
      <span class="help-block error">
        <strong>{{ $errors->first('nombre') }}</strong>
      </span>
    </div>

    
    <div class="col-md-6 form-group">
      <label class="control-label">Descripción</label>
      <textarea  class="form-control form-control-sm" name="descripcion">{{$tipo->descripcion}}</textarea>
    </div>

  </div>
  <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
  <hr>
	<div class="row" >
    <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
      <a href="{{route('tiposempresa.index')}}" class="btn btn-outline-secondary">Cancelar</a>
      <button type="submit" class="btn btn-success">Guardar</button>
    </div>
	</div>
</form>
@endsection