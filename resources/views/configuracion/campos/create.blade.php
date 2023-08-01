@extends('layouts.app')
@section('content')
	<form method="POST" action="{{ route('personalizar_inventario.store') }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-impuesto" >
   {{ csrf_field() }} 
  <div class="row">
    <div class="col-md-3 form-group">
      <label class="control-label">Nombre <span class="text-danger">*</span></label>
      <input type="text" class="form-control"  id="nombre" name="nombre"  required="" value="{{old('nombre')}}" maxlength="200">
      <span class="help-block error">
        <strong>{{ $errors->first('nombre') }}</strong>
      </span>
    </div>

    <div class="col-md-4 form-group">
      <label class="control-label">Campo <span class="text-danger">*</span></label>      
      <input type="text" class="form-control"  id="campo" name="campo"  required="" value="{{old('campo')}}" maxlength="200" pattern="[A-Za-z]" >
      <span class="help-block error">
        <strong>{{ $errors->first('campo') }}</strong>
      </span>
      <p><small>Nombre del campo en Base de datos. No debe tener caracteres especiales, ni espacios, ni tildes. No es editable</small></p>
    </div>
    <div class="col-md-5 form-group ">
      <label class="control-label">Descripción </label>
      <input type="text" class="form-control"  id="descripcion" name="descripcion"  value="{{old('descripcion')}}" maxlength="200">
      <span class="help-block error">
        <strong>{{ $errors->first('descripcion') }}</strong>
      </span>
      <p><small>Se mostrará en el formulario de crear o editar el producto</small></p>

    </div>

  </div>
  <div class="row">
    <div class="col-md-3 form-group monetario">
      <label class="control-label">Nro de Caracteres</label>
      <input type="number" class="form-control"  id="varchar" name="varchar"  value="{{old('varchar')}}" maxlength="8">

      <span class="help-block error">
        <strong>{{ $errors->first('varchar') }}</strong>
      </span>
    </div>
    <div class="col-md-2 form-group">
      <label class="control-label">¿Es Requerido? <span class="text-danger">*</span></label>
      <div class="row">
          <div class="col-sm-6">
          <div class="form-radio">
            <label class="form-check-label">
            <input type="radio" class="form-check-input" name="tipo" id="tipo1" value="1"> Si
            <i class="input-helper"></i></label>
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-radio">
            <label class="form-check-label">
            <input type="radio" class="form-check-input" name="tipo" id="tipo" value="0" checked=""> No
            <i class="input-helper"></i></label>
          </div>
        </div>
        </div>
    </div>
    <div class="col-md-4 form-group ">
      <label class="control-label">Valor por Defecto </label>
      <input type="text" class="form-control"  id="default" name="default"  value="{{old('default')}}" maxlength="200">
      <span class="help-block error">
        <strong>{{ $errors->first('default') }}</strong>
      </span>
      <p><small>Se escribira automaticamente en el campo en el formulario para crear o modificar un producto</small></p>
    </div>

    <div class="col-md-3 form-group">
      <label class="control-label">¿Activar el autocompletar? <span class="text-danger">*</span></label>
      <div class="row">
          <div class="col-sm-4">
          <div class="form-radio">
            <label class="form-check-label">
            <input type="radio" class="form-check-input" name="autocompletar" id="autocompletar1" value="1" checked=""> Si
            <i class="input-helper"></i></label>
          </div>
        </div>
        <div class="col-sm-4">
          <div class="form-radio">
            <label class="form-check-label">
            <input type="radio" class="form-check-input" name="autocompletar" id="autocompletar" value="0" > No
            <i class="input-helper"></i></label>
          </div>
        </div>
        </div>
    </div>
  </div>
  <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
  <hr>
	<div class="row" >
    <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
      <a href="{{route('personalizar_inventario.index')}}" class="btn btn-outline-secondary">Cancelar</a>
      <button type="submit" class="btn btn-success">Guardar</button>
    </div>
	</div> 
</form>
@endsection