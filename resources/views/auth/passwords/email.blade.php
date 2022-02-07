@extends('layouts.auth')

@section('content')
<div class="header-overlay"></div>
<form class="form-forgot-pass" method="POST" action="{{ route('password.email') }}">
  {{ csrf_field() }}
  <div class="text-alfrente dsn-pass">
    <h2 class="form-login-heading">¿Olvidó su Contrseña?</h2>
  </div>
  <div class="login-wrap">
    @if ($errors->has('error_message'))
    <span class="help-block">
      <strong>{{ $errors->first('error_message') }}</strong>
    </span>
    @endif
    <div class="text-alfrente">          
      <input type="text" class="form-control {{ $errors->has('email') ? ' has-error' : '' }}" placeholder="Ingrese su Correo Electrónico" name="email" autofocus maxlength="100" required="">
    </div>
    @if ($errors->has('email'))
    <span class="help-block">
      <strong>{{ $errors->first('email') }}</strong>
    </span>
    @endif
    <div class="row mt">
      <div class="col-md-12">
        <div class="btn-olvpass" style="float:left;">
          <button class="btn btn-theme pull-right" type="submit"><i class="far fa-envelope"></i> Enviar</button>
        </div>
        <div class="btn-olvpass" style="float:right;">
          <a href="{{ route('login') }}" class="btn btn-default pull-left" > Cancelar</a>  
        </div> 
      </div>
    </div>    
  </div> 
</form>


@endsection
