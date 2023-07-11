@extends('layouts.app')
@section('boton')
    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
	       <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
	    </div>
	@else
	<a href="{{route('ajustes.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Ajuste de Inventario</a>
	@endif
@endsection		
@section('content')
	@if(Session::has('success'))
		<div class="alert alert-success" style="margin-left: 2%;margin-right: 2%;">
			{{Session::get('success')}}
		</div>
	@endif
		<form id="form-table-ajuste">
			<input type="hidden" name="orderby"id="order_by"  value="1">
			<input type="hidden" name="order" id="order" value="0">
			<input type="hidden" id="form" value="form-table-ajuste">
			<div  class="row card-description nomargin">	
				<div id="filtro_tabla" @if(!$busqueda) style="display: none;" @endif class="col-md-12">
				<table class="table table-striped table-hover filtro thresp" style="width: 100%">				
					<tr class="form-group">
						<th class="calendar_small"><input type="text" class="form-control form-control-sm datepicker" name="name_1" placeholder="Fecha" value="{{$request->name_1}}"></th>
						<th><input type="text" class="form-control form-control-sm" name="name_2" placeholder="Producto"  value="{{$request->name_2}}"></th>
						<th class="monetario ">
							<select name="name_3_simb">
								<option value="=" {{$request->name_3_simb=='='?'selected':''}}>=</option>
								<option value=">" {{$request->name_3_simb=='>'?'selected':''}}>></option>
								<option value="<" {{$request->name_3_simb=='<'?'selected':''}}><</option>
							</select>
							<input type="number" class="form-control form-control-sm" name="name_3" placeholder="Cantidad" value="{{$request->name_3}}">
						</th>

						<th>
							<select name="name_4" class="form-control-sm selectpicker" title="Tipo"  data-width="100px">
								<option value="1" {{$request->name_4=='1'?'selected':''}}>Incremento</option>
								<option value="0" {{$request->name_4=='0'?'selected':''}}>Disminución</option>	      	     
			  				</select>
			  			</th>

						<th class="monetario ">
							<select name="name_5_simb">
								<option value="=" {{$request->name_5_simb=='='?'selected':''}}>=</option>
								<option value=">" {{$request->name_5_simb=='>'?'selected':''}}>></option>
								<option value="<" {{$request->name_5_simb=='<'?'selected':''}}><</option>
							</select>
							<input type="number" class="form-control form-control-sm" name="name_5" placeholder="Costo Unitario" value="{{$request->name_5}}">
						</th>
						<th class="monetario ">
							<select name="name_6_simb">
								<option value="=" {{$request->name_6_simb=='='?'selected':''}}>=</option>
								<option value=">" {{$request->name_6_simb=='>'?'selected':''}}>></option>
								<option value="<" {{$request->name_6_simb=='<'?'selected':''}}><</option>
							</select>
							<input type="number" class="form-control form-control-sm" name="name_6" placeholder="Costo Total" value="{{$request->name_6}}">
						</th>
						<th></th>
					</tr>
				</table>
				<button class="btn btn-link no-padding">Filtrar</button>
				@if(!$busqueda) <button type="button" class="btn btn-link no-padding"  onclick="hidediv('filtro_tabla'); showdiv('boto_filtrar'); vaciar_fitro();">Cerrar</button>

				@else
					<a href="{{route('ajustes.index')}}" class="btn btn-link no-padding" >Cerrar</a> 
				@endif				
			</div>
			<div class="col-md-12">
				<button type="button" @if($busqueda) style="display: none;" @endif class="btn btn-link float-right" id="boto_filtrar" onclick="showdiv('filtro_tabla'); hidediv('boto_filtrar');"><i class="fas fa-search"></i> Filtrar</button>
			</div>
		</div>
	</form>

	<div class="row card-description">
		<div class="col-md-12">
			<table class="table table-striped table-hover" id="notable-general">


			<thead class="thead-dark">
				<tr>
	              <th>Fecha <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==1?'':'no_order'}}" campo="1" order="@if($request->orderby==1){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==1){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Producto <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==2?'':'no_order'}}" campo="2" order="@if($request->orderby==2){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==2){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Cantidad <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==3?'':'no_order'}}" campo="3" order="@if($request->orderby==3){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==3){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Tipo <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==4?'':'no_order'}}" campo="4" order="@if($request->orderby==4){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==4){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Costo Unitario <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==5?'':'no_order'}}" campo="5" order="@if($request->orderby==5){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==5){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Costo Total <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==6?'':'no_order'}}" campo="6" order="@if($request->orderby==6){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==6){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Acciones</th> 
	          </tr>                              
			</thead>

			<tbody>
				@foreach($ajustes as $ajuste)
					<tr @if($ajuste->id==Session::get('ajuste_id')) class="active_table" @endif> 
						<td><a href="{{route('ajustes.show',$ajuste->nro)}}">{{date('d-m-Y', strtotime($ajuste->fecha))}}</a></td>
						<td><a href="{{route('inventario.show',$ajuste->producto)}}">{{$ajuste->producto()->producto}}</a></td>
						<td>{{$ajuste->cant}}</td> 
						<td>{{$ajuste->ajuste()}}</td>
						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($ajuste->costo_unitario)}}</td>
						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($ajuste->total)}}</td>
						<td>
							<a href="{{route('ajustes.show',$ajuste->nro)}}" class="btn btn-outline-info btn-icons"><i class="far fa-eye"></i></i></a>
							<a href="{{route('ajustes.edit',$ajuste->nro)}}" class="btn btn-outline-primary btn-icons"><i class="fas fa-edit"></i></a>
							<form action="{{ route('ajustes.destroy',$ajuste->nro) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-ajustes{{$ajuste->nro}}">
	        						{{ csrf_field() }}
									<input name="_method" type="hidden" value="DELETE">
	    						</form>
	    						<button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-ajustes{{$ajuste->nro}}', '¿Estas seguro que deseas eliminar la ajustes?', 'Los items de la ajustes volveran a su bodega de origen');"><i class="fas fa-times"></i></button>
						</td>
					</tr>
				@endforeach
			</tbody> 
		</table>

		<div class="text-right">
		</div>
		</div>
	</div>
@endsection