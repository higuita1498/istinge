<form method="POST" action="{{-- route('contratos.update_planes') --}}" style="padding: 0%;" role="form" class="forms-sample" novalidate id="formulario">
    @csrf
    <div class="row" style="text-align: center;">
        <div class="col-md-12">
            <h4>ACTUALIZAR PLAN DE INTERNET</h4>
            <hr>
        </div>
    </div>

    <div style="padding: 1% 3%;">
        <div class="row">
      		<div class="col-md-8 offset-md-2 form-group">
                <label class="control-label">Servidor <span class="text-danger">*</span></label>
                <div class="input-group">
                    <select class="form-control selectpicker" name="server_configuration_id" id="server_configuration_id" required="" title="Seleccione" data-live-search="true" data-size="5" onchange="getPlanes(this.value);">
                        @foreach($servidores as $servidor)
                        <option value="{{$servidor->id}}" {{old('server_configuration_id')==$servidor->id?'selected':''}}>{{$servidor->nombre}} - {{$servidor->ip}}</option>
                        @endforeach
                    </select>
                    <input type="hidden" id="servidor" value="{{old('server_configuration_id')}}">
                </div>
                <span class="help-block error">
                    <strong>{{ $errors->first('server_configuration_id') }}</strong>
                </span>
            </div>
            <div class="col-md-8 offset-md-2 form-group">
                <label class="control-label">Plan <span class="text-danger">*</span></label>
                <div class="input-group">
                    <select class="form-control selectpicker" name="plan_id" id="plan_id" required="" title="Seleccione" data-live-search="true" data-size="5">

                    </select>
                </div>
                <span class="help-block error">
                    <strong>{{ $errors->first('plan_id') }}</strong>
                </span>
            </div>
      	</div>
  	</div>

  	<div class="row" style="text-align: right;">
  	    <div class="col-md-12">
  	        <hr>
  	        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal" id="cancelarc">Cancelar</button>
            <button id="guardarc" type="submit" class="btn btn-success mr-5">Guardar</button>
        </div>
    </div>
</form>