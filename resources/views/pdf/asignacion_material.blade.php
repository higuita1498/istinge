@extends('layouts.pdf')

@section('content')
<style type="text/css">
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
        font-size: 12px;
        line-height: 12px;
        padding-right: 2px;
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
        margin-bottom: 20px;
    }

    .desgloce {
        width: 100%;
        overflow: hidden;
        border-collapse: collapse;
        border-radius: 0.4em;
    }

    td {
        border: 1px solid #9e9b9b;
        padding: 3px;
    }

    th {
        border: 1px solid #ccc;
        background: #f0f0f0;
        padding: 3px;
    }

    .border_bottom {
        border-bottom: 2px solid #ccc !important;
    }

    .padding-right {
        padding-right: 10px !important;
    }

    .padding-left {
        padding-left: 10px !important;
    }
</style>

<div style="width: 100%;">
    <div style="width: 20%; display: inline-block; vertical-align: top; text-align: center;">
        <img src="{{public_path('images/Empresas/Empresa'.$empresa->id.'/'.$empresa->logo)}}" alt="Logo" style="width: 100%;">
    </div>
    <div style="width: 57%; text-align: center; display: inline-block;">
        <h4>{{$empresa->nombre}}</h4>
        <p style="line-height: 12px;">
            NIT: {{$empresa->nit}} <br>
            {{$empresa->direccion}} <br>
            {{$empresa->telefono}}
            @if($empresa->web)
            <br>{{$empresa->web}}
            @endif
            <br>{{$empresa->email}}
        </p>
    </div>
    <div style="width: 20%; display: inline-block; text-align: center; vertical-align: top; margin-top: 2%;">
        <h4>Asignación de Material</h4>
        <p class="small">No. {{$asignacion->referencia}}</p>
    </div>
</div>

<!-- Información del Técnico -->
<table class="titulo">
    <tr>
        <th width="20%" class="right smalltd">TÉCNICO</th>
        <td colspan="3">{{$asignacion->tecnico->nombres}}</td>
        <th width="20%" class="center smalltd">FECHA</th>
    </tr>
    <tr>
        <th class="right smalltd">IDENTIFICACIÓN</th>
        <td colspan="3">{{$asignacion->tecnico->cedula}}</td>
        <td class="center">{{date('d/m/Y', strtotime($asignacion->fecha))}}</td>
    </tr>
    @if($asignacion->notas)
    <tr>
        <th class="right smalltd">NOTAS</th>
        <td colspan="4">{{$asignacion->notas}}</td>
    </tr>
    @endif
</table>

<!-- Tabla de Materiales -->
<table class="desgloce">
    <thead>
        <tr>
            <th width="10%" class="center">Código</th>
            <th width="40%" class="center">Material</th>
            <th width="35%" class="center">Descripción</th>
            <th width="15%" class="center">Cantidad</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
        <tr>
            <td class="center">{{$item->id_material}}</td>
            <td class="left padding-left">{{$item->nombre}}</td>
            <td class="left padding-left">{{$item->descripcion}}</td>
            <td class="center">{{$item->cantidad}}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<!-- Firmas -->
<div style="margin-top: 50px;">
    <table style="width: 100%; text-align: center;">
        <tr>
            <td style="width: 50%; border: none;">
                <div style="border-top: 1px solid #000; margin: 0 50px;">
                    <p>Firma del Técnico<br>{{$asignacion->tecnico->nombres}}</p>
                </div>
            </td>
            <td style="width: 50%; border: none;">
                <div style="border-top: 1px solid #000; margin: 0 50px;">
                    <p>Firma y Sello<br>{{$empresa->nombre}}</p>
                </div>
            </td>
        </tr>
    </table>
</div>

<div style="position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px;">
    <p>Documento generado el {{date('d/m/Y H:i:s')}}</p>
</div>
@endsection