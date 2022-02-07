@extends('layouts.app') 

@section('content')

    <div class="row">
<div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body color " >
                    <h4 class="card-title">TOTALES DE PAGOS</h4>
                    <table class="table table-striped table-hover " id="example">
                        <thead class="thead-dark">
                        <tr>
                            <th>Empresa </th>
                            <th>Pagos</th>
                        </tr>
                        </thead>
                        @foreach($pagos as $pago)
                            <tr>
                                <td>{{$pago->nombre}}</td>
                                <td>{{$pago->gastos}}</td>
                            </tr>
                            @endforeach

                            </tbody>
                    </table>
                    
                </div>

            </div>
        </div>
    </div>
    
    <div>
       @endsection
