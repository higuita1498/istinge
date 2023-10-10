@extends('layouts.auth')

@section('content')
<style>.header-overlay {
    background: #000000;
    opacity: .75;
}
.login100-form-avatar img {
    width: 60%!important;
}
.btn-crearcuenta button:hover {
    background-color: #333!important;
    border-color: #fff!important;
}</style>
    <div class="header-overlay ov-login"></div>
    <form class="login100-form validate-form" method="POST" action="{{ route('login') }}">
          {{ csrf_field() }}
          <div class="login100-form-avatar text-center" style="text-align: center;width: 100%;">
            <img src="{{asset('images/Empresas/Empresa1/logo.png')}}" alt="AVATAR">
          </div>
          @if(Session::has('success'))
  <div class="alert alert-success" style="text-align: center;">
    <button type="button" class="close" data-dismiss="alert">X</button>
    <strong>{{Session::get('success')}}</strong>
  </div>
 @endif
  @if(Session::has('success_pass'))
  <div class="alert alert-success" style="text-align: center;">
    <button type="button" class="close" data-dismiss="alert">X</button>
    <strong>{{Session::get('success_pass')}}</strong>
  </div>
 @endif
          @if ($errors->has('error_message'))
            <span class="help-block">
              <p>{{ $errors->first('error_message') }}</p>
            </span>
          @endif

          <div class="wrap-input100 validate-input m-b-10" data-validate = "El Nombre de Usuario es  requerido">
            <input class="input100" type="text" name="username" name="username" placeholder="Nombre de Usuario">
            <span class="focus-input100"></span>
            <span class="symbol-input100">
              <i class="fa fa-user"></i>
            </span>
          </div>
          @if ($errors->has('username'))
            <span class="help-block">
              <p>{{ $errors->first('username') }}</p>
            </span>
          @endif
          <div class="wrap-input100 validate-input m-b-10" data-validate = "La Contraseña es requerida">
            <input class="input100" type="password" name="password" placeholder="Contraseña">
            <span class="focus-input100"></span>
            <span class="symbol-input100">
              <i class="fa fa-lock"></i>
            </span>
          </div>
          @if ($errors->has('password'))
            <span class="help-block">
              <p>{{ $errors->first('password') }}</p>
            </span>
          @endif
         <div class="wrap-input100 validate-input m-b-10" >
           <label class="form-check-label">
              <input type="checkbox" class="form-check-input" name="remember" {{ old('remember') ? 'checked' : '' }}> Recordarme
            </label>

        </div>
          {{--<div class="container-login100-form-btn p-t-10">
            <button class="login100-form-btn">
              Iniciar
            </button>
          </div>--}}

          <div class="col-md-12">
  <div class="btn-crearcuenta btn-iniciarsesion">
    <button  class="btn btn-primary">
      INICIAR
    </button>
  </div>
  {{-- <p style="color:white;padding-top:25px;">Señor usuario recuerde que su factura vence el día 10 de octubre de 2023 por favor adjunte su pago aquí para evitar ser suspendido el día 11 de octubre.</p> --}}
</div>

          {{--<div class="text-center w-full p-t-25 p-b-230">
            <a href="{{ route('password.request') }}" class="txt1">¿Olvido su Contraseña?
            </a>
          </div>--}}
        </form>
@endsection
