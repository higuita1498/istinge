@extends('layouts.app')

@section('content')

<form method="post" action="{{ route('nomina.liquidar.update') }}">
    @csrf


<input type="hidden" name="idPersona" value="{{ $persona->id }}"/>
<input type="hidden" name="idComprobante" value="{{ $comprobanteLiquidacion->id }}"/>
<input type="hidden" name="idContrato" value="{{ $contratoPersona->id }}"/>

<div class="div-content-1 p-5">

    <div class="row">

        <div class="col-6">

            <h3>1. Causas liquidación</h3>
            <div class="form-group">
                <label class="control-label">¿Motivo de liquidación?<span class="text-danger">*</span></label>
                <textarea class="form-control" name="motivo" id="motivo" required>{{ $comprobanteLiquidacion->motivo }}</textarea>

                <span class="help-block error">
                    <strong>{{ $errors->first('motivo') }}</strong>
                </span>
            </div>
            <label for="motivo"></label>


            <div class="form-group">
                <label class="control-label">¿Liquidación con justa causa? <span class="text-danger">*</span></label>
                <div class="form-check">
                    <input class="form-check-input liquidar" type="radio" name="isCausal" id="isCausal1" value="1" {{ $comprobanteLiquidacion->is_justa_causa == 1 ? 'checked' : '' }}>
                    <label class="form-check-label" for="isCausal1">
                      SI
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input liquidar" type="radio" name="isCausal" id="isCausal2" value="0" {{ $comprobanteLiquidacion->is_justa_causa == 0 ? 'checked' : '' }}>
                    <label class="form-check-label" for="isCausal2">
                        NO
                    </label>
                </div>

                <span class="help-block error">
                    <strong>{{ $errors->first('isCausal') }}</strong>
                </span>
            </div>

            <div class="form-group">
                <label class="control-label">Período de prueba <span class="text-danger">*</span></label>
                <div class="form-check">
                    <input class="form-check-input liquidar" type="radio" name="isPrueba" id="isPrueba1" value="1" {{ $comprobanteLiquidacion->is_periodo_prueba == 1 ? 'checked' : '' }}>
                    <label class="form-check-label" for="isPrueba1">
                      SI
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input liquidar" type="radio" name="isPrueba" id="isPrueba2"  value="0" {{ $comprobanteLiquidacion->is_periodo_prueba == 0 ? 'checked' : '' }}>
                    <label class="form-check-label" for="isPrueba2">
                        NO
                    </label>
                </div>

                <span class="help-block error">
                    <strong>{{ $errors->first('isPrueba') }}</strong>
                </span>
            </div>

        </div>

        <div class="col-6">

            <h3>2. Fechas liquidación</h3>

            <div class="form-group">
                <label class="control-label">Fecha de contratación<span class="text-danger">*</span></label>

                <input type="text" class="form-control liquidar-fecha"  id="fechaContratacion" value="{{ date('d-m-Y', strtotime($comprobanteLiquidacion->fecha_contratacion)) }}" name="fechaContratacion">

                <span class="help-block error">
                    <strong>{{ $errors->first('fechaContratacion') }}</strong>
                </span>
            </div>


            <div class="form-group">
                <label class="control-label">Fecha de terminación<span class="text-danger">*</span></label>

                <input type="text" class="form-control liquidar-fecha"  id="fechaTerminacion" value="{{ date('d-m-Y', strtotime($comprobanteLiquidacion->fecha_terminacion)) }}" name="fechaTerminacion">

                <span class="help-block error">
                    <strong>{{ $errors->first('fechaTerminacion') }}</strong>
                </span>
            </div>

            <div class="form-group">
                <label class="control-label">Días a liquidar<span class="text-danger">*</span></label>

                <input type="number" value="{{ $comprobanteLiquidacion->dias_liquidar}}" class="form-control" id="diasLiquidar" name="diasLiquidar" onchange="resumenLiquidacion(false);">

                <span class="help-block error">
                    <strong>{{ $errors->first('diasLiquidar') }}</strong>
                </span>
            </div>


            <div class="form-group">
                <label class="control-label">Días de vacaciones<span class="text-danger">*</span></label>

                <input type="number" class="form-control liquidar"  id="diasVacaciones" name="diasVacaciones" value="{{ $comprobanteLiquidacion->dias_vacaciones }}" step="any">

                <span class="help-block error">
                    <strong>{{ $errors->first('diasVacaciones') }}</strong>
                </span>
            </div>

        </div>

    </div>

    <div class="row">

        <div class="col-6">

            <h3>3. Bases cálculo liquidación</h3>

            <div class="form-group col-md-12">
                <label class="control-label">Salario Base <span class="text-danger">*</span></label>
                <input type="text" class="form-control liquidar" name="salarioBase" id="salarioBase" maxlength="200" value="{{ number_format($comprobanteLiquidacion->base_salario) }}" required="">
                <span class="help-block error">
                    <strong>{{ $errors->first('salarioBase') }}</strong>
                </span>
            </div>

            <div class="form-group col-md-12">
                <label class="control-label">Vacaciones <span class="text-danger">*</span></label>
                <input type="text" class="form-control liquidar" name="vacaciones" id="vacaciones" maxlength="200" value="{{ number_format($comprobanteLiquidacion->base_vacaciones) }}" required="">
                <span class="help-block error">
                    <strong>{{ $errors->first('vacaciones') }}</strong>
                </span>
            </div>

            <div class="form-group col-md-12">
                <label class="control-label">Cesantías <span class="text-danger">*</span></label>
                <input type="text" class="form-control liquidar" name="cesantias" id="cesantias" maxlength="200" value="{{ number_format($comprobanteLiquidacion->base_cesantias) }}" required="">
                <span class="help-block error">
                    <strong>{{ $errors->first('cesantias') }}</strong>
                </span>
            </div>

            <div class="form-group col-md-12">
                <label class="control-label">Prima <span class="text-danger">*</span></label>
                <input type="text" class="form-control liquidar" name="prima" id="prima" maxlength="200" value="{{ number_format($comprobanteLiquidacion->base_prima) }}" required="">
                <span class="help-block error">
                    <strong>{{ $errors->first('prima') }}</strong>
                </span>
            </div>

        </div>

        <div class="col-6">

            <h3>4. Opciones adicionales</h3>

            <div class="form-group">
                <label class="control-label">¿Incluir dominicales?<span class="text-danger">*</span></label>
                <div class="form-check">
                    <input class="form-check-input liquidar" type="radio" name="isIncluirDominicales" id="isIncluirDominicales1" value="1" {{ $comprobanteLiquidacion->is_dominicales == 1 ? 'checked' : '' }}>
                    <label class="form-check-label" for="isIncluirDominicales1">
                      SI
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input liquidar" type="radio" name="isIncluirDominicales" id="isIncluirDominicales2" value="0" {{ $comprobanteLiquidacion->is_dominicales == 0 ? 'checked' : '' }}>
                    <label class="form-check-label" for="isIncluirDominicales2">
                        NO
                    </label>
                </div>

                <span class="help-block error">
                    <strong>{{ $errors->first('isIncluirDominicales') }}</strong>
                </span>
            </div>

        </div>

    </div>

    <div class="row">

        <div class="col-6">

            <h3>5. Otros ingresos</h3>

            <div class="form-group col-md-12">
                <label class="control-label">Otros ingresos <span class="text-danger">*</span></label>
                <input type="text" class="form-control liquidar" name="otrosIngresos" id="otrosIngresos" maxlength="200" min="0" required="" value="{{ number_format($comprobanteLiquidacion->otros_ingresos) }}">
                <span class="help-block error">
                    <strong>{{ $errors->first('otrosIngresos') }}</strong>
                </span>
            </div>

        </div>

        <div class="col-6">

            <h3>6. Otros egresos</h3>

            <div class="form-group col-md-12">
                <label class="control-label">Valor préstamos <span class="text-danger">*</span></label>
                <input type="text" class="form-control liquidar" name="valorPrestamos" id="valorPrestamos" maxlength="200" min="0" value="{{ number_format($comprobanteLiquidacion->valor_prestamos) }}" required="">
                <span class="help-block error">
                    <strong>{{ $errors->first('valorPrestamos') }}</strong>
                </span>
            </div>

            <div class="form-group col-md-12">
                <label class="control-label">Otras deducciones <span class="text-danger">*</span></label>
                <input type="text" class="form-control liquidar" name="otrasDeducciones" id="otrasDeducciones" maxlength="200" min="0" value="{{ number_format($comprobanteLiquidacion->otras_deducciones) }}" required="">
                <span class="help-block error">
                    <strong>{{ $errors->first('otrasDeducciones') }}</strong>
                </span>
            </div>

        </div>

    </div>

    <div class="row">

        <div class="col-12">

            <h3>7. Notas</h3>

            <textarea class="form-control" rows="5" cols="5" name="notas">
                {{ $comprobanteLiquidacion->notas }}
            </textarea>

        </div>

    </div>

