@extends('layouts.app')

@section('boton')
	@if(Auth::user()->modo_lectura())
		<div class="alert alert-warning text-left" role="alert">
			<h4 class="alert-heading text-uppercase">Integra Colombia: Suscripción Vencida</h4>
			<p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
		</div>
	@else
	<a href="{{route('factura.create_item',$inventario->id)}}" class="btn btn-outline-primary btn-sm "><i class="fas fa-plus"></i>Facturar este ítem</a>
	<a href="{{route('facturasp.create_item',$inventario->id)}}" class="btn btn-outline-primary btn-sm "><i class="fas fa-plus"></i>Comprar este ítem</a>
	<a href="{{route('inventario.edit',$inventario->id)}}" class="btn btn-outline-primary btn-sm "><i class="fas fa-edit"></i>Editar</a>
	@endif
@endsection
@section('content')
<div class="row card-description">
	<div class="col-md-9">
		<div class="table-responsive">
			<table class="table table-striped table-bordered table-sm info">
				<tbody>
					<tr>
						<th width="20%">Código</th>
						<td><b>{{$inventario->id}}</b></td>
					</tr>
					<tr>
						<th >Referencia</th>
						<td>{{$inventario->ref}}</td>
					</tr>
					<tr>
						<th>Nombre</th>
						<td>{{$inventario->producto}}</td>
					</tr>
					<tr>
						<th>Tipo</th>
						<td>{{$inventario->type}}</td>
					</tr>
					<tr>
						<th>Línea</th>
						<td>{{$inventario->linea}}</td>
					</tr>
					<tr>
						<th>Precio de Venta</th>
						<td style="padding: 0 1%  !important; " border="0">
								<table class="precios_table">
								<tr>
									<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($inventario->precio)}}</td>
									<td><span class="text-muted">{{$inventario->precio()}}</span></td>
								</tr>
								@foreach($inventario->precios() as $precio)
								<tr>
									<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($precio->precio)}}</td>
									<td><span class="text-muted">{{$precio->lista()->nombre()}}</span></td>
								</tr>
								 

							@endforeach
							</table>
						</td>
					</tr>
					@if($inventario->tipo_producto==1)
					<tr>
						<th>Precio de Compra</th>
						<td style="padding: 0 1%  !important; " border="0">
								<table class="precios_table">
								<tr>
									<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($inventario->costo_unidad)}}</td>
									<td><span class="text-muted">Unitario</span></td>
								</tr>
							</table>
						</td>
					</tr>
					@endif
					<tr>
						<th>Impuesto</th>
						<td>{{$inventario->impuesto()}}</td>
					</tr>
					<tr>
						<th>Descripción</th>
						<td>@php echo($inventario->descripcion); @endphp</td>
					</tr>
					<tr>
						<th>Categoría</th>
						<td>{{$inventario->categoria()}}</td>
					</tr>
					@if($inventario->tipo_producto==1)
					<tr>
						<th>Unidad de medida</th>
						<td>{{$inventario->unidad()}}</td>
					</tr>
					<tr>
						<th>Cantidad inicial</th>
						<td>{{round($inventario->inventario('inicial'))}}</td>
					</tr>
					<tr>
						<th>Inventario</th>
						<td>{{round($inventario->inventario())}}</td>
					</tr>
					<tr>
						<th>Bodegas</th>
						<td>
							<table class="precios_table">
								@foreach($inventario->bodegas() as $bodega)
									<tr>
										<td>{{round($bodega->nro)}} </td>
										<td> <span class="text-muted"> {{$bodega->bodega()->bodega}}</span></td>
									</tr>
								@endforeach
							</table>
						</td>
					</tr>
					@endif
					@if(auth()->user()->empresa()->carrito == 1)
                    <tr>
                        <th>Lista</th>
                        <td>
                            {{$inventario->lista()}}
                        </td>
                    </tr>
                
                    <tr>
						<th>Link</th>
						<td>{{$inventario->link}}</td>
					</tr>
					@endif
					@if(count($extras)>0)
					<tr>
						<th><b>Campos Extras</b></th>
					</tr>
					@foreach($extras as $campo)
						<tr>
							<th>{{$campo->nombre}} @if($campo->descripcion)<br><small>{{$campo->descripcion}}</small>@endif</th>
							<td>{{$inventario->campoExt($campo->campo)}}</td>
						</tr>
						
					@endforeach

					@endif
					
				</tbody>

				 
			</table>
		</div>
	</div>
	<div class="col-md-3" style="text-align: center;">
		@if($inventario->imagen)
			<img class="img-responsive" src="{{asset('images/Empresas/Empresa'.$inventario->empresa.'/inventario/'.$inventario->imagen)}}" alt="" style="    width: 100%;" onerror="this.onerror=null; this.src='@if(Auth::user()->empresa()->img_default) {{asset("images/Empresas/Empresa".Auth::user()->empresa."/".Auth::user()->empresa()->img_default)}} @else {{asset('images/producto-sin-imagen.png')}} @endif ';" 

			>
		@else
			<img class="img-responsive" src="@if(Auth::user()->empresa()->img_default) {{asset("images/Empresas/Empresa".Auth::user()->empresa."/".Auth::user()->empresa()->img_default)}} @else {{asset('images/producto-sin-imagen.png')}} @endif" alt="" style="    width: 100%;">
			
      	@endif
		
		</div>
	</div>

