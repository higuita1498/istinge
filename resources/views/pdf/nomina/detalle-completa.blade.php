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
            text-align: center;
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
        <div style="width: 30%; display: inline-block; vertical-align: top; text-align: center; height:auto !important;overflow:hidden; text-align:left; margin-bottom:1%;">
            @if(isset($codqr))
            <div>
                <img src="data:image/png;base64, {!! base64_encode(QrCode::format('png')->size(150)->generate($codqr)) !!} ">
            </div>
            @else
            @if(isset($empresa))
            @php $isImageLogo = true; @endphp
                <img src="{{asset('images/Empresas/Empresa'.$empresa->id.'/'.$empresa->logo)}}" alt="{{$empresa->nombre}}" width="90%" style="max-width: 100%; max-height:100px; object-fit:contain; text-align:left;">
            @endif
            @endif
        </div>
        @if(isset($codqr))
        <div style="position:absolute;top:11em;left:0em;text-align:left;text-align:left;width: 100%;">
            <p style="font-size:9px;"><strong>cune: </strong>{{$nomina->cune}}</p>
        </div>
        @else
        
        @if(!isset($isImageLogo) && !isset($codqr))
        
         <div style="width: 30%; display: inline-block; vertical-align: top; text-align: center; height:100px !important;  margin-bottom: 2%; overflow:hidden; align-self: flex-start;">
            <img src="{{asset('images/Empresas/Empresa'.$empresa->id.'/'.$empresa->logo)}}" alt="{{$empresa->nombre}}" width="90%" style="max-width: 100%; max-height:100px; object-fit:contain; text-align:left;">
        </div>
        
        @endif
        
        @endif
        <div style="width: 40%; text-align: center; display: inline-block;  height:auto; margin-right:45px;">
            <h4>{{$user->empresaObj->nombre}}</h4>
            <p style="line-height: 12px;">{{$user->empresaObj->tip_iden('mini')}} {{$user->empresaObj->nit}} @if($user->empresaObj->dv != null || $user->empresaObj->dv === 0)
                    - {{$user->empresaObj->dv}} @endif<br>
                {{$user->empresaObj->direccion}} <br>
                {{$user->empresaObj->telefono}}
                @if($user->empresaObj->web)
                    <br>{{$user->empresaObj->web}}
                @endif
                <br><a href="mailto:{{$user->empresaObj->email}}" target="_top">{{$user->empresaObj->email}}</a>
            </p>
        </div>
        <div style="width: 21%; display: inline-block; text-align: left; vertical-align: top;margin-top: 2%;">
            <p class="medium">Colilla de Pago</p>
            <p class="medium" style="margin-top: 5px!important;"><b>No. {{ $numeracion }}</b></p>
            <!--<h4 style="text-align: left; ">No. 0000</h4>-->
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
                <td>{{$persona->direccion}}</td>
                <th class="right" style="padding-right: 2px;">DIAS TRABAJADOS</th>
                <td style="border-bottom: 2px solid #ccc;">{{$totalidad['diasTrabajados']['total']}}</td>
                <td class="center" style="border-right: 2px solid #ccc;">{{Carbon\Carbon::now()->format('d/m/Y')}}</td>
            </tr>
            <tr>
                <th class="right smalltd">{{$persona->tipo_documento('mini')}}</th>
                <td colspan="">{{$persona->nro_documento }}</td>
                <th class="right" style="padding-right: 2px;">MÉTODO DE PAGO</th>
                <td style="border-bottom: 2px solid #ccc;">{{ $persona->metodo_pago() }}</td>
                <th class="center" style="font-size: 8px"><b>PERIODO DE PAGO</b></th>
            </tr>
            <tr>
                <th class="right smalltd">TELÉFONO</th>
                <td style="border-bottom: 2px solid #ccc;">{{$persona->nro_celular}}</td>
                <th class="right" style="padding-right: 2px;">EMAIL</th>
                <td style="border-bottom: 2px solid #ccc;">{{$persona->correo}}</td>
                <td class="center"
                    style="border-right: 2px solid #ccc; border-bottom: 2px solid #ccc;">{{$mensajePeriodo}}</td>
            </tr>
        </table>
    </div>

    <div style="width: 100%; text-align: center;font-weight: bold;">
        <h2>RESUMEN DEL PAGO</h2>
    </div>

    <div>
        <table border="0" class="titulo">
            <tr>
                <th width="80%" style="height: 30px;" class="left padding-left">Items</th>
                <th width="20%" style="height: 30px;">Valor</th>
            </tr>
            <tr>
                <td width="80%" style="height: 20px;" class="padding-left">Salario</td>
                <td width="20%" style="height: 20px;"
                    class="center">{{$user->empresaObj->moneda}} {{App\Funcion::Parsear($totalidad['pago']['salario'])}}</td>
            </tr>
            <tr>
                <td width="80%" style="height: 20px;" class="padding-left">Subsidio de transporte</td>
                <td width="20%" style="height: 20px;"
                    class="center">{{$user->empresaObj->moneda}} {{App\Funcion::Parsear($totalidad['salarioSubsidio']['subsidioTransporte'])}}</td>
            </tr>
            @if($totalidad['pago']['extrasOrdinariasRecargos'] > 0)
            <tr>
                <td width="80%" style="height: 20px;" class="padding-left">Horas extra, ordinarias y recargos</td>
                <td width="20%" style="height: 20px;"
                    class="center">{{$user->empresaObj->moneda}} {{App\Funcion::Parsear($totalidad['pago']['extrasOrdinariasRecargos'])}}</td>
            </tr>
            @endif
            @if($totalidad['pago']['vacaciones'] > 0)
            <tr>
                <td width="80%" style="height: 20px;" class="padding-left">Vacaciones</td>
                <td width="20%" style="height: 20px;"
                    class="center">{{$user->empresaObj->moneda}} {{App\Funcion::Parsear($totalidad['pago']['vacaciones'])}}</td>
            </tr>
            @endif
            @if($totalidad['pago']['ingresosAdicionales'] > 0)
            <tr>
                <td width="80%" style="height: 20px;" class="padding-left">Ingresos adicionales</td>
                <td width="20%" style="height: 20px;"
                    class="center">{{$user->empresaObj->moneda}} {{ App\Funcion::Parsear($totalidad['pago']['ingresosAdicionales']) }}</td>
            </tr>
            @endif
            <tr>
                <td width="80%" style="height: 20px;" class="padding-left">Retenciones y deducciones</td>
                <td width="20%" style="height: 20px;" class="center">
                    - {{$user->empresaObj->moneda}} {{ App\Funcion::Parsear($totalidad['pago']['retencionesDeducciones']) }}</td>
            </tr>
            @if($prestacionSocial->count() > 0)
                @foreach($prestacionSocial as $p)
                    <tr>
                        <td width="80%" style="height: 20px;"
                            class="padding-left">{{ str_replace('_', ' ', $p->nombre) }}</td>
                        <td width="20%" style="height: 20px;"
                            class="center"> {{$user->empresaObj->moneda}} {{ App\Funcion::Parsear($p->valor_pagar) }}</td>
                    </tr>
                    @php $adicionales += $p->valor_pagar; @endphp
                @endforeach
            @endif
            
             @foreach($totalDetallesNomina as $categoria)
                        @if($categoria['fk_nomina_cuenta'] == 2 && $categoria['horas'] > 0)
                            @if($categoria['nombre'] == 'LICENCIA NO REMUNERADA')
                                <tr>
                                    <td width="80%" style="height: 20px;"
                                        class="left padding-left">{{ strtolower($categoria['nombre'] ? $categoria['nombre'] : 'sin definir') }}</td>
                                    <td width="20%" style="height: 20px;"
                                        class="center"> - {{$user->empresaObj->moneda}} {{ App\Funcion::Parsear($categoria['valor_categoria']) }}</td>
                                </tr>
                            @endif
                        @endif
            @endforeach
            
            <tr>
                <th width="80%" style="height: 30px;" class="left padding-left">Total neto a pagar al empleado</th>
                <th width="20%" style="height: 30px;"
                    class="center">{{$user->empresaObj->moneda}} {{ App\Funcion::Parsear($totalidad['pago']['total'] + ($adicionales)) }}</th>
            </tr>
        </table>
    </div>
    
    <div>
        
        @php $valorLiquidar = 0; @endphp
        @if($nomina->liquidacionComprobante())
            @php $valorLiquidar =  $nomina->liquidacionComprobante()->total; @endphp
            <br>
            <table border="0" class="titulo">
                
                <tr>
                    <th width="80%" style="height: 30px;" class="left padding-left">Concepto liquidación</th>
                    <th width="20%" style="height: 30px;">Valor</th>
                </tr>
            
                
                   <tr>
                        <td width="80%" style="height: 20px;" class="padding-left">liquidación por retiro</td>
                        <td width="20%" style="height: 20px;"
                         class="center">{{$user->empresaObj->moneda}} {{ App\Funcion::Parsear($valorLiquidar) }}</td>
                    </tr>
                 
                
            </table>
        @endif
    </div>

    
    <div style="width: 100%; display: table; height:auto; margin-top: 10px;">
    
        <div style="display: table-row;">
            <div style="width: 49%; display: table-cell; height:auto">
                @if($totalidad['pago']['extrasOrdinariasRecargos'] > 0)
                <h3 style="">HORAS EXTRA, ORDINARIAS Y RECARGOS</h3>
                <table border="0" class="titulo">
                    <tr>
                        <th width="60%" style="height: 30px;" class="left padding-left">Concepto</th>
                        <th width="20%" style="height: 30px;" class="center">Cantidad</th>
                        <th width="20%" style="height: 30px;" class="center">Valor</th>
                    </tr>
                    @php $totalCategoria = 0; $totalHoras = 0; @endphp
                    @foreach($totalDetallesNomina as $categoria)
                        @if($categoria['fk_nomina_cuenta'] == 1 && $categoria['valor_categoria'])
                            <tr>
                                <td width="60%" style="height: 20px;"
                                    class="left padding-left">{{ $categoria['nombre'] }}</td>
                                <td width="20%" style="height: 20px;"
                                    class="center">{{ $categoria['numero_horas'] }}</td>
                                <td width="20%" style="height: 20px;"
                                    class="center">{{$user->empresaObj->moneda}} {{ App\Funcion::Parsear($categoria['valor_categoria']) }}</td>
                            </tr>
                            @php $totalCategoria += $categoria['valor_categoria']; $totalHoras += $categoria['numero_horas']; @endphp
                        @endif
                    @endforeach
                    <tr>
                        <th width="60%" style="height: 30px;" class="left padding-left">Total pago por horas</th>
                        <th width="20%" style="height: 30px;"
                            class="center">{{ App\Funcion::Parsear($totalHoras) }}</th>
                        <th width="20%" style="height: 30px;"
                            class="center">{{$user->empresaObj->moneda}} {{ App\Funcion::Parsear($totalCategoria) }}</th>
                    </tr>
                </table>
                @endif
            </div>
            <div style="width: 2%; display: table-cell; height:auto">
            </div>
            <div style="display: table-cell; height:auto;">
                @if(1 == 1)
                <h3 style="">VACACIONES, INCAPACIDADES Y LICENCIAS</h3>
                <table border="0" class="titulo">
                    <tr>
                        <th width="60%" style="height: 30px;" class="left padding-left">Concepto</th>
                        <th width="20%" style="height: 30px;" class="center">Cantidad</th>
                        <th width="20%" style="height: 30px;" class="center">Valor</th>
                    </tr>
                    @php $totalCategoria = 0; $totalHoras = 0; @endphp
                    @foreach($totalDetallesNomina as $categoria)
                        @if($categoria['fk_nomina_cuenta'] == 2 && $categoria['horas'] > 0)
                            <tr>
                                <td width="60%" style="height: 20px;"
                                    class="left padding-left">{{ $categoria['nombre'] ? $categoria['nombre'] : 'sin definir' }}</td>
                                <td width="20%" style="height: 20px;"
                                    class="center">{{$horas = $categoria['horas']}}</td>
                                <td width="20%" style="height: 20px;"
                                    class="center">{{$user->empresaObj->moneda}} {{ App\Funcion::Parsear($categoria['valor_categoria']) }}</td>
                            </tr>
                            @php $totalCategoria += $categoria['valor_categoria']; $totalHoras += $horas; @endphp
                        @endif
                    @endforeach
                    <tr>
                        <th width="60%" style="height: 30px;" class="left padding-left">Total novedades</th>
                        <th width="20%" style="height: 30px;" class="center">{{$totalHoras}}</th>
                        <th width="20%" style="height: 30px;"
                            class="center">{{$user->empresaObj->moneda}} {{ App\Funcion::Parsear($totalCategoria) }}</th>
                    </tr>
                </table>
                @endif
            </div>
        </div>
    </div>

    <div style="width: 100%; display: table; height:auto; margin-top: 10px;">
        <div style="display: table-row;">
            <div style="width: 49%; display: table-cell; height:auto">
                <h3 style="">INGRESOS ADICIONALES</h3>
                <table border="0" class="titulo">
                    <tr>
                        <th width="80%" style="height: 30px;" class="left padding-left">Concepto</th>
                        <th width="20%" style="height: 30px;" class="center">Valor</th>
                    </tr>
                    @php $totalCategoria = 0; $totalHoras = 0; @endphp
                    @foreach($totalDetallesNomina as $categoria)
                        @if($categoria['fk_nomina_cuenta'] == 3 && $categoria['valor_categoria'] > 0)
                            <tr>
                                <td width="80%" style="height: 20px;"
                                    class="left padding-left">{{ $categoria['nombre'] }}</td>
                                <td width="20%" style="height: 20px;"
                                    class="center">{{$user->empresaObj->moneda}} {{ App\Funcion::Parsear($categoria['valor_categoria']) }}</td>
                            </tr>
                            @php  $totalCategoria += $categoria['valor_categoria']; @endphp
                        @endif
                    @endforeach
                    <tr>
                        <th width="80%" style="height: 30px;" class="left padding-left">Total ingresos adicionales</th>
                        <th width="20%" style="height: 30px;"
                            class="center">{{$user->empresaObj->moneda}} {{ App\Funcion::Parsear($totalCategoria) }}</th>
                    </tr>
                </table>
            </div>
            <div style="width: 2%; display: table-cell; height:auto">
            </div>
            <div style="display: table-cell; height:auto;">
                @if($totalidad['pago']['retencionesDeducciones'] > 0)
                <h3 style="">RETENCIONES Y DEDUCCIONES</h3>
                <table border="0" class="titulo">
                    <tr>
                        <th width="65%" style="height: 30px;" class="left padding-left">Concepto</th>
                        <th width="15%" style="height: 30px;" class="center">%</th>
                        <th width="20%" style="height: 30px;" class="center">Valor</th>
                    </tr>
                    <tr>
                        <th width="65%" style="height: 20px;" class="left padding-left">Retenciones</th>
                        <th width="15%" style="height: 20px;" class="center"></th>
                        <th width="20%" style="height: 20px;"
                            class="center">{{$user->empresaObj->moneda}} {{ App\Funcion::Parsear($totalidad['retenciones']['total']) }}</th>
                    </tr>
                    <tr>
                        <td width="65%" style="height: 20px;" class="left padding-left">Salud</td>
                        <td width="15%" style="height: 20px;" class="center">4%</td>
                        <td width="20%" style="height: 20px;"
                            class="center">{{$user->empresaObj->moneda}} {{ App\Funcion::Parsear($totalidad['retenciones']['salud']) }}</td>
                    </tr>
                    <tr>
                        <td width="65%" style="height: 20px;" class="left padding-left">Pension</td>
                        <td width="15%" style="height: 20px;" class="center">4%</td>
                        <td width="20%" style="height: 20px;"
                            class="center">{{$user->empresaObj->moneda}} {{ App\Funcion::Parsear($totalidad['retenciones']['pension']) }}</td>
                    </tr>
                    <tr>
                        <th width="65%" style="height: 20px;" class="left padding-left">Deducciones</th>
                        <th width="15%" style="height: 20px;" class="center"></th>
                        <th width="20%" style="height: 20px;"
                            class="center">{{$user->empresaObj->moneda}} {{ App\Funcion::Parsear($totalidad['deducciones']['total']) }}</th>
                    </tr>
                    @foreach($totalDetallesNomina as $deduccion)
                        @if($deduccion['fk_nomina_cuenta'] == 4)
                            <tr>
                                <td width="65%" style="height: 20px;"
                                    class="left padding-left">{{$deduccion['nombre']}}</td>
                                <td width="15%" style="height: 20px;" class="center"></td>
                                <td width="20%" style="height: 20px;"
                                    class="center">{{$user->empresaObj->moneda}} {{ App\Funcion::Parsear($deduccion['valor_categoria']) }}</td>
                            </tr>
                        @endif
                    @endforeach
                    <tr>
                        <th width="65%" style="height: 30px;" class="left padding-left">Total retenciones y
                            deducciones
                        </th>
                        <th width="15%" style="height: 30px;" class="center"></th>
                        <th width="20%" style="height: 30px;"
                            class="center">{{$user->empresaObj->moneda}} {{ App\Funcion::Parsear($totalidad['pago']['retencionesDeducciones']) }}</th>
                    </tr>
                </table>
                @endif
            </div>
        </div>
    </div>

    <div style="padding-top: 10%; text-align: center;">
        <div style="padding-top: 8%; text-align: center;">
            <div style="display: inline-block; width: 45%; border-top: 1px solid #000;     margin-right: 10%;">
                <p class="small"> ELABORADO POR: </p>
            </div>
            <div style="display: inline-block; width: 44%; border-top: 1px solid #000;">
                <p class="small"> ACEPTADA, FIRMA Y/O SELLO Y FECHA</p>
            </div>
        </div>
    </div>
    <div style="width: 100%;height:auto;">
        <div style="width: 50%; display: inline-block; text-align:left;">
            @if(isset($codqr))
            <img style="width:75%; height:auto; position:absolute; bottom:20px" src="{{asset('images/cadena_oficial.png')}}">
            @endif
        </div>
        <div style="width: 50%; display: inline-block; text-align:right;margin-left:100px;">
            <img style="width:75%; height:auto; position:absolute; bottom:10px;" src="{{asset('images/logo_factura.png')}}">
        </div>
    </div>

@endsection
