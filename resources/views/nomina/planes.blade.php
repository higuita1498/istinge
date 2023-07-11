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
        font-size: 0.85rem;
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
        -webkit-box-shadow: 0 5px 8px -6px rgba(0, 0, 0, .2);
        -moz-box-shadow: 0 5px 8px -6px rgba(0, 0, 0, .2);
        box-shadow: 0 5px 8px -6px rgba(0, 0, 0, .2);
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
        right: -3px;
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

    body>div.container-scroller>div>div>div.content-wrapper>div>div>div>div {
        display: none;
    }

    .plan {
        border: solid 1px #107468 !important;
        color: #107468;
        padding: 1rem !important;
    }
</style>

<section class="pricing py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-md-8 offset-md-2">
                <div class="card mb-5 mb-lg-0 plan">
                    <div class="card-body text-center">
                        <h4>Su empresa posee el plan <strong>{{ $plan }}<br>{{ $rango ?? '' }}<br>@php echo $estado ?? ''; @endphp</strong></h4>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <h1 class="display-4 text-center font-weight-normal">Planes Nómina Electrónica</h1>
            <h4 class="display-5 text-center"> Selecciona el plan que más se ajuste a tu negocio</h4>
        </div>

        <hr>

        <div class="row">
            <div class="col-lg-3 d-flex">
                <div class="card mb-5 mb-lg-0">
                    <div class="card-body">
                        <h5 class="card-title text-muted text-uppercase text-center">Nómina Electrónica Básico</h5>
                        <h6 class="card-price text-center">15.000<span class="period">COP/MES</span></h6>
                        <hr>
                        <ul class="fa-ul">
                            <li><span class="fa-li"><i class="fas fa-users"></i></span>De 1 a 6 empleados</li>
                        </ul>
                        <hr>
                        @if($personal <= 6) <a href="{{route('nomina.plan_pago',$valor = 15000)}}" class="btn btn-block btn-secondary text-uppercase">¡OBTENER!</a>
                        @else
                        <a href="javascript:noDisponible();" class="btn btn-block btn-secondary text-uppercase">NO DISPONIBLE</a>
                        @endif
                    </div>
                </div>
            </div>
            <!-- Plus Tier -->
            <div class="col-lg-3 d-flex">
                <div class="card mb-5 mb-lg-0">
                    <div class="card-body">
                        <h5 class="card-title text-muted text-uppercase text-center">Nómina Electrónica Emprendedor</h5>
                        <h6 class="card-price text-center">20.000<span class="period">COP/MES</span></h6>
                        <hr>
                        <ul class="fa-ul">
                            <li><span class="fa-li"><i class="fas fa-users"></i></span>De 7 a 15 empleados</li>
                        </ul>
                        <hr>
                        @if($personal <= 15) <a class="btn-obtener-conf"><a href="{{route('nomina.plan_pago',$valor = 20000)}}" class="btn btn-block btn-secondary text-uppercase">¡OBTENER!</a>
                        @else
                        <a href="javascript:;" class="btn btn-block btn-secondary text-uppercase">NO DISPONIBLE</a>
                        @endif
                    </div>
                </div>
            </div>
            <!-- Pro Tier -->
            <div class="col-lg-3 d-flex">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-muted text-uppercase text-center">Nómina Electrónica PYME</h5>

                        <h6 class="card-price text-center">30.000<span class="period">COP/MES</span></h6>
                        <hr>
                        <ul class="fa-ul">
                            <li><span class="fa-li"><i class="fas fa-users"></i></span>De 16 a 25 empleados</li>
                        </ul>
                        <hr>
                        @if($personal <= 25) <a class="btn-obtener-conf"><a href="{{route('nomina.plan_pago',$valor = 30000)}}" class="btn btn-block btn-secondary text-uppercase">¡OBTENER!</a>
                        @else
                        <a href="javascript:;" class="btn btn-block btn-secondary text-uppercase">NO DISPONIBLE</a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-lg-3 d-flex">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title text-muted text-uppercase text-center">Nómina Electrónica Avanzado</h5>
                        <h6 class="card-price text-center">40.000<span class="period">COP/MES</span></h6>
                        <hr>
                        <ul class="fa-ul">
                            <li><span class="fa-li"><i class="fas fa-users"></i></span>De 26 a 50 empleados</li>
                        </ul>
                        <hr>
                        @if($personal <= 50) <a class="btn-obtener-conf"><a href="{{route('nomina.plan_pago',$valor = 40000)}}" class="btn btn-block btn-secondary text-uppercase">¡OBTENER!</a>
                        @else
                        <a href="javascript:;" class="btn btn-block btn-secondary text-uppercase">NO DISPONIBLE</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card mb-5 mb-lg-0">
                    <div class="card-body text-center">
                        <p>Si tu empresa no se acomoda a ninguno de los planes que ofrecemos, nosotros podemos personalizar tu plan y llegar a un acuerdo.</p>
                        <div class="separador"></div>
                        <center><a style="width:38%;" href="//api.whatsapp.com/send?phone=573226501735&amp;text=Hola%20quiero%20personalizar%20un%20plan%20de%20nomina%20electronica%20para%20mi%20empresa" target="_blank" class="btn btn-block btn-secondary text-uppercase"><span class="small" style="font-weight: bolder">Contáctanos</span></a></center>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
<script>
    function noDisponible() {
        Swal.fire({
            position: 'center',
            type: 'error',
            text: 'Su empresa posee un personal mayor al que le ofrece este plan',
            title: 'PLAN NO DISPONIBLE',
            showConfirmButton: true,
            timer: 5000
        });
    }
</script>
@endsection