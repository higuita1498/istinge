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

    .desgloce.mov tbody > tr > td {
    padding: 1.5% 3px !important;
    border: 1px solid #ccc;
    }
    </style>
       
    
    @if(auth()->user()->modo_lectura())
        <div class="alert alert-warning text-left" role="alert">
            <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
            <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
        </div>
    @else
        @if(auth()->user()->rol <> 8)
            <div class="row" style="margin: -2% 0 0 2%;">
                <div class="col-md-12">
                        @if(Auth::user()->empresa()->form_fe == 1 && $factura->emitida == 0 && Auth::user()->empresa()->estado_dian == 1 && Auth::user()->empresa()->technicalkey != null)
                            <a  href="#"  class="btn btn-outline-primary btn-sm"title="Emitir Factura" onclick="validateDian({{ $factura->id }}, '{{route('xml.factura',$factura->id)}}', '{{$factura->codigo}}')" ><i class="fas fa-sitemap"></i>Emitir</a>
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
        
        <div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
			<button type="button" class="close" data-dismiss="alert">×</button>
			<strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
		</div>
		
        <!-- Desgloce -->
        <div class="row" style="padding: 2% 6%;">
            <div class="col-md-12 fact-table">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-sm pagos"  width="100%">
                        <thead>
                            <tr>
                                <th>Nro</th>
                                <th width="20%">Cuenta contable</th>
                                <th width="20%">tecrero</th>
                                <th width="20%">Detalle</th>
                                <th width="20%">Descripción</th>
                                <th width="10%">Débito</th>
                                <th width="10%">Crédito</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $i = 1 @endphp
                            @foreach($movimientos as $mov)
                                <tr>
                                    <td>{{$i}}</td>
                                    <td>{{$mov->codigo_cuenta}} - {{$mov->cuenta()->nombre}}</td>
                                    <td>{{$mov->cliente->nombre}}</td>
                                    <td>{{$mov->asociadoA()}}</td>
                                    <td>{{$mov->descripcion}}</td>
                                    <td>{{$mov->debito}}</td>
                                    <td>{{$mov->credito}}</td>
                                </tr>
                                @php $i++; @endphp
                            @endforeach
                            <tr>
                                <td style="border:none;"></td>
                                <td style="border:none;"></td>
                                <td style="border:none;"></td>
                                <td style="border:none;"></td>
                                <td style="border:none;">Total:</td>
                                <td>{{$mov->totalDebito()->total}}</td>
                                <td>{{$mov->totalCredito()->total}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
