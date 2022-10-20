@extends('layouts.app')

@section('content')
    <form method="POST" action="{{ route('productos.store_devolucion') }}" style="padding: 2% 3%;" role="form" class="forms-sample" id="form-asignacion">
        @csrf
        <div class="row">
            <div class="form-group col-md-4">
                <label class="control-label">Cliente / Contrato / Producto <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" name="producto" id="producto" required="" title="Seleccione" data-live-search="true" data-size="5">
                    @foreach($contratos as $contrato)
                    <option value="{{$contrato->id}}">{{$contrato->nombre}} {{$contrato->apellido1}} {{$contrato->apellido2}} - {{$contrato->nit}} [Contrato {{$contrato->nro}}] [{{ $contrato->producto }} - {{ $contrato->ref }}]</option>
                    @endforeach
                </select>
            </div>
            @if(Auth::user()->empresa()->oficina)
                <div class="form-group col-md-3">
                    <label class="control-label">Oficina Asociada <span class="text-danger">*</span></label>
                    <select class="form-control selectpicker" name="oficina" id="oficina" required="" title="Seleccione" data-live-search="true" data-size="5">
                        @foreach($oficinas as $oficina)
                        <option value="{{$oficina->id}}" {{ $oficina->id == auth()->user()->oficina ? 'selected' : '' }}>{{$oficina->nombre}}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>
        <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
        <hr>
        <div class="row" >
            <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
                <a href="{{route('productos.index_asignacion')}}" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
            </div>
        </div>
    </form>
@endsection