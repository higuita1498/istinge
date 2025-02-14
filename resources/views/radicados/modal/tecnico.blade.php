<form method="POST" action="" style="padding: 0%;" role="form" class="forms-sample" novalidate id="formulario">
    @csrf
    <div class="row" style="text-align: center;">
        <div class="col-md-12">
            <h4>SELECCIONAR TECNICO</h4>
            <hr>
        </div>
    </div>

    <div style="padding: 1% 3%;">
        <div class="row">
      		<div class="col-md-8 offset-md-2 form-group">
                <label class="control-label">Técnico <span class="text-danger">*</span></label>
                <div class="input-group">
                    <select class="form-control selectpicker" name="tecnico_lote" id="tecnico_lote" required="" title="Seleccione" data-live-search="true" data-size="5">
                        @foreach($tecnicos as $tecnico)
                        <option value="{{$tecnico->id}}">{{$tecnico->nombres}}</option>
                        @endforeach
                    </select>
                </div>
                <span class="help-block error">
                    <strong>{{ $errors->first('tecnico') }}</strong>
                </span>
            </div>
      	</div>
  	</div>

  	<div class="row" style="text-align: right;">
  	    <div class="col-md-12">
  	        <hr>
  	        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal" id="cancelarc">Cancelar</button>
            <button id="guardarc" type="button" class="btn btn-success mr-5">Guardar</button>
        </div>
    </div>
</form>
