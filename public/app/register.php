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
  <title><?=$title;?> | Registrate</title>
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
  
</head>

<body class="bg-theme bg-theme2">

<!-- start loader -->
   <div id="pageloader-overlay" class="visible incoming"><div class="loader-wrapper-outer"><div class="loader-wrapper-inner" ><div class="loader"></div></div></div></div>
   <!-- end loader -->

<!-- Start wrapper-->
 <div id="wrapper">

	<div class="card card-authentication1 mx-auto my-4">
		<div class="card-body">
		 <div class="card-content p-2">
		 	<div class="text-center">
		 		<img src="assets/images/logo0.png" alt="logo icon" width="200px">
		 	</div>
		  <div class="card-title text-uppercase text-center py-3">asociar contrato</div>
		    <form method="post" action="procesar_registro.php">
			  <div class="form-group">
			  <label for="exampleInputName" class="sr-only">Número de Identificación</label>
			   <div class="position-relative has-icon-right">
				  <input type="number" id="nit" class="form-control input-shadow" placeholder="Número de Identificación" required name="nit">
				  <div class="form-control-position">
					  <i class="icon-user"></i>
				  </div>
			   </div>
			  </div>
			  <div class="form-group">
			  <label for="exampleInputEmailId" class="sr-only">Usuario</label>
			   <div class="position-relative has-icon-right">
				  <input type="text" id="username" class="form-control input-shadow" placeholder="Tu Nombre de usuario" name="username" required>
				  <div class="form-control-position">
					  <i class="icon-user"></i>
				  </div>
			   </div>
			  </div>
			  <div class="form-group">
			   <label for="exampleInputPassword" class="sr-only">Contraseña</label>
			   <div class="position-relative has-icon-right">
				  <input type="password" id="exampleInputPassword" class="form-control input-shadow" placeholder="Tu contraseña" required name="pass">
				  <div class="form-control-position">
					  <i class="icon-lock"></i>
				  </div>
			   </div>
			  </div>
			  
			  <div class="form-group">
			      <div class="icheck-material-white">
			          <input type="checkbox" id="user-checkbox" name="user-checkbox" value="1">
			          <label for="user-checkbox">Acepto recibir material promocional y marketing</label>
			      </div>
			  </div>
			 
			   <div id="result"><br></div>
			 <button type="submit" class="btn btn-light btn-block waves-effect waves-light" style="background-color: white; color: black;" id="btn">Registrarse</button>
			 
			 </form>
		   </div>
		  </div>
		  <div class="card-footer text-center py-3">
		    <p class="mb-0">¿Ya tienes una cuenta? <a href="index.php"> Inicia Sesión</a></p>
		  </div>
	     </div>
    
     <!--Start Back To Top Button-->
    <a href="javaScript:void();" class="back-to-top"><i class="fa fa-angle-double-up"></i> </a>
    <!--End Back To Top Button-->
	
	
	</div><!--wrapper-->
	
  <!-- Bootstrap core JavaScript-->
  <script src="assets/js/jquery.min.js"></script>
   <script type="text/javascript">
  $(document).ready(function(){
    $("#nit").change(function(){
      var nit = $(this).val();

      if(nit.length > 0){
        $("#result").html('').removeAttr('style');
        $.ajax({
          type : 'POST',
          url  : 'verify.php',
          data : $(this).serialize(),
          success : function(data){
            var result = $.trim(data);
            if (result == "Disculpe, debe poseer un contrato activo para hacer uso de Mi Intercarnet") {
              document.getElementById('btn').style.display = 'none';
              $("#result").html(data).attr('style','color: white;text-align: center;text-transform: uppercase;border: 1px solid rgba(255, 255, 255, 0.1);padding: 10px;background: #ff000066;border-radius: 15px;');
            }else{
              $("#result").html('').removeAttr('style');
              document.getElementById('btn').style.display = 'inline';
            }
          }
        });
        return false;
      }else{
        $("#result").html('').removeAttr('style');
      }
    });
  });
</script>

  <script src="assets/js/popper.min.js"></script>
  <script src="assets/js/bootstrap.min.js"></script>
	
  <!-- sidebar-menu js -->
  <script src="assets/js/sidebar-menu.js"></script>
  
  <!-- Custom scripts -->
  <script src="assets/js/app-script.js"></script>
    <?php include "partials/whatsapp.php"; ?>
    <?php include "partials/bot.php"; ?>
  
</body>
</html>
