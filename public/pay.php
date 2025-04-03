<?php
    include "include/conexion.php";
?>
<!DOCTYPE html>
<html lang="es-CO">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta property="og:site_name" content="<?=utf8_encode($empresa['nombre']);?>" /> <!-- website name -->
	<meta property="og:site" content="<?=$empresa['web'];?>" /> <!-- website link -->
	<meta property="og:title" content="<?=utf8_encode($empresa['nombre']);?>"/> <!-- title shown in the actual shared post -->
	<meta property="og:description" content="<?=utf8_encode($empresa['nombre']);?>" /> <!-- description shown in the actual shared post -->
	<meta property="og:image" content="<?=$empresa['web'];?>/assets/images/logo.png" /> <!-- image link, make sure it's jpg -->
	<meta property="og:url" content="<?=$empresa['web'];?>" /> <!-- where do you want your post to link to -->
	<meta name="twitter:card" content="summary_large_image"> <!-- to have large image post format in Twitter -->

    <title><?=utf8_encode($empresa['nombre']);?> | Somos Número Uno en Conexión</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="./css/bootstrap.min.css" rel="stylesheet">
    <link href="./css/fontawesome-all.min.css" rel="stylesheet">
    <link href="./software/vendors/sweetalert2/sweetalert2.min.css" rel="stylesheet"/>
    <link href="./css/aos.min.css" rel="stylesheet">
    <link href="./css/swiper.css" rel="stylesheet">
    <link href="./css/style.css" rel="stylesheet">

    <link rel="icon" href="./assets/images/favicon.png">
    <style>
        .contrato-card {
        background: #f9f9f9;
        border: 2px solid #ddd;
        border-radius: 10px;
        padding: 15px;
        text-align: center;
        cursor: pointer;
        transition: 0.3s;
        width: 180px;
        margin: 10px;
        display: inline-block;
        box-shadow: 2px 2px 5px rgba(0,0,0,0.1);
    }

    .contrato-card:hover {
        border-color: #007bff;
        transform: scale(1.05);
    }

    .contrato-card.selected {
        background: #007bff;
        color: white;
        border-color: #0056b3;
    }
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
          #navbarsExampleDefault > ul > li:nth-child(5) > a{
              cursor: pointer;
              background: var(--gradient);
              -webkit-background-clip: text;
              -webkit-text-fill-color: transparent;
          }
    </style>
