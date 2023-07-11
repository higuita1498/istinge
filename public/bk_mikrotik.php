<?php
    header('Content-Type: application/json');
    date_default_timezone_set('America/Bogota');
    
    require 'routeros_api.class.php';
    
    if($_POST){
        $ip = strip_tags($_POST['ip']);
        $api = strip_tags($_POST['api']);
        $web = strip_tags($_POST['web']);
        $usuario = strip_tags($_POST['usuario']);
        $clave = strip_tags($_POST['clave']);
        
        $API = new RouterosAPI();
        if ($API->connect($ip, $usuario, $clave)) {
            $API->write('/system/resource/print');
            $READ = $API->read(false);
            $ARRAY = $API->parseResponse($READ);
            
            $API->disconnect();
            
            $json['type'] = 'success';
            $json['mensaje'] = 'Conexión a la Mikrotik Realizada';
            $json['title'] = 'SATISFACTORIO';
            $json['boardname'] = $ARRAY[0]['board-name'];
            $json['uptime'] = $ARRAY[0]['uptime'];
            $json['cpuload'] = $ARRAY[0]['cpu-load'];
            $json['version'] = $ARRAY[0]['version'];
            echo json_encode($json);
            exit;
        }else{
            $json['type'] = 'error';
            $json['mensaje'] = 'Conexión a la Mikrotik No Realizada';
            $json['title'] = 'ERROR';
            echo json_encode($json);
            exit;
        }
   }
?>