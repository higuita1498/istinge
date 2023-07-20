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
            font-size: 20px;line-height: 14px;    margin: 0;
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
        <div style="width: 30%; display: inline-block; vertical-align: top; text-align: center; height:100px !important;  margin-top: 2%; overflow:hidden;  text-align:left;">
            {{-- <img src="{{asset('images/logo1.jpg')}}" alt="" style="max-width: 100%; max-height:100px; object-fit:contain;"> --}}
        </div>
        <div style="width: 40%; text-align: center; display: inline-block;  height:auto">
            <h4>{{Auth::user()->empresa()->nombre}}</h4>
            <p style="line-height: 12px;">{{Auth::user()->empresa()->tip_iden('mini')}} {{Auth::user()->empresa()->nit}} @if(Auth::user()->empresa()->dv != null) - {{Auth::user()->empresa()->dv}} @endif
                <br>
                {{Auth::user()->empresa()->direccion}} <br>
                {{Auth::user()->empresa()->telefono}}
                @if(Auth::user()->empresa()->web)
                    <br>{{Auth::user()->empresa()->web}}
                @endif
                <br> <a href="mailto:{{Auth::user()->empresa()->email}}" target="_top">{{Auth::user()->empresa()->email}}</a>
            </p>
        </div>
        <div style="width: 28%; display: inline-block; text-align: left; vertical-align: top; margin-top: 2%;">
            <p class="medium"> Radicado</p>
            <h4 style="text-align: left; ">No. #{{$radicado->codigo}}</h4>
        </div>
    </div>

    <div class="row card-description">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-sm info" style="width: 100%;height:auto;">
                    <tbody>
                        <tr>
                            <th width="20%" style="text-align: center;">DATOS GENERALES</th>
                            <th></th>
                        </tr>
                        <tr>
                            <th style="text-align: left; padding-left: 9px;">N° Radicado</th>
                            <td style="padding-left: 9px;">{{$radicado->codigo}}</td>
                        </tr>
                        <tr>
                            <th style="text-align: left; padding-left: 9px;">Fecha</th>
                            <td style="padding-left: 9px;">{{date('d-m-Y', strtotime($radicado->fecha))}}</td>
                        </tr>
                        <tr>
                            <th style="text-align: left; padding-left: 9px;">Contrato</th>
                            <td style="padding-left: 9px;">{{$radicado->contrato}}</td>
                        </tr>
                        <tr>
                            <th style="text-align: left; padding-left: 9px;">Cliente</th>
                            <td style="padding-left: 9px;">{{$radicado->nombre}}</td>
                        </tr>
                        <tr>
                            <th style="text-align: left; padding-left: 9px;">N° Telefónico</th>
                            <td style="padding-left: 9px;">{{$radicado->telefono}}</td>
                        </tr>
                        <tr>
                            <th style="text-align: left; padding-left: 9px;">Correo</th>
                            <td style="padding-left: 9px;">{{$radicado->correo}}</td>
                        </tr>
                        <tr>
                            <th style="text-align: left; padding-left: 9px;">Dirección</th>
                            <td style="padding-left: 9px;">{{$radicado->direccion}}</td>
                        </tr>
                        <tr>
                            <th style="text-align: left; padding-left: 9px;">Estatus</th>
                            <td style="padding-left: 9px;">
                                @if ($radicado->estatus == 0)
                                    <span class="text-danger font-weight-bold">Pendiente</span>
                                @endif
                                @if ($radicado->estatus == 1)
                                    <span class="text-success font-weight-bold">Resuelto</span>
                                @endif
                                @if ($radicado->estatus == 2)
                                    <span class="text-danger font-weight-bold">Escalado / Pendiente</span>
                                @endif
                                @if ($radicado->estatus == 3)
                                    <span class="text-success font-weight-bold">Escalado / Resuelto</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th style="text-align: left; padding-left: 9px;">Observaciones</th>
                            <td style="padding-left: 9px;">{{$radicado->desconocido}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div style="width: 70%; margin-top: 1%;">
        <div style="padding-top: 8%; text-align: center;">
            <div style="display: inline-block; width: 45%;">
                <p class="small"></p>
            </div>
            <div style="display: inline-block; width: 45%;">
                <img src="{{$radicado->firma}}" class="img-fluid w-50" style="width: 90%">
                <p class="small" style="border-top: 1px solid #000;"> ACEPTADA, FIRMA Y/O SELLO Y FECHA</p>
            </div>
        </div>
    </div>
@endsection
