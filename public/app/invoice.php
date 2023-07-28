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

    $active = 3;
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

  <link href="../software/vendors/sweetalert2/sweetalert2.min.css" rel="stylesheet"/>
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
        <div class="card mt-3" style="padding: 15px;"class="d-none" id="invoice_view">
            <div>
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
                								<td class="text-center"><span id="factura_desc"></span>0%</td>
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
                                        <input type="hidden" name="public-key" value="" />
                                        <input type="hidden" name="currency" value="COP" />
                                        <input type="hidden" name="amount-in-cents" id="amount-in-cents" value="MONTO_EN_CENTAVOS" />
                                        <input type="hidden" name="reference" id="reference" value="REFERENCIA_DE_PAGO" />
                                        <!-- OPCIONALES -->
                                        <input type="hidden" name="redirect-url" value="https://istingenieria.online/app/wompi.php" />
                                        <button class="btn btn-success" type="submit">Pagar con Wompi 1</button>
                                    </form>
                                </div>
                			</div>
                		</div>
                	</div>
                </div>
            </div>
            <div class="card mt-3" style="padding: 15px;"class="d-none" id="invoice_error">
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
  <!--end color switcher-->

  </div><!--End wrapper-->

  <!-- Bootstrap core JavaScript-->
  <script src="assets/js/jquery.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            var id_cliente = <?= $cliente; ?>;
            $.ajax({
                type : 'GET',
                url  : 'bk_factura.php',
                data : {id_cliente:id_cliente},
                dataType: 'JSON',
                success : function(data){
                    if(data.success == 'true'){
                        $('#invoice_view').removeClass("d-none");
                        $('#invoice_error').html('');
                        document.getElementById('cliente_nombre').innerHTML = data.cliente.nombre;
                        document.getElementById('cliente_nit').innerHTML = data.cliente.nit;

                        document.getElementById('factura_codigo').innerHTML = data.factura.codigo;
                        document.getElementById('factura_fecha').innerHTML = data.factura.fecha;
                        document.getElementById('factura_vencimiento').innerHTML = data.factura.vencimiento;
                        document.getElementById('factura_ref').innerHTML = data.factura.ref;
                        document.getElementById('factura_descripcion').innerHTML = data.factura.ref;
                        document.getElementById('factura_precio').innerHTML = parseFloat(data.factura.precio).toFixed(2);
                        document.getElementById('amount-in-cents').innerHTML = parseFloat(data.factura.precio).toFixed(2);
                        var monto_pago = parseFloat(data.factura.precio)+parseFloat(0);
                        $('#amount-in-cents').val(monto_pago+'00');
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

                        $("#buyerEmail").val(data.cliente.email);
                        $("#buyerFullName").val(data.cliente.nombre);
                        $("#referenceCode").val(data.factura.codigo);
                        $("#amount").val(parseFloat(data.factura.precio)+parseFloat(0));
                        $("#description").val('Pago Factura Nro. '+data.factura.codigo+' Top Link S.A.S.');
                    }else{
                        $('#invoice_error').removeClass("d-none");
                        $('#invoice_view').addClass("d-none");
                    }
                }
            });
            return false;
        });

        function signature(){
            var MD5 = function(d){result = M(V(Y(X(d),8*d.length)));return result.toLowerCase()};function M(d){for(var _,m="0123456789ABCDEF",f="",r=0;r<d.length;r++)_=d.charCodeAt(r),f+=m.charAt(_>>>4&15)+m.charAt(15&_);return f}function X(d){for(var _=Array(d.length>>2),m=0;m<_.length;m++)_[m]=0;for(m=0;m<8*d.length;m+=8)_[m>>5]|=(255&d.charCodeAt(m/8))<<m%32;return _}function V(d){for(var _="",m=0;m<32*d.length;m+=8)_+=String.fromCharCode(d[m>>5]>>>m%32&255);return _}function Y(d,_){d[_>>5]|=128<<_%32,d[14+(_+64>>>9<<4)]=_;for(var m=1732584193,f=-271733879,r=-1732584194,i=271733878,n=0;n<d.length;n+=16){var h=m,t=f,g=r,e=i;f=md5_ii(f=md5_ii(f=md5_ii(f=md5_ii(f=md5_hh(f=md5_hh(f=md5_hh(f=md5_hh(f=md5_gg(f=md5_gg(f=md5_gg(f=md5_gg(f=md5_ff(f=md5_ff(f=md5_ff(f=md5_ff(f,r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+0],7,-680876936),f,r,d[n+1],12,-389564586),m,f,d[n+2],17,606105819),i,m,d[n+3],22,-1044525330),r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+4],7,-176418897),f,r,d[n+5],12,1200080426),m,f,d[n+6],17,-1473231341),i,m,d[n+7],22,-45705983),r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+8],7,1770035416),f,r,d[n+9],12,-1958414417),m,f,d[n+10],17,-42063),i,m,d[n+11],22,-1990404162),r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+12],7,1804603682),f,r,d[n+13],12,-40341101),m,f,d[n+14],17,-1502002290),i,m,d[n+15],22,1236535329),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+1],5,-165796510),f,r,d[n+6],9,-1069501632),m,f,d[n+11],14,643717713),i,m,d[n+0],20,-373897302),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+5],5,-701558691),f,r,d[n+10],9,38016083),m,f,d[n+15],14,-660478335),i,m,d[n+4],20,-405537848),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+9],5,568446438),f,r,d[n+14],9,-1019803690),m,f,d[n+3],14,-187363961),i,m,d[n+8],20,1163531501),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+13],5,-1444681467),f,r,d[n+2],9,-51403784),m,f,d[n+7],14,1735328473),i,m,d[n+12],20,-1926607734),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+5],4,-378558),f,r,d[n+8],11,-2022574463),m,f,d[n+11],16,1839030562),i,m,d[n+14],23,-35309556),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+1],4,-1530992060),f,r,d[n+4],11,1272893353),m,f,d[n+7],16,-155497632),i,m,d[n+10],23,-1094730640),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+13],4,681279174),f,r,d[n+0],11,-358537222),m,f,d[n+3],16,-722521979),i,m,d[n+6],23,76029189),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+9],4,-640364487),f,r,d[n+12],11,-421815835),m,f,d[n+15],16,530742520),i,m,d[n+2],23,-995338651),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+0],6,-198630844),f,r,d[n+7],10,1126891415),m,f,d[n+14],15,-1416354905),i,m,d[n+5],21,-57434055),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+12],6,1700485571),f,r,d[n+3],10,-1894986606),m,f,d[n+10],15,-1051523),i,m,d[n+1],21,-2054922799),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+8],6,1873313359),f,r,d[n+15],10,-30611744),m,f,d[n+6],15,-1560198380),i,m,d[n+13],21,1309151649),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+4],6,-145523070),f,r,d[n+11],10,-1120210379),m,f,d[n+2],15,718787259),i,m,d[n+9],21,-343485551),m=safe_add(m,h),f=safe_add(f,t),r=safe_add(r,g),i=safe_add(i,e)}return Array(m,f,r,i)}function md5_cmn(d,_,m,f,r,i){return safe_add(bit_rol(safe_add(safe_add(_,d),safe_add(f,i)),r),m)}function md5_ff(d,_,m,f,r,i,n){return md5_cmn(_&m|~_&f,d,_,r,i,n)}function md5_gg(d,_,m,f,r,i,n){return md5_cmn(_&f|m&~f,d,_,r,i,n)}function md5_hh(d,_,m,f,r,i,n){return md5_cmn(_^m^f,d,_,r,i,n)}function md5_ii(d,_,m,f,r,i,n){return md5_cmn(m^(_|~f),d,_,r,i,n)}function safe_add(d,_){var m=(65535&d)+(65535&_);return(d>>16)+(_>>16)+(m>>16)<<16|65535&m}function bit_rol(d,_){return d<<_|d>>>32-_};
            var firma_cadena = '538AwCyXSvTiqPE5F1xsj91F6y~899988~'+$("#referenceCode").val()+'~'+$("#amount").val()+'~COP';
            $("#signature").val(MD5(firma_cadena));
        }

        function confirmarAlert(form, mensaje="Será redireccionado a la pasarela de pago PayU", submensaje='¿Desea continuar?', confirmar='Si'){
        /*if($("#buyerFullName").val() != '' && $("#buyerEmail").val() != ''){*/
        if($("#amount").val() != '' && $("#referenceCode").val() != '' ){
          signature();
          $("#btn-payu").click();
          cargando(true);

          /*swal({
            title: mensaje,
            text: submensaje,
            type: 'question',
            showCancelButton: true,
            confirmButtonColor: '#00ce68',
            cancelButtonColor: '#d33',
            confirmButtonText: confirmar,
            cancelButtonText: 'No',
          }).then((result) => {
            if (result.value) {
              $("#preloader").removeClass('d-none');
              $("#btn-payu").click();
              //document.getElementById(form).submit();
              cargando(true);
            }
          });*/
        }else{
          swal({
            title: 'Debe llenar la información solicitada',
            type: 'warning',
            showCancelButton: true,
            showConfirmButton: false,
            cancelButtonColor: '#00ce68',
            cancelButtonText: 'Aceptar',
          })
        }
      }
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
  <script src="../software/vendors/sweetalert2/sweetalert2.min.js"></script>


</body>
</html>
