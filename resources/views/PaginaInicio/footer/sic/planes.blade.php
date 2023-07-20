@extends('layouts.includes.inicio')
@section('content2')

    <br>
    <br>
    <br>
    <section class="pricing py-5">
        <div class="container">
            <div>
                <h1 class="display-4 text-center text-white" style="font-weight: normal; text-shadow: 2px 2px black" >Planes</h1>
                <h4 class="display-5 text-center text-white" style="text-shadow: 1px 1px black"> Selecciona el plan que más se ajuste a tu negocio</h4>

            </div>
            <hr>
            <div class="row">
                <!-- Free Tier -->
                <div class="col-lg-3 d-flex">
                    <div class="card mb-5 mb-lg-0">
                        <div class="card-body">
                            <h5 class="card-title text-muted text-uppercase text-center">Gratis</h5>
                            <h6 class="card-price text-center">0<span class="period">COP/MES</span></h6>
                            <hr>
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
                            <hr>
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
                            <hr>
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
                            <hr>
                            <a href="https://api.whatsapp.com/send?phone=573206177170&text=Hola%20quiero%20mas%20informacion%20de%20los%20planes%20que%20ofrecen" class="btn btn-block btn-primary text-uppercase"><span class="small" style="font-weight: bolder">MÁS INFORMACIÓN</span></a>
                        </div>
                        <div class="card-footer">
                            <h6 class="text-center" ><a href="{{route('sic.registrarse')}}" style="color: green"> PRUEBA 30 DÍAS GRATIS</a></h6>
                            <hr>
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
                            <hr>
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
                            <hr>
                            <a href="https://api.whatsapp.com/send?phone=573206177170&text=Hola%20quiero%20mas%20informacion%20de%20los%20planes%20que%20ofrecen" class="btn btn-block btn-primary text-uppercase"><span class="small" style="font-weight: bolder">MÁS INFORMACIÓN</span></a>
                        </div>
                        <div class="card-footer">
                            <h6 class="text-center" ><a href="{{route('sic.registrarse')}}" style="color: green"> PRUEBA 30 DÍAS GRATIS</a></h6>
                            <hr>
                            <h6 class="text-center" style="color: green" >10% DESCUENTO por pago anual</h6>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 d-flex">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title text-muted text-uppercase text-center">Avanzado</h5>
                            <h6 class="card-price text-center">90.000<span class="period">COP/MES</span></h6>
                            <hr>
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
                            <hr>
                            <a href="https://api.whatsapp.com/send?phone=573206177170&text=Hola%20quiero%20mas%20informacion%20de%20los%20planes%20que%20ofrecen" class="btn btn-block btn-primary text-uppercase"><span class="small" style="font-weight: bolder">MÁS INFORMACIÓN</span></a>
                        </div>
                        <div class="card-footer">
                            <h6 class="text-center" ><a href="{{route('sic.registrarse')}}" style="color: green"> PRUEBA 30 DÍAS GRATIS</a></h6>
                            <hr>
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
            <hr>
            <div class=" text-center" style="display:block;margin:auto;">
            <img src="{{asset('images/dian.png')}}" alt="" width="250" height="70" style="">
                <img src="{{asset('images/cadena.png')}}" alt="" width="250" height="70">
            </div>
        </div>
    </section>
@endsection
