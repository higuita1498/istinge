
<form method="POST" action="{{ route('puc.update', $categoria->id) }}" role="form" class="forms-sample" novalidate id="form-categoria" >
      <input name="_method" type="hidden" value="PATCH">
<div class="modal-header">
  <h5 class="modal-title" id="modal-small-CenterTitle">Modificar categoría</h5>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<div class="modal-body">
  @if($categoria->asociado())
  <div class="row">
      <div class="col-md-12 form-group">
        <label class="control-label">Asociada </label>
        <input type="text" class="form-control"  disabled="" value="{{$categoria->asociado()->nombre}}">
      </div>
    </div>
  @endif
  <input type="hidden" name="asociado" value="{{$categoria->nro}}">
   @csrf 
    <div class="row">
      <div class="col-md-12 form-group">
        <label class="control-label">Nombre <span class="text-danger">*</span></label>
        <input type="text" class="form-control"  id="nombre" name="nombre"  required="" value="{{$categoria->nombre}}" maxlength="200">
        <span class="help-block error">
          <strong>{{ $errors->first('nombre') }}</strong>
        </span>
      </div>
    </div>
  <div class="row">
    <div class="col-md-12 form-group">
      <label class="control-label">Código</label>
      <input type="text" class="form-control"  id="codigo" name="codigo"  value="{{$categoria->codigo}}" maxlength="50">
      <span class="help-block error">
        <strong>{{ $errors->first('codigo') }}</strong>
      </span> 
    </div>
  </div>
  <div class="row">
    <div class="col-md-12 form-group">
      <label class="control-label">Descrición </label>
      <textarea  class="form-control form-control-sm " name="descripcion" >{{$categoria->descripcion}}</textarea>
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