@extends('layouts.app')
@section('boton')
    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
	        <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
	    </div>
	@else
		<a href="{{route('facturas.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nueva Factura de Venta</a>
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
		
		<div class="col-md-12 table-responsive">
			<form id="form-table-facturas">
			<input type="hidden" name="orderby"id="order_by"  value="1">
			<input type="hidden" name="order" id="order" value="0">
			<input type="hidden" id="form" value="form-table-facturas">
			<div id="filtro_tabla" @if(!$busqueda) style="display: none;" @endif>
				<table class="table table-striped table-hover filtro thresp">				
					<tr class="form-group">
						<th><input type="text" class="form-control form-control-sm" name="name_1" placeholder="Número" value="{{$request->name_1}}"></th>
						<th><input type="text" class="form-control form-control-sm" name="name_2" placeholder="Cliente"  value="{{$request->name_2}}"></th>
						<th class="calendar_small"><input type="text" class="form-control form-control-sm datepicker" name="name_3" placeholder="Creación" value="{{$request->name_3}}"></th>
						<th class="calendar_small"><input type="text" class="form-control form-control-sm datepickerinput" name="name_4" placeholder="Vencimiento" value="{{$request->name_4}}"></th>
						<th class="monetario">
							<select name="name_5_simb">
								<option value="=" {{$request->name_5_simb=='='?'selected':''}}>=</option>
								<option value=">" {{$request->name_5_simb=='>'?'selected':''}}>></option>
								<option value="<" {{$request->name_5_simb=='<'?'selected':''}}><</option>
							</select>
							<input type="text" class="form-control form-control-sm" name="name_5" placeholder="Total" value="{{$request->name_5}}"></th>
						<th class="monetario ">
							<select name="name_6_simb">
								<option value="=" {{$request->name_6_simb=='='?'selected':''}}>=</option>
								<option value=">" {{$request->name_6_simb=='>'?'selected':''}}>></option>
								<option value="<" {{$request->name_6_simb=='<'?'selected':''}}><</option>
							</select>
							<input type="text" class="form-control form-control-sm" name="name_6" placeholder="Pagado" value="{{$request->name_6}}"></th>
						<th class="monetario">
							<select name="name_7_simb">
								<option value="=" {{$request->name_7_simb=='='?'selected':''}}>=</option>
								<option value=">" {{$request->name_7_simb=='>'?'selected':''}}>></option>
								<option value="<" {{$request->name_7_simb=='<'?'selected':''}}><</option>
							</select>

							<input type="text" class="form-control form-control-sm" name="name_7" placeholder="Por Pagar" value="{{$request->name_7}}"></th>
						<th><select name="name_8[]" class="form-control-sm selectpicker" title="Estado"  data-width="100px" multiple>	  
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
				<button class="btn btn-link no-padding">Filtrar</button>
				@if(!$busqueda) 
					<button type="button" class="btn btn-link no-padding"  onclick="hidediv('filtro_tabla'); showdiv('boto_filtrar'); vaciar_fitro();">Cerrar</button>

				@else
					<a href="{{route('facturas.index')}}" class="btn btn-link no-padding" >Cerrar</a> 
				@endif				
			</div>

			<div class="row">
				<div class="col-md-12">
					<button type="button" @if($busqueda) style="display: none;" @endif class="btn btn-link float-right" id="boto_filtrar" onclick="showdiv('filtro_tabla'); hidediv('boto_filtrar');"><i class="fas fa-search"></i> Filtrar</button>
				</div>
			</div>
			</form>
			<table class="table table-striped table-hover " id="table-facturas">
			<thead class="thead-dark">
				<tr>
	              <th>Número <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==1?'':'no_order'}}" campo="1" order="@if($request->orderby==1){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==1){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Cliente <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==2?'':'no_order'}}" campo="2" order="@if($request->orderby==2){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==2){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Creación <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==3?'':'no_order'}}" campo="3" order="@if($request->orderby==3){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==3){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Vencimiento <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==4?'':'no_order'}}" campo="4" order="@if($request->orderby==4){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==4){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Total <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==5?'':'no_order'}}" campo="5" order="@if($request->orderby==5){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==5){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
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
						<td><div class="elipsis-short" style="width:135px;"><a href="{{route('contactos.show',$factura->cliente)}}" target="_blanck">{{$factura->nombrecliente}}</a></div></td>
						<td>{{date('d-m-Y', strtotime($factura->fecha))}}</td>
						<td class="@if(date('Y-m-d') > $factura->vencimiento && $factura->estatus==1) text-danger @endif">{{date('d-m-Y', strtotime($factura->vencimiento))}}</td>
						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->total()->total)}}</td>  
						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->pagado())}}</td>
						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->porpagar())}}</td>
						<td class="text-{{$factura->estatus(true)}}">{{$factura->estatus()}}</td>
						<td>
							<a href="{{route('facturas.show',$factura->nro)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a> 
							<a href="{{route('facturas.imprimir',$factura->nro)}}" target="_blank" class="btn btn-outline-primary btn-icons"title="Imprimir"><i class="fas fa-print"></i></a>	
							@if($factura->estatus==1)
								<a  href="{{route('ingresos.create_id', ['cliente'=>$factura->cliente, 'factura'=>$factura->nro])}}" class="btn btn-outline-primary btn-icons" title="Agregar pago"><i class="fas fa-money-bill"></i></a>
								<a href="{{route('facturas.edit',$factura->nro)}}"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
								
							@endif

							<form action="{{ route('factura.anular',$factura->nro) }}" method="POST" class="delete_form" style="display: none;" id="anular-factura{{$factura->id}}">
    						{{ csrf_field() }}
							</form>
							
							@if($factura->estatus==1)
							
							<button class="btn btn-outline-danger  btn-icons" type="button" title="Anular" onclick="confirmar('anular-factura{{$factura->id}}', '¿Está seguro de que desea anular la factura?', ' ');"><i class="fas fa-minus"></i></button>
							@elseif($factura->estatus==2)
							<button class="btn btn-outline-success  btn-icons" type="submit" title="Abrir" onclick="confirmar('anular-factura{{$factura->id}}', '¿Está seguro de que desea abrir la factura?', ' ');"><i class="fas fa-unlock-alt"></i></button>
							@endif
						</td> 
					</tr> 
				@endforeach
			</tbody>
		</table>

		<div class="text-right">
			{{$facturas->links()}}
		</div>
		</div>
	</div>
@endsection
