<?php
    header('Content-Type: application/json');
    
    include "conexion.php";
    
    if($_GET){
        $id_cliente = strip_tags($_GET['id_cliente']);

        $plan = "SELECT p.name plan, p.price precio, p.ceil_down_kbps bajada, p.ceil_up_kbps subida FROM usuarios_app AS u JOIN contracts AS cs ON u.uid_cliente = cs.client_id JOIN planes AS p ON p.id = cs.plan_id WHERE u.id_cliente = '$id_cliente'";

        $result = mysqli_fetch_assoc(mysqli_query($con, $plan));
        
        if($result){
            $json['success'] = 'true';
            $json['plan']    = array_map("utf8_encode", $result);
            echo json_encode($json);
            exit;
        }else{
            $json['success'] = 'false';
            $json['mensaje'] = 'No existe ningun registro con los parámetros enviados';
            echo json_encode($json);
            exit;
        }
   }
?>