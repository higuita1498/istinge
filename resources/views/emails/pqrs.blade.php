<!DOCTYPE html>
<html>
<head>
	<title>Notificación de Respuesta a Solicitud</title>
</head>
<body>
	<div align="center">
		<img src="{{asset('/images/Empresas/Empresa1/logo.png')}}" width="25%">
	</div>
    <p>
        <b>Notificación de Respuesta a Solicitud</b>
    </p>
    
    <p>Hola, <b>{{ $datos['nombres'] }}</b>.</p>
    <p>Recientemente ha registrado una solictud en nuestra página web, la cual ha sido procesada el día de hoy, a continuación te mostramos los detalles:</p>
    <p><b>Tipo de Solicitud:</b> {{ $datos['solicitud'] }}</p>
    <p><b>Fecha de Solicitud:</b> {{ $datos['fecha'] }}</p>
    <p><b>Mensaje de solicitud:</b> {{ $datos['mensaje'] }}</p>

    <p><h4>Respuesta a su Solicitud</h4></p>
    
    <p><b>Fecha de Respuesta:</b> {{ $datos['fecha_resp'] }}</p>
    <p><b>Respuesta dada por:</b> {{ $datos['updated_by'] }}</p>
    <p><b>Respuesta a su solicitud:</b> {{ $datos['respuesta'] }}</p>

    <p align="center">Con {{Auth::user()->empresa()->nombre}} te ofrecemos la mejor atención y sobre todo el mejor servicio de conexión a internet.</p>
</body>
</html>