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

    h3 {
        font-weight: bold;
        text-align: center;
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
        font-size: 17px;
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
        padding-top: 1px;
        border-left: 2px solid #fff;
        border-top: 2px solid #fff;
        border-bottom: 2px solid #fff;
        border-right: 2px solid #fff;
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

    .vigilate {
        /*background-color: green;*/
        margin-bottom: 2px;
        position: absolute;
        top: -55px;
        right: 15px;
        z-index: -1000;
    }

    .vigilate img {
        width: 255px;
        height: 100px;
    }
</style>

<div style="width: 100%;height:auto;">
    <div style="width: 30%; display: inline-block; vertical-align: top; text-align: center; height:100px !important;  margin-bottom: 2%; overflow:hidden; text-align:left;">
    </div>
    <div style="width: 40%; text-align: center; display: inline-block;  height:auto; margin-right:45px;">
        <h4>{{$user->empresaObj->nombre}}</h4>
        <p style="line-height: 12px;">{{$user->empresaObj->tip_iden('mini')}} {{$user->empresaObj->nit}} @if($user->empresaObj->dv != null || $user->empresaObj->dv === 0) - {{$user->empresaObj->dv}} @endif<br>
            {{$user->empresaObj->direccion}} <br>
            {{$user->empresaObj->telefono}}
            @if($user->empresaObj->web)
            <br>{{$user->empresaObj->web}}
            @endif
            <br><a href="mailto:{{$user->empresaObj->email}}" target="_top">{{$user->empresaObj->email}}</a>
        </p>
    </div>
    <div style="width: 21%; display: inline-block; text-align: left; vertical-align: top;margin-top: 2%;">

    </div>
</div>

<div>
    <table border="0" class="titulo">
        <tr>
            <th width="10%" class="right smalltd">SEÑOR(ES)</th>
            <td colspan="3" style="border-top: 2px solid #ccc;">{{$persona->nombre()}}</td>
            <th width="22%" class="center" style="font-size: 8px"><b>FECHA DE GENERACION (DD/MM/AA)</b></th>
        </tr>
        <tr>
            <th class="right smalltd" width="10%">DIRECCION</th>
            <td colspan="3">{{$persona->direccion}}</td>
            <td class="center" style="border-right: 2px solid #ccc;">{{Carbon\Carbon::now()->format('d/m/Y')}}</td>
        </tr>
        <tr>
            <th class="right smalltd">{{$persona->tipo_documento('mini')}}</th>
            <td colspan="">{{$persona->nro_documento }}</td>
            <th class="right" style="padding-right: 2px;">MÉTODO DE PAGO</th>
            <td style="border-bottom: 2px solid #ccc;">{{ $persona->metodo_pago() }}</td>
            <th class="center" style="font-size: 8px"><b>FECHA DE PAGO (DD/MM/AA)</b></th>
        </tr>
        <tr>
            <th class="right smalltd">TELÉFONO</th>
            <td style="border-bottom: 2px solid #ccc;">{{$persona->nro_celular}}</td>
            <th class="right" style="padding-right: 2px;">EMAIL</th>
            <td style="border-bottom: 2px solid #ccc;">{{$persona->correo}}</td>
            <td class="center" style="border-right: 2px solid #ccc; border-bottom: 2px solid #ccc;">{{Carbon\Carbon::now()->format('d/m/Y')}}</td>
        </tr>
        <tr>
            <th class="right smalltd">DIAS PRIMA</th>
            <td style="border-bottom: 2px solid #ccc;">{{$prestacionSocial->dias_trabajados}}</td>
            <th class="right" style="padding-right: 2px;">SALARIO BASE</th>
            <td style="border-right: 2px solid #ccc; border-bottom: 2px solid #ccc;">{{$user->empresaObj->moneda}} {{App\Funcion::Parsear($prestacionSocial->base)}}</td>
        </tr>
    </table>
</div>

<div style="width: 100%; text-align: center;font-weight: bold; margin-top: 20px;">
    <h2>RESUMEN DEL PAGO</h2>
</div>

<div>
    <table border="0" class="titulo">
        <tr>
            <th width="80%" style="height: 30px;" class="left padding-left">Items</th>
            <th width="20%" style="height: 30px;">Valor</th>
        </tr>
        <tr>
            <td width="80%" style="height: 20px;" class="padding-left">Prima de Servicios {{$year}} - {{$periodoPrima}}</td>
            <td width="20%" style="height: 20px;" class="center">{{$user->empresaObj->moneda}} {{App\Funcion::Parsear($prestacionSocial->valor_pagar)}}</td>
        </tr>
        <tr>
            <th width="80%" style="height: 30px;" class="left padding-left">Total neto a pagar al empleado</th>
            <th width="20%" style="height: 30px;" class="center">{{$user->empresaObj->moneda}} {{ App\Funcion::Parsear($prestacionSocial->valor_pagar) }}</th>
        </tr>
    </table>
</div>

<div style="padding-top: 12%; text-align: center;">
    <div style="padding-top: 8%; text-align: center;">
        <div style="display: inline-block; width: 45%; border-top: 1px solid #000;     margin-right: 10%;">
            <p class="small"> ELABORADO POR: </p>
        </div>
        <div style="display: inline-block; width: 44%; border-top: 1px solid #000;">
            <p class="small"> ACEPTADA, FIRMA Y/O SELLO Y FECHA</p>
        </div>
    </div>
</div>

@endsection
