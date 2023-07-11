<!DOCTYPE html>
<html lang="en">
<head><meta charset="gb18030">
	
	
	
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Sistema de cotizacion Avanzado</title>
	<link href="https://fonts.googleapis.com/css?family=Roboto+Slab:300,400" rel="stylesheet">
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
	<link rel="stylesheet" type="text/css" href="{{asset('css/pushbar.css')}}">
	<link rel="stylesheet" href="{{asset('css/paginicio.css')}}">
	<link href="{{asset('vendors/fontawesome/css/all.css')}}" rel="stylesheet" />
	<link href="https://fonts.googleapis.com/css?family=Archivo+Black" rel="stylesheet">
	
	<link rel="shortcut icon" href="{{asset('images/favicon2.png')}}" />
	 <meta name="description" content="Software administrativo en la nube para gestionar tu negocio. Contamos con facturación electrónica y un completo ERP. Crea tu cuenta Gratis por 30 dias" />
	
	<script src="{{asset('js/paginicio/jquery-3.3.1.min.js')}}"></script>
	<script src="{{asset('js/paginicio/main.js')}}"></script>
	<script type="text/javascript" src="{{asset('js/paginicio/floating-wpp.min.js')}}"></script>
	
	
	<link rel="stylesheet" href="{{asset('css/floating-wpp.min.css')}}">
	<style>
        section.pricing {
            background: #007bff;
            background: linear-gradient(to right, #0062E6, #33AEFF);
        }

        .pricing .card {
            border: none;
            border-radius: 1rem;
            transition: all 0.2s;
            box-shadow: 0 0.5rem 1rem 0 rgba(0, 0, 0, 0.1);
        }

        .pricing hr {
            margin: 1.5rem 0;
        }

        .pricing .card-title {
            margin: 0.5rem 0;
            font-size: 0.9rem;
            letter-spacing: .1rem;
            font-weight: bold;
        }

        .pricing .card-price {
            font-size: 2.5rem;
            margin: 0;
        }

        .pricing .card-price .period {
            font-size: 0.8rem;
        }

        .pricing ul li {
            margin-bottom: 1rem;
        }

        .pricing .text-muted {
            opacity: 0.7;
        }

        .pricing .btn {
            font-size: 80%;
            border-radius: 5rem;
            letter-spacing: .1rem;
            font-weight: bold;
            padding: 1rem;
            opacity: 0.7;
            transition: all 0.2s;
        }

        /* Hover Effects on Card */

        @media (min-width: 992px) {
            .pricing .card:hover {
                margin-top: -.25rem;
                margin-bottom: .25rem;
                box-shadow: 0 0.5rem 1rem 0 rgba(0, 0, 0, 0.3);
            }
            .pricing .card:hover .btn {
                opacity: 1;
            }
        }
        
        .ribbon {
            position: absolute;
            right: -5px; top: -5px;
            z-index: 1;
            overflow: hidden;
            width: 75px; height: 75px;
            text-align: right;
        }
        .ribbon span {
            font-size: 10px;
            font-weight: bold;
            color: #FFF;
            text-transform: uppercase;
            text-align: center;
            line-height: 20px;
            transform: rotate(45deg);
            -webkit-transform: rotate(45deg);
            width: 100px;
            display: block;
            background: #79A70A;
            background: linear-gradient(#2989d8 0%, #1e5799 100%);
            box-shadow: 0 3px 10px -5px rgba(0, 0, 0, 1);
            position: absolute;
            top: 19px; right: -21px;
        }
        .ribbon span::before {
            content: "";
            position: absolute; left: 0px; top: 100%;
            z-index: -1;
            border-left: 3px solid #1e5799;
            border-right: 3px solid transparent;
            border-bottom: 3px solid transparent;
            border-top: 3px solid #1e5799;
        }
        .ribbon span::after {
            content: "";
            position: absolute; right: 0px; top: 100%;
            z-index: -1;
            border-left: 3px solid transparent;
            border-right: 3px solid #1e5799;
            border-bottom: 3px solid transparent;
            border-top: 3px solid #1e5799;
        }
    </style>
</head>
<body>
	<div class="contenedor">
	    <div id="wppbtn"></div> 
		<header>
			<div class="menu" id="scamenu">
				<div class="header-left">
                    <a href="{{route('Inicio')}}">
                        <img class="header-img" src="{{asset('/images/logo_bienvenida.png')}}">
                    </a>
				</div>
				<div class="header-right">
					<nav>
						<ul>
							<li><a href="{{route('Inicio')}}">Inicio</a></li>
							<li><a href="{{route('sic.modulos')}}">Módulos</a></li>
							<li><a href="{{route('sic.planes')}}">Planes</a></li>
							<li><a href="{{route('contactanos.index')}}">Contacto</a></li>
							<div class="btn-crearcuenta">
						<a target="_blank" href="{{route('login')}}"><button type="button" class="btn btn-primary tam-btn-std" >
						    INGRESAR
								</button></a>
						<a href="{{route('sic.registrarse')}}"><button type="button" class="btn btn-primary">
							REGISTRATE AHORA
						</button></a>
					</div>
						</ul>
					</nav>
				</div>
			</div>
		</header> 
		
		<main id="pushbar123">
			<nav class="nav-ocult">
				<button class="btn-menu" data-pushbar-target="pushbar-menu"><i class="fas fa-bars"></i></button>
				<img class="header-img" src="{{asset('/images/999.png')}}" style="    margin-top: -28px;">
				<!--<a href="#" class="banner" data-pushbar-target="pushbar-productos">SIC (Siatema Integrado Cotizaciones)</a>-->
			</nav>

			<!-- Pushbars: Menu -->
			<div data-pushbar-id="pushbar-menu" class="pushbar from_left pushbar-menu">
				<div class="btn-cerrar derecha">
					<button data-pushbar-close><i class="fas fa-times"></i></button>
				</div>
				<nav class="menu">
					<a href="{{route('Inicio')}}">Inicio</a>
					<a href="{{route('sic.modulos')}}">Módulos</a>
					<a href="{{route('sic.planes')}}">Planes</a>
					<a href="{{route('contactanos.index')}}">Contacto</a>
					<div class="btn-crearcuenta">
					    <center>
            <a href="{{route('login')}}"><button type="button" class="btn btn-primary">
              INICIAR SESION
            </button></a></center>
						<a href="{{route('sic.registrarse')}}"><button type="button" class="btn btn-primary">
							REGISTRATE AHORA
						</button></a>
					</div>
				</nav>
			</div>
		</main>
		

		@yield('content2')

		<div class="footer-sca">
			<ul class="column">
				<li class="column-inicio">
					<h2>Gestordepartes.net</h2>
					<div class="column-items"><a href="">Quienes somos</a></div>
					<div class="column-items"><a href="{{route('sic.planes')}}">Planes</a></div>
					<div class="column-items"><a href="{{route('sic.modulos')}}">Módulos</a></div>
					<div class="column-items"><a href="https://api.whatsapp.com/send?phone=573206177170&text=Hola%20Gestor,%20como%20puedo%20trabajar%20con%20ustedes?">Trabaja con nosotros</a></div>
				</li>
				<li class="column-inicio">
					<h2>Funciones</h2>
					<div class="column-items"><a href="{{route('sic.planes')}}">Planes</a></div>
					<div class="column-items"><a href="{{route('sic.modulos')}}">Módulos</a></div>
				</li>
				<li class="column-inicio">
					<h2>Contactanos</h2>
					<div class="column-items"><a href="{{route('contactanos.index')}}">Contactanos</a></div>
					<div class="column-items"><a href="https://api.whatsapp.com/send?phone=573206177170&text=Hola%20Gestor,%20necesito%20ayuda">Centro de ayuda</a></div>
				</li>
			</ul>
          <div class="container-fluid clearfix">
            <span class="text-muted d-block text-sm-left d-sm-inline-block">Copyright©  2020 Intercarnet.
            </div>
		</div>

		<script src="{{asset('js/paginicio/pushbar.js')}}"></script>
		<script type="text/javascript">
			var pushbar = new Pushbar({
				blur: true,
				overlay: true
			});
		</script>