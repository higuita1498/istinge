@extends('layouts.app')
@section('content')
@if(!empty($_GET['pro']))
@php($new = \App\Model\Inventario\Inventario::where('id', htmlspecialchars($_GET['pro']))->get())@endphp
<input type="hidden" id="impuestosId" value="{{$new[0]['id_impuesto']}}">
@endif

@if(Session::has('error'))
<div class="alert alert-danger">
  {{Session::get('error')}}
</div>

<script type="text/javascript">
  setTimeout(function() {
    $('.alert').hide();
    $('.active_table').attr('class', ' ');
  }, 5000);
</script>
@endif

@if(Session::has('success-newcontact'))
<div class="alert alert-success" style="text-align: center;">
  {{Session::get('success-newcontact')}}
</div>

<script type="text/javascript">
  setTimeout(function() {
    $('.alert').hide();
    $('.active_table').attr('class', ' ');
  }, 5000);
</script>
@endif

@include('etiquetas-estado.create', ['post' => false, 'colores' => $colores, 'tipo' => 4])


<form method="POST" action="{{ route('facturasp.store') }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-factura">
  @csrf
  {{-- <input type="hidden" value="1" name="cotizacion" id="cotizacion_si">--}}
  <input type="hidden" value="1" name="orden_si" id="orden_si">
  <input type="hidden" value="1" name="fact_prov" id="fact_prov">

  <div class="row text-right">
    <div class="col-md-5">

      <div class="form-group row">
        <label class="col-sm-4 col-form-label">Productor <span class="text-danger">*</span></label>
        <div class="col-sm-8">
          <div class="input-group">
            <select class="form-control selectpicker" name="proveedor" id="cliente" required="" title="Seleccione" data-live-search="true" data-size="5" onchange="contacto(this.value);">
              @foreach($clientes as $client)
              <option {{old('productor')==$client->id?'selected':''}} {{$proveedor==$client->id?'selected':''}} value="{{$client->id}}">{{$client->nombre}} - {{$client->nit}}</option>
              @endforeach
            </select>
          </div>
          <p class="text-left nomargin">
            <a href="{{route('contactos.create')}}" data-toggle="modal" data-target="#myModal" id='contacto'><i class="fas fa-plus"></i> Nuevo Contacto...</a>
          </p>
        </div>
        <span class="help-block error">
          <strong>{{ $errors->first('productor') }}</strong>
        </span>
      </div>
      <div class="form-group row">
        <label class="col-sm-4  col-form-label">Observaciones <br> <small>(No visible en el documento impreso)</small></label>
        <div class="col-sm-8">
          <textarea class="form-control form-control-sm min_max_100" style="min-height: 97px !important;" name="observaciones" id="obs">{{old('observaciones')}}</textarea>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-sm-4  col-form-label">Notas <br> <small>(Visible en el documento impreso)</small></label>
        <div class="col-sm-8">
          <textarea class="form-control form-control-sm min_max_100" style="min-height: 97px !important;" name="notas" id="notas">{{old('notas')}}</textarea>
        </div>
      </div>

      @if ($estadoModulo)
      <div class="form-group row">
        <label class="col-sm-4 col-form-label">Etiqueta de estado</label>
        <div class="col-sm-8">
          <select class="form-control selectpicker" name="etiqueta_estado" id="etiqueta-estado" onchange="" title="Seleccione">
            @foreach($etiquetas as $etiqueta)
            <option value="{{$etiqueta->id}}" class="font-weight-bold" style="color:white; background-color:{{optional($etiqueta->color)->codigo}}" {{old('etiqueta_estado') == $etiqueta->id ? 'selected' : ''}}>
              {{$etiqueta->nombre}}
            </option>
            @endforeach
          </select>
          <button type="button" class="btn btn-link no-padding" style="font-size: 13px" onclick="crearEtiqueta();"> Crear nueva etiqueta</button>
        </div>
      </div>
      @endif

    </div>


    {{--
      <div class="form-check form-check-flat">
        <label class="form-check-label">
          Cliente
          <input type="checkbox" class="form-check-input" name="contacto[]" value="0">
        <i class="input-helper"></i><i class="input-helper"></i></label>
      </div> --}}




    <div class="col-md-6 offset-md-1">
      @if(auth()->user()->empresaObj->equivalente == 1)
      <div class="form-group row">

        <style>
          .form-check .form-check-label .input-helper:before {
            left: 3px;
          }
        </style>

        <label class="col-sm-4 col-form-label">¿Documento Soporte?<span class="text-danger">*</span></label>
        <div class="form-check form-check-flat">
          <label class="form-check-label">
            <input type="checkbox" class="form-check-input" name="equivalente" id="equivalente" value="1">
            <i class="input-helper"></i><i class="input-helper"></i></label>
        </div>

      </div>
      @endif
      <div class="form-group row" id="cod_dian" style="display: none">
        <label class="col-sm-4 col-form-label">Número Dian</label>
        <div class="col-sm-8">
          <input type="text" class="form-control" id="codigo_dian" name="codigo_dian" required maxlength="35" value="" readonly>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-sm-4 col-form-label">Número de factura</label>
        <div class="col-sm-8">
          <input type="text" class="form-control" id="codigo" name="codigo" required maxlength="35" value="{{$codigoFactura}}">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-sm-4 col-form-label">Fecha <span class="text-danger">*</span></label>
        <div class="col-sm-8">
          <input type="text" class="form-control" id="fecha" value="{{date('d-m-Y')}}" name="fecha" disabled="">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-sm-4 col-form-label">Plazo <a><i data-tippy-content="Tiempo maximo para realizar el pago, puedes agregar nuevos plazos haciendo <a href='#'>clíck aquí</a>" class="icono far fa-question-circle"></i></a></label>
        <div class="col-sm-8">
          <select name="plazo" id="plazo" class="form-control " title="Seleccione">
            @foreach($terminos as $termino)
            <option value="{{$termino->id}}" dias="{{$termino->dias}}">{{$termino->nombre}}</option>
            @endforeach
            <option value="n" dias="n">Vencimiento manual</option>
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-sm-4 col-form-label">Fecha de <br> Vencimiento <span class="text-danger">*</span></label>
        <div class="col-sm-8">
          <input type="text" class="form-control datepicker" id="vencimiento" value="{{date('d-m-Y')}}" name="vencimiento" disabled="">
        </div>
      </div>
      <div class="form-group row">
        <label class="col-sm-4 col-form-label">Comprador <a><i data-tippy-content="Comprador asociado a la factura de compra, puedes agregar nuevos comprador haciendo <a href='#'>clíck aquí</a>" class="icono far fa-question-circle"></i></a></label>
        <div class="col-sm-8">
          <select name="comprador" id="comprador" class="form-control selectpicker " title="Seleccione" data-size="5" required>
            @foreach($vendedores as $vendedor)
            <option value="{{$vendedor->id}}">{{$vendedor->nombre}}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="form-group row">
        <label class="col-sm-4 col-form-label">Bodega <span class="text-danger">*</span></label>
        <div class="col-sm-8">
          <select name="bodega" id="bodega" class="form-control" required="">
            @foreach($bodegas as $bodega)
            <option value="{{$bodega->id}}" {{old('bodega')==$bodega->id?'selected':''}}>{{$bodega->bodega}}</option>
            @endforeach
          </select>
        </div>
      </div>
    </div>
  </div>

  <div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
    <button type="button" class="close" data-dismiss="alert">×</button>
    <strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
  </div>


  <!-- agregar productos desde grupo modal -->
  <a data-toggle="modal" data-target="#modalGrupos" style="color: #022454; font-size:15px; cursor: pointer; text-decoration: underline;">
    Agregar productos desde un grupo.
  </a>
  <!-- Desgloce -->
  <div class="fact-table">
    <div class="row">
      <div class="col-md-12">
        <table class="table table-striped table-sm" id="table-form" width="100%">
          <thead class="thead-dark">
            <th width="5%"></th>
            <th width="28%">Categoría/Ítem</th>
            <th width="8%">Precio</th>
            <th width="5%">Desc %</th>
            <th width="12%">Impuesto</th>
            <th width="13%">Descripción</th>
            <th width="7%">Cantidad</th>
            <th width="10%">Total</th>
            <th width="2%"></th>
          </thead>
          <tbody>

            <tr id="1">
              <td class="no-padding">
                <a class="btn btn-outline-secondary btn-icons" title="Actualizar" onclick="inventario('1');" id="actualizar1"><i class="fas fa-sync"></i></a>
              </td>
              <td class="no-padding">
                <div class="resp-item">
                  @if($producto) <input type="hidden" id="producto_inv" value="true"> @endif
                  <select class="form-control form-control-sm buscar no-padding" title="Seleccione" data-live-search="true" data-size="5" name="item[]" id="item1" onchange="rellenar(1, this.value);" required="">
                    <optgroup label="Ítems">
                      @foreach($inventario as $item)
                      <option value="{{$item->id}}" @if($producto) {{$producto->id==$item->id?'selected':''}} @endif>{{$item->producto}} - ({{$item->ref}})</option>
                      @endforeach
                    </optgroup>
                    @foreach($categorias as $categoria)
                    <optgroup label="{{$categoria->nombre}}">
                      @foreach($categoria->hijos(true) as $categoria1)
                      <option {{old('categoria')==$categoria1->id?'selected':''}} value="cat_{{$categoria1->id}}" {{$categoria1->estatus==0?'disabled':''}}>{{$categoria1->nombre}}-({{$categoria1->codigo}})</option>
                      @foreach($categoria1->hijos(true) as $categoria2)
                      <option class="hijo" {{old('categoria')==$categoria2->id?'selected':''}} value="cat_{{$categoria2->id}}" {{$categoria2->estatus==0?'disabled':''}}>{{$categoria2->nombre}}-({{$categoria2->codigo}})</option>
                      @foreach($categoria2->hijos(true) as $categoria3)
                      <option class="nieto" {{old('categoria')==$categoria3->id?'selected':''}} value="cat_{{$categoria3->id}}" {{$categoria3->estatus==0?'disabled':''}}>{{$categoria3->nombre}}-({{$categoria3->codigo}})</option>
                      @foreach($categoria3->hijos(true) as $categoria4)
                      <option class="bisnieto" {{old('categoria')==$categoria4->id?'selected':''}} value="cat_{{$categoria4->id}}" {{$categoria3->estatus==0?'disabled':''}}>{{$categoria4->nombre}}-({{$categoria4->codigo}})</option>
                      @endforeach

                      @endforeach

                      @endforeach
                      @endforeach
                    </optgroup>
                    @endforeach
                  </select>
                  <p class="text-left nomargin">
                    <a href="" data-toggle="modal" data-target="#modalproduct" class="modalTr" tr="1">
                      <i class="fas fa-plus"></i> Nuevo Producto
                    </a>
                  </p>
                </div>
              </td>
              <td class="monetario">
                <div class="resp-precio">
                  <input type="number" class="form-control form-control-sm" id="precio1" name="precio[]" placeholder="Precio Unitario" onkeyup="total(1)" required="">
                </div>
              </td>
              <td>
                <input type="text" class="form-control form-control-sm" id="desc1" name="desc[]" placeholder="%" onkeyup="total(1)" onkeypress="return event.charCode >= 46 && event.charCode <=57" min="0" max="100">
              </td>
              <td>
                <select class="form-control form-control-sm selectpicker" name="impuesto1[]" id="impuesto1" title="Impuesto" onchange="impuestoFacturaDeVenta(this.id); totalall();" required="" multiple>
                  @foreach($impuestos as $impuesto)
                  <option value="{{$impuesto->id}}" porc="{{$impuesto->porcentaje}}">{{$impuesto->nombre}} - {{$impuesto->porcentaje}}%</option>
                  @endforeach
                </select>
              </td>
              <td style="padding-top: 1% !important;">
                <div class="resp-descripcion">
                  <textarea class="form-control form-control-sm" id="descripcion1" name="descripcion[]" placeholder="Descripción"></textarea>
                </div>
              </td>
              <td width="5%">
                <input type="number" class="form-control form-control-sm" id="cant1" name="cant[]" step="0.01" placeholder="Cantidad" onchange="total(1);" min="0.01" required="">
              </td>
              <td>
                <div class="resp-total">
                  <input type="text" class="form-control form-control-sm text-right" id="total1" value="0" disabled="">
                </div>
              </td>
              <td><button type="button" class="btn btn-outline-secondary btn-icons" onclick="Eliminar(1);" style="color:#E13130">X</button></td>
            </tr>
          </tbody>
        </table>

        <div class="alert alert-danger" style="display: none;" id="error-items"></div>
      </div>
    </div>

    <button class="btn btn-outline-primary" onclick="insertarNuevaFila();" type="button" style="margin-top: 5%">Agregar línea</button><a><i data-tippy-content="Agrega nuevas lineas haciendo <a href='#'>clíck aquí</a>" class="icono far fa-question-circle"></i></a>
    <div class="row" style="margin-top: 10%; margin-left:0px;">
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
    <!-- Totales

          </tr> -->
    <!-- Totales -->
    <div class="row" style="margin-top: 10%;">
      <div class="col-md-4 offset-md-8">
        <table class="text-right widthtotal">
          <tr>
            <td width="40%">Subtotal</td>
            <td>{{Auth::user()->empresaObj->moneda}} <span id="subtotal">0</span></td>
            <input type="hidden" id="subtotal_categoria_js" value="0">
          </tr>
          <tr>
            <td>Descuento</td>
            <td id="descuento">{{Auth::user()->empresaObj->moneda}} 0</td>
          </tr>
        </table>
        <table class="text-right widthtotal" style="width: 100%" id="totales">
          <tr style="display: none">
            <td width="40%">Subtotal</td>
            <td>{{Auth::user()->empresaObj->moneda}} <span id="subsub">0</span></td>
          </tr>
          <tr>
            <td width="40%">Subtotal</td>
            <td>{{Auth::user()->empresaObj->moneda}} <span id="subtotal2">0</span></td>

          </tr>
        </table>

        <table class="text-right widthtotal" id="totalesreten" style="width: 100%">
          <tbody></tbody>
        </table>


        <hr>
        <table class="text-right widthtotal" style="font-size: 24px !important;">
          <tr>
            <td width="40%">TOTAL A PAGAR</td>
            <td>{{Auth::user()->empresaObj->moneda}} <span id="total">0</span></td>
          </tr>
        </table>
      </div>
    </div>
  </div>
  <hr>
  <div class="row">

    <div class="col-sm-2 float-right" style=" padding-top: 1%;">
      <a href="{{route('facturasp.index')}}" class="btn btn-outline-secondary">Cancelar</a>
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
          <input type="checkbox" class="form-check-input" name="print" value="1">Imprimir
          <i class="input-helper"></i></label>
      </div>
    </div>
    <div class="col-md-2">
      <div class="form-check form-check-flat">
        <label class="form-check-label">
          <input type="checkbox" class="form-check-input" name="send" value="1">Enviar por correo
          <i class="input-helper"></i></label>
      </div>
    </div>
    <div class="col-sm-2 float-left" style="padding-top: 1%;">
      <button type="button" id="submitcheck" class="btn btn-success submit-prevent-button" onclick="checkValores()">Guardar</button>
    </div>
  </div>


