@extends('layouts.app')

@section('style')
<link rel="stylesheet" type="text/css" href="{{asset('css/lightbox/lightbox.min.css')}}">
@endsection

@section('content')

@if (session()->has('error'))
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    {{ session()->get('error') }}
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@endif

<div class="row card-description configuracion">
	<p style="margin-bottom:5px;margin-left: 13px;">Consulta y realiza el acuse de recepción, aceptación o rechazo de los documentos recibidos de tus proveedores. Ver más.</p>
</div>
<div class="row card-description configuracion">
        <div class="col-sm-3">
            <a href="{{route('recepcion.index')}}">Documentos Electrónicos</a> <br>
            <p>Genera el acuse de recepción y acepta o rechaza los documentos electrónicos válidos que recibas en tu buzón de correo</p>
        </div>

        <div class="col-sm-3">
            <a href="{{route('configuracion.create')}}">Buzón de correo</a> <br>
            <p>Consulta el historial de correos recibidos de tus proveedores en tu buzón</p>
        </div>
    </div>
@endsection