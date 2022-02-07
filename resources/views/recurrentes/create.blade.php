@extends('layouts.app')
@section('content')

  <!--Formulario Facturas-->
	<form method="POST" action="{{ route('recurrentes.store') }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-factura" >
		{{ csrf_field() }}
		<div class="row text-right">
			<div class="col-md-5">
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Numeración <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <div class="input-group">
              <select class="form-control selectpicker" name="numeracion" id="numeracion" required="" title="Seleccione" >
                @foreach($numeraciones as $numeracion)
                  <option {{old('numeracion')==$numeracion->id?'selected':''}} value="{{$numeracion->id}}">{{$numeracion->nombre}}</option>
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
                  <option {{old('cliente')==$client->id?'selected':''}} {{$cliente==$client->id?'selected':''}}  value="{{$client->id}}">{{$client->nombre}} - {{$client->nit}}</option>
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
            <textarea  class="form-control form-control-sm min_max_100" name="observaciones"></textarea>
          </div>
        </div> 
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Lista de Precios <a><i data-tippy-content="Indica la lista de precios que se utilizará en la factura de venta" class="icono far fa-question-circle"></i></a></label>
          <div class="col-sm-8">
            <select name="lista_precios" id="lista_precios" class="form-control selectpicker">
              @foreach($listas as $lista)  
                <option value="{{$lista->id}}">{{$lista->nombre()}} </option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Bodega <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <select name="bodega" id="bodega" class="form-control"  required="">
              @foreach($bodegas as $bodega)  
                <option value="{{$bodega->id}}">{{$bodega->bodega}}</option>
              @endforeach
            </select>
          </div>
        </div> 
		  </div>

		  <div class="col-md-5 offset-md-2">
  			<div class="form-group row">
  				<label class="col-sm-4 col-form-label">Fecha de inicio <span class="text-danger">*</span><a><i data-tippy-content="Fecha en la que se crea la primera factura de venta" class="icono far fa-question-circle"></i></a></label>
	  			<div class="col-sm-8">
	  				<input type="text" class="form-control datepicker"  id="fecha" value="{{date('d-m-Y')}}" name="fecha" disabled=""  >
	  			</div>
  			</div>
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Vencimiento <a><i data-tippy-content="Indica el último día de la creación automatica de la factura de venta" class="icono far fa-question-circle"></i></a></label>
          <div class="col-sm-8">
            <input type="text" class="form-control" id="vencimiento" value="" name="vencimiento">
          </div>
        </div>

  			<div class="form-group row">
  				<label class="col-sm-4 col-form-label">Plazo <span class="text-danger">*</span> <a><i data-tippy-content="Vencimiento de la factura de venta" class="icono far fa-question-circle"></i></a></label>
	  			<div class="col-sm-8">
	  				<select name="plazos" id="plazos" class="form-control " required="">
              @foreach($terminos as $termino)  
                <option value="{{$termino->id}}" dias="{{$termino->dias}}">{{$termino->nombre}}</option>
              @endforeach
	  				</select>
	  			</div>
  			</div>

        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Frecuencia <span class="text-danger">*</span><a><i data-tippy-content="Indica cada cúantos meses se va a crear se generará la la factura de venta. Ejemplo si coloca 3 indica que cada 3 meses se generara la factura" class="icono far fa-question-circle"></i></a></label>
          <div class="col-sm-8">
            <input type="number" name="frecuencia" class="form-control " min="1" required="">            
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
              <tr id="1">
                <td class="no-padding">
                  <a class="btn btn-outline-secondary btn-icons" title="Actualizar" onclick="inventario('1');"><i class="fas fa-sync"></i></a>
                </td>
                <td  class="no-padding" style="padding-top: 2% !important;">                           
                  <select class="form-control form-control-sm selectpicker no-padding"  title="Seleccione" data-live-search="true" data-size="5" name="item[]" id="item1" onchange="rellenar(1, this.value);" required="">
                  @foreach($inventario as $item)
                  <option value="{{$item->id}}">{{$item->producto}}</option>
                  @endforeach
                  </select>
                  <p style="text-align: left;     margin: 0;"> 
                  <a href="{{route('inventario.create')}}" target="_blanck"><i class="fas fa-plus"></i> Nuevo Producto</a></p>
                </td>
                <td>
                  <input type="text" class="form-control form-control-sm" id="ref1" name="ref[]" placeholder="Referencia" required="">
                </td>
                <td>
                  <input type="text" class="form-control form-control-sm precio" id="precio1" name="precio[]" placeholder="Precio Unitario" onkeyup="total(1)" required="">
                </td>
                <td>
                  <input type="text" class="form-control form-control-sm nro" id="desc1" name="desc[]" placeholder="%" onkeyup="total(1)" >
                </td>
                <td>
                <select class="form-control form-control-sm selectpicker" name="impuesto[]" id="impuesto1" title="Impuesto" onchange="totalall();" required="">
                  @foreach($impuestos as $impuesto)
                    <option value="{{$impuesto->id}}" porc="{{$impuesto->porcentaje}}">{{$impuesto->nombre}} - {{$impuesto->porcentaje}}%</option>
                  @endforeach
                </select>
              </td>
              <td  style="padding-top: 1% !important;">                    				
                <textarea  class="form-control form-control-sm" id="descripcion1" name="descripcion[]" placeholder="Descripción" ></textarea>
              </td>
              <td>
                <input type="number" class="form-control form-control-sm" id="cant1" name="cant[]" placeholder="Cantidad" onchange="total(1);" min="1" required="">
                <p class="text-danger nomargin" id="pcant1"></p>
              </td>
              <td>
                <input type="text" class="form-control form-control-sm" id="total1" value="0" disabled="">
              </td>
              <td>
                <button type="button" class="btn btn-outline-secondary btn-icons" onclick="Eliminar(1);">X</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
		</div>
    <button class="btn btn-outline-primary" onclick="createRow();" type="button" style="margin-top: 5%">Agregar línea</button>

    <!-- Totales -->
		<div class="row" style="margin-top: 10%;">
			<div class="col-md-4 offset-md-8">
				<table class="text-right widthtotal" id="totales">
					<tr>
						<td width="40%">Subtotal</td>
						<td>{{Auth::user()->empresa()->moneda}} <span id="subtotal">0</span></td>
					</tr>
					<tr>
						<td>Descuento</td><td id="descuento">{{Auth::user()->empresa()->moneda}} 0</td>
					</tr>
					<tr>
						<td>Subtotal</td>
						<td>{{Auth::user()->empresa()->moneda}} <span id="subsub">0</span></td>
					</tr>
				</table>
				<hr>
				<table class="text-right widthtotal" style="font-size: 24px !important;">
					<tr>
						<td width="40%">TOTAL</td>
						<td>{{Auth::user()->empresa()->moneda}} <span id="total">0</span></td>
					</tr>
				</table>
			</div>
		</div>
    <div class="row" style="margin-top: 5%; padding: 3%; min-height: 180px;">
      <div class="col-md-8 form-group">
        <label class="form-label">Términos y Condiciones</label>
        <textarea  class="form-control min_max_100" name="term_cond">{{Auth::user()->empresa()->terminos_cond}}</textarea>
      </div>
      <div class="col-md-4 form-group">
        <label class="form-label">Notas</label>
        <textarea  class="form-control form-control-sm min_max_100" name="notas">{{Auth::user()->empresa()->notas_fact}}</textarea>
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