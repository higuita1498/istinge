@extends('layouts.app')	
@section('content')
	<form id="form-table-valorinv">
	<input type="hidden" name="orderby"id="order_by"  value="1">
	<input type="hidden" name="order" id="order" value="0">
	<input type="hidden" id="form" value="form-table-valorinv">

	<div class="row card-description">
		
	    <div class="col-md-4">

  			<div class="form-group row">
  				<label class="col-sm-3 col-form-label">Bodega<span class="text-danger">*</span></label>
  				<div class="col-sm-9 input-group">
  					<select class="form-control selectpicker no-padding" name="valor_bodega" id="valor_bodega" required="">
		         		<option value="0">Todos</option>
						@foreach($bodegas as $bob)
							<option value="{{$bob->id}}" {{$bob->id==$request->valor_bodega?'selected':''}}  >{{$bob->bodega}}</option>
						@endforeach
			        </select>
  				</div>
  			</div>
	  	</div>
	</div>
	<div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
			<button type="button" class="close" data-dismiss="alert">×</button>
			<strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
		</div>
	<div class="row card-description">
		<div class="col-md-12 table-responsive">
			<p>Consulta el valor del inventario actual, la cantidad de items inventariables que tienes y su costo promedio</p>
			<table class="table table-striped table-hover table-sm" >
			<thead class="thead-dark">
				<tr>
	              <th>Ítem <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==1?'':'no_order'}}" campo="1" order="@if($request->orderby==1){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==1){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Referencia <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==2?'':'no_order'}}" campo="2" order="@if($request->orderby==2){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==2){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              @if($request->valor_bodega>0)
					<th>Cantidad en <br> {{$request->bodega}} <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==3?'':'no_order'}}" campo="3" order="@if($request->orderby==3){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==3){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>	              	
	              @endif
	              <th>Cantidad total <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==4?'':'no_order'}}" campo="4" order="@if($request->orderby==4){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==4){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Unidad <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==5?'':'no_order'}}" campo="5" order="@if($request->orderby==5){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==5){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Estado <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==6?'':'no_order'}}" campo="6" order="@if($request->orderby==6){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==6){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Costo Promedio <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==7?'':'no_order'}}" campo="7" order="@if($request->orderby==7){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==7){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Total <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==8?'':'no_order'}}" campo="8" order="@if($request->orderby==8){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==8){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	          </tr>                              
			</thead> 
			<tbody>
				@foreach($productos as $producto)
					<tr>
						<td><a href="{{route('inventario.show',$producto->id)}}" target="_black">{{$producto->producto}}</a></td>
						<td>{{$producto->ref}}</td>
						@if($request->valor_bodega>0)
							<td class="text-right" style="padding-right: 4% !important;">{{$producto->total_bodega}}</td>	              	
						@endif
						<td class="text-right" style="padding-right: 4% !important;">{{$producto->total}}</td>
						<td >{{round($producto->unidad,0)}}</td>
						<td>{{$producto->status==1?'Activo':'Inactivo'}}</td>
						<td class="text-right" style="padding-right: 4% !important;">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($producto->precio)}}</td>
						<td class="text-right" style="padding-right: 4% !important;">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($producto->precio_total)}}</td>
					</tr>
				@endforeach
			</tbody> 
			<tfoot class="thead-dark">
				<tr >
					<th colspan="2" class="text-right">Cantidad Total</th>					
					@if($request->valor_bodega>0)
					<th class="text-right" style="padding-right: 4% !important;">{{$canttotal}}</th>	              	
					@endif
					<th class="text-right" style="padding-right: 4% !important;">{{$totalcant}}</th>
					<th class="text-right" colspan="3" >Valor del Inventario</th>
					<th class="text-right" style="padding-right: 4% !important;">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($valortotal)}}</th>
				</tr>
			</tfoot>
		</table>

		<div class="text-right">
			{{$productos->links()}}
            @if($productos->lastPage() != 1)
                @include('layouts.includes.goTo')
            @endif
		</div>
		</div>
	</div>

	</form>
@endsection
