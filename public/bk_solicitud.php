<?php
    header('Content-Type: application/json');
    date_default_timezone_set('America/Bogota');
    
    include "./app/conexion.php";
    
    if($_POST){
        $nombre = strip_tags($_POST['nombre']);
        $cedula = strip_tags($_POST['cedula']);
        $nrouno = strip_tags($_POST['nrouno']);
        $nrodos = strip_tags($_POST['nrodos']);
        $email = strip_tags($_POST['email']);
        $direccion = strip_tags($_POST['direccion']);
        $plan = strip_tags($_POST['plan']);
        $fecha = date('Y-m-d');
        
        //REGISTRO DEL INGRESO
        $query = "INSERT INTO solicitudes (nombre, cedula, nrouno, nrodos, email, direccion, plan, fecha) VALUES ('$nombre', '$cedula', '$nrouno', '$nrodos', '$email', '$direccion', '$plan', '$fecha')";
        mysqli_query($con,$query);
        
        $query = "SELECT MAX(id) AS id FROM solicitudes";
        $result_query = mysqli_query($con,$query);
        $assoc_i = mysqli_fetch_assoc($result_query);
        $solicitud = $assoc_i['id'];
        
        if($solicitud){
            $json['success'] = 'true';
            $json['mensaje'] = 'Pronto nos estaremos comunicando con Ud. Gracias por confiar en Rapilink S.A.S';
            echo json_encode($json);
            exit;
        }else{
            $json['success'] = 'false';
            $json['mensaje'] = 'Ha ocurrido un error, por favor contacte a un asesor para mayores detalles';
            echo json_encode($json);
            exit;
        }
   }
?>