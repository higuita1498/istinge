@extends('layouts.pdf')

@section('content')
<style type="text/css">
    /**
        * Define the width, height, margins and position of the watermark.
        **/
    #watermark {
        position: fixed;
        top: 25%;
        width: 100%;
        text-align:
            center;
        opacity: .6;
        transform: rotate(-30deg);
        transform-origin: 50% 50%;
        z-index: 1000;
        font-size: 130px;
        color: #a5a5a5;
    }

    body {
        font-family: Helvetica, sans-serif;
        font-size: 12px;
        color: #000;
    }

    h4 {
        font-weight: bold;
        text-align: center;
        margin: 0;
        font-size: 14px;
    }

    .small {
        font-size: 10px;
        line-height: 12px;
        margin: 0;
    }

    .smalltd {
        font-size: 10px;
        line-height: 12px;
        padding-right: 2px;
    }

    .medium {
        font-size: 20px;
        line-height: 14px;
        margin: 0;
    }

    a {
        color: #000;
        text-decoration: none;
    }

    th {
        background: #ccc;
    }

    td {
        padding-left: 2px;
    }

    .center {
        text-align: center;
    }

    .right {
        text-align: right;
    }

    .left {
        text-align: left;
    }


    .titulo {
        width: 100%;
        border-collapse: collapse;
        border-radius: 0.4em;
        overflow: hidden;
    }

    td {
        border: 1px solid #9e9b9b;
    }

    th {
        border: 1px solid #ccc;
    }

    .desgloce {
        width: 100%;
        overflow: hidden;
        border-collapse: collapse;
        border-top-left-radius: 0.4em;
        border-top-right-radius: 0.4em;
    }

    .desgloce td {
        padding-top: 3px;
        border-left: 2px solid #fff;
        border-top: 2px solid #fff;
        border-bottom: 2px solid #fff;
        border-right: 2px solid #ccc;
    }

    .foot td {
        padding-top: 3px;
        border: 1px solid #fff;
        padding-right: 1%;
    }

    .foot th {
        padding: 2px;
        border-radius: unset;
    }

    .border_left {
        border-left: 3px solid #ccc !important;
    }

    .border_bottom {
        border-bottom: 5px solid #ccc !important;
    }

    .border_right {
        border-right: 3px solid #ccc !important;
    }

    .padding-right {
        padding-right: 1% !important;
    }

    .padding-left {
        padding-left: 1%;
    }
</style>

<div style="width: 100%;height:auto;">
    <div style="width: 30%; display: inline-block; vertical-align: top; text-align: center; height:100px !important;  margin-top: 2%; overflow:hidden;  text-align:left;">
        <img src="{{asset('images/Empresas/Empresa'.$empresa->id.'/'.$empresa->logo)}}" alt="" style="max-width: 100%; max-height:100px; object-fit:contain;">
    </div>
    <div style="width: 40%; text-align: center; display: inline-block;  height:auto">
        <h4>{{$empresa->nombre}}
            @isset($empresa->nombre_persona_natural)
            <br>
            <small>{{$empresa->nombre_persona_natural}}</small>
            @endisset
        </h4>
        <p style="line-height: 12px;">{{$empresa->tip_iden('mini')}} {{$empresa->nit}} @if($empresa->dv != null) - {{$empresa->dv}} @endif<br>
            {{$empresa->direccion}} <br>
            {{$empresa->telefono}}
            @if($empresa->web)
            <br>{{$empresa->web}}
            @endif
            <br> <a href="mailto:{{$empresa->email}}" target="_top">{{$empresa->email}}</a>
        </p>

    </div>
    <div style="width: 28%; display: inline-block; vertical-align: top;
    padding-top: 2%;">
        @if($factura->codigo_dian != null)
        <p class="medium" style="position:absolute;top:-2.5%;font-size:1.10em"> {{"Documento soporte a no obligados a facturar electronicamente"}}</p>
        @else
        <p class="medium">Factura de Proveedor</p>
        @endif
        <h4 style="text-align: left; margin-top:4%">No. @if($factura->codigo_dian != null){{$factura->codigo_dian}} @else{{$factura->codigo}} @endif</h4>
        <p class="small">@if($factura->codigo_dian != null) {{"Factura de proveedores"}} @else {{$tipo}} @endif</p>
        @if($factura->empresa == 160 && config('app.entorno') == 2)
        <p class="small">Consecutivo contable <strong>{{$factura->codigo ?? $factura->nro}}</strong></p>
        @endif
        @if($factura->codigo_dian != null) <h4 style="text-align: left; ">{{ 'No responsable de IVA' }}</h4> @endif
    </div>
