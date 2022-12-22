@extends('layouts.app')

@section('styles')

@endsection

@section('content')

<div class="row">
    <div class="col-12 w-100">
           <h4 style="text-align:center"> <a href="{{ route('CortarFacturas', ['fechaCorte' => $fecha]) }}">Ejecutar 25 facturas con fecha de corte: {{$fecha}}</a> </h4>
    </div>
</div>
<div id="accordion">
    @forelse($gruposFaltantes as $gp)
        <div class="card">
            <div class="card-header" id="headingOne">
            <h5 class="mb-0">
                <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                {{ $gp->nombre }} {{ count($contactos[$gp->id]) }} / {{ ($gp->contratos() - $perdonados) }}    FALTANTES: {{ count($contactos[$gp->id]) }}
                </button>
            </h5>
            </div>

            <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
            <div class="card-body">

            </div>
            </div>
        </div>

        @empty

        <div class="alert alert-info center" role="alert">
                No hay facturas pendientes con fecha de vencimiento {{ $fecha }}
        </div>

    @endforelse
</div>

@endsection

@section('scripts')

@endsection
