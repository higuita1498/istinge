
<form method="POST" action="{{ route('categorias.store') }}" role="form" class="forms-sample" novalidate id="form-categoria" >
<div class="modal-header">
  <h5 class="modal-title" id="modal-small-CenterTitle">Nueva categoría</h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<div class="modal-body">
  <div class="row">
      <div class="col-md-12 form-group">
        <label class="control-label">Asociada </label>
        <input type="text" class="form-control"  disabled="" value="{{$categoria->nombre}}">
      </div>
    </div>
  <input type="hidden" name="asociado" value="{{$categoria->nro}}">
   {{ csrf_field() }} 
    <div class="row">
      <div class="col-md-12 form-group">
        <label class="control-label">Nombre <span class="text-danger">*</span></label>
        <input type="text" class="form-control"  id="nombre" name="nombre"  required="" value="{{old('nombre')}}" maxlength="200">
        <span class="help-block error">
          <strong>{{ $errors->first('nombre') }}</strong>
        </span>
      </div>
    </div>
  <div class="row">
    <div class="col-md-12 form-group">
      <label class="control-label">Código</label>
      <input type="text" class="form-control"  id="codigo" name="codigo"  value="{{old('codigo')}}" maxlength="50">
      <span class="help-block error">
        <strong>{{ $errors->first('codigo') }}</strong>
      </span> 
    </div>
  </div>
  <div class="row">
    <div class="col-md-12 form-group">
      <label class="control-label">Descrición </label>
      <textarea  class="form-control form-control-sm " name="descripcion" >{{old('descripcion')}}</textarea>
      <span class="help-block error">
        <strong>{{ $errors->first('descripcion') }}</strong>
      </span> 
    </div>
  </div>
    <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
  <button type="submit" class="btn btn-success">Guardar</button>

</div>

  </form>