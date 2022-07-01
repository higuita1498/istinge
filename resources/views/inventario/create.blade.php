@extends('layouts.app')
@section('content')
	@if(Session::has('error'))
	    <div class="alert alert-danger">
	      {{Session::get('error')}}
	    </div>
	  @endif
	<form method="POST" action="{{ route('inventario.store') }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-inventario" enctype="multipart/form-data">
  		{{ csrf_field() }}
  		<div class="row"> 
			<div class="form-group col-md-4">
	  			<label class="control-label">Nombre del Producto <span class="text-danger">*</span></label>
				<input type="text" class="form-control" name="producto" id="producto" required="" maxlength="200" value="{{old('producto')}}">
				<span class="help-block error">
		        	<strong>{{ $errors->first('producto') }}</strong>
		        </span>
		        
			</div>
			<div class="form-group col-md-4">
	  			<label class="control-label">Referencia <span class="text-danger">*</span></label>
				<input type="text" class="form-control" name="ref" id="ref" maxlength="200" value="{{old('ref')}}">
				<span class="help-block error">
		        	<strong>{{ $errors->first('ref') }}</strong>
		        </span>
			</div>
			<div class="form-group col-md-4">
				<label class="control-label">Impuesto <span class="text-danger">*</span></label>
				<select class="form-control selectpicker" name="impuesto" id="impuesto" required="" title="Seleccione">
					@foreach($impuestos as $impuesto)
						<option {{old('impuesto')==$impuesto->id?'selected':''}} value="{{$impuesto->id}}">{{$impuesto->nombre}} - {{$impuesto->porcentaje}} %</option>
					@endforeach
			  </select>
			  <span class="help-block error">
				  <strong>{{ $errors->first('impuesto') }}</strong>
			  </span>
			</div>
  		</div>
  		<div class="row">
			<div class="form-group col-md-5">
				<label class="control-label">Precio de Venta <span class="text-danger">*</span></label>
				<input type="number" class="form-control " id="precio" name="precio" required="" maxlength="24" value="{{old('precio')}}" placeholder="{{Auth::user()->empresa()->moneda}}" min="0" >
				<span class="help-block error">
					<strong>{{ $errors->first('precio') }}</strong>
				</span>
				<span class="litte">Use el punto (.) para colocar decimales</span>
			</div>
  			<div class="form-group col-md-7 ">
	  			<div class="row">
	  				<div class="col-md-6 monetario">
	  					
	  				</div>
	  				<div class="col-md-6" style="padding-top: 6%;padding-left: 0;"><button type="button" class="btn btn-link " style="padding-left: 0;" onclick="agregarlista_precios();" @if(json_encode($listas)=='[]') title="Usted no tiene lista de precios registrada" @endif><i class="fas fa-plus"></i> Agregar otra lista de precio</button></div>
	  			</div>
	  			<div class="row" id="lista_precios_inventario">
	  				<div class="col-md-12">
	  					<table id="table_lista_precios">
	  						<tbody>
	  						</tbody>
	  					</table>
	  				</div>
	  			</div>

			</div>

			<div class="form-group col-md-4 ">
				<div class="row">
				</div>
			</div>

  		</div>
		  <div class="row">
			<div class="form-group col-md-3">
				<label class="control-label">Inventario <span class="text-danger">*</span></label>
				<select class="form-control selectpicker" data-live-search="true" data-size="5" name="inventario" id="inventario"  title="Seleccione">
					@foreach($cuentas as $cuenta)
						<option {{old('inventario')==$cuenta->id?'selected':''}} value="{{$cuenta->id}}">{{$cuenta->nombre}} - {{$cuenta->codigo}}</option>
					@endforeach
			  	</select>
			  <span class="help-block error">
				  <strong>{{ $errors->first('inventario') }}</strong>
			  </span>
			</div>
			<div class="form-group col-md-3">
				<label class="control-label">Costo <span class="text-danger">*</span></label>
				<select class="form-control selectpicker" data-live-search="true" data-size="5" name="costo" id="costo"  title="Seleccione">
					@foreach($cuentas as $cuenta)
						<option {{old('costo')==$cuenta->id?'selected':''}} value="{{$cuenta->id}}">{{$cuenta->nombre}} - {{$cuenta->codigo}}</option>
					@endforeach
			  </select>
			  <span class="help-block error">
				  <strong>{{ $errors->first('costo') }}</strong>
			  </span>
			</div>
			<div class="form-group col-md-3">
				<label class="control-label">Venta <span class="text-danger">*</span></label>
				<select class="form-control selectpicker" data-live-search="true" data-size="5" name="venta" id="venta"  title="Seleccione">
					@foreach($cuentas as $cuenta)
						<option {{old('venta')==$cuenta->id?'selected':''}} value="{{$cuenta->id}}">{{$cuenta->nombre}} - {{$cuenta->codigo}}</option>
					@endforeach
			  </select>
			  <span class="help-block error">
				  <strong>{{ $errors->first('venta') }}</strong>
			  </span>
			</div>
			<div class="form-group col-md-3">
				<label class="control-label">Devolución <span class="text-danger">*</span></label>
				<select class="form-control selectpicker" data-live-search="true" data-size="5" name="devolucion" id="devolucion"  title="Seleccione">
					@foreach($cuentas as $cuenta)
						<option {{old('devolucion')==$cuenta->id?'selected':''}} value="{{$cuenta->id}}">{{$cuenta->nombre}} - {{$cuenta->codigo}}</option>
					@endforeach
			  </select>
			  <span class="help-block error">
				  <strong>{{ $errors->first('devolucion') }}</strong>
			  </span>
			</div>
		  </div>
		  <div class="row">
			<div class="form-group col-md-5">
	  			
			</div>
  			<div class="form-group col-md-7 ">
	  			<div class="row">
	  				<div class="col-md-6 monetario">
	  				</div>
	  				<div class="col-md-6" style="padding-top: 1%;padding-left: 0;"><button type="button" class="btn btn-link " style="padding-left: 0;" onclick="agregar_cuenta();" @if(json_encode($cuentas)=='[]') title="Usted no tiene cuentas registradas" @endif><i class="fas fa-plus"></i> Agregar otras cuentas contables</button></div>
	  			</div>
	  			<div class="row" id="lista_cuentas">
	  				<div class="col-md-12">
	  					<table id="table_cuentas">
	  						<tbody>
	  						</tbody>
	  					</table>
	  				</div>
	  			</div>

			</div>
  		</div>
  		<div class="row">
			<div class="form-group col-md-8">
	  			<label class="control-label" for="email">Descripción</label>
	  			<textarea class="form-control {{auth()->user()->empresa()->carrito == 1 ? 'ckeditor' : ''}}" name="descripcion" id="descripcion" rows="4" value="{{old('descripcion')}}"></textarea>
				{{--<input type="text" class="form-control" id="email" name="descripcion" maxlength="255"  value="{{old('descripcion')}}">--}}
				<div class="help-block error with-errors"></div>
				<span class="help-block error">
					<strong>{{ $errors->first('descripcion') }}</strong>
				</span>
			</div>

  			<div class="form-group col-md-4">
	  			<label class="control-label"><button type="button" class="btn btn-link btn-fw" id="button_show_div_img">Imagen (Opcional)</button></label>
	  			<div style="display: none;" id="div_imagen">
	  				<input type="file" class="dropify" name="imagen" />
					<span class="help-block error">
			        	<strong>{{ $errors->first('imagen') }}</strong>
			        </span>
	  			</div>
			</div>
			
			
  		</div>
  		@if(Auth::user()->empresa()->carrito==1)
  		<div class="row" >
	  		<div class="col-md-12">
	  			<div class="form-group row">
					<label for="publico" class="col-md-3 col-form-label">¿Estara el producto público en la web? <a><i data-tippy-content="Si eliges 'si' automaticamente al presionar guardar el producto irá a tu tienda online" class="icono far fa-question-circle"></i></a></label>
				    <div class="col-md-2">
				    	<div class="row">
							<div class="col-sm-6">
							<div class="form-radio">
								<label class="form-check-label">
								<input type="radio" class="form-check-input" name="publico" id="publico1" value="1" > Si
								<i class="input-helper"></i></label>
							</div>
						</div>
						<div class="col-sm-6">
							<div class="form-radio">
								<label class="form-check-label">
								<input type="radio" class="form-check-input" name="publico" id="publico" value="0" checked=""> No
								<i class="input-helper"></i></label>
							</div>
						</div>
						</div>
				    </div>
				
				</div>
			</div>
		</div>	

		<div class="row">
	  		<div class="col-md-12 form-group">
	  			Imagenes extras
	  			<input type="file" class="form-control" name="imagenes_extra[]" multiple/>
	  		</div>
		</div>	
		@endif	

  		<div class="row" >

			<div class="form-group col-md-4">
	  			<label class="control-label">¿Producto Inventariable? <a><i data-tippy-content="Son productos que tienen cantidad y un precio unitario" class="icono far fa-question-circle"></i></a></label>
				<div class="row">
					<div class="col-sm-6">
					<div class="form-radio">
						<label class="form-check-label">
						<input type="radio" class="form-check-input" name="tipo_producto" id="tipo_producto1" value="2" checked=""> No
						<i class="input-helper"></i></label>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="form-radio">
						<label class="form-check-label">
						<input type="radio" class="form-check-input" name="tipo_producto" id="tipo_producto2" value="1" @if(old('tipo_producto')==1) checked="" @endif> Si
						<i class="input-helper"></i></label>
					</div>
				</div>
				</div>
				<span class="help-block error">
					<strong>{{ $errors->first('tipo_producto') }}</strong>
				</span>
			</div>
			
			<div class="form-group col-md-4 {{ $type == 'TV' ? 'd-none' : '' }}">
	  			<label class="control-label">Tipo <span class="text-danger">*</span></label>
	  			<select class="form-control selectpicker" name="type" id="type" required="" title="Seleccione" data-live-search="true" data-size="5">
	  			    <option value="MATERIAL">MATERIAL</option>
	  			    <option value="MODEMS">MODEMS</option>
	  			    @if($type == 'TV')
	  			    <option value="TV" {{ $type == 'TV' ? 'selected' : '' }}>TV</option>
	  			    @endif
                </select>
				<span class="help-block error">
		        	<strong>{{ $errors->first('type') }}</strong>
		        </span>
			</div>

  			<div id="inventariable" class="col-md-10" style="@if(old('tipo_producto')) display: block; @else display: none; @endif  ">
				<div class="row">
					<div class="form-group col-md-4" >
						<label class="control-label">Unidad de medida</label>
						<select class="form-control selectpicker" name="unidad" id="unidad" required="" title="Seleccione" data-live-search="true" data-size="5">
			  				@foreach($medidas as $medida)
			  					<optgroup label="{{$medida->medida}}">
	    							@foreach($unidades as $unidad)
	    							@if($medida->id==$unidad->tipo)
	    								<option {{old('unidad')==$unidad->id?'selected':''}} value="{{$unidad->id}}">{{$unidad->unidad}}</option>
	    							@endif
	    							@endforeach
 								 </optgroup>

		                  		
			  				@endforeach
		                </select>
						<strong>{{ $errors->first('unidad') }}</strong>
					</div>
					<div class="form-group col-md-4 monetario" >
						<label class="control-label">Costo unidad</label>
						<input type="number" class="form-control" name="costo_unidad" id="costo_unidad" required="" maxlength="24" min="0" value="{{old('costo_unidad')}}" placeholder="{{Auth::user()->empresa()->moneda}}">
						<span class="help-block error">
						<strong>{{ $errors->first('costo_unidad') }}</strong>
						</span>
						<span class="litte">Use el punto (.) para colocar decimales</span>
					</div>
				</div>
	  			<div class="row" id="bodega_inventario">
	  				<div class="col-md-8 form-group">
	  					<table id="table_bodega">
	  						<tbody>

	  						</tbody>
	  					</table>
	  				</div>
	  			</div>
	  			<button type="button" class="btn btn-link" onclick="agregarbodega_inventario();" style="padding-top: 0;"><i class="fas fa-plus"></i> Agregar en otra bodega</button>
  			</div>
  			
  			@if(auth()->user()->empresa()->carrito == 1)
            <div class="form-group col-md-2">
                <label class="control-label">Asignar a una lista</label>
                <select name="list" class="form-control">
                    <option value="0" selected>Ninguna</option>
                    <option value="1">Más vendidos</option>
                    <option value="2">Recientes</option>
                    <option value="3">Oferta</option>
                </select>
            </div>
            
               <div class="form-group col-md-6">
            	<label class="control-label">Link<a><i data-tippy-content="Si tienes mas información como un video, historia o página referente al producto deja el link acá" class="icono far fa-question-circle"></i></a></label>
				<input type="text" class="form-control" name="link" id="link" maxlength="400" value="{{old('link')}}">
				<span class="help-block error">
		        	<strong>{{ $errors->first('link') }}</strong>
		        </span>
            </div>
            @endif
  		</div>

        <div class="row">

        </div>

  		<div class="row">

  			@php  $search=array(); @endphp
  			 @foreach($extras as $campo)
                <div class="form-group col-md-4" >
					<label class="control-label">{{$campo->nombre}} @if($campo->tipo==1) <span class="text-danger">*</span> @endif</label>
					<a><i data-tippy-content="Edita el nombre del campo para categorizar mejor tus productos segun el tipo de negocio que tengas <a target='_blank' href='https://gestordepartes.net/empresa/configuracion/personalizar_inventario'>aquí</a>" class="icono far fa-question-circle"></i></a>
					<input type="text" class="form-control" name="ext_{{$campo->campo}}" id="{{$campo->campo}}-autocomplete" @if($campo->tipo==1) required="" @endif  @if($campo->varchar) maxlength="{{$campo->varchar}}" @endif   value="{{$campo->default}}">
				<p><small>{{$campo->descripcion}}</small></p>
				</div>
				@if($campo->autocompletar==1)
                	@php $search[]=$campo->campo; @endphp 
					<input type="hidden" id="search{{$campo->campo}}" value="{{json_encode($campo->records())}}">
				@endif
            @endforeach

           @if ($search) <input type="hidden" id="camposextra" value="{{json_encode($search)}}"> @endif

  		</div>

		{{--<div class="row">
			<table class="table" id="table-extras">
				<tbody>
					<tr id="1">
						@php  $search=array(); @endphp
						@foreach($extras as $campo)
							<td>
								<div class="form-group col-md-12" >
								    
									<label class="control-label">{{$campo->nombre}} @if($campo->tipo==1) <span class="text-danger">*</span> @endif</label>
									<input type="text" class="form-control" name="ext_{{$campo->campo}}[]" id="{{$campo->campo}}-autocomplete" @if($campo->tipo==1) required="" @endif  @if($campo->varchar) maxlength="{{$campo->varchar}}" @endif   value="{{$campo->default}}">
									<p><small>{{$campo->descripcion}}</small></p>
									
								</div>
							</td>
							@if($campo->autocompletar==1)
								@php $search[]=$campo->campo; @endphp
								<input type="hidden" id="search{{$campo->campo}}" value="{{json_encode($campo->records())}}">
							@endif
						@endforeach
						@if ($search) <input type="hidden" id="camposextra" value="{{json_encode($search)}}"> @endif
					</tr>
				</tbody>
			</table>
			<p class="text-left nomargin" >
				<button type="button" class="btn  btn-xs btn-sm btn-link" onclick="camposExtras();">
					<i class="fas fa-plus"></i>Agregar Campos Extras</button>
			</p>
		</div>--}}

  		<small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
  		<hr>
  		<div class="row" style="text-align: right;">
  			<div class="col-md-12">
				<a href="{{route('inventario.index')}}" class="btn btn-outline-light" >Cancelar</a>
  				<button type="submit" class="btn btn-success">Guardar</button>
  			</div>
  		</div>
  		
  	</form>
	<input type="hidden" id="json_extras"  value="{{json_encode($extras)}}" >
  	<input type="hidden" id="json_precios" value="{{json_encode($listas)}}">
  	<input type="hidden" id="json_bodegas" value="{{json_encode($bodegas)}}">
  	<input type="hidden" id="json_cuentas" value="{{json_encode($cuentas)}}">
