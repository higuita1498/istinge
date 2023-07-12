<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <title>Factura electrónica</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
</head>
<body style="margin: 0; padding: 0;">
    <table width="640" cellspacing="0" cellpadding="0" border="0" align="center" style="width:640px;margin:0 auto;padding:15px;border: 1px solid #08344a;">
        <tbody>
            <tr>
                <td width="640" align="center" style="padding:20px 20px 10px">
                    <table width="640" cellspacing="0" cellpadding="0" border="0">
                        <tbody>
                            <tr>
                                <td width="640">

                                    <table width="640" cellspacing="0" cellpadding="0" border="0">
                                        <tbody>
                                            <tr>
                                                <td colspan="3" width="580" style="font-size:24px;font-family:Arial,Helvetica,sans-serif;color:#000;text-align:left;font-weight:bolder;border-bottom-width:2px;border-bottom-style:solid;border-bottom-color: #08344a;">
                                                    {{$empresa->nombre}}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <table width="641" cellspacing="0" cellpadding="0" border="0">
                                        <tbody>
                                            <tr>
                                                <td colspan="3">
                                                    <table width="641" cellspacing="0" cellpadding="0" border="0" bgcolor="#ffffff">
                                                        <tbody>
                                                            <tr bgcolor="#ffffff"><td></td></tr>
                                                            <tr bgcolor="#ffffff">
                                                                <td style="padding:20px 30px">
                                                                    <div style="text-align:left;line-height:22px;font-family:Arial,Helvetica,sans-serif;font-size:15px;font-weight:normal;margin-top:0px">
                                                                        Sr (a)  <strong>{{$cliente}}</strong>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                            <tr bgcolor="#ffffff">
                                                                <td style="padding:0px 30px">
                                                                    <div style="text-align:left;line-height:22px;font-family:Arial,Helvetica,sans-serif;font-size:15px;font-weight:normal;margin-top:0px"> {{$empresa->nombre}} le informa que se generó la siguiente factura</div>
                                                                    <br>
                                                                </td>
                                                            </tr>
                                                            <tr bgcolor="#ffffff">
                                                                <td style="padding:10px 30px">
                                                                    <table align="center" style="width:80%;height:64px;margin:0 auto;font-size:9pt;font-family:Arial,Helvetica,sans-serif;border:none;background-color:#f0f0f0;color:#000">
                                                                        <tbody><tr>
                                                                            <td style="padding-right:10px;width:50%" align="right">Número de factura</td>
                                                                            <td><strong>{{$factura->codigo}}</strong></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td style="padding-right:10px;width:50%" align="right">A nombre de</td>
                                                                            <td><strong>{{$cliente}}</strong></td>
                                                                        </tr>

                                                                        <tr>
                                                                            <td style="padding-right:10px;width:50%" align="right">Valor total</td>
                                                                            <td><strong>{{$empresa->moneda}} {{$total}}</strong></td>
                                                                        </tr>
                                                                    </tbody></table>
                                                                </td>

                                                            </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <tr bgcolor="#ffffff">
                                                    <td style="padding:0px 30px">
                                                        <div style="text-align:left;line-height:22px;font-family:Arial,Helvetica,sans-serif;font-size:15px;font-weight:normal;margin-top:0px"> </div>
                                                        <br>
                                                    </td>
                                                </tr>
                                                <tr bgcolor="#ffffff">
                                                    <td style="padding:0px 30px">
                                                        <div style="text-align:left;line-height:22px;font-family:Arial,Helvetica,sans-serif;font-size:15px;font-weight:normal;margin-top:0px"> </div>
                                                        <br>
                                                    </td>
                                                </tr>
                                                <tr bgcolor="#f0f0f0">
                                                    <td height="40">
                                                        <p style="font-family:Arial,Helvetica,sans-serif;font-size:9pt;color:#5a5a5a;text-align:center;background-color:#f0f0f0">Este correo electrónico es generado automaticamente. No lo responda.

                                                        </p>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td style="padding:0px 30px 0px 30px">
                                                        <br>
                                                        <div style="width:600px;margin:0 auto;padding:0px;text-align:center;font-family:Arial,Helvetica,sans-serif;color:#999999;font-size:12px;">
                                                        Factura elaborada y enviada a través de {{$empresa->nombre}}</div>
                                                        <center>
                                                            <div style="background-color: #fff;width: 50%;margin-top: 30px;">
                                                                <img style="width:50%;" src="{{asset('/images/Empresas/Empresa1/logo.png')}}">
                                                            </div>
                                                        </center>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
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
