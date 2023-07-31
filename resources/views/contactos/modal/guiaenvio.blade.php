{{ csrf_field() }}
<input type="hidden" name="facturaguia" value="{{$facturaid}}">
@if($guia_envio != null)


<input type="hidden"  id="idmunicipio_destino" value="{{ $guia_envio->municipio_id }}">
  			<input type="hidden"  id="iddepartamento_destino" value="{{ $guia_envio->departamento_id }}">
            <input type="hidden" id="pastpais_destino" value="{{$guia_envio->pais_id}}">
            <input type="hidden" name="tipoguia" value="{{$tipo}}">
			<input type="hidden" name="guia_id" value="{{$guia_envio->id}}">


			<div class="card card-body">
			  <div class="row">


				  <div class="col-md-12">
					  <h4>Remitente</h4>
						  <div class="row">
								<div class="form-group col-md-3">
									<label class="control-label">Tipo de Identificación <span class="text-danger">*</span></label>
									<select class="form-control selectpicker" name="tip_iden_remitente" id="tip_iden_remitente" required="" onchange="guiaenviosearchDV(this.value,1)" title="Seleccione">
										@foreach($identificaciones as $identificacion)
										<option {{$guia_envio->tipiden_remitente_id==$identificacion->id?'selected':''}} value="{{$identificacion->id}}" title="{{$identificacion->mini()}}" >{{$identificacion->identificacion}}</option>
										@endforeach
								</select>
								<span class="help-block error">
									<strong>{{ $errors->first('tip_iden_remitente') }}</strong>
								</span>
								</div>
								<div class="form-group col-md-3">
									<label class="control-label">Identificación <span class="text-danger">*</span><a><i data-tippy-content="Identificación de la persona" class="icono far fa-question-circle"></i></a></label>
								<input type="text" class="form-control" name="nitremitente" id="nitremitente" required="" maxlength="20" value="{{$guia_envio->identificacion_remitente}}">
								<span class="help-block error">
									<strong>{{ $errors->first('nitremitente') }}</strong>
								</span>
							</div>
						
								<div class="form-group col-md-1" style="display: none;" id="dvremitente">
								<label class="control-label">DV <span class="text-danger">*</span></label>
								<input type="text" class="form-control" name="dvremitente" id="dvinputr" readonly required="" maxlength="20" value="{{$guia_envio->dvremitente}}">
								<span class="help-block error">
									<strong>{{ $errors->first('dvremitente') }}</strong>
								</span>
							</div>

							<div class="form-group col-md-5">
								<label class="control-label">Nombre <span class="text-danger">*</span></label>
								<input type="text" class="form-control" name="nombre_remitente" id="nombre_remitente" required="" maxlength="200" value="{{$guia_envio->nombre_remitente}}">
								<span class="help-block error">
									<strong>{{ $errors->first('nombre_remitente') }}</strong>
								</span>
							</div>

						  </div>


					  <div class="row">
						<div class="form-group col-md-4">
							<label class="control-label">Dirección <span class="text-danger">*</span></label>
							<textarea class="form-control" name="direccion_remitente" >{{$guia_envio->direccion_remitente}}</textarea>
							<span class="help-block error">
								<strong>{{ $errors->first('direccion_remitente') }}</strong>
							</span>
						</div>

						<div class="form-group col-md-4">
							<label class="control-label">Teléfono</label>
							<div class="row">
								<div class="col-md-4 nopadding ">
									<select class="form-control selectpicker prefijo" name="prefijo_remitente" id="prefijo_remitente" title="Seleccione" data-size="5" data-live-search="true">
										@foreach($prefijos as $prefijo)
										<option @if($guia_envio->prefijo_remitente_id) {{'+'.$prefijo->phone_code==$guia_envio->prefijo_remitente_id?'selected':''}}  @endif

											data-icon="flag-icon flag-icon-{{strtolower($prefijo->iso2)}}"
	 
										value="+{{$prefijo->phone_code}}" title="+{{$prefijo->phone_code}}" data-subtext="+{{$prefijo->phone_code}}">{{$prefijo->nombre}}</option>
										@endforeach
								  </select>
								</div>
								<div class="col-md-8" style="padding-left:0;">
									<input type="text" class="form-control" id="telefono_remitente" name="telefono_remitente" maxlength="15" value="{{$guia_envio->telefono_remitente}}">
								</div>
							</div>
						  <span class="help-block error">
							  <strong>{{ $errors->first('telefono_remitente') }}</strong>
						  </span>
					  </div>

						<div class="form-group col-md-4">
							<label class="control-label">Email<span class="text-danger">*</span></label>
							<input type="email" class="form-control" name="email_remitente" id="email_remitente" required="" maxlength="200" value="{{$guia_envio->email_remitente}}">
							<span class="help-block error">
								<strong>{{ $errors->first('email_remitente') }}</strong>
							</span>
						</div>
					  </div>

					</div>


			  </div>

			  <div class="row mt-2">
				<div class="col-md-12">
					<h4>Destinatario</h4>

					<div class="row">
						<div class="form-group col-md-3">
							<label class="control-label">Tipo de Identificación <span class="text-danger">*</span></label>
							<select class="form-control selectpicker" name="tip_iden_destino" id="tip_iden_destino" required="" onchange="guiaenviosearchDV(this.value,2)" title="Seleccione">
								@foreach($identificaciones as $identificacion)
								<option {{$guia_envio->tipiden_destino_id==$identificacion->id?'selected':''}} value="{{$identificacion->id}}" title="{{$identificacion->mini()}}" >{{$identificacion->identificacion}}</option>
								@endforeach
						</select>
						<span class="help-block error">
							<strong>{{ $errors->first('tip_iden_destino') }}</strong>
						</span>
						</div>
						<div class="form-group col-md-3">
							<label class="control-label">Identificación <span class="text-danger">*</span><a><i data-tippy-content="Identificación de la persona" class="icono far fa-question-circle"></i></a></label>
						<input type="text" class="form-control" name="nitdestino" id="nitdestino" required="" maxlength="20" value="{{$guia_envio->identificacion_destino}}">
						<span class="help-block error">
							<strong>{{ $errors->first('nitdestino') }}</strong>
						</span>
					</div>
				
						<div class="form-group col-md-1" style="display: none;" id="dvdestino">
						<label class="control-label">DV <span class="text-danger">*</span></label>
						<input type="text" class="form-control" name="dvdestino" id="dvinputd" readonly required="" maxlength="20" value="{{$guia_envio->dvdestino}}">
						<span class="help-block error">
							<strong>{{ $errors->first('dvdestino') }}</strong>
						</span>
					</div>

					<div class="form-group col-md-5">
						<label class="control-label">Nombre <span class="text-danger">*</span></label>
						<input type="text" class="form-control" name="nombre_destino" id="nombre_destino" required="" maxlength="200" value="{{$guia_envio->nombre_destino}}">
						<span class="help-block error">
							<strong>{{ $errors->first('nombre_destino') }}</strong>
						</span>
					</div>

				  </div>


			  <div class="row">
				<div class="form-group col-md-4">
					<label class="control-label">Dirección <span class="text-danger">*</span></label>
					<textarea class="form-control" name="direccion_destino" >{{$guia_envio->direccion_destino}}</textarea>
					<span class="help-block error">
						<strong>{{ $errors->first('direccion_destino') }}</strong>
					</span>
				</div>

				<div class="form-group col-md-4">
					<label class="control-label">Teléfono</label>
					<div class="row">
						<div class="col-md-4 nopadding ">
							<select class="form-control selectpicker prefijo" name="prefijo_destino" id="prefijo_destino" title="Seleccione" data-size="5" data-live-search="true">
								@foreach($prefijos as $prefijo)
								<option @if($guia_envio->prefijo_destino_id) {{'+'.$prefijo->phone_code==$guia_envio->prefijo_destino_id?'selected':''}}  @endif

									data-icon="flag-icon flag-icon-{{strtolower($prefijo->iso2)}}"

								value="+{{$prefijo->phone_code}}" title="+{{$prefijo->phone_code}}" data-subtext="+{{$prefijo->phone_code}}">{{$prefijo->nombre}}</option>
								@endforeach
						  </select>
						</div>
						<div class="col-md-8" style="padding-left:0;">
							<input type="text" class="form-control" id="telefono_destino" name="telefono_destino" maxlength="15" value="{{$guia_envio->telefono_destino}}">
						</div>
					</div>
				  <span class="help-block error">
					  <strong>{{ $errors->first('telefono_destino') }}</strong>
				  </span>
			  </div>

				<div class="form-group col-md-4">
					<label class="control-label">Transportadora<span class="text-danger">*</span></label>
					<select class="form-control selectpicker" name="transportadora" id="transportadora" required="" title="Seleccione">
						@foreach($transportadoras as $transportadora)
							<option {{$guia_envio->transportadora_id==$transportadora->id?'selected':''}} value="{{$transportadora->id}}">{{$transportadora->nombre}}</option>
						@endforeach
				</select>
				<span class="help-block error">
					<strong>{{ $errors->first('transportadora') }}</strong>
				</span>
				</div>
			  </div>

			  <div class="row">
				<div class="form-group col-md-4">
					<label class="control-label">País</label>
					<select class="form-control selectpicker" name="pais_destino" id="pais_destino" required="" title="Seleccione" data-live-search="true" data-size="5" onchange="validateCountryGuia(this.value)">
						@foreach($paises as $pais)
						<option value="{{$pais->codigo}}" {{ $guia_envio->pais_id == $pais->codigo ? 'selected' : '' }}>{{$pais->nombre}}</option>
						@endforeach	
					</select>
				</div>


					<div class="form-group col-md-4" id="validatedestino1">
						<label class="control-label">Departamento <span class="text-danger">*</span></label>
						<select class="form-control selectpicker" name="departamento_destino" id="departamento_destino" required="" title="Seleccione" data-live-search="true" data-size="5" onchange="searchMunicipalityDestino(this.value)">
							@foreach($departamentos as $departamento)
							<option value="{{ $departamento->id }}"
								{{ $guia_envio->departamento_id == $departamento->id ? 'selected' : '' }}
								>{{ $departamento->nombre }}</option>
							@endforeach
						</select>
					</div>
			
					<div class="form-group col-md-4" id="validatedestino2">
						<label class="control-label">Municipio <span class="text-danger">*</span></label>
						<select class="form-control selectpicker" name="municipio_destino" id="municipio_destino" required="" title="Seleccione" data-live-search="true" data-size="5">
							<option selected value="{{ $guia_envio->municipio_id }}">  {{$guia_envio->municipio_id != null ? \App\Contacto::municipio_static($guia_envio->municipio_id)->nombre : 'no seleccionado' }}</option>
						</select>
					</div>
				
			  </div>

				</div>
			  </div>

            </div>

        @else





        <div class="card card-body">
            <div class="row">


                <div class="col-md-12">
                    <h4>Remitente</h4>
                        <div class="row">
                              <div class="form-group col-md-3">
                                  <label class="control-label">Tipo de Identificación <span class="text-danger">*</span></label>
                                  <select class="form-control selectpicker" name="tip_iden_remitente" id="tip_iden_remitente" required="" onchange="guiaenviosearchDV(this.value,1)" title="Seleccione">
                                      @foreach($identificaciones as $identificacion)
                                          <option {{old('tip_iden_remitente')==$identificacion->id?'selected':''}} value="{{$identificacion->id}}" title="{{$identificacion->mini()}}">{{$identificacion->identificacion}}</option>
                                      @endforeach
                              </select>
                              <span class="help-block error">
                                  <strong>{{ $errors->first('tip_iden_remitente') }}</strong>
                              </span>
                              </div>
                              <div class="form-group col-md-3">
                                  <label class="control-label">Identificación <span class="text-danger">*</span><a><i data-tippy-content="Identificación de la persona" class="icono far fa-question-circle"></i></a></label>
                              <input type="text" class="form-control" name="nitremitente" id="nitremitente" required="" maxlength="20" value="{{old('nitremitente')}}">
                              <span class="help-block error">
                                  <strong>{{ $errors->first('nitremitente') }}</strong>
                              </span>
                          </div>
                      
                              <div class="form-group col-md-1" style="display: none;" id="dvremitente">
                              <label class="control-label">DV <span class="text-danger">*</span></label>
                              <input type="text" class="form-control" name="dvremitente" id="dvinputr" readonly required="" maxlength="20" value="">
                              <span class="help-block error">
                                  <strong>{{ $errors->first('dvremitente') }}</strong>
                              </span>
                          </div>

                          <div class="form-group col-md-5">
                              <label class="control-label">Nombre <span class="text-danger">*</span></label>
                              <input type="text" class="form-control" name="nombre_remitente" id="nombre_remitente" required="" maxlength="200" value="{{old('nombre_remitente')}}">
                              <span class="help-block error">
                                  <strong>{{ $errors->first('nombre_remitente') }}</strong>
                              </span>
                          </div>

                        </div>


                    <div class="row">
                      <div class="form-group col-md-4">
                          <label class="control-label">Dirección <span class="text-danger">*</span></label>
                          <textarea class="form-control" name="direccion_remitente" >{{old('direccion_remitente')}}</textarea>
                          <span class="help-block error">
                              <strong>{{ $errors->first('direccion_remitente') }}</strong>
                          </span>
                      </div>

                      <div class="form-group col-md-4">
                          <label class="control-label">Teléfono</label>
                          <div class="row">
                              <div class="col-md-4 nopadding ">
                                  <select class="form-control selectpicker prefijo" name="prefijo_remitente" id="prefijo_remitente" title="Seleccione" data-size="5" data-live-search="true">
                                      @foreach($prefijos as $prefijo)
                                          <option @if(old('prefijo_remitente')) {{old('prefijo_remitente')==$prefijo->phone_code?'selected':''}} @else {{'+'.$prefijo->phone_code==Auth::user()->empresa()->codigo?'selected':''}}  @endif
        
                                               data-icon="flag-icon flag-icon-{{strtolower($prefijo->iso2)}}"
        
                                           value="+{{$prefijo->phone_code}}" title="+{{$prefijo->phone_code}}" data-subtext="+{{$prefijo->phone_code}}">{{$prefijo->nombre}}</option>
                                      @endforeach
                                </select>
                              </div>
                              <div class="col-md-8" style="padding-left:0;">
                                  <input type="text" class="form-control" id="telefono_remitente" name="telefono_remitente" maxlength="15" value="{{old('telefono_remitente')}}">
                              </div>
                          </div>
                        <span class="help-block error">
                            <strong>{{ $errors->first('telefono_remitente') }}</strong>
                        </span>
                    </div>

                      <div class="form-group col-md-4">
                          <label class="control-label">Email<span class="text-danger">*</span></label>
                          <input type="email" class="form-control" name="email_remitente" id="email_remitente" required="" maxlength="200" value="{{old('email_remitente')}}">
                          <span class="help-block error">
                              <strong>{{ $errors->first('email_remitente') }}</strong>
                          </span>
                      </div>
                    </div>

                  </div>


            </div>

            <div class="row mt-2">
              <div class="col-md-12">
                  <h4>Destinatario</h4>

                  <div class="row">
                      <div class="form-group col-md-3">
                          <label class="control-label">Tipo de Identificación <span class="text-danger">*</span></label>
                          <select class="form-control selectpicker" name="tip_iden_destino" id="tip_iden_destino" required="" onchange="guiaenviosearchDV(this.value,2)" title="Seleccione">
                              @foreach($identificaciones as $identificacion)
                                  <option {{old('tip_iden_destino')==$identificacion->id?'selected':''}} value="{{$identificacion->id}}" title="{{$identificacion->mini()}}">{{$identificacion->identificacion}}</option>
                              @endforeach
                      </select>
                      <span class="help-block error">
                          <strong>{{ $errors->first('tip_iden_destino') }}</strong>
                      </span>
                      </div>
                      <div class="form-group col-md-3">
                          <label class="control-label">Identificación <span class="text-danger">*</span><a><i data-tippy-content="Identificación de la persona" class="icono far fa-question-circle"></i></a></label>
                      <input type="text" class="form-control" name="nitdestino" id="nitdestino" required="" maxlength="20" value="{{old('nitdestino')}}">
                      <span class="help-block error">
                          <strong>{{ $errors->first('nitdestino') }}</strong>
                      </span>
                  </div>
              
                      <div class="form-group col-md-1" style="display: none;" id="dvdestino">
                      <label class="control-label">DV <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" name="dvdestino" id="dvinputd" readonly required="" maxlength="20" value="">
                      <span class="help-block error">
                          <strong>{{ $errors->first('dvdestino') }}</strong>
                      </span>
                  </div>

                  <div class="form-group col-md-5">
                      <label class="control-label">Nombre <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" name="nombre_destino" id="nombre_destino" required="" maxlength="200" value="{{old('nombre_destino')}}">
                      <span class="help-block error">
                          <strong>{{ $errors->first('nombre_destino') }}</strong>
                      </span>
                  </div>

                </div>


            <div class="row">
              <div class="form-group col-md-4">
                  <label class="control-label">Dirección <span class="text-danger">*</span></label>
                  <textarea class="form-control" name="direccion_destino" >{{old('direccion_destino')}}</textarea>
                  <span class="help-block error">
                      <strong>{{ $errors->first('direccion_destino') }}</strong>
                  </span>
              </div>

              <div class="form-group col-md-4">
                  <label class="control-label">Teléfono</label>
                  <div class="row">
                      <div class="col-md-4 nopadding ">
                          <select class="form-control selectpicker prefijo" name="prefijo_destino" id="prefijo_destino" title="Seleccione" data-size="5" data-live-search="true">
                              @foreach($prefijos as $prefijo)
                                  <option @if(old('prefijo_destino')) {{old('prefijo_destino')==$prefijo->phone_code?'selected':''}} @else {{'+'.$prefijo->phone_code==Auth::user()->empresa()->codigo?'selected':''}}  @endif

                                       data-icon="flag-icon flag-icon-{{strtolower($prefijo->iso2)}}"

                                   value="+{{$prefijo->phone_code}}" title="+{{$prefijo->phone_code}}" data-subtext="+{{$prefijo->phone_code}}">{{$prefijo->nombre}}</option>
                              @endforeach
                        </select>
                      </div>
                      <div class="col-md-8" style="padding-left:0;">
                          <input type="text" class="form-control" id="telefono_destino" name="telefono_destino" maxlength="15" value="{{old('telefono_destino')}}">
                      </div>
                  </div>
                <span class="help-block error">
                    <strong>{{ $errors->first('telefono_destino') }}</strong>
                </span>
            </div>

              <div class="form-group col-md-4">
                  <label class="control-label">Transportadora<span class="text-danger">*</span></label>
                  <select class="form-control selectpicker" name="transportadora" id="transportadora" required="" title="Seleccione">
                      @foreach($transportadoras as $transportadora)
                          <option {{old('transportadora')==$transportadora->id?'selected':''}} value="{{$transportadora->id}}">{{$transportadora->nombre}}</option>
                      @endforeach
              </select>
              <span class="help-block error">
                  <strong>{{ $errors->first('transportadora') }}</strong>
              </span>
              </div>
            </div>

            <div class="row">
              <div class="form-group col-md-4">
                  <label class="control-label">País</label>
                  <select class="form-control selectpicker" name="pais_destino" id="pais_destino" required="" title="Seleccione" data-live-search="true" data-size="5" onchange="validateCountryGuia(this.value)">
                      @foreach($paises as $pais)
                      <option value="{{$pais->codigo}}" {{ $pais->codigo == 'CO' ? 'selected' : '' }}>{{$pais->nombre}}</option>
                      @endforeach	
                  </select>
              </div>
              
                  <div class="form-group col-md-4" id="validatedestino1">
                      <label class="control-label">Departamento <span class="text-danger">*</span></label>
                      <select class="form-control selectpicker" name="departamento_destino" id="departamento_destino" required="" title="Seleccione" data-live-search="true" data-size="5" onchange="searchMunicipalityDestino(this.value)">
                          @foreach($departamentos as $departamento)
                          <option value="{{ $departamento->id }}">{{ $departamento->nombre }}</option>
                          @endforeach
                      </select>
                  </div>
          
                  <div class="form-group col-md-4" id="validatedestino2">
                      <label class="control-label">Municipio <span class="text-danger">*</span></label>
                      <select class="form-control selectpicker" name="municipio_destino" id="municipio_destino" required="" title="Seleccione" data-live-search="true" data-size="5">
                      </select>
                  </div>
              
            </div>


              </div>
            </div>

          </div>

    @endif

    <div class="row" style="text-align: right;">
        <div class="col-md-12">
          <a href="#" class="btn btn-outline-light" >Cancelar</a>
            <button target="_blank" type="submit" class="btn btn-success">Asociar</button>
        </div>
    </div>

    <script>
        validateCountryGuia($("#pastpais_destino").val());
        
        //Refrescar selectpickers
        $("#tip_iden_remitente").selectpicker();
        $("#tip_iden_destino").selectpicker();
        $("#pais_destino").selectpicker();
        $("#departamento_destino").selectpicker();
        $("#municipio_destino").selectpicker();
        $("#transportadora").selectpicker();
        $("#prefijo_remitente").selectpicker();
        $("#prefijo_destino").selectpicker();
        
    </script>