<div class="row card-description">
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
		        <em>(El máximo de imágenes subidas es de <strong> 10</strong>. Las imágenes no deben pesar mas de<strong> 2Mb </strong> y deben estar entre los formatos correctos <strong> .png .jpg .gif .bmp .jpeg. </strong>)</em>
		    </div>
		    <div class="fallback">
		        <input name="file" type="file" multiple accept="image/*"/>
		    </div>
		</form>
	</div>
</div>

<input type="hidden" value="{{$inventario->id}}" id="idproduct">
<div class="row card-description">
	<div class="col-md-12">
<ul class="nav nav-tabs" id="myTab" role="tablist">
  <li class="nav-item">
    <a class="nav-link active" id="profile-tab" data-toggle="tab" href="#facturas_venta" role="tab" aria-controls="facturas_venta" aria-selected="false">Facturas de venta</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="facturas_compra-tab" data-toggle="tab" href="#facturas_compra" role="tab" aria-controls="facturas_compra" aria-selected="false">Facturas de compra</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="notas_credito-tab" data-toggle="tab" href="#notas_credito" role="tab" aria-controls="notas_credito" aria-selected="false">
    Notas crédito</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="notas_debito-tab" data-toggle="tab" href="#notas_debito" role="tab" aria-controls="notas_debito" aria-selected="false">
    Notas débito</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="cotizaciones-tab" data-toggle="tab" href="#cotizaciones" role="tab" aria-controls="cotizaciones" aria-selected="false">Cotizaciones</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="remi-tab" data-toggle="tab" href="#remi" role="tab" aria-controls="remi" aria-selected="false">Remisiones</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="ordenes_compra-tab" data-toggle="tab" href="#ordenes_compra" role="tab" aria-controls="ordenes_compra" aria-selected="false">
    Órdenes de compra</a>
  </li>
</ul>
<div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
			<button type="button" class="close" data-dismiss="alert">×</button>
			<strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
		</div>
