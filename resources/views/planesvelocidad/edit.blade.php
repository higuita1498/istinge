@extends('layouts.app')
@section('content')
	<form method="POST" action="{{ route('planes-velocidad.update', $plan->id) }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-retencion" >
	    {{ csrf_field() }}
	    <input name="_method" type="hidden" value="PATCH">
	    
	    <ul class="nav nav-pills mb-5" id="pills-tab" role="tablist">
	        <li class="nav-item">
	            <a class="nav-link active" id="pills-basica-tab" data-toggle="pill" href="#pills-basica" role="tab" aria-controls="pills-basica" aria-selected="true">Configuración Básica</a>
	        </li>
	        <li class="nav-item">
	            <a class="nav-link" id="pills-avanzado-tab" data-toggle="pill" href="#pills-avanzado" role="tab" aria-controls="pills-avanzado" aria-selected="false">Configuración Avanzada</a>
	        </li>
	    </ul>
	    
	    <div class="tab-content" id="pills-tabContent">
	        <div class="tab-pane fade show active" id="pills-basica" role="tabpanel" aria-labelledby="pills-basica-tab">
        	    <div class="row">
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Mikrotik Asociada <span class="text-danger">*</span></label>
        	            <select name="mikrotik" id="mikrotik" class="form-control selectpicker " title="Seleccione" data-live-search="true" data-size="5" required>
                        @foreach($mikrotiks as $mikrotik)
                            <option {{$plan->mikrotik==$mikrotik->id?'selected':''}} value="{{$mikrotik->id}}">{{$mikrotik->nombre}}</option>
                        @endforeach
                        </select>
        	            <span class="help-block error">
        	                <strong>{{ $errors->first('mikrotik') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Nombre <span class="text-danger">*</span></label>
        	            <input type="text" class="form-control"  id="name" name="name"  required="" value="{{$plan->name}}" maxlength="200">
        	            <span class="help-block error">
        	                <strong>{{ $errors->first('name') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Precio <span class="text-danger">*</span></label>
        	            <input type="text" class="form-control"  id="price" name="price"  required="" value="{{$plan->price}}" maxlength="200">
        	            <span class="help-block error">
        	                <strong>{{ $errors->first('price') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Vel. de Descarga <span class="text-danger">*</span></label>
        	            <input type="text" class="form-control"  id="download" name="download"  required="" value="{{$plan->download}}" maxlength="200">
        	            <span class="help-block error">
        	                <strong>{{ $errors->first('download') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Vel. de Subida <span class="text-danger">*</span></label>
        	            <input type="text" class="form-control"  id="upload" name="upload"  required="" value="{{$plan->upload}}" maxlength="200">
        	            <span class="help-block error">
        	                <strong>{{ $errors->first('upload') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Tipo <span class="text-danger">*</span></label>
        	            <select class="form-control selectpicker" name="type" id="type" required="" title="Seleccione" onchange="typeChange();">
        	                <option {{$plan->type==0?'selected':''}} value="0">Queue Simple</option>
        	                <option {{$plan->type==1?'selected':''}} value="1">PCQ</option>
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
        	   </div>
	        </div>
	        <div class="tab-pane fade" id="pills-avanzado" role="tabpanel" aria-labelledby="pills-avanzado-tab">
	            <div class="row">
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Burst limit subida</label>
        	            <input type="number" class="form-control"  id="burst_limit_subida" name="burst_limit_subida"  value="{{$plan->burst_limit_subida}}" maxlength="200" min="0">
        	            <span class="help-block error">
        	                <strong>{{ $errors->first('burst_limit_subida') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Burst limit bajada</label>
        	            <input type="number" class="form-control"  id="burst_limit_bajada" name="burst_limit_bajada"  value="{{$plan->burst_limit_bajada}}" maxlength="200" min="0">
        	            <span class="help-block error">
        	                <strong>{{ $errors->first('burst_limit_bajada') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Burst threshold subida</label>
        	            <input type="number" class="form-control"  id="burst_threshold_subida" name="burst_threshold_subida"  value="{{$plan->burst_threshold_subida}}" maxlength="200" min="0">
        	            <span class="help-block error">
        	                <strong>{{ $errors->first('burst_threshold_subida') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Burst threshold bajada</label>
        	            <input type="number" class="form-control"  id="burst_threshold_bajada" name="burst_threshold_bajada"  value="{{$plan->burst_threshold_bajada}}" maxlength="200" min="0">
        	            <span class="help-block error">
        	                <strong>{{ $errors->first('burst_threshold_bajada') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Burst time subida</label>
        	            <input type="number" class="form-control"  id="burst_time_subida" name="burst_time_subida"  value="{{$plan->burst_time_subida}}" maxlength="200" min="0">
        	            <span class="help-block error">
        	                <strong>{{ $errors->first('burst_time_subida') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Burst time bajada</label>
        	            <input type="number" class="form-control"  id="burst_time_bajada" name="burst_time_bajada"  value="{{$plan->burst_time_bajada}}" maxlength="200" min="0">
        	            <span class="help-block error">
        	                <strong>{{ $errors->first('burst_time_bajada') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Queue Type de subida</label>
        	            <input type="number" class="form-control"  id="queue_type_subida" name="queue_type_subida"  value="{{$plan->queue_type_subida}}" maxlength="200" min="0">
        	            <span class="help-block error">
        	                <strong>{{ $errors->first('queue_type_subida') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Queue Type de bajada</label>
        	            <input type="number" class="form-control"  id="queue_type_bajada" name="queue_type_bajada"  value="{{$plan->queue_type_bajada}}" maxlength="200" min="0">
        	            <span class="help-block error">
        	                <strong>{{ $errors->first('queue_type_bajada') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Parent</label>
        	            <input type="number" class="form-control"  id="parenta" name="parenta"  value="{{$plan->parenta}}" maxlength="200" min="0">
        	            <span class="help-block error">
        	                <strong>{{ $errors->first('parenta') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Prioridad</label>
        	            <input type="number" class="form-control"  id="prioridad" name="prioridad"  value="{{$plan->prioridad}}" min="1" max="8">
        	            <span class="help-block error">
        	                <strong>{{ $errors->first('prioridad') }}</strong>
        	            </span>
        	        </div>
        	   </div>
	        </div>
	    </div>
	   <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
	   <hr>
	   <div class="row" >
	       <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
	           <a href="{{route('planes-velocidad.index')}}" class="btn btn-outline-secondary">Cancelar</a>
	           <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
	       </div>
	   </div>
    </form>
@endsection

@section('scripts')
<script type="text/javascript">
    function typeChange(){
        if(document.getElementById("type").value == 1){
            document.getElementById("div_address").classList.remove('d-none');
            document.getElementById("address_list").setAttribute('required', true);
        }else{
            document.getElementById("div_address").classList.add('d-none');
            document.getElementById("address_list").removeAttribute('required');
        }
        document.getElementById("address_list").value = '';
    }
</script>
@endsection