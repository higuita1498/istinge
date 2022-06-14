<form method="POST" action="{{ route('contactos.storeBack') }}" style="padding: 0%;" role="form" class="forms-sample" novalidate id="formulario">
    @csrf
    <div class="row" style="text-align: center;">
        <div class="col-md-12">
            <h3>AGREGAR NUEVO CLIENTE</h3>
            <hr>
        </div>
    </div>
    
    <div style="padding: 1% 3%;">
        <div class="row">
      		<div class="form-group col-md-3">
      			<label class="control-label">Tipo de Identificación <span class="text-danger">*</span></label>
      			<select class="form-control selectpicker" name="tip_iden" id="tip_iden" required="" onchange="searchDV(this.value)" title="Seleccione">
      				@foreach($identificaciones as $identificacion)
      				  <option @if($identificacion->id == 3) ? selected @endif {{old('tip_iden')==$identificacion->id?'selected':''}} value="{{$identificacion->id}}" title="{{$identificacion->mini()}}">{{$identificacion->identificacion}}</option>
      				@endforeach
      			</select>
      			<span class="help-block error">
      				<strong>{{ $errors->first('tip_iden') }}</strong>
      			</span>
      		</div>
      		<div class="form-group col-md-3">
      			<label class="control-label">Identificación <span class="text-danger">*</span><a><i data-tippy-content="Identificación de la persona" class="icono far fa-question-circle"></i></a></label>
      			<input type="text" class="form-control" name="nit" id="nit" required="" maxlength="20" value="{{old('nit')}}" onkeypress="return event.charCode >= 48 && event.charCode <=57">
      			<span class="help-block error">
      				<strong>{{ $errors->first('nit') }}</strong>
      			</span>
      		</div>
      		<div class="form-group col-md-1" style="display: none;" id="dvnit">
      			<label class="control-label">DV <span class="text-danger">*</span></label>
      			<input type="text" class="form-control" name="dv" id="dv" disabled required="" maxlength="20" value="" onkeypress="return event.charCode >= 48 && event.charCode <=57">
      			<input type="hidden" name="dvoriginal" id="dvoriginal" value="">
      			<span class="help-block error">
      				<strong>{{ $errors->first('dv') }}</strong>
      			</span>
      		</div>
            <div class="form-group col-md-3">
                <label class="control-label">Nombres <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="nombre" id="nombre" required="" maxlength="200" value="{{old('nombre')}}">
                <span class="help-block error">
                    <strong>{{ $errors->first('nombre') }}</strong>
                </span>
            </div>
            <div class="form-group col-md-3">
                <label class="control-label">Apellido 1 <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="apellido1" id="apellido1" required="" maxlength="200" value="{{old('apellido1')}}">
                <span class="help-block error">
                    <strong>{{ $errors->first('apellido1') }}</strong>
                </span>
            </div>
            <div class="form-group col-md-3">
                <label class="control-label">Apellido 2</label>
                <input type="text" class="form-control" name="apellido2" id="apellido2" maxlength="200" value="{{old('apellido2')}}">
                <span class="help-block error">
                    <strong>{{ $errors->first('apellido2') }}</strong>
                </span>
            </div>
      	</div>
    
      	<div class="row">
      		<div class="form-group col-md-3">
      			<label class="control-label">País</label>
      			<select class="form-control   selectpicker" name="pais" id="pais" required="" title="Seleccione" data-live-search="true" data-size="5" onchange="validateCountry(this.value)">
      				@foreach($paises as $pais)
      				  <option value="{{$pais->codigo}}" {{ $pais->codigo == 'CO' ? 'selected' : '' }}>{{$pais->nombre}}</option>
      				@endforeach
      			</select>
      		</div>
      		<div class="form-group col-md-3" id="validatec1">
      			<label class="control-label">Departamento <span class="text-danger">*</span></label>
      			<select class="form-control   selectpicker" name="departamento" id="departamento" required="" title="Seleccione" data-live-search="true" data-size="5" onchange="searchMunicipality(this.value)">
      				@foreach($departamentos as $departamento)
      				  {{--<option @if($departamento->nombre == 'Atlantico') ? selected : '' @endif value="{{ $departamento->id }}">{{ $departamento->nombre }}</option>--}}
      				  <option value="{{ $departamento->id }}">{{ $departamento->nombre }}</option>
      				@endforeach
      			</select>
      		</div>
      		<div class="form-group col-md-3" id="validatec2">
      			<label class="control-label">Municipio <span class="text-danger">*</span></label>
      			<select class="form-control   selectpicker" name="municipio" id="municipio" required="" title="Seleccione" data-live-search="true" data-size="5">
      				
      			</select>
      		</div>
      		<div class="form-group col-md-3" id="validatec3">
      			<label class="control-label">Código Postal</label>
      			<a><i data-tippy-content="Si desconoces tu código postal <a target='_blank' href='http://visor.codigopostal.gov.co/472/visor/'>haz click aquí</a>" class="icono far fa-question-circle"></i></a>
      			<input type="text" class="form-control" id="cod_postal" name="cod_postal" maxlength="200"  value="{{old('cod_postal')}}" onkeypress="return event.charCode >= 48 && event.charCode <=57">
      		</div>
      		<div class="form-group col-md-6">
      			<label class="control-label">Dirección <span class="text-danger">*</span></label>
      			<input type="text" name="direccion" class="form-control" value="{{old('direccion')}}" required>
      			<span class="help-block error">
      				<strong>{{ $errors->first('direccion') }}</strong>
      			</span>
      		</div>
            <div class="form-group col-md-3">
                <label class="control-label">Corregimiento/Vereda</label>
                <input type="text" name="vereda" class="form-control" value="{{old('vereda')}}">
                <span class="help-block error">
                    <strong>{{ $errors->first('vereda') }}</strong>
                </span>
            </div>
      		<div class="form-group col-md-3">
      			<label class="control-label">Barrio </label>
      			<input type="text" name="barrio" class="form-control" value="{{old('barrio')}}">
      			<span class="help-block error">
      				<strong>{{ $errors->first('barrio') }}</strong>
      			</span>
      		</div>
      		<div class="form-group col-md-3">
      			<label class="control-label" for="email">Correo Electrónico <span class="text-danger">*</span></label>
      			<input type="email" class="form-control" id="email" name="email" data-error="Dirección de correo electrónico invalida" maxlength="100"  value="{{old('email')}}" required>
      			<div class="help-block error with-errors"></div>
      			<span class="help-block error">
      				<strong>{{ $errors->first('email') }}</strong>
      			</span>
      		</div>
      	</div>
    
      	<div class="row">
      		<div class="form-group col-md-3">
      			<label class="control-label">Celular <span class="text-danger">*</span></label>
      			<input type="text" class="form-control" id="celular" name="celular" maxlength="15" value="{{old('celular')}}" onkeypress="return event.charCode >= 48 && event.charCode <=57" required>
      			<span class="help-block error">
      				<strong>{{ $errors->first('celular') }}</strong>
      			</span>
      		</div>
      		<div class="form-group col-md-3">
      			<label class="control-label">Teléfono</label>
      			<input type="text" class="form-control" id="telefono1" name="telefono1" maxlength="15" value="{{old('telefono1')}}" onkeypress="return event.charCode >= 48 && event.charCode <=57">
      			<span class="help-block error">
      				<strong>{{ $errors->first('telefono1') }}</strong>
      			</span>
      		</div>
      		<div class="form-group col-md-3">
      			<label class="control-label">Teléfono 2</label>
      			<input type="text" class="form-control" id="telefono2" name="telefono2" maxlength="15" value="{{old('telefono2')}}" onkeypress="return event.charCode >= 48 && event.charCode <=57">
      			<span class="help-block error">
      				<strong>{{ $errors->first('telefono2') }}</strong>
      			</span>
      		</div>
      		<div class="form-group col-md-3">
      			<label class="control-label">Fax</label>
      			<input type="text" class="form-control" id="fax" name="fax" maxlength="15" value="{{old('fax')}}" onkeypress="return event.charCode >= 48 && event.charCode <=57">
      			<span class="help-block error">
      				<strong>{{ $errors->first('fax') }}</strong>
      			</span>
      		</div>
      	</div>
    
      	<div class="row">
            <div class="form-group col-md-3">
                <label class="control-label">Estrato</label>
                <select class="form-control selectpicker" id="estrato" name="estrato"  title="Seleccione" data-live-search="true" data-size="5">
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                    <option value="6">6</option>
                </select>
                <span class="help-block error">
                    <strong>{{ $errors->first('estato') }}</strong>
                </span>
            </div>
      	    <div class="form-group col-md-3">
    			<label class="control-label">Tipo de Contacto <span class="text-danger">*</span></label>
    			<div class="form-check form-check-flat">
    				<label class="form-check-label">
    					<input type="checkbox" class="form-check-input" name="tipo_contacto[]" value="0" checked=""> Cliente
    					<i class="input-helper"></i></label>
    			</div>
    			<div class="form-check form-check-flat">
    				<label class="form-check-label">
    					<input type="checkbox" class="form-check-input" name="tipo_contacto[]" value="1"> Proveedor
    					<i class="input-helper"></i></label>
    			</div>
    			<span class="help-block error">
    				<strong>{{ $errors->first('tipo_contacto') }}</strong>
    			</span>
    		</div>
            <div class="form-group col-md-3">
                <label class="control-label">Oficina Asociada <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" name="oficina" id="oficina" required="" title="Seleccione" data-live-search="true" data-size="5">
                    @foreach($oficinas as $oficina)
                    <option value="{{$oficina->id}}" {{ $oficina->id == auth()->user()->oficina ? 'selected' : '' }}>{{$oficina->nombre}}</option>
                    @endforeach
                </select>
            </div>
      		<div class="form-group col-md-12">
      			<label class="control-label">Observaciones</label>
      			<textarea class="form-control" name="observaciones" >{{old('observaciones')}}</textarea>
      			<span class="help-block error">
      				<strong>{{ $errors->first('observaciones') }}</strong>
      			</span>
      		</div>
      	</div>
      	
      	<small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
  	</div>
  	
  	<div class="row" style="text-align: right;">
  	    <div class="col-md-12">
  	        <hr>
  	        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal" id="cancelarc">Cancelar</button>
            <button id="guardarc" type="submit" class="btn btn-success mr-5">Guardar</button>
        </div>
    </div>
