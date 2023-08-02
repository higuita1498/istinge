<style>
	.select-group input.form-control{ width: 65%}
	.select-group select.input-group-addon { width: 35%; }
	.input-group-addon{
		background: #f9f9f9;
		border-color: #dee4e6;
		font-size: .9em;
		font-weight: bold;
		color: #495057;
		padding: 0 1% 0 2%;
	}
    .input-group-prepend .input-group-text {
	    background: #f9f9f9;
	    border-color: #dee4e6;
	    font-size: 0.9rem;
	}
</style>
<form method="POST" action="{{ route('planes-velocidad.storeBack') }}" style="padding: 0%;" role="form" class="forms-sample" novalidate id="formPlan">
    @csrf
    <div class="row" style="text-align: center;">
        <div class="col-md-12">
            <h3>AGREGAR NUEVO PLAN DE VELOCIDAD</h3>
            <hr>
        </div>
    </div>

	<div style="padding: 1% 3%;">
	    <div class="row">
	    	<div class="col-md-12">
		    	<ul class="nav nav-pills mb-" id="pills-tab" role="tablist">
			        <li class="nav-item">
			            <a class="nav-link active" id="pills-basica-tab" data-toggle="pill" href="#pills-basica" role="tab" aria-controls="pills-basica" aria-selected="true">Configuración Básica</a>
			        </li>
			        <li class="nav-item">
			            <a class="nav-link" id="pills-avanzado-tab" data-toggle="pill" href="#pills-avanzado" role="tab" aria-controls="pills-avanzado" aria-selected="false">Configuración Avanzada</a>
			        </li>
			    </ul>

			    <hr style="border-top: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}; margin: .5rem 0rem 2rem;">

			    <div class="tab-content" id="pills-tabContent">
			        <div class="tab-pane fade show active" id="pills-basica" role="tabpanel" aria-labelledby="pills-basica-tab">
			            <div class="row">
		        	        <div class="col-md-3 form-group">
		        	            <label class="control-label">Mikrotik Asociada <span class="text-danger">*</span></label>
		        	            <select name="mikrotik[]" id="mikrotik" class="form-control selectpicker " title="Seleccione" data-live-search="true" data-size="5" required>
		                        @foreach($servidores as $mikrotik)
		                            <option value="{{$mikrotik->id}}">{{$mikrotik->nombre}}</option>
		                        @endforeach
		                        </select>
		        	            <span class="help-block error">
		        	                <strong>{{ $errors->first('mikrotik') }}</strong>
		        	            </span>
		        	        </div>
		        	        <div class="col-md-3 form-group">
		        	            <label class="control-label">Nombre <span class="text-danger">*</span></label>
		        	            <input type="text" class="form-control"  id="name" name="name"  required="" value="{{old('name')}}" maxlength="200">
		        	            <span class="help-block error">
		        	                <strong>{{ $errors->first('name') }}</strong>
		        	            </span>
		        	        </div>
		        	        <div class="col-md-3 form-group">
		        	            <label class="control-label">Precio <span class="text-danger">*</span></label>
		        	            <div class="input-group mb-2">
		        	            	<input type="number" class="form-control"  id="price" name="price"  required="" value="{{old('price')}}" maxlength="200" onkeypress="return event.charCode >= 48 && event.charCode <=57" min="0">
		        	            	<div class="input-group-prepend">
		        	            		<div class="input-group-text font-weight-bold">{{ Auth::user()->empresa()->moneda }}</div>
		        	            	</div>
		        	            </div>
		        	            <span class="help-block error">
		        	                <strong>{{ $errors->first('price') }}</strong>
		        	            </span>
		        	        </div>
		        	        <div class="col-md-3 form-group">
		        	            <label class="control-label">Vel. de Descarga <span class="text-danger">*</span></label>
		        	            <div class="input-group mb-2">
		        	            	<input type="number" class="form-control"  id="download" name="download"  required="" value="{{old('download')}}" maxlength="200" min="0" onkeypress="return event.charCode >= 48 && event.charCode <=57">
		        	            	<select class="input-group-addon" name="inicial_download">
										<option value="k">Kbps</option>
										<option value="M">Mbps</option>
									</select>
		        	            </div>
		        	            <span class="help-block error">
		        	                <strong>{{ $errors->first('download') }}</strong>
		        	            </span>
		        	        </div>
		        	        <div class="col-md-3 form-group">
		        	            <label class="control-label">Vel. de Subida <span class="text-danger">*</span></label>
		        	            <div class="input-group mb-2">
		        	            	<input type="number" class="form-control"  id="upload" name="upload"  required="" value="{{old('upload')}}" maxlength="200" min="0" onkeypress="return event.charCode >= 48 && event.charCode <=57">
		        	            	<select class="input-group-addon" name="inicial_upload">
										<option value="k">Kbps</option>
										<option value="M">Mbps</option>
									</select>
		        	            </div>
		        	            <span class="help-block error">
		        	                <strong>{{ $errors->first('upload') }}</strong>
		        	            </span>
		        	        </div>
		        	        <div class="col-md-3 form-group">
		        	            <label class="control-label">Tipo <span class="text-danger">*</span></label>
		        	            <select class="form-control selectpicker" name="type" id="type" required="" title="Seleccione" onchange="typeChange();">
		        	                <option {{old('type')==0?'selected':''}} value="0">Queue Simple</option>
		        	                <option {{old('type')==1?'selected':''}} value="1">PCQ</option>
		          			    </select>
		        	            <span class="help-block error">
		        	                <strong>{{ $errors->first('type') }}</strong>
		        	            </span>
		        	        </div>
		        	        <div class="col-md-3 form-group d-none" id="div_address">
		        	            <label class="control-label">Address List <span class="text-danger">*</span></label>
		        	            <div class="input-group">
		        	                <input type="text" class="form-control" name="address_list" id="address_list">
		        	            </div>
		        	            <span class="help-block error">
		        	                <strong>{{ $errors->first('address_list') }}</strong>
		        	            </span>
		        	        </div>
		        	        <div class="col-md-3 form-group">
		        	            <label class="control-label">Servidor DHCP</label>
		        	            <div class="input-group">
		        	                <input type="text" class="form-control" name="dhcp_server" id="dhcp_server" value="{{old('dhcp_server')}}">
		        	            </div>
		        	            <span class="help-block error">
		        	                <strong>{{ $errors->first('dhcp_server') }}</strong>
		        	            </span>
		        	        </div>
		        	        <div class="col-md-3 form-group">
		        	            <label class="control-label">Tipo de Plan <span class="text-danger">*</span></label>
		        	            <select class="form-control selectpicker" name="tipo_plan" id="tipo_plan" required="" title="Seleccione">
		        	                <option {{old('tipo_plan')==1?'selected':''}} value="1">Residencial</option>
		        	                <option {{old('tipo_plan')==2?'selected':''}} value="2">Corportativo</option>
		          			    </select>
		        	            <span class="help-block error">
		        	                <strong>{{ $errors->first('tipo_plan') }}</strong>
		        	            </span>
		        	        </div>
		        	   </div>
			        </div>
			        <div class="tab-pane fade" id="pills-avanzado" role="tabpanel" aria-labelledby="pills-avanzado-tab">
			            <div class="row">
		        	        <div class="col-md-3 form-group">
		        	            <label class="control-label">Burst limit subida</label>
		        	            <div class="input-group mb-2">
		        	            	<input type="number" class="form-control"  id="burst_limit_subida" name="burst_limit_subida"  value="{{old('burst_limit_subida')}}" maxlength="200" min="0" onkeypress="return event.charCode >= 48 && event.charCode <=57">
		        	            	<select class="input-group-addon" name="inicial_burst_limit_subida">
										<option value="k">Kbps</option>
										<option value="M">Mbps</option>
									</select>
		        	            </div>

		        	            <span class="help-block error">
		        	                <strong>{{ $errors->first('burst_limit_subida') }}</strong>
		        	            </span>
		        	        </div>
		        	        <div class="col-md-3 form-group">
		        	            <label class="control-label">Burst limit bajada</label>
		        	            <div class="input-group mb-2">
		        	            	<input type="number" class="form-control"  id="burst_limit_bajada" name="burst_limit_bajada"  value="{{old('burst_limit_bajada')}}" maxlength="200" min="0" onkeypress="return event.charCode >= 48 && event.charCode <=57">
		        	            	<select class="input-group-addon" name="inicial_burst_limit_bajada">
										<option value="k">Kbps</option>
										<option value="M">Mbps</option>
									</select>
		        	            </div>

		        	            <span class="help-block error">
		        	                <strong>{{ $errors->first('burst_limit_bajada') }}</strong>
		        	            </span>
		        	        </div>
		        	        <div class="col-md-3 form-group">
		        	            <label class="control-label">Burst threshold subida</label>
		        	            <div class="input-group mb-2">
		        	            	<input type="number" class="form-control"  id="burst_threshold_subida" name="burst_threshold_subida"  value="{{old('burst_threshold_subida')}}" maxlength="200" min="0" onkeypress="return event.charCode >= 48 && event.charCode <=57">
		        	            	<select class="input-group-addon" name="inicial_burst_threshold_subida">
										<option value="k">Kbps</option>
										<option value="M">Mbps</option>
									</select>
		        	            </div>

		        	            <span class="help-block error">
		        	                <strong>{{ $errors->first('burst_threshold_subida') }}</strong>
		        	            </span>
		        	        </div>
		        	        <div class="col-md-3 form-group">
		        	            <label class="control-label">Burst threshold bajada</label>
		        	            <div class="input-group mb-2">
		        	            	<input type="number" class="form-control"  id="burst_threshold_bajada" name="burst_threshold_bajada"  value="{{old('burst_threshold_bajada')}}" maxlength="200" min="0" onkeypress="return event.charCode >= 48 && event.charCode <=57">
		        	            	<select class="input-group-addon" name="inicial_burst_threshold_bajada">
										<option value="k">Kbps</option>
										<option value="M">Mbps</option>
									</select>
		        	            </div>

		        	            <span class="help-block error">
		        	                <strong>{{ $errors->first('burst_threshold_bajada') }}</strong>
		        	            </span>
		        	        </div>

		        	        <div class="col-md-3 form-group">
		        	            <label class="control-label">Limit at subida</label>
		        	            <div class="input-group mb-2">
		        	            	<input type="number" class="form-control"  id="limit_at_subida" name="limit_at_subida"  value="{{old('limit_at_subida')}}" maxlength="200" min="0" onkeypress="return event.charCode >= 48 && event.charCode <=57">
		        	            	<select class="input-group-addon" name="inicial_limit_at_subida">
										<option value="k">Kbps</option>
										<option value="M">Mbps</option>
									</select>
		        	            </div>

		        	            <span class="help-block error">
		        	                <strong>{{ $errors->first('limit_at_subida') }}</strong>
		        	            </span>
		        	        </div>
		        	        <div class="col-md-3 form-group">
		        	            <label class="control-label">Limit at bajada</label>
		        	            <div class="input-group mb-2">
		        	            	<input type="number" class="form-control"  id="limit_at_bajada" name="limit_at_bajada"  value="{{old('limit_at_bajada')}}" maxlength="200" min="0" onkeypress="return event.charCode >= 48 && event.charCode <=57">
		        	            	<select class="input-group-addon" name="inicial_limit_at_bajada">
										<option value="k">Kbps</option>
										<option value="M">Mbps</option>
									</select>
		        	            </div>

		        	            <span class="help-block error">
		        	                <strong>{{ $errors->first('limit_at_bajada') }}</strong>
		        	            </span>
		        	        </div>

		        	        <div class="col-md-3 form-group">
		        	            <label class="control-label">Burst time subida</label>
		        	            <div class="input-group mb-2">
		        	            	<input type="number" class="form-control"  id="burst_time_subida" name="burst_time_subida"  value="{{old('burst_time_subida')}}" maxlength="200" min="0" onkeypress="return event.charCode >= 48 && event.charCode <=57">
		        	            	<div class="input-group-prepend">
		        	            		<div class="input-group-text font-weight-bold">Seg</div>
		        	            	</div>
		        	            </div>

		        	            <span class="help-block error">
		        	                <strong>{{ $errors->first('burst_time_subida') }}</strong>
		        	            </span>
		        	        </div>
		        	        <div class="col-md-3 form-group">
		        	            <label class="control-label">Burst time bajada</label>
		        	            <div class="input-group mb-2">
		        	            	<input type="number" class="form-control"  id="burst_time_bajada" name="burst_time_bajada"  value="{{old('burst_time_bajada')}}" maxlength="200" min="0" onkeypress="return event.charCode >= 48 && event.charCode <=57">
		        	            	<div class="input-group-prepend">
		        	            		<div class="input-group-text font-weight-bold">Seg</div>
		        	            	</div>
		        	            </div>

		        	            <span class="help-block error">
		        	                <strong>{{ $errors->first('burst_time_bajada') }}</strong>
		        	            </span>
		        	        </div>
		        	        <div class="col-md-3 form-group">
		        	            <label class="control-label">Queue Type de subida</label>
		        	            <input type="text" class="form-control"  id="queue_type_subida" name="queue_type_subida"  value="{{old('queue_type_subida')}}" maxlength="200">

		        	            <span class="help-block error">
		        	                <strong>{{ $errors->first('queue_type_subida') }}</strong>
		        	            </span>
		        	        </div>
		        	        <div class="col-md-3 form-group">
		        	            <label class="control-label">Queue Type de bajada</label>
		        	            <input type="text" class="form-control"  id="queue_type_bajada" name="queue_type_bajada"  value="{{old('queue_type_bajada')}}" maxlength="200">

		        	            <span class="help-block error">
		        	                <strong>{{ $errors->first('queue_type_bajada') }}</strong>
		        	            </span>
		        	        </div>
		        	        <div class="col-md-3 form-group">
		        	            <label class="control-label">Parent</label>
		        	            <input type="text" class="form-control"  id="parenta" name="parenta"  value="{{old('parenta')}}" maxlength="200">
		        	            <span class="help-block error">
		        	                <strong>{{ $errors->first('parenta') }}</strong>
		        	            </span>
		        	        </div>
		        	        <div class="col-md-3 form-group">
		        	            <label class="control-label">Prioridad</label>
		        	            <input type="number" class="form-control"  id="prioridad" name="prioridad"  value="8" min="1" max="8" onkeypress="return event.charCode >= 48 && event.charCode <=57">
		        	            <span class="help-block error">
		        	                <strong>{{ $errors->first('prioridad') }}</strong>
		        	            </span>
		        	        </div>
						</div>
			        </div>
			    </div>
			</div>
	    </div>
	    <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
	</div>

    <div class="row" style="text-align: right;">
	    <div class="col-md-12">
	    	<hr>
	    	<button type="button" class="btn btn-outline-secondary" data-dismiss="modal" id="cancelar_plan">Cancelar</button>
            <a href="javascript:guardar_plan()" id="guardar_plan" type="submit" class="btn btn-success mr-5">Guardar</a>
        </div>
    </div>
</form>

<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
<script>
	function guardar_plan(){
		$.post($("#formPlan").attr('action'), $("#formPlan").serialize(), function (dato) {
			if(dato['success']==true){
				var select = $('#plan_id');
				select.append('<option value=' + dato['id'] + ' selected>'+dato['type']+': '+dato['name']+'</option>');
				select.selectpicker('refresh');
				$("#plan_id").trigger('change');
				$('#cancelar_plan').click();
				$('#formPlan').trigger("reset");
				swal("Registro Guardado", "Nuevo Plan de Velocidad Agregado!!!", "success");
				$('#mikrotik, #tipo_plan, #fecha_corte, #fecha_suspension').val('').selectpicker('refresh');
			} else {
				swal('ERROR!!', dato['mensaje'], "error");
			}
		}, 'json');
	}
</script>