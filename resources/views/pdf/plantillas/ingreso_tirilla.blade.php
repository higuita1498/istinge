@extends('layouts.pdf')

@section('content')
    <style type="text/css">
        /**
        * Define the width, height, margins and position of the watermark.
        **/#watermark {
            position: fixed; top: 25%;
            width: 100%; text-align:
                    center; opacity: .6;
            transform: rotate(-30deg);
            transform-origin: 50% 50%;
            z-index: 1000;
            font-size: 130px;
            color: #a5a5a5;
        }

        body{
            font-family: Helvetica, sans-serif;
            font-size: 13px;
            color: #000;
        }
        h4{
            font-weight: bold;m
            text-align: center;
            margin: 0;font-size: 14px;
        }
        .small{
            font-size: 13px;line-height: 12px;    margin: 0;
        }
        .smalltd{
            font-size: 13px;line-height: 12px; padding-right: 2px;
        }
        .medium{
            font-size: 20px;line-height: 14px;    margin: 0;
        }
        a{
            color: #000;
            text-decoration: none;
        }
       /* th{
            background: #ccc;
        }
        td{
            padding-left: 2px;
        }*/
        .center
        {
            text-align: center;
        }
        .right
        {
            text-align: right;
        }
        .left
        {
            text-align: left;
        }


        .titulo{
            width: 100%;
            border-collapse: collapse;
            border-radius: 0.4em;
            overflow: hidden;
        }
      /*  td {
            border: 1px  solid #9e9b9b;
        }

        th {
            border: 1px  solid #ccc;
        }*/
        .desgloce{
            width: 100%;
            overflow: hidden;
            border-collapse: collapse;
            border-top-left-radius: 0.4em;
            border-top-right-radius: 0.4em;
        }
        .desgloce td{
            padding-top: 3px;
            border-left: 2px solid #fff;
            border-top: 2px solid #fff;
            border-bottom: 2px solid #fff;
            border-right: 2px solid #ccc;
        }
        .foot td{
            padding-top: 3px;
            border: 1px solid #fff;
            padding-right: 1%;
        }
        .foot th{
            padding: 2px;
            border-radius: unset;
        }
        .border_left{
            border-left: 3px solid #ccc !important;
        }
        .border_bottom{
            border-bottom: 5px solid #ccc !important;
        }
        .border_right{
            border-right: 3px solid #ccc !important;
        }
        .padding-right{
            padding-right:1% !important;
        }
        .padding-left{
            padding-left: 1%;
        }

    </style>
    <div style="width: 100%;">
        <div style="width: 100%; text-align: center; display: inline-block;">
            <h4>{{Auth::user()->empresa()->nombre}}</h4>
            <p style="line-height: 15px;font-size: 12px;">{{Auth::user()->empresa()->tip_iden('mini')}} {{Auth::user()->empresa()->nit}} <br>
                {{Auth::user()->empresa()->direccion}} <br>
                {{Auth::user()->empresa()->telefono}}
                @if(Auth::user()->empresa()->web)
                    <br>{{Auth::user()->empresa()->web}}
                @endif
                <br> <a href="" target="_top">{{Auth::user()->empresa()->email}}</a>
            </p>
        </div>
    </div>
    <div style="width: 100%;">
        <div style="width: 100%; text-align: center; display: inline-block;">
            Señor(es): {{$ingreso->cliente()->nombre}} {{$ingreso->cliente()->apellidos()}}<br>
            @if($ingreso->cliente()->direccion) Dirección: {{$ingreso->cliente()->direccion}}<br>@endif
            @if($ingreso->cliente()->ciudad) Ciudad: {{$ingreso->cliente()->ciudad}}<br>@endif
            @if($ingreso->cliente()->telefono1) Teléfono: {{$ingreso->cliente()->telefono1}}<br>@endif
            @if($ingreso->cliente()->nit) {{ $ingreso->cliente()->tip_iden('mini')}}: {{$ingreso->cliente()->nit}}<br>@endif<br>
        </div>
    </div>

    <div style="width: 100%; text-align: center; display: inline-block;">
        @if($ingreso->tipo == 1 || $ingreso->tipo == 2) Ingreso: @elseif($ingreso->tipo == 3) Cuenta de Cobro: @endif No. {{$ingreso->nro}}<br>
        Fecha Expedición: {{date('d/m/Y', strtotime($ingreso->fecha))}}<br>
        Fecha Vencimiento: {{date('d/m/Y', strtotime($ingreso->ingresofactura()->factura()->vencimiento))}}<br>
        Estado: @if($ingreso->ingresofactura()->factura()->estatus == 0) Cerrada @endif @if($ingreso->ingresofactura()->factura()->estatus == 1) Abierta @endif @if($ingreso->ingresofactura()->factura()->estatus == 2) Anulada @endif<br><br>

        Recibo de Caja: No. {{ $ingreso->nro }}<br>
        Fecha del Pago: {{ date('d/m/Y', strtotime($ingreso->fecha)) }}<br>
        Cuenta: {{ $ingreso->cuenta()->nombre }}<br>
        Método de Pago: {{ $ingreso->metodo_pago() }}<br>
        Periodo: {{$ingreso->ingresofactura()->factura()->periodoCobradoTexto()}}<br>
        @if($ingreso->notas) Notas: {{ $ingreso->notas }} @endif
    </div>

    <br>
    <div style="width: 100%; text-align: center; display: inline-block; border-top: solid 1px #000; margin-top: 10px;">
        <table style="width: 100%;text-align: center;">
            <thead>
                <tr>
                    <th style="width: 70%;">Ítem</th>
                    <th style="width: 30%;">Valor</th>
                </tr>
            </thead>
            <tbody>
            @php $totalApagar = 0; @endphp
            @foreach($items as $item)
             @php $totalApagar=$totalApagar+$item->precio; @endphp
                <tr>
                    <td>{{$item->ref}}</td>
                    <td>{{$empresa->moneda}}{{App\Funcion::Parsear($item->precio)}}</td>
                </tr>
            @endforeach

            <!-- calculando impuesto -->
            @foreach($items as $item)
                @if($item->impuesto != 0)
                @php
                $totalApagar=$totalApagar + ($item->impuesto * $item->precio) / 100 ;
                @endphp
                <tr>
                    <td>IVA {{round($item->impuesto)}} %</td>
                    <td>{{$empresa->moneda}}{{App\Funcion::Parsear(($item->impuesto * $item->precio) / 100 )}}</td>
                </tr>
                @endif
            @endforeach


            </tbody>
        </table>
    </div>
    <br>
    <br>

    <div  style="width: 100%; text-align: center; display: inline-block; padding: 5px 0 5px 5px; margin-bottom: 5px;">
        <table style="width: 100%;">
            <tbody>
                <!--<tr>-->
                <!--    <td style="width: 70%;">Subtotal:</td>-->
                <!--    <td style="width: 30%;text-align: center;">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($ingreso->total()->subtotal)}}</td>-->
                <!--</tr>-->
                @if($ingreso->total()->imp)
                    @foreach($ingreso->total()->imp as $imp)
                        @if(isset($imp->total))
                            <tr>
                                <td style="width: 70%;">{{$imp->nombre}} ({{$imp->porcentaje}}%)</td>
                                <td style="width: 30%;">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($imp->total)}}</td>
                            </tr>
                        @endif
                    @endforeach
                @endif
                <tr>
                    <td style="width: 70%;">Monto a Pagar:</td>
                    <td style="width: 30%;text-align: center;">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($totalApagar)}} </td>
                </tr>
                <tr>
                    <td style="width: 70%;">Monto Pagado:</td>
                    <td style="width: 30%;text-align: center;">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($ingreso->pago())}} </td>
                </tr>
                @if($ingreso->total()->total - $ingreso->pago() > 0)
                <tr>
                    <td style="width: 70%;">Monto Pendiente:</td>
                    <td style="width: 30%;text-align: center;">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($ingreso->pago() - $ingreso->pagado())}} </td>
                </tr>
                @endif
                 @if($ingreso->valor_anticipo > 0)
                <tr>
                    <td style="width: 70%;">Saldo a favor generado:</td>
                    <td style="width: 30%;text-align: center;">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($ingreso->valor_anticipo)}} </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    <br>

    <div style="width: 100%; text-align: center; display: inline-block;">
        <table  style="width: 100%;">
            <tbody>
                @if(isset($resolucion->resolucion))
                <tr>
                    <td style="text-align: center;">RESOLUCIÓN DIAN #{{$resolucion->resolucion}}<br>RANGO DEL {{$resolucion->inicioverdadero}} HASTA {{$resolucion->final}}.</td>
                </tr>
                @endif
                <tr>
                    <td style="text-align: center;">INTEGRA S.A.S</td>
                </tr>
                <tr>
                    <td style="text-align: center;"><b>TIRILLA IMPRESA EL {{ date('d/m/Y') }}</b></td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection
