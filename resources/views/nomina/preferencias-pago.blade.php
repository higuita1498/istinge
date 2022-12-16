@extends('layouts.app')
@section('style')
<style>
    .paper::before {
        display: none;
    }
</style>
@endsection
@section('content')
@if(Session::has('error'))
<div class="alert alert-danger">
    {{Session::get('error')}}
</div>
@endif

@if(Session::has('success'))
<div class="alert alert-success">
    {{Session::get('success')}}
</div>

<script type="text/javascript">
    setTimeout(function() {
        $('.alert').hide();
        $('.active_table').attr('class', ' ');
    }, 5000);
</script>
@endif
{{-- @include('nomina.tips.serie-base', ['pasos' => \collect([8])->diff($guiasVistas->keyBy('nro_tip')->keys())->all()]) --}}
<div class="paper p-0">

    <form method="POST" action="{{route('nomina.preferecia-pago.store')}}" style="padding: 2% 3%;" role="form" class="forms-sample" id="idpreferencia">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="frecuencia_pago">Frecuencia de pago</label>
                    <select class="form-control selectpicker" id="frecuencia_pago" name="frecuencia_pago" title="Seleccione la frecuencia de pago" data-live-search="true" data-size="5">
                        <option value="1" {{ isset($preferencia) && $preferencia ? ($preferencia->frecuencia_pago == 1 ? 'selected' : '') : '' }}>Frecuencia Quincenal</option>
                        <option value="2" {{ isset($preferencia) && $preferencia ? ($preferencia->frecuencia_pago == 2 ? 'selected' : '') : '' }}>Frecuencia Mensual</option>
                    </select>
                    @error('frecuencia_pago')
                    <span class="help-block error">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                    <input type="hidden" value="{{ isset($preferencia) && $preferencia ? $preferencia->frecuencia_pago : '' }}" id="preferenciahidden" />
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="medio_pago">Medio de pago</label>
                    <select class="form-control selectpicker" id="medio_pago" name="medio_pago" title="Seleccione el medio de pago" data-live-search="true" data-size="5" onchange="validarMedio(this.value)">
                        @foreach ($mediosPago as $medio)
                        <option value="{{$medio->id}}" {{ isset($preferencia) ? ($preferencia->medio_pago == $medio->id ? 'selected' : '') : '' }}>{{$medio->metodo}}</option>
                        @endforeach
                    </select>
                    @error('medio_pago')
                    <span class="help-block error">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="banco">Banco del cual realizarás los pagos</label>
                    <select class="form-control selectpicker" id="banco" name="banco" title="Seleccione el banco" data-live-search="true" data-size="5">
                        @foreach ($bancos as $banco)
                        <option value="{{$banco->id}}" {{ isset($preferencia) ? ($preferencia->banco == $banco->id ? 'selected' : '') : '' }}>{{$banco->nombre}}</option>
                        @endforeach
                    </select>
                    @error('banco')
                    <span class="help-block error">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tipo_cuenta">Tipo de cuenta</label>
                    <select class="form-control selectpicker" id="tipo_cuenta" name="tipo_cuenta" title="Seleccione el tipo de cuenta" data-live-search="true" data-size="5">
                        <option value="1" {{ isset($preferencia) ? ($preferencia->tipo_cuenta == 1 ? 'selected' : '') : '' }}>Cuenta de ahorros</option>
                        <option value="2" {{ isset($preferencia) ? ($preferencia->tipo_cuenta == 2 ? 'selected' : '') : '' }}>Cuenta corriente</option>
                    </select>
                    @error('tipo_cuenta')
                    <span class="help-block error">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="frecuencia_pago">Número de cuenta</label>
                    <input type="number" class="form-control" name="nro_cuenta" id="nro_cuenta" value="{{$preferencia->nro_cuenta ?? ''}}">
                    @error('nro_cuenta')
                    <span class="help-block error">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>
            <div class="col-md-6 d-none">
                <div class="form-group">
                    <label for="operador_pago">Operador de pago</label>
                    <select class="form-control selectpicker" id="operador_pago" name="operador_pago" title="Seleccione el operador de pago" data-live-search="true" data-size="5">
                        <option value=""></option>
                    </select>
                    @error('operador_pago')
                    <span class="help-block error">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="arl">ARL que utilizas</label>
                    <select class="form-control selectpicker" id="arl" name="arl" title="Seleccione el ARL" data-live-search="true" data-size="5">
                        @foreach ($aseguradoras as $aseguradora)
                        <option value="{{ $aseguradora->id}}" {{isset($preferencia) ? ($preferencia->arl == $aseguradora->id ? 'selected' : '') : '' }}>{{ $aseguradora->nombre }}</option>
                        @endforeach
                    </select>
                    @error('arl')
                    <span class="help-block error">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="fecha_constitucion">Fecha de constitución <a><i data-tippy-content="Se refiere a la fecha de creación de la empresa" class="icono far fa-question-circle"></i></a></label>
                    <input type="text" class="form-control datepicker" readonly name="fecha_constitucion" id="fecha_constitucion" value="{{$preferencia->fecha_constitucion  ?? ''}}">
                    @error('fecha_constitucion')
                    <span class="help-block error">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <button type="button" onclick="cambioFrecuencia()" class="btn btn-success rounded float-right">Guardar</button>
            </div>
        </div>
    </form>
</div>

@endsection



@section('scripts')
<script>
    $('.datepicker').datepicker({
        uiLibrary: 'bootstrap4',
        iconsLibrary: 'fontawesome',
        format: 'yyyy-mm-dd',
        locale: 'es-es',
        keyboardNavigation: true,
    });

    function cambioFrecuencia(){
        
    
        preferencia_escogida = $("#frecuencia_pago").val();
        preferencia = $("#preferenciahidden").val();

       // $("#idpreferencia").submit();
        
        if(preferencia != preferencia_escogida){

            Swal.fire({
                title: '¿Está seguro?',
                text: "La frecuencia de pago del mes actual cambiara y los datos del periodo serán restablecidos",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'SI'
                }).then((result) => {
                    if (result.value) {
                        Swal.fire(
                            'Cambio en proceso',
                            '',
                            'success'
                            )
                        $("#idpreferencia").submit();
                }
                })
        }else{

            Swal.fire({
                title: '¿Está seguro?',
                text: "Los datos del periodo actual serán restablecidos",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'SI'
                }).then((result) => {
                    if (result.value) {
                        Swal.fire(
                            'Cambio en proceso',
                            '',
                            'success'
                            )
                        $("#idpreferencia").submit();
                }
                })

        }

    }

    function validarMedio(medio) {
        if (medio == 1) {
            let banco = $('#banco');
            banco.val('');
            banco.selectpicker('refresh');
            banco.attr('disabled', true);

            let tipo_cuenta = $('#tipo_cuenta');
            tipo_cuenta.val('');
            tipo_cuenta.selectpicker('refresh');
            tipo_cuenta.attr('disabled', true);

            let nro_cuenta = $('#nro_cuenta');
            nro_cuenta.val('');
            nro_cuenta.attr('disabled', true);


        } else {
            $('#banco').attr('disabled', false);
            $('#tipo_cuenta').attr('disabled', false);
            $('#nro_cuenta').attr('disabled', false);
        }
    }
</script>


<script>
    $(document).ready(function() {

        firstTip = $('.tour-tips').first().attr('nro_tip');

        if (firstTip) {
            nuevoTip(firstTip, 7000);
        }

    });
</script>
@endsection