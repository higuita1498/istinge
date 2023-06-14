<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="" />
    <meta name="author" content="">
    <meta name="keyword" content="">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title }}</title>

    <link rel="shortcut icon" href="{{ asset('images/Empresas/Empresa1/favicon.png') }}" />

    {{-- Fuentes --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins">

    <link rel="stylesheet" href="{{ asset('vendors/iconfonts/mdi/css/materialdesignicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.addons.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('vendors/DataTables/datatables.min.css') }}" />
    <link rel="stylesheet" type="text/css"
        href="{{ asset('vendors/bootstrap-selectpicker/css/bootstrap-select.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('vendors/flag-icon-css/css/flag-icon.min.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('vendors/sweetalert2/sweetalert2.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('vendors/bootstrap-datepicker/css/gijgo.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/morris/morris.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/profile-picture/profile-picture.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/dropify/dropify.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/dropzone/dropzone.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/light-gallery/css/lightgallery.css') }}">
    <link rel="stylesheet" href="{{ asset('vendors/autocomplete/jquery.auto-complete.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/documentacion.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">

    {{--
        Esta sentencia es necesaria, ya que algunos componentes tienen estilos
        definidos dentro de la plantilla de Blade. La idea es que se vayan retirando
        poco a poco estos estilos de ahí.
    --}}
    @yield('style')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>
    @if (Auth::user()->online === 0)
        @php
            Auth::logout();
            return Redirect::to('login');
        @endphp
    @endif

    <div id="contenedor_carga">
        <img id="carga" src="{{ asset('images/gif-tuerca.gif') }}">
    </div>
    <div class="loader"></div>
    <div class="container-scroller">
        <!-- partial:partials/_navbar.html -->
        @include('layouts.includes.navbar')
        <!-- partial -->
        <div class="page-body-wrapper">
            <!-- partial:partials/_sidebar.html -->
            <nav class="sidebar sidebar-offcanvas" id="sidebar">
                <ul class="nav">
                    <li class="nav-item nav-profile">
                        <div class="nav-link" style="padding: 6% !important">
                            <div class="user-wrapper">
                                <div class="profile-image">
                                    @if (Auth::user()->image)
                                        <img src="{{ asset('images/Empresas/Empresa' . Auth::user()->empresa . '/usuarios/' . Auth::user()->image) }}"
                                            onerror="{{ asset('images/no-user-image.png') }}" alt="profile image">
                                    @else
                                        <img src="{{ asset('images/no-user-image.png') }}" alt="profile image">
                                    @endif
                                </div>
                                <div class="text-wrapper">
                                    <p style="text-transform:capitalize;" class="profile-name">
                                        {{ Auth::user()->nombres }}</p>
                                    @if (Auth::user()->empresa())
                                        <input type="hidden" value="{{ Auth::user()->empresa()->precision }}"
                                            id="precision">
                                        <input type="hidden" value="{{ Auth::user()->empresa()->sep_dec }}"
                                            id="sep_dec">
                                        <input type="hidden"
                                            value="{{ Auth::user()->empresa()->sep_dec == '.' ? ',' : '.' }}"
                                            id="sep_miles">
                                    @endif
                                    <div>
                                        <small class="designation">{{ Auth::user()->roles->rol }}</small>
                                        <span class="status-indicator online"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li class="nav-item" id="">
                        <a class="nav-link" href="{{ route('home') }}">
                            <i class="menu-icon fas fa-home"></i>
                            <span class="menu-title">Inicio</span>
                        </a>
                    </li>
                    @include('layouts.includes.menu')
                </ul>
            </nav>
            <!-- partial -->
            <div class="container-fluid main-panel">
                <div class="content-wrapper">
                    <div class="grid-margin stretch-card">
                        <div class="card">
                            <div class="body-card">
                                <div class="row " style="padding: 2%;">
                                    @php
                                        $col_md1 = 8;
                                        $col_md2 = 4;
                                        if (isset($invert)) {
                                            $col_md1 = 4;
                                            $col_md2 = 8;
                                        }
                                        if (isset($middel)) {
                                            $col_md1 = 6;
                                            $col_md2 = 6;
                                        }
                                        if (isset($precice)) {
                                            $col_md1 = 5;
                                            $col_md2 = 7;
                                        }
                                        if (isset($minus_dere)) {
                                            $col_md1 = 2;
                                            $col_md2 = 10;
                                        }
                                        if (isset($minus_izq)) {
                                            $col_md1 = 10;
                                            $col_md2 = 2;
                                        }
                                        if (isset($full)) {
                                            $col_md1 = 12;
                                            $col_md2 = 0;
                                        }
                                        if (isset($invertfalse)) {
                                            $col_md1 = 8;
                                            $col_md2 = 4;
                                        }
                                    @endphp
                                    <div class="col-md-{{ $col_md1 }}" style="text-align: left;">
                                        <h1 id="titulo"><i class="{{ $icon }}"></i>
                                            {{ isset($title_sub) ? $title_sub : $title }}</h1>
                                    </div>
                                    <div class="col-md-{{ $col_md2 }}" style="text-align: right;">
                                        @yield('boton')
                                    </div>
                                </div>
                                <!-- msj cconfirmacion CRM -->
                                @if (Session::has('novence'))
                                    <div class="alert alert-warning alert-dismissible fade show" role="alert"
                                        style="background-color:#FFFC33;">
                                        <strong>{{ Session::get('novence') }}</strong>
                                        <button type="button" class="close" data-dismiss="alert"
                                            aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif
                                <!-- fin msj-->
                                <!-- Funcion para generar el imprimir -->
                                @if (Session::has('print'))
                                    @if (Session::get('print'))
                                        <input type="hidden" id="imprimir"
                                            value="{{ route('facturas.imprimir', Session::get('print')) }}">
                                    @endif
                                @endif

                                @yield('content')
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Modal Small-->
                <div class="modal fade" id="modal-small" tabindex="-1" role="dialog"
                    aria-labelledby="modal-small-CenterTitle" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content" id="modal-small-div">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLongTitle">Modal title</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                ...
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary">Save changes</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Modal -->
                <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog"
                    aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Modal title</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                ...
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-primary">Save changes</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- NOTIFICACIONES -->
                <input type="hidden" name="nro_notificacionesP" id="nro_notificacionesP" value="0">
                <audio id="play_notificacion" preload="auto" tabindex="0" controls="" class="d-none">
                    <source src="{{ asset('images/alerta.mp3') }}">
                </audio>
                <div class="modal fade" id="modalNotificacionP" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header p-0">
                                <center><img src="{{ asset('images/Empresas/Empresa1/logo.png') }}" style="width:15%"
                                        class="m-2"></center>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                    style="margin: -10px;">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body" id="modal-bodyP">

                            </div>
                        </div>
                    </div>
                </div>

                <!-- NOTIFICACIONES -->
                <input type="hidden" name="nro_notificacionesW" id="nro_notificacionesW" value="0">
                <audio id="play_notificacion" preload="auto" tabindex="0" controls="" class="d-none">
                    <source src="{{ asset('images/alerta.mp3') }}">
                </audio>
                <div class="modal fade" id="modalNotificacionW" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header p-0">
                                <center><img src="{{ asset('images/Empresas/Empresa1/logo.png') }}" style="width:15%"
                                        class="m-2"></center>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                    style="margin: -10px;">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body" id="modal-bodyW">

                            </div>
                        </div>
                    </div>
                </div>

                <!-- NOTIFICACIONES -->
                <input type="hidden" name="nro_notificacionesR" id="nro_notificacionesR" value="0">
                <audio id="play_notificacion" preload="auto" tabindex="0" controls="" class="d-none">
                    <source src="{{ asset('images/alerta.mp3') }}">
                </audio>
                <div class="modal fade" id="modalNotificacionR" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header p-0">
                                <center><img src="{{ asset('images/Empresas/Empresa1/logo.png') }}" style="width:15%"
                                        class="m-2"></center>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                    style="margin: -10px;">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body" id="modal-bodyR">

                            </div>
                        </div>
                    </div>
                </div>

                <!-- NOTIFICACIONES -->
                <input type="hidden" name="nro_notificacionesT" id="nro_notificacionesT" value="0">
                <audio id="play_notificacion" preload="auto" tabindex="0" controls="" class="d-none">
                    <source src="{{ asset('images/alerta.mp3') }}">
                </audio>
                <div class="modal fade" id="modalNotificacionT" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header p-0">
                                <center><img src="{{ asset('images/Empresas/Empresa1/logo.png') }}" style="width:15%"
                                        class="m-2"></center>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                    style="margin: -10px;">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body" id="modal-bodyT">

                            </div>
                        </div>
                    </div>
                </div>

                <!-- NOTIFICACIONES -->
                <input type="hidden" name="nro_notificaciones" id="nro_notificaciones" value="0">
                <audio id="play_notificacion" preload="auto" tabindex="0" controls="" class="d-none">
                    <source src="{{ asset('images/alerta.mp3') }}">
                </audio>
                <div class="modal fade" id="modalNotificacion" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header p-0">
                                <center><img src="{{ asset('images/Empresas/Empresa1/logo.png') }}" style="width:15%"
                                        class="m-2"></center>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"
                                    style="margin: -10px;">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body" id="modal-bodyc">

                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" id="pageLength"
                    value="{{ Auth::user()->rol > 1 ? Auth::user()->empresa()->pageLength : '25' }}">

                <footer class="footer">
                    <div class="container-fluid clearfix">
                        <span class="text-muted d-block text-center text-sm-left d-sm-inline-block">
                            Todos los Derechos Reservados © @if (Auth::user()->rol > 1)
                                {{ Auth::user()->empresa()->nombre }}
                            @endif
                            <a href="#" target="_blank"></a>
                        </span>
                        <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center">
                            Realizado por: <a href="https://networkingenieria.com/" target="_blank">Network Ingeniería
                                S.A.S</a> <i class="mdi mdi-heart text-danger"></i>
                        </span>
                    </div>
                </footer>
            </div>
            <div class="whatsapp text-left">
                <a href="https://api.whatsapp.com/send?phone=+573135774747&text=Hola Network Soft, necesito soporte para la empresa {{ Auth::user()->rol > 1 ? Auth::user()->empresa()->nombre : '' }}"
                    target="_blank" title="Soporte vía Whatsapp">
                    <img src="{{ asset('images/whatsapp.png') }}" alt="WhatsApp" />
                </a>
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            var contenedor = document.getElementById('contenedor_carga');
            contenedor.style.visibility = 'hidden';
            contenedor.style.opacity = '0';
        }
    </script>
    <!-- container-scroller -->

    <!-- plugins:js -->
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.addons.js') }}"></script>
    <!-- endinject -->
    <!-- Plugin js for this page-->
    <!-- End plugin js for this page-->
    <!-- inject:js -->
    <script src="{{ asset('js/off-canvas.js') }}"></script>
    <script src="{{ asset('js/misc.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendors/DataTables/datatables.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/CollapsibleLists.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendors/bootstrap-selectpicker/js/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('vendors/bootstrap-datepicker/js/gijgo.min.js') }}"></script>
    <script src="{{ asset('vendors/bootstrap-datepicker/js/messages/messages.es-es.min.js') }}"></script>
    <!-- Custom js for this page-->
    <script type="text/javascript" src="{{ asset('vendors/validation/jquery.validate.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendors/validation/localization/messages_es.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/jquery.mask.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendors/sweetalert2/sweetalert2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendors/morris/morris.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendors/sortable/jquery.sortable.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendors/autocomplete/jquery.auto-complete.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendors/profile-picture/profile-picture.js') }}"></script>
    <script type="text/javascript" src="{{ asset('vendors/dropify/dropify.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
    <script src="{{ asset('js/paginicio/planes.js') }}"></script>
    <!-- Dropzone Plugin Js -->
    <script src="{{ asset('vendors/dropzone/dropzone.js') }}"></script>
    <!-- Light Gallery Plugin Js -->
    <script src="{{ asset('vendors/light-gallery/js/lightgallery-all.js') }}"></script>
    <!-- endinject -->
    <script src="{{ asset('js/moment.js') }}"></script>
    <script src="{{ asset('js/function.js') }}?v={{ Auth::user()->rol == 1 ? '1' : Auth::user()->empresa()->cache }}">
    </script>
    <script src="{{ asset('js/custom.js') }}?v={{ Auth::user()->rol == 1 ? '1' : Auth::user()->empresa()->cache }}">
    </script>
    <script src="{{ asset('js/dian.js') }}?v={{ Auth::user()->rol == 1 ? '1' : Auth::user()->empresa()->cache }}">
    </script>
    <script type="text/javascript" src='https://maps.google.com/maps/api/js?sensor=false&libraries=places'></script>
    <script type="text/javascript" src="{{ asset('js/locationpicker.jquery.js') }}"></script>

    <script src="//cdn.datatables.net/plug-ins/1.12.1/sorting/ip-address.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            $("#{{ $seccion }}").addClass("active");
            if ($("#{{ $seccion }}").find('.sub-menu').length) {
                $("#{{ $seccion }}").find('.collapse').addClass('show');
            }
            @if (isset($subseccion))
                $("#{{ $subseccion }}").addClass("active");
            @endif
        });
    </script>

    <!-- End custom js for this page-->
    <script src="{{ asset('vendors/documentacion/index.all.min.js') }}"></script>
    <script src="{{ asset('vendors/documentacion/popper.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/paginicio/floating-wpp.min.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('css/floating-wpp.min.css') }}">
    <script src="{{ asset('vendors/ckeditor/ckeditor.js') }}"></script>
    <script>
        tippy('.icono', {
            content: 'global content',
            animation: 'perspective',
            arrow: true,
            arrowType: 'sharp',
            interactive: true,
        })
    </script>

    <script type="text/javascript">
        $(document).on("mouseup", function(e) {
            if ($("#sidebar").hasClass('active')) {
                var container = $("#sidebar");
                if (!container.is(e.target) && container.has(e.target).length === 0) {
                    container.removeClass('active');
                }
            }
        });
    </script>
    @yield('scripts')
</body>

</html>
