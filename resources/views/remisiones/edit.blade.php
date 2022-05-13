@extends('layouts.app')
@section('content')
  <!--Formulario Facturas-->
  <form method="POST" action="{{ route('remisiones.update', $remision->id) }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-factura" >
    {{ csrf_field() }} 
    <input name="_method" type="hidden" value="PATCH">
    <input type="hidden" value="1" name="cotizacion" id="cotizacion_si">
    <div class="row text-right">
      <div class="col-md-5">
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Cliente <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <div class="input-group">
              <select class="form-control selectpicker" name="cliente" id="cliente" required="" title="Seleccione" data-live-search="true" data-size="5" onchange="contacto(this.value);">
                @foreach($clientes as $client)
                  <option {{$remision->cliente==$client->id?'selected':''}} value="{{$client->id}}">{{$client->nombre}} {{$client->apellido1}} {{$client->apellido2}} - {{$client->nit}}</option>
                @endforeach
              </select>
            </div>
          </div>
          <span class="help-block error">
            <strong>{{ $errors->first('cliente') }}</strong>
          </span>
        </div>    
        <div class="form-group row">
          <label class="col-sm-4  col-form-label">Observaciones <br> <small>No visible en la remisión</small></label>
          <div class="col-sm-8">
            <textarea  class="form-control form-control-sm min_max_100" name="observaciones">{{$remision->observaciones}}</textarea>
          </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-4 col-form-label">Notas de la remisión</label>
            <div class="col-sm-8">
              <textarea  class="form-control form-control-sm min_max_100" name="notas">{{$remision->notas}}</textarea>
            </div>
          </div>
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Bodega <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <select name="bodega" id="bodega" class="form-control"  required="">
              @foreach($bodegas as $bodega)  
                <option value="{{$bodega->id}}" {{$remision->bodega==$bodega->id?'selected':''}}>{{$bodega->bodega}}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>

      <div class="col-md-5 offset-md-2">
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Fecha <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <input type="text" class="form-control datepicker"  id="fecha" value="{{date('d-m-Y', strtotime($remision->fecha))}}" name="fecha" disabled=""  > 
          </div>
        </div>

        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Vencimiento <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <input type="text" class="form-control" id="vencimiento" value="{{date('d-m-Y', strtotime($remision->vencimiento))}}" name="vencimiento" disabled="">
          </div>
        </div>

        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Vendedor</label>
          <div class="col-sm-8">
            <select name="vendedor" id="vendedor" class="form-control selectpicker " title="Seleccione" data-live-search="true" data-size="5">
              @foreach($vendedores as $vendedor)  
                <option value="{{$vendedor->id}}" {{$remision->vendedor==$vendedor->id?'selected':''}}>{{$vendedor->nombre}}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Lista de Precios</label>
          <div class="col-sm-8">
            <select name="lista_precios" id="lista_precios" class="form-control selectpicker" required="">
              @foreach($listas as $lista)  
                <option value="{{$lista->id}}" {{$remision->lista_precios==$lista->id?'selected':''}}>{{$lista->nombre()}} </option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Tipo de documento <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <select name="documento" id="documento" class="form-control selectpicker " data-live-search="true" data-size="5" required="">
              <option value="1" {{$remision->documento==1?'selected':''}}>Remisión</option>
              <option value="2" {{$remision->documento==2?'selected':''}}>Orden de Servicio</option>
            </select>
          </div>
        </div>
      </div>
      <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
      
    </div>

    <hr>
    	<div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
			<button type="button" class="close" data-dismiss="alert">×</button>
			<strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
		</div>
    <!-- Desgloce -->
    <div class="fact-table">
    <div class="row">
      <div class="col-md-12">
        <table class="table table-striped table-sm" id="table-form" width="100%">
          <thead class="thead-dark">
            <tr>
              <th width="29%">Ítem/Referencia</th>
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
                <td  class="no-padding"> 
                <div class="resp-item">
                  <input type="hidden" name="id_item{{$cont}}" value="{{$item->id}}">                     
                  <select class="form-control form-control-sm selectpicker no-padding"  title="Seleccione" data-live-search="true" data-size="5" name="item[]" id="item{{$cont}}" onchange="rellenar({{$cont}}, this.value);" required="">
                  @foreach($inventario as $itemm)
                  <option value="{{$itemm->id}}" {{$item->producto==$itemm->id?'selected':''}}>{{$itemm->producto}} - ({{$item->ref}})</option>
                  @endforeach
                  </select>
                  <p class="text-left nomargin">
                            <a href="" data-toggle="modal" data-target="#modalproduct" class="modalTr" tr="{{$cont}}">
                                <i class="fas fa-plus"></i> Nuevo Producto
                            </a>
                        </p>
                  </div>
                  
                </td>
                <td>
                    <div class="resp-refer">
                  <input type="text" class="form-control form-control-sm" id="ref{{$cont}}" name="ref[]" placeholder="Referencia" required="" value="{{$item->ref}}">
                </div>
                </td>
                <td class="monetario">
                    <div class="resp-precio">
                  <input type="number" class="form-control form-control-sm" id="precio{{$cont}}" name="precio[]" placeholder="Precio Unitario" onkeyup="total({{$cont}})" required="" maxlength="24" min="0" value="{{$item->precio}}">
                </div>
                </td>
                <td>
                  <input type="text" class="form-control form-control-sm" id="desc{{$cont}}" name="desc[]" placeholder="%" onkeyup="total({{$cont}})" value="{{$item->desc}}">
                </td>
                <td>
                <select class="form-control form-control-sm selectpicker" name="impuesto[]" id="impuesto{{$cont}}" title="Impuesto" onchange="totalall();" required="">
                  @foreach($impuestos as $impuesto)
                    <option value="{{$impuesto->id}}" porc="{{$impuesto->porcentaje}}" {{$item->id_impuesto==$impuesto->id?'selected':''}}>{{$impuesto->nombre}} - {{$impuesto->porcentaje}}%</option>
                  @endforeach
                </select>
              </td>
              <td  style="padding-top: 1% !important;">     
              <div class="resp-descripcion">
                <textarea  class="form-control form-control-sm" id="descripcion{{$cont}}" name="descripcion[]" placeholder="Descripción" >{{$item->descripcion}}</textarea>
              </div>
              </td>
              <td>
                <input type="number" class="form-control form-control-sm" id="cant{{$cont}}" name="cant[]" placeholder="Cantidad" onchange="total({{$cont}});" min="1" required="" value="{{$item->cant}}">
                <p class="text-danger nomargin" id="pcant{{$cont}}"></p>
              </td>
              <td>
                  <div class="resp-total">
                <input type="text" class="form-control form-control-sm text-right" id="total{{$cont}}" value="{{App\Funcion::Parsear($item->total())}}" disabled="">
                </div>
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
            <td>{{Auth::user()->empresa()->moneda}} <span id="subtotal">{{App\Funcion::Parsear($remision->total()->subtotal)}}</span></td>
          </tr>
          <tr> 
            <td>Descuento</td><td id="descuento">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($remision->total()->descuento)}}</td>
          </tr>
          <tr>
            <td>Subtotal</td>
            <td>{{Auth::user()->empresa()->moneda}} <span id="subsub">{{App\Funcion::Parsear($remision->total()->subsub)}}</span></td>
          </tr>

          @php $cont=0; @endphp
           @if($remision->total()->imp)
            @foreach($remision->total()->imp as $imp)
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
            <td>{{Auth::user()->empresa()->moneda}} <span id="total">{{App\Funcion::Parsear($remision->total()->total)}}</span></td>
          </tr>
        </table>
      </div>
    </div>
    </div>
    <hr>
    <!--Botones Finales -->
    <div class="row" >
      <div class="col-md-12 text-right" style="padding-top: 1%;">
        <a href="{{route('remisiones.index')}}" class="btn btn-outline-secondary">Cancelar</a>
        <button type="submit" class="btn btn-success">Guardar</button>
      </div>
    </div>
  </form>
  <input type="hidden" id="impuestos" value="{{json_encode($impuestos)}}">
  <input type="hidden" id="allproductos" value="{{json_encode($inventario)}}">
  <input type="hidden" id="url" value="{{url('/')}}">
  <input type="hidden" id="jsonproduc" value="{{route('inventario.all')}}">
  <input type="hidden" id="simbolo" value="{{Auth::user()->empresa()->moneda}}">

 {{-- <div class="modal fade" id="myModal2" role="dialog">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal">&times;</button>
          <h4 class="modal-title"></h4>
        </div>
        <div class="modal-body">
          @include('inventario.modal.createRemision')
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>--}}
  <div class="modal fade" id="modalproduct" role="dialog">
    <div class="modal-dialog modal-lg">
        <input type="hidden" id="trFila" value="0">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                @include('inventario.modal.create')
            </div>
            {{--<div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>--}}
        </div>
    </div>
</div>
@endsection