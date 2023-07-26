<?php
    $sessionTime = 365 * 24 * 60 * 60;
    session_set_cookie_params($sessionTime);
    session_start();
    if ((isset($_POST['username']) && !empty($_POST['username'])) && (isset($_POST['pass']) && !empty($_POST['pass'])) && (isset($_POST['nit']) && !empty($_POST['nit']))) {
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $user2 = str_replace("'", "`", $_POST['username']);
            $pass = str_replace("'", "`", $_POST['pass']);
            $nit = str_replace("'", "`", $_POST['nit']);
            
            if (isset($_POST['user-checkbox']) && $_POST['user-checkbox'] == '1')
                $marketing = 1;
            else
                $marketing = 0;
            
            include "conexion.php";
            $info_cliente = "SELECT id, UID FROM contactos WHERE nit = '$nit' AND status = 1";
            $result = mysqli_query($con,$info_cliente);
            $assoc_info = mysqli_fetch_assoc($result);
            $id = $assoc_info['id'];
            $uid = $assoc_info['id'];
            
            if($uid ){
                $existe_contract = "SELECT * FROM `contracts` WHERE client_id = '$uid' AND status = 1";
                $result_user = mysqli_query($con,$existe_contract);
                $assoc_user = mysqli_fetch_assoc($result_user);
                $existe_contract = $assoc_user['id'];
                
                if($existe_contract){
                    date_default_timezone_set('America/Bogota');
                    $fecha = date("Y-m-d h:i:s");
                    
                    $user_duplicado = "SELECT * FROM usuarios_app WHERE user = '$user2'";
                    $result_user = mysqli_query($con,$user_duplicado);
                    $assoc_user = mysqli_fetch_assoc($result_user);
                    $user_duplicado = $assoc_user['id'];
                    
                    $contrato_registrado = "SELECT * FROM usuarios_app WHERE uid_cliente = '$uid'";
                    $result_user = mysqli_query($con,$contrato_registrado);
                    $assoc_user = mysqli_fetch_assoc($result_user);
                    $contrato_registrado = $assoc_user['id'];
                    
                    if($user_duplicado){
                        echo "<script>alert('DISCULPE: El usuario indicado ya está en uso por otro cliente!'); window.location.href='register.php';</script>";
                    }else if($contrato_registrado){
                        echo "<script>alert('DISCULPE: El contrato ya se encuentra registrado en la aplicación!'); window.location.href='register.php';</script>";
                    }else{
                        $query = "INSERT INTO usuarios_app(id_cliente,uid_cliente,user,password,status,marketing,created_at,updated_at) VALUES ('$id','$uid','$user2','$pass','1','$marketing','$fecha','$fecha')";
                        mysqli_query($con,$query);
                        $_SESSION['username'] = $user2;
                        $_SESSION['logueado'] = true;
                        header("Location: dashboard.php");
                    }
                }else{
                    echo "<script>alert('DISCULPE: El cliente no posee un contrato asociado!'); window.location.href='register.php';</script>";
                }
            }else{
                echo "<script>alert('DISCULPE: La identificación indicada no se encuentra registrada por ningún cliente!'); window.location.href='register.php';</script>";
            }
        }
    }else{
    	echo "<script>alert('Por favor llena los campos vacios!'); window.location.href='register.php';</script>";
    }
?>