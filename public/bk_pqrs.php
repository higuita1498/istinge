<?php
    header('Content-Type: application/json');
    date_default_timezone_set('America/Bogota');
    
    include "./app/conexion.php";
    
    if($_POST){
        $solicitud = strip_tags($_POST['solicitud']);
        $nombres = strip_tags($_POST['nombres']);
        $email = strip_tags($_POST['email']);
        $telefono = strip_tags($_POST['telefono']);
        $direccion = strip_tags($_POST['direccion']);
        $mensaje = strip_tags($_POST['mensaje']);
        $fecha = date('Y-m-d');
        
        //REGISTRO
        $query = "INSERT INTO pqrs (solicitud, nombres, email, telefono, direccion, mensaje, fecha) VALUES ('$solicitud', '$nombres', '$email', '$telefono', '$direccion', '$mensaje', '$fecha')";
        mysqli_query($con,$query);
        
        $query = "SELECT MAX(id) AS id FROM pqrs";
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