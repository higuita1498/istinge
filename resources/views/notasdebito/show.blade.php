@extends('layouts.app')

@section('boton')  
  <a href="#" class="btn btn-outline-primary btn-sm "title="Imprimir" target="_blank"><i class="fas fa-edit"></i> Editar</a> 
@endsection   

@section('content')
  <div class="row card-description">
  <div class="col-md-12">
    <div class="table-responsive">
      <table class="table table-striped table-bordered table-sm info">
        <tbody>
          <tr>
            <th colspan="2"></th>
          </tr>
          <tr>
            <th width="20%">Código</th> <td>{{$nota->codigo}}</td>
          </tr>
          <tr>
            <th>Cliente</th> <td><a href="{{route('contactos.show',$nota->proveedor()->id)}}" target="_blanck">{{$nota->proveedor()->nombre}}</a></td>
          </tr>
          <tr>
            <th>Creación</th> <td>{{date('d/m/Y', strtotime($nota->fecha))}}</td>
          </tr>
          <tr>
            <th>Total</th> <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($nota->total()->total)}} </td>
          </tr>
          <tr>
            <th>Por aplicar</th> <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($nota->por_aplicar())}}</td>
          </tr>
          <tr>
            <th>Observaciones </th> <td>{{$nota->observaciones}}</td>
          </tr>
          <tr>
            <th>Notas</th> <td>{{$nota->notas}}</td>
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
              <th >Ítem/Categoría</th>
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
                  @if($item->tipo_item==1)
                    <td><a href="{{route('inventario.show',$item->producto)}}" target="_blanck">{{$item->producto()}}</a></td>
                  @else
                    <td><a >{{$item->producto()}}</a></td>
                  @endif 
                    <td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($item->precio)}}</td>
                    <td>{{$item->desc?$item->desc:0}}%</td>
                    <td>{{$item->impuesto()}}</td>
                    <td>{{$item->descripcion}}</td>
                    <td class="text-center">{{$item->cant}}</td>
                    <td class="text-right">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($item->total())}}</td>
                </tr>

            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <th colspan="6" class="text-right">Subtotal</th>
              <td class="text-right">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($nota->total()->subtotal)}}</td>
            </tr>
            <tr>
              <th colspan="6" class="text-right">Descuento</th>
              <td class="text-right">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($nota->total()->descuento)}}</td>
            </tr>
            <tr>
              <th colspan="6" class="text-right">Subtotal</th>
              <td class="text-right">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($nota->total()->subsub)}}</td>
            </tr>
            @if($nota->total()->imp)
              @foreach($nota->total()->imp as $imp)
                @if(isset($imp->total))
                  <tr>
                    <th colspan="6" class="text-right">{{$imp->nombre}} ({{$imp->porcentaje}}%)</th>
                    <td class="text-right">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($imp->total)}}</td>
                  </tr>
                @endif
              @endforeach
            @endif
            @foreach($retencionesNotas as $retencion)
              <tr>
              <!--<td>RF </td>
              <td>{{$retencion->retencion()->porcentaje}}%</td>-->
                <th colspan="6" class="text-right">RF {{$retencion->retencion()->porcentaje}}%</th>
                <td class="text-right">{{Auth::user()->empresa()->moneda}} -{{App\Funcion::Parsear($retencion->valor)}}</td>
              </tr>
            @endforeach

            <tr>
              <th colspan="6" class="text-right">TOTAL</th>
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
          <td><a href="{{route('facturasp.show',$factura->factura()->nro)}}" target="_blanck" >{{$factura->factura()->codigo?$factura->factura()->codigo:('Factura '.date('d-m-Y', strtotime($factura->factura()->fecha_factura)))}}</a></td>
          <td class="text-center">{{date('d-m-Y', strtotime($factura->factura()->fecha_factura))}}</td>
          <td class="text-center">{{date('d-m-Y', strtotime($factura->factura()->vencimiento_factura))}}</td>
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


  @if(count($DevolucionesDebito)>0)
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
              @foreach($DevolucionesDebito as $devolucion)
                <tr>
                  <td class="text-left"><a  href="@if($devolucion->ingreso()){{route('ingresos.show',$devolucion->ingreso()->nro)}}@else # @endif">{{date('d-m-Y', strtotime($devolucion->fecha))}}</a></td>
                  <td class="text-center">{{$devolucion->cuenta()->nombre}}</td>
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