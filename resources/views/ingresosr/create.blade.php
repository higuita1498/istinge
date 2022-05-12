@extends('layouts.app')
@section('content')
	<form method="POST" action="{{ route('ingresosr.store') }}" style="padding: 2% 3%;" role="form" class="forms-sample" id="form-ingreso" >
    @if($remision)
    <input type="hidden" id="factura" value="{{$remision}}">
    @endif
    <input type="hidden" id="inputremision" value="1">
    <h5>INFORMACIÓN GENERAL DEL INGRESO </h5>
  		{{ csrf_field() }}
  		<div class="row" style=" text-align: right; margin-top: 5%">
  			<div class="col-md-5">
	  			<div class="form-group row">
	  				<label class="col-sm-4 col-form-label">Cliente </label>
		  			<div class="col-sm-8">
		  				<select class="form-control selectpicker" name="cliente" id="cliente" title="Seleccione" data-live-search="true" data-size="5" onchange="factura_pendiente();">
		  				@foreach($clientes as $clien)
		              		<option {{old('cliente')==$clien->id?'selected':''}} {{$cliente==$clien->id?'selected':''}}  value="{{$clien->id}}">{{$clien->nombre}} {{$clien->apellido1}} {{$clien->apellido2}} - {{$clien->nit}}</option>
		  				@endforeach
            	</select>
		  			</div>
		  			
					<span class="help-block error">
			        	<strong>{{ $errors->first('cliente') }}</strong>
			    </span>
	  		</div>  	
 
        <div class="form-group row">
          <label class="col-sm-4 col-form-label">Cuenta <span class="text-danger">*</span><a><i data-tippy-content="Crea tus cuentas haciendo <a href='#'>clíck aquí</a>" class="icono far fa-question-circle"></i></a></label>
          <div class="col-sm-8">
            <select class="form-control selectpicker" name="cuenta" id="cuenta" title="Seleccione" data-live-search="true" data-size="5" required="">
              @php $tipos_cuentas=\App\Banco::tipos();@endphp
              @foreach($tipos_cuentas as $tipo_cuenta)
                <optgroup label="{{$tipo_cuenta['nombre']}}">

                  @foreach($bancos as $cuenta)
                    @if($cuenta->tipo_cta==$tipo_cuenta['nro'])
                      <option value="{{$cuenta->id}}">{{$cuenta->nombre}}</option>
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
        <label class="col-sm-4 col-form-label">Fecha <a><i data-tippy-content="Fecha en la que se recibió el ingreso" class="icono far fa-question-circle"></i></a></label>
        <div class="col-sm-8">
          <input type="text" class="form-control datepicker"  id="fecha" value="{{date('d-m-Y')}}" name="fecha" disabled=""  >
        </div>
      </div>
		</div>
		<div class="col-md-5 offset-md-2">
  			<div class="form-group row" style="text-align: left;">
  				<label class="col-sm-12 col-form-label" >Recibo de caja # Numeración automática</label>
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

  		<div class="row">
        <div class="col-md-12" id="si">
          <h5>REMISIONES PENDIENTES</h5>
          <div id="factura_pendiente">Debes Seleccionar un cliente</div>
        </div>
  			
      </div>
  		
  		<hr>
  		<div class="row" >
        
        <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">

          <a href="{{route('ingresos.index')}}" class="btn btn-outline-secondary">Cancelar</a>
          <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success" id="button-guardar">Guardar</button>
        </div>
  		</div>


  	</form>
	<input type="hidden" id="url" value="{{url('/')}}">
  <input type="hidden" id="retenciones" value="{{json_encode($retenciones)}}">
  <input type="hidden" id="simbolo" value="{{Auth::user()->empresa()->moneda}}">

 
@endsection