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
                                                CONTRATO DIGITAL DE SERVICIOS
                                            </h1>
                                            <hr>
                                            <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;text-align: justify;">
                                                Hola <b>{{$contrato->nombre}} {{$contrato->apellidos()}}</b>,
                                            </p>
                                            <p style="margin:0 0 12px 0;font-size:16px;line-height:24px;font-family:Arial,sans-serif;text-align: justify;">
                                                {{Auth::user()->empresa()->nombre}} le hace llegar su contrato digital de servicios, el cual se encuentra adjunto en este correo electrónico.
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