</div>

<div class="div-content-2 p-5">

<div class="row">

    <div class="col-6">
        <h3>8. Resumen liquidación</h3>

        <div class="row">

            <div class="col-6">
                <p style="text-align: left; font-weight: 500">  Vacaciones </p>
            </div>

            <div class="col-6">
                <p style="text-align: right" id="r-vacaciones"> 31.547 </p>
            </div>

        </div>

        <div class="row">

            <div class="col-6">
                <p style="text-align: left; font-weight: 500">  Cesantías </p>
            </div>

            <div class="col-6">
                <p style="text-align: right" id="r-cesantías">70.485 </p>
            </div>

        </div>

        <div class="row">

            <div class="col-6">
                <p style="text-align: left; font-weight: 500"> Intereses a las Cesantías </p>
            </div>

            <div class="col-6">
                <p style="text-align: right" id="r-intereses-cesantias"> 588 </p>
            </div>

        </div>

        <div class="row">

            <div class="col-6">
                <p style="text-align: left; font-weight: 500"> Prima de Servicios </p>
            </div>

            <div class="col-6">
                <p style="text-align: right" id="r-prima-servicios">70.485 </p>
            </div>

        </div>

        <div class="row">

            <div class="col-6">
                <p style="text-align: left; font-weight: 500">  Indemnización por despido </p>
            </div>

            <div class="col-6">
                <p style="text-align: right" id="r-indemnizacion">0</p>
            </div>

        </div>

        <div class="row">

            <div class="col-6">
                <p style="text-align: lef; font-weight: 500"> Otros </p>
            </div>

            <div class="col-6">
                <p style="text-align: right" id="r-otros">0</p>
            </div>

        </div>

        <div class="row">

            <div class="col-6">
                <p style="text-align: left; font-weight: 500"> Total liquidación </p>
            </div>

            <div class="col-6">
                <a href="javascript:editTotal();"><i class="fas fa-edit"></i></a>
                <p style="text-align: right; border-top: 1px solid #000;" id="r-total">173.105</p>
                <input type="number" style="float:right" name="total" id="total" />
            </div>

        </div>

    </div>

    <div class="col-6">
        <h3>9. Revisión</h3>
           <div class="row">

           </div>

           <div class="row">
                <div class="col-12">
                    Si deseas que en la liquidación aparezca el salario pendiente por cancelar,
                    primero debes terminar de liquidar el empleado y en el siguiente paso podrás
                    descargarla de esta forma.
                </div>
           </div>

        <h3>10. Revisión</h3>

        <div class="row">

            <div class="col-12">

                Al confirmar la liquidación, el empleado no volverá a aparecer en la lista de empleados activos, ni en el siguiente pago de nómina.
                Si luego deseas editar algo o reincorporar nuevamente el empleado, lo podrás hacer desde la sección "Empleados".

            </div>

        </div>


        <div class="row">
            <div class="col-3 m-5">
                <button type="submit" class="btn btn-success">Editar liquidacion</button>
            </div>

            <div class="col-3 m-5">
                <a href="{{ route('personas.show', $persona->id) }}" role="button"
                class="btn btn-primary">Cancelar</a>
            </div>
        </div>

    </div>

