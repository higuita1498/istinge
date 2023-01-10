@extends('layouts.app')

@section('boton')

@endsection   

@section('content')
    <style>
        .paper:before {
            top: 0px;
            right: 0px;
            border-color: #f9fafd #f9f9f9 #eaedf7 #eaedf7;
        }
        td .elipsis-short-item {
            width: 300px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
    </style>
    
    @if(auth()->user()->modo_lectura())
        <div class="alert alert-warning text-left" role="alert">
            <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
           <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
        </div>
    @else
        @if(auth()->user()->rol <> 8)
            <div class="row" style="margin: -2% 0 0 2%;">
                <div class="col-md-12">
                        @if(Auth::user()->empresa()->form_fe == 1 && $factura->emitida == 0 && Auth::user()->empresa()->estado_dian == 1 && Auth::user()->empresa()->technicalkey != null && $factura->tipo == 2)
                            <a  href="#"  class="btn btn-outline-primary btn-sm"title="Emitir Factura" onclick="validateDian({{ $factura->id }}, '{{route('xml.factura',$factura->id)}}', '{{ ($factura->codigo ? $factura->codigo : null) }}')" ><i class="fas fa-sitemap"></i>Emitir</a>
                        @endif
                        <a href="{{route('facturas.imprimir',['id' => $factura->id, 'name'=> 'Factura No. '.$factura->codigo.'.pdf'])}}" class="btn btn-outline-primary btn-sm "title="Imprimir" target="_blank"><i class="fas fa-print"></i> Imprimir</a>
                        @if(Auth::user()->empresa()->tirilla == 1 && $factura->estatus==0 && $factura->total()->total == $factura->pagado())
                            <a href="{{route('facturas.tirilla', ['id' => $factura->id, 'name' => "Factura No. $factura->id.pdf"])}}" class="btn btn-outline-warning btn-sm "title="Tirilla" target="_blank"><i class="fas fa-print"></i>Imprimir tirilla</a>
                        @endif
                        <a href="{{route('facturas.pdf',$factura->id)}}" class="btn btn-outline-info btn-sm "title="Descargar"><i class="fas fa-download"></i> Descargar</a>
                        @if($factura->cliente()->email)
                            @if($factura->correo==0)
                                <a href="{{route('facturas.enviar',$factura->id)}}" class="btn btn-outline-success btn-sm "title="Enviar"><i class="far fa-envelope"></i> Enviar por Correo Al Cliente</a>
                            @else
                                <a href="#" class="btn btn-danger btn-sm disabled" title="Factura enviada por Correo"><i class="far fa-envelope"></i> Factura enviada por Correo</a>
                            @endif
                        @endif
                        @if($factura->estatus==1)
                            <a href="{{route('ingresos.create_id', ['cliente'=>$factura->cliente()->id, 'factura'=>$factura->id])}}" class="btn btn-outline-primary btn-sm" title="Agregar Pago"><i class="fas fa-plus"></i> Agregar Pago</a>
                        @endif
                        @if($factura->emitida != 1)
                            <a class="btn btn-outline-primary btn-sm" href="{{route('facturas.edit',$factura->id)}}" target="_blank"><i class="fas fa-edit"></i> Editar</a>
                        @endif
                        @if(Auth::user()->empresa()->estado_dian != 1)
                            <form action="{{ route('factura.anular',$factura->id) }}" method="POST" class="delete_form" style="display: none;" id="anular-factura{{$factura->id}}">
                                {{ csrf_field() }}
                            </form>
                            @if(Auth::user()->rol == 3)
                                 <a class="btn btn-outline-danger btn-sm" href="#" onclick="confirmar('anular-factura{{$factura->id}}', '¿Está seguro de que desea anular la factura?', ' ');"><i class="fas fa-minus"></i> Anular</a>
                            @endif
                        @endif
                        
                        <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                            <div class="btn-group" role="group">
                                <button id="btnGroupDrop1" type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Más acciones
                                </button>
                                <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                    @if($factura->estatus==1)
                                        <form action="{{ route('factura.cerrar',$factura->id) }}" method="POST" class="delete_form" style="display: none;" id="cerrar-factura{{$factura->id}}">
                                            {{ csrf_field() }}
                                        </form>
                                        @if(Auth::user()->rol == 3)
                                            <a class="dropdown-item" href="#" onclick="confirmar('cerrar-factura{{$factura->id}}', '¿Está seguro de que desea cerrar la factura?', ' ');">Cerrar sin pago</a>
                                        @endif
                                    @endif
                                    <a class="dropdown-item" href="{{route('facturas.imprimircopia',$factura->id)}}" target="_blank">Imprimir como Copia</a>
                                    <a class="dropdown-item" href="{{route('facturas.copia',$factura->id)}}" target="_blank">Descargar como Copia</a>
                                    @if($factura->tipo == 1 && isset($contrato) && $contrato->opciones_dian == 1)
                                    <a class="dropdown-item" href="{{route('facturas.convertirelectronica',$factura->id)}}">Convertir a factura electrónica</a>
                                    @endif
                                    @if($factura->emitida == 1)
                                        <a class="dropdown-item" href="{{route('facturas.xml',$factura->id)}}" target="_blank">Descargar xml</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
        @endif
    @endif
    
    <!-- BANNER DE VALORES -->
    <div class="card-body">
        <div class="row" style="box-shadow: 1px 2px 4px 0 rgba(0,0,0,0.15);background-color: #fff; padding:2% !important;">
            <div class="offset-md-1 offset-xl-1 offset-lg-1 col-xl-2 col-lg-2 col-md-2 col-sm-12  stretch-card" style="border: 1px solid #fff !important;">
                <div class="card card-statistics" style="background-color: #fff !important;">
                    <div class="clearfix">
                        <div class="float-center">
                            <p class="mb-0 text-center">Valor total</p>
                            <div class="fluid-container">
                                <h4 class="font-weight-medium text-center mb-0">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->total()->total)}}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-2 col-lg-2 col-md-2 col-sm-6  stretch-card" style="border: 1px solid #fff !important;">
                <div class="card card-statistics" style="background-color: #fff !important;">
                    <div class="clearfix">
                        <div class="float-center">
                            <p class="mb-0 text-center">Retenido</p>
                            <div class="fluid-container">
                                <h4 class="font-weight-medium text-center mb-0">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->retenido(true))}}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    <div class="col-xl-2 col-lg-2 col-md-2 col-sm-6  stretch-card" style="border: 1px solid #fff !important;">
      <div class="card card-statistics" style="background-color: #fff !important;">
          <div class="clearfix">
            <div class="float-center">
              <p class="mb-0 text-center">Devoluciones</p>
              <div class="fluid-container">
                <h4 class="font-weight-medium text-center mb-0">{{Auth::user()->empresa()->moneda}}                   {{App\Funcion::Parsear($factura->devoluciones())}}</h4>
              </div>
            </div>
          </div>
      </div>
    </div>
    <div class="col-xl-2 col-lg-2 col-md-2 col-sm-6  stretch-card" style="border: 1px solid #fff !important;">
      <div class="card card-statistics" style="background-color: #fff !important;">
          <div class="clearfix">
            <div class="float-center">
              <p class="mb-0 text-center">Cobrado</p>
              <div class="fluid-container">
                <h4 class="font-weight-medium text-center mb-0 text-success">{{Auth::user()->empresa()->moneda}} 
                {{App\Funcion::Parsear($factura->pagado())}}</h4>
              </div>
            </div>
        </div>
      </div>
    </div>
    <div class="col-xl-2 col-lg-2 col-md-2 col-sm-6  stretch-card" style="border: 1px solid #fff !important;">
      <div class="card card-statistics" style="background-color: #fff !important;">
          <div class="clearfix">
            <div class="float-center">
              <p class="mb-0 text-center">Por cobrar</p>
              <div class="fluid-container">
                <h4 class="font-weight-medium text-center mb-0 text-danger">{{Auth::user()->empresa()->moneda}} 
                {{App\Funcion::Parsear($factura->porpagar())}}</h4>
              </div>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>
