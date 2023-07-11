@extends('layouts.app')
@section('content')
	<form method="POST" action="{{ route('numeraciones_dian.update', $numeracion->id) }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-numeracion" >
   {{ csrf_field() }} 
  <input type="hidden" name="tipo" value="1">
  <div class="row">
    <div class="col-md-3 form-group">
      <label class="control-label">Nombre <span class="text-danger">*</span></label>
      <input type="text" class="form-control"  id="nombre" name="nombre"  required="" value="{{$numeracion->nombre}}" maxlength="200">
      <span class="help-block error">
        <strong>{{ $errors->first('nombre') }}</strong>
      </span>
    </div>
    <div class="col-md-3 form-group">
      <label class="control-label">Prefijo </label>
      <input type="text" class="form-control"  id="prefijo" name="prefijo" value="{{$numeracion->prefijo}}" maxlength="8">
      <span class="help-block error">
        <strong>{{ $errors->first('prefijo') }}</strong>
      </span>
    </div>
    
    <div class="col-md-2 form-group">
      <label class="control-label">Número inicial <span class="text-danger">*</span></label>
      <input type="text" class="form-control nro"  id="inicioverdadero" name="inicioverdadero"  required="" value="{{$numeracion->inicioverdadero}}" maxlength="8">
      <span class="help-block error">
        <strong>{{ $errors->first('inicioverdadero') }}</strong>
      </span>
    </div>
    
    <div class="col-md-2 form-group">
      <label class="control-label">Próximo número<span class="text-danger">*</span></label>
      <input type="text" class="form-control nro"  id="inicio" name="inicio"  required="" value="{{$numeracion->inicio}}" maxlength="8">
      <span class="help-block error">
        <strong>{{ $errors->first('inicio') }}</strong>
      </span>
    </div>
    
    <div class="col-md-2 form-group">
      <label class="control-label">Número final </label>
      <input type="text" class="form-control nro"  id="final" name="final"   value="{{$numeracion->final}}" maxlength="8">
      <span class="help-block error">
        <strong>{{ $errors->first('final') }}</strong>
      </span>
    </div>
  </div>

  <div class="row">
    <div class="col-md-3 form-group">
      <label class="control-label">Vigencia desde</label>
      <input type="text" class="form-control"  id="desde" name="desde"  value="@if($numeracion->desde) {{date('d-m-Y', strtotime($numeracion->desde))}} @endif">
      <span class="help-block error">
        <strong>{{ $errors->first('desde') }}</strong>
      </span>
    </div>
    <div class="col-md-3 form-group">
      <label class="control-label">Vigencia hasta </label>
      <input type="text" class="form-control"  id="hasta" name="hasta"   value="@if($numeracion->hasta) {{date('d-m-Y', strtotime($numeracion->hasta))}} @endif">
      <span class="help-block error">
        <strong>{{ $errors->first('hasta') }}</strong>
      </span>
    </div>
    <div class="col-md-3 form-group">
      <label class="control-label">Preferida </label>
        <div class="row">
          <div class="col-sm-6">
          <div class="form-radio">
            <label class="form-check-label">
            <input type="radio" class="form-check-input" name="preferida" id="preferida1" value="1" @if($numeracion->preferida==1) checked="" @endif > Si
            <i class="input-helper"></i></label>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-radio">
            <label class="form-check-label">
            <input type="radio" class="form-check-input" name="preferida" id="preferida" value="0" @if($numeracion->preferida==0) checked="" @endif>No
            <i class="input-helper"></i></label>
          </div>
        </div>
        </div>
    </div>

    

  </div>

  <div class="row">
    <div class="col-md-3 form-group">
      <label class="control-label">Número de resolución</label>
      <input type="text" class="form-control"  id="nroresolucion" name="nroresolucion"   value="{{$numeracion->nroresolucion}}" maxlength="200">
      <span class="help-block error">
        <strong>{{ $errors->first('nroresolucion') }}</strong>
      </span>
    </div>

    <div class="col-md-6 form-group">
      <label class="control-label">Resolución</label>
            <textarea  class="form-control form-control-sm" name="resolucion" placeholder="Ejemplo: Resolución Facturación por Computador N°. 22000002222 de 2015/01/01 Rango del 1 al 100">{{$numeracion->resolucion}}</textarea>
      <span class="help-block error">
        <strong>{{ $errors->first('resolucion') }}</strong>
      </span>
    </div>
  </div>
  <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
  <hr>
	<div class="row" >
    <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
      <a href="{{route('configuracion.numeraciones')}}" class="btn btn-outline-secondary">Cancelar</a>
      <button type="submit" class="btn btn-success">Guardar</button>
    </div>
	</div>
</form>
@endsection
