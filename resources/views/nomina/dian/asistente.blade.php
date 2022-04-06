@extends('layouts.app')

@section('content')
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<style>
    h5 {
        font-size: .1rem;
    }

    #titulo {
        display: none;
    }

    body {
        background: #eee;
    }

    .process-box {
        background: #fff;
        padding: 10px;
        border-radius: 15px;
        position: relative;
        box-shadow: 2px 2px 7px 0 #00000057;
    }

    .process-left:after {
        content: "";
        border-top: 15px solid #ffffff;
        border-bottom: 15px solid #ffffff;
        border-left: 15px solid #ffffff;
        border-right: 15px solid #ffffff;
        display: inline-grid;
        position: absolute;
        right: -15px;
        top: 42%;
        transform: rotate(45deg);
        box-shadow: 3px -2px 3px 0px #00000036;
        z-index: 1;
    }

    .process-right:after {
        content: "";
        border-top: 15px solid #ffffff00;
        border-bottom: 15px solid #ffffff;
        border-left: 15px solid #ffffff;
        border-right: 15px solid #ffffff00;
        display: inline-grid;
        position: absolute;
        left: -15px;
        top: 42%;
        transform: rotate(45deg);
        box-shadow: -1px 1px 3px 0px #0000001a;
        z-index: 1;
    }

    .process-step {
        background: #1a59a1;
        text-align: center;
        width: 80%;
        margin: 0 auto;
        color: #fff;
        height: 100%;
        padding-top: 8px;
        position: relative;
        top: -26px;
        border-radius: 0px 0px 10px 10px;
        box-shadow: -6px 8px 0px 0px #00000014;
    }

    .process-point-right {
        background: #ffffff;
        width: 25px;
        height: 25px;
        border-radius: 50%;
        border: 8px solid #1a59a1;
        box-shadow: 0 0 0px 4px #5c5c5c;
        margin: auto 0;
        position: absolute;
        bottom: 40px;
        left: -63px;
    }

    .process-point-right:before {
        content: "";
        height: 144px;
        width: 11px;
        background: #5c5c5c;
        display: inline-grid;
        transform: rotate(36deg);
        position: relative;
        left: -50px;
        top: -0px;
    }

    .process-point-left {
        background: #ffffff;
        width: 25px;
        height: 25px;
        border-radius: 50%;
        border: 8px solid #1a59a1;
        box-shadow: 0 0 0px 4px #5c5c5c;
        margin: auto 0;
        position: absolute;
        bottom: 40px;
        right: -63px;
    }

    .process-point-left:before {
        content: "";
        height: 144px;
        width: 11px;
        background: #5c5c5c;
        display: inline-grid;
        transform: rotate(-38deg);
        position: relative;
        left: 50px;
        top: 0px;

    }

    .process-last:before {
        display: none;
    }

    .process-box p {
        z-index: 9;
    }

    .process-step p {
        font-size: 20px;
    }

    .process-step h2 {
        font-size: 39px;
    }

    .process-step:after {
        content: "";
        border-top: 8px solid #04889800;
        border-bottom: 8px solid #1a59a1;
        border-left: 8px solid #04889800;
        border-right: 8px solid #1a59a1;
        display: inline-grid;
        position: absolute;
        left: -16px;
        top: 0;
    }

    .process-step:before {
        content: "";
        border-top: 8px solid #ff000000;
        border-bottom: 8px solid #1a59a1;
        border-left: 8px solid #1a59a1;
        border-right: 8px solid #ff000000;
        display: inline-grid;
        position: absolute;
        right: -16px;
        top: 0;
    }

    .process-line-l {
        background: white;
        height: 4px;
        position: absolute;
        width: 136px;
        right: -153px;
        top: 64px;
        z-index: 9;
    }

    .process-line-r {
        background: white;
        height: 4px;
        position: absolute;
        width: 136px;
        left: -153px;
        top: 63px;
        z-index: 9;
    }
</style>

