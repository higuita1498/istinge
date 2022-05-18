@extends('layouts.app')
@section('content')  
  @if(Session::has('success'))
    <div class="alert alert-success" >
      {{Session::get('success')}}
    </div>

    <script type="text/javascript">
      setTimeout(function(){ 
          $('.alert').hide();
          $('.active_table').attr('class', ' ');
      }, 5000);
    </script>
  @endif
  @if(Session::has('danger'))
    <div class="alert alert-danger" >
      {{Session::get('danger')}}
    </div>
  @endif
	<form method="POST" action="{{ route('ingresos.store') }}" style="padding: 2% 3%;" role="form" class="forms-sample" id="form-ingreso" >
    @if($factura)
    <input type="hidden" id="factura" value="{{$factura}}">
    @endif
    <h5>INFORMACIÓN GENERAL DEL INGRESO </h5>
  		{{ csrf_field() }}
  		<div class="row" style=" text-align: right; margin-top: 5%">
  			<div class="col-md-5">
	  			<div class="form-group row">
	  				<label class="col-sm-4 col-form-label">Cliente </label>
		  			<div class="col-sm-8">
		  				<select class="form-control selectpicker" name="cliente" id="cliente" title="Seleccione" data-live-search="true" data-size="5" onchange="factura_pendiente(); saldoContacto(this.value)">
		  				@foreach($clientes as $clien)
		              		<option {{old('cliente')==$clien->id?'selected':''}} {{$cliente==$clien->id?'selected':''}} {{$pers==$clien->id?'selected':''}} value="{{$clien->id}}">{{$clien->nombre}} {{$clien->apellido1}} {{$clien->apellido2}} - {{$clien->nit}}</option>
		  				@endforeach
            	</select>
		  			</div>
		  			
					<span class="help-block error">
			        	<strong>{{ $errors->first('cliente') }}</strong>
			    </span>
	  		</div>  	
 
        <div class="form-group row occultrd">
          <label class="col-sm-4 col-form-label">Cuenta <span class="text-danger">*</span><a><i data-tippy-content="Crea tus cuentas haciendo <a href='#'>clíck aquí</a>" class="icono far fa-question-circle"></i></a></label>
          <div class="col-sm-8"> 
            <select class="form-control selectpicker" name="cuenta" id="cuenta" title="Seleccione" data-live-search="true" data-size="5" required="">
              @php $tipos_cuentas=\App\Banco::tipos();@endphp
              @foreach($tipos_cuentas as $tipo_cuenta)
                <optgroup label="{{$tipo_cuenta['nombre']}}">

                  @foreach($bancos as $cuenta)
                    @if($cuenta->tipo_cta==$tipo_cuenta['nro'])
                      <option value="{{$cuenta->id}}" {{$banco==$cuenta->nro?'selected':''}} {{$bank==$cuenta->nro?'selected':''}} selected>{{$cuenta->nombre}}</option>
                    @endif
                  @endforeach
                </optgroup>
              @endforeach
            </select>
            <span class="help-block error">
                  <strong>{{ $errors->first('cuenta') }}</strong>
            </span>
          </div>
          
        
      </div> 
      <div class="form-group row occultrd">
          <label class="col-sm-4 col-form-label">Método de pago </label>
          <div class="col-sm-8">
            <select class="form-control selectpicker" name="metodo_pago" id="metodo_pago" title="Seleccione" data-live-search="true" data-size="5">
              @foreach($metodos_pago as $metodo)
                    <option value="{{$metodo->id}}" @if(Auth::user()->id == 21) {{$metodo->id==9?'selected':''}}  @else {{$metodo->id==1?'selected':''}} @endif>{{$metodo->metodo}}</option>
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
              <option value="2" >Anticipo</option>
          </select>
        </div>
        
      <span class="help-block error">
            <strong>{{ $errors->first('realizar') }}</strong>
      </span>
    </div> 
    <div class="cls-realizar-inv">
    <div class="form-group row">
      <label class="col-sm-4 col-form-label">Forma de pago</label>
      <div class="col-sm-8">
        <select class="form-control selectpicker" name="forma_pago" id="forma_pago" title="Seleccione" data-live-search="true" data-size="5" onchange="showAnti()">
          @foreach($formas as $f)
          <option value="{{$f->id}}">{{$f->codigo}} - {{$f->nombre}}</option>
          @endforeach
        </select>
      </div>
      
    <span class="help-block error">
          <strong>{{ $errors->first('realizar') }}</strong>
    </span>
  </div> 
