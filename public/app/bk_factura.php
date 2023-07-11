<?php
    header('Content-Type: application/json');
    
    include "conexion.php";
    
    if($_GET){
        $id_cliente = strip_tags($_GET['id_cliente']);

        $factura = "SELECT f.id, f.codigo, f.fecha, f.vencimiento, d.descripcion, d.ref, d.precio, d.desc, i.nombre, i.porcentaje FROM factura AS f JOIN items_factura AS d ON d.factura = f.id JOIN impuestos AS i ON i.id = d.id_impuesto WHERE f.estatus = 1 AND f.tipo = 1 AND f.cliente = '$id_cliente'";
        $resultf = mysqli_fetch_assoc(mysqli_query($con, $factura));
        
        $cliente = "SELECT c.nombre, c.nit, c.direccion, c.celular, c.email FROM contactos AS c WHERE c.id = '$id_cliente'";
        $resultc = mysqli_fetch_assoc(mysqli_query($con, $cliente));
        
        if($resultf && $resultc){
            $json['success']  = 'true';
            $json['factura'] = array_map("utf8_encode", $resultf);
            $json['cliente'] = array_map("utf8_encode", $resultc);
            echo json_encode($json);
            exit;
        }else{
            $json['success'] = 'false';
            $json['mensaje'] = 'No posee ninguna factura pendiente';
            echo json_encode($json);
            exit;
        }
   }
?>