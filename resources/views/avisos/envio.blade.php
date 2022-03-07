@extends('layouts.app')
@section('content')
	<form method="POST" action="{{ route('avisos.envio_aviso') }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-retencion">
	    @csrf
	    <input type="hidden" value="{{$opcion}}" name="type">
	    <div class="row">
	        <div class="col-md-3 form-group">
	            <label class="control-label">Plantilla <span class="text-danger">*</span></label>
        	    <select name="plantilla" id="plantilla" class="form-control selectpicker " title="Seleccione" data-live-search="true" data-size="5" required>
        	        @foreach($plantillas as $plantilla)
        	        <option {{old('plantilla')==$plantilla->id?'selected':''}} value="{{$plantilla->id}}">{{$plantilla->title}}</option>
        	        @endforeach
        	    </select>
        	    <span class="help-block error">
        	        <strong>{{ $errors->first('plantilla') }}</strong>
        	    </span>
        	</div>
        	
        	<div class="col-md-5 form-group {{ $id ? 'd-none':'' }}">
        	    <label class="control-label">Clientes <span class="text-danger">*</span></label>
        	    <div class="btn-group btn-group-toggle" data-toggle="buttons">
        	        <label class="btn btn-success">
        	            <input type="radio" name="options" id="radio_1" onchange="chequeo();"> Habilitados
        	        </label>
        	        <label class="btn btn-danger">
        	            <input type="radio" name="options" id="radio_2" onchange="chequeo();"> Deshabilitados
        	        </label>
        	        <label class="btn btn-secondary">
        	            <input type="radio" name="options" id="radio_3" onchange="chequeo();"> Manual
        	        </label>
        	    </div>
        	</div>
        	
        	<div class="col-md-3 form-group" id="seleccion_manual">
	            <label class="control-label">Selección manual de clientes</label>
        	    <select name="contrato[]" id="contrato" class="form-control selectpicker" title="Seleccione" data-live-search="true" data-size="5" required multiple data-actions-box="true" data-select-all-text="Todos" data-deselect-all-text="Ninguno">
        	        @php $estados=\App\Contrato::tipos();@endphp
        	        @foreach($estados as $estado)
        	        <optgroup label="{{$estado['nombre']}}">
        	            @foreach($contratos as $contrato)
        	                @if($contrato->state==$estado['state'])
        	                    <option class="{{$contrato->state}}" value="{{$contrato->id}}" {{$contrato->id==$id?'selected':''}}>{{$contrato->c_nombre}} - {{$contrato->c_nit}}</option>
        	                @endif
        	            @endforeach
        	        </optgroup>
        	        @endforeach
        	    </select>
        	    <span class="help-block error">
        	        <strong>{{ $errors->first('cliente') }}</strong>
        	    </span>
        	</div>
       </div>
	    
	   <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
	   
	   <hr>
	   
	   <div class="row" >
	       <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
	           <a href="{{route('avisos.index')}}" class="btn btn-outline-secondary">Cancelar</a>
	           <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
	       </div>
	   </div>
    </form>
@endsection

@section('scripts')
<script type="text/javascript">
    function chequeo(){
        if($("#radio_1").is(":checked")){
            $(".enabled").attr('selected','selected');
            $(".disabled").removeAttr("selected");
            //$("#seleccion_manual").addClass('d-none');
        }else if($("#radio_2").is(":checked")){
            $(".disabled").attr('selected','selected');
            $(".enabled").removeAttr("selected");
            //$("#seleccion_manual").addClass('d-none');
        }else if($("#radio_3").is(":checked")){
            //$("#seleccion_manual").removeClass('d-none');
        }
        $("#contrato").selectpicker('refresh');
    }
    
</script>
@endsection