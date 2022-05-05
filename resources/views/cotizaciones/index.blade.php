@extends('layouts.app')
@section('boton')
	@if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
	        <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
	    </div>
	@else
	<a href="{{route('cotizaciones.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nueva Cotización</a>
	@endif
@endsection
@section('content')
	@if(Session::has('success'))
		<div class="alert alert-success" style="margin-left: 2%;margin-right: 2%;">
			<button type="button" class="close" data-dismiss="alert">×</button>
			{{Session::get('success')}}
		</div>
	@endif 

	@if(Session::has('error'))
	
		<div class="alert alert-danger" style="margin-left: 2%;margin-right: 2%;">
			<button type="button" class="close" data-dismiss="alert">×</button>
			{{Session::get('error')}}
		</div>
	@endif 
	<div class="row card-description">
		<div class="col-md-12">
			<form id="form-table-facturas">
				<input type="hidden" name="orderby"id="order_by"  value="1">
				<input type="hidden" name="order" id="order" value="0">
				<input type="hidden" id="form" value="form-table-facturas">
				<div id="filtro_tabla" @if(!$busqueda) style="display: none;" @endif>
					<table class="table table-striped table-hover filtro">				
						<tr class="form-group">
							<th><input type="text" class="form-control form-control-sm" name="name_1" placeholder="Número" value="{{$request->name_1}}"></th>
							<th><input type="text" class="form-control form-control-sm" name="name_2" placeholder="Cliente"  value="{{$request->name_2}}"></th>
							<th></th>
							<th class="calendar_small"><input type="text" class="form-control form-control-sm datepicker" name="name_3" placeholder="Creación " value="{{$request->name_3}}"></th>
							<th class="monetario">
								<select name="name_4_simb">
									<option value="=" {{$request->name_4_simb=='='?'selected':''}}>=</option>
									<option value=">" {{$request->name_4_simb=='>'?'selected':''}}>></option>
									<option value="<" {{$request->name_4_simb=='<'?'selected':''}}><</option>
								</select>
								<input type="text" class="form-control form-control-sm" name="name_4" placeholder="Total" value="{{$request->name_4}}">
							</th>	
							<th><select name="name_5" class="form-control-sm selectpicker" title="Estado"  data-width="100px">	  
								<option value="2" {{$request->name_5=='2'?'selected':''}}>Por Cotizar</option>
								<option value="t" {{$request->name_5=='t'?'selected':''}}>Todas</option>		     
			  				</select>
			  			</th>

						</tr>
					</table>
					<button class="btn btn-link no-padding">Filtrar</button>
					@if(!$busqueda) 
						<button type="button" class="btn btn-link no-padding"  onclick="hidediv('filtro_tabla'); showdiv('boto_filtrar'); vaciar_fitro();">Cerrar</button>

					@else
						<a href="{{route('cotizaciones.index')}}" class="btn btn-link no-padding" >Cerrar</a> 
					@endif				
				</div>
				<div class="row">
					<div class="col-md-12">
						<button type="button" @if($busqueda) style="display: none;" @endif class="btn btn-link float-right" id="boto_filtrar" onclick="showdiv('filtro_tabla'); hidediv('boto_filtrar');"><i class="fas fa-search"></i> Filtrar</button>
					</div>
				</div>
			</form>

			<table class="table table-striped table-hover" id="table-cotizacion">
			<thead class="thead-dark">
				<tr>
	              <th>Código <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==1?'':'no_order'}}" campo="1" order="@if($request->orderby==1){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==1){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Cliente <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==2?'':'no_order'}}" campo="2" order="@if($request->orderby==2){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==2){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Creación <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==3?'':'no_order'}}" campo="3" order="@if($request->orderby==3){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==3){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Total <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==4?'':'no_order'}}" campo="4" order="@if($request->orderby==4){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==4){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Estatus <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==5?'':'no_order'}}" campo="5" order="@if($request->orderby==5){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==5){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Acciones</th>
	          </tr>                              
			</thead> 
			<tbody>
				@foreach($facturas as $factura) 
					<tr @if($factura->id==Session::get('codigo')) class="active" @endif>
						<td><a href="{{route('cotizaciones.show',$factura->cot_nro)}}" >{{$factura->cot_nro}}</a> </td>
						<td>@if($factura->cliente)
							<a href="{{route('contactos.show',$factura->cliente)}}" target="_blanck">{{$factura->nombrecliente}}</a>
							@else
								{{$factura->nombrecliente}}
							@endif

							</td>
						<td>{{date('d-m-Y', strtotime($factura->fecha))}}</td>
						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->total)}}</td>
						<td class="text-{{$factura->estatus(true)}}">{{$factura->estatus()}}</td>
						<td>
							@if(auth()->user()->modo_lectura())
							@else
							<a href="{{route('cotizaciones.show',$factura->cot_nro)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>
							@if(Auth::user()->modo_lectura())
							@else
								<a href="{{route('cotizaciones.edit',$factura->cot_nro)}}"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
								@if($factura->estatus!=2)
								<a href="{{route('cotizaciones.imprimir.nombre',['id' => $factura->cot_nro, 'name'=> 'Cotizacion No. '.$factura->cot_nro.'.pdf'])}}" target="_blanck"  class="btn btn-outline-primary btn-icons" title="Imprimir"><i class="fas fa-print"></i></a>
								<form action="{{ route('cotizaciones.destroy',$factura->cot_nro) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-factura{{$factura->id}}">
								{{ csrf_field() }}
								<input name="_method" type="hidden" value="DELETE">
								</form>
								<button class="btn btn-outline-danger  btn-icons negative_paging" type="submit" title="Eliminar" onclick="confirmar('eliminar-factura{{$factura->id}}', '¿Estas seguro que deseas eliminar la cotización?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
								@endif
							@endif
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