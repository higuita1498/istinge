<!DOCTYPE html>
<html lang="es-CO">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta property="og:site_name" content="Rapilink S.A.S" /> <!-- website name -->
	<meta property="og:site" content="https://rapilink.xyz" /> <!-- website link -->
	<meta property="og:title" content="Rapilink S.A.S"/> <!-- title shown in the actual shared post -->
	<meta property="og:description" content="Rapilink S.A.S" /> <!-- description shown in the actual shared post -->
	<meta property="og:image" content="https://rapilink.xyz/assets/images/logo.png" /> <!-- image link, make sure it's jpg -->
	<meta property="og:url" content="https://rapilink.xyz" /> <!-- where do you want your post to link to -->
	<meta name="twitter:card" content="summary_large_image"> <!-- to have large image post format in Twitter -->

    <title>Rapilink S.A.S | Somos Número Uno en Conexión</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="./css/bootstrap.min.css" rel="stylesheet">
    <link href="./css/fontawesome-all.min.css" rel="stylesheet">
    <link href="./software/vendors/sweetalert2/sweetalert2.min.css" rel="stylesheet"/>
    <link href="./css/aos.min.css" rel="stylesheet">
    <link href="./css/swiper.css" rel="stylesheet">
    <link href="./css/style.css" rel="stylesheet">

    <link rel="icon" href="./assets/images/favicon.png">
    <style>
        .loader {
	          position: fixed;
	          left: 0px;
	          top: 0px;
	          width: 100%;
	          height: 100%;
	          z-index: 9999;
	          background: url('software/images/loader.gif') 50% 50% no-repeat rgb(249,249,249);
	          opacity: .8;
	          display: none;
	      }
	      #navbarsExampleDefault > ul > li:nth-child(6) > a{
	          cursor: pointer;
	          background: var(--gradient);
	          -webkit-background-clip: text;
	          -webkit-text-fill-color: transparent;
	      }
    </style>
