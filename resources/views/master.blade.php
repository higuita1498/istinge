@extends('layouts.app') 

@section('content')
<div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
        <div class="card">
            <div class="card-body color " >
                <h4 class="card-title">LISTADO DE EMPRESAS</h4>
                <table class="table table-striped" id="example">
                        <thead class="thead-dark">
                        <tr>
                            <th>Empresa </th>
                            <th>Fec.Creacion</th>
                            <th>Fec.Vencimiento</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($empresas as $empresa)
                            @php
                                $fecha_inicio = $empresa->created_at;
                                $fecha_final = date('Y-m-d', strtotime($empresa->created_at."+ 2 month"));
                                $dias_ = $fecha_inicio->diffInDays($fecha_final);
                                $dias_1 = $fecha_inicio->diffInDays(\Carbon\Carbon::now());
                                $tot_dias = $dias_ - $dias_1;
                                if($tot_dias<0){
                                    $tot_dias = 0;
                                }
                            @endphp
                            <tr>
                                <td>{{$empresa->nombre}}</td>
                                <td>{{date('d-m-Y', strtotime($empresa->created_at))}}</td>
                                <td>{{$tot_dias}}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
            </div>

        </div>
    </div>
</div>
@endsection