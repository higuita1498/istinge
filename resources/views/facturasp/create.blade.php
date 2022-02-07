@extends('layouts.app')
@section('content')
    @if(!empty($_GET['pro']))
        @php($new = \App\Model\Inventario\Inventario::where('id', htmlspecialchars($_GET['pro']))->get())
        <input type="hidden" id="impuestosId" value="{{$new[0]['id_impuesto']}}">
    @endif
    
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

  <form method="POST" action="{{ route('facturasp.store') }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-factura" >
      {{ csrf_field() }}
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
            <p class="text-left nomargin d-none">
              <a href="{{route('contactos.create')}}" data-toggle="modal" data-target="#myModal" id='contacto'><i class="fas fa-plus"></i> Nuevo Contacto</a></p>
          </div>
          <span class="help-block error">
            <strong>{{ $errors->first('productor') }}</strong>
          </span>
        </div>    
        <div class="form-group row">
          <label class="col-sm-4  col-form-label">Observaciones <br> <small>(No visible en el documento impreso)</small></label>
          <div class="col-sm-8">
            <textarea  class="form-control form-control-sm min_max_100" name="observaciones" id="obs">{{old('observaciones')}}</textarea>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-4  col-form-label">Notas <br> <small>(Visible en el documento impreso)</small></label>
          <div class="col-sm-8">
            <textarea  class="form-control form-control-sm min_max_100" name="notas" id="notas">{{old('notas')}}</textarea>
          </div>
        </div>

      </div>
      <div class="col-md-6 offset-md-1">
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Número de factura</label>
          <div class="col-sm-8">
            <input type="text" class="form-control"  id="codigo" name="codigo" required maxlength="35" value="{{$codigoFactura}}">
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Fecha <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <input type="text" class="form-control"  id="fecha" value="{{date('d-m-Y')}}" name="fecha" disabled=""  >
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
                  <select name="comprador" id="vendedor" class="form-control selectpicker " title="Seleccione"  data-size="5" required>
                      @foreach($vendedores as $vendedor)
                          <option value="{{$vendedor->id}}">{{$vendedor->nombre}}</option>
                      @endforeach
                  </select>
              </div>
          </div>
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Bodega <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <select name="bodega" id="bodega" class="form-control"  required="">
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
              <td  class="no-padding">
                  <div class="resp-item">
               @if($producto) <input type="hidden" id="producto_inv" value="true">   @endif
                <select class="form-control form-control-sm buscar no-padding"  title="Seleccione" data-live-search="true" data-size="5" name="item[]" id="item1" onchange="rellenar(1, this.value);" required="">
                <optgroup label="Ítems">
                @foreach($inventario as $item)
                <option value="{{$item->id}}" @if($producto) {{$producto->id==$item->id?'selected':''}}  @endif >{{$item->producto}} - ({{$item->ref}})</option>
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
              <td width="5%">
                <input type="number" class="form-control form-control-sm" id="cant1" name="cant[]" placeholder="Cantidad" onchange="total(1);" min="1" required="">
              </td>
              <td>
                  <div class="resp-total">
                <input type="text" class="form-control form-control-sm text-right" id="total1" value="0" disabled="">
                </div>
              </td>
              <td><button type="button" class="btn btn-outline-secondary btn-icons" onclick="Eliminar(1);">X</button></td>
            </tr>
          </tbody>
        </table>

        <div class="alert alert-danger" style="display: none;" id="error-items"></div>
      </div>
    </div>

    <button class="btn btn-outline-primary" onclick="createRow();" type="button" style="margin-top: 5%">Agregar línea</button><a><i data-tippy-content="Agrega nuevas lineas haciendo <a href='#'>clíck aquí</a>" class="icono far fa-question-circle"></i></a>
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
    <!-- Totales 
            
          </tr> -->
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
                          <td >{{Auth::user()->empresa()->moneda}} <span id="subsub">0</span></td>
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
                          <td width="40%">TOTAL A PAGAR</td>
                          <td>{{Auth::user()->empresa()->moneda}} <span id="total">0</span></td>
                      </tr>
                  </table>
              </div>
          </div>
      </div>
      <hr>
      <div class="row" >
        
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
            <button type="submit" class="btn btn-success">Guardar</button>
        </div>
      </div>


    </form>
  <input type="hidden" id="impuestos" value="{{json_encode($impuestos)}}">
  @foreach ($impuestos as $impuesto)
      <input type="hidden" id="hddn_imp_{{$impuesto->id}}" value="{{$impuesto->tipo}}">
    @endforeach
  <input type="hidden" id="allproductos" value="{{json_encode($inventario)}}">
  <input type="hidden" id="url" value="{{url('/')}}">
  <input type="hidden" id="jsonproduc" value="{{route('inventario.all')}}">
  <input type="hidden" id="simbolo" value="{{Auth::user()->empresa()->moneda}}">

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
                    {{--@include('contactos.modal.modal')--}}
                </div>
                {{-- <div class="modal-footer">
                   <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                 </div>--}}
            </div>
        </div>
    </div>
  
     {{-- Modal Editar Direccion Contacto--}}
    <div class="modal fade" id="modaleditDirection" role="dialog"  data-backdrop="static" data-keyboard="false">
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

  <script
      src="https://code.jquery.com/jquery-3.3.1.min.js"
      integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
      crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/js-cookie@2/src/js.cookie.min.js"></script>


    <script>


      $(document).ready(function(){
          
          $('.buscar').select2();

          let lastRegis = new URLSearchParams(window.location.search);
          let notas     = Cookies.get('notas');
          let codigo    = Cookies.get('codigo');
          let obs       = Cookies.get('obs');
          let cliente   = Cookies.get('cliente');

          if(cliente != null){
              setTimeout(function () {
                  $('#cliente').val(cliente).change();
                  clearTimeout(this);
                  $('#codigo').val(codigo);
                  $('#obs').val(obs);
                  $('#notas').val(notas);
              }, 1000);
              Cookie.remove('cliente', { path: '' });
              Cookie.remove('codigo', { path: '' });
              Cookie.remove('obs', { path: '' });
              Cookie.remove('notas', { path: '' });
          }

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
              }, 1000);

          }

          var minute = new Date(new Date().getTime() + 3 * 60 * 1000);

          $("#cliente").change(function (){
              Cookies.set('cliente', $('#cliente').val(),  {
                  expires: minute
              });
          });

          $("#codigo").change(function (){
              Cookies.set('codigo', $('#codigo').val(),  {
                  expires: minute
              });
          });

          $("#obs").change(function (){
              Cookies.set('obs', $('#obs').val(),  {
                  expires: minute
              });
          });

          $("#notas").change(function (){
              Cookies.set('notas', $('#notas').val(),  {
                  expires: minute
              });
          });



      });

  </script>

@endsection

