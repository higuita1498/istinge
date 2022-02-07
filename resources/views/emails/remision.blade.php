<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
  <meta charset="utf-8">
  <title></title>
</head>

<body>
  <style></style>
  <div class="fondo" style="color: #333; font-family: Verdana, Geneva, sans-serif; font-size: 16px; height: 97vh;">
    <div class="main" style="background: #da985963; margin-left: 10%; padding-bottom: 5%; padding-left: 5%; padding-right: 5%; text-align: center; width: 70%;">
      <img src="http://localhost/SCA/public/images/logo.png" style="width: 25%;">
      <div style="background: #fff; padding: 2%; padding-bottom: 2%;">
        <p style="text-align: justify;">Cordial saludo,</p>
        <p style="text-align: justify;">En este correo se adjunta la remisión #{{$remision->nro}} con fecha {{date('d-m-Y', strtotime($remision->fecha))}}, le agradecemos por utilizar los servicios de {{Auth::user()->empresa()->nombre}}.</p>
        <p style="text-align: justify;">Cualquier inquietud será atendida en el teléfono: {{Auth::user()->empresa()->telefono}} . </p>

        <p style="text-align: justify;">Se recomienda confirmar la recepción de este correo.</p>
        <p style="text-align: justify;">Si desea responder a este correo, por favor hacerlo a {{Auth::user()->empresa()->email}} </p>



        <p style="text-align: justify;">Atentamente, </p>

        <p style="text-align: justify;">{{Auth::user()->empresa()->nombre}}</p>



      </div>

    </div>
  </div>



</body>

</html>