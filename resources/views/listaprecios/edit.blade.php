@extends('layouts.app')
@section('content')
	<form method="POST" action="{{ route('lista_precios.update', $lista->nro) }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-listaprecios" >
   {{ csrf_field() }}
  <input name="_method" type="hidden" value="PATCH">
  <div class="row">

    <div class="col-md-4 form-group">
      <label class="control-label">Nombre<span class="text-danger">*</span></label>
      <input type="text" class="form-control"  id="nombre" name="nombre"  required="" value="{{$lista->nombre}}" maxlength="200">
      <span class="help-block error">
        <strong>{{ $errors->first('nombre') }}</strong>
      </span>
    </div>
    <div class="col-md-5 form-group">
      <label class="control-label">Tipo</label>
      <div class="row" style="    margin-top: -10px;">
          <div class="col-sm-6">
          <div class="form-radio">
            <label class="form-check-label">
            <input type="radio" class="form-check-input" name="tipo" id="tipo1" value="1"  @if($lista->tipo==1) checked="" @endif  required="" disabled=""> Porcentaje
            <i class="input-helper"></i></label>
          </div>
            <small>Se calcula con base en el precio indicado en la lista general</small>
        </div>
        <div class="col-sm-6">
          <div class="form-radio">
            <label class="form-check-label">
            <input type="radio" class="form-check-input" name="tipo" id="tipo2" value="0"  @if($lista->tipo==0) checked="" @endif required="" disabled=""> Valor
            <i class="input-helper"></i></label>
          </div>
            <small>Indica un precio específico para cada ítem</small>
        </div>
        </div>
    </div>
    <div class="col-md-3 form-group " id="div_porcentaje"  @if($lista->tipo==0) style="display: none;" @endif>
      <label class="control-label">Porcentaje <span class="text-danger">*</span></label>
      <div class="input-group monetario">
        <input type="number" class="form-control" name="porcentaje" id="porcentaje"  value="{{$lista->porcentaje}}" max="100" >
        <div class="input-group-append"> 
          <span class="input-group-text">%</span>
        </div>
      </div>
      <span class="help-block error">
        <strong>{{ $errors->first('porcentaje') }}</strong>
      </span> 
    </div>
  </div>
  <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
  <hr>
	<div class="row" >
    <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
      <a href="{{route('lista_precios.index')}}" class="btn btn-outline-secondary">Cancelar</a>
      <button type="submit" class="btn btn-success">Guardar</button>
    </div>
	</div>
</form>
@endsection