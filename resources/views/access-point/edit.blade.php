@extends('layouts.app')
@section('content')
	<form method="POST" action="{{ route('access-point.update', $ap->id) }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-banco" >
	    @csrf
	    <input name="_method" type="hidden" value="PATCH">
	    <div class="row">
	        <div class="col-md-4 form-group">
	            <label class="control-label">Nombre del AP <span class="text-danger">*</span></label>
	            <input type="text" class="form-control"  id="nombre" name="nombre"  required="" value="{{$ap->nombre}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('nombre') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-4 form-group">
	            <label class="control-label">Contraseña del AP <span class="text-danger">*</span></label>
	            <input type="text" class="form-control"  id="password" name="password"  required="" value="{{$ap->password}}" maxlength="200">
	            <span class="help-block error">
	                <strong>{{ $errors->first('password') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-4 form-group">
	            <label class="control-label">Modo de Red <span class="text-danger">*</span></label>
	            <select class="form-control selectpicker" name="modo_red" id="modo_red" title="Seleccione" required="">
	                <option value="1" {{ ($ap->modo_red == 1) ? 'selected' : '' }}>Bridge</option>
	                <option value="2" {{ ($ap->modo_red == 2) ? 'selected' : '' }}>Enrutador</option>
	            </select>
	            <span class="help-block error">
	                <strong>{{ $errors->first('modo_red') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-4 form-group">
	            <label class="control-label">Nodo <span class="text-danger">*</span></label>
	            <select class="form-control selectpicker" name="nodo" id="nodo" title="Seleccione" required="">
	                @foreach($nodos as $nodo)
                        <option value="{{$nodo->id}}" {{ ($ap->nodo == $nodo->id) ? 'selected' : '' }}>{{$nodo->nombre}}</option>
                    @endforeach
	            </select>
	            <span class="help-block error">
	                <strong>{{ $errors->first('nodo') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-4 form-group">
	            <label class="control-label">Estado <span class="text-danger">*</span></label>
	            <select class="form-control selectpicker" name="status" id="status" title="Seleccione" required="">
	                <option value="1" {{ ($ap->status == 1) ? 'selected' : '' }}>Habilitado</option>
	                <option value="0" {{ ($ap->status == 0) ? 'selected' : '' }}>Deshabilitado</option>
	            </select>
	            <span class="help-block error">
	                <strong>{{ $errors->first('status') }}</strong>
	            </span>
	        </div>
	        <div class="col-md-12 form-group">
	            <label class="control-label">Descripción</label>
	            <textarea  class="form-control form-control-sm" name="descripcion" rows="3">{{$ap->descripcion}}</textarea>
	        </div>
	    </div>
	    <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
	    <hr>
	    <div class="row" >
	        <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
	            <a href="{{route('access-point.index')}}" class="btn btn-outline-secondary">Cancelar</a>
	            <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
	        </div>
	    </div>
	</form>
@endsection