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
            background-color: {{Auth::user()->empresa()->color}};
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
                        <div style="width: 100%; margin-top: 10px; text-align:center;">
                            <img src="{{asset('images/Empresas/Empresa'.Auth::user()->empresa.'/'.Auth::user()->empresa()->logo)}}" alt="" style="width: 100px;">
                        </div>

                        <div style="width: 100%; background-color: {{Auth::user()->empresa()->color}}; clear:both;  margin-top: 10px;">
                            <p style="color: white; margin: 2px; text-align: justify; padding: 5px;" class="">
                                Este contrato explica las condiciones para la prestaci??n de los servicios entre usted y <b>{{Auth::user()->empresa()->nombre}}</b>, por el que pagar?? m??nimo mensualmente <b>$ _______</b>. Este contrato tendr?? vigencia de ____ meses, contados a partir del <b>__/__/____</b>. El plazo m??ximo de instalaci??n es de 15 d??as h??biles. Acepto que mi contrato se renueve sucesiva y autom??ticamente por un plazo igual al inicial <input checked="checked" type="checkbox"> *
                            </p>
                        </div>

                        <div style="width: 100%;  margin-top: 10px;">
                            <table width="100%">
                                <thead>
                                    <tr>
                                        <th style="padding: 3px 6px;background-color: {{Auth::user()->empresa()->color}}; color: white; text-align: left;border: 1px solid #000;">EL SERVICIO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <p style="text-align: justify;" class="small">Con este contrato nos comprometemos a prestarle los servicios que usted elija*:</p>
                                            <p style="text-align: justify;" class="small">Telefon??a fija <input type="checkbox" /> Internet fijo <input type="checkbox" {{isset($contrato->contrato()->server_configuration_id) ? 'checked="checked' : ''}} /> Televisi??n <input type="checkbox" {{isset($contrato->contrato()->servicio_tv) ? 'checked="checked' : ''}}></p>
                                            <p style="text-align: justify;" class="small">Servicios adicionales ______________________________</p>
                                            <p style="text-align: justify;" class="small">Usted se compromete a pagar oportunamente el precio acordado.</p>
                                            <p style="text-align: justify;" class="small mb-2">El servicio se activar?? a m??s tardar el d??a <b>__/__/____</b>.</p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div style="width: 100%;  margin-top: 10px; border: 1px  solid #000;">
                            <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">INFORMACI??N DEL SUSCRIPTOR</p><br>
                            <p style="text-align: justify;" class="small pl-2">Contrato No.: <b>{{ $contrato->details() ? $contrato->details()->nro : '' }}</b> </p>
                            <p style="text-align: justify;" class="small pl-2">Nombre / Raz??n Social: <b>{{ $contrato->nombre }} {{ $contrato->apellidos() }}</b></p>
                            <p style="text-align: justify;" class="small pl-2">Identificaci??n: <b>{{ $contrato->tip_iden('corta') }} {{ $contrato->nit }}@if($contrato->dv != null || $contrato->dv === 0)-{{$contrato->dv}} @endif</b></p>
                            <p style="text-align: justify;" class="small pl-2">Correo electr??nico: <b>{{ $contrato->email }}</b></p>
                            <p style="text-align: justify;" class="small pl-2">Tel??fono de contacto: <b>{{ $contrato->celular }}</b></p>
                            <p style="text-align: justify;" class="small pl-2">Direcci??n Servicio: <b>{{ $contrato->direccion }}</b> Estrato: <b>{{ $contrato->estrato ? $contrato->estrato : '   ' }}</b></p>
                            <p style="text-align: justify;" class="small pl-2">Departamento: <b>{{ $contrato->departamento()->nombre }}</b> Municipio: <b>{{ $contrato->municipio()->nombre }}</b></p>
                            <p style="text-align: justify;" class="small pl-2">Direcci??n Suscriptor: <b>{{ $contrato->direccion }}</b></p><br>
                        </div>

                        <div style="width: 100%;  margin-top: 5px; border: 1px  solid #000;">
                            <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">CONDICIONES COMERCIALES CARACTER??STICAS DEL PLAN</p><br>
                            <p style="text-align: justify;" class="small pl-2">Tipo de Cliente: Nuevo <input type="checkbox" /> Modificaci??n <input type="checkbox" /></p>
                            @if(isset($contrato->contrato()->tecnologia))
                            <p style="text-align: justify;" class="small pl-2">Tipo red: FTTH <input type="checkbox" {{$contrato->contrato()->tecnologia == 1 ? 'checked="checked' : ''}}> WIRELESS <input type="checkbox" {{$contrato->contrato()->tecnologia == 2 ? 'checked="checked' : ''}}></p><br>
                            @endif
                            <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">INTERNET</p>
                            <table style="width: 100%; text-align:center; padding:5px;">
                                <tr style="background-color: {{Auth::user()->empresa()->color}}; color: #fff;">
                                    <td colspan="2">Incluidos en el plan</td>
                                    <td colspan="2">Adicionales</td>
                                </tr>
                                <tr>
                                    <td style="font-size: 9px;">Megas Down</td>
                                    <td>{{ isset($contrato->details()->server_configuration_id) ? $contrato->details()->plan()->download : '' }}</td>
                                    <td style="font-size: 9px;">Ip Fijo</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td style="font-size: 9px;">Megas Up</td>
                                    <td>{{ isset($contrato->details()->server_configuration_id) ? $contrato->details()->plan()->upload : '' }}</td>
                                    <td style="font-size: 9px;">Otros</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td style="font-size: 9px;">Valor</td>
                                    <td style="font-size: 9px;"><b>{{Auth::user()->empresa()->moneda}} {{ isset($contrato->details()->server_configuration_id) ? App\Funcion::Parsear($contrato->details()->plan()->price) : '________' }}</b></td>
                                    <td style="font-size: 9px;">Total</td>
                                    <td style="font-size: 9px;">{{Auth::user()->empresa()->moneda}} {{ isset($contrato->details()->server_configuration_id) ? App\Funcion::Parsear($contrato->details()->plan()->price) : '________' }}</td>
                                </tr>
                            </table>

                            <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">TELEVISI??N</p>
                            <table style="width: 100%; text-align:center; padding:5px;">
                                <tr style="background-color: {{Auth::user()->empresa()->color}}; color: #fff;">
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
                                    <td style="font-size: 9px;">{{Auth::user()->empresa()->moneda}}{{ isset($contrato->contrato()->servicio_tv) ? App\Funcion::Parsear((($contrato->contrato()->plan('true')->precio * $contrato->contrato()->plan('true')->impuesto)/100)+$contrato->contrato()->plan('true')->precio) : '________' }}</td>
                                    <td style="font-size: 9px;">Total</td>
                                    <td style="font-size: 9px;">{{Auth::user()->empresa()->moneda}} {{ isset($contrato->contrato()->servicio_tv) ? App\Funcion::Parsear((($contrato->contrato()->plan('true')->precio * $contrato->contrato()->plan('true')->impuesto)/100)+$contrato->contrato()->plan('true')->precio) : '________' }}</td>
                                    @php
                                    $total_tv = 0; $total_internet = 0;
                                    if (isset($contrato->contrato()->servicio_tv)){
                                        $total_tv = (($contrato->contrato()->plan('true')->precio * $contrato->contrato()->plan('true')->impuesto)/100)+$contrato->contrato()->plan('true')->precio;
                                    }
                                    if (isset($contrato->contrato()->server_configuration_id)){
                                        $total_internet = $contrato->contrato()->plan()->price;
                                    }
                                    @endphp
                                </tr>
                            </table>
                        </div>

                        <div style="border: 1px  solid #000; margin-top: 5px; padding:2px; text-align: right;">
                            VALOR TOTAL <span style="background-color:silver;">&nbsp;&nbsp;&nbsp;{{Auth::user()->empresa()->moneda}} {{ App\Funcion::Parsear($total_tv + $total_internet) }}&nbsp;&nbsp;&nbsp;</span>&nbsp;&nbsp;&nbsp;
                        </div>

                        <br><p style="text-align: justify; color: blue;" class="small">* Espacio diligenciado por el usuario</p>
                    </td>

                    <td style="vertical-align:top;" width="50%">
                        <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">PRINCIPALES OBLIGACIONES DEL USUARIO</p><br>
                        <p style="text-align: justify;" class="small">1) Pagar oportunamente los servicios prestados, incluyendo los intereses de mora cuando haya incumplimiento 2) suministrar informaci??n verdadera 3) hacer uso adecuado de los equipos y los servicios 4) No divulgar ni acceder a pornograf??a infantil (consultar anexo) 5) avisar a las autoridades cualquier evento de robo o hurto de elementos de la red, como el cable 6) No cometer o ser part??cipe de fraude 7) hacer uso adecuado de su derecho a presentar PQR. 8) actuar de buena fe. El operador podr?? terminar el contrato ante incumplimiento de estas obligaciones.</p><br>
                        <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">CALIDAD Y COMPENSACI??N</p><br>
                        <p style="text-align: justify;" class="small">Cuando se presente indisponibilidad del servicio o este se suspenda a pesar de su pago oportuno, lo compensaremos en su pr??xima factura. Debemos cumplir con las condiciones de calidad definidas por la CRC.<br>Cons??ltelas en la p??gina: {{ Auth::user()->empresa()->web }}</p><br>
                        <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">CESI??N</p><br>
                        <p style="text-align: justify;" class="small">Si quiere ceder este contrato a otra persona, debe presentar una solicitud por escrito a trav??s de nuestros Medios de Atenci??n, acompa??ada de la aceptaci??n por escrito de la persona a la que se har?? la cesi??n. Dentro de los 15 d??as h??biles siguientes, analizaremos su solicitud y le daremos una respuesta. Si se acepta la cesi??n queda liberado de cualquier responsabilidad con nosotros.</p><br>
                        <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">MODIFICACI??N</p><br>
                        <p style="text-align: justify;" class="small">Nosotros no podemos modificar el contrato sin su autorizaci??n. Esto incluye que no podemos cobrarle servicios que no haya aceptado expresamente. Si esto ocurre tiene derecho a terminar el contrato, incluso estando vigente la cl??usula de permanencia m??nima, sin la obligaci??n de pagar suma alguna por este concepto. No obstante, usted puede en cualquier momento modificar los servicios contratados. Dicha modificaci??n se har?? efectiva en el per??odo de facturaci??n siguiente, para lo cual deber?? presentar la solicitud de modificaci??n por lo menos con 3 d??as h??biles de anterioridad al corte de facturaci??n.</p><br>
                        <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">SUSPENSI??N</p><br>
                        <p style="text-align: justify;" class="small">Usted tiene derecho a solicitar la suspensi??n del servicio por un m??ximo de 2 meses al a??o. Para esto debe presentar la solicitud antes del inicio del ciclo de facturaci??n que desea suspender. Si existe una cl??usula de permanencia m??nima, su vigencia se prorrogar?? por el tiempo que dure la suspensi??n.</p><br>
                        <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">TERMINACI??N</p><br>
                        <p style="text-align: justify;" class="small">Usted puede terminar el contrato en cualquier momento sin penalidades. Para esto debe realizar una solicitud a trav??s de cualquiera de nuestros Medios de Atenci??n m??nimo 3 d??as h??biles antes del corte de facturaci??n (su corte de facturaci??n es el d??a ___ de cada mes). Si presenta la solicitud con una anticipaci??n menor, la terminaci??n del servicio se dar?? en el siguiente periodo de facturaci??n.<br><br>As?? mismo, usted puede cancelar cualquiera de los servicios contratados, para lo que le informaremos las condiciones en las que ser??n prestados los servicios no cancelados y actualizaremos el contrato. As?? mismo, si el operador no inicia la prestaci??n del servicio en el plazo acordado, usted puede pedir la restituci??n de su dinero y la terminaci??n del contrato.</p><br>
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
                        <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">PAGO Y FACTURACI??N</p><br>
                        <p style="text-align: justify;" class="small">La factura le debe llegar como m??nimo 5 d??as h??biles antes de la fecha de pago. Si no llega, puede solicitarla a trav??s de nuestros Medios de Atenci??n y debe pagarla oportunamente.<br>Si no paga a tiempo, previo aviso, suspenderemos su servicio hasta que pague sus saldos pendientes. Contamos con 3 d??as h??biles luego de su pago para reconectarle el servicio. Si no paga a tiempo, tambi??n podemos reportar su deuda a las centrales de riesgo.<br>Para esto tenemos que avisarle por lo menos con 20 d??as calendario de anticipaci??n. Si paga luego de este reporte tenemos la obligaci??n dentro del mes de seguimiento de informar su pago para que ya no aparezca reportado.<br>Si tiene un reclamo sobre su factura, puede presentarlo antes de la fecha de pago y en ese caso no debe pagar las sumas reclamadas hasta que resolvamos su solicitud. Si ya pag??, tiene 6 meses para presentar la reclamaci??n</p><br>
                        <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">C??MO COMUNICARSE CON NOSOTROS (MEDIOS DE ATENCI??N)</p>

                        <table width="100%" style="margin: 0">
                            <tbody>
                                <tr>
                                    <th style="background-color: {{Auth::user()->empresa()->color}}; color: white; text-align: center;" width="5%">1</th>
                                    <td style="border: 1px solid #000;font-size:11px " width="95%">
                                        <p style="font-size: 9px; padding:0 5px; text-align: justify;">Nuestros medios de atenci??n son: oficinas f??sicas, p??gina web, redes sociales y l??neas telef??nicas gratuitas.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table width="100%" style="margin: 0">
                            <tbody>
                                <tr>
                                    <th style="background-color: {{Auth::user()->empresa()->color}}; color: white; text-align: center;" width="5%">2</th>
                                    <td style="border: 1px solid #000;font-size:11px" width="95%">
                                    <p style="font-size: 9px; padding:0 5px; text-align: justify;">Presente cualquier queja, petici??n/reclamo o recurso a trav??s de estos medios y le responderemos en m??ximo 15 d??as h??biles.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table width="100%" style="margin: 0">
                            <tbody>
                                <tr>
                                    <th style="background-color: {{Auth::user()->empresa()->color}}; color: white; text-align: center;" width="5%">3</th>
                                    <td style="border: 1px solid #000;font-size:11px" width="95%">
                                    <p style="font-size: 9px; padding:0 5px; text-align: justify;">Si no respondemos es porque aceptamos su petici??n o reclamo. Esto se llama silencio administrativo positivo y aplica para internet y telefon??a.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <p style="font-size:9px; padding: 2px; margin:2px;text-align: center; font-weight: bold;">Si no est?? de acuerdo con nuestra respuesta</p>

                        <table width="100%" style="margin: 0">
                            <tbody>
                                <tr>
                                    <th style="background-color: {{Auth::user()->empresa()->color}}; color: white; text-align: center;" width="5%">4</th>
                                    <td style="border: 1px solid #000;font-size:11px " width="95%">
                                    <p style="font-size: 9px; padding:0 5px; text-align: justify;">Cuando su queja o petici??n sea por los servicios de telefon??a y/o internet, y est?? relacionada con actos de negativa del contrato, suspensi??n del servicio, terminaci??n del contrato, corte y facturaci??n; usted puede insistir en su solicitud ante nosotros, dentro de los 10 d??as h??biles siguientes a la respuesta, y pedir que si no llegamos a una soluci??n satisfactoria para usted, enviemos su reclamo directamente a la SIC (Superintendencia de Industria y Comercio) quien resolver?? de manera definitiva su solicitud. Esto se llama recurso de reposici??n y en subsidio apelaci??n. Cuando su queja o petici??n sea por el servicio de televisi??n, puede enviar la misma a la Autoridad Nacional de Televisi??n, para que esta Entidad resuelva su solicitud.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <p style="font-size:9px; text-align: justify;font-weight: bold;" class="small titulo-bg">ACEPTO CL??USULA DE PERMANENCIA M??NIMA</p><br>

                        <p style="text-align: justify;" class="small">En consideraci??n a que le estamos otorgando un descuento respecto del valor del cargo por conexi??n, o le diferimos el pago del mismo, se incluye la presente cl??usula de permanencia m??nima. En la factura encontrar?? el valor a pagar si decide terminar el contrato anticipadamente</p><br>

                        <table width="100%" style="font-size: 10px">
                            <tbody>
                                <tr>
                                    <th style="padding: 0px!important; background-color:{{Auth::user()->empresa()->color}}; color: white; text-align: left; font-size: 10px;" width="65% pl-2">Valor total del cargo por conexi??n</th>
                                    <td style="padding: 0px!important; border: 1px solid {{Auth::user()->empresa()->color}}; font-size: 10px" width="35%">
                                        <p style="padding: 0;margin:0;">$_______</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table width="100%" style="font-size: 10px">
                            <tbody>
                                <tr>
                                    <th style="background-color:{{Auth::user()->empresa()->color}}; color: white; text-align: left; font-size: 10px;" width="65%">Suma que le fue descontada o diferida del valor total del cargo por conexi??n</th>
                                    <td style="border: 1px solid {{Auth::user()->empresa()->color}}; font-size: 10px" width="35%">
                                        <p style="padding: 0;margin:0;">$_______</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table width="100%" style="font-size: 10px">
                            <tbody>
                                <tr>
                                    <th style="background-color:{{Auth::user()->empresa()->color}}; color: white; text-align: left; font-size: 10px;" width="65%">Fecha de inicio de la permanencia m??nima</th>
                                    <td style="border: 1px solid {{Auth::user()->empresa()->color}}; font-size: 10px" width="35%">
                                        <p style="padding: 0;margin:0;">{{Carbon\Carbon::parse($contrato->created_at)->format('d-m-Y')}}</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table width="100%" style="font-size: 10px">
                            <tbody>
                                <tr>
                                    <th style="background-color:{{Auth::user()->empresa()->color}}; color: white; text-align: left; font-size: 10px;" width="65%">Fecha de finalizaci??n de la permanencia m??nima</th>
                                    <td style="border: 1px solid {{Auth::user()->empresa()->color}}; font-size: 10px" width="35%">
                                        <p style="padding: 0;margin:0;">{{Carbon\Carbon::parse($contrato->created_at)->addYear()->format('d-m-Y')}}</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <table width="100%" style="font-size: 10px;">
                            <thead>
                                <tr>
                                    <th style="background-color: {{Auth::user()->empresa()->color}}; color: white; text-align: center; font-size: 10px; padding: 0;margin:0;">Valor a pagar si termina el contrato anticipadamente seg??n el mes</th>
                                </tr>
                            </thead>
                        </table>

                        <table width="100%">
                            <tbody>
                                <tr style="background-color: {{Auth::user()->empresa()->color}}; border: solid 1px {{Auth::user()->empresa()->color}}; color: #fff; text-align: center;">
                                    @for ($i = 1; $i <= 6; $i++)
                                        <td style="font-size: 8px;">MES {{ $i }}</td>
                                    @endfor
                                </tr>
                                <tr class="tr-precios">
                                    @for ($i = 0; $i < 6; $i++)
                                    <td style="font-size: 7px; border: solid 1px {{Auth::user()->empresa()->color}}; text-align: center;">
                                        {{Auth::user()->empresa()->moneda}} {{ App\Funcion::Parsear((Auth::user()->empresa()->clausula_permanencia / $contrato->contrato()->contrato_permanencia_meses) * (12-$i)) }}
                                    </td>
                                    @endfor
                                </tr>

                                <tr style="background-color: {{Auth::user()->empresa()->color}}; border: solid 1px {{Auth::user()->empresa()->color}}; color: #fff; text-align: center;">
                                    @for ($i = 7; $i <= 12; $i++)
                                        <td style="font-size: 8px;">MES {{ $i }}</td>
                                    @endfor
                                </tr>
                                <tr class="tr-precios">
                                    @for ($i = 0; $i < 6; $i++)
                                    <td style="font-size: 7px; border: solid 1px {{Auth::user()->empresa()->color}}; text-align: center;">
                                        {{Auth::user()->empresa()->moneda}} {{ App\Funcion::Parsear((Auth::user()->empresa()->clausula_permanencia / $contrato->contrato()->contrato_permanencia_meses) * (6-$i)) }}
                                    </td>
                                    @endfor
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td style="vertical-align:top;" width="50%">
                        <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">CAMBIO DE DOMICILIO</p><br>
                        <p style="text-align: justify;" class="small">Usted puede cambiar de domicilio y continuar con el servicio siempre que sea t??cnicamente posible. Si desde el punto de vista t??cnico no es viable el traslado del servicio, usted puede ceder su contrato a un tercero o terminarlo pagando el valor de la cl??usula de permanencia m??nima si esta vigente.</p><br>
                        <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">LARGA DISTANCIA (TELEFON??A)</p><br>
                        <p style="text-align: justify;" class="small">Nos comprometemos a usar el operador de larga distancia que usted nos indique, para lo cual debe marcar el c??digo de larga distancia del operador que elija.</p><br>
                        <p style="text-align: justify;font-weight: bold;" class="small titulo-bg">COBRO POR RECONEXI??N DEL SERVICIO</p><br>
                        <p style="text-align: justify;" class="small">En caso de suspensi??n del servicio por mora en el pago, podremos cobrarle un valor por reconexi??n que corresponder?? estrictamente a los costos asociados a la operaci??n de reconexi??n. En caso de servicios empaquetados procede m??ximo un cobro de reconexi??n por cada tipo de conexi??n empleado en la prestaci??n de los servicios. <b>{{ $contrato->contrato()->costo_reconexion > 0 ? 'Costo reconexi??n: '.Auth::user()->empresa()->moneda.' '.App\Funcion::Parsear($contrato->contrato()->costo_reconexion) : '' }}</b></p><br>
                        <p style="padding: 5px; color: white; background-color: {{Auth::user()->empresa()->color}};text-align: justify;" class="small">El usuario es el ??NICO responsable por el contenido y la informaci??n que se curse a trav??s de la red y del uso que se haga de los equipos o de los servicios.</p>
                        <p style="margin-top: 5px; padding: 5px; color: white; background-color: {{Auth::user()->empresa()->color}};text-align: justify;" class="small">Los equipos de comunicaciones que ya no use son desechos que no deben ser botados a la caneca, consulte nuestra pol??tica de recolecci??n de aparatos en desuso.</p>
                        @if(Auth::user()->empresa()->contrato_digital)
                        <div style="border: 1px  solid #000; margin-top: 5px;">
                            <p style="font-size: 9px;text-align: justify; padding:5px;" class="small">
                                {{ Auth::user()->empresa()->contrato_digital }}
                            </p>
                        </div>
                        @endif

                        <div style="border: 1px  solid #000; margin-top: 5px;text-align: center;">
                            <img src="data:image/png;base64,{{substr($contrato->firma_isp,1)}}" style="width: 20%; margin-top: 12.5px;">
                            <p style="color: #9e9b9b;text-align: center;" class="small">Aceptaci??n contrato mediante firma o cualquier otro medio v??lido</p>
                        </div>

                        <table width="100%" style="border: 1px solid #000; margin-top: 5px;">
                            <tbody>
                                <tr>
                                    <td style="margin: 0px; padding: 0px;">
                                        <p style="font-size:11px;margin: 2px 5px; padding: 0px; border: 0;">CC/CE <b>{{ $contrato->nit }}</b></p>
                                    </td>
                                    <td style="margin: 0px; padding: 0px;">
                                        <p style="font-size:11px;margin: 2px 5px; padding: 0px; border: 0;">FECHA <b>{{ date('d/m/Y', strtotime($contrato->fecha_isp)) }}</b></p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div style="margin-top: 5px;">
                            <p style="color: #000;text-align: left; padding:5px 0;" class="small">Consulte el r??gimen de protecci??n de usuarios en <a href="www.crcom.gov.co" target="_blank"><b>www.crcom.gov.co</b></a></p>
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
                            <b>AL CONTRATO DE PRESTACI??N DE SERVICIOS DE TELECOMUNICACIONES, PARA PREVENIR Y CONTRARRESTAR LA EXPLOTACI??N Y LA PORNOGRAF??A INFANTIL</b></p><br>
                        <p style="text-align: justify;" class="small">
                            Las partes se comprometen de manera expresa y suscriben el presente documento en constancia, a dar cumplimiento a todas las disposiciones legales y reglamentarias sobre el adecuado uso de la red, y la prevenci??n de acceso a p??ginas de contenido restringido, toda forma de explotaci??n pornogr??fica, turismo sexual y dem??s formas de abuso de menores seg??n lo previsto en la Ley 679 de 2001 y sus decretos reglamentarios. As?? mismo se comprometen a implementar todas las medidas de tipo t??cnico que considere necesarias para prevenir dichas conductas.<br><br>En cumplimiento del art??culo 7?? del Decreto 1524 de 2002, "Por el cual reglamenta el art??culo 5?? de la Ley 679 de 2001" y con el objeto de prevenir el acceso de menores de edad a cualquier modalidad de informaci??n pornogr??fica contenida en Internet o en las distintas clases de redes inform??ticas a las cuales se tenga acceso mediante redes globales de informaci??n.<br><br>As?? mismo con el fin de propender para que estos medios no sean aprovechados con fines de explotaci??n sexual infantil u ofrecimiento de servicios comerciales que impliquen abuso sexual con menores de edad. Se advierte que el incumplimiento de las siguientes prohibiciones y deberes acarrear?? para el incumplido las sanciones administrativas y penales contempladas en la Ley 679 de 2001 y en el Decreto 1524 de 2002.<br><br>
                            <b>PROHIBICIONES.</b><br><br>
                            Los proveedores o servidores, administradores y usuarios de redes globales de informaci??n no podr??n:<br><br>
                            1. Alojar en su propio sitio im??genes, textos, documentos o archivos audiovisuales que impliquen directa o indirectamente actividades sexuales con menores de edad.<br>
                            2. Alojar en su propio sitio material pornogr??fico, en especial en modo de im??genes o videos, cuando existan indicios de que las personas fotografiadas o filmadas son menores de edad.<br>
                            3. Alojar en su propio sitio v??nculos o "links", sobre sitios telem??ticos que contengan o distribuyan material pornogr??fico relativo a menores de edad.<br><br>
                            <b>DEBERES.</b><br><br>
                            Sin perjuicio de la obligaci??n de denuncia consagrada en la ley para todos los residentes en Colombia, los proveedores, administradores y usuarios de redes globales de informaci??n deber??n:<br><br>
                            1. Denunciar ante las autoridades competentes cualquier acto criminal contra menores de edad de que tengan conocimiento, incluso de la difusi??n de material pornogr??fico asociado a menores.<br>
                            2. Combatir con todos los medios t??cnicos a su alcance la difusi??n de material pornogr??fico con menores de edad.<br>
                            3. Abstenerse de usar las redes globales de informaci??n para divulgaci??n de material ilegal con menores de edad.<br>
                            4. Establecer mecanismos t??cnicos de bloqueo por medio de los cuales los usuarios se puedan proteger a s?? mismos o a sus hijos de material ilegal, ofensivo o indeseable en relaci??n con menores de edad. Se proh??be expresamente el alojamiento de contenidos de pornograf??a infantil.
                        </p>
                    </td>
                    <td style="vertical-align:top;" width="50%">
                        <p style="text-align: justify;" class="small">
                            Sanciones Administrativas. Los proveedores o servidores, administradores y usuarios que no cumplan o infrinjan lo establecido en el presente cap??tulo ser??n sancionados por el Ministerio de Tecnolog??as de la Informaci??n y las Comunicaciones sucesivamente de la siguiente manera:<br><br>
                            1. Multas hasta de cien (100) salarios m??nimos legales mensuales vigentes, que ser??n pagadas al Fondo Contra la Explotaci??n Sexual de Menores, de que trata el art??culo 24 de la Ley 679 de 2001.<br>
                            2. Suspensi??n de la correspondiente p??gina electr??nica.<br>
                            3. Cancelaci??n de la correspondiente p??gina electr??nica. Para la imposici??n de estas sanciones se aplicar?? el procedimiento establecido en el C??digo de Procedimiento Administrativo y de lo Contencioso Administrativo, con observancia del debido proceso y criterios de adecuaci??n, proporcionalidad y reincidencia.<br><br>
                            Par??grafo. El Ministerio de Tecnolog??as de la Informaci??n y las Comunicaciones adelantar?? las investigaciones administrativas pertinentes e impondr??, si fuere el caso, las sanciones previstas en este T??tulo, sin perjuicio de las investigaciones penales que adelanten las autoridades competentes y de las sanciones a que ello diere lugar.
                        </p>

                        <br>

                        <div style="border: 1px  solid #000; margin-top: 5px;text-align: center;">
                            <img src="data:image/png;base64,{{substr($contrato->firma_isp,1)}}" style="width: 20%; margin-top: 12.5px;">
                            <p style="color: #9e9b9b;text-align: center;" class="small">Aceptaci??n contrato mediante firma o cualquier otro medio v??lido</p>
                        </div>

                        <table width="100%" style="border: 1px solid #000; margin-top: 5px;">
                            <tbody>
                                <tr>
                                    <td style="margin: 0px; padding: 0px;">
                                        <p style="font-size:11px;margin: 2px 5px; padding: 0px; border: 0;">CC/CE <b>{{ $contrato->nit }}</b></p>
                                    </td>
                                    <td style="margin: 0px; padding: 0px;">
                                        <p style="font-size:11px;margin: 2px 5px; padding: 0px; border: 0;">FECHA <b>{{ date('d/m/Y', strtotime($contrato->fecha_isp)) }}</b></p>
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
                            <b>AL CONTRATO DE PRESTACI??N DE SERVICIOS DE TELECOMUNICACIONES RESPECTO AL USO DE DATOS PERSONALES</b></p><br>
                        <p style="text-align: justify;" class="small">
                            <u> {{ $contrato->nombre }} {{ $contrato->apellidos() }} </u>, identificado como aparece el pie de mi firma, por medio del presente escrito, manifiesto que he sido informado y autorizo <b>{{Auth::user()->empresa()->nombre}}</b> para que:<br><br>
                            Act??e como responsable del Tratamiento de datos personales de los cuales soy titular y que, conjunta o separadamente podr??n recolectar, usar y tratar mis datos personales conforme la Pol??tica de Tratamiento de Datos Personales de la Compa????a disponible en <b>{{Auth::user()->empresa()->web}}</b><br><br>
                            Es de car??cter facultativo responder preguntas que versen sobre Datos Sensibles o sobre menores de edad.<br><br>
                            Mis derechos como titular de los datos son los previstos en la Constituci??n y la ley 1581 de 2012, especialmente el derecho a conocer, actualizar, rectificar y suprimir mi informaci??n personal, as?? como el derecho a revocar el consentimiento otorgado para el tratamiento de datos personales.<br><br>
                            Los derechos pueden ser ejercidos a trav??s de los canales gratuitos dispuestos por la empresa y observando la Pol??tica de Tratamiento de Datos Personales de la misma.<br><br>
                            Para cualquier inquietud o informaci??n adicional relacionada con el tratamiento de datos personales, puedo contactarme al correo electr??nico {{Auth::user()->empresa()->email }}<br><br>
                            La empresa garantiza la confidencialidad, libertad, seguridad, veracidad, transparencia, acceso y circulaci??n restringida de mis datos y se reservan el derecho de modificar su Pol??tica de Tratamiento de Datos Personales en cualquier momento. Cualquier cambio ser?? informado y publicado oportunamente en la p??gina web.<br><br>
                            Teniendo en cuenta lo anterior, autorizo de manera voluntaria, previa, expl??cita, informada e inequ??voca a <b>{{Auth::user()->empresa()->nombre}}</b> para tratar mis datos personales de acuerdo con la Pol??tica de Tratamiento de Datos Personales de la y para los fines relacionados con su objeto social y en especial para fines legales, contractuales, comerciales descritos en la Pol??tica de Tratamiento de Datos Personales de Empresa. La informaci??n obtenida para el Tratamiento de mis datos personales la he suministrado de forma voluntaria y es ver??dica.
                        </p>

                        <br>

                        <div style="border: 1px  solid #000; margin-top: 5px;text-align: center;">
                            <img src="data:image/png;base64,{{substr($contrato->firma_isp,1)}}" style="width: 20%; margin-top: 12.5px;">
                            <p style="color: #9e9b9b;text-align: center;" class="small">Aceptaci??n contrato mediante firma o cualquier otro medio v??lido</p>
                        </div>

                        <table width="100%" style="border: 1px solid #000; margin-top: 5px;">
                            <tbody>
                                <tr>
                                    <td style="margin: 0px; padding: 0px;">
                                        <p style="font-size:11px;margin: 2px 5px; padding: 0px; border: 0;">CC/CE <b>{{ $contrato->nit }}</b></p>
                                    </td>
                                    <td style="margin: 0px; padding: 0px;">
                                        <p style="font-size:11px;margin: 2px 5px; padding: 0px; border: 0;">FECHA <b>{{ date('d/m/Y', strtotime($contrato->fecha_isp)) }}</b></p>
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
                            <b>FORMATO AUTORIZACI??N CONSULTA CENTRALES DE RIESGO</b></p><br>
                        <p style="text-align: justify;" class="small">
                            Yo, <u> {{ $contrato->nombre }} {{ $contrato->apellidos() }} </u> identificado como aparece al pie de mi firma, obrando en mi propio nombre y/o en representaci??n de: ____________________ AUTORIZO EXPRESA E IRREVOCABLEMENTE a <b>{{Auth::user()->empresa()->nombre}}</b>, libre y voluntariamente, para que reporte, consulte y divulgue a cualquier operador y/o fuente de informaci??n legalmente establecido, toda la informaci??n referente a mi comportamiento como cliente que se relacione con el nacimiento, ejecuci??n, modificaci??n, liquidaci??n y/o extinci??n de las obligaciones que se deriven del presente contrato, en cualquier tiempo, y que podr?? reflejarse en las bases de datos de DATACREDIDO, CIFIN, COVINOC o de cualquier otro operador y/o fuente de informaci??n legalmente establecido. La permanencia de la informaci??n estar?? sujeta a los principios, t??rminos y condiciones consagrados en la ley 1266 de 2008 y dem??s normas que lo modifiquen, aclaren o reglamenten. As?? mismo, autorizo, expresa e irrevocablemente a <b>{{Auth::user()->empresa()->nombre}}</b> para que consulte toda la informaci??n financiera, crediticia, comercial, de servicios y la proveniente de otros pa??ses, atinente a mis relaciones comerciales que tenga con el Sistema Financiero, comercial y de servicios, o de cualquier sector, tanto en Colombia como en el Exterior, en cualquier tiempo. PAR??GRAFO: La presente autorizaci??n se extiende para que <b>{{Auth::user()->empresa()->nombre}}</b> pueda compartir informaci??n con terceros p??blicos o privados, bien sea que ??stos ostenten la condici??n de fuentes de informaci??n, operadores de informaci??n o usuarios, con quienes EL CLIENTE tenga v??nculos jur??dicos de cualquier naturaleza, todo conforme a lo establecido en las normas legales vigentes dentro del marco del Sistema de Administraci??n de Riesgos de Lavado de Activos y Financiaci??n al Terrorismo SARLAFT de <b>{{Auth::user()->empresa()->nombre}}</b>
                        </p>

                        <br>

                        <div style="border: 1px  solid #000; margin-top: 5px;text-align: center;">
                            <img src="data:image/png;base64,{{substr($contrato->firma_isp,1)}}" style="width: 20%; margin-top: 12.5px;">
                            <p style="color: #9e9b9b;text-align: center;" class="small">Aceptaci??n contrato mediante firma o cualquier otro medio v??lido</p>
                        </div>

                        <table width="100%" style="border: 1px solid #000; margin-top: 5px;">
                            <tbody>
                                <tr>
                                    <td style="margin: 0px; padding: 0px;">
                                        <p style="font-size:11px;margin: 2px 5px; padding: 0px; border: 0;">CC/CE <b>{{ $contrato->nit }}</b></p>
                                    </td>
                                    <td style="margin: 0px; padding: 0px;">
                                        <p style="font-size:11px;margin: 2px 5px; padding: 0px; border: 0;">FECHA <b>{{ date('d/m/Y', strtotime($contrato->fecha_isp)) }}</b></p>
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
                            Usted manifiesta que, de acuerdo con lo establecido en el art??culo 622 del C??digo de Comercio, autoriza expresa e irrevocablemente a nosotros para llenar los espacios en blanco del pagar?? suscrito simult??neamente con la firma del presente contrato, otorgado en su favor, los cuales corresponden a la fecha de vencimiento y cuant??a del capital.<br><br>
                            El pagare podr?? ser llenado sin previo aviso, de acuerdo con las siguientes instrucciones:
                            <br><br>
                            - La cuant??a ser?? igual al monto de todas las sumas que, por cualquier concepto le est?? debiendo a <b>{{Auth::user()->empresa()->nombre}}</b> el d??a que sea llenado incluyendo en dicha cuant??a el valor de aquellas obligaciones que se declaren se plazo vencido como anteriormente se autoriz??.<br>
                            - La fecha de vencimiento ser?? el d??a que el titulo sea llenado o la del d??a siguiente.<br>
                            - En materia de intereses se observar??n para su c??lculo y liquidaci??n las siguientes pautas:<br><br>
                            1. Los intereses de mora ser??n los m??ximos legalmente autorizados por la ley.<br>
                            2. Si al momento de ser llenado este pagare se han causado intereses sobre la obligaci??n, estos se incluir??n dentro de la cuant??a total.<br>
                            3. En el caso de mi incumplimiento optare por declarar vencido el plazo pactado y hacer exigible la cancelaci??n de todas las obligaciones a mi cargo. declarar vencido el plazo pactado y hacer exigible la cancelaci??n de todas las obligaciones a mi cargo.<br><br>
                            Adicionalmente, con la suscripci??n del presente documento autorizamos a <b>{{Auth::user()->empresa()->nombre}}</b>, en forma libre, expresa e irrevocable, para que obtenga, reporte y actualice ene cualquier banco de datos, la informaci??n y referencias sobre nosotros como personas naturales y/o jur??dicas: nombre apellido y documento de identidad; nuestro comportamiento cr??dito comercial, h??bitos de pago manejo de cuentas bancarias y en general el cumplimiento de nuestras obligaciones pecuniarias. El pagare, si llenado, ser?? exigible inmediatamente, y prestar?? merito ejecutivo sin formalidad adicional alguna.<br><br>
                            PAGARE.<br>
                            Yo, <u> {{ $contrato->nombre }} {{ $contrato->apellidos() }} </u>, identificado con {{ $contrato->tip_iden('corta') }} numero {{ $contrato->nit }}  @if($contrato->dv != null || $contrato->dv === 0)-{{$contrato->dv}} @endif, actuando en nombre propio, por medio del presente escrito manifiesto, lo siguiente:<br><br>
                            PRIMERO: Que debo y pagar??, incondicional y solidariamente a la orden de la empresa <b>{{Auth::user()->empresa()->nombre}}</b> o quien represente sus derechos la cantidad de ____________________ ($____________________) pesos moneda legal Colombiana.<br><br>
                            SEGUNDO: Que el pago total de la mencionada obligaci??n se efectuar?? en un s??lo contado, el d??a _____ del mes de __________ del a??o _____________.<br><br>
                            TERCERO: Que en caso de mora pagar??, intereses de mora a la m??s alta tasa permitida por la Ley, desde el d??a siguiente a la fecha de exigibilidad del presente pagar??, y hasta cuando su pago total se efect??e.<br><br>
                            CUARTO: Expresamente declaro excusado el protesto del presente pagar?? y los requerimientos judiciales o extrajudiciales para la constituci??n en mora.<br><br>
                            QUINTO: En caso de que haya lugar al recaudo judicial o extrajudicial de la obligaci??n contenida en el presente t??tulo valor ser?? a mi cargo las costas judiciales y/o los honorarios que se causen por tal raz??n. En constancia de lo anterior, se suscribe en la ciudad de __________________, a los ____ d??as del mes de ______ del a??o _______.
                        </p>
                    </td>
                    <td style="vertical-align:top;" width="50%">
                        <div style="border: 1px  solid #000; margin-top: 0px;text-align: center;">
                            <img src="data:image/png;base64,{{substr($contrato->firma_isp,1)}}" style="width: 20%; margin-top: 12.5px;">
                            <p style="color: #9e9b9b;text-align: center;" class="small">Aceptaci??n contrato mediante firma o cualquier otro medio v??lido</p>
                        </div>

                        <table width="100%" style="border: 1px solid #000; margin-top: 5px;">
                            <tbody>
                                <tr>
                                    <td style="margin: 0px; padding: 0px;">
                                        <p style="font-size:11px;margin: 2px 5px; padding: 0px; border: 0;">CC/CE <b>{{ $contrato->nit }}</b></p>
                                    </td>
                                    <td style="margin: 0px; padding: 0px;">
                                        <p style="font-size:11px;margin: 2px 5px; padding: 0px; border: 0;">FECHA <b>{{ date('d/m/Y', strtotime($contrato->fecha_isp)) }}</b></p>
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
