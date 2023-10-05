<!DOCTYPE html>
<html>
<head>
    <title>Archivo Adjunto</title>
</head>
<body>
    <h2>¡Archivo Adjunto!</h2>

    <p>Se ha adjuntado un archivo y se ha enviado por correo electrónico.</p>

    <p>Descarga el archivo adjunto a continuación:</p>

    <a href="{{ asset($rutaArchivo) }}" download="{{ $rutaArchivo }}">Descargar archivo adjunto</a>

    <p>Gracias por usar nuestro servicio.</p>
</body>
</html>
