<p>Nota: Solo apareceran en el recibo de caja las ingresos con valores recibidos</p>
<table class="table table-striped table-hover" id="table-facturas">
  <thead class="thead-dark">
    <tr>
      <th>Número</th>
      <th>Total</th>
      <th>Pagado</th>
      <th>Por Pagar</th>
      <th>Retenciones</th>
      <th>Valor recibido</th>
    </tr>
  </thead>
  <tbody>
    @php $id=$pagar=0; $porpagar=0; $entro=false; $retenciones=array();
    $total=0; 
    @endphp

    @foreach($facturas as $factura)

    @php $pagar=$factura->porpagar(); $value=''; $entro=false; $retenciones=array(); $porpagar=$id=0; @endphp
      @foreach($items as $item)
        @if ($factura->id==$item->factura) 
          @php  $id=$factura->id; $pagar=(float)$factura->porpagar()+(float)$item->pago;
          $value=$item->pago;  $entro=true; 
          $porpagar=(float)$item->pago+$item->retencion();
          $retenciones=$item->retenciones();
          break; 
          @endphp
        @endif

      @endforeach

      <tr id="{{$factura->id}}"  @if($factura->id==$id) class="active_table" @endif>

        <input type="hidden" id="retencion_previas_{{$factura->id}}" value="{{$factura->retenciones_previas_actual($id)}}">
        <input type="hidden" id="impuestos_factura_{{$factura->id}}" value="{{$factura->impuestos_totales()}}">

        <td><input type="hidden" name="factura_pendiente[]" value="{{$factura->id}}"><a href="{{route('facturas.show',$factura->nro)}}" target="_blank">{{$factura->codigo}}</a></td>
        <!--TOTAL-->
        <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->total()->total)}}</td>
        <!--PAGADO-->
        <td>

          {{Auth::user()->empresa()->moneda}}

          {{$entro?App\Funcion::Parsear($factura->pagado()+$factura->retenido()-$porpagar):App\Funcion::Parsear($factura->pagado()+$factura->retenido())}} 
        </td>
        <!--Por pagar-->
        <td>
          {{Auth::user()->empresa()->moneda}}{{$entro?App\Funcion::Parsear($factura->porpagar()+$porpagar):App\Funcion::Parsear($factura->porpagar())}}

        <input type="hidden" id="subfact{{$factura->id}}" value="{{$factura->total()->subtotal}}">
        <input type="hidden" id="descuento{{$factura->id}}" value="{{$factura->total()->descuento}}">
        <input type="hidden" id="totalfact{{$factura->id}}" value="{{$entro?$factura->porpagar()+$porpagar:$factura->porpagar()}}">
        </td>
        <td> 
          @php $total=$cont=0; @endphp
            <div id="retenciones_factura_{{$factura->id}}">
            @foreach($retenciones as $retencion)
                @php $cont+=1; $total=$retencion->valor==(float)(App\Funcion::Parsear($retencion->retencion*$value/100))?0:1; @endphp
                <div id="div_reten{{$factura->id}}_{{$cont}}" class="row no-padding">
                  <input type="hidden" name="fact{{$factura->id}}_nro_{{$cont}}" value="{{$retencion->id}}">
                  <div class="no-padding col-md-6">
                    <select class="form-control form-control-sm selectpicker no-padding" title="Seleccione" data-live-search="true" data-size="5" name="fact{{$factura->id}}_retencion[]" id="fact{{$factura->id}}_retencion{{$cont}}" required="" onchange="retencion_calculate({{$cont}}, this.value, true, 'fact{{$factura->id}}_', {{$factura->id}});">
                      @foreach($retencioness as $retenci)
                        <option value="{{$retenci->id}}" {{$retenci->id==$retencion->id_retencion?'selected':''}}>{{$retenci->nombre}} ({{$retenci->porcentaje}})%</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="monetario col-md-5 no-padding ">
                    <input type="number" required="" style="display: inline-block; width: 80%;" class="form-control form-control-sm" onchange="totales_ingreso();editmonto({{$factura->id}});" id="fact{{$factura->id}}_precio_reten{{$cont}}" name="fact{{$factura->id}}_precio_reten[]" placeholder="Valor retenido" maxlength="24"  min="0" required=""  disabled="disabled" value="{{$retencion->valor}}">
                  </div>
                  <div class="col-md-1 no-padding "><button type="button" class="btn btn-link btn-icons" onclick="Eliminar('div_reten{{$factura->id}}_{{$cont}}'); totales_ingreso();">X</button>
                  </div>
              </div>    
            @endforeach
          </div>
            @if($factura->retenido() == 0)
                <button class="btn btn-link btn-fw no-padding" type="button" onclick="crearDivRetentionFact({{$factura->id}});" style="margin-top: 1%;">Agregar Retención</button>
            @endif


        </td> 
        <td class="monetario" style="vertical-align: text-bottom;">
          <input type="hidden" id="editmonto{{$factura->id}}" value="1">
          <input type="text" class="form-control form-control-sm" id="precio{{$factura->id}}" name="precio[]" placeholder="Valor" value="{{$value}}" onkeyup="totales_ingreso();"> 
          <p id="p_error_{{$factura->id}}" class="text-danger"></p>

        </td>
      </tr>
    @endforeach
  </tbody>
