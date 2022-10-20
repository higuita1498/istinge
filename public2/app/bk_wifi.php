<?php
    header('Content-Type: application/json');
    date_default_timezone_set('America/Bogota');
    
    include "conexion.php";
    
    /* CONEXION MIKROTIK
    include "routeros_api.class.php";
    
    $query        = "SELECT nombre, ip, puerto_api, usuario, clave FROM mikrotik WHERE id = 4";
    $result_query = mysqli_query($con, $query);
    $assoc_m      = mysqli_fetch_assoc($result_query);
    $mk_ip = $assoc_m['ip']; $mk_api = $assoc_m['puerto_api']; $mk_usuario = $assoc_m['usuario']; $mk_clave = $assoc_m['clave'];
    
    $API = new RouterosAPI();
    $API->port = $mk_api;
    
    if ($API->connect($mk_ip,$mk_usuario,$mk_clave)) {
        $API->write("/interface wireless security-profiles set wpa2-pre-shared-key=".$contrasena_nuevo." [find name="default"] mode=dynamic-keys");
        $READ = $API->read(false);
        $ARRAY = $API->parse_response($READ);
        $API->disconnect();
    } else {
        
    }
    CONEXION MIKROTIK */
    
    if($_POST){
        $fecha = date('Y-m-d');
        $id_cliente = strip_tags($_POST['id_cliente']);
        $nombre_antiguo = strip_tags($_POST['nombre_antiguo']);
        $nombre_nuevo = strip_tags($_POST['nombre_nuevo']);
        $contrasena_antiguo = strip_tags($_POST['contrasena_antiguo']);
        $contrasena_nuevo = strip_tags($_POST['contrasena_nuevo']);
        $oculta = strip_tags($_POST['red_oculta']);
        
        //INFO CLIENTE
            $query = "SELECT * FROM contactos WHERE id = '$id_cliente'";
            $result_query = mysqli_query($con,$query);
            $assoc_c = mysqli_fetch_assoc($result_query);
            $uid_cliente  = $assoc_c['UID'];
            
        //INFO CONTRATO
            $query = "SELECT * FROM contracts WHERE client_id = '$uid_cliente'";
            $result_query = mysqli_query($con,$query);
            $assoc_e = mysqli_fetch_assoc($result_query);
            $ip  = $assoc_e['ip'];
            $mac = $assoc_e['mac_address'];
        
        //REGISTRO DEL WIFI
            $query = "INSERT INTO wifi (id_cliente, red_antigua, red_nueva, pass_antigua, pass_nueva, ip, mac, fecha, oculta) VALUES ('$id_cliente', '$nombre_antiguo', '$nombre_nuevo', '$contrasena_antiguo', '$contrasena_nuevo', '$ip', '$mac', '$fecha', '$oculta')";
            $result = mysqli_query($con,$query);
            
            if($result){
                $json['success'] = 'true';
                $json['mensaje'] = 'Su contraseña ha sido cambiada exitosamente, verá reflejado los cambios en máximo dos horas laborales';
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