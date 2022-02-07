@extends('layouts.app') 

@section('content')

    <div class="row">
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body color " >
                    <h4 class="card-title">TOTALES DE FACTURAS </h4>
                    <table class="table table-striped table-hover" id="example">
                        <thead class="thead-dark">
                        <tr>
                            <th>Empresa </th>
                            <th>Facturas</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($facturas as $factura)
                            <tr>
                                <td>{{$factura->nombre}}</td>
                                <td>{{$factura->facturas}}</td>
                            </tr>
                        @endforeach

                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    
    <div>
       @endsection