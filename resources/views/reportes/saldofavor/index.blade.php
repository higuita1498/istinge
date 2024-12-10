@extends('layouts.app')
@section('content')


    <form id="form-reporte">

        <div class="row card-description">
            <div class="form-group col-md-4" style=" padding-top: 24px;">
                <button type="button" id="exportar" class="btn btn-outline-success">Exportar a Excel</button>
            </div>
        </div>

        <input type="hidden" name="orderby"id="order_by"  value="2">
        <input type="hidden" name="order" id="order" value="1">
        <input type="hidden" id="form" value="form-reporte">

        <div class="row card-description">
            <div class="col-md-12 table-responsive">
                <table class="table table-striped table-hover " id="table-facturas">
                    <thead class="thead-dark">
                    <tr>
                        <th>Documento</th>
                        <th>Cliente </th>
                        <th>Total </th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($saldos_favor as $saldo)
                        <tr>
                            <td><a href="{{route('contactos.show',$saldo->id)}}" target="_blank">{{$saldo->nit}}</a></td>
                            <td><a href="{{route('contactos.show',$saldo->id)}}" target="_blank">{{$saldo->nombre}}  {{$saldo->apellidos()}}</a></td>
                            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($saldo->saldo_favor)}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot class="thead-dark">
                    <th  colspan="2" class="text-right">Total</th>
                    <th>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($total)}}</th>
                    </tfoot>

                </table>
                {!! $saldos_favor->render() !!}
            </div>
        </div>
    </form>
    <input type="hidden" id="urlgenerar" value="{{route('reportes.facturasEstandar')}}">
    <input type="hidden" id="urlexportar" value="{{route('exportar.saldosFavor')}}">
@endsection