</div>
    <div class="form-group row cls-realizar d-none" >
       <div class="form-group row ">
      <label class="col-sm-4 col-form-label">Donde ingresa el dinero <span class="text-danger">*</span></label>
      <div class="col-sm-8">
        <select class="form-control selectpicker" name="puc" id="puc" title="Seleccione" data-live-search="true" data-size="5" required>
          @foreach($categorias as $categoria)
            <option value="{{$categoria->id}}" >{{$categoria->nombre}} - {{$categoria->codigo}}</option>
          @endforeach
        </select>
      </div>
      
    <span class="help-block error">
          <strong>{{ $errors->first('puc') }}</strong>
    </span>
       </div>
  </div> 
    <div class="form-group row cls-realizar d-none" >
      <div class="form-group row ">
    <label class="col-sm-4 col-form-label">Cuenta del anticipo <span class="text-danger">*</span></label>
    <div class="col-sm-8">
      <select class="form-control selectpicker" name="anticipo" id="anticipo" title="Seleccione" data-live-search="true" data-size="5" required>
        @foreach($anticipos as $anticipo)
          <option value="{{$anticipo->id}}" >{{$anticipo->nombre}} - {{$anticipo->codigo}}</option>
        @endforeach
      </select>
    </div>
    
  <span class="help-block error">
        <strong>{{ $errors->first('anticipo') }}</strong>
  </span>
      </div>
  </div> 
    <div class="cls-realizar d-none" >
      <div class="form-group row ">
        <label class="col-sm-4 col-form-label">Valor Recibido <span class="text-danger">*</span></label>
        <div class="col-sm-8">
          <input type="number" class="form-control" name="valor_recibido" id="valor_recibido" required>
        </div>
        
      <span class="help-block error">
            <strong>{{ $errors->first('valor_recibido') }}</strong>
      </span>
      </div>
    </div> 
      <div class="form-group row">
        <label class="col-sm-4 col-form-label">Fecha</label>
        <div class="col-sm-8">
          <input type="text" class="form-control datepicker"  id="fecha" value="{{date('d-m-Y')}}" name="fecha" disabled=""  >
        </div>
      </div>

                <div class="form-group row d-none">
                    <label class="col-sm-4 col-form-label">¿utilizar saldo a favor del ciente? <a><i
                                    data-tippy-content="Si está opcion te aparece es por que el cliente escogido tiene un saldo a favor y puedes pagar las facturas con ese saldo."
                                    class="icono far fa-question-circle"></i></a></label>
                    <div class="col-sm-8">
                        <div class="form-group row">
                            <div class="col-sm-4">
                                <div class="form-radio">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" name="saldo" id="publico1"
                                               value="1" onchange="hidedivtwo('occultrd');"> Si
                                        <i class="input-helper"></i><i class="input-helper"></i></label>
                                </div>
                            </div>

                            <div class="col-sm-4">
                                <div class="form-radio">
                                    <label class="form-check-label">
                                        <input type="radio" class="form-check-input" name="saldo" id="publico"
                                               value="0" onchange="showdivtwo('occultrd');" checked=""> No
                                        <i class="input-helper"></i><i class="input-helper"></i></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

        <style type="text/css">
          .form-radio label input + .input-helper:before{
            border:1px solid #000;
          }
        </style>

      
