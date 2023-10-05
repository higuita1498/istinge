@extends('layouts.app')

@section('style')
    <style>
        .notice {
            padding: 15px;
            background-color: #fafafa;
            border-left: 6px solid #7f7f84;
            margin-bottom: 10px;
            -webkit-box-shadow: 0 5px 8px -6px rgba(0,0,0,.2);
            -moz-box-shadow: 0 5px 8px -6px rgba(0,0,0,.2);
            box-shadow: 0 0 0 0 rgba(0,0,0,0);
        }
        .notice-sm {
            padding: 10px;
            font-size: 80%;
        }
        .notice-lg {
            padding: 35px;
            font-size: large;
        }
        .notice-success {
            border-color: #80D651;
        }
        .notice-success>strong {
            color: #80D651;
        }
        .notice-info {
            border-color: #267eb5;
        }
        .notice-info>strong {
            color: #45ABCD;
        }
        .notice-warning {
            border-color: #FEAF20;
        }
        .notice-warning>strong {
            color: #FEAF20;
        }
        .notice-danger {
            border-color: #d73814;
        }
        .notice-danger>strong {
            color: #d73814;
        }
        .card-counter{
            box-shadow: 2px 2px 10px #797979;
            margin: 5px;
            padding: 20px 10px;
            background-color: #fff;
            height: 100px;
            border-radius: 5px;
            transition: .1s linear all;
        }
        .card-counter.primary{
            background-color: #007bff;
            color: #FFF;
        }
        .card-counter.danger{
            background-color: #ef5350;
            color: #FFF;
        }
        .card-counter.success{
            background-color: #66bb6a;
            color: #FFF;
        }
        .card-counter.info{
            background-color: #26c6da;
            color: #FFF;
        }
        .card-counter i{
            font-size: 5em;
            opacity: 0.2;
        }
        .card-counter .count-numbers{
            position: absolute;
            right: 35px;
            top: 20px;
            font-size: 32px;
            display: block;
        }
        .card-counter .count-name{
            position: absolute;
            right: 35px;
            top: 65px;
            font-style: italic;
            text-transform: capitalize;
            opacity: 0.5;
            display: block;
            font-size: 18px;
        }
        hr {
            margin-top: 1rem;
            margin-bottom: 1rem;
            border: 0;
            border-top: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
        }
    </style>
@endsection

