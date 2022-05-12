@extends('layouts.app')

@section('boton')
    @if(auth()->user()->modo_lectura())
        <div class="alert alert-warning text-left" role="alert">
            <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
            <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
        </div>
    @else
        @if($gasto->tipo!=4)
    	<a href="{{route('pagos.imprimir.nombre',['id' => $gasto->id, 'name'=> 'Pago No. '.$gasto->nro.'.pdf'])}}" class="btn btn-outline-primary btn-sm "title="Imprimir" target="_blank"><i class="fas fa-print"></i> Imprimir</a>
    	@endif
        @if(isset($_SESSION['permisos']['254']))
    	@if($gasto->nro && $gasto->estatus>0 && $gasto->tipo!=3)
    		<a href="{{route('pagos.edit',$gasto->id)}}" class="btn btn-outline-info btn-sm "title="Editar"><i class="fas fa-edit"></i> Editar</a>
    	@endif
        @endif
    	@if($gasto->beneficiario())
        	<a href="{{route('pagos.enviar',$gasto->id)}}" class="btn btn-outline-primary btn-sm "title="Enviar"><i class="far fa-envelope"></i> Enviar por Correo Al Cliente</a>
        @endif
    	@if($gasto->tipo!=3)
            @if(isset($_SESSION['permisos']['255']))
    		<form action="{{ route('pagos.destroy',$gasto->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-gasto{{$gasto->id}}">
    		{{ csrf_field() }}
    		<input name="_method" type="hidden" value="DELETE">
    		</form>
    		<button class="btn btn-outline-danger btn-sm" type="submit" title="Eliminar" onclick="confirmar('eliminar-gasto{{$gasto->id}}', '¿Estas seguro que deseas eliminar el gasto?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i> Eliminar</button>
            @endif
            @if(isset($_SESSION['permisos']['254']))
    		<form action="{{ route('pagos.anular',$gasto->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="anular-gasto{{$gasto->id}}">
    		{{ csrf_field() }}
    		</form>
    		@if($gasto->estatus==1)
    		<button class="btn btn-outline-danger btn-sm" type="submit" title="Anular" onclick="confirmar('anular-gasto{{$gasto->id}}', '¿Está seguro de que desea anular el gasto?', ' ');"><i class="fas fa-minus"></i> Anular</button>
    		@else
    		<button class="btn btn-outline-success btn-sm" type="submit" title="Abrir" onclick="confirmar('anular-gasto{{$gasto->id}}', '¿Está seguro de que desea abrir el gasto?', ' ');"><i class="fas fa-unlock-alt"></i> Abrir</button>
    		@endif
            @endif
    	@endif
    @endif

    <div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
        <button type="button" class="close" data-dismiss="alert">×</button>
        <strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
	</div>
@endsection

@section('style')
    <style>
        .elipsis-short {
            width: 500px !important;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }
        @media all and (max-width: 768px){
            .elipsis-short {
                width: 250px;
                overflow: hidden;
                white-space: nowrap;
                text-overflow: ellipsis;
            }
        }
    </style>
@endsection


