<?php
    header('Content-Type: application/json');
    date_default_timezone_set('America/Bogota');
    
    include "conexion.php";
    
    if($_POST){
        $usuario = strip_tags($_POST['usuario']);
        $nit = strip_tags($_POST['nit']);
        
        //CONTACTOS
        $query = "SELECT * FROM contactos WHERE nit = $nit";
        $result_query = mysqli_query($con,$query);
        $assoc_c = mysqli_fetch_assoc($result_query);
        $id_cliente = $assoc_c['id'];
        $email = $assoc_c['email'];
        
        if($id_cliente){
            //USUARIOAPP
            $query = "SELECT * FROM usuarios_app WHERE id_cliente = $id_cliente";
            $result_query = mysqli_query($con,$query);
            $assoc_u = mysqli_fetch_assoc($result_query);
            $usuario = $assoc_u['user'];
            $password = $assoc_u['password'];
            
            $remitente = 'info@toplink.com.co';
            $destinatario = $email;
            $asunto = "Recuperación de Acceso a la APP";
            $mensaje = "Buen día, le hacemos envío de las credenciales de acceso a la APP. Usuario: ".$usuario." Contraseña: ".$password;
            $headers = "From: $remitente\r\nReply-to: $remitente";
            mail($destinatario, $asunto, $mensaje, $headers);
            
            $json['type'] = 'success';
            $json['title'] = 'CORREO ENVIADO';
            $json['mensaje'] = 'Se ha validado la información y se procedió a enviarle las credenciales vía correo electrónico.';
            echo json_encode($json);
            exit;
        }else{
            $json['type'] = 'error';
            $json['title'] = 'ERROR';
            $json['mensaje'] = 'Los datos suministrados son erróneos, intente nuevamente';
            echo json_encode($json);
            exit;
        }
   }
?>