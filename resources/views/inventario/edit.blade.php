@extends('layouts.app')
@section('content')
@if(Session::has('error'))
<div class="alert alert-danger">
	{{Session::get('error')}}
</div>
@endif

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
	.pst-buttons{
		position: absolute;
		bottom:0px;
		right:22px;
	}
</style>

<form method="POST" action="{{ route('inventario.update',$inventario->id) }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-inventario" enctype="multipart/form-data">
	{{ csrf_field() }}
	<input type="hidden" name="back" value="{{ old('back')?old('back'):URL::previous() }}">
	<input name="_method" type="hidden" value="PATCH">
	<div class="row">
		<div class="form-group col-md-4">
			<label class="control-label">Nombre del Producto <span class="text-danger">*</span></label>
			<input type="text" class="form-control" name="producto" id="producto" required="" maxlength="200" value="{{$inventario->producto}}">
			<span class="help-block error">
				<strong>{{ $errors->first('producto') }}</strong>
			</span>
		</div>
		<div class="form-group col-md-4">
			<label class="control-label">Referencia <span class="text-danger">*</span></label>
			<input type="text" class="form-control" name="ref" id="ref" maxlength="200" value="{{$inventario->ref}}">
			<span class="help-block error">
				<strong>{{ $errors->first('ref') }}</strong>
			</span>
		</div>

		<div class="form-group col-md-4">
			<label class="control-label">Impuesto <span class="text-danger">*</span></label>

			<select class="form-control selectpicker" name="impuesto" id="impuesto" required="" title="Seleccione">
				@foreach($impuestos as $impuesto)
				<option {{$inventario->id_impuesto==$impuesto->id?'selected':''}} value="{{$impuesto->id}}">{{$impuesto->nombre}} - {{$impuesto->porcentaje}} %</option>
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
			<input type="text" class="form-control " id="precio" name="precio" required="" maxlength="24" value="{{App\Funcion::precision($inventario->precio)}}" placeholder="{{Auth::user()->empresa()->moneda}}" min="0" @if($inventario->tipo_producto==1) @endif >
			<span class="help-block error">
				<strong>{{ $errors->first('precio') }}</strong>
			</span>
			<span class="litte">Use el punto (.) para colocar decimales</span>
		</div>

		<div class="form-group col-md-7 ">
			<div class="row">
				<div class="col-md-6" style="padding-top: 6%;padding-left: 0;"><button type="button" class="btn btn-link " style="padding-left: 0;" onclick="agregarlista_precios();" @if(json_encode($listas)=='[]') disabled="" title="Usted no tiene lista de precios registrados" @endif><i class="fas fa-plus"></i> Agregar otra lista de precio</button></div>
			</div>
			<div class="row" id="lista_precios_inventario">
				<div class="col-md-12">
					<table id="table_lista_precios">
						<tbody>
							@foreach($inventario->precios() as $key => $precio)
							@php $tipo=0 @endphp
							<tr id="tr_lista_{{($key+1)}}">
								<td width="20%"><label class="control-label">Lista de precios <span class="text-danger">*</span></label></td>
								<td width="30%">
									<select class="form-control form-control-sm selectpicker no-padding"  title="Seleccione" name="preciolista[]" id="preciolista{{($key+1)}}" onchange="change_precio_lista({{($key+1)}});" required="">							        		
										@foreach($listas as $lista) 
										@if($precio->lista==$lista->id) @php $tipo=$lista->tipo @endphp @endif
										<option value="{{$lista->id}}" {{$precio->lista==$lista->id?'selected':''}}  >{{$lista->nombre()}}</option>
										@endforeach
									</select>
									<input type="hidden" name="idlistaprecio{{($key)}}" value="{{$precio->id}}">
								</td>
								<td width="30%" class="monetario"><input type="number" class="form-control form-control-sm" id="preciolistavalor{{($key+1)}}" name="preciolistavalor[]" placeholder="Precio" required="" maxlength="24" min="0" value="{{$precio->precio}}" @if($tipo==1) disabled="" @endif></td>
								<td width="5%"><button type="button" class="btn btn-link" onclick="Eliminar('tr_lista_{{($key+1)}}');">X</button></td>
							</tr>

							@endforeach

						</tbody>
					</table>
				</div> 
			</div>

		</div>
	</div>
	<div class="row">
		<div class="form-group col-md-3">
			<label class="control-label">Inventario <span class="text-danger">*</span></label>
			<select class="form-control selectpicker" data-live-search="true" data-size="5" name="inventario" id="inventario" required="" title="Seleccione">
				@foreach($cuentas as $cuenta)
					<option @foreach($cuentasInventario  as $ci) @if($ci->cuenta_id == $cuenta->id && $ci->tipo == 1){{'selected'}}@endif @endforeach 
						value="{{$cuenta->id}}">{{$cuenta->nombre}} - {{$cuenta->codigo}}
					</option>
				@endforeach
			  </select>
		  <span class="help-block error">
			  <strong>{{ $errors->first('inventario') }}</strong>
		  </span>
		</div>
		<div class="form-group col-md-3">
			<label class="control-label">Costo <span class="text-danger">*</span></label>
			<select class="form-control selectpicker" data-live-search="true" data-size="5" name="costo" id="costo" required="" title="Seleccione">
				@foreach($cuentas as $cuenta)
				<option @foreach($cuentasInventario  as $ci) @if($ci->cuenta_id == $cuenta->id && $ci->tipo == 2){{'selected'}}@endif @endforeach 
					value="{{$cuenta->id}}">{{$cuenta->nombre}} - {{$cuenta->codigo}}
				</option>				
				@endforeach
		  </select>
		  <span class="help-block error">
			  <strong>{{ $errors->first('costo') }}</strong>
		  </span>
		</div>
		<div class="form-group col-md-3">
			<label class="control-label">Venta <span class="text-danger">*</span></label>
			<select class="form-control selectpicker" data-live-search="true" data-size="5" name="venta" id="venta" required="" title="Seleccione">
				@foreach($cuentas as $cuenta)
				<option @foreach($cuentasInventario  as $ci) @if($ci->cuenta_id == $cuenta->id && $ci->tipo == 3){{'selected'}}@endif @endforeach 
					value="{{$cuenta->id}}">{{$cuenta->nombre}} - {{$cuenta->codigo}}
				</option>
				@endforeach
		  </select>
		  <span class="help-block error">
			  <strong>{{ $errors->first('venta') }}</strong>
		  </span>
		</div>
		<div class="form-group col-md-3">
			<label class="control-label">Devolución <span class="text-danger">*</span></label>
			<select class="form-control selectpicker" data-live-search="true" data-size="5" name="devolucion" id="devolucion" required="" title="Seleccione">
				@foreach($cuentas as $cuenta)
				<option @foreach($cuentasInventario  as $ci) @if($ci->cuenta_id == $cuenta->id && $ci->tipo == 4){{'selected'}}@endif @endforeach 
					value="{{$cuenta->id}}">{{$cuenta->nombre}} - {{$cuenta->codigo}}
				</option>
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
							@php $key2= 0; @endphp
							@foreach($cuentasInventario as $cuenta)
							@if($cuenta->tipo == null)
							<tr id="tr_cuenta_{{($key2+1)}}">
								<td width="20%"><label class="control-label">Cuenta contable <span class="text-danger">* {{$cuenta->nombreProductoServicio()}}</span></label></td>
								<td width="30%">
									<select class="form-control form-control-sm selectpicker no-padding"  title="Seleccione" name="cuentacontable[]" id="cuentacontable{{($key2+1)}}" required="">							        		
										@foreach($cuentas as $c) 
										<option value="{{$c->id}}" {{$cuenta->cuenta_id ==$c->id ? 'selected':''}}  >{{$c->nombre}} - {{$c->codigo}}</option>
										@endforeach
									</select>
									<input type="hidden" name="idcuenta{{($key2)}}" value="{{$cuenta->id}}">
								</td>
								<td width="5%"><button type="button" class="btn btn-link" onclick="eliminarCuenta('tr_cuenta_{{($key2+1)}}');">X</button></td>
							</tr>
							@endif
							@php $key2++; @endphp
							@endforeach

						</tbody>
					  </table>
				  </div>
			  </div>

		</div>
	  </div>

	<div class="row">
		<div class="form-group col-md-8">
			<label class="control-label" for="email">Descripción</label>
			<textarea class="form-control {{auth()->user()->empresa()->carrito == 1 ? 'ckeditor' : ''}}" name="descripcion" id="descripcion" rows="4" >{{$inventario->descripcion}}</textarea>
			{{--<input type="text" class="form-control" id="email" name="descripcion" maxlength="255"  value="{{$inventario->descripcion}}">--}}
			<div class="help-block error with-errors"></div>
			<span class="help-block error">
				<strong>{{ $errors->first('descripcion') }}</strong>
			</span>
		</div>
		<div class="form-group col-md-4">
			<label class="control-label"><button type="button" class="btn btn-link btn-fw" id="button_show_div_img">Imagen (Destacada)</button></label>
			<div style="display: none;" id="div_imagen">
				<input type="file" class="dropify" name="imagen" />
				<span class="help-block error">
					<strong>{{ $errors->first('imagen') }}</strong>
				</span>
			</div>

		</div>
	</div>

	<div class="row">
		<div class="invent-feleft">
			@if(Auth::user()->empresa()->carrito==1)
			<div class="form-group row">
				<label for="publico" class="col-md-6 col-form-label">¿Estara el producto público en la web?</label>
				<div class="col-md-3">
					<div class="row">
						<div class="col-sm-6">
							<div class="form-radio">
								<label class="form-check-label">
									<input type="radio" class="form-check-input" name="publico" id="publico1" value="1" @if($inventario->publico==1) checked @endif> Si
									<i class="input-helper"></i></label>
								</div>
							</div>
							<div class="col-sm-6">
								<div class="form-radio">
									<label class="form-check-label">
										<input type="radio" class="form-check-input" name="publico" id="publico" value="0" @if($inventario->publico==0) checked @endif> No
										<i class="input-helper"></i></label>
									</div>
								</div>
							</div>
						</div>

					</div>
					@endif
					<div class="row">
						<div class="form-group col-md-6">
							<label class="control-label">¿Producto Inventariable?</label>
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
												<input type="radio" class="form-check-input" name="tipo_producto" id="tipo_producto2" value="1" @if($inventario->tipo_producto==1) checked="" @endif> Si
												<i class="input-helper"></i></label>
											</div>
										</div>
									</div>
									<span class="help-block error">
										<strong>{{ $errors->first('tipo_producto') }}</strong>
									</span>
								</div>
								
								@if(auth()->user()->empresa()->carrito == 1)
								<div class="form-group col-md-5">
									<label class="control-label">Asignar a una lista</label>
									<select name="list" class="form-control">
										<option value="0" {{$inventario->lista == 0 ? 'selected' : '' }}>Ninguna</option>
										<option value="1" {{$inventario->lista == 1 ? 'selected' : '' }}>Más vendidos</option>
										<option value="2" {{$inventario->lista == 2 ? 'selected' : '' }}>Recientes</option>
										<option value="3" {{$inventario->lista == 3 ? 'selected' : '' }}>Oferta</option>
									</select>
								</div>


								<div class="form-group col-md-12">
            	                 <label class="control-label">Link<a><i data-tippy-content="Si tienes mas información como un video, historia o página referente al producto deja el link acá" class="icono far fa-question-circle"></i></a></label>
				                 <input type="text" class="form-control" name="link" id="link" maxlength="400" value="{{$inventario->link}}">
				                <span class="help-block error">
		        	            <strong>{{ $errors->first('link') }}</strong>
		                        </span>
                               </div>
                               @endif

								<div id="inventariable" class="col-md-12" style="@if($inventario->tipo_producto==1) display: block; @else display: none; @endif  ">
									<div class="row">
										<div class="form-group col-md-4" >
											<label class="control-label">Unidad de medida</label>
											<select class="form-control selectpicker" name="unidad" id="unidad" required="" title="Seleccione" data-live-search="true" data-size="5">
												@foreach($medidas as $medida)
												<optgroup label="{{$medida->medida}}">
													@foreach($unidades as $unidad)
													@if($medida->id==$unidad->tipo)
													<option {{$inventario->unidad==$unidad->id?'selected':''}} value="{{$unidad->id}}">{{$unidad->unidad}}</option>
													@endif
													@endforeach
												</optgroup>
												@endforeach
											</select>
											<strong>{{ $errors->first('unidad') }}</strong>
										</div>
										<div class="form-group col-md-4 monetario" >
											<label class="control-label">Costo unidad</label>
											<input type="number" class="form-control" name="costo_unidad" id="precio_unid" required="" maxlength="24"  min="0" value="{{$inventario->costo_unidad}}" >
											<span class="help-block error">
												<strong>{{ $errors->first('nro') }}</strong> 
											</span>
										</div>
									</div>
									<div class="row" id="bodega_inventario">
										<div class="col-md-12 form-group">
											<table id="table_bodega">
												<tbody>
													@foreach($inventario->bodegas() as $key => $bodega)
													<tr id="tr_bodega_{{($key+1)}}">
														<td width="15%"><label class="control-label">Bodega <span class="text-danger">*</span></label></td>
														<td width="25%">
															<select class="form-control form-control-sm selectpicker no-padding"  title="Seleccione" name="bodega[]" id="bodega{{($key+1)}}" required="" onchange="comprobar_bodegas({{($key+1)}}, this.value)">
																@foreach($bodegas as $bob)
																<option value="{{$bob->id}}" {{$bob->id==$bodega->bodega?'selected':''}}  >{{$bob->bodega}}</option>
																@endforeach
															</select>
															<input type="hidden" name="idbodega{{($key)}}" value="{{$bodega->id}}">
														</td> 
														<td width="25%" class="text-center"><label class="control-label">Cantidad Inicial <span class="text-danger">*</span></label></td>
														<td width="25%" class="monetario"><input type="number" class="form-control form-control-sm" id="bodegavalor{{($key+1)}}" name="bodegavalor[]" required="" maxlength="24" min="0" value="{{$bodega->inicial}}"></td>
														<td width="5%">
															@if($key>0 && $bodega->transferencias()==0)
															<button type="button" class="btn btn-link" onclick="Eliminar('tr_bodega_{{($key+1)}}');">X</button>
															@endif	
														</td></tr>
														@endforeach
													</tbody>
												</table>
											</div>
										</div>
										<button type="button" class="btn btn-link" onclick="agregarbodega_inventario();" style="padding-top: 0;"><i class="fas fa-plus"></i> Agregar en otra bodega</button>
									</div>
								</div>
							</div>


							<div class="edit-inv-img">
								@if($inventario->imagen)
								<img class="img-responsive pic-inv-img img-contenida" src="{{asset('images/Empresas/Empresa'.$inventario->empresa.'/inventario/'.$inventario->imagen)}}" alt="" style="    width: 100%;" onerror="this.onerror=null; this.src='@if(Auth::user()->empresa()->img_default) {{asset("images/Empresas/Empresa".Auth::user()->empresa."/".Auth::user()->empresa()->img_default)}} @else {{asset('images/producto-sin-imagen.png')}} @endif ';">
								@else
								<img class="img-responsive edit-inv-img img-contenida" src="@if(Auth::user()->empresa()->img_default) {{asset("images/Empresas/Empresa".Auth::user()->empresa."/".Auth::user()->empresa()->img_default)}} @else {{asset('images/producto-sin-imagen.png')}} @endif" alt="" style="    width: 100%;">

								@endif
							</div>
						</div>


						<div class="row" >


						</div>

						<div class="row">
							@php  $search=array(); @endphp
							@foreach($extras as $campo)
							<div class="form-group col-md-4" >
								<label class="control-label">{{$campo->nombre}} @if($campo->tipo==1) <span class="text-danger">*</span> @endif</label>
								<input type="text" class="form-control" name="ext_{{$campo->campo}}" id="{{$campo->campo}}-autocomplete" @if($campo->tipo==1) required="" @endif  @if($campo->varchar) maxlength="{{$campo->varchar}}" @endif   value="{{$inventario->campoExt2($campo->campo)?$inventario->campoExt2($campo->campo):$campo->default}}">
								<p><small>{{$campo->descripcion}}</small></p>
							</div>
							@if($campo->autocompletar==1)
							@php $search[]=$campo->campo; @endphp 
							<input type="hidden" id="search{{$campo->campo}}" value="{{json_encode($campo->records())}}">
							@endif
							@endforeach

							@if ($search) <input type="hidden" id="camposextra" value="{{json_encode($search)}}"> @endif
						</div>

	

						<div class="pst-buttons">
							<small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
							<hr>
							<div class="row" style="text-align: right;">
								<div class="col-md-12">
									<a href="{{route('inventario.index')}}" class="btn btn-outline-light" >Cancelar</a>
									<button type="submit" class="btn btn-success">Guardar</button>
								</div>
							</div>
						</div>

					</form>
    <hr>
	<div class="row card-description mb-5">
    	<div class="col-md-12">
    		<h2>Imagenes Extras</h2>
    		<div id="aniimated-thumbnials" class="list-unstyled row clearfix">
    			@foreach($inventario->imagenes() as $imagen)
    				<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 img-obj-{{$imagen->id}}">
    			        <div class="image-thumb">
    			            <img class="img-responsive thumbnail" src="{{asset('images/Empresas/Empresa'.$inventario->empresa.'/inventario/'.$inventario->id.'/'.$imagen->imagen)}}" onerror="this.src='@if(Auth::user()->empresa()->img_default) {{asset("images/Empresas/Empresa".Auth::user()->empresa."/".Auth::user()->empresa()->img_default)}} @else {{asset('images/producto-sin-imagen.png')}} @endif'">
    			            <div class="image-fav">    
    			            	<div class="image-fav-icons">
    			            		<button type="button" class="btn btn-link btn-icons" onclick="delete_img('{{$imagen->id}}');"><i class="fas fa-trash-alt"></i></button>
    			                	<a href="{{asset('images/Empresas/Empresa'.$inventario->empresa.'/inventario/'.$inventario->id.'/'.$imagen->imagen)}}" class="img-view"><i class="fas fa-eye"></i><img class="img-responsive thumbnail"  src="{{asset('images/Empresas/Empresa'.$inventario->empresa.'/inventario/'.$inventario->id.'/'.$imagen->imagen)}}"  onerror="this.src='{{asset('images/producto-sin-imagen.png')}}'" style="display:none"></a>
    			            	</div>
    			            </div>
    			            
    			        </div>
    			    </div>
    			@endforeach
    		</div>
    		<form action="{{route('inventario.imagenes', $inventario->id)}}" id="frmFileUpload" class="dropzone" method="POST" >
    			<input type="hidden"  name="tipo" value="add">
    			{{ csrf_field() }}
    		    <div class="dz-message">
    		        <div class="drag-icon-cph">
    		            <i class="fas fa-mouse-pointer"></i>
    		        </div>
    		        <h3>Suelte imágenes aquí o haga clic para subirlas</h3>
    		        <em>(El máximo de imágenes subidas es de <strong> 10</strong>. Las imágenes no deben pesar mas de<strong> 5Mb </strong> y deben estar entre los formatos correctos <strong> .png .jpg .gif .bmp .jpeg. </strong>)</em>
    		    </div>
    		    <div class="fallback">
    		        <input name="file" type="file" multiple accept="image/*"/>
    		    </div>
    		</form>
    	</div>
    </div>
					<input type="hidden" id="json_precios" value="{{json_encode($listas)}}">
					<input type="hidden" id="json_bodegas" value="{{json_encode($bodegas)}}">
					<input type="hidden" id="json_cuentas" value="{{json_encode($cuentas)}}">	

					@endsection
