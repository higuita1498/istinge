@extends('layouts.app')

@section('boton')
  @if(auth()->user()->modo_lectura())
      <div class="alert alert-warning text-left" role="alert">
          <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
          <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
      </div>
  @else
    <a href="{{route('notascredito.imprimir.nombre',['id' => $nota->nro, 'name'=> 'Nota Credito No. '.$nota->nro.'.pdf'])}}" target="_blanck" class="btn btn-outline-primary btn-sm "title="Imprimir" target="_blank"><i class="fas fa-print"></i> Imprimir</a>
    <a href="{{route('notascredito.enviar',$nota->nro)}}" class="btn btn-outline-primary btn-sm "title="Enviar"><i class="far fa-envelope"></i> Enviar por Correo Al Cliente</a>
    <a href="{{route('notascredito.edit',$nota->nro)}}" class="btn btn-outline-primary btn-sm "title="Imprimir" target="_blank"><i class="fas fa-edit"></i> Editar</a>
    <div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
      <button type="button" class="close" data-dismiss="alert">×</button>
      <strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
    </div>
  @endif
@endsection   

@section('content')
  @if(Session::has('success'))
    <div class="alert alert-success" >
      {{Session::get('success')}}
    </div>

    <script type="text/javascript">
      setTimeout(function(){ 
          $('.alert').hide();
          $('.active_table').attr('class', ' ');
      }, 5000);
    </script>
  @endif 
  <div class="row card-description">
  <div class="col-md-12">
    <div class="table-responsive">
      <table class="table table-striped table-bordered table-sm info">
        <tbody>
          <tr>
            <th width="20%">Código</th> <td>{{$nota->nro}}</td>
          </tr>
          <tr>
            <th>Tipo de nota crédito</th> <td>{{$nota->tipo()}}</td>
          </tr>
          <tr>
            <th>Cliente</th> <td><a href="{{route('contactos.show',$nota->cliente()->id)}}" target="_blanck">{{$nota->cliente()->nombre}}</a></td>
          </tr>
          <tr>
            <th>Creación</th> <td>{{date('d/m/Y', strtotime($nota->fecha))}}</td>
          </tr>
          <tr>
            <th>Total</th> <td>{{Auth::user()->empresa()->moneda}} {{$nota->total()->total}}</td>
          </tr>
          <tr>
            <th>Por aplicar</th> <td>{{Auth::user()->empresa()->moneda}}{{$nota->por_aplicar()}}</td>
          </tr>
          <tr>
            <th>Observaciones </th> <td>{{$nota->observaciones}}</td>
          </tr>
          <tr>
            <th>Notas</th> <td>{{$nota->notas}}</td>
          </tr>
          <tr>
            <th>Lista de precios</th>
            <td>{{$nota->lista_precios()}}</td>
          </tr>
          <tr>
            <th>Bodega</th>
            <td>{{$nota->bodega()}}</td>
          </tr>          
        </tbody>
      </table>
    </div>
  </div>
  </div>

	<div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
			<button type="button" class="close" data-dismiss="alert">×</button>
			<strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
		</div>
  <div class="row card-description">
  <div class="col-md-12">
    <div class="table-responsive">
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
                    <td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($item->precio)}}</td>
                    <td>{{$item->desc?$item->desc:0}}%</td>
                    <td>{{$item->impuesto()}}</td>
                    <td>{{$item->descripcion}}</td>
                    <td>{{$item->cant}}</td>
                    <td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($item->total())}}</td>
                </tr>

            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <th colspan="7" class="text-right">Subtotal</th>
              <td class="text-right">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($nota->total()->subtotal)}}</td>
            </tr>
            <tr>
              <th colspan="7" class="text-right">Descuento</th>
              <td class="text-right">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($nota->total()->descuento)}}</td>
            </tr>
            <tr>
              <th colspan="7" class="text-right">Subtotal</th>
              <td class="text-right">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($nota->total()->subsub)}}</td>
            </tr>
            @if(isset($retenciones))
              @foreach($retenciones as $retencion)
                <tr>
                <!--<td>RF </td>
                <td>{{$retencion->retencion()->porcentaje}}%</td>-->
                  <th colspan="7" class="text-right">{{$retencion->nombre}}({{$retencion->porcentaje}}%)</th>
                  <td class="text-right">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($retencion->valor)}}</td>
                </tr>
              @endforeach
            @endif
            @if($nota->total()->imp)
              @foreach($nota->total()->imp as $imp)
                @if(isset($imp->total))
                  <tr>
                    <th colspan="7" class="text-right">{{$imp->nombre}} ({{$imp->porcentaje}}%)</th>
                    <td class="text-right">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($imp->total)}}</td>
                  </tr>
                @endif
              @endforeach
            @endif


            <tr>
              <th colspan="7" class="text-right">TOTAL</th>
              <td class="text-right">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($nota->total()->total)}}</td>
            </tr>
          </tfoot>
        </table>
    </div>
  </div>
  </div>
  @if(count($facturas)>0)
  <div class="row card-description">
    <div class="col-md-12">
      <h5>Créditos a facturas de venta</h5>
      	<div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
			<button type="button" class="close" data-dismiss="alert">×</button>
			<strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
		</div>
      <div class="table-responsive">
        <table class="table table-striped table-sm desgloce"  width="100%">
          <thead>
            <th>Factura de Venta</th>
            <th>Fecha</th>
            <th>Fecha de Vencimiento</th>
            <th>Observaciones</th>
            <th>Total</th>
            <th>Pagado</th>
            <th>Por pagar</th>
            <th>Monto</th>
          </thead>
          <tbody>
            @foreach($facturas as $factura)
              <tr>
          <td><a href="{{route('facturas.show',$factura->factura()->nro)}}" target="_blanck" >{{$factura->factura()->codigo}}</a></td>
          <td class="text-center">{{date('d-m-Y', strtotime($factura->factura()->fecha))}}</td>
          <td class="text-center">{{date('d-m-Y', strtotime($factura->factura()->vencimiento))}}</td>
          <td>{{$factura->factura()->observaciones}}</td>
          <td class="text-right">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->factura()->total()->total)}}</td>
          <td class="text-right">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->factura()->pagado())}}</td>
          <td class="text-right">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->factura()->porpagar())}}</td>
          <td class="text-right">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->pago)}}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
  @endif


  @if(count($devoluciones)>0)
    <div class="row card-description">
      <div class="col-md-12">
        <h5>Devoluciones</h5>
        <div class="table-responsive">
          <table class="table table-striped table-sm desgloce"  width="100%">
            <thead>
              <th>Fecha</th>
              <th>Cuenta</th>
              <th>Monto</th>
              <th>Observaciones</th>
            </thead>
            <tbody>
              @foreach($devoluciones as $devolucion)
                <tr>
                  <td class="text-left"><a  href="@if($devolucion->gasto()){{route('pagos.show',$devolucion->gasto()->nro)}}@else # @endif">{{date('d-m-Y', strtotime($devolucion->fecha))}}</a></td>
                  <td class="text-center">
                    <a href="{{route('bancos.show',$devolucion->cuenta()->nro)}}" target="_blanck">{{$devolucion->cuenta()->nombre}}</a>
                  </td>
                  <td class="text-right">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($devolucion->monto)}}</td>
                  <td>{{$devolucion->observaciones}}</td>
                </tr>
              @endforeach
            </tbody> 
          </table>
        </div>
      </div>
    </div>


  @endif
@endsection