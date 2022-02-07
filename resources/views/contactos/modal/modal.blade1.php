<form method="POST" action="{{ route('contactos.storeBack') }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-contacto">
    {{ csrf_field() }}
    <div class="row">
        <div class="form-group col-md-3">
            <label class="control-label">Tipo de Identificación <span class="text-danger">*</span></label>
            <select class="form-control selectpicker" name="tip_iden" id="tip_iden" required="" title="Seleccione">
                @foreach($identificaciones as $identificacion)
                    <option {{old('tip_iden')==$identificacion->id?'selected':''}} value="{{$identificacion->id}}" title="{{$identificacion->mini()}}">{{$identificacion->identificacion}}</option>
                @endforeach
            </select>
            <span class="help-block error">
		        	<strong>{{ $errors->first('tip_iden') }}</strong>
		        </span>
        </div>
        <div class="form-group col-md-3">
            <label class="control-label">Identificación <span class="text-danger">*</span><a><i data-tippy-content="Identificación de la persona" class="icono far fa-question-circle"></i></a></label>
            <input type="text" class="form-control" name="nit" id="nit" required="" maxlength="20" value="{{old('nit')}}">
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
					<strong>{{ $errors->first('ciudad') }}</strong>
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
            <label class="control-label">Teléfono  <span class="text-danger">*</span></label>
            <div class="row">
                <div class="col-md-4 nopadding ">
                    <select class="form-control selectpicker prefijo" name="pref1" id="pref1" required="" title="Seleccione" data-size="5" data-live-search="true">
                        @foreach($prefijos as $prefijo)
                            <option @if(old('pref1')) {{old('pref1')==$prefijo->phone_code?'selected':''}} @else {{'+'.$prefijo->phone_code==Auth::user()->empresa()->codigo?'selected':''}}  @endif

                                    data-icon="flag-icon flag-icon-{{strtolower($prefijo->iso2)}}"

                                    value="+{{$prefijo->phone_code}}" title="+{{$prefijo->phone_code}}" data-subtext="+{{$prefijo->phone_code}}">{{$prefijo->nombre}}</option>
                        @endforeach
                    </select>


                </div>
                <div class="col-md-8" style="padding-left:0;">
                    <input type="text" class="form-control" id="telefono1" name="telefono1" required="" maxlength="15" value="{{old('telefono1')}}">
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
                    <select class="form-control selectpicker prefijo" name="pref2" id="pref2" title="Seleccione" data-size="5" data-live-search="true">
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
                    <select class="form-control selectpicker prefijo" name="preffax" id="preffax" title="Seleccione" data-size="5" data-live-search="true">
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
                    <select class="form-control selectpicker prefijo" name="prefcelular" id="prefcelular" title="Seleccione" data-size="5" data-live-search="true">
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
            <label class="control-label">Tipo de Contacto  <span class="text-danger">*</span></label>
            <div class="form-check form-check-flat">
                <label class="form-check-label">
                    <input type="checkbox" class="form-check-input" name="contacto[]" value="0"> Cliente
                    <i class="input-helper"></i></label>
            </div>
            <div class="form-check form-check-flat">
                <label class="form-check-label">
                    <input type="checkbox" class="form-check-input" name="contacto[]" value="1" id="prove"> Proveedor
                    <i class="input-helper"></i></label>
            </div>
            <span class="help-block error">
					<strong>{{ $errors->first('contacto') }}</strong>
				</span>
        </div>

        <div class="form-group col-md-3">
            <label class="control-label">Tipo de Empresa <span class="text-danger">*</span><a><i data-tippy-content="Tipo empresa a la que pertenece el contacto" class="icono far fa-question-circle"></i></a></label>
            <select class="form-control selectpicker" name="tipo_empresa" id="tipo_empresa" required="" title="Seleccione" data-live-search="true" data-size="5">
                @foreach($tipos_empresa as $tipo_empresa)
                    <option {{old('tipo_empresa')==$tipo_empresa->id?'selected':''}} value="{{$tipo_empresa->id}}">{{$tipo_empresa->nombre}}</option>
                @endforeach
            </select>
            <p class="text-left nomargin"> <a href="{{route('tiposempresa.create')}}" target="_blanck"><i class="fas fa-plus"></i> Nuevo Tipo de Empresa</a></p>
            <span class="help-block error">
		        	<strong>{{ $errors->first('tipo_empresa') }}</strong>
		        </span>
        </div>

        <div class="form-group col-md-3">
            <label class="control-label">Lista de Precios <a><i data-tippy-content="Lista de precios que desee asociar a este contacto" class="icono far fa-question-circle"></i></a></label>
            <select class="form-control selectpicker" name="lista_precio" id="lista_precio" title="Seleccione" data-size="5">

                @foreach($listas as $lista)
                    <option {{old('lista_precio')==$lista->id?'selected':''}} value="{{$lista->id}}">{{$lista->nombre()}}</option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-3">
            <label class="control-label">Vendedor <a><i data-tippy-content="Vendedor que desee asociar a este contacto" class="icono far fa-question-circle"></i></a></label>
            <select class="form-control selectpicker" name="vendedor" id="vendedor" title="Seleccione" data-live-search="true" data-size="5">
                @foreach($vendedores as $vendedor)
                    <option {{old('vendedor')==$vendedor->id?'selected':''}} value="{{$vendedor->id}}">{{$vendedor->nombre}}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-12">
            <label class="control-label">Observaciones</label>

            <textarea class="form-control" name="observaciones" >{{old('observaciones')}}</textarea>
            <span class="help-block error">
					<strong>{{ $errors->first('observaciones') }}</strong>
				</span>
        </div>
    </div>
    <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
    <div class="row" style="margin-top: 2%;">
        <div class="col-md-12">

            <h4 class="card-title">Personas Asociadas</h4>
            <table class="table table-sm table-striped" id="table-form-contacto" width="100%">
                <thead class="thead-dark">
                <tr>
                    <th width="27%">Nombre	y Apellido</th>
                    <th width="28%">Correo Electrónico</th>
                    <th width="15%">Teléfono</th>
                    <th width="15%">Celular</th>
                    <th width="10%">Enviar Notificaciones <a><i data-tippy-content="Marque 'si' cuando desee que esta persona reciba correos con facturas disponibles o vencidas"  class="icono far fa-question-circle"></i></a></th>
                    <th width="5%"></th>
                </tr>
                </thead>
                <tbody>
                </tbody>

            </table>
            <button class="btn btn-outline-primary" onclick="createRowContato();" type="button" >Asociar Persona</button><a><i data-tippy-content="En caso de ser una empresa, personas pertenecientes" class="icono far fa-question-circle"></a></i>

        </div>
    </div>
    <hr>
    <div class="row" style="text-align: right;">
        <div class="col-md-12">
            <a href="{{route('empresas.index')}}" class="btn btn-outline-light" >Cancelar</a>
            <button type="submit" class="btn btn-success">Guardar</button>
        </div>
    </div>
</form>

<script>
    $(document).ready(function(){

        setTimeout(function () {
            $('#prove').prop('checked', true);
            clearTimeout(this);
        }, 1000);
    });
</script>
