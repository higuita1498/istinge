@extends('layouts.app')

@section('content')
    <div class="row card-description configuracion">
        <div class="col-sm-3" style="border: 0px"></div>
        <div class="col-sm-6">
            <h4 class="card-title">{{$plan->nombre}}</h4>
            <p>Ingresos:
                <span class="font-weight-bold">
                    {{\App\Funcion::Parsear($plan->ingresos)}} $
                </span>
            </p>
            <p>
                Facturas:
                <span class="font-weight-bold">
                    {{$plan->facturas}}
                </span>
            </p>
            <p>
                Meses:
                <span class="font-weight-bold">
                    {{$plan->meses}}
                </span>
            </p>
            @if(!$pagoPersonal)
                <p>
                    <a class="text-black" href="{{route('planes.p_pagos', [$price, $idPlan])}}"> <b>Pagar.</b></a>
                </p>
            @endif
        </div>
    </div>
@endsection
