	<form method="POST" action="{{ route('inventario.storeback', '/empresa/remisiones/create') }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-inventario" enctype="multipart/form-data">
        <input type="hidden" name="toUrl" value="{{url('/').'/empresa/remisiones/create'}}">
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
	  			<label class="control-label">Categoria <span class="text-danger">*</span><a><i data-tippy-content="Selecciona la categoría en la que se registrarán los valores por venta del ítem" class="icono far fa-question-circle"></i></a></label>
	  			<select class="form-control selectpicker" name="categoria" id="categoria" required="" title="Seleccione" data-live-search="true" data-size="5">
	  				@foreach($categorias2 as $categoria)
                  		@if($categoria->estatus==1)
                  			<option {{old('categoria')==$categoria->id?'selected':( Auth::user()->empresa()->categoria_default==$categoria->id?'selected':'')}} value="{{$categoria->id}}">{{$categoria->nombre}}</option>
                  		@endif
                  		@foreach($categoria->hijos(true) as $categoria1)
                  			@if($categoria1->estatus==1)
	                  			<option {{old('categoria')==$categoria1->id?'selected':( Auth::user()->empresa()->categoria_default==$categoria1->id?'selected':'')}} value="{{$categoria1->id}}">{{$categoria1->nombre}}</option>
	                  		@endif
	                  		@foreach($categoria1->hijos(true) as $categoria2)
	                  			@if($categoria2->estatus==1)
		                  			<option {{old('categoria')==$categoria2->id?'selected':( Auth::user()->empresa()->categoria_default==$categoria2->id?'selected':'')}} value="{{$categoria2->id}}">{{$categoria2->nombre}}</option>
		                  		@endif
		                  		@foreach($categoria2->hijos(true) as $categoria3)
		                  			@if($categoria3->estatus==1)
			                  			<option {{old('categoria')==$categoria3->id?'selected':( Auth::user()->empresa()->categoria_default==$categoria3->id?'selected':'')}} value="{{$categoria3->id}}">{{$categoria3->nombre}}</option>
			                  		@endif
			                  		@foreach($categoria3->hijos(true) as $categoria4)
			                  			@if($categoria4->estatus==1)
				                  			<option {{old('categoria')==$categoria4->id?'selected':( Auth::user()->empresa()->categoria_default==$categoria4->id?'selected':'')}} value="{{$categoria4->id}}">{{$categoria4->nombre}}</option>
				                  		@endif

			                  		@endforeach

		                  		@endforeach

	                  		@endforeach

                  		@endforeach


	  				@endforeach
                </select>
				<span class="help-block error">
		        	<strong>{{ $errors->first('categoria') }}</strong>
		        </span>
			</div>
  		</div>
  		<div class="row">
			<div class="form-group col-md-5">
	  			<label class="control-label">Impuesto <span class="text-danger">*</span></label>
	  			<select class="form-control selectpicker" name="impuesto" id="impuesto" required="" title="Seleccione">
	  				@foreach($impuestos2 as $impuesto)
                  		<option {{old('impuesto')==$impuesto->id?'selected':''}} value="{{$impuesto->id}}">{{$impuesto->nombre}} - {{$impuesto->porcentaje}} %</option>
	  				@endforeach
                </select>
				<span class="help-block error">
					<strong>{{ $errors->first('impuesto') }}</strong>
				</span>
			</div>
  			<div class="form-group col-md-7 ">
	  			<div class="row">
	  				<div class="col-md-6 monetario">
	  					<label class="control-label">Precio de Venta <span class="text-danger">*</span></label>
						<input type="number" class="form-control " id="precio" name="precio" required="" maxlength="24" value="0" placeholder="{{Auth::user()->empresa()->moneda}}" min="0" >
						<span class="help-block error">
				        	<strong>{{ $errors->first('precio') }}</strong>
				        </span>
					    <span class="litte">Use el punto (.) para colocar decimales</span>
	  				</div>
	  				<div class="col-md-6" style="padding-top: 6%;padding-left: 0;"><button type="button" class="btn btn-link " style="padding-left: 0;" onclick="agregarlista_precios();" @if(json_encode($listas)=='[]') disabled="" title="Usted no tiene lista de precios registrada" @endif><i class="fas fa-plus"></i> Agregar otra lista de precio</button></div>
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
  		</div>
  		<div class="row">
			<div class="form-group col-md-8">
	  			<label class="control-label" for="email">Descripción</label>
				<input type="text" class="form-control" id="email" name="descripcion" maxlength="255"  value="{{old('descripcion')}}">
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

            <div class="form-group col-md-2">
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
                                <input type="radio" class="form-check-input" name="tipo_producto" id="tipo_producto2" value="1"> Si
                                <i class="input-helper"></i></label>
                        </div>
                    </div>
                </div>
                <span class="help-block error">
					<strong>{{ $errors->first('tipo_producto') }}</strong>
				</span>
            </div>

  			<div id="inventariable" class="col-md-10" style="@if(old('tipo_producto')) display: block; @else display: none; @endif  ">
				<div class="row">
					<div class="form-group col-md-4" >
						<label class="control-label">Unidad de medida</label>
						<select class="form-control selectpicker" name="unidad" id="unidad" required="" title="Seleccione" data-live-search="true" data-size="5">
			  				@foreach($medidas2 as $medida)
			  					<optgroup label="{{$medida->medida}}">
	    							@foreach($unidades2 as $unidad)
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
			<div class="form-group col-md-2">
				<label class="control-label">Asignar a una lista</label>
				<select name="list" class="form-control">
					<option value="0" selected>Ninguna</option>
					<option value="1">Más vendidos</option>
					<option value="2">Recientes</option>
					<option value="3">Oferta</option>
				</select>
			</div>
  		</div>
  		<div class="row">
  			@php  $search=array(); @endphp
  			 @foreach($extras2 as $campo)
                <div class="form-group col-md-4" >
					<label class="control-label">{{$campo->nombre}} @if($campo->tipo==1) <span class="text-danger">*</span> @endif</label>
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
  		<small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
  		<hr>
  		<div class="row" style="text-align: right;">
  			<div class="col-md-12">
  				<button type="submit" class="btn btn-success">Guardar</button>
  			</div>
  		</div>

  	</form>
  	<input type="hidden" id="json_precios" value="{{json_encode($listas2)}}">
  	<input type="hidden" id="json_bodegas" value="{{json_encode($bodegas2)}}">

    <script>


        setTimeout(function () {
            $('#precio').removeAttr("disabled");
        }, 2000);

        
    </script>
