
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
      <div class="col-md-6 form-group">
        <label class="control-label">Asociada </label>
        <input type="text" class="form-control"  disabled="" value="{{$categoria->asociado()->nombre}} - {{$categoria->asociado()->codigo}}">
      </div>
      <div class="col-md-6 form-group">
        <label class="control-label">Nombre <span class="text-danger">*</span></label>
        <input type="text" class="form-control"  id="nombre" name="nombre"  required="" value="{{$categoria->nombre}}" maxlength="200">
        <span class="help-block error">
          <strong>{{ $errors->first('nombre') }}</strong>
        </span>
      </div>
    </div>
  @endif
  <input type="hidden" name="asociado" value="{{$categoria->nro}}">
   @csrf 
    <div class="row">
      
      <div class="col-md-6 form-group">
        <label class="control-label">Código</label>
        <input type="text" class="form-control"  id="codigo" name="codigo"  value="{{$categoria->codigo}}" maxlength="50">
        <span class="help-block error">
          <strong>{{ $errors->first('codigo') }}</strong>
        </span> 
      </div>
      <div class="col-md-6 form-group">
        <label class="control-label">Descripción</label>
        <textarea  class="form-control form-control-sm " name="descripcion" >{{$categoria->descripcion}}</textarea>
        <span class="help-block error">
          <strong>{{ $errors->first('descripcion') }}</strong>
        </span> 
      </div>
    </div>
    <div class="row">
      <div class="col-md-6 form-group">
        <label class="control-label">¿Tercero?<span class="text-danger">*</span></label>
        <select class="form-control" name="tercero" id="tercero" title="¿Tercero?" >
          <option value="0" readonly>sleeccione</option>
          <option value="1" {{$categoria->tercero == 1  ? 'selected' : ''}}>Sí</option>
          <option value="0" {{$categoria->tercero == 0  ? 'selected' : ''}}>No</option>
        </select>
        <span class="help-block error">
          <strong>{{ $errors->first('tercero') }}</strong>
        </span>
      </div>
      <div class="col-md-6 form-group">
        <label class="control-label">Grupo<span class="text-danger">*</span></label>
        <select class="form-control" name="grupo" id="grupo" title="Grupo">
          <option value="0" readonly>Seleccione grupo</option>
          @foreach($grupos as $grupo)
            <option value="{{$grupo->id}}" {{$grupo->id == $categoria->id_grupo ? 'selected' : ''}}>{{$grupo->nombre}}</option>
          @endforeach
        </select>
        <span class="help-block error">
          <strong>{{ $errors->first('grupo') }}</strong>
        </span>
      </div>
    </div>
    <div class="row">
      <div class="col-md-6 form-group">
        <label class="control-label">Tipo<span class="text-danger">*</span></label>
        <select class="form-control" name="tipo" id="tipo" title="Tercero" >
          <option value="0" readonly>Seleccione tipo</option>
          @foreach($tipos as $tipo)
            <option value="{{$tipo->id}}" {{$tipo->id == $categoria->id_tipo ? 'selected' : ''}}> {{$tipo->nombre}}</option>
          @endforeach
        </select>
        <span class="help-block error">
          <strong>{{ $errors->first('tipo') }}</strong>
        </span>
      </div>
      <div class="col-md-6 form-group">
        <label class="control-label">Balance<span class="text-danger">*</span></label>
        <select class="form-control" name="balance" id="balance" required="" title="Grupo">
          <option value="0" readonly>Seleccione balance</option>
          @foreach($balances as $balance)
          <option value="{{$balance->id}}" {{$balance->id == $categoria->id_balance ? 'selected' : ''}}>{{$balance->nombre}}</option>
          @endforeach
        </select>
        <span class="help-block error">
          <strong>{{ $errors->first('balance') }}</strong>
        </span>
      </div>
    </div>
    <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
</div>
<div class="modal-footer">
  <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
  <input  type="button" onclick="updatePuc()" class="btn btn-success" id="btnUpdate" value="Actualizar">

</div>

  </form>