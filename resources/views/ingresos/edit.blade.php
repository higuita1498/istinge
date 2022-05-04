@extends('layouts.app')
@section('content')
	<form method="POST" action="{{ route('ingresos.update', $ingreso->nro) }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-ingreso" >
    <h5>INFORMACIÓN GENERAL DEL INGRESO </h5>
  		{{ csrf_field() }} 
      <input name="_method" type="hidden" value="PATCH">
      <input type="hidden" id="ingreso" value="{{$ingreso->nro}}">
  		<div class="row" style=" text-align: right; margin-top: 5%">
  			<div class="col-md-5">
	  			<div class="form-group row">
	  				<label class="col-sm-4 col-form-label">Cliente </label>
		  			<div class="col-sm-8">
		  				<select class="form-control selectpicker" name="cliente" id="cliente" title="Seleccione" data-live-search="true" data-size="5" onchange="factura_pendiente();">
		  				@foreach($clientes as $clien)
		              		<option {{$ingreso->cliente==$clien->id?'selected':''}}  value="{{$clien->id}}">{{$clien->nombre}} - {{$clien->nit}}</option>
		  				@endforeach
		            	</select>
		  			</div>
		  			
					<span class="help-block error">
			        	<strong>{{ $errors->first('cliente') }}</strong>
			    </span>
	  		</div>  	

        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Cuenta <span class="text-danger">*</span></label>
          <div class="col-sm-8">
            <select class="form-control selectpicker" name="cuenta" id="cuenta" title="Seleccione" data-live-search="true" data-size="5" required="">
              @php $tipos_cuentas=\App\Banco::tipos();@endphp
              @foreach($tipos_cuentas as $tipo_cuenta)
                <optgroup label="{{$tipo_cuenta['nombre']}}">

                  @foreach($bancos as $cuenta)
                    @if($cuenta->tipo_cta==$tipo_cuenta['nro'])
                      <option value="{{$cuenta->id}}" {{$ingreso->cuenta==$cuenta->id?'selected':''}}>{{$cuenta->nombre}}</option>
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
                    <option value="{{$metodo->id}}"  {{$ingreso->metodo_pago==$metodo->id?'selected':''}}>{{$metodo->metodo}}</option>
                @endforeach
            </select>
          </div>
          
        <span class="help-block error">
              <strong>{{ $errors->first('metodo_pago') }}</strong>
        </span>
      </div> 
      <div class="form-group row">
        <label class="col-sm-4 col-form-label">Realizar un</label>
        <div class="col-sm-8">
          <select class="form-control selectpicker" name="realizar" id="realizar" title="Seleccione" data-live-search="true" data-size="5" onchange="showAnti()">
              <option value="1" selected>Pago a Factura o Categoría</option>
              <option value="2" {{$ingreso->anticipo == 1 ? 'selected' : ''}}>Anticipo</option>
          </select>
        </div>
        
      <span class="help-block error">
            <strong>{{ $errors->first('realizar') }}</strong>
      </span>
    </div> 
    <div class="form-group row cls-realizar d-none" >
       <div class="form-group row ">
      <label class="col-sm-4 col-form-label">Donde ingresa el dinero</label>
      <div class="col-sm-8">
        <select class="form-control selectpicker" name="puc" id="puc" title="Seleccione" data-live-search="true" data-size="5">
          @foreach($categorias as $categoria)
            <option value="{{$categoria->id}}" >{{$categoria->nombre}} {{$categoria->codigo}}</option>
          @endforeach
        </select>
      </div>
      
    <span class="help-block error">
          <strong>{{ $errors->first('puc') }}</strong>
    </span>
       </div>
  </div> 
    <div class="cls-realizar d-none" >
      <div class="form-group row ">
        <label class="col-sm-4 col-form-label">Valor Recibido</label>
        <div class="col-sm-8">
          <input type="number" class="form-control" name="valor_recibido" id="valor_recibido">
        </div>
        
      <span class="help-block error">
            <strong>{{ $errors->first('valor_recibido') }}</strong>
      </span>
      </div>
    </div> 
      <div class="form-group row">
        <label class="col-sm-4 col-form-label">Fecha</label>
        <div class="col-sm-8">
          <input type="text" class="form-control datepicker"  id="fecha" value="{{date('d-m-Y', strtotime($ingreso->fecha))}}" name="fecha" disabled=""  >
        </div>
      </div>
		</div>
		<div class="col-md-6 offset-md-1">
  			<div class="form-group row">
          <label class="col-sm-4 col-form-label text-right">Recibo de caja #</label>
          <div class="col-sm-8  text-left">
            <input type="text" readonly="" value="{{$ingreso->nro}}">
          </div>
  			</div>
  			<div class="form-group row">
          <label class="col-sm-4 col-form-label">Observaciones</label>
          <div class="col-sm-8">
            <textarea  class="form-control min_max_100" name="observaciones">{{$ingreso->observaciones}}</textarea>
          </div>
  			</div>

        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Notas del recibo <small>Visibles al imprimir</small></label>
          <div class="col-sm-8">
            <textarea  class="form-control min_max_100" name="notas">{{$ingreso->notas}}</textarea>
          </div>
        </div>
        <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
  		</div>

  

  		</div>

    <h5>TIPO DE TRANSACCIÓN</h5>
    <div class="row" style=" margin-top: 5%; text-align: center;">
      <div class="col-md-12">
        <h6>¿Asociar este ingreso a una factura de venta existente en SCA?</h6>
        <p>Recuerda que puedes registrar un ingreso sin necesidad de que esté asociado a una factura de venta</p>
        <div class="row">
          <div class="col-sm-1 offset-sm-5">
          <div class="form-radio">
            <label class="form-check-label">
            <input type="radio" class="form-check-input" name="tipo" id="tipo1" value="1" onchange="showdiv('si'); hidediv('no'); factura_pendiente('{{$ingreso->nro}}');" {{$ingreso->tipo==1?'checked':'' }} > Si
            <i class="input-helper"></i></label>
          </div>
        </div>
        <div class="col-sm-1">
          <div class="form-radio">
            <label class="form-check-label">
            <input type="radio" class="form-check-input" name="tipo" id="tipo" value="2" onchange="showdiv('no');  hidediv('si');" {{$ingreso->tipo==2?'checked':'' }}> No
            <i class="input-helper"></i></label>
          </div>
        </div>
        </div>
      </div>
    </div>
  		<div class="row">
        <div class="col-md-12" id="si" style="display: none;">
          <h5>FACTURAS DE VENTA PENDIENTES</h5>
          <div id="factura_pendiente"></div>
        </div>
  			<div class="col-md-12 fact-table" id="no" @if($ingreso->tipo!=2) style="display: none;"@endif >
          <h5>¿A QUÉ CATEGORÍA(S) PERTENECE ESTE INGRESO?</h5>  
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
                @if($ingreso->tipo==2)
                @php $cont=0; @endphp
                @foreach($items as $item)
                @php $cont+=1; @endphp
                  <tr id="{{$cont}}">
                    <td  class="no-padding">  
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
                    <td class="monetario">
                      <input type="number" class="form-control form-control-sm" id="precio_categoria{{$cont}}" name="precio_categoria[]" placeholder="Precio" min="0" onkeyup="total_linea({{$cont}})" required="" value="{{$item->valor}}">
                    </td>
                    <td>
                      <select class="form-control form-control-sm selectpicker" name="impuesto_categoria[]" id="impuesto_categoria{{$cont}}" title="Impuesto" onchange="total_categorias();" required="">
                          @foreach($impuestos as $impuesto)
                            <option value="{{$impuesto->id}}" porc="{{$impuesto->porcentaje}}" {{$impuesto->id==$item->id_impuesto?'selected':''}}>{{$impuesto->nombre}} - {{$impuesto->porcentaje}}%</option>
                          @endforeach
                      </select>
                    </td>
                    <td width="5%">
                      <input type="number" class="form-control form-control-sm" id="cant_categoria{{$cont}}" name="cant_categoria[]" placeholder="Cantidad" onchange="total_linea({{$cont}});" min="1" required="" value="{{round($item->cant,4)}}">
                    </td>
                    <td  style="padding-top: 1% !important;">                           
                      <textarea  class="form-control form-control-sm" id="descripcion_categoria{{$cont}}" name="descripcion_categoria[]" placeholder="Observaciones">{{$item->descripcion}}</textarea>
                    </td>
                    <td>
                      <input type="text" class="form-control form-control-sm text-right" id="total_categoria{{$cont}}" value="{{App\Funcion::Parsear($item->total())}}" disabled="">  
                    </td>
                  <td><button type="button" class="btn btn-outline-secondary btn-icons" onclick="Eliminar({{$cont}}); total_categorias(); ">X</button></td> 
                </tr>
                @endforeach
               @endif 
            </tbody>
          </table>
          <button class="btn btn-outline-primary" onclick="CrearFilaCategorias();" type="button" style="margin-top: 5%; margin-bottom: 1%;">Agregar línea</button>

          <h5>¿ TE APLICARON ALGUNA RETENCIÓN ?</h5>
          <div class="col-md-7 no-padding">
            <table class="table table-striped table-sm" id="table-retencion">
              <thead class="thead-dark">
                <th width="60%">Tipo de Retención</th>
                <th width="34%">Valor</th>
                <th width="5%"></th>
              </thead>
              <tbody>
                @php $total=$cont=0; @endphp 
                @foreach($retencionesIngreso as $retenido)
                <tr  id="reten{{$cont}}"> 
                  <td  class="no-padding">                          
                    <select class="form-control form-control-sm selectpicker no-padding"  title="Seleccione" data-live-search="true" data-size="5" name="retencion[]" id="retencion{{$cont}}" required="" onchange="retencion_calculate({{$cont}}, this.value);" >

                        @foreach($retenciones as $retencion)
                                <option {{$retencion->id==$retenido->id_retencion?'selected':''}}  value="{{$retencion->id}}">{{$retencion->nombre}} ({{$retencion->porcentaje}}%)</option>
                        @endforeach
                    </select>
                  </td>
                  <td class="monetario">
                    @php $block=(App\Funcion::precision($retenido->retencion*$ingreso->total()->subtotal/100)==$retenido->valor?0:1); @endphp 
                    <input type="hidden" name="reten{{$cont}}" value="{{$retenido->id}}">
                    <input type="hidden" value='{{$block}}' id="lock_reten{{$cont}}">
                    <input type="monetario" style="display: inline-block; width: 80%;" class="form-control form-control-sm" onkeyup="total_categorias()" id="precio_reten{{$cont}}" name="precio_reten[]" placeholder="Valor retenido" onkeyup="total_linea({{$cont}})" required="" value="{{$retenido->valor}}" @if($block==0) disabled="" @endif>
                  </td>
                  <td>
                    <button type="button" class="btn btn-outline-secondary btn-icons" onclick="Eliminar('reten{{$cont}}'); total_categorias();">X</button>
                  </td>
                </tr>
                @php $cont+=1; @endphp
                @endforeach
              </tbody>
            </table>
            <button class="btn btn-outline-primary" onclick="CrearFilaRetencion();" type="button" style="margin-top: 2%;">Agregar Retención</button>
          </div>

      <div class="row" style="margin-top: 5%;">
        <div class="col-md-4 offset-md-8">
          <table style="text-align: right;  width: 100%;" id="totales">
            <tr>
              <td width="40%">Subtotal</td>

              <input type="hidden" id="subtotal_categoria_js" value="{{$ingreso->total()->subtotal}}">
              <input type="hidden" id="impuestos_categoria_js" value="{{$ingreso->total()->ivas}}">

              <td>{{Auth::user()->empresa()->moneda}} <span id="subtotal_categoria"> {{App\Funcion::Parsear($ingreso->total()->subtotal)}}</span></td>
            </tr>
            @php $cont=0; @endphp
            @if($ingreso->total()->imp)
            @foreach($ingreso->total()->imp as $imp)
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
              @if($ingreso->total()->reten)
              @foreach($ingreso->total()->reten as $reten)
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
              <td>{{Auth::user()->empresa()->moneda}} <span id="total_categoria">{{App\Funcion::Parsear($ingreso->total()->total)}}</span></td>
            </tr>
          </table>
          <p id="p_rentencion" class="text-danger"></p>
        </div>
        </div> 
      </div>
  		</div>
</div>
  		
  		<hr>
  		<div class="row" >
        
        <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">

          <a href="{{route('ingresos.index')}}" class="btn btn-outline-secondary">Cancelar</a>
          <button type="submit" class="btn btn-success" id="button-guardar">Guardar</button>
        </div>
  		</div>


  	</form>
  	<input type="hidden" id="url" value="{{url('/')}}">
    <input type="hidden" id="impuestos" value="{{json_encode($impuestos)}}">
    <input type="hidden" id="retenciones" value="{{json_encode($retenciones)}}">
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
@endsection