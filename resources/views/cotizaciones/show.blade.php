@extends('layouts.app')

@section('boton')
    @if(auth()->user()->modo_lectura())
        <div class="alert alert-warning text-left" role="alert">
            <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
           <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
        </div>
    @else
        <a href="{{route('cotizaciones.edit',$factura->cot_nro)}}" class="btn btn-outline-primary btn-sm "title="Imprimir" target="_blank"><i class="fas fa-edit"></i> Editar</a>
        @if($factura->estatus!=2)
            <a href="{{route('cotizaciones.imprimir.nombre',['id' => $factura->cot_nro, 'name'=> 'Cotizacion No. '.$factura->cot_nro.'.pdf'])}}" class="btn btn-outline-primary btn-sm "title="Imprimir" target="_blank"><i class="fas fa-print"></i> Imprimir</a>
            <a href="{{route('cotizaciones.facturar',$factura->cot_nro)}}" class="btn btn-outline-primary btn-sm "title="Convertir a factura de venta"><i class=""></i> Convertir a factura de venta</a>
            <a href="{{route('cotizaciones.remision',$factura->cot_nro)}}" class="btn btn-outline-primary btn-sm "title="Convertir a remisión"><i class=""></i> Convertir a remisión</a>
            <a href="{{route('cotizaciones.enviar',$factura->cot_nro)}}" class="btn btn-outline-primary btn-sm "title="Enviar al Cliente"><i class="far fa-envelope"></i> Enviar por Correo</a>
        @endif
    @endif
@endsection   

