<?php
include "conexion.php";
session_start();
if (isset($_SESSION['logueado']) && $_SESSION['logueado']) {
    $usuario_actual = $_SESSION['username'];
    $cliente_actual = "SELECT id_cliente FROM usuarios_app WHERE user = '$usuario_actual'";
    $result_cliente = mysqli_query($con,$cliente_actual);
    $assoc_cliente = mysqli_fetch_assoc($result_cliente);
    $cliente = $assoc_cliente['id_cliente'];
}else{
   header("Location: index.php");
}
    $active = 2;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
  <meta name="description" content=""/>
  <meta name="author" content=""/>
  <title><?=$title;?> | Detalles de tu Plan Contratado</title>
  <link href="assets/css/pace.min.css" rel="stylesheet"/>
  <script src="assets/js/pace.min.js"></script>
  <link rel="icon" href="assets/images/logo0.png" type="image/x-icon">
  <link href="assets/plugins/vectormap/jquery-jvectormap-2.0.2.css" rel="stylesheet"/>
  <link href="assets/plugins/simplebar/css/simplebar.css" rel="stylesheet"/>
  <link href="assets/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="assets/css/animate.css" rel="stylesheet" type="text/css"/>
  <link href="assets/css/icons.css" rel="stylesheet" type="text/css"/>
  <link href="assets/css/sidebar-menu.css" rel="stylesheet"/>
  <link href="assets/css/app-style.css" rel="stylesheet"/>
</head>

<body class="bg-theme bg-theme2">
    <div id="wrapper">
        <?php include "partials/header.php"; ?>
        <div class="clearfix"></div>
        <div class="content-wrapper">
            <div class="container-fluid">
                <div class="card mt-3">
                    <div class="card-content"><br>
                        <div align="center">
                            <h4>DETALLES DE TU PLAN CONTRATADO</h4>
                        </div><br>
                        <div class="row row-group m-0 pb-5">
                            <div class="col-md-3">
                                <div class="card-body" align="center">
                                  <div style="background-color: transparent; border-radius: 25px; width: 200px;">
                                     <img src="assets/images/factura.png" width="138px"><br>
                                     <b style="color: white; font-size: 20px;">Nombre</b><br>
                                     <b style="color: white; font-size: 20px;" id="nombreplan"></b>
                                   </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card-body" align="center">
                                    <div style="background-color: transparent; border-radius: 25px; width: 200px;">
                                        <img src="assets/images/upload.png" width="150px"><br>
                                        <b style="color: white; font-size: 20px;">Velocidad de subida</b><br>
                                        <b style="color: white; font-size: 20px;" id="subida"></b>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card-body" align="center">
                                    <div style="background-color: transparent; border-radius: 25px; width: 200px;">
                                        <img src="assets/images/download.png" width="145px"><br>
                                        <b style="color: white; font-size: 20px;">Velocidad de bajada</b>
                                        <br><b style="color: white; font-size: 20px;" id="bajada"></b>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card-body" align="center">
                                    <div style="background-color: transparent; border-radius: 25px; width: 200px;">
                                       <img src="assets/images/price.png" width="138px"><br>
                                       <b style="color: white; font-size: 20px;">Precio</b>
                                       <br><b style="color: white; font-size: 20px;" id="precio"></b> <b>COP</b>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            
                <div class="card mt-3">
                    <div class="card-content"><br>
                        <div align="center">
                            <h4>Mide tu velocidad aquí</h4>
                        </div><br>
                        <div class="row row-group m-0">
                            <div class="col-md-12 border-light">
                                <a href="https://istsas.speedtestcustom.com/" target="_blank">
                                    <div class="card-body" align="center">
                                        <div style="background-color: transparent; border-radius: 25px;  width: 200px;">
                                            <img src="assets/images/speed.png" width="120px"><br>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                 </div>
                 
                <div class="overlay toggle-menu"></div>
            </div>
        </div>
        
        <a href="javaScript:void();" class="back-to-top"><i class="fa fa-angle-double-up"></i> </a>
        
        <?php include "partials/footer.php"; ?>
    </div>
    
    <script src="assets/js/jquery.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            var id_cliente = <?= $cliente; ?>;
            $.ajax({
                type : 'GET',
                url  : 'bk_all.php',
                data : {id_cliente:id_cliente},
                dataType: 'JSON',
                success : function(data){
                    document.getElementById('nombreplan').innerHTML = data.plan.plan;
                    document.getElementById('subida').innerHTML = data.plan.subida;
                    document.getElementById('bajada').innerHTML = data.plan.bajada;
                    document.getElementById('precio').innerHTML = data.plan.precio;
                }
            });
            return false;
        });
    </script>
    
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/plugins/simplebar/js/simplebar.js"></script>
    <script src="assets/js/sidebar-menu.js"></script>
    <script src="assets/js/jquery.loading-indicator.js"></script>
    <script src="assets/js/app-script.js"></script>
    <script src="assets/plugins/Chart.js/Chart.min.js"></script>
    <script src="assets/js/index.js"></script>
    <?php include "partials/whatsapp.php"; ?>
    <?php include "partials/bot.php"; ?>
</body>
</html>
