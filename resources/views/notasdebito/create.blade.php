@extends('layouts.app')
@section('content')
  <form method="POST" action="{{ route('notasdebito.store') }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-factura" >
    {{ csrf_field() }}
    {{--<input type="hidden" value="1" name="cotizacion" id="cotizacion_si">
    <input type="hidden" value="1" name="fact_prov" id="fact_prov">
    <input type="hidden" value="1" name="fact_prov" id="orden_si">

    <input type="hidden" value="1" name="fact_debito" id="fact_debito">--}}

      <input type="hidden" value="1" name="fact_prov" id="fact_prov">
      <input type="hidden" value="1" name="cotizacion" id="cotizacion_si">


    <div class="row" style=" text-align: right;">
      <div class="col-md-6">

        {{--<div class="form-group row">
          <label class="col-sm-4 col-form-label">Numeración</label>
          <div class="col-sm-8">
            <input type="text" name="codigo" class="form-control">
          </div>
        </div>--}}
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Proveedor <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <select class="form-control form-control-sm selectpicker" name="proveedor" id="cliente" title="Seleccione" data-live-search="true" data-size="5" required="" onchange="contacto(this.value); getFacturas(this.value)">
              @foreach($proveedores as $proveedor)
                <option {{old('proveedor')==$proveedor->id?'selected':''}} value="{{$proveedor->id}}">{{$proveedor->nombre}} - {{$proveedor->nit}}</option>
              @endforeach
            </select>
          </div>
          <span class="help-block error">
                <strong>{{ $errors->first('proveedor') }}</strong>
          </span>
        </div>
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Observaciones <br><small>(no visible en la nota crédito)</small> </label>
          <div class="col-sm-8">
            <textarea  class="form-control form-control-sm min_max_100" name="observaciones"></textarea>
          </div>
        </div>
      </div>
      <div class="col-md-5 offset-md-1">
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Fecha <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <input type="text" class="form-control datepicker"  id="fecha" value="{{date('d-m-Y')}}" name="fecha" disabled=""  >
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Bodega <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <select name="bodega" id="bodega" class="form-control" required="">
              @foreach($bodegas as $bodega)
                <option value="{{$bodega->id}}">{{$bodega->bodega}}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Factura<a>
              <i data-tippy-content="Lista de facturas de venta asociadas al lciente" class="icono far fa-question-circle"></i></a></label>
          <div class="col-sm-8">
            <select name="factura" id="lista_factura" required class="form-control form-control-sm  selectpicker" onchange="itemsFactura(this.value);" title="Seleccione Factura" data-live-search="true" data-size="5">

            </select>
          </div>
        </div>
      </div>
    </div>
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
              <th width="39%">Categoría/Ítem</th>
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
              <td  class="no-padding">
              <div class="resp-item">
                <select class="form-control form-control-sm selectpicker no-padding"  title="Seleccione" data-live-search="true" data-size="5" name="item[]" id="item1" onchange="rellenar(1, this.value);" required="">
                <optgroup label="Ítems inventariables">
                @foreach($inventario as $item)
                <option value="{{$item->id}}">{{$item->producto}} - ({{$item->ref}})</option>
                 @endforeach
                </optgroup>
                @foreach($categorias as $categoria)
                <optgroup label="{{$categoria->nombre}}">
                    @foreach($categoria->hijos(true) as $categoria1)
                      <option {{old('categoria')==$categoria1->id?'selected':''}} value="cat_{{$categoria1->id}}" {{$categoria1->estatus==0?'disabled':''}}>{{$categoria1->nombre}}</option>
                      @foreach($categoria1->hijos(true) as $categoria2)
                          <option class="hijo" {{old('categoria')==$categoria2->id?'selected':''}} value="cat_{{$categoria2->id}}" {{$categoria2->estatus==0?'disabled':''}}>{{$categoria2->nombre}}</option>
                        @foreach($categoria2->hijos(true) as $categoria3)
                          <option class="nieto" {{old('categoria')==$categoria3->id?'selected':''}} value="cat_{{$categoria3->id}}" {{$categoria3->estatus==0?'disabled':''}}>{{$categoria3->nombre}}</option>
                          @foreach($categoria3->hijos(true) as $categoria4)
                            <option class="bisnieto" {{old('categoria')==$categoria4->id?'selected':''}} value="cat_{{$categoria4->id}}" {{$categoria3->estatus==0?'disabled':''}}>{{$categoria4->nombre}}</option>
                          @endforeach

                        @endforeach

                      @endforeach
                    @endforeach
                  </optgroup>
                  @endforeach
                </select>
              </div>
              </td>
              <td class="monetario">
                  <div class="resp-precio">
                <input type="number" class="form-control form-control-sm" id="precio1" name="precio[]" placeholder="Precio Unitario" onkeyup="total(1)" required="" maxlength="12" min="0">
              </div>
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
              <div class="resp-descripcion">
                <textarea  class="form-control form-control-sm" id="descripcion1" name="descripcion[]" placeholder="Descripción" ></textarea>
              </div>
              </td>
              <td>
                <input type="number" class="form-control form-control-sm" id="cant1" name="cant[]" placeholder="Cantidad" onchange="total(1);" min="1" required="">
                <p class="text-danger nomargin" id="pcant1"></p>
              </td>
              <td>
                  <div class="resp-total">
                <input type="text" class="form-control form-control-sm text-right" id="total1" value="{{App\Funcion::Parsear(0)}}" disabled="">
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

        <div class="row"  style="margin-top: 10%; margin-left:0px;">
            <div class="col-md-7 no-padding">
                <h5>RETENCIONES</h5>
                <table class="table table-striped table-sm" id="table-retencion">
                    <thead class="thead-dark">
                    <th width="60%">Tipo de Retención</th>
                    <th width="34%">Valor</th>
                    <th width="5%"></th>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <button class="btn btn-outline-primary" onclick="CrearFilaRetencion();" type="button" style="margin-top: 2%;">Agregar Retención</button><a><i data-tippy-content="Agrega nuevas retenciones haciendo <a href='#'>clíck aquí</a>" class="icono far fa-question-circle"></i></a>
            </div>
        </div>
    <!-- Totales -->
        <div class="row" style="margin-top: 10%;">
            <div class="col-md-4 offset-md-8">
                <table class="text-right widthtotal" >
                    <tr>
                        <td width="40%">Subtotal</td>
                        <td>{{Auth::user()->empresa()->moneda}} <span id="subtotal">0</span></td>
                        <input type="hidden" id="subtotal_categoria_js" value="0">
                    </tr>
                    <tr>
                        <td>Descuento</td><td id="descuento">{{Auth::user()->empresa()->moneda}} 0</td>
                    </tr>
                </table>
                <table class="text-right widthtotal"  style="width: 100%" id="totales">
                    <tr style="display: none">
                        <td width="40%">Subtotal</td>
                        <td>{{Auth::user()->empresa()->moneda}} <span id="subsub">0</span></td>
                    </tr>
                    <tr>
                        <td width="40%">Subtotal</td>
                        <td>{{Auth::user()->empresa()->moneda}} <span id="subtotal2">0</span></td>
                    </tr>
                </table>

                <table  class="text-right widthtotal"  id="totalesreten" style="width: 100%">
                    <tbody></tbody>
                </table>


                <hr>
                <table class="text-right widthtotal" style="font-size: 24px !important;">
                    <tr>
                        <td width="40%">TOTAL </td>
                        <td>{{Auth::user()->empresa()->moneda}} <span id="total">0</span></td>
                    </tr>
                </table>
            </div>
        </div>
    <div class="alert alert-danger  alert-view-show" style="display: none;" id="error-cliente"></div>
      {{--
      <div class="row">
          <div class="col-md-12">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Hay devolución de dinero</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Débito a factura de venta</a>
              </li>
        </ul>

  <div class="tab-content " id="myTabContent">
    <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
      <table class="table table-striped pagos" width="100%" id="devoluciones-dinero">
        <thead>
            <th width="24%" class="text-center">Fecha</th>
            <th width="25%" class="text-center">Cuenta</th>
            <th width="20%" class="text-center">Monto</th>
            <th width="25%" class="text-center">Observaciones</th>
            <th width="5%" class="text-center"></th>
        </thead>
        <tbody>
          <tr id="devol_1">
            <td class="form-group "><div class="resp-fecha"><input type="text" class="form-control datepickerinput" value="{{date('d-m-Y')}}" name="fecha_dev[]" id="fecha_dev1" disabled=""  style="border: 1px solid #a6b6bd52  !important;"></div></td>
            <td>
                <div class="resp-item">
              <select class="form-control form-control-sm selectpicker" name="cuentaa_dev[]" id="cuenta1" title="Seleccione" data-live-search="true" data-size="5">
                @php $tipos_cuentas=\App\Banco::tipos();@endphp
                @foreach($tipos_cuentas as $tipo_cuenta)
                  <optgroup label="{{$tipo_cuenta['nombre']}}">

                    @foreach($bancos as $cuenta)
                      @if($cuenta->tipo_cta==$tipo_cuenta['nro'])
                        <option value="{{$cuenta->id}}">{{$cuenta->nombre}}</option>
                      @endif
                    @endforeach
                  </optgroup>
                @endforeach
              </select>
            </div>
            </td>
            <td class="monetario"><div class="resp-precio"><input type="number" class="form-control form-control-sm" id="monto1" name="montoa_dev[]" placeholder="Monto" onchange="function_totales_facturas();"></div></td>
            <td  style="padding-top: 1% !important;">
            <div class="resp-descripcion">
              <textarea  class="form-control form-control-sm" id="descripcion1" name="descripciona_dev[]" placeholder="Descripción" ></textarea></td>
           </div>
            <td>
              <button type="button" class="btn btn-link btn-icons" onclick="Eliminar('devol_1');">X</button>
            </td>
          </tr>
        </tbody>
      </table>
      <button class="btn btn-link"  type="button" onclick="agregardevolucion();"><i class="fas fa-plus"></i>Agregar devolución de dinero</button>
    </div>
    <div class="tab-pane fade fact-table" id="profile" role="tabpanel" aria-labelledby="profile-tab">
      <table class="table table-striped table-hover pagos" width="100%" id="facturas-cliente">
        <thead>
          <th width="20%">Número</th>

          <th width="20%">Total</th>
          <th width="20%">Pagado</th>
          <th width="20%">Por pagar</th>
          <th width="15%">Monto</th>
          <th width="5%"></th>
        </thead>
        <tbody>
        </tbody>
      </table>
      <button class="btn btn-link"  type="button" onclick="agregarfactura('true');"><i class="fas fa-plus"></i>Agregar factura de Venta</button>
    </div>
  </div>
        </div>
      </div>
      </div>--}}
      <hr>
      <div class="row" >
        <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
          <a href="{{route('notascredito.index')}}" class="btn btn-outline-secondary">Cancelar</a>
          <button type="submit" class="btn btn-success" id="boton-guardar">Guardar</button>
        </div>
      </div>
    </form>

  <input type="hidden" id="impuestos" value="{{json_encode($impuestos)}}">
  @foreach ($impuestos as $impuesto)
      <input type="hidden" id="hddn_imp_{{$impuesto->id}}" value="{{$impuesto->tipo}}">
  @endforeach
  @php
    $i=0;
  @endphp
  @foreach ($impuestos as $impuesto)
      @php
          $i++;
      @endphp
      <input type="hidden" id="hddn_imp_INDEX{{$impuesto->id}}" value="{{$i}}">
  @endforeach
  <input type="hidden" id="allproductos" value="{{json_encode($inventario)}}">
  <input type="hidden" id="json-facturas" value="">
  <input type="hidden" id="url" value="{{url('/')}}">
  <input type="hidden" id="jsonproduc" value="{{route('inventario.all')}}">
  <input type="hidden" id="simbolo" value="{{Auth::user()->empresa()->moneda}}">
  <input type="hidden" id="todaytoday" value="{{date('d-m-Y')}}">
  <input type="hidden" id="retenciones" value="{{json_encode($retenciones)}}">
  {{--<input type="hidden" id="bancos-input" value='
      @foreach($tipos_cuentas as $tipo_cuenta)
        <optgroup label="{{$tipo_cuenta["nombre"]}}">
          @foreach($bancos as $cuenta)
            @if($cuenta->tipo_cta==$tipo_cuenta["nro"])
              <option value="{{$cuenta->id}}">{{$cuenta->nombre}}</option>
            @endif
          @endforeach
        </optgroup>
      @endforeach'>
  <input type="hidden" id="allcategorias" value='
      @foreach($categorias as $categoria)
    <optgroup label="{{$categoria->nombre}}">
      @foreach($categoria->hijos(true) as $categoria1)
        <option {{old('categoria')==$categoria1->id?'selected':''}} value="cat_{{$categoria1->id}}" {{$categoria1->estatus==0?'disabled':''}}>{{$categoria1->nombre}}</option>
        @foreach($categoria1->hijos(true) as $categoria2)
          <option class="hijo" {{old('categoria')==$categoria2->id?'selected':''}} value="cat_{{$categoria2->id}}" {{$categoria2->estatus==0?'disabled':''}}>{{$categoria2->nombre}}</option>
          @foreach($categoria2->hijos(true) as $categoria3)
            <option class="nieto" {{old('categoria')==$categoria3->id?'selected':''}} value="cat_{{$categoria3->id}}" {{$categoria3->estatus==0?'disabled':''}}>{{$categoria3->nombre}}</option>
            @foreach($categoria3->hijos(true) as $categoria4)
              <option class="bisnieto" {{old('categoria')==$categoria4->id?'selected':''}} value="cat_{{$categoria4->id}}" {{$categoria3->estatus==0?'disabled':''}}>{{$categoria4->nombre}}</option>
            @endforeach
          @endforeach
        @endforeach
      @endforeach
    </optgroup>
  @endforeach'>--}}
@endsection

@section('scripts')
  <script>
      function getFacturas(id){
          // $('#error-cliente').hide();
          var url = $('#url').val()+'/empresa/facturasp/proveedor/'+id;
          $.ajax({
              url: url,
              complete: function(data){
                  console.log(data.responseText);
                  // $('#json-facturas').val(data.responseText);
                  data = JSON.parse(data.responseText);
                  $('#lista_factura').find('option').remove();
                  $('#table-retencion tbody tr').remove();
                  $.each(data,function(key, value)
                  {
                      $('#lista_factura').append('<option value=' + value.id + '>' + value.codigo + '</option>');
                  });
                  $('#lista_factura').selectpicker('refresh');
              },
              error: function(data){
                  alert('Disculpe, estamos presentando problemas al tratar de enviar el formulario, intentelo mas tarde');
              }
          });

      }

      function itemsFactura(id) {

          var url = $('#url').val() + '/empresa/notasdebito/items/' + id;
          $.ajax({
              url: url,
              /* beforeSend: function(){
                   cargando(true);
               },*/
              complete: function (data) {
                  console.log(data.responseText);
                  data = JSON.parse(data.responseText);

                  $('#table-form tbody tr').remove();
                  $('#table-retencion tbody tr').remove();
                  var i = 0;
                  $.each(data, function (key, value) {

                      if (value.desc == null) {
                          value.desc = '';
                      }
                      if (value.impuesto == null ) {
                          value.impuesto = '';
                      }
                      if (value.descripcion == null) {
                          value.descripcion = '';
                      }
                      if (value.id_impuesto == null ) {
                          value.id_impuesto = '';
                      }
                      i++;
                      $('#table-form').append(
                          '<tr id="' + i + '">' +
                          '<td class="no-padding">' +
                          '<div class="resp-item">' +
                          '<input type="hidden" name="item[]" value="' + value.producto + '">' +
                          '<input type="text" class="form-control form-control-sm" disabled value="' + value.nombre + ' - ' + value.refer + '">' +
                          '</div>' +
                          '</td>' +
                          '<td class="monetario">' +
                          '<div class="resp-precio">' +
                          '<input type="number" class="form-control form-control-sm "  id="precio' + i + '" name="precio[]" placeholder="Precio Unitario" onkeyup="total(' + i + ')" required maxlength="24" min="0" value="' + value.precio + '">' +
                          '</div>' +
                          '</td>' +
                          '<td>' +
                          '<div class="resp-item">' +
                          '<input type="hidden" name="descuento[]" value="' + value.desc + '" >' +
                          '<input type="text" class="form-control form-control-sm nro "  id="desc' + i + '" name="desc[]" placeholder="%" value="' + value.desc + '" disabled>' +
                          '</div>' +
                          '</td>' +
                          '<td class="no-padding">' +
                          '<div class="resp-item">' +
                          '<input type="hidden" name="impuesto[]" value="' + value.id_impuesto + '" porc="' + value.impuesto + '"  id="impuesto' + i + '">' +
                          '<input type="text"  class="form-control form-control-sm" disabled value="' + value.impuesto + '">' +
                          '</div>' +
                          '</td>' +
                          '<td  style="padding-top: 1% !important;">' +
                          '<div class="resp-descripcion">' +
                          '<textarea  class="form-control form-control-sm" id="descripcion' + i + '" name="descripcion[]" placeholder="Descripción" >' + value.descripcion + '</textarea>' +
                          '</div>' +
                          '</td>' +
                          '<td>' +
                          '<input type="number" class="form-control form-control-sm cantidades" id="cant' + i + '" name="cant[]" placeholder="Cantidad" onkeyup="total(' + i + ')" onclick="total(' + i + ');" min="1" value="' + value.cant + '" required="">' +
                          '<p class="text-danger nomargin" id="pcant' + i + '"></p>' +
                          '</td>' +
                          '<td>' +
                          '<div class="resp-total">' +
                          '<input type="text" class="form-control form-control-sm text-right " name="total[]" id="total' + i + '" value="" disabled>' +
                          '</div>' +
                          '</td>' +
                          '<td>' +
                          '<button type="button" class="btn btn-outline-secondary btn-icons" onclick="Eliminar(' + i + ');">X</button>' +
                          '</td>' +
                          '</tr>'
                      );
                      $('.cantidades').trigger('click');
                  });


              },
              error: function (data) {

                  alert('Disculpe, estamos presentando problemas al tratar de enviar el formulario, intentelo mas tarde');
              }
          });
      }
  </script>
@endsection
