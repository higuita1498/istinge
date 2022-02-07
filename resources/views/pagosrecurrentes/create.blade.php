@extends('layouts.app')
@section('content')
	<form method="POST" action="{{ route('pagosrecurrentes.store') }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-factura" >
  		{{ csrf_field() }}
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
                        <option value="{{$cuenta->id}}">{{$cuenta->nombre}}</option>
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
                      <option value="{{$metodo->id}}">{{$metodo->metodo}}</option>
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
              <textarea  class="form-control min_max_100" name="observaciones"></textarea>
            </div>
          </div>

	  			 	
 
        
      
		</div>
		<div class="col-md-6 offset-md-1">
      <div class="form-group row">
            <label class="col-sm-4 col-form-label">Beneficiario </label>
            <div class="col-sm-8">
              <select class="form-control selectpicker" name="beneficiario" id="cliente" title="Seleccione" data-live-search="true" data-size="5" onchange="factura_pendiente();">
              @foreach($beneficiarios as $clien)
                      <option {{old('cliente')==$clien->id?'selected':''}} value="{{$clien->id}}">{{$clien->nombre}} - {{$clien->nit}}</option>
              @endforeach
              </select>
            </div>
            
          <span class="help-block error">
                <strong>{{ $errors->first('cliente') }}</strong>
          </span>
        </div> 

           {{--<div class="form-group row" >
          <label class="col-sm-4 col-form-label" >¿Pago recurrente mensualmente? <a><i data-tippy-content="Si elige si, escoja el dia en especifico que se hará el cobro mensualmente." class="icono far fa-question-circle"></i></a></label>
            <div class="col-sm-8">
                <div class="form-group row">
          <div class="col-sm-4">
              <div class="form-radio">
                <label class="form-check-label">
                <input type="radio" class="form-check-input" name="fecha_fija" id="fecha_fija" value="1" onchange="hidedivtwopago('occultrd');"> Si
                <i class="input-helper"></i><i class="input-helper"></i></label>
              </div>
              </div>

              <div class="col-sm-4">
        
             <div class="form-radio">
                <label class="form-check-label">
                <input type="radio" class="form-check-input" name="fecha_fija" id="fecha_fija" value="0" onchange="showdivtwo('occultrd');" checked=""> No
                <i class="input-helper"></i><i class="input-helper"></i></label>
              </div>

              </div>
        </div>
            </div>
        </div>--}}

        <div class="form-group row occultrd">
          <label class="col-sm-4 col-form-label">Fecha Pago <span class="text-danger">*</span><a><i data-tippy-content="Fecha en la que se crea el primer pago" class="icono far fa-question-circle"></i></a></label>
          <div class="col-sm-8">
            <input type="text" class="form-control datepicker"  id="fecha" value="{{date('d-m-Y')}}" name="fecha" disabled=""  >
          </div>
        </div>
        {{--<div class="form-group row">
          <label class="col-sm-4 col-form-label">Fecha Finalización<a><i data-tippy-content="Fecha en la que se crea el último comprobante" class="icono far fa-question-circle"></i></a></label>
          <div class="col-sm-8">
            <input type="text" class="form-control" id="vencimiento" name="vencimiento">
          </div>
        </div>--}}


  			<div class="form-group row occultrd">
          <label class="col-sm-4 col-form-label">Frecuencia <span class="text-danger">*</span> <a><i data-tippy-content="Frecuencia en la que se genera el egreso (Meses)" class="icono far fa-question-circle"></i></a></label>
          <div class="col-sm-8">
            <input type="number" name="frecuencia" class="form-control " min="1" required="">
          </div>
        </div>

        <div class="form-group row" id="saldo123" style="display:none;">
          <label class="col-sm-4 col-form-label">Día de cobro especifico<a><i data-tippy-content="Escoja el día especifico que se cobrará cada mes" class="icono far fa-question-circle"></i></a></label>
          <div class="col-sm-8">
            <input type="text" class="form-control" id="vencimiento" name="fechaespecifica">
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
            	<tbody><tr id="1">
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
                    <select class="form-control form-control-sm selectpicker" name="impuesto_categoria[]" id="impuesto_categoria1" title="Impuesto" onchange="total_categorias(1);" required="" disabled="">
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
                      <div class="resp-precio">
                    <input type="text" class="form-control form-control-sm text-right" id="total_categoria1" value="0.00" disabled="">  
                 </div>
                  </td>
                <td><button type="button" class="btn btn-outline-secondary btn-icons" onclick="Eliminar(1);">X</button></td>
              </tr>
            </tbody>
          </table>
          <button class="btn btn-outline-primary" onclick="CrearFilaCategorias();" type="button" style="margin-top: 5%; margin-bottom: 1%;">Agregar línea</button><a><i data-tippy-content="Si no existe la línea puedes crearla haciendo <a href='#'>clíck aquí</a>" class="icono far fa-question-circle"></i></a>

          

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
  		<div class="row" >
        
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

  <style type="text/css">
          .form-radio label input + .input-helper:before{
            border:1px solid #000;
          }
        </style>

@endsection