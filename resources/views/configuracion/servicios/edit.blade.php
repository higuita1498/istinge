@extends('layouts.app')
@section('content')
	<form method="POST" action="{{ route('servicio.update', $servicio->id) }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-servicio" >
   {{ csrf_field() }}
  <div class="row">
    <div class="col-md-4 form-group">
      <label class="control-label">Nombre <span class="text-danger">*</span></label>
      <input type="text" class="form-control" id="nombre" name="nombre"  required="" maxlength="200" value="{{$servicio->nombre}}">
      <span class="help-block error">
        <strong>{{ $errors->first('nombre') }}</strong>
      </span>
    </div>
    
    <div class="col-md-4 form-group">
      <label class="control-label">Tiempo (minutos) <span class="text-danger">*</span></label>
      <input type="number" class="form-control"  id="tiempo" name="tiempo"  required="" value="{{$servicio->tiempo}}" min="0">
      <span class="help-block error">
        <strong>{{ $errors->first('tiempo') }}</strong>
      </span>
    </div>

  </div>
  <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
  <hr>
	<div class="row" >
    <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
      <a href="{{route('configuracion.servicios')}}" class="btn btn-outline-secondary">Cancelar</a>
      <button type="submit" class="btn btn-success">Guardar</button>
    </div>
	</div>
</form>
@endsection