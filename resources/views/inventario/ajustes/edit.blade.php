 @extends('layouts.app')
@section('content')
	<form method="POST" action="{{ route('ajustes.update', $ajuste->id) }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-ajuste" >
   {{ csrf_field() }}
  	<input name="_method" type="hidden" value="PATCH">
  	<div class="row">
	    <div class="col-md-4  form-group">
	        <label class="control-label">Bodega<span class="text-danger">*</span></label>
	        <select class="form-control selectpicker no-padding"  title="Seleccione" name="bodega" id="bodega" required="">
	          @foreach($bodegas as $bob)
	            <option value="{{$bob->id}}" {{$bob->id==$ajuste->bodega?'selected':''}}  >{{$bob->bodega}}</option>
	          @endforeach
	        </select>
	  	</div>
	  	<div class="col-md-4  form-group">
	        <label class="control-label">Ítem<span class="text-danger">*</span></label>
	        <select class="form-control selectpicker"  title="Seleccione" data-live-search="true" data-size="5" name="producto" id="item" required="">
	 			@php $producto=[]; @endphp
	        	@foreach($inventario as $item)
	        	@if($item->id_producto==$ajuste->producto) @php $producto=$item; @endphp      	@endif
	            <option value="{{$item->id_producto}}" {{$item->id_producto==$ajuste->producto?'selected':''}}  >{{$item->producto}} - ({{$item->ref}})</option>
	          @endforeach
	        </select>
	  	</div>
		<div class="col-md-4 form-group monetario">
			<label class="control-label">Costo Unitario (Informativo)<span class="text-danger">*</span></label>
			<input type="number" class="form-control"  id="costo" name="costo" value="{{$ajuste->costo_unitario}}" required="">
		</div>
	</div>

	<div class="row">
		<div class="col-md-4  form-group">
	        <label class="control-label">Tipo de Ajuste<span class="text-danger">*</span></label>
	        <div class="row">
				<div class="col-sm-6">
					<div class="form-radio">
					<label class="form-check-label">
					<input type="radio" class="form-check-input" name="ajuste" id="ajuste1" value="1" {{$ajuste->ajuste==1?'checked':''}} > Incremento
					<i class="input-helper"></i><i class="input-helper"></i></label>
					</div>
				</div>
				<div class="col-sm-6">
				<div class="form-radio">
				<label class="form-check-label">
				<input type="radio" class="form-check-input" name="ajuste" id="ajuste2" value="0" {{$ajuste->ajuste==0?'checked':''}}> Disminución
				<i class="input-helper"></i><i class="input-helper"></i></label>
				</div>
				</div>
			</div>
	  	</div>
		<div class="col-md-2 form-group">
			<label class="control-label">Cantidad Actual</label>
	 			@php $cantA=$ajuste->cant;
	 			if($ajuste->ajuste==1){$cantA*=-1;}@endphp
	 			
			<input type="text" class="form-control"  id="cantA" disabled="" value="{{$producto->nro+$cantA}}">
		</div>
		<div class="col-md-3 form-group">
			<label class="control-label">Cantidad <span class="text-danger">*</span></label>
			<input type="number" class="form-control"  id="cant" name="cant" value="{{$ajuste->cant}}" min="0" required=""><p id="cant-error-error" class="error" for="cant"></p>
		</div> 
		<div class="col-md-3 form-group">
			<label class="control-label">Cantidad Final</label>
			<input type="text" class="form-control"  id="cantF" disabled="" value="{{$producto->nro}}">
		</div>		
	</div>

	<div class="row">
		<div class="col-md-4 form-group">
			<label class="control-label">Fecha<span class="text-danger">*</span></label>
			<input type="text" class="form-control datepicker"  id="fecha" value="{{date('d-m-Y', strtotime($ajuste->fecha))}}" name="fecha" disabled=""  required="">
		</div>
		<div class="col-md-8 form-group">
			<label class="control-label">Observaciones</label>
			<textarea class="form-control" name="observaciones" >{{$ajuste->observaciones}}</textarea>
		</div>      
    </div>  
  	<small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
  	<hr>
	<div class="row" >
    <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
      <a href="{{route('ajustes.index')}}" class="btn btn-outline-secondary">Cancelar</a>
      <button type="submit" class="btn btn-success" id="boton_guardar">Guardar</button>
    </div>
	</div>
</form>
<input type="hidden" id="json_inventario" value="{{json_encode($inventario)}}">
<input type="hidden" id="url" value="{{url('/')}}">
@endsection