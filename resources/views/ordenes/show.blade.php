@extends('layouts.app')
@section('boton')
@if(Auth::user()->modo_lectura())
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <a>Esta en Modo Lectura si desea seguir disfrutando de Nuestros Servicios Cancelar Alguno de Nuestros Planes <a class="text-black" href="{{route('PlanesPagina.index')}}"> <b>Click Aqui.</b></a></a>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@else
  @if(empty($orden->nro) && $orden->estatus>0 && $orden->tipo==2)
    <a href="{{route('ordenes.edit',$orden->orden_nro)}}" class="btn btn-outline-primary btn-sm "title="Editar"><i class="fas fa-edit"></i> Editar</a> 
  @endif  
  <a href="{{route('ordenes.imprimir.nombre',['id' => $orden->orden_nro, 'name'=> 'Orden Compra No. '.$orden->orden_nro.'.pdf'])}}" class="btn btn-outline-primary btn-sm "title="Imprimir" target="_blank"><i class="fas fa-print"></i> Imprimir</a>
  <a href="{{route('ordenes.enviar',$orden->orden_nro)}}" class="btn btn-outline-primary btn-sm "title="Enviar al Cliente"><i class="far fa-envelope"></i> Enviar por Correo</a>
  @if($orden->estatus==1 && $orden->tipo==2)
    <form action="{{ route('ordenes.anular',$orden->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="anular-orden{{$orden->id}}">
      {{ csrf_field() }}
    </form>
    <button class="btn btn-outline-danger btn-sm" type="submit" title="Anular" onclick="confirmar('anular-orden{{$orden->id}}', '¿Está seguro de que desea anular la orden de compra?', ' ');">Anular</button>

    <form action="{{ route('ordenes.facturar',$orden->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="convertir-orden{{$orden->id}}">
      {{ csrf_field() }}
    </form>
    <button class="btn btn-outline-primary btn-sm" type="submit" title="Convertir a compra" onclick="confirmar('convertir-orden{{$orden->id}}', '¿Está seguro de que desea convertir a factura esta orden de compra?', ' ');"><i class="fas fa-edit"></i>Convertir a compra</button>
  @endif
@endif

	<div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
				<button type="button" class="close" data-dismiss="alert">×</button>
				<strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
			</div>

  
  
@endsection   

@section('content')
<div class="row card-description">
	<div class="col-md-12">
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
		<div class="table-responsive">
			<table class="table table-striped table-bordered table-sm info">
				<tbody> 
					<tr>
						<th width="20%">Estado</th>
						<td>{{$orden->estatus()}}</td>
					</tr>
					<tr>
						<th>Proveedor</th> 
						<td><a href="{{route('contactos.show',$orden->proveedor()->id)}}" target="_blanck">{{$orden->proveedor()->nombre}}</a></td>
					</tr>
					<tr>
						<th>Bodega</th>
						<td>{{$orden->bodega()}}</td>
					</tr>
					<tr>
						<th>Fecha</th>
						<td>{{date('d-m-Y', strtotime($orden->fecha))}}</td>
					</tr>
					<tr>
						<th>Fecha de entrega</th>
						<td>{{date('d-m-Y', strtotime($orden->vencimiento))}}</td>
					</tr>
					
					<tr>
                        <th>Comprador</th>
                        <td>{{$orden->compradorName }}</td>
                    </tr>
					
						<th>Observaciones</th>
						<td>{{$orden->observaciones}}</td>
					</tr>
						<th>Notas</th>
						<td>{{$orden->notas}}</td>
					</tr>
						<th>Términos y condiciones	</th>
						<td>{{$orden->term_cond}}</td>
					</tr>
						<th>Total</th>
						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($orden->total()->total)}}</td>
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
                  <td>{{$item->cant}}</td>
                  <td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($item->total())}}</td>
                </tr>

            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <th colspan="6" class="text-right">Subtotal</th>
              <td class="text-right">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($orden->total()->subtotal)}}</td>
            </tr>
            <tr>
              <th colspan="6" class="text-right">Descuento</th>
              <td class="text-right">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($orden->total()->descuento)}}</td>
            </tr>
            <tr>
              <th colspan="6" class="text-right">Subtotal</th>
              <td class="text-right">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($orden->total()->subsub)}}</td>
            </tr>
            @if($orden->total()->imp)
              @foreach($orden->total()->imp as $imp)
                @if(isset($imp->total))
                  <tr>
                    <th colspan="6" class="text-right">{{$imp->nombre}} ({{$imp->porcentaje}}%)</th>
                    <td class="text-right">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($imp->total)}}</td>
                  </tr>
                @endif
              @endforeach
            @endif

            <tr>
              <th colspan="6" class="text-right">TOTAL</th>
              <td class="text-right">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($orden->total()->total)}}</td>
            </tr>
          </tfoot>
        </table>
    </div>
  </div>
  </div>


@if($orden->tipo==1)
@php $factura = $orden->factura(); @endphp
  <div class="row card-description">
  <div class="col-md-8">
    <h5>Factura de compra asociada</h5>
    <div class="table-responsive">
        <table class="table table-striped table-sm desgloce"  width="100%">
          <thead >
            <tr>
              <th>Fecha</th>
              <th>Número</th>
              <th>Monto</th>
              <th>Observaciones</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              
            <td><a href="{{route('facturasp.show',$factura->nro)}}" target="_blanck">{{date('d-m-Y', strtotime($factura->fecha_factura))}}</a></td>
            <td>{{$factura->nro}}</td>
            <td>{{Auth::user()->empresa()->moneda}}{{$factura->total()->total}}</td>
            <td>{{$factura->observaciones_factura}}</td>
            </tr>
          </tbody>
        </table>
    </div>
  </div>
  </div>
@endif
@endsection