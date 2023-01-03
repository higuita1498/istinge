@extends('layouts.app')

@section('boton')
    <a href="{{route('descuentos.index')}}" class="btn btn-outline-danger btn-sm"><i class="fas fa-backward"></i> Regresar</a>
@endsection

@section('style')
<style>
    .card-header {
        background-color: rgb(49 126 191);
        border-bottom: 1px solid rgb(49 126 191);
    }
</style>
@endsection

@section('content')
    <div class="row card-description">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-sm info">
                    <tbody>
                        <tr>
                            <th class="bg-th text-center" width="20%">DATOS GENERALES</th>
                            <th class="bg-th text-center"></th></th>
                        </tr>
                        <tr>
                            <th>Descuento Nro</th>
                            <td>{{$descuento->id}}</td>
                        </tr>
                        <tr>
                            <th>Descuento</th>
                            <td>{{$descuento->descuento}}%</td>
                        </tr>
                        <tr>
                            <th>Factura</th>
                            <td>{{$descuento->factura()->codigo}}</td>
                        </tr>
                        <tr>
                            <th>Cliente</th>
                            <td>{{$descuento->factura()->cliente()->nombre}} {{$descuento->factura()->cliente()->apellidos()}}</td>
                        </tr>
                        <tr>
                            <th>Estado</th>
                            <td><span class='text-{{$descuento->estado("true")}}'><strong>{{$descuento->estado()}}</strong></span></td>
                        </tr>
                        <tr>
                            <th>Creado por</th>
                            <td>{{ $descuento->created_by()->nombres }}</td>
                        </tr>
                        @if($descuento->updated_by)
                        <tr>
                            <th>Aprobado por</th>
                            <td>{{ $descuento->updated_by() }} / {{ date('d-m-Y g:i:s A', strtotime($descuento->updated_at)) }}</td>
                        </tr>
                        @endif
                        <tr style="white-space: normal;">
                            <th>Comentario</th>
                            <td style="white-space: normal;">{{ $descuento->comentario }}</td>
                        </tr>
                        </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
