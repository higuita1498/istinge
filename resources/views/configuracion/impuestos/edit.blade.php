@extends('layouts.app')
@section('content')
	<form method="POST" action="{{ route('impuestos.update', $impuesto->id) }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-impuesto" >
  <input name="_method" type="hidden" value="PATCH">
   {{ csrf_field() }} 
  <div class="row">
    <div class="col-md-3 form-group">
      <label class="control-label">Nombre <span class="text-danger">*</span></label>
      <input type="text" class="form-control"  id="nombre" name="nombre"  required="" value="{{$impuesto->nombre}}" maxlength="200">
      <span class="help-block error">
        <strong>{{ $errors->first('nombre') }}</strong>
      </span>
    </div>

    <div class="col-md-2 form-group">
      <label class="control-label">Porcentaje <span class="text-danger">*</span></label>
      <div class="input-group monetario">
        <input type="number" class="form-control" name="porcentaje" id="porcentaje"  value="{{$impuesto->porcentaje}}" max="100" >
        <div class="input-group-append">
          <span class="input-group-text">%</span>
        </div>
      </div>
      <span class="help-block error">
        <strong>{{ $errors->first('dias') }}</strong>
      </span> 
    </div>


    <div class="col-md-2 form-group">
      <label class="control-label">Tipo de Impuesto <span class="text-danger">*</span></label>
      <select class="form-control selectpicker"  id="tipo" name="tipo"  required="">
          <option value="1" {{$impuesto->tipo==1?'selected':''}}>IVA</option>
          <option value="2" {{$impuesto->tipo==2?'selected':''}}>ICO</option>
          <option value="3" {{$impuesto->tipo==3?'selected':''}}>Otro</option>
      </select>
      <span class="help-block error">
        <strong>{{ $errors->first('tipo') }}</strong>
      </span>
    </div>
    <div class="col-md-5 form-group">
      <label class="control-label">Descripción</label>
      <textarea  class="form-control form-control-sm " name="descripcion" >{{$impuesto->descripcion}}</textarea>
      <span class="help-block error">
        <strong>{{ $errors->first('descripcion') }}</strong>
      </span>
    </div>

  </div>

  <hr>

  <h4>Configuración de contabilidad</h4> 
  <div class="row">
    <div class="col-md-6 form-group">
      <label class="control-label">Cuenta contable para ventas<span class="text-danger">*</span></label>
      <select class="form-control selectpicker"  id="venta" name="venta" title="Seleccione" data-live-search="true" data-size="5">
          @foreach($cuentas as $cuenta)
            <option value="{{$cuenta->id}}" {{$cuenta->id == $impuesto->puc_venta ? 'selected' : ''}}>{{$cuenta->nombre}} - {{$cuenta->codigo}}</option>
          @endforeach
      </select>
      <span class="help-block error">
        <strong>{{ $errors->first('venta') }}</strong>
      </span>
    </div>
  </div>
  <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
  <hr>
	<div class="row" >
    <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
      <a href="{{route('impuestos.index')}}" class="btn btn-outline-secondary">Cancelar</a>
      <button type="submit" class="btn btn-success">Guardar</button>
    </div>
	</div>
</form>
@endsection