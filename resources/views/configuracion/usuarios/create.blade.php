@extends('layouts.app')
@section('content')
	<form method="POST" action="{{ route('usuarios.store') }}" style="padding: 2% 3%;    " autocomplete="off" role="form" class="forms-sample" novalidate id="form-usuarios" >
   {{ csrf_field() }}
  <div class="row">
    <div class="col-md-4 form-group">
      <label class="control-label">Nombre y Apellido <span class="text-danger">*</span></label>
      <input type="text" class="form-control"  id="nombres" name="nombres" autocomplete="nope"  required="" value="{{old('nombres')}}" maxlength="200">
      <span class="help-block error">
        <strong>{{ $errors->first('nombres') }}</strong>
      </span>
    </div>

    <div class="col-md-5 form-group">
      <label class="control-label">Correo Electrónico <span class="text-danger">*</span></label>
      <input type="email" class="form-control" name="email" id="email" required=""  autocomplete="nope" value="{{old('email')}}" maxlength="200" >
      <span class="help-block error">
        <strong>{{ $errors->first('email') }}</strong>
      </span>
    </div>


    <div class="col-md-3 form-group">
      <label class="control-label">Tipo de Perfil <span class="text-danger">*</span></label>
      <select class="form-control selectpicker"  id="rol" name="rol"  required="" title="Seleccione">
          @foreach($roles as $rol)
            <option value="{{$rol->id}}" {{old('rol')==$rol->id?'selected':''}} >{{$rol->rol}}</option>
          @endforeach
      </select>
      <span class="help-block error">
        <strong>{{ $errors->first('rol') }}</strong>
      </span>
    </div>

    <div class="col-md-3 form-group">
      <label class="control-label">Cuenta Asociada</label>
      <select class="form-control selectpicker"  id="cuenta" name="cuenta[]"  title="Seleccione" multiple data-max-options="5">
          @foreach($cuentas as $cuenta)
            <option value="{{$cuenta->id}}" {{old('cuenta')==$cuenta->id?'selected':''}} >{{$cuenta->nombre}}</option>
          @endforeach
      </select>
      <span class="help-block error">
        <strong>{{ $errors->first('cuenta') }}</strong>
      </span>
    </div>

    <div class="col-md-3 form-group">
        <label class="control-label">Observaciones</label>
        <textarea class="form-control" id="observaciones" name="observaciones" rows="4" placeholder="Agregue sus observaciones aquí...">{{ old('observaciones') }}</textarea>
        <span class="help-block error">
            <strong>{{ $errors->first('observaciones') }}</strong>
        </span>
    </div>

    @if(Auth::user()->empresa()->oficina)
    <div class="col-md-3 form-group">
      <label class="control-label">Oficina Asociada</label>
      <select class="form-control selectpicker" id="oficina" name="oficina" title="Seleccione">
          <option value="0">Ninguna</option>
          @foreach($oficinas as $oficina)
            <option value="{{$oficina->id}}" {{old('oficina')==$oficina->id?'selected':''}} >{{$oficina->nombre}}</option>
          @endforeach
      </select>
      <span class="help-block error">
        <strong>{{ $errors->first('oficina') }}</strong>
      </span>
    </div>
    @endif

  </div>

      <div class="row">
      <div class="form-group col-md-4">
          <label class="control-label">Usuario <span class="text-danger">*</span></label>
        <input type="text" class="form-control" name="username" id="username" required="" maxlength="100"  autocomplete="nope">
        <span class="help-block error">
          <strong>{{ $errors->first('username') }}</strong>
        </span>
      </div>
      <div class="form-group col-md-4">
          <label for="inputPassword" class="control-label">Contraseña <span class="text-danger">*</span></label>
        <input type="password" class="form-control" name="password" id="password" autocomplete="nope" required>
      </div>
      <div class="form-group col-md-4">
          <label class="control-label">Confirmar Contraseña <span class="text-danger">*</span></label>
        <input type="password" class="form-control"  id="inputPasswordConfirm" name="inputPasswordConfirm" required >
        <div class="help-block error with-errors"></div>
      </div>
      </div>

  <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
  <hr>
	<div class="row" >
    <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
      <a href="{{route('usuarios.index')}}" class="btn btn-outline-secondary">Cancelar</a>
      <button type="submit" class="btn btn-success">Guardar</button>
    </div>
	</div>
</form>
@endsection
