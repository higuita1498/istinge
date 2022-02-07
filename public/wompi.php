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
                        <a class="nav-link active" aria-current="page" href="index.php#header">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#about">Acerca de Nosotros</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#plans">Planes Hogar Fibra</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#test">Test de Velocidad</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Pagos en Linea</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#contact">Contacto</a>
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
                <div class="col-xl-10 offset-xl-1">
                    <h1>RESPUESTA DE WOMPI</h1>
                </div> <!-- end of col -->
            </div> <!-- end of row -->
        </div> <!-- end of container -->
    </header> <!-- end of ex-header -->
    <!-- end of header -->


    <!-- Basic -->
    <div class="ex-basic-1 pt-5 pb-5">
        <div class="container">
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
        $(document).ready(function(){
            cargando(true);
            wompi = 'https://production.wompi.co/v1/transactions/<?=$_GET['id'];?>';
            
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
                        cargando(false);
                        theDiv2.appendChild(content2);
                        swal({
                            title: 'Pago Anulado',
                            type: 'error',
                            showCancelButton: false,
                            showConfirmButton: true,
                            cancelButtonColor: '#00ce68',
                            cancelButtonText: 'Aceptar',
                        });
                    }else if(data.data.status == "DECLINED"){
                        var theDiv2 = document.getElementById("status");
                        var content2 = document.createTextNode("Rechazada");
                        cargando(false);
                        theDiv2.appendChild(content2);
                        swal({
                            title: 'Pago Rechazado',
                            type: 'error',
                            showCancelButton: false,
                            showConfirmButton: true,
                            cancelButtonColor: '#00ce68',
                            cancelButtonText: 'Aceptar',
                        });
                    }else if(data.data.status == "PENDING"){
                        var theDiv2 = document.getElementById("status");
                        var content2 = document.createTextNode("Pendiente");
                        cargando(false);
                        theDiv2.appendChild(content2);
                        swal({
                            title: 'Pago Pendiente',
                            type: 'error',
                            showCancelButton: false,
                            showConfirmButton: true,
                            cancelButtonColor: '#00ce68',
                            cancelButtonText: 'Aceptar',
                        });
                    }else{
                        var theDiv2 = document.getElementById("status");
                        var content2 = document.createTextNode("Error Desconocido");
                        cargando(false);
                        theDiv2.appendChild(content2);
                        swal({
                            title: 'Error Desconocido',
                            type: 'error',
                            showCancelButton: false,
                            showConfirmButton: true,
                            cancelButtonColor: '#00ce68',
                            cancelButtonText: 'Aceptar',
                        });
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
                            data : {reference:reference, saldo:saldo, status:status, transactionId:transactionId},
                            success : function(data){
                                cargando(false);
                                if(data.mensaje == true){
                                    swal({
                                        title: data.mensaje,
                                        type: 'success',
                                        showCancelButton: false,
                                        showConfirmButton: true,
                                        cancelButtonColor: '#00ce68',
                                        cancelButtonText: 'Aceptar',
                                    });
                                }else{
                                    swal({
                                        title: data.mensaje,
                                        type: 'error',
                                        showCancelButton: false,
                                        showConfirmButton: true,
                                        cancelButtonColor: '#00ce68',
                                        cancelButtonText: 'Aceptar',
                                    });
                                }
                            }
                        });
                    }
                }
            });
            return false;
        });
        
        function cargando(abierta){
            if (abierta) {
                $(".loader").show();
            }else{
                $(".loader").hide();
            }
        }
    </script>
</body>
</html>