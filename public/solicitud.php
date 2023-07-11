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
	      #navbarsExampleDefault > ul > li:nth-child(4) > a{
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
                    <h1>Solicitud de Servicio</h1>
                </div> <!-- end of col -->
            </div> <!-- end of row -->
        </div> <!-- end of container -->
    </header> <!-- end of ex-header -->
    <!-- end of header -->


    <!-- Basic -->
    <div class="ex-basic-1 pt-5 pb-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-10 offset-lg-1 text-center" data-aos="fade-down"> 
                    <img src="./assets/images/logo.png" alt="about">
                </div>
            </div>
            <div class="row" id="form-factura0">
					<div class="col-lg-10 offset-lg-1 text-justify">
						<div class="mb-0 mb-lg-0" data-aos="fade-up" data-aos-delay="300">
						    <div class="text-center pb-0">
						        <h2 class="py-2">¿QUIERES ADQUIRIR UN PLAN?</h2>
						    </div>
						    
						    <div class="row gy-4 aos-init aos-animate d-none" data-aos="zoom-in">
                                <div class="col-lg-4">
                                    <div class="card bg-transparent px-4 text-center" style="border: 10px solid;border-image-slice: 1;border-width: 3px;border-image-source: var(--gradient);">
                                        <h4 class="py-2">4 MEGAS</h4>
                                        <p class="py-3">Plan Básico</p>
                                        <div class="block align-items-center">
                                            <p class="pe-2"><i class="far fa-check-circle fa-1x"></i></p>
                                            <p>Hasta 4 dispositivos</p>
                                        </div>
                                        <h4 class="py-3 d-none">$70.000/Mensual</h4>
                                    </div>  
                                </div>
                
                                <div class="col-lg-4">
                                    <div class="card bg-transparent px-4 text-center" style="border: 10px solid;border-image-slice: 1;border-width: 3px;border-image-source: var(--gradient);">
                                        <h4 class="py-2">7 MEGAS</h4>
                                        <p class="py-3">Plan Intermedio</p>
                                        <div class="block align-items-center">
                                            <p class="pe-2"><i class="far fa-check-circle fa-1x"></i></p>
                                            <p>Hasta 6 dispositivos</p>
                                        </div>
                                        <h4 class="py-3 d-none">$120.000/Mensual</h4>
                                    </div>  
                                </div>
                
                                <div class="col-lg-4">
                                    <div class="card bg-transparent px-4 text-center" style="border: 10px solid;border-image-slice: 1;border-width: 3px;border-image-source: var(--gradient);">
                                        <h4 class="py-2">10 MEGAS</h4>
                                        <p class="py-3">Plan Top</p>
                                        <div class="block align-items-center">
                                            <p class="pe-2"><i class="far fa-check-circle fa-1x"></i></p>
                                            <p>Hasta 10 dispositivos</p>
                                        </div>
                                        <h4 class="py-3 d-none">$195.000/Mensual</h4>
                                    </div>  
                                </div>
                            </div>
                            
							<form id="guardarSolicitud" class="row mt-5" action="bk_solicitud.php">
						        <div class="col-12 pb-2">
						        	<input class="form-control" type="text" name="nombre" id="nombre" placeholder="Nombre Completo" title="Nombre Completo" required="" autocomplete="off">
					        	</div>

					        	<div class="col-12 pb-2">
						        	<input class="form-control" type="number" name="cedula" id="cedula" placeholder="Identificación" title="Identificación" required="" autocomplete="off" min="0">
					        	</div>
					        	
					        	<div class="col-6 pb-2">
						        	<input class="form-control" type="number" name="nrouno" id="nrouno" placeholder="Nro Teléfono" title="Nro Teléfono" required="" autocomplete="off" min="0">
					        	</div>
					        	
					        	<div class="col-6 pb-2">
						        	<input class="form-control" type="number" name="nrodos" id="nrodos" placeholder="Nro Celular" title="Nro Celular" autocomplete="off" min="0">
					        	</div>
					        	
					        	<div class="col-12 pb-2">
						        	<input class="form-control" type="email" name="email" id="email" placeholder="Correo Electrónico" title="Correo Electrónico" autocomplete="off">
					        	</div>
					        	
					        	<div class="col-12 pb-2">
						        	<select class="form-control" name="plan" id="plan" title="Plan a Contratar" required="">
						        	    <option selected disabled>Plan a Contratar</option>
						        	    <option value="PLAN FIBRA ÓPTICA - 4MB">PLAN FIBRA ÓPTICA - 4MB</option>
						        	    <option value="PLAN FIBRA ÓPTICA - 7MB">PLAN FIBRA ÓPTICA - 7MB</option>
						        	    <option value="PLAN FIBRA ÓPTICA - 10MB">PLAN FIBRA ÓPTICA - 10MB</option>
						        	    <option value="PLAN FIBRA ÓPTICA - 15MB">PLAN FIBRA ÓPTICA - 15MB</option>
						        	    <option value="PLAN FIBRA ÓPTICA - 20MB">PLAN FIBRA ÓPTICA - 20MB</option>
						        	    <option value="PLAN FIBRA ÓPTICA - 40MB">PLAN FIBRA ÓPTICA - 40MB</option>
						        	    <option value="PLAN PLUS - 3MB">PLAN PLUS - 3MB</option>
						        	    <option value="PLAN EXTREME - 4MB">PLAN EXTREME - 4MB</option>
						        	    <option value="PLAN TURBO - 5MB">PLAN TURBO - 5MB</option>
						        	    <option value="PLAN DIAMANTE -7MB">PLAN DIAMANTE -7MB</option>
						        	    <option value="PLAN ULTRA - 10MB">PLAN ULTRA - 10MB</option>
						        	</select>
					        	</div>
					        	
					        	<div class="col-12 pb-2">
						        	<textarea class="form-control" name="direccion" id="direccion" required="" autocomplete="off" placeholder="Dirección"></textarea>
					        	</div>
							</form>

							<div class="col-12 py-2">
								<center><button class="btn btn-main" style="color: var(--secondary);" onclick="javascript:guardarSolicitud();">Realizar Solicitud</button></center>
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
        function guardarSolicitud(){
            cargando(true);
            if($("#nombre").val() != ''  && $("#identificacion").val() != ''  && $("#nrouno").val() != ''  && $("#direccion").val() != ''   && $("#plan").val() != '' ){
                $.post($("#guardarSolicitud").attr('action'), $("#guardarSolicitud").serialize(), function(data) {
                    $('#guardarSolicitud').trigger("reset");
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