@extends('layouts.app')

@section('boton')
    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">Integra Colombia: Suscripción Vencida</h4>
	       <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
	    </div>
	@else
	    @if(isset($_SESSION['permisos']['42']))
		    <a href="{{route('facturas.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nueva Factura de Venta</a>
		@endif
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
			}, 8000);
		</script>
	@endif

	@if(Session::has('error'))
		<div class="alert alert-danger" >
			{{Session::get('error')}}
		</div>

		<script type="text/javascript">
			setTimeout(function(){
			    $('.alert').hide();
			    $('.active_table').attr('class', ' ');
			}, 8000);
		</script>
	@endif

@if(Session::has('message_denied'))
	<div class="alert alert-danger" role="alert">
		{{Session::get('message_denied')}}
		@if(Session::get('errorReason'))<br> <strong>Razon(es): <br></strong>
			@if(is_string(Session::get('errorReason')))
				{{Session::get('errorReason')}}
			@elseif (count(Session::get('errorReason')) >= 1)
				@php $cont = 0 @endphp
				@foreach(Session::get('errorReason') as $error)
					@php $cont = $cont + 1; @endphp
					{{$cont}} - {{$error}} <br>
				@endforeach
			@endif
		@endif
		<button type="button" class="close" data-dismiss="alert" aria-label="Close">
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
@endif

@if(Session::has('message_success'))
<div class="alert alert-success" role="alert">
	{{Session::get('message_success')}}
	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
		<span aria-hidden="true">&times;</span>
	</button>
