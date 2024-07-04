@extends('layouts.app')
@section('content')
	<form method="POST" action="{{ route('usuarios.update', $usuario->id) }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-usuarios" >
   {{ csrf_field() }}
      <input name="_method" type="hidden" value="PATCH">
  <div class="row">
    <div class="col-md-4 form-group">
      <label class="control-label">Nombre y Apellido <span class="text-danger">*</span></label>
      <input type="text" class="form-control"  id="nombres" name="nombres"  required="" value="{{$usuario->nombres}}" maxlength="200">
      <span class="help-block error">
        <strong>{{ $errors->first('nombres') }}</strong>
      </span>
    </div>

    <div class="col-md-5 form-group">
      <label class="control-label">Correo Electrónico <span class="text-danger">*</span></label>
      <input type="email" class="form-control" name="email" id="email" required=""   value="{{$usuario->email}}" maxlength="200" >
      <span class="help-block error">
        <strong>{{ $errors->first('email') }}</strong>
      </span>
    </div>


    <div class="col-md-3 form-group">
      <label class="control-label">Tipo de Perfil <span class="text-danger">*</span></label>
      <select class="form-control selectpicker"  id="rol" name="rol"  required="" title="Seleccione">
          @foreach($roles as $rol)
            <option value="{{$rol->id}}" {{$usuario->rol==$rol->id?'selected':''}} >{{$rol->rol}}</option>
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
            <option value="{{$cuenta->id}}" {{$cuenta->id==$usuario->cuenta?'selected':''}} {{$cuenta->id==$usuario->cuenta_1?'selected':''}} {{$cuenta->id==$usuario->cuenta_2?'selected':''}} {{$cuenta->id==$usuario->cuenta_3?'selected':''}} {{$cuenta->id==$usuario->cuenta_4?'selected':''}} >{{$cuenta->nombre}}</option>
          @endforeach
      </select>
      <span class="help-block error">
        <strong>{{ $errors->first('cuenta') }}</strong>
      </span>
    </div>

    @if(Auth::user()->empresa()->oficina)
    <div class="col-md-3 form-group">
      <label class="control-label">Oficina Asociada</label>
      <select class="form-control selectpicker" id="oficina" name="oficina" title="Seleccione">
          <option value="0" {{$usuario->oficina==NULL?'selected':''}} >Ninguna</option>
          @foreach($oficinas as $oficina)
            <option value="{{$oficina->id}}" {{$usuario->oficina==$oficina->id?'selected':''}} >{{$oficina->nombre}}</option>
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
      <input type="text" class="form-control" name="username" id="username" required="" maxlength="100" value="{{$usuario->username}}">
      <span class="help-block error">
        <strong>{{ $errors->first('username') }}</strong>
      </span>
    </div>
    <div class="form-group col-md-2">
      <label class="control-label">Cambiar contraseña</label>
      <div class="form-check form-check-flat mt-0" >
        <label class="form-check-label">
          <input type="checkbox" class="form-check-input" name="changepass" id="changepass" value="1"> Cambiar
        </label>
      </div>
    </div>
    <input type="hidden" id="cambiar" value="0">
  </div>

  <div id="pass" class="row" style="@if(!$errors->first('pass_actual')) display: none; @endif ">
    @if($usuario->id == Auth::user()->id)
    <div class="form-group col-md-4">
      <label for="inputPassword" class="control-label">Contraseña Actual <span class="text-danger">*</span></label>
      <input type="password" class="form-control" name="pass_actual" id="pass_actual" required>
      <span class="help-block error">
        <strong>{{ $errors->first('pass_actual') }}</strong>
      </span>
    </div>
    @endif
    <div class="form-group col-md-4">
      <label for="inputPassword" class="control-label">Contraseña <span class="text-danger">*</span></label>
      <input type="password" class="form-control" name="password" id="password" >
    </div>
    <div class="form-group col-md-4">
      <label class="control-label">Confirmar Contraseña <span class="text-danger">*</span></label>
      <input type="password" class="form-control"  id="inputPasswordConfirm" name="inputPasswordConfirm"  >
      <div class="help-block error with-errors"></div>
    </div>
  </div>
  <div class="col-md-6 form-group">
    <label class="control-label">Observaciones</label>
    <textarea class="form-control" id="observaciones" name="observaciones" value="{{$usuario->observaciones}}" rows="4" placeholder="Agregue sus observaciones aquí...">{{ old('observaciones', $usuario->observaciones) }}</textarea>
    <span class="help-block error">
        <strong>{{ $errors->first('observaciones') }}</strong>
    </span>
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
