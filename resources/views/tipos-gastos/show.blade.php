@extends('layouts.app')

@section('boton')

@endsection

@section('style')
<style>
    .card-header {
        background-color: {{ env('APP_COLOR') }};
        border-bottom: 1px solid {{ env('APP_COLOR') }};
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
                            <th class="bg-th" width="20%">DATOS GENERALES</th>
                            <th class="bg-th"></th>
                        </tr>
                        <tr>
                            <th>Nombre</th>
                            <td>{{ $tipo->nombre }}</td>
                        </tr>
                        <tr>
                            <th>Descripción</th>
                            <td><strong>{{ $tipo->descripcion }}</strong></td>
                        </tr>
                        <tr>
                            <th>Estado</th>
                            <td>
                                <strong class="text-{{$tipo->estado('true')}}">{{$tipo->estado()}}</strong>
                            </td>
                        </tr>
                        <tr>
                            <th>Creado por</th>
                            <td>{{ $tipo->created_by()->nombres }}</td>
                        </tr>
                        @if($tipo->updated_by)
                        <tr>
                            <th>Actualizado por</th>
                            <td>{{ $tipo->updated_by()->nombres }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>

</script>
@endsection
