<?php
    include "conexion.php";
 ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no"/>
  <meta name="description" content=""/>
  <meta name="author" content=""/>
  <title><?=$title;?> | Recuperar Contraseña</title>
  <!-- loader-->
  <link href="assets/css/pace.min.css" rel="stylesheet"/>
  <script src="assets/js/pace.min.js"></script>
  <!--favicon-->
  <link rel="icon" href="assets/images/logo0.png" type="image/x-icon">
  <!-- Bootstrap core CSS-->
  <link href="assets/css/bootstrap.min.css" rel="stylesheet"/>
  <!-- animate CSS-->
  <link href="assets/css/animate.css" rel="stylesheet" type="text/css"/>
  <!-- Icons CSS-->
  <link href="assets/css/icons.css" rel="stylesheet" type="text/css"/>
  <!-- Custom Style-->
  <link href="assets/css/app-style.css" rel="stylesheet"/>
  <link href="../software/vendors/sweetalert2/sweetalert2.min.css" rel="stylesheet"/>
  
  <style>
        .loader {
          position: fixed;
          left: 0px;
          top: 0px;
          width: 100%;
          height: 100%;
          z-index: 9999;
          background: url('../software/images/loader.gif') 50% 50% no-repeat rgb(0,0,0);
          opacity: .8;
          display: none;
      }
    </style>
  
</head>

<body class="bg-theme bg-theme2">
<div class="loader"></div>
<!-- Start wrapper-->
 <div id="wrapper">

 <div class="height-100v d-flex align-items-center justify-content-center">
	<div class="card card-authentication1 mb-0">
		<div class="card-body">
		 <div class="card-content p-2">
		  <div class="card-title text-uppercase pb-2">Recuperar Contraseña</div>
		    <p class="pb-2">Por favor indique su nombre de usuario y su identificación para validar la información y procedar al envío del coreo electrónico.</p>
		    <form id="reset_pass" method="POST" onsubmit="event.preventDefault();" action="bk_reset.php">
			  <div class="form-group">
			  <label for="exampleInputEmailAddress" class="">Nombre de Usuario</label>
			   <div class="position-relative has-icon-right">
				  <input type="text" id="usuario" name="usuario" class="form-control input-shadow" placeholder="">
				  <div class="form-control-position">
					  <i class="icon-envelope-open"></i>
				  </div>
			   </div>
			  </div>
			  <div class="form-group">
			  <label for="exampleInputEmailAddress" class="">Identificación</label>
			   <div class="position-relative has-icon-right">
				  <input type="text" id="nit" name="nit" class="form-control input-shadow" placeholder="">
				  <div class="form-control-position">
					  <i class="icon-envelope-open"></i>
				  </div>
			   </div>
			  </div>
			  <a href="javascript:reset_pass()" id="btn_cambiar" class="btn btn-light btn-block mt-3">Recuperar Contraseña</a>
			 </form>
		   </div>
		  </div>
		   <div class="card-footer text-center py-3">
		    <p class="text-warning mb-0"><a href="index.php">Iniciar sesión</a></p>
		  </div>
	     </div>
	     </div>
    
     <!--Start Back To Top Button-->
    <a href="javaScript:void();" class="back-to-top"><i class="fa fa-angle-double-up"></i> </a>
    <!--End Back To Top Button-->
	
	</div><!--wrapper-->
	
  <!-- Bootstrap core JavaScript-->
  <script src="assets/js/jquery.min.js"></script>
  <script src="assets/js/popper.min.js"></script>
  <script src="assets/js/bootstrap.min.js"></script>
	
  <!-- sidebar-menu js -->
  <script src="assets/js/sidebar-menu.js"></script>
  
  <!-- Custom scripts -->
  <script src="assets/js/app-script.js"></script>
  <script src="../software/vendors/sweetalert2/sweetalert2.min.js"></script>
  
  <script>
      function reset_pass(){
            $(".loader").show();
            if($('#usuario').val().length > 0 && $('#nit').val().length > 0){
                $.post($("#reset_pass").attr('action'), $("#reset_pass").serialize(), function(data) {
                    Swal.fire({
                        type: data['type'],
                        title: data['title'],
                        html: data['mensaje'],
                        showConfirmButton: false,
                        timer: 4000
                    });
                    if(data['type'] == 'success'){
                        setTimeout( function() { location.href = "index.php"; }, 3000 );
                    }
                    $(".loader").hide();
                }, 'json');
            }else{
                $(".loader").hide();
                Swal.fire({
                    type: 'warning',
                    title: 'ALERTA',
                    html: 'Complete la información solicitada'
                });
            }
        }
  </script>
  
	
</body>
</html>
