<!DOCTYPE html>
<html>
<head>
	<title>Notificación de Cambio de Contraseña WIFI</title>
</head>
<body>
	<div align="center">
		<img src="{{asset('/images/Empresas/Empresa1/logo.png')}}" width="25%">
	</div>
    <h4>
        <b>Notificación de Cambio de Contraseña WIFI</b>
    </h4>
    
    <p>Hola, <b>{{ $datos['nombres'] }}</b>.</p>
    <p>Recientemente ha solicitado un cambio de contraseña de su WIFI, la cual ha sido procesada el día de hoy, a continuación te mostramos los detalles:</p>
    <p><b>Fecha de Solicitud:</b> {{ $datos['fecha'] }}</p>
    <p><b>Nombre de Red:</b> {{ $datos['red_nueva'] }}</p>
    <p><b>Clave de Red:</b> {{ $datos['pass_nueva'] }}</p>
    <p><b>Estado de Red (Oculta):</b> {{ $datos['oculta'] }}</p>
    <p><b>IP de Red:</b> {{ $datos['ip'] }}</p>

    <p align="center">Con {{Auth::user()->empresa()->nombre}} te ofrecemos la mejor atención y sobre todo el mejor servicio de conexión a internet.</p>
</body>
</html>