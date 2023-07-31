@extends('layouts.app')
@section('content')
	<form method="POST" action="{{ route('canales.update', $canal->id) }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-canales">
    <input name="_method" type="hidden" value="PATCH">
    @csrf
    <div class="row">
      <div class="col-md-4 form-group">
        <label class="control-label">Nombre <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="nombre" name="nombre" required="" value="{{$canal->nombre}}" maxlength="200">
        <span class="help-block error">
          <strong>{{ $errors->first('nombre') }}</strong>
        </span>
      </div>
      <div class="col-md-12 form-group">
        <label class="control-label">Observaciones</label>
        <textarea  class="form-control form-control-sm" name="observaciones">{{$canal->observaciones}}</textarea>
        <span class="help-block error">
          <strong>{{ $errors->first('observaciones') }}</strong>
        </span>
      </div>
    </div>
    <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
    <hr>
    <div class="row" >
      <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
        <a href="{{route('canales.index')}}" class="btn btn-outline-secondary">Cancelar</a>
        <button type="submit" class="btn btn-success">Guardar</button>
      </div>
    </div>
  </form>
@endsection