</form>

@section('scripts')
    <script>
        $('#us2').locationpicker({
            location: {
                latitude: {{ $gmaps->latitude }},
                longitude: {{ $gmaps->longitude }}
            },
            zoom: 6,
            radius: 300,
            inputBinding: {
                latitudeInput: $('#us2-lat'),
                longitudeInput: $('#us2-lon'),
                radiusInput: $('#us2-radius'),
                locationNameInput: $('#us2-address')
            },
            mapTypeId: google.maps.MapTypeId.roadmap,
        });

        $("#formulario").submit(function () {
            return false;
        });

        $(document).ready(function () {
            $('#mac_address').mask('AA:AA:AA:AA:AA:AA', {
                'translation': {A: {pattern: /[0-9a-fA-F]/}},
            });
            $('.mac_address').mask('AA:AA:AA:AA:AA:AA', {
                'translation': {A: {pattern: /[0-9a-fA-F]/}},
            });

            $('#departamento').val({{ Auth::user()->empresa()->fk_iddepartamento }}).selectpicker('refresh');
            searchMunicipality({{ Auth::user()->empresa()->fk_iddepartamento }}, {{ Auth::user()->empresa()->fk_idmunicipio }});

            $("#guardarc").click(function (formulario) {
                $.post($("#formulario").attr('action'), $("#formulario").serialize(), function (dato) {
                    if(dato['status']=='OK'){
                        var select = $('#client_id');
                        select.append('<option value=' + dato['id'] + ' selected>' + dato['contacto'] + ' - ' + dato['nit'] + '</option>');
                        select.selectpicker('refresh');
                        $("#client_id").trigger('change');
                        $('#cancelarc').click();
                        $('#formulario').trigger("reset");
                        swal("Registro Guardado", "Nuevo Cliente Agregado!!!", "success");
                    } else {
                        swal('Info!!', dato['mensaje'], "error");
                    }
                }, 'json');
            });

            $('#tip_iden').change(function(){
                if($('#tip_iden').val() == 6){
                    searchDV($("#tip_iden").val());
                }else{
                    document.getElementById("dvnit").style.display = "none"
                }
            });

            if($("#servidor").val() > 0){
                getPlanes($("#servidor").val());
                $("#conexion").val($("#conexion_bd").val());
                $("#local_address").val($("#segmento_bd").val());
                $("#local_address").selectpicker('refresh');
            }

            $('#contrato_permanencia').change(function(){
                if($('#contrato_permanencia').val() == 1){
                    $("#div_meses").removeClass('d-none');
                    $("#contrato_permanencia_meses").val('').selectpicker('refresh');
                }else{
                    $("#contrato_permanencia_meses").val('').selectpicker('refresh');
                    $("#div_meses").addClass('d-none');
                }
            });
        });

        setTimeout(function () {
            $("#municipio").val({{ Auth::user()->empresa()->fk_idmunicipio }});
            $("#municipio").selectpicker('refresh');
            $('#clnte').prop('checked', true);
            clearTimeout(this);
        }, 1000);
    </script>
@endsection