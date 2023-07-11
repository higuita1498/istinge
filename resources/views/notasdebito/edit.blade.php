@extends('layouts.app')
@section('content')
  <form method="POST" action="{{ route('notasdebito.update', $nota->id) }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-factura" > 
    {{ csrf_field() }}
    {{--<input name="_method" type="hidden" value="PATCH">
    <input type="hidden" value="1" name="cotizacion" id="cotizacion_si">
    <input type="hidden" value="1" name="fact_prov" id="fact_prov">
    <input type="hidden" value="1" name="fact_prov" id="orden_si">

    <input type="hidden" value="1" name="fact_debito" id="fact_debito">--}}
    <input type="hidden" value="1" name="fact_prov" id="fact_prov">
    <input type="hidden" value="1" name="cotizacion" id="cotizacion_si">
    <input name="_method" type="hidden" value="PATCH">
    <div class="row" style=" text-align: right;">
      <div class="col-md-6">

        {{--<div class="form-group row">
          <label class="col-sm-4 col-form-label">Numeración</label>
          <div class="col-sm-8">
            <input type="text" name="codigo" class="form-control" value="{{$nota->codigo}}">
          </div>
        </div>--}}
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Proveedor <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <select class="form-control form-control-sm selectpicker" name="proveedor" id="cliente" title="Seleccione" data-live-search="true" data-size="5" required="" onchange="contacto(this.value); onchangecliente(this.value)">
              @foreach($proveedores as $proveedor)
                <option {{$nota->proveedor==$proveedor->id?'selected':''}} value="{{$proveedor->id}}">{{$proveedor->nombre}} - {{$proveedor->nit}}</option>
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
            <textarea  class="form-control form-control-sm min_max_100" name="observaciones">{{$nota->observaciones}}</textarea>
          </div>
        </div>         
      </div>
      <div class="col-md-5 offset-md-1">
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Fecha <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <input type="text" class="form-control datepicker"  id="fecha" value="{{date('d-m-Y', strtotime($nota->fecha))}}" name="fecha" disabled=""  >
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Bodega <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <select name="bodega" id="bodega" class="form-control" required="">
              @foreach($bodegas as $bodega)  
                <option value="{{$bodega->id}}" {{$nota->bodega==$bodega->id?'selected':''}}>{{$bodega->bodega}}</option>
              @endforeach
            </select>
          </div>
        </div>
      </div>
    </div>

    <!-- Desgloce -->
    <div class="row">
      <div class="col-md-12 fact-table">
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
            @php $cont=0; @endphp
            @foreach($items as $item) 
            @php $cont+=1; @endphp
            <tr id="{{$cont}}">                          
              <td  class="no-padding">
                  <input type="hidden" name="id_item{{$cont}}" value="{{$item->id}}">                           
                <select class="form-control form-control-sm selectpicker no-padding"  title="Seleccione" data-live-search="true" data-size="5" name="item[]" id="item{{$cont}}" onchange="rellenar(1, this.value);" required="">
                <optgroup label="Ítems inventariables">
                @foreach($inventario as $itemm)
                <option value="{{$itemm->id}}" {{$item->producto==$itemm->id && $item->tipo_item==1?'selected':''}} >{{$itemm->producto}} - ({{$itemm->ref}})</option>
                 @endforeach
                </optgroup>
                @foreach($categorias as $categoria)
                <optgroup label="{{$categoria->nombre}}">
                    @foreach($categoria->hijos(true) as $categoria1)
                      <option  {{$item->producto==$categoria1->id && $item->tipo_item==2?'selected':''}}  value="cat_{{$categoria1->id}}" {{$categoria1->estatus==0?'disabled':''}}>{{$categoria1->nombre}}</option>
                      @foreach($categoria1->hijos(true) as $categoria2)
                          <option class="hijo" {{$item->producto==$categoria2->id && $item->tipo_item==2?'selected':''}}   value="cat_{{$categoria2->id}}" {{$categoria2->estatus==0?'disabled':''}}>{{$categoria2->nombre}}</option>
                        @foreach($categoria2->hijos(true) as $categoria3)
                          <option class="nieto" {{$item->producto==$categoria3->id && $item->tipo_item==2?'selected':''}} value="cat_{{$categoria3->id}}" {{$categoria3->estatus==0?'disabled':''}}>{{$categoria3->nombre}}</option>
                          @foreach($categoria3->hijos(true) as $categoria4)
                            <option class="bisnieto" {{$item->producto==$categoria4->id && $item->tipo_item==2?'selected':''}} value="cat_{{$categoria4->id}}" {{$categoria3->estatus==0?'disabled':''}}>{{$categoria4->nombre}}</option>
                          @endforeach
                        @endforeach
                      @endforeach
                    @endforeach
                  </optgroup>
                  @endforeach
                </select>
              </td>
              <td class="monetario">
                <input type="number" class="form-control form-control-sm" id="precio{{$cont}}" name="precio[]" placeholder="Precio Unitario" onkeyup="total({{$cont}})" required=""  value="{{$item->precio}}">
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
              <td width="5%">
                <input type="number" class="form-control form-control-sm" id="cant{{$cont}}" name="cant[]" placeholder="Cantidad" onchange="total(1);" min="1" required="" value="{{$item->cant}}">
              </td>
              <td>
                <input type="text" class="form-control form-control-sm text-right" id="total{{$cont}}" value="{{App\Funcion::Parsear($item->total())}}" disabled="">
              </td>
              <td><button type="button" class="btn btn-outline-secondary btn-icons" onclick="Eliminar({{$cont}});">X</button></td>
            </tr>
             @endforeach
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
          @php $cont=0; @endphp
          @foreach($retencionesNotas as $retencion)
            @php $cont+=1; @endphp
            <tr id="reten{{$cont}}">
              <td  class="no-padding">
                <select class="form-control form-control-sm selectpicker no-padding"  title="Seleccione" data-live-search="true" data-size="5" name="retencion[]" id="retencion{{$cont}}" required="" onchange="retencion_calculate({{$cont}}, this.value);" >
                  @foreach($retenciones as $reten)
                    <option value="{{$reten->id}}" {{$retencion->id_retencion==$reten->id?'selected':''}}>{{$reten->nombre}} - {{$reten->porcentaje}}%</option>
                  @endforeach
                </select>
              </td>
              <td class="monetario">
                <input type="hidden" value="0" id="lock_reten{{$cont}}">
                <input type="number" value="{{$retencion->valor}}" required="" style="display: inline-block; width: 80%;"
                       class="form-control form-control-sm" maxlength="24" onkeyup="total_categorias()"
                       id="precio_reten{{$cont}}" name="precio_reten[]" placeholder="Valor retenido"
                       onkeyup="total_linea({{$cont}})" required="" min="0" disabled>
              </td>
              <td>
                <button type="button" class="btn btn-outline-secondary btn-icons"
                        onclick="Eliminar('reten{{$cont}}'); total_categorias();">X</button></td>
            </tr>
          @endforeach
          </tbody>
        </table>
        <button class="btn btn-outline-primary" onclick="CrearFilaRetencion();" type="button" style="margin-top: 2%;">Agregar Retención</button><a><i data-tippy-content="Agrega nuevas retenciones haciendo <a href='#'>clíck aquí</a>" class="icono far fa-question-circle"></i></a>
      </div>
    </div>

    <!-- Totales -->
    <div class="row" style="margin-top: 10%;">
      <div class="col-md-4 offset-md-8">
        <table class="text-right widthtotal">
          <tr>
            <td width="40%">Subtotal</td>
            <td>{{Auth::user()->empresa()->moneda}}<span id="subtotal">{{App\Funcion::Parsear($nota->total()->subtotal)}}</span></td>
            <input type="hidden" id="subtotal_categoria_js" value="{{App\Funcion::Parsear($nota->total()->subtotal)}}">
          </tr>
          <tr>
            <td>Descuento</td><td id="descuento">{{App\Funcion::Parsear($nota->total()->descuento)}}</td>
          </tr>
        </table>

        <table class="text-right widthtotal" id="totales">
          <tr style="display: none;">
            <td width="40%">Subtotal</td>
            <td>{{Auth::user()->empresa()->moneda}} <span id="subsub">{{App\Funcion::Parsear($nota->total()->subsub)}}</span></td>
          </tr>
          <tr >
            <td width="40%">Subtotal</td>
            <td>{{Auth::user()->empresa()->moneda}} <span id="subtotal2">{{App\Funcion::Parsear($nota->total()->subtotal -$nota->total()->descuento)}}</span></td>
          </tr>
            @php $cont=0; @endphp
            @if($nota->total()->imp)
              @foreach($nota->total()->imp as $imp)
                @if(isset($imp->total))
                  @php $cont=$cont+1; @endphp
                  <tr id="imp{{$cont}}">
                    <td class="text-right">{{$imp->nombre}} ({{$imp->porcentaje}}%)</td>
                    <td class="text-right" id="totalimp{{$cont}}">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($imp->total)}}</td>
                  </tr>
                @endif
              @endforeach
            @endif
        </table>

        <table class="text-right widthtotal"  id="totalesreten" style="width: 100%">
          <tbody>
          @php $cont=0; @endphp
          @if($nota->total()->reten)
            @foreach($nota->total()->reten as $key => $reten)

              @if(isset($reten->total))
                @php $cont+=1; @endphp
                <input type="hidden" id="retentotalmonto{{$reten->id}}" value="{{$reten->total}}">
                <tr id="retentotal{{$key}}">
                  <td width="40%" >{{$reten->nombre}} ( {{$reten->porcentaje}}%)</td>
                  <td id="retentotalvalue{{$reten->id}}">
                    -{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($reten->total)}}</td>
                </tr>

              @endif
            @endforeach
          @endif
          </tbody>
        </table>
        <hr>
        <table class="text-right widthtotal" style="font-size: 24px !important;">
          <tr>
            <td width="40%">TOTAL</td>
            <td>{{Auth::user()->empresa()->moneda}} <span id="total">{{App\Funcion::Parsear($nota->total()->total)}}</span></td>
            <input type="hidden" id="total_value" value="{{$nota->total()->total}}">
          </tr>
        </table>
      </div>
    </div>
    <div class="alert alert-danger  alert-view-show" style="display: none;" id="error-cliente"></div>
      {{--<div class="row">
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
  <div class="tab-pane fade show active fact-table" id="home" role="tabpanel" aria-labelledby="home-tab">
    <table class="table table-striped pagos" width="100%" id="devoluciones-dinero">
      <thead>
          <th width="24%" class="text-center">Fecha</th>
          <th width="25%" class="text-center">Cuenta</th>
          <th width="20%" class="text-center">Monto</th>
          <th width="25%" class="text-center">Observaciones</th>
          <th width="5%" class="text-center"></th>
      </thead>
      <tbody>

      @php $cont=0; @endphp
      @php $tipos_cuentas=\App\Banco::tipos();@endphp
      @foreach($DevolucionesDebito as $devolucion) 
        @php $cont+=1; @endphp
        <tr id="devol_{{$cont}}">
          <td class="form-group ">
            <input type="hidden" name="id_devolucion{{$cont}}" value="{{$devolucion->id}}">      
            <input type="text" class="form-control" value="{{date('d-m-Y', strtotime($devolucion->fecha))}}" name="fecha_dev[]" id="fecha_dev{{$cont}}" disabled=""  style="border: 1px solid #a6b6bd52  !important;">
          </td>
          <td>
            <select class="form-control form-control-sm selectpicker" name="cuentaa_dev[]" id="cuenta{{$cont}}" title="Seleccione" data-live-search="true" data-size="5">
              @foreach($tipos_cuentas as $tipo_cuenta)
                <optgroup label="{{$tipo_cuenta['nombre']}}">

                  @foreach($bancos as $cuenta)
                    @if($cuenta->tipo_cta==$tipo_cuenta['nro'])
                      <option value="{{$cuenta->id}}" {{$devolucion->cuenta==$cuenta->id?'selected':''}}>{{$cuenta->nombre}}</option>
                    @endif
                  @endforeach
                </optgroup>
              @endforeach
            </select>
          </td>
          <td class="monetario"><input type="number" class="form-control form-control-sm" id="monto{{$cont}}" name="montoa_dev[]" placeholder="Monto" onchange="function_totales_facturas();" value="{{$devolucion->monto}}"></td>
          <td  style="padding-top: 1% !important;">                           
            <textarea  class="form-control form-control-sm" id="descripcion{{$cont}}" name="descripciona_dev[]" placeholder="Descripción" >{{$devolucion->observaciones}}</textarea></td>
          <td>
            <button type="button" class="btn btn-link btn-icons" onclick="Eliminar('devol_{{$cont}}');">X</button>
          </td>
        </tr>

      @endforeach        
      </tbody>
    </table>
    <button class="btn btn-link"  type="button" onclick="agregardevolucion();"><i class="fas fa-plus"></i>Agregar devolución de dinero</button>
  </div>
  <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">    
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
        @php $cont=0; @endphp
        @foreach($facturas_reg as $factura)
          @php $cont=$cont+1; @endphp
          <tr id="tr_fact-client_{{$cont}}">

            <td class="no-padding">
              <input type="hidden" name="id_facturacion{{$cont}}" value="{{$factura->id}}">
              <select class="form-control form-control-sm selectpicker no-padding" title="Seleccione" data-size="5" name="factura[]" id="cod_factura{{$cont}}" onchange="rellenar_fact({{$cont}}, this.value);" required="">
                @foreach($facturas as $fact)
                  <option value="{{$fact->id}}" {{$fact->id==$factura->factura?'selected':''}}>{{$fact->codigo?$fact->codigo:('Factura '.date('d-m-Y', strtotime($fact->fecha_factura)))}}</option>

                @endforeach
              </select>
            </td>
            <td id="totalfact{{$cont}}">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->factura()->total()->total)}}</td>
            <td id="pagado{{$cont}}">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->factura()->pagado())}}</td>
            <td id="porpagar{{$cont}}">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->factura()->porpagar()+$factura->pago)}}</td>

            <td class="monetario">
              <input type="number" class="form-control form-control-sm" id="monto_fact{{$cont}}" name="monto_fact[]" placeholder="Monto" onkeyup="total(1)" required="" maxlength="24" min="0" onchange="function_totales_facturas();" max="{{($factura->factura()->porpagar()+$factura->pago)}}" value="{{$factura->pago}}">
            </td>
            <td>
              <button type="button" class="btn btn-link btn-icons" onclick="Eliminar('tr_fact-client_{{$cont}}'); function_totales_facturas();">X</button>
            </td>
        </tr>

        @endforeach
      </tbody>
    </table>
    <button class="btn btn-link"  type="button" onclick="agregarfactura('true');"><i class="fas fa-plus"></i>Agregar factura de Venta</button>
  </div>
