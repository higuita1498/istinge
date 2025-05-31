@extends('layouts.app')

@section('styles')
<style>
    .card-counter {
        box-shadow: 2px 2px 10px #DADADA;
        padding: 20px 10px;
        background-color: #fff;
        height: 100px;
        border-radius: 5px;
        transition: .3s linear all;
    }

    .card-counter:hover {
        box-shadow: 4px 4px 20px #DADADA;
        transition: .3s linear all;
    }

    .card-counter i {
        font-size: 4em;
        opacity: 0.2;
    }

    .card-counter .count-numbers {
        position: absolute;
        right: 35px;
        top: 20px;
        font-size: 32px;
        display: block;
    }

    .card-counter .count-name {
        position: absolute;
        right: 35px;
        top: 65px;
        font-style: italic;
        text-transform: capitalize;
        opacity: 0.5;
        display: block;
        font-size: 18px;
    }

    .table th {
        background-color: #f8f9fa;
    }
</style>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h4><i class="fas fa-tools"></i> Panel de Control - Materiales Asignados</h4>
        <hr>
    </div>
</div>

<!-- Cards de Resumen -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card-counter bg-success text-white">
            <i class="fas fa-clipboard-list float-left"></i>
            <span class="count-numbers">{{ count($materialesAgrupados) }}</span>
            <span class="count-name">Tipos de Material</span>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card-counter bg-warning text-white">
            <i class="fas fa-clock float-left"></i>
            <span class="count-numbers">{{ $asignacionesRecientes->count() }}</span>
            <span class="count-name">Asignaciones Recientes</span>
        </div>
    </div>
</div>

<!-- Tabla de Materiales Agrupados -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-layer-group"></i>
                    Resumen de Materiales por Tipo
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="tabla-materiales-agrupados">
                        <thead>
                            <tr>
                                <th>Material</th>
                                <th>Referencia</th>
                                <th>Cantidad Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($materialesAgrupados as $material)
                            <tr>
                                <td>{{ $material['nombre'] }}</td>
                                <td>{{ $material['ref'] }}</td>
                                <td>{{ $material['cantidad'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Asignaciones Recientes -->
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="fas fa-history"></i>
                    Últimas Asignaciones (30 días)
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="tabla-asignaciones">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Referencia</th>
                                <th>Material</th>
                                <th>Cantidad</th>
                                <th>Notas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($asignacionesRecientes as $asignacion)
                            @foreach($asignacion->items as $item)
                            <tr>
                                <td>{{ date('d-m-Y', strtotime($asignacion->fecha)) }}</td>
                                <td>{{ $asignacion->referencia }}</td>
                                <td>{{ $item->material->producto }}</td>
                                <td>{{ $item->cantidad }}</td>
                                <td>{{ $asignacion->notas }}</td>
                            </tr>
                            @endforeach
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Inicializar DataTables
        $('#tabla-materiales-agrupados').DataTable({
            language: {
                url: "{{asset('vendors/DataTables/es.json')}}"
            },
            order: [
                [2, 'desc']
            ], // Ordenar por cantidad total
            pageLength: 10
        });

        $('#tabla-asignaciones').DataTable({
            language: {
                url: "{{asset('vendors/DataTables/es.json')}}"
            },
            order: [
                [0, 'desc']
            ], // Ordenar por fecha
            pageLength: 10
        });
    });
</script>
@endsection