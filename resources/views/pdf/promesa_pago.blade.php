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

    <div style="width: 100%;height:auto;">
        <div style="width: 30%; display: inline-block; vertical-align: top; text-align: center; height:100px !important;  margin-bottom: 2%; overflow:hidden; text-align:left;">
            <img src="{{asset('images/Empresas/Empresa'.Auth::user()->empresa.'/'.Auth::user()->empresa()->logo)}}" alt="" style="max-width: 100%; max-height:100px; object-fit:contain; text-align:left;">
        </div>
        <div style="width: 40%; text-align: center; display: inline-block;  height:auto; margin-right:45px;">
            <h4>{{Auth::user()->empresa()->nombre}}</h4>
            <p style="line-height: 12px;">{{Auth::user()->empresa()->tip_iden('mini')}} {{Auth::user()->empresa()->nit}} @if(Auth::user()->empresa()->dv != null || Auth::user()->empresa()->dv === 0) - {{Auth::user()->empresa()->dv}} @endif<br>
                {{Auth::user()->empresa()->direccion}} <br>
                {{Auth::user()->empresa()->telefono}}
                @if(Auth::user()->empresa()->web)
                    <br>{{Auth::user()->empresa()->web}}
                @endif
                <br> <a href="mailto:{{Auth::user()->empresa()->email}}" target="_top">{{Auth::user()->empresa()->email}}</a>
            </p>

        </div>
        <div style="width: 21%; display: inline-block; text-align: left; vertical-align: top;margin-top: 2%;">
            <p class="medium">Promesa de Pago</p>
            <h4 style="text-align: left; ">No. #{{$promesa->nro}}</h4>
        </div>
    </div>
    
    <div style="">
        <table border="0" class="titulo">
            <tr>
                <th width="10%" class="right smalltd">SEÑOR(ES)</th>
                <td colspan="3" style="border-top: 2px solid #ccc;">{{$promesa->cliente()->nombre}} {{$promesa->cliente()->apellidos()}}</td>
                <th width="22%" class="center" style="font-size: 8px"><b>FECHA DE PAGO<br>(DD/MM/AA)</b></th>
            </tr>
            <tr>
                <th class="right smalltd" width="10%">DIRECCION</th>
                <td colspan="">{{$promesa->cliente()->direccion}}</td>
                <th class="right smalltd" width="15%" style="padding-right: 2px;">{{$promesa->cliente()->tip_iden('mini')}}</th>
                <td style="border-bottom: 2px solid #ccc;" width="20%" >{{$promesa->cliente()->nit }}
                    @if($promesa->cliente()->dv != null)
                        - {{$promesa->cliente()->dv }}
                    @endif</td>
                <td class="center" style="border-right: 2px solid #ccc;">{{Carbon\Carbon::parse($promesa->fecha)->format('d/m/Y')}}</td>
                
            </tr>
            <tr>
                <th class="right smalltd">CIUDAD</th>
                <td colspan="">{{$promesa->cliente()->municipio()->nombre}}</td>
                <th class="right" style="padding-right: 2px; font-size: 9px">DÍAS PARA PAGAR</th>
                <td style="border-bottom: 2px solid #ccc;" >{{\App\Funcion::diffDates($promesa->vencimiento,$promesa->fecha)}}</td>
                <th class="center" style="font-size: 8px"><b>FECHA DE PROMESA DE PAGO (DD/MM/AA)</b></th>
            </tr>
            <tr>
                <th class="right smalltd">CELULAR</th>
                <td style="border-bottom: 2px solid #ccc;">{{$promesa->cliente()->celular}}</td>
                <th class="right smalltd" style="padding-right: 2px;">EMAIL</th>
                <td style="border-bottom: 2px solid #ccc;" >{{$promesa->cliente()->email}}</td>
                <td class="center" style="border-right: 2px solid #ccc; border-bottom: 2px solid #ccc;">{{date('d/m/Y', strtotime($promesa->vencimiento))}}</td>
            </tr>
        </table>
    </div>


    <div style="margin-top: 2%;">
        <table border="0" class="desgloce" >
            <thead>
                <tr>
                    <th width="70%" colspan="2" style="padding: 3px;" class="center smalltd">Concepto</th>
                    <th style="padding: 3px;" width="15%" class="center smalltd">Valor</th>
                </tr>
            </thead>
            <tbody>
                @php $cont=0; @endphp
                @if($factura)
                @php $cont++; @endphp
                    <tr>
                        <td colspan="2" class="left padding-left border_left @if($cont==$itemscount && $cont>6) border_bottom @endif">
                            Promesa de pago a factura N° {{$factura->codigo}}
                        </td>
                        <td class="center padding-right border_right  @if($cont==$itemscount && $cont>6) border_bottom @endif">
                            {{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->total()->total)}}
                        </td>
                    </tr>
                @endif

                @if($cont<7)
                @php $cont=7-$cont; @endphp
                    @for($i=1; $i<=$cont; $i++)
                        <tr>
                            <td colspan="2" class="border_left @if($cont==$i) border_bottom @endif" style="height: 15px;"></td>
                            <td class="border_right @if($cont==$i) border_bottom @endif" style="height: 15px;"></td>
                        </tr>

                    @endfor

                @endif

            </tbody>
        </table>
    </div>
    
    <div style="width: 70%; margin-top: 1%">
        <div style="padding-top: 8%; text-align: center;">
            <div style="display: inline-block; width: 45%; border-top: 1px solid #000;     margin-right: 10%;">
                <p class="small"> ELABORADO POR: {{$promesa->usuario()->nombres}}</p>
            </div>
            <div style="display: inline-block; width: 44%; border-top: 1px solid #000;">
                <p class="small"> ACEPTADA, FIRMA Y/O SELLO Y FECHA</p>
            </div>
        </div>
    </div>
@endsection
