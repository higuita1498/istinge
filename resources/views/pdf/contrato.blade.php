@extends('layouts.pdf')

@section('content')
    <style type="text/css">
        body, td, th {
            font-family: Segoe, "Segoe UI", "DejaVu Sans", "Trebuchet MS", Verdana, sans-serif;
            font-style: normal;
            font-size: 11px;
        }

        body {
            padding: 0;
            margin: 0
        }

        table p, table h4 {
            margin: 5px 0;
        }

        h1, h4 {
            margin: 7px 0;
        }

        .table {
            font-family: Segoe, "Segoe UI", "DejaVu Sans", "Trebuchet MS", Verdana, sans-serif;
            border-collapse: collapse;
            width: 100%;
            border: 1px solid #bdbdbd;
            font-size: 11px;
        }

        .table th {
            border: 1px solid #bdbdbd;
            padding: 4px;
        }

        .table td {
            border: 1px solid #bdbdbd;
            padding: 2px;
            font-size: 11px;
        }

        .table th {
            padding-top: 6px;
            padding-bottom: 6px;
            text-align: left;
            background-image: -webkit-linear-gradient(top, #f5f5f5 0, #e8e8e8 100%);
            background-image: -o-linear-gradient(top, #f5f5f5 0, #e8e8e8 100%);
            background-image: -webkit-gradient(linear, left top, left bottom, from(#f5f5f5), to(#e8e8e8));
            background-image: linear-gradient(to bottom, #f5f5f5 0, #e8e8e8 100%);
            filter: progid: DXImageTransform.Microsoft.gradient(startColorstr='#fff5f5f5', endColorstr='#ffe8e8e8', GradientType=0);
            background-repeat: repeat-x;
        }

        .respuesta-tecnico {
            width: 500px;
            display: inline-block;
        }

        .asunto_ticket {
            font-size: 16px;
        }
        .small{
            font-size: 10px;line-height: 10px;margin: 0;
        }
        .titulo-bg {
            /* background-color: {{$empresa->color}}; */
            background-color: {{$empresa->color}};

            color: white;
            padding: 5px;
            font-size: 12px;
            border: 1px  solid #000;
        }
        .mb-2{
            margin-bottom:  .75em;
        }
        .mt-2{
            margin-top:  .75em;
        }
        .p-1{
            padding: .25em;
        }
        .p-2{
            padding: .5em;
        }
        .pl-2{
            padding-left: .75em;
        }
    </style>
    <style media="print" type="text/css">
        @page {
            size: auto;
            /* auto is the initial value */
            margin-bottom: 0px;
            /* this affects the margin in the printer settings */
        }
    </style>

    <div style="">
        <table width="100%">
            <tbody>
                <tr>
                    <td>
                        <div style="margin-top: 10px; text-align:center;">
                            <img src="{{asset('images/Empresas/Empresa'.$empresa->id.'/'.$empresa->logo)}}" alt="" style="width: 130px !important;">
                        </div>

                        <div style="width: 100%; background-color: {{$empresa->color}}; clear:both;  margin-top: 10px;">
                            <p style="color: white; margin: 2px; text-align: justify; padding: 5px;" class="">
                                Este contrato explica las condiciones para la prestación de los servicios entre usted y <b>{{Auth::user()->empresa()->nombre}}</b>, por el que pagará mínimo mensualmente <b>$ _______</b>. Este contrato tendrá vigencia de ____ meses, contados a partir del <b>__/__/____</b>. El plazo máximo de instalación es de 15 días hábiles. Acepto que mi contrato se renueve sucesiva y automáticamente por un plazo igual al inicial <input checked="checked" type="checkbox"> *
                            </p>
                        </div>

                        <div style="width: 100%;  margin-top: 10px;">
                            <table width="100%">
                                <thead>
                                    <tr>
                                        <th style="padding: 3px 6px;background-color: {{$empresa->color}}; color: white; text-align: left;border: 1px solid #000;">EL SERVICIO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <p style="text-align: justify;" class="small">Con este contrato nos comprometemos a prestarle los servicios que usted elija*:</p>
                                            <p style="text-align: justify;" class="small">Telefonía fija <input type="checkbox" /> Internet fijo <input type="checkbox" {{isset($contract->server_configuration_id) ? 'checked="checked' : ''}} /> Televisión <input type="checkbox" {{isset($contract->servicio_tv) ? 'checked="checked' : ''}}></p>
                                            <p style="text-align: justify;" class="small">Servicios adicionales ______________________________</p>
                                            <p style="text-align: justify;" class="small">Usted se compromete a pagar oportunamente el precio acordado.</p>
                                            <p style="text-align: justify;" class="small mb-2">El servicio se activará a más tardar el día <b>__/__/____</b>.</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div style="width: 100%;  margin-top: 10px; border: 1px  solid #000;">
                            <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">INFORMACIÓN DEL SUSCRIPTOR</p><br>
                            <p style="text-align: justify;" class="small pl-2">Contrato No.: <b>{{ $contractDetails ? $contractDetails->nro : '' }}</b> </p>
                            <p style="text-align: justify;" class="small pl-2">Nombre / Razón Social: <b>{{ $contact->nombre }} {{ $contact->apellidos() }}</b></p>
                            <p style="text-align: justify;" class="small pl-2">Identificación: <b>{{ $contact->tip_iden('corta') }} {{ $contact->nit }}@if($contact->dv != null || $contact->dv === 0)-{{$contact->dv}} @endif</b></p>
                            <p style="text-align: justify;" class="small pl-2">Correo electrónico: <b>{{ $contact->email }}</b></p>
                            <p style="text-align: justify;" class="small pl-2">Teléfono de contacto: <b>{{ $contact->celular }}</b></p>
                            <p style="text-align: justify;" class="small pl-2">Dirección Servicio: <b>{{ $contact->direccion }}</b> Estrato: <b>{{ $contact->estrato ? $contact->estrato : '   ' }}</b></p>
                            <p style="text-align: justify;" class="small pl-2">Departamento: <b>{{ $contact->departamento()->nombre }}</b> Municipio: <b>{{ $contact->municipio()->nombre }}</b></p>
                            <p style="text-align: justify;" class="small pl-2">Dirección Suscriptor: <b>{{ $contact->direccion }}</b></p><br>
                        </div>

                        <div style="width: 100%;  margin-top: 5px; border: 1px  solid #000;">
                            <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">CONDICIONES COMERCIALES CARACTERÍSTICAS DEL PLAN</p><br>
                            <p style="text-align: justify;" class="small pl-2">Tipo de Cliente: Nuevo <input type="checkbox" /> Modificación <input type="checkbox" /></p>
                            @if(isset($contract->tecnologia))
                            <p style="text-align: justify;" class="small pl-2">Tipo red: FTTH <input type="checkbox" {{$contract->tecnologia == 1 ? 'checked="checked' : ''}}> WIRELESS <input type="checkbox" {{$contract->tecnologia == 2 ? 'checked="checked' : ''}}></p><br>
                            @endif
                            <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">INTERNET</p>
                            <table style="width: 100%; text-align:center; padding:5px;">
                                <tr style="background-color: {{$empresa->color}}; color: #fff;">
                                    <td colspan="2">Incluidos en el plan</td>
                                    <td colspan="2">Adicionales</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 9px;">Megas Down</td>
                                    <td>{{ isset($contractDetails->server_configuration_id) ? $contractDetails->plan()->download : '' }}</td>
                                    <td style="font-size: 9px;">Ip Fijo</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td style="font-size: 9px;">Megas Up</td>
                                    <td>{{ isset($contractDetails->server_configuration_id) ? $contractDetails->plan()->upload : '' }}</td>
                                    <td style="font-size: 9px;">Otros</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td style="font-size: 9px;">Valor</td>
                                    <td style="font-size: 9px;"><b>{{$empresa->moneda}} {{ isset($contractDetails->server_configuration_id) ? App\Funcion::Parsear($contractDetails->plan()->price) : '________' }}</b></td>
                                    <td style="font-size: 9px;">Total</td>
                                    <td style="font-size: 9px;">{{$empresa->moneda}} {{ isset($contractDetails->server_configuration_id) ? App\Funcion::Parsear($contractDetails->plan()->price) : '________' }}</td>
                                </tr>
                            </table>

                            <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">TELEVISIÓN</p>
                            <table style="width: 100%; text-align:center; padding:5px;">
                                <tr style="background-color: {{$empresa->color}}; color: #fff;">
                                    <td colspan="2">Incluidos en el plan</td>
                                    <td colspan="2">Adicionales</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 9px;">Decos</td>
                                    <td></td>
                                    <td style="font-size: 9px;">Decos</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td style="font-size: 9px;">Puntos TV</td>
                                    <td></td>
                                    <td style="font-size: 9px;">Puntos TV</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td style="font-size: 9px;">Valor</td>
                                    <td style="font-size: 9px;">{{$empresa->moneda}}{{ isset($contract->servicio_tv) ? App\Funcion::Parsear((($contract->plan('true')->precio * $contract->plan('true')->impuesto)/100)+$contract->plan('true')->precio) : '________' }}</td>
                                    <td style="font-size: 9px;">Total</td>
                                    <td style="font-size: 9px;">{{$empresa->moneda}} {{ isset($contract->servicio_tv) ? App\Funcion::Parsear((($contract->plan('true')->precio * $contract->plan('true')->impuesto)/100)+$contract->plan('true')->precio) : '________' }}</td>
                                    @php
                                    $total_tv = 0; $total_internet = 0;
                                    if (isset($contract->servicio_tv)){
                                        $total_tv = (($contract->plan('true')->precio * $contract->plan('true')->impuesto)/100)+$contract->plan('true')->precio;
                                    }
                                    if (isset($contract->server_configuration_id)){
                                        $total_internet = $contract->plan()->price;
                                    }
                                    @endphp
                                </tr>
                            </table>
                        </div>

                        <div style="border: 1px  solid #000; margin-top: 5px; padding:2px; text-align: right;">
                            VALOR TOTAL <span style="background-color:silver;">&nbsp;&nbsp;&nbsp;{{$empresa->moneda}} {{ App\Funcion::Parsear($total_tv + $total_internet) }}&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;
                        </div>

                        <br><p style="text-align: justify; color: blue;" class="small">* Espacio diligenciado por el usuario</p>
                    </td>

                    <td style="vertical-align:top;" width="50%">
                        <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">PRINCIPALES OBLIGACIONES DEL USUARIO</p><br>
                        <p style="text-align: justify;" class="small">1) Pagar oportunamente los servicios prestados, incluyendo los intereses de mora cuando haya incumplimiento 2) suministrar información verdadera 3) hacer uso adecuado de los equipos y los servicios 4) No divulgar ni acceder a pornografía infantil (consultar anexo) 5) avisar a las autoridades cualquier evento de robo o hurto de elementos de la red, como el cable 6) No cometer o ser partícipe de fraude 7) hacer uso adecuado de su derecho a presentar PQR. 8) actuar de buena fe. El operador podrá terminar el contrato ante incumplimiento de estas obligaciones.</p><br>
                        <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">CALIDAD Y COMPENSACIÓN</p><br>
                        <p style="text-align: justify;" class="small">Cuando se presente indisponibilidad del servicio o este se suspenda a pesar de su pago oportuno, lo compensaremos en su próxima factura. Debemos cumplir con las condiciones de calidad definidas por la CRC.<br>Consúltelas en la página: {{ Auth::user()->empresa()->web }}</p><br>
                        <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">CESIÓN</p><br>
                        <p style="text-align: justify;" class="small">Si quiere ceder este contrato a otra persona, debe presentar una solicitud por escrito a través de nuestros Medios de Atención, acompañada de la aceptación por escrito de la persona a la que se hará la cesión. Dentro de los 15 días hábiles siguientes, analizaremos su solicitud y le daremos una respuesta. Si se acepta la cesión queda liberado de cualquier responsabilidad con nosotros.</p><br>
                        <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">MODIFICACIÓN</p><br>
                        <p style="text-align: justify;" class="small">Nosotros no podemos modificar el contrato sin su autorización. Esto incluye que no podemos cobrarle servicios que no haya aceptado expresamente. Si esto ocurre tiene derecho a terminar el contrato, incluso estando vigente la cláusula de permanencia mínima, sin la obligación de pagar suma alguna por este concepto. No obstante, usted puede en cualquier momento modificar los servicios contratados. Dicha modificación se hará efectiva en el período de facturación siguiente, para lo cual deberá presentar la solicitud de modificación por lo menos con 3 días hábiles de anterioridad al corte de facturación.</p><br>
                        <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">SUSPENSIÓN</p><br>
                        <p style="text-align: justify;" class="small">Usted tiene derecho a solicitar la suspensión del servicio por un máximo de 2 meses al año. Para esto debe presentar la solicitud antes del inicio del ciclo de facturación que desea suspender. Si existe una cláusula de permanencia mínima, su vigencia se prorrogará por el tiempo que dure la suspensión.</p><br>
                        <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">TERMINACIÓN</p><br>
                        <p style="text-align: justify;" class="small">Usted puede terminar el contrato en cualquier momento sin penalidades. Para esto debe realizar una solicitud a través de cualquiera de nuestros Medios de Atención mínimo 3 días hábiles antes del corte de facturación (su corte de facturación es el día ___ de cada mes). Si presenta la solicitud con una anticipación menor, la terminación del servicio se dará en el siguiente periodo de facturación.<br><br>Así mismo, usted puede cancelar cualquiera de los servicios contratados, para lo que le informaremos las condiciones en las que serán prestados los servicios no cancelados y actualizaremos el contrato. Así mismo, si el operador no inicia la prestación del servicio en el plazo acordado, usted puede pedir la restitución de su dinero y la terminación del contrato.</p><br>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div style="">
        <table width="100%">
            <tbody>
                <tr>
                    <td style="vertical-align:top;" width="50%">
                        <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">PAGO Y FACTURACIÓN</p><br>
                        <p style="text-align: justify;" class="small">La factura le debe llegar como mínimo 5 días hábiles antes de la fecha de pago. Si no llega, puede solicitarla a través de nuestros Medios de Atención y debe pagarla oportunamente.<br>Si no paga a tiempo, previo aviso, suspenderemos su servicio hasta que pague sus saldos pendientes. Contamos con 3 días hábiles luego de su pago para reconectarle el servicio. Si no paga a tiempo, también podemos reportar su deuda a las centrales de riesgo.<br>Para esto tenemos que avisarle por lo menos con 20 días calendario de anticipación. Si paga luego de este reporte tenemos la obligación dentro del mes de seguimiento de informar su pago para que ya no aparezca reportado.<br>Si tiene un reclamo sobre su factura, puede presentarlo antes de la fecha de pago y en ese caso no debe pagar las sumas reclamadas hasta que resolvamos su solicitud. Si ya pagó, tiene 6 meses para presentar la reclamación</p><br>
                        <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">CÓMO COMUNICARSE CON NOSOTROS (MEDIOS DE ATENCIÓN)</p>

                        <table width="100%" style="margin: 0">
                            <tbody>
                                <tr>
                                    <th style="background-color: {{$empresa->color}}; color: white; text-align: center;" width="5%">1</th>
                                    <td style="border: 1px solid #000;font-size:11px " width="95%">
                                        <p style="font-size: 9px; padding:0 5px; text-align: justify;">Nuestros medios de atención son: oficinas físicas, página web, redes sociales y líneas telefónicas gratuitas.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table width="100%" style="margin: 0">
                            <tbody>
                                <tr>
                                    <th style="background-color: {{$empresa->color}}; color: white; text-align: center;" width="5%">2</th>
                                    <td style="border: 1px solid #000;font-size:11px" width="95%">
                                    <p style="font-size: 9px; padding:0 5px; text-align: justify;">Presente cualquier queja, petición/reclamo o recurso a través de estos medios y le responderemos en máximo 15 días hábiles.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table width="100%" style="margin: 0">
                            <tbody>
                                <tr>
                                    <th style="background-color: {{$empresa->color}}; color: white; text-align: center;" width="5%">3</th>
                                    <td style="border: 1px solid #000;font-size:11px" width="95%">
                                    <p style="font-size: 9px; padding:0 5px; text-align: justify;">Si no respondemos es porque aceptamos su petición o reclamo. Esto se llama silencio administrativo positivo y aplica para internet y telefonía.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <p style="font-size:9px; padding: 2px; margin:2px;text-align: center; font-weight: bold;">Si no está de acuerdo con nuestra respuesta</p>

                        <table width="100%" style="margin: 0">
                            <tbody>
                                <tr>
                                    <th style="background-color: {{$empresa->color}}; color: white; text-align: center;" width="5%">4</th>
                                    <td style="border: 1px solid #000;font-size:11px " width="95%">
                                    <p style="font-size: 9px; padding:0 5px; text-align: justify;">Cuando su queja o petición sea por los servicios de telefonía y/o internet, y esté relacionada con actos de negativa del contrato, suspensión del servicio, terminación del contrato, corte y facturación; usted puede insistir en su solicitud ante nosotros, dentro de los 10 días hábiles siguientes a la respuesta, y pedir que si no llegamos a una solución satisfactoria para usted, enviemos su reclamo directamente a la SIC (Superintendencia de Industria y Comercio) quien resolverá de manera definitiva su solicitud. Esto se llama recurso de reposición y en subsidio apelación. Cuando su queja o petición sea por el servicio de televisión, puede enviar la misma a la Autoridad Nacional de Televisión, para que esta Entidad resuelva su solicitud.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <p style="font-size:9px; text-align: justify;font-weight: bold;" class="small titulo-bg">ACEPTO CLÁUSULA DE PERMANENCIA MÍNIMA</p><br>

                        <p style="text-align: justify;" class="small">En consideración a que le estamos otorgando un descuento respecto del valor del cargo por conexión, o le diferimos el pago del mismo, se incluye la presente cláusula de permanencia mínima. En la factura encontrará el valor a pagar si decide terminar el contrato anticipadamente</p><br>

                        <table width="100%" style="font-size: 10px">
                            <tbody>
                                <tr>
                                    <th style="padding: 0px!important; background-color:{{$empresa->color}}; color: white; text-align: left; font-size: 10px;" width="65% pl-2">Valor total del cargo por conexión</th>
                                    <td style="padding: 0px!important; border: 1px solid {{$empresa->color}}; font-size: 10px" width="35%">
                                        <p style="padding: 0;margin:0;">$_______</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table width="100%" style="font-size: 10px">
                            <tbody>
                                <tr>
                                    <th style="background-color:{{$empresa->color}}; color: white; text-align: left; font-size: 10px;" width="65%">Suma que le fue descontada o diferida del valor total del cargo por conexión</th>
                                    <td style="border: 1px solid {{$empresa->color}}; font-size: 10px" width="35%">
                                        <p style="padding: 0;margin:0;">$_______</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table width="100%" style="font-size: 10px">
                            <tbody>
                                <tr>
                                    <th style="background-color:{{$empresa->color}}; color: white; text-align: left; font-size: 10px;" width="65%">Fecha de inicio de la permanencia mínima</th>
                                    <td style="border: 1px solid {{$empresa->color}}; font-size: 10px" width="35%">
                                        <p style="padding: 0;margin:0;">{{Carbon\Carbon::parse($contractDetails->created_at)->format('d-m-Y')}}</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table width="100%" style="font-size: 10px">
                            <tbody>
                                <tr>
                                    <th style="background-color:{{$empresa->color}}; color: white; text-align: left; font-size: 10px;" width="65%">Fecha de finalización de la permanencia mínima</th>
                                    <td style="border: 1px solid {{$empresa->color}}; font-size: 10px" width="35%">
                                        <p style="padding: 0;margin:0;">{{Carbon\Carbon::parse($contractDetails->created_at)->addYear()->format('d-m-Y')}}</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table width="100%" style="font-size: 10px;">
                            <thead>
                                <tr>
                                    <th style="background-color: {{$empresa->color}}; color: white; text-align: center; font-size: 10px; padding: 0;margin:0;">Valor a pagar si termina el contrato anticipadamente según el mes</th>
                                </tr>
                            </thead>
                        </table>

                        <table width="100%">
                            <tbody>
                                <tr style="background-color: {{$empresa->color}}; border: solid 1px {{$empresa->color}}; color: #fff; text-align: center;">
                                    @for ($i = 1; $i <= 6; $i++)
                                        <td style="font-size: 8px;">MES {{ $i }}</td>
                                    @endfor
                                </tr>
                                <tr class="tr-precios">
                                    @for ($i = 0; $i < 6; $i++)
                                    <td style="font-size: 7px; border: solid 1px {{$empresa->color}}; text-align: center;">
                                        @if($contract)
                                        {{$empresa->moneda}} {{ App\Funcion::Parsear(($empresa->clasula_permanencia / $contract->contrato_permanencia_meses) * (12-$i)) }}
                                        @else
                                        {{$empresa->moneda}} {{ App\Funcion::Parsear(($empresa->clasula_permanencia)) }}
                                        @endif
                                    </td>
                                    @endfor
                                </tr>

                                <tr style="background-color: {{$empresa->color}}; border: solid 1px {{$empresa->color}}; color: #fff; text-align: center;">
                                    @for ($i = 7; $i <= 12; $i++)
                                        <td style="font-size: 8px;">MES {{ $i }}</td>
                                    @endfor
                                </tr>
                                <tr class="tr-precios">
                                    @for ($i = 0; $i < 6; $i++)
                                    <td style="font-size: 7px; border: solid 1px {{$empresa->color}}; text-align: center;">
                                    @if($contract)
                                        {{$empresa->moneda}} {{ App\Funcion::Parsear(($empresa->clasula_permanencia / $contract->contrato_permanencia_meses) * (6-$i)) }}
                                        @else
                                        {{$empresa->moneda}} {{ App\Funcion::Parsear(($empresa->clasula_permanencia)) }}
                                    @endif
                                    </td>
                                    @endfor
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td style="vertical-align:top;" width="50%">
                        <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">CAMBIO DE DOMICILIO</p><br>
                        <p style="text-align: justify;" class="small">Usted puede cambiar de domicilio y continuar con el servicio siempre que sea técnicamente posible. Si desde el punto de vista técnico no es viable el traslado del servicio, usted puede ceder su contrato a un tercero o terminarlo pagando el valor de la cláusula de permanencia mínima si esta vigente.</p><br>
                        <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">LARGA DISTANCIA (TELEFONÍA)</p><br>
                        <p style="text-align: justify;" class="small">Nos comprometemos a usar el operador de larga distancia que usted nos indique, para lo cual debe marcar el código de larga distancia del operador que elija.</p><br>
                        <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">COBRO POR RECONEXIÓN DEL SERVICIO</p><br>
                        <p style="text-align: justify;" class="small">En caso de suspensión del servicio por mora en el pago, podremos cobrarle un valor por reconexión que corresponderá estrictamente a los costos asociados a la operación de reconexión. En caso de servicios empaquetados procede máximo un cobro de reconexión por cada tipo de conexión empleado en la prestación de los servicios. @if($contract) <b>{{ $contract->costo_reconexion > 0 ? 'Costo reconexión: '.$empresa->moneda.' '.App\Funcion::Parsear($contract->costo_reconexion) : '' }}</b> @endif </p><br>
                        <p style="padding: 5px; color: white; background-color: {{$empresa->color}};text-align: justify;" class="small">El usuario es el ÚNICO responsable por el contenido y la información que se curse a través de la red y del uso que se haga de los equipos o de los servicios.</p>
                        <p style="margin-top: 5px; padding: 5px; color: white; background-color: {{$empresa->color}};text-align: justify;" class="small">Los equipos de comunicaciones que ya no use son desechos que no deben ser botados a la caneca, consulte nuestra política de recolección de aparatos en desuso.</p>
                        @if(Auth::user()->empresa()->contrato_digital && $contract)
                        <div style="border: 1px  solid #000; margin-top: 5px;">
                            <p style="font-size: 9px;text-align: justify; padding:5px;" class="small">
                                {{ Auth::user()->empresa()->contrato_digital }}
                            </p>
                        </div>
                        @endif

                        <div style="border: 1px  solid #000; margin-top: 5px;text-align: center;">
                            <img src="data:image/png;base64,{{substr($contact->firma_isp,1)}}" style="width: 20%; margin-top: 12.5px;">
                            <p style="color: #9e9b9b;text-align: center;" class="small">Aceptación contrato mediante firma o cualquier otro medio válido</p>
                        </div>

                        <table width="100%" style="border: 1px solid #000; margin-top: 5px;">
                            <tbody>
                                <tr>
                                    <td style="margin: 0px; padding: 0px;">
                                        <p style="font-size:11px;margin: 2px 5px; padding: 0px; border: 0;">CC/CE <b>{{ $contact->nit }}</b></p>
                                    </td>
                                    <td style="margin: 0px; padding: 0px;">
                                        <p style="font-size:11px;margin: 2px 5px; padding: 0px; border: 0;">FECHA <b>{{ date('d/m/Y', strtotime($contact->fecha_isp)) }}</b></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div style="margin-top: 5px;">
                            <p style="color: #000;text-align: left; padding:5px 0;" class="small">Consulte el régimen de protección de usuarios en <a href="www.crcom.gov.co" target="_blank"><b>www.crcom.gov.co</b></a></p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div style="">
        <table width="100%">
            <tbody>
                <tr>
                    <td style="vertical-align:top;" width="50%">
                        <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">
                            ANEXO 1
                        </p>
                        <p style="text-align: center; margin-top: 5px;" class="small">
                            <br>
                            <b>AL CONTRATO DE PRESTACIÓN DE SERVICIOS DE TELECOMUNICACIONES, PARA PREVENIR Y CONTRARRESTAR LA EXPLOTACIÓN Y LA PORNOGRAFÍA INFANTIL</b></p><br>
                        <p style="text-align: justify;" class="small">
                            Las partes se comprometen de manera expresa y suscriben el presente documento en constancia, a dar cumplimiento a todas las disposiciones legales y reglamentarias sobre el adecuado uso de la red, y la prevención de acceso a páginas de contenido restringido, toda forma de explotación pornográfica, turismo sexual y demás formas de abuso de menores según lo previsto en la Ley 679 de 2001 y sus decretos reglamentarios. Así mismo se comprometen a implementar todas las medidas de tipo técnico que considere necesarias para prevenir dichas conductas.<br><br>En cumplimiento del artículo 7º del Decreto 1524 de 2002, "Por el cual reglamenta el artículo 5° de la Ley 679 de 2001" y con el objeto de prevenir el acceso de menores de edad a cualquier modalidad de información pornográfica contenida en Internet o en las distintas clases de redes informáticas a las cuales se tenga acceso mediante redes globales de información.<br><br>Así mismo con el fin de propender para que estos medios no sean aprovechados con fines de explotación sexual infantil u ofrecimiento de servicios comerciales que impliquen abuso sexual con menores de edad. Se advierte que el incumplimiento de las siguientes prohibiciones y deberes acarreará para el incumplido las sanciones administrativas y penales contempladas en la Ley 679 de 2001 y en el Decreto 1524 de 2002.<br><br>
                            <b>PROHIBICIONES.</b><br><br>
                            Los proveedores o servidores, administradores y usuarios de redes globales de información no podrán:<br><br>
                            1. Alojar en su propio sitio imágenes, textos, documentos o archivos audiovisuales que impliquen directa o indirectamente actividades sexuales con menores de edad.<br>
                            2. Alojar en su propio sitio material pornográfico, en especial en modo de imágenes o videos, cuando existan indicios de que las personas fotografiadas o filmadas son menores de edad.<br>
                            3. Alojar en su propio sitio vínculos o "links", sobre sitios telemáticos que contengan o distribuyan material pornográfico relativo a menores de edad.<br><br>
                            <b>DEBERES.</b><br><br>
                            Sin perjuicio de la obligación de denuncia consagrada en la ley para todos los residentes en Colombia, los proveedores, administradores y usuarios de redes globales de información deberán:<br><br>
                            1. Denunciar ante las autoridades competentes cualquier acto criminal contra menores de edad de que tengan conocimiento, incluso de la difusión de material pornográfico asociado a menores.<br>
                            2. Combatir con todos los medios técnicos a su alcance la difusión de material pornográfico con menores de edad.<br>
                            3. Abstenerse de usar las redes globales de información para divulgación de material ilegal con menores de edad.<br>
                            4. Establecer mecanismos técnicos de bloqueo por medio de los cuales los usuarios se puedan proteger a sí mismos o a sus hijos de material ilegal, ofensivo o indeseable en relación con menores de edad. Se prohíbe expresamente el alojamiento de contenidos de pornografía infantil.
                        </p>
                    </td>
                    <td style="vertical-align:top;" width="50%">
                        <p style="text-align: justify;" class="small">
                            Sanciones Administrativas. Los proveedores o servidores, administradores y usuarios que no cumplan o infrinjan lo establecido en el presente capítulo serán sancionados por el Ministerio de Tecnologías de la Información y las Comunicaciones sucesivamente de la siguiente manera:<br><br>
                            1. Multas hasta de cien (100) salarios mínimos legales mensuales vigentes, que serán pagadas al Fondo Contra la Explotación Sexual de Menores, de que trata el artículo 24 de la Ley 679 de 2001.<br>
                            2. Suspensión de la correspondiente página electrónica.<br>
                            3. Cancelación de la correspondiente página electrónica. Para la imposición de estas sanciones se aplicará el procedimiento establecido en el Código de Procedimiento Administrativo y de lo Contencioso Administrativo, con observancia del debido proceso y criterios de adecuación, proporcionalidad y reincidencia.<br><br>
                            Parágrafo. El Ministerio de Tecnologías de la Información y las Comunicaciones adelantará las investigaciones administrativas pertinentes e impondrá, si fuere el caso, las sanciones previstas en este Título, sin perjuicio de las investigaciones penales que adelanten las autoridades competentes y de las sanciones a que ello diere lugar.
                        </p>

                        <br>

                        <div style="border: 1px  solid #000; margin-top: 5px;text-align: center;">
                            <img src="data:image/png;base64,{{substr($contact->firma_isp,1)}}" style="width: 20%; margin-top: 12.5px;">
                            <p style="color: #9e9b9b;text-align: center;" class="small">Aceptación contrato mediante firma o cualquier otro medio válido</p>
                        </div>

                        <table width="100%" style="border: 1px solid #000; margin-top: 5px;">
                            <tbody>
                                <tr>
                                    <td style="margin: 0px; padding: 0px;">
                                        <p style="font-size:11px;margin: 2px 5px; padding: 0px; border: 0;">CC/CE <b>{{ $contact->nit }}</b></p>
                                    </td>
                                    <td style="margin: 0px; padding: 0px;">
                                        <p style="font-size:11px;margin: 2px 5px; padding: 0px; border: 0;">FECHA <b>{{ date('d/m/Y', strtotime($contact->fecha_isp)) }}</b></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div style="">
        <table width="100%">
            <tbody>
                <tr>
                    <td style="vertical-align:top;" width="50%">
                        <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">
                            ANEXO 2
                        </p>
                        <p style="text-align: center; margin-top: 5px;" class="small">
                            <br>
                            <b>AL CONTRATO DE PRESTACIÓN DE SERVICIOS DE TELECOMUNICACIONES RESPECTO AL USO DE DATOS PERSONALES</b></p><br>
                        <p style="text-align: justify;" class="small">
                            <u> {{ $contact->nombre }} {{ $contact->apellidos() }} </u>, identificado como aparece el pie de mi firma, por medio del presente escrito, manifiesto que he sido informado y autorizo <b>{{Auth::user()->empresa()->nombre}}</b> para que:<br><br>
                            Actúe como responsable del Tratamiento de datos personales de los cuales soy titular y que, conjunta o separadamente podrán recolectar, usar y tratar mis datos personales conforme la Política de Tratamiento de Datos Personales de la Compañía disponible en <b>{{Auth::user()->empresa()->web}}</b><br><br>
                            Es de carácter facultativo responder preguntas que versen sobre Datos Sensibles o sobre menores de edad.<br><br>
                            Mis derechos como titular de los datos son los previstos en la Constitución y la ley 1581 de 2012, especialmente el derecho a conocer, actualizar, rectificar y suprimir mi información personal, así como el derecho a revocar el consentimiento otorgado para el tratamiento de datos personales.<br><br>
                            Los derechos pueden ser ejercidos a través de los canales gratuitos dispuestos por la empresa y observando la Política de Tratamiento de Datos Personales de la misma.<br><br>
                            Para cualquier inquietud o información adicional relacionada con el tratamiento de datos personales, puedo contactarme al correo electrónico {{Auth::user()->empresa()->email }}<br><br>
                            La empresa garantiza la confidencialidad, libertad, seguridad, veracidad, transparencia, acceso y circulación restringida de mis datos y se reservan el derecho de modificar su Política de Tratamiento de Datos Personales en cualquier momento. Cualquier cambio será informado y publicado oportunamente en la página web.<br><br>
                            Teniendo en cuenta lo anterior, autorizo de manera voluntaria, previa, explícita, informada e inequívoca a <b>{{Auth::user()->empresa()->nombre}}</b> para tratar mis datos personales de acuerdo con la Política de Tratamiento de Datos Personales de la y para los fines relacionados con su objeto social y en especial para fines legales, contractuales, comerciales descritos en la Política de Tratamiento de Datos Personales de Empresa. La información obtenida para el Tratamiento de mis datos personales la he suministrado de forma voluntaria y es verídica.
                        </p>

                        <br>

                        <div style="border: 1px  solid #000; margin-top: 5px;text-align: center;">
                            <img src="data:image/png;base64,{{substr($contact->firma_isp,1)}}" style="width: 20%; margin-top: 12.5px;">
                            <p style="color: #9e9b9b;text-align: center;" class="small">Aceptación contrato mediante firma o cualquier otro medio válido</p>
                        </div>

                        <table width="100%" style="border: 1px solid #000; margin-top: 5px;">
                            <tbody>
                                <tr>
                                    <td style="margin: 0px; padding: 0px;">
                                        <p style="font-size:11px;margin: 2px 5px; padding: 0px; border: 0;">CC/CE <b>{{ $contact->nit }}</b></p>
                                    </td>
                                    <td style="margin: 0px; padding: 0px;">
                                        <p style="font-size:11px;margin: 2px 5px; padding: 0px; border: 0;">FECHA <b>{{ date('d/m/Y', strtotime($contact->fecha_isp)) }}</b></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td style="vertical-align:top;" width="50%">
                        <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">
                            ANEXO 3
                        </p>
                        <p style="text-align: center; margin-top: 5px;" class="small">
                            <br>
                            <b>FORMATO AUTORIZACIÓN CONSULTA CENTRALES DE RIESGO</b></p><br>
                        <p style="text-align: justify;" class="small">
                            Yo, <u> {{ $contact->nombre }} {{ $contact->apellidos() }} </u> identificado como aparece al pie de mi firma, obrando en mi propio nombre y/o en representación de: ____________________ AUTORIZO EXPRESA E IRREVOCABLEMENTE a <b>{{Auth::user()->empresa()->nombre}}</b>, libre y voluntariamente, para que reporte, consulte y divulgue a cualquier operador y/o fuente de información legalmente establecido, toda la información referente a mi comportamiento como cliente que se relacione con el nacimiento, ejecución, modificación, liquidación y/o extinción de las obligaciones que se deriven del presente contrato, en cualquier tiempo, y que podrá reflejarse en las bases de datos de DATACREDIDO, CIFIN, COVINOC o de cualquier otro operador y/o fuente de información legalmente establecido. La permanencia de la información estará sujeta a los principios, términos y condiciones consagrados en la ley 1266 de 2008 y demás normas que lo modifiquen, aclaren o reglamenten. Así mismo, autorizo, expresa e irrevocablemente a <b>{{Auth::user()->empresa()->nombre}}</b> para que consulte toda la información financiera, crediticia, comercial, de servicios y la proveniente de otros países, atinente a mis relaciones comerciales que tenga con el Sistema Financiero, comercial y de servicios, o de cualquier sector, tanto en Colombia como en el Exterior, en cualquier tiempo. PARÁGRAFO: La presente autorización se extiende para que <b>{{Auth::user()->empresa()->nombre}}</b> pueda compartir información con terceros públicos o privados, bien sea que éstos ostenten la condición de fuentes de información, operadores de información o usuarios, con quienes EL CLIENTE tenga vínculos jurídicos de cualquier naturaleza, todo conforme a lo establecido en las normas legales vigentes dentro del marco del Sistema de Administración de Riesgos de Lavado de Activos y Financiación al Terrorismo SARLAFT de <b>{{Auth::user()->empresa()->nombre}}</b>
                        </p>

                        <br>

                        <div style="border: 1px  solid #000; margin-top: 5px;text-align: center;">
                            <img src="data:image/png;base64,{{substr($contact->firma_isp,1)}}" style="width: 20%; margin-top: 12.5px;">
                            <p style="color: #9e9b9b;text-align: center;" class="small">Aceptación contrato mediante firma o cualquier otro medio válido</p>
                        </div>

                        <table width="100%" style="border: 1px solid #000; margin-top: 5px;">
                            <tbody>
                                <tr>
                                    <td style="margin: 0px; padding: 0px;">
                                        <p style="font-size:11px;margin: 2px 5px; padding: 0px; border: 0;">CC/CE <b>{{ $contact->nit }}</b></p>
                                    </td>
                                    <td style="margin: 0px; padding: 0px;">
                                        <p style="font-size:11px;margin: 2px 5px; padding: 0px; border: 0;">FECHA <b>{{ date('d/m/Y', strtotime($contact->fecha_isp)) }}</b></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div style="">
        <table width="100%">
            <tbody>
                <tr>
                    <td style="vertical-align:top;" width="50%">
                        <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">
                            ANEXO 4
                        </p>
                        <p style="text-align: center; margin-top: 5px;" class="small">
                            <br>
                            <b>CARTA DE INSTRUCCIONES</b>
                        </p>
                        <br>
                        <p style="text-align: justify;" class="small">
                            Usted manifiesta que, de acuerdo con lo establecido en el artículo 622 del Código de Comercio, autoriza expresa e irrevocablemente a nosotros para llenar los espacios en blanco del pagaré suscrito simultáneamente con la firma del presente contrato, otorgado en su favor, los cuales corresponden a la fecha de vencimiento y cuantía del capital.<br><br>
                            El pagare podrá ser llenado sin previo aviso, de acuerdo con las siguientes instrucciones:
                            <br><br>
                            - La cuantía será igual al monto de todas las sumas que, por cualquier concepto le esté debiendo a <b>{{Auth::user()->empresa()->nombre}}</b> el día que sea llenado incluyendo en dicha cuantía el valor de aquellas obligaciones que se declaren se plazo vencido como anteriormente se autorizó.<br>
                            - La fecha de vencimiento será el día que el titulo sea llenado o la del día siguiente.<br>
                            - En materia de intereses se observarán para su cálculo y liquidación las siguientes pautas:<br><br>
                            1. Los intereses de mora serán los máximos legalmente autorizados por la ley.<br>
                            2. Si al momento de ser llenado este pagare se han causado intereses sobre la obligación, estos se incluirán dentro de la cuantía total.<br>
                            3. En el caso de mi incumplimiento optare por declarar vencido el plazo pactado y hacer exigible la cancelación de todas las obligaciones a mi cargo. declarar vencido el plazo pactado y hacer exigible la cancelación de todas las obligaciones a mi cargo.<br><br>
                            Adicionalmente, con la suscripción del presente documento autorizamos a <b>{{Auth::user()->empresa()->nombre}}</b>, en forma libre, expresa e irrevocable, para que obtenga, reporte y actualice ene cualquier banco de datos, la información y referencias sobre nosotros como personas naturales y/o jurídicas: nombre apellido y documento de identidad; nuestro comportamiento crédito comercial, hábitos de pago manejo de cuentas bancarias y en general el cumplimiento de nuestras obligaciones pecuniarias. El pagare, si llenado, será exigible inmediatamente, y prestará merito ejecutivo sin formalidad adicional alguna.<br><br>
                            PAGARE.<br>
                            Yo, <u> {{ $contact->nombre }} {{ $contact->apellidos() }} </u>, identificado con {{ $contact->tip_iden('corta') }} numero {{ $contact->nit }}  @if($contact->dv != null || $contact->dv === 0)-{{$contact->dv}} @endif, actuando en nombre propio, por medio del presente escrito manifiesto, lo siguiente:<br><br>
                            PRIMERO: Que debo y pagaré, incondicional y solidariamente a la orden de la empresa <b>{{Auth::user()->empresa()->nombre}}</b> o quien represente sus derechos la cantidad de ____________________ ($____________________) pesos moneda legal Colombiana.<br><br>
                            SEGUNDO: Que el pago total de la mencionada obligación se efectuará en un sólo contado, el día _____ del mes de __________ del año _____________.<br><br>
                            TERCERO: Que en caso de mora pagaré, intereses de mora a la más alta tasa permitida por la Ley, desde el día siguiente a la fecha de exigibilidad del presente pagaré, y hasta cuando su pago total se efectúe.<br><br>
                            CUARTO: Expresamente declaro excusado el protesto del presente pagaré y los requerimientos judiciales o extrajudiciales para la constitución en mora.<br><br>
                            QUINTO: En caso de que haya lugar al recaudo judicial o extrajudicial de la obligación contenida en el presente título valor será a mi cargo las costas judiciales y/o los honorarios que se causen por tal razón. En constancia de lo anterior, se suscribe en la ciudad de __________________, a los ____ días del mes de ______ del año _______.
                        </p>
                    </td>
                    <td style="vertical-align:top;" width="50%">
                        <div style="border: 1px  solid #000; margin-top: 0px;text-align: center;">
                            <img src="data:image/png;base64,{{substr($contact->firma_isp,1)}}" style="width: 20%; margin-top: 12.5px;">
                            <p style="color: #9e9b9b;text-align: center;" class="small">Aceptación contrato mediante firma o cualquier otro medio válido</p>
                        </div>

                        <table width="100%" style="border: 1px solid #000; margin-top: 5px;">
                            <tbody>
                                <tr>
                                    <td style="margin: 0px; padding: 0px;">
                                        <p style="font-size:11px;margin: 2px 5px; padding: 0px; border: 0;">CC/CE <b>{{ $contact->nit }}</b></p>
                                    </td>
                                    <td style="margin: 0px; padding: 0px;">
                                        <p style="font-size:11px;margin: 2px 5px; padding: 0px; border: 0;">FECHA <b>{{ date('d/m/Y', strtotime($contact->fecha_isp)) }}</b></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
@endsection
