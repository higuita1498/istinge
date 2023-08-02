@extends('layouts.app')
@section('content')
@if(Session::has('success-newcontact'))
<div class="alert alert-success" style="text-align: center;">
  {{Session::get('success-newcontact')}}
</div>

<script type="text/javascript">
  setTimeout(function(){ 
    $('.alert').hide();
    $('.active_table').attr('class', ' ');
  }, 5000);
</script>
@endif
  <!--Formulario Facturas-->
  <form method="POST" action="{{ route('remisiones.store') }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-factura" >
    {{ csrf_field() }} 
    <input type="hidden" value="1" name="cotizacion" id="remision_si">
    <div class="row text-right">
      <div class="col-md-5">
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Cliente <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <div class="input-group">
              <select class="form-control selectpicker" name="cliente" id="cliente" required="" title="Seleccione" data-live-search="true" data-size="5" onchange="contacto(this.value,false,2);">
                @foreach($clientes as $client)
                  <option {{old('cliente')==$client->id?'selected':''}} value="{{$client->id}}">{{$client->nombre}} {{$client->apellido1}} {{$client->apellido2}} - {{$client->nit}}</option>
                @endforeach
              </select>
                <div class="input-group-append" >
                <span class="input-group-text nopadding">
                  <a class="btn btn-outline-secondary btn-icons" title="Actualizar" onclick="contactos('{{route('contactos.clientes.json')}}', 'cliente');" style="margin-left: 8%;"><i class="fas fa-sync"></i></a>
                </span>
                </div>
            </div>
              <p class="text-left nomargin">
                  <a href="#{{--{{route('contactos.create')}}--}}" id="contacto">
                      <i class="fas fa-plus"></i> Nuevo Contacto
                  </a>
        </p>
          </div>
          <span class="help-block error">
            <strong>{{ $errors->first('cliente') }}</strong>
          </span>
        </div>    
        <div class="form-group row">
          <label class="col-sm-4  col-form-label">Observaciones <br> <small>No visible en la remisión</small></label>
          <div class="col-sm-8">
            <textarea  class="form-control form-control-sm min_max_100" name="observaciones"></textarea>
          </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-4 col-form-label">Notas de la remisión<a><i data-tippy-content="Notas visibles en la remision" class="icono far fa-question-circle"></i></a></label>
            <div class="col-sm-8">
              <textarea  class="form-control form-control-sm min_max_100" name="notas"></textarea>
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
        <div class="form-group row" style="text-align: center;">
          <label class="col-sm-12 col-form-label" > <h4><b class="text-primary">No. </b> {{$nro->remision}}</h4></label>
        </div>
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Fecha <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <input type="text" class="form-control datepicker"  id="fecha" value="{{date('d-m-Y')}}" name="fecha" disabled=""  > 
          </div>
        </div> 

        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Vencimiento <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <input type="text" class="form-control" id="vencimiento" value="{{date('d-m-Y')}}" name="vencimiento" disabled="">
          </div>
        </div>

        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Vendedor</label>
          <div class="col-sm-8">
            <select name="vendedor" id="vendedor" class="form-control selectpicker " title="Seleccione" data-live-search="true" data-size="5" required >
              @foreach($vendedores as $vendedor)  
                <option value="{{$vendedor->id}}">{{$vendedor->nombre}}</option>
              @endforeach
            </select>
          </div> 
        </div>

        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Lista de Precios <a><i data-tippy-content="Lista de precios asociada a la remisión, puedes agregar nuevas listas de precio haciendo <a href='#'>clíck aquí</a>" class="icono far fa-question-circle"></i></a></label>
          <div class="col-sm-8">
            <select name="lista_precios" id="lista_precios" class="form-control selectpicker">
              @foreach($listas as $lista)  
                <option value="{{$lista->id}}">{{$lista->nombre()}} </option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Tipo de documento <span class="text-danger">*</span><a><i data-tippy-content="Elige el nombre que desees dar a tus documentos" class="icono far fa-question-circle"></i></a></label>
          <div class="col-sm-8">
            <select name="documento" id="documento" class="form-control selectpicker " data-live-search="true" data-size="5" required="">
              <option value="1">Remisión</option>
              <option value="2">Orden de Servicio</option>
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
              <th width="5%"></th>
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
              <tr id="1">
                  <td class="no-padding">
                      <a class="btn btn-outline-secondary btn-icons" title="Actualizar" onclick="inventario('1');"><i class="fas fa-sync"></i></a>
                  </td>
                <td  class="no-padding">
                    <div class="resp-item">                        
                  <select class="form-control form-control-sm selectpicker no-padding"  title="Seleccione" data-live-search="true" data-size="5" name="item[]" id="item1" onchange="rellenar(1, this.value);" required="">
                      {{--@foreach($inventario as $key => $chunk)
                          @foreach($chunk as $item)
                              <option value="{{$item['id']}}">{{$item['producto']}} - {{$item['ref']}}</option>
                          @endforeach
                      @endforeach--}}

                      @foreach($inventario as $item)
                  <option value="{{$item->id}}">{{$item->producto}} - ({{$item->ref}})</option>
                  @endforeach
                  </select>
                        <p class="text-left nomargin">
                            <a href="" data-toggle="modal" data-target="#modalproduct" class="modalTr" tr="1">
                                <i class="fas fa-plus"></i> Nuevo Producto
                            </a>
                        </p>
                  </div>
                </td>
                <td>
                    <div class="resp-refer">
                  <input type="text" class="form-control form-control-sm" id="ref1" name="ref[]" placeholder="Referencia" required="">
                </div>
                </td>
                <td class="monetario">
                    <div class="resp-precio">
                  <input type="number" class="form-control form-control-sm" id="precio1" name="precio[]" placeholder="Precio Unitario" onkeyup="total(1)" required="" maxlength="24" min="0">
                    </div>
                </td>
                <td>
                  <input type="text" class="form-control form-control-sm" id="desc1" name="desc[]" placeholder="%" onkeyup="total(1)" >
                </td>
                <td>
                <select class="form-control form-control-sm selectpicker" name="impuesto[]" id="impuesto1" title="Impuesto" onchange="totalall();" required="">
                  @foreach($impuestos as $impuesto)
                    <option value="{{$impuesto->id}}" porc="{{$impuesto->porcentaje}}">{{$impuesto->nombre}} - {{$impuesto->porcentaje}}%</option>
                  @endforeach
                </select>
              </td>
              <td  style="padding-top: 1% !important;">
                  <div class="resp-descripcion">                        
                <textarea  class="form-control form-control-sm" id="descripcion1" name="descripcion[]" placeholder="Descripción" ></textarea>
                </div>
              </td>
              <td>
                <input type="number" class="form-control form-control-sm" id="cant1" name="cant[]" placeholder="Cantidad" onchange="total(1);" min="1" required="" maxlength="24">
                <p class="text-danger nomargin" id="pcant1"></p>
              </td>
              <td>
                  <div class="resp-total">
                <input type="text" class="form-control form-control-sm text-right" id="total1" value="0" disabled="">
                </div>
              </td>
              <td>
                <button type="button" class="btn btn-outline-secondary btn-icons" onclick="Eliminar(1);">X</button>
              </td>
            </tr>
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
    </div>
    <hr>
    <!--Botones Finales -->
    <div class="row" >
        <div class="col-md-2" style="padding-top: 1%;">
            <a href="{{route('facturas.index')}}" class="btn btn-outline-secondary">Cancelar</a>
        </div>
        <div class="col-md-2">
            <div class="form-check form-check-flat">
                <label class="form-check-label">
                    <input type="checkbox" class="form-check-input" name="pago" id="pago" value="1"> Agregar Pago
                    <i class="input-helper"></i></label>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-check form-check-flat">
                <label class="form-check-label">
                    <input type="checkbox" class="form-check-input" name="new" id="new" value="1"> Crear una nueva
                    <i class="input-helper"></i></label>
            </div>
        </div>
        <div class="col-md-2">
            <div class="form-check form-check-flat">
                <label class="form-check-label">
                    <input type="checkbox" class="form-check-input" name="print"  value="1">Imprimir
                    <i class="input-helper"></i></label>
            </div>
        </div>
        <!--
        <div class="col-md-2">
            <div class="form-check form-check-flat">
                <label class="form-check-label">
                    <input type="checkbox" class="form-check-input" name="send" value="1">Enviar por correo
                    <i class="input-helper"></i></label>
            </div>
        </div>
        -->
        <div class="col-md-2">
            <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
        </div>
    </div>
  </form>
{{--
  <div class="modal fade" id="myModal2" role="dialog">
      <div class="modal-dialog modal-lg">
          <div class="modal-content">
              <div class="modal-header">
                  <button type="button" class="close" data-dismiss="modal">&times;</button>
                  <h4 class="modal-title"></h4>
              </div>
              <div class="modal-body">
                  @include('inventario.modal.create')
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              </div>
          </div>
      </div>
  </div>--}}

