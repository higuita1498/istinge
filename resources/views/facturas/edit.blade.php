@extends('layouts.app')



@section('content')
    <div class="paper">
        <!-- Membrete -->
        <div class="row">
            <div class="col-md-4 text-center">
                <img class="img-responsive" src="{{asset('images/Empresas/Empresa'.Auth::user()->empresa.'/'.Auth::user()->empresa()->logo)}}" alt="" width="50%">
            </div>
            <div class="col-md-4 text-center padding1">
                <h4>{{Auth::user()->empresa()->nombre}}</h4>
                <p>{{Auth::user()->empresa()->tip_iden('mini')}} {{Auth::user()->empresa()->nit}} @if(Auth::user()->empresa()->dv != null || Auth::user()->empresa()->dv == 0) - {{Auth::user()->empresa()->dv}} @endif <br> {{Auth::user()->empresa()->email}}</p>
            </div>
            <div class="col-md-4 text-center padding1" >
                <h4><b class="text-primary">No. </b> {{$factura->codigo}}</h4>
            </div>
        </div>
        <hr>

        <input type="hidden" value="1" name="fact_vent" id="fact_vent">
        <input type="hidden" value="{{$factura->tipo == 2 ? '2' : '1'}}" name="facelectronica" id ="facelectornica">
        <input type="hidden" value="1" name="editfactura" id ="editfactura">
        <!--Formulario Facturas-->
        <form method="POST" action="{{ route('facturas.update', $factura->id) }}" style="padding: 2% 3%;    " role="form" class="forms-sample"  id="form-factura" >
            {{ csrf_field() }}

            @include('facturas.includes.comment-descuento', ['comentario2' => $factura->comentario_2 ])

            <input type="hidden" value="{{back()->getTargetUrl()}}" name="page">
            <input name="_method" type="hidden" value="PATCH">
            <div class="row text-right">
                <div class="col-md-5">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Cliente <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <div class="input-group">
                                <select class="form-control selectpicker" name="cliente" id="cliente" required="" title="Seleccione" data-live-search="true" data-size="5" onchange="contacto(this.value); contratos_facturas(this.value);">
                                    @foreach($clientes as $client)
                                        <option {{$factura->cliente==$client->id?'selected':''}}   value="{{$client->id}}">{{$client->nombre}} {{$client->apellido1}} {{$client->apellido2}} - {{$client->nit}}</option>
                                    @endforeach
                                </select>
                                <div class="input-group-append" >
                <span class="input-group-text nopadding">
                  <a class="btn btn-outline-secondary btn-icons" title="Actualizar" onclick="contactos('{{route('contactos.clientes.json')}}', 'cliente');" style="margin-left: 8%;"><i class="fas fa-sync"></i></a>
                </span>
                                </div>
                            </div>
                            <p class="text-left nomargin">
                                <a href="{{route('contactos.create')}}" target="_blanck"><i class="fas fa-plus"></i> Nuevo Contacto</a></p>
                        </div>
                        <span class="help-block error">
          	<strong>{{ $errors->first('cliente') }}</strong>
          </span>
                    </div>

            {{-- Nuevo desarrollo de contratos. --}}
            @if(count($contratos) > 0 && isset($contratosFacturas))
            <div class="form-group row">
                <label class="col-sm-4 col-form-label">Contrato <span class="text-danger">*</span></label>
                <div class="col-sm-8">
                <div class="input-group">
                    <select class="form-control selectpicker" name="contratos_json" id="contratos_json" required=""
                    title="Seleccione un contrato" data-live-search="true" data-size="5"
                    onchange="rowItemsContrato(this.value)"
                    >
                        @foreach($contratos as $co)
                            <option value="{{$co->id}}" {{isset($contratosFacturas) && $contratosFacturas->contrato_nro==$co->nro?'selected':''}}
                                >{{$co->nro}}</option>
                        @endforeach
                    </select>
                </div>

                        </div>
                <span class="help-block error">
                    <strong>{{ $errors->first('contratos_json') }}</strong>
                </span>
            </div>
            @endif

            <div class="form-group row">
                <label class="col-sm-4 col-form-label">Identificación</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" readonly="" id="ident" value="">
                </div>
            </div>
            <div class="form-group row">
                <label class="col-sm-4 col-form-label">Teléfono</label>
                <div class="col-sm-8">
                    <input type="text" class="form-control" readonly="" id="telefono" value="">
                </div>
            </div>
            <div class="form-group row">
                <p class="col-sm-4 " style="background: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};border-radius: 20px;color: #fff;padding: 1%;text-align: center;"><a onclick="toggediv('masopciones');">Más opciones</a></p>
            </div>
        </div>

                <div class="col-md-5 offset-md-2">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Fecha <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control datepicker"  id="fecha"  name="fecha" disabled="" value="{{date('d-m-Y', strtotime($factura->fecha))}}"  >
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Plazo</label>
                        <div class="col-sm-8">
                            <select name="plazo" id="plazo" class="form-control " title="Seleccione">
                                @foreach($terminos as $termino)
                                    <option value="{{$termino->id}}" dias="{{$termino->dias}}" {{$factura->plazo==$termino->id?'selected':''}}>{{$termino->nombre}}</option>
                                @endforeach
                                <option value="n" dias="n" {{$factura->plazo=='n'?'selected':''}}>Vencimiento manual</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Vencimiento <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control datepickerinput" id="vencimiento" value="{{date('d-m-Y', strtotime($factura->vencimiento))}}" name="vencimiento" disabled="">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Bodega <span class="text-danger">*</span></label>
                        <div class="col-sm-8">
                            <select name="bodega" id="bodega" class="form-control">
                                @foreach($bodegas as $bodega)
                                    <option value="{{$bodega->id}}" {{$factura->bodega==$bodega->id?'selected':''}}>{{$bodega->bodega}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group row">
              <label class="col-sm-4 col-form-label">Tipo de operación <span class="text-danger">*</span></label>
              <div class="col-sm-8">
                  <select name="tipo_operacion" id="tipo_operacion" class="form-control selectpicker " data-live-search="true" data-size="5" required="" onchange="operacion(this.value)">
                    <option value="1" {{ $factura->tipo_operacion == 1 ? 'selected' : '' }}>Estandar</option>
                    <option value="2" {{ $factura->tipo_operacion == 2 ? 'selected' : '' }}>Factura de servicios AIU</option>
                  </select>
              </div>
          </div>

                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Tipo de documento <span class="text-danger">*</span><a><i data-tippy-content="Elige el nombre que desees dar a tus documentos" class="icono far fa-question-circle"></i></a></label>
                        <div class="col-sm-8">
                            <select name="documento" id="documento" class="form-control selectpicker " data-live-search="true" data-size="5" required="">
                                <option value="1" @if(isset($tipo_documento->tipo)){{$tipo_documento->tipo==1?'selected':''}}@endif>Factura de Venta</option>
                                <option value="3" @if(isset($tipo_documento->tipo)){{$tipo_documento->tipo==4?'selected':''}}@endif>Cuenta de Cobro</option>
                            </select>
                        </div>
                    </div>

                    @if(auth()->user()->empresa()->estado_dian == 1)
                        <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Orden de compra<a><i data-tippy-content="Número de orden de compra o servicio (dejar vacio si no tiene número)" class="icono far fa-question-circle"></i></a></label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="ordencompra" id="ordencompra" value="{{$factura->ordencompra}}">
                        </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="row text-right" style="display: none;" id="masopciones">
                <div class="col-md-5">
                    <div class="form-group row">
                        <label class="col-sm-4  col-form-label">Observaciones <br> <small>No visible en la factura de venta</small></label>
                        <div class="col-sm-8">
                            <textarea  class="form-control form-control-sm min_max_100" name="observaciones">{{$factura->observaciones}}</textarea>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 offset-md-3">
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Vendedor</label>
                        <div class="col-sm-8">
                            <select name="vendedor" id="vendedor" class="form-control selectpicker " title="Seleccione" data-live-search="true" data-size="5" required>
                                @foreach($vendedores as $vendedor)
                                    <option value="{{$vendedor->id}}" {{$factura->vendedor==$vendedor->id?'selected':''}}>{{$vendedor->nombre}}</option>
                                @endforeach
                            </select>
                        </div>
                        <span class="help-block error">
          	                <strong>{{ $errors->first('vendedor') }}</strong>
                        </span>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Periodo a cobrar</label>
                        <div class="col-sm-8">
                            <select name="periodo_facturacion" id="periodo_facturacion" class="form-control selectpicker " title="Seleccione" data-live-search="false" data-size="5" required>
                                <option value="1" @if($factura->periodo_facturacion == 1) {{'selected'}} @endif>Mes anticipado</option>
                                <option value="2" @if($factura->periodo_facturacion == 2) {{'selected'}} @endif>Mes vencido</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-sm-4 col-form-label">Lista de Precios</label>
                        <div class="col-sm-8">
                            <select name="lista_precios" id="lista_precios" class="form-control selectpicker">
                                @foreach($listas as $lista)
                                    <option value="{{$lista->id}}" {{$factura->lista_precios==$lista->id?'selected':''}}>{{$lista->nombre()}} </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <div id="notasaui">
    @if($factura->tipo_operacion == 2)
    <div class="alert alert-warning" style="text-align: left;" id="notasaui">
        <button type="button" class="close" data-dismiss="alert">×</button>
        Recuerde los siguientes pasos para facturar servicios AIU: <br>
        1. El item de venta "Administración" debe de llevar una <strong>descripción</strong> del objeto que se contrató. <br>
        2. El item "utilidad lleva un impuesto del 19%".
    </div>
    @endif
</div>

<div>
    <p id="contratos_nombres"></p>
    <input type="hidden" name="contratos_asociados" id="contratos_asociados">
</div>

            <!-- Desgloce -->
            <div class="fact-table">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-striped table-sm" id="table-form" width="100%">
                            <thead class="thead-dark">
                            <tr>
                                <th width="5%"></th>
                                <th width="24%">Ítem</th>
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
                            @if(Auth::user()->empresa == 44)

                            @endif
                            @foreach($items as $item)
                                @php $cont++; @endphp
                                <tr id="{{$cont}}">
                                    <td class="no-padding">
                                        <a class="btn btn-outline-secondary btn-icons" title="Actualizar" onclick="inventario('{{$cont}}');"><i class="fas fa-sync"></i></a>
                                    </td>
                                    <td  class="no-padding">
                                        <div class="resp-item">
                                            <input type="hidden" name="id_item{{$cont}}" value="{{$item->id}}">
                                            <select class="form-control form-control-sm selectpicker no-padding"  title="Seleccione" data-live-search="true" data-size="5" name="item[]" id="item{{$cont}}" onchange="rellenar({{$cont}}, this.value);" required="">
                                                @foreach($inventario as $itemm)
                                                    <option value="{{$itemm->id}}" {{$item->producto==$itemm->id?'selected':''}}>{{$itemm->producto}} - ({{$itemm->ref}})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <p class="text-left nomargin">
                                            <a href="" data-toggle="modal" data-target="#modalproduct" class="modalTr" tr="1">
                                                <i class="fas fa-plus"></i> Nuevo Producto
                                            </a>
                                        </p>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" id="ref{{$cont}}" name="ref[]" placeholder="Referencia" required="" value="{{$item->ref}}">

                                    </td>
                                    <td class="monetario">
                                        <input type="number" class="form-control form-control-sm" id="precio{{$cont}}"
                                               name="precio[]" placeholder="Precio Unitario" onkeyup="total({{$cont}})"
                                               required="" maxlength="24" min="0" value="{{$item->precio}}">

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
                                        <textarea  class="form-control form-control-sm" id="descripcion{{$cont}}" name="descripcion[]" placeholder="Descripción" >{{$item->descripcion}}</textarea>
                                    </td>
                                    <td>
                                        <input type="number" class="form-control form-control-sm" id="cant{{$cont}}" name="cant[]" placeholder="Cantidad" onchange="total({{$cont}});" min="1" required="" value="{{$item->cant}}">
                                        <p class="text-danger nomargin" id="pcant{{$cont}}"></p>
                                    </td>
                                    <td>

                                        <input type="text" class="form-control form-control-sm text-right" id="total{{$cont}}" value="{{App\Funcion::Parsear($item->total())}}" disabled="">

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
            <!-- Retenciones -->
            <div class="row"  style="margin-top: 10%; margin-left:0px;">
                <div class="col-md-5 no-padding">
                    <h5>RETENCIONES</h5>
                    <table class="table table-striped table-sm" id="table-retencion">
                        <thead class="thead-dark">
                        <th width="60%">Tipo de Retención</th>
                        <th width="34%">Valor</th>
                        <th width="5%"></th>
                        </thead>
                        <tbody>
                        @php $cont=0; @endphp
                        @foreach($retencionesFacturas as $retencion)
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
                        </tbody>
                    </table>
                    <button class="btn btn-outline-primary" onclick="CrearFilaRetencion();" type="button" style="margin-top: 2%;">Agregar Retención</button><a><i data-tippy-content="Agrega nuevas retenciones haciendo <a href='#'>clíck aquí</a>" class="icono far fa-question-circle"></i></a>
                </div>
                    <div class="col-md-7">
                        <h5>FORMAS DE PAGO <a><i data-tippy-content="Elige a que cuenta ira enlazado el movimiento contable" class="icono far fa-question-circle"></i></a></h5>
                        <table class="table table-striped table-sm" id="table-formaspago">
                          <thead class="thead-dark">
                            <th width="50%">Cuenta</th>
                            <th width="25%">Cruce</th>
                            <th width="20%" class="no-padding">Valor</th>
                            <th width="5%"></th>
                          </thead>
                          <tbody>
                            @php $cont=0; $totalformas= 0; @endphp
                              @foreach($formasPago as $forma)
                            @php $cont+=1; $totalformas+=$forma->debito; @endphp
                              <tr id="forma{{$cont}}" fila="{{$cont}}">
                                <td  class="no-padding">
                                    <select class="form-control form-control-sm selectpicker no-padding"  title="Seleccione" data-live-search="true" data-size="5" name="formapago[]" id="formapago{{$cont}}" onchange="llenarSelectAnticipo(this.value, $factura->cliente);" required="" >
                                        @if($forma->recibocaja_id != null)
                                        <option value="0" selected>Agregar un anticipo</option>
                                        @endif
                                        @foreach($relaciones as $relacion)
                                            <option value="{{$relacion->id}}" {{$relacion->id == $forma->formapago_id ? 'selected': ''}}>{{$relacion->codigo}} - {{$relacion->nombre}}</option>
                                        @endforeach
                                    </select>
                                  </td>
                                  <td  class="no-padding" id="tdanticipo{{$cont}}">
                                      <select class="form-control form-control-sm selectpicker no-padding"  title="Seleccione" data-live-search="true" data-size="5" name="selectanticipo[]" id="selectanticipo{{$cont}}">
                                        @if($forma->recibocaja_id != null)
                                        @foreach($factura->recibosAnticipo(1) as $recibo)
                                            <option value="{{$recibo->id}}" id="optionAnticipo{{$cont}}" precio="{{round($recibo->valor_anticipo,4)}}" {{$recibo->id == $forma->recibocaja_id ? 'selected': ''}}>RC-{{$recibo->nro}} - {{round($recibo->valor_anticipo,4)}}</option>
                                        @endforeach
                                        @endif
                                      </select>
                                  </td>
                                  <td class="monetario">
                                    <input type="hidden" value='0' id="lock_forma{{$cont}}">
                                    <input type="number" required="" style="display: inline-block; width: 100%;" class="form-control form-control-sm"  value="{{$forma->debito}}" maxlength="24" id="precioformapago{{$cont}}" name="precioformapago[]" placeholder="valor forma de pago" onkeyup="total_linea_formapago({{$cont}})" required="" min="0">
                                  </td>
                                <td><button type="button" class="btn btn-outline-secondary btn-icons" onclick="Eliminar_forma('forma{{$cont}}');">X</button></td>
                              </tr>
                              @endforeach
                          </tbody>
                        </table>
                        <div class="row">
                          <div class="col-md-6">
                            <button class="btn btn-outline-primary" onclick="CrearFilaFormaPago();" type="button" style="margin-top: 2%;">Agregar forma de pago</button><a><i data-tippy-content="Agrega nuevas formas de pago haciendo <a href='#'>clíck aquí</a>" class="icono far fa-question-circle"></i></a>
                          </div>
                          <div class="col-md-6 d-flex justify-content-between pt-3">
                            <h5>Total:</h5>
                            <span>$</span><span id="anticipototal">{{$totalformas}}</span>
                          </div>
                          <div class="col-md-12">
                            <span class="text-danger" style="font-size:12px"><strong>El total de las formas de pago debe coincidir con el total neto</strong></span>
                          </div>
                        </div>
                    </div>
            </div>
            <!-- Totales -->
            <div class="row" style="margin-top: 10%;">
                <div class="col-md-4 offset-md-8">
                    <table class="text-right widthtotal">
                        <tr>
                            <td width="40%">Subtotal</td>
                            <td>{{Auth::user()->empresa()->moneda}}<span id="subtotal">{{App\Funcion::Parsear($factura->total()->subtotal)}}</span></td>
                            <input type="hidden" id="subtotal_categoria_js" value="{{App\Funcion::Parsear($factura->total()->subtotal)}}">
                        </tr>
                        <tr>
                            <td>Descuento</td><td id="descuento">{{App\Funcion::Parsear($factura->total()->descuento)}}</td>
                        </tr>
                    </table>

                    <table class="text-right widthtotal"  style="width: 100%" id="totales">
                        <tr style="display: none">
                            <td width="40%">Subtotal</td>
                            <td>{{Auth::user()->empresa()->moneda}} <span id="subsub">{{App\Funcion::Parsear($factura->total()->subsub)}}</span></td>
                        </tr>
                        <tr >
                            <td width="40%">Subtotal</td>
                            <td>{{Auth::user()->empresa()->moneda}} <span id="subtotal2">{{App\Funcion::Parsear($factura->total()->subtotal -$factura->total()->descuento)}}</span></td>
                        </tr>

                        @php $cont=0; @endphp
                        @if($factura->total()->imp)
                            @foreach($factura->total()->imp as $imp)
                                @if(isset($imp->total)) @php $cont+=1; @endphp
                                <tr id="imp{{$cont}}">
                                    <td>{{$imp->nombre}} ({{$imp->porcentaje}}%)</td>
                                    <td id="totalimp{{$cont}}">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($imp->total)}}</td>
                                </tr>
                                @endif
                            @endforeach
                        @endif
                    </table>

                    <table class="text-right widthtotal"  id="totalesreten" style="width: 100%">
                        <tbody>
                        @php $cont=0; @endphp
                        @if($factura->total()->reten)
                            @foreach($factura->total()->reten as $key => $reten)

                                @if(isset($reten->total))
                                    @php $cont+=1; @endphp
                                    <input type="hidden" id="retentotalmonto{{$reten->id}}" value="{{$reten->total}}">
                                    <tr id="retentotal{{$key}}">
                                        <td width="40%" >{{$reten->nombre}} ({{$reten->porcentaje}}%)</td>
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
                            <td>{{Auth::user()->empresa()->moneda}} <span id="total">{{App\Funcion::Parsear($factura->total()->total)}}</span></td>
                        </tr>
                    </table>
                </div>
            </div>
            </div>
            <!-- Terminos y Condiciones -->
            <div class="row" style="margin-top: 5%; padding: 3%; min-height: 180px;">
                <div class="col-md-8 form-group">
                    <label class="form-label">Términos y Condiciones</label>
                    <textarea  class="form-control min_max_100" name="term_cond">{{$factura->term_cond}}</textarea>
                </div>
                <div class="col-md-4 form-group">
                    <label class="form-label">Notas
                    <div id="notasaui"></div></label>

                    <textarea  class="form-control form-control-sm min_max_100" name="notas">{{$factura->facnotas}}</textarea>
                </div>
            </div>
            <div class="col-md-12"><small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small></div>
            <hr>
            <!--Botones Finales -->
            <div class="row" >
                <div class="col-md-12 text-right" style="padding-top: 1%;">
                    <a href="{{route('facturas.index')}}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success">Guardar</button>
                </div>
            </div>
        </form>
        <input type="hidden" id="impuestos" value="{{json_encode($impuestos)}}">
        <input type="hidden" id="allproductos" value="{{json_encode($inventario)}}">
        <input type="hidden" id="url" value="{{url('/')}}">
        @foreach ($impuestos as $impuesto)
            <input type="hidden" id="hddn_imp_{{$impuesto->id}}" value="{{$impuesto->tipo}}">
        @endforeach
        <input type="hidden" id="jsonproduc" value="{{route('inventario.all')}}">
        <input type="hidden" id="simbolo" value="{{Auth::user()->empresa()->moneda}}">
        <input type="hidden" id="retenciones" value="{{json_encode($retenciones)}}">
        <input type="hidden" id="formaspago" value="{{json_encode($relaciones)}}">
        <input type="hidden" id="edit" value="1">
        <input type="hidden" id="factura" value="{{$factura->id}}">
        {{-- VARIABLE DE SALDO A FAVOR DEL CLIENTE --}}
        <input type="hidden" id="saldofavorcliente" name="saldofavorcliente">
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

  <!-- MODAL creación producto -->
  <div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
       aria-hidden="true" id="modalproduct">
      <div class="modal-dialog modal-lg">
          <div class="modal-content">
              @include('inventario.modal.create2')
          </div>
      </div>
  </div>
  <!--  -->

@endsection
