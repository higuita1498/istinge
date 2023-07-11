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
  <link rel="icon" href="assets/images/logo0.png" type="image/x-icon">
  <!-- Vector CSS -->
  <link href="assets/plugins/vectormap/jquery-jvectormap-2.0.2.css" rel="stylesheet"/>
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
  <style>.invoice-title h2, .invoice-title h3 {display: inline-block; }.table > tbody > tr > .no-line { border-top: none; } .table > thead > tr > .no-line { border-bottom: none; }.table > tbody > tr > .thick-line { border-top: 2px solid; }</style>
    <style type="text/css">

.columns {
  display: flex;
  flex-flow: row wrap;
  justify-content: center;
  margin: 5px 0;
}

.column {
  flex: 1;

  margin: 2px;
  padding: 10px;
  &:first-child { margin-left: 0; }
  &:last-child { margin-right: 0; }

}

@media screen and (max-width: 980px) {
  .columns .column {
    margin-bottom: 5px;
    flex-basis: 40%;
    &:nth-last-child(2) {
      margin-right: 0;
    }
    &:last-child {
      flex-basis: 100%;
      margin: 0;
    }
  }
}

@media screen and (max-width: 680px) {
  .columns .column {
    flex-basis: 100%;
    width: 300px;
  }
}
@media screen and (max-width: 680px) {
  .columns {
  display: flex;
  flex-flow: row wrap;
}
  }
  .wrapper2 {
  max-width: 960px;
  width: 95%;
  margin: 20px auto;
  margin-top: -175px;
  height: 100px;
}

.columns2 {
  display: flex;
  flex-flow: row wrap;
  justify-content: center;
  margin: 5px 0;
  margin-top: -60px;
  height: 100px;
}

.column2 {
  flex: 1;

  margin: 2px;
  padding: 0px;
  &:first-child { margin-left: 0; }
  &:last-child { margin-right: 0; }

}
@media screen and (max-width: 991px){
   .wrapper2 {
  max-width: 960px;
  width: 95%;
  margin: 20px auto;
  margin-top: -175px;
  height: 250px;
}

.columns2 {
  display: flex;
  flex-flow: row wrap;
  justify-content: center;
  margin: 5px 0;
  margin-top: -60px;
  height: 250px;
}
}
@media screen and (max-width: 767px){
   .wrapper2 {
  max-width: 960px;
  width: 95%;
  margin: 20px auto;
  margin-top: -37px;
  height: 250px;
}

.columns2 {
  display: flex;
  flex-flow: row wrap;
  justify-content: center;
  margin: 5px 0;
  margin-top: -60px;
  height: 250px;
}
}
@media screen and (max-width: 680px){
   .wrapper2 {
  max-width: 960px;
  width: 95%;
  margin: 20px auto;
  margin-top: -37px;
  height: 250px;
}

.columns2 {
  display: flex;
  flex-flow: row wrap;
  justify-content: center;
  margin: 5px 0;
  margin-top: -60px;
  height: 250px;
}
}

@media screen and (max-width: 980px) {
  .columns2 .column2 {
    margin-bottom: 5px;
    flex-basis: 40%;
    &:nth-last-child(2) {
      margin-right: 0;
    }
    &:last-child {
      flex-basis: 100%;
      margin: 0;
    }
  }
}

@media screen and (max-width: 680px) {
  .columns2 .column2 {
    flex-basis: 100%;
    margin: 0 0 5px 0;
  }
}
@media screen and (max-width: 680px) {
  .columns2 {
  display: flex;
  flex-flow: row wrap;
  justify-content: center;
  margin: 5px 0;
  margin-top: 70px;
}
  }
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
        <div class="card mt-3" style="padding: 15px;">
            <div class="d-none" id="invoice_view">
                    	<div class="invoice-title">
                			<h3>Factura</h3><h3 class="pull-right">Nro. <i id="factura_codigo"></i></h3>
                		</div>
                		<hr>
  <section class="columns">
  <div class="column">

       <address>
        <strong>Cliente</strong><br>
        <i id="cliente_nombre"></i><br>
        <strong>NIT</strong><br>
        <i id="cliente_nit"></i><br>
        </address>

  </div>

  <div class="column">

         <address>
            <strong>Fecha Factura</strong><br>
            <i id="factura_fecha"></i><br>
            <strong>Vencimiento Factura</strong><br>
            <i id="factura_vencimiento"></i><br>
        </address>

  </div>

