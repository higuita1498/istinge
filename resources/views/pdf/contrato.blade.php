@extends('layouts.pdf')

@section('content')
    <style type="text/css">
        body{
            font-family: Helvetica, sans-serif;
            font-size: 12px;
            color: #000;
            height: auto;
        }
        h4{
            font-weight: bold;
            text-align: center;
            margin: 0;font-size: 14px;
        }
        .small{
            font-size: 10px;line-height: 12px;    margin: 0;
        }
        .smalltd{
            font-size: 10px;line-height: 12px; padding-right: 2px;
        }
        .medium{
            font-size: 17px;line-height: 14px;    margin: 0;
        }
        a{
            color: #000;
            text-decoration: none;
        }
        th{
            background: #ccc;
        }
        td{
            padding-left: 2px;
        }
        .center{
            text-align: center;
        }
        .right{
            text-align: right;
        }
        .left{
            text-align: left;
        }
        .titulo{
            width: 100%;
            border-collapse: collapse;
            border-radius: 0.4em;
            overflow: hidden;
        }
        td {
          border: 1px  solid #9e9b9b;
        }

        th {
          border: 1px  solid #ccc;
        }
        .desgloce{
            width: 100%;
            overflow: hidden;
            border-collapse: collapse;
            border-top-left-radius: 0.4em;
            border-top-right-radius: 0.4em;
        }
        .desgloce td{
            padding-top: 3px;
            border-left: 2px solid #fff;
            border-top: 2px solid #fff;
            border-bottom: 2px solid #fff;
            border-right: 2px solid #ccc;
        }
        .foot td{
            padding-top: 3px;
            border: 1px solid #fff;
            padding-right: 1%;
        }
        .foot th{
            padding: 2px;
            border-radius: unset;
        }
        .border_left{
            border-left: 3px solid #ccc !important;
        }
        .border_bottom{
            border-bottom: 5px solid #ccc !important;
        }
        .border_right{
            border-right: 3px solid #ccc !important;
        }
        .padding-right{
             padding-right:1% !important;
        }
        .padding-left{
            padding-left: 1%;
        }
    </style>

    <div style="width: 100%; display: inline-block; height: auto;">
        <div style="height: auto; width: 50%; display: inline-block;margin-top: 10%; margin-right: 1%;">
            <center>
                <img src="{{asset('images/Empresas/Empresa'.Auth::user()->empresa.'/'.Auth::user()->empresa()->logo)}}" alt="" style="max-width: 100%; max-height:100px; object-fit:contain; text-align:center;">
            </center>
            <br><br><br><br>
            
            <p style="text-align: justify;" class="small">Este contrato explica las condiciones para la prestación de los servicios entre usted y <b>{{ Auth::user()->empresa()->nombre }}</b> por el que pagará mínimo mensualmente $ 70000.00. Este contrato tendrá vigencia de __ meses, contados a partir del <b>{{date('d-m-Y', strtotime($contrato->fecha_isp))}}</b>. El plazo máximo de instalación es de 15 días Hábiles. Acepto que mi contrato se renueve sucesiva y automáticamente por un plazo igual al inicial.</p><br>
            <p style="text-align: justify;font-weight: bold;" class="small">EL SERVICIO</p><br>
            <p style="text-align: justify;" class="small">Con este contrato nos comprometemos a prestarle los servicios que usted elija:</p><br>
            <p style="text-align: justify;" class="small">Telefonía fija (  ).</p>
            <p style="text-align: justify;" class="small">Internet fijo ( <b style="color: red;">X</b> ).</p>
            <p style="text-align: justify;" class="small">Televisión (  ).</p>
            <p style="text-align: justify;" class="small">Servicios adicionales ______________________________.</p>
            <p style="text-align: justify;" class="small">Usted se compromete a pagar oportunamente el precio acordado.</p><br>
            
            <div style="border: 1px  solid #9e9b9b; padding: 10px;">
                <br><p style="text-align: justify;font-weight: bold;" class="small">INFORMACIÓN DEL SUSCRIPTOR</p><br>
                <p style="text-align: justify;" class="small">Contrato No.:<b></b> </p>
                <p style="text-align: justify;" class="small">Nombre / Razón Social:<b> {{ $contrato->nombre }}</b></p>
                <p style="text-align: justify;" class="small">Identificación:<b>  {{ $contrato->nit }}</b></p>
                <p style="text-align: justify;" class="small">Correo electrónico:<b>  {{ $contrato->email }}</b></p>
                <p style="text-align: justify;" class="small">Teléfono de contacto:<b>  {{ $contrato->celular }}</b></p>
                <p style="text-align: justify;" class="small">Dirección Servicio:<b>  {{ $contrato->direccion }}</b></p>
                <p style="text-align: justify;" class="small">Departamento:<b> 7 DE ABRIL</b></p>
                <p style="text-align: justify;" class="small">Municipio:<b> BARRANQUILLA</b></p>
                <p style="text-align: justify;" class="small">Dirección Suscriptor:<b>  {{ $contrato->direccion }}</b></p><br>
            </div>
            
            <div style="border: 1px solid #9e9b9b; padding: 5px; margin-top: 5px;">
                <br><p style="text-align: justify;font-weight: bold;" class="small">CONDICIONES COMERCIALES CARACTERÍSTICAS DEL PLAN</p>
                <br><br><br><br><br><br><br><br><br><br><br><br><br><br>
            </div>
            
            <br><p style="text-align: justify; color: blue;" class="small">* Espacio diligenciado por el usuario</p>
        </div>
        
        <div style="height: auto; width: 50%; display: inline-block; margin-left: 1%;">
            <p style="text-align: justify;font-weight: bold;" class="small">PRINCIPALES OBLIGACIONES DEL USUARIO</p><br>
            <p style="text-align: justify; margin-left:20px;" class="small">1) Pagar oportunamente los servicios prestados, incluyendo los intereses de mora cuando haya incumplimiento;</p>
            <p style="text-align: justify; margin-left:20px;" class="small">2) Suministrar información verdadera;</p>
            <p style="text-align: justify; margin-left:20px;" class="small">3) Hacer uso adecuado de los equipos y los servicios;</p>
            <p style="text-align: justify; margin-left:20px;" class="small">4) No divulgar ni acceder a pornografía infantil (Consultar anexo);</p>
            <p style="text-align: justify; margin-left:20px;" class="small">5) Avisar a las autoridades cualquier evento de robo o hurto de elementos de la red, como el cable;</p>
            <p style="text-align: justify; margin-left:20px;" class="small">6) No cometer o ser partícipe de actividades de fraude.</p><br>
            <p style="text-align: justify;font-weight: bold;" class="small">CALIDAD Y COMPENSACIÓN</p><br>
            <p style="text-align: justify;" class="small">Cuando se presente indisponibilidad del servicio o este se suspenda a pesar de su pago oportuno, lo compensaremos en su próxima factura. Debemos cumplir con las condiciones de calidad definidas por la CRC. Consúltelas en la página: {{ Auth::user()->empresa()->web }}</p><br>
            <p style="text-align: justify;font-weight: bold;" class="small">CESIÓN</p><br>
            <p style="text-align: justify;" class="small">Si quiere ceder este contrato a otra persona, debe presentar una solicitud por escrito a través de nuestros Medios de Atención, acompañada de la aceptación por escrito de la persona a la que se hará la cesión. Dentro de los 15 días hábiles siguientes, analizaremos su solicitud y le daremos una respuesta. Si se acepta la cesión queda liberado de cualquier responsabilidad con nosotros.</p><br>
            <p style="text-align: justify;font-weight: bold;" class="small">MODIFICACIÓN</p><br>
            <p style="text-align: justify;" class="small">Nosotros no podemos modificar el contrato sin su autorización. Esto incluye que no podemos cobrarle servicios que no haya aceptado expresamente. Si esto ocurre tiene derecho a terminar el contrato, incluso estando vigente la cláusula de permanencia mínima, sin la obligación de pagar suma alguna por este concepto. No obstante, usted puede en cualquier momento modificar los servicios contratados. Dicha modificación se hará efectiva en el período de facturación siguiente, para lo cual deberá presentar la solicitud de modificación por lo menos con 3 días hábiles de anterioridad al corte de facturación.</p><br>
            <p style="text-align: justify;font-weight: bold;" class="small">SUSPENSIÓN</p><br>
            <p style="text-align: justify;" class="small">Usted tiene derecho a solicitar la suspensión del servicio por un máximo de 2 meses al año. Para esto debe presentar la solicitud antes del inicio del ciclo de facturación que desea suspender. Si existe una cláusula de permanencia mínima, su vigencia se prorrogará por el tiempo que dure la suspensión.</p><br>
            <p style="text-align: justify;font-weight: bold;" class="small">TERMINACIÓN</p><br>
            <p style="text-align: justify;" class="small">Usted puede terminar el contrato en cualquier momento sin penalidades. Para esto debe realizar una solicitud a través de cualquiera de nuestros Medios de Atención mínimo 3 días hábiles antes del corte de facturación (su corte de facturación es el día <b>Dia 6 de cada mes</b>). Si presenta la solicitud con una anticipación menor, laterminación del servicio se dará en el siguiente periodo de facturación. Así mismo, usted puede cancelar cualquiera de los servicios contratados, para lo que le informaremos las condiciones en las que serán prestados los servicios no cancelados y actualizaremos el contrato. Así mismo, usted puede cancelar cualquiera de los servicios contratados, para lo que le informaremos las condiciones en las que serán prestados los servicios no cancelados y actualizaremos el contrato. Así mismo, si el operador no inicia la prestación del servicio en el plazo acordado, usted puede pedir la restitución de su dinero y la terminación del contrato.</p><br>
            <p style="color: white;text-align: justify;" class="small"></p><br>
        </div>
    </div>
    
    {{-- <div style="page-break-after:always;"></div> --}}
    
    <div style="width: 100%;height: auto;">
        <p style="color: white; text-align: justify;" class="small">.</p>
    </div>
    
    <div style="width: 100%; display: inline-block; height: auto;">
        <div style="height: auto; width: 50%; display: inline-block; margin-right: 1%;margin-top: 7.5%;">
            <p style="text-align: justify;font-weight: bold;" class="small">PAGO Y FACTURACIÓN</p><br>
            <p style="text-align: justify;" class="small">La factura le debe llegar como mínimo 5 días hábiles antes de la fecha de pago. Si no llega, puede solicitarla a través de nuestros Medios de Atención y debe pagarla oportunamente. Si no paga a tiempo, previo aviso, suspenderemos su servicio hasta que pague sus saldos pendientes. Contamos con 3 días hábiles luego de su pago para reconectarle el servicio. Si no paga a tiempo, también podemos reportar su deuda a las centrales de riesgo. Para esto tenemos que avisarle por lo menos con 20 días calendario de anticipación. Si paga luego de este reporte tenemos la obligación dentro del mes de seguimiento de informar su pago para que ya no aparezca reportado. Si tiene un reclamo sobre su factura, puede presentarlo antes de la fecha de pago y en ese caso no debe pagar las sumas reclamadas hasta que resolvamos su solicitud. Si ya pagó, tiene 6 meses para presentar la reclamación.</p><br>
            <p style="text-align: justify;font-weight: bold;" class="small">CÓMO COMUNICARSE CON NOSOTROS (MEDIOS DE ATENCIÓN)</p><br>
            <p style="text-align: justify; margin-left:20px;" class="small">1) Nuestros medios de atención son: oficinas físicas, página web, redes sociales y líneas telefónicas gratuitas.</p>
            <p style="text-align: justify; margin-left:20px;" class="small">2) Presente cualquier queja, petición/reclamo o recurso a través de estos medios y le responderemos en máximo 15 días hábiles.</p>
            <p style="text-align: justify; margin-left:20px;" class="small">3) Si no respondemos es porque aceptamos su petición o reclamo. Esto se llama silencio administrativo positivo y aplica para internet y telefonía.</p><br>
            <p style="text-align: center; margin-left:20px; font-weight: bold;" class="small">Si no está de acuerdo con nuestra respuesta</p><br>
            <p style="text-align: justify; margin-left:20px;" class="small">4) Cuando su queja o petición sea por los servicios de telefonía y/o internet, y esté relacionada con actos de negativa del contrato, suspensión del servicio, terminación del contrato, corte y facturación; usted puede insistir en su solicitud ante nosotros, dentro de los 10 días hábiles siguientes a la respuesta, y pedir que si no llegamos a una solución satisfactoria para usted, enviemos su reclamo directamente a la SIC (Superintendencia de Industria y Comercio) quien resolverá de manera definitiva su solicitud. Esto se llama recurso de reposición y en subsidio apelación. Cuando su queja o petición sea por el servicio de televisión, puede enviar la misma a la Autoridad Nacional de Televisión, para que esta Entidad resuelva su solicitud.</p><br>
            <p style="text-align: justify;" class="small">Cuando se presente indisponibilidad del servicio o este se suspenda a pesar de su pago oportuno, lo compensaremos en su próxima factura. Debemos cumplir con las condiciones de calidad definidas por la CRC. Consúltelas en la página: {{ Auth::user()->empresa()->web }}</p><br>
            <p style="text-align: justify;font-weight: bold;" class="small">ACEPTO CLÁUSULA DE PERMANENCIA MÍNIMA</p><br>
            <p style="text-align: justify;" class="small">En consideración a que le estamos otorgando un descuento respecto del valor del cargo por conexión, o le diferimos el pago del mismo, se incluye la presente cláusula de permanencia mínima. En la factura encontrará el valor a pagar si decide terminar el contrato anticipadamente</p><br>
        </div>
        
        <div style="height: auto; width: 50%; display: inline-block; margin-left: 1%;">
            <p style="text-align: justify;font-weight: bold;" class="small">CAMBIO DE DOMICILIO</p><br>
            <p style="text-align: justify;" class="small">Usted puede cambiar de domicilio y continuar con el servicio siempre que sea técnicamente posible. Si desde el punto de vista técnico no es viable el traslado del servicio, usted puede ceder su contrato a un tercero o terminarlo pagando el valor de la cláusula de permanencia mínima si esta vigente.</p><br>
            <p style="text-align: justify;font-weight: bold;" class="small">LARGA DISTANCIA (TELEFONÍA)</p><br>
            <p style="text-align: justify;" class="small">Nos comprometemos a usar el operador de larga distancia que usted nos indique, para lo cual debe marcar el código de larga distancia del operador que elija.</p><br>
            <p style="text-align: justify;font-weight: bold;" class="small">COBRO POR RECONEXIÓN DEL SERVICIO</p><br>
            <p style="text-align: justify;" class="small">En caso de suspensión del servicio por mora en el pago, podremos cobrarle un valor por reconexión que corresponderá estrictamente a los costos asociados a la operación de reconexión. En caso de servicios empaquetados procede máximo un cobro de reconexión por cada tipo de conexión empleado en la prestación de los servicios.<br><br><b>Costo reconexión: $__________</b></p><br>
            <p style="text-align: justify;" class="small">El usuario es el ÚNICO responsable por el contenido y la información que se curse a través de la red y del uso que se haga de los equipos o de los servicios.<br>Los equipos de comunicaciones que ya no use son desechos que no deben ser botados a la caneca, consulte nuestra política de recolección de aparatos en desuso.</p><br>
            
            <div style="border: 1px solid #9e9b9b; padding: 5px; margin-top: 5px;">
                <br><p style="text-align: center;font-weight: bold;" class="small">Aceptación contrato mediante firma o cualquier otro medio válido</p><br>
                <img src="data:image/png;base64,{{substr($contrato->firma_isp,1)}}" style="width: 100%">
                <br><p style="text-align: center; font-weight: bold;" class="small">{{ $contrato->nombre}}<br>{{$contrato->tip_iden('mini')}} - {{ $contrato->nit}}<br>{{date('d-m-Y', strtotime($contrato->fecha_isp))}}</p>
            </div>
            
            <p style="color: white; text-align: justify;" class="small"></p><br>
        </div>
    </div>
@endsection
