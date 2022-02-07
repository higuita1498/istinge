
<form method="POST" action="" role="form" class="forms-sample" novalidate id="form-contacto" onsubmit="enviarForm(this,event);" style="padding: 1%">
	<input type="hidden" name="modal" value="1">
		{{ csrf_field() }}
	<div class="modal-header">
	    <h5 class="modal-title" id="exampleModalLongTitle">Agregar nuevo cliente</h5>
	    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	      <span aria-hidden="true">&times;</span>
	    </button>
	  </div>
	  <div class="modal-body">
	  	<div class="row">
  			<div class="form-group col-md-3">
	  			<label class="control-label">Tipo de Identificación </label>
	  			<select class="form-control selectpicker" name="tip_iden" id="tip_iden" title="Seleccione">
	  				@foreach($identificaciones as $identificacion)
                  		<option {{old('tip_iden')==$identificacion->id?'selected':''}} value="{{$identificacion->id}}" title="{{$identificacion->mini()}}">{{$identificacion->identificacion}}</option>
	  				@endforeach
                </select>
				<span class="help-block error">
		        	<strong>{{ $errors->first('tip_iden') }}</strong>
		        </span>
			</div>
			<div class="form-group col-md-3">
	  			<label class="control-label">Identificación </label>
				<input type="text" class="form-control" name="nit" id="nit"  maxlength="20" value="{{old('nit')}}">
				<span class="help-block error">
					<strong>{{ $errors->first('nit') }}</strong>
				</span>
			</div>

			<div class="form-group col-md-6">
	  			<label class="control-label">Nombre <span class="text-danger">*</span></label>
				<input type="text" class="form-control" name="nombre" id="nombre" required="" maxlength="200" value="{{old('nombre')}}">
				<span class="help-block error">
		        	<strong>{{ $errors->first('nombre') }}</strong>
		        </span>
			</div>
  		</div> 
  		<div class="row">
  			<div class="form-group col-md-5">
	  			<label class="control-label">Dirección </label>
	  			<textarea class="form-control" name="direccion" >{{old('direccion')}}</textarea>
				<span class="help-block error">
					<strong>{{ $errors->first('direccion') }}</strong>
				</span>
			</div>
			<div class="form-group col-md-3">
	  			<label class="control-label">Ciudad</label>
				<input type="text" class="form-control" id="ciudad" name="ciudad" maxlength="200"  value="{{old('ciudad')}}">
				<span class="help-block error">
					<strong>{{ $errors->first('email') }}</strong>
				</span>
			</div>
			<div class="form-group col-md-4">
	  			<label class="control-label" for="email">Correo Electrónico</label>
				<input type="email" class="form-control" id="email" name="email" data-error="Dirección de correo electrónico invalida" maxlength="100"  value="{{old('email')}}">
				<div class="help-block error with-errors"></div>
				<span class="help-block error">
					<strong>{{ $errors->first('email') }}</strong>
				</span>
			</div>
			


  		</div>
  		<div class="row">
  			<div class="form-group col-md-3">
	  			<label class="control-label">Teléfono </label>
	  			<div class="row">
	  				<div class="col-md-4 nopadding ">
	  					<select class="form-control selectpicker prefijo" name="pref1" id="pref1" title="Cod" data-size="5" data-live-search="true">
			  				@foreach($prefijos as $prefijo)
		                  		<option @if(old('pref1')) {{old('pref1')==$prefijo->phone_code?'selected':''}} @else {{'+'.$prefijo->phone_code==Auth::user()->empresa()->codigo?'selected':''}}  @endif

		                  		 	data-icon="flag-icon flag-icon-{{strtolower($prefijo->iso2)}}"

		                  		 value="+{{$prefijo->phone_code}}" title="+{{$prefijo->phone_code}}" data-subtext="+{{$prefijo->phone_code}}">{{$prefijo->nombre}}</option>
			  				@endforeach
		                </select>

				
	  				</div>
	  				<div class="col-md-8" style="padding-left:0;">
	  					<input type="text" class="form-control" id="telefono1" name="telefono1" maxlength="15" value="{{old('telefono1')}}">
	  				</div>
	  			</div>
				<span class="help-block error">
		        	<strong>{{ $errors->first('telefono1') }}</strong>
		        </span>
			</div>
  			<div class="form-group col-md-3">
	  			<label class="control-label">Teléfono 2</label>
  				<div class="row">
  					<div class="col-md-4 nopadding ">
	  					<select class="form-control selectpicker prefijo" name="pref2" id="pref2" title="Cod" data-size="5" data-live-search="true">
			  				@foreach($prefijos as $prefijo)
		                  		<option @if(old('pref2')) {{old('pref2')==$prefijo->phone_code?'selected':''}} @else {{'+'.$prefijo->phone_code==Auth::user()->empresa()->codigo?'selected':''}}  @endif

		                  		 	data-icon="flag-icon flag-icon-{{strtolower($prefijo->iso2)}}"

		                  		 value="+{{$prefijo->phone_code}}" title="+{{$prefijo->phone_code}}" data-subtext="+{{$prefijo->phone_code}}">{{$prefijo->nombre}}</option>
			  				@endforeach
		                </select>
	  				</div>
	  				<div class="col-md-8" style="padding-left:0;">
	  					<input type="text" class="form-control" id="telefono2" name="telefono2" maxlength="15" value="{{old('telefono2')}}">
	  				</div>
  				</div>
				<span class="help-block error">
		        	<strong>{{ $errors->first('telefono2') }}</strong>
		        </span>
			</div>
			<div class="form-group col-md-3">
	  			<label class="control-label">Fax</label>
					<div class="row">
						<div class="col-md-4 nopadding ">
		  					<select class="form-control selectpicker prefijo" name="preffax" id="preffax" title="Cod" data-size="5" data-live-search="true">
				  				@foreach($prefijos as $prefijo)
			                  		<option @if(old('preffax')) {{old('preffax')==$prefijo->phone_code?'selected':''}} @else {{'+'.$prefijo->phone_code==Auth::user()->empresa()->codigo?'selected':''}}  @endif

			                  		 	data-icon="flag-icon flag-icon-{{strtolower($prefijo->iso2)}}"

			                  		 value="+{{$prefijo->phone_code}}" title="+{{$prefijo->phone_code}}" data-subtext="+{{$prefijo->phone_code}}">{{$prefijo->nombre}}</option>
				  				@endforeach
			                </select>
		  				</div>
		  				<div class="col-md-8" style="padding-left:0;">
		  					<input type="text" class="form-control" id="fax" name="fax"  maxlength="15" value="{{old('fax')}}">
		  				</div>
					</div>
					<span class="help-block error">
			        	<strong>{{ $errors->first('fax') }}</strong>
			        </span>
			</div>
			<div class="form-group col-md-3">
	  			<label class="control-label">Celular</label>
	  			<div class="row">
	  				<div class="col-md-4 nopadding ">
	  					<select class="form-control selectpicker prefijo" name="prefcelular" id="prefcelular" title="Cod" data-size="5" data-live-search="true">
			  				@foreach($prefijos as $prefijo)
		                  		<option @if(old('prefcelular')) {{old('prefcelular')==$prefijo->phone_code?'selected':''}} @else {{'+'.$prefijo->phone_code==Auth::user()->empresa()->codigo?'selected':''}}  @endif

		                  		 	data-icon="flag-icon flag-icon-{{strtolower($prefijo->iso2)}}"

		                  		 value="+{{$prefijo->phone_code}}" title="+{{$prefijo->phone_code}}" data-subtext="+{{$prefijo->phone_code}}">{{$prefijo->nombre}}</option>
			  				@endforeach
		                </select>
	  				</div>
	  				<div class="col-md-8" style="padding-left:0;">
	  					<input type="text" class="form-control" id="celular" name="celular" maxlength="15" value="{{old('celular')}}">
	  				</div>
	  			</div>
				
				<span class="help-block error">
		        	<strong>{{ $errors->first('celular') }}</strong>
		        </span>
			</div>
			
			
			
  		</div>
  		<div class="row">
			<div class="form-group col-md-3">
	  			<label class="control-label">Tipo de Contacto </label>
				<div class="form-check form-check-flat">
                    <label class="form-check-label">
                      <input type="checkbox" class="form-check-input" name="contacto[]" value="0" checked=""> Cliente
                    <i class="input-helper"></i></label>
                  </div>
                  <div class="form-check form-check-flat">
                    <label class="form-check-label">
                      <input type="checkbox" class="form-check-input" name="contacto[]" value="1" > Proveedor
                    <i class="input-helper"></i></label>
                  </div>
                  <span class="help-block error">
					<strong>{{ $errors->first('contacto') }}</strong>
				</span>
			</div>

  			<div class="form-group col-md-3">
	  			<label class="control-label">Tipo de Empresa</label>
	  			<select class="form-control selectpicker" name="tipo_empresa" id="tipo_empresa" title="Seleccione" data-live-search="true" data-size="5">
	  				@foreach($tipos_empresa as $tipo_empresa)
                  		<option {{old('tipo_empresa')==$tipo_empresa->id?'selected':''}} value="{{$tipo_empresa->id}}">{{$tipo_empresa->nombre}}</option>
	  				@endforeach
                </select>
				<span class="help-block error">
		        	<strong>{{ $errors->first('tipo_empresa') }}</strong>
		        </span>
			</div>

  			<div class="form-group col-md-6">
	  			<label class="control-label">Observaciones</label>

	  			<textarea class="form-control" name="observaciones" >{{old('observaciones')}}</textarea>
				<span class="help-block error">
					<strong>{{ $errors->first('observaciones') }}</strong>
				</span>
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
  		$(document).ready(function(){
		    $('#departamento').val(2).selectpicker('refresh');
			searchMunicipality(2);
		});
  			$('.selectpicker').selectpicker();
  			if ($('#form-contacto').length > 0) {
			    $('#telefono1').mask('000000000000');
			    $('#telefono2').mask('000000000000');
			    $('#fax').mask('000000000000');
			    $('#celular').mask('000000000000');
			      $('#username').mask('AAAAAAAAAAAAAAAAAAAA');
			      $("#form-contacto").validate({language: 'es'
			    });

			  }

  		</script>