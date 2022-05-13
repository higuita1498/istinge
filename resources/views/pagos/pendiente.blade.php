@if($total>0)
<input type="hidden" id="errores" value="">
<p>Nota: Solo apareceran en el recibo de caja las facturas con valores recibidos</p>
<table class="table table-striped table-hover" id="table-facturas">
  <thead class="thead-dark">
    <tr>
      <th>Número</th>
      <th>Fecha creación</th>
      <th>Fecha vencimiento</th>
      <th>Total</th>
      <th>Pagado</th>
      <th>Por Pagar</th>
      <th width="35%">Retenciones</th>
      <th>Valor recibido</th>
    </tr>
  </thead>
  <tbody>
    @foreach($facturas as $factura)
      <tr id="{{$factura->id}}" @if($factura->nro==$id) class="active_table" @endif>
        <input type="hidden" id="impuestos_factura_{{$factura->id}}" value="{{$factura->impuestos_totales()}}">        
        <input type="hidden" id="retencion_previas_{{$factura->id}}" value="{{$factura->retencions_previas()}}">
        <td>
          <input type="hidden" name="factura_pendiente[]" value="{{$factura->id}}">
          <a href="{{route('facturasp.show',$factura->id)}}" target="_blank">{{$factura->codigo?$factura->codigo:'Factura: '.date('d-m-Y', strtotime($factura->fecha_factura))}}</a>
        </td>
        <td>{{date('d-m-Y', strtotime($factura->fecha_factura))}}</td>
        <td>{{date('d-m-Y', strtotime($factura->vencimiento_factura))}}</td>
        <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->total()->total)}}</td>
        <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->pagado())}}</td>
        <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->porpagar())}}
          <input type="hidden" id="subfact{{$factura->id}}" value="{{$factura->total()->subtotal}}">
          <input type="hidden" id="totalfact{{$factura->id}}" value="{{$factura->porpagar()}}">
          <input type="hidden" id="descuento{{$factura->id}}" value="{{$factura->total()->descuento}}">
        </td>
        <td>
          <div id="retenciones_factura_{{$factura->id}}">
            
          </div>
          <button class="btn btn-link btn-fw no-padding" type="button" onclick="crearDivRetentionFact({{$factura->id}});" style="margin-top: 1%;">Agregar Retención</button>
        </td> 
        <td class="monetario" style="vertical-align: text-bottom;"> 
          <input type="hidden" id="editmonto{{$factura->id}}" value="1">
          <input type="number" class="form-control form-control-sm precio" id="precio{{$factura->id}}" name="precio[]" placeholder="Valor" max="{{$factura->total()->total}}" maxlength="24" onchange="totales_ingreso();" onkeyup=" editmonto({{$factura->id}});" min="0">

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
<p class="text-warning text-center">El cliente seleccionado no tiene <b>facturas de compras</b> registradas</p>
@endif
