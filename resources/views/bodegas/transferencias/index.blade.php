@extends('layouts.app')
@section('boton')	
	<a href="{{route('transferencia.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nueva Transferencia entre Bodegas</a>
	
@endsection		 
@section('content')
	@if(Session::has('success'))
		<div class="alert alert-success">
			{{Session::get('success')}}
		</div>
		<script type="text/javascript">
			setTimeout(function(){ 
			    $('.alert').hide();
			    $('.active_table').attr('class', ' ');
			}, 5000);
		</script>
	@endif

	

	<form id="form-table-ajuste">
		<input type="hidden" name="orderby"id="order_by"  value="1">
		<input type="hidden" name="order" id="order" value="0">
		<input type="hidden" id="form" value="form-table-ajuste">

		<div  class="row card-description nomargin">
			<div id="filtro_tabla" @if(!$busqueda) style="display: none;" @endif class="col-md-12">
				<table class="table table-striped table-hover filtro" style="width: 100%">				
					<tr class="form-group">
						<th><input type="text" class="form-control form-control-sm" name="name_1" placeholder="Código" value="{{$request->name_1}}"></th>
						<th class="calendar_small"><input type="text" class="form-control form-control-sm datepicker" name="name_2" placeholder="Fecha" value="{{$request->name_2}}"></th>
						<th>
							<select name="name_3" class="form-control-sm selectpicker" title="Bodega origen"  data-width="150px">	  
								@foreach($bodegas as $bodega)
			                  		<option {{$request->name_3==$bodega->id?'selected':''}} value="{{$bodega->id}}">{{$bodega->bodega}}</option>
				  				@endforeach     
		  					</select>	
						</th>
						<th>
							<select name="name_4" class="form-control-sm selectpicker" title="Bodega destino"  data-width="150px">	  
								@foreach($bodegas as $bodega)
			                  		<option {{$request->name_4==$bodega->id?'selected':''}} value="{{$bodega->id}}">{{$bodega->bodega}}</option>
				  				@endforeach     
		  					</select>	
						</th>								
						<th></th>
					</tr>
				</table>
				<button class="btn btn-link no-padding">Filtrar</button>
				@if(!$busqueda) <button type="button" class="btn btn-link no-padding"  onclick="hidediv('filtro_tabla'); showdiv('boto_filtrar'); vaciar_fitro();">Cerrar</button>

				@else
					<a href="{{route('transferencia.index')}}" class="btn btn-link no-padding" >Cerrar</a> 
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
						<th>Código <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==1?'':'no_order'}}" campo="1" order="@if($request->orderby==1){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==1){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
						<th>Fecha <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==2?'':'no_order'}}" campo="2" order="@if($request->orderby==2){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==2){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
						<th>Bodega origen<button type="button" class="btn btn-link no-padding orderby {{$request->orderby==3?'':'no_order'}}" campo="3" order="@if($request->orderby==3){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==3){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
						<th>Bodega destino<button type="button" class="btn btn-link no-padding orderby {{$request->orderby==4?'':'no_order'}}" campo="4" order="@if($request->orderby==4){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==4){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
						<th>Acciones</th>
		          </tr>                              
				</thead>
				<tbody>
					@foreach($transferencias as $transferencia)
						<tr @if($transferencia->id==Session::get('transferencia_id')) class="active_table" @endif> 
							<td>{{$transferencia->nro}}</td>
							<td>{{date('d-m-Y', strtotime($transferencia->fecha))}}</td>
							<td>{{$transferencia->bodega()->bodega}}</td>
							<td>{{$transferencia->bodega('destino')->bodega}}</td>
							<td>
								<a href="{{route('transferencia.show',$transferencia->nro)}}" class="btn btn-outline-info btn-icons"><i class="far fa-eye"></i></i></a>
								<a href="{{route('transferencia.imprimir',$transferencia->nro)}}" target="_blank" class="btn btn-outline-primary btn-icons"title="Imprimir"><i class="fas fa-print"></i></a>
								<a href="{{route('transferencia.edit',$transferencia->nro)}}" class="btn btn-outline-primary btn-icons"><i class="fas fa-edit"></i></a>
								<form action="{{ route('transferencia.destroy',$transferencia->nro) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-transferencia{{$transferencia->nro}}">
	        						{{ csrf_field() }}
									<input name="_method" type="hidden" value="DELETE">
	    						</form>
	    						<button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-transferencia{{$transferencia->nro}}', '¿Estas seguro que deseas eliminar la transferencia?', 'Los items de la transferencia volveran a su bodega de origen');"><i class="fas fa-times"></i></button>

							</td>
						</tr>
					@endforeach
				</tbody> 
			</table>
		</div>
	</div>
@endsection