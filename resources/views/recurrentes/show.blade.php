@extends('layouts.app')

@section('boton') 
  <a href="{{route('recurrentes.edit',$factura->nro)}}" class="btn btn-outline-primary btn-sm " title="Editar" target="_blank"><i class="fas fa-edit"></i> Editar</a> 
@endsection   

@section('content')
<div class="row card-description">
  <div class="col-md-12">
    <div class="table-responsive">
      <table class="table table-striped table-bordered table-sm info">
        <tbody>
          <tr>
            <th width="20%">Cliente</th>
            <td>{{$factura->cliente()->nombre}}</td>
          </tr>
          <tr>
            <th>Fecha de Incio</th>
            <td>{{date('d-m-Y', strtotime($factura->fecha))}}</td>
          </tr>
          <tr>
            <th>Fecha de Finalización</th>
            <td>@if($factura->vencimiento){{date('d-m-Y', strtotime($factura->vencimiento))}} @else Corre para siempre @endif</td>
          </tr>
          <tr>
            <th>Proxima emisión</th>
            <td>{{date('d-m-Y', strtotime($factura->proxima))}}</td>
          </tr>
          <tr>
            <th>Total</th>
            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->total()->total)}}</td>
          </tr>
          <tr>
            <th>Frecuencia (meses)</th>
            <td>{{$factura->frecuencia}}</td>
          </tr>
          <tr>
            <th>Termino (días)</th>
            <td>{{$factura->plazo()}}</td>
          </tr>
          <tr>
            <th>Observaciones</th>
            <td>{{$factura->observaciones}}</td>
          </tr>
          <tr>
            <th>Términos y Condiciones</th>
            <td>{{$factura->term_cond}}</td>
          </tr>
          <tr>
            <th>Notas</th>
            <td>{{$factura->notas}}</td>
          </tr>
          <tr>
            <th>Lista de precio </th>
            <td>{{$factura->lista_precios()}}</td>
          </tr>
          <tr>
            <th>Bodega</th>
            <td>{{$factura->bodega()}}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
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
                    <td><a href="{{route('inventario.show',$item->producto)}}" target="_blanck">{{$item->producto()->producto}}</a></td>
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
              <td class="text-right">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($factura->total()->subtotal)}}</td>
            </tr>
            <tr>
              <th colspan="7" class="text-right">Descuento</th>
              <td class="text-right">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($factura->total()->descuento)}}</td>
            </tr>
            <tr>
              <th colspan="7" class="text-right">Subtotal</th>
              <td class="text-right">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($factura->total()->subsub)}}</td>
            </tr>
            @if($factura->total()->imp)
              @foreach($factura->total()->imp as $imp)
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
              <td class="text-right">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($factura->total()->total)}}</td>
            </tr>
          </tfoot>
        </table>
    </div>
  </div>
  </div>
@endsection
