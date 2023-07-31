<?php
    include "conexion.php";
    session_start();
    if (isset($_SESSION['logueado']) && $_SESSION['logueado']) {
        $usuario_actual = $_SESSION['username'];
        $cliente_actual = "SELECT id_cliente, uid_cliente FROM usuarios_app WHERE user = '$usuario_actual'";
        $result_cliente = mysqli_query($con,$cliente_actual);
        $assoc_cliente = mysqli_fetch_assoc($result_cliente);
        $cliente = $assoc_cliente['id_cliente'];
        $uid_cliente = $assoc_cliente['uid_cliente'];
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
        <title><?=$title;?> | Respuesta de Pago</title>
        <link href="assets/css/pace.min.css" rel="stylesheet"/>
        <script src="assets/js/pace.min.js"></script>
        <!--favicon-->
        <link rel="icon" href="assets/images/logo0.png" type="image/x-icon">
        <link href="assets/plugins/vectormap/jquery-jvectormap-2.0.2.css" rel="stylesheet"/>
        <link href="assets/plugins/simplebar/css/simplebar.css" rel="stylesheet"/>
        <link href="assets/css/bootstrap.min.css" rel="stylesheet"/>
        <link href="assets/css/animate.css" rel="stylesheet" type="text/css"/>
        <link href="assets/css/icons.css" rel="stylesheet" type="text/css"/>
        <link href="assets/css/sidebar-menu.css" rel="stylesheet"/>
        <link href="assets/css/app-style.css" rel="stylesheet"/>
        <style>.invoice-title h2, .invoice-title h3 {display: inline-block; }.table > tbody > tr > .no-line { border-top: none; } .table > thead > tr > .no-line { border-bottom: none; }.table > tbody > tr > .thick-line { border-top: 2px solid; }</style>
    </head>

    <body class="bg-theme bg-theme2">
        <div id="wrapper">
            <?php include "partials/header.php"; ?>

            <div class="clearfix"></div>

            <div class="content-wrapper">
                <div class="container-fluid">
                    <div class="card">
                        <div class="row">
                            <div class="container-fluid">
                                <div class="card shadow mb-0">
                                    <div class="card-body">
                                        <div class="col-lg-12">
                                            <h4 class="text-center"> Respuesta de la Transacción </h4><hr>
                                        </div>
                                        <div class="col-lg-12">
                                            <div class="table-responsive">
                                                <table class="table table-bordered">
                                                    <tbody>
                                                        <tr>
                                                            <td class="bold w-50">Factura Nro</td>
                                                            <td class="w-50" id="referencia"></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="bold w-50">Monto</td>
                                                            <td class="w-50" id="monto"></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="bold w-50">Descripción</td>
                                                            <td class="w-50" class="" id="desc"></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="bold w-50">Status</td>
                                                            <td class="w-50" class="" id="status"></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
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
        </div>
  
        <script src="assets/js/jquery.min.js"></script>
    <script>
    $(document).ready(function(){
        wompi = 'https://production.wompi.co/v1/transactions/<?=$_GET['id'];?>';
        uid = '<?=$uid_cliente;?>';
        $.ajax({
            url: wompi,
            dataType: 'json',
            success: function(data) {
                console.log(data);
                var theDiv = document.getElementById("monto");
                var content = document.createTextNode(data.data.amount_in_cents/100+" COP");
                theDiv.appendChild(content);
                
                if(data.data.status == "APPROVED"){
                    var theDiv2 = document.getElementById("status");
                    var content2 = document.createTextNode("Aprobada");
                    theDiv2.appendChild(content2);
                }else if(data.data.status == "VOIDED"){
                    var theDiv2 = document.getElementById("status");
                    var content2 = document.createTextNode("Anulada");
                    theDiv2.appendChild(content2); 
                }else if(data.data.status == "DECLINED"){
                    var theDiv2 = document.getElementById("status");
                    var content2 = document.createTextNode("Rechazada");
                    theDiv2.appendChild(content2); 
                }else if(data.data.status == "PENDING"){
                    var theDiv2 = document.getElementById("status");
                    var content2 = document.createTextNode("Pendiente");
                    theDiv2.appendChild(content2); 
                }else{
                    var theDiv2 = document.getElementById("status");
                    var content2 = document.createTextNode("Error Desconocido");
                    theDiv2.appendChild(content2); 
                }
                $("#desc").text('Pago de Factura');
                
                var theDiv3 = document.getElementById("referencia");
                var content3 = document.createTextNode(data.data.reference);
                theDiv3.appendChild(content3);
                var reference = data.data.reference;
                var saldo = data.data.amount_in_cents/100 + ".00";
                var transactionId = data.data.id;
                var status = data.data.status;
                
                if(data.data.status == "APPROVED"){
                    $.ajax({
                        type : 'POST',
                        url  : 'bk_pay.php',
                        data : {reference:reference, saldo:saldo, status:status, transactionId:transactionId, uid: uid},
                        success : function(data){
                            alert(data.mensaje);
                        }
                    });
                }
            }
        });
        return false;
    });
    </script>
   
  <script src="assets/js/popper.min.js"></script>
  <script src="assets/js/bootstrap.min.js"></script>
  
 <!-- simplebar js -->
  <script src="assets/plugins/simplebar/js/simplebar.js"></script>
  <!-- sidebar-menu js -->
  <script src="assets/js/sidebar-menu.js"></script>
  <!-- loader scripts -->
  <!-- Custom scripts -->
  <script src="assets/js/app-script.js"></script>
  <!-- Chart js -->
 
  <!-- Index js -->
  <?php include "partials/whatsapp.php"; ?>
  <?php include "partials/bot.php"; ?>
    </body>
</html>