@section('content')
    @if(Session::has('success') || Session::has('error'))
        @if(Session::has('success'))
        <div class="alert alert-success">
            {{Session::get('success')}}
        </div>
        @endif
        @if(Session::has('error'))
        <div class="alert alert-danger">
            {{Session::get('error')}}
        </div>
        @endif

        <script type="text/javascript">
            setTimeout(function(){
                $('.alert').hide();
                $('.active_table').attr('class', ' ');
            }, 5000);
        </script>
    @endif

    <div class="paper" style="margin-top: 2%;">
        @if($factura->estatus())
        <div class="ribbon {{$factura->estatus('si')?'Abierta':''}}"><span>{{$factura->estatus()}}</span></div>
        @endif
        <!-- Membrete -->
        <div class="row">
            <div class="col-md-4 text-center">
                <img class="img-responsive" src="{{asset('images/Empresas/Empresa'.Auth::user()->empresa.'/'.Auth::user()->empresa()->logo)}}" alt="" width="50%">
            </div>
            <div class="col-md-4 text-center padding1">
                <h4>{{Auth::user()->empresa()->nombre}}</h4>
                <p>{{Auth::user()->empresa()->tip_iden('mini')}} {{Auth::user()->empresa()->nit}} <br> {{Auth::user()->empresa()->email}}</p>
            </div>
            <div class="col-md-4 text-center padding1" >
                <h4><b class="text-primary">No. </b> {{$factura->cot_nro}}</h4>
            </div>
        </div>

        <!--Cliente-->
        <div class="row" style="margin-top: 3%; padding: 2% 7%;">
            <div class="col-md-12 fact-table">
                <table class="table table-striped cliente">
                    <tbody>
                        <tr>
                            <td width="10%">Cliente</td>
                            <th width="60%">@if($factura->cliente) <a href="{{route('contactos.show',$factura->cliente()->id)}}" target="_blanck">{{$factura->cliente()->nombre}} {{$factura->cliente()->apellidos()}}</a> @else {{$factura->cliente()->nombre}} {{$factura->cliente()->apellidos()}} @endif</th>
                            <td width="10%">Creación</td>
                            <th width="10%">{{date('d/m/Y', strtotime($factura->fecha))}}</th>
                        </tr>
                        <tr>
                            <td>@if($factura->cliente){{$factura->cliente()->tip_iden('true')}}@else Identificación @endif</td>
                            <th>@if($factura->cliente){{$factura->cliente()->nit}}@endif</th>
                            <td>Vencimiento</td>
                            <th>{{date('d/m/Y', strtotime($factura->vencimiento))}}</th>
                        </tr>
                        <tr>
                            <td>Teléfono</td>
                            <th>@if($factura->cliente){{$factura->cliente()->celular}} @else {{$factura->cliente()->telefono}} @endif</th>
                            <td>Correo Electrónico</td>
                            <th>{{$factura->cliente()->email}}</th>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
			<button type="button" class="close" data-dismiss="alert">×</button>
			<strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
		</div>

        <!-- Desgloce -->
        <div class="row" style="padding: 2% 6%;">
            <div class="col-md-12 fact-table">
                <table class="table table-striped table-sm desgloce"  width="100%">
                    <thead>
                        <tr>
                            <th>Ítem</th>
                            <th width="13%">Referencia</th>
                            <th width="12%">Precio</th>
                            <th width="7%">Desc %</th>
                            <th width="12%">Impuesto</th>
                            <th width="13%">Descripción</th>
                            <th width="7%">Cantidad</th>
                            <th width="10%">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                            <tr>
                                <td width="13%">
                                    @if($item->tipo_inventario==1)
                                        <a href="{{route('inventario.show',$item->producto)}}" target="_blanck">{{$item->producto()}}</a>
                                    @else
                                        @foreach($item->producto(true) as $datos)
                                            {{$datos[0]}} {{$datos[0]?':':''}} <b>{{$datos[1]}}</b><br>
                                        @endforeach
                                    @endif
                                </td>
                                <td>{{$item->ref}}</td>
                                <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($item->precio)}}</td>
                                <td>{{$item->desc?$item->desc:0}}%</td>
                                <td>{{$item->impuesto()}}</td>
                                <td><div class="elipsis-short" style="width:135px;"><a title="{{$item->descripcion}}">{{$item->descripcion}}</a></div></td>
                                <td>{{round($item->cant,0)}}</td>
                                <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($item->total())}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Totales -->
        <div class="row" style="margin-top: 2%; padding: 2% 6%;">
            <div class="col-md-4 text-center">
                <div class="align-bottom" style="width: 100%; border-top: 1px solid #ccc; margin-right: 10%;margin-top: 20%;">
                    <p style="font-weight: 500 !important;"> ELABORADO POR: {{$factura->vendedor()}}</p>
                </div>
            </div>
            <div class="col-md-4 offset-md-4">
                <table class="text-right widthtotal" id="totales">
                    <tr>
                        <td width="40%">Subtotal</td>
                        <td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($factura->total()->subtotal)}}</td>
                    </tr>
                    <tr>
                        <td>Descuento</td><td id="descuento">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($factura->total()->descuento)}}</td>
                    </tr>
                    <tr>
                        <td>Subtotal</td>
                        <td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($factura->total()->subsub)}}</td>
                    </tr>
                    @if($factura->total()->imp)
                        @foreach($factura->total()->imp as $imp)
                            @if(isset($imp->total))
                                <tr>
                                    <td>{{$imp->nombre}} ({{$imp->porcentaje}}%)</td>
                                    <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($imp->total)}}</td>
                                </tr>
                            @endif
                        @endforeach
                    @endif
                </table>

                <hr>

                <table class="text-right widthtotal" style="font-size: 24px !important;">
                    <tr>
                        <td width="40%">TOTAL</td>
                        <td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($factura->total()->total)}}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Terminos y Condiciones -->
        <div class="row" style="margin-top: 2%; padding: 2% 6%; min-height: 180px;">
            <div class="col-md-4 offset-md-8">
                <label class="form-label" style="font-weight: 500 !important;">Notas</label>
                <p>{{$factura->notas}}</p>
            </div>
        </div>
    </div>

    <div class="row" style="padding: 0 3.5% 3.5% 3.5%;">
        <div class="col-md-7" style="box-shadow: 1px 2px 4px 0 rgba(0,0,0,0.15);background-color: #fff; padding:2% !important;">
            <h6>Observaciones</h6>
            <p class="text-justify">
                {{$factura->observaciones}}
            </p>
        </div>
        <div class="col-md-4 offset-md-1" style="box-shadow: 1px 2px 4px 0 rgba(0,0,0,0.15);background-color: #fff; padding:2% !important;">
            <table class="table table-striped cliente">
                <tbody>
                    <tr>
                        <td>Vendedor</td>
                        <th class="text-right">{{$factura->vendedor()}}</th>
                    </tr>
                    <tr>
                        <td>Lista de precios</td>
                        <th class="text-right">{{$factura->lista_precios()}}</th>
                    </tr>
                    <tr>
                        <td>Bodega</td>
                        <th class="text-right">{{$factura->bodega()}}</th>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection