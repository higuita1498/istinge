<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <meta charset="utf-8">
  <title></title>

</head>

<body style="background: url('https://images.pexels.com/photos/159888/pexels-photo-159888.jpeg?auto=compress&cs=tinysrgb&h=650&w=940'); background-repeat: no-repeat
      text-align: center; background-size: cover; color: #3d3d3dc7; font-family: Verdana, Geneva, sans-serif; font-size: 16px;">
  <style type="text/css"></style>
  <div class="main" style="background: #da985963; margin-left: 10%; padding-bottom: 5%; padding-left: 5%; padding-right: 5%; text-align: center; width: 70%;">
    <img src="{{asset('images/logo.png')}}" style="    width: 25%;">
    <div style="background: #fff; padding-bottom: 2%;">
      <h1 style="color: #e58c3b; font-size: 30px; font-weight: normal; margin: 0; padding-top: 80px; text-align: center;">{{$data["titulo"]}} </h1>
      <div style="padding: 2% 5%;">
        <ul style="list-style: none;">
          <li style="text-align: justify;">Empresa <b>{{$data["empresa"]}}</b></li>
          <li style="text-align: justify;">Usuario <b>{{$data["usuario"]}}</b></li>
          <li style="text-align: justify;">Correo Electrónico <b>{{$data["email"]}}</b></li>
          <li style="text-align: justify;">Categoría <b>{{$data["modulo"]}}</b></li>
          @if($data["imagen"] )

          <li style="text-align: justify;"><a href="{{asset('images/Empresas/Empresa'.$data['empresaid'].'/soporte/'.$data['soporte'].'/'.$data['nombre_imagen'])}}">Imagen</a> </li>
          @endif
        </ul>
        <p style="text-align: justify;">{{$data["error"]}}</p>
        

      </div>

    </div>
  </div>


</body>

</html>