<!-- FIN BANNER DE VALORES -->

 <!-- BANNER DE VALORES -->
 @if($factura->contrato_id != null)
 <div class="card-body" style="margin-bottom:-29px;">
<div class="row" style="box-shadow: 1px 2px 4px 0 rgba(0,0,0,0.15);background-color: #fff; padding:0% !important;">
    <div class="stretch-card" style="border: 1px solid #fff !important;margin-left:15px;">
        <div class="card card-statistics" style="background-color: #fff !important;">
            <div class="clearfix">
                <div class="float-center">
                    <p class="mb-0 text-center">Contrato asociado</p>
                    <div class="fluid-container">
                        <h4 class="font-weight-medium text-center mb-0">No. <a href="{{route('contratos.show',$factura->contrato_id)}}" target="_blank">{{$factura->contrato()->nro}}</a></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<!-- FIN BANNER DE VALORES -->
@endif

@if(Session::has('success') || Session::has('error'))
  @if(Session::has('success'))
  <div class="alert alert-success alert-view-show">
    {{Session::get('success')}}
  </div>
  @endif

  @if(Session::has('error'))
  <div class="alert alert-danger alert-view-show">
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

    <div class="paper mx-3">
        <div class="ribbon {{$factura->estatus()}}"><span>{{$factura->estatus()}}</span></div>
        <!-- Membrete -->
        <div class="row">
            <div class="col-md-4 text-center">
                <img class="img-responsive" src="{{asset('images/Empresas/Empresa'.Auth::user()->empresa.'/'.Auth::user()->empresa()->logo)}}" alt="" width="50%">
            </div>
            <div class="col-md-4 text-center padding1">
                <h4>{{Auth::user()->empresa()->nombre}}</h4>
                <p>{{Auth::user()->empresa()->tip_iden('mini')}} {{Auth::user()->empresa()->nit}}  @if(Auth::user()->empresa()->dv != null || Auth::user()->empresa()->dv == 0) - {{Auth::user()->empresa()->dv}} @endif<br> {{Auth::user()->empresa()->email}}</p>
            </div>
            <div class="col-md-4 text-center padding1" >
                <h4><b class="text-primary">No. </b> {{$factura->codigo}}</h4>  @if(isset($factura->nro_remision))<h4><b class="text-primary">No. Remision </b> {{$factura->nro_remision}}</h4> @endif
            </div>
        </div>
        <!--Cliente-->
        <div class="row" style="margin-top: 3%; padding: 2% 7%;">
            <div class="col-md-12 fact-table">
                <table class="table table-striped cliente">
                    <tbody>
                        <tr>
                            <td width="10%">Cliente</td>
                            @if(auth()->user()->rol == 8)
                            <th width="60%">{{$factura->cliente()->nombre}} {{$factura->cliente()->apellidos()}}</th>
                            @else
                            <th width="60%"><a href="{{route('contactos.show',$factura->cliente()->id)}}" target="_blank">{{$factura->cliente()->nombre}} {{$factura->cliente()->apellidos()}}</a></th>
                            @endif
                            <td width="10%">Creación</td>
                            <th width="10%">{{date('d-m-Y', strtotime($factura->fecha))}}</th>
                        </tr>
                        <tr>
                            <td>{{$factura->cliente()->tip_iden('true')}}</td>
                            <th>{{$factura->cliente()->nit}}</th>
                            <td>Pago Oportuno</td>
                            <th>{{date('d-m-Y', strtotime($factura->pago_oportuno))}}</th>
                        </tr>
                        <tr>
                            <td>Teléfono</td>
                            <th>{{$factura->cliente()->celular?$factura->cliente()->celular:$factura->cliente()->telefono1}}</th>
                            <td>Vencimiento</td>
                            <th>{{date('d-m-Y', strtotime($factura->vencimiento))}}</th>
                        </tr>
                        @if($factura->cliente()->contrato())
                        <tr>
                            <td>Dirección</td>
                            <th colspan="3">{{$factura->cliente()->contrato()->address_street ? $factura->cliente()->contrato()->address_street : $factura->cliente()->direccion}}</th>
                        </tr>
                        @endif
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
                    <td><div class="elipsis-short-item">@if(auth()->user()->rol == 8) {{$item->producto()}} @else <a href="{{route('inventario.show',$item->producto)}}" target="_blank">{{$item->producto()}}</a>@endif</div></td>
                    <td><div class="elipsis-short">{{$item->ref}}</div></td>
                    <td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($item->precio)}}</td>
                    <td>{{$item->desc?$item->desc:0}}%</td>
                    <td>{{$item->impuesto()}}</td>
                    <td><div class="elipsis-short">{{$item->descripcion}}</div></td>
                    <td>{{round($item->cant)}}</td>
                    <td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($item->total())}}</td>
                </tr>

            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    @if($factura->retenido()>0)
      <div class="row">
        <div class="col-md-4 offset-md-1 retenciones"> <b>RETENCIONES APLICADAS:</b> {{$factura->retenciones()}}  </div>
      </div>
    @endif  

    <!-- Totales -->
    <div class="row" style="margin-top: 2%; padding: 2% 6%;">
      <div class="col-md-4 text-center">
        <div class="align-bottom" style="width: 100%; border-top: 1px solid #ccc;     margin-right: 10%;margin-top: 20%;">
            <p style="    font-weight: 500 !important;"> ELABORADO POR: {{$factura->vendedor()}}</p>
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
            <td width="40%">Subtotal</td>
            <td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($factura->total()->resul)}}</td>
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
          @foreach($retenciones as $retencion)
            <tr>
              <!--<td>RF </td>
              <td>{{$retencion->retencion()->porcentaje}}%</td>-->
              <td>RF {{$retencion->retencion()->porcentaje}}%</td>
              <td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($retencion->valor)}}</td>
            </tr>
          @endforeach
        </table>
        <hr>
        <table class="text-right widthtotal" style="font-size: 24px !important;">
          <tr>
            <td width="40%">TOTAL A PAGAR</td>
            <td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($factura->total()->total)}}</td>
          </tr>
        </table>
      </div>
    </div>

    <!-- Terminos y Condiciones -->
    <div class="row" style="margin-top: 2%; padding: 2% 6%; min-height: 180px;">
      <div class="col-md-8">
        <label class="form-label" style="font-weight: 500 !important;">Términos y Condiciones</label>
        <p>{{$factura->term_cond}}</p>
      </div>
      <div class="col-md-4">
        <label class="form-label" style="font-weight: 500 !important;">Notas</label>
        <p>{{$factura->facnotas}}</p>
      </div>
    </div>
  </div>

    <div class="row" style="padding: 0 2.7%;">
        <div class="col-md-7" style="box-shadow: 1px 2px 4px 0 rgba(0,0,0,0.15);background-color: #fff; padding:2% !important;">
            <h6>Observaciones</h6>
            <p class="text-justify">
                @php
                    $count = substr_count($factura->observaciones, "|");
                    if($count == 0){
                        echo $factura->observaciones;
                    }else{
                        for ($i=0; $i <= $count ; $i++) {
                            $porcion = explode("|", $factura->observaciones);
                            echo $porcion[$i]."<br>";
                        }
                    }
                @endphp
            </p>
        </div>
        <div class="col-md-4 offset-md-1" style="box-shadow: 1px 2px 4px 0 rgba(0,0,0,0.15);background-color: #fff; padding:2% !important;">
            <h6>Detalles</h6>
            <p class="text-justify mb-0"><strong>{{ $factura->facturacion_automatica == 0 ? 'Facturación Manual':'Facturación Automática'}}</strong></p>
            @if($factura->created_by)
            <p class="text-justify mb-0"><strong>Creada por:</strong> {{$factura->created_by()->nombres}}</p>
            @endif
            <p class="text-justify mb-0"><strong>Creada el:</strong> {{date('d-m-Y g:i:s A', strtotime($factura->created_at))}}</p>

            <table class="table table-striped cliente d-none">
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

    <div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
		<button type="button" class="close" data-dismiss="alert">×</button>
		<strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
	</div>
	
    <div class="row card-description" style="padding: 0 2.7%; margin-top: 2%;">
        <div class="col-md-12 fact-table" style="box-shadow: 1px 2px 4px 0 rgba(0,0,0,0.15);background-color: #fff; padding:2% !important;">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="profile-tab" data-toggle="tab" href="#pagos_recibidos" role="tab" aria-controls="pagos_recibidos-tab" aria-selected="false" style="font-size: 1.1em; display: inline-block;">Pagos recibidos  
                        @if(auth()->user()->modo_lectura())
                        @else
                        @if($factura->estatus==1)
                            <a href="{{route('ingresos.create_id', ['cliente'=>$factura->cliente()->id, 'factura'=>$factura->nro])}}" class="btn btn-secondary btn-sm btn-rounded text-center" target="_blank" title="Agregar Pagos"><i style="margin: 0; display: inline-block;" class="fas fa-plus"></i></a>
                        @endif
                        @endif
                    </a>
                </li>
                @if($factura->notas_credito(true)>0)
                <li class="nav-item">
                    <a class="nav-link" id="notas_credito-tab" data-toggle="tab" href="#notas_credito" role="tab" aria-controls="notas_credito" aria-selected="false" style="font-size: 1.1em">Notas crédito</a>
                </li>
                @endif
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active" id="pagos_recibidos" role="tabpanel" aria-labelledby="pagos_recibidos-tab" >
                    <table class="table table-striped pagos">
                        <thead>
                            <th>Fecha</th>
                            <th>Recibo de caja #</th>
                            <th>Estado</th>
                            <th>Método de pago</th>
                            <th>Monto</th>
                            <th>Observaciones</th>
                        </thead>
                    <tbody>
                    @if($factura->pagos(true)>0)
                        @foreach($factura->pagos() as $pago)
                        <tr>
                            <td>@if(auth()->user()->rol == 8){{date('d-m-Y', strtotime($pago->ingreso()->fecha))}}@else<a href="{{route('ingresos.show',$pago->ingreso()->id)}}">{{date('d-m-Y', strtotime($pago->ingreso()->fecha))}}</a>@endif</td>
                            <td>@if(auth()->user()->rol == 8){{$pago->ingreso()->nro}}@else<a href="{{route('ingresos.show',$pago->ingreso()->id)}}">{{$pago->ingreso()->nro}}</a>@endif</td>
                            <td></td>
                            <td>{{$pago->ingreso()->metodo_pago()}}</td>
                            <td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($pago->pago())}}</td>
                            <td>{{$pago->ingreso()->observaciones}}</td>
                        </tr>
                        @endforeach                
                    @else
                        @if(auth()->user()->modo_lectura())
                        @else
                        @if($factura->estatus==1)
                        <tr>
                            <td colspan="6">
                                <p class="text-center lead" style="margin-top: 5%"> Tu factura aún no tiene pagos recibidos  <a href="{{route('ingresos.create_id', ['cliente'=>$factura->cliente()->id, 'factura'=>$factura->nro])}}" class="btn btn-secondary btn-sm" ><i class="fas fa-plus"></i> Agregar Pagos</a></p>
                            </td>
                        </tr>
                        @endif
                        @endif
                    @endif
                </tbody>
            </table>          
        </div>

      @if($factura->notas_credito(true)>0)
        <div class="tab-pane fade" id="notas_credito" role="tabpanel" aria-labelledby="notas_credito-tab">
          <table class="table table-striped table-hover pagos">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Nota crédito #</th>
                <th>Monto</th>
                <th>Observaciones</th>
              </tr>                              
            </thead>
            <tbody>
              @foreach($factura->notas_credito() as $notas)
                <tr>
                  <td> <a href="{{route('notascredito.show',$notas->nota()->nro)}}">{{date('d-m-Y', strtotime($notas->nota()->fecha))}}</a> </td>
                  <td>{{$notas->nota()->nro}}</td>
                  <td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($notas->pago)}}</td>
                  <td>{{$notas->nota()->observaciones}}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
        </div>
    </div>

    <div class="modal fade" id="facturacionElectronica" tabindex="-1" role="dialog" aria-labelledby="facturacionElectronica" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="facturacionelectronica">Observaciones facturación electrónica</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-striped">
                        <tr>
                            <td><b>Estado de factura:</b> 
                                @if($realStatus)
                                   {{$factura->statusdian==0 ? "RECHAZADA" : "ACEPTADA"}}
                                @else
                                    Pendiente por aceptar
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td>
                                @if($factura->statusdian==0)
                                    <a class="btn btn-primary btn-lg btn-block" href="{{route('factura.aceptarfe', $factura->id)}}">Aceptar factura</a>
                                    <br>
                                    <hr>
                                    <h4>Observaciones <i data-tippy-content="Si la factura de venta pasadas 24 horas, el sistema la aceptará automaticamente" class=" icono far fa-question-circle"></i> </h4>
                                    <h5>{{$factura->observacionesdian}}</h5>
                                @else
                                    <h4>Observaciones <i data-tippy-content="Si la factura de venta pasadas 24 horas, el sistema la aceptará automaticamente" class=" icono far fa-question-circle"></i></h4>
                                    <h5>{{$factura->observacionesdian}}</h5>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

@endsection
