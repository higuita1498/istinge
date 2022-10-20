@extends('layouts.app')

@section('content')
<div class="alert alert-warning" style="text-align: left;">
  <button type="button" class="close" data-dismiss="alert">×</button>
  <strong><small style="color:#000;font-weight:600;">Para cambiar la foto debes de tener en cuenta:</small><br></strong>
  <small>1. Se recomienda una imagen mínimo de "200px x 200px" para poder verse bien. <br>
        2. Una vez cambiada la imagen debe darle en el ícono </small><strong style="color:#000;font-weight:600;"><i class="far fa-save"></i></strong>
</div>
<div class="row card-description">
  <div class="col-md-4">
    <div id="change_profile_pic">
       <div class="profile">
          <div class="photo"> 
          <input type="file" accept="image/*">
          <div class="photo__helper">
            <div class="photo__frame photo__frame--circle">
              <canvas class="photo__canvas"></canvas>
              <div class="message is-empty">
                <p class="message--desktop">
                  Deja tu foto aquí o navega por tu computadora
                  <br><br><i class="fas fa-upload"></i>
                </p>
                <p class="message--mobile">
                  Toca aquí para seleccionar tu imagen
                  <br><br><i class="fas fa-upload"></i>
                </p>
              </div>
              <div class="message is-loading">
                <i class="fa fa-2x fa-spin fa-spinner"></i>
              </div>
              <div class="message is-dragover">
                <i class="fa fa-2x fa-cloud-upload"></i>
                <p> Arrastra tu foto</p>
              </div>
              <div class="message is-wrong-file-type">
                <p>Solo se permiten imagenes</p>
                <p class="message--desktop">Deja tu foto aquí o navega por tu computadora
                  <br><br><i class="fas fa-upload"></i>
                </p>
                <p class="message--mobile">Toca aquí para seleccionar tu imagen
                <br><br><i class="fas fa-upload"></i></p>
              </div>
             <div class="message is-wrong-image-size">
                 <p>Tu foto debe ser más grande que 350px</p>
             </div>
            </div>
          </div>
          <div class="photo__options hide">
            <div class="photo__zoom">
              <input type="range" class="zoom-handler">
            </div>
          </div>
          <div class="photo__options hide actions">
            <a href="javascript:;" class="save-btn" id="save_profile_pic"><i class="far fa-save"></i></a>
            <a href="javascript:;"  class="remove cancel-btn" onclick="cancel_profile_pic();"><i class="fas fa-ban"></i></a>
          </div>
        </div>
      </div>
    </div>
    <div class="profile-userpic">
      <form action="{{ route('miusuario') }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="form-usuario">
        {{ csrf_field() }}
        <input type="hidden" name="imagenperfil" id="imagenperfil">
      </form>x
      <img src="{{asset('images/Empresas/Empresa'.$usuario->empresa.'/usuarios/'.$usuario->image)}}" onerror="this.src='{{asset('images/no-user-image.png')}}'" alt="" class="rounded-circle" style="width:200px;">
      <span><a href="javascript:void(0);" onclick="change_profile_pic();">Cambia tu foto<br><br><i class="fas fa-upload"></i></a></span>
    </div>
  </div>
  <div class="col-md-8" style="padding-top: 5%;">

    @if(Session::has('success'))
      <div class="alert alert-success" >
        {{Session::get('success')}}
      </div>

      <script type="text/javascript">
        setTimeout(function(){ 
            $('.alert').hide();
            $('.active_table').attr('class', ' ');
        }, 5000);
      </script>
    @endif

    <form method="POST" action="{{ route('miusuario') }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-myusuario" >
     {{ csrf_field() }} 
      <div class="table-responsive">
        <table class="table table-striped table-bordered table-sm">
          <tbody>
            <tr>
              <td width="20%">Nombre y Apellido</td>
              <td>
                <input type="text" class="form-control"  id="nombres" name="nombres"  required="" value="{{$usuario->nombres}}" maxlength="200" @if (!$errors->any()) disabled=""  @endif>
                <span class="help-block error">
                  <strong>{{ $errors->first('nombres') }}</strong>
                </span>
              </td>
            </tr>
            <tr>
              <td>Correo Electrónico</td>
              <td>
                <input type="email" class="form-control" name="email" id="email" required=""   value="{{$usuario->email}}" maxlength="200"  @if (!$errors->any()) disabled=""  @endif>
                <span class="help-block error">
                  <strong>{{ $errors->first('email') }}</strong>
                </span> 
              </td>
            </tr>
            <tr>
              <td>Nombre de Usuario</td>
              <td>
                <input type="text" class="form-control" name="username" id="username" required="" maxlength="100" value="{{$usuario->username}}" @if (!$errors->any()) disabled=""  @endif>
                <span class="help-block error">
                  <strong>{{ $errors->first('username') }}</strong>
                </span>
              </td>
            </tr>
            <tr id="form-enabled" style="@if (!$errors->first('pass_actual')) display: none;  @endif ">  
               <td>Cambiar contraseña</td>
                <td>
                   <div class="form-check form-check-flat mt-0" >
                    <label class="form-check-label">
                      <input type="checkbox" class="form-check-input" name="changepass" id="changepass" value="1" @if ($errors->first('pass_actual')) checked  @endif> Cambiar
                    </label>
                  </div>
                  <input type="hidden" id="cambiar" value="@if ($errors->first('pass_actual')) 1 @else 0  @endif"></td>
            </tr>
          </tbody>
        </table>
      </div>
      <div id="pass" class="row" style="@if(!$errors->first('pass_actual')) display: none; @endif ">
        <div class="form-group col-md-4">
          <label for="inputPassword" class="control-label">Contraseña Actual <span class="text-danger">*</span></label>
          <input type="password" class="form-control" name="pass_actual" id="pass_actual" >
          <span class="help-block error">
            <strong>{{ $errors->first('pass_actual') }}</strong>
          </span>
        </div>
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
      <hr>
      <div class="row" @if (!$errors->any()) style="display: none;"  @endif  id="modificar" >
        <div class="col-sm-12" style="text-align: right;">
          <a class="btn btn-outline-secondary" onclick="nodisabled('form-myusuario', false, true); hidediv('form-enabled');hidediv('pass');">Cancelar</a>
          <button type="submit" class="btn btn-success">Guardar</button>
        </div>
      </div>
      <div class="row"  >
        <div class="col-sm-12" style="text-align: right; @if ($errors->any()) display: none;  @endif" id="boton"  >
          <button type="button" class="btn btn-primary" onclick="nodisabled('form-myusuario', true, true); showdiv('form-enabled');">Modificar</button>
        </div>
      </div>
    </form>
  </div> 
</div>



@endsection