</form>


@include('inventario.modal.grupos', ['grupos' => $grupos])


<input type="hidden" id="impuestos" value="{{json_encode($impuestos)}}">
@foreach ($impuestos as $impuesto)
<input type="hidden" id="hddn_imp_{{$impuesto->id}}" value="{{$impuesto->tipo}}">
@endforeach
<input type="hidden" id="allproductos" value="{{json_encode($inventario)}}">
<input type="hidden" id="url" value="{{url('/')}}">
<input type="hidden" id="jsonproduc" value="{{route('inventario.all')}}">
<input type="hidden" id="simbolo" value="{{Auth::user()->empresaObj->moneda}}">
<input type="hidden" id="comprasP" value="1">

<input type="hidden" id="allcategorias" value='@foreach($categorias as $categoria)
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
                          @endforeach'>
<input type="hidden" id="retenciones" value="{{json_encode($retenciones)}}">
<!-- Modal -->
<div class="modal fade" id="contactoModal" role="dialog">
  <div class="modal-dialog mw-100 w-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title" id="modal-titlec"></h4>
      </div>
      <div class="modal-body" id="modal-bodyc">
        <?php session()->put('prov', true); ?>

        {{--@include('contactos.modal.modal')--}}
      </div>
      {{-- <div class="modal-footer">
                   <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                 </div>--}}
    </div>
  </div>
