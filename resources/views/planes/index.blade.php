@extends('layouts.app')

    

@section('content')
<style>
        section.pricing {
            background: #eafff6;
            background: linear-gradient(to right, #fafff8, white);
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
            font-size: 0.6rem;
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
        
        .notice {
            padding: 15px;
            background-color: #fafafa;
            border-left: 6px solid #7f7f84;
            margin-bottom: 10px;
            -webkit-box-shadow: 0 5px 8px -6px rgba(0,0,0,.2);
            -moz-box-shadow: 0 5px 8px -6px rgba(0,0,0,.2);
            box-shadow: 0 5px 8px -6px rgba(0,0,0,.2);
        }
        .notice-sm {
            padding: 10px;
            font-size: 80%;
        }
        .notice-lg {
            padding: 35px;
            font-size: large;
        }
        .notice-success {
            border-color: #80D651;
        }
        .notice-success>strong {
            color: #80D651;
        }
        .notice-info {
            border-color: #45ABCD;
        }
        .notice-info>strong {
            color: #45ABCD;
        }
        .notice-warning {
            border-color: #FEAF20;
        }
        .notice-warning>strong {
            color: #FEAF20;
        }
        .notice-danger {
            border-color: #d73814;
        }
        .notice-danger>strong {
            color: #d73814;
        }
          .ribbon-wrapper {
    width: 85px;
    height: 88px;
    overflow: hidden;
    position: absolute;
    top: -3px;
    right: -3px
}
.ribbon {
    font-size: 10px;
    color: #FFF;
    text-transform: uppercase;
    font-family: 'Montserrat Bold', 'Helvetica Neue', Helvetica, Arial, sans-serif;
    letter-spacing: .04em;
    line-height: 22px;
    text-align: center;
    text-shadow: 0 -1px 0 rgba(0, 0, 0, .4);
    -webkit-transform: rotate(45deg);
    -moz-transform: rotate(45deg);
    -ms-transform: rotate(45deg);
    -o-transform: rotate(45deg);
    transform: rotate(45deg);
    position: relative;
    padding: 7px 0;
    right: -11px;
    top: 10px;
    width: 120px;
    height: 30px;
    -webkit-box-shadow: 0 0 3px rgba(0, 0, 0, .3);
    box-shadow: 0 0 3px rgba(0, 0, 0, .3);
    background-color: #dedede;
    background-image: -webkit-linear-gradient(top, #ffffff 45%, #dedede 100%);
    background-image: -o-linear-gradient(top, #ffffff 45%, #dedede 100%);
    background-image: linear-gradient(to bottom, #ffffff 45%, #dedede 100%);
    background-repeat: repeat-x;
    filter: progid: DXImageTransform.Microsoft.gradient(startColorstr='#ffffffff', endColorstr='#ffdedede', GradientType=0)
}

.ribbon:before,
.ribbon:after {
    content: "";
    border-top: 5px solid #9e9e9e;
    border-left: 3px solid transparent;
    border-right: 3px solid transparent;
    position: absolute;
    bottom: -3px
}

.ribbon:before {
    left: 0
}

.ribbon:after {
    right: 0
}
.ribbon.blue {
    background-color: #1a8bbc;
    background-image: -webkit-linear-gradient(top, #177aa6 45%, #1a8bbc 100%);
    background-image: -o-linear-gradient(top, #177aa6 45%, #1a8bbc 100%);
    background-image: linear-gradient(to bottom, #177aa6 45%, #1a8bbc 100%);
    background-repeat: repeat-x;
    filter: progid: DXImageTransform.Microsoft.gradient(startColorstr='#177aa6', endColorstr='#ff1a8bbc', GradientType=0)
}

.ribbon.blue:before,
.ribbon.blue:after {
    border-top: 3px solid #115979
}
    </style>
        <section class="pricing py-5">
        <div class="container">
            @if($AllOk)
                <div class="notice notice-success">
                    <strong>Notificación: </strong>
                    <hr>
                    &emsp;{{$msg}}
                    <br>
                    &emsp;Fecha de vencimiento: <b> {{$fecha}}</b>
                </div>
            @endif
            @if($fechaLimit)
                <div class="notice notice-warning">
                    <strong>Notificación: </strong>
                    <hr>
                    &emsp;{{$msg}}
                    <br>
                    &emsp;Fecha de vencimiento: <b> {{$fecha}}</b>
                </div>
            @endif
            <div>
                <h1 class="display-4 text-center " style="font-weight: normal" >Planes</h1>
                <h4 class="display-5 text-center "> Selecciona el plan que más se ajuste a tu negocio</h4>

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
                            <a href="{{route('planes.verificar', 0)}}" class="btn btn-block btn-primary text-uppercase"><span class="small" style="font-weight: bolder">Síguelo usando</span></a>
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
                            <a class="btn-obtener-conf"><a href="{{route('planes.pagos',$valor = 35000)}}" class="btn btn-block btn-primary text-uppercase">¡OBTENER!</a>
                        </div>
                        <div class="card-footer">
                            <h6 class="text-center" style="color: green" >10% DESCUENTO por pago anual</h6>
                        </div>
                    </div>
                </div>
                <!-- Pro Tier -->
                <div class="col-lg-3 d-flex">
                    <div class="card">
                         <div class="ribbon-wrapper">
                            <div class="ribbon blue">¡PROMOCIÓN!</div>
                        </div>
                        <div class="card-body">
                            
                            <h5 class="card-title text-muted text-uppercase text-center">Pyme -<span cal style="color: #ec0a00; font-weight: normal; font-size: medium"> ¡POPULAR!</span></h5>
                           
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
                            <a class="btn-obtener-conf"><a href="{{route('planes.pagos',$valor = 60000)}}" class="btn btn-block btn-primary text-uppercase">¡OBTENER!</a>
                        </div>
                        <div class="card-footer">
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
                            <a class="btn-obtener-conf"><a href="{{route('planes.pagos',$valor = 90000)}}" class="btn btn-block btn-primary text-uppercase">¡OBTENER!</a>
                        </div>
                        <div class="card-footer">
                            <h6 class="text-center" style="color: green" >10% DESCUENTO por pago anual</h6>
                        </div>
                    </div>
                </div>
            </div>
                @if(!$personalPlan)
                    <br>
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
                @endif
            <hr>
            <div class=" text-center" style="display:block;margin:auto;">
                <img src="{{asset('images/dian.png')}}" alt="" width="250" height="70" style="">
                <img src="{{asset('images/cadena.png')}}" alt="" width="250" height="70">
            </div>
        </div>
    </section>
@endsection