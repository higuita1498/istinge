@extends('layouts.app')
@section('content')

    @if(Session::has('error'))
    <div class="alert alert-danger" role="alert">
        {{Session::get('error')}}

        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">×</span>
        </button>
    </div>
    @endif

    <form method="POST" action="{{ route('notascredito.update', $nota->id) }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-factura" >
        @csrf
        <input name="_method" type="hidden" value="PATCH">
        <input type="hidden" value="1" name="fact_prov" id="fact_prov">
        <input type="hidden" value="1" name="cotizacion" id="cotizacion_si">
        <input type="hidden" value="1" name="dian" id="dian">


        <div class="row" style=" text-align: right;">
            <div class="col-md-6">
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Cliente <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        <select class="form-control form-control-sm selectpicker" name="cliente" id="cliente" title="Seleccione" data-live-search="true" data-size="5" required="" onchange="contacto(this.value); getFacturas(this.value)">
                            @foreach($clientes as $cliente)
                                <option {{$nota->cliente==$cliente->id?'selected':''}} value="{{$cliente->id}}">{{$cliente->nombre}} {{$cliente->apellido1}} {{$cliente->apellido2}} - {{$cliente->nit}}</option>
                            @endforeach
                        </select>
                    </div>
                    <span class="help-block error">
                <strong>{{ $errors->first('cliente') }}</strong>
          </span>
                </div>

                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Notas <a><i data-tippy-content="Notas visibles en la impresión" class="icono far fa-question-circle"></i></a></label>
                    <div class="col-sm-8">
                        <textarea  class="form-control form-control-sm min_max_100" name="notas" rows="2">{{$nota->notas}}</textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Observaciones <br><small>(no visible en la nota crédito)</small> </label>
                    <div class="col-sm-8">
                        <textarea  class="form-control form-control-sm min_max_100" name="observaciones" rows="2">{{$nota->observaciones}}</textarea>
                    </div>
                </div>
            </div>
            <div class="col-md-5 offset-md-1">
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Tipo de nota crédito</label>
                    <div class="col-sm-8">
                        <select class="form-control form-control-sm selectpicker" name="tipo" id="tipo" title="Seleccione" data-live-search="true" data-size="5" required>
                            @foreach($tipos as $tipo)
                                <option value="{{$tipo->id}}" {{$nota->tipo==$tipo->id?'selected':''}}> {{$tipo->tipo}}</option>
                            @endforeach
                        </select>
                    </div>
                    <span class="help-block error">
                <strong>{{ $errors->first('metodo_pago') }}</strong>
          </span>
                </div>
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
                                <option value="{{$bodega->id}}">{{$bodega->bodega}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row" style="display: none;">
                    <label class="col-sm-4 col-form-label">Lista de Precios <a><i data-tippy-content="Lista de precios que desee asociar a la nota de crédito" class="icono far fa-question-circle"></i></a></label>
                    <div class="col-sm-8">
                        <select name="lista_precios" id="lista_precios" class="form-control selectpicker">
                            @foreach($listas as $lista)
                                <option value="{{$lista->id}}">{{$lista->nombre()}} </option>
                            @endforeach
                        </select>
                    </div>
                </div>


                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Factura <a>
                            <i data-tippy-content="Lista de facturas de venta asociadas al lciente" class="icono far fa-question-circle"></i></a></label>
                    <div class="col-sm-8">
                        <select name="factura" id="lista_factura" class="form-control form-control-sm  selectpicker" onchange="itemsFactura(this.value);" title="Seleccione Factura" data-live-search="true" data-size="5">
                            @foreach($facturaContacto as $facturaC)
                                <option value="{{$facturaC->id}}" @if(isset($notasFacturas)) {{$facturaC->id==$notasFacturas->factura?'selected':''}} @endif >{{$facturaC->codigo}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                  <div class="form-group row">
              <label class="col-sm-4 col-form-label">Tipo de operación <span class="text-danger">*</span></label>
              <div class="col-sm-8">
                  <select name="tipo_operacion" id="tipo_operacion" class="form-control selectpicker " data-live-search="true" data-size="5" required="" onchange="operacion(this.value)">
                    <option value="1" {{ $nota->tipo_operacion == 1 ? 'selected' : '' }}>Estandar</option>
                    <option value="2" {{ $nota->tipo_operacion == 2 ? 'selected' : '' }}>Nota Crédito de servicios AIU</option>
                    <option value="3" {{ $nota->tipo_operacion == 3 ? 'selected' : '' }}>Nota Crédito con detalle de recaudo a terceros</option>
                  </select>
              </div>
          </div>

            @if(auth()->user()->empresaObj->estado_dian == 1)
                <div class="form-group row">
                <label class="col-sm-4 col-form-label">Orden de compra<a><i data-tippy-content="Número de orden de compra o servicio (dejar vacio si no tiene número)" class="icono far fa-question-circle"></i></a></label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" name="ordencompra" id="ordencompra" value="{{$nota->ordencompra}}">
                </div>
                </div>
            @endif

            </div>
        </div>

        <div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
        </div>

               <div id="notasaui">
    @if($nota->tipo_operacion == 2)
    <div class="alert alert-warning" style="text-align: left;">
        <button type="button" class="close" data-dismiss="alert">×</button>
        Recuerde los siguientes pasos para facturar servicios AIU: <br>
        1. El item de venta "Administración" debe de llevar una <strong>descripción</strong> del objeto que se contrató. <br>
        2. El item "utilidad lleva un impuesto del 19%".
    </div>
    @endif
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
                                        <input type="hidden" name="item{{$cont}}" value="{{$item->id}}">
                                        <select class="form-control form-control-sm selectpicker no-padding rellenar calcularLinea" cont="{{$cont}}" title="Seleccione" data-live-search="true" data-size="5" name="item[]" id="item{{$cont}}" required>
                                            @foreach($inventario as $itemm)
                                                <option value="{{$itemm->id}}" {{$item->producto==$itemm->id?'selected':''}}>{{$itemm->producto}} - ({{$itemm->ref}})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </td>
                                <td>
                                    <div class="resp-refer">
                                        <input type="text" class="form-control form-control-sm" id="ref{{$cont}}" name="ref[]" placeholder="Referencia" required value="{{$item->ref}}">
                                    </div>
                                </td>
                                <td class="monetario">
                                    <div class="resp-precio">
                                        <input type="number" class="form-control form-control-sm calcularLinea" cont="{{$cont}}" id="precio{{$cont}}" name="precio[]" placeholder="Precio Unitario" onkeyup="total({{$cont}})" required maxlength="24" min="0" value="{{round($item->precio,4)}}">
                                    </div>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm calcularLinea " cont="{{$cont}}" id="desc{{$cont}}" onkeyup="total({{round($cont)}})" name="desc[]" placeholder="%" value="{{$item->desc}}" onkeypress="return event.charCode >= 46 && event.charCode <=57" min="0" max="100">
                                </td>
                                <td>
                                    <select class="form-control form-control-sm selectpicker calcularLinea impuestos" cont="{{$cont}}" name="impuesto[]" id="impuesto{{$cont}}" title="Impuesto" required multiple  onchange="impuestoFacturaDeVenta(this.id); totalall();">
                                        @foreach($impuestos as $impuesto)
                                            <option value="{{round($impuesto->id)}}" porc="{{round($impuesto->porcentaje)}}" {{$item->id_impuesto==$impuesto->id && $item->id_impuesto != NULL ?'selected':''}} {{$item->id_impuesto_1==$impuesto->id && $item->id_impuesto_1 != NULL ?'selected':''}} {{$item->id_impuesto_2==$impuesto->id && $item->id_impuesto_2 != NULL ?'selected':''}} {{$item->id_impuesto_3==$impuesto->id && $item->id_impuesto_3 != NULL ?'selected':''}} {{$item->id_impuesto_4==$impuesto->id && $item->id_impuesto_4 != NULL ?'selected':''}} {{$item->id_impuesto_5==$impuesto->id && $item->id_impuesto_5 != NULL ?'selected':''}} {{$item->id_impuesto_6==$impuesto->id && $item->id_impuesto_6 != NULL ?'selected':''}} {{$item->id_impuesto_7==$impuesto->id && $item->id_impuesto_7 != NULL ?'selected':''}}>{{$impuesto->nombre}} - {{round($impuesto->porcentaje)}}%</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td  style="padding-top: 1% !important;">
                                    <div class="resp-descripcion">
                                        <textarea  class="form-control form-control-sm" id="descripcion{{$cont}}" name="descripcion[]" placeholder="Descripción" >{{$item->descripcion}}</textarea>
                                    </div>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm calcularLinea" cont="{{$cont}}" id="cant{{$cont}}" name="cant[]" placeholder="Cantidad" min="1"  onchange="total({{$cont}});" required value="{{round($item->cant,4)}}">
                                    <p class="text-danger nomargin" id="pcant{{$cont}}"></p>
                                </td>
                                <td>
                                    <div class="resp-total">
                                        <input type="text" class="form-control form-control-sm text-right" id="total{{$cont}}" value="{{App\Funcion::Parsear($item->total())}}" disabled="">
                                    </div>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-outline-secondary btn-icons " onclick="eliminarColumna({{$cont}})" cont="">X</button>
                                </td>
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
                                    <input type="number" value="{{round($retencion->valor, 4)}}" required="" style="display: inline-block; width: 80%;"
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
                            <td>{{Auth::user()->empresaObj->moneda}}<span id="subtotal">{{App\Funcion::Parsear($nota->total()->subtotal)}}</span></td>
                            <input type="hidden" id="subtotal_categoria_js" value="{{App\Funcion::Parsear($nota->total()->subtotal)}}">
                            <input type="hidden" id="detalle_monto" name="detalle_monto" value="{{ $nota->total_recaudo }}">
                        </tr>
                        <tr>
                            <td>Descuento</td><td id="descuento">{{App\Funcion::Parsear($nota->total()->descuento)}}</td>
                        </tr>
                    </table>
                    <table class="text-right widthtotal"  style="width: 100%" id="totales">
                        <tr style="display: none;">
                            <td width="40%">Subtotal</td>
                            <td>{{Auth::user()->empresaObj->moneda}} <span id="subsub">{{App\Funcion::Parsear($nota->total()->subsub)}}</span></td>
                        </tr>
                        <tr >
                            <td width="40%">Subtotal</td>
                            <td>{{Auth::user()->empresaObj->moneda}} <span id="subtotal2">{{App\Funcion::Parsear($nota->total()->subtotal -$nota->total()->descuento)}}</span></td>
                        </tr>
                        @php $cont=0; @endphp
                        @if($nota->total()->imp)
                            @foreach($nota->total()->imp as $imp)
                                @if(isset($imp->total)) @php $cont+=1; @endphp
                                <tr id="imp{{$cont}}">
                                    <td>{{$imp->nombre}} ({{$imp->porcentaje}}%)</td>
                                    <td id="totalimp{{$cont}}">{{Auth::user()->empresaObj->moneda}}{{App\Funcion::Parsear($imp->total)}}</td>
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
                                            -{{Auth::user()->empresaObj->moneda}} {{App\Funcion::Parsear($reten->total)}}</td>
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
                            <td>{{Auth::user()->empresaObj->moneda}} <span id="total">{{App\Funcion::Parsear($nota->total()->total + $nota->total_recaudo)}}</span></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="alert alert-danger" style="display: none;" id="error-cliente"></div>
  
        <hr>
        <div class="row" >
            <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
                <a href="{{route('notascredito.index')}}" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success" id="boton-guardar">Guardar</button>
            </div>
        </div>
    </form>

    <input type="hidden" id="impuestos" value="{{json_encode($impuestos)}}">
    @foreach ($impuestos as $impuesto)
      <input type="hidden" id="hddn_imp_{{$impuesto->id}}" value="{{$impuesto->tipo}}">
    @endforeach
    <input type="hidden" id="allproductos" value="{{json_encode($inventario)}}">
    <input type="hidden" id="json-facturas" value="">
    <input type="hidden" id="url" value="{{url('/')}}">
    <input type="hidden" id="jsonproduc" value="{{route('inventario.all')}}">
    <input type="hidden" id="simbolo" value="{{Auth::user()->empresaObj->moneda}}">
    <input type="hidden" id="todaytoday" value="{{date('d-m-Y')}}">
    <input type="hidden" id="retenciones" value="{{json_encode($retenciones)}}">

@endsection

@section('scripts')

<script>
        function getFacturas(id){
            var url = $('#url').val()+'/empresa/facturas/cliente/'+id;
            $.ajax({
                url: url,
                complete: function(data){
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

        function getRetenciones(id){
            var url =$('#url').val()+'/empresa/notascredito/reteitems/'+id;
            $.ajax({
                url: url,
                complete: function(data){
                    var i = 0;
                    data = JSON.parse(data.responseText);


                    $.each(data,function(key, value)
                    {
                        i++;
                        $('#table-retencion').append(
                                '<tr  id="reten'+i+'">' +
                                '<td class="no-padding">'+
                                    '<div class="resp-item">'+
                                        '<input class="calcular" type="hidden" name="impuesto[]" onkeyup="retencion_calculate('+i+','+value.id_impuesto+')" value="'+value.id_impuesto+'" porc="" id="impuesto'+i+'">'+
                                        '<input type="text" class="form-control" disabled value="'+value.nombre+'">'+
                                    '</div>'+
                                '</td>'+
                                '<td class="monetario">'+
                                    '<input type="hidden" value="" id="lock_reten'+i+'">'+
                                    '<input type="number" required="" style="display: inline-block; width: 80%;" class="form-control retenciones" maxlength="24" onclick="total_categorias()" id="precio_reten'+i+'" name="precio_reten[]" placeholder="Valor retenido" onclick="total_linea('+i+')" required="" min="0" value="'+value.valor+'">'+
                                '</td>'+
                                '<td>'+

                                '</tr>'
                        );
                        $('.calcular').trigger('onkeyup');

                    });


                },
                error: function(data){

                    alert('Disculpe, estamos presentando problemas al tratar de enviar el formulario, intentelo mas tarde');
                }
            });
        }


        function itemsFactura(id){

            var url =$('#url').val()+'/empresa/notascredito/items/'+id;
            $.ajax({
                url: url,
               /* beforeSend: function(){
                    cargando(true);
                },*/
                complete: function(data){
                    console.log(data.responseText);
                    data = JSON.parse(data.responseText);

                    $('#table-form tbody tr').remove();
                    $('#table-retencion tbody tr').remove();
                    var i = 0;
                    $.each(data,function(key, value)
                    {

                        if(value.desc == null){
                            value.desc = '';
                        }
                        if(value.impuesto == null){
                            value.impuesto = '';
                        }
                        if(value.descripcion == null){
                            value.descripcion = '';
                        }
                        i++;
                        //var impuesto+i = [];
                        $('#table-form').append(
                            '<tr id="'+i+'">'+
                                '<td class="no-padding">'+
                                    '<div class="resp-item">'+
                                    '<input type="hidden" name="item[]" value="'+value.producto+'">'+
                                    '<input type="text" class="form-control" disabled value="'+value.nombre+' - '+value.ref+'">'+
                                    '</div>'+
                                '</td>'+
                                '<td>'+
                                    '<div class="resp-refer">'+
                                    '<input type="hidden" name="ref[]"  value="'+value.ref+'">'+
                                    '<input type="text" class="form-control" id="ref'+i+'" placeholder="Referencia" required disabled value="'+value.ref+'">'+
                                    '</div>'+
                                '</td>'+
                                '<td class="monetario">'+
                                    '<div class="resp-precio">'+
                                    '<input type="number" class="form-control "  id="precio'+i+'" name="precio[]" placeholder="Precio Unitario" onkeyup="total('+i+')" required maxlength="24" min="0" value="'+Math.round(value.precio,4)+'">'+
                                    '</div>'+
                                '</td>'+
                                '<td>'+
                                    '<div class="resp-item">'+
                                    '<input type="hidden" name="descuento[]" value="'+value.desc+'" >'+
                                    '<input type="text" class="form-control nro "  id="desc'+i+'" name="desc[]" placeholder="%" value="'+value.desc+'" disabled>'+
                                    '</div>'+
                                '</td>'+
                                '<td class="no-padding">'+
                                    '<div class="resp-item">'+
                                        '<input type="hidden" name="impuesto'+i+'[]" value="'+value.id_impuesto+'" porc="'+Math.round(value.impuesto,4)+'"  id="impuesto'+i+'">'+
                                        '<input type="text"  class="form-control" disabled value="'+Math.round(value.impuesto,4)+'%">'+
                                    '</div>'+
                                '</td>'+
                                '<td  style="padding-top: 1% !important;">'+
                                    '<div class="resp-descripcion">'+
                                    '<textarea  class="form-control" id="descripcion'+i+'" name="descripcion[]" placeholder="Descripción" >'+value.descripcion+'</textarea>'+
                                    '</div>'+
                                '</td>'+
                                '<td>'+
                                    '<input type="number" class="form-control cantidades" id="cant'+i+'" name="cant[]" placeholder="Cantidad" onkeyup="total('+i+')" onclick="total('+i+');" min="1" value="'+Math.round(value.cant,4)+'" required="">'+
                                    '<p class="text-danger nomargin" id="pcant'+i+'"></p>'+
                                '</td>'+
                                '<td>'+
                                    '<div class="resp-total">'+
                                    '<input type="text" class="form-control text-right " name="total[]" id="total'+i+'" value="" disabled>'+
                                    '</div>'+
                                '</td>'+
                                '<td>'+
                                    '<button type="button" class="btn btn-outline-secondary btn-icons" onclick="eliminarColumna('+i+');">X</button>'+
                                '</td>'+
                            '</tr>'

                        );
                        //MULTI IVA
                        if(value.id_impuesto){ $("#impuesto" + id + " option[value=" + value.id_impuesto + "]").attr('selected', 'selected'); }
                        if(value.id_impuesto_1){ $("#impuesto" + id + " option[value=" + value.id_impuesto_1 + "]").attr('selected', 'selected'); }
                        if(value.id_impuesto_2){ $("#impuesto" + id + " option[value=" + value.id_impuesto_2 + "]").attr('selected', 'selected'); }
                        if(value.id_impuesto_3){ $("#impuesto" + id + " option[value=" + value.id_impuesto_3 + "]").attr('selected', 'selected'); }
                        if(value.id_impuesto_4){ $("#impuesto" + id + " option[value=" + value.id_impuesto_4 + "]").attr('selected', 'selected'); }
                        if(value.id_impuesto_5){ $("#impuesto" + id + " option[value=" + value.id_impuesto_5 + "]").attr('selected', 'selected'); }
                        if(value.id_impuesto_6){ $("#impuesto" + id + " option[value=" + value.id_impuesto_6 + "]").attr('selected', 'selected'); }
                        if(value.id_impuesto_7){ $("#impuesto" + id + " option[value=" + value.id_impuesto_7 + "]").attr('selected', 'selected'); }
                        //MULTI IVA
                        $('#impuesto' + id).selectpicker('refresh');
                        $('.cantidades').trigger('click');
                    });


                },
                error: function(data){

                    alert('Disculpe, estamos presentando problemas al tratar de enviar el formulario, intentelo mas tarde');
                }
            });


        }

        function eliminarColumna(i) {
          $("#" + i).remove();
          var opt = 1;

          $('#table-form tbody tr').each(function () {
            id = $(this).attr('id');
            $("#impuesto"+id).removeAttr('name').attr('name','impuesto'+opt+'[]');
            opt++;
          });
          total(i);
        }


    </script>

@endsection