</div>

{{-- Modal Editar Direccion Contacto--}}
<div class="modal fade" id="modaleditDirection" role="dialog" data-backdrop="static" data-keyboard="false">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Editar Direcciones</h4>
      </div>
      <div class="modal-body">

        <div class="container">
          <div id="conte-modalesedit"></div>
        </div>
      </div>
      <div class="modal-footer">
      </div>
    </div>
  </div>
</div>
{{-- /Modal Editar --}}


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

<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/js-cookie@2/src/js.cookie.min.js"></script>


<script>
  function checkValores() {
    var opt = 0;
    $('#table-form tbody tr').each(function() {
      id = $(this).attr('id');
      var precio = $('#precio' + id).val();
      if (precio <= 0 && precio != '') {
        Swal.fire({
          position: 'top-center',
          type: 'error',
          title: 'El costo del item ' + id + ' debe ser mayor a 0',
          showConfirmButton: true,
          timer: 2500
        });
        $('#precio' + id).focus();
        opt++;
      }
    });

    if (opt == 0) {
      $("#form-factura").submit();
      $('.submit-prevent-button').attr('disabled', 'true');
      setTimeout(() => {
        $('.submit-prevent-button').removeAttr('disabled');
      }, 5000);
    }
  }

  $(document).ready(function() {

    $('.buscar').selectpicker();

    let lastRegis = new URLSearchParams(window.location.search);
    let notas = Cookies.get('notas');
    let codigo = Cookies.get('codigo');
    let obs = Cookies.get('obs');
    let cliente = Cookies.get('cliente');

    if (cliente != null) {
      setTimeout(function() {
        $('#cliente').val(cliente).change();
        clearTimeout(this);
        $('#codigo').val(codigo);
        $('#obs').val(obs);
        $('#notas').val(notas);
      }, 1000);
      Cookie.remove('cliente', {
        path: ''
      });
      Cookie.remove('codigo', {
        path: ''
      });
      Cookie.remove('obs', {
        path: ''
      });
      Cookie.remove('notas', {
        path: ''
      });
    }

    if (lastRegis.has('pro')) {

      let idPro = lastRegis.get('pro');
      let impuesto = $('#impuestosId').val();

      setTimeout(function() {
        $('#item1').val(idPro).change();
        $('#impuesto1').val(impuesto).change();
        clearTimeout(this);
      }, 1000);

    }

    if (lastRegis.has('cnt')) {

      let idCnt = lastRegis.get('cnt');

      setTimeout(function() {
        $('#cliente').val(idCnt).change();
        clearTimeout(this);
      }, 1000);

    }

    var minute = new Date(new Date().getTime() + 3 * 60 * 1000);

    $("#cliente").change(function() {
      Cookies.set('cliente', $('#cliente').val(), {
        expires: minute
      });
    });

    $("#codigo").change(function() {
      Cookies.set('codigo', $('#codigo').val(), {
        expires: minute
      });
    });

    $("#obs").change(function() {
      Cookies.set('obs', $('#obs').val(), {
        expires: minute
      });
    });

    $("#notas").change(function() {
      Cookies.set('notas', $('#notas').val(), {
        expires: minute
      });
    });
  });
