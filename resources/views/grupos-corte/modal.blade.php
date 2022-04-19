<form method="POST" action="{{ route('grupos-corte.storeBack') }}" style="padding: 0%;" role="form" class="forms-sample" novalidate id="formGrupo">
    @csrf
    <div class="row" style="text-align: center;">
        <div class="col-md-12">
            <h3>AGREGAR NUEVO GRUPO DE CORTE</h3>
            <hr>
        </div>
    </div>

	<div style="padding: 1% 3%;">
	    <div class="row">
	        <div class="col-md-12 form-group">
	            <label class="control-label">Nombre <span class="text-danger">*</span></label>
	            <input type="text" class="form-control"  id="nombre" name="nombre"  required="" value="{{old('nombre')}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('nombre') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-6 form-group">
	            <label class="control-label">Fecha de Factura <span class="text-danger">*</span></label>
	            <select class="form-control selectpicker" name="fecha_factura" id="fecha_factura" title="Seleccione" data-live-search="true" data-size="5">
	            	<option {{old('fecha_factura')==0?'selected':''}} value="0">No Aplica</option>
	            	@for ($i = 1; $i < 31; $i++)
	            	    <option {{old('fecha_factura')==$i?'selected':''}} value="{{$i}}">{{$i}}</option>
	            	@endfor
            	</select>
	            <span class="help-block error">
	                <strong>{{ $errors->first('fecha_factura') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-6 form-group">
	            <label class="control-label">Fecha de Pago <span class="text-danger">*</span></label>
	            <select class="form-control selectpicker" name="fecha_pago" id="fecha_pago" title="Seleccione" data-live-search="true" data-size="5">
	            	<option {{old('fecha_pago')==0?'selected':''}} value="0">No Aplica</option>
	            	@for ($i = 1; $i < 31; $i++)
	            	    <option {{old('fecha_pago')==$i?'selected':''}} value="{{$i}}">{{$i}}</option>
	            	@endfor
            	</select>
	            <span class="help-block error">
	                <strong>{{ $errors->first('fecha_pago') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-6 form-group">
	            <label class="control-label">Fecha de Corte <span class="text-danger">*</span></label>
	            <select class="form-control selectpicker" name="fecha_corte" id="fecha_corte" title="Seleccione" data-live-search="true" data-size="5">
	            	<option {{old('fecha_corte')==0?'selected':''}} value="0">No Aplica</option>
	            	@for ($i = 1; $i < 31; $i++)
	            	    <option {{old('fecha_corte')==$i?'selected':''}} value="{{$i}}">{{$i}}</option>
	            	@endfor
            	</select>
	            <span class="help-block error">
	                <strong>{{ $errors->first('fecha_corte') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-6 form-group">
	            <label class="control-label">Fecha de Suspensión <span class="text-danger">*</span></label>
	            <select class="form-control selectpicker" name="fecha_suspension" id="fecha_suspension" title="Seleccione" data-live-search="true" data-size="5">
	            	<option {{old('fecha_suspension')==0?'selected':''}} value="0">No Aplica</option>
	            	@for ($i = 1; $i < 31; $i++)
	            	    <option {{old('fecha_suspension')==$i?'selected':''}} value="{{$i}}">{{$i}}</option>
	            	@endfor
            	</select>
	            <span class="help-block error">
	                <strong>{{ $errors->first('fecha_suspension') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-6 form-group d-none">
	            <label class="control-label">Estado <span class="text-danger">*</span></label>
	            <select class="form-control selectpicker" name="status" id="status" title="Seleccione" required="">
	                <option value="1" selected>Habilitado</option>
	                <option value="0">Deshabilitado</option>
	            </select>
	            <span class="help-block error">
	                <strong>{{ $errors->first('status') }}</strong>
	            </span>
	        </div>
	    </div>
	    <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
	</div>

    <div class="row" style="text-align: right;">
	    <div class="col-md-12">
	    	<hr>
	    	<button type="button" class="btn btn-outline-secondary" data-dismiss="modal" id="cancelar_grupo">Cancelar</button>
            <a href="javascript:guardar_grupo()" id="guardar_grupo" type="submit" class="btn btn-success mr-5">Guardar</a>
        </div>
    </div>
</form>

<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
<script>
	function guardar_grupo(){
		$.post($("#formGrupo").attr('action'), $("#formGrupo").serialize(), function (dato) {
			if(dato['success']==true){
				var select = $('#grupo_corte_s');
				select.append('<option value=' + dato['id'] + ' selected>'+dato['nombre']+' (Corte '+dato['corte']+' - Suspensión '+dato['suspension']+')</option>');
				select.selectpicker('refresh');
				$("#grupo_corte_s").trigger('change');
				$('#cancelar_grupo').click();
				$('#formGrupo').trigger("reset");
				swal("Registro Guardado", "Nuevo Grupo de Corte Agregado!!!", "success");
				$('#fecha_factura, #fecha_pago, #fecha_corte, #fecha_suspension').val(0).selectpicker('refresh');
			} else {
				swal('Info!!', dato['mensaje'], "error");
			}
		}, 'json');
	}
</script>