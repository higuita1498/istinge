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
    @if(isset($_SESSION['permisos']['113']) && Auth::user()->rol != 8)
        <div class="row card-description">
            <div class="col-md-12">
                <div class="card-body">
                    <div class="notice notice-info">
                        <h4>ATAJOS</h4>
                        <hr>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{route('contactos.create')}}">
                            <div class="card-counter success">
                                <i class="fas fa-users"></i>
                                <span class="count-numbers">Crear</span>
                                <span class="count-name">Cliente</span>
                            </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{route('contratos.create')}}">
                            <div class="card-counter success">
                                <i class="fas fa-file-contract"></i>
                                <span class="count-numbers">Crear</span>
                                <span class="count-name">Contrato</span>
                            </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{route('radicados.create')}}">
                            <div class="card-counter success">
                                <i class="fas fa-ticket-alt"></i>
                                <span class="count-numbers">Crear</span>
                                <span class="count-name">Radicado</span>
                            </div>
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{route('facturas.create')}}">
                            <div class="card-counter success">
                                <i class="fas fa-file-invoice-dollar"></i>
                                <span class="count-numbers">Crear</span>
                                <span class="count-name">Factura</span>
                            </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <div class="card-body">
                    <div class="notice notice-info">
                        <h4>CONTRATOS</h4>
                        <hr>
                    </div>
                    <div class="row">
                        <div class="col-md-3 offset-md-1">
                            <a href="{{route('contratos.index')}}">
                			<div class="card-counter primary">
                                <i class="fas fa-file-contract"></i>
                                <span class="count-numbers">{{ $contra_ena + $contra_disa }}</span>
                			    <span class="count-name">Registrados</span>
                			</div>
                			</a>
                		</div>
                		<div class="col-md-3">
                		    <a href="{{route('contratos.enabled')}}">
                			<div class="card-counter success">
                                <i class="fas fa-file-contract"></i>
                                <span class="count-numbers">{{ $contra_ena }}</span>
                			    <span class="count-name">Habilitados</span>
                			</div>
                			</a>
                		</div>
                		<div class="col-md-3">
                		    <a href="{{route('contratos.disabled')}}">
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
            
            <div class="col-md-12">
                <div class="card-body">
                    <div class="notice notice-info">
                        <h4>FACTURACIÓN</h4>
                        <hr>
                    </div>
                    <div class="row">
                        <div class="col-md-3 offset-md-1">
                		    <a href="{{route('facturas.index')}}">
                			<div class="card-counter primary">
                                <i class="fas fa-file-invoice-dollar"></i>
                                <span class="count-numbers">{{ $factura }}</span>
                			    <span class="count-name">Generados</span>
                			</div>
                			</a>
                		</div>
                		<div class="col-md-3">
                		    <a href="{{ route('facturas.tipo', 'cerradas') }}">
                			<div class="card-counter success">
                                <i class="fas fa-file-invoice-dollar"></i>
                                <span class="count-numbers">{{ $factura_cerrada }}</span>
                			    <span class="count-name">Cerradas</span>
                			</div>
                			</a>
                		</div>
                		<div class="col-md-3">
                		    <a href="{{ route('facturas.tipo', 'abiertas') }}">
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
            
            <div class="col-md-12">
                <div class="card-body">
                    <div class="notice notice-info">
                        <h4>RADICADOS</h4>
                        <hr>
                    </div>
                    <div class="row">
                        <div class="col-md-3 offset-md-1">
                			<a href="{{route('radicados.index')}}">
                			<div class="card-counter primary">
                                <i class="fas fa-ticket-alt"></i>
                                <span class="count-numbers">{{ $radicados }}</span>
                			    <span class="count-name">Generados</span>
                			</div>
                			</a>
                		</div>
                		<div class="col-md-3">
                			<a href="{{route('radicados.index')}}">
                			<div class="card-counter success">
                                <i class="fas fa-ticket-alt"></i>
                                <span class="count-numbers">{{ $radicados_solventado }}</span>
                			    <span class="count-name">Solventados</span>
                			</div>
                			</a>
                		</div>
                		<div class="col-md-3">
                			<a href="{{route('radicados.index')}}">
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
