<?php
    header('Content-Type: application/json');
    
    include "conexion.php";
    
    if($_GET){
        $id_cliente = strip_tags($_GET['id_cliente']);

        $contrato = "SELECT cs.id, cs.fecha_corte, cs.fecha_suspension, cs.state, cs.ip, cs.mac_address FROM usuarios_app AS u JOIN contracts AS cs ON u.uid_cliente = cs.client_id WHERE u.id_cliente = '$id_cliente'";

        $result = mysqli_fetch_assoc(mysqli_query($con, $contrato));
        
        if($result){
            $json['success']  = 'true';
            $json['contrato'] = array_map("utf8_encode", $result);
            echo json_encode($json);
            exit;
        }else{
            $json['success'] = 'false';
            $json['mensaje'] = 'No posee ningún contrato asociado';
            echo json_encode($json);
            exit;
        }
   }
?>