</div>

</div>

</form>

@endsection

@section('scripts')



<script>



$(function(){

    $('#fechaContratacion').datepicker({
      locale: 'es-es',
      uiLibrary: 'bootstrap4',
      format: 'dd-mm-yyyy',
    });

    $('#fechaTerminacion').datepicker({
      locale: 'es-es',
      uiLibrary: 'bootstrap4',
      format: 'dd-mm-yyyy',
    });

    resumenLiquidacion();
    $('.liquidar').on('change', function(){
        resumenLiquidacion(false);
    });

    $('.liquidar-fecha').on('change', function(){
        resumenLiquidacion(true);
    });

});




function resumenLiquidacion(isDias = true){

    if(isDias){
        calcDiasLiquidar();
    }

if($('input[name="isPrueba"]:checked').val() == '1'){
    $('#isCausal1').prop('checked', true);
    $('#isCausal1').attr('checked', true);
    $('#isCausal2').attr('disabled', true);
}else{
    $('#isCausal2').attr('disabled', false);
}

let diasVacaciones = parseFloat($('#diasVacaciones').val());
let baseVacaciones =  parseFloat($('#vacaciones').val().replace(/,/g, ""));
let diasLiquidar =  parseFloat($('#diasLiquidar').val());
let baseCesantias =  parseFloat($('#cesantias').val().replace(/,/g, ""));
let basePrima = parseFloat($('#prima').val().replace(/,/g, ""));
let baseSalario =  parseFloat($('#salarioBase').val().replace(/,/g, ""));
var total = 0;

$('#r-vacaciones').text(formatNumber(vacaciones = (diasVacaciones * (baseVacaciones / 30))));
$('#r-cesantías').text(formatNumber(cesantias = (baseCesantias * diasLiquidar / 360)));
$('#r-intereses-cesantias').text(formatNumber(interesesC = (cesantias * (12/100) * (diasLiquidar / 360))));
$('#r-prima-servicios').text(formatNumber(prima = (basePrima * diasLiquidar / 360)));

var diasSalario = 0;

if(diasLiquidar <= 360){
    if(baseSalario < ($('#salario-minimo-vigente').val() * 10)){
        diasSalario = 30;
    }else{
        diasSalario = 20;
    }
}else{
    if(baseSalario < ($('#salario-minimo-vigente').val() * 10)){
        diasSalario = 30;
        diaAdicional = 20;
    }else{
        diasSalario = 20;
        diaAdicional = 15;
    }

        anosAdicionales = (diasLiquidar - 360) / 360;
        if(anosAdicionales >= 1){
           anosAdicionales = parseInt(anosAdicionales);
        }else{
            anosAdicionales = 0;
        }

        diasSalario += diaAdicional * anosAdicionales;
}

indemnizacion = 0;

if($('input[name="isCausal"]:checked').val() == '0'){
    indemnizacion = ((baseSalario  / 30) * diasSalario);
}

$('#r-indemnizacion').text(formatNumber(indemnizacion));
$('#r-otros').text(formatNumber(otrosIngresos =  parseFloat($('#otrosIngresos').val().replace(/,/g, ""))));
$('#r-total').text(formatNumber(total = (vacaciones + cesantias + interesesC + prima + indemnizacion + otrosIngresos)));
$('#total').val(total);

refreshMask();
}


