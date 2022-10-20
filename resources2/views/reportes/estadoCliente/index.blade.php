@extends('layouts.app')
@section('content')
    <p  class="card-description">Consulta el estado de cuenta de un cliente en especifico</p>
    <hr>
    <div class="card-body">
        <form method="get" action="{{route('reportes.estadoClienteShow')}}">
            <div class="row">
                <div class="col-sm-1 mr-2">
                    <label for="clientes">Clientes:</label>
                </div>
                <div class="col-sm-3 mb-3">

                    <select name="client" id="client" class="selectpicker" data-live-search="true">
                        <option value="">Seleccione un cliente...</option>
                        @foreach($clientes as $cliente)
                            <option value="{{$cliente->id}}">
                                {{$cliente->nombre}} - {{$cliente->nit}}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-2">
                    <button type="submit" class="btn btn-primary">Consultar</button>
                </div>
            </div>
        </form>
    </div>
@endsection
