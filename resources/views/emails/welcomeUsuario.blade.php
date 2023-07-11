<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <meta charset="utf-8">
  <title></title>

</head>

<body style="background: url('https://images.pexels.com/photos/159888/pexels-photo-159888.jpeg?auto=compress&amp;amp;cs=tinysrgb&amp;amp;h=650&amp;amp;w=940'); background-repeat: no-repeat
      text-align: center; background-size: cover; color: #3d3d3dc7; font-family: Verdana, Geneva, sans-serif; font-size: 16px;">
  <style type="text/css"></style>
  <div class="main" style="background: #da985963; margin-left: 10%; padding-bottom: 5%; padding-left: 5%; padding-right: 5%; text-align: center; width: 70%;">
    <img src="{{asset('images/logo.png')}}" style="    width: 25%;">
    <div style="background: #fff; padding-bottom: 2%;">
      <h1 style="color: #e58c3b; font-size: 30px; font-weight: normal; margin: 0; padding-top: 80px; text-align: center;">Bienvenido al Sistema de cotización Avanzado </h1>
      <div style="padding: 2% 5%;">
        <p style="text-align: justify;">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Etiam facilisis sem a tempus vestibulum. Suspendisse euismod posuere hendrerit. Morbi lorem orci, vehicula nec tincidunt bibendum, ullamcorper eget purus. </p>

        <h4 style="text-align: left;">Sus datos son:</h4>
        <ul style="list-style: none;">
          <li style="text-align: justify;">Nombre <b>{{$data["nombre"]}}</b></li>
          <li style="text-align: justify;">Correo Electrónico <b>{{$data["email"]}}</b></li>
        </ul>

        <h4 style="text-align: left;">Datos para ingresar en el sistema</h4>
        <ul style="list-style: none;">
          <li style="text-align: justify;">Nombre de Usuario <b>{{$data["username"]}}</b></li>
          <li style="text-align: justify;">Contraseña <b>{{$data["password"]}}</b></li>
          <li style="text-align: justify;">Enlace <b>{{url('/')}}</b></li>
        </ul>

        <a href="{{url('/')}}" target="_blank" class="button" style="background: #00a855; border-radius: 6px; color: #fff; font-size: 20px; padding: 10px 35px; text-align: center; text-decoration: none;">Ingresar</a>



      </div>

    </div>
  </div>


</body>

</html>