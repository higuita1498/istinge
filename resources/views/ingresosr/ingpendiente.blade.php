<p>Nota: Solo apareceran en el recibo de caja las ingresos con valores recibidos</p>
<table class="table table-striped table-hover" id="table-facturas">
  <thead class="thead-dark">
    <tr>
      <th>Número</th>
      <th>Total</th>
      <th>Pagado</th>
      <th>Por Pagar</th>
      <th>Valor recibido</th>
    </tr>
  </thead>
  <tbody>
    @php $id=$pagar=0; $porpagar=0; $entro=false; $retenciones=array();@endphp
    @foreach($remisiones as $factura)
    @php $pagar=$factura->porpagar(); $value=''; $entro=false; $retenciones=array(); $porpagar=$id=0; @endphp
      @foreach($items as $item)
        @if ($factura->id==$item->remision) 
          @php  $id=$factura->id; $pagar=(float)$factura->porpagar()+(float)$item->pago;
          $value=$item->pago;  $entro=true; 
          $porpagar=(float)$item->pago;
          break; 
          @endphp
        @endif

      @endforeach
      @php $pagar=$entro?App\Funcion::precision($factura->porpagar()+$porpagar):App\Funcion::precision($factura->porpagar()) @endphp
      <tr id="{{$factura->id}}"  @if($factura->id==$id) class="active_table" @endif>
        <td><input type="hidden" name="factura_pendiente[]" value="{{$factura->id}}">{{$factura->nro}}</td>
        <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->total()->total)}}</td>
        <td>{{Auth::user()->empresa()->moneda}}{{$entro?App\Funcion::Parsear($factura->pagado()-$porpagar):App\Funcion::Parsear($factura->pagado())}} </td>
        <td>{{Auth::user()->empresa()->moneda}}{{$entro?App\Funcion::Parsear($factura->porpagar()+$porpagar):App\Funcion::Parsear($factura->porpagar())}}

        <input type="hidden" id="subfact{{$factura->id}}" value="{{App\Funcion::precision($factura->total()->subtotal)}}">
        <input type="hidden" id="totalfact{{$factura->id}}" value="{{App\Funcion::precision($pagar)}}">
        </td>
        <td class="monetario" style="vertical-align: text-bottom;">
          <input type="text" class="form-control form-control-sm" id="precio{{$factura->id}}" name="precio[]" placeholder="Valor" max="{{App\Funcion::precision($pagar)}}" min="0" value="{{App\Funcion::precision($value)}}" onkeyup="totales_ingreso();"> 
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
              <input type="hidden" id="subtotal_facturas_js" value="{{$ingreso->total()->total}}">
            <td>{{Auth::user()->empresa()->moneda}} <span id="subtotal">{{App\Funcion::Parsear($ingreso->total()->total)}}</span></td> 
          </tr>
        </table>
        <table class="text-right ingresos" style="text-align: right; width: 100%;" id="fact_totalesreten">
          <tbody>
            @php $cont=0; @endphp
              @if($ingreso->total()->reten)
              @foreach($ingreso->total()->reten as $reten)
                  @if(isset($reten->total))  
                     <tr id="fact_retentotal{{$cont}}"><td width="40%" style="font-size: 0.8em;">{{$reten->nombre}} ({{$reten->porcentaje}}%)</td><td id="fact_retentotalvalue{{$cont}}">-{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($reten->total)}}</td></tr>               
                    @php $cont+=1; @endphp
                  @endif
              @endforeach
              @endif

            

          </tbody>
        </table>
        <table class="text-right ingresos">
          <tr> 
            <td width="40%">TOTAL</td>
            <td>{{Auth::user()->empresa()->moneda}} <span id="total">{{App\Funcion::Parsear($ingreso->total()->subtotal)}}</span></td>
          </tr>
        </table>
      </div>
    </div>
    <script type="text/javascript">
      $('.selectpicker').selectpicker();
    </script>