@endsection
@section('scripts')
<script>
	function camposExtras(){
		var nro = $('#table-extras tbody tr').length + 1;
		var i;
		var tr;

		if($('#'+nro).length > 0){
			for(i = 1; i <= nro; i++){
				if($('#'+i).length == 0){
					nro = i;
					break;
				}
			}
		}

		data=$('#json_extras').val();
		data=JSON.parse(data);
		//var tabla = $('#scConsejos'+nro);
		tr += '<tr id ="'+nro+'">';
		$.each(data,function(key, value)
		{
			if(value.tipo == 1){
				var requerido = '<span class="text-danger">*</span>';
			}else{
				requerido = '';
			}

			if(value.default==null || value.descripcion== null){
				value.default = '';
				value.descripcion = '';
			}
			tr+= '<td>' +
					'<div class="form-group col-md-12" >' +
					'<label class="control-label">'+value.nombre+'</label>'+requerido+' ' +
					'<input type="text" class="form-control" name="ext_'+value.campo+'[]" id="'+value.campo+'-autocomplete"  value="'+value.default+'">' +
					'<p><small>'+value.descripcion +'</small></p>' +
					'</div>' +
					'</td>';

		});

		tr+='</tr>';

		$('#table-extras').append(tr);

	}

</script>
@endsection