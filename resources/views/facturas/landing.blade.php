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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <link data-require="sweet-alert@*" data-semver="0.4.2" rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
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
                                @if($noEdit)
                                    <div class="card-body ">
                                        @if($mody)
                                            <h3>Su factura electrónica ha sido <u>{{$factura->statusdian == 0 ? "RECHAZADA" : "ACEPTADA"}}</u></h3>
                                        @else
                                            <h3>Ha <u>expirado</u> el tiempo de respuesta, su factura electronica ha sido <u>ACEPTADA</u></h3>
                                        @endif
                                        <hr>
                                        <br>
                                        <table class="table table-striped">
                                            <tr>
                                                <td><b>Proveedor:</b> {{$empresa->nombre}}</td>
                                                <td><b>Identificacion Proveedor:</b> {{$empresa->nit}} </td>
                                            </tr>
                                            <tr>
                                                <td><b>Cliente: </b>{{$factura->cliente()->nombre}} </td>
                                                <td><b>Identificación cliente: </b> {{$factura->cliente()->nit}}</td>
                                            </tr>
                                            <tr>
                                                <td><b>Documento electrónico: </b> {{$factura->codigo}} </td>
                                                <td><b>Fecha de facturación: </b> {{$factura->fecha}} </td>
                                            </tr>
                                            <tr>
                                                <td><b>Valor total: </b> {{App\Funcion::ParsearAPI($factura->totalAPI($empresa->id)->total, $empresa->id)}}</td>
                                                <td><b>Valor importe: </b> {{App\Funcion::ParsearAPI($factura->totalAPI($empresa->id)->total, $empresa->id)}}</td>
                                            </tr>
                                                <tr>
                                                    <td>Estado: </td>
                                                    <td>
                                                        <input type="text" readonly value="{{$factura->statusdian == 0 ? "RECHAZADA" : "ACEPTADA"}}">
                                                    </td>
                                                </tr>
                                        </table>
                                        <hr>
                                        <div class="form-group">
                                            <a href="{{route('imprimirFe', $factura->nonkey)}}" class="btn btn-lg btn-block text-white" style="background-color: #00aced">Descargar/Ver PDF</a>
                                            <a href="{{route('xmlFe', $factura->nonkey)}}" class="btn btn-lg btn-block text-white" style="background-color: #0c85d0">Descargar XML</a>
                                        </div>
                                        <div class="form-group">
                                            <hr>
                                            <button class="btn btn-lg btn-block text-white"
                                                    id="close" style="background-color: #08344a">Cerrar</button>
                                        </div>
                                    </div>
                            </div>
                        </div>
                </tr>
                <tr bgcolor="#ffffff">
                <tr>
                    <td style="padding:10px 30px 20px 30px;font-size:9pt">
                    </td>
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
            @else
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
                        <td><b>Cliente: </b>{{$factura->cliente()->nombre}} </td>
                        <td><b>Identificación cliente: </b> {{$factura->cliente()->nit}}</td>
                    </tr>
                    <tr>
                        <td><b>Documento electrónico: </b> {{$factura->codigo}} </td>
                        <td><b>Fecha de facturación: </b> {{$factura->fecha}} </td>
                    </tr>
                    <tr>
                        <td><b>Valor total: </b> {{App\Funcion::ParsearAPI($factura->totalAPI($empresa->id)->total, $empresa->id)}}</td>
                        <td><b>Valor importe: </b> {{App\Funcion::ParsearAPI($factura->totalAPI($empresa->id)->total, $empresa->id)}}</td>
                    </tr>
                    <form method="post" action="{{route('saveFe')}}" id="from1" name="from1">
                        @csrf
                        <input type="hidden" name="nonkey" value="{{$key}}">
                    <tr>
                        <td>Estado: </td>
                        <td>
                            <select id="statusdian" class="form-control" name="statusdian" >
                                <option disabled selected value="0">Seleccione...</option>
                                <option value="1">Documento electrónico aceptado</option>
                                <option value="0">Documento electrónico rechazado</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <hr>
                <div class="form-group">
                    <label for="exampleInputEmail1"><b>Observaciones</b></label>
                    <textarea class="form-control" id="observacionesdian" rows="3" name="observacionesdian" disabled></textarea>
                    <hr>
                    <a href="{{route('imprimirFe', $factura->nonkey)}}" class="btn btn-lg btn-block text-white" style="background-color: #00aced">Descargar/Ver PDF</a>
                    <a href="{{route('xmlFe', $factura->nonkey)}}" class="btn btn-lg btn-block text-white" style="background-color: #0c85d0">Descargar XML</a>
                    <hr>
                    <button class="btn btn-lg btn-block text-white" style="background-color: #08344a">Guardar</button>
                </div>
                </form>
            </div>
        </div>
    </div>
</tr>
    <tr bgcolor="#ffffff">
            <tr>
                <td style="padding:10px 30px 20px 30px;font-size:9pt">
                    <span>Dando cumplimiento al Decreto 2242 de 2015, si pasadas venticuatro (24) horas siguientes a la recepción de la factura, y aún no ha sido rechazada, el sistema la dará por aceptada.</span>
                </td>
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
@endif
<script>
    $(document).ready(function() {

        $('#statusdian').on("change", function(){

            var selectedOption = $('#statusdian :selected').val();
            $('#observacionesdian').prop('disabled', selectedOption == 0 ? false : true );
            $('#observacionesdian').prop('required', selectedOption == 0 ? true : false );
            if (selectedOption != 0){
                $('#observacionesdian').val("");
            }



        });

        $('#close').click(function(){
            window.close();
        });

    });
</script>
<script>
    document.querySelector('#from1').addEventListener('submit', function(e) {
        var form = this;

        e.preventDefault();

        swal({
            title: "Confirmación de guardado",
            text: "Una vez confirmado no podrá cambiar el estatus.",
            icon: "warning",
            buttons: [
                'Cancelar',
                'Guardar'
            ],
            dangerMode: true,
        }).then(function(isConfirm) {
            if (isConfirm) {
                swal({
                    title: 'Guardado',
                    icon: 'success'
                }).then(function() {
                    form.submit();
                });
            } else {
                swal("Cancelado", "", "error");
            }
        });
    });
</script>
</body>
</html>
