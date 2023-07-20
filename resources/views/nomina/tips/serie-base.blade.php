<input type="hidden" id="tour-modulo" value="Nomina">
<div style="display:none">
@if(in_array(1, $pasos))
<div class="tour-tips" id="tip-1" nro_tip="1" element="#liquidar-nomina" onElement="right" isRedirect="true">

    <div class="contenido">
        <strong>¡Comencemos!</strong> <br>
        Nos encontramos en la sección de liquidar nómina 💵, \n
        acá es donde reportarás las novedades de cada periodo
    </div>

    <div class="botones">
        <a href="#" style="color:white">Siguiente</a>
    </div>

</div>
@endif

@if(in_array(2, $pasos))
<div class="tour-tips" id="tip-2" nro_tip="2" element="#liquidar-nomina" onElement="right" isRedirect="false">

    <div class="contenido">
        <strong>Agrega novedades</strong> <br>
        Para agregar novedades en tu nómina solo debes:<br>
        1. Buscar el empleado al que quieres agregarle la novedad.<br>
        2. Identificar la columna de la novedad correspondiente que quieras agregar.<br>
        3. Hacer clic en el botón de <i class="far fa-edit"></i> y agregar la información.<br>
    </div>

    <div class="botones">
        <a href="javascript:nuevoTip(3)" style="color:white"> Siguiente </a>
    </div>

</div>
@endif

@if(in_array(3, $pasos))
<div class="tour-tips" id="tip-3" nro_tip="3" element="#empleados" onElement="top" isRedirect="false">

    <div class="contenido">
        <strong>Lista de personas</strong> <br>
            Gestione los pagos y novedades del empleado en esta tabla, si agrega una nueva persona esta quedara listada en el periodo correspondiente!
        <br>
    </div>

    <div class="botones">
        <a href="javascript:nuevoTip(4)" style="color:white"> Ok </a>
    </div>

</div>
@endif


@if(in_array(4, $pasos))
<div class="tour-tips" id="tip-4" nro_tip="4" element="#btn-confirmar-nomina" onElement="top" isRedirect="false">

    <div class="contenido">
        <strong>Revise totales</strong> <br>
        Una vez hayas agregado las novedades del periodo puedes:<br>
        1. Revisar el total a pagar.<br>
        2. Descargar la colilla de pago de algún empleado.<br>
        3. y revisar los cálculos.<br>
        <br>

        Una vez confirmes los datos haz clic en Liquidar nomina para generar todos los reportes y archivos de la nomina.
    </div>

    <div class="botones">
        <a href="javascript:nuevoTip(10)" style="color:white"> Entendido </a>
    </div>

</div>
@endif


@if(in_array(5, $pasos))

<div class="tour-tips" id="tip-5" nro_tip="5" element="#crear-persona-empleado" onElement="right" isRedirect="true">

    <div class="contenido">
        <strong>Crear empleados</strong> <br>
        No hay nomina sin empleados y ha llegado la hora de registrar los de su empresa.
        <br>
    </div>

    <div class="botones">
        <a tourRedirect="{{ route('personas.create') }}" href="javascript:setRedirect('{{ route('personas.create') }}')" style="color:white"> Crear </a>
    </div>

</div>

@endif

@if(in_array(6, $pasos))

<div class="tour-tips" id="tip-6" nro_tip="6" element="#texto_nomina" onElement="right" isRedirect="false">

    <div class="contenido">
        <strong>Nomina electronica</strong> <br>
        Active o desactive la nomina en su empresa haciendo clic aquí.
        <br>
    </div>

    <div class="botones">
        <a href="javascript:habilitarNomina(); javascript:nuevoTip(7, 8000);" style="color:white"> Ok </a>
    </div>

</div>

@endif


@if(in_array(7, $pasos))

<div class="tour-tips" id="tip-7" nro_tip="7" element="#preferencia_pago" onElement="right" isRedirect="true">

    <div class="contenido">
        <strong>Configure los pagos</strong> <br>
        Agregue aquí las preferencias de pago para su empresa.
        <br>
    </div>

    <div class="botones">
        <a tourRedirect="{{route('nomina.preferecia-pago')}}" href="javascript:setRedirect('{{route('nomina.preferecia-pago')}}')" style="color:white"> Crear </a>
    </div>

</div>

@endif

@if(in_array(8, $pasos))

<div class="tour-tips" id="tip-8" nro_tip="8" element="#personas-nomina" onElement="right" isRedirect="false">

    <div class="contenido">
        <strong>Guarde la frecuencia de pago</strong> <br>
         Cuando configure la frecuencia de pago luego cree una persona.
        <br>
    </div>

    <div class="botones">
        <a href="#" style="color:white"> Ok </a>
    </div>

