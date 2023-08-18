@extends('layouts.app')
@section('content')

@if(Session::has('success-newtypecontact'))
    <div class="alert alert-success">
	    {{Session::get('success-newtypecontact')}}
    </div>
    <script type="text/javascript">
        setTimeout(function() {
            $('.alert').hide();
            $('.active_table').attr('class', ' ');
        }, 5000);
    </script>
@endif

<form method="POST" action="{{ route('contactos.store') }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-contacto">
	@csrf
	<div class="row">
		{{-- <div class="form-group col-md-3">
			<label class="control-label">Tipo de Identificación <span class="text-danger">*</span></label>
			<select class="form-control selectpicker" name="tip_iden" id="tip_iden" required="" onchange="searchDV(this.value)" title="Seleccione">
				@foreach($identificaciones as $identificacion)
				<option {{old('tip_iden')==$identificacion->id?'selected':''}} value="{{$identificacion->id}}" title="{{$identificacion->mini()}}">{{$identificacion->identificacion}}</option>
				@endforeach
			</select>
			<span class="help-block error">
				<strong>{{ $errors->first('tip_iden') }}</strong>
			</span>
		</div> --}}
		<div class="form-group col-md-3">
			<label class="control-label">Coloque el titulo del campo 1<span class="text-danger">*</span><a><i data-tippy-content="Identificación de la persona" class="icono far fa-question-circle"></i></a></label>
			<input type="text" class="form-control" name="nit" id="nit" required="" maxlength="10" value="{{old('nit')}}" onkeypress="return event.charCode >= 48 && event.charCode <=57">
			<label for="vehicle1"> Es archivo el campo 1</label><br>
            <input type="checkbox" id="archivo1" name="archivo1" value="">
            <span class="help-block error">
				<strong>{{ $errors->first('nit') }}</strong>
			</span>
		</div>
        <div class="form-group col-md-3">
			<label class="control-label">Coloque el titulo del campo 2<span class="text-danger">*</span><a><i data-tippy-content="Identificación de la persona" class="icono far fa-question-circle"></i></a></label>
			<input type="text" class="form-control" name="nit" id="nit" required="" maxlength="10" value="{{old('nit')}}" onkeypress="return event.charCode >= 48 && event.charCode <=57">
			<label for="vehicle1"> Es archivo el campo 2</label><br>
            <input type="checkbox" id="archivo1" name="archivo1" value="">
            <span class="help-block error">
				<strong>{{ $errors->first('nit') }}</strong>
			</span>
		</div>
        <div class="form-group col-md-3">
			<label class="control-label">Coloque el titulo del campo 3<span class="text-danger">*</span><a><i data-tippy-content="Identificación de la persona" class="icono far fa-question-circle"></i></a></label>
			<input type="text" class="form-control" name="nit" id="nit" required="" maxlength="10" value="{{old('nit')}}" onkeypress="return event.charCode >= 48 && event.charCode <=57">
			<label for="vehicle1"> Es archivo el campo 3</label><br>
            <input type="checkbox" id="archivo1" name="archivo1" value="">
            <span class="help-block error">
				<strong>{{ $errors->first('nit') }}</strong>
			</span>
		</div>
        <div class="form-group col-md-3">
			<label class="control-label">Coloque el titulo del campo 4<span class="text-danger">*</span><a><i data-tippy-content="Identificación de la persona" class="icono far fa-question-circle"></i></a></label>
			<input type="text" class="form-control" name="nit" id="nit" required="" maxlength="10" value="{{old('nit')}}" onkeypress="return event.charCode >= 48 && event.charCode <=57">
			<label for="vehicle1"> Es archivo el campo 4</label><br>
            <input type="checkbox" id="archivo1" name="archivo1" value="">
            <span class="help-block error">
				<strong>{{ $errors->first('nit') }}</strong>
			</span>
		</div>
        <div class="form-group col-md-3">
			<label class="control-label">Coloque el titulo del campo 5<span class="text-danger">*</span><a><i data-tippy-content="Identificación de la persona" class="icono far fa-question-circle"></i></a></label>
			<input type="text" class="form-control" name="nit" id="nit" required="" maxlength="10" value="{{old('nit')}}" onkeypress="return event.charCode >= 48 && event.charCode <=57">
			<label for="vehicle1"> Es archivo el campo 5</label><br>
            <input type="checkbox" id="archivo1" name="archivo1" value="">
            <span class="help-block error">
				<strong>{{ $errors->first('nit') }}</strong>
			</span>
		</div>

		{{-- <div class="form-group col-md-1" style="display: none;" id="dvnit">
			<label class="control-label">DV <span class="text-danger">*</span></label>
			<input type="text" class="form-control" name="dv" id="dv" disabled required="" maxlength="20" value="">
			<input type="hidden" name="dvoriginal" id="dvoriginal" value="">
			<span class="help-block error">
				<strong>{{ $errors->first('dv') }}</strong>
			</span>
		</div> --}}

		{{-- <div class="form-group col-md-3">
			<label class="control-label">Nombres <span class="text-danger">*</span></label>
			<input type="text" class="form-control" name="nombre" id="nombre" required="" maxlength="200" value="{{old('nombre')}}">
			<span class="help-block error">
				<strong>{{ $errors->first('nombre') }}</strong>
			</span>
		</div> --}}
        {{-- <div class="form-group col-md-3">
            <label class="control-label">Apellido 1</label>
            <input type="text" class="form-control" name="apellido1" id="apellido1" maxlength="200" value="{{old('apellido1')}}">
            <span class="help-block error">
                <strong>{{ $errors->first('apellido1') }}</strong>
            </span>
        </div> --}}
        {{-- <div class="form-group col-md-3">
            <label class="control-label">Apellido 2</label>
            <input type="text" class="form-control" name="apellido2" id="apellido2" maxlength="200" value="{{old('apellido2')}}">
            <span class="help-block error">
                <strong>{{ $errors->first('apellido2') }}</strong>
            </span>
        </div> --}}
	</div>
	{{-- <div class="row" id="tipop" style="display: none;">
		<div class="form-group col-md-3">
			<label class="control-label">Tipo Persona<span class="text-danger">*</span></label>
			<select class="form-control selectpicker" name="tipo_persona" id="tipo_persona" required="" title="Seleccione" onchange="tipopersona(this.value)">

				<option {{old('tipo_persona')=='1'?'selected':''}} value="1">Persona Natural</option>
				<option {{old('tipo_persona')=='2'?'selected':''}} value="2">Persona Juridica</option>
			</select>
			<span class="help-block error">
				<strong>{{ $errors->first('tipo_persona') }}</strong>
			</span>
		</div>

		<div class="form-group col-md-3">
			<label class="control-label">Responsabilidad<span class="text-danger">*</span></label>
			<select class="form-control selectpicker" name="responsable" id="responsabilidad" required="" title="Seleccione">
				<option value="1">Responsable de IVA</option>
				<option value="2">No Responsable de IVA</option>
			</select>
			<span class="help-block error">
				<strong>{{ $errors->first('tipo_persona') }}</strong>
			</span>
		</div>
	</div> --}}
	{{-- <div class="row">
		<div class="form-group col-md-3">
			<label class="control-label">País</label>
			<select class="form-control selectpicker" name="pais" id="pais" required="" title="Seleccione" data-live-search="true" data-size="5" onchange="validateCountry(this.value)">
				@foreach($paises as $pais)
				<option value="{{$pais->codigo}}" {{ $pais->codigo == 'CO' ? 'selected' : '' }}>{{$pais->nombre}}</option>
				@endforeach
			</select>
		</div>

		<div class="form-group col-md-3" id="validatec1">
			<label class="control-label">Departamento <span class="text-danger">*</span></label>
			<select class="form-control selectpicker" name="departamento" id="departamento" required="" title="Seleccione" data-live-search="true" data-size="5" onchange="searchMunicipality(this.value)">
				@foreach($departamentos as $departamento)
				<option value="{{ $departamento->id }}">{{ $departamento->nombre }}</option>
				@endforeach
			</select>
		</div>

		<div class="form-group col-md-3" id="validatec2">
			<label class="control-label">Municipio <span class="text-danger">*</span></label>
			<select class="form-control selectpicker" name="municipio" id="municipio" required="" title="Seleccione" data-live-search="true" data-size="5">
			</select>
		</div>

		<div class="form-group col-md-3" id="validatec3">
			<label class="control-label">Código Postal
				<a><i data-tippy-content="Si desconoces tu código postal <a target='_blank' href='http://visor.codigopostal.gov.co/472/visor/'>haz click aquí</a>" class="icono far fa-question-circle"></i></a>
			</label>
			<input type="text" class="form-control" id="cod_postal" name="cod_postal" maxlength="200" value="{{old('cod_postal')}}">
		</div>

		<div class="form-group col-md-9">
			<label class="control-label">Dirección </label>
			<input type="text" class="form-control" name="direccion" value="{{old('direccion')}}">
			<span class="help-block error">
				<strong>{{ $errors->first('direccion') }}</strong>
			</span>
		</div>

		<div class="form-group col-md-3">
			<label class="control-label" for="email">Correo Electrónico </label>
			<input type="email" class="form-control" id="email" name="email" data-error="Dirección de correo electrónico invalida" maxlength="100" value="{{old('email')}}" autocomplete="off">
			<div class="help-block error with-errors" id="formato-correo"></div>
			<span class="help-block error">
				<strong>{{ $errors->first('email') }}</strong>
			</span>
		</div>
	</div> --}}

	{{-- <div class="row">
		<div class="form-group col-md-3">
			<label class="control-label">Teléfono <span class="text-danger">*</span></label>
			<input type="text" class="form-control" id="telefono1" name="telefono1" required="" maxlength="15" value="{{old('telefono1')}}">
			<span class="help-block error">
				<strong>{{ $errors->first('telefono1') }}</strong>
			</span>
		</div>
		<div class="form-group col-md-3">
			<label class="control-label">Teléfono 2</label>
			<input type="text" class="form-control" id="telefono2" name="telefono2" maxlength="15" value="{{old('telefono2')}}">
			<span class="help-block error">
				<strong>{{ $errors->first('telefono2') }}</strong>
			</span>
		</div>
		<div class="form-group col-md-3">
			<label class="control-label">Fax</label>
			<input type="text" class="form-control" id="fax" name="fax" maxlength="15" value="{{old('fax')}}">
			<span class="help-block error">
				<strong>{{ $errors->first('fax') }}</strong>
			</span>
		</div>
		<div class="form-group col-md-3">
			<label class="control-label">Celular</label>
			<input type="text" class="form-control" id="celular" name="celular" maxlength="15" value="{{old('celular')}}">
			<span class="help-block error">
				<strong>{{ $errors->first('celular') }}</strong>
			</span>
		</div>
	</div> --}}

	{{-- <div class="row">
		<div class="form-group col-md-3">
			<label class="control-label">Tipos de Contactos <span class="text-danger">*</span><a><i data-tippy-content="Tipo empresa a la que pertenece el contacto" class="icono far fa-question-circle"></i></a></label>
			<select class="form-control selectpicker" name="tipo_empresa" id="tipo_empresa" required="" title="Seleccione" data-live-search="true" data-size="5">
				{{-- @foreach($tipos_empresa as $tipo_empresa)
				<option {{old('tipo_empresa')==$tipo_empresa->id?'selected':''}} {{$tipo_empresa->nombre=='VARIOS'?'selected':''}} {{$tipo_empresa->nombre=='Varios'?'selected':''}} value="{{$tipo_empresa->id}}">{{$tipo_empresa->nombre}}</option>
				@endforeach --}}
			{{-- </select>
			<span class="help-block error">
				<strong>{{ $errors->first('tipo_empresa') }}</strong>
			</span> --}}
		{{-- </div> --}}
		{{-- <div class="form-group col-md-3">
			<label class="control-label">Lista de Precios <a><i data-tippy-content="Lista de precios que desee asociar a este contacto" class="icono far fa-question-circle"></i></a></label>
			<select class="form-control selectpicker" name="lista_precio" id="lista_precio" title="Seleccione" data-size="5"> --}}
				{{-- @foreach($listas as $lista)
				<option {{old('lista_precio')==$lista->id?'selected':''}} {{$lista->nro == 1 ? 'selected':''}} value="{{$lista->id}}">{{$lista->nombre()}}</option>
				@endforeach --}}
			{{-- </select>
		</div>
		<div class="form-group col-md-3">
			<label class="control-label">Vendedor <a><i data-tippy-content="Vendedor que desee asociar a este contacto" class="icono far fa-question-circle"></i></a></label>
			<select class="form-control selectpicker" name="vendedor" id="vendedor" title="Seleccione" data-live-search="true" data-size="5"> --}}
				{{-- @foreach($vendedores as $vendedor)
				<option {{old('vendedor')==$vendedor->id?'selected':''}} {{$vendedor->nombre=='Principal'?'selected':''}} value="{{$vendedor->id}}">{{$vendedor->nombre}}</option>
				@endforeach --}}
			{{-- </select>
		</div>
		@if(Auth::user()->empresa()->oficina)
		<div class="form-group col-md-3">
  			<label class="control-label">Oficina Asociada <span class="text-danger">*</span></label>
  			<select class="form-control selectpicker" name="oficina" id="oficina" required="" title="Seleccione" data-live-search="true" data-size="5">
  				@foreach($oficinas as $oficina)
  				  <option value="{{$oficina->id}}" {{ $oficina->id == auth()->user()->oficina ? 'selected' : '' }}>{{$oficina->nombre}}</option>
  				@endforeach
  			</select>
  		</div>
  		@endif
	</div>  --}}

	{{-- <div class="row">
	    <div class="form-group col-md-3 d-none">
			<label class="control-label">Tipo de Contacto <span class="text-danger">*</span></label>
			<div class="form-check form-check-flat">
				<label class="form-check-label">
					<input type="checkbox" class="form-check-input" name="tipo_contacto[]" value="0"> Cliente
					<i class="input-helper"></i></label>
			</div>
			<div class="form-check form-check-flat">
				<label class="form-check-label">
					<input type="checkbox" class="form-check-input" name="tipo_contacto[]" value="1" checked=""> Proveedor
					<i class="input-helper"></i></label>
			</div>
			<span class="help-block error">
				<strong>{{ $errors->first('tipo_contacto') }}</strong>
			</span>
		</div>
		<div class="form-group col-md-12">
			<label class="control-label">Observaciones</label>
			<textarea class="form-control" name="observaciones">{{old('observaciones')}}</textarea>
			<span class="help-block error">
				<strong>{{ $errors->first('observaciones') }}</strong>
			</span>
		</div>
	</div> --}}

	{{-- <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small> --}}
	<hr>

	<div class="row" style="text-align: right;">
		<div class="col-md-12">
			<a href="{{route('contactos.proveedores')}}" class="btn btn-outline-light">Cancelar</a>
			<button type="submit" id="button-save" class="btn btn-success">Guardar</button>
		</div>
	</div>
</form>

<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
<script>
	$(document).ready(function() {
		let lastRegis = new URLSearchParams(window.location.search);
		if (lastRegis.has('cnt')) {

			let idCnt = lastRegis.get('cnt');

			setTimeout(function() {
				$('#tipo_empresa').val(idCnt).change();
				clearTimeout(this);
			}, 1000);
		}

		var correo = document.getElementById('email'),
			intervalo;

		correo.addEventListener('input', function() {
			campo = event.target;
			valido = document.getElementById('formato-correo');

			emailRegex = /^[-\w.%+]{1,64}@(?:[A-Z0-9-]{1,63}\.){1,125}[A-Z]{2,63}$/i;
			//Se muestra un texto a modo de ejemplo, luego va a ser un icono
			clearInterval(intervalo);
			intervalo = setInterval(() => {

				if (emailRegex.test(campo.value)) {
					valido.innerText = "Formato de correo válido";
					valido.style.color = "green";
					$('#button-save').removeAttr('disabled', true);

				} else {
					valido.innerText = "Formato de correo incorrecto";
					valido.style.color = "red";
					$('#button-save').attr('disabled', true);

				}

				clearInterval(intervalo);
			}, 1000);

		}, false);
	});
</script>

@endsection

@section('scripts')
<script type="text/javascript">
	$(document).ready(function() {
		var option = document.getElementById('tip_iden').value;

		if (option == 6) {
			searchDV($("#tip_iden").val());
		}
		$('#departamento').val({{ Auth::user()->empresa()->fk_iddepartamento }}).selectpicker('refresh');
		searchMunicipality({{ Auth::user()->empresa()->fk_iddepartamento }}, {{ Auth::user()->empresa()->fk_idmunicipio }});
	});
	$('#email').keydown(function(e) {
		if (e.keyCode == 32) {
			return false;
		}
	});

	function plazo() {
		var dias = $('#plazo_credito option:selected').attr('dias');
		let fechaActual = $('#vencimiento').val();
		let fechaVencimiento = moment(fechaActual, "DD-MM-YYYY");
		moment.locale('es');

		if ($.isNumeric(dias)) {
			let fecha = fechaVencimiento.add(parseInt(dias), 'days').format("DD-MM-YYYY");
			$('#vencimiento').val(fecha);
		}
	}

	setTimeout(function () {
		$("#municipio").val({{ Auth::user()->empresa()->fk_idmunicipio }});
		$("#municipio").selectpicker('refresh');
    }, 500);
</script>

@endsection