</div>
<div>
    <table border="1" class="titulo">
        <tr>
            <th width="10%" class="right smalltd">SEÑOR(ES)</th>
            <td colspan="3" style="border-top: 2px solid #ccc;">{{$factura->proveedor()->nombre}}</td>
            <th width="22%" class="center" style="font-size: 8px"><b>FECHA DE EXPEDICIÓN (DD/MM/AA)</b></th>
        </tr>
        <tr>
            <th class="right smalltd" width="10%">DIRECCION</th>
            <td colspan="3">{{$factura->proveedor()->direccion}}</td>
            <td class="center" style="border-right: 2px solid #ccc;">{{$factura->fecha ? Carbon\Carbon::parse($factura->fecha)->format('d/m/Y') : Carbon\Carbon::parse($factura->fecha_factura)->format('d/m/Y')}}</td>
        </tr>
        <tr>
            <th class="right smalltd">CIUDAD</th>
            <td colspan="">{{$factura->proveedor()->municipio()->nombre}}</td>
            <th class="right" width="15%" style="padding-right: 2px;">{{$factura->proveedor()->tip_iden('mini')}}</th>
            <td style="border-bottom: 2px solid #ccc;" width="20%">{{$factura->proveedor()->nit }}
                @if($factura->proveedor()->dv != null)
                - {{$factura->proveedor()->dv }}
                @endif</td>
            <th class="center" style="font-size: 8px"><b>FECHA DE VENCIMIENTO (DD/MM/AA)</b></th>
        </tr>
        <tr>
            <th class="right smalltd">TELÉFONO</th>
            <td style="border-bottom: 2px solid #ccc;">{{$factura->proveedor()->telefono1}}</td>
            <th class="right" style="padding-right: 2px;">EMAIL</th>
            <td style="border-bottom: 2px solid #ccc;">{{$factura->proveedor()->email}}</td>
            <td class="center" style="border-right: 2px solid #ccc; border-bottom: 2px solid #ccc;">
                {{$factura->vencimiento ? $factura::formatoFecha($factura->vencimiento) : $factura::formatoFecha($factura->vencimiento_factura)}}
            </td>
        </tr>
    </table>
</div>


