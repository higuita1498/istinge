@extends('layouts.app')
@section('boton')

	@if(Auth::user()->modo_lectura())
		<div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
	       <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
	    </div>
	@else
	{{-- <div class="btn-group oclt-btneximp" role="group">
	    <button id="btnGroupDrop1" type="button" class="btn btn-secondary dropdown-toggle btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	      <i class="fas fa-upload"></i> Importar desde Excel
	    </button>
	    <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
	      <a class="dropdown-item" href="{{route('inventario.importar')}}">Importar nuevos</a>
	      <a class="dropdown-item" href="{{route('inventario.actualizar')}}">Actualización masiva</a>
	    </div>
  	</div>

	<a href="{{route('inventario.exportar')}}" class="btn btn-secondary btn-sm oclt-btneximp" ><i class="fas fa-download"></i> Exportar</a> --}}
	    @if($type == 'TV')
	        <a href="{{route('inventario.television_create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Plan de TV</a>
	    @else
	        <a href="{{route('inventario.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Producto</a>
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
			}, 5000);
		</script>
	@endif
	
	<style>
	    td .elipsis-short-325 {
	    	width: 325px;
	    	overflow: hidden;
	    	white-space: nowrap;
	    	text-overflow: ellipsis;
	    }
	    @media all and (max-width: 768px){
	    	td .elipsis-short-325 {
	    		width: 225px;
	    		overflow: hidden;
	    		white-space: nowrap;
	    		text-overflow: ellipsis;
	    	}
	    }
	</style>

	<form id="form-table-inventario">
	    <p><h4 class="ml-3">{{ $type == 'TV' ? 'Planes de Televisión' : 'Total Productos' }}: {{$totalProductos}}</h4></p>
		<input type="hidden" name="orderby"id="order_by"  value="1">
		<input type="hidden" name="order" id="order" value="0">
		<input type="hidden" id="form" value="form-table-inventario">
		<div class="row d-none">
			<div class="offset-md-6 col-md-6">
				<div class="form-group offset-md-7 col-md-5">
		  			<label class="control-label">Lista de precios <a><i data-tippy-content="Filtrar por lista de precios creada" class="icono far fa-question-circle"></i></a></label>
		  			<select class="form-control selectpicker" name="lista" id="lista" onchange="document.getElementById('form-table-inventario').submit();">
		  				@foreach($listas as $lista)
	                  		<option {{$lista->nro==$request->lista?'selected':''}} value="{{$lista->nro}}">{{$lista->nombre()}}</option>
		  				@endforeach
	                </select>

		  		</div>			
			</div>
		</div>
		<div  class="row card-description nomargin">		
			<div id="filtro_tabla" @if(!$busqueda) style="display: none;" @endif class="col-md-12">
				<table class="table table-striped table-hover filtro thresp">				
					<tr class="form-group">
						<th><input type="text" class="form-control form-control-sm" name="name_1" placeholder="Referencia" value="{{$request->name_1}}"></th>
						<th><input type="text" class="form-control form-control-sm" name="name_2" placeholder="Producto"  value="{{$request->name_2}}"></th>
						<th class="monetario ">
							<select name="name_3_simb">
								<option value=">" {{$request->name_3_simb=='>'?'selected':''}}>></option>
								<option value="<" {{$request->name_3_simb=='<'?'selected':''}}><</option>
							</select>
							<input type="number" class="form-control form-control-sm" name="name_3" placeholder="Precio" value="{{$request->name_3}}">
						</th>
						<th class="monetario ">
							<select name="name_4_simb">
								<option value=">" {{$request->name_4_simb=='>'?'selected':''}}>></option>
								<option value="<" {{$request->name_4_simb=='<'?'selected':''}}><</option>
							</select>
							<input type="number" class="form-control form-control-sm" name="name_4" placeholder="Disp" value="{{$request->name_4}}">
						</th>
						<th>						
							@if(Auth::user()->empresa()->carrito==1)
								<select name="name_5" class="form-control-sm selectpicker" title="Web" data-width="100px">
									<option value="1" {{$request->name_5=='1'?'selected':''}}>Público</option>
									<option value="0" {{$request->name_5=='0'?'selected':''}}>Oculto</option>
								</select>
							@endif
						</th>

						@php $cont=5; @endphp
		              	@foreach($tabla as $campo)
	                        @php $cont++; $tit='name_'.$cont @endphp

							<th><input type="text" class="form-control form-control-sm" name="name_{{$cont}}" placeholder="{{$campo->nombre}}" value="{{$request->$tit}}"></th>
	                    @endforeach

						<th></th>
					</tr>
				</table>
				<center>
				    <button class="my-3 btn btn-outline-primary btn-sm">Filtrar</button>
				    @if(!$busqueda)
				        <button type="button" class="my-3 btn btn-outline-danger btn-sm"  onclick="hidediv('filtro_tabla'); showdiv('boto_filtrar'); vaciar_fitro();">Cerrar</button>
				    @else
					    <a href="{{route('inventario.index')}}" class="my-3 btn btn-outline-danger btn-sm">Cerrar</a> 
				    @endif
			    </center>
			</div>
			<div class="col-md-12">
				<button type="button" @if($busqueda) style="display: none;" @endif class="btn btn-outline-primary float-right ml-2 btn-sm" id="boto_filtrar" onclick="showdiv('filtro_tabla'); hidediv('boto_filtrar');"><i class="fas fa-search"></i> Filtrar</button>
			</div>
		</div>
        <div class="form-inline ml-3">
            <div class="form-group">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <div class="input-group-text">Mostrar</div>
                    </div>
                    <select name="itemsPage" class=" form-control selectpicker" data-width="100px" onchange="document.getElementById('form-table-inventario').submit();">
                        <option value="1" {{$request->itemsPage=='1'?'selected':''}}>25</option>
                        <option value="2" {{$request->itemsPage=='2'?'selected':''}}>50</option>
                        <option value="3" {{$request->itemsPage=='3'?'selected':''}}>100</option>
                    </select>
                    <div class="input-group-append">
                        <div class="input-group-text">Registros por página</div>
                    </div>
                </div>
            </div>
        </div>
	</form>
	<div class="row card-description">
		<div class="col-md-12">
			<table class="table table-striped table-hover" id="table-inventario">
			<thead class="thead-dark">
				<tr>
					<th>Referencia <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==1?'':'no_order'}}" campo="1" order="@if($request->orderby==1){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==1){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
					<th>Producto <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==2?'':'no_order'}}" campo="2" order="@if($request->orderby==2){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==2){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
					<th>Precio <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==3?'':'no_order'}}" campo="3" order="@if($request->orderby==3){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==3){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
					<th>Disp. <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==4?'':'no_order'}}" campo="4" order="@if($request->orderby==4){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==4){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
					@if(Auth::user()->empresa()->carrito==1)
						<th>Estatus <br> en la Web  <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==5?'':'no_order'}}" campo="5" order="@if($request->orderby==5){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==5){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
					@endif
					@php $cont=6; @endphp
					@foreach($tabla as $campo)
					    <th>{{$campo->nombre}} <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==$cont?'':'no_order'}}" campo="{{$cont}}" order="@if($request->orderby==$cont){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==$cont){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
					    @php $cont++; @endphp
					@endforeach
					<th>Acciones</th>
	          </tr>                              
			</thead>
			<tbody>
				@foreach($productos as $producto)
					<tr @if($producto->id==Session::get('producto_id')) class="active_table" @endif>
						<td><div class="elipsis-short"><a href="{{route('inventario.show',$producto->id)}}">{{$producto->ref}}</a></div></td>
						<td><div class="elipsis-short-325"><a href="{{route('inventario.show',$producto->id)}}" title="{{$producto->producto}}">{{$producto->producto}}</a></div></td>
						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($producto->precio)}}</td>
						<td>{{$producto->tipo_producto==1?round($producto->inventario())." ".$producto->unidad(true):'N/A'}}</td>

	                    @if(Auth::user()->empresa()->carrito==1)
							<td class="{{$producto->web(true)}}">{{$producto->web()}}</td>
						@endif
		             	@foreach($tabla as $campo)
	                        <td>{{$producto->campoExt($campo->campo)}}</td>
	                    @endforeach

						<td>
							<a href="{{route('inventario.show',$producto->id)}}" class="btn btn-outline-info btn-icons"><i class="far fa-eye"></i></i></a>
						@if(Auth::user()->modo_lectura())
						@else
							<a href="{{route('inventario.edit',$producto->id)}}" class="btn btn-outline-primary btn-icons"><i class="fas fa-edit"></i></a>
							<form action="{{ route('inventario.act_desc',$producto->id) }}" method="POST" class="delete_form" style="margin:  0;display: inline-block;" id="act_desc-{{$producto->id}}">
		                    {{ csrf_field() }}
		                	</form>
			                @if($producto->status==1)
			                  <button type="button" class="btn btn-outline-secondary  btn-icons" type="submit" title="Desactivar" onclick="confirmar('act_desc-{{$producto->id}}', '¿Estas seguro que deseas desactivar este producto?', 'No aparecera para seleccionar en la creación de facturas');"><i class="fas fa-power-off"></i></button>
			                @else
			                  <button type="button" class="btn btn-outline-success  btn-icons" type="submit" title="Activar" onclick="confirmar('act_desc-{{$producto->id}}', '¿Estas seguro que deseas activar este producto?', 'Aparecera para seleccionar en la creación de facturas');"><i class="fas fa-power-off"></i></button>
			                @endif

			                @if($producto->uso()==0)
			                  	<form action="{{ route('inventario.destroy',$producto->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-inventario{{$producto->id}}">
	    						{{ csrf_field() }}
								<input name="_method" type="hidden" value="DELETE">
								</form>
								<button type="button" class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-inventario{{$producto->id}}', '¿Estas seguro que deseas eliminar el producto?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
			                @endif

			                @if(Auth::user()->empresa()->carrito==1)				
								<form action="{{ route('inventario.publicar',$producto->id) }}" method="POST" class="delete_form" style="margin:  0;display: none;" id="publicar-inventario{{$producto->id}}">
								{{ csrf_field() }}
								</form>
								@if($producto->publico==1)
								<button type="button" class="btn btn-outline-danger  btn-icons" type="submit" title="Ocultar" onclick="confirmar('publicar-inventario{{$producto->id}}', '¿Estas seguro que deseas ocultar el producto?', 'No se mostrara en la web');"><i class="fas fa-flag"></i></button>
								@else
								<button type="button" class="btn btn-outline-success  btn-icons" type="submit" title="Publicar" onclick="confirmar('publicar-inventario{{$producto->id}}', '¿Estas seguro que deseas publicar el producto en la web?', 'Se mostrara en la web');"><i class="fas fa-flag"></i></button>

								@endif
							@endif
						@endif
						</td> 
					</tr>
				@endforeach

				
			</tbody> 
		</table>

		<div class="text-right">
			{!!$productos->render()!!}
            @if($productos->lastPage() != 1)
                @include('layouts.includes.goTo')
            @endif
		</div>
		</div>
	</div>
@endsection
