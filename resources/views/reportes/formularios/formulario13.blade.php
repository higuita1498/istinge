@extends('layouts.app')

@section('content')
    <div class="container m-auto px-4 py-3">
        <p>A continuación podrás generar el reporte para el Formulario 1.3.</p>
        <span id="vue-page">
            <formulario13-form errors="{{ $errors }}"></formulario13-form>
        </span>
    </div>
@endsection('content')
