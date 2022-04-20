@extends('layouts.app')
@section('content')
    @if(Session::has('success'))
        <div class="alert alert-success alerta" style="margin-left: 2%;margin-right: 2%;">
	    {{Session::get('success')}}
        </div>
        <script type="text/javascript">
            setTimeout(function() {
                $('.alerta').hide();
                $('.active_table').attr('class', ' ');
            }, 5000);
        </script>
    @endif

    @if(Session::has('danger'))
        <div class="alert alert-danger" alerta style="margin-left: 2%;margin-right: 2%;">
	    {{Session::get('danger')}}
        </div>
        <script type="text/javascript">
            setTimeout(function() {
                $('.alerta').hide();
                $('.active_table').attr('class', ' ');
            }, 5000);
        </script>
    @endif

    <div class="alert alert-{{ $servidor->estado('true') }} text-uppercase" style="margin-left: 2%;margin-right: 2%;">
    	LA CONFIGURACIÓN DEL SERVIDOR SE ENCUENTRA <strong>{{ $servidor->estado() }}</strong>
    </div>

	<form method="POST" action="{{ route('servidor-correo.update', $servidor->id) }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-banco">
	    @csrf
	    <input name="_method" type="hidden" value="PATCH">
	    <div class="row">
	        <div class="col-md-4 form-group">
	            <label class="control-label">Servidor</label>
	            <input type="text" class="form-control"  id="servidor" name="servidor"   value="{{$servidor->servidor}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('servidor') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-4 form-group">
	            <label class="control-label">Seguridad</label>
	            <select class="form-control selectpicker" name="seguridad" id="seguridad" title="Seleccione" >
	                <option value="ssl" {{ $servidor->seguridad == 'ssl'?'selected':'' }}>SSL</option>
	                <option value="tls" {{ $servidor->seguridad == 'tls'?'selected':'' }}>TLS</option>
	            </select>
	            <span class="help-block error">
	                <strong>{{ $errors->first('seguridad') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-4 form-group">
	            <label class="control-label">Usuario</label>
	            <input type="text" class="form-control"  id="usuario" name="usuario"   value="{{$servidor->usuario}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('usuario') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-4 form-group">
	            <label class="control-label">Password</label>
	            <input type="text" class="form-control"  id="password" name="password"   value="{{$servidor->password}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('password') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-4 form-group">
	            <label class="control-label">Puerto</label>
	            <input type="number" class="form-control"  id="puerto" name="puerto"   value="{{$servidor->puerto}}" maxlength="200" min="0">
	            <span class="help-block error">
	                <strong>{{ $errors->first('puerto') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-4 form-group">
	            <label class="control-label">Enviar como (Nombre)</label>
	            <input type="text" class="form-control"  id="name" name="name"   value="{{$servidor->name}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('name') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-4 form-group">
	            <label class="control-label">Enviar como (Correo)</label>
	            <input type="text" class="form-control"  id="address" name="address"   value="{{$servidor->address}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('address') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-4 form-group">
	            <label class="control-label">Estado</label>
	            <select class="form-control selectpicker" name="estado" id="estado" title="Seleccione" >
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