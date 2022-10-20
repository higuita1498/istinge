 @extends('layouts.app')
@section('content')
	<form method="POST" action="{{ route('ajustes.store') }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-ajuste">
   {{ csrf_field() }}
  	<div class="row">
	    <div class="col-md-4  form-group">
	        <label class="control-label">Bodega<span class="text-danger">*</span><a><i data-tippy-content="Bodega a la cual pertenece el producto" class="icono far fa-question-circle"></i></a></label>
	        <select class="form-control selectpicker no-padding"  title="Seleccione" name="bodega" id="bodega" required="">
	          @foreach($bodegas as $bob)
	            <option value="{{$bob->id}}" {{$bob->id==old('bodega')?'selected':''}}  >{{$bob->bodega}}</option>
	          @endforeach
	        </select>
	  	</div>
	  	<div class="col-md-4  form-group">
	        <label class="control-label">Ítem<span class="text-danger">*</span></label>
	        <select class="form-control selectpicker"  title="Seleccione" data-live-search="true" data-size="5" name="producto" id="item" required=""></select>
	  	</div>
		<div class="col-md-4 form-group monetario">
			<label class="control-label">Costo Unitario (Informativo)<span class="text-danger">*</span></label>
			<input type="number" class="form-control"  id="costo" name="costo" value="old('costo')" required="">
		</div>
	</div>

	<div class="row">
		<div class="col-md-4  form-group">
	        <label class="control-label">Tipo de Ajuste<span class="text-danger">*</span></label>
	        <div class="row">
				<div class="col-sm-6">
					<div class="form-radio">
					<label class="form-check-label">
					<input type="radio" class="form-check-input" name="ajuste" id="ajuste1" value="1" {{old('ajuste')==1?'checked':''}} > Incremento
					<i class="input-helper"></i><i class="input-helper"></i></label>
					</div>
				</div>
				<div class="col-sm-6">
				<div class="form-radio">
				<label class="form-check-label">
				<input type="radio" class="form-check-input" name="ajuste" id="ajuste2" value="0" {{old('ajuste')==0?'checked':''}}> Disminución
				<i class="input-helper"></i><i class="input-helper"></i></label>
				</div>
				</div>
			</div>
	  	</div>
		<div class="col-md-2 form-group">
			<label class="control-label">Cantidad Actual</label>
			<input type="text" class="form-control"  id="cantA" disabled="">
		</div>
		<div class="col-md-3 form-group">
			<label class="control-label">Cantidad <span class="text-danger">*</span></label>
			<input type="number" maxlength="30" class="form-control"  id="cant" name="cant" value="old('cant')" min="0" required="">
			<p id="cant-error-error" class="error" for="cant"></p>
		</div>
		<div class="col-md-3 form-group">
			<label class="control-label">Cantidad Final</label>
			<input type="text" class="form-control"  id="cantF" disabled="">
		</div>		
	</div>

	<div class="row">
		<div class="col-md-4 form-group">
			<label class="control-label">Fecha<span class="text-danger">*</span><a><i data-tippy-content="Fecha en la que se hizo el ajuste de inventario" class="icono far fa-question-circle"></i></a></label>
			<input type="text" class="form-control datepicker"  id="fecha" value="{{date('d-m-Y')}}" name="fecha" disabled=""  required="">
		</div>
		<div class="col-md-8 form-group">
			<label class="control-label">Observaciones</label>
			<textarea class="form-control" name="observaciones" >{{old('observaciones')}}</textarea>
		</div>      
    </div>  
  	<small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
  	<hr>
	<div class="row" >
    <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
      <a href="{{route('ajustes.index')}}" class="btn btn-outline-secondary">Cancelar</a>
      <button type="submit" class="btn btn-success" id="boton_guardar" onclick="submitLimit(this.id)">Guardar</button>
    </div>
	</div>
</form>
<input type="hidden" id="json_inventario" value="[]">
<input type="hidden" id="url" value="{{url('/')}}">
@endsection
