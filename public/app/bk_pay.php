<?php
    header('Content-Type: application/json');
    date_default_timezone_set('America/Bogota');
    
    include "conexion.php";
    
    if($_POST){
        $saldo = strip_tags($_POST['saldo']);
        //$saldo -= 2800;
        $reference = strip_tags($_POST['reference']);
        $transactionId = strip_tags($_POST['transactionId']);
        $uid = strip_tags($_POST['uid']);
        $fecha = date('Y-m-d');
        
        if(!empty($_POST['status'])){
            $status = strip_tags($_POST['status']);
        }
        
        //FACTURA
        $query = "SELECT * FROM factura WHERE codigo = $reference";
        $result_query = mysqli_query($con,$query);
        $assoc_f = mysqli_fetch_assoc($result_query);
        $factura = $assoc_f['id'];
        $cliente = $assoc_f['cliente'];
        $estatus = $assoc_f['estatus'];
        
        if($estatus == 1){
            //CONSECUTIVO DE CAJA
            $query = "SELECT nro FROM ingresos WHERE empresa = 1 ORDER BY id DESC LIMIT 1";
            $result_query = mysqli_query($con,$query);
            $assoc_n = mysqli_fetch_assoc($result_query);
            $nro = $assoc_n['nro'];
            $nro++;
        
            //REGISTRO DEL INGRESO
            $query = "INSERT INTO ingresos (nro, empresa, cliente, cuenta, metodo_pago, fecha, observaciones, tipo, estatus) VALUES ('$nro', '1', '$cliente', '1', '9', '$fecha', 'Pago Wompi ID: $transactionId', '1', '1')";
            mysqli_query($con,$query);
            
            $query = "SELECT MAX(id) AS id FROM ingresos";
            $result_query = mysqli_query($con,$query);
            $assoc_i = mysqli_fetch_assoc($result_query);
            $id_ingreso = $assoc_i['id'];
            
            //REGISTRO INGRESOS_FACTURA
            $query = "INSERT INTO ingresos_factura (ingreso, factura, pagado, pago) VALUES ('$id_ingreso', '$factura', '0.00', '$saldo')";
            mysqli_query($con,$query);

            //AUMENTA EL CONSECUTIVO DE CAJA
            $nro++;
            $query = "UPDATE numeraciones SET caja = '$nro' WHERE empresa = 1";
            mysqli_query($con,$query);
            
            //ASENTAMOS EL MOVIMIENTO DE INGRESO
            $query = "INSERT INTO movimientos (empresa, banco, contacto, tipo, saldo, fecha, modulo, id_modulo, descripcion) VALUES ('1', '1', '$cliente', '1', '$saldo', '$fecha', '1', '$id_ingreso', '$id_ingreso')";
            mysqli_query($con,$query);
            
            //ACTUALIZAMOS LA FACTURA
            $query = "UPDATE factura SET estatus = 0 WHERE codigo = '$reference'";
            mysqli_query($con,$query);
            
            //ACTUALIZAMOS EL CONTRATO
            $query = "UPDATE contracts SET state = 'enabled' WHERE client_id = '$uid'";
            mysqli_query($con,$query);
            
            //INFO CONTRATO
            $query = "SELECT * FROM contracts WHERE client_id = '$uid'";
            $result_query = mysqli_query($con,$query);
            $assoc_e = mysqli_fetch_assoc($result_query);
            $contrato = $assoc_e['id'];
            
            //INFO WISPRO
            $query = "SELECT wispro FROM empresas WHERE id = 1";
            $result_query = mysqli_query($con,$query);
            $assoc_e = mysqli_fetch_assoc($result_query);
            $wispro = $assoc_e['wispro'];
            
            //CONECTAMOS A WISPRO Y ACTUALIZAMOS EL CONTRATO
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => "https://www.cloud.wispro.co/api/v1/contracts/".$contrato."?state=enabled",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "PUT",
                CURLOPT_HTTPHEADER => array(
                    "Authorization: ".$wispro
                ),
            ));
            
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            
            if($response){
                $json['success'] = 'true';
                $json['mensaje'] = 'Pago registrado correctamente en la plataforma.';
                echo json_encode($json);
                exit;
            }else{
                $json['success'] = 'false';
                $json['mensaje'] = 'Ha ocurrido un error, por favor contacte a un asesor para mayores detalles';
                echo json_encode($json);
                exit;
            }
        }else{
            $json['success'] = 'false';
            $json['mensaje'] = 'Su pago ya se encuentra asociada a una factura';
            echo json_encode($json);
            exit;
        }
   }
?>