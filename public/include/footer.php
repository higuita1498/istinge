<!-- Location -->
    <section class="location text-light py-5" id="contact">
        <div class="container" data-aos="zoom-in">
            <div class="row">
                <div class="col-lg-12 align-items-center text-center pb-4">
                    <h2 class="py-2">CONTÁCTANOS IST INGENIERIA Y SOLUCIONES TECNOLÓGICAS S.A.S.</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-3 d-flex align-items-center">
                    <div class="p-2"><i class="far fa-map fa-3x"></i></div>
                    <div class="ms-2">
                        <h6>DIRECCIÓN</h6>
                        <p>Transversal 1 A4 68 C 29 TO 15 AP 503, Soledad- Atlántico</p>
                    </div>
                </div>
                <div class="col-lg-3 d-flex align-items-center" >
                    <div class="p-2"><i class="fas fa-mobile-alt fa-3x"></i></div>
                    <div class="ms-2">
                        <h6>TELÉFONO</h6>
                        <p>(322) 5921616</p>
                    </div>
                </div>
                <div class="col-lg-3 d-flex align-items-center" >
                    <div class="p-2"><i class="far fa-envelope fa-3x"></i></div>
                    <div class="ms-2">
                        <h6>CORREO</h6>
                        <p>servicioalcliente@istsas.com</p>
                    </div>
                </div>
                <div class="col-lg-3 d-flex align-items-center" >
                    <div class="p-2"><i class="far fa-clock fa-3x"></i></div>
                    <div class="ms-2">
                        <h6>HORARIO</h6>
                        <p>09:00 AM - 18:00 PM</p>
                    </div>
                </div>
            </div> <!-- end of row -->
        </div> <!-- end of container -->
    </section> <!-- end of location -->

    <!-- Footer -->
    <section class="footer text-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 py-md-4 text-center">
                    <div class="align-items-center">
                        <p class="text-center">Copyright &copy;<script>document.write(new Date().getFullYear());</script> Todos los derechos reservados | Desarrollado por <a href="https://innovaapp.co" target="_blank">InnovaApp S.A.S</a></p>
                    </div>
                </div>
            </div> <!-- end of row -->
        </div> <!-- end of container -->
    </section> <!-- end of footer -->

    <div class="whatsapp text-left">
        <a href="https://api.whatsapp.com/send?phone=573225921616&text=Hola,%20estoy%20interesado%20en%20el%20servicio%20de%20Internet." target="_blank" title="Contactame por Whatsapp">
            <img src="./assets/images/whatsapp.png" alt="WhatsApp" />
        </a>
    </div>

    <style>
    .whatsapp {
        position: fixed;
        right:25px; /*Margen derecho*/
        bottom:20px; /*Margen abajo*/
        z-index:999;
    }
    .whatsapp img {
        width:60px; /*Alto del icono*/
        height:60px; /*Ancho del icono*/
    }
    .whatsapp:hover{
        opacity: 0.7 !important;
        filter: alpha(opacity=70) !important;
    }
</style>
<!-- Messenger plugin de chat Code -->
    <div id="fb-root"></div>

    <!-- Your plugin de chat code -->
    <div id="fb-customer-chat" class="fb-customerchat">
    </div>

    <script>
      var chatbox = document.getElementById('fb-customer-chat');
      chatbox.setAttribute("page_id", "115427590251137");
      chatbox.setAttribute("attribution", "biz_inbox");

      window.fbAsyncInit = function() {
        FB.init({
          xfbml            : true,
          version          : 'v11.0'
        });
      };

      (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = 'https://connect.facebook.net/es_LA/sdk/xfbml.customerchat.js';
        fjs.parentNode.insertBefore(js, fjs);
      }(document, 'script', 'facebook-jssdk'));
    </script>