@extends('layouts.app')


@section('style')
<style>
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

    .form-control.form-control-sm {
        padding: 0px;
    }
</style>
@endsection


@section('content')

{{-- @include('nomina.tips.serie-base', ['pasos' => \collect([17, 18])->diff(auth()->user()->guiasVistas()->keyBy('nro_tip')->keys())->all()]) --}}


<div class="row">

    @if(Session::has('error'))
    <div class="col-12">
        <div class="alert alert-danger">
            {{Session::get('error')}}
        </div>
    </div>

    @elseif(Session::has('success'))
    <div class="col-12">
        <div class="alert alert-success">
            {{Session::get('success')}}
        </div>
    </div>
    @endif


    <div class="col-12 col-md-6 px-3">
        @include('nomina.includes.periodo', ['mensajePeriodo' => $mensajePeriodo])
    </div>

    <div class="col-12 col-md-6 px-3">
        <div class="notice">
            <strong style="text-decoration: line-through"> 1 - Ingresar novedades</strong>
            <hr>
            <strong> 2 - Pagar y generar reportes</strong>
        </div>
    </div>

</div>

@if($request->ajuste)
<a href="{{ route('nomina.ajustar', ['periodo' => $request->periodo, 'year' => $request->year, 'persona' => $request->persona]) }}" style="margin-left:10px"> <i class="fas fa-chevron-left"></i> Regresar a editar nomina </a>
@else
<a href="{{ route('nomina.liquidar', ['periodo' => $request->periodo, 'year' => $request->year, 'editar' => 'true', $request->periodo_quincenal, 'periodo_quincenal' => $request->periodo_quincenal]) }}" style="margin-left:10px"> <i class="fas fa-chevron-left"></i> Regresar a editar nomina </a>
@endif

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h4>¡La nómina ha sido generada satisfactoriamente!</h4>
                <p>Ahora puedes enviarle la interfaz contable a tu contador, descarga el archivo para pagar en tu banco y realiza el pago de la seguridad social.
            </div>
        </div>
    </div>
</div>

<div class="row">

    <div class="col-6">
        <div class="card shadow-sm border-0 bg-light h-100 w-100">
            <div class="card-body p-2">
                <h5 class="card-title text-center" id="colillas-pago">Colillas de pago</h5>

                <ul style="list-style: none;">
                    <li><a href="{{ route('nomina.agrupadas', ['periodo' => $request->periodo, 'year'=> $request->year, 'tipo' => $tipo]) }}" target="_blank"> <i class="fas fa-grip-vertical"></i> <span>Agrupadas</span></li> </a>
                    <li><a href="{{ route('nomina.individuales', ['periodo' => $request->periodo, 'year'=> $request->year, 'tipo' => $tipo]) }}" target="_blank"> <i class="far fa-user"></i> <span>Individuales</span></li> </a>
                    <li><a href="{{ route('liquidar-nomina.correo', ['periodo' => $request->periodo, 'year' => $request->year, 'tipo' => $tipo]) }}"> <i class="far fa-envelope"></i> <span>Notificar pago via email</span></li> </a>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-6">
        <div class="card shadow-sm border-0 bg-light h-100 w-100">
            <div class="card-body p-2">
                <h5 class="card-title text-center" id="g-reportes">Reportes</h5>
                <ul style="list-style: none;">
                    <li><a href="{{ route('nomina.resumenExcel', ['periodo' => $request->periodo, 'year'=> $request->year, 'tipo' => $tipo]) }}"> <i class="far fa-file-alt"></i> <span>Resumen nomina</span></li> </a>
                    <li><a href="{{ route('nomina.exportar', ['periodo' => $request->periodo, 'year'=> $request->year, 'tipo' => $tipo]) }}"> <i class="far fa-calendar"></i> <span>Reporte novedades</span></li> </a>
                    {{-- <li><a href="#"> <i class="far fa-window-maximize"></i> <span>Interfaz contable</span></li> </a> --}}
                </ul>
            </div>
        </div>
    </div>

    <!-- <div class="col-4">
        <div class="card w-100">
            {{-- <div class="card-body p-2 border-right border-top">
                <h5 class="card-title">Pagos</h5>
                <ul style="list-style: none;">
                    <li><a href="#"> <i class="far fa-file"></i> <span>Pagar seguridad social</span></li> </a>
                    <li><a href="#"> <i class="fas fa-file"></i> <span>Archivo pago seguridad social</span></li> </a>
                    <li><a href="#"> <i class="fas fa-university"></i> <span>Archivo pago en banco</span></li> </a>
                </ul>
            </div> --}}
        </div>
    </div> -->

</div>



@endsection

@section('scripts')
<script type="text/javascript">
    setTimeout(function() {
        $('.alert').hide();
    }, 5000);


    $(document).ready(function() {


        firstTip = $('.tour-tips').first().attr('nro_tip');

        if (firstTip) {
            nuevoTip(firstTip);
        }

    });
</script>
@endsection
