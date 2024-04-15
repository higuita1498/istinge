<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="UTF-8">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="" />
        <meta name="author" content="">
        <meta name="keyword" content="">
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <title>{{$title}}</title>

        <link rel="shortcut icon" href="{{asset('images/Empresas/Empresa1/favicon.png')}}" />
        <link rel="stylesheet" href="{{asset('gritter/css/jquery.gritter.css')}}">
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
                .alerta-whatsapp{
                    background: #506de300 !important;
                }
            .alerta-whatsapp img{
                height: 50px;
                border-radius: 50%;
            }
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
                background: {{Auth::user()->username == 'desarrollo' ? '#022454' : (isset(Auth::user()->empresa()->color) ? Auth::user()->empresa()->color : '#022454') }};
            }
            .configuracion > div {
                border: 4px solid {{Auth::user()->username == 'desarrollo' ? '#022454' : (isset(Auth::user()->empresa()->color) ? Auth::user()->empresa()->color : '#022454') }};
            }
            .configuracion h4 {
                color: #000;
            }
            .text-primary {
                color: {{Auth::user()->username == 'desarrollo' ? '#022454' : (isset(Auth::user()->empresa()->color) ? Auth::user()->empresa()->color : '#022454') }};
            }
            .configuracion > div > a {
                color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
            }
            .form-radio label input + .input-helper:after {
                background: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
            }
            .notice-info {
                border-color: {{Auth::user()->username == 'desarrollo' ? '#022454' : (isset(Auth::user()->empresa()->color) ? Auth::user()->empresa()->color : '#022454') }};
            }
            .btn-link {
                color: {{Auth::user()->username == 'desarrollo' ? '#022454' : (isset(Auth::user()->empresa()->color) ? Auth::user()->empresa()->color : '#022454') }};
            }
            .sidebar .nav .sub-menu .nav-item .nav-link:hover, #sidebar > ul > li > a:hover {
                color: #c7c7c7;
            }
            .nav-pills .nav-link.active, .nav-pills .show > .nav-link {
                color: #fff;
                background-color: {{Auth::user()->username == 'desarrollo' ? '#022454' : (isset(Auth::user()->empresa()->color) ? Auth::user()->empresa()->color : '#022454') }};
            }
            .nav-tabs .nav-link.active, .nav-tabs .nav-item.show .nav-link {
                color: #fff;
                background-color: {{Auth::user()->username == 'desarrollo' ? '#022454' : (isset(Auth::user()->empresa()->color) ? Auth::user()->empresa()->color : '#022454') }};
                border-color: #dee2e6 #dee2e6 #fff;
            }
            .card-notificacion {
                border-radius: 20px;
                background: #fff!important;
                border: solid 2px {{Auth::user()->username == 'desarrollo' ? '#022454' : (isset(Auth::user()->empresa()->color) ? Auth::user()->empresa()->color : '#022454') }};
            }
            .card-notificacion:hover {
                border-radius: 20px;
                background: {{Auth::user()->username == 'desarrollo' ? '#022454' : (isset(Auth::user()->empresa()->color) ? Auth::user()->empresa()->color : '#022454') }};
                border: solid 2px #fff;
            }
            .bg-th {
                background: {{Auth::user()->username == 'desarrollo' ? '#022454' : (isset(Auth::user()->empresa()->color) ? Auth::user()->empresa()->color : '#022454') }};
                border-color: {{Auth::user()->username == 'desarrollo' ? '#022454' : (isset(Auth::user()->empresa()->color) ? Auth::user()->empresa()->color : '#022454') }};
                color: #fff !important;
            }
            .table-bordered {
                border: 2px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}!important;
            }
            .table.table-bordered th {
                color: #fff;
                background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
                border-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
            }
            .table .thead-dark th {
                color: #fff;
                background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
                border-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
            }
            table.dataTable thead .sorting_asc:before, table.dataTable thead .sorting_desc:after {
                display: block !important;
                color: #ffffff;
            }
            .page-item.active .page-link {
                background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
                border-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
            }
            .page-item.active .page-link {
                background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
                border-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
            }
            .page-item.disabled .page-link {
                color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
                border-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
            }
            .page-link {
                color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
                border: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
            }
            .card-counter.primary:hover, .card-counter.success:hover, .card-counter.danger:hover {
                background-color: #4f4f4f;
            }
            .page-link:hover {
                color: #ffffff;
                text-decoration: none;
                background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
                border-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
            }
            .stretch-card { border: 1px solid #a6b6bd52 !important;border-radius: 3px; }
            .content-wrapper { background: #fff; }
            .card { background: #c2c2c21a !important; }
            .img-gafica{
                border: solid 1px {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
                border-radius: 10px;
            }
            .btn-system {
                color: #fff;
                background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
                border-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
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
                border: solid 1px {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}} !important;
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
                border-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
            }
            legend {
                width: auto;
                padding: 0% 2%;
                font-size: 1rem;
                color: #fff;
                background: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
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
                background: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
                color: #AAA;
            }
            .gj-picker-bootstrap {
                border: 0;
                border-radius: 20px;
            }
            #tabla-facturas_wrapper .dt-buttons, #tabla-contratos_wrapper .dt-buttons,#tabla-planes_wrapper .dt-buttons,#tabla-mikrotiks_wrapper .dt-buttons,#tabla-nodos_wrapper .dt-buttons,#tabla-aps_wrapper .dt-buttons,#tabla-grupos_wrapper .dt-buttons,#tabla-bancos_wrapper .dt-buttons,#tabla-wifis_wrapper .dt-buttons,#table_sin_gestionar_wrapper .dt-buttons, #table_sin_gestionarG_wrapper .dt-buttons,#tabla-ventas-externas_wrapper .dt-buttons{
                float: right !important;
            }
            #tabla-contratos_length,#tabla-planes_length,#tabla-mikrotiks_length,#tabla-nodos_length,#tabla-aps_length,#tabla-grupos_length,#tabla-bancos_length,#tabla-wifis_length,#table_sin_gestionar_length,#table_sin_gestionarG_length,#tabla-ventas-externas_length{
                margin: 1% 0 !important;
            }
            #tabla-facturas_wrapper .dt-buttons button, #tabla-contratos_wrapper .dt-buttons button,#tabla-planes_wrapper .dt-buttons button,#tabla-mikrotiks_wrapper .dt-buttons button,#tabla-nodos_wrapper .dt-buttons button,#tabla-aps_wrapper .dt-buttons button,#tabla-grupos_wrapper .dt-buttons button,#tabla-bancos_wrapper .dt-buttons button,#tabla-wifis_wrapper .dt-buttons button,#table_sin_gestionar_wrapper .dt-buttons button,#table_sin_gestionarG_wrapper .dt-buttons button,#tabla-ventas-externas_wrapper .dt-buttons button{
                color: #fff !important;
                background-color: #00ce68 !important;
                border-color: #00ce68 !important;
            }
            #tabla-facturas_wrapper .dt-buttons button:hover, #tabla-contratos_wrapper .dt-buttons button:hover,#tabla-planes_wrapper .dt-buttons button:hover,#tabla-mikrotiks_wrapper .dt-buttons button:hover,#tabla-nodos_wrapper .dt-buttons button:hover,#tabla-aps_wrapper .dt-buttons button:hover,#tabla-grupos_wrapper .dt-buttons button:hover,#tabla-bancos_wrapper .dt-buttons button:hover,#tabla-wifis_wrapper .dt-buttons button:hover,#table_sin_gestionar_wrapper .dt-buttons button:hover,#table_sin_gestionarG_wrapper .dt-buttons button:hover,#tabla-ventas-externas_wrapper .dt-buttons button:hover{
                color: #fff !important;
                background-color: #218838 !important;
                border-color: #1e7e34 !important;
            }
            #tabla-facturas_wrapper .dt-buttons button:nth-child(2), #tabla-contratos_wrapper .dt-buttons button:nth-child(2),#tabla-planes_wrapper .dt-buttons button:nth-child(2),#tabla-mikrotiks_wrapper .dt-buttons button:nth-child(2),#tabla-nodos_wrapper .dt-buttons button:nth-child(2),#tabla-aps_wrapper .dt-buttons button:nth-child(2),#tabla-grupos_wrapper .dt-buttons button:nth-child(2),#tabla-bancos_wrapper .dt-buttons button:nth-child(2),#tabla-wifis_wrapper .dt-buttons button:nth-child(2),#table_sin_gestionar_wrapper .dt-buttons button:nth-child(2),#table_sin_gestionarG_wrapper .dt-buttons button:nth-child(2),#tabla-ventas-externas_wrapper .dt-buttons button:nth-child(2){
                color: #fff !important;
                background-color: #e65251 !important;
                border-color: #e65251 !important;
            }
            #tabla-facturas_wrapper .dt-buttons button:nth-child(2), #tabla-contratos_wrapper .dt-buttons button:nth-child(2):hover,#tabla-planes_wrapper .dt-buttons button:nth-child(2):hover,#tabla-mikrotiks_wrapper .dt-buttons button:nth-child(2):hover,#tabla-nodos_wrapper .dt-buttons button:nth-child(2):hover,#tabla-aps_wrapper .dt-buttons button:nth-child(2):hover,#tabla-grupos_wrapper .dt-buttons button:nth-child(2):hover,#tabla-bancos_wrapper .dt-buttons button:nth-child(2):hover,#tabla-wifis_wrapper .dt-buttons button:nth-child(2):hover,#table_sin_gestionar_wrapper .dt-buttons button:nth-child(2):hover,#table_sin_gestionarG_wrapper .dt-buttons button:nth-child(2):hover,#tabla-ventas-externas_wrapper .dt-buttons button:nth-child(2):hover{
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
        @if(Auth::user()->online === 0)
        @php
            Auth::logout();
            return Redirect::to('login');
        @endphp
        @endif

        <div id="contenedor_carga">
            <img id="carga" src="{{asset('images/gif-tuerca.gif')}}">
        </div>
        <div class="loader"></div>
        <div class="container-scroller">
            <!-- partial:partials/_navbar.html -->
            @include('layouts.includes.navbar')
            <!-- partial -->
            <div class="container-fluid page-body-wrapper">
                <!-- partial:partials/_sidebar.html -->
                <nav class="sidebar sidebar-offcanvas" id="sidebar">
                    <ul class="nav">
                        <li class="nav-item nav-profile">
                            <div class="nav-link" style="padding: 6% !important">
                                <div class="user-wrapper">
                                    <div class="profile-image">
                                        @if(Auth::user()->image)
                                        <img src="{{asset('images/Empresas/Empresa'.Auth::user()->empresa.'/usuarios/'.Auth::user()->image)}}" onerror="this.src='{{asset('images/no-user-image.png')}}'" alt="profile image">
                                        @else
                                        <img src="{{asset('images/no-user-image.png')}}" alt="profile image">
                                        @endif
                                    </div>
                                    <div class="text-wrapper">
                                        <p style="text-transform:capitalize;" class="profile-name">{{Auth::user()->nombres}}</p>
                                        @if(Auth::user()->empresa())
                                        <input type="hidden" value="{{Auth::user()->empresa()->precision}}" id="precision">
                                        <input type="hidden" value="{{Auth::user()->empresa()->sep_dec}}" id="sep_dec">
                                        <input type="hidden" value="{{Auth::user()->empresa()->sep_dec=='.'?',':'.'}}" id="sep_miles">
                                        @endif
                                        <div>
                                            <small class="designation">{{Auth::user()->roles->rol}}</small>
                                            <span class="status-indicator online"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="nav-item" id="">
                            <a  class="nav-link"  href="{{route('home')}}">
                                <i class="menu-icon fas fa-home"></i>
                                <span class="menu-title">Inicio</span>
                            </a>
                        </li>
                        @include('layouts.includes.menu')
                    </ul>
                </nav>
                <!-- partial -->
                <div class="main-panel">
                    <div class="content-wrapper">
                        <div class="grid-margin stretch-card">
                            <div class="card">
                                <div class="body-card">
                                    <div class="row " style="padding: 2%;">
                                        @php
                                            $col_md1=8;
                                            $col_md2=4;
                                            if(isset($invert)){ $col_md1=4; $col_md2=8; }
                                            if(isset($middel)){ $col_md1=6; $col_md2=6; }
                                            if(isset($precice)){ $col_md1=5; $col_md2=7; }
                                            if(isset($minus_dere)){ $col_md1=2; $col_md2=10; }
                                            if(isset($minus_izq)){ $col_md1=10; $col_md2=2; }
                                            if(isset($full)){ $col_md1=12; $col_md2=0; }
                                            if(isset($invertfalse)){ $col_md1=8; $col_md2=4; }
                                        @endphp
                                        <div class="col-md-{{$col_md1}}" style="text-align: left;">
                                            <h1 id="titulo"><i class="{{$icon}}"></i> {{isset($title_sub)?$title_sub:$title}}</h1>
                                        </div>
                                        <div class="col-md-{{$col_md2}}" style="text-align: right;">
                                            @yield('boton')
                                        </div>
                                    </div>
                                    <!-- msj cconfirmacion CRM -->
                                    @if(Session::has('novence'))
                                        <div class="alert alert-warning alert-dismissible fade show" role="alert" style="background-color:#FFFC33;">
                                            <strong>{{Session::get('novence')}}</strong>
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    @endif
                                    <!-- fin msj-->
                                    <!-- Funcion para generar el imprimir -->
                                    @if(Session::has('print'))
                                        @if(Session::get('print'))
                                            <input type="hidden" id="imprimir" value="{{route('facturas.imprimir', Session::get('print'))}}">
                                        @endif
                                    @endif

                                    @yield('content')
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Modal Small-->
                    <div class="modal fade" id="modal-small" tabindex="-1" role="dialog" aria-labelledby="modal-small-CenterTitle" aria-hidden="true">
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
                    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
                        <source src="{{asset('images/alerta.mp3')}}">
                    </audio>
                    <div class="modal fade" id="modalNotificacionP" role="dialog">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header p-0">
                                    <center><img src="{{asset('images/Empresas/Empresa1/logo.png')}}" style="width:15%" class="m-2"></center>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin: -10px;">
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
                        <source src="{{asset('images/alerta.mp3')}}">
                    </audio>
                    <div class="modal fade" id="modalNotificacionW" role="dialog">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header p-0">
                                    <center><img src="{{asset('images/Empresas/Empresa1/logo.png')}}" style="width:15%" class="m-2"></center>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin: -10px;">
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
                        <source src="{{asset('images/alerta.mp3')}}">
                    </audio>
                    <div class="modal fade" id="modalNotificacionR" role="dialog">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header p-0">
                                    <center><img src="{{asset('images/Empresas/Empresa1/logo.png')}}" style="width:15%" class="m-2"></center>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin: -10px;">
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
                        <source src="{{asset('images/alerta.mp3')}}">
                    </audio>
                    <div class="modal fade" id="modalNotificacionT" role="dialog">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header p-0">
                                    <center><img src="{{asset('images/Empresas/Empresa1/logo.png')}}" style="width:15%" class="m-2"></center>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin: -10px;">
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
                        <source src="{{asset('images/alerta.mp3')}}">
                    </audio>
                    <div class="modal fade" id="modalNotificacion" role="dialog">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header p-0">
                                    <center><img src="{{asset('images/Empresas/Empresa1/logo.png')}}" style="width:15%" class="m-2"></center>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="margin: -10px;">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body" id="modal-bodyc">

                                </div>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" id="pageLength" value="{{Auth::user()->rol > 1 ? Auth::user()->empresa()->pageLength : '25'}}">

                    <footer class="footer">
                        <div class="container-fluid clearfix">
                            <span class="text-muted d-block text-center text-sm-left d-sm-inline-block">
                                Todos los Derechos Reservados © @if(Auth::user()->rol > 1) {{ Auth::user()->empresa()->nombre }} @endif<a href="#" target="_blank"></a>
                            </span>
                            <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center">
                                Realizado por: <a href="https://networkingenieria.com/" target="_blank">Network Ingeniería S.A.S</a> <i class="mdi mdi-heart text-danger"></i>
                            </span>
                        </div>
                    </footer>
                </div>
                <div class="whatsapp text-left">
                    <a href="https://api.whatsapp.com/send?phone=+573053652834&text=Hola Integra Colombia, necesito soporte para la empresa {{Auth::user()->rol > 1 ? Auth::user()->empresa()->nombre:''}}" target="_blank" title="Soporte vía Whatsapp">
                        <img src="{{asset('images/whatsapp.png')}}" alt="WhatsApp" />
                    </a>
                </div>
            </div>
        </div>

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
        {{-- <script src="{{asset('vendors/js/vendor.bundle.addons.js')}}"></script> --}}
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
        <script src="{{asset('js/function.js')}}?v={{ Auth::user()->rol == 1 ? '1' : Auth::user()->empresa()->cache }}"></script>
        <script src="{{asset('js/custom.js')}}?v={{ Auth::user()->rol == 1 ? '1' : Auth::user()->empresa()->cache }}"></script>
        <script src="{{asset('js/dian.js')}}?v={{ Auth::user()->rol == 1 ? '1' : Auth::user()->empresa()->cache }}"></script>
        <script type="text/javascript" src='https://maps.google.com/maps/api/js?sensor=false&libraries=places'></script>
        <script type="text/javascript" src="{{asset('js/locationpicker.jquery.js')}}"></script>

        <script src="//cdn.datatables.net/plug-ins/1.12.1/sorting/ip-address.js"></script>

        <script src="https://cdn.socket.io/4.3.1/socket.io.min.js"></script>
        <script src="{{asset('gritter/js/jquery.gritter.min.js')}}"></script>
        <div class="d-none" id="audioContainer"></div>
        <script type="text/javascript">
            const audioElement = new Audio('{{asset("images/alerta.mp3")}}');
            const audioElementA = new Audio('{{asset("images/asig.mp3")}}');
            var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            @php
                use Illuminate\Support\Facades\DB;
                use Illuminate\Support\Facades\Auth;
                $instancia = DB::table("instancia")
                                ->first();

            @endphp

            @if(!is_null($instancia??null) && !empty($instancia??""))

                const socketSerVER = io.connect('https://api.whatsive.com:{{$instancia->port}}', {
                    'reconnection': true,
                    'reconnectionDelay': 2000,
                    'reconnectionDelayMax': 2000,
                    'reconnectionAttempts': 5
                });
                socketSerVER.on('changeoperador', function(data) {
                    if (data.tecnico == "{{$user = Auth::user()->id}}") {
                        audioElementA.play();
                        $.gritter.add({
                            title: 'CHAT ASIGNADO',
                            text: 'El chat  <b>' + data.cliente + '</b> te fué asignado.',
                            sticky: true,
                            time: '',
                            class_name: 'alerta-whatsapp'
                        });
                    }
                })

                socketSerVER.on('newmessagewat', function(datos) {
                    if(datos?.author!=null){

                    }else{

                        @php

                            $permisoWppNoti = DB::table("permisos_usuarios")
                                            ->where('id_usuario',Auth::user()->id)
                                            ->where('id_permiso',857)
                                            ->first();
                        @endphp

                         @if(!is_null($permisoWppNoti))
                        audioElement.play();
                        @endif

                        let typechats = {
                            "video": "  <span class = 'fas fa-video fa-lg' ></span> Video",
                            "ptt": "  <span class = 'fas fa-microphone fa-lg' ></span> Audio",
                            "audio": "  <span class = 'fas fa-microphone fa-lg' ></span> Audio",
                            "image": "  <span class = 'fas fa-image fa-lg' ></span> Imagen",
                            "sticker": "  <span class = 'fas fa-file fa-lg' ></span> Sticker",
                            "document": "  <span class = 'fas fa-file-archive fa-lg' ></span> Archivo",
                            "location": "  <span class = 'fas fa-map fa-lg' ></span> Ubicacion",
                            "call_log": "  <span style = 'color:red' class = 'fa fa-phone fa-lg' ></span> Llamada perdida ",
                            "e2e_notification" :"Respuesta automatica",
                            "ciphertext" : "  <span class = 'fas fa-microphone fa-lg' ></span> Audio",
                            "revoked" : "<span class = 'fa fa-ban fa-lg' ></span> Elimino el mensaje",
                            "vcard" : "<span class = 'fa fa-user fa-lg' ></span> Contacto",
                            "notification_template" : "<span class = 'fa fa-clock-o fa-lg' ></span> Aviso whatsapp",
                            "gp2" : "<span class = 'fa fa-clock-o fa-lg' ></span> Aviso whatsapp",
                        };
                        if(!datos.isStatus && (datos.type!="e2e_notification" && datos.type!="notification_template" && datos.type!="g2p")){
                            if(datos.type != "chat"){
                                datos._data.body = typechats[datos.type];
                            }
                            if(!datos?.picurl){
                                datos.picurl = "https://ramenparados.com/wp-content/uploads/2019/03/no-avatar-png-8.png";
                            }
                            nombre = datos.to.replace("@c.us","");
                            if(datos?.contact){
                                if(datos.contact?.name){
                                    datos._data.notifyName = datos.contact.name;
                                }else{
                                    datos._data.notifyName = datos.contact.pushname;
                                }
                            }

                            @if(!is_null($permisoWppNoti))
                                alertawhat(
                                    {
                                        nombre:datos._data.notifyName,
                                        mensaje:datos._data.body,
                                        avatar:datos.picurl
                                    }
                                );
                            @endif
                        }
                    }
                })
                socketSerVER.on('closesion', function(data) {
                    swal({
                        title: "Se cerro la sesion de Whatsapp ",
                        text: "Vuelva a conectar el dispositivo para poder seguir usando Whatsapp",
                        type: 'error',
                        showCancelButton: false,
                        confirmButtonColor: '#00ce68',
                        cancelButtonColor: '#d33',
                        confirmButtonText: confirmar,
                        cancelButtonText: 'No',
                    }).then((result) => {
                        location.reload();
                    });
                });
                socketSerVER.on('disconnected', function(data) {
                    swal({
                        title: "Se cerro la sesion de Whatsapp ",
                        text: "Vuelva a conectar el dispositivo para poder seguir usando Whatsapp",
                        type: 'error',
                        showCancelButton: false,
                        confirmButtonColor: '#00ce68',
                        cancelButtonColor: '#d33',
                        confirmButtonText: "confirmar",
                        cancelButtonText: 'No',
                    }).then((result) => {
                        location.reload();
                    });
                });

            @endif

            function alertawhat(data) {
                $.gritter.add({
                    title: data.nombre,
                    text: data.mensaje,
                    image: '<img src="' + data.avatar + '" >',
                    sticky: false,
                    time: 7000,
                    class_name: 'alerta-whatsapp'
                });
            }

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
