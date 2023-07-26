<!doctype html>
<html lang="en">
<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <link rel="stylesheet" href="{{asset('vendors/iconfonts/mdi/css/materialdesignicons.min.css')}}">
    <link rel="stylesheet" href="{{asset('vendors/css/vendor.bundle.base.css')}}">
    <link rel="stylesheet" href="{{asset('vendors/css/vendor.bundle.addons.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('vendors/DataTables/datatables.min.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('vendors/bootstrap-selectpicker/css/bootstrap-select.min.css')}}"/>

    <link href="{{asset('vendors/fontawesome/css/all.css')}}" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="{{asset('vendors/flag-icon-css/css/flag-icon.min.css')}}"/>
    <link rel="stylesheet" type="text/css" href="{{asset('vendors/sweetalert2/sweetalert2.min.css')}}"/>


    <link href="https://fonts.googleapis.com/css?family=Poppins" rel="stylesheet">
    <!-- endinject -->
    <!-- plugin css for this page -->
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link href="{{asset('vendors/bootstrap-datepicker/css/gijgo.min.css')}}" rel="stylesheet">
    <link href="{{asset('vendors/morris/morris.css')}}" rel="stylesheet">
    <link href="{{asset('vendors/profile-picture/profile-picture.css')}}" rel="stylesheet">
    <link href="{{asset('vendors/dropify/dropify.css')}}" rel="stylesheet">

    <link href="{{asset('vendors/dropzone/dropzone.css')}}" rel="stylesheet">
    <!-- Light Gallery Plugin Css -->
    <link href="{{asset('vendors/light-gallery/css/lightgallery.css')}}" rel="stylesheet">
    <link href="{{asset('vendors/autocomplete/jquery.auto-complete.css')}}" rel="stylesheet">
    <link rel="stylesheet" href="{{asset('css/style.css')}}">
    <link rel="stylesheet" href="{{asset('css/documentacion.css')}}">


    <!-- endinject -->
    <link rel="shortcut icon" href="{{asset('images/favicon.png')}}" />
    <title>Factura electronica</title>
</head>
<body>



</body>
</html>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>

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
                        <div class="container">
                            <div class="card text-center border-dark mb-3">
                                <div class="card-body ">
                                    <h5>Acuse de recibo exitoso, a continuación podrá aceptar o rechazar el documento electrónico</h5>
                                    <hr>
                                    <br>
                                    <table class="table table-striped">
                                        <tr>
                                            <td><b>Proveedor:</b> {{$empresa->nombre}}</td>
                                            <td><b>Identificacion Proveedor:</b> {{$empresa->nit}} </td>
                                        </tr>
                                        <tr>
                                            <td><b>Cliente: </b>{{$nota->cliente()->nombre}} </td>
                                            <td><b>Identificación cliente: </b> {{$nota->cliente()->nit}}</td>
                                        </tr>
                                        <tr>
                                            <td><b>Documento electrónico: </b> {{$nota->nro}} </td>
                                            <td><b>Fecha de facturación: </b> {{$nota->fecha}} </td>
                                        </tr>
                                        <tr>
                                            <td><b>Valor total: </b> {{App\Funcion::ParsearAPI($nota->totalAPI($empresa->id)->total, $empresa->id)}}</td>
                                            <td><b>Valor importe: </b> {{App\Funcion::ParsearAPI($nota->totalAPI($empresa->id)->total, $empresa->id)}}</td>
                                        </tr>
                                        <tr>
                                            <td>Estado: </td>
                                            <td>
                                                <select id="inputState" class="form-control">
                                                    <option selected>Seleccione...</option>
                                                    <option>Documento electrónico aceptado</option>
                                                    <option>Documento electrónico rechazado</option>
                                                </select>
                                            </td>
                                        </tr>
                                    </table>
                                    <hr>
                                </div>
                            </div>
                        </div>
                </tr>
                <tr bgcolor="#ffffff">
                    <td style="padding:20px 90px">
                        <table style="width:100%">
                            <tbody>
                            <tr>

                                <td style="width:200px;font-size:14px;font-family:Arial,Helvetica,sans-serif;color:#fff;text-align:center;padding:15px;background-color: #08344a;border-radius:5px;margin-left:auto;margin-right:auto;">
                                    <a style="color:#fff;text-decoration:none" href="#"> Guardar</a></td>

                            </tr>
                            </tbody>
                        </table>
                    </td>
                    <td></td>
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
    <tr>
        <td style="padding:10px 30px 20px 30px;font-size:9pt">
            <span>Dando cumplimiento al Decreto 2242 de 2015, si pasados tres (3) días hábiles siguientes a la recepción de la factura, y aún no ha sido rechazada, el sistema la dará por aceptada.</span>
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
                Nota Crédito elaborada y enviada a través de GestorU</div>
            <center>
                <div style="background-color: #08344a;width: 310px;height: 75px;margin-top: 30px;">
                    <img style="width:50%;" src="https://gestordepartes.net/images/999.png">
                </div>
            </center>
        </td>
    </tr>
    <tr>
        <td> .</td>
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
