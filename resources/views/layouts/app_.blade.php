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
        <title>{{$title}}</title>
        
        <link rel="shortcut icon" href="{{asset('images/Empresas/Empresa1/favicon.png')}}" />
        
        <link rel="stylesheet" href="{{asset('vendors/iconfonts/mdi/css/materialdesignicons.min.css')}}">
        <link rel="stylesheet" href="{{asset('vendors/css/vendor.bundle.base.css')}}">
        <link rel="stylesheet" href="{{asset('vendors/css/vendor.bundle.addons.css')}}">
        <link rel="stylesheet" type="text/css" href="{{asset('vendors/DataTables/datatables.min.css')}}"/>
        <link rel="stylesheet" type="text/css" href="{{asset('vendors/bootstrap-selectpicker/css/bootstrap-select.min.css')}}"/>
        <link rel="stylesheet" href="{{asset('vendors/fontawesome/css/all.css')}}" />
        <link rel="stylesheet" type="text/css" href="{{asset('vendors/flag-icon-css/css/flag-icon.min.css')}}"/>
        <link rel="stylesheet" type="text/css" href="{{asset('vendors/sweetalert2/sweetalert2.min.css')}}"/>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins">
        <link rel="stylesheet" href="{{asset('vendors/bootstrap-datepicker/css/gijgo.min.css')}}">
        <link rel="stylesheet" href="{{asset('vendors/morris/morris.css')}}">
        <link rel="stylesheet" href="{{asset('vendors/profile-picture/profile-picture.css')}}">
        <link rel="stylesheet" href="{{asset('vendors/dropify/dropify.css')}}">
        <link rel="stylesheet" href="{{asset('vendors/dropzone/dropzone.css')}}">
        <link rel="stylesheet" href="{{asset('vendors/light-gallery/css/lightgallery.css')}}">
        <link rel="stylesheet" href="{{asset('vendors/autocomplete/jquery.auto-complete.css')}}">
        <link rel="stylesheet" href="{{asset('css/style.css')}}">
        <link rel="stylesheet" href="{{asset('css/documentacion.css')}}">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css">
        
        <style>
            .paper{
                margin: 0px 25px 30px 25px;
                padding-top: 5%;
            }
            .paper:before {
                top: 0px;
                right: 0px;
                border-color: #f9fafd #f9f9f9 #eaedf7 #eaedf7;
            }
            .sidebar {
                background: {{$empresa->color}};
            }
            .configuracion > div {
                border: 4px solid {{$empresa->color}};
            }
            .configuracion h4 {
                color: #000;
            }
            .text-primary {
                color: {{$empresa->color}} !important;
            }
            .configuracion > div > a {
                color: {{$empresa->color}};
            }
            .form-radio label input + .input-helper:after {
                background: {{$empresa->color}};
            }
            .notice-info {
                border-color: {{$empresa->color}} !important;
            }
            .btn-link {
                color: {{$empresa->color}} !important;
            }
            .sidebar .nav .sub-menu .nav-item .nav-link:hover, #sidebar > ul > li > a:hover {
                color: #c7c7c7;
            }
            .nav-pills .nav-link.active, .nav-pills .show > .nav-link {
                color: #fff;
                background-color: {{$empresa->color}};
            }
            .nav-tabs .nav-link.active, .nav-tabs .nav-item.show .nav-link {
                color: #fff;
                background-color: {{$empresa->color}};
                border-color: #dee2e6 #dee2e6 #fff;
            }
            .card-notificacion {
                border-radius: 20px;
                background: #fff!important;
                border: solid 2px {{$empresa->color}}!important;
            }
            .card-notificacion:hover {
                border-radius: 20px;
                background: {{$empresa->color}}!important;
                border: solid 2px #fff;
            }
            .bg-th {
                background: {{$empresa->color}} !important;
                border-color: {{$empresa->color}} !important;
                color: #fff !important;
            }
            .table-bordered {
                border: 2px solid {{$empresa->color}}!important;
            }
            .table.table-bordered th {
                color: #fff;
                background-color: {{$empresa->color}};
                border-color: {{$empresa->color}};
            }
            .table .thead-dark th {
                color: #fff;
                background-color: {{$empresa->color}};
                border-color: {{$empresa->color}};
            }
            table.dataTable thead .sorting_asc:before, table.dataTable thead .sorting_desc:after {
                display: block !important;
                color: #ffffff;
            }
            .page-item.active .page-link {
                background-color: {{$empresa->color}};
                border-color: {{$empresa->color}};
            }
            .page-item.active .page-link {
                background-color: {{$empresa->color}};
                border-color: {{$empresa->color}};
            }
            .page-item.disabled .page-link {
                color: {{$empresa->color}};
                border-color: {{$empresa->color}};
            }
            .page-link {
                color: {{$empresa->color}};
                border: 1px solid {{$empresa->color}};
            }
            .card-counter.primary:hover, .card-counter.success:hover, .card-counter.danger:hover {
                background-color: #4f4f4f; 
            }
            .page-link:hover {
                color: #ffffff;
                text-decoration: none;
                background-color: {{$empresa->color}};
                border-color: {{$empresa->color}};
            }
            .stretch-card { border: 1px solid #a6b6bd52 !important;border-radius: 3px; }
            .content-wrapper { background: #fff; }
            .card { background: #c2c2c21a !important; }
            .img-gafica{
                border: solid 1px {{$empresa->color}};
                border-radius: 10px;
            }
            .btn-system {
                color: #fff;
                background-color: {{$empresa->color}};
                border-color: {{$empresa->color}};
            }
            .btn-system:hover, .btn-system:active  {
                color: #fff;
                background-color: #333;
                border-color: #333;
            }
            .min_max_70 {
                min-height: 70px;
                max-height: 145px;
            }
            #form-filter{
                padding-left: 1.5rem !important;
                padding-right: 1.5rem !important;
            }
            #form-filter > div, #form-filterG > div{
                border: solid 1px {{$empresa->color}} !important;
                padding: 2% 1%;
            }
            .whatsapp {
                position: fixed;
                right:25px; /*Margen derecho*/
                bottom:20px; /*Margen abajo*/
                z-index:999;
            }
            .whatsapp img {
                width:60px; /*Alto del icono*/
                height:60px; /*Ancho del icono*/
            }
            .whatsapp:hover{
                opacity: 0.7 !important;
                filter: alpha(opacity=70) !important;
            }
            .select2-container--default .select2-selection--multiple {
                border: 1px solid #dee4e6;
                border-radius: 2px;
            }
            .Cerrada-emitida span {
                font-size: 0.8em; padding: 1%; font-weight: bold; color: #FFF; text-transform: uppercase; text-align: center; line-height: 20px; transform: rotate(-45deg); -webkit-transform: rotate(-45deg); width: 79%; display: block; background: #79A70A; background: linear-gradient(#00CE68 0%, #00CE68 100%); box-shadow: 0 3px 10px -5px rgba(0, 0, 0, 1); position: absolute; top: 19%; left: -36px;
            }
            .Cerrada-emitida span::before {
                content: ""; position: absolute; left: 0px; top: 100%; z-index: -1; border-left: 3px solid #00CE68; border-right: 3px solid transparent; border-bottom: 3px solid transparent; border-top: 3px solid #00CE68;
            }
            .Cerrada-emitida span::after {
                content: ""; position: absolute; right: 0px; top: 100%; z-index: -1; border-left: 3px solid transparent; border-right: 3px solid #00CE68; border-bottom: 3px solid transparent; border-top: 3px solid #00CE68;
            }
            .Abierta-no span, .Abierta-emitida span{
                font-size: 0.8em; padding: 1%; font-weight: bold; color: #FFF; text-transform: uppercase; text-align: center; line-height: 20px; transform: rotate(-45deg); -webkit-transform: rotate(-45deg); width: 79%; display: block; background: #e65251; background: linear-gradient(#e65251 0%, #e65251 100%); box-shadow: 0 3px 10px -5px rgba(0, 0, 0, 1); position: absolute; top: 19%; left: -36px;
            }
            .Abierta-no span::before, .Abierta-emitida span::before{
                content: ""; position: absolute; left: 0px; top: 100%; z-index: -1; border-left: 3px solid #e65251; border-right: 3px solid transparent; border-bottom: 3px solid transparent; border-top: 3px solid #e65251;
            }
            .Abierta-no span::after, .Abierta-emitida span::after{
                content: ""; position: absolute; right: 0px; top: 100%; z-index: -1; border-left: 3px solid transparent; border-right: 3px solid #e65251; border-bottom: 3px solid transparent; border-top: 3px solid #e65251;
            }
            .form-group label {
                font-weight: 500;
            }
            .btn-none, .btn-none: hover{
                background-color: transparent;
                border-color: transparent;
            }
            fieldset {
                border-width: 1px;
                border-style: double;
                border-color: {{$empresa->color}};
            }
            legend {
                width: auto;
                padding: 0% 2%;
                font-size: 1rem;
                color: #fff;
                background: {{$empresa->color}};
                border-radius: 5px;
                text-transform: uppercase;
            }
            div.dataTables_wrapper div.dataTables_length select {
                width: 60px;
            }
            .border, .loader-demo-box {
                border: 1px solid #dee4e6 !important;
            }
            .gj-picker-bootstrap [role=header] {
                background: {{$empresa->color}};
                color: #AAA;
            }
            .gj-picker-bootstrap {
                border: 0;
                border-radius: 20px;
            }
            #tabla-contratos_wrapper .dt-buttons,#tabla-planes_wrapper .dt-buttons,#tabla-mikrotiks_wrapper .dt-buttons,#tabla-nodos_wrapper .dt-buttons,#tabla-aps_wrapper .dt-buttons,#tabla-grupos_wrapper .dt-buttons,#tabla-bancos_wrapper .dt-buttons,#tabla-wifis_wrapper .dt-buttons,#table_sin_gestionar_wrapper .dt-buttons, #table_sin_gestionarG_wrapper .dt-buttons,#tabla-ventas-externas_wrapper .dt-buttons{
                float: right !important;
            }
            #tabla-contratos_length,#tabla-planes_length,#tabla-mikrotiks_length,#tabla-nodos_length,#tabla-aps_length,#tabla-grupos_length,#tabla-bancos_length,#tabla-wifis_length,#table_sin_gestionar_length,#table_sin_gestionarG_length,#tabla-ventas-externas_length{
                margin: 1% 0 !important;
            }
            #tabla-contratos_wrapper .dt-buttons button,#tabla-planes_wrapper .dt-buttons button,#tabla-mikrotiks_wrapper .dt-buttons button,#tabla-nodos_wrapper .dt-buttons button,#tabla-aps_wrapper .dt-buttons button,#tabla-grupos_wrapper .dt-buttons button,#tabla-bancos_wrapper .dt-buttons button,#tabla-wifis_wrapper .dt-buttons button,#table_sin_gestionar_wrapper .dt-buttons button,#table_sin_gestionarG_wrapper .dt-buttons button,#tabla-ventas-externas_wrapper .dt-buttons button{
                color: #fff !important;
                background-color: #00ce68 !important;
                border-color: #00ce68 !important;
            }
            #tabla-contratos_wrapper .dt-buttons button:hover,#tabla-planes_wrapper .dt-buttons button:hover,#tabla-mikrotiks_wrapper .dt-buttons button:hover,#tabla-nodos_wrapper .dt-buttons button:hover,#tabla-aps_wrapper .dt-buttons button:hover,#tabla-grupos_wrapper .dt-buttons button:hover,#tabla-bancos_wrapper .dt-buttons button:hover,#tabla-wifis_wrapper .dt-buttons button:hover,#table_sin_gestionar_wrapper .dt-buttons button:hover,#table_sin_gestionarG_wrapper .dt-buttons button:hover,#tabla-ventas-externas_wrapper .dt-buttons button:hover{
                color: #fff !important;
                background-color: #218838 !important;
                border-color: #1e7e34 !important;
            }
            #tabla-contratos_wrapper .dt-buttons button:nth-child(2),#tabla-planes_wrapper .dt-buttons button:nth-child(2),#tabla-mikrotiks_wrapper .dt-buttons button:nth-child(2),#tabla-nodos_wrapper .dt-buttons button:nth-child(2),#tabla-aps_wrapper .dt-buttons button:nth-child(2),#tabla-grupos_wrapper .dt-buttons button:nth-child(2),#tabla-bancos_wrapper .dt-buttons button:nth-child(2),#tabla-wifis_wrapper .dt-buttons button:nth-child(2),#table_sin_gestionar_wrapper .dt-buttons button:nth-child(2),#table_sin_gestionarG_wrapper .dt-buttons button:nth-child(2),#tabla-ventas-externas_wrapper .dt-buttons button:nth-child(2){
                color: #fff !important;
                background-color: #e65251 !important;
                border-color: #e65251 !important;
            }
            #tabla-contratos_wrapper .dt-buttons button:nth-child(2):hover,#tabla-planes_wrapper .dt-buttons button:nth-child(2):hover,#tabla-mikrotiks_wrapper .dt-buttons button:nth-child(2):hover,#tabla-nodos_wrapper .dt-buttons button:nth-child(2):hover,#tabla-aps_wrapper .dt-buttons button:nth-child(2):hover,#tabla-grupos_wrapper .dt-buttons button:nth-child(2):hover,#tabla-bancos_wrapper .dt-buttons button:nth-child(2):hover,#tabla-wifis_wrapper .dt-buttons button:nth-child(2):hover,#table_sin_gestionar_wrapper .dt-buttons button:nth-child(2):hover,#table_sin_gestionarG_wrapper .dt-buttons button:nth-child(2):hover,#tabla-ventas-externas_wrapper .dt-buttons button:nth-child(2):hover{
                color: #fff !important;
                background-color: #c82333 !important;
                border-color: #bd2130 !important;
            }
            div.dataTables_wrapper div.dataTables_paginate {
                text-align: -webkit-center !important;
            }
            </style>
        @yield('style')
    </head>
    <body>
        <div id="contenedor_carga">
            <img id="carga" src="{{asset('images/gif-tuerca.gif')}}">
        </div>
        <div class="loader"></div>
        <div class="container">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="content-wrapper">
                        <div class="grid-margin stretch-card">
                            <div class="card">
                                <div class="body-card">
                                    <div class="row" style="padding: 2%;">
                                        <div class="col-12">
                                            @yield('content')
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <footer class="footer d-none">
            <div class="container-fluid clearfix">
                <span class="text-muted d-block text-center text-sm-left d-sm-inline-block">
                    Todos los Derechos Reservados © {{ $empresa->nombre }}<a href="#" target="_blank"></a>
                </span>
                <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center">
                    Realizado por: <a href="https://networkingenieria.com/" target="_blank">Network Ingeniería S.A.S</a> <i class="mdi mdi-heart text-danger"></i>
                </span>
            </div>
        </footer>

        <script >
            window.onload = function(){
                var contenedor = document.getElementById('contenedor_carga');
                contenedor.style.visibility = 'hidden';
                contenedor.style.opacity = '0';
            }
        </script>
        <!-- container-scroller -->
        
        <!-- plugins:js -->
        <script src="{{asset('vendors/js/vendor.bundle.base.js')}}"></script>
        <script src="{{asset('vendors/js/vendor.bundle.addons.js')}}"></script>
        <!-- endinject -->
        <!-- Plugin js for this page-->
        <!-- End plugin js for this page-->
        <!-- inject:js -->
        <script src="{{asset('js/off-canvas.js')}}"></script>
        <script src="{{asset('js/misc.js')}}"></script>
        <script type="text/javascript" src="{{asset('vendors/DataTables/datatables.min.js')}}"></script>
        <script type="text/javascript" src="{{asset('js/CollapsibleLists.js')}}"></script>
        <script type="text/javascript" src="{{asset('vendors/bootstrap-selectpicker/js/bootstrap-select.min.js')}}"></script>
        <script src="{{asset('vendors/bootstrap-datepicker/js/gijgo.min.js')}}"></script>
        <script src="{{asset('vendors/bootstrap-datepicker/js/messages/messages.es-es.min.js')}}"></script>
        <!-- Custom js for this page-->
        <script type="text/javascript" src="{{asset('vendors/validation/jquery.validate.min.js')}}"></script>
        <script type="text/javascript" src="{{asset('vendors/validation/localization/messages_es.js')}}"></script>
        <script type="text/javascript" src="{{asset('js/jquery.mask.min.js')}}"></script>
        <script type="text/javascript" src="{{asset('vendors/sweetalert2/sweetalert2.min.js')}}"></script>
        <script type="text/javascript" src="{{asset('vendors/morris/morris.min.js')}}"></script>
        <script type="text/javascript" src="{{asset('vendors/sortable/jquery.sortable.min.js')}}"></script>
        <script type="text/javascript" src="{{asset('vendors/autocomplete/jquery.auto-complete.js')}}"></script>
        <script type="text/javascript" src="{{asset('vendors/profile-picture/profile-picture.js')}}"></script>
        <script type="text/javascript" src="{{asset('vendors/dropify/dropify.js')}}"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
        <script src="{{asset('js/paginicio/planes.js')}}"></script>
        <!-- Dropzone Plugin Js -->
        <script src="{{asset('vendors/dropzone/dropzone.js')}}"></script>
        <!-- Light Gallery Plugin Js -->
        <script src="{{asset('vendors/light-gallery/js/lightgallery-all.js')}}"></script>
        <!-- endinject -->
        <script src="{{asset('js/moment.js')}}"></script>
        <script src="{{asset('js/function.js')}}"></script>
        <script src="{{asset('js/custom.js')}}"></script>
        <script src="{{asset('js/dian.js')}}"></script>
        <script type="text/javascript" src='https://maps.google.com/maps/api/js?sensor=false&libraries=places'></script>
        <script type="text/javascript" src="{{asset('js/locationpicker.jquery.js')}}"></script>
        
        <script type="text/javascript">
            $( document ).ready(function() {
                $("#{{$seccion}}").addClass("active");
                if ($("#{{$seccion}}").find('.sub-menu').length) {
                    $("#{{$seccion}}").find('.collapse').addClass('show');
                }
                @if(isset($subseccion))
                $("#{{$subseccion}}").addClass("active");
                @endif
            });
        </script>
        
        <!-- End custom js for this page-->
        <script src="{{asset('vendors/documentacion/index.all.min.js')}}"></script>
        <script src="{{asset('vendors/documentacion/popper.min.js')}}"></script>
        <script type="text/javascript" src="{{asset('js/paginicio/floating-wpp.min.js')}}"></script>
        <link rel="stylesheet" href="{{asset('css/floating-wpp.min.css')}}">
        <script src="{{asset('vendors/ckeditor/ckeditor.js')}}"></script>
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
            $(document).on("mouseup",function(e) {
                if($("#sidebar").hasClass('active')){
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
