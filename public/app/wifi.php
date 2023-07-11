<?php
    include "conexion.php";
    session_start();
    if (isset($_SESSION['logueado']) && $_SESSION['logueado']) {
        $usuario_actual = $_SESSION['username'];
        $cliente_actual = "SELECT id_cliente FROM usuarios_app WHERE user = '$usuario_actual'";
        $result_cliente = mysqli_query($con,$cliente_actual);
        $assoc_cliente = mysqli_fetch_assoc($result_cliente);
        $cliente = $assoc_cliente['id_cliente'];
        
        $wifi = "SELECT * FROM wifi WHERE id_cliente = ".$_SESSION['id_cliente']." ORDER BY id DESC";
        $result_wifi = mysqli_query($con,$wifi);
        $assoc_wifi = mysqli_fetch_assoc($result_wifi);
        $pass_new = $assoc_wifi['pass_nueva'];
        $red_new = $assoc_wifi['red_nueva'];
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
  <title><?=$title;?> | Cambio de cotraseña WIFI</title>
  <link href="assets/css/pace.min.css" rel="stylesheet"/>
  <script src="assets/js/pace.min.js"></script>
  <link rel="icon" href="assets/images/favicon.ico" type="image/x-icon">
  <link href="assets/plugins/simplebar/css/simplebar.css" rel="stylesheet"/>
  <link href="assets/css/bootstrap.min.css" rel="stylesheet"/>
  <link href="assets/css/animate.css" rel="stylesheet" type="text/css"/>
  <link href="assets/css/icons.css" rel="stylesheet" type="text/css"/>
  <link href="assets/css/sidebar-menu.css" rel="stylesheet"/>
  <link href="assets/css/app-style.css" rel="stylesheet"/>
  <link href="../software/vendors/sweetalert2/sweetalert2.min.css" rel="stylesheet"/>
  
  <style>
      .row.row-group>div {
          border-right: 0px solid rgba(255, 255, 255, 0.12);
      }
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
    <div id="wrapper">
        <?php include "partials/header.php"; ?>
        <div class="clearfix"></div>
        <div class="content-wrapper">
            <div class="container-fluid">
                <div class="card mt-3">
                    <div class="card-content"><br>
                        <div align="center">
                            <h4 class="mb-3">CAMBIO DE CONTRASEÑA WIFI</h4>
                        </div>
                    </div>
                </div>
            
                <div class="card mt-3">
                    <div class="card-content">
                        <div align="center">
                            <h4 class="m-3">Complete la información solicitada</h4>
                        </div>
                    <form id="solicitud_wifi" method="POST" onsubmit="event.preventDefault();" action="bk_wifi.php">
                        <input class="d-none" type="text" name="id_cliente" id="id_cliente" value="<?php echo $_SESSION['id_cliente']; ?>">
                        <input class="d-none" type="text" name="pass_new" id="pass_new" value="<?php echo $pass_new; ?>">
                        <input class="d-none" type="text" name="red_new" id="red_new" value="<?php echo $red_new; ?>">
                        <div class="row row-group m-4">
                            <div class="col-md-3 offset-md-3">
                                <label>Nombre de Red Antigua</label>
                                <input type="text" class="form-control" id="nombre_antiguo" name="nombre_antiguo" autocomplete="off" required>
                            </div>
                            <div class="col-md-3">
                                <label>Nombre de Red Nueva</label>
                                <input type="text" class="form-control" id="nombre_nuevo" name="nombre_nuevo" autocomplete="off" required>
                            </div>
                        </div>
                        <div class="row row-group m-4">
                            <div class="col-md-3">
                                <label>Contraseña Antigua</label>
                                <input type="password" class="form-control" id="contrasena_antiguo" name="contrasena_antiguo" autocomplete="off" maxlength="64" required>
                            </div>
                            <div class="col-md-3">
                                <label>Contraseña Nueva</label>
                                <input type="password" class="form-control" id="contrasena_nuevo" name="contrasena_nuevo" autocomplete="off" maxlength="64" required>
                            </div>
                            <div class="col-md-3">
                                <label>Confirme la Contraseña</label>
                                <input type="password" class="form-control" id="confirmar_contrasena" autocomplete="off" maxlength="64" required>
                            </div>
                            <div class="col-md-3">
								<label></label>
								<button class="btn btn-primary" type="button" onclick="mostrarContrasena()" style="margin-top: 32px;">Mostrar Contraseñas</button>
							</div>
                        </div>
                        <div class="row row-group m-4">
                            <div class="col-md-3 offset-md-4">
                                <label>Red Oculta</label>
                                <select class="form-control" name="red_oculta" id="red_oculta" required onchange="verificar_red(this.value);">
                                    <option value="0" selected>No</option>
                                    <option value="1">Si</option>
                                </select>
                            </div>
                        </div>
                        <div align="center">
                            <a href="javascript:solicitud_wifi()" id="btn_cambiar" class="btn btn-success mb-4">Cambiar Contraseña</a>
                        </div>
                    </form>
                    </div>
                 </div>
                 
                <div class="overlay toggle-menu"></div>
            </div>
        </div>
        
        <a href="javaScript:void();" class="back-to-top"><i class="fa fa-angle-double-up"></i> </a>
        
        <?php include "partials/footer.php"; ?>
    </div>
    
    <script src="assets/js/jquery.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            var id_cliente = <?=$cliente; ?>;
            
            $.ajax({
                type : 'GET',
                url  : 'bk_all.php',
                data : {id_cliente:id_cliente},
                dataType: 'JSON',
                success : function(data){
                    if(data.wifi){
                        if(data.wifi.status == 1){
                            $('#div_wifi, #div_link').addClass('d-none');
                            $('#div_link_wifi').removeClass('d-none');
                        }else{
                            $('#div_wifi, #div_link').removeClass('d-none');
                            $('#div_link_wifi').addClass('d-none');
                        }
                    }
                    
                    if(data.red == null){
                        $('#div_link_wifi').addClass('d-none');
                    }else{
                        $('#div_link_wifi').removeClass('d-none');
                    }
                }
            });
            return false;
        });
        
        $('#message').keydown(function () {
            var max = 20;
            var len = $(this).val().length;
            if (len >= max) {
                $('#mensaje_ayuda').text('Has llegado al límite');// Aquí enviamos el mensaje a mostrar          
                $('#mensaje_ayuda').addClass('text-danger');
                $('#message').addClass('is-invalid');
                $('#inputsubmit').addClass('disabled');    
                document.getElementById('inputsubmit').disabled = true;                    
            }      else {
                var ch = max - len;
                $('#mensaje_ayuda').text(ch + ' carácteres restantes');
                $('#mensaje_ayuda').removeClass('text-danger');            
                $('#message').removeClass('is-invalid');            
                $('#inputsubmit').removeClass('disabled');
                document.getElementById('inputsubmit').disabled = false;            
            }
        });
  
        function solicitud_wifi(){
            var max = 64;
            var min = 8;
            
            if($('#pass_new').val().length > 0){
                if($('#pass_new').val() === $('#contrasena_antiguo').val()){
                    if($('#contrasena_nuevo').val() === $('#confirmar_contrasena').val()){
                        if($('#contrasena_nuevo').val().length >= min && $('#contrasena_nuevo').val().length <= max){
                            $("#btn_cambiar").addClass('disabled');
                            $(".loader").show();
                            $.post($("#solicitud_wifi").attr('action'), $("#solicitud_wifi").serialize(), function(data) {
                                Swal.fire({
                                type: 'success',
                                title: 'COMPLETADO',
                                html: data['mensaje'],
                                showConfirmButton: false,
                                timer: 4000
                            });
                            setTimeout( function() { location.href = "dashboard.php"; }, 2000 );
                            $(".loader").hide();
                            }, 'json');
                        }else{
                            Swal.fire({
                                type: 'warning',
                                title: 'ERROR',
                                html: 'La contraseña permitida debe tener un rango entre 8 y 64 dígitos, intente nuevamete'
                            });
                        }
                    }else{
                        Swal.fire({
                            type: 'error',
                            title: 'ERROR',
                            html: 'Las contraseñas indicadas no coinciden, intente nuevamete'
                        });
                    }
                }else{
                    Swal.fire({
                        type: 'error',
                        title: 'ERROR',
                        html: 'La contraseña anterior no coincide con la almacenada, intente nuevamete'
                    });
                }
            }else{
                if($('#contrasena_nuevo').val() === $('#confirmar_contrasena').val()){
                    if($('#contrasena_nuevo').val().length >= min && $('#contrasena_nuevo').val().length <= max){
                        $("#btn_cambiar").addClass('disabled');
                        $(".loader").show();
                        $.post($("#solicitud_wifi").attr('action'), $("#solicitud_wifi").serialize(), function(data) {
                            Swal.fire({
                                type: 'success',
                                title: 'COMPLETADO',
                                html: data['mensaje'],
                                showConfirmButton: false,
                                timer: 4000
                            });
                            setTimeout( function() { location.href = "dashboard.php"; }, 2000 );
                            $(".loader").hide();
                        }, 'json');
                    }else{
                        Swal.fire({
                            type: 'warning',
                            title: 'ERROR',
                            html: 'La contraseña permitida debe tener un rango entre 8 y 64 dígitos, intente nuevamete'
                        });
                    } 
                }else{
                    Swal.fire({
						type: 'error',
						title: 'ERROR',
						html: 'Las contraseñas indicadas no coinciden, intente nuevamete'
					});
                }
            }
        }
        
        function verificar_red(option) {
            if (option == 1) {
                Swal.fire({
                    type: 'warning',
                    title: 'ALERTA',
                    html: 'Señor usuario si usted coloca red oculta recuerde que esta ya no le aparecerá en redes disponibles y le tocará agregarla manualmente.'
                });
            }
        }
        
        function mostrarContrasena(){
			var contrasena_antiguo = document.getElementById("contrasena_antiguo");
			var contrasena_nuevo = document.getElementById("contrasena_nuevo");
			var confirmar_contrasena = document.getElementById("confirmar_contrasena");
			if(contrasena_antiguo.type == "password"){
				contrasena_antiguo.type = "text";
				contrasena_nuevo.type = "text";
				confirmar_contrasena.type = "text";
			}else{
				contrasena_antiguo.type = "password";
				contrasena_nuevo.type = "password";
				confirmar_contrasena.type = "password";
			}
		}
    </script>
    
    <script src="assets/js/popper.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/plugins/simplebar/js/simplebar.js"></script>
    <script src="assets/js/sidebar-menu.js"></script>
    <script src="assets/js/app-script.js"></script>
    <script src="../software/vendors/sweetalert2/sweetalert2.min.js"></script>
    <?php include "partials/whatsapp.php"; ?>
</body>
</html>
