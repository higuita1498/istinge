@extends('layouts.app')
@section('content')
	<form method="POST" action="{{ route('bancos.store') }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-banco" >
   {{ csrf_field() }}
  <div class="row">
    <div class="col-md-3 form-group">
      <label class="control-label">Tipo de la Cuenta <span class="text-danger">*</span></label>
      <select class="form-control form-control-sm selectpicker" name="tipo_cta" id="tipo_cta" title="Seleccione" required="">
        <option value="1" @if(old('tipo_cta')==1) selected="" @endif >Banco</option>
        <option value="2" @if(old('tipo_cta')==2) selected="" @endif >Tarjeta de crédito</option>
        <option value="3" @if(old('tipo_cta')==3) selected="" @endif >Efectivo</option>
        <option value="4" @if(old('tipo_cta')==4) selected="" @endif >Punto de Venta</option>
      </select>
      <span class="help-block error">
        <strong>{{ $errors->first('tipo_cta') }}</strong>
      </span>
    </div>

    <div class="col-md-3 form-group">
      <label class="control-label">Nombre de la Cuenta <span class="text-danger">*</span></label>
      <input type="text" class="form-control"  id="nombre" name="nombre"  required="" value="{{old('nombre')}}" maxlength="200">
      <span class="help-block error">
        <strong>{{ $errors->first('nombre') }}</strong>
      </span>
    </div>

    <div class="col-md-3 form-group">
      <label class="control-label">Número de la Cuenta</label>
      <input type="number" class="form-control"  id="nro_cta" name="nro_cta"  value="{{old('nro_cta')}}" maxlength="50" min="0">
      <span class="help-block error">
        <strong>{{ $errors->first('nro_cta') }}</strong>
      </span>
    </div>
    <div class="col-md-3 form-group monetario">
      <label class="control-label">Saldo inicial <span class="text-danger">*</span></label>
      <input type="number" class="form-control"  id="saldo" name="saldo" required="" value="{{old('saldo')}}" maxlength="24" min="0">
      <span class="help-block error">
        <strong>{{ $errors->first('saldo') }}</strong>
      </span>
    </div>
    <div class="col-md-3 form-group">
      <label class="control-label">Fecha <span class="text-danger">*</span></label>
      <input type="text" class="form-control datepicker"  id="fecha" name="fecha" required="" value="{{date('d-m-Y')}}" >
      <span class="help-block error">
        <strong>{{ $errors->first('fecha') }}</strong>
      </span>
    </div>
    @if(Auth::user()->empresa()->oficina)
    <div class="form-group col-md-3">
        <label class="control-label">Oficina Asociada <span class="text-danger">*</span></label>
        <select class="form-control selectpicker" name="oficina" id="oficina" required="" title="Seleccione" data-live-search="true" data-size="5">
          @foreach($oficinas as $oficina)
            <option value="{{$oficina->id}}" {{ $oficina->id == auth()->user()->oficina ? 'selected' : '' }}>{{$oficina->nombre}}</option>
          @endforeach
        </select>
      </div>
      @endif
    <div class="col-md-6 form-group">
      <label class="control-label">Descripción</label>
      <textarea  class="form-control" name="descripcion">{{old('descripcion')}}</textarea>
    </div>
  </div>
  <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
  <hr>
	<div class="row" >
    <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
      <a href="{{route('bancos.index')}}" class="btn btn-outline-secondary">Cancelar</a>
      <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
    </div>
	</div>
</form>
@endsection