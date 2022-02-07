@extends('layouts.app')


@section('content')
    <div class="card-body">
        @include('layouts.notify')
        <div class="row">
            <div class="col-md-5 offset-md-1 reportes">
                <h3><i class="fas fa-chart-line"></i> Administrativos</h3>
                <ul class="list-report">
                    <li><a href="{{route('reportes.ventas')}}">Reporte de Facturas Pagadas</a></li>
                    <li><a href="{{route('reportes.facturasImpagas')}}">Reporte de Facturas Impagas</a></li>
                    <li><a href="{{route('reportes.transacciones')}}" >Reporte de Transacciones</a></li>
                    <li><a href="{{route('reportes.cajas')}}" >Reporte de Cajas</a></li>
                    <li><a href="{{route('reportes.instalacion')}}" >Reporte de Contratos con Instalación</a></li>
                    <li><a href="{{route('reportes.radicados')}}" >Reporte de Radicados</a></li>
                </ul>
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
        </div>
    </div>
    <div class="affix"></div>
@endsection
