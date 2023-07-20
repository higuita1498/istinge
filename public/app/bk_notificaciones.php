<?php
    header('Content-Type: application/json');
    
    include "conexion.php";
    
    if($_GET){
        $fecha = strip_tags($_GET['fecha']);

        $notificaciones = "SELECT * FROM notificaciones WHERE desde >= '$fecha' AND hasta <= '$fecha' AND status = 1";

        $result = mysqli_fetch_assoc(mysqli_query($con, $notificaciones));
        
        if($result){
            $json['success']  = 'true';
            $json['notificaciones'] = array_map("utf8_encode", $result);
            echo json_encode($json);
            exit;
        }else{
            $json['success'] = 'false';
            $json['mensaje'] = 'No existe notificaciones para su fecha actual';
            echo json_encode($json);
            exit;
        }
   }
?>