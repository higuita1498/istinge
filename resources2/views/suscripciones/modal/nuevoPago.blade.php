<form method="POST" action="{{ route('suscripciones.store') }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-banco" >
    {{ csrf_field() }}
    <div class="row">
        <div class="col-md-6 form-group">
            <label class="control-label">Tipo de Plan <span class="text-danger">*</span></label>
            <select class="form-control form-control-sm selectpicker" name="tipo_plan" id="tipo_plan" title="Seleccione" required="">
                <option value="1" @if(old('tipo_plan')==1) selected="" @endif >Plan Normal</option>
                <option value="2" @if(old('tipo_plan')==2) selected="" @endif >Plan Pro</option>
                <option value="3" @if(old('tipo_plan')==3) selected="" @endif >Plan Premium</option>
            </select>
            <span class="help-block error">
        <strong>{{ $errors->first('tipo_plan') }}</strong>
      </span>
        </div>

        <div class="col-md-6 form-group">
            <label class="control-label">Modo de Pago <span class="text-danger">*</span></label>
            <select class="form-control form-control-sm selectpicker" name="tipo_pago" id="tipo_pago" title="Seleccione" required="">
                <option value="1" @if(old('tipo_pago')==1) selected="" @endif >Transferencia</option>
                <option value="2" @if(old('tipo_pago')==2) selected="" @endif >Paypal</option>
                <option value="3" @if(old('tipo_pago')==3) selected="" @endif >Payuu</option>
            </select>
            <span class="help-block error">
        <strong>{{ $errors->first('tipo_pago') }}</strong>
      </span>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 form-group">
            <label class="control-label">Referencia <span class="text-danger">*</span></label>
            <input type="text" class="form-control"  id="nro_ref" name="nro_ref"  required="" value="{{old('nro_ref')}}" maxlength="200">
            <span class="help-block error">
        <strong>{{ $errors->first('nro_ref') }}</strong>
      </span>
        </div>
    </div>


    <div class="row">
        <div class="col-md-6 form-group monetario">
            <label class="control-label">Meses Pagados <span class="text-danger">*</span></label>
            <input type="number" class="form-control"  id="meses" name="meses" required="" value="{{old('mes')}}" maxlength="24" >
            <span class="help-block error">
        <strong>{{ $errors->first('saldo') }}</strong>
      </span>
        </div>

        <div class="col-md-6 form-group monetario">
            <label class="control-label">Monto Pagado <span class="text-danger">*</span></label>
            <input type="number" class="form-control"  id="monto" name="monto" required="" value="{{old('monto')}}" maxlength="24" >
            <span class="help-block error">
        <strong>{{ $errors->first('monto') }}</strong>
      </span>
        </div>

    </div>
    <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
    <hr>
    <div class="row" >
        <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
            <button type="submit" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-success">Guardar</button>
        </div>
    </div>
</form>