@section('content')
    @if(auth()->user()->modo_lectura())
        <div class="alert alert-warning text-left" role="alert">
            <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
           <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
        </div>
    @endif

    @if(isset($_SESSION['permisos']['113']) && Auth::user()->rol != 8)
        <div class="row card-description">
            @if(auth()->user()->modo_lectura())
            @else
            <div>
                 <form action="{{ route('subir-archivo') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <p style="color:white;padding-top:25px;text-align:center;padding-left:50px;font-size:16px;background:#57c7d4;">Señor usuario recuerde que su factura vence el día 10 de octubre de 2023 por favor adjunte su pago aquí para evitar ser suspendido el día 11 de octubre.</p>

                    <div class="form-group">
                        <label for="archivo">Seleccionar archivo:</label>
                        <input type="file" name="archivo" id="archivo" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Enviar Archivo</button>
                </form>
            </div>

            <div class="col-md-12">
                <div class="card-body">
                    <div class="notice notice-info">
                        <h4 class="float-left">ATAJOS</h4>
                        <a class="btn btn-sm btn-none float-right" data-toggle="collapse" href="#welcomeAtajos" role="button" aria-expanded="false" aria-controls="welcomeAtajos" style="color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}} !important;">
                            <i class="fas fa-angle-double-up"></i>
                        </a>
                        <hr class="mt-4">
                    </div>
                    <div class="collapse show" id="welcomeAtajos">
                        <div class="row">
                            @if(isset($_SESSION['permisos']['2']))
                            <div class="col-md-2 offset-md-1">
                                <a href="{{route('contactos.create')}}">
                                <div class="card-counter success">
                                    <i class="fas fa-users"></i>
                                    <span class="count-numbers">Crear</span>
                                    <span class="count-name">Cliente</span>
                                </div>
                                </a>
                            </div>
                            @endif
                            @if(isset($_SESSION['permisos']['411']))
                            <div class="col-md-2">
                                <a href="{{route('contratos.create')}}">
                                <div class="card-counter success">
                                    <i class="fas fa-file-contract"></i>
                                    <span class="count-numbers">Crear</span>
                                    <span class="count-name">Contrato</span>
                                </div>
                                </a>
                            </div>
                            @endif
                            @if(isset($_SESSION['permisos']['202']))
                            <div class="col-md-2">
                                <a href="{{route('radicados.create')}}">
                                <div class="card-counter success">
                                    <i class="fas fa-ticket-alt"></i>
                                    <span class="count-numbers">Crear</span>
                                    <span class="count-name">Radicado</span>
                                </div>
                                </a>
                            </div>
                            @endif
                            @if(isset($_SESSION['permisos']['42']))
                            <div class="col-md-2">
                                <a href="{{route('facturas.create')}}">
                                <div class="card-counter success">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                    <span class="count-numbers">Crear</span>
                                    <span class="count-name">Factura</span>
                                </div>
                                </a>
                            </div>
                            @endif
                            @if(isset($_SESSION['permisos']['420']))
                            <div class="col-md-2">
                                <a href="{{route('facturas.create-electronica')}}">
                                <div class="card-counter success">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                    <span class="count-numbers">Crear</span>
                                    <span class="count-name" style="right: 20px;font-size: 13px;top: 70px;">Factura Electrónica</span>
                                </div>
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="col-md-12">
                <div class="card-body">
                    <div class="notice notice-info">
                        <h4 class="float-left">CONTRATOS</h4>
                        <a class="btn btn-sm btn-none float-right" data-toggle="collapse" href="#welcomeContratos" role="button" aria-expanded="false" aria-controls="welcomeContratos" style="color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}} !important;">
                            <i class="fas fa-angle-double-up"></i>
                        </a>
                        <hr class="mt-4">
                    </div>
                    <div class="collapse show" id="welcomeContratos">
                        <div class="row">
                            <div class="col-md-3 offset-md-1">
                                <a href="{{ isset($_SESSION['permisos']['411']) ? route('contratos.index') : 'javascript:void' }}">
                    			<div class="card-counter primary">
                                    <i class="fas fa-file-contract"></i>
                                    <span class="count-numbers">{{ $contra_ena + $contra_disa }}</span>
                    			    <span class="count-name">Registrados</span>
                    			</div>
                    			</a>
                    		</div>
                    		<div class="col-md-3">
                                <a href="{{ isset($_SESSION['permisos']['411']) ? route('contratos.enabled') : 'javascript:void' }}">
                    			<div class="card-counter success">
                                    <i class="fas fa-file-contract"></i>
                                    <span class="count-numbers">{{ $contra_ena }}</span>
                    			    <span class="count-name">Habilitados</span>
                    			</div>
                    			</a>
                    		</div>
                    		<div class="col-md-3">
                                <a href="{{ isset($_SESSION['permisos']['411']) ? route('contratos.disabled') : 'javascript:void' }}">
                    			<div class="card-counter danger">
                                    <i class="fas fa-file-contract"></i>
                                    <span class="count-numbers">{{ $contra_disa }}</span>
                    			    <span class="count-name">Deshabilitados</span>
                    			</div>
                    			</a>
                    		</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card-body">
                    <div class="notice notice-info">
                        <h4 class="float-left">FACTURACION</h4>
                        <a class="btn btn-sm btn-none float-right" data-toggle="collapse" href="#welcomeFacturacion" role="button" aria-expanded="false" aria-controls="welcomeFacturacion" style="color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}} !important;">
                            <i class="fas fa-angle-double-up"></i>
                        </a>
                        <hr class="mt-4">
                    </div>
                    <div class="collapse show" id="welcomeFacturacion">
                        <div class="row">
                            <div class="col-md-3 offset-md-1">
                    		    <a href="{{ isset($_SESSION['permisos']['40']) ? route('facturas.index') : 'javascript:void' }}">
                    			<div class="card-counter primary">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                    <span class="count-numbers">{{ $factura }}</span>
                    			    <span class="count-name">Generados</span>
                    			</div>
                    			</a>
                    		</div>
                    		<div class="col-md-3">
                                <a href="{{ isset($_SESSION['permisos']['40']) ? route('facturas.tipo', 'cerradas') : 'javascript:void' }}">
                    			<div class="card-counter success">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                    <span class="count-numbers">{{ $factura_cerrada }}</span>
                    			    <span class="count-name">Cerradas</span>
                    			</div>
                    			</a>
                    		</div>
                    		<div class="col-md-3">
                                <a href="{{ isset($_SESSION['permisos']['40']) ? route('facturas.tipo', 'abiertas') : 'javascript:void' }}">
                    			<div class="card-counter danger">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                    <span class="count-numbers">{{ $factura_abierta }}</span>
                    			    <span class="count-name">Abiertas</span>
                    			</div>
                    			</a>
                    		</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card-body">
                    <div class="notice notice-info">
                        <h4 class="float-left">RADICADOS</h4>
                        <a class="btn btn-sm btn-none float-right" data-toggle="collapse" href="#welcomeRadicados" role="button" aria-expanded="false" aria-controls="welcomeRadicados" style="color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}} !important;">
                            <i class="fas fa-angle-double-up"></i>
                        </a>
                        <hr class="mt-4">
                    </div>
                    <div class="collapse show" id="welcomeRadicados">
                        <div class="row">
                            <div class="col-md-3 offset-md-1">
                                <a href="{{ isset($_SESSION['permisos']['201']) ? route('radicados.index') : 'javascript:void' }}">
                    			<div class="card-counter primary">
                                    <i class="fas fa-ticket-alt"></i>
                                    <span class="count-numbers">{{ $radicados }}</span>
                    			    <span class="count-name">Generados</span>
                    			</div>
                    			</a>
                    		</div>
                    		<div class="col-md-3">
                                <a href="{{ isset($_SESSION['permisos']['201']) ? route('radicados.index') : 'javascript:void' }}">
                    			<div class="card-counter success">
                                    <i class="fas fa-ticket-alt"></i>
                                    <span class="count-numbers">{{ $radicados_solventado }}</span>
                    			    <span class="count-name">Solventados</span>
                    			</div>
                    			</a>
                    		</div>
                    		<div class="col-md-3">
                                <a href="{{ isset($_SESSION['permisos']['201']) ? route('radicados.index') : 'javascript:void' }}">
                    			<div class="card-counter danger">
                                    <i class="fas fa-ticket-alt"></i>
                                    <span class="count-numbers">{{ $radicados_pendiente }}</span>
                    			    <span class="count-name">Pendientes</span>
                    			</div>
                    			</a>
                    		</div>
                        </div>
                    </div>
                </div>
            </div>
    	</div>
    @endif

    @if(Auth::user()->rol == 8)
        <div class="row card-description">
    	    <form action="https://checkout.wompi.co/p/" method="GET" id="form-wompi" class="d-none">
    	        <input type="hidden" name="public-key" value="{{env('WOMPI_KEY')}}" />
    	        <input type="hidden" name="currency" value="COP" />
    	        <input type="hidden" name="amount-in-cents" id="amount-in-cents" />
    	        <input type="hidden" name="reference" value="{{str_replace(' ', '_', Auth::user()->nombres)}}<?php echo '_IST_'.rand();?>" />
    	        <input type="hidden" name="redirect-url" value="https://istingenieria.online/RecargaWompi" />
    	        <button class="btn btn-success" type="submit" disabled>Pagar con Wompi</button>
    	    </form>

    	    <div class="col-md-4 offset-md-4" style="text-align:center;">
    	        <div class="contact-form">
    	            <h4>RECARGA SALDO CON WOMPI</h4>
    	            <input type="number" min="1" class="form-control my-3" id="recarga" value="0">
    	            <button class="btn btn-success" type="submit" onclick="confirmarp('form-wompi');" disabled>RECARGAR</button>
    	        </div>
    	    </div>
    	</div>

    	<script>
    	    function confirmarp(form, mensaje="Lo vamos a redirigir a la pasarela de pago WOMPI para realizar la recarga", submensaje='¿Desea continuar?', confirmar='Si'){
                if($("#buyerFullName").val() != ''){
                    swal({
                        title: mensaje,
                        text: submensaje,
                        type: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#00ce68',
                        cancelButtonColor: '#d33',
                        confirmButtonText: confirmar,
                        cancelButtonText: 'No',
                    }).then((result) => {
                        if (result.value) {
                            /*cargando(true);

                            $.ajax({
                                url: 'bk_equipos.php',
                                type: 'POST',
                                data: infoEmpleado,
                                contentType: false,
                                processData: false,
                                success: function(data) {
                                    data = JSON.parse(data);

                                    if (data['success'] == true) {
                                        var monto_pago = $("#recarga").val();
                                        $("#amount-in-cents").val(monto_pago+'00');
                                        document.getElementById(form).submit();
                                        cargando(true);
                                    }
                                }
                            });
                            */
                        }
                    });
                }else{
                    swal({
                        title: 'Debe llenar la información solicitada',
                        text: submensaje,
                        type: 'warning',
                        showCancelButton: true,
                        showConfirmButton: false,
                        cancelButtonColor: '#00ce68',
                        cancelButtonText: 'Aceptar',
                    })
                }
            }
    	    </script>
    @endif

	<input type="hidden" id="simbolo" value="$">
@endsection