</script>

<script src="/lowerScripts/facturasp/facturasp.js"></script>

<script>
  function insertarNuevaFila() {
    $('#error-items').hide();
    var nro = $('#table-form tbody tr').length + 1;

    if ($('#' + nro).length > 0) {
      for (i = 1; i <= nro; i++) {
        if ($('#' + i).length == 0) {
          nro = i;
          break;
        }
      }
    }
    factura = true;
    ref = true;
    prove = false;
    if ($('#cotizacion_si').length > 0) {
      factura = false;
      prove = true;
    }
    if ($('#orden_si').length > 0) {
      ref = false;
    }
    datos = `<tr  id="${nro}">`;
    if (factura) {
      datos += `<td class="no-padding"><a class="btn btn-outline-secondary btn-icons" title="Actualizar" onclick="inventario('${nro}');" id="actualizar${nro}"><i class="fas fa-sync"></i></a></td>`;

    }
    datos += `<td class="no-padding"><div class="resp-item"><select required="" class="form-control form-control-sm selectpicker items_inv no-padding"  title="Seleccione" data-live-search="true" data-size="5" name="item[]" id="item${nro}" onchange="rellenar(` + nro + `, this.value);">
    </select ></div>`;
    if (factura || prove) {
      datos += `<p style="text-align: left;     margin: 0;">
        <a href="" data-toggle="modal" data-target="#modalproduct" class="modalTr" tr="${nro}"><i class="fas fa-plus"></i> Nuevo Producto</a></p>`;

    }

    if (ref) {
      datos += `<input type="hidden" name="camposextra[]" value="${nro}"></td>
        <td >
        <input type="text" class="form-control form-control-sm" id="ref${nro}" name="ref[]" placeholder="Referencia" required="">
        </td>`;
    }
    datos += `
    <td class="monetario">
    <input type="number" class="form-control form-control-sm" id="precio${nro}" maxlength="24" min="0" name="precio[]" placeholder="Precio Unitario" onkeyup="total(${nro})" required="">
    </td>
    <td>
    <input type="text" class="form-control form-control-sm" id="desc${nro}" name="desc[]" maxlength="5" placeholder="%" onkeypress="return event.charCode >= 46 && event.charCode <=57" onkeyup="total(${nro})" max="100" min="0">
    </td>
    <td class="td-impuesto">
    <select class="form-control form-control-sm selectpicker" name="impuesto${nro}[]" id="impuesto${nro}" required title="Impuesto" onchange="impuestoFacturaDeVenta('impuesto${nro}'); total(${nro});checkImp(${nro});" multiple data-live-search="true" data-size="10">

    </select>
    </td>
    <td  style="padding-top: 1% !important;">
    <textarea  class="form-control form-control-sm" id="descripcion${nro}" name="descripcion[]" placeholder="Descripción"></textarea>
    </td>
    <td>
    <input type="number" class="form-control form-control-sm" id="cant${nro}" name="cant[]" placeholder="Cantidad"   maxlength="24" step="0.01"  min="0.01" onchange="total(${nro});"  required="">
    <p class="text-danger nomargin" id="pcant${nro}"></p></td>
    <td>
    <input type="text" class="form-control form-control-sm text-right" id="total${nro}" value="0" disabled=""></td>
    <td><button type="button" onclick="Eliminar(${nro});" class="btn btn-outline-danger btn-icons" style="color:#E13130">X</button></td>
    ` +
      `</tr>`;
    $('#table-form tbody').append(datos);
    var impuestos = JSON.parse($('#impuestos').val());
    $.each(impuestos, function(key, value) {
      $('#impuesto' + nro).append($('<option>', {
        value: value.id,
        text: value.nombre + "-" + value.porcentaje + "%"
      }));
    });

    var obj = JSON.parse($('#allproductos').val());
    var optios = '';
    if ($('#orden_si').length > 0) {
      optios += "<optgroup  label='Ítems inventariables'>";
    }

    $.each(obj, function(key, value) {
      optios += "<option  value='" + value.id + "'>" + value.producto + ' - ' + '(' + value.ref + ')' + " </option>";
    });

    if ($('#orden_si').length > 0) {
      optios += " </optgroup>";
      optios += $('#allcategorias').val();

    }

    $('#item' + nro).append(optios);

    $('.precio').mask('0000000000.00', {
      reverse: true
    });
    $('.nro').mask('000');
    $('#item' + nro).selectpicker();
    $('#impuesto' + nro).selectpicker();

    return nro;
  }
</script>

@endsection