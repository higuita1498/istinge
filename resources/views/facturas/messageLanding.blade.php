<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <link rel="shortcut icon" href="{{asset('images/favicon.png')}}" />
    <title>Factura electronica</title>
</head>
<body>



</body>
</html>


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
                                    {{$empresa}}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <div class="container">
                            <div class="card text-center border-dark mb-3">
                                <div class="card-body ">
                                    <h4>Información guardada correctamente</h4>
                                    <h5>Documento electronico <u>{{$status}}</u></h5>
                                    <hr>
                                    <br>
                                        <button class="btn btn-lg btn-block text-white"
                                                onclick="window.close();" style="background-color: #08344a">Cerrar</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                </tr>
                <tr bgcolor="#ffffff">
                <tr>
                </tr>
                <tr bgcolor="#f0f0f0">
                    <td height="40">
                    </td>
                </tr>
                <tr>
                    <td style="padding:0px 30px 0px 30px">
                        <br>
                        <div style="width:600px;margin:0 auto;padding:0px;text-align:center;font-family:Arial,Helvetica,sans-serif;color:#999999;font-size:12px;">
                            Factura elaborada y enviada a través de GestorU</div>
                        <center>
                            <div style="background-color: #08344a;width: 310px;height: 75px;margin-top: 30px;">
                                <img style="width:50%;" src="https://gestordepartes.net/images/999.png">
                            </div>
                        </center>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td><br></td>
    </tr>
    </tbody>
</table>
</td>
</tr>
</tbody>
</table>
<script>
</script>
</body>
</html>