<div class="tab-content fact-table" id="myTabContent">
  <div class="tab-pane fade show active" id="facturas_venta" role="tabpanel" aria-labelledby="facturas_venta-tab">
  	<input type="hidden" id="url-show-facturas" value="{{route('factura.datatable.producto', $inventario->id)}}">
  	<table class="table table-striped table-hover table-condensed responsive display nowrap pagos " id="table-show-facturas">
		<thead>
			<tr>
              <th>Número</th>
              <th>Cliente</th>
              <th>Creación</th>
              <th>Vencimiento</th>
              <th>Total</th>
              <th>Pagado</th>
              <th>Por Pagar</th>
              <th>Estado</th>
              <th>Acciones</th>
          </tr>                              
		</thead>
		<tbody id="body-show-facturas">

		</tbody>
	</table>
  </div>
  <div class="tab-pane fade" id="facturas_compra" role="tabpanel" aria-labelledby="facturas_compra-tab">
  	<input type="hidden" id="url-show-facturas-compras" value="{{route('facturasp.datatable.producto', $inventario->id)}}">
  	<table class="table table-striped table-hover pagos" id="table-show-facturas-compras" style="width: 100%;">
		<thead>
			<tr>
              <th>Factura</th>
              <th>Proveedor</th>
              <th>Creación</th>
              <th>Vencimiento</th>
              <th>Total</th>
              <th>Pagado</th>
              <th>Por Pagar</th>
              <th>Acciones</th>
          </tr>                              
		</thead>
		<tbody id="body-facturas-compras"></tbody>
	</table>
  </div>

  <div class="tab-pane fade" id="notas_credito" role="tabpanel" aria-labelledby="notas_credito-tab">
  	<input type="hidden" id="url-show-notascredito" value="{{route('notascredito.datatable.producto', $inventario->id)}}">
  	<table class="table table-striped table-hover pagos " id="table-show-notascredito" style="width: 100%">
		<thead>
			<tr>
              <th>Código</th>
              <th>Cliente</th>
              <th>Creación</th>
              <th>Total</th>
              <th>Por Aplicar</th>
              <th>Acciones</th>
          </tr>                              
		</thead>
		<tbody id="body-notas_credito-tab"></tbody>
	</table>
  </div>

  <div class="tab-pane fade" id="notas_debito" role="tabpanel" aria-labelledby="notas_debito-tab">
	<input type="hidden" id="url-show-notasdebito" value="{{route('notasdebito.datatable.producto', $inventario->id)}}">
  	<table class="table table-striped table-hover pagos " id="table-show-notasdebito" style="width: 100%">
		<thead>
			<tr>
              <th>Código</th>
              <th>Proveedor</th>
              <th>Creación</th>
              <th>Total</th>
              <th>Por Aplicar</th>
              <th>Acciones</th>
          </tr>                              
		</thead>
		<tbody id="body-notas_debito-tab"></tbody>
	</table>
  </div>
  
  <div class="tab-pane fade" id="cotizaciones" role="tabpanel" aria-labelledby="cotizaciones-tab">
  	<input type="hidden" id="url-show-cotizaciones" value="{{route('cotizaciones.datatable.producto', $inventario->id)}}">
  	<table class="table table-striped table-hover pagos " id="table-show-cotizaciones" style="width: 100%">
		<thead>
			<tr>
              <th>Código</th>
              <th>Cliente</th>
              <th>Creación</th>
              <th>Total</th>
              <th>Estatus</th>
              <th>Acciones</th>
          </tr>                              
		</thead>
		<tbody id="body-cotizaciones-tab"></tbody>
	</table>
  </div>
  <div class="tab-pane fade" id="remi" role="tabpanel" aria-labelledby="remi-tab">
  	<input type="hidden" id="url-show-remisiones" value="{{route('remisiones.datatable.producto', $inventario->id)}}">
  	<table class="table table-striped table-hover pagos " id="table-show-remisiones" style="width: 100%">
		<thead>
			<tr>
              <th>Código</th>
              <th>Cliente</th>
              <th>Creación</th>
              <th>Vencimiento</th>
              <th>Estado</th>
              <th>Total</th>
              <th>Acciones</th>
          </tr> 
		</thead>
        <tbody id="body-remi-tab"></tbody>
	</table>
  </div>
  
  <div class="tab-pane fade" id="ordenes_compra" role="tabpanel" aria-labelledby="ordenes_compra-tab">
  	<input type="hidden" id="url-show-ordenes" value="{{route('ordenes.datatable.producto', $inventario->id)}}">
  	<table class="table table-striped table-hover pagos " id="table-show-ordenes" style="width: 100%">
		<thead>
			<tr>
              <th>Código</th>
              <th>Cliente</th>
              <th>Fecha</th>
              <th>Fecha de Entrega</th>
              <th>Estado</th>
              <th>Total</th>
              <th>Acciones</th>
          </tr>                              
		</thead>
		<tbody id="body-ordenes_compra-tab"></tbody>
	</table>
  </div>

</div>
</div>
</div>
<script !src="">
    $(document).ready(function (){

        $('#profile-tab').click(function(){
           alert('TEST');
        });

    });
</script>
@endsection
