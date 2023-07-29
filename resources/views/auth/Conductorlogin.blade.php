@extends('layouts.auth')

@section('content')
  <form class="form-login" id="form-login-conductor" method="POST" action="{{ route('conductor.login') }}">
    {{ csrf_field() }}
    <h2 class="form-login-heading">Inicio de Sesión <br> para Conductores</h2>
    <div class="login-wrap">


      @if ($errors->has('error_message'))
        <span class="help-block">
          <strong>{{ $errors->first('error_message') }}</strong>
        </span>
      @endif           
      <input type="text" class="form-control" placeholder="Nombre de Usuario" name="username" autofocus maxlength="100" required="" value="{{old('username')}}">
      @if ($errors->has('username'))
        <span class="help-block">
          <strong>{{ $errors->first('username') }}</strong>
        </span>
      @endif
      <br>
      <input type="password" name="password"  class="form-control" placeholder="Contraseña">
      @if ($errors->has('password'))
        <span class="help-block">
          <strong>{{ $errors->first('password') }}</strong>
        </span>
      @endif
      <br>
      <input type="text"class="form-control" placeholder="Placa" name="placa" id="placa" maxlength="7" required=""  onKeyUp="this.value = this.value.toUpperCase();" value="{{old('placa')}}">
      @if ($errors->has('placa'))
        <span class="help-block">
          <strong>{{ $errors->first('placa') }}</strong>
        </span>
      @endif
      <div class="row mt">                        
        <div class="col-md-3 text-center left-movile" title="Recordarme">
            <div class="switch switch-square"
                data-on-label="Si"
                data-off-label="No">
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}/>
            </div>
        </div>   
        <div class="col-md-8">
            <label class="checkbox">          
                <span class="pull-right">
                    <a data-toggle="modal" href="login.html#myModal">¿Olvido su Contraseña?</a>

                </span>
            </label>
        </div>                    
      </div>
      <button class="btn btn-theme btn-block" type="submit"><i class="fa fa-lock"></i> Iniciar</button>    
    </div>
    <div class="login-wrap" style="padding-top: 0px !important;">
       <a  href="{{ route('login') }}">No soy Conductor</a>
     </div>
  </form>
                  <!-- Modal -->
                  <div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="myModal" class="modal fade">
                      <div class="modal-dialog">
                          <div class="modal-content">
                              <div class="modal-header">
                                  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                                  <h4 class="modal-title">Aun no funciona Forgot Password ?</h4>
                              </div>
                              <div class="modal-body">
                                  <p>Enter your e-mail address below to reset your password.</p>
                                  <input type="text"  placeholder="Email" autocomplete="off" class="form-control placeholder-no-fix">
        
                              </div>
                              <div class="modal-footer">
                                  <button data-dismiss="modal" class="btn btn-default" type="button">Cancel</button>
                                  <button class="btn btn-theme" type="button">Submit</button>
                              </div>
                          </div>
                      </div>
                  </div>
                  <!-- modal -->
        
@endsection
