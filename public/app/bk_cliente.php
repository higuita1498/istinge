<?php
    header('Content-Type: application/json');
    
    include "conexion.php";
    
    if($_GET){
        $id_cliente = strip_tags($_GET['id_cliente']);

        $cliente = "SELECT c.nombre, c.nit, c.direccion, c.celular, c.email FROM contactos AS c WHERE c.id = '$id_cliente'";

        $result = mysqli_fetch_assoc(mysqli_query($con, $cliente));
        
        if($result){
            $json['success'] = 'true';
            $json['cliente'] = array_map("utf8_encode", $result);
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