</div>
@endif
	<div class="row card-description">
		<div class="col-md-12 table-responsive">
			<form id="form-table-facturas">
			<input type="hidden" name="orderby"id="order_by"  value="1">
			<input type="hidden" name="order" id="order" value="0">
			<input type="hidden" id="form" value="form-table-facturas">

			<div id="filtro_tabla" @if(!$busqueda) style="display: none;" @endif>
			@if(Auth::user()->rol==47)
			    <table class="table table-striped table-hover filtro thresp bg-white">
					<tr class="form-group">
						<th>
							<select class="form-control selectpicker" name="name_2" id="name_2" title="Cliente" data-live-search="true" data-size="5">
								@foreach($clientes as $cliente)
								    <option value="{{$cliente->nombre}}" title="{{$cliente->nombre}} - {{$cliente->nit}}" {{$request->name_2==$cliente->nombre?'selected':''}}>{{$cliente->nombre}} - {{$cliente->nit}}</option>
								@endforeach
						    </select>
						</th>
					</tr>
				</table>
			@else
				<table class="table table-striped table-hover filtro thresp bg-white">
					<tr class="form-group">
						<th width="33%">
							<input type="text" class="form-control" name="name_1" placeholder="Número Factura" value="{{$request->name_1}}">
						</th>
						<th width="33%">
							<select class="form-control selectpicker" name="name_2" id="name_2" title="Cliente" data-live-search="true" data-size="5">
								@foreach($clientes as $cliente)
								    <option value="{{$cliente->nombre}}" title="{{$cliente->nombre}} - {{$cliente->nit}}" {{$request->name_2==$cliente->nombre?'selected':''}}>{{$cliente->nombre}} - {{$cliente->nit}}</option>
								@endforeach
						    </select>
						</th>
                        <th width="33%">
                        	<select name="name_10" class="form-control selectpicker" title="Fecha Corte">
							@if($request->name_10)
								<option value="15" @if($request->name_10 == 15) selected="" @endif >Día 15</option>
								<option value="30" @if($request->name_10 == 30) selected="" @endif >Día 30</option>
							@else
								<option value="15" >Día 15</option>
								<option value="30" >Día 30</option>
							@endif
			  				</select>
			  			</th>
					</tr>
					<tr class="form-group">
						<th width="33%">
							<input type="text" class="form-control datepicker" name="name_3" placeholder="F. Creación" value="{{$request->name_3}}" autocomplete="off">
						</th>
						<th width="33%">
							<input type="text" class="form-control datepickerinput" name="name_4" placeholder="F. Vencimiento" value="{{$request->name_4}}" autocomplete="off">
						</th>
						<th width="33%">
							<select name="name_8[]" class="form-control selectpicker" title="Estado Factura" multiple>
							@if(is_array($request->name_8))
								<option value="1" @if(in_array("1", $request->name_8)) selected="" @endif >Abierta</option>
								<option value="0" @if(in_array("0", $request->name_8)) selected="" @endif >Cerrada</option>
								<option value="2" @if(in_array("2", $request->name_8)) selected="" @endif >Anulada</option>
							@else
								<option value="1" >Abierta</option>
								<option value="0" >Cerrada</option>
								<option value="2" >Anulada</option>
							@endif
			  				</select>
			  			</th>
					</tr>
				</table>
			@endif
				<center><button class="my-3 btn btn-outline-primary @if(Auth::user()->rol==47) btn-xl @else btn-sm @endif">Filtrar</button>
				@if(!$busqueda)
					<button type="button" class="my-3 btn btn-outline-danger @if(Auth::user()->rol==47) btn-xl @else btn-sm @endif" onclick="hidediv('filtro_tabla'); showdiv('boto_filtrar'); vaciar_fitro();">Cerrar</button>
				@else
					<a href="{{route('facturas.index')}}" class="my-3 btn btn-outline-danger @if(Auth::user()->rol==47) btn-xl @else btn-sm @endif">Cerrar</a>
				@endif</center>
			</div>

			<div id="imprimir_tabla" style="display: none;">
				<div class="row">
					<div class="form-group col-md-12">
						<div class="row">
							<div class="col-md-2 offset-md-7">
								<select class="form-control-sm form-control selectpicker" id="fecha_creacion" onchange="concatenar();">
									<option selected disabled>F. Corte</option>
									<option value="2020-10-30">30-10-2020</option>
								</select>
							</div>
							<div class="col-md-3">
								<a href="" class="btn btn-outline-success disabled" title="Imprimir Facturas" target="_blank" id="btn_export" role="button" aria-disabled="true">Imprimir</a>
								<button type="button" class="btn btn-outline-danger" onclick="hidediv('imprimir_tabla'); showdiv('boto_imprimir');"><i class="fas fa-close"></i>Cancelar</button>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="col-md-12">
					<button type="button" @if($busqueda) style="display: none;" @endif class="btn btn-outline-primary float-right ml-2 @if(Auth::user()->rol==47) btn-xl @else btn-sm @endif" id="boto_filtrar" onclick="showdiv('filtro_tabla'); hidediv('boto_filtrar');"><i class="fas fa-search"></i> Filtrar</button>
					{{-- <button type="button" @if($busqueda) style="display: none;" @endif class="btn btn-outline-success btn-sm float-right" id="boto_imprimir" onclick="showdiv('imprimir_tabla'); hidediv('boto_imprimir');">Imprimir Facturas</button> --}}
				</div>
			</div>
			</form>
			<table class="table table-striped table-hover " id="table-facturas">
			<thead class="thead-dark">
				<tr>
	              <th>Número <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==1?'':'no_order'}}" campo="1" order="@if($request->orderby==1){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==1){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Cliente <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==2?'':'no_order'}}" campo="2" order="@if($request->orderby==2){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==2){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Vendedor</th>
	              <th>Creación <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==3?'':'no_order'}}" campo="3" order="@if($request->orderby==3){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==3){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Vencimiento <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==4?'':'no_order'}}" campo="4" order="@if($request->orderby==4){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==4){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Total <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==5?'':'no_order'}}" campo="5" order="@if($request->orderby==5){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==5){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>IVA</th>
	              <th>Pagado <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==6?'':'no_order'}}" campo="6" order="@if($request->orderby==6){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==6){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Por Pagar <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==7?'':'no_order'}}" campo="7" order="@if($request->orderby==7){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==7){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
				  <th>Estado <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==8?'':'no_order'}}" campo="8" order="@if($request->orderby==8){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==8){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Acciones</th>
	          </tr>
			</thead>
			<tbody >
				@foreach($facturas as $factura)
					<tr @if($factura->id==Session::get('codigo')) class="active_table" @endif>
						<td><a href="{{route('facturas.show',$factura->nro)}}" >{{$factura->codigo}}</a></td>
						<td><div class="elipsis-short-325" ><a title="{{$factura->nombrecliente}}" href="{{route('contactos.show',$factura->cliente)}}" target="_blanck">{{$factura->nombrecliente}}</a></div></td>
						<td><div class="elipsis-short" style="width:135px;"><a href="{{route('contactos.show',$factura->cliente)}}" target="_blanck">{{$factura->vendedor()}}</a></div></td>
						<td>{{date('d-m-Y', strtotime($factura->fecha))}}</td>
						<td class="@if(date('Y-m-d') > $factura->vencimiento && $factura->estatus==1) text-danger @endif">{{date('d-m-Y', strtotime($factura->vencimiento))}}</td>
						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->total()->total)}}</td>
						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->impuestos_totales())}}</td>
						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->pagado())}}</td>
						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->porpagar())}}</td>
						<td class="text-{{$factura->estatus(true)}}">{{$factura->estatus()}}
						@if(Auth::user()->empresa()->estado_dian == 1)
						{{ $factura->emitida ==1 ? ' - Emitida' : ' - No Emitida' }}
						@endif</td>
                        <td>
                        @if(Auth::user()->rol==47)
                            @if($factura->estatus==1)
                                <a href="{{route('ingresos.create_id', ['cliente'=>$factura->cliente, 'factura'=>$factura->nro])}}" class="btn btn-outline-primary btn-xl" title="Agregar pago"><i class="fas fa-money-bill"></i></a>
                            @endif
                            @if(Auth::user()->empresa()->tirilla && $factura->estatus==0)
                                <a href="{{route('facturas.tirilla', ['id' => $factura->nro, 'name'=> 'Factura No.'.$factura->nro.'.pdf'])}}" target="_blank" class="btn btn-outline-warning btn-xl"title="Imprimir tirilla"><i class="fas fa-file-invoice"></i></a>
                            @endif
                        @else
                            @if(isset($_SESSION['permisos']['41']))
                                <a href="{{route('facturas.show',$factura->nro)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>
                            @endif
							@if(Auth::user()->modo_lectura())
							@else
								<a href="{{route('facturas.imprimir',['id' => $factura->nro, 'name'=> 'Factura No. '.$factura->codigo.'.pdf'])}}" target="_blank" class="btn btn-outline-primary btn-icons"title="Imprimir"><i class="fas fa-print"></i></a>
                                @if(Auth::user()->empresa()->tirilla && $factura->estatus==0)
                                    <a href="{{route('facturas.tirilla', ['id' => $factura->nro, 'name'=> 'Factura No.'.$factura->nro.'.pdf'])}}" target="_blank" class="btn btn-outline-warning btn-icons"title="Imprimir tirilla"><i class="fas fa-file-invoice"></i></a>
                                @endif

								@if($factura->estatus==1)
									<a href="{{route('ingresos.create_id', ['cliente'=>$factura->cliente, 'factura'=>$factura->nro])}}" class="btn btn-outline-primary btn-icons" title="Agregar pago"><i class="fas fa-money-bill"></i></a>
									@if($factura->emitida !=1)
									    @if(isset($_SESSION['permisos']['43']))
									        <a href="{{route('facturas.edit',$factura->nro)}}"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
									    @endif
									@endif
								@endif
								<form action="{{ route('factura.anular',$factura->nro) }}" method="POST" class="delete_form" style="display: none;" id="anular-factura{{$factura->id}}">
									{{ csrf_field() }}
								</form>
								@if(isset($_SESSION['permisos']['43']))
								@if($factura->estatus == 1)
									<button class="btn btn-outline-danger  btn-icons" type="button" title="Anular" onclick="confirmar('anular-factura{{$factura->id}}', '¿Está seguro de que desea anular la factura?', ' ');"><i class="fas fa-minus"></i></button>
								@elseif($factura->estatus==2)
									<button class="btn btn-outline-success  btn-icons" type="submit" title="Abrir" onclick="confirmar('anular-factura{{$factura->id}}', '¿Está seguro de que desea abrir la factura?', ' ');"><i class="fas fa-unlock-alt"></i></button>
								@endif
								@endif
								@if($factura->emailcliente)
								    @if($factura->estatus==1)
                                        @if($factura->correo==0)
								        <a href="{{route('facturas.enviar',$factura->nro)}}" class="btn btn-outline-success btn-icons" title="Enviar"><i class="far fa-envelope"></i></a>
								        @else
								        <button class="btn btn-danger btn-icons disabled" title="Factura enviada por Correo"><i class="far fa-envelope"></i></a>
								        @endif
								    @endif
								@endif
							@endif
						@endif
                        </td>
                    </tr>
				@endforeach
			</tbody>
		</table>

		<div class="text-right">
		    {!!$facturas->render()!!}
		</div>
		</div>
	</div>

	{{-- Modal Imprimir  --}}
		<div class="modal fade" id="modalprint" role="dialog">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-body">
						<div id="urlPrint"></div>
					</div>
				</div>
			</div>
		</div>
	{{--/Modal Imprimir  --}}

    <script>
		function concatenar(){
			var fecha = $("#fecha_creacion").val();

			if(fecha != ''){
				$("#btn_export").removeAttr('href').removeClass('disabled').removeAttr('aria-disabled');
				$("#btn_export").attr('href', 'facturas/facturacion/'+fecha);
			}else{
			    $("#btn_export").removeAttr('href').addClass('disabled').attr('aria-disabled',true);
			}
		}

		function print_invoice(url){
			$("#modalprint").modal('show');
			$("#urlPrint").html('<embed src="'+url+'" frameborder="0" width="100%" height="500px">');
		}
	</script>
@endsection

@section('scripts')
<script>
</script>
@endsection
