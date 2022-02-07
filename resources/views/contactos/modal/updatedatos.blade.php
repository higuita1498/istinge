	<input name="_method" type="hidden" value="PATCH">
	{{ csrf_field() }}

	

	<div class="row">
		<div class="form-group col-md-12">
			<label class="control-label">Nombre</label>
			<input type="text" class="form-control" name="nombre" disabled="" value="{{ $contacto->nombre }}">
		</div>
	</div>
	
	@if($contacto->email == null && Auth::user()->empresa()->form_fe == 1)
	<div class="alert alert-danger" style="margin-left: 2%;margin-right: 2%;">
		El cliente debe de tener un correo electrónico.
	</div>

	<div class="form-group col-md-4">
		<label class="control-label" for="email">Correo Electrónico <span class="text-danger">*</span></label>
		<input type="email" class="form-control" id="email_2" name="email" data-error="Dirección de correo electrónico invalida" maxlength="100"  value="{{old('email')}}" required>
		<div class="help-block error with-errors"></div>
		<span class="help-block error">
			<strong>{{ $errors->first('email') }}</strong>
		</span>
	</div>
	@endif


	<div class="row" id="tipo_persona_2" style="display:none;">
		<div class="row">
			<div class="col-md-12">
				@if($contacto->tip_iden == 6 && $contacto->dv == null || $contacto->tipo_persona == null || $contacto->responsableiva == null)
				<div class="alert alert-danger" style="margin-left: 2%;margin-right: 2%;">
					Debes completar los campos.
					@elseif(!is_numeric($contacto->tip_iden))
					La identifiación debe de tener solo números.
				</div>
				@endif
			</div>
		</div>
		
		<div class="row" style="width: 100%">
			@if($contacto->tip_iden == 6 && $contacto->dv == null || !is_numeric($contacto->tip_iden) || $contacto->tipo_persona == null || $contacto->responsableiva == null)

			@if(!is_numeric($contacto->tip_iden) || $contacto->tip_iden == 6 && $contacto->dv == null)
			<div class="form-group col-md-2">
				<label class="control-label">Tipo de Identificación <span class="text-danger">*</span></label>
				<select class="form-control selectpicker" name="tip_iden" id="tip_iden_2" required="" onchange="searchDV(this.value)" title="Seleccione" disabled>
					@foreach($identificaciones as $identificacion)
					<option {{$contacto->tip_iden==$identificacion->id?'selected':''}} value="{{$identificacion->id}}" title="{{$identificacion->mini()}}" >{{$identificacion->identificacion}}</option>
					@endforeach
				</select>
				<span class="help-block error">
					<strong>{{ $errors->first('tip_iden') }}</strong>
				</span>
			</div>
			<div class="form-group col-md-3">
				<label class="control-label">Identificación <span class="text-danger">*</span></label>
				<input type="text" class="form-control" name="nit" id="nit_2" required="" numeric maxlength="20" value="{{$contacto->nit}}">
				<span class="help-block error">
					<strong id="vldnit_2"></strong>
				</span>
			</div>
			@endif

			@if($contacto->tip_iden == 6 && $contacto->dv == null || !is_numeric($contacto->tip_iden))
			<div class="form-group col-md-1" style="display: block;" id="dvnit">
				<label class="control-label">DV <span class="text-danger">*</span></label>
				<input type="text" class="form-control" name="dv" id="dv_2" disabled required="" maxlength="20" value="">
				<input type="hidden" name="dvoriginal" id="dvoriginal_2" value="">
				<span class="help-block error">
					<strong>{{ $errors->first('dv') }}</strong>
				</span>
			</div>
			@endif

			@endif

			@if($contacto->tipo_persona == null)
			<div class="form-group col-md-3">
				<label class="control-label">Tipo Persona<span class="text-danger">*</span></label>
				<select class="form-control selectpicker" name="tipo_persona" id="tipo_persona2" required=""  title="Seleccione" onchange="tipopersonaModal(this.value)">

					<option {{ $contacto->tipo_persona == 1 ? 'selected' : '' }} value="1">Persona Natural</option>
					<option {{ $contacto->tipo_persona == 2 ? 'selected' : '' }} value="2">Persona Juridica</option>
				</select>
				<span class="help-block error">
					<strong>{{ $errors->first('tipo_persona') }}</strong>
				</span>
			</div>
			@endif
			
			@if($contacto->responsableiva == null)
			<div class="form-group col-md-3">
				<label class="control-label">Responsabilidad<span class="text-danger">*</span></label>
				<select class="form-control selectpicker" name="responsable" id="responsable2" required=""  title="Seleccione">

					<option {{ $contacto->responsableiva == 1 ? 'selected' : '' }} value="1">Responsable de IVA</option>
					<option {{ $contacto->responsableiva == 2 ? 'selected' : '' }} value="2">No Responsable de IVA</option>
				</select>
				<span class="help-block error">
					<strong>{{ $errors->first('tipo_persona') }}</strong>
				</span>
			</div>
			@endif
		</div>
	</div>

	@if($contacto->fk_iddepartamento == null || $contacto->fk_idmunicipio == null || $contacto->fk_idpais == null)
	<div id="verifi_direction">
		<div class="alert alert-danger" style="margin-left: 2%;margin-right: 2%;">
			Debes de actualizar el país, departamento, municipio y código postal para poder tener la facturación electrónica.
		</div>

		<div class="row">
                
			<div class="form-group col-md-3">
				<label class="control-label">País</label>
				<select class="form-control selectpicker" name="pais" id="pais_2" required="" title="Seleccione" data-live-search="true" data-size="5" onchange="validateCountry(this.value)">
					@foreach($paises as $pais)
					<option value="{{$pais->codigo}}" {{ $contacto->fk_idpais == $pais->codigo ? 'selected' : $pais->codigo == 'CO' ? 'selected' : '' }}>{{$pais->nombre}}</option>
					@endforeach
				</select>
			</div>

			<div class="form-group col-md-3" id="validatec1">
				<label class="control-label">Departamento</label>
				<select class="form-control selectpicker" name="departamento" id="departamento_2" required title="Seleccione" data-live-search="true" data-size="5" onchange="searchMunicipalityModal(this.value)">
					@foreach($departamentos as $departamento)
					<option value="{{ $departamento->id }}"
						{{ $contacto->fk_iddepartamento == $departamento->id ? 'selected' : '' }}
						>{{ $departamento->nombre }}</option>
						@endforeach
					</select>
				</div>

				<div class="form-group col-md-3" id="validatec2">
					<label class="control-label">Municipio</label>
					<select class="form-control selectpicker" name="municipio" id="municipio_2" required="" title="Seleccione" data-live-search="true" data-size="5" required>
						<option selected value="{{ $contacto->fk_idmunicipio }}">  {{ $contacto->municipio()->nombre }}</option>
					</select>
				</div>

				<div class="form-group col-md-3" id="validatec3">
					<label class="control-label">Código Postal</label>
					<a><i id="tippycodigo" class="icono far fa-question-circle"></i></a>
					<input type="text" class="form-control" id="cod_postal_2" name="cod_postal" maxlength="200"  value="{{$contacto->cod_postal}}">
				</div>

				<div class="form-group col-md-5">
					<label class="control-label">Dirección </label>
					<textarea class="form-control" name="direccion" id="direccion_2">{{$contacto->direccion}}</textarea>
					<span class="help-block error">
						<strong>{{ $errors->first('direccion') }}</strong>
					</span>
				</div>

				@if($contacto->fk_idmunicipio == null && $contacto->ciudad != "")
				<div class="form-group col-md-3">
					<label class="control-label">Ciudad (antes)</label>
					<input type="text" class="form-control" id="ciudad" name="ciudad" maxlength="200"  value="{{$contacto->ciudad}}">
					<span class="help-block error">
						<strong>{{ $errors->first('ciudad') }}</strong>
					</span>
				</div>
				@endif
			</div>
	</div>
	@endif
</div>

		<div class="row" style="text-align: right;">
			<div class="col-md-12">
				<button type="button" onclick="updateDirectionClient(this.value)" value="{{ $contacto->id }}" class="btn btn-success">Guardar</button>
			</div>
		</div>

		<script type="text/javascript">
			$(document).ready(function(){
				var option = document.getElementById('tip_iden_2').value;

				if (option == 6) {
					searchDVmodal($("#tip_iden_2").val());
				}
			});
		</script>