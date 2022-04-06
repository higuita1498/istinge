@extends('layouts.app')

@section('style')
    <style>
        #fecha_contratacion-error {
            top: 35px;
            position: absolute;
        }
    </style>
@endsection


@section('content')
    @if(Session::has('danger'))
        <div class="alert alert-danger">
            {{Session::get('danger')}}
        </div>

        <script type="text/javascript">
            setTimeout(function () {
                $('.alert').hide();
                $('.active_table').attr('class', ' ');
            }, 5000);
        </script>
    @endif

    @if(Session::has('error'))
        <div class="alert alert-danger">
            {{Session::get('error')}}
        </div>

        <script type="text/javascript">
            setTimeout(function () {
                $('.alert').hide();
                $('.active_table').attr('class', ' ');
            }, 5000);
        </script>
    @endif

    @if(isset($errors))
        
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger">{{ $error }}</div>
            <br>
        @endforeach
        
    @endif

    <form method="POST" action="{{ route('personas.store') }}" style="padding: 2% 3%;" role="form" class="forms-sample"
          id="form-contacto">
        @csrf
        <div class="row">
            <div class="form-group col-md-12">
                <span class="font-weight-bold" style="border: 0; border-bottom: 1px solid rgb(208 143 80);">DATOS PRINCIPALES</span>
            </div>

            <div class="form-group col-md-3">
                <label class="control-label">Nombre <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="nombre" id="nombre" required="" maxlength="150"
                       value="{{old('nombre')}}">
                <span class="help-block error">
					<strong>{{ $errors->first('nombre') }}</strong>
				</span>
            </div>
            <div class="form-group col-md-3">
                <label class="control-label">Apellido <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="apellido" id="apellido" required="" maxlength="150"
                       value="{{old('apellido')}}">
                <span class="help-block error">
					<strong>{{ $errors->first('apellido') }}</strong>
				</span>
            </div>
            <div class="form-group col-md-3">
                <label class="control-label">Tipo de Documento <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" name="tipo_documento" id="tipo_documento" required=""
                        title="Seleccione">
                    @foreach($identificaciones as $identificacion)
                        <option {{old('tipo_documento')==$identificacion->id?'selected':''}} @if($identificacion->id==6) class="d-none"
                                @endif value="{{$identificacion->id}}" @if($identificacion->id == 3) selected="selected"
                                @endif title="{{$identificacion->mini()}}">{{$identificacion->identificacion}}</option>
                    @endforeach
                </select>
                <span class="help-block error">
					<strong>{{ $errors->first('tipo_documento') }}</strong>
				</span>
            </div>
            <div class="form-group col-md-3">
                <label class="control-label">N° Documento <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="nro_documento" id="nro_documento" required=""
                       maxlength="30" value="{{old('nro_documento')}}"
                       onkeypress="return event.charCode >= 48 && event.charCode <=57">
                <span class="help-block error">
					<strong>{{ $errors->first('nro_documento') }}</strong>
				</span>
            </div>

            <div class="form-group col-md-3">
                <label class="control-label" for="email">Correo Electrónico <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="correo" name="correo" required="" maxlength="150"
                       value="{{old('correo')}}" autocomplete="off">
                <span class="help-block error">
					<strong>{{ $errors->first('correo') }}</strong>
				</span>
            </div>
            <div class="form-group col-md-3">
                <label class="control-label">Fecha de Nacimiento</label>
                <input type="text" class="form-control datepickerinput" id="nacimiento" value="{{old('nacimiento')}}"
                       name="nacimiento" autocomplete="off"
                       onkeypress="return event.charCode >= 48 && event.charCode <=57">
                <span class="help-block error">
					<strong>{{ $errors->first('nacimiento') }}</strong>
				</span>
            </div>
            <div class="form-group col-md-3">
                <label class="control-label">N° Celular</label>
                <input type="text" class="form-control" id="nro_celular" name="nro_celular" maxlength="30"
                       value="{{old('nro_celular')}}" onkeypress="return event.charCode >= 48 && event.charCode <=57">
                <span class="help-block error">
					<strong>{{ $errors->first('nro_celular') }}</strong>
				</span>
            </div>

            <div class="form-group col-md-3">
                <label class="control-label">Dirección hogar</label>
                <input type="text" class="form-control" name="direccion" value="{{old('direccion')}}" maxlength="500">
                <span class="help-block error">
					<strong>{{ $errors->first('direccion') }}</strong>
				</span>
            </div>

            <div class="form-group col-md-6" id="validatec1">
                <label class="control-label">Departamento <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" name="departamento" id="departamento" required=""
                        title="Seleccione" data-live-search="true" data-size="5"
                        onchange="searchMunicipality(this.value)">
                    @foreach($departamentos as $departamento)
                        <option value="{{ $departamento->id }}">{{ $departamento->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-6" id="validatec2">
                <label class="control-label">Municipio <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" name="municipio" id="municipio" required="" title="Seleccione"
                        data-live-search="true" data-size="5">
                </select>
            </div>

        </div>

        <div class="row mt-3">
            <div class="form-group col-md-12">
                <span class="font-weight-bold" style="border: 0; border-bottom: 1px solid rgb(208 143 80);">INFORMACIÓN LABORAL</span>
            </div>

            <div class="form-group col-md-3">
                <label class="control-label">Tipo de Contrato <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" name="tipo_contrato" id="tipo_contrato" required=""
                        title="Seleccione" data-live-search="true" data-size="5">
                    @foreach($tipo_contratos as $tipo_contrato)
                        <option {{old('tipo_contrato')==$tipo_contrato->id?'selected':''}} value="{{$tipo_contrato->id}}">{{$tipo_contrato->nombre}}</option>
                    @endforeach
                </select>
                <span class="help-block error">
					<strong>{{ $errors->first('tipo_contrato') }}</strong>
				</span>
            </div>
            <div class="form-group col-md-3">
                <label class="control-label">Término de Contrato <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" name="termino_contrato" id="termino_contrato" required=""
                        title="Seleccione" data-live-search="true" data-size="5">
                    @foreach($termino_contratos as $termino_contrato)
                        <option {{old('termino_contrato')==$termino_contrato->id?'selected':''}} value="{{$termino_contrato->id}}">{{$termino_contrato->nombre}}</option>
                    @endforeach
                </select>
                <span class="help-block error">
					<strong>{{ $errors->first('termino_contrato') }}</strong>
				</span>
            </div>
            <div class="form-group col-md-3">
                <label class="control-label">Fecha de Contratación <span class="text-danger">*</span></label>
                <input type="text" class="form-control datepicker" id="fecha_contratacion" required=""
                       value="{{old('fecha_contratacion')}}" name="fecha_contratacion" required="" autocomplete="off">
                <span class="help-block error">
                    <strong>{{ $errors->first('fecha_contratacion') }}</strong>
                </span>
            </div>
            <div class="form-group col-md-3 d-none" id="div_finalizacion">
                <label class="control-label">Fecha de Finalización de Contrato <span class="text-danger">*</span></label>
                <input type="text" class="form-control datepickerdos" id="fecha_finalizacion" value="{{old('fecha_finalizacion')}}" name="fecha_finalizacion" required="" autocomplete="off">
                <span class="help-block error">
                    <strong>{{ $errors->first('fecha_finalizacion') }}</strong>
                </span>
            </div>
            <div class="form-group col-md-3">
                <label class="control-label">Salario Base <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" name="salario_base" id="salario_base" required=""
                        title="Seleccione" data-live-search="true" data-size="5">
                    @foreach($salario_bases as $salario_base)
                        <option {{old('salario_base')==$salario_base->id?'selected':''}} value="{{$salario_base->id}}">{{$salario_base->nombre}}</option>
                    @endforeach
                </select>
                <span class="help-block error">
					<strong>{{ $errors->first('salario_base') }}</strong>
				</span>
            </div>

            <div class="form-group col-md-3">
                <label class="control-label">Valor <span class="text-danger">*</span></label>
                <input type="text" class="form-control valor" name="valor" id="valor" required="" maxlength="10"
                       value="{{old('valor')}}">
                <span class="help-block error">
					<strong>{{ $errors->first('valor') }}</strong>
				</span>
            </div>
            <div class="form-group col-md-3">
                <label class="control-label">¿Subsidio de transporte?</label>
                <div class="col-sm-12">
                    <div class="form-group row">
                        <div class="col-sm-4">
                            <div class="form-radio mt-1">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="subsidio" id="publico1" value="1"
                                           checked=""> Si <i class="input-helper"></i><i class="input-helper"></i>
                                </label>
                            </div>
                        </div>
                        <div class="col-sm-6 text-left">
                            <div class="form-radio mt-1">
                                <label class="form-check-label">
                                    <input type="radio" class="form-check-input" name="subsidio" id="publico" value="0">
                                    No <i class="input-helper"></i><i class="input-helper"></i>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <span class="help-block error">
					<strong>{{ $errors->first('subsidio') }}</strong>
				</span>
            </div>
            <div class="form-group col-md-3">
                <label class="control-label">Clase de Riesgo ARL</label>
                <select class="form-control selectpicker" name="clase_riesgo" id="clase_riesgo" title="Seleccione"
                        data-live-search="true" data-size="5">
                    @foreach($clase_riesgos as $clase_riesgo)
                        <option {{old('clase_riesgo')==$clase_riesgo->id?'selected':''}} value="{{$clase_riesgo->id}}">{{$clase_riesgo->nombre}}</option>
                    @endforeach
                </select>
                <span class="help-block error">
					<strong>{{ $errors->first('clase_riesgo') }}</strong>
				</span>
            </div>
            <div class="form-group col-md-3">
                <label class="control-label">Días Disfrutados Vacaciones <a><i
                                data-tippy-content="Días disfrutados de vacaciones desde que entró a la empresa"
                                class="icono far fa-question-circle"></i></a></label>
                <input type="text" class="form-control" name="dias_vacaciones" id="dias_vacaciones" maxlength="10"
                       value="{{old('dias_vacaciones')}}"
                       onkeypress="return event.charCode >= 48 && event.charCode <=57">
                <span class="help-block error">
					<strong>{{ $errors->first('dias_vacaciones') }}</strong>
				</span>
            </div>

            <div class="form-group col-md-12" style="overflow-x: auto;">
                <label class="control-label">Días de descanso</label>
                <div class="col-sm-12"  style="overflow-x: auto;">
                    <div class="form-group row mb-0">
                        <div class="col-sm-8 form-check-inline">
                            <div class="form-radio mt-1 mr-2">
                                <label class="form-check-label">
                                    <input type="checkbox" class="form-check-input" name="dias_descanso[]"
                                           value="Lunes"> Lunes <i class="input-helper"></i><i class="input-helper"></i>
                                </label>
                            </div>
                            <div class="form-radio mt-1 mr-2">
                                <label class="form-check-label">
                                    <input type="checkbox" class="form-check-input" name="dias_descanso[]"
                                           value="Martes"> Martes <i class="input-helper"></i><i
                                            class="input-helper"></i>
                                </label>
                            </div>
                            <div class="form-radio mt-1 mr-2">
                                <label class="form-check-label">
                                    <input type="checkbox" class="form-check-input" name="dias_descanso[]"
                                           value="Miercoles"> Miércoles <i class="input-helper"></i><i
                                            class="input-helper"></i>
                                </label>
                            </div>
                            <div class="form-radio mt-1 mr-2">
                                <label class="form-check-label">
                                    <input type="checkbox" class="form-check-input" name="dias_descanso[]"
                                           value="Jueves"> Jueves <i class="input-helper"></i><i
                                            class="input-helper"></i>
                                </label>
                            </div>
                            <div class="form-radio mt-1 mr-2">
                                <label class="form-check-label">
                                    <input type="checkbox" class="form-check-input" name="dias_descanso[]"
                                           value="Viernes"> Viernes <i class="input-helper"></i><i
                                            class="input-helper"></i>
                                </label>
                            </div>
                            <div class="form-radio mt-1 mr-2">
                                <label class="form-check-label">
                                    <input type="checkbox" class="form-check-input" name="dias_descanso[]"
                                           value="Sabado" checked=""> Sábado <i class="input-helper"></i><i
                                            class="input-helper"></i>
                                </label>
                            </div>
                            <div class="form-radio mt-1">
                                <label class="form-check-label">
                                    <input type="checkbox" class="form-check-input" name="dias_descanso[]"
                                           value="Domingo" checked=""> Domingo <i class="input-helper"></i><i
                                            class="input-helper"></i>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <span class="help-block error">
					<strong>{{ $errors->first('dias_descanso') }}</strong>
				</span>
            </div>
        </div>

        <div class="row mt-3">
            <div class="form-group col-md-12">
                <span class="font-weight-bold" style="border: 0; border-bottom: 1px solid rgb(208 143 80);">DATOS DE PAGO</span>
            </div>

            <div class="form-group col-md-3">
                <label class="control-label">Método de Pago <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" name="metodo_pago" required="" id="metodo_pago"
                        title="Seleccione" data-live-search="true">
                    @foreach($metodo_pagos as $metodo_pago)
                        <option {{$metodo_pago->id == $preferenciaPago->medio_pago ? 'selected' : ''}} {{old('metodo_pago')==$metodo_pago->id?'selected':''}}
                                @if($metodo_pago->id==2 || $metodo_pago->id==4 || $metodo_pago->id==5 || $metodo_pago->id==6 || $metodo_pago->id==7 || $metodo_pago->id==8) class="d-none"
                                @endif
                                value="{{$metodo_pago->id}}">{{$metodo_pago->metodo}}</option>
                    @endforeach
                </select>
                <span class="help-block error">
					<strong>{{ $errors->first('metodo_pago') }}</strong>
				</span>
            </div>
            <div class="form-group col-md-3 d-none transferencia">
                <label class="control-label">Banco <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" name="banco" id="banco" required="" title="Seleccione"
                        data-live-search="true" data-size="5">
                    @foreach($bancos as $banco)
                        <option {{old('banco')==$banco->id?'selected':''}} value="{{$banco->id}}">{{$banco->nombre}}</option>
                    @endforeach
                </select>
                <span class="help-block error">
					<strong>{{ $errors->first('banco') }}</strong>
				</span>
            </div>
            <div class="form-group col-md-3 d-none transferencia">
                <label class="control-label">Tipo de Cuenta <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" name="tipo_cuenta" id="tipo_cuenta" title="Seleccione">
                    <option value="1" {{old('tipo_cuenta')=='Ahorro'?'selected':''}}>Ahorro</option>
                    <option value="2" {{old('tipo_cuenta')=='Corriente'?'selected':''}}>Corriente</option>
                </select>
                <span class="help-block error">
					<strong>{{ $errors->first('tipo_cuenta') }}</strong>
				</span>
            </div>
            <div class="form-group col-md-3 d-none transferencia">
                <label class="control-label">N° de Cuenta <span class="text-danger">*</span></label>
                <input type="text" class="form-control" name="nro_cuenta" id="nro_cuenta" maxlength="30"
                       value="{{old('nro_cuenta')}}" onkeypress="return event.charCode >= 48 && event.charCode <=57">
                <span class="help-block error">
					<strong>{{ $errors->first('nro_cuenta') }}</strong>
				</span>
            </div>
        </div>

        <div class="row mt-3">
            <div class="form-group col-md-12">
                <span class="font-weight-bold" style="border: 0; border-bottom: 1px solid rgb(208 143 80);">DATOS DEL PUESTO DE TRABAJO</span>
            </div>

            <div class="form-group col-md-3">
                <label class="control-label">Sede de Trabajo <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" name="sede" id="sede" required="" title="Seleccione"
                        data-live-search="true" data-size="5">
                    @foreach($sedes as $sede)
                        <option {{old('sede')==$sede->id?'selected':''}} value="{{$sede->id}}">{{$sede->nombre}}</option>
                    @endforeach
                </select>
                <p class="text-left nomargin">
                    <a href="#" data-toggle="modal" data-target="#modalSede"><i class="fas fa-plus"></i> Crear Nueva
                        Sede</a>
                </p>
                <span class="help-block error">
					<strong>{{ $errors->first('sede') }}</strong>
				</span>
            </div>
            <div class="form-group col-md-3">
                <label class="control-label">Área <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" name="area" id="area" required="" title="Seleccione"
                        data-live-search="true" data-size="5">
                    @foreach($areas as $area)
                        <option {{old('area')==$area->id?'selected':''}} value="{{$area->id}}">{{$area->nombre}}</option>
                    @endforeach
                </select>
                <p class="text-left nomargin">
                    <a href="#" data-toggle="modal" data-target="#modalArea"><i class="fas fa-plus"></i> Crear Nueva
                        Área</a>
                </p>
                <span class="help-block error">
					<strong>{{ $errors->first('area') }}</strong>
				</span>
            </div>
            <div class="form-group col-md-3">
                <label class="control-label">Cargo <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" name="cargo" id="cargo" required="" title="Seleccione"
                        data-live-search="true" data-size="5">
                    @foreach($cargos as $cargo)
                        <option {{old('cargo')==$cargo->id?'selected':''}} value="{{$cargo->id}}">{{$cargo->nombre}}</option>
                    @endforeach
                </select>
                <p class="text-left nomargin">
                    <a href="#" data-toggle="modal" data-target="#modalCargo"><i class="fas fa-plus"></i> Crear Nuevo
                        Cargo</a>
                </p>
                <span class="help-block error">
					<strong>{{ $errors->first('cargo') }}</strong>
				</span>
            </div>
            <div class="form-group col-md-3">
                <label class="control-label">Centro de costo <span class="text-danger">*</span> <a><i
                                data-tippy-content="El centro de costos permitirá asociar a cada persona a una cuenta contable lo cual facilitara el ingreso de la información al software contable"
                                class="icono far fa-question-circle"></i></a></label>
                <select class="form-control selectpicker" name="centro_costo" id="centro_costo" required=""
                        title="Seleccione" data-live-search="true" data-size="5">
                    @foreach($centro_costos as $centro_costo)
                        <option {{old('centro_costo')==$centro_costo->id?'selected':''}} value="{{$centro_costo->id}}">{{$centro_costo->nombre}}</option>
                    @endforeach
                </select>
                <span class="help-block error">
					<strong>{{ $errors->first('centro_costo') }}</strong>
				</span>
            </div>
        </div>

        <div class="row mt-3">
            <div class="form-group col-md-12">
                <span class="font-weight-bold" style="border: 0; border-bottom: 1px solid rgb(208 143 80);">ENTIDADES DE SEGURIDAD SOCIAL</span>
            </div>

            <div class="form-group col-md-3">
                <label class="control-label">EPS <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" name="eps" id="eps" required="" title="Seleccione"
                        data-live-search="true" data-size="5">
                    @foreach($epss as $eps)
                        <option {{old('eps')==$eps->id?'selected':''}} value="{{$eps->id}}">{{$eps->nombre}}</option>
                    @endforeach
                </select>
                <span class="help-block error">
					<strong>{{ $errors->first('eps') }}</strong>
				</span>
            </div>
            <div class="form-group col-md-3">
                <label class="control-label">Fondo de pensiones <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" name="fondo_pension" id="fondo_pension" required=""
                        title="Seleccione" data-live-search="true" data-size="5">
                    @foreach($fondo_pensiones as $fondo_pension)
                        <option {{old('fondo_pension')==$fondo_pension->id?'selected':''}} value="{{$fondo_pension->id}}">{{$fondo_pension->nombre}}</option>
                    @endforeach
                </select>
                <span class="help-block error">
					<strong>{{ $errors->first('fondo_pension') }}</strong>
				</span>
            </div>
            <div class="form-group col-md-3">
                <label class="control-label">Fondo cesantías <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" name="fondo_cesantia" id="fondo_cesantia" required=""
                        title="Seleccione" data-live-search="true" data-size="5">
                    @foreach($fondo_cesantias as $fondo_cesantia)
                        <option {{old('fondo_cesantia')==$fondo_cesantia->id?'selected':''}} value="{{$fondo_cesantia->id}}">{{$fondo_cesantia->nombre}}</option>
                    @endforeach
                </select>
                <span class="help-block error">
					<strong>{{ $errors->first('fondo_cesantia') }}</strong>
				</span>
            </div>
        </div>

        <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>

        <hr>

        <div class="row" style="text-align: right;">
            <div class="col-md-12">
                <a href="{{route('personas.index')}}" class="btn btn-outline-light">Cancelar</a>
                <button type="submit" id="button-save" class="btn btn-success" onclick="guardarPersona(this)">Guardar
                </button>
            </div>
        </div>
    </form>

    <div class="modal fade" id="modalSede" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    @include('nomina.personas.modal.sede')
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalArea" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    @include('nomina.personas.modal.area')
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCargo" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title"></h4>
                </div>
                <div class="modal-body">
                    @include('nomina.personas.modal.cargo')
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            $('.valor').mask('000.000.000', {reverse: true});
            $('#metodo_pago').trigger('change');
            $('.datepickerdos').datepicker({
                locale: 'es-es',
                uiLibrary: 'bootstrap4',
                format: 'dd-mm-yyyy',
            });
        });

        $('#metodo_pago').change(function (e) {
            if ($('#metodo_pago').val() == '3') {
                $('.transferencia').removeClass('d-none');
                $('#banco,#tipo_cuenta,#nro_cuenta').val('');
                $('#banco,#tipo_cuenta,#nro_cuenta').selectpicker('refresh');
                $('#banco,#tipo_cuenta,#nro_cuenta').attr('required', true);
            } else {
                $('.transferencia').addClass('d-none');
                $('#banco,#tipo_cuenta,#nro_cuenta').val('');
                $('#banco,#tipo_cuenta,#nro_cuenta').selectpicker('refresh');
                $('#banco,#tipo_cuenta,#nro_cuenta').removeAttr('required');
            }
        });

        $('#termino_contrato').change(function (e) {
            if($('#termino_contrato').val() == '1'){
                $('#div_finalizacion').addClass('d-none');
                $('#fecha_finalizacion').removeAttr('required');
            }else{
                $('#div_finalizacion').removeClass('d-none');
                $('#fecha_finalizacion').attr('required', true);
            }
        });

        $('#fecha_contratacion').change(function() {
            if ($('#fecha_finalizacion').length > 0) {fecha(); }
        });

        $("#form-contacto").validate();

        function guardarPersona(btn) {
            setTimeout(function () {
                $(btn).attr('disabled', true);
            }, 500);

            setTimeout(function () {
                $(btn).attr('disabled', false);
            }, 3500);
        }

        function fecha(){
            $('#fecha_finalizacion').datepicker('destroy');
            $('#fecha_finalizacion').datepicker({
                locale: 'es-es',
                format: 'dd-mm-yyyy',
                uiLibrary: 'bootstrap4',
                minDate: $('#fecha_contratacion').val()
            });
        }
    </script>
@endsection
