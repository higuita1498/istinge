@extends('layouts.app')
@section('content')
  <!--Formulario Facturas-->
  <form method="POST" action="{{ route('cotizaciones.update', $cotizacion->id) }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-factura" >
    {{ csrf_field() }}
      <input name="_method" type="hidden" value="PATCH">
    <input type="hidden" value="1" name="cotizacion" id="cotizacion_si">
    <input type="hidden" name="tipocliente" id="tipocliente" value="{{$cotizacion->cliente?1:2}}">
    <div class="row text-right">
      <div class="col-md-6">
        <div class="form-group row" id="div-contacto" @if(!$cotizacion->cliente)style="display:none" @endif>
          <label class="col-sm-4 col-form-label">Cliente <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <div class="input-group">
              <select class="form-control selectpicker" name="cliente" id="cliente" required="" title="Seleccione" data-live-search="true" data-size="5" onchange="contacto(this.value);">
                @foreach($clientes as $client)
                  <option @if($cotizacion->cliente) {{$cotizacion->cliente==$client->id?'selected':''}}  @endif value="{{$client->id}}">{{$client->nombre}} {{$client->apellido1}} {{$client->apellido2}} - {{$client->nit}}</option>
                @endforeach
              </select> 
            </div>
            <p class="text-left nomargin"> 
              <button type="button" class="btn btn-link no-padding" style="font-size: 13px" onclick="contacto_rapido();"><i class="fas fa-plus"></i> Crear Contacto Rápido</button>
              <!--<button type="button" class="btn btn-link no-padding" style="font-size: 13px" onclick="modal_show('{{route('contactos.create.modal')}}', 'small');"><i class="fas fa-plus"></i> Crear Contacto</button>-->
            </p>
          </div>
          <span class="help-block error">
            <strong>{{ $errors->first('cliente') }}</strong>
          </span>
        </div>    
        <div id="contacto-rapido" @if($cotizacion->cliente) style="display:none" @endif  >
          <div class="form-group row">
          <label class="col-sm-4 col-form-label">Cliente <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <input type="text" class="form-control" name="clienterapido" value="@if(!$cotizacion->cliente){{$cotizacion->cliente()->nombre}} @endif" id="clienterapido">
            <p class="text-left nomargin"> <small>Este Cliente no estará registrado como Contacto</small></p>

            <p class="text-left nomargin"> 
              <button type="button" class="btn btn-link no-padding" style="font-size: 13px" onclick="contacto_rapido(true);"> Usar Contacto del Sistema</button>
            </p>

          </div>
          <span class="help-block error">
            <strong>{{ $errors->first('cliente') }}</strong>
          </span>
        </div> 
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Teléfono</label>
          <div class="col-sm-8">
            <input type="text" class="form-control" name="telefono" id="telefono" value="@if(!$cotizacion->cliente){{$cotizacion->cliente()->telefono}} @endif">
          </div>
        </div> 

        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Correo Electrónico <small><br> Para enviar documentos por correo</small></label>
          <div class="col-sm-8">
            <input type="email" class="form-control" id="email" name="email" data-error="Dirección de correo electrónico invalida" maxlength="100"  value="@if(!$cotizacion->cliente){{$cotizacion->cliente()->email}} @endif">
          </div>
        </div> 

        </div>
        <div class="form-group row">
          <label class="col-sm-4  col-form-label">Observaciones <br> <small>No visible en la cotización</small></label>
          <div class="col-sm-8">
            <textarea  class="form-control form-control-sm min_max_100" name="observaciones">{{$cotizacion->observaciones}}</textarea>
          </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-4 col-form-label">Notas de la cotización</label>
            <div class="col-sm-8">
              <textarea  class="form-control form-control-sm min_max_100" name="notas">{{$cotizacion->notas}}</textarea>
            </div>
          </div>
      </div>

      <div class="col-md-5 offset-md-1">
        <div class="form-group row">
          <label class="col-sm-5 col-form-label">Fecha <span class="text-danger">*</span></label>
          <div class="col-sm-7">
            <input type="text" class="form-control datepicker"  id="fecha" value="{{date('d-m-Y', strtotime($cotizacion->fecha))}}" name="fecha" disabled=""  >
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-5 col-form-label">Vencimiento <span class="text-danger">*</span></label>
          <div class="col-sm-7">
            <input type="text" class="form-control   " id="vencimiento" value="{{date('d-m-Y', strtotime($cotizacion->vencimiento))}}" name="vencimiento" disabled="">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-5 col-form-label">Vendedor</label>
          <div class="col-sm-7">
            <select name="vendedor" id="vendedor" class="form-control selectpicker " title="Seleccione" data-live-search="true" data-size="5">
              @foreach($vendedores as $vendedor)  
                <option value="{{$vendedor->id}}" {{$cotizacion->vendedor==$vendedor->id?'selected':''}}>{{$vendedor->nombre}}</option>
              @endforeach
            </select>
          </div> 
        </div>
        
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Lista de Precios</label>
          <div class="col-sm-8">
            <select name="lista_precios" id="lista_precios" class="form-control selectpicker">
              @foreach($listas as $lista)  
                <option value="{{$lista->id}}" {{$cotizacion->lista_precios==$lista->id?'selected':''}}>{{$lista->nombre()}} </option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Bodega <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <select name="bodega" id="bodega" class="form-control"  required="">
              @foreach($bodegas as $bodega)  
                <option value="{{$bodega->id}}" {{$cotizacion->bodega==$bodega->id?'selected':''}}>{{$bodega->bodega}}</option>
              @endforeach
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
                  @if($item->tipo_inventario==2)
                    <input type="text" class="form-control form-control-sm" name="item[]" id="item2" required="" placeholder="Nombre del Item" value="{{$item->producto()}}">
                    @if($extras)
                      <p class="text-left" style="margin: 0;"> <button type="button" class="btn  btn-xs btn-sm btn-link" onclick="camposextras('2');"><i class="fas fa-plus"></i>Agregar Campos Extras</button></p>

                      <div id="extra{{$cont}}">
                        @php $context=0; @endphp
                        @foreach($item->producto(true) as $key => $datos)
                        @php if($key==0){continue;} $context+=1; @endphp
                        <div id="divextra{{$cont}}_{{$context}}" class="row">
                          <div class="col-sm-5 no-padding">
                            <select required="" class="form-control form-control-sm selectpicker" title="Seleccione" data-live-search="true" data-size="5" name="campoextra{{$cont}}[]" id="campoextra{{$cont}}_{{$context}}">
                              @foreach($extras as $extra)
                                <option value="{{$extra->id}}" {{$datos[2]==$extra->id?'selected':''}}>{{$extra->nombre}}</option>
                              @endforeach
                            </select>
                          </div>
                          <div class="col-sm-5 no-padding"><input type="text" class="form-control form-control-sm" style="margin-top: 3%;" name="datoextra2[]" id="datoextra2_1" required="" placeholder="Dato" value="{{$datos[1]}}"></div>
                          <div class="col-sm-2 no-padding"><button type="button" onclick="Eliminar('divextra2_1');" class="btn btn-link btn-icons">X</button></div>
                      </div> 
                      @endforeach
                        
                    </div>  

                    @endif



                  @else
                    <select class="form-control form-control-sm selectpicker no-padding"  title="Seleccione" data-live-search="true" data-size="5" name="item[]" id="item{{$cont}}" onchange="rellenar({{$cont}}, this.value);" required="">
                    @foreach($inventario as $itemm)
                    <option value="{{$itemm->id}}" {{$item->producto==$itemm->id?'selected':''}}>{{$itemm->producto}} - ({{$item->ref}})</option>
                    @endforeach
                    </select>
                  @endif

                  <input type="hidden" name="camposextra[]" value="{{$cont}}">
                  
                  </div>
                </td>
                <td>
                    <div class="resp-refer">
                  <input type="text" class="form-control form-control-sm" id="ref{{$cont}}" name="ref[]" placeholder="Referencia"  value="{{$item->ref}}" required="">
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
    <!--  <button class="btn btn-outline-primary" onclick="createRowNoInventario();" type="button" style="margin-top: 5%">Agregar línea</button> -->
    <!-- Totales -->
    <div class="row" style="margin-top: 10%;">
      <div class="col-md-4 offset-md-8">
        <table class="text-right widthtotal" id="totales">
          
          <tr>
            <td width="40%">Subtotal</td>
            <td>{{Auth::user()->empresa()->moneda}} <span id="subtotal">{{App\Funcion::Parsear($cotizacion->total()->subtotal)}}</span></td>
          </tr>
          <tr> 
            <td>Descuento</td><td id="descuento">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($cotizacion->total()->descuento)}}</td>
          </tr>
          <tr>
            <td>Subtotal</td>
            <td>{{Auth::user()->empresa()->moneda}} <span id="subsub">{{App\Funcion::Parsear($cotizacion->total()->subsub)}}</span></td>
          </tr>

          @php $cont=0; @endphp
           @if($cotizacion->total()->imp)
            @foreach($cotizacion->total()->imp as $imp)
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
            <input type="hidden" id="total_value">
            <td>{{Auth::user()->empresa()->moneda}} <span id="total">{{App\Funcion::Parsear($cotizacion->total()->total)}}</span></td>
          </tr>
        </table>
      </div>
    </div>
    </div>
    <hr> 
    <!--Botones Finales -->
    <div class="row" >
      <div class="col-md-12 text-right" style="padding-top: 1%;">
        <a href="{{route('cotizaciones.index')}}" class="btn btn-outline-secondary">Cancelar</a>
        <button type="submit" class="btn btn-success">Guardar</button>
      </div>
    </div>
  </form>
  {{-- Modal Nuevo producto  --}}
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

          </div>
      </div>
  </div>
  {{--/Modal Nuevo producto  --}}
  <input type="hidden" id="impuestos" value="{{json_encode($impuestos)}}">
  <input type="hidden" id="allproductos" value="{{json_encode($inventario)}}">
  <input type="hidden" id="url" value="{{url('/')}}">
  <input type="hidden" id="jsonproduc" value="{{route('inventario.all')}}">
  <input type="hidden" id="simbolo" value="{{Auth::user()->empresa()->moneda}}">
  <input type="hidden" id="camposestras" value="{{json_encode($extras)}}">

@endsection