function formatNumber(value) {
        var valueFormated =  (parseFloat(value)).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,').slice(0, -3);

        return valueFormated;
}

function calcDiasLiquidar(){

let fechaContratacion = $('#fechaContratacion').val();
let fechaTerminacion = $('#fechaTerminacion').val();
var dateParts1 = fechaContratacion.split("-");
var dateParts2 = fechaTerminacion.split("-");

var date1 = new Date(`${dateParts1[1]}/${dateParts1[0]}/${dateParts1[2]}`);
var date2 = new Date(`${dateParts2[1]}/${dateParts2[0]}/${dateParts2[2]}`);

// To calculate the time difference of two dates
var Difference_In_Time = date2.getTime() - date1.getTime();

// To calculate the no. of days between two dates
var Difference_In_Days = Difference_In_Time / (1000 * 3600 * 24);

$('#diasLiquidar').val(Difference_In_Days + 1);

}

function refreshMask(){

    let salarioBase = $('#salarioBase').val();
    let vacaciones =  $('#vacaciones').val();
    let cesantias = $('#cesantias').val();
    let prima = $('#prima').val();
    let otrosIngresos = $('#otrosIngresos').val();
    let valorPrestamos = $('#valorPrestamos').val();
    let otrasDeducciones = $('#otrasDeducciones').val();

    $('#salarioBase').mask('000,000,000', {reverse: true});
    $('#vacaciones').mask('000,000,000', {reverse: true});
    $('#cesantias').mask('000,000,000', {reverse: true});
    $('#prima').mask('000,000,000', {reverse: true});
    $('#otrosIngresos').mask('000,000,000', {reverse: true});
    $('#valorPrestamos').mask('000,000,000', {reverse: true});
    $('#otrasDeducciones').mask('000,000,000', {reverse: true});

    $('#salarioBase').val(salarioBase);
    $('#vacaciones').val(vacaciones);
    $('#cesantias').val(cesantias);
    $('#prima').val(prima);
    $('#otrosIngresos').val(otrosIngresos);
    $('#valorPrestamos').val(valorPrestamos);
    $('#otrasDeducciones').val(otrasDeducciones);

}

function editTotal(){
    $('#total').attr('type', 'number');
    $('#total').css('display', 'block');
    $('#total').focus();
    $('#total').after('<span style="font-size:12px;">El valor total es el dato que se informa a la DIAN, puedes editarlo.</span>');
    $('#total').on('keyup', function(){
        $('#r-total').text(formatNumber($('#total').val()));
    })
}


</script>



@endsection
