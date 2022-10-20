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
	      #navbarsExampleDefault > ul > li:nth-child(7) > a{
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
                        <a class="nav-link" aria-current="page" href="index.php#header">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#about">Acerca de Nosotros</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#plans">Planes Hogar Fibra</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="solicitud.php">Solicitar Servicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php#test">Test de Velocidad</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pay.php">Pagos en Linea</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pqrs.php">PQR</a>
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
                <div class="col-xl-10 offset-xl-1 text-center">
                    <h1>PQRS Rapilink S.A.S</h1>
                </div> <!-- end of col -->
            </div> <!-- end of row -->
        </div> <!-- end of container -->
    </header> <!-- end of ex-header -->
    <!-- end of header -->


    <!-- Basic -->
    <div class="ex-basic-1 pb-5">
        <div class="container">
            <div class="row" id="form-factura0">
					<div class="col-lg-6 offset-lg-3 text-justify">
						<div class="mb-5 mb-lg-0" data-aos="fade-up" data-aos-delay="300">
							<form id="guardarPQRS" class="row mt-5" action="bk_pqrs.php">
							    <div class="col-12 pb-2">
							        <select class="form-control" name="solicitud" id="solicitud" placeholder="Tipo de Solicitud" title="Tipo de Solicitud" required="">
								        <option selected="" disabled="">Tipo de Solicitud</option>
										<option value="Peticiones">Peticiones</option>
										<option value="Quejas">Quejas</option>
										<option value="Reclamos">Reclamos</option>
										<option value="Sugerencias">Sugerencias</option>
									</select>
					        	</div>
						        <div class="col-12 pb-2">
						        	<input class="form-control" type="text" name="nombres" id="nombres" placeholder="Nombre Completo" title="Nombre Completo" required="" autocomplete="off">
					        	</div>
					        	<div class="col-6 pb-2">
						        	<input class="form-control" type="email" name="email" id="email" placeholder="Correo Electrónico" title="Correo Electrónico" required=""  autocomplete="off">
					        	</div>
					        	<div class="col-6 pb-2">
						        	<input class="form-control" type="number" name="telefono" id="telefono" placeholder="Nro Teléfono" title="Nro Teléfono" required="" autocomplete="off" min="0">
					        	</div>
					        	<div class="col-12 pb-2">
						        	<textarea class="form-control" name="direccion" id="direccion" required="" autocomplete="off" placeholder="Dirección"></textarea>
					        	</div>
					        	<div class="col-12 pb-2">
						        	<textarea class="form-control" name="mensaje" id="mensaje" required="" autocomplete="off" placeholder="Mensaje"></textarea>
					        	</div>
							</form>

							<div class="col-12 py-2">
								<center><button class="btn btn-main" style="color: var(--secondary);" onclick="javascript:guardarPQRS();">Realizar Solicitud</button></center>
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
        function guardarPQRS(){
            cargando(true);
            if($("#solicitud").val() != ''  && $("#nombres").val() != ''  && $("#email").val() != ''  && $("#telefono").val() != ''   && $("#direccion").val() != ''   && $("#mensaje").val() != ''){
                $.post($("#guardarPQRS").attr('action'), $("#guardarPQRS").serialize(), function(data) {
                    $('#guardarPQRS').trigger("reset");
                    swal("Solicitud Registrada", data['mensaje'], "success");
                    cargando(false);
                }, 'json');
            }else{
                cargando(false);
                swal({
                    title: 'Debe llenar la información solicitada',
                    type: 'warning',
                    showCancelButton: true,
                    showConfirmButton: false,
                    cancelButtonColor: '#00ce68',
                    cancelButtonText: 'Aceptar',
                });
            }
        }
            
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