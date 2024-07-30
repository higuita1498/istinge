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
        .tabla-manual td{
            padding: 15px !important;
            width: calc(100% / 12);
        }
        .tabla-manual {
            width: 100%;
            border-collapse: collapse;
        }
    </style>

    <div style="width: 100%;height:auto;">
        <div style="width: 30%; display: inline-block; vertical-align: top; text-align: center; height:100px !important;  margin-top: 2%; overflow:hidden;">
            <img src="{{asset('images/Empresas/Empresa'.Auth::user()->empresa.'/'.Auth::user()->empresa()->logo)}}" alt="" style="max-width: 100%; max-height:100px; object-fit:contain; text-align:left;">
        </div>
        <div style="width: 40%; text-align: center; display: inline-block;  height:auto">
            <h4 style="text-transform: uppercase;">{{Auth::user()->empresa()->nombre}}</h4>
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
            <img src="{{asset('images/Empresas/Empresa'.Auth::user()->empresa.'/'.Auth::user()->empresa()->logo1)}}" alt="" style="max-width: 100%; max-height:100px; object-fit:contain; text-align:left;">

            {{-- <p class="medium" style="text-align: center;"> Radicado</p>
            <h4 style="text-align: center;">No. #{{$radicado->codigo}}</h4> --}}
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
                            <th style="text-align: left; padding-left: 9px;">Nro. Radicado</th>
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
                            <th style="text-align: left; padding-left: 9px;">Nro. Telefónico</th>
                            <td style="padding-left: 9px;">{{$radicado->telefono}}</td>
                        </tr>
                        <tr>
                            <th style="text-align: left; padding-left: 9px;">Correo</th>
                            <td style="padding-left: 9px;">{{$radicado->correo}}</td>
                        </tr>
                        @if($radicado->barrio)
                        <tr>
                            <th style="text-align: left; padding-left: 9px;">Barrio</th>
                            <td style="padding-left: 9px;">{{$radicado->barrio}}</td>
                        </tr>
                        @endif
                        <tr>
                            <th style="text-align: left; padding-left: 9px;">Dirección</th>
                            <td style="padding-left: 9px;">{{$radicado->direccion}}</td>
                        </tr>
                        <tr>
                            <th style="text-align: left; padding-left: 9px;">Nodo</th>
                            <td style="padding-left: 9px;">{{$contrato->nodo}}</td>
                        </tr>
                        <tr>
                            <th style="text-align: left; padding-left: 9px;">IP</th>
                            <td style="padding-left: 9px;">{{$contrato->ip}}</td>
                        </tr>
                        <tr>
                            <th style="text-align: left; padding-left: 9px;">Tipo de Servicio</th>
                            @if ($radicado->servicio == 2)
                            <td style="padding-left: 9px;"><span class="text-danger font-weight-bold">No tiene internet</span></td>
                            @endif
                            @if ($radicado->servicio == 4)
                            <td style="padding-left: 9px;"><span class="text-danger font-weight-bold">Instalaciones</span></td>
                            @endif
                            @if ($radicado->servicio == 5)
                            <td style="padding-left: 9px;"><span class="text-danger font-weight-bold">Lentitud o intermitencia                            </span></td>
                            @endif
                            @if ($radicado->servicio == 6)
                            <td style="padding-left: 9px;"><span class="text-danger font-weight-bold">Traslados</span></td>
                            @endif
                            @if ($radicado->servicio == 8)
                            <td style="padding-left: 9px;"><span class="text-danger font-weight-bold">Suspensión temporal</span></td>
                            @endif
                            @if ($radicado->servicio == 9)
                            <td style="padding-left: 9px;"><span class="text-danger font-weight-bold">Cambio de clave</span></td>
                            @endif
                            @if ($radicado->servicio == 10)
                            <td style="padding-left: 9px;"><span class="text-danger font-weight-bold">Retiro de Servicio</span></td>
                            @endif
                            @if ($radicado->servicio == 11)
                            <td style="padding-left: 9px;"><span class="text-danger font-weight-bold">Cambio de plan</span></td>
                            @endif
                            @if ($radicado->servicio == 12)
                            <td style="padding-left: 9px;"><span class="text-danger font-weight-bold">Ajuste a la factura</span></td>
                            @endif
                            @if ($radicado->servicio == 13)
                            <td style="padding-left: 9px;"><span class="text-danger font-weight-bold">No tiene Television</span></td>
                            @endif
                            @if ($radicado->servicio == 14)
                            <td style="padding-left: 9px;"><span class="text-danger font-weight-bold">Ventas</span></td>
                            @endif
                            @if ($radicado->servicio == 15)
                            <td style="padding-left: 9px;"><span class="text-danger font-weight-bold">Cambio de Titular</span></td>
                            @endif
                            @if ($radicado->servicio == 16)
                            <td style="padding-left: 9px;"><span class="text-danger font-weight-bold">Visita Tecnica</span></td>
                            @endif
                            @if ($radicado->servicio == 17)
                            <td style="padding-left: 9px;"><span class="text-danger font-weight-bold">Migraciones</span></td>
                            @endif
                            @if ($radicado->servicio == 18)
                            <td style="padding-left: 9px;"><span class="text-danger font-weight-bold">Cobranza Datacredito</span></td>
                            @endif
                            @if ($radicado->servicio == 20)
                            <td style="padding-left: 9px;"><span class="text-danger font-weight-bold">Instalación televisión</span></td>
                            @endif
                            @if ($radicado->servicio == 23)
                            <td style="padding-left: 9px;"><span class="text-danger font-weight-bold">Cambios Osstelco</span></td>
                            @endif
                            @if ($radicado->servicio == 24)
                            <td style="padding-left: 9px;"><span class="text-danger font-weight-bold">PQR</span></td>
                            @endif

                        </tr>
                        <tr>
                            <th style="text-align: left; padding-left: 9px;">Punto de Acceso</th>
                            <td style="padding-left: 9px;">{{$radicado->ap}}</td>
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
                            <td style="padding: 0px 9px; text-align: justify;">{{$radicado->desconocido}}</td>
                        </tr>
                        @if ($radicado->tecnico)
                        <tr>
                            <th style="text-align: left; padding-left: 9px;">Técnico Asociado</th>
                            <td style="padding: 0px 9px; text-align: justify;">{{$radicado->tecnico_reporte()}}</td>
                        </tr>
                        @endif
                        @if ($radicado->reporte)
                        <tr>
                            <th style="text-align: left; padding-left: 9px;">Reporte Técnico</th>
                            <td style="padding: 0px 9px; text-align: justify;">{{$radicado->reporte}}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div style="margin-bottom:20px !important;margin-top: 3% !important;">
        <h4>DETALLES DE EQUIPOS (instalación – retiro)</h4>
        <table class="tabla-manual">
            <thead>
                <tr>
                    <th>MARCA</th>
                    <th>MARCA O MODELO TV</th>
                    <th>SERIAL</th>
                    <th>MAC</th>
                    <th>SEÑAL O POTENCIA</th>
                    <th>CANTIDAD DE PUNTOS</th>

                </tr>
            </thead>
            <tbody>
                <tr style="padding:25px;">
                    <td> </td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                </tr>
                <tr style="padding:25px;">
                    <td> </td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                </tr>
                <tr>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                </tr>
                <tr>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                </tr>
                <tr>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                    <td> </td>
                </tr>

                <!-- Puedes agregar más filas según sea necesario -->
            </tbody>
        </table>

    </div>

    <div style="width: 70%; margin-top: 7%;">
        <div style="padding-top: 8%; text-align: center;">
            <div style="display: inline-block; width: 45%;">
                <p class="small"></p>
            </div>
            <div style="display: inline-block; width: 45%;">
                @if($radicado->firma)
                <img src="data:image/png;base64,{{substr($radicado->firma,1)}}" class="img-fluid w-50" style="width: 90%">
                @endif
                <p class="small" style="border-top: 1px solid #000;"> ACEPTADA, FIRMA Y/O SELLO Y FECHA</p>
            </div>
        </div>
    </div>
@endsection