</head>
<body>
    <div class="loader"></div>

    <nav id="navbar" class="navbar navbar-expand-lg fixed-top navbar-dark" aria-label="Main navigation">
        <div class="container">
            <a class="navbar-brand logo-image" href="index.php"><img src="./assets/images/logo.png" alt="alternative"></a>
            <button class="navbar-toggler p-0 border-0" type="button" id="navbarSideCollapse" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="navbar-collapse offcanvas-collapse" id="navbarsExampleDefault" >
                <ul class="navbar-nav ms-auto navbar-nav-scroll">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="index.php#header">Inicio</a>
                    </li>
                    <!--<li class="nav-item">-->
                    <!--    <a class="nav-link" href="index.php#about">Acerca de Nosotros</a>-->
                    <!--</li>-->
                    <!--<li class="nav-item">-->
                    <!--    <a class="nav-link" href="index.php#plans">Planes Hogar Fibra</a>-->
                    <!--</li>-->
                    <!--<li class="nav-item">-->
                    <!--    <a class="nav-link" href="index.php#test">Test de Velocidad</a>-->
                    <!--</li>-->
                    <li class="nav-item">
                        <a class="nav-link" href="#pago">Pagos en Linea</a>
                    </li>
                    <!--<li class="nav-item">-->
                    <!--    <a class="nav-link" href="#contact">Contacto</a>-->
                    <!--</li>-->
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
            </div>
        </div>
    </nav>
    <header class="ex-header">
        <div class="container">
            <div class="row">
                <div class="col-xl-10 offset-xl-1 text-center">
                    <h1>Pagos en Línea</h1>
                </div>
            </div>
        </div>
    </header>

    <div class="ex-basic-1 pt-5 pb-5">
        <div class="container">
            <div class="row" id="form-factura0">
                <div class="col-lg-6 offset-lg-3 text-justify">
                    <div class="mb-5 mb-lg-0" data-aos="fade-up" data-aos-delay="300">
                        <form>
                            <div class="col-12 pb-2 d-none">
                                <input class="form-control" type="text" name="nombre" id="nombre" placeholder="Nombre Completo" title="Nombre Completo" required="" autocomplete="off">
                            </div>
                            <div class="col-12 pb-2">
                                <input class="form-control" type="text" name="identificacion" id="identificacion" placeholder="Identificación" title="Identificación" required="" autocomplete="off">
                            </div>
                        </form>
                        <div class="col-12 py-2">
                            <center><button class="btn btn-main" style="color: var(--secondary);" onclick="contratos();">Consultar</button></center>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row" style="display: none;" id="mensaje">
                <div class="col-lg-8 offset-lg-2">

                    <div id="contratosContainer">

                    </div>

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
                                    <th>Precio</th>
                                    <td><span id="resul_price"></span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <center>
                        <hr>
                        <form action="https://checkout.wompi.co/p/" method="GET" id="form-wompi" class="d-none">
                            <input type="hidden" name="public-key" id="public_key_wompi" />
                            <input type="hidden" name="currency" value="COP" />
                            <input type="hidden" name="amount-in-cents" id="amount-in-cents" />
                            <input type="hidden" name="reference" id="reference"/>
                            <input type="hidden" name="redirect-url" id="redirect_url_wompi" />
                            <button class="btn btn-success" type="submit">Pagar con Wompi</button>
                        </form>
                        <button class="btn btn-main d-none" style="color: var(--secondary);" onclick="confirmar('form-wompi', 'WOMPI');" id="btn_wompi">Pagar con Wompi</button>

                        <form method="post" action="https://checkout.payulatam.com/ppp-web-gateway-payu/" id="form-payu" class="d-none">
                            <input id="merchantId"      name="merchantId"      type="hidden"  value="">
                            <input id="accountId"       name="accountId"       type="hidden"  value="">
                            <input id="description"     name="description"     type="hidden"  value="">
                            <input id="referenceCode"   name="referenceCode"   type="hidden"  value="">
                            <input id="amount"          name="amount"          type="hidden"  value="">
                            <input id="tax"             name="tax"             type="hidden"  value="0">
                            <input id="taxReturnBase"   name="taxReturnBase"   type="hidden"  value="0">
                            <input id="currency"        name="currency"        type="hidden"  value="COP">
                            <input id="signature"       name="signature"       type="hidden"  value="">
                            <input id="test"            name="test"            type="hidden"  value="1">
                            <input id="buyerFullName"   name="buyerFullName"   type="hidden"  value="">
                            <input id="telephone"       name="telephone"       type="hidden"  value="">
                            <input id="buyerEmail"      name="buyerEmail"      type="hidden"  value="">
                            <input id="responseUrl"     name="responseUrl"     type="hidden"  value="">
                            <input id="confirmationUrl" name="confirmationUrl" type="hidden"  value="">
                            <input name="Submit"          type="submit"  value="Enviar">
                        </form>
                        <button class="btn btn-main d-none" style="color: var(--secondary);" onclick="confirmar('form-payu', 'PayU');" id="btn_payu">Pagar con PayU</button>

                        <form id="form-epayco" class="d-none">
                            <script
                                src="https://checkout.epayco.co/checkout.js"
                                class="epayco-button"
                                data-epayco-currency="cop"
                                data-epayco-country="co"
                                data-epayco-test="true"
                                data-epayco-external="true"
                                data-epayco-response="https://ejemplo.com/respuesta.html"
                                data-epayco-confirmation="https://ejemplo.com/confirmacion"
                                data-epayco-methodconfirmation="post"
                                id="script_epayco">
                            </script>
                        </form>
                        <button class="btn btn-main d-none" style="color: var(--secondary);" onclick="confirmar('form-epayco', 'ePayco');" id="btn_epayco">Pagar con ePayco</button>

                        <button class="btn btn-main d-none" style="color: var(--secondary);" onclick="confirmar('form-combopay', 'ComboPay');" id="btn_combopay">Pagar con ComboPay</button>
                        <a class="d-none" id="a_combopay"></a>
                    </center>
                </div>
            </div>
        </div>
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
    <script src="js/jquery.md5.js"></script>

    <script>
        function cargando(abierta){
            if (abierta) {
                $(".loader").show();
            }else{
                $(".loader").hide();
            }
        }

        function number_format (number, decimals, dec_point, thousands_sep) {
            number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
            var n = !isFinite(+number) ? 0 : +number,
            prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
            sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
            dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
            s = '',
            toFixedFix = function (n, prec) {
                var k = Math.pow(10, prec);
                return '' + Math.round(n * k) / k;
            };
            s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
            if (s[0].length > 3) {
                s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
            }
            if ((s[1] || '').length < prec) {
                s[1] = s[1] || '';
                s[1] += new Array(prec - s[1].length + 1).join('0');
            }
            return s.join(dec);
        }

        function confirmar(form, mensaje, submensaje='¿Desea continuar?'){
            Swal.fire({
                type: 'question',
                title: 'Será redireccionado a la pasarela de pago '+mensaje,
                text: submensaje,
                confirmButtonText: 'Aceptar',
                cancelButtonText: 'Cancelar',
                showCancelButton: true,
                confirmButtonColor: '#00ce68',
                cancelButtonColor: '#d33',
            }).then((result) => {
                if (result.value) {
                    cargando(true);
                    if(form == 'form-epayco'){
                        $(".epayco-button-render").click();
                    }else if(form == 'form-combopay'){
                        $("#a_combopay")[0].click();
                    }else{
                        document.getElementById(form).submit();
                    }
                }
            })
        }

        function contratos() {
        if ($("#identificacion").val() == '') {
            Swal.fire({
                title: 'Disculpe, llene los campos con la información solicitada para completar la consulta',
                icon: 'error',
                timer: 5000
            });
            return false;
        }

        $.ajax({
            url: '/software/factura/' + $("#identificacion").val(),
            beforeSend: function () {
                cargando(true);
            },
            success: function (data) {
                cargando(false);
                $('#mensaje').removeAttr('style');
                let contratosContainer = $("#contratosContainer");
                contratosContainer.empty(); // Limpiar antes de agregar nuevos contratos

                if (data.contrato.length === 0) {
                    contratosContainer.append('<p class="text-gray-500 text-center">No hay contratos disponibles.</p>');
                    return;
                }

                data.contrato.forEach(contrato => {
                    console.log(contrato)
                    let contratoCard = `
                        <div class="contrato-card" onclick="consultar('${contrato.facturaId}', '${contrato.nit}')">
                            <p class="numero">Factura #${contrato.factura}</p>
                            <p class="valor">$${contrato.price.toLocaleString()}</p>
                        </div>
                    `;
                    contratosContainer.append(contratoCard);
                });
            },
            error: function () {
                cargando(false);
                Swal.fire({
                    title: 'Error al obtener los contratos',
                    icon: 'error',
                    timer: 5000
                });
            }
        });
    }


        function consultar(facturaId, identificacion){

            $.ajax({
                url: '/software/factura/'+identificacion+'/'+facturaId,
                beforeSend: function(){
                    cargando(true);
                },
                success:  function(data){
                    if(data.contrato){
                        $('#form-factura0').hide();
                        $('#mensaje').removeAttr('style');

                        var fullname = data.contrato.nombre;
                        if(data.contrato.apellido1){
                            fullname = fullname+' '+data.contrato.apellido1;
                        }
                        if(data.contrato.apellido1){
                            if(data.contrato.apellido2 != null){
                                fullname = fullname+' '+data.contrato.apellido2;
                            }
                        }

                        $("#resul_cliente").text(fullname);
                        $("#resul_identificacion").text(data.contrato.nit);
                        $("#resul_factura").text(data.contrato.factura);
                        $("#resul_emision").text(data.contrato.emision);
                        $("#resul_vencimiento").text(data.contrato.vencimiento);
                        $("#resul_price").text(number_format(data.contrato.price, '2', ',', '.'));

                        $(".contrato-card").removeClass("selected");
                        $(`.contrato-card:contains('${facturaId}')`).addClass("selected");
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

                    if(data.contrato){
                        var str = window.location.hostname;
                        $.each(data.pasarelas, function(index, value){
                            if(value.nombre == 'WOMPI'){
                                $("#reference").val('<?=$nom_empresa;?>-'+data.contrato.factura);
                                $("#amount-in-cents").val(parseFloat(data.contrato.price)+'00');
                                $("#public_key_wompi").val(value.api_key);
                                $("#redirect_url_wompi").val('https://'+str+'/wompi.php');
                                $("#btn_wompi").removeClass('d-none');
                            }else if(value.nombre == 'PayU'){
                                var amount = (parseFloat(data.contrato.price)*1);
                                $("#merchantId").val(value.merchantId);
                                $("#accountId").val(value.accountId);
                                $("#description").val('Factura '+data.contrato.factura);
                                $("#referenceCode").val('<?=$nom_empresa;?>-'+data.contrato.factura);
                                $("#amount").val(amount);
                                $("#tax").val(number_format(data.contrato.price, '2', ',', '.'));
                                $("#buyerFullName").val(data.cliente.nombre);
                                $("#buyerEmail").val(data.cliente.email);
                                $("#telephone").val(data.cliente.celular);
                                $("#responseUrl").val('https://'+str+'/payu.php');
                                $("#confirmationUrl").val('https://'+str+'/software/api/pagos/payu');
                                $("#btn_payu").removeClass('d-none');

                                $("#signature").val($.md5(value.api_key+"~"+value.merchantId+"~<?=$nom_empresa;?>-"+data.contrato.factura+"~"+amount*1+"~COP"));
                            }else if(value.nombre == 'ePayco'){
                                var amount = (parseFloat(data.contrato.price)*1);
                                $("#script_epayco").attr('data-epayco-key', value.api_key)
                                .attr('data-epayco-amount', amount)
                                .attr('data-epayco-name', '<?=$nom_empresa;?>-'+data.contrato.factura)
                                .attr('data-epayco-description', '<?=$nom_empresa;?>-'+data.contrato.factura)
                                .attr('data-epayco-email-billing', data.cliente.email)
                                .attr('data-epayco-name-billing', data.cliente.nombre)
                                .attr('data-epayco-address-billing', data.cliente.direccion)
                                .attr('data-epayco-mobilephone-billing', data.cliente.celular)
                                .attr('data-epayco-number-doc-billing', data.cliente.nit)
                                .attr('data-epayco-response', 'https://'+str+'/epayco.php')
                                .attr('data-epayco-confirmation', 'https://'+str+'/software/api/pagos/epayco');
                                $("#btn_epayco").removeClass('d-none');
                            }else if(value.nombre == 'ComboPay'){
                                var token = {
                                    "url": "https://api.combopay.co/api/oauth/token?grant_type=password&client_secret="+value.merchantId+"&username="+value.user+"&password="+value.pass+"&client_id="+value.accountId,
                                    "method": "POST",
                                    "timeout": 0,
                                };
                                $.ajax(token).done(function (response) {
                                    if(response.access_token){
                                        var amount = (parseFloat(data.contrato.price)*1);

                                        if(data.contrato.tip_iden==3){ var tip_iden = 'CC'; }else if(data.contrato.tip_iden==6){ var tip_iden = 'NIT'; }
                                        var link = {
                                            "url": "https://api.combopay.co/api/invoice-company-customer?value="+amount+"&description="+data.contrato.factura+"&invoice=<?=$nom_empresa;?>-"+data.contrato.factura+"&url_data_return=https://"+str+"/software/api/pagos/combopay&url_client_redirect=https://"+str+"/software/api/pagos/combopay&name="+fullname+"&document_type="+tip_iden+"&customer_phone_number="+data.contrato.celular+"&document="+data.contrato.nit+"&customer_address="+data.contrato.direccion,
                                            "method": "POST",
                                            "timeout": 0,
                                            "headers": {
                                                "Authorization": "Bearer "+response.access_token+""
                                            },
                                        };

                                        $.ajax(link).done(function (response) {
                                            if(response.payment_link){
                                                $("#btn_combopay").removeClass('d-none');
                                                $("#a_combopay").attr('href', response.payment_link);
                                            }
                                        });
                                    }
                                });
                            }
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
    </script>
</body>
</html>