@extends('layouts.app')

@section('content')
    <form method="POST" action="{{ route('productos.store_asignacion') }}" style="padding: 2% 3%;" role="form" class="forms-sample" id="form-asignacion">
        @csrf
        <div class="row">
            <div class="form-group col-md-4">
                <label class="control-label">Contrato / Cliente <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" name="contrato" id="contrato" required="" title="Seleccione" data-live-search="true" data-size="5">
                    @foreach($contratos as $contrato)
                    <option value="{{$contrato->id}}">{{$contrato->nombre}} {{$contrato->apellido1}} {{$contrato->apellido2}} - {{$contrato->nit}} [Contrato {{$contrato->nro}}]</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="control-label">Producto <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" name="producto" id="producto" required="" title="Seleccione" data-live-search="true" data-size="5">
                    @foreach($productos as $producto)
                    <option value="{{$producto->id}}">{{$producto->producto}} {{$contrato->ref}}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="control-label">¿Desea vender este router? <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" name="venta" id="venta_input" required="" title="Seleccione">
                    <option value="1">Si</option>
                    <option value="0">No</option>
                </select>
            </div>
            <div class="form-group col-md-4 d-none" id="div_tipo_pago">
                <label class="control-label">Tipo de Pago <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" name="tipo_pago" id="tipo_pago" title="Seleccione">
                    <option value="1">Contado</option>
                    <option value="2">Cuotas</option>
                </select>
            </div>
            <div class="form-group col-md-4 d-none" id="div_cuotas">
                <label class="control-label">Nro de Cuotas <span class="text-danger">*</span></label>
                <input type="number" class="form-control" name="cuotas" id="cuotas" min="1">
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

@section('scripts')
    <script>
        $('#venta_input').change(function() {
            if($('#venta_input').val() == 1){
                $("#tipo_pago").val('').selectpicker('refresh');
                $("#div_tipo_pago").removeClass('d-none');
            }else{
                $("#tipo_pago").val('').selectpicker('refresh');
                $("#cuotas").val('');
                $("#div_tipo_pago, #div_cuotas").addClass('d-none');
            }
        });

        $('#tipo_pago').change(function() {
            if($('#tipo_pago').val() == 2){
                $("#cuotas").val('');
                $("#div_cuotas").removeClass('d-none');
            }else{
                $("#cuotas").val('');
                $("#div_cuotas").addClass('d-none');
            }
        });
    </script>
@endsection
