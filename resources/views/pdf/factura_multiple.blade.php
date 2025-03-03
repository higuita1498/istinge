@extends('layouts.pdf')

@section('content')
    <style type="text/css">
        /**
        * Define the width, height, margins and position of the watermark.
        **/#watermark {
            position: fixed; top: 25%;
            width: 100%; text-align:
            center; opacity: .6;
            transform: rotate(-30deg);T
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
        .divheader-pr{
            width: 100%;
            height:auto;
            border: 1px solid {{$empresa->color}};
            background-color:#fff;
            border-radius:5px;
            justify-content:center;
            padding-top: 15px;
            margin-bottom: 2px;
            color:{{$empresa->color}};
        }

        .divheader-pr a{
            color:{{$empresa->color}};
        }

        .divheader-datoscli{
            width: 18%;
            border: 1px solid {{$empresa->color}};
            background-color: {{$empresa->color}};
            border-radius: 5px;
            justify-content: center;
            margin-bottom: 7px;
            margin-top: 7px;
            padding-left: 20px;
            padding-right: 20px;
            color:#fff;
        }

        .divheader-datoscli p{
            margin-top: 5px;
            margin-bottom: 5px;
        }

        .titulo tr td{
            /*border:1px solid #ccc;*/
            border-top: 2px solid #ccc;
            border-bottom: 2px solid #ccc;
        }

        .divheader-datosfact{
            width: 18%;
            border: 1px solid {{$empresa->color}};
            background-color: {{$empresa->color}};
            border-radius: 5px;
            justify-content: center;
            margin-top:1px;
            margin-bottom: 1px;
            padding-left: 20px;
            padding-right: 20px;
            color:#fff;
        }

        .divheader-datosfact p, .divheader-nota p, .divheader-estadocuenta p{
            margin-top: 5px;
            margin-bottom: 5px;
        }

        .divheader-nota{
            width: 9%;
            border: 1px solid {{$empresa->color}};
            background-color: {{$empresa->color}};
            border-radius: 5px;
            justify-content: center;
            margin-bottom: 2px;
            padding-left: 20px;
            padding-right: 0px;
            color:#fff;
        }

        .nota-content, .div-content-border{
            border:1px solid {{$empresa->color}}; border-radius:5px;margin-top:5px;
        }
        .nota-content p{
            text-align:justify;
            margin:10px;
        }
        .divheader-estadocuenta{
            width: 20%;
            border: 1px solid {{$empresa->color}};
            background-color: {{$empresa->color}};
            border-radius: 5px;
            /*margin-bottom: 7px;*/
            /*margin-top: 7px;*/
            padding-left: 20px;
            padding-right: 0px;
            color:#fff;
        }

        .tr-estadocuenta > td, .tr-estadocuenta-precio > td{
            width: 20%;
            list-style: none;
            border: 0px;

        }

        .tr-estadocuenta > td li{
           padding:15px;
           border-radius:5px;
           height:35px;
           text-align:center;
        }

        .tr-estadocuenta-precio >  td li{
            text-align:center;
            border:1px solid #ccc;
        }

        .tr-mainvalorreconexion > td{
            list-style: none;
            border: 0px;
            color:#fff;
        }

        .tr-mainvalorreconexion > td li{
            background-color:{{$empresa->color}};
            border-radius:5px;
            padding: 10px;
            text-align: center;
        }

        .table-noborder{
            border:none;
        }

        .no-border{
            border:none;
        }

        .border-tdblue{
            border: 1px solid {{$empresa->color}};
            border-radius:5px;
        }

        .margin-docpdf{
            width:100%; position:relative; margin-bottom:7px;margin-top:7px;
        }

        .qr-table td:first-child{
            border: 1px solid {{$empresa->color}};
            border-radius:5px;
        }

        .qr-table td:nth-child(0n+2), .qr-table td:last-child{
            border: none;
        }

        .td-qrback{
            background-color:{{$empresa->color}};
            text-align:center;
            border-radius:5px;
        }

        .tableinterna td{
            border-radius: 5px;
        }

        .tr-estadocuenta.mi-clausula > td li{
            height:70px;
            text-align:center;
        }

        .miclausula-li > td li{
            border-radius: 5px;
            border: 1px solid {{$empresa->color}};
        }

        .tr-meses{
            text-align:center;
            background-color:#ccc;
        }

        .tr-precios{
            text-align:center;
        }
        .tr-estadocuenta > td li{
            color:#fff;
        }

        .imgwifi{
            width:80px;
        }
        .d-none{
            display: none;
        }
    </style>


    @foreach($facturas as $key => $factura)
        <div class="divheader-pr">
            <div style="width: 30%; display: inline-block; vertical-align: top; text-align: center; height:100px !important;  margin-top: 2%; overflow:hidden; text-align:center;">
                @if(env('APP_ENV')=='local')
                    <img src="{{ public_path('images/Empresas/Empresa'.$empresa->id.'/'.$empresa->logo) }}" alt="" style="max-width: 100%; max-height:100px; object-fit:contain; text-align:left;">
                @else
                    <img src="{{ asset('images/Empresas/Empresa'.$empresa->id.'/'.$empresa->logo) }}" alt="" style="max-width: 100%; max-height:100px; object-fit:contain; text-align:left;">
                @endif
            </div>
            <div style="width: 40%; text-align: center; display: inline-block;  height:auto; margin-right:45px;margin-top: .5%;">
                <br><br>
                <h4>{{$empresa->nombre}}</h4>
                <p style="line-height: 12px;">{{$empresa->tip_iden('mini')}} {{$empresa->nit}} @if($empresa->dv != null || $empresa->dv === 0) - {{$empresa->dv}} @endif<br>
                    {{$empresa->direccion}} <br>
                    {{$empresa->telefono}}
                    @if($empresa->web)
                        <br>{{$empresa->web}}
                    @endif
                    <br> <a href="mailto:{{$empresa->email}}" target="_top">{{$empresa->email}}</a>
                </p>
            </div>
            <div style="width: 21%; display: inline-block; text-align: left; vertical-align: top;margin-top: 2%;">
                <table style="border:none;width:100%;height:auto;">
                    <tr>
                        @if(env('APP_ENV')=='local')
                            <img class="imgwifi" src="{{public_path('images/wifi.png')}}" alt="">
                        @else
                            <img class="imgwifi" src="{{asset('images/wifi.png')}}" alt="">
                        @endif
                    </tr>
                </table>
            </div>
        </div>

        <div class="margin-docpdf">
            <table style="width:100%;margin:5px 0px;margin-left: -3px;">
                <tbody>
                <tr>
                    <td style="width:14%;border:none;">
                        <div style="background-color:{{$empresa->color}};text-align:center;border-radius:5px;height:16px;padding:5px;text-align:left;color:#fff;">
                            DATOS DEL CLIENTE
                        </div>
                    </td>
                    @if($factura->contratos() != false)
                        <td style="width:27%;border:none;padding-left:30%;">
                            <div style="background-color:{{$empresa->color}};text-align:center;border-radius:5px;height:16px;padding:5px;text-align:left;color:#fff;">
                                CONTRATO(S)
                            </div>
                        </td>
                        <td style="border:1px solid {{$empresa->color}};text-align:center;border-radius:5px;width:18%;">
                            @foreach ( $factura->contratos() as $detalleContrato )
                                No. {{$detalleContrato->contrato_nro}}
                            @endforeach
                        </td>
                    @endif
                </tr>
                </tbody>
            </table>
        </div>


        <div style="">
            <table border="0" class="titulo">
                <tr>
                    <th width="23.5%" class="right smalltd">NOMBRE</th>
                    <td colspan="1" style="">{{$factura->cliente()->nombre}} {{$factura->cliente()->apellidos()}}</td>
                </tr>
                <tr>
                    <th class="right smalltd">NIT O C.C</th>
                    <td style="border-bottom: 2px solid #ccc;">{{$factura->cliente()->nit}}</td>
                </tr>
                <tr>
                    <th class="right smalltd" width="10%">CELULAR</th>
                    <td colspan="">{{$factura->cliente()->celular}}</td>
                </tr>
                <tr>
                    <th class="right smalltd" width="10%">DIRECCION</th>
                    <td colspan="">{{isset($factura->contract()->address_street) ? $factura->contract()->address_street : $factura->cliente()->direccion}}</td>
                </tr>
                <tr>
                    <th class="right smalltd" width="10%">BARRIO</th>
                    <td colspan="">{{$factura->cliente()->barrio_id != null ? $factura->cliente()->barrio()->nombre : $factura->cliente()->barrio}}</td>
                </tr>
                <tr>
                    <th class="right smalltd">CIUDAD/DEP</th>
                    <td colspan="">{{$factura->cliente()->municipio()->nombre}}</td>
                </tr>
                <tr>
                    <th class="right smalltd" width="10%">EMAIL</th>
                    <td colspan="">{{$factura->cliente()->email}}</td>
                </tr>
                <tr>
                    <th class="right smalltd" width="10%">ORDEN DE COMPRA</th>
                    <td colspan="">{{$factura->ordencompra}}</td>
                </tr>
                <tr>
                    <th class="right smalltd" width="10%">ORDEN DE SERVICIO</th>
                    <td colspan="">{{$factura->ordenservicio}}</td>
                </tr>
                <tr>
                    <th class="right smalltd" width="10%">PLAZO</th>
                    <td colspan="">
                        @if($factura->plazo == 31)
                            {{$factura->plazos()->nombre}}
                        @else
                            {{$factura->plazos()->nombre}} DIAS
                        @endif
                    </td>
                </tr>

            </table>
        </div>

        <div class="margin-docpdf">
            <table style="width:100%;margin:5px 0px;margin-left: -3px;">
                <tbody>
                <tr>
                    <td style="width:14%;border:none;">
                        <div style="background-color:{{$empresa->color}};text-align:center;border-radius:5px;height:16px;padding:5px;text-align:left;color:#fff;">
                            DETALLES
                        </div>
                    </td>
                    <td style="width:27%;border:none;padding-left:30%;">
                        <div style="background-color:{{$empresa->color}};text-align:center;border-radius:5px;height:16px;padding:5px;text-align:left;color:#fff;">
                            @if($factura->emitida == 1)
                                FACTURA ELECTRONICA DE VENTA
                            @else
                                ESTADO DE CUENTA
                            @endif
                        </div>
                    </td>
                    <td style="border:1px solid {{$empresa->color}};text-align:center;border-radius:5px;width:18%;">No. #{{substr($factura->codigo,2)}}</td>
                </tr>
                </tbody>
            </table>
        </div>

        <div style="">
            <table border="0" class="titulo">
                <tr>
                    <th width="23.5%" class="right smalltd">REFERENCIA DE PAGO</th>
                    <td colspan="1" style="">{{$factura->codigo}}</td>
                </tr>
                <tr>
                    <th class="right smalltd" width="10%">FECHA CREACION FACTURA</th>
                    <td colspan="">{{Carbon\Carbon::parse($factura->fecha)->format('d-m-Y')}}</td>
                </tr>
                <tr>
                    <th class="right smalltd">FECHA PAGO OPORTUNO</th>
                    <td colspan="">{{Carbon\Carbon::parse($factura->pago_oportuno)->format('d-m-Y')}}</td>
                </tr>
                <tr>
                    <th class="right smalltd">FECHA SUSPENSION</th>
                    <td style="border-bottom: 2px solid #ccc;">{{Carbon\Carbon::parse($factura->vencimiento)->format('d-m-Y')}}</td>
                </tr>
                <tr>
                    <th class="right smalltd">PERIODO COBRADO</th>
                    <td style="border-bottom: 2px solid #ccc;">{{$factura->periodoCobradoTexto()}} {{$factura->diasCobradosProrrateo()}} días</td>
                </tr>
                <tr>
                    <th class="right smalltd">ESTADO FACTURA</th>
                    <td style="border-bottom: 2px solid #ccc; text-transform: uppercase;">{{$factura->estatus()}}</td>
                </tr>
            </table>
        </div>

        <div class="margin-docpdf">
            <div class="divheader-nota">
                <p>NOTA</p>
            </div>
        </div>

        <div class="nota-content">
            <p>
                Estimado Cliente, paga oportunamente y evita la suspensión del servicio, cobro de reconexión e intereses demora. El incumplimiento en los pagos genera reportes a
                Centrales de Riesgo como moroso. Una vez realices tu pago, este se aplicará a más tardar el siguiente día hábil. Si ya realizaste el pago, haz caso omiso.
            </p>
        </div>

        <div style="margin-top: 1%;">
            <h4 style="text-align: center">{{$factura->titulo}}</h4>
            <h4>
                <table border="0" class="desgloce">
                    <thead>
                    <tr>
                        <th style="padding: 3px;" width="28%" class="center smalltd">Item</th>
                        <th style="padding: 3px;" width="14%" class="center smalltd">Referencia</th>
                        <th style="padding: 3px;" width="8%" class="center smalltd">Cantidad</th>
                        <th style="padding: 3px;" width="14%" class="center smalltd">Precio</th>
                        <th style="padding: 3px;" width="12%" class="center smalltd">Iva</th>
                        <th style="padding: 3px;" width="8%" class="center smalltd">Dcto</th>
                        <th style="padding: 3px;" width="19%" class="center smalltd">Total</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php $cont=0; @endphp
                    @foreach($items[$key] as $item)

                        @php $cont++; @endphp
                        <tr>
                            <td style="word-wrap: break-word;" class="left padding-left border_left @if($cont==$itemscount && $cont>6) border_bottom @endif">{{$item->producto()}} @if($item->descripcion) ({{$item->descripcion}}) @endif</td>
                            <td class="center @if($cont==$itemscount && $cont>6) border_bottom @endif">{{$item->ref}}</td>
                            <td class="center  @if($cont==$itemscount && $cont>6) border_bottom @endif">{{round($item->cant,3)}}</td>
                            <!--<td class="center padding-right  @if($cont==$itemscount && $cont>6) border_bottom @endif">{{$empresa->moneda}}{{App\Funcion::Parsear($item->precio)}}</td>-->
                            <td class="center padding-right  @if($cont==$itemscount && $cont>6) border_bottom @endif">{{$empresa->moneda}}{{round($item->precio,4)}}</td>
                            <td class="center border_left">
                                {{$item->impuesto == 0 ? '0%' : number_format(round($item->impuesto),0) . "%"}} {{isset($item->impuesto_1) ? ', '. number_format(round($item->impuesto_1),0) . "%" : ''}}
                                {{isset($item->impuesto_2) ? ', '. number_format(round($item->impuesto_2),0) . "%" : ''}} {{isset($item->impuesto_3) ? ', '. number_format(round($item->impuesto_3),0)  . "%": ''}} {{isset($item->impuesto_4) ? ', '. number_format(round($item->impuesto_4),0) . "%" : ''}}
                                {{isset($item->impuesto_5) ? ', '. number_format(round($item->impuesto_5),0) . "%" : ''}} {{isset($item->impuesto_6) ? ', '. number_format(round($item->impuesto_6),0)  . "%": ''}} {{isset($item->impuesto_7) ? ', '. number_format(round($item->impuesto_7),0) . "%" : ''}}

                            </td>
                            <td class="center padding-left border_right @if($cont==$itemscount && $cont>6) border_bottom @endif">{{$item->desc == 0 ? '' :  round($item->desc,4) . "%"}}</td>
                            <td class="center padding-left border_right  @if($cont==$itemscount && $cont>6) border_bottom @endif">{{$empresa->moneda}}{{App\Funcion::Parsear($item->total())}}</td>
                        </tr>

                    @endforeach
                    <tr>
                        <td class="border_left border_bottom" style="height: 15px;"></td>
                        <td class="border_bottom" style="height: 15px;"></td>
                        <td class="border_bottom" style="height: 15px;"></td>
                        <td class="border_bottom" style="height: 15px;"></td>
                        <td class="border_bottom" style="height: 15px;"></td>
                        <td class="border_bottom" style="height: 15px;"></td>
                        <td class="border_right border_bottom " style="height: 15px;"></td>
                    </tr>
                    </tbody>

                    <tfoot>
                    <tr class="foot">
                        <th colspan="5" class="smalltd">{{$factura->facnotas}}</th>
                        <td class="right">SubTotal</td>
                        <td class="right padding-right">{{$empresa->moneda}}{{App\Funcion::Parsear($factura->total()->subtotal)}}</td>
                    </tr>
                    @if($factura->total()->descuento>0)
                        <tr class="foot">
                            <td colspan="5" class="smalltd"></td>
                            <td class="right">Descuento</td>
                            <td class="right padding-right">{{$empresa->moneda}}{{App\Funcion::Parsear($factura->total()->descuento)}} </td>
                        </tr>
                        <tr class="foot">
                            <td colspan="5" class="smalltd"></td>
                            <td class="right">SubTotal</td>
                            <td class="right padding-right">{{$empresa->moneda}}{{App\Funcion::Parsear($factura->total()->resul)}}</td>
                        </tr>
                    @endif
                    @if($factura->total()->imp)
                        @foreach($factura->total()->imp as $imp)
                            @if(isset($imp->total))
                                <tr class="foot">
                                    <td colspan="4" class="smalltd"></td>
                                    <td colspan="2" class="right">{{$imp->nombre}} ({{round($imp->porcentaje,2)}}%)</td>
                                    <td class="right padding-right">{{$empresa->moneda}}{{App\Funcion::Parsear($imp->total)}}</td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                    @foreach($retenciones[$key] as $retencion)

                        <tr class="foot">
                            <td colspan="4" class="smalltd"></td>
                            <td colspan="2" class="right">{{$retencion->retencion()->nombre}} ({{round($retencion->retencion()->porcentaje,2)}}%)</td>
                            <td class="right padding-right">{{$empresa->moneda}} {{App\Funcion::Parsear($retencion->valor)}}</td>
                        </tr>
                    @endforeach
                    <tr class="foot">
                        <td colspan="5"> </td>
                        <th class="right padding-right">Total</th>
                        <th class="right padding-right">{{$empresa->moneda}}{{App\Funcion::Parsear($factura->total()->total)}} </th>
                    </tr>
                    </tfoot>
                </table>
            </h4>
        </div>

        <div class="margin-docpdf">
            <div class="divheader-estadocuenta">
                <p>ESTADO DE CUENTA</p>
            </div>

            <div class="div-content-border">
                <div>
                    <table style="width:100%;margin:5px;">
                        <tbody>
                        <tr class="tr-estadocuenta">
                            <td><li style="background-color:#f6c009;">SALDO MES ANTERIOR</li></td>
                            <td><li style="background-color:#6cad40;">SALDO MES ACTUAL</li></td>
                            <td><li style="background-color:#589cdc;">EQUIPO / CUOTA </li></td>
                            <td><li style="background-color:#ccc;">SERVICIO ADICIONAL</li></td>
                            <td><li style="background-color:#6cad40;">TOTAL</li></td>
                        </tr>
                        <tr class="tr-estadocuenta-precio">
                            <td><li>{{$empresa->moneda}} {{App\Funcion::Parsear($factura->estadoCuenta()->saldoMesAnterior)}}</li></td>
                            <td><li>{{$empresa->moneda}} {{App\Funcion::Parsear($factura->estadoCuenta()->saldoMesActual)}}</li></td>
                            <td><li>{{$empresa->moneda}} 0</li></td>
                            <td><li>{{$empresa->moneda}} 0</li></td>
                            <td><li>{{$empresa->moneda}} {{App\Funcion::Parsear($factura->estadoCuenta()->total)}}</li></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    @endforeach
@endsection
