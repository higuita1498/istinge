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
 ?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
        <meta name="description" content=""/>
        <meta name="author" content=""/>
        <title><?=$title;?> | Dashboard</title>
        <!-- loader-->
        <link href="assets/css/pace.min.css" rel="stylesheet"/>
        <script src="assets/js/pace.min.js"></script>
        <!--favicon-->
        <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
        <!-- Vector CSS -->
        <!-- link href="assets/plugins/vectormap/jquery-jvectormap-2.0.2.css" rel="stylesheet"/-->
        <!-- simplebar CSS-->
        <link href="assets/plugins/simplebar/css/simplebar.css" rel="stylesheet"/>
        <!-- Bootstrap core CSS-->
        <link href="assets/css/bootstrap.min.css" rel="stylesheet"/>
        <!-- animate CSS-->
        <link href="assets/css/animate.css" rel="stylesheet" type="text/css"/>
        <!-- Icons CSS-->
        <link href="assets/css/icons.css" rel="stylesheet" type="text/css"/>
        <!-- Sidebar CSS-->
        <link href="assets/css/sidebar-menu.css" rel="stylesheet"/>
        <!-- Custom Style-->
        <link href="assets/css/app-style.css" rel="stylesheet"/>
        <style>
            .email-signature{background:#fff;font-family:Ubuntu,sans-serif;padding:40px 15px 10px 230px;box-shadow:0 0 10px -5px #555;overflow:hidden;position:relative}.email-signature:after,.email-signature:before{content:'';background:radial-gradient(#b00606,#b00606,#b00606);height:400px;width:400px;border-radius:50%;position:absolute;left:0;bottom:-160px;left:-215px}.email-signature:after{background:radial-gradient(#b00606,#b00606,#b00606);height:330px;width:330px;border:25px solid #fff;box-shadow:0 0 10px rgba(0,0,0,.5)}.email-signature .signature-icon{color:#b00606;background-color:#fff;font-size:70px;text-align:center;line-height:150px;height:150px;width:150px;border-radius:50%;box-shadow:0 0 10px #555;position:absolute;left:30px;bottom:30px;z-index:1}.email-signature .signature-details{margin:0 0 10px 0}.email-signature .signature-details:before{content:'';background:radial-gradient(#b00606,#b00606,#b00606);height:100px;width:100px;border-radius:50%;position:absolute;top:-40px;right:-40px}.email-signature .title{color:#b00606;font-size:27px;font-weight:700;text-transform:uppercase;margin:0}.email-signature .title span{color:#222;font-weight:500}.email-signature .post{color:#555;font-size:13px;font-weight:400;letter-spacing:3px;text-transform:uppercase}.email-signature .signature-content{font-size:0;padding:0;margin:0;list-style:none}.email-signature .signature-content li{color:#666;font-size:13px;letter-spacing:.5px;margin:0 0 4px}.email-signature .signature-content li span{font-size:12px;margin:0 5px 0 0}.email-signature .icon{text-align:right;padding:0;margin:0;list-style:none}.email-signature .icon li{margin:0 1px;display:inline-block}.email-signature .icon li a{color:#888;font-size:13px;text-align:center;line-height:25px;height:25px;width:25px;border:1px solid #888;border-radius:50%;transition:all .3s ease 0s}.email-signature .icon li a:hover{color:#b00606}@media screen and (max-width:576px){.email-signature{padding:190px 10px 10px}.email-signature:after,.email-signature:before{transform:translateX(-50%);bottom:auto;top:-240px;left:50%}.email-signature .signature-content,.email-signature .signature-details{text-align:center;margin:0 0 15px}.email-signature .signature-details:before{display:none}.email-signature .signature-icon{transform:translateX(-50%);left:50%;bottom:auto;top:20px}.email-signature .icon{text-align:center}}
        </style>
    </head>

    <body class="bg-theme bg-theme2">
        <!-- Start wrapper-->
        <div id="wrapper">
            <?php include "partials/header.php"; ?>
            
            <div class="clearfix"></div>
            
            <div class="content-wrapper">
                <!-- <div class="card mt-3" style="border-radius: 25px;">
                    <div class="card-content" style="background-color: white; border-radius: 25px;"><br>
                        <div align="center">
                            <img src="assets/images/logo.png" width="250px">
                        </div>
                    </div>
                </div>-->
                <div class="container-fluid">
                    <div class="card mt-3 p-3" style="background-color: rgba(0,0,0,.7);">
                        <div class="card-content">
                            <div class="row" style="place-content: center;">
                                <div class="col-lg-12 col-md-offset-2 col-md-8 col-sm-offset-1 col-sm-10">
                                    <div class="email-signature">
                                        <div class="signature-icon">
                                            <img src="assets/images/logo.png" class="w-75">
                                        </div>
                                        <div class="signature-details">
                                            <h2 class="title">Bienvenido, <span id="nombre_cliente"></span></h2>
                                        </div>
                                        <ul class="signature-content">
                                            <li><span class="fa fa-user"></span> <span id="nit_cliente"></span></li>
                                            <li><span class="fa fa-envelope"></span> <span id="email_cliente"></span></li>
                                            <li><span class="fa fa-map-marker"></span> <span id="direccion_cliente"></span></li>
                                            <li><span class="fa fa-phone"></span> <span id="tel_cliente"></span></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>    
                
                    <div class="card mt-3">
                        <div class="card-content"><br>
                            <div align="center">
                                <h4>Administra tu cuenta hogar</h4>
                            </div><br>
                            <div class="row row-group m-0">
                                <div class="col-md-4 border-light">
                                    <div class="card-body" align="center">
                                       <a href="plan.php"><div style="background-color: transparent; border-radius: 25px; width: 200px;">
                                         <img src="assets/images/consumo.png" width="150px">
                                         <b style="color: white;">Consulta tu plan detallado</b>
                                       </div></a>
                                    </div>
                                </div>
                                <div class="col-md-4 border-light">
                                    <div class="card-body" align="center">
                                        <a href="invoice.php">
                                        <div style="background-color: transparent; border-radius: 25px;  width: 200px;">
                                            <img src="assets/images/factura.png" width="150px"><br>
                                            <b style="color: white;">Paga tu Factura</b>
                                        </div>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-4 border-light" style="place-self: center;">
                                    <div class="card-body" align="center">
                                        <a href="plan.php">
                                        <div style="background-color: transparent; border-radius: 25px;  width: 200px;">
                                            <b style="color: white; font-size: 20px;">PLAN CONTRATADO</b><br>
                                            <b style="color: white; font-size: 20px;" id="nombreplan"></b><br>
                                            <b style="font-size: 20px;" id="stateplan"></b>
                                        </div>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-4 border-light" id="div_link">
                                    <div class="card-body" align="center">
                                        <a href="wifi.php" id="link">
                                        <div style="background-color: transparent; border-radius: 25px;  width: 200px;">
                                            <img src="assets/images/wifi.png" width="200px"><br>
                                            <b style="color: white;">Cambiar contraseña WIFI</b>
                                        </div>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-4 border-light d-none" id="div_link_wifi">
                                    <div class="card-body" align="center">
                                        <div style="background-color: transparent; border-radius: 25px;  width: 200px;">
                                            <img src="assets/images/wifi.png" width="180px"><br>
                                            <b style="color: white;">Red: <span id="nombre_red"></span></b><br>
                                            <b style="color: white;">Contraseña: <span id="pass_red"></span></b>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                
                    <div class="card mt-3">
                        <div class="card-content"><br>
                            <div align="center">
                                <h4>Atención al cliente y soporte</h4>
                            </div><br>
                            <div class="row row-group m-0">
                                <div class="col-md-12 border-light">
                                    <div class="card-body" align="center">
                                        <div style="background-color: transparent; border-radius: 25px;  width: 200px;">
                                            <a href="tel:+"><img src="assets/images/phone.png" width="80px"><br>
                                            <b style="color: white;">Llamar</b> 
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                
                    <div class="overlay toggle-menu"></div>
                </div>
            </div>
        
        <a href="javaScript:void();" class="back-to-top"><i class="fa fa-angle-double-up"></i> </a>
        
        <?php include "partials/footer.php"; ?>
        
        <script src="assets/js/jquery.min.js"></script>
        
        <script type="text/javascript">
            $(document).ready(function(){
            var id_cliente = <?php echo $cliente; ?>;
            $.ajax({
                type : 'GET',
                url  : 'bk_all.php',
                data : {id_cliente:id_cliente},
                dataType: 'JSON',
                success : function(data){
                    document.getElementById('nombre_cliente').innerHTML = data.cliente.nombre;
                    document.getElementById('nit_cliente').innerHTML = data.cliente.nit;
                    document.getElementById('email_cliente').innerHTML = data.cliente.email;
                    document.getElementById('direccion_cliente').innerHTML = data.cliente.direccion;
                    document.getElementById('tel_cliente').innerHTML = data.cliente.celular;
                    
                    document.getElementById('nombreplan').innerHTML = data.plan.plan;
                    if(data.contrato.state == 'enabled'){
                        document.getElementById('stateplan').innerHTML = 'HABILITADO';
                        $('#stateplan').attr('style','color: green');
                    }else{
                        document.getElementById('stateplan').innerHTML = 'DESHABILITADO';
                        $('#stateplan').attr('style','color: red');
                    }
                    
                    if(data.wifi){
                        if(data.wifi.status == 1){
                            $('#div_wifi, #div_link').addClass('d-none');
                            $('#div_link_wifi').removeClass('d-none');
                        }else{
                            $('#div_wifi, #div_link').removeClass('d-none');
                            $('#div_link_wifi').addClass('d-none');
                        }
                    }
                    
                    if(data.red == null){
                        $('#div_link_wifi').addClass('d-none');
                    }else{
                        $('#div_link_wifi').removeClass('d-none');
                        document.getElementById('nombre_red').innerHTML = data.red.red_nueva;
                        document.getElementById('pass_red').innerHTML = data.red.pass_nueva;
                    }
                }
            });
            return false;
        });
        </script>
   
        <script src="assets/js/popper.min.js"></script>
        <script src="assets/js/bootstrap.min.js"></script>
        <script src="assets/plugins/simplebar/js/simplebar.js"></script>
        <script src="assets/js/sidebar-menu.js"></script>
        <!-- script src="assets/js/jquery.loading-indicator.js"></script-->
        <script src="assets/js/app-script.js"></script>
        <script src="assets/plugins/Chart.js/Chart.min.js"></script>
        <?php include "partials/whatsapp.php"; ?>
        <?php include "partials/bot.php"; ?>
    </body>
</html>
