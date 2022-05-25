<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <meta name="x-apple-disable-message-reformatting">
        <title></title>
        <style>
            table, td, div, h1, p {font-family: Arial, sans-serif;}
        </style>
    </head>
    <body style="margin:0;padding:0;">
        <table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;background:#ffffff;">
            <tr>
                <td align="center" style="padding:0;">
                    <table role="presentation" style="width:700px;border-collapse:collapse;border:1px solid #cccccc;border-spacing:0;text-align:left;">
                        <tr>
                            <td align="center" style="padding:0;background:#eeeeee;">
                                <img src="{{config('app.url').'/images/Empresas/Empresa1/logo.png'}}" alt="" width="300" style="height:auto;display:block;" />
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:36px 30px 20px 30px;">
                                <table role="presentation" style="width:100%;border-collapse:collapse;border:0px;border-spacing:0;">
                                    <tr>
                                        <td style="padding:0 0 20px 0;color:#153643;">
                                            <h1 style="font-size:24px;margin:0 0 20px 0;font-family:Arial,sans-serif;">
                                                Reporte de Radicado
                                            </h1>
                                            <hr>
                                            <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;text-align: justify;">
                                                Hola <b>{{ $datos->nombre }}</b>,
                                            </p>
                                            <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;text-align: justify;">
                                                El día {{date('d-m-Y', strtotime($datos->fecha))}} hemos recibido la solicitud de radicado, el cual ha sido registrado en nuestro sistema bajo el código N° {{ $datos->codigo }}, y se han detectado cambios, a continuación te mostramos los detalles:
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
                                                <tr>
                                                    <td style="padding:0 0 20px 0;color:#153643;text-align: center;">
                                                        <b>RADICADO</b>
                                                    </td>
                                                    <td style="padding:0 0 20px 0;color:#153643;text-align: center;">
                                                        N° {{ $datos->codigo }}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:0 0 20px 0;color:#153643;text-align: center;">
                                                        <b>FECHA</b>
                                                    </td>
                                                    <td style="padding:0 0 20px 0;color:#153643;text-align: center;">
                                                        {{date('d-m-Y', strtotime($datos->fecha))}}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:0 0 20px 0;color:#153643;text-align: center;">
                                                        <b>ESTADO</b>
                                                    </td>
                                                    <td style="padding:0 0 20px 0;color:#153643;text-align: center;">
                                                        <span class="text-{{ $datos->estatus('true') }}">{{ $datos->estatus() }}</span>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:0 0 20px 0;color:#153643;text-align: center;">
                                                        <b>PRIORIDAD</b>
                                                    </td>
                                                    <td style="padding:0 0 20px 0;color:#153643;text-align: center;">
                                                        {{ $datos->prioridad() }}
                                                    </td>
                                                </tr>
                                                @if($datos->servicio)
                                                <tr>
                                                    <td style="padding:0 0 20px 0;color:#153643;text-align: center;">
                                                        <b>TIPO DE SERVICIO</b>
                                                    </td>
                                                    <td style="padding:0 0 20px 0;color:#153643;text-align: center;">
                                                        {{ $datos->servicio()->nombre }}
                                                    </td>
                                                </tr>
                                                @endif
                                                <tr>
                                                    <td style="padding:0 0 20px 0;color:#153643;text-align: center;">
                                                        <b>REPORTE CLIENTE</b>
                                                    </td>
                                                    <td style="padding:0 0 20px 0;color:#153643;text-align: justify;">
                                                        {{ $datos->desconocido }}
                                                    </td>
                                                </tr>
                                                @if($datos->reporte)
                                                <tr>
                                                    <td style="padding:0 0 20px 0;color:#153643;text-align: center;">
                                                        <b>REPORTE TÉCNICO</b>
                                                    </td>
                                                    <td style="padding:0 0 20px 0;color:#153643;text-align: justify;">
                                                        {{ $datos->reporte }}
                                                    </td>
                                                </tr>
                                                @endif
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                                <p style="margin:0px 0;font-size:10px;line-height:24px;font-family:Arial,sans-serif;text-align: center;">
                                    ESTE CORREO ELECTRÓNICO ES GENERADO AUTOMATICAMENTE. NO LO RESPONDA.
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:30px;background:{{ Auth::user()->empresa()->color }};">
                                <table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;font-size:9px;font-family:Arial,sans-serif;">
                                    <tr>
                                        <td style="padding:0;width:100%;" align="center">
                                            <p style="margin:0;font-size:14px;line-height:16px;font-family:Arial,sans-serif;color:#ffffff;">
                                                Copyright © {{ Auth::user()->empresa()->nombre }} 2022<br>Todos los derechos reservados<br><b>Network Soft - Software Administrativo de ISP</b>
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
