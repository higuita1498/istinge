@if($total>0)
<style>
    .active_table{
        background: #5a5eda30 !important;
    }
</style>
<input type="hidden" id="errores" value="">
<input type="hidden" id="cant_facturas" name="cant_facturas" value="{{ $total }}">
<p>Nota: Solo apareceran en el recibo de caja las facturas con valores recibidos</p>
<table class="table table-striped table-hover" id="table-facturas">
  <thead class="thead-dark">
    <tr>
      <th class="text-center">Factura</th>
      <th class="text-center">F. Creación</th>
      <th class="text-center">F. Vencimiento</th>
      <th class="text-center">Total</th>
      <th class="text-center">Pagado</th>
      <th class="text-center">Por Pagar</th>
      <th width="35%">Retenciones</th>
      <th class="text-center">Monto Recibido</th>
    </tr>
  </thead>
  <tbody>
    @php $count = count($facturas); @endphp
    
    @foreach($facturas as $factura)
      <tr id="{{$factura->id}}" @if($factura->nro==$id || $count == 1) class="active_table" @endif>
        <input type="hidden" id="retencion_previas_{{$factura->id}}" value="{{$factura->retenciones_previas()}}">
        <input type="hidden" id="impuestos_factura_{{$factura->id}}" value="{{$factura->impuestos_totales()}}">

        <td class="text-center"><input type="hidden" name="factura_pendiente[]" value="{{$factura->id}}"><a href="{{route('facturas.show',$factura->id)}}" target="_blank">{{$factura->codigo}}</a></td>
        <td class="text-center">{{date('d-m-Y', strtotime($factura->fecha))}}</td>
        <td class="text-center">{{date('d-m-Y', strtotime($factura->vencimiento))}}</td>
        <td class="text-center">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->total()->total)}}</td>
        <td class="text-center">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->pagado())}}</td>
        <td class="text-center">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->porpagar())}}
          <input type="hidden" id="subfact{{$factura->id}}" value="{{$factura->total()->subtotal}}">
          <input type="hidden" id="descuento{{$factura->id}}" value="{{$factura->total()->descuento}}">
          <input type="hidden" id="totalfact{{$factura->id}}" value="{{$factura->porpagar()}}">
        </td>
        <td>
          <div id="retenciones_factura_{{$factura->id}}">
            
          </div>
          <button class="btn btn-link btn-fw no-padding" type="button" onclick="crearDivRetentionFact({{$factura->id}});" style="margin-top: 1%;">Agregar Retención</button>
        </td> 
        <td class="monetario text-center" style="vertical-align: text-bottom;"> 
          <input type="hidden" id="editmonto{{$factura->id}}" value="1">
          <input type="number" class="form-control form-control-sm" id="precio{{$factura->id}}" name="precio[]" placeholder="Valor" maxlength="24" onchange="totales_ingreso();" onkeyup="editmonto({{$factura->id}});" min="0" value="@if($count == 1){{$factura->total()->total}}@endif">

          <p id="p_error_{{$factura->id}}" class="text-danger"></p>

        </td>
      </tr>
      
      @if($total>1)
      <tr>
        <td class="text-center font-weight-bold" colspan="3">PAGO ADICIONAL POR SERVICIO DE RECONEXIÓN</td>
        <td class="text-center">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear(10000)}}</td>
        <td class="text-center">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear(0)}}</td>
        <td class="text-center">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear(10000)}}</td>
        <td></td> 
        <td class="monetario text-center" style="vertical-align: text-bottom;">
          <input type="number" class="form-control form-control-sm" max="10000" maxlength="24" min="10000" value="10000" readonly>
        </td>
      </tr>
      @endif
      @php $count++; @endphp
    @endforeach
  </tbody>
</table>
@if(isset($factura))
    <br>
    {{-- FORMAS DE PAGO Y RETENCIONES PARA CUANDO ENTRA DINERO (RECIBO DE CAJA) POR UNA CATEGORIA --}}
    <div class="row saldofavorcreate">
      <div class="col-md-5 no-padding">
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
              </tbody>
            </table>
            <div class="row">
              <div class="col-md-6">
                <button class="btn btn-outline-primary" onclick="CrearFilaFormaPago();" type="button" style="margin-top: 2%;">Agregar forma de pago</button><a><i data-tippy-content="Agrega nuevas formas de pago haciendo <a href='#'>clíck aquí</a>" class="icono far fa-question-circle"></i></a>
              </div>
              <div class="col-md-6 d-flex justify-content-between pt-3">
                <h5>Total:</h5>
                <span>$</span><span id="anticipototal">0</span>  
              </div>
              <div class="col-md-12">
                <span class="text-danger" style="font-size:12px"><strong>El total de las formas de pago debe coincidir con el total neto</strong></span>
              </div>
            </div>
        </div>
    </div>

<div class="row" style="margin-top: 1%;">
      <div class="col-md-4 offset-md-8">
        <table class="text-right ingresos" id="totales-ingreso">
          <tr>
            <td width="75%">Subtotal</td>
              <input type="hidden" id="subtotal_facturas_js" value="0">
            <td>{{Auth::user()->empresa()->moneda}} <span id="subtotal">{{$factura->total()->total}}</span></td>
          </tr>
        </table>

        <table class="text-right ingresos" style="text-align: right; width: 100%;" id="fact_totalesreten">
          <tbody></tbody>
        </table>
        @if($total>1)
        <table class="text-right ingresos">
          <tr>
            <td width="75%">Reconxeión</td>
            <td>{{Auth::user()->empresa()->moneda}} 10000</td>
          </tr>
        </table>
        @endif
        <input type="hidden" name="saldofavor" id="saldofavor" value="0">
        <table class="text-right ingresos">
          <tr>
            <td width="75%">TOTAL</td>
            <td>{{Auth::user()->empresa()->moneda}} <span id="total">@if($total>1){{$factura->total()->total+10000}} @else {{$factura->total()->total}} @endif</span></td>
          </tr>
        </table>
      </div>
    </div>
@endif
@else
<p class="text-warning text-center">El cliente seleccionado no tiene <b>facturas de venta</b> pendientes por pagar</p>
@endif
