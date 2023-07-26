@extends('layouts.app')
@section('content')
<div class="container-fluid">
  <div class="card border-0 shadow-sm">
    <form method="POST" action="{{ route('numeraciones_nomina.update', $numeracion->id) }}" class="forms-sample" id="form-numeracion-edit">
      <div class="card-body">
        @csrf

        <div class="form-group row">
          <label class="col-sm-2 col-form-label">Nombre <span class="text-danger">*</span>
            <a><i data-tippy-content="Asigna el nombre con el que identificarás esta numeración." class="icono far fa-question-circle" tabindex="0"></i></a>
          </label>
          <div class="col-sm-5">
            <input type="text" class="form-control" id="nombre" name="nombre" required="" value="{{$numeracion->nombre}}" maxlength="200">
            <span class="help-block error">
              <strong>{{ $errors->first('nombre') }}</strong>
            </span>
          </div>
        </div>

        <div class="form-group row">
          <label class="col-sm-2 col-form-label">Tipo de Nómina <span class="text-danger">*</span></label>
          <div class="col-sm-5">
            <select class="form-control selectpicker" name="tipo_nomina" id="tipo_nomina" title="Seleccione" data-live-search="true" data-size="5">
              <option value="1" {{$numeracion->tipo_nomina == 1 ? 'selected' : ''}}>Nómina Electrónica</option>
              <option value="2" {{$numeracion->tipo_nomina == 2 ? 'selected' : ''}}>Nómina de ajuste</option>
            </select>
            <span class="help-block error">
              <strong>{{ $errors->first('tipo_nomina') }}</strong>
            </span>
          </div>
        </div>


        <div class="form-group row">
          <label class="col-sm-2 col-form-label">Prefijo
            <a><i data-tippy-content="Agrega una o varias letras a tus numeraciones para diferenciarlas, por ejemplo puedes asignar VE para tu equipo de ventas." class="icono far fa-question-circle" tabindex="0"></i></a>
          </label>
          <div class="col-sm-5">
            <input type="text" class="form-control" id="prefijo" name="prefijo" value="{{$numeracion->prefijo}}" maxlength="8">
            <span class="help-block error">
              <strong>{{ $errors->first('prefijo') }}</strong>
            </span>
          </div>
        </div>

        <div class="form-group row">
          <label class="col-sm-2 col-form-label">Número inicial <span class="text-danger">*</span>
            <a><i data-tippy-content="Indica el número con el que empezarás a emitir documentos de nómina con esta numeración." class="icono far fa-question-circle" tabindex="0"></i></a>
          </label>
          <div class="col-sm-5">
            <input type="text" class="form-control" id="inicioverdadero" name="inicioverdadero" required="" value="{{$numeracion->inicioverdadero}}" maxlength="8" onkeypress="return event.charCode >= 48 && event.charCode <=57">
            <span class="help-block error">
              <strong>{{ $errors->first('inicioverdadero') }}</strong>
            </span>
          </div>
        </div>

        <div class="form-group row">
          <label class="col-sm-2 col-form-label">Próximo número<span class="text-danger">*</span></label>
          <div class="col-sm-5">
            <input type="text" class="form-control" id="inicio" name="inicio" required="" value="{{$numeracion->inicio}}" maxlength="8" onkeypress="return event.charCode >= 48 && event.charCode <=57">
            <span class="help-block error">
              <strong>{{ $errors->first('inicio') }}</strong>
            </span>
          </div>
        </div>



        <div class="form-group row">
          <label class="col-sm-2 col-form-label">Preferida
            <a><i data-tippy-content="Si marcas esta numeración como preferida será seleccionada por defecto cuando vayas a crear una emisión de nómina." class="icono far fa-question-circle" tabindex="0"></i></a>
          </label>
          <div class="row ml-1">
            <div class="col">
              <div class="form-radio">
                <label class="form-check-label">
                  <input type="radio" class="form-check-input" name="preferida" id="preferida1" value="1" {{ $numeracion->preferida == 1 ? 'checked' : '' }}> Si
                  <i class="input-helper"></i></label>
              </div>
            </div>
            <div class="col">
              <div class="form-radio">
                <label class="form-check-label">
                  <input type="radio" class="form-check-input" name="preferida" id="preferida" value="0" {{ $numeracion->preferida == 0 ? 'checked' : '' }}>No
                  <i class="input-helper"></i></label>
              </div>
            </div>
            <span class="help-block error">
              <strong>{{ $errors->first('preferida') }}</strong>
            </span>
          </div>
        </div>

        <br>
        <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
      </div>

      <div class="card-footer">
        <div class="row my-2">
          <div class="col-12">
            <a href="{{route('numeraciones_nomina.index')}}" class="btn btn-outline-secondary float-right m-1">Cancelar</a>
            <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success float-right m-1">Guardar</button>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection