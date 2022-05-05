@extends('layouts.app')

@section('boton') 
@if(auth()->user()->modo_lectura())
  <div class="alert alert-warning text-left" role="alert">
    <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
    <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
  </div>
@else
  <a href="{{route('factura.remision',$remision->nro)}}" class="btn btn-outline-primary btn-sm "title="Facturar" target="_blank"><i class="fas fa-"></i> Facturar</a>
  <a href="{{route('remisiones.edit',$remision->nro)}}" class="btn btn-outline-primary btn-sm "title="Imprimir" target="_blank"><i class="fas fa-edit"></i> Editar</a>
  <a href="{{route('remisiones.imprimir',['id' => $remision->nro, 'name'=> 'Remision No. '.$remision->nro.'.pdf'])}}" target="_black" class="btn btn-outline-primary btn-sm" title="Imprimir" target="_blank"><i class="fas fa-print"></i> Imprimir</a>

  <a href="{{route('remisiones.enviar',$remision->nro)}}" class="btn btn-outline-primary btn-sm "title="Enviar"><i class="far fa-envelope"></i> Enviar por Correo</a>
  <form action="{{ route('remisiones.anular',$remision->nro) }}" method="post" class="delete_form" style="display: none;" id="anular-remision{{$remision->id}}">
      {{ csrf_field() }}
    </form>
    @if($remision->estatus==1)
    <button class="btn btn-outline-primary btn-sm"  type="button" title="Anular" onclick="confirmar('anular-remision{{$remision->id}}', '¿Está seguro de que desea anular la remisión?', ' ');"><i class="fas fa-minus"></i>Anular</button>
    @else
    <button  class="btn btn-outline-primary btn-sm" type="button" title="Abrir" onclick="confirmar('anular-remision{{$remision->id}}', '¿Está seguro de que desea abrir la remisión?', ' ');">
      <i class="fas fa-unlock-alt"> Convertir a Abierta</i>
    </button>
    @endif
@endif
@endsection   

@section('content')
  <style type="text/css"> .card{ background: #f9f1ed !important;}</style>
  
  
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

@if($remision->pagado()>0)
  <div class="card-body">
    <div class="row" style="box-shadow: 1px 2px 4px 0 rgba(0,0,0,0.15);background-color: #fff; padding:2% !important;">
      <div class="offset-md-3 offset-xl-3 offset-lg-3 col-xl-2 col-lg-2 col-md-2 col-sm-12  stretch-card">
        <div class="card card-statistics" style="background-color: #fff !important;">
            <div class="clearfix">
              <div class="float-center">
                <p class="mb-0 text-center">Valor total</p>
                <div class="fluid-container">
                  <h4 class="font-weight-medium text-center mb-0">{{Auth::user()->empresa()->moneda}}
                  {{App\Funcion::Parsear($remision->total()->total)}}</h4>
                </div>
              </div>
          </div>
        </div>
      </div>
      <div class="col-xl-2 col-lg-2 col-md-2 col-sm-6  stretch-card">
        <div class="card card-statistics" style="background-color: #fff !important;">
            <div class="clearfix">
              <div class="float-center">
                <p class="mb-0 text-center">Cobrado</p>
                <div class="fluid-container">
                  <h4 class="font-weight-medium text-center mb-0 text-success">{{Auth::user()->empresa()->moneda}} 
                  {{App\Funcion::Parsear($remision->pagado())}}</h4> 
                </div>
              </div>
          </div>
        </div>
      </div>
      <div class="col-xl-2 col-lg-2 col-md-2 col-sm-6  stretch-card">
        <div class="card card-statistics" style="background-color: #fff !important;">
            <div class="clearfix">
              <div class="float-center">
                <p class="mb-0 text-center">Por cobrar</p>
                <div class="fluid-container">
                  <h4 class="font-weight-medium text-center mb-0 text-danger">{{Auth::user()->empresa()->moneda}} 
                  {{App\Funcion::Parsear(App\Funcion::precision($remision->porpagar()))}}</h4>
                </div>
              </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endif

  <div class="paper " style="    margin-top: 2%;">

   <div class="ribbon {{$remision->estatus('si')}}"><span>{{$remision->estatus()}}</span></div>
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
        <h4><b class="text-primary">No. </b> {{$remision->nro}}</h4> 
      </div>
    </div>
    	<div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
			<button type="button" class="close" data-dismiss="alert">×</button>
			<strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
		</div>

    <!--Cliente-->
    <div class="row" style="margin-top: 3%; padding: 2% 7%;">
      <div class="col-md-12 fact-table">
        <table class="table table-striped cliente">
          <tbody>
            <tr>
              <td width="10%">Cliente</td>
              <th width="60%"><a href="{{route('contactos.show',$remision->cliente()->id)}}" target="_blanck">{{$remision->cliente()->nombre}}</a></th>
              <td width="10%">Creación</td>
              <th width="10%">{{date('d/m/Y', strtotime($remision->fecha))}}</th>
            </tr>
            <tr>
              <td>{{$remision->cliente()->tip_iden('true')}}</td>
              <th>{{$remision->cliente()->nit}}</th>
              <td>Vencimiento</td>
              <th>{{date('d/m/Y', strtotime($remision->vencimiento))}}</th>
            </tr>
            <tr>
              <td>Teléfono</td>
              <th>{{$remision->cliente()->telefono1}}</th>
              <td>Tipo de documento</td>
              <th>{{$remision->documento}}</th>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Desgloce -->
    <div class="row" style="padding: 2% 6%;">
      <div class="col-md-12 fact-table">
        <table class="table table-striped table-sm desgloce"  width="100%">
          <thead >
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
                    <td><a href="{{route('inventario.show',$item->producto)}}" target="_blanck">{{$item->producto()}}</a></td>
                    <td>{{$item->ref}}</td>
                    <td>{{Auth::user()->empresa()->moneda}}{{$item->precio}}</td>
                    <td>{{$item->desc?$item->desc:0}}%</td>
                    <td>{{$item->impuesto()}}</td>
                    <td>{{$item->descripcion}}</td>
                    <td>{{$item->cant}}</td>
                    <td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($item->total())}}</td>
                </tr>

            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <!-- Totales -->
    <div class="row" style="margin-top: 2%; padding: 2% 6%;">
      <div class="col-md-4 text-center">
        <div class="align-bottom" style="width: 100%; border-top: 1px solid #ccc;     margin-right: 10%;margin-top: 20%;">
            <p style="    font-weight: 500 !important;"> ELABORADO POR: {{$remision->vendedor()}}</p>
        </div>
      </div>
      <div class="col-md-4 offset-md-4">
        <table class="text-right widthtotal" id="totales">
          <tr>
            <td width="40%">Subtotal</td>
            <td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($remision->total()->subtotal)}}</td>
          </tr> 
          <tr>
            <td>Descuento</td><td id="descuento">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($remision->total()->descuento)}}</td>
          </tr>
          <tr>
            <td>Subtotal</td>
            <td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($remision->total()->subsub)}}</td>
          </tr>
          @if($remision->total()->imp)
            @foreach($remision->total()->imp as $imp)
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
            <td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($remision->total()->total)}}</td>
          </tr>
        </table>
      </div>
    </div>

    <!-- Terminos y Condiciones -->
    <div class="row" style="margin-top: 2%; padding: 2% 6%; min-height: 180px;">
      <div class="col-md-12">
        <label class="form-label" style="font-weight: 500 !important;">Notas</label>
        <p>{{$remision->notas}}</p>
      </div>
    </div>

  </div>

  <div class="row" style="padding: 0 1%;">
     <div class="col-md-7" style="box-shadow: 1px 2px 4px 0 rgba(0,0,0,0.15);background-color: #fff; padding:2% !important;">
        <h6>Observaciones</h6>
        <p class="text-justify">
          {{$remision->observaciones}}
        </p>
      </div>
      <div class="col-md-4 offset-md-1" style="box-shadow: 1px 2px 4px 0 rgba(0,0,0,0.15);background-color: #fff; padding:2% !important;">
        
        <table class="table table-striped cliente">
          <tbody>
            <tr>
              <td>Vendedor</td>
              <th class="text-right">{{$remision->vendedor()}}</th>
            </tr>
          <tr>
            <td>Lista de precios</td>
            <th class="text-right">{{$remision->lista_precios()}}</th>
          </tr>
          <tr>
            <td>Bodega</td>
            <th class="text-right">{{$remision->bodega()}}</th>
          </tr>
          </tbody>
        </table>
         
      </div>
  </div>

  <div class="row" style="padding: 0 1%; margin-top: 2%;">
     <div class="col-md-12" style="box-shadow: 1px 2px 4px 0 rgba(0,0,0,0.15);background-color: #fff; padding:2% !important;">
        <h5>Pagos recibidos 
          @if($remision->estatus==1)
          <a href="{{route('ingresosr.create_id', ['cliente'=>$remision->cliente()->id, 'remision'=>$remision->nro])}}" class="btn btn-secondary btn-sm btn-rounded text-center" target="_blank" title="Agregar Pagos"><i style="    margin: 0;" class="fas fa-plus"></i></a>
          @endif</h5>
        @if($remision->pagos(true)>0)
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
              @foreach($remision->pagos() as $pago)
                <tr> 
                  <td><a href="{{route('ingresosr.show',$pago->ingreso()->nro)}}">{{date('d-m-Y', strtotime($pago->ingreso()->fecha))}}</a></td>
                  <td>{{$pago->ingreso()->nro}}</td>
                  <td></td>
                  <td>{{$pago->ingreso()->metodo_pago()}}</td>
                  <td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($pago->pago())}}</td>
                  <td>{{$pago->ingreso()->observaciones}}</td>
                </tr>
              @endforeach
              
            </tbody>
          </table>

        @else
          <p class="text-center lead" style="margin-top: 5%"> Tu remision aún no tiene pagos recibidos  <a href="{{route('ingresosr.create_id', ['cliente'=>$remision->cliente()->id, 'remision'=>$remision->nro])}}" class="btn btn-secondary btn-sm" ><i class="fas fa-plus"></i> Agregar Pagos</a></p>
        @endif
        

    

    
      </div>
  </div>

@endsection
