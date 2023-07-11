
<form method="POST" action="{{route('bancos.transferencia', $banco->nro)}}" role="form" novalidate id="form-transferencia">
	{{ csrf_field() }}
	<div class="modal-header">
	    <h5 class="modal-title" id="exampleModalLongTitle">Transferencia entre cuentas</h5>
	    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	      <span aria-hidden="true">&times;</span>
	    </button>
	  </div>
	  <div class="modal-body ">

		<div class="form-group row">
			<label class="col-sm-4 col-form-label">Cuenta de Origen</label>
  			<div class="col-sm-8">
        		<div class="input-group">
        			<select class="form-control selectpicker" name="cuenta_origen" id="cuenta_origen" title="Seleccione" disabled="">
        			@php $tipos_cuentas=\App\Banco::tipos();@endphp
					@foreach($tipos_cuentas as $tipo_cuenta)
						<optgroup label="{{$tipo_cuenta['nombre']}}">
							@foreach($bancos as $cuenta)
							@if($cuenta->tipo_cta==$tipo_cuenta['nro'])
								<option value="{{$cuenta->id}}" {{$banco->id==$cuenta->id?'selected':''}}>{{$cuenta->nombre}}</option>
							@endif
							@endforeach
						</optgroup>
					@endforeach
					</select>
        		</div>
        	</div>
		</div>

		<div class="form-group row">
			<label class="col-sm-4 col-form-label">Cuenta de Destino <span class="text-danger">*</span></label>
  			<div class="col-sm-8">
        		<div class="input-group">
        			<select class="form-control selectpicker" name="cuenta_destino" id="cuenta_destino" title="Seleccione"  required="">
        			@php $tipos_cuentas=\App\Banco::tipos();@endphp
					@foreach($tipos_cuentas as $tipo_cuenta)
						<optgroup label="{{$tipo_cuenta['nombre']}}">
							@foreach($bancos as $cuenta)
							@if($cuenta->tipo_cta==$tipo_cuenta['nro'] && $banco->id<>$cuenta->id)
								<option value="{{$cuenta->id}}">{{$cuenta->nombre}}</option>
							@endif
							@endforeach
						</optgroup>
					@endforeach
					</select>
        		</div>
        	</div>
		</div>

		<div class="form-group row">
			<label class="col-sm-4 col-form-label">Monto <span class="text-danger">*</span></label>
  			<div class="col-sm-8">
        		<div class="input-group monetario">
	  				<input type="number" class="form-control precio" name="monto" min="0" maxlength="24">
        			
        		</div>
        	</div>
		</div>

		<div class="form-group row">
			<label class="col-sm-4 col-form-label">Fecha <span class="text-danger">*</span></label>
			<div class="col-sm-8">
				<input type="text" class="form-control datepicker"  id="fecha" value="{{date('d-m-Y')}}" name="fecha">
			</div>
		</div>

		<div class="form-group row">
		  	<label class="col-sm-4  col-form-label">Observaciones</label>
			<div class="col-sm-8">
				<textarea  class="form-control form-control-sm min_max_100" name="observaciones"></textarea>
			</div>
	    </div>

		<small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
	  </div>
	  <div class="modal-footer">
	    <button type="button" class="btn btn-outline-light" data-dismiss="modal">Cerrar</button>
	    <button type="submit" class="btn btn-success">Guardar</button>
	  </div>
  	</form>
  		
	<script type="text/javascript">
		$('.selectpicker').selectpicker();
		$('.datepicker').datepicker({
		  locale: 'es-es',
		  uiLibrary: 'bootstrap4', 
		  format: 'dd-mm-yyyy',
		});
    	$("#form-transferencia").validate({language: 'es'});

	</script>