</div>
        </div>
      </div>
      <hr>--}}
      <div class="row" >
        <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
          <a href="{{route('notascredito.index')}}" class="btn btn-outline-secondary">Cancelar</a>
          <button type="submit" class="btn btn-success" id="boton-guardar">Guardar</button>
        </div>
      </div>
    </form> 

  <input type="hidden" id="impuestos" value="{{json_encode($impuestos)}}">
  <input type="hidden" id="allproductos" value="{{json_encode($inventario)}}">
  <input type="hidden" id="json-facturas" value="{{json_encode($facturas)}}">
  <input type="hidden" id="url" value="{{url('/')}}">
  <input type="hidden" id="jsonproduc" value="{{route('inventario.all')}}">
  <input type="hidden" id="simbolo" value="{{Auth::user()->empresa()->moneda}}">
  <input type="hidden" id="todaytoday" value="{{date('d-m-Y')}}">
  {{--<input type="hidden" id="bancos-input" value='@foreach($tipos_cuentas as $tipo_cuenta)
                <optgroup label="{{$tipo_cuenta["nombre"]}}">

                  @foreach($bancos as $cuenta)
                    @if($cuenta->tipo_cta==$tipo_cuenta["nro"])
                      <option value="{{$cuenta->id}}">{{$cuenta->nombre}}</option>
                    @endif
                  @endforeach
                </optgroup>
              @endforeach'>  --}}
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
@endsection