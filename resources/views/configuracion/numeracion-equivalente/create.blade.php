@extends('layouts.app')
@section('content')
<form method="POST" action="{{ route('numeraciones_equivalente.store') }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-numeracion">
  @csrf
  <div class="row">
    <div class="col-md-3 form-group">
      <label class="control-label">Nombre <span class="text-danger">*</span></label>
      <input type="text" class="form-control" id="nombre" name="nombre" required="" value="{{old('nombre')}}" maxlength="200">
      <span class="help-block error">
        <strong>{{ $errors->first('nombre') }}</strong>
      </span>
    </div>
    <div class="col-md-3 form-group">
      <label class="control-label">Prefijo </label>
      <input type="text" class="form-control" id="prefijo" name="prefijo" value="{{old('prefijo')}}" maxlength="8">
      <span class="help-block error">
        <strong>{{ $errors->first('prefijo') }}</strong>
      </span>
    </div>
    <div class="col-md-3 form-group">
      <label class="control-label">Número inicial <span class="text-danger">*</span></label>
      <input type="text" class="form-control" id="inicio" name="inicio" required="" value="{{old('inicio')}}" maxlength="8">
      <span class="help-block error">
        <strong>{{ $errors->first('inicio') }}</strong>
      </span>
    </div>
    <div class="col-md-3 form-group">
      <label class="control-label">Número final </label>
      <input type="text" class="form-control" id="final" name="final" value="{{old('final')}}" maxlength="8">
      <span class="help-block error">
        <strong>{{ $errors->first('final') }}</strong>
      </span>
    </div>
  </div>

  <div class="row">
    <div class="col-md-3 form-group">
      <label class="control-label">Vigencia desde</label>
      <input type="text" class="form-control" id="desde" name="desde" value="{{old('desde')}}">
      <span class="help-block error">
        <strong>{{ $errors->first('desde') }}</strong>
      </span>
    </div>
    <div class="col-md-3 form-group">
      <label class="control-label">Vigencia hasta </label>
      <input type="text" class="form-control" id="hasta" name="hasta" value="{{old('hasta')}}">
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
              <input type="radio" class="form-check-input" name="preferida" id="preferida1" value="1" @if(old('preferida')==1) checked="" @endif> Si
              <i class="input-helper"></i></label>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-radio">
            <label class="form-check-label">
              <input type="radio" class="form-check-input" name="preferida" id="preferida" value="0" @if(old('preferida')==0) checked="" @endif>No
              <i class="input-helper"></i></label>
          </div>
        </div>
      </div>
    </div>



  </div>

  <div class="row">
    <div class="col-md-3 form-group">
      <label class="control-label">Número de resolución</label>
      <input type="text" class="form-control" id="nroresolucion" name="nroresolucion" value="{{old('nroresolucion')}}" maxlength="200">
      <span class="help-block error">
        <strong>{{ $errors->first('nroresolucion') }}</strong>
      </span>
    </div>

    <div class="col-md-6 form-group">
      <label class="control-label">Resolución</label>
      <textarea class="form-control form-control-sm" name="resolucion" placeholder="Ejemplo: Resolución Facturación por Computador N°. 22000002222 de 2015/01/01 Rango del 1 al 100"></textarea>
      <span class="help-block error">
        <strong>{{ $errors->first('resolucion') }}</strong>
      </span>
    </div>
  </div>
  <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
  <hr>
  <div class="row">
    <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
      <a href="{{route('configuracion.index')}}" class="btn btn-outline-secondary">Cancelar</a>
      <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
    </div>
  </div>
</form>
@endsection