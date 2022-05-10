@extends('layouts.app')
@section('boton')
@if(auth()->user()->modo_lectura())
      <div class="alert alert-warning text-left" role="alert">
          <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
          <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
      </div>
  @else
  @if($factura->estatus==1 && $factura->tipo==1)
    <a href="{{route('facturasp.edit',$factura->id)}}" class="btn btn-outline-primary btn-sm "title="Editar"><i class="fas fa-edit"></i> Editar</a>
  @endif
@endif
  
  
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
						<th width="20%">Número</th>
						<td>{{$factura->codigo}}</td>
					</tr>
					<tr>
						<th>Proveedor</th> 
						<td><a href="{{route('contactos.show',$factura->proveedor()->id)}}" target="_blanck">{{$factura->proveedor()->nombre}}</a></td>
					</tr>
					<tr>
						<th>Bodega</th>
						<td>{{$factura->bodega()}}</td>
					</tr>
					<tr>
						<th>Fecha</th>
						<td>{{date('d-m-Y', strtotime($factura->fecha_factura))}}</td>
					</tr>
					<tr>
						<th>Vencimiento</th>
						<td>{{date('d-m-Y', strtotime($factura->vencimiento_factura))}}</td>
					</tr>
          <tr>
						<th>Observaciones</th>
						<td>{{$factura->observaciones_factura}}</td>
					</tr>
          <tr>
						<th>Total</th>
						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->total()->total)}}</td>
					</tr>
          <tr>
            <th>Por pagar</th>
            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->porpagar())}}</td>
          </tr>
          <tr>
            <td colspan="2"></td>
          </tr>
          <tr>
            <th>Valor Retenido</th>
            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->total()->totalreten)}}</td>
          </tr>
          <tr>
            <th>Valor pagado</th>
            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->pagado())}}</td>
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
              <th width="12%">Referencia</th>
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
                  <td>{{$item->ref()}}</td>
                  <td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($item->precio)}}</td>
                  <td>{{$item->desc?$item->desc:0}}%</td>
                  <td>{{$item->impuesto()}}</td>
                  <td>{{$item->descripcion}}</td>
                  <td class="text-center">{{$item->cant}}</td>
                  <td class="text-right" style="padding-right: 5px !important;">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($factura->total()->totaltotal)}}</td>
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
              <td class="text-right">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($factura->total()->subtotal2)}}</td>
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
            @foreach($retenciones as $retencion)
                <tr>
                <!--<td>RF </td>
              <td>{{$retencion->retencion()->porcentaje}}%</td>-->
                    <td colspan="7" class="text-right">RF {{$retencion->retencion()->porcentaje}}%</td>
                    <td class="text-right">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($retencion->valor)}}</td>
                </tr>
            @endforeach

            <tr>
              <th colspan="7" class="text-right">TOTAL</th>
              <td class="text-right">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($factura->total()->totaltotal)}}</td>
            </tr>
          </tfoot>
        </table>
    </div>
  </div>
  </div>
    

	<div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
				<button type="button" class="close" data-dismiss="alert">×</button>
				<strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
			</div>
  <div class="row card-description" style="padding: 0 1%; margin-top: 2%;">
  <div class="col-md-12" style="box-shadow: 1px 2px 4px 0 rgba(0,0,0,0.15);background-color: #fff; padding:2% !important;">
    <ul class="nav nav-tabs" id="myTab" role="tablist">
      <li class="nav-item">
        <a class="nav-link active" id="profile-tab" data-toggle="tab" href="#pagos_recibidos" role="tab" aria-controls="pagos_recibidos-tab" aria-selected="false" style="font-size: 1.1em; display: inline-block;">Pagos asociados  
        </a>
        @if($factura->estatus==1)
            <a href="{{route('pagos.create_id', ['proveedor'=>$factura->proveedor()->id, 'factura'=>$factura->nro])}}" class="btn btn-secondary btn-sm btn-rounded text-center" target="_blank" title="Asociar Pago"><i style="display: inline-block;    margin: 0;" class="fas fa-plus"></i></a>
          @endif
      </li>

      @if($factura->notas_debito(true)>0)
        <li class="nav-item">
          <a class="nav-link" id="notas_credito-tab" data-toggle="tab" href="#notas_debito" role="tab" aria-controls="notas_debito" aria-selected="false" style="font-size: 1.1em">Notas débito</a>
        </li>
      @endif
    </ul>
    
    <div class="tab-content" id="myTabContent">
      <div class="tab-pane fade show active fact-table" id="pagos_recibidos" role="tabpanel" aria-labelledby="pagos_recibidos-tab" >
        <table class="table table-striped pagos">
          <thead>
            <th>Fecha</th>
            <th>Comprobante de egreso #</th>
            <th>Estado</th>
            <th>Monto</th>
            <th>Observaciones</th>
          </thead>

          <tbody>
            @foreach($factura->gastos() as $gasto)              
            <tr>
              <td><a href="{{route('pagos.show',$gasto->gasto()->id)}}">{{date('d-m-Y', strtotime($gasto->gasto()->fecha))}}</a></td>
                <td>
                  <a href="{{route('pagos.show',$gasto->gasto()->id)}}">
                      {{$gasto->gasto()->nro}}
                  </a>
                </td>
              <td>{{$gasto->gasto()->estatus()}} </td>
              <td class="text-center">{{Auth::user()->empresa()->moneda}} {{\App\Funcion::Parsear($gasto->pago())}} </td>
              <td>{{$gasto->gasto()->observaciones}} </td>
            </tr> 
            @endforeach
          </tbody>
        </table>          
      </div>

      @if($factura->notas_debito(true)>0)
        <div class="tab-pane fade" id="notas_debito" role="tabpanel" aria-labelledby="notas_debito-tab">
          <table class="table table-striped table-hover pagos">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Nota débito #</th>
                <th>Monto</th>
                <th>Observaciones</th>
              </tr>                              
            </thead>
            <tbody>
              @foreach($factura->notas_debito() as $notas)
                <tr>
                  <td> <a href="{{route('notasdebito.show',$notas->nota()->nro)}}">{{date('d/m/Y', strtotime($notas->nota()->fecha))}}</a> </td>
                  <td>{{$notas->nota()->codigo?$notas->nota()->codigo:$notas->nota()->nro}}</td>
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
@endsection
