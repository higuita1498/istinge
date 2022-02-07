@extends('layouts.app')
@section('content')
    <style type="text/css"> .card{ background: #f9f1ed !important;}</style>

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
  <!--Formulario Facturas-->
  <div class="paper">
      
  <form method="POST" action="{{ route('cotizaciones.store') }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-factura" >
    {{ csrf_field() }}
    <input type="hidden" name="tipocliente" id="tipocliente" value="1">
    {{--<input type="hidden" value="1" name="cotizacion" id="cotizacion_si">--}}
    <div class="row text-right">
      <div class="col-md-6">
        <div class="form-group row" id="div-contacto">
          <label class="col-sm-4 col-form-label">Cliente <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <div class="input-group">
              <select class="form-control selectpicker" name="cliente" id="cliente" required="" title="Seleccione" data-live-search="true" data-size="5" onchange="contacto(this.value);">
                @foreach($clientes as $client)
                  <option {{old('cliente')==$client->id?'selected':''}} value="{{$client->id}}">{{$client->nombre}} - {{$client->nit}}</option>
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
        <div id="contacto-rapido" style="display: none;" >
          <div class="form-group row">
            <label class="col-sm-4 col-form-label">Cliente <span class="text-danger">*</span></label>
            <div class="col-sm-8">
              <input type="text" class="form-control" name="clienterapido" value="" id="clienterapido">
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
              <input type="text" class="form-control" name="telefono" id="telefono" value="">
            </div>
          </div>

          <div class="form-group row">
            <label class="col-sm-4 col-form-label">Correo Electrónico <small><br> Para enviar documentos por correo</small></label>
            <div class="col-sm-8">
              <input type="email" class="form-control" id="email" name="email" data-error="Dirección de correo electrónico invalida" maxlength="100"  value="">
            </div>
          </div>

        </div>
        <div class="form-group row">
          <label class="col-sm-4  col-form-label">Observaciones <br> <small>No visible en la cotización</small></label>
          <div class="col-sm-8">
            <textarea  class="form-control form-control-sm min_max_100" name="observaciones"></textarea>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Notas de la cotización<br> <small>Visible en la cotización</small></label>
          <div class="col-sm-8">
            <textarea  class="form-control form-control-sm min_max_100" name="notas"></textarea>
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
          <label class="col-sm-4 col-form-label">Vencimiento <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <input type="text" class="form-control  "id="vencimiento" value="{{date('d-m-Y')}}" name="vencimiento" disabled="">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Vendedor <a><i data-tippy-content="Vendedor asociado a la cotización, puedes agregar nuevos vendedores haciendo <a href='#'>clíck aquí</a>" class="icono far fa-question-circle"></i></a></label>
          <div class="col-sm-8">
            <select name="vendedor" id="vendedor" class="form-control selectpicker " title="Seleccione" data-live-search="true" data-size="5">
              @foreach($vendedores as $vendedor)
                <option value="{{$vendedor->id}}">{{$vendedor->nombre}}</option>
              @endforeach
            </select>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Lista de Precios<a><i data-tippy-content="Lista de precios asociada a la cotización, puedes agregar nuevas listas de precio haciendo <a href='#'>clíck aquí</a>" class="icono far fa-question-circle"></i></a></label>
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
      <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>

    </div>

    <hr>
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
                <a class="btn btn-outline-secondary btn-icons" title="Actualizar" onclick="inventario('1');" id="actualizar1"><i class="fas fa-sync"></i></a>
              </td>
              <td  class="no-padding">
                <div class="resp-item">
                  <select class="form-control form-control-sm selectpicker no-padding"  title="Seleccione" data-live-search="true" data-size="5" name="item[]" id="item1" onchange="rellenar(1, this.value);" required="">
                    @foreach($inventario as $item)
                      <option value="{{$item->id}}">{{$item->producto}} - ({{$item->ref}})</option>
                    @endforeach
                  </select>
                  <p class="text-left nomargin">
                    <a href="" data-toggle="modal" data-target="#modalproduct" class="modalTr" tr="1">
                      <i class="fas fa-plus"></i> Nuevo Producto
                    </a>
                  </p>
                  <input type="hidden" name="camposextra[]" value="1">
                </div>
              </td>
              <td>
                <div class="resp-refer">
                  <input type="text" class="form-control form-control-sm" id="ref1" name="ref[]" placeholder="Referencia" required="">
                </div>
              </td>
              <td class="monetario">
                <div class="resp-precio">
                  <input type="number" class="form-control form-control-sm" id="precio1" name="precio[]" placeholder="Precio Unitario" onkeyup="total(1)" required="" maxlength="12" min="0">
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
      <!-- <button class="btn btn-outline-primary" onclick="createRowNoInventario();" type="button" style="margin-top: 5%">Agregar línea</button> -->



      <!-- Totales -->
      <div class="row" style="margin-top: 10%;">
        <div class="col-md-4 offset-md-8">
          <table class="text-right widthtotal" id="totales">
            <tr>
              <td width="40%">Subtotal</td>
              <td>{{Auth::user()->empresa()->moneda}} <span id="subtotal">{{App\Funcion::Parsear(0)}}</span></td>
            </tr>
            <tr>
              <td>Descuento</td><td id="descuento">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear(0)}}</td>
            </tr>
            <tr>
              <td>Subtotal</td>
              <td>{{Auth::user()->empresa()->moneda}} <span id="subsub">{{App\Funcion::Parsear(0)}}</span></td>
            </tr>
          </table>
          <hr>
          <table class="text-right widthtotal" style="font-size: 24px !important;">
            <tr>
              <td width="40%">TOTAL</td>

              <td>{{Auth::user()->empresa()->moneda}} <span id="total">{{App\Funcion::Parsear(0)}}</span></td>
            </tr>
          </table>
        </div>
      </div>
    </div>
    <hr>
    <!--Botones Finales -->
    <div class="row" >
      <div class="col-md-12 text-right" style="padding-top: 1%;">
        <a href="{{route('facturas.index')}}" class="btn btn-outline-secondary">Cancelar</a>
        <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
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
  </div>
  <input type="hidden" id="impuestos" value="{{json_encode($impuestos)}}">
  <input type="hidden" id="allproductos" value="{{json_encode($inventario)}}">
  <input type="hidden" id="url" value="{{url('/')}}">
  <input type="hidden" id="jsonproduc" value="{{route('inventario.all')}}">
  <input type="hidden" id="simbolo" value="{{Auth::user()->empresa()->moneda}}">
  <input type="hidden" id="camposestras" value="{{json_encode($extras)}}">

@endsection