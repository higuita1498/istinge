<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <meta charset="utf-8">
  <title></title>
</head>

<body style="background: url(https://images.pexels.com/photos/215/road-sky-clouds-cloudy.jpg?auto=compress&amp;amp;cs=tinysrgb&amp;amp;dpr=2&amp;amp;h=650&amp;amp;w=940); background-repeat: no-repeat; background-size: cover; color: #3d3d3dc7; font-family: Verdana, Geneva, sans-serif; font-size: 16px; text-align: center;">
  <style></style>
  <img src="http://preoperacional-env-1.uabm2p27bf.us-east-1.elasticbeanstalk.com/assets/img/logo_blanco.png" style="width: 281px;margin-top: 4%;">
  <div class="main" style="background: #f6f3f6c7; margin-left: 25%; margin-right: 25%; margin-top: 2%; width: 50%;">
    <h1 style="color: #c9aa5f; font-size: 30px; font-weight: normal; margin: 0; padding-top: 50px; text-align: center;">¿Olvidaste tu Contraseña?</h1>
    <br>
    <div style="margin-left: 5%; margin-right: 5%; min-height: 215px; width: 90%;">
      <p style="text-align: justify;">Hemos recibido una notificación de recuperar contraseña, si usted no la realizo ignore este mensaje. </p>
      <h4 style="text-align: center;">Ingrese al siguiente link para cambiar la contraseña</h4><br>
      <a href="{{url('change_pass')}}/{{$data['token']}}" target="_blank" class="button" style="background: #68dff0; border-radius: 6px; color: #fff; font-size: 20px; padding: 10px 35px; text-align: center; text-decoration: none;">Cambiar contraseña</a>
    </div>
  </div>
  <div class="footer" style="background: #c9aa5f; margin: 2% 25% 0 25% !important; padding: 1% 2%; width: 46% !important;">
    <p style="color: #eee; text-align: center;">Copyright @ 2018, Todos los derechos reservados.<br> Por favor no responda este correo </p>
    <a href="#" style="color: #eee; text-align: center;">Politicas de Privacidad</a> |
    <a href="#" style="color: #eee; text-align: center;">Términos y condiciones</a>
  </div>

</body>

</html>