@extends('layouts.app')
@section('content')
    @if(Session::has('success'))
        <div class="alert alert-success" style="margin-left: 2%;margin-right: 2%;">
	    {{Session::get('success')}}
        </div>
        <script type="text/javascript">
            setTimeout(function() {
                $('.alert').hide();
                $('.active_table').attr('class', ' ');
            }, 5000);
        </script>
    @endif

    @if(Session::has('danger'))
        <div class="alert alert-danger" style="margin-left: 2%;margin-right: 2%;">
	    {{Session::get('danger')}}
        </div>
        <script type="text/javascript">
            setTimeout(function() {
                $('.alert').hide();
                $('.active_table').attr('class', ' ');
            }, 5000);
        </script>
    @endif

	<form method="POST" action="{{ route('servidor-correo.update', $servidor->id) }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-banco">
	    @csrf
	    <input name="_method" type="hidden" value="PATCH">
	    <div class="row">
	        <div class="col-md-4 form-group">
	            <label class="control-label">Servidor <span class="text-danger">*</span></label>
	            <input type="text" class="form-control"  id="servidor" name="servidor"  required="" value="{{$servidor->servidor}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('servidor') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-4 form-group">
	            <label class="control-label">Seguridad <span class="text-danger">*</span></label>
	            <select class="form-control selectpicker" name="seguridad" id="seguridad" title="Seleccione" required="">
	                <option value="ssl" {{ $servidor->seguridad == 'ssl'?'selected':'' }}>SSL</option>
	                <option value="tls" {{ $servidor->seguridad == 'tls'?'selected':'' }}>TLS</option>
	            </select>
	            <span class="help-block error">
	                <strong>{{ $errors->first('seguridad') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-4 form-group">
	            <label class="control-label">Usuario <span class="text-danger">*</span></label>
	            <input type="text" class="form-control"  id="usuario" name="usuario"  required="" value="{{$servidor->usuario}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('usuario') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-4 form-group">
	            <label class="control-label">Password <span class="text-danger">*</span></label>
	            <input type="text" class="form-control"  id="password" name="password"  required="" value="{{$servidor->password}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('password') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-4 form-group">
	            <label class="control-label">Puerto <span class="text-danger">*</span></label>
	            <input type="number" class="form-control"  id="puerto" name="puerto"  required="" value="{{$servidor->puerto}}" maxlength="200" min="0">
	            <span class="help-block error">
	                <strong>{{ $errors->first('puerto') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-4 form-group">
	            <label class="control-label">Estado <span class="text-danger">*</span></label>
	            <select class="form-control selectpicker" name="estado" id="estado" title="Seleccione" required="">
	                <option value="1" {{ $servidor->estado == '1'?'selected':'' }}>Habilitado</option>
	                <option value="0" {{ $servidor->estado == '0'?'selected':'' }}>Deshabilitado</option>
	            </select>
	            <span class="help-block error">
	                <strong>{{ $errors->first('estado') }}</strong>
	            </span>
	        </div>
	    </div>
	    <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
	    <hr>
	    <div class="row" >
	        <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
	            <a href="{{route('configuracion.index')}}" class="btn btn-outline-secondary">Cancelar</a>
	            <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
	        </div>
	    </div>
	</form>
@endsection