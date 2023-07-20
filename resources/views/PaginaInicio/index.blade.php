@extends('layouts.includes.inicio')
@section('content2')
<link href="https://fonts.googleapis.com/css?family=Indie+Flower" rel="stylesheet">
<main id="pegajoso1">
		<section class="all-image" style="overflow: hidden;">
				<div class="header-video">
					<video src="/images/PagInicio/video-inicio.mp4" autoplay loop muted></video>
					<div class="header-overlay"></div>
					<div class="header-content">
						<h1 class="title-video-o">Bienvenidos <br> al Sistema Administrativo <br>Avanzado</h1>
						<p>Somos un software en la nube para hacer crecer tu negocio.</p>
						<div class="btn-crearcuenta btn-invideo">
							<a href="{{route('sic.registrarse')}}"><button type="button" class="btn btn-primary">
								REGISTRATE AHORA GRATIS
							</button></a>
						</div>

					</div>
				</div>

			</section>
	<div class="section2">
		<center>
			<div class="enlamitad">
				<h1 style="padding-top: 20px; margin-bottom: 30px;">GESTIONA TU EMPRESA <br><font color="#ff7e00">(Sistema Integrado Administrativo)</font></h1>
				<ul id="list-services">
					<li><a href="#"><img src="/images/PagInicio/contactos.png" class="quebusca-img padentro"></a>
					    <h2>Contactos</h2>
					    <div class="content-plan-paginicio">
						<p>Puedes crear tus contactos y categorizarlos.</p>
						</div>
					</li>
					<li><a href="#"><img src="/images/PagInicio/inventario.png" class="quebusca-img padentro"></a><h2>Inventario</h2>
					<div class="content-plan-paginicio">
						<p>Gestiona y controla tu inventario de forma rápida.</p>
						</div></li>
                    <li><a href="#"><img src="/images/PagInicio/facturacion.png" class="quebusca-img padentro"></a><h2>Fact. electrónica</h2>
                        <div class="content-plan-paginicio">
                            <p>Realiza facturación electronica con validación previa.</p>
                        </div></li>

							<li><a href="#"><img src="/images/PagInicio/ingresos.png" class="quebusca-img padentro"></a><h2>Facturación</h2>
							<div class="content-plan-paginicio">
								<p>Emite cotizaciones, remisiones y facturas de forma rápida y facil.</p></div></li>
								<li><a href="#"><img src="/images/PagInicio/gastos.png" class="quebusca-img padentro"></a><h2>Compras</h2>
								<div class="content-plan-paginicio">
									<p>Controla y administra tus compras.</p></div></li>
										<li><a href="#"><img src="/images/PagInicio/banco.png" class="quebusca-img padentro"></a><h2>Bancos</h2>
										<div class="content-plan-paginicio">
										<p>Controla sus cuentas y movimientos en un sistema inteligente.</p></div></li>
									    
										<li><a href="#"><img src="/images/PagInicio/soporte.png" class="quebusca-img padentro"></a><h2>Soporte</h2>
										<div class="content-plan-paginicio">
										<p>Puedes generar tickets sobre posibles dudas que surjan cuando utilices nuestro sistema.</p></div></li>
										<li><a href="#"><img src="/images/PagInicio/reportes.png" class="quebusca-img padentro"></a><h2>Reportes</h2>
										<div class="content-plan-paginicio">
										<p>Consulta y genera reportes en tiempo real.</p></div></li>
										<li><a href="#"><img src="/images/PagInicio/pagina-web.png" class="quebusca-img padentro"></a><h2>Pagina web</h2>
										<div class="content-plan-paginicio">
										<p>Integra y Administra tu página web desde nuestro sistema.</p></div></li>
									</ul>
								</div>
							</center>

							<div style="text-align: center;">
								<p>

								</p>
							</div>

							<!-- Pushbars: Carrito de Compras -->


							<!-- Pushbars: Productos -->


							<!-- Pushbars: Newsletter -->

						</div>
                        <section class="pricing py-5" style="background: #f4f4f4;">
                            <div class="container">
                                <div>
                                    <h1 class="display-4 text-center" style="color:#4a5869;font-weight: normal; text-shadow: 1px 1px black" >Planes</h1>
                                    <h4 class="display-5 text-center" style="color: #4a5869;text-shadow: 0.5px 0.5px black"> Selecciona el plan que más se ajuste a tu negocio</h4>

                                </div>
                                <br/><div class="separador"></div>
                                <div class="row" >
                                    <!-- Free Tier -->
                                    <div class="col-lg-3 d-flex">
                                        <div class="card mb-5 mb-lg-0">
                                            <div class="card-body">
                                                <h5 class="card-title text-muted text-uppercase text-center">Gratis</h5>
                                                <h6 class="card-price text-center">0<span class="period">COP/MES</span></h6>
                                                <br/><div class="separador"></div>
                                                <ul class="fa-ul">
                                                    <li><span class="fa-li"><i class="fas fa-check"></i>
                                                        </span>10 Facturas de venta mensuales
                                                        <span class="text-muted small"><p>(Incluye facturación electrónica DIAN)</p></span>
                                                    </li>
                                                    <li><span class="fa-li"><i class="fas fa-check"></i></span></li>
                                                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Valor de facturación mensual
                                                        hasta <p>5 millones COP</p></li>
                                                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Compras</li>
                                                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Inventario</li>
                                                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Bancos</li>
                                                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Reportes</li>
                                                </ul>
                                                <br/><div class="separador"></div>
                                                <a href="{{route('sic.registrarse')}}" class="btn btn-block btn-primary text-uppercase"><span class="small" style="font-weight: bolder">Empieza a usarlo</span></a>
                                            </div>
                                            <div class="card-footer">
                                                <h6 class="text-center" style="color: green" >¡TOTALMENTE GRATIS!</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Plus Tier -->
                                    <div class="col-lg-3 d-flex">
                                        <div class="card mb-5 mb-lg-0">
                                            <div class="card-body">
                                                <h5 class="card-title text-muted text-uppercase text-center">Emprendedor</h5>
                                                <h6 class="card-price text-center">35.000<span class="period">COP/MES</span></h6>
                                                <br/><div class="separador"></div>
                                                <ul class="fa-ul">
                                                    <li><span class="fa-li"><i class="fas fa-check"></i>
                                                        </span>100 Facturas de venta mensuales
                                                        <span class="text-muted small"><p>(Incluye facturación electrónica DIAN)</p></span>
                                                    </li>
                                                    <li><span class="fa-li"><i class="fas fa-check"></i></span></li>
                                                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Valor de facturación mensual
                                                        hasta <p>10 millones COP</p></li>
                                                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Compras</li>
                                                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Inventario</li>
                                                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Bancos</li>
                                                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Reportes</li>
                                                </ul>
                                                <br/><div class="separador"></div>
                                                <a href="https://api.whatsapp.com/send?phone=573206177170&text=Hola%20quiero%20mas%20informacion%20de%20los%20planes%20que%20ofrecen" class="btn btn-block btn-primary text-uppercase"><span class="small" style="font-weight: bolder">MÁS INFORMACIÓN</span></a>
                                            </div>
                                            <div class="card-footer">
                                                <h6 class="text-center" ><a href="{{route('sic.registrarse')}}" style="color: green"> PRUEBA 30 DÍAS GRATIS</a></h6>
                                                <br/><div class="separador"></div>
                                                <h6 class="text-center" style="color: green" >10% DESCUENTO por pago anual</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- Pro Tier -->
                                    <div class="col-lg-3 d-flex">
                                        <div class="card">

                                            <div class="card-body">
                                                <h5 class="card-title text-muted text-uppercase text-center">Pyme -<span cal style="color: #ec0a00; font-weight: bolder; font-size: large"> ¡POPULAR!</span></h5>
                                                <div class="ribbon"><span>¡PROMOCIÓN!</span></div>
                                                <h6 class="card-price text-center">60.000<span class="period">COP/MES</span></h6>
                                                <br/><div class="separador"></div>
                                                <ul class="fa-ul">
                                                    <li><span class="fa-li"><i class="fas fa-check"></i>
                                                        </span>500 Facturas de venta mensuales
                                                        <span class="text-muted small"><p>(Incluye facturación electrónica DIAN)</p></span>
                                                    </li>
                                                    <li><span class="fa-li"><i class="fas fa-check"></i></span></li>
                                                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Valor de facturación mensual
                                                        hasta <p style="margin-bottom: 0px"><del class="text-muted">35 millones COP</del></p>
                                                        45 millones COP
                                                    </li>
                                                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Compras</li>
                                                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Inventario</li>
                                                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Bancos</li>
                                                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Reportes</li>
                                                </ul>
                                                <br/><div class="separador"></div>
                                                <a href="https://api.whatsapp.com/send?phone=573206177170&text=Hola%20quiero%20mas%20informacion%20de%20los%20planes%20que%20ofrecen" class="btn btn-block btn-primary text-uppercase"><span class="small" style="font-weight: bolder">MÁS INFORMACIÓN</span></a>
                                            </div>
                                            <div class="card-footer">
                                                <h6 class="text-center" ><a href="{{route('sic.registrarse')}}" style="color: green"> PRUEBA 30 DÍAS GRATIS</a></h6>
                                                <br/><div class="separador"></div>
                                                <h6 class="text-center" style="color: green" >10% DESCUENTO por pago anual</h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 d-flex">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title text-muted text-uppercase text-center">Avanzado</h5>
                                                <h6 class="card-price text-center">90.000<span class="period">COP/MES</span></h6>
                                                <br/><div class="separador"></div>
                                                <ul class="fa-ul">
                                                    <li><span class="fa-li"><i class="fas fa-check"></i>
                                                        </span>1000 Facturas de venta mensuales
                                                        <span class="text-muted small"><p>(Incluye facturación electrónica DIAN)</p></span>
                                                    </li>
                                                    <li><span class="fa-li"><i class="fas fa-check"></i></span></li>
                                                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Valor de facturación mensual
                                                        hasta <p>100 millones COP</p></li>
                                                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Compras</li>
                                                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Inventario</li>
                                                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Bancos</li>
                                                    <li><span class="fa-li"><i class="fas fa-check"></i></span>Reportes</li>
                                                </ul>
                                                <br/><div class="separador"></div>
                                                <a href="https://api.whatsapp.com/send?phone=573206177170&text=Hola%20quiero%20mas%20informacion%20de%20los%20planes%20que%20ofrecen" class="btn btn-block btn-primary text-uppercase"><span class="small" style="font-weight: bolder">MÁS INFORMACIÓN</span></a>
                                            </div>
                                            <div class="card-footer">
                                                <h6 class="text-center" ><a href="{{route('sic.registrarse')}}" style="color: green"> PRUEBA 30 DÍAS GRATIS</a></h6>
                                                <br/><div class="separador"></div>
                                                <h6 class="text-center" style="color: green" >10% DESCUENTO por pago anual</h6>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <br/>
                                <div class="row">
                                    <center>
                                    <div class="col-lg-8 d-flex">
                                        <div class="card mb-5 mb-lg-0">
                                            <div class="card-body">
                                                <h5 class="card-title text-muted text-uppercase text-center">Corporativo</h5>
                                                <br/>
                                                <div class="separador"></div>
                                                <ul class="fa-ul">
                                                    <li><span class="fa-li"><i class="fas fa-check"></i></span>
                                                    Si tu empresa no se acomoda a ninguno de los planes que ofrecemos, nosotros podemos personalizar tu plan y llegar a un acuerdo.
                                                    {{--<span class="text-muted small"><p>(Incluye facturación electrónica DIAN)</p></span>--}}
                                                    </li>
                                                </ul>
                                                <br/><div class="separador"></div>
                                                <a style="width:38%;" href="//api.whatsapp.com/send?phone=573206177170&amp;text=Hola%20quiero%20personalizar%20un%20plan%20para%20mi%20empresa" target="_blank" class="btn btn-block btn-primary text-uppercase"><span class="small" style="font-weight: bolder">Contáctanos</span></a>
                                            </div>
                                            <div class="card-footer">
                                                <h6 class="text-center" style="color: green" ></h6>
                                            </div>
                                        </div>
                                    </div>
                                    </center>
                                </div>
                                <br>
                                <div class="separador"></div>
                                <div class=" text-center" style="display:block;margin:auto;">
                                    <img src="{{asset('images/dian.png')}}" alt="" width="250" height="70" style="">
                                    <img src="{{asset('images/cadena.png')}}" alt="" width="250" height="70">
                                </div>
                            </div>
                        </section>
						<div class="section2">
							<center>

								<div class="prueba-gratis">
									<h1>¡PRUEBALO <strong style="color:#ff7e00;">GRATIS!</strong></h1><br>
									<h4>Te damos a probar todos los modulos que tenemos disponibles en este momento.</h4>
									<a class="button outline large button-abajo-planes" href="{{route('sic.registrarse')}}">CREAR <strong>CUENTA</strong></a>
								</div>

							</center>
						</div>
						<script defer src="https://use.fontawesome.com/releases/v5.3.1/js/all.js"></script>
					</main>

					@endsection