@section('content')
    <div class="row card-description">
    	<div class="col-md-12">
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
    		<div class="table-responsive">
    			<table class="table table-striped table-bordered table-sm info">
    				<tbody>
    					<tr>
    						<th width="20%">Comprobante de egreso</th>
    						<td>{{$gasto->nro}}</td>
    					</tr>
    					<tr>
    						<th>Estado</th>
    						<td>{{$gasto->estatus()}}</td>
    					</tr>
    					<tr>
    						<th>Beneficiario</th>
    						<td>@if($gasto->beneficiario())<a href="{{route('contactos.show',$gasto->beneficiario()->id)}}" target="_blank">{{$gasto->beneficiario()->nombre}} {{$gasto->beneficiario()->apellidos()}}</a>@else {{Auth::user()->empresa()->nombre}} @endif</td>
    					</tr>
    					<tr>
    						<th>Fecha</th>
    						<td>{{date('d-m-Y', strtotime($gasto->fecha))}}</td>
    					</tr>
    					<tr>
    						<th>Cuenta</th>
    						<td>{{$gasto->cuenta()->nombre}}</td>
    					</tr>
    					</tr>
    						<th>Observaciones</th>
    						<td>{{$gasto->observaciones}}</td>
    					</tr>
    					</tr>
    						<th>Notas</th>
    						<td>{{$gasto->notas}}</td>
    					</tr>
    					<tr>
    						<th>Método de pago</th>
    						<td>{{$gasto->metodo_pago()}}</td>
    					</tr>
    					</tr>
    						<th>Total</th>
    						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($gasto->pago())}}</td>
    					</tr>
                        @if($gasto->created_by)
                        <tr>
                            <th><strong>Realizado por</strong></th>
                            <td>{{$gasto->created_by()->nombres}}</td>
                        </tr>
                        @endif
                        @if($gasto->updated_by)
                        <tr>
                            <th><strong>Actualizado por</strong></th>
                            <td>{{$gasto->updated_by()->nombres}}</td>
                        </tr>
                        @endif
    				</tbody>
    			</table>
    		</div>
    	</div>
    </div>

	<div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
		<button type="button" class="close" data-dismiss="alert">×</button>
		<strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
	</div>

    @if($gasto->tipo==1)
    <div class="row card-description">
    	<div class="col-md-12">
    		<div class="table-responsive">
    			<table class="table table-striped table-bordered table-sm pagos">
    				<thead>
    					<tr>
    						<th>Número</th>
    						<th>Fecha</th>
    						<th>Vencimiento</th>
    						<th>Total</th>
    						<th>Pagado</th>
    						<th>Por Pagar</th>
    						<th>Monto</th>
    						<th>Retenciones</th>
    						<th>Total</th>
    					</tr>
    				</thead>
    				<tbody>
    					@foreach($items as $item)
    						<tr>
    							<td><a href="{{route('facturasp.showid', $item->factura)}}" target="_blank">{{$item->factura()->codigo}}</a></td>
    							<td>{{date('d-m-Y', strtotime($item->factura()->fecha_factura))}}</td>
    							<td>{{date('d-m-Y', strtotime($item->factura()->vencimiento_factura))}}</td>
    							<td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($item->factura()->total()->total)}}</td>
    							<td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($item->pagado)}}</td>
    							<td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear(($item->factura()->porpagar()))}}</td>
    							<td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($item->pago)}}</td>
    							<td class="retencion">@foreach($item->retenciones() as $retenido)
    								<div class="row">
    									<div class="col-md-12" >
    										<div class="row">
    											<div class="col-md-8 text-right"><small>{{$retenido->retencion()->nombre}} {{$retenido->retencion}}%</small></div>
    											<div class="col-md-4 text-right"><small>-{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($retenido->valor)}}</small></div>
    										</div>
    									</div>
    								</div>
    				                @endforeach
    				            </td>
    							<td>
    								{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($item->pago())}}
    							</td>
    						</tr>
    					@endforeach
    				</tbody>

    			</table>
    		</div>
    	</div>
    </div>
    @elseif($gasto->tipo==2  || $gasto->tipo==4 || $gasto->tipo==5)
    <div class="row card-description">
    	<div class="col-md-12">
    		<div class="table-responsive">
    			<table class="table table-striped table-bordered table-sm pagos">
    				<thead>
    					<tr>
    						<th class="text-left">Categoria</th>
    						<th>Valor</th>
    						<th>Impuesto</th>
    						<th>Cantidad</th>
    						<th>Observaciones</th>
    						<th>Total</th>
    					</tr>
    				</thead>
    				<tbody>
    					@foreach($items as $item)
    						<tr>
    							<td class="text-left"><div class='elipsis-short'>{{$item->categoria()}}</div></td>
    							<td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($item->valor)}}</td>
    							<td>{{$item->impuesto()}}</td>
    							<td>{{round($item->cant,3)}}</td>
    							<td>{{$item->descripcion}}</td>
    							<td class="text-right">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($item->valor)}}</td>
    						</tr>
    					@endforeach
    				</tbody>
    				<tfoot>
    					<tr>
    						<th colspan="4" style="background-color: #ffffff;border-color: #ffffff;"></th>
    						<th class="text-right">Subtotal</th>
    						<td class="text-right">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($gasto->total()->subtotal)}}</td>
    					</tr>
    					@php $cont=0; @endphp
    			        @if($gasto->total()->imp)
    			        @foreach($gasto->total()->imp as $imp)
    			            @if(isset($imp->total))
    			              @php $cont+=1; @endphp
    			              <tr id="imp{{$cont}}">
    							<td colspan="4"></td>
    			                <th class="text-right">{{$imp->nombre}} ({{$imp->porcentaje}}%)</td>
    			                <td class="text-right">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($imp->total)}}</td>
    			              </tr>
    			            @endif
    			        @endforeach
    			        @endif

    			        @php $cont=0; @endphp
    			          @if($gasto->total()->reten)
    			          @foreach($gasto->total()->reten as $reten)
    			              @if(isset($reten->total))
    			                 <tr>
    								<td colspan="4"></td>
    			                 	<th  class="text-right" style="font-size: 0.8em;">{{$reten->nombre}} ({{$reten->porcentaje}}%)</td>
    			                 	<td class="text-right">-{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($reten->total)}} </td></tr>
    			                @php $cont+=1; @endphp
    			              @endif
    			          @endforeach
    			          @endif

    			          <tr>
    						<td colspan="4"></td>
    			          	<th class="text-right">TOTAL</td>
              				<td class="text-right">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($gasto->total()->total)}}</td>
    			          </tr>
    				</tfoot>

    			</table>
    		</div>
    	</div>
    </div>
    @else
    <div class="row card-description">
    	<div class="col-md-8">
    		<div class="table-responsive">
    			<table class="table table-striped table-bordered table-sm pagos">
    				<thead>
    					<tr>
    						<th class="text-left">Nota crédito</th>
    						<th class="text-left">Fecha</th>
    						<th class="text-right">Valor devuelto</th>
    					</tr>
    				</thead>
    				<tfoot>
    					<tr>
    						<td class="text-left"><a href="{{route('notascredito.show',$gasto->notas()->nro)}}" >{{$gasto->notas()->nro}}</a> </td>
    						<td class="text-left">{{date('d-m-Y', strtotime($gasto->fecha))}}</td>
    						<td class="text-right">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($gasto->total_credito)}}</td>
    					</tr>
    				</tfoot>
    			</table>
    		</div>
    	</div>
    </div>
    @endif
@endsection
