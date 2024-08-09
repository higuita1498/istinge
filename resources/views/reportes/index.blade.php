@extends('layouts.app')


@section('content')
    @if(auth()->user()->modo_lectura())
        <div class="alert alert-warning text-left" role="alert">
            <h4 class="alert-heading text-uppercase">Integra Colombia: Suscripción Vencida</h4>
           <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
        </div>
    @else
    <div class="card-body">
        @include('layouts.notify')
        <div class="row">
            <div class="col-md-5 offset-md-1 reportes">
                <h3><i class="fas fa-chart-line"></i> Administrativos</h3>
                <ul class="list-report">
                    <li><a href="{{route('reportes.ventas')}}">Reporte de Facturas Pagadas</a></li>
                    <li><a href="{{route('reportes.facturasImpagas')}}">Reporte de Facturas Impagas</a></li>
                    <li><a href="{{route('reportes.facturasElectronicas')}}">Reporte de Facturas Electrónicas</a></li>
                    <li><a href="{{route('reportes.facturasEstandar')}}">Reporte de Facturas Estándar</a></li>
                    <li><a href="{{route('reportes.notasCredito')}}">Reporte de Notas de Crédito</a></li>
                    <li><a href="{{route('reportes.transacciones')}}" >Reporte de Transacciones</a></li>
                    <li><a href="{{route('reportes.cajas')}}" >Reporte de Cajas</a></li>
                    <li><a href="{{route('reportes.instalacion')}}" >Reporte de Contratos con Instalación</a></li>
                    <li><a href="{{route('reportes.radicados')}}" >Reporte de Radicados</a></li>
                    <li><a href="{{route('reportes.ivas')}}" >Reporte de ivas</a></li>
                    {{-- <li><a href="{{route('reportes.planes')}}" >Reporte de Planes</a></li> --}}
                </ul>
            </div>

                <div class="col-md-5 offset-md-1 reportes">
                    <h3><i class="fa fa-gavel" aria-hidden="true"></i> MINTIC COLOMBIA</h3>
                    <ul class="list-report">
                        <li><a href="{{route('reportes.mintic.1.2')}}">Reporte 1.2</a></li>
                        <li><a href="{{route('reportes.mintic.1.2')}}">Res.6333 T.1.1</a></li>
                        <li><a href="{{route('reportes.mintic.1.2')}}">Res.6333 T.1.2</a></li>
                        <li><a href="{{route('reportes.mintic.1.2')}}">Res.6333 T.1.3</a></li>
                        <li><a href="{{route('reportes.mintic.1.2')}}">Res.6755 Formato T.4.2</a></li>
                        <li><a href="{{route('reportes.mintic.1.2')}}">Res.6755 Formato T.4.3</a></li>
                        <li><a href="{{route('reportes.exogena')}}">Reporte Exógena</a></li>

                    </ul>
                </div>
                </div>

        <div class="col-md-5 offset-md-1 reportes">
            <h3><i class="fas fa-store-alt"></i> Puntos de Ventas</h3>
            <ul class="list-report">
                @if(auth()->user()->rol <> 8)
                <li><a href="{{route('reportes.recargas')}}" >Reporte de Recargas</a></li>
                <li><a href="{{route('reportes.puntoVenta')}}" >Reporte de Puntos de Ventas (Ganancias)</a></li>
                <li><a href="{{route('reportes.puntoVentaRecaudo')}}" >Reporte de Puntos de Ventas (Recaudos)</a></li>
                @endif
            </ul>
        </div>

        <div class="row">
            <div class="col-md-5 offset-md-1 reportes">
                <h3><i class="fas fa-chart-line"></i> Contables</h3>
                <ul class="list-report">
                    <li><a href="{{route('reportes.balance')}}">Reporte de Balances</a></li>
                </ul>
            </div>

        </div>

    </div>
    <div class="col-md-5 offset-md-1 reportes">
        <div class="col-md- offset-md-1 reportes">
            <h3><i class="fa fa-gavel" aria-hidden="true"></i> REPORTES PASARELA DE PAGO</h3>
            <ul class="list-report">
                <li><a href="{{route('reportes.mintic.1.2')}}">Reporte Pasarela Combo pay</a></li>
            </ul>
        </div>
    </div>
    <div class="affix"></div>
    @endif
@endsection
