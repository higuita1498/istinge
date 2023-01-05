@extends('layouts.app')

@section('styles')

@endsection

@section('content')

<div class="row">
    <div class="col-12 w-100">
           <h4 style="text-align:center"> <a href="{{ route('CortarFacturas', ['fechaCorte' => $fecha]) }}">Existen {{ $totalFacturas }} facturas abiertas por cortar con fecha de vencimiento: {{ $fecha }} (clic para enviar max: 25)</a> </h4>
    </div>
    <br>
</div>
<br>
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
                No hay facturas pendientes por cortar con fecha de vencimiento: {{ $fecha }}
        </div>

    @endforelse
</div>
<div class="row ml-3 mt-5">
<div class="col-4">
<h5 style="font-weight:bold">CANTIDAD DE CONTRATOS: {{ $cantidadContratos }}<h5>
</div>
</div>
<div class="row ml-3 mt-5">
@if(!$request->generadas)
<div class="col-4">
    <a href="{{ route('grupos-corte.estados', [$grupo, $fecha, 'generadas' => 'facturas']) }}" target="_blank"><h5>Ver ({{ $facturasGeneradas->count() }}) Facturas generadas en la fecha</h5></a>
</div>
@else
<div class="col-4">
    <a href="{{ route('grupos-corte.estados', [$grupo, $fecha]) }}" target="_blank"><h5>Ver ({{ $facturasCortadas->count() }}) Facturas vencidas y cortadas</h5></a>
</div>
@endif
</div>
<div class="row card-description w-100">
<div class="col-md-12">
    @if($request->generadas)
    <h3 style="text-align: center">Facturas generadas ({{ $facturasGeneradas->count() }})</h3>
    @php $data = $facturasGeneradas; @endphp
    @else
    @php $data = $facturasCortadas; @endphp
    <h3 style="text-align: center">Facturas vencidas y cortadas ({{ $facturasCortadas->count() }})</h3>
    @endif
    <table class="table table-striped table-hover w-100" id="table-cortadas">
        <thead class="thead-dark">
            <tr>
                <th>Nro</th>
                <th>Cliente</th>
                <th>Grupo</th>
                <th>Fecha Factura</th>
                <th>Fecha Pago</th>
                <th>Fecha Corte</th>
                <th>Fecha Suspensión</th>
                <th>Hora Suspensión</th>
            </tr>
        </thead>
        <tbody>
            
            @foreach($data as $facturaC)
            <tr>
                <td><a href="{{ route('facturas.show', $facturaC->id) }}" target="_blank">{{ $facturaC->codigo ?? $facturaC->nro }}</a></td>
                <td><a href="{{ route('contactos.show', $facturaC->cliente) }}" target="_blank"> {{ $facturaC->nombreCliente }} </a></td>
                <td><a href="{{ route('grupos-corte.show', $facturaC->idGrupo) }}" target="_blank"> {{ $facturaC->nombreGrupo }} </a></td>
                <td>@if($facturaC->fecha) {{ date('d-m-Y', strtotime($facturaC->fecha))  }} @endif</td>
                <td>@if($facturaC->pago_oportuno) {{ date('d-m-Y', strtotime($facturaC->pago_oportuno)) }} @endif</td>
                <td>@if($facturaC->vencimiento) {{ date('d-m-Y', strtotime($facturaC->vencimiento)) }} @endif</td>
                <td>@if($facturaC->suspension) {{ date('d-m-Y', strtotime($facturaC->suspension)) }} @endif</td>
                <td>{{ $facturaC->hora_suspension }}</td>
            </tr>
            @endforeach
            
        </tbody>
    </table>

</div>
</div>




@endsection

@section('scripts')

<script>

    $(document).ready(function() {

        $('#table-cortadas').DataTable({
			responsive: true,
			searching: false,
			language: {
				'url': '/vendors/DataTables/es.json'
			},
			order: [
				[0, "desc"]
			]});

    });

</script>

@endsection
