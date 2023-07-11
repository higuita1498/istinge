@extends('layouts.app')
@section('content')
	<form method="POST" action="{{ route('roles.update',$rol->id) }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-retencion" >
   {{ csrf_field() }} 
   <input name="_method" type="hidden" value="PATCH">
  <div class="row">
    <div class="col-md-3 form-group">
      <label class="control-label">Nombre del Tipo de Usuario<span class="text-danger">*</span></label>
      <input type="text" class="form-control"  id="rol" name="rol"  required="" value="{{$rol->rol}}" maxlength="200">
      <span class="help-block error">
        <strong>{{ $errors->first('rol') }}</strong>
      </span>
    </div>

  </div>
  <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
  <hr>
	<div class="row" >
    <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
      <a href="{{route('roles.index')}}" class="btn btn-outline-secondary">Cancelar</a>
      <button type="submit" class="btn btn-success">Guardar</button>
    </div>
	</div>
</form>
@endsection