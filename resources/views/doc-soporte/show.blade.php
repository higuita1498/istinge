@extends('layouts.app')
@section('boton')
@if(Auth::user()->modo_lectura())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <a>Estas en modo lectura, si deseas seguir disfrutando de nuestros servicios adquiere alguno de nuestros planes <a class="text-black" href="{{route('PlanesPagina.index')}}"> <b>Click Aquí.</b></a></a>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@else
  <a href="{{route('downloadFacturasPApp',$factura->id)}}" class="btn btn-outline-primary btn-sm d-lg-none" title="Descargar"><i class="fas fa-download"></i> Descargar</a>
  @if($factura->estatus==1 && $factura->tipo==1 && $factura->pagos_anulados() == true || $factura->tipo == 1 && $factura->estatus == 0 && env('APP_URL') == "https://gestordepartes.net" && Auth::user()->empresa == 128)
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
                        <th colspan="2"></th>
                    </tr>
					<tr>
						<th width="20%">Número</th>
						<td>{{$factura->nro}}</td>
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
          @if($factura->created_by)
          <tr>
            <th>Creado por</th>
            <td>{{ $factura->author->nombres}}</td>
          </tr>
          @endif
          @if($factura->updated_by)
          <tr>
            <th>Actualizado por</th>
            <td>{{ $factura->updated_by()->nombres}}</td>
          </tr>
          @endif
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
						<td>{{Auth::user()->empresaObj->moneda}}{{App\Funcion::Parsear($factura->total()->total)}}</td>
					</tr>
          <tr>
            <th>Por pagar</th>
            <td>
              @if($factura->estatus == 4 || $factura->estatus == 3)
                {{Auth::user()->empresaObj->moneda}}0
              @else
                {{Auth::user()->empresaObj->moneda}}{{App\Funcion::Parsear($factura->porpagar())}}
              @endif
            </td>
          </tr>
          <tr>
            <th>Estatus</th>
            <td>
              @if ($factura->estatus == 0)
                <span class="text-danger font-weight-bold">Cerrada</span>
              @endif
              @if ($factura->estatus == 1)
                <span class="text-success font-weight-bold">Abierta</span>
              @endif
              @if ($factura->estatus == 2)
                <span class="text-info font-weight-bold">Por pagar</span>
              @endif
              @if ($factura->estatus == 3)
                <span class="text-danger font-weight-bold">Cerrada por Devolución</span>
              @endif
              @if ($factura->estatus == 4)
                <span class="text-danger font-weight-bold">Cerrada con Devolución</span>
              @endif
              @if ($factura->estatus == 5)
                <span class="text-success font-weight-bold">Abierta con Devolución</span>
              @endif
            </td>
            @if($factura->orden_nro)
            <tr>
              <th>Orden de Compra Asociada</th>
              <td><a href="{{route('ordenes.show',$factura->orden_nro)}}" target="_blank">Nro. {{ $factura->orden_nro }}</a></td>
            </tr>
            @endif
          </tr>
          <tr>
            <td colspan="2"></td>
          </tr>
          <tr>
            <th>Valor Retenido</th>
            <td>{{Auth::user()->empresaObj->moneda}}{{App\Funcion::Parsear($factura->total()->totalreten)}}</td>
          </tr>
          <tr>
            <th>Valor pagado</th>
            <td>{{Auth::user()->empresaObj->moneda}}{{App\Funcion::Parsear($factura->pagado())}}</td>
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
                  <td>{{Auth::user()->empresaObj->moneda}} {{App\Funcion::Parsear($item->precio)}}</td>
                  <td>{{$item->desc?$item->desc:0}}%</td>
                  <td>{{$item->impuesto()}}</td>
                  <td>{{$item->descripcion}}</td>
                  <td class="text-center">{{round($item->cant, 3)}}</td>
                  <td class="text-right" style="padding-right: 5px !important;">{{Auth::user()->empresaObj->moneda}} {{App\Funcion::Parsear($item->totalImp())}}</td>
                </tr>

            @endforeach
          </tbody>
          <tfoot>
            <tr>
              <th colspan="7" class="text-right">Subtotal</th>
              <td class="text-right">{{Auth::user()->empresaObj->moneda}} {{App\Funcion::Parsear($factura->total()->subtotal)}}</td>
            </tr>
            <tr>
              <th colspan="7" class="text-right">Descuento</th>
              <td class="text-right">{{Auth::user()->empresaObj->moneda}} {{App\Funcion::Parsear($factura->total()->descuento)}}</td>
            </tr>

            <tr>

              <th colspan="7" class="text-right">Subtotal</th>
              <td class="text-right">{{Auth::user()->empresaObj->moneda}} {{App\Funcion::Parsear($factura->total()->subtotal2)}}</td>
            </tr>

            @if($factura->total()->imp)
              @foreach($factura->total()->imp as $imp)
                @if(isset($imp->total))
                  <tr>
                    <th colspan="7" class="text-right">{{$imp->nombre}} ({{$imp->porcentaje}}%)</th>
                    <td class="text-right">{{Auth::user()->empresaObj->moneda}}{{App\Funcion::Parsear($imp->total)}}</td>
                  </tr>
                @endif
              @endforeach
            @endif
            @foreach($retenciones as $retencion)
                <tr>
                <!--<td>RF </td>
              <td>{{$retencion->retencion()->porcentaje}}%</td>-->
                    <td colspan="7" class="text-right">RF {{$retencion->retencion()->porcentaje}}%</td>
                    <td class="text-right">{{Auth::user()->empresaObj->moneda}} {{App\Funcion::Parsear($retencion->valor)}}</td>
                </tr>
            @endforeach

            <tr>
              <th colspan="7" class="text-right">TOTAL</th>
              <td class="text-right">{{Auth::user()->empresaObj->moneda}} {{App\Funcion::Parsear($factura->total()->total)}}</td>
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
        @if($factura->estatus==1 || $factura->estatus==5)
            <a href="{{route('pagos.create_id', ['cliente'=>$factura->proveedor()->id, 'factura'=>$factura->nro])}}" class="btn btn-secondary btn-sm btn-rounded text-center" target="_blank" title="Asociar Pago"><i style="display: inline-block;    margin: 0;" class="fas fa-plus"></i></a>
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
              <td class="text-center">{{Auth::user()->empresaObj->moneda}} {{\App\Funcion::Parsear($gasto->pago())}} </td>
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
                  <td> <a href="{{route('notasdebito.show',$notas->nota()->id)}}">{{date('d-m-Y', strtotime($notas->nota()->fecha))}}</a> </td>
                  <td><a href="{{route('notasdebito.show',$notas->nota()->id)}}">{{$notas->nota()->codigo}}</a></td>
                  <td>{{Auth::user()->empresaObj->moneda}} {{App\Funcion::Parsear($notas->nota()->total()->total)}}</td>
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