{{-- Modal contacto nuevo --}}
<div class="modal fade" id="contactoModal" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="modal-titlec"></h4>
            </div>
            <div class="modal-body" id="modal-bodyc">
                {{--@include('contactos.modal.modal')--}}
            </div>
            {{-- <div class="modal-footer">
               <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
             </div>--}}
        </div>
    </div>
</div>

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
            {{--<div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>--}}
        </div>
    </div>
</div>
{{--/Modal Nuevo producto  --}}

  <input type="hidden" id="impuestos" value="{{json_encode($impuestos)}}">
  <input type="hidden" id="allproductos" value="{{json_encode($inventario)}}">
  <input type="hidden" id="url" value="{{url('/')}}">
  <input type="hidden" id="jsonproduc" value="{{route('inventario.all')}}">
  <input type="hidden" id="simbolo" value="{{Auth::user()->empresa()->moneda}}">

  <script
      src="https://code.jquery.com/jquery-3.3.1.min.js"
      integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
      crossorigin="anonymous"></script>
  <script>

      $(document).ready(function(){

          let lastRegis = new URLSearchParams(window.location.search);

          if(lastRegis.has('pro')){

              let idPro     = lastRegis.get('pro');
              let impuesto  = $('#impuestosId').val();

              setTimeout(function () {
                  $('#item1').val(idPro).change();
                  $('#impuesto1').val(impuesto).change();
                  clearTimeout(this);
              }, 1000);

          }

          if(lastRegis.has('cnt')){

              let idCnt     = lastRegis.get('cnt');

              setTimeout(function () {
                  $('#cliente').val(idCnt).change();
                  clearTimeout(this);
              }, 2000);

          }
      });



  </script>

@endsection