</head>
<body>
    <div class="loader"></div>
    <!-- Navigation -->
    <nav id="navbar" class="navbar navbar-expand-lg fixed-top navbar-dark" aria-label="Main navigation">
        <div class="container">

            <!-- Image Logo -->
            <a class="navbar-brand logo-image" href="index.php"><img src="./assets/images/logo.png" alt="alternative"></a>

            <!-- Text Logo - Use this if you don't have a graphic logo -->
            <!-- <a class="navbar-brand logo-text" href="index.php">Top Link</a> -->

            <button class="navbar-toggler p-0 border-0" type="button" id="navbarSideCollapse" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="navbar-collapse offcanvas-collapse" id="navbarsExampleDefault" >
                <ul class="navbar-nav ms-auto navbar-nav-scroll">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="#header">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">Acerca de Nosotros</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#plans">Planes Hogar Fibra</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="solicitud.php">Solicitar Servicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#test">Test de Velocidad</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#pago">Pagos en Linea</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pqrs.php">PQR</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contact">Contacto</a>
                    </li>
                </ul>
                <span class="nav-item social-icons d-none">
                    <span class="fa-stack">
                        <a href="#your-link">
                            <i class="fas fa-circle fa-stack-2x"></i>
                            <i class="fab fa-facebook-f fa-stack-1x"></i>
                        </a>
                    </span>
                    <span class="fa-stack">
                        <a href="#your-link">
                            <i class="fas fa-circle fa-stack-2x"></i>
                            <i class="fab fa-twitter fa-stack-1x"></i>
                        </a>
                    </span>
                </span>
            </div> <!-- end of navbar-collapse -->
        </div> <!-- end of container -->
    </nav> <!-- end of navbar -->
    <!-- end of navigation -->

    <!-- Header -->
    <header class="ex-header">
        <div class="container">
            <div class="row">
                <div class="col-xl-10 offset-xl-1 text-center">
                    <h1>Pagos en Línea</h1>
                </div> <!-- end of col -->
            </div> <!-- end of row -->
        </div> <!-- end of container -->
    </header> <!-- end of ex-header -->
    <!-- end of header -->


    <!-- Basic -->
    <div class="ex-basic-1 pt-5 pb-5">
        <div class="container">
            <div class="row" id="form-factura0">
					<div class="col-lg-6 offset-lg-3 text-justify">
						<div class="mb-5 mb-lg-0" data-aos="fade-up" data-aos-delay="300">
							<form>
						        <div class="col-12 pb-2">
						        	<input class="form-control" type="text" name="nombre" id="nombre" placeholder="Nombre Completo" title="Nombre Completo" required="" autocomplete="off">
					        	</div>

					        	<div class="col-12 pb-2">
						        	<input class="form-control" type="text" name="identificacion" id="identificacion" placeholder="Identificación" title="Identificación" required="" autocomplete="off">
					        	</div>
							</form>

							<div class="col-12 py-2">
								<center><button class="btn btn-main" style="color: var(--secondary);" onclick="consultar();">Consultar</button></center>
							</div>
						</div>
					</div>
				</div>

				<div class="row" style="display: none;" id="mensaje">
				    <div class="col-lg-8 offset-lg-2">
				        <div class="table-responsive">
				            <table class="table table-striped table-bordered table-sm info">
				                <tbody>
				                    <tr>
				                        <th width="25%">DATOS GENERALES</th>
				                        <th></th>
				                    </tr>
				                    <tr>
				                        <th>Cliente</th>
				                        <td><span id="resul_cliente"></span></td>
				                    </tr>
				                    <tr>
                                        <th>Identificación</th>
                                        <td><span id="resul_identificacion"></span></td>
                                    </tr>
                                    <tr>
                                        <th>N° Factura</th>
                                        <td><span id="resul_factura"></span></td>
                                    </tr>
                                    <tr>
                                        <th>Emisión</th>
                                        <td><span id="resul_emision"></span></td>
                                    </tr>
                                    <tr>
                                        <th>Vencimiento</th>
                                        <td><span id="resul_vencimiento"></span></td>
                                    </tr>
                                    <tr>
                                        <th>Plan</th>
                                        <td><span id="resul_plan"></span></td>
                                    </tr>
                                    <tr>
                                        <th>Precio</th>
                                        <td><span id="resul_price"></span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-12 mb-5 d-none">
                        <form action="https://checkout.wompi.co/p/" method="GET" id="form-wompi">
                            <input type="hidden" name="public-key" value="pub_prod_7BEvWOpu64Zl6OKx3ZP9p1XGBn5eq7bG" />
                            <input type="hidden" name="currency" value="COP" />
                            <input type="hidden" name="amount-in-cents" id="amount-in-cents" />
                            <input type="hidden" name="reference" id="reference" />
                            <input type="hidden" name="redirect-url" value="https://toplink.com.co/wompi.php" />
                            <button class="btn btn-main" style="color: var(--secondary);" type="submit">Pagar con Wompi</button>
                        </form>
                    </div>

                    <div class="col-12 mb-5 d-none">
                        <form method="post" action="https://checkout.payulatam.com/ppp-web-gateway-payu/" id="form-payu">
                            <input name="ApiKey" id="ApiKey" type="hidden" value="538AwCyXSvTiqPE5F1xsj91F6y">
                            <input name="merchantId"    type="hidden"  value="899988"   >
                            <input name="accountId"     type="hidden"  value="906617" >

                            <input name="buyerEmail" id="buyerEmail" type="hidden">
                            <input name="buyerFullName" id="buyerFullName" type="hidden">

                            <input name="referenceCode" id="referenceCode" type="hidden">
                            <input name="currency" id="currency" type="hidden" value="COP">
                            <input name="amount" id="amount" type="hidden">
                            <input name="description" id="description" type="hidden">
                            <input name="test" type="hidden" value="0" >
                            <input name="tax" type="hidden" value="0" >

                            <input name="signature" id="signature" type="hidden">

                            <input name="responseUrl" type="hidden" value="https://toplink.com.co/pay.php">
                            <input name="confirmationUrl" type="hidden" value="https://toplink.com.co/pay.php">
                            <input name="Submit" type="submit"  value="Enviar">
                        </form>
                    </div>

                    <!-- INTEGRANDO LA PASARELA DE PAGO TOPPAY -->

                    <div class="col-12 mb-5 d-none">
                        <form method="post" action="https://production.toppaylatam.com/api/transactions" id="form-toppay">
                            <input name="ApiKey" id="ApiKey" type="hidden" value="5eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJtZXJ">
                            <!-- <input name="merchantId"    type="hidden"  value="899988"   >
                            <input name="accountId"     type="hidden"  value="906617" > -->

                            <input name="buyerEmail" id="buyerEmail" type="hidden">
                            <input name="buyerFullName" id="buyerFullName" type="hidden">

                            <input name="referenceCode" id="referenceCode" type="hidden">
                            <input name="expiration" id="expirationy" type="hidden" value="2023-07-26">
                            <input name="currency" id="currency" type="hidden" value="COP">
                            <input name="amount" id="amount" type="hidden">
                            <input name="description" id="description" type="hidden">
                            <input name="test" type="hidden" value="0" >
                            <input name="tax" type="hidden" value="0" >

                            <input name="signature" id="signature" type="hidden">

                            <input name="responseUrl" type="hidden" value="https://toplink.com.co/pay.php">
                            <input name="confirmationUrl" type="hidden" value="https://toplink.com.co/pay.php">
                            <input name="Submit" type="submit"  value="Enviar">
                        </form>
                    </div>
                    <!-- FIN DE LA INTEGRACION  -->

                    <div class="col-12" style="text-align:center;">
                        <div class="contact-form">
                            <button class="btn btn-main" style="color: var(--secondary);" type="submit" onclick="confirmar('form-wompi');">Pagar con Wompi</button>
                            <button class="btn btn-main" style="color: var(--secondary);" type="submit" onclick="confirmarAlert('form-payu');">Pagar con PayU</button>
                        </div>
                    </div>
                </div>
        </div> <!-- end of container -->
    </div>

    <?php include('include/footer.php'); ?>

    <!-- Scripts -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script src="./js/bootstrap.min.js"></script><!-- Bootstrap framework -->
    <script src="./js/purecounter.min.js"></script> <!-- Purecounter counter for statistics numbers -->
    <script src="./js/swiper.min.js"></script><!-- Swiper for image and text sliders -->
    <script src="./js/aos.js"></script><!-- AOS on Animation Scroll -->
    <script src="./js/script.js"></script>  <!-- Custom scripts -->
    <script src="./software/vendors/sweetalert2/sweetalert2.min.js"></script>

    <script>

        //function confirmar(form, mensaje="El uso de la plataforma WOMPI tiene un valor de 2800 COP adicional al monto de la facturación", submensaje='¿Desea continuar?', confirmar='Si'){
        function confirmar(form, mensaje="Será redireccionado a la pasarela de pago WOMPI", submensaje='¿Desea continuar?', confirmar='Si'){
            if($("#buyerFullName").val() != ''){
                swal({
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
                        document.getElementById(form).submit();
                        cargando(true);
                    }
                });
            }else{
                swal({
                    title: 'Debe llenar la información solicitada',
                    text: submensaje,
                    type: 'warning',
                    showCancelButton: true,
                    showConfirmButton: false,
                    cancelButtonColor: '#00ce68',
                    cancelButtonText: 'Aceptar',
                })
            }
        }

        function consultar(){
            if ($("#nombre").val() == '' || $("#identificacion").val() == '') {
                Swal.fire({
                    title: 'Disculpe, llene los campos con la información solicitada para completar la consulta',
                    type: 'error',
                    showCancelButton: false,
                    showConfirmButton: false,
                    cancelButtonColor: '#d33',
                    cancelButtonText: 'Cancelar',
                    timer: 5000
                });
                return false;
            }

            $.ajax({
                url: '/software/factura/'+$("#identificacion").val(),
                beforeSend: function(){
                    cargando(true);
                },
                success: function(data){
                    data=JSON.parse(data);
                    if (data) {
                        //var monto_pago = parseFloat(data.price)+parseFloat(2800);
                        var monto_pago = parseFloat(data.price)+parseFloat(0);
                        $('#form-factura0').hide();
                        $('#mensaje').removeAttr('style');
                        $("#resul_codigo").text(data.public_id);
                        $("#amount-in-cents").val(monto_pago+'00');
                        $("#reference").val(data.factura);
                        $("#resul_cliente").text(data.nombre);
                        $("#resul_identificacion").text(data.nit);
                        $("#resul_codigo").text(data.public_id);
                        $("#resul_plan").text(data.plan);
                        $("#resul_factura").text(data.factura);
                        $("#resul_emision").text(data.emision);
                        $("#resul_vencimiento").text(data.vencimiento);
                        $("#resul_price").text(parseFloat(data.price)+parseFloat(0)+' COP');
                        $("#description").val(data.plan);

                        $("#buyerEmail").val(data.email);
                        $("#buyerFullName").val(data.nombre);
                        $("#referenceCode").val(data.factura);
                        $("#amount").val(parseFloat(data.price)+parseFloat(0));
                        $("#description").val('Pago Factura Nro. '+data.factura+' Rapilink S.A.S');


                    }else{
                        Swal.fire({
                            title: 'No existe ninguna factura pendiente, relacionada con el cliente indicado',
                            type: 'error',
                            showCancelButton: false,
                            showConfirmButton: false,
                            cancelButtonColor: '#d33',
                            cancelButtonText: 'Cancelar',
                            timer: 10000
                        });
                    }
                    cargando(false);
                },
                error: function(data){
                    Swal.fire({
                        title: 'Disculpe, estamos presentando problemas al tratar de enviar el formulario, intentelo más tarde.',
                        type: 'error',
                        showCancelButton: false,
                        showConfirmButton: false,
                        cancelButtonColor: '#d33',
                        cancelButtonText: 'Cancelar',
                        timer: 5000
                    });
                    cargando(false);
                }
            });
        }

        function cargando(abierta){
            if (abierta) {
                $(".loader").show();
            }else{
                $(".loader").hide();
            }
        }

        function signature(){
            var MD5 = function(d){result = M(V(Y(X(d),8*d.length)));return result.toLowerCase()};function M(d){for(var _,m="0123456789ABCDEF",f="",r=0;r<d.length;r++)_=d.charCodeAt(r),f+=m.charAt(_>>>4&15)+m.charAt(15&_);return f}function X(d){for(var _=Array(d.length>>2),m=0;m<_.length;m++)_[m]=0;for(m=0;m<8*d.length;m+=8)_[m>>5]|=(255&d.charCodeAt(m/8))<<m%32;return _}function V(d){for(var _="",m=0;m<32*d.length;m+=8)_+=String.fromCharCode(d[m>>5]>>>m%32&255);return _}function Y(d,_){d[_>>5]|=128<<_%32,d[14+(_+64>>>9<<4)]=_;for(var m=1732584193,f=-271733879,r=-1732584194,i=271733878,n=0;n<d.length;n+=16){var h=m,t=f,g=r,e=i;f=md5_ii(f=md5_ii(f=md5_ii(f=md5_ii(f=md5_hh(f=md5_hh(f=md5_hh(f=md5_hh(f=md5_gg(f=md5_gg(f=md5_gg(f=md5_gg(f=md5_ff(f=md5_ff(f=md5_ff(f=md5_ff(f,r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+0],7,-680876936),f,r,d[n+1],12,-389564586),m,f,d[n+2],17,606105819),i,m,d[n+3],22,-1044525330),r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+4],7,-176418897),f,r,d[n+5],12,1200080426),m,f,d[n+6],17,-1473231341),i,m,d[n+7],22,-45705983),r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+8],7,1770035416),f,r,d[n+9],12,-1958414417),m,f,d[n+10],17,-42063),i,m,d[n+11],22,-1990404162),r=md5_ff(r,i=md5_ff(i,m=md5_ff(m,f,r,i,d[n+12],7,1804603682),f,r,d[n+13],12,-40341101),m,f,d[n+14],17,-1502002290),i,m,d[n+15],22,1236535329),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+1],5,-165796510),f,r,d[n+6],9,-1069501632),m,f,d[n+11],14,643717713),i,m,d[n+0],20,-373897302),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+5],5,-701558691),f,r,d[n+10],9,38016083),m,f,d[n+15],14,-660478335),i,m,d[n+4],20,-405537848),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+9],5,568446438),f,r,d[n+14],9,-1019803690),m,f,d[n+3],14,-187363961),i,m,d[n+8],20,1163531501),r=md5_gg(r,i=md5_gg(i,m=md5_gg(m,f,r,i,d[n+13],5,-1444681467),f,r,d[n+2],9,-51403784),m,f,d[n+7],14,1735328473),i,m,d[n+12],20,-1926607734),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+5],4,-378558),f,r,d[n+8],11,-2022574463),m,f,d[n+11],16,1839030562),i,m,d[n+14],23,-35309556),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+1],4,-1530992060),f,r,d[n+4],11,1272893353),m,f,d[n+7],16,-155497632),i,m,d[n+10],23,-1094730640),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+13],4,681279174),f,r,d[n+0],11,-358537222),m,f,d[n+3],16,-722521979),i,m,d[n+6],23,76029189),r=md5_hh(r,i=md5_hh(i,m=md5_hh(m,f,r,i,d[n+9],4,-640364487),f,r,d[n+12],11,-421815835),m,f,d[n+15],16,530742520),i,m,d[n+2],23,-995338651),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+0],6,-198630844),f,r,d[n+7],10,1126891415),m,f,d[n+14],15,-1416354905),i,m,d[n+5],21,-57434055),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+12],6,1700485571),f,r,d[n+3],10,-1894986606),m,f,d[n+10],15,-1051523),i,m,d[n+1],21,-2054922799),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+8],6,1873313359),f,r,d[n+15],10,-30611744),m,f,d[n+6],15,-1560198380),i,m,d[n+13],21,1309151649),r=md5_ii(r,i=md5_ii(i,m=md5_ii(m,f,r,i,d[n+4],6,-145523070),f,r,d[n+11],10,-1120210379),m,f,d[n+2],15,718787259),i,m,d[n+9],21,-343485551),m=safe_add(m,h),f=safe_add(f,t),r=safe_add(r,g),i=safe_add(i,e)}return Array(m,f,r,i)}function md5_cmn(d,_,m,f,r,i){return safe_add(bit_rol(safe_add(safe_add(_,d),safe_add(f,i)),r),m)}function md5_ff(d,_,m,f,r,i,n){return md5_cmn(_&m|~_&f,d,_,r,i,n)}function md5_gg(d,_,m,f,r,i,n){return md5_cmn(_&f|m&~f,d,_,r,i,n)}function md5_hh(d,_,m,f,r,i,n){return md5_cmn(_^m^f,d,_,r,i,n)}function md5_ii(d,_,m,f,r,i,n){return md5_cmn(m^(_|~f),d,_,r,i,n)}function safe_add(d,_){var m=(65535&d)+(65535&_);return(d>>16)+(_>>16)+(m>>16)<<16|65535&m}function bit_rol(d,_){return d<<_|d>>>32-_};
            var firma_cadena = '538AwCyXSvTiqPE5F1xsj91F6y~899988~'+$("#referenceCode").val()+'~'+$("#amount").val()+'~COP';
            $("#signature").val(MD5(firma_cadena));
        }

        function confirmarAlert(form, mensaje="Será redireccionado a la pasarela de pago PayU", submensaje='¿Desea continuar?', confirmar='Si'){
        /*if($("#buyerFullName").val() != '' && $("#buyerEmail").val() != ''){*/
        if($("#amount").val() != '' && $("#referenceCode").val() != '' ){
          signature();

          swal({
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
              document.getElementById(form).submit();
              cargando(true);
            }
          });
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
</body>
</html>
