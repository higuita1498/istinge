<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <meta charset="utf-8">
  <title></title>

</head>

<body style="background: #e0dfe2; color: #3d3d3dc7; font-family: Verdana, Geneva, sans-serif; font-size: 16px; text-align: center;">
  <style></style>
  <div class="main" style="background: #f6f3f6; margin-left: 5%; margin-right: 5%; width: 90%;">
    <img src="https://images.pexels.com/photos/215/road-sky-clouds-cloudy.jpg?auto=compress&amp;amp;cs=tinysrgb&amp;amp;dpr=2&amp;amp;h=650&amp;amp;w=940" width="100%">
    <h1 style="color: #c9aa5f; font-size: 30px; font-weight: normal; margin: 0; padding-top: 100px; text-align: center;">Bienvenido al Sistema Preoperacionales Vehiculares </h1>

    <div style="margin-left: 5%; margin-right: 5%; width: 90%;">
      <p style="text-align: justify;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam facilisis sem a tempus vestibulum. Suspendisse euismod posuere hendrerit. Morbi lorem orci, vehicula nec tincidunt bibendum, ullamcorper eget purus. </p>

      <br>
      <h4 style="text-align: left;">Sus datos son:</h4>
      <ul style="list-style: none;">
        <li style="text-align: justify;">Nombres: <b>{{$data["nombres"]}}</b></li>
        <li style="text-align: justify;">Cédula: <b>{{$data["cedula"]}}</b></li>
        <li style="text-align: justify;">Teléfono: <b>{{$data["telefono"]}}</b></li>
        <li style="text-align: justify;">Correo Electrónico: <b>{{$data["email"]}}</b></li>
        <li style="text-align: justify;">Empresa Asociada: <b>{{$data["empresa"]}}</b></li>
        <li style="text-align: justify;">Empresa Asociada: <b>{{$data["empresa"]}}</b></li>
      </ul>

      <h4 style="text-align: left;">Datos para ingresar en el sistema</h4>
      <ul style="list-style: none;">
        <li style="text-align: justify;">Nombre de Usuario: <b>{{$data["username"]}}</b></li>
        <li style="text-align: justify;">Contraseña: <b>{{$data["password"]}}</b></li>
        <li style="text-align: justify;">Enlace: <b>{{url('/')}}</b></li>
      </ul>

      <a href="{{url('/')}}" target="_blank" class="button" style="background: #68dff0; border-radius: 6px; color: #fff; font-size: 20px; padding: 10px 35px; text-align: center; text-decoration: none;">Ingresar</a>



    </div>

    <div class="footer" style="background: #c9aa5f; margin: 50px 0 0 0 !important; margin-left: 5%; margin-right: 5%; padding: 1% 2%; width: 96% !important;">
      <p style="color: #eee; text-align: center;">Copyright @ 2018, Todos los derechos reservados.<br> Por favor no responda este correo </p>
      <a href="#" style="color: #eee; text-align: center;">Politicas de Privacidad</a> |
      <a href="#" style="color: #eee; text-align: center;">Términos y condiciones</a>
    </div>
  </div>

</body>

</html>