</table>


<br>
{{-- FORMAS DE PAGO Y RETENCIONES PARA CUANDO ENTRA DINERO (RECIBO DE CAJA) POR UNA CATEGORIA --}}
<div class="row">
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
        @php $cont=0; $totalformas= 0; @endphp
          @foreach($formasPago as $forma) 

        @php $cont+=1; $totalformas+=$forma->debito; @endphp
          <tr id="forma{{$cont}}" fila="{{$cont}}">
            <td  class="no-padding">
                <select class="form-control form-control-sm selectpicker no-padding"  title="Seleccione" data-live-search="true" data-size="5" name="formapago[]" id="formapago{{$cont}}" onchange="llenarSelectAnticipo(this.value, $factura->cliente);" required="" >
                    @if($forma->recibocaja_id != null || $forma->enlace_a == 5)
                    <option value="0" selected>Agregar un anticipo</option>
                    @endif
                    @foreach($relaciones as $relacion)
                        <option value="{{$relacion->id}}" {{$relacion->id == $forma->formapago_id ? 'selected': ''}}>{{$relacion->codigo}} - {{$relacion->nombre}}</option>
                    @endforeach
                </select>
              </td>
              <td  class="no-padding" id="tdanticipo{{$cont}}">
                  <select class="form-control form-control-sm selectpicker no-padding"  title="Seleccione" data-live-search="true" data-size="5" name="selectanticipo[]" id="selectanticipo{{$cont}}">
                    @if($forma->recibocaja_id != null || $forma->enlace_a == 5)
                    @foreach($factura->recibosAnticipo(1) as $recibo)
                        @php $valorUsar = $recibo->saldoFavorUsado(); @endphp
                        <option value="{{$recibo->id}}" id="optionAnticipo{{$cont}}" precio="{{round($recibo->valor_anticipo+$valorUsar,4)}}" {{$recibo->id == $forma->recibocaja_id ? 'selected': ''}}>RC-{{$recibo->nro}} - {{round($recibo->valor_anticipo+$valorUsar,4)}}</option>
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

 
 <div class="row" style="margin-top: 1%;">
      <div class="col-md-4 offset-md-8">
        <table class="text-right ingresos" id="totales-ingreso">
          <tr>
            <td width="40%">Subtotal</td>

              <input type="hidden" id="subtotal_facturas_js" value="{{$ingreso->total()->total}}">
            <td>{{Auth::user()->empresa()->moneda}} <span id="subtotal">{{App\Funcion::Parsear($ingreso->total()->subtotal)}}</span></td> 
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
            <td>{{Auth::user()->empresa()->moneda}} <span id="total">{{App\Funcion::Parsear($ingreso->total()->total)}}</span></td>
          </tr>
        </table>
      </div>
    </div>
    <script type="text/javascript">
      $('.selectpicker').selectpicker();
    </script>
