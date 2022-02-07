<?php
    header('Content-Type: application/json');
    
    include "conexion.php";
    
    if($_GET){
        $identificacion = strip_tags($_GET['identificacion']);

        $radicado = "SELECT id, fecha, desconocido, servicio, tecnico, reporte, estatus, codigo, responsable FROM `radicados` WHERE identificacion = '$identificacion' AND estatus = 0 OR estatus = 2";

        $result = mysqli_fetch_assoc(mysqli_query($con, $radicado));
        
        if($result){
            $json['success']  = 'true';
            $json['radicado'] = array_map("utf8_encode", $result);
            echo json_encode($json);
            exit;
        }else{
            $json['success'] = 'false';
            $json['mensaje'] = 'No existe radicados asociados al cliente';
            echo json_encode($json);
            exit;
        }
   }
?>