</section>
                	</div>
            <div class="row">
                	<div class="col-md-12">
                		<div class="panel panel-default">
                			<div class="panel-heading">
                				<h3 class="panel-title"><strong></strong></h3>
                			</div>
                			<div class="panel-body">
                				<div class="table-responsive">
                					<table class="table table-condensed">
                						<thead>
                                            <tr>
                    							<td><strong>Item</strong></td>
                    							<td class="text-center"><strong>Ref</strong></td>
                    							<td class="text-center"><strong>Imp</strong></td>
                    							<td class="text-center"><strong>Desc</strong></td>
                    							<td class="text-right"><strong>Precio</strong></td>
                                            </tr>
                						</thead>
                						<tbody>
                                            <tr>
                    							<td><span id="factura_descripcion"></span></td>
                								<td class="text-center"><span id="factura_ref"></span></td>
                								<td class="text-center"><span id="factura_impuesto"></span> (<span id="factura_porcentaje"></span>%)</td>
                								<td class="text-center"><span id="factura_desc"></span>%</td>
                								<td class="text-right"><span id="factura_precio"></span></td>
                							</tr>
                							<tr>
                								<td class="thick-line"></td>
                								<td class="thick-line"></td>
                								<td class="thick-line"></td>
                								<td class="thick-line text-center"><strong>Subtotal</strong></td>
                								<td class="thick-line text-right"><span id="factura_subtotal"></span></td>
                							</tr>
                							<tr>
                								<td class="no-line"></td>
                								<td class="no-line"></td>
                								<td class="no-line"></td>
                								<td class="no-line text-center"><strong>Total</strong></td>
                								<td class="no-line text-right"><span id="factura_total"></span></td>
                							</tr>
                						</tbody>
                					</table>
                				</div><br>
                                <div align="center">
                                    <form action="https://checkout.wompi.co/p/" method="GET">
                                        <!-- OBLIGATORIOS -->
                                        <input type="hidden" name="public-key" value="pub_prod_atqXPyTdg7vBqQIzkbrba9JJShVgfbyM" />
                                        <input type="hidden" name="currency" value="COP" />
                                        <input type="hidden" name="amount-in-cents" id="amount-in-cents" value="MONTO_EN_CENTAVOS" />
                                        <input type="hidden" name="reference" id="reference" value="REFERENCIA_DE_PAGO" />
                                        <!-- OPCIONALES -->
                                        <input type="hidden" name="redirect-url" value="https://intercarnet.com/app/wompi.php" />
                                        <button class="d-none btn btn-success" type="submit">Pagar con Wompi</button>
                                    </form>
                                </div>
                			</div>
                		</div>
                	</div>
                </div>
            </div>
            <div class="d-none" id="invoice_error">
                <div class="row">
                    <div class="col-12">
                    	<div class="invoice-title text-center">
                			<h3>Ud. no posee ninguna factura pendiente por pagar</h3>
                		</div>
                	</div>
                </div>
            </div>
        </div> 

      <!--End Dashboard Content-->
    
  <!--start overlay-->
      <div class="overlay toggle-menu"></div>
    <!--end overlay-->
    
    </div>
    <!-- End container-fluid-->
    
    </div><!--End content-wrapper-->
   <!--Start Back To Top Button-->
    <a href="javaScript:void();" class="back-to-top"><i class="fa fa-angle-double-up"></i> </a>
    <!--End Back To Top Button-->
  <!--Start footer-->
  <!--End footer-->
  
  <!--start color switcher-->
<?php include "partials/color.php"; ?>
  <!--end color switcher-->
   
  </div><!--End wrapper-->

  <!-- Bootstrap core JavaScript-->
  <script src="assets/js/jquery.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            var id_cliente = <?php echo $cliente; ?>;
            $.ajax({
                type : 'GET',
                url  : 'bk_factura.php',
                data : {id_cliente:id_cliente},
                dataType: 'JSON',
                success : function(data){
                    if(data.success == 'true'){
                        $('#invoice_view').removeClass("d-none");
                        document.getElementById('cliente_nombre').innerHTML = data.cliente.nombre;
                        document.getElementById('cliente_nit').innerHTML = data.cliente.nit;
                        
                        document.getElementById('factura_codigo').innerHTML = data.factura.codigo;
                        document.getElementById('factura_fecha').innerHTML = data.factura.fecha;
                        document.getElementById('factura_vencimiento').innerHTML = data.factura.vencimiento;
                        document.getElementById('factura_ref').innerHTML = data.factura.ref;
                        document.getElementById('factura_descripcion').innerHTML = data.factura.descripcion;
                        document.getElementById('factura_precio').innerHTML = parseFloat(data.factura.precio).toFixed(2);
                        document.getElementById('amount-in-cents').innerHTML = parseFloat(data.factura.precio).toFixed(2);
                        $('#amount-in-cents').val(parseFloat(data.factura.precio).toFixed(2).replace('.', ''));
                        $('#reference').val(data.factura.codigo);
                        
                        if(data.factura.desc == null){
                            document.getElementById('factura_desc').innerHTML = '0.00';
                        }else{
                            document.getElementById('factura_desc').innerHTML = data.factura.desc;
                        }
                        
                        document.getElementById('factura_impuesto').innerHTML = data.factura.nombre;
                        document.getElementById('factura_porcentaje').innerHTML = data.factura.porcentaje;
                        document.getElementById('factura_subtotal').innerHTML = parseFloat(data.factura.precio).toFixed(2);
                        document.getElementById('factura_total').innerHTML = parseFloat(data.factura.precio).toFixed(2);
                    }else{
                        $('#invoice_error').removeClass("d-none");
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
  <script src="assets/js/jquery.loading-indicator.js"></script>
  <!-- Custom scripts -->
  <script src="assets/js/app-script.js"></script>
  <!-- Chart js -->
  
  <script src="assets/plugins/Chart.js/Chart.min.js"></script>
 
  <!-- Index js -->
  <?php include "partials/whatsapp.php"; ?>
  <?php include "partials/bot.php"; ?>

</body>
</html>