<section class="our-blog p-0 m-0 bg-silver">
    <div class="container">

        @if(session()->has('success'))
        <div class="alert alert-success">
            {{session()->get('success')}}
        </div>

        <script type="text/javascript">
            setTimeout(function() {
                $('.alert').hide();
                $('.active_table').attr('class', ' ');
            }, 5000);
        </script>
        @endif

        <div>
            <p class="display-4 text-center font-weight-normal">Asistente de habilitación DIAN</p>
            <hr>
            <p class="text-justify" style="font-size: .9em;padding: 1% 15%;"><strong>MODO DE OPERACIÓN</strong><br><br>En este paso deberás <strong>configurar el modo de operación</strong> seleccionando a <strong>Cadena S.A.S como tu proveedor tecnológico.</strong> Solo debes seguir estos pasos:</p>
        </div>
    </div>

    <div class="container work-process pb-5 pt-3">
        <div class="row">
            <div class="col-md-6">
                <div class="process-box process-left" data-aos="fade-right" data-aos-duration="1000">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="process-step">
                                <p class="m-0 p-0">Paso</p>
                                <h2 class="m-0 p-0">01</h2>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <h5>&nbsp;</h5>
                            <p class="text-justify pr-2"><small>Ingresa a la página de la DIAN en la opción <a href="https://catalogo-vpfe-hab.dian.gov.co/User/Login" target="_blank"><strong>"Habilitación"</strong></a>.</small></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6"></div>
        </div>

        <div class="row">
            <div class="col-md-6"></div>
            <div class="col-md-6">
                <div class="process-box process-right" data-aos="fade-left" data-aos-duration="1000">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="process-step">
                                <p class="m-0 p-0">Paso</p>
                                <h2 class="m-0 p-0">02</h2>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <h5>&nbsp;</h5>
                            <p class="text-justify"><small>Ingresa tus datos de inicio de sesión seleccionando el tipo de usuario, así:<br><br><strong>Empresa:</strong> si eres persona jurídica, ingresa la cédula del representante legal y el NIT de la compañía.<br><strong>Persona:</strong> si eres persona natural, elige el tipo de documento e ingresa el número de identificación.</small></p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="process-box process-left" data-aos="fade-right" data-aos-duration="1000">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="process-step">
                                <p class="m-0 p-0">Paso</p>
                                <h2 class="m-0 p-0">03</h2>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <h5>&nbsp;</h5>
                            <p class="text-justify pr-2"><small>Recibirás en tu correo electrónico (registrado en el RUT) el token de acceso a la plataforma, para ingresar haz clic en <strong>“Acceder”</strong>.</small></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6"></div>
        </div>

        <div class="row">
            <div class="col-md-6"></div>
            <div class="col-md-6">
                <div class="process-box process-right" data-aos="fade-left" data-aos-duration="1000">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="process-step">
                                <p class="m-0 p-0">Paso</p>
                                <h2 class="m-0 p-0">04</h2>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <h5>&nbsp;</h5>
                            <p class="text-justify"><small>Haz clic en el botón <strong>“Participantes”</strong> y luego en la opción <strong>“Otros documentos”</strong>.</small></p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="process-box process-left" data-aos="fade-right" data-aos-duration="1000">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="process-step">
                                <p class="m-0 p-0">Paso</p>
                                <h2 class="m-0 p-0">05</h2>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <h5>&nbsp;</h5>
                            <p class="text-justify pr-2"><small>Selecciona la opción <strong>“Nómina Electrónica y Nómina de Ajuste”</strong>.</small></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6"></div>
        </div>

        <div class="row">
            <div class="col-md-6"></div>
            <div class="col-md-6">
                <div class="process-box process-right" data-aos="fade-left" data-aos-duration="1000">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="process-step">
                                <p class="m-0 p-0">Paso</p>
                                <h2 class="m-0 p-0">06</h2>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <h5>&nbsp;</h5>
                            <p class="text-justify"><small>Haz clic en el botón <strong>“Aceptar”</strong> para confirmar que desea iniciar el proceso de habilitación para Nómina Electrónica y Nómina Ajuste.</small></p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="process-box process-left" data-aos="fade-right" data-aos-duration="1000">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="process-step">
                                <p class="m-0 p-0">Paso</p>
                                <h2 class="m-0 p-0">07</h2>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <h5>&nbsp;</h5>
                            <p class="text-justify pr-2"><small>Haz clic en el botón <strong>“Emisor”</strong> de la ventana de <strong>“Participantes”.</strong>.</small></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6"></div>
        </div>

        <div class="row">
            <div class="col-md-6"></div>
            <div class="col-md-6">
                <div class="process-box process-right" data-aos="fade-left" data-aos-duration="1000">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="process-step">
                                <p class="m-0 p-0">Paso</p>
                                <h2 class="m-0 p-0">08</h2>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <h5>&nbsp;</h5>
                            <p class="text-justify"><small>Selecciona la opción <strong>“Aceptar”</strong> para confirmar que deseas iniciar el proceso como Emisor.</small></p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="process-box process-left" data-aos="fade-right" data-aos-duration="1000">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="process-step">
                                <p class="m-0 p-0">Paso</p>
                                <h2 class="m-0 p-0">09</h2>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <h5>&nbsp;</h5>
                            <p class="text-justify pr-2"><small>En el cuadro de <strong>“Configurar Modos de operación”</strong> selecciona la opción <strong>“Software de un proveedor tecnológico”</strong> y luego haz clic en el botón <strong>“Aceptar”</strong>.</small></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6"></div>
        </div>

        <div class="row">
            <div class="col-md-6"></div>
            <div class="col-md-6">
                <div class="process-box process-right" data-aos="fade-left" data-aos-duration="1000">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="process-step">
                                <p class="m-0 p-0">Paso</p>
                                <h2 class="m-0 p-0">10</h2>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <h5>&nbsp;</h5>
                            <p class="text-justify"><small>Configura el modo de operación completando los datos solicitados por el sistema:<br><br>En <strong>“Nombre de empresa proveedora”</strong> elige a <strong>“Cadena SAS”</strong> y en campo correspondiente al <strong>“Nombre de Software”</strong> elige <strong>“Nómina electrónica”</strong>.</small></p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="process-box process-left" data-aos="fade-right" data-aos-duration="1000">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="process-step">
                                <p class="m-0 p-0">Paso</p>
                                <h2 class="m-0 p-0">11</h2>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <h5>&nbsp;</h5>
                            <p class="text-justify pr-2"><small>Haz clic en el botón <strong>“Adicionar”</strong>.</small></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6"></div>
        </div>

        <div class="row">
            <div class="col-md-6"></div>
            <div class="col-md-6">
                <div class="process-box process-right" data-aos="fade-left" data-aos-duration="1000">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="process-step">
                                <p class="m-0 p-0">Paso</p>
                                <h2 class="m-0 p-0">12</h2>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <h5>&nbsp;</h5>
                            <p class="text-justify"><small>En la sección de listados de modos de operación asociados, podrás ver el proveedor tecnológico asociado y la opción de consultar el <strong>set de pruebas</strong>.</small><br><br><a href="{{asset('images/asistente_nomina_a.png')}}" target="_blank"><img src="{{asset('images/asistente_nomina_a.png')}}" alt="" style="max-width: 100%; max-height:100px; object-fit:contain;"></a></p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="process-box process-left" data-aos="fade-right" data-aos-duration="1000">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="process-step">
                                <p class="m-0 p-0">Paso</p>
                                <h2 class="m-0 p-0">13</h2>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <h5>&nbsp;</h5>
                            <p class="text-justify pr-2"><small>Debes copiar el código del set de pruebas que encuentras en el detalle del set para usarlo en el siguiente paso.</small><br><br><a href="{{asset('images/asistente_nomina_b.png')}}" target="_blank"><img src="{{asset('images/asistente_nomina_b.png')}}" alt="" style="max-width: 100%; max-height:100px; object-fit:contain;"></a></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6"></div>
        </div>
    </div>

    <div class="container">
        <div>
            <hr>
            <div class="row">
                <div class="col-md-8 offset-md-2 mb-5">
                    <div class="card mb-lg-0" style="border: none;border-radius: 1rem;transition: all 0.2s;box-shadow: 0 0.5rem 1rem 0 rgb(0 0 0 / 10%);">
                        <div class="card-body text-center">
                            <p class="display-4 text-center font-weight-normal">Set de Pruebas</p>
                            <p class="text-center" style="font-size: .9em;padding: 1% 10%;">Al dar clic en <strong>iniciar prueba</strong>, se enviarán tus documentos a modo de prueba a la DIAN</p>

                            <form class="justify-content-center" method="get" action="{{ route('nomina-dian.proceso-habilitacion') }}" autocomplete="off">
                                <div class="row">
                                    <div class="col-sm-12 text-center">
                                        @csrf
                                        <center>
                                            <div class="form-group" style="width:87%;">
                                                <input type="text" style={{$empresa->nomina_dian == 1 ? "width:97%;border-color:#00c700!important;" : 'width:100%;'}} class="form-control mb-2 mr-sm-2" id="test_id" name="test_id" placeholder="Código TestSetId" style="width: 300px;" value="{{$empresa->test_nomina}}">
                                            </div>
                                        </center>
                                        {{-- <button type="submit" class="btn btn-primary mb-2"><i class="fas fa-play"></i>Iniciar Prueba</button> --}}
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12 mb-4">
                                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                                            Sigue los pasos de emisión en el orden dado
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 d-flex">
                                        <img src="https://img.icons8.com/color/48/000000/1-c.png" class="img-responsive mb-2" />
                                        @if ($empresa->nomina_dian)
                                        <button type="button" class="btn btn-primary mb-2 disabled"><i class="fas fa-play"></i>Enviar nómina individual</button>
                                        @else
                                        <button type="button" class="btn btn-primary mb-2" onclick="formHabilitación(1)"><i class="fas fa-play"></i>Enviar nómina individual</button>
                                        @endif
                                    </div>
                                    <div class="col-sm-6 d-flex">
                                        <img src="https://img.icons8.com/color/48/000000/2-c.png" class="img-responsive mb-2" />
                                        @if ($empresa->nomina_dian)
                                        <button type="button" class="btn btn-primary mb-2 disabled"><i class="fas fa-play"></i>Enviar nómina ajuste</button>
                                        @else
                                        <button type="button" class="btn btn-primary mb-2" onclick="formHabilitación(2)"><i class="fas fa-play"></i>Enviar nómina ajuste</button>
                                        @endif
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    @if($empresa->nomina_dian==1)
                    <div class="alert alert-success" role="alert">
                        ¡Has sido habilitado para poder emitir la nómina electrónica de tu empresa!
                        <br>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    AOS.init();

    function formHabilitación(tipo) {
        var settestid = $("#test_id").val();

        $(document).ajaxStart(function() {

            const Toast = Swal.mixin({
                toast: true,
                position: 'top-center',
                showConfirmButton: false,
                timer: 1000000000,
                timerProgressBar: true,
                onOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            })

            Toast.fire({
                type: 'success',
                title: 'Emitiendo nominas, no cierre esta página...',
            })
        });

        $.ajax({
            url: '/empresa/nominadian/proceso-habilitacion',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            method: 'get',
            data: {
                tipo: tipo,
                settestid: settestid
            },
            success: function(response) {
                console.log(response);
               
            }
        })

        $(document).ajaxStop(function() {

            const Toast = Swal.mixin({
                toast: true,
                position: 'top-center',
                showConfirmButton: false,
                timer: 5000,
                timerProgressBar: true,
                onOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            })
            Toast.fire({
                type: 'success',
                title: 'Emisiones realizadas correctamente...',
            })
            
             if(tipo == 2){
                    location.reload();
            }
        });
    }
</script>
@endsection