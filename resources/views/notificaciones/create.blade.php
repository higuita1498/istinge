@extends('layouts.app')
@section('content')
	<style>
    .readonly{ border: 0 !important; }
  </style>

    @if(Session::has('danger'))
		<div class="alert alert-danger" >
			{{Session::get('danger')}}
		</div>

		<script type="text/javascript">
			setTimeout(function(){
			    $('.alert').hide();
			    $('.active_table').attr('class', ' ');
			}, 5000);
		</script>
	@endif
	
	<form method="POST" action="{{ route('notificaciones.store') }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-notificaciones" >
	    {{ csrf_field() }}
	    <div class="row">
	        <div class="col-md-3 form-group">
	            <label class="control-label">Fecha Inicio <span class="text-danger">*</span></label>
	            <input type="text" class="form-control datepicker" id="fecha" name="desde" required="" value="{{date('d-m-Y')}}" >
	            <span class="help-block error">
	                <strong>{{ $errors->first('desde') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-3 form-group">
	            <label class="control-label">Fecha Final <span class="text-danger">*</span></label>
	            <input type="text" class="form-control datepicker" id="vencimiento" name="hasta" required="" value="{{date('d-m-Y')}}" >
	            <span class="help-block error">
	                <strong>{{ $errors->first('hasta') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-3 form-group">
	            <label class="control-label">Tipo <span class="text-danger">*</span></label>
	            <select class="form-control selectpicker" id="tipo" name="tipo" required="">
	                <option value="0">Notificaión</option>
	                <option value="1">Noticia</option>
	            </select>
	            <span class="help-block error">
	                <strong>{{ $errors->first('tipo') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-3 form-group">
	            <label class="control-label">Estatus <span class="text-danger">*</span></label>
	            <select class="form-control selectpicker" id="status" name="status" required="">
	                <option value="1" selected>Activa</option>
	                <option value="0">Vencida</option>
	            </select>
	            <span class="help-block error">
	                <strong>{{ $errors->first('status') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-12 form-group">
	            <label class="control-label">Mensaje <span class="text-danger">*</span></label>
	            <textarea class="form-control form-control-sm min_max_100" name="mensaje"></textarea>
	            <span class="help-block error">
	                <strong>{{ $errors->first('mensaje') }}</strong>
	            </span>
	        </div>
	    </div>
	    <small>Los campos marcados con son obligatorios</small>
	    
	    <hr>
	    <div class="row" >
	        <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
	            <a href="{{route('notificaciones.index')}}" class="btn btn-outline-secondary">Cancelar</a>
	            <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
	        </div>
	    </div>
	</form>
@endsection

