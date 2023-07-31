<?php
$sessionTime = 365 * 24 * 60 * 60; // 1 año de duración
session_set_cookie_params($sessionTime);
session_start();
if ( (isset($_POST['user']) && !empty($_POST['user'])) && (isset($_POST['pass']) && !empty($_POST['pass'])) ) {
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        $user = str_replace("'", "`", $_POST['user']);
        $pass = str_replace("'", "`", $_POST['pass']);

        include "conexion.php";
        $query = "SELECT user,password,id_cliente FROM usuarios_app WHERE user = '$user' AND password = '$pass' AND status = '1'";
        $result = mysqli_query($con,$query);
        $assoc_f = mysqli_fetch_assoc($result);
        $id_cliente = $assoc_f['id_cliente'];
        $uid_cliente = $assoc_f['uid_cliente'];
        $num_rows = mysqli_num_rows($result);
        $user_exist = false;
        if ($num_rows > 0) {
            $user_exist = true;
            if ($user_exist) {
                $_SESSION['logueado'] = true;
                $_SESSION['username'] = $user;
                $_SESSION['id_cliente'] = $id_cliente;
                $_SESSION['uid_cliente'] = $uid_cliente;
                header("Location: dashboard.php");
            }
        }else{
            echo "<script>alert('Usuario o Contraseña invalidos'); window.location.href='index.php';</script>";
        }
}
}else{
    echo "<script>alert('Por favor llena los campos vacios'); window.location.href='index.php';</script>";
}
?>
