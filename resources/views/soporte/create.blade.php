@extends('layouts.app')
@section('content')
	<form method="POST" action="{{ route('soporte.store') }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-soporte" enctype="multipart/form-data">
   {{ csrf_field() }} 
   <div class="row">
     <div class="col-md-6 form-group">
       <label class="control-label">Nombre</label>
       <input type="text" disabled="" class="form-control" value="{{Auth::user()->nombres}}">
     </div>
     <div class="col-md-6 form-group">
       <label class="control-label">Empresa</label>
       <input type="text" disabled="" class="form-control" value="{{Auth::user()->empresa()->nombre}}">
     </div>
   </div>
  <div class="row">
    <div class="col-md-8 form-group">
      <label class="control-label">Título <span class="text-danger">*</span></label>
      <input type="text" class="form-control"  id="titulo" name="titulo"  required="" value="{{old('titulo')}}" maxlength="250">
      <span class="help-block error">
        <strong>{{ $errors->first('titulo') }}</strong>
      </span>
    </div>
    <div class="col-md-4 form-group">
      <label class="control-label">Categoría <span class="text-danger">*</span></label>
      <select class="form-control selectpicker"  id="modulo" name="modulo"  required="" title="Seleccione" data-live-search="true" data-size="5">
        @foreach($categoria as $modulo)
          <option value="{{$modulo->id}}" {{old('modulo')==$modulo->id?'selected':''}}>{{$modulo->nombre}}</option>
        @endforeach
      </select>
      <span class="help-block error">
        <strong>{{ $errors->first('modulo') }}</strong>
      </span> 
    </div>

  </div>
  <div class="row">
    <div class="col-md-12 form-group">
      <label class="control-label">Descripción <span class="text-danger">*</span></label>
      <textarea  class="form-control form-control-sm min_max_100" name="error" required="">{{old('error')}}</textarea>
      <span class="help-block error">
        <strong>{{ $errors->first('error') }}</strong>
      </span>
    </div>
  </div>

  <div class="row">
    <div class="form-group col-md-5">
      <label class="control-label">Imagen</label>
      <input type="file" class="form-control " name="imagen" value="{{old('imagen')}}">
      <span class="help-block error">
        <strong>{{ $errors->first('imagen') }}</strong>
      </span>
    </div>
  </div>
  <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
  <hr>
	<div class="row" >
    <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
      <a href="{{route('soporte.index')}}" class="btn btn-outline-secondary">Cancelar</a>
      <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
    </div>
	</div>
</form>
@endsection