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


<div class="row card-description w-100">
<div class="col-md-12">
    <h3 style="text-align: center">Facturas vencidas y cortadas ({{ $facturasCortadas->count() }})</h3>
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
            
            @foreach($facturasCortadas as $facturaC)
            <tr>
                <td><a href="{{ route('facturas.show', $facturaC->id) }}" target="_blank">{{ $facturaC->codigo ?? $facturaC->nro }}</a></td>
                <td><a href="{{ route('contactos.show', $facturaC->cliente) }}" target="_blank"> {{ $facturaC->nombreCliente }} </a></td>
                <td><a href="{{ route('grupos-corte.show', $facturaC->idGrupo) }}" target="_blank"> {{ $facturaC->nombreGrupo }} </a></td>
                <td>{{ date('d-m-Y', strtotime($facturaC->fecha))  }}</td>
                <td>{{ date('d-m-Y', strtotime($facturaC->pago_oportuno)) }}</td>
                <td>{{ date('d-m-Y', strtotime($facturaC->vencimiento)) }}</td>
                <td>{{ date('d-m-Y', strtotime($facturaC->suspension)) }}</td>
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
