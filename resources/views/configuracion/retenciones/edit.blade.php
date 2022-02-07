@extends('layouts.app')
@section('content')
	<form method="POST" action="{{ route('retenciones.update', $retencion->id) }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-retencion" >
  <input name="_method" type="hidden" value="PATCH">
   {{ csrf_field() }} 
  <div class="row">
    <div class="col-md-3 form-group">
      <label class="control-label">Nombre <span class="text-danger">*</span></label>
      <input type="text" class="form-control"  id="nombre" name="nombre"  required="" value="{{$retencion->nombre}}" maxlength="200">
      <span class="help-block error">
        <strong>{{ $errors->first('nombre') }}</strong>
      </span>
    </div>

    <div class="col-md-2 form-group">
      <label class="control-label">Porcentaje <span class="text-danger">*</span></label>
      <div class="input-group monetario">
        <input type="number" class="form-control" name="porcentaje" id="porcentaje"  value="{{ $retencion->porcentaje }}" max="100" >
        <div class="input-group-append">
          <span class="input-group-text">%</span>
        </div>
      </div>
      <span class="help-block error">
        <strong>{{ $errors->first('dias') }}</strong>
      </span> 
    </div>


    <div class="col-md-3 form-group">
      <label class="control-label">Tipo de retencion <span class="text-danger">*</span></label>
      <select class="form-control selectpicker"  id="tipo" name="tipo"  required="">
          <option value="1" {{$retencion->tipo==1?'selected':''}}>Retención de IVA</option>
          <option value="2" {{$retencion->tipo==2?'selected':''}}>Retención en la fuente</option>
          <option value="3" {{$retencion->tipo==3?'selected':''}}>Retención de Industria y Comercio</option>
          <option value="4" {{$retencion->tipo==4?'selected':''}}>Otro tipo de retención</option>
      </select>
      <span class="help-block error">
        <strong>{{ $errors->first('tipo') }}</strong>
      </span>
    </div>
    <div class="col-md-4 form-group">
      <label class="control-label">Descripción</label>
      <textarea  class="form-control form-control-sm " name="descripcion" >{{$retencion->descripcion}}</textarea>
      <span class="help-block error">
        <strong>{{ $errors->first('descripcion') }}</strong>
      </span>
    </div>

  </div>
  <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
  <hr>
	<div class="row" >
    <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
      <a href="{{route('retenciones.index')}}" class="btn btn-outline-secondary">Cancelar</a>
      <button type="submit" class="btn btn-success">Guardar</button>
    </div>
	</div>
</form>
@endsection