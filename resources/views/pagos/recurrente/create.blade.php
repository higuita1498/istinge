@extends('layouts.app')
@section('boton')
    <div class="row" style="margin: -2% 0 0 2%;">
        <div class="col-md-12">
            <a href="{{route('pagosrecurrentes.edit',$gasto->nro)}}" class="btn btn-outline-primary btn-sm "title="Editar"><i class="fas fa-edit"></i> Modificar datos de pago</a>
        </div>
    </div>
@endsection
@section('content')

    <form method="POST" action="{{ route('pagosRecu.pagar') }}" style="padding: 2% 3%;    " role="form" class="forms-sample">
        {{ csrf_field() }}
        <input type="hidden" value="{{$gasto->id}}" name="idgasto">
        <div class="row" style=" text-align: right;">
            <div class="col-md-5">
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Banco <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        <select class="form-control selectpicker" name="cuenta" id="cuenta" title="Seleccione" data-live-search="true" data-size="5" required="">
                            @php $tipos_cuentas=\App\Banco::tipos();@endphp
                            @foreach($tipos_cuentas as $tipo_cuenta)
                                <optgroup label="{{$tipo_cuenta['nombre']}}">

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
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Fecha<span class="text-danger">*</span><a><i data-tippy-content="Fecha en la que se realizó el pago" class="icono far fa-question-circle"></i></a></label>
                    <div class="col-sm-8">
                        <input type="text" class="form-control datepicker"  id="fecha" value="{{date('d-m-Y')}}" name="fecha" >
                    </div>
                </div>
                <div class="form-group row" title="Frecuencia en la que se genera el egreso (Meses)">
                    <label class="col-sm-4 col-form-label">Frecuencia</label>
                    <div class="col-sm-8">
                        <input type="text" name="frecuencia" class="form-control " min="1" required="" value="{{$gasto->frecuencia}}" disabled>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label">Notas del Egreso <small class="text-muted">(Visibles al imprimir)</small></label>
                    <div class="col-sm-8">
                        <textarea  class="form-control min_max_70" name="notas"></textarea>
                    </div>
                </div>
            </div>
            <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
        </div>

        <hr>

        <input type="hidden" id="retenciones" value="{{json_encode($retenciones)}}">

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
                                <tr id="retentotal{{$cont}}">
                                    <td width="40%" style="font-size: 0.8em;">{{$reten->nombre}} ({{$reten->porcentaje}}%)</td>
                                    <td id="retentotalvalue{{$cont}}">-{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($reten->total)}}</td>
                                </tr>
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

        <hr>

        <div class="row">
            <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
                <a href="{{route('pagosrecurrentes.index')}}" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-success">Pagar</button>
            </div>
        </div>
    </form>

@endsection
