@extends('layouts.app')

@section('styles')

@endsection

@section('content')

<div class="row">
    <div class="col-12 w-100">
           <h4 style="text-align:center"> <a href="{{ route('CortarFacturas', ['fechaCorte' => $fecha]) }}">Existen {{ $totalFacturas }} facturas abiertas con fecha de vencimiento: {{ $fecha }} (enviar max: 25)</a> </h4>
    </div>
</div>
<div id="accordion">
    @forelse($gruposFaltantes as $gp)
        <div class="card">
            <div class="card-header" id="headingOne">
            <h5 class="mb-0">
                <button class="btn btn-link" data-toggle="collapse" data-target="#collapse{{ $gp->id }}" aria-expanded="true" aria-controls="collapse{{ $gp->id }}">
                {{ $gp->nombre }}  ( FALTAN <span style="color:red">{{ count($contactos[$gp->id]) }}</span> FACTURAS ABIERTAS POR CORTAR )
                </button>
            </h5>
            </div>

            <div id="collapse{{ $gp->id }}" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
            <div class="card-body">
                <ul class="list-group">
                    @foreach($contactos[$gp->id] as $c)
                    <li class="list-group-item"><a href="{{route('facturas.show', $c->factura)}}" target="_blank">{{ $c->nombre }}  | Factura: {{ $c->codigo }}</li>
                    @endforeach
                </ul>
            </div>
            </div>
        </div>

        @empty

        <div class="alert alert-info center" role="alert">
                No hay facturas pendientes con fecha de vencimiento: {{ $fecha }}
        </div>

    @endforelse
</div>

@endsection

@section('scripts')

@endsection
