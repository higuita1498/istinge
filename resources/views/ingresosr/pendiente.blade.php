@if($total>0)
<p>Nota: Solo apareceran en el recibo de caja las remisiones con valores recibidos</p>
<table class="table table-striped table-hover" id="table-facturas">
  <thead class="thead-dark">
    <tr>
      <th>Número</th>
      <th>Fecha</th>
      <th>Total</th>
      <th>Pagado</th>
      <th>Por Pagar</th>
      <th>Valor recibido</th>
    </tr>
  </thead>
  <tbody>
    @foreach($remisiones as $factura)
      <tr id="{{$factura->id}}" @if($factura->nro==$id) class="active_table" @endif>
        <td><input type="hidden" name="factura_pendiente[]" value="{{$factura->id}}">
          {{$factura->nro}}</td>
          <td>{{$factura->fecha}}</td>
        <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->total()->total)}}</td>
        <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->pagado())}}</td>
        <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->porpagar())}}
        <input type="hidden" id="subfact{{$factura->id}}" value="{{$factura->total()->subtotal}}">
        <input type="hidden" id="totalfact{{$factura->id}}" value="{{App\Funcion::precision($factura->porpagar())}}">
        </td>
        <td class="monetario" style="vertical-align: text-bottom;">
          <input type="number" class="form-control form-control-sm" id="precio{{$factura->id}}" name="precio[]" placeholder="Valor" max="{{App\Funcion::precision($factura->porpagar())}}" maxlength="24" min="0" onkeyup="pre_retencion_calculate({{$factura->id}});">

          <p id="p_error_{{$factura->id}}" class="text-danger"></p>

        </td>
      </tr>
    @endforeach
  </tbody>
</table>
<div class="row" style="margin-top: 1%;">
      <div class="col-md-4 offset-md-8">
        <table class="text-right ingresos" id="totales-ingreso">
          <tr>
            <td width="40%">Subtotal</td>
              <input type="hidden" id="subtotal_facturas_js" value="0">
            <td>{{Auth::user()->empresa()->moneda}} <span id="subtotal">{{App\Funcion::Parsear(0)}}</span></td>
          </tr>
        </table>

        <table class="text-right ingresos" style="text-align: right; width: 100%;" id="fact_totalesreten">
          <tbody></tbody>
        </table>

        <table class="text-right ingresos">
          <tr>
            <td width="40%">TOTAL</td>
            <td>{{Auth::user()->empresa()->moneda}} <span id="total">{{App\Funcion::Parsear(0)}}</span></td>
          </tr>
        </table>
      </div>
    </div>

@else
<p class="text-warning text-center">El cliente seleccionado no tiene <b>remisiones</b> pendientes por pagar</p>
@endif
