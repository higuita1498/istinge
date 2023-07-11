@extends('layouts.pdf')

@section('content')
    <style type="text/css">
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
        .text-center{
            text-align: center !important; 
        }

    </style>

    <div style="width: 100%;">
        <div style="width: 20%; display: inline-block; vertical-align: top; text-align: center; ">
            <img src="{{asset('images/Empresas/Empresa'.Auth::user()->empresa.'/'.Auth::user()->empresa()->logo)}}" alt="" style="width: 100%;">
        </div>
        <div style="width: 57%; text-align: center; display: inline-block;">
            <h4>{{Auth::user()->empresa()->nombre}}</h4>
            <p style="line-height: 12px;">{{Auth::user()->empresa()->tip_iden('mini')}} {{Auth::user()->empresa()->nit}} <br>
                {{Auth::user()->empresa()->direccion}} <br>
                {{Auth::user()->empresa()->telefono}} 
                @if(Auth::user()->empresa()->web)
                    <br>{{Auth::user()->empresa()->web}} 
                @endif
                <br> <a href="mailto:{{Auth::user()->empresa()->email}}" target="_top">{{Auth::user()->empresa()->email}}</a> 
            </p>

        </div>
        <div style="width: 20%; display: inline-block; text-align: left;    vertical-align: top;
    margin-top: 2%;">
            <p class="small text-center">Transferencia entre bodegas</p>
            <h4 class=" text-center">No. #{{$transferencia->nro}}</h4>
            
        </div>
    </div>
    <div style="">
        <table border="1" class="titulo">
            <tr>
                <th class="right smalltd">Bodega destino</th>
                <td style="border-top: 2px solid #ccc; width: 500px;">{{$transferencia->bodega()->bodega}}</td>
                <th  style="text-align: center;">Fecha (DD/MM/AA)</th>
            </tr> 
            <tr>
                <th class="right smalltd">Bodega origen</th>
                <td style="border-top: 2px solid #ccc; width: 500px;">{{$transferencia->bodega('destino')->bodega}}</td>
                <td  style="text-align: center;">{{date('d-m-Y', strtotime($transferencia->fecha))}}</td>
            </tr> 
        </table>
    </div>


    <div style="margin-top: 2%;">
        <table border="0" class="desgloce" >
            <thead>
                <tr>
                    <th style="padding: 3px;" width="20%" class="center smalltd">Referencia</th>
                    <th style="padding: 3px;"  class="center smalltd">Ítem</th>
                    <th style="padding: 3px;" width="15%" class="center smalltd">Cantidad</th>
                </tr>
            </thead>
            <tbody>
                 @php $cont=0; @endphp
                @foreach($trans as $item)

                @php $cont++; @endphp
                    <tr>
                        <td class="left padding-left border_left @if($cont==$itemscount && $cont>6) border_bottom @endif" style="text-align: center !important;">{{$item->producto()->ref}} </td>
                        <td class="right padding-right  @if($cont==$itemscount && $cont>6) border_bottom @endif" style="text-align: left !important;">{{$item->producto()->producto}}</td>
                        <td class="right padding-right border_right  @if($cont==$itemscount && $cont>6) border_bottom @endif" style="text-align: center !important;">{{$item->nro}}</td>
                    </tr>

                @endforeach
                @if($cont<7)
                @php $cont=7-$cont; @endphp
                    @for($i=1; $i<=$cont; $i++)
                        <tr>
                            <td class="border_left @if($cont==$i) border_bottom @endif" style="height: 15px;"></td>
                            <td class=" @if($cont==$i) border_bottom @endif" style="height: 15px;"></td>
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
                <p class="small"> ELABORADO POR</p>
            </div>
        </div>
    </div>

    
     

@endsection