<div class="col-md-12" style="background: #80808061;border: 1px solid #80808061; display: none;" id="saldo123">
    <div class="row">
      <div class="col-md-4 text-right" style="    padding: 4%; font-weight: bold; color:#808080 ">Saldo</div>
      {{--<div class="col-md-8 text-left text-danger" style="padding: 4%; font-weight: bold;">$-9,104,265</div>--}}
        <input class="col-md-8 text-left text-danger" style="padding: 4%; font-weight: bold" name="total_saldo" id="total_saldo" type="text" value="0" disabled>
    </div>
  </div>
      
      
		</div>
		<div class="col-md-5 offset-md-2">
		    <div class="form-group row">
                      <label class="col-sm-4 col-form-label">Nro</label>
                      <div class="col-sm-8">
                          <input type="text" class="form-control" value="{{$numero}}" readonly disabled>
                      </div>
                  </div>
    			<div class="form-group row">
          <label class="col-sm-4 col-form-label">Observaciones</label>
          <div class="col-sm-8">
            <textarea  class="form-control min_max_100" name="observaciones"></textarea>
          </div>
  			</div>

        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Notas del recibo <small>Visibles al imprimir</small></label>
          <div class="col-sm-8">
            <textarea  class="form-control min_max_100" name="notas"></textarea>
          </div>
        </div>
        <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
  		</div>

  

  		</div>

    <h5>TIPO DE TRANSACCIÓN</h5>
    <div class="row cls-realizar-inv" style=" margin-top: 5%; text-align: center;">
      <div class="col-md-12">
        <h6>¿Asociar este ingreso a una factura de venta existente? <a><i data-tippy-content="<font color='#d08f50'>Si</font> para cancelar o abonar facturas <br><font color='#d08f50'>No</font> para registrar otros ingresos" class="icono far fa-question-circle"></i></a></h6>
        <p>Recuerda que puedes registrar un ingreso sin necesidad de que esté asociado a una factura de venta</p>
        <div class="row">
          <div class="col-sm-1 offset-sm-5">
          <div class="form-radio">
            <label class="form-check-label">
            <input type="radio" class="form-check-input" name="tipo" id="tipo1" value="1" onchange="showdiv('si'); hidediv('no'); factura_pendiente();" {{$factura?'checked':'' }}> Si
            <i class="input-helper"></i></label>
          </div>
        </div>
        <div class="col-sm-1">
          <div class="form-radio">
            <label class="form-check-label">
            <input type="radio" class="form-check-input" name="tipo" id="tipo" value="2" onchange="showdiv('no');  hidediv('si');"> No
            <i class="input-helper"></i></label>
          </div>
        </div>
        </div>
      </div>
    </div>
  		<div class="row cls-realizar-inv">
        <div class="col-md-12 fact-table" id="si" style="display: none;">
          <h5>FACTURAS DE VENTA PENDIENTES</h5>
          <div id="factura_pendiente"></div>
        </div>
  			<div class="col-md-12 fact-table" id="no" style="display: none;">
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
            		<tr id="1">
                  <td  class="no-padding">
                      <div class="resp-item">                     
                    <select class="form-control form-control-sm selectpicker no-padding"  title="Seleccione" data-live-search="true" data-size="5" name="categoria[]" id="categoria1" required="" onchange="enabled(1);" >
              				@foreach($categorias as $categoria)
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
                      @endforeach
              			</select>
              			</div>
					  			</td>
            			<td class="monetario">
            			    <div class="resp-precio">
  				          <input type="number" class="form-control form-control-sm" id="precio_categoria1" name="precio_categoria[]" placeholder="Precio" onchange="total_linea(1)" maxlength="24" min="0" required="" disabled="">
	  			        </div>
	  			        </td>
            			<td>
                    <select class="form-control form-control-sm selectpicker" name="impuesto_categoria[]" id="impuesto_categoria1" title="Impuesto" onchange="total_categorias();" required="" disabled="">
                        @foreach($impuestos as $impuesto)
                          <option value="{{$impuesto->id}}" porc="{{$impuesto->porcentaje}}">{{$impuesto->nombre}} - {{$impuesto->porcentaje}}%</option>
                        @endforeach
                    </select>
	  			        </td>
                  <td width="5%">
                    <input type="number" class="form-control form-control-sm" id="cant_categoria1" name="cant_categoria[]" placeholder="Cantidad" onchange="total_linea(1);" min="1" required="" disabled="">
                  </td>
            			<td  style="padding-top: 1% !important;">   
            			<div class="resp-observaciones">
						        <textarea  class="form-control form-control-sm" id="descripcion_categoria1" name="descripcion_categoria[]" placeholder="Observaciones" disabled=""></textarea>
            			</div>
            			</td>
            			<td>
            			    <div class="resp-total">
	  				        <input type="text" class="form-control form-control-sm text-right" id="total_categoria1" value="0.00" disabled="">	
        	  			    </div>
        	  			</td>
	  			      <td><button type="button" class="btn btn-outline-secondary btn-icons" onclick="Eliminar(1);">X</button></td>
          		</tr>
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
              </tbody>
            </table>
            <button class="btn btn-outline-primary" onclick="CrearFilaRetencion();" type="button" style="margin-top: 2%;">Agregar Retención</button>
          </div>

      <div class="row" style="margin-top: 5%;">
        <div class="col-md-4 offset-md-8">
          <table style="text-align: right;  width: 100%;" id="totales">
            <tr>
              <td width="40%">Subtotal</td>
              <input type="hidden" id="subtotal_categoria_js" value="0">
              <input type="hidden" id="impuestos_categoria_js" value="0">
              <td>{{Auth::user()->empresa()->moneda}} <span id="subtotal_categoria">0</span></td>
            </tr>
          </table>
          <table style="text-align: right; width: 100%;" id="totalesreten">
            <tbody></tbody>
          </table>
          <hr>
          <table style="text-align: right; font-size: 24px !important; width: 100%;">
            <tr>
              <td width="40%">TOTAL</td>
              <td>{{Auth::user()->empresa()->moneda}} <span id="total_categoria">0</span></td>
            </tr>
          </table>
        </div>
        </div> 
      </div>
  		</div>
