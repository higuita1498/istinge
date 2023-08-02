@extends('layouts.app')
@section('content')

  <!--Formulario Facturas-->
	<form method="POST" action="{{ route('recurrentes.update', $factura->id) }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-factura" > 
		{{ csrf_field() }}
    <input name="_method" type="hidden" value="PATCH">
		<div class="row text-right">
			<div class="col-md-5">
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Numeración <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <div class="input-group">
              <select class="form-control selectpicker" name="numeracion" id="numeracion" required="" title="Seleccione" >
                @foreach($numeraciones as $numeracion)
                  <option {{$factura->numeracion==$numeracion->id?'selected':''}} value="{{$numeracion->id}}">{{$numeracion->nombre}}</option>
                @endforeach
              </select>
            </div>
          </div>
          <span class="help-block error">
            <strong>{{ $errors->first('numeracion') }}</strong>
          </span>
        </div>    

  			<div class="form-group row">
  				<label class="col-sm-4 col-form-label">Cliente <span class="text-danger">*</span></label>
	  			<div class="col-sm-8">
            <div class="input-group">
              <select class="form-control selectpicker" name="cliente" id="cliente" required="" title="Seleccione" data-live-search="true" data-size="5" onchange="contacto(this.value);">
                @foreach($clientes as $client)
                  <option {{$factura->cliente==$client->id?'selected':''}}  value="{{$client->id}}">{{$client->nombre}} - {{$client->nit}}</option>
                @endforeach
              </select>
            </div>
	  			</div>
          <span class="help-block error">
          	<strong>{{ $errors->first('cliente') }}</strong>
          </span>
  		  </div>  	
        <div class="form-group row">
          <label class="col-sm-4  col-form-label">Observaciones</label>
          <div class="col-sm-8">
            <textarea  class="form-control form-control-sm min_max_100" name="observaciones">{{$factura->observaciones}}</textarea>
          </div>
        </div> 
        <div class="form-group row" title="Indica la lista de precios que se utilizará en la factura de venta">
          <label class="col-sm-4 col-form-label">Lista de Precios</label>
          <div class="col-sm-8">
            <select name="lista_precios" id="lista_precios" class="form-control selectpicker">
              @foreach($listas as $lista)  
                <option value="{{$lista->id}}" {{$factura->lista_precios==$lista->id?'selected':''}}>{{$lista->nombre()}} </option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Bodega <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <select name="bodega" id="bodega" class="form-control"  required="">
              @foreach($bodegas as $bodega)  
                <option value="{{$bodega->id}}" {{$factura->bodega==$bodega->id?'selected':''}}>{{$bodega->bodega}}</option>
              @endforeach
            </select>
          </div>
        </div> 
		  </div>

		  <div class="col-md-5 offset-md-2" title="Fecha en la que se crea la primera factura de venta">
  			<div class="form-group row">
  				<label class="col-sm-4 col-form-label">Fecha de inicio <span class="text-danger">*</span></label>
	  			<div class="col-sm-8">
	  				<input type="text" class="form-control datepicker"  id="fecha" value="{{date('d-m-Y', strtotime($factura->fecha))}}" name="fecha" disabled=""  >
	  			</div>
  			</div>
        <div class="form-group row" title="Indica el último día de la creación automatica de la factura de venta">
          <label class="col-sm-4 col-form-label">Vencimiento</label>
          <div class="col-sm-8">
            <input type="text" class="form-control" id="vencimiento" value="@if($factura->vencimiento){{date('d-m-Y', strtotime($factura->vencimiento))}} @endif" name="vencimiento" disabled="">
          </div>
        </div>

  			<div class="form-group row" title="Vencimiento de la factura de venta">
  				<label class="col-sm-4 col-form-label">Plazo <span class="text-danger">*</span></label>
	  			<div class="col-sm-8">
	  				<select name="plazos" id="plazos" class="form-control " required="">
              @foreach($terminos as $termino)  
                <option value="{{$termino->id}}" dias="{{$termino->dias}}" {{$factura->plazo==$termino->id?'selected':''}}>{{$termino->nombre}}</option>
              @endforeach
	  				</select>
	  			</div>
  			</div>

        <div class="form-group row" title="Indica cada cúantos meses se va a crear se generará la la factura de venta. Ejemplo si coloca 3 indica que cada 3 meses se generara la factura">
          <label class="col-sm-4 col-form-label">Frecuencia <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <input type="number" name="frecuencia" class="form-control " min="1" value="{{$factura->frecuencia}}">            
          </div>
        </div>       

  		</div>
    </div>
    <hr>
    <!-- Desgloce -->
		<div class="row">
			<div class="col-md-12 fact-table">
        <table class="table table-striped table-sm" id="table-form" width="100%">
        	<thead class="thead-dark">
        		<tr>
              <th width="5%"></th>
        			<th width="24%">Ítem</th>
              <th width="10%">Referencia</th>
              <th width="12%">Precio</th>
        			<th width="5%">Desc %</th>
        			<th width="12%">Impuesto</th>
        			<th width="13%">Descripción</th>
        			<th width="7%">Cantidad</th>
        			<th width="10%">Total</th>
              <th width="2%"></th>
        		</tr>
        	</thead>
            <tbody>
            @php $cont=0; @endphp
            @foreach($items as $item) 
            @php $cont+=1; @endphp
              <tr id="{{$cont}}">
                <td class="no-padding">
                </td>
                <td  class="no-padding">      
                  <input type="hidden" name="id_item{{$cont}}" value="{{$item->id}}">                     
                  <select class="form-control form-control-sm selectpicker no-padding"  title="Seleccione" data-live-search="true" data-size="5" name="item[]" id="item{{$cont}}" onchange="rellenar({{$cont}}, this.value);" required="">
                  @foreach($inventario as $itemm)
                  <option value="{{$itemm->id}}" {{$item->producto==$itemm->id?'selected':''}}>{{$itemm->producto}} - ({{$item->ref}})</option>
                  @endforeach
                  </select>
                  
                </td>
                <td>
                  <input type="text" class="form-control form-control-sm" id="ref{{$cont}}" name="ref[]" placeholder="Referencia" required="" value="{{$item->ref}}">
                </td>
                <td class="monetario">
                  <input type="number" class="form-control form-control-sm" id="precio{{$cont}}" name="precio[]" placeholder="Precio Unitario" onkeyup="total({{$cont}})" required="" maxlength="24" min="0" value="{{$item->precio}}">
                </td>
                <td>
                  <input type="text" class="form-control form-control-sm nro" id="desc{{$cont}}" name="desc[]" placeholder="%" onkeyup="total({{$cont}})" value="{{$item->desc}}">
                </td>
                <td>
                <select class="form-control form-control-sm selectpicker" name="impuesto[]" id="impuesto{{$cont}}" title="Impuesto" onchange="totalall();" required="">
                  @foreach($impuestos as $impuesto)
                    <option value="{{$impuesto->id}}" porc="{{$impuesto->porcentaje}}" {{$item->id_impuesto==$impuesto->id?'selected':''}}>{{$impuesto->nombre}} - {{$impuesto->porcentaje}}%</option>
                  @endforeach
                </select>
              </td>
              <td  style="padding-top: 1% !important;">                           
                <textarea  class="form-control form-control-sm" id="descripcion{{$cont}}" name="descripcion[]" placeholder="Descripción" >{{$item->descripcion}}</textarea>
              </td>
              <td>
                <input type="number" class="form-control form-control-sm" id="cant{{$cont}}" name="cant[]" placeholder="Cantidad" onchange="total({{$cont}});" min="1" required="" value="{{$item->cant}}">
                <p class="text-danger nomargin" id="pcant{{$cont}}"></p>
              </td>
              <td>
                <input type="text" class="form-control form-control-sm text-right" id="total{{$cont}}" value="{{App\Funcion::Parsear($item->total())}}" disabled="">
              </td>
              <td>
                <button type="button" class="btn btn-outline-secondary btn-icons" onclick="Eliminar({{$cont}});">X</button>
              </td>
            </tr>
             @endforeach
          </tbody>
        </table>
        <div class="alert alert-danger" style="display: none;" id="error-items"></div>
      </div>
		</div>
    <button class="btn btn-outline-primary" onclick="createRow();" type="button" style="margin-top: 5%">Agregar línea</button>

    <!-- Totales -->
    <div class="row" style="margin-top: 10%;">
      <div class="col-md-4 offset-md-8">
        <table class="text-right widthtotal" id="totales">
          <tr>
            <td width="40%">Subtotal</td>
            <td>{{Auth::user()->empresa()->moneda}} <span id="subtotal">{{App\Funcion::Parsear($factura->total()->subtotal)}}</span></td>
          </tr>
          <tr> 
            <td>Descuento</td><td id="descuento">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($factura->total()->descuento)}}</td>
          </tr>
          <tr>
            <td>Subtotal</td>
            <td>{{Auth::user()->empresa()->moneda}} <span id="subsub">{{App\Funcion::Parsear($factura->total()->subsub)}}</span></td>
          </tr>

          @php $cont=0; @endphp
           @if($factura->total()->imp)
            @foreach($factura->total()->imp as $imp)
                @if(isset($imp->total)) @php $cont+=1; @endphp
                  <tr id="imp{{$cont}}">
                    <td>{{$imp->nombre}} ({{$imp->porcentaje}}%)</td>
                    <td id="totalimp{{$cont}}">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($imp->total)}}</td>
                  </tr>
                @endif
            @endforeach
        @endif
 
        </table>
        <hr>
        <table class="text-right widthtotal" style="font-size: 24px !important;">
          <tr>
            <td width="40%">TOTAL</td>
            <td>{{Auth::user()->empresa()->moneda}} <span id="total">{{App\Funcion::Parsear($factura->total()->total)}}</span></td>
          </tr>
        </table>
      </div>
    </div>
    <div class="row" style="margin-top: 5%; padding: 3%; min-height: 180px;">
      <div class="col-md-8 form-group">
        <label class="form-label">Términos y Condiciones</label>
        <textarea  class="form-control min_max_100" name="term_cond">{{$factura->term_cond}}</textarea>
      </div>
      <div class="col-md-4 form-group">
        <label class="form-label">Notas</label>
        <textarea  class="form-control form-control-sm min_max_100" name="notas">{{$factura->notas}}</textarea>
      </div>
    </div>
  	<hr>
    <!--Botones Finales -->
		<div class="row" >
      <div class="col-md-12 text-right" style="padding-top: 1%;">
        <a href="{{route('recurrentes.index')}}" class="btn btn-outline-secondary">Cancelar</a>
        <button type="submit" class="btn btn-success">Guardar</button>
      </div>
		</div>
  </form>
  <input type="hidden" id="impuestos" value="{{json_encode($impuestos)}}">
	<input type="hidden" id="allproductos" value="{{json_encode($inventario)}}">
	<input type="hidden" id="url" value="{{url('/')}}">
  <input type="hidden" id="jsonproduc" value="{{route('inventario.all')}}">
  <input type="hidden" id="simbolo" value="{{Auth::user()->empresa()->moneda}}">

@endsection