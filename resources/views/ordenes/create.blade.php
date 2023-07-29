@extends('layouts.app')
@section('content')
  <form method="POST" action="{{ route('ordenes.store') }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-factura" >
      {{ csrf_field() }}
    <input type="hidden" value="1" name="cotizacion" id="cotizacion_si">
    <input type="hidden" value="1" name="orden_si" id="orden_si">
    <div class="row text-right">
      <div class="col-md-5">
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Productor <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <div class="input-group">
              <select class="form-control selectpicker" name="proveedor" id="cliente" required="" title="Seleccione" data-live-search="true" data-size="5" onchange="contacto(this.value);">
                @foreach($clientes as $client)
                  <option {{old('productor')==$client->id?'selected':''}} value="{{$client->id}}">{{$client->nombre}} - {{$client->nit}}</option>
                @endforeach
              </select>
            </div>
          </div>
          <span class="help-block error">
            <strong>{{ $errors->first('productor') }}</strong>
          </span>
        </div>    
        <div class="form-group row">
          <label class="col-sm-4  col-form-label">Observaciones <br> <small>(No visible en el documento impreso)</small></label>
          <div class="col-sm-8">
            <textarea  class="form-control form-control-sm min_max_100" name="observaciones">{{old('observaciones')}}</textarea>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-4  col-form-label">Notas <br> <small>(Visible en el documento impreso)</small></label>
          <div class="col-sm-8">
            <textarea  class="form-control form-control-sm min_max_100" name="notas">{{old('notas')}}</textarea>
          </div>
        </div>

      </div>
      <div class="col-md-6 offset-md-1">
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Fecha <span class="text-danger">*</span><a><i data-tippy-content="Fecha de registro de la orden de compra" class="icono far fa-question-circle"></i></a></label>
          <div class="col-sm-8">
            <input type="text" class="form-control datepicker"  id="fecha" value="{{date('d-m-Y')}}" name="fecha" disabled=""  >
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Fecha de Entrega <span class="text-danger">*</span><a><i data-tippy-content="Fecha conciliada con el proveedor para hacer la entrega" class="icono far fa-question-circle"></i></a></label>
          <div class="col-sm-8">
            <input type="text" class="form-control" id="vencimiento" value="{{date('d-m-Y')}}" name="vencimiento" disabled="">
          </div>
        </div>

        <div class="form-group row">
          <label class="col-sm-4  col-form-label">Terminos y Condiciones <br></label>
          <div class="col-sm-8">
            <textarea  class="form-control form-control-sm min_max_100" name="term_cond">{{old('term_cond')}}</textarea>
          </div>
        </div>

        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Bodega <span class="text-danger">*</span><a><i data-tippy-content="Bodega desde la cual se obtendran los productos" class="icono far fa-question-circle"></i></a></label>
          <div class="col-sm-8">
            <select name="bodega" id="bodega" class="form-control"  required="">
              @foreach($bodegas as $bodega)  
                <option value="{{$bodega->id}}" {{old('bodega')==$bodega->id?'selected':''}}>{{$bodega->bodega}}</option>
              @endforeach
            </select>
          </div>
        </div>
        
        <div class="form-group row">
          <label class="col-sm-4 control-label">Comprador</label>
          <div class="col-sm-8">
              <select name="comprador" id="vendedor" class="form-control selectpicker " title="Seleccione" data-live-search="true" data-size="5" required>
                  @foreach($vendedores as $vendedor)
                      <option value="{{$vendedor->id}}">{{$vendedor->nombre}}</option>
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
            <th width="28%">Categoría/Ítem/Referencia</th>
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
              <td  class="no-padding"> 
              <div class="resp-item">
                <select class="form-control form-control-sm selectpicker no-padding"  title="Seleccione" data-live-search="true" data-size="5" name="item[]" id="item1" onchange="rellenar(1, this.value);" required="">
                <optgroup label="Ítems">
                @foreach($inventario as $item)
                <option value="{{$item->id}}" >{{$item->producto}} - ({{$item->ref}})</option>
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
                <input type="number" class="form-control form-control-sm" id="precio1" name="precio[]" placeholder="Precio Unitario" onkeyup="total(1)" required="">
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
      </div>
    </div>

    <button class="btn btn-outline-primary" onclick="createRow();" type="button" style="margin-top: 5%">Agregar línea</button>
      <div class="row" style="margin-top: 10%;">
        <div class="col-md-4 offset-md-8">
          <table style="text-align: right;  width: 100%;" id="totales">
            <tr>
              <td width="40%">Subtotal</td>
              <td id="subtotal">$0</td>
            </tr>
            <tr>
              <td>Descuento</td>
              <td id="descuento">$0</td>
            </tr>
            <tr>
              <td>Subtotal</td>
              <td id="subsub">$0</td>
            </tr>
          </table>
          <hr>
          <table style="text-align: right; font-size: 24px !important; width: 100%;">
            <tr>
              <td width="40%">TOTAL</td>
              <td id="total">$0</td>
            </tr>
          </table>
        </div>
      </div>
      </div>
      <hr>
      <div class="row" >
        
        <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
          
          <a href="{{route('ordenes.index')}}" class="btn btn-outline-secondary">Cancelar</a>
          <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
        </div>
      </div>


    </form>
  <input type="hidden" id="impuestos" value="{{json_encode($impuestos)}}">
  <input type="hidden" id="allproductos" value="{{json_encode($inventario)}}">
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
                            <!-- MODAL creación producto -->
<div class="modal fade" id="modalproduct" role="dialog">
    <div class="modal-dialog modal-lg">
        <input type="hidden" id="trFila" value="0">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"></h4>
            </div>
            <div class="modal-body">
                @include('inventario.modal.create2')
            </div>

        </div>
    </div>
</div>
  <!--  -->

@endsection