<div style="margin-top: 2%;">
    <table border="0" class="desgloce">
        <thead>
            <tr>
                <th style="padding: 3px;" width="40%" class="center smalltd">Ítem</th>
                <th style="padding: 3px;" width="18%" class="center smalltd">Referencia</th>
                <th style="padding: 3px;"  class="center smalltd">Cantidad</th>
                <th style="padding: 3px;" width="10%" class="center smalltd">Precio</th>
                <th style="padding: 3px;" width="10%" class="center smalltd">Descuento</th>
                <th style="padding: 3px;" width="15%" class="center smalltd">Total</th>
            </tr>
        </thead>
        <tbody>
            @php $cont=0; @endphp
            @foreach($items as $item)

            @php $cont++; @endphp
            <tr>
                <td class="left padding-left border_left @if($cont==$itemscount && $cont>6) border_bottom @endif">{{$item->producto()}} @if($item->descripcion) ({{$item->descripcion}}) @endif</td>

                <td class="center @if($cont==$itemscount && $cont>6) border_bottom @endif">{{$item->ref}}</td>
                <td class="center  @if($cont==$itemscount && $cont>6) border_bottom @endif">{{round($item->cant,4)}}</td>
                <td class="right padding-right  @if($cont==$itemscount && $cont>6) border_bottom @endif">{{$empresa->moneda}}{{App\Funcion::Parsear($item->precio)}}</td>
                <td class="center  @if($cont==$itemscount && $cont>6) border_bottom @endif">{{$item->desc == 0 ? '' :  $item->desc . "%"}}</td>
                <td class="right padding-right border_right  @if($cont==$itemscount && $cont>6) border_bottom @endif">{{$empresa->moneda}}{{App\Funcion::Parsear($item->total())}}</td>
            </tr>

            @endforeach
            @if($cont<7) @php $cont=7-$cont; @endphp @for($i=1; $i<=$cont; $i++) <tr>
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
                <th></th>
                <td class="right">SubTotal</td>
                <td class="right padding-right">{{$empresa->moneda}}{{App\Funcion::Parsear($factura->total()->subtotal)}}</td>
            </tr>
            @if($factura->total()->descuento>0)
            <tr class="foot">
                <td></td>
                <td class="right">Descuento</td>
                <td class="right padding-right">{{$empresa->moneda}}{{App\Funcion::Parsear($factura->total()->descuento)}} </td>
            </tr>
            <tr class="foot">
                <td></td>
                <td class="right">SubTotal</td>
                <td class="right padding-right">{{$empresa->moneda}}{{App\Funcion::Parsear($factura->total()->subsub)}}</td>
            </tr>
            @endif
            @if($factura->total()->imp)
            @foreach($factura->total()->imp as $imp)
            @if(isset($imp->total))
            <tr class="foot">
                <td></td>
                <td class="right">{{$imp->nombre}} ({{$imp->porcentaje}}%)</td>
                <td class="right padding-right">{{$empresa->moneda}}{{App\Funcion::Parsear($imp->total)}}</td>
            </tr>
            @endif
            @endforeach
            @endif
            @foreach($retenciones as $retencion)
            <tr class="foot">
                <td></td>
                <td class="right">{{$retencion->tipo()}} {{$retencion->retencion}}%</td>
                <td class="right padding-right">- {{$empresa->moneda}} {{App\Funcion::Parsear($retencion->valor)}}</td>
            </tr>
            @endforeach
            <tr class="foot">
                <td colspan="{{ $marcaPermiso ? "5" : "4" }}"> </td>
                <th class="right padding-right">Total</th>
                <th class="right padding-right">{{$empresa->moneda}}{{App\Funcion::Parsear($factura->total()->total)}} </th>
            </tr>
        </tfoot>

    </table>


    @if($codqr)
    <p style="font-size:7px;margin-top:-20px;"><strong>cufe: </strong>{{$CUDSvr}}</p>
    @endif

</div>


@if($factura->codigo_dian != null)
<p style="text-align: justify;" class="small"> {{$numeracion->resolucion ?? ''}} </p>
@endif
<div style="width: 70%; margin-top: 1%">
    <p style="text-align: justify;" class="small">{{$factura->term_cond}}</p>
    @if($codqr)
    <div>
        <img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(200)->generate($codqr)) !!} ">
    </div>
    @endif
    <div style="padding-top: 8%; text-align: center;">
        <div style="display: inline-block; width: 45%; border-top: 1px solid #000;     margin-right: 10%;">
            <p class="small"> ELABORADO POR:{{$factura->comprador()->nombre}}</p>
        </div>
        <div style="display: inline-block; width: 44%; border-top: 1px solid #000;">
            <p class="small"> ACEPTADA, FIRMA Y/O SELLO Y FECHA</p>
        </div>
    </div>
</div>

<div id="watermark">{{$factura->estatus==2?'ANULADA':''}}</div>

<div style="width: 100%;height:auto;">
    <div style="width: 50%; display: inline-block; text-align:left;">

    </div>
    <div style="width: 50%; display: inline-block; text-align:right;margin-left:100px;">
        <img style="width:75%; height:auto; position:absolute; bottom:20px;" src="{{asset('images/logo_factura.png')}}">
    </div>
</div>
@endsection
