<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=7" />
    <meta http-equiv="X-UA-Compatible" content="IE=8" />
    <meta http-equiv="X-UA-Compatible" content="IE=9" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=no;">
    <meta name="viewport" content="width=600,initial-scale = 2.3,user-scalable=no">
    <meta name="viewport" content="width=device-width">
    <title>Nómina Electrónica</title>
</head>

<body>
    <table align="center" bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px;width:100%">
        <tbody>
            <tr>
                <td align="center" valign="top">
                    <table align="center" border="0" cellpadding="0" cellspacing="0" width="90%">
                        <tbody>
                            <tr>
                                <td height="30" style="font-size:1px;line-height:1px">&nbsp;</td>
                            </tr>
                            <tr style="background-color: #185091; display: flex;">
                                <td align="left" valign="top" style="font-family:Arial,sans-serif;font-size:18px;line-height:20px;color:#ffff;font-weight:bold;text-align:left;padding-top:0;padding-bottom:0;padding-right:0px;padding-left:25px; width: 40%; object-fit:cover; margin-right: auto; margin-left: auto; margin-top: auto;margin-bottom: auto;">
                                    <span>{{ucfirst($empresa->nombre)}}</span>
                                </td>
                                <td align="right" valign="top" style="font-family:Arial,sans-serif;font-size:15px;line-height:20px;color:#ffff;font-weight:bold;text-align:right;padding-top:15px;padding-bottom:15px;padding-right:15px;padding-left:25px; width: 60%;">
                                    Detalles de tu nómina electrónica
                                </td>
                            </tr>

                            <tr>
                                <td height="10" style="font-size:1px;line-height:1px">
                                    &nbsp;
                                </td>
                            </tr>
                            <tr>
                                <td align="left" valign="top" style="font-family:Arial,sans-serif;font-size:34px;line-height:38px;color:#000000;font-weight:bold;text-align:left">
                                </td>
                            </tr>
                            <tr>
                                <td height="25" style="font-size:1px;line-height:1px">
                                    &nbsp;
                                </td>
                            </tr>
                            <tr>
                                <td align="left" valign="top" style="font-family:Arial,sans-serif;font-size:14px;line-height:16px;color:#000000;font-weight:700;text-align:left;padding-top:0;padding-bottom:0;padding-right:15px;padding-left:25px;">
                                    Hola {{ $persona->nombre ?? '' }} {{ $persona->apellido ?? '' }},
                                </td>
                            </tr>
                            <tr>
                                <td height="10" style="font-size:1px;line-height:1px">
                                    &nbsp;
                                </td>
                            </tr>

                            <tr>
                                <td align="left" valign="top" style="font-family:Arial,sans-serif;font-size:13px;line-height:16px;color:#000000;font-weight:normal;text-align:left;padding-top:0;padding-bottom:0;padding-right:15px;padding-left:25px;">
                                    <br>
                                    <strong>Gracias por elegir {{ config('app.name') }} para la gestión de tu nómina</strong> Queremos informarte que tu nómina electrónica <strong></strong> ha sido generada satisfactoriamente:
                                </td>
                            </tr>

                            <tr>
                                <td height="20" style="font-size:1px;line-height:1px">&nbsp;</td>
                            </tr>

                            <tr>
                                <td height="20" style="font-size:1px;line-height:1px">&nbsp;</td>
                            </tr>
                            <tr>
                                <td align="center" valign="top">
                                    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tbody>
                                            <tr>
                                                <td width="15%" style="font-size:1px">
                                                    <img src="{{$message->embed(public_path() . '/images/nomina.jpg')}}" width="100%" style="width:100%;" class="CToWUd">
                                                </td>

                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>

                            <tr>
                                <td align="center" valign="top">
                                    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tbody>


                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td height="20" style="font-size:1px;line-height:1px">&nbsp;
                                    <br>
                                </td>
                            </tr>
                            <tr>
                                <td align="center" valign="top">
                                    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tbody>


                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td align="center" valign="top">
                                    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tbody>

                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td align="center" valign="top">
                                    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%">
                                        <tbody>

                                            <tr>
                                                <td height="25" style="font-size:1px">&nbsp;
                                                    <br>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="left" valign="top" style="font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:18px;color:#000000;font-weight:700;padding-top:0;padding-bottom:0;padding-right:15px;padding-left:25px;">
                                                    <span>
                                                        Puedes ver el detalle de tu nómina electrónica, descargando el archivo adjunto en este correo
                                                    </span>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td height="25" style="font-size:1px">&nbsp;
                                                    <br>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td height="100" style="background-color: #185091;" valign="middle" width="100%">
                                                    <table border="0" cellpadding="0" cellspacing="0" style="max-width:560px;" width="100%">
                                                        <tbody>
                                                            <tr>
                                                                <td align="left" valign="top" style="font-family:Arial,Helvetica,sans-serif;font-size:14px;line-height:18px;color:#ffff;font-weight:700;padding-top:0;padding-bottom:0;padding-right:15px;padding-left:25px; justify-content:center; text-align:center;">

                                                                    Todos los derechos reservados <a href="{{ config('app.url') }}" target="_blank" style="color: #fff; font-weight: bold; text-align: center;">@ {{ ucfirst(config('app.name')) }}</a>

                                                                    <p style="text-align: center; color:#c2c2c2 !important">{{ now()->isoFormat('LLLL') }}</p>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </td>
                                            </tr>


                                            <tr>
                                                <td height="25" style="font-size:1px;line-height:1px">&nbsp;
                                                    <br>
                                                </td>
                                            </tr>


                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                                <td height="30" style="font-size:1px;line-height:1px">
                                    &nbsp;
                                </td>
                            </tr>



                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</body>

</html>