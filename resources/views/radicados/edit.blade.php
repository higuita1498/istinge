@extends('layouts.app')
@section('content')
  <style>
    .readonly{ border: 0 !important;background-color: #f9f9f9 !important; }
    label, small { font-weight: 500; }
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

  <form method="POST" action="{{ route('radicados.update', $radicado->id ) }}" style="padding: 2% 3%;" role="form" class="forms-sample" id="form-banco" >
   {{ csrf_field() }}
      <input name="_method" type="hidden" value="PATCH">
      <div class="row">
    <div class="col-md-4 form-group">
      <label class="control-label">Nombre del Cliente</label>
      <input type="text" class="form-control readonly"  id="nombre" name="nombre"  required="" value="{{$radicado->nombre}}" maxlength="200" readonly="">
      <span class="help-block error">
        <strong>{{ $errors->first('nombre') }}</strong>
      </span>
    </div>

    <div class="col-md-4 form-group">
      <label class="control-label">Identificación</label>
      <input type="text" class="form-control readonly" id="ident" name="ident" readonly="" value="{{$radicado->identificacion}}" maxlength="20">
      <span class="help-block error">
        <strong>{{ $errors->first('identificacion') }}</strong>
      </span>
    </div>

    <div class="col-md-4 form-group">
      <label class="control-label">N° Contrato</label>
      <input type="text" class="form-control readonly"  id="contrato" name="contrato"  value="{{$radicado->contrato}}" maxlength="200" readonly="">
      <span class="help-block error">
        <strong>{{ $errors->first('contrato') }}</strong>
      </span>
    </div>

    <div class="col-md-4 form-group">
      <label class="control-label">N° Telefónico <span class="text-danger">*</span></label>
      <input type="text" class="form-control"  id="telefono" name="telefono"  required="" value="{{$radicado->telefono}}" maxlength="20">
      <span class="help-block error">
        <strong>{{ $errors->first('telefono') }}</strong>
      </span>
    </div>

    <div class="col-md-4 form-group">
      <label class="control-label">Correo Electrónico</label>
      <input type="email" class="form-control"  id="correo" name="correo"  value="{{$radicado->correo}}" maxlength="200">
      <span class="help-block error">
        <strong>{{ $errors->first('correo') }}</strong>
      </span>
    </div>

    <div class="col-md-12 form-group">
      <label class="control-label">Dirección <span class="text-danger">*</span></label>
      <input type="text" class="form-control"  id="direccion" name="direccion"  value="{{$radicado->direccion}}" maxlength="200" required="">
      <span class="help-block error">
        <strong>{{ $errors->first('direccion') }}</strong>
      </span>
    </div>

    <div class="col-md-3 form-group">
      <label class="control-label">Fecha <span class="text-danger">*</span></label>
      <input type="text" class="form-control datepicker"  id="fecha" name="fecha" required="" value="{{ date('d-m-Y', strtotime($radicado->fecha))}}" required="">
      <span class="help-block error">
        <strong>{{ $errors->first('fecha') }}</strong>
      </span>
    </div>

    <div class="col-md-3 form-group">
      <label class="control-label">Tipo de Servicio <span class="text-danger">*</span></label>
      <select class="form-control selectpicker" name="servicio" id="servicio" required="" title="Seleccione" required="" onchange="serviceDV(this.value)">
        @foreach($servicios as $servicio)
          <option {{ $radicado->servicio==$servicio->id?'selected':''}} value="{{$servicio->id}}">{{$servicio->nombre}}</option>
        @endforeach
      </select>
      <span class="help-block error">
        <strong>{{ $errors->first('servicio') }}</strong>
      </span>
    </div>

    <div class="col-md-3 form-group" id="div_valor" @if($radicado->servicio == 4) style="display: block;" @else style="display: none;" @endif>
      <label class="control-label">Valor de la Instalación <span class="text-danger">*</span></label>
      <input type="number" class="form-control" name="valor" id="valor" value="{{ $radicado->valor }}">
    </div>

    <div class="col-md-3 form-group">
      <label class="control-label">¿Escalar Caso? <span class="text-danger">*</span></label>
      <select class="form-control selectpicker" name="estatus" id="estatus" required="" title="Seleccione" onchange="searchDV(this.value)" required="">
          <option {{ $radicado->tecnico == NULL ? 'selected':'' }} value="0" selected>No</option>
          <option {{ $radicado->tecnico != NULL ? 'selected':'' }} value="2">Si</option>
      </select>
    </div>

    <div class="col-md-3 form-group" id="div_tecnico" style="display:{{ $radicado->tecnico != NULL ? 'block':'none' }};">
      <label class="control-label">Técnico Asociado <span class="text-danger">*</span></label>
      <select class="form-control selectpicker" name="tecnico" id="tecnico" title="Seleccione">
        @foreach($tecnicos as $tecnico)
          <option {{ $radicado->tecnico == $tecnico->id?'selected':''}} value="{{$tecnico->id}}">{{$tecnico->nombres}}</option>
        @endforeach
      </select>
    </div>
    <div class="col-md-3 form-group">
      <label class="control-label">Prioridad <span class="text-danger">*</span></label>
      <select class="form-control selectpicker" name="prioridad" id="prioridad" required="" title="Seleccione">
        <option value="1" {{ $radicado->prioridad == 1 ? 'selected':'' }}>Baja</option>
        <option value="2" {{ $radicado->prioridad == 2 ? 'selected':'' }}>Media</option>
        <option value="3" {{ $radicado->prioridad == 3 ? 'selected':'' }}>Alta</option>
      </select>
      <span class="help-block error">
        <strong>{{ $errors->first('prioridad') }}</strong>
      </span>
    </div>
    <div class="col-md-3 form-group">
        <label class="control-label">Medio de atencion </span></label>
        <select class="form-control selectpicker" name="medio" id="prioridad" required="" title="Seleccione">
            <option value="Oficina" {{ old('medio', $radicado->medio) == 'Oficina' ? 'selected' : '' }}>Oficina</option>
            <option value="Línea Telefónica" {{ old('medio', $radicado->medio) == 'Línea Telefónica' ? 'selected' : '' }}>Línea Telefónica</option>
            <option value="Página Web" {{ old('medio', $radicado->medio) == 'Página Web' ? 'selected' : '' }}>Página Web</option>
            <option value="Red social" {{ old('medio', $radicado->medio) == 'Red social' ? 'selected' : '' }}>Red social</option>
            <option value="Otros" {{ old('medio', $radicado->medio) == 'Otros' ? 'selected' : '' }}>Otros</option>
            <option value="Aplicación móvil" {{ old('medio', $radicado->medio) == 'Aplicación móvil' ? 'selected' : '' }}>Aplicación móvil</option>
            <option value="Servicios de mensajería instantánea" {{ old('medio', $radicado->medio) == 'Servicios de mensajería instantánea' ? 'selected' : '' }}>Servicios de mensajería instantánea</option>
        </select>
        <span class="help-block error">
            <strong>{{ $errors->first('prioridad') }}</strong>
        </span>
    </div>
    <div class="col-md-3 form-group">
        <label class="control-label">Grado de satifacción </span></label>
        <select class="form-control selectpicker" name="grado" id="prioridad" required="" title="Seleccione">
            <option value="USUARIOS_NS_MUY_INSATISFECHO" {{ old('medio', $radicado->medio) == 'USUARIOS_NS_MUY_INSATISFECHO' ? 'selected' : '' }}>USUARIOS_NS_MUY_INSATISFECHO</option>
            <option value="USUARIOS_NS_INSATISFECHO" {{ old('medio', $radicado->medio) == 'USUARIOS_NS_INSATISFECHO' ? 'selected' : '' }}>USUARIOS_NS_INSATISFECHO</option>
            <option value="USUAR_NS_NI_INSATISF_NI_SATISF" {{ old('medio', $radicado->medio) == 'USUAR_NS_NI_INSATISF_NI_SATISF' ? 'selected' : '' }}>USUAR_NS_NI_INSATISF_NI_SATISF</option>
            <option value="USUARIOS_NS_SATISFECHO" {{ old('medio', $radicado->medio) == 'USUARIOS_NS_SATISFECHO' ? 'selected' : '' }}>USUARIOS_NS_SATISFECHO</option>
            <option value="USUARIOS_NS_MUY_SATISFECHO" {{ old('medio', $radicado->medio) == 'USUARIOS_NS_MUY_SATISFECHO' ? 'selected' : '' }}>USUARIOS_NS_MUY_SATISFECHO</option>

        </select>
        <span class="help-block error">
            <strong>{{ $errors->first('prioridad') }}</strong>
        </span>
    </div>
    <div class="col-md-3 form-group">
                <label class="control-label">Revisión </span></label>
                <select class="form-control selectpicker" name="revision" id="revision" required="" title="Seleccione">
                    <option value="Pendiente">Pendiente	</option>
                    <option value="Rechazado">Rechazado</option>
                    <option value="Aprobado">Aprobado</option>
                </select>
                <span class="help-block error">
                    <strong>{{ $errors->first('prevision') }}</strong>
                </span>
            </div>
    @if(Auth::user()->empresa()->oficina)
    <div class="form-group col-md-3">
      <label class="control-label">Oficina Asociada <span class="text-danger">*</span></label>
      <select class="form-control selectpicker" name="oficina" id="oficina" required="" title="Seleccione" data-live-search="true" data-size="5">
        @foreach($oficinas as $oficina)
          <option value="{{$oficina->id}}" {{ $oficina->id == auth()->user()->oficina ? 'selected' : '' }}>{{$oficina->nombre}}</option>
        @endforeach
      </select>
    </div>
    @endif

    <div class="col-md-12 form-group">
      <label class="control-label">Observaciones <span class="text-danger">*</span></label>
      <p style="color:gray; font-weight:500; text-align:justify;">{{ $radicado->desconocido }}</p>
      <textarea  class="form-control form-control-sm min_max_100" id="desconocido" name="desconocido"></textarea>
      <span class="help-block error">
        <strong>{{ $errors->first('desconocido') }}</strong>
      </span>
    </div>
  </div>
  <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
  <hr>
  <div class="row" >
    <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
      <a href="{{route('radicados.index')}}" class="btn btn-outline-secondary">Cancelar</a>
      <button type="submit" class="btn btn-success">Guardar</button>
    </div>
  </div>
</form>
@endsection

@section('scripts')
  <script>
    function searchDV(id){
      option = id;
      if (option == 2) {
        document.getElementById("div_tecnico").style.display = "block";
      }else{
        document.getElementById("div_tecnico").style.display = "none";
        $("#tecnico").val('').selectpicker('refresh');
      }
    }

    function serviceDV(id){
      if (id == 4) {
        document.getElementById("div_valor").style.display = "block";
        $("#valor").val('').prop('required', true);
      }else{
        document.getElementById("div_valor").style.display = "none";
        $("#valor").val('').prop('required', false);
      }
    }
  </script>
@endsection
