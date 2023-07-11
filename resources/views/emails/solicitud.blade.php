<!DOCTYPE html>
<html>
<head>
	<title>Notificación de Respuesta a Solicitud</title>
</head>
<body>
	<div align="center">
		<img src="{{asset('/images/Empresas/Empresa1/logo.png')}}" width="25%">
	</div>
    <h4>
        <b>Notificación de Respuesta a Solicitud</b>
    </h4>
    
    <p>Hola, <b>{{ $datos['nombres'] }}</b>.</p>
    <p>Recientemente ha registrado una solictud de servicio en nuestra página web, la cual ha sido procesada el día de hoy, a continuación te mostramos los detalles:</p>
    <p><b>Fecha de Solicitud:</b> {{ $datos['fecha'] }}</p>
    <p><b>Servicio solicitado:</b> {{ $datos['plan'] }}</p>
    <p><b>Dirección del Servicio:</b> {{ $datos['direccion'] }}</p>

    <p align="center">Con {{Auth::user()->empresa()->nombre}} te ofrecemos la mejor atención y sobre todo el mejor servicio de conexión a internet.</p>
</body>
</html>