<div style="margin:0;padding:0;min-width:100%;font-family:Gotham,'Helvetica Neue',Helvetica,Arial,sans-serif;background-color:#08344a;">
    <center style="width:100%;table-layout:fixed">
    <div style="max-width:600px">
    
    <table border="0" cellpadding="0" cellspacing="0" align="center" style="border-spacing:0;font-family:sans-serif;color:#666;background:#fff;margin:0 auto;width:100%;max-width:600px;">
        <tbody>
        <tr>
        <td style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;text-align:center;">
        <img src="{{asset('images/999.png')}}" alt="" style="border-width:0;width:auto;height:auto;background-color:#08344a;" class="CToWUd a6T" tabindex="0"><div class="a6S" dir="ltr" style="opacity: 0.01; left: 755px; top: 83px;"><div id=":1y1" class="T-I J-J5-Ji aQv T-I-ax7 L3 a5q" role="button" tabindex="0" aria-label="Descargar el archivo adjunto " data-tooltip-class="a1V" data-tooltip="Descargar"><div class="aSK J-J5-Ji aYr"></div></div></div>
        </td></tr>
        <tr>
        <td><table width="530" border="0" align="center" cellpadding="0" cellspacing="0">
        <tbody>
        <tr>
        <td colspan="2" align="left" style="padding:20px 60px 40px;font-family:sans-serif;background-color:rgb(255,255,255);font-size:14px;line-height:20px">
    
        <table width="480" border="0" align="center" cellpadding="0" cellspacing="0" style="color:rgb(102,102,102);font-family:Roboto,RobotoDraft,Helvetica,Arial,sans-serif;font-size:small">
            <tbody>
            <tr>
            <td colspan="2" align="left" style="font-family:sans-serif;margin:0px;padding:20px 40px;line-height:20px;color:rgb(89,89,89)">
            <h2 style="font-size:18px;font-weight:normal;color:rgb(0,83,94)">Hola {{$empresa->nombre}}</h2>
            <p style="font-size:14px"><strong style="color:green">!Felicidades!</strong> estás listo para facturar electronicamente con gestor de partes <br><br>
            Resumimos algunos datos que la DIAN comparte con nosotros:</p>

            <table style="font-size:0.95em;border-spacing:0px;float:left;min-width:100%;margin-top:3%;margin-bottom:5%;">
                <tbody>
                <tr>
                    <td style="background-color:#08344ac2;color:#f8fbfb;padding:1.3% 3%;border-radius:3px 0px 0px 0px;width:49%">NOMBRE</td>
                    <td style="background-color:#08344ac2;width:25%;color:#f8fbfb;border-radius:0px 3px 0px 0px">DATO</td>
                </tr>
        
                <tr style="background-color:#ededed">
                    <td style="border-right:1px solid #ffffff;padding:1.5% 3%;color:#979797;border-bottom:1px solid #ffffff">
                        Número de resolución
                    </td>
                    <td style="color:#08344ac2;padding:1.5% 3%;border-bottom:1px solid #ffffff;font-weight:bold">
                        {{$rango_numeracion[0]['resolutionNumber']}}
                    </td>
                </tr>

                <tr style="background-color:#ededed">
                    <td style="border-right:1px solid #ffffff;padding:1.5% 3%;color:#979797;border-bottom:1px solid #ffffff">
                        Prefijo
                    </td>
                    <td style="color:#08344ac2;padding:1.5% 3%;border-bottom:1px solid #ffffff;font-weight:bold">
                        {{$rango_numeracion[0]['prefix']}}
                    </td>
                </tr>

                <tr style="background-color:#ededed">
                    <td style="border-right:1px solid #ffffff;padding:1.5% 3%;color:#979797;border-bottom:1px solid #ffffff">
                        Numeración Desde
                    </td>
                    <td style="color:#08344ac2;padding:1.5% 3%;border-bottom:1px solid #ffffff;font-weight:bold">
                        {{$rango_numeracion[0]['fromNumber']}}
                    </td>
                </tr>

                <tr style="background-color:#ededed">
                    <td style="border-right:1px solid #ffffff;padding:1.5% 3%;color:#979797;border-bottom:1px solid #ffffff">
                        Numeración Hasta
                    </td>
                    <td style="color:#08344ac2;padding:1.5% 3%;border-bottom:1px solid #ffffff;font-weight:bold">
                        {{$rango_numeracion[0]['toNumber']}}
                    </td>
                </tr>

                <tr style="background-color:#ededed">
                    <td style="border-right:1px solid #ffffff;padding:1.5% 3%;color:#979797;border-bottom:1px solid #ffffff">
                        Válido desde
                    </td>
                    <td style="color:#08344ac2;padding:1.5% 3%;border-bottom:1px solid #ffffff;font-weight:bold">
                        {{$rango_numeracion[0]['validDateTimeFrom']}}
                    </td>
                </tr>

                <tr style="background-color:#ededed">
                    <td style="border-right:1px solid #ffffff;padding:1.5% 3%;color:#979797;border-bottom:1px solid #ffffff">
                        Válido Hasta
                    </td>
                    <td style="color:#08344ac2;padding:1.5% 3%;border-bottom:1px solid #ffffff;font-weight:bold">
                        {{$rango_numeracion[0]['validDateTimeTo']}}
                    </td>
                </tr>
            
                </tbody>
            </table>

            <br><br>

            <p>Te recordamos como fuiste activado como facturador electrónico frente a la DIAN: </p>
    
            <table style="font-size:0.95em;border-spacing:0px;float:left;min-width:100%;margin-top:3%;margin-bottom:5%;">
                <tbody>
                <tr>
                    <td style="background-color:#08344ac2;color:#f8fbfb;padding:1.3% 3%;border-radius:3px 0px 0px 0px;width:49%">TIPO</td>
                    <td style="background-color:#08344ac2;width:25%;color:#f8fbfb;border-radius:0px 3px 0px 0px">ENVIADAS</td>
                    <td style="background-color:#08344ac2;width:100%;color:#f8fbfb;border-radius:0px 3px 0px 0px">ACPETADAS</td>
                    <td style="background-color:#08344ac2;width:100%;color:#f8fbfb;border-radius:0px 3px 0px 0px">RECHAZADAS</td>
                </tr>
        
                <tr style="background-color:#ededed">
                    <td style="border-right:1px solid #ffffff;padding:1.5% 3%;color:#979797;border-bottom:1px solid #ffffff">Facturas de venta</td>
                    <td style="color:#08344ac2;padding:1.5% 3%;border-bottom:1px solid #ffffff;font-weight:bold">
                        {{json_decode($empresa->json_test,true)['validos'] + json_decode($empresa->json_test,true)['fallidos']}}
                    </td>
                    <td style="color:#08344ac2;padding:1.5% 3%;border-bottom:1px solid #ffffff;font-weight:bold">
                        {{json_decode($empresa->json_test,true)['validos']}}
                    </td>
                    <td style="color:#08344ac2;padding:1.5% 3%;border-bottom:1px solid #ffffff;font-weight:bold">
                        {{json_decode($empresa->json_test,true)['fallidos']}}
                    </td>
                </tr>
        
                <tr style="background-color:#ededed">
                    <td style="border-right:1px solid #ffffff;padding:1.5% 3%;color:#979797;border-bottom:1px solid #ffffff">Notas Crédito</td>
                    <td style="color:#08344ac2;padding:1.5% 3%;border-bottom:1px solid #ffffff;font-weight:bold">
                        {{json_decode($empresa->json_test_creditnote,true)['validos'] + json_decode($empresa->json_test_creditnote,true)['fallidos']}}
                    </td>
                    <td style="color:#08344ac2;padding:1.5% 3%;border-bottom:1px solid #ffffff;font-weight:bold">
                        {{json_decode($empresa->json_test_creditnote,true)['validos']}}
                    </td>
                    <td style="color:#08344ac2;padding:1.5% 3%;border-bottom:1px solid #ffffff;font-weight:bold">
                        {{json_decode($empresa->json_test_creditnote,true)['fallidos']}}
                    </td>
                </tr>
        
                <tr style="background-color:#ededed">
                    <td style="border-right:1px solid #ffffff;padding:1.5% 3%;color:#979797;border-bottom:1px solid #ffffff">Notas Débito</td>
                    <td style="color:#08344ac2;padding:1.5% 3%;border-bottom:1px solid #ffffff;font-weight:bold">
                        {{json_decode($empresa->json_test_debitnote,true)['validos'] + json_decode($empresa->json_test_debitnote,true)['fallidos']}}
                    </td>
                    <td style="color:#08344ac2;padding:1.5% 3%;border-bottom:1px solid #ffffff;font-weight:bold">
                        {{json_decode($empresa->json_test_debitnote,true)['validos']}}
                    </td>
                    <td style="color:#08344ac2;padding:1.5% 3%;border-bottom:1px solid #ffffff;font-weight:bold">
                        {{json_decode($empresa->json_test_debitnote,true)['fallidos']}}
                    </td>
                </tr>
            
                </tbody>
            </table>
    
        {{-- <br>Este nuevo plan está disponible por tan solo&nbsp;<b>$20.000 pesos&nbsp;</b>mensuales y te permitirá generar un máximo de&nbsp;<b>10 facturas de venta</b>, incluyendo las que generes como electrónicas, y registrar hasta $10.000.000 de pesos mensuales en ingresos.</p> --}}
        </td>
        </tr>
     
        <tr>
        <td colspan="2" align="center" style="font-family:sans-serif;margin:0px;padding:20px 40px 60px;border-top:1px solid rgb(238,238,238);border-bottom:1px solid rgb(238,238,238)">
        <h2 style="font-size:20px;font-weight:normal;color:rgb(0,83,94)">Centro de&nbsp;<strong>Ayuda</strong></h2>
        <p style="font-size:14px;line-height:20px;color:rgb(0,83,94)">Consulta&nbsp;<a href="" style="color:rgb(0,83,94);text-decoration-line:none" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://ayudas.alegra.com/es/&amp;source=gmail&amp;ust=1586276103200000&amp;usg=AFQjCNEeFpsbeWNf_5JiPNwQ5g6UXrPmIg">aquí</a>&nbsp;la documentación y aprende paso a paso a usar todas las funcionalidades de la aplicación.
        <br><strong>Si tienes dudas puedes escribirnos al siguiente número:</strong>&nbsp;
        <a href="//api.whatsapp.com/send?phone=573206177170&amp;text=Hola%20tengo%20una%20duda%20" style="color:rgb(0,83,94);text-decoration-line:none" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://www.alegra.com/123&amp;source=gmail&amp;ust=1586276103200000&amp;usg=AFQjCNGC7wGNR8tcmL4scgaJphBVluLqGQ"><span class="il">(+57) </span>320 617 71 70</a></p>
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
    <tr>
    <td>
    <center style="width:100%;table-layout:fixed">
            <table width="600px" border="0" cellpadding="0" cellspacing="0">
                <tbody>
                    {{-- <tr>
                        <td style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0">
                    <img src="https://ci3.googleusercontent.com/proxy/DOWN_Tyi_xcXjzHRT2sgRqFQdT_hKqwfhybNzllfWPBUPqbkjtI2fHwj-bNlo9vukpZ410ut2n5-Ro83PmUVE2Tabaj-NxBp95T5qIh4VrpJEtg=s0-d-e1-ft#https://cdn2.alegra.com/email/images/ui/2019/footer-colombia.png" alt="" style="border-width:0;width:100%;max-width:600px;height:auto;vertical-align:bottom;display:block" class="CToWUd a6T" tabindex="0"><div class="a6S" dir="ltr" style="opacity: 0.01; left: 755px; top: 1630px;"><div id=":1y2" class="T-I J-J5-Ji aQv T-I-ax7 L3 a5q" role="button" tabindex="0" aria-label="Descargar el archivo adjunto " data-tooltip-class="a1V" data-tooltip="Descargar"><div class="aSK J-J5-Ji aYr"></div></div></div>
                        </td>
                    </tr> --}}
                    <tr>
                        <td style="padding-top:0;padding-bottom:0;padding-right:0;padding-left:0;text-align:center;">
                            <img src="{{asset('images/dian-cadena.png')}}" alt="" style="border-width:0;width:55%;max-width:600px;height:auto;vertical-align:bottom" class="CToWUd a6T" tabindex="0">
                        </td>
                    </tr>
                    <tr>
                    <td style="padding:0;background-color:#08344a;text-align:center">
                    {{-- <p style="font-size:11px;margin:0;line-height:150%;text-align:center;padding:15px 20px;padding-bottom:0px;color:#fffce8">Descarga nuestra app:</p>
                    <p style="font-size:11px;margin:0;line-height:150%;text-align:center;padding:15px 20px;padding-top:0px;color:#fffce8">
                    <a href="https://play.google.com/store/apps/details?id=co.alegra.app" style="color:#00b195;text-decoration:underline" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://play.google.com/store/apps/details?id%3Dco.alegra.app&amp;source=gmail&amp;ust=1586276103200000&amp;usg=AFQjCNFPLhgdh-sQkf2trLhC-al5BVrLeQ"><img style="margin-right:5px;border:0;margin:5px;padding:5px" src="https://ci6.googleusercontent.com/proxy/XlPtqvVd92OibrPFQDmg_IO3Yfmed9wSUM51eHoVFviIsu9S-V2x2KfydCxjSjrPEfGFio01KxttNdc7xeY1-q7yluqeo_QT0aZvYyfIyg-mSyxMt3ac7DqAdui6DmTJYQ=s0-d-e1-ft#https://cdn1.alegra.com/images/emails/newfeatures-common/button_app-google.png" width="125" height="37" alt="" class="CToWUd"></a>
                    <a href="https://itunes.apple.com/WebObjects/MZStore.woa/wa/viewSoftware?id=1084859936&amp;mt=8" style="color:#00b195;text-decoration:underline" target="_blank" data-saferedirecturl="https://www.google.com/url?q=https://itunes.apple.com/WebObjects/MZStore.woa/wa/viewSoftware?id%3D1084859936%26mt%3D8&amp;source=gmail&amp;ust=1586276103200000&amp;usg=AFQjCNFjeIkXljQAFIrpzTQj4Tsf9QrErw"><img style="margin-left:5px;border:0;margin:5px;padding:5px" src="https://ci6.googleusercontent.com/proxy/a9k3yI7jg7Gjz5yT6VYUpMg3mzFMcbyz1E1mmRKB8h8aKuVRTZFREl_BgGbBFy4RWVLN-nMmSSArQArLKpGQo-vk5T5sUgw6P5pez4WJhCShgS_swergVpNpyomxDQ=s0-d-e1-ft#https://cdn1.alegra.com/images/emails/newfeatures-common/button_app-ios.png" width="125" height="37" alt="" class="CToWUd"></a>
                    </p> --}}
                    <hr style="border:none;border-bottom:1px dotted #fff;margin:0;margin-bottom:20px">
                    <p style="font-size:11px;margin:0;line-height:150%;text-align:center;padding:15px 20px;padding-bottom:0px;color:#fffce8">Síguenos:</p>
                    <p style="font-size:11px;margin:0;line-height:150%;text-align:center;padding:15px 20px;padding-top:0px;color:#fffce8">
                    <a href="https://www.facebook.com/alegra.web/" style="color:#00b195;text-decoration:underline" target="_blank" data-saferedirecturl="#"><img src="https://ci3.googleusercontent.com/proxy/avXnYOCUKS9upUz0hjprFAuEMcb5dEoJaCtvRD8RDGDMrceUrCNYVCpeCEPcObu7zzM2N4oC1M002Gi3f4AutncxrLYAGQCGK413dui74fX8mPR9ASWOCb7c3rGSZDE=s0-d-e1-ft#https://cdn1.alegra.com/images/emails/newfeatures-common/social-facebook.png" width="35" height="35" alt="" style="border:0;margin:5px;padding:5px" class="CToWUd"></a>
                    <a href="https://twitter.com/AlegraWeb" style="color:#00b195;text-decoration:underline" target="_blank" data-saferedirecturl="#"><img src="https://ci5.googleusercontent.com/proxy/Tpt7FoLG5olpgpCNu2OJ2gCrFX-El2xG-5CzSWKV6kzZXfaoawDcMUpAJDRd0-n2mEsbQGDsGD7uE-33cnnWq86XMD8_lHBT2tMRKkXTPBUeTAUvaKy8snzrQNo1GA=s0-d-e1-ft#https://cdn1.alegra.com/images/emails/newfeatures-common/social-twitter.png" width="35" height="35" alt="" style="border:0;margin:5px;padding:5px" class="CToWUd"></a>
                    <a href="https://www.instagram.com/alegraweb/" style="color:#00b195;text-decoration:underline" target="_blank" data-saferedirecturl="#"><img src="https://ci6.googleusercontent.com/proxy/6L1FBmU7lmTWf-H68rXe1B3nrHAcDqr9TeUkLbBav4BpzyMQP_tbMSyQsGGdmeI5R-uffy-PY5-iwvCY8wXzSaPTrtxlBxujFsaujZI0L7dtWN00wQfAJYN_YvoynvRX=s0-d-e1-ft#https://cdn1.alegra.com/images/emails/newfeatures-common/social-instagram.png" width="35" height="35" alt="" style="border:0;margin:5px;padding:5px" class="CToWUd"></a>
                    </p>
                    <hr style="border:none;border-bottom:1px dotted #fff;margin:0;margin-bottom:20px">
                    <p style="font-size:11px;margin:0;line-height:150%;text-align:center;padding:15px 20px;padding-bottom:0px;color:#fffce8">
                    Al crear la cuenta en <span class="il">Gestor de Partes</span>, aceptaste nuestros términos y condiciones.<br>
                    
                    © <span class="il">Gestor de Partes</span> - Todos los derechos reservados
                    </p>
                    </td>
                    </tr>
                    <tr>
                    <td style="padding:0;background-color:#08344a;text-align:center">
                    <p style="font-size:11px;margin:0;line-height:150%;text-align:justify;padding:15px 20px;color:#fffce8">&nbsp;</p>
                    </td>
                    </tr>
                </tbody>
            </table>
    </center>
    </td>
    </tr>
    </tbody></table>
    
    </div>
    </center><div class="yj6qo"></div><div class="adL">
    </div></div>