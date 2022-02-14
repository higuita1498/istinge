@extends('layouts.app')

@section('content')
    <form method="POST" action="{{ route('pagosrecurrentes.update', $gasto->id) }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-factura" >
        {{ csrf_field() }}
        <input name="_method" type="hidden" value="PATCH">
        <div class="row" style=" text-align: right;">
            <div class="col-md-5">
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Banco <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        <select class="form-control selectpicker" name="cuenta" id="cuenta" title="Seleccione" data-live-search="true" data-size="5" required="">
                            @php $tipos_cuentas=\App\Banco::tipos();@endphp
                            @foreach($tipos_cuentas as $tipo_cuenta)
                            <option value="" disabled class="font-weight-bold text-black">
                                {{$tipo_cuenta['nombre']}}
                            </option>
                            @foreach($bancos as $cuenta)
                            @if($cuenta->tipo_cta==$tipo_cuenta['nro'])
                            <option value="{{$cuenta->id}}" {{$gasto->cuenta==$cuenta->id?'selected':''}}>{{$cuenta->nombre}}</option>
                            @endif
                            @endforeach
                            </optgroup>
                            @endforeach
                        </select>
                    </div>
                    <span class="help-block error">
                        <strong>{{ $errors->first('cuenta') }}</strong>
                    </span>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Método de pago </label>
                    <div class="col-sm-8">
                        <select class="form-control selectpicker" name="metodo_pago" id="metodo_pago" title="Seleccione" data-live-search="true" data-size="5">
                            @foreach($metodos_pago as $metodo)
                            <option value="{{$metodo->id}}" {{$gasto->metodo_pago==$metodo->id?'selected':''}}>{{$metodo->metodo}}</option>
                            @endforeach
                        </select>
                    </div>
                    <span class="help-block error">
                        <strong>{{ $errors->first('metodo_pago') }}</strong>
                    </span>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Observaciones</label>
                    <div class="col-sm-8">
                        <textarea  class="form-control min_max_70" name="observaciones">{{$gasto->observaciones}}</textarea>
                    </div>
                </div>
            </div>
            <div class="col-md-6 offset-md-1">
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Tipo de Gasto <span class="text-danger">*</span><a><i data-tippy-content="Organice los tipos de gastos que utilizará su empresa <a href='{{ route('tipos-gastos.index') }}' target='_blank'>aquí</a>." class="icono far fa-question-circle"></i></a></label>
                    <div class="col-sm-8">
                        <select class="form-control selectpicker" name="tipo_gasto" id="tipo_gasto" title="Seleccione" data-live-search="true" data-size="5" onchange="factura_pendiente();" disabled>
                            @foreach($tipos_gastos as $tipo)
                                <option {{$gasto->tipo_gasto==$tipo->id?'selected':''}} value="{{$tipo->id}}">{{$tipo->nombre}}</option>
                            @endforeach
                        </select>
                        <input type="hidden" name="tipo_gasto" value="{{$gasto->tipo_gasto}}">
                    </div>
                    <span class="help-block error">
                        <strong>{{ $errors->first('tipo_gasto') }}</strong>
                    </span>
                </div>
                <div class="form-group row" title="Fecha en la que se crea el primer comprobante">
                    <label class="col-sm-4 col-form-label">Fecha Pago <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control datepicker"  id="fecha" value="{{date('d-m-Y', strtotime($gasto->fecha))}}" name="fecha" disabled="">
                    </div>
                </div>
                <div class="form-group row" title="Frecuencia en la que se genera el egreso (Meses)">
                    <label class="col-sm-4 col-form-label">Frecuencia <span class="text-danger">*</span> <a><i data-tippy-content="Frecuencia en la que se genera el egreso (Meses)" class="icono far fa-question-circle"></i></a></label>
                    <div class="col-sm-8">
                        <input type="number" name="frecuencia" class="form-control " min="1" required="" value="{{$gasto->frecuencia}}">
                    </div>
                </div>

                <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
            </div>
        </div>

        <div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
            <button type="button" class="close" data-dismiss="alert">×</button>
            <strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
        </div>

        <div class="row">
            <div class="col-md-12 fact-table" id="no" >
                <div id="div-categoria">
                    <table class="table table-striped table-sm" id="table-form" width="100%">
                        <thead class="thead-dark">
                            <tr>
                                <th width="28%">Categoria</th>
                                <th width="8%">Valor</th>
                                <th width="12%">Impuesto</th>
                                <th width="7%">Cantidad</th>
                                <th width="13%">Observaciones</th>
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
                                            <input type="hidden" name="id_cate{{$cont}}" value="{{$item->id}}">
                                            <select class="form-control form-control-sm selectpicker no-padding"  title="Seleccione" data-live-search="true" data-size="5" name="categoria[]" id="categoria{{$cont}}" required="" onchange="enabled({{$cont}});" >
                                                @foreach($categorias as $categoria)
                                                <optgroup label="{{$categoria->nombre}}">
                                                    @foreach($categoria->hijos(true) as $categoria1)
                                                    <option {{old('categoria')==$categoria1->id?'selected':''}} value="{{$categoria1->id}}" {{$categoria1->estatus==0?'disabled':''}} {{$categoria1->id==$item->categoria?'selected':''}}>{{$categoria1->nombre}}</option>
                                                    @foreach($categoria1->hijos(true) as $categoria2)
                                                    <option class="hijo" {{old('categoria')==$categoria2->id?'selected':''}} value="{{$categoria2->id}}" {{$categoria2->estatus==0?'disabled':''}} {{$categoria2->id==$item->categoria?'selected':''}}>{{$categoria2->nombre}}</option>
                                                    @foreach($categoria2->hijos(true) as $categoria3)
                                                    <option class="nieto" {{old('categoria')==$categoria3->id?'selected':''}} value="{{$categoria3->id}}" {{$categoria3->estatus==0?'disabled':''}} {{$categoria3->id==$item->categoria?'selected':''}}>{{$categoria3->nombre}}</option>
                                                    @foreach($categoria3->hijos(true) as $categoria4)
                                                    <option class="bisnieto" {{old('categoria')==$categoria4->id?'selected':''}} value="{{$categoria4->id}}" {{$categoria3->estatus==0?'disabled':''}} {{$categoria4->id==$item->categoria?'selected':''}}>{{$categoria4->nombre}}</option>
                                                    @endforeach
                                                    @endforeach
                                                    @endforeach
                                                    @endforeach
                                                </optgroup>
                                                @endforeach
                                            </select>
                                        </td>
                                    </div>
                                    <td class="monetario">
                                        <div class="resp-precio">
                                            <input type="number" class="form-control form-control-sm" id="precio_categoria{{$cont}}" name="precio_categoria[]" placeholder="Precio" min="0" onkeyup="total_linea({{$cont}})" required="" value="{{round($item->valor,2)}}">
                                        </div>
                                    </td>
                                    <td>
                                        <select class="form-control form-control-sm selectpicker" name="impuesto_categoria[]" id="impuesto_categoria{{$cont}}" title="Impuesto" onchange="total_categorias({{$cont}});" required="">
                                            @foreach($impuestos as $impuesto)
                                            <option value="{{$impuesto->id}}" porc="{{$impuesto->porcentaje}}" {{$impuesto->id==$item->id_impuesto?'selected':''}}>{{$impuesto->nombre}} - {{$impuesto->porcentaje}}%</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td width="5%">
                                        <input type="number" class="form-control form-control-sm" id="cant_categoria{{$cont}}" name="cant_categoria[]" placeholder="Cantidad" onchange="total_linea({{$cont}});" min="1" required="" value="{{round($item->cant,4)}}">
                                    </td>
                                    <td  style="padding-top: 1% !important;">
                                        <div class="resp-descripcion">
                                            <textarea  class="form-control form-control-sm" id="descripcion_categoria{{$cont}}" name="descripcion_categoria[]" placeholder="Observaciones">{{$item->descripcion}}</textarea>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="resp-total">
                                            <input type="text" class="form-control form-control-sm text-right" id="total_categoria{{$cont}}" value="{{App\Funcion::Parsear($item->total())}}" disabled="">
                                        </div>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-outline-secondary btn-icons" onclick="Eliminar({{$cont}}); total_categorias(); ">X</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <button class="btn btn-outline-primary" onclick="CrearFilaCategorias();" type="button" style="margin-top: 5%; margin-bottom: 1%;">Agregar línea</button>

                        <div class="row" style="margin-top: 5%;">
                            <div class="col-md-4 offset-md-8">
                                <table style="text-align: right;  width: 100%;" id="totales">
                                    <tr>
                                        <td width="40%">Subtotal</td>
                                        <input type="hidden" id="subtotal_categoria_js" value="{{$gasto->total()->subtotal}}">
                                        <input type="hidden" id="impuestos_categoria_js" value="{{$gasto->total()->ivas}}">
                                        <td>{{Auth::user()->empresa()->moneda}} <span id="subtotal_categoria"> {{App\Funcion::Parsear($gasto->total()->subtotal)}}</span></td>
                                    </tr>
                                    @php $cont=0; @endphp
                                    @if($gasto->total()->imp)
                                        @foreach($gasto->total()->imp as $imp)
                                            @if(isset($imp->total))
                                                @php $cont+=1; @endphp
                                                <tr id="imp{{$cont}}">
                                                    <td>{{$imp->nombre}} ({{$imp->porcentaje}}%)</td>
                                                    <td id="totalimp{{$cont}}">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($imp->total)}}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    @endif
                                </table>
                                <table style="text-align: right; width: 100%;" id="totalesreten">
                                    <tbody>
                                        @php $cont=0; @endphp
                                        @if($gasto->total()->reten)
                                            @foreach($gasto->total()->reten as $reten)
                                                @if(isset($reten->total))
                                                    <tr id="retentotal{{$cont}}"><td width="40%" style="font-size: 0.8em;">{{$reten->nombre}} ({{$reten->porcentaje}}%)</td><td id="retentotalvalue{{$cont}}">-{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($reten->total)}}</td></tr>
                                                    @php $cont+=1; @endphp
                                                @endif
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>

                                <hr>

                                <table style="text-align: right; font-size: 24px !important; width: 100%;">
                                    <tr>
                                        <td width="40%">TOTAL</td>
                                        <td>{{Auth::user()->empresa()->moneda}} <span id="total_categoria">{{App\Funcion::Parsear($gasto->total()->total)}}</span></td>
                                    </tr>
                                </table>
                                <p id="p_rentencion" class="text-danger"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr>

            <div class="row">
                <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
                    <a href="{{route('pagosrecurrentes.index')}}" class="btn btn-outline-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success">Guardar</button>
                </div>
            </div>
        </form>
        <input type="hidden" id="url" value="{{url('/')}}">
        <input type="hidden" id="impuestos" value="{{json_encode($impuestos)}}">
        <input type="hidden" id="simbolo" value="{{Auth::user()->empresa()->moneda}}">
        <input type="hidden" id="allcategorias" value='@foreach($categorias as $categoria)
            <optgroup label="{{$categoria->nombre}}">
                @foreach($categoria->hijos(true) as $categoria1)
                <option {{old('categoria')==$categoria1->id?'selected':''}} value="{{$categoria1->id}}" {{$categoria1->estatus==0?'disabled':''}}>{{$categoria1->nombre}}</option>
                @foreach($categoria1->hijos(true) as $categoria2)
                <option class="hijo" {{old('categoria')==$categoria2->id?'selected':''}} value="{{$categoria2->id}}" {{$categoria2->estatus==0?'disabled':''}}>{{$categoria2->nombre}}</option>
                @foreach($categoria2->hijos(true) as $categoria3)
                <option class="nieto" {{old('categoria')==$categoria3->id?'selected':''}} value="{{$categoria3->id}}" {{$categoria3->estatus==0?'disabled':''}}>{{$categoria3->nombre}}</option>
                @foreach($categoria3->hijos(true) as $categoria4)
                <option class="bisnieto" {{old('categoria')==$categoria4->id?'selected':''}} value="{{$categoria4->id}}" {{$categoria3->estatus==0?'disabled':''}}>{{$categoria4->nombre}}</option>
                @endforeach
                @endforeach
                @endforeach
                @endforeach
            </optgroup>
            @endforeach'>
        <input type="hidden" id="retenciones" value="{{json_encode(array())}}">

@endsection
