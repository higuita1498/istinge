@extends('layouts.app')
@section('boton')
	@if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
	        <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
	    </div>
	@else
		<a href="{{route('remisiones.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nueva Remisión</a>
	@endif
	
@endsection		
@section('content')
	@if(Session::has('success'))
		<div class="alert alert-success" style="margin-left: 2%;margin-right: 2%;">
			{{Session::get('success')}}
		</div>
	@endif
	
	<div class="row card-description">
			<div class="col-md-12 table-responsive">
			<form id="form-table-remisiones">
			<input type="hidden" name="orderby"id="order_by"  value="1">
			<input type="hidden" name="order" id="order" value="0">
			<input type="hidden" id="form" value="form-table-remisiones">
			<div id="filtro_tabla" @if(!$busqueda) style="display: none;" @endif>
				<table class="table table-striped table-hover filtro thresp">				
					<tr class="form-group">
						<th><input type="text" class="form-control form-control-sm" name="name_1" placeholder="Número" value="{{$request->name_1}}"></th>
						<th><input type="text" class="form-control form-control-sm" name="name_2" placeholder="Cliente"  value="{{$request->name_2}}"></th>
						<th><input type="text" class="form-control form-control-sm" name="name_9" placeholder="Vendedor"  value="{{$request->name_9}}"></th>
						<th class="calendar_small"><input type="text" class="form-control form-control-sm datepicker" name="name_3" placeholder="Creación" value="{{$request->name_3}}"></th>
						<th class="calendar_small"><input type="text" class="form-control form-control-sm datepickerinput" name="name_4" placeholder="Vencimiento" value="{{$request->name_4}}"></th>
						<th class="monetario">
							<select name="name_5_simb">
								<option value="=" {{$request->name_5_simb=='='?'selected':''}}>=</option>
								<option value=">" {{$request->name_5_simb=='>'?'selected':''}}>></option>
								<option value="<" {{$request->name_5_simb=='<'?'selected':''}}><</option>
							</select>
							<input type="number" class="form-control form-control-sm" name="name_5" placeholder="Total" value="{{$request->name_5}}"></th>
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

							<input type="number" class="form-control form-control-sm" name="name_7" placeholder="Por Pagar" value="{{$request->name_7}}"></th>
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
					<a href="{{route('remisiones.index')}}" class="btn btn-link no-padding" >Cerrar</a> 
				@endif				
			</div>

			<div class="row">
				<div class="col-md-12">
					<button type="button" @if($busqueda) style="display: none;" @endif class="btn btn-link float-right" id="boto_filtrar" onclick="showdiv('filtro_tabla'); hidediv('boto_filtrar');"><i class="fas fa-search"></i> Filtrar</button>
				</div>
			</div>
			</form>

			<table class="table table-striped table-hover" id="table-remisiones">
			<thead class="thead-dark">
				<tr>
	              <th>Código <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==1?'':'no_order'}}" campo="1" order="@if($request->orderby==1){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==1){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Cliente <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==2?'':'no_order'}}" campo="2" order="@if($request->orderby==2){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==2){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Vendedor <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==9?'':'no_order'}}" campo="9" order="@if($request->orderby==9){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==9){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Creación <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==3?'':'no_order'}}" campo="3" order="@if($request->orderby==3){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==3){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Vencimiento <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==4?'':'no_order'}}" campo="4" order="@if($request->orderby==4){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==4){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Total <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==5?'':'no_order'}}" campo="5" order="@if($request->orderby==5){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==5){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Pagado <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==6?'':'no_order'}}" campo="6" order="@if($request->orderby==6){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==6){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Por Pagar <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==7?'':'no_order'}}" campo="7" order="@if($request->orderby==7){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==7){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Estado <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==8?'':'no_order'}}" campo="8" order="@if($request->orderby==8){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==8){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Acciones</th>
	          </tr>                              
			</thead>
			<tbody>
				@foreach($facturas as $factura)
					<tr @if($factura->id==Session::get('remision_id')) class="active_table" @endif>
						<td><a href="{{route('remisiones.show',$factura->id)}}" >{{$factura->nro}}</a></td>
						<td><div class="elipsis-short-325"><a href="{{route('contactos.show',$factura->cliente()->id)}}" target="_blank">{{$factura->cliente()->nombre}} {{$factura->cliente()->apellidos()}}</a></div></td>
						<td>{{$factura->nombreVendedor}}</td>
						<td>{{date('d-m-Y', strtotime($factura->fecha))}}</td>
						<td class="@if($factura->vencimiento<date('Y-m-d') && $factura->estatus==1) text-danger @endif">{{date('d-m-Y', strtotime($factura->vencimiento))}} </td>
						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->total()->total)}}</td>
						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->pagado())}}</td> 
						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->porpagar())}}</td>
						<td class="text-{{$factura->estatus(true, true)}}">{{$factura->estatus()}}</td> 
						<td>
							@if(auth()->user()->modo_lectura())
							@else
							<a href="{{route('remisiones.show',$factura->id)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a> 
							<a href="{{route('remisiones.imprimir',['id' => $factura->nro, 'name'=> 'Remision No. '.$factura->nro.'.pdf'])}}" target="_black"  class="btn btn-outline-primary btn-icons" title="Imprimir"><i class="fas fa-print"></i></a>
							@if($factura->estatus==1)
								<a  href="{{route('ingresosr.create_id', ['cliente'=>$factura->cliente()->id, 'factura'=>$factura->nro])}}" class="btn btn-outline-primary btn-icons" title="Agregar pago"><i class="fas fa-money-bill"></i></a>						
								<a href="{{route('remisiones.edit',$factura->nro)}}"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>

							<form action="{{ route('remisiones.anular',$factura->nro) }}" method="POST" class="delete_form" style="display: none;" id="anular-factura{{$factura->id}}">
    							{{ csrf_field() }}
							</form>
							
							@if($factura->estatus==1)
							
							<button class="btn btn-outline-danger  btn-icons" type="button" title="Anular" onclick="confirmar('anular-factura{{$factura->id}}', '¿Está seguro de que desea anular la remisión?', ' ');"><i class="fas fa-minus"></i></button>
							@elseif($factura->estatus==2)
							<button class="btn btn-outline-success  btn-icons" type="submit" title="Abrir" onclick="confirmar('anular-factura{{$factura->id}}', '¿Está seguro de que desea abrir la remisión?', ' ');"><i class="fas fa-unlock-alt"></i></button>
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
@endsection