</div>

@endif


@if(in_array(9, $pasos))

<div class="tour-tips" id="tip-9" nro_tip="9" element="#btn-generar-nomina" onElement="left" isRedirect="false">

    <div class="contenido">
        <strong>Genere un nuevo periodo de nomina</strong> <br>
            Las personas que se encuentren habilitadas serán parte del periodo seleccionado.
        <br>
    </div>

    <div class="botones">
        <a href="#" style="color:white"> Ok </a>
    </div>

</div>

@endif


@if(in_array(10, $pasos))
<div class="tour-tips" id="tip-10" nro_tip="10" element="#th-extras-r" onElement="top" isRedirect="false">

    <div class="contenido">
        <strong>Edite o agregue</strong> <br>
            Ingrese las horas extras y recargos desde esta columna!
        <br>
    </div>

    <div class="botones">
        <a href="javascript:nuevoTip(11)" style="color:white"> Ok </a>
    </div>

</div>
@endif

@if(in_array(11, $pasos))
<div class="tour-tips" id="tip-11" nro_tip="11" element="#th-vacaciones-i" onElement="top" isRedirect="false">

    <div class="contenido">
        <strong>Edite o agregue</strong> <br>
            Ingrese las vacaciones, incapacidades y licencias que tuvo el empleado desde esta columna!
        <br>
    </div>

    <div class="botones">
        <a href="javascript:nuevoTip(12)" style="color:white"> Ok </a>
    </div>

</div>
@endif

@if(in_array(12, $pasos))
<div class="tour-tips" id="tip-12" nro_tip="12" element="#th-ingresos" onElement="top" isRedirect="false">

    <div class="contenido">
        <strong>Edite o agregue</strong> <br>
            Ingrese las horas extras y recargos desde esta columna!
        <br>
    </div>

    <div class="botones">
        <a href="javascript:nuevoTip(13)" style="color:white"> Ok </a>
    </div>

</div>
@endif

@if(in_array(13, $pasos))
<div class="tour-tips" id="tip-13" nro_tip="13" element="#th-deducciones" onElement="top" isRedirect="false">

    <div class="contenido">
        <strong>Edite o agregue</strong> <br>
            Ingrese las deducciones, prestamos y retenciones en la fuente del empleado
        <br>
    </div>

    <div class="botones">
        <a href="javascript:nuevoTip(14)" style="color:white"> Ok </a>
    </div>

</div>
@endif


@if(in_array(14, $pasos))
<div class="tour-tips" id="tip-14" nro_tip="14" element="#th-acciones" onElement="left" isRedirect="false">

    <div class="contenido">
        <strong>Accione los botones</strong>
        <br>
        <i class="far fa-eye"></i> Observe el resumen y los calculos realizados
        <br>
        <i class="far fa-file"></i> Genere un pdf con la colilla de pago
        <br>
    </div>

    <div class="botones">
        <a href="javascript:nuevoTip(15)" style="color:white"> Ok </a>
    </div>

</div>
@endif



@if(in_array(15, $pasos))
<div class="tour-tips" id="tip-15" nro_tip="15" element="#btn-prestaciones-sociales" onElement="bottom" isRedirect="false">

    <div class="contenido">
        <strong>Pague las prestaciones</strong>
        <br>
        Puede realizar el pago parcial de las prestaciones sociales (prima de servicios, cesantías e intereses a las cesantías) en el periodo vigente
    </div>

    <div class="botones">
        <a href="javascript:void(0)" style="color:white"> Ok </a>
    </div>

</div>
@endif


@if(in_array(17, $pasos))
<div class="tour-tips" id="tip-17" nro_tip="17" element="#colillas-pago" onElement="right" isRedirect="false">

    <div class="contenido">
        <strong>Revise las colillas de pago</strong>
        <br>
        imprima de forma agrupada, individual o también puede enviar el comprobante de pago vía email a los empleados de forma rápida.
    </div>

    <div class="botones">
        <a href="javascript:nuevoTip(18)" style="color:white"> Ok </a>
    </div>

</div>
@endif

@if(in_array(18, $pasos))
<div class="tour-tips" id="tip-18" nro_tip="18" element="#g-reportes" onElement="right" isRedirect="false">

    <div class="contenido">
        <strong>Genere reportes</strong>
        <br>
        Ahora puede generar y descargar los reportes de las nominas liquidadas
    </div>

    <div class="botones">
        <a href="javascript:void(0)" style="color:white"> Ok </a>
    </div>

</div>
@endif


</div>