</div>
  		
  		<hr>

      <div class="row">
        <div class="col-md-6">
          <div class="form-group cls-anticipo d-none">
                <div class="form-group">
              <label class="col-form-label">Donde ingresa el dinero <span class="text-danger">*</span></label>

            
                <select class="form-control selectpicker" name="puc_banco" id="puc_banco" title="Seleccione" data-live-search="true" data-size="5" required>
                  @foreach($formas as $f)
                    <option value="{{$f->id}}" >{{$f->nombre}} - {{$f->codigo}}</option>
                  @endforeach
                </select>
              
            <span class="help-block error">
                  <strong>{{ $errors->first('puc_banco') }}</strong>
            </span>
                </div>
          </div> 
        </div>
        <div class="col-md-6">
          <div class="form-group cls-anticipo d-none">
              <div class="form-group">

                <label class="col-form-label">Cuenta del anticipo <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" name="anticipo_factura" id="anticipo_factura" title="Seleccione" data-live-search="true" data-size="5" required>
                  @foreach($anticipos as $anticipo)
                    <option value="{{$anticipo->id}}" >{{$anticipo->nombre}} - {{$anticipo->codigo}}</option>
                  @endforeach
                </select>

                <span class="help-block error">
                      <strong>{{ $errors->first('anticipo_factura') }}</strong>
                </span>
              </div>
          </div> 
        </div>
      </div>

  		<div class="row" >
        
        <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">

          <a href="{{route('ingresos.index')}}" class="btn btn-outline-secondary">Cancelar</a>
          <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success" id="button-guardar">Guardar</button>
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

@section('scripts')
<script src="{{asset('lowerScripts/ingreso/ingreso.js')}}"></script>
@endsection