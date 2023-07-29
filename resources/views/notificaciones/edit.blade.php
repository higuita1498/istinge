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
	
	<form method="POST" action="{{ route('notificaciones.update', $notificacion->id ) }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-notificaciones">
        {{ csrf_field() }}
        <input name="_method" type="hidden" value="PATCH">
	    <div class="row">
	        <div class="col-md-3 form-group">
	            <label class="control-label">Fecha Inicio <span class="text-danger">*</span></label>
	            <input type="text" class="form-control datepicker" id="fecha" name="desde" required="" value="{{date('d-m-Y', strtotime($notificacion->desde))}}" >
	            <span class="help-block error">
	                <strong>{{ $errors->first('desde') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-3 form-group">
	            <label class="control-label">Fecha Final <span class="text-danger">*</span></label>
	            <input type="text" class="form-control datepicker" id="vencimiento" name="hasta" required="" value="{{date('d-m-Y', strtotime($notificacion->hasta))}}" >
	            <span class="help-block error">
	                <strong>{{ $errors->first('hasta') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-3 form-group">
	            <label class="control-label">Tipo <span class="text-danger">*</span></label>
	            <select class="form-control selectpicker" id="tipo" name="tipo" required="" value="{{ $notificacion->tipo }}">
	                <option value="0" {{ $notificacion->status == 0 ? 'selected':'' }}>Notificaión</option>
	                <option value="1" {{ $notificacion->status == 1 ? 'selected':'' }}>Noticia</option>
	            </select>
	            <span class="help-block error">
	                <strong>{{ $errors->first('tipo') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-3 form-group">
	            <label class="control-label">Estatus <span class="text-danger">*</span></label>
	            <select class="form-control selectpicker" id="status" name="status" required="" value="{{ $notificacion->status }}">
	                <option value="1" {{ $notificacion->status == 1 ? 'selected':'' }}>Activa</option>
	                <option value="0" {{ $notificacion->status == 0 ? 'selected':'' }}>Vencida</option>
	            </select>
	            <span class="help-block error">
	                <strong>{{ $errors->first('status') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-12 form-group">
	            <label class="control-label">Mensaje <span class="text-danger">*</span></label>
	            <textarea class="form-control form-control-sm min_max_100" name="mensaje">{{ $notificacion->mensaje }}</textarea>
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