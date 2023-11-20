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
                    <table role="presentation" style="width:602px;border-collapse:collapse;border:1px solid #cccccc;border-spacing:0;text-align:left;">
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
                                                Reporte Monitor Blacklist
                                            </h1>
                                            <hr>
                                            <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;text-align: justify;">
                                                Hola <b>{{ $datos[0]['empresa'] }}</b>,
                                            </p>
                                            <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;text-align: justify;">
                                                Recientemente hemos verificado las IP registradas en el Monitor Blacklist, y se han detectado cambios, a continuación te mostramos los detalles:
                                            </p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;">
                                                <tr>
                                                    <td style="padding:0 0 20px 0;color:#153643;text-align: center;">
                                                        <b>NOMBRE</b>
                                                    </td>
                                                    <td style="padding:0 0 20px 0;color:#153643;text-align: center;">
                                                        <b>IP</b>
                                                    </td>
                                                    <td style="padding:0 0 20px 0;color:#153643;text-align: center;">
                                                        <b>LISTADA EN</b>
                                                    </td>
                                                    <td style="padding:0 0 20px 0;color:#153643;text-align: center;">
                                                        <b>ESTADO</b>
                                                    </td>
                                                </tr>
                                                @php $i = 0; @endphp
                                                @foreach($datos as $item)
                                                <tr>
                                                    <td style="padding:0 0 20px 0;color:#153643;text-align: center;">
                                                        {{ $datos[$i]['nombre'] }}
                                                    </td>
                                                    <td style="padding:0 0 20px 0;color:#153643;text-align: center;">
                                                        {{ $datos[$i]['ip'] }}
                                                    </td>
                                                    <td style="padding:0 0 20px 0;color:#153643;text-align: center;">
                                                        {{ $datos[$i]['blacklisted_count'] }} sitios
                                                    </td>
                                                    <td style="padding:0 0 20px 0;color:red;text-align: center;">
                                                        Lista Negra
                                                    </td>
                                                </tr>
                                                @php $i++; @endphp
                                                @endforeach
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding:0 0 20px 0;color:#153643;">
                                            <p style="margin:12px 0 0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;text-align: justify;">
                                                Lo invitamos a ingresar a "Integra Colombia - Software Administrativo de ISP" y revisar los reportes para tomar las medidas necesarias.
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                                <p style="margin:0px 0;font-size:10px;line-height:24px;font-family:Arial,sans-serif;text-align: center;">
                                    ESTE CORREO ELECTRÓNICO ES GENERADO AUTOMATICAMENTE. NO LO RESPONDA.
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:30px;background:{{ $datos[0]['color'] }};">
                                <table role="presentation" style="width:100%;border-collapse:collapse;border:0;border-spacing:0;font-size:9px;font-family:Arial,sans-serif;">
                                    <tr>
                                        <td style="padding:0;width:100%;" align="center">
                                            <p style="margin:0;font-size:14px;line-height:16px;font-family:Arial,sans-serif;color:#ffffff;">
                                                Copyright © {{ $datos[0]['empresa'] }} 2022<br>Todos los derechos reservados<br><b>Integra Colombia - Software Administrativo de ISP</b>
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