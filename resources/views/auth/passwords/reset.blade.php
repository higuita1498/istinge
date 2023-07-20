@extends('layouts.auth')

@section('content')
@if (Session::has('msj'))
<div class="form-forgot-pass alert alert-danger" style="margin-top: 15px;">
  <button class="close" data-close="alert"></button>
  <span>
   {{Session::get('msj')}}
   <a href="{{ route('login') }}" > Ir al Inicio</a>
 </span>


</div>
@else
<div class="header-overlay"></div>
<form class="form-forgot-pass" method="POST" action="{{ route('pass.save') }}" role="form" data-toggle="validator">
  {{ csrf_field() }}
  <div class="text-alfrente dsn-pass">
    <h2 class="form-login-heading">Recuperar Contraseña</h2>
  </div>
  <div class="login-wrap">
    @if ($errors->has('error_message'))
    <span class="help-block">
      <strong>{{ $errors->first('error_message') }}</strong>
    </span>
    @endif
    <input type="hidden" name="token" value="{{ $result->token }}">
    <input type="hidden" name="email" value="{{ $result->email }}">
    <div class="form-group col-md-12">
      <input type="password" class="form-control" placeholder="Contraseña" name="password" id="password" data-minlength="6" required>
        @if ($errors->has('password'))
                                    <span role="alert" style="color:red;">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                    @endif
      <div class="help-block" style="color:#fff;">Mínimo de 6 caracteres </div>   
    </div>
    <div class="form-group col-md-12">
      <input type="password" class="form-control" placeholder="Confirmar Contraseña" name="inputPasswordConfirm" id="inputPasswordConfirm" data-match="#inputPassword" data-match-error="Los valores no coinciden" required >
             @if ($errors->has('inputPasswordConfirm'))
                                    <span role="alert" style="color:red;">
                                        <strong>{{ $errors->first('inputPasswordConfirm') }}</strong>
                                    </span>
                                    @endif
      <div class="help-block with-errors"></div>
    </div>

    <div class="row mt">
      <div class="col-md-12">
        <div class="btn-olvpass" style="float:left;">
          <button class="btn btn-theme pull-right" type="submit"> Cambiar</button>
        </div>
        <div class="btn-olvpass" style="float:right;">
          <a href="{{ route('login') }}" class="btn btn-default pull-left" >Inicio</a>
        </div> 
      </div>
    </div>    
  </div> 
</form>
@endif
@endsection
