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
            font-size: 12px;
            color: #000;
        }
        h4{
            font-weight: bold;
            text-align: center;
            margin: 0;font-size: 14px;
        }
        .small{
            font-size: 10px;line-height: 12px;    margin: 0;
        }
        .smalltd{
            font-size: 10px;line-height: 12px; padding-right: 2px;
        }
        .medium{
            font-size: 17px;line-height: 14px;    margin: 0;
        }
        a{
            color: #000;
            text-decoration: none;
        }
        th{
            background: #ccc;
        }
        td{
            padding-left: 2px;
        }
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
        td {
            border: 1px  solid #9e9b9b;
        }

        th {
            border: 1px  solid #ccc;
        }
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
        <div style="width: 20%; display: inline-block; vertical-align: top; text-align: center; ">
            <img src="{{asset('images/Empresas/Empresa'.$empresa->id.'/'.$empresa->logo)}}" alt="" style="width: 100%;">
        </div>
        <div style="width: 57%; text-align: center; display: inline-block;">
            <h4>{{$empresa->nombre}}</h4>
            <p style="line-height: 12px;">{{$empresa->tip_iden('mini')}} {{$empresa->nit}} - {{ $empresa->dv }}<br>
                {{$empresa->direccion}} <br>
                {{$empresa->telefono}}
                @if($empresa->web)
                    <br>{{$empresa->web}}
                @endif
                <br> <a href="mailto:{{$empresa->email}}" target="_top">{{$empresa->email}}</a>
            </p>

        </div>
        <div style="width: 20%; display: inline-block; text-align: left;    vertical-align: top;
    margin-top: 2%;">
            <p class="medium">@if($factura->tipo == 1)Factura de Venta @elseif($factura->tipo == 3) Cuenta de Cobro @endif</p>
            <h4 style="text-align: left; ">No. {{$factura->codigo}}</h4>
            <p class="small">{{$tipo}}</p>

        </div>
    </div>
    <div style="">
        <table border="1" class="titulo">
            <tr>
                <th width="10%" class="right smalltd">SEÑOR(ES)</th>
                <td colspan="3" style="border-top: 2px solid #ccc;">{{$factura->cliente()->nombre}}</td>
                <th width="22%" class="center" style="font-size: 8px"><b>FECHA DE EXPEDICIÓN (DD/MM/AA)</b></th>
            </tr>
            <tr>
                <th class="right smalltd" width="10%">DIRECCION</th>
                <td colspan="">{{$factura->cliente()->direccion}}</td>
                <th class="right" width="15%" style="padding-right: 2px;">{{$factura->cliente()->tip_iden('mini')}}</th>
                <td style="border-bottom: 2px solid #ccc;" width="20%" >{{$factura->cliente()->nit }}
                    @if($factura->cliente()->dv != null)
                        - {{$factura->cliente()->dv }}
                    @endif</td>
                <td class="center" style="    border-right: 2px solid #ccc;">{{date('d/m/Y', strtotime($factura->fecha))}}</td>
            </tr>
            <tr>
                <th class="right smalltd">CIUDAD</th>
                <td colspan="">{{$factura->cliente()->ciudad}}</td>
                <th class="right" style="padding-right: 2px;">FORMA DE PAGO</th>
                <td style="border-bottom: 2px solid #ccc;" >{{$factura->plazo()}}</td>
                <th class="center" style="font-size: 8px"><b>FECHA DE VENCIMIENTO (DD/MM/AA)</b></th>
            </tr>
            <tr>
                <th class="right smalltd">TELÉFONO</th>
                <td style="border-bottom: 2px solid #ccc;">{{$factura->cliente()->telefono1}}</td>
                <th class="right" style="padding-right: 2px;">EMAIL</th>
                <td style="border-bottom: 2px solid #ccc;" >{{$factura->cliente()->email}}</td>
                <td class="center" style="border-right: 2px solid #ccc; border-bottom: 2px solid #ccc;">{{date('d/m/Y', strtotime($factura->vencimiento))}}</td>
            </tr>

        </table>
    </div>


    <div style="margin-top: 2%;">
        <table border="0" class="desgloce" >
            <thead>
            <tr>
                <th style="padding: 3px;"  width="40%" class="center smalltd">Ítem</th>
                <th style="padding: 3px;" width="18%"class="center smalltd">Referencia</th>
                <th style="padding: 3px;" width="10%" class="center smalltd">Cantidad</th>
                <th style="padding: 3px;" width="10%" class="center smalltd">Precio</th>
                <th style="padding: 3px;" width="14%" class="center smalltd">Descuento</th>
                <th style="padding: 3px;" width="15%" class="center smalltd">Total</th>
            </tr>
            </thead>
            <tbody>
            @php $cont=0; @endphp
            @foreach($items as $item)

                @php $cont++; @endphp
                <tr>
                    <td class="left padding-left border_left @if($cont==$itemscount && $cont>6) border_bottom @endif">{{strtolower($item->producto())}} @if($item->descripcion) ({{strtolower($item->producto())}}) @endif</td>
                    <td class="center @if($cont==$itemscount && $cont>6) border_bottom @endif">{{strtolower($item->ref)}}</td>
                    <td class="center  @if($cont==$itemscount && $cont>6) border_bottom @endif">{{$item->cant}}</td>
                    <td class="right padding-right  @if($cont==$itemscount && $cont>6) border_bottom @endif">{{$empresa->moneda}}{{App\Funcion::ParsearAPI($item->precio, $empresa->id)}}</td>
                    <td class="center  @if($cont==$itemscount && $cont>6) border_bottom @endif">{{$item->desc?$item->desc:0}}%</td>
                    <td class="right padding-right border_right  @if($cont==$itemscount && $cont>6) border_bottom @endif">{{$empresa->moneda}}{{App\Funcion::ParsearAPI($item->total(), $empresa->id)}}</td>
                </tr>

            @endforeach
            @if($cont<7)
                @php $cont=7-$cont; @endphp
                @for($i=1; $i<=$cont; $i++)
                    <tr>
                        <td class="border_left @if($cont==$i) border_bottom @endif" style="height: 15px;"></td>
                        <td class=" @if($cont==$i) border_bottom @endif" style="height: 15px;"></td>
                        <td class=" @if($cont==$i) border_bottom @endif" style="height: 15px;"></td>
                        <td class=" @if($cont==$i) border_bottom @endif" style="height: 15px;"></td>
                        <td class=" @if($cont==$i) border_bottom @endif" style="height: 15px;"></td>
                        <td class="border_right @if($cont==$i) border_bottom @endif" style="height: 15px;"></td>
                    </tr>

                @endfor

            @endif
            </tbody>

            <tfoot>
            <tr class="foot">
                <th colspan="4" class="smalltd">{{$factura->facnotas}}</th>
                <td class="right">SubTotal</td>
                <td class="right padding-right">{{$empresa->moneda}}{{App\Funcion::ParsearAPI($factura->totalAPI($factura->empresa)->subtotal, $empresa->id)}}</td>
            </tr>
            @if($factura->totalAPI($factura->empresa)->descuento>0)
                <tr class="foot">
                    <td colspan="4" class="smalltd"></td>
                    <td class="right">Descuento</td>
                    <td class="right padding-right">{{$empresa->moneda}}{{App\Funcion::ParsearAPI($factura->totalAPI($factura->empresa)->descuento, $empresa->id)}} </td>
                </tr>
                <tr class="foot">
                    <td colspan="4" class="smalltd"></td>
                    <td class="right">SubTotal</td>
                    <td class="right padding-right">{{$empresa->moneda}}{{App\Funcion::ParsearAPI($factura->totalAPI($factura->empresa)->subsub, $empresa->id)}}</td>
                </tr>
            @endif
            @if($factura->totalAPI($factura->empresa)->imp)
                @foreach($factura->totalAPI($factura->empresa)->imp as $imp)
                    @if(isset($imp->total))
                        <tr class="foot">
                            <td colspan="4" class="smalltd"></td>
                            <td class="right">{{$imp->nombre}} ({{$imp->porcentaje}}%)</td>
                            <td class="right padding-right">{{$empresa->moneda}}{{App\Funcion::ParsearAPI($imp->total, $empresa->id)}}</td>
                        </tr>
                    @endif
                @endforeach
            @endif
            @foreach($retenciones as $retencion)

                <tr class="foot">
                    <td colspan="4" class="smalltd"></td>
                    <td class="right">{{$retencion->tipo()}}</td>
                    <td class="right padding-right">{{$retencion->retencion()->porcentaje}}%</td>
                </tr>
            @endforeach
            <tr class="foot">
                <td colspan="4"> </td>
                <th class="right padding-right">Total</th>
                <th class="right padding-right">{{$empresa->moneda}}{{App\Funcion::ParsearAPI($factura->totalAPI($factura->empresa)->total, $empresa->id)}} </th>
            </tr>
            </tfoot>


        </table>

        <p style="text-align: justify;" class="small">{{$resolucion->resolucion}}</p>
    </div>
    <div style="width: 70%; margin-top: 1%">
        <p style="text-align: justify;" class="small">{{$factura->term_cond}}</p>
        @if(isset($codqr))
            <div>
                <img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(200)->generate($codqr)) !!} ">
            </div>
        @endif
        <div style="padding-top: 8%; text-align: center;">
            <div style="display: inline-block; width: 45%; border-top: 1px solid #000;     margin-right: 10%;">
                <p class="small"> ELABORADO POR:{{$factura->vendedor()}}</p>
            </div>
            <div style="display: inline-block; width: 44%; border-top: 1px solid #000;">
                <p class="small"> ACEPTADA, FIRMA Y/O SELLO Y FECHA</p>
            </div>
        </div>
    </div>



    <div id="watermark">{{$factura->estatus==2?'ANULADA':''}}</div>
@endsection
