<?php
    header('Content-Type: application/json');
    
    include "conexion.php";
    
    if($_GET){
        $id_cliente = strip_tags($_GET['id_cliente']);

        $plan = "SELECT p.name plan, p.price precio, p.download bajada, p.upload subida FROM usuarios_app AS u JOIN contracts AS cs ON u.uid_cliente = cs.client_id JOIN planes_velocidad AS p ON p.id = cs.plan_id WHERE u.id_cliente = '$id_cliente'";
        $contrato = "SELECT cs.id, cs.fecha_corte, cs.fecha_suspension, cs.state, cs.ip, cs.mac_address FROM usuarios_app AS u JOIN contracts AS cs ON u.uid_cliente = cs.client_id WHERE u.id_cliente = '$id_cliente'";
        $cliente = "SELECT c.nombre, c.nit, c.direccion, c.celular, c.email FROM contactos AS c WHERE c.id = '$id_cliente'";
        $wifi = "SELECT * FROM wifi WHERE id_cliente = '$id_cliente' ORDER BY id DESC";
        $red = "SELECT * FROM wifi WHERE id_cliente = '$id_cliente' AND status = 0 ORDER BY id DESC";
        //$mikrotik = "SELECT nombre, ip, puerto_web, puerto_api, usuario, clave FROM mikrotik WHERE id = 4";

        $resultp = mysqli_fetch_assoc(mysqli_query($con, $plan));
        $resultc = mysqli_fetch_assoc(mysqli_query($con, $contrato));
        $resultcl = mysqli_fetch_assoc(mysqli_query($con, $cliente));
        $resultw = mysqli_fetch_assoc(mysqli_query($con, $wifi));
        $resultr = mysqli_fetch_assoc(mysqli_query($con, $red));
        //$resultk = mysqli_fetch_assoc(mysqli_query($con, $mikrotik));
        
        if($resultp && $resultc){
            $json['success'] = 'true';
            $json['plan']    = array_map("utf8_encode", $resultp);
            $json['contrato'] = array_map("utf8_encode", $resultc);
            $json['cliente'] = array_map("utf8_encode", $resultcl);
            $json['wifi'] = array_map("utf8_encode", $resultw);
            $json['red'] = array_map("utf8_encode", $resultr);
            //$json['mikrotik'] = array_map("utf8_encode", $resultk);
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