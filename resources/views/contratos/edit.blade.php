@extends('layouts.app')
@section('content')
    <style>
        .readonly{ border: 0 !important;background-color: #f9f9f9 !important; font-weight: bold !important; }
        small { font-weight: 500; }
        .dropdown-header > span{font-weight: 500;}
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

    <form method="POST" action="{{ route('contratos.update', $contrato->id ) }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-contrato">
        {{ csrf_field() }}
        <input name="_method" type="hidden" value="PATCH">
        <div class="row">
            <input type="hidden" id="interfaz_user" value="{{$contrato->interfaz}}">
            <div class="col-md-4 form-group">
                <label class="control-label">Nombre del Cliente</label>
                <input type="text" class="form-control readonly"  id="nombre" name="nombre"  required="" value="{{$contrato->nombre}}" maxlength="200" readonly="">
                <span class="help-block error">
                    <strong>{{ $errors->first('nombre') }}</strong>
                </span>
            </div>
            <div class="col-md-4 form-group">
                <label class="control-label">Identificación</label>
                <input type="text" class="form-control readonly" id="ident" name="ident" readonly="" value="{{$contrato->nit}}" maxlength="20">
                <span class="help-block error">
                    <strong>{{ $errors->first('identificacion') }}</strong>
                </span>
            </div>
            <div class="col-md-4 form-group">
                <label class="control-label">Nro.Contrato</label>
                <input type="text" class="form-control readonly"  id="contrato" name="contrato"  value="{{$contrato->nro}}" maxlength="200" readonly="">
                <span class="help-block error">
                    <strong>{{ $errors->first('contrato') }}</strong>
                </span>
            </div>
            
            <div class="col-md-4 form-group">
	            <label class="control-label">Servidor <span class="text-danger">*</span></label>
	            <div class="input-group">
	                <select class="form-control selectpicker" name="server_configuration_id" id="server_configuration_id" required="" title="Seleccione" data-live-search="true" data-size="5" onchange="getPlanes(this.value);">
	                    @foreach($servidores as $servidor)
	                        <option value="{{$servidor->id}}" {{$servidor->id==$contrato->server_configuration_id?'selected':''}}>{{$servidor->nombre}} - {{$servidor->ip}}</option>
	                    @endforeach
	                </select>
	            </div>
	            <span class="help-block error">
	                <strong>{{ $errors->first('server_configuration_id') }}</strong>
	            </span>
	        </div>
            <div class="col-md-4 form-group">
                <label class="control-label">Plan <span class="text-danger">*</span></label>
                <div class="input-group">
                    <select class="form-control selectpicker" name="plan_id" id="plan_id" required="" title="Seleccione" data-live-search="true" data-size="5">
                        @foreach($planes as $plan)
                        <option value="{{$plan->id}}" {{$plan->id==$contrato->plan_id?'selected':''}}>{{$plan->type()}}: {{$plan->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4 form-group">
	            <label class="control-label">Tipo Conexión <span class="text-danger">*</span></label>
	            <select class="form-control selectpicker" id="conexion" name="conexion"  required="" title="Seleccione" data-live-search="true" data-size="5" onchange="interfazChange();">
	                <option value="1" {{$contrato->conexion==1?'selected':''}}>PPPOE</option>
	                <option value="2" {{$contrato->conexion==2?'selected':''}}>DHCP</option>
	                <option value="3" {{$contrato->conexion==3?'selected':''}}>IP Estática</option>
	                <option value="4" {{$contrato->conexion==4?'selected':''}}>VLAN</option>
	            </select>
	        </div>
	        
	        <div class="col-md-4 form-group {{$contrato->conexion==3?'':'d-none'}}" id="div_interfaz">
                <label class="control-label">Interfaz de Conexión <span class="text-danger">*</span></label>
                <div class="input-group">
	                <select class="form-control selectpicker" name="interfaz" id="interfaz" required="" title="Seleccione" data-live-search="true" data-size="5">
	                    @foreach($interfaces as $interfaz)
                        <option value="{{$interfaz->name}}" {{$interfaz->name==$contrato->interfaz?'selected':''}}>{{$interfaz->name}}</option>
                        @endforeach
	                </select>
                    <span class="help-block error">
                        <strong>{{ $errors->first('interfaz') }}</strong>
                    </span>
                </div>
            </div>
            <div class="col-md-4 form-group">
                <label class="control-label" id="div_local_address">Segmento de IP</label>
                  <div class="input-group">
                    {{-- <input type="text" class="form-control" name="local_address" value="{{$contrato->local_address}}" id="local_address" onkeypress="return event.charCode >= 48 && event.charCode <=57 || event.charCode==46 || event.charCode==47">
                    <div class="input-group-append" id="option_segmento">
                        @if($contrato->local_address_new)
                        <a href="javascript:deleteSegmento();" class="btn btn-outline-danger btn-sm"><i class="fas fa-minus" style="margin: 2px;"></i></a>
                        @else
                        <a href="javascript:addSegmento();" class="btn btn-outline-success btn-sm"><i class="fas fa-plus" style="margin: 2px;"></i></a>
                        @endif
                    </div> --}}
                    <select class="form-control selectpicker" name="local_address" id="local_address" required="" title="Seleccione" data-live-search="true" data-size="5">

                    </select>
                    <span class="help-block error">
                        <strong>{{ $errors->first('local_address') }}</strong>
                    </span>
                </div>
            </div>
            <div class="col-md-4 form-group">
                <label class="control-label" id="div_ip">Dirección IP (Remote Address) <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input type="text" class="form-control" name="ip" value="{{$contrato->ip}}" id="ip" required="" onkeypress="return event.charCode >= 48 && event.charCode <=57 || event.charCode==46">
                    <div class="input-group-append">
                        <button class="btn btn-outline-success btn-sm" type="button" id="searchIP"><i class="fa fa-search" style="margin: 2px;"></i></button>
                    </div>
                    <span class="help-block error">
                        <strong>{{ $errors->first('ip') }}</strong>
                    </span>
                </div>
            </div>
            
            <div class="col-md-4 form-group  {{$contrato->local_address_new?'':'d-none'}}" id="new_segmento">
                <label class="control-label">Segmento de IP</label>
                <input type="text" class="form-control" name="local_address_new" value="{{$contrato->local_address_new}}" id="local_address_new" onkeypress="return event.charCode >= 48 && event.charCode <=57 || event.charCode==46 || event.charCode==47">
            </div>
            <div class="col-md-4 form-group  {{$contrato->ip_new?'':'d-none'}}" id="new_ip">
                <label class="control-label" id="div_ip">Dirección IP (Remote Address) <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input type="text" class="form-control" name="ip_new" value="{{$contrato->ip_new}}" id="ip_new" required="" onkeypress="return event.charCode >= 48 && event.charCode <=57 || event.charCode==46">
                    <div class="input-group-append">
                        <button class="btn btn-outline-success btn-sm" type="button" id="searchIP2"><i class="fa fa-search" style="margin: 2px;"></i></button>
                    </div>
                    <span class="help-block error">
                        <strong>{{ $errors->first('ip_new') }}</strong>
                    </span>
                </div>
            </div>

            <div class="col-md-4 form-group {{$contrato->conexion==1?'':'d-none'}}" id="div_usuario">
                <label class="control-label">Usuario <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input type="text" class="form-control" name="usuario" id="usuario" value="{{ $contrato->usuario}}">
                    <span class="help-block error">
                        <strong>{{ $errors->first('usuario') }}</strong>
                    </span>
                </div>
            </div>

            <div class="col-md-4 form-group {{$contrato->conexion==1?'':'d-none'}}" id="div_password">
                <label class="control-label">Contraseña <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input type="text" class="form-control" name="password" id="password" value="{{ $contrato->password}}">
                    <span class="help-block error">
                        <strong>{{ $errors->first('password') }}</strong>
                    </span>
                </div>
            </div>

            <div class="col-md-4 form-group d-none">
                <label class="control-label">Access Point Asociado <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" id="ap" name="ap" required="" title="Seleccione" data-live-search="true" data-size="5">
                    @foreach($nodos as $nodo)
                    <optgroup label="{{$nodo->nombre}}">
                        @foreach($aps as $ap)
                            @if($ap->nodo==$nodo->id)
                            <option id="{{$ap->id}}" value="{{$ap->id}}" {{$ap->id==$contrato->ap?'selected':''}}>{{$ap->nombre}}</option>
                            @endif
                        @endforeach
                    </optgroup>
                    @endforeach
                </select>
                <span class="help-block error">
                    <strong>{{ $errors->first('ap') }}</strong>
                </span>
            </div>
            
            <div class="col-md-4 form-group {{$contrato->conexion==1?'d-none':''}}" id="div_mac">
                <label class="control-label">Dirección MAC <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="text" class="form-control mac_address" name="mac_address" id="mac_address" value="{{ $contrato->mac_address }}">
                    <span class="help-block error">
                        <strong>{{ $errors->first('mac_address') }}</strong>
                    </span>
                </div>
            </div>
            
            <div class="col-md-4 form-group">
                <label class="control-label">Grupo de Corte <span class="text-danger">*</span></label>
                <div class="input-group">
                    <select class="form-control selectpicker" name="grupo_corte" id="grupo_corte" required="" title="Seleccione" data-live-search="true" data-size="5">
                        @foreach($grupos as $grupo)
                            <option value="{{$grupo->id}}" {{$grupo->id == $contrato->grupo_corte? 'selected':''}}>{{$grupo->nombre}} (Corte {{ $grupo->fecha_corte }} - Suspensión {{ $grupo->fecha_suspension }})</option>
                        @endforeach
                    </select>
                </div>
                <span class="help-block error">
                    <strong>{{ $errors->first('grupo_corte') }}</strong>
                </span>
            </div>

            <div class="col-md-4 form-group">
                <label class="control-label">Puerto de Conexión</label>
                <div class="input-group">
                    <select class="form-control selectpicker" name="puerto_conexion" id="puerto_conexion" required="" title="Seleccione" data-live-search="true" data-size="5">
                        @foreach($puertos as $puerto)
                            <option value="{{$puerto->id}}" {{$puerto->id == $contrato->puerto_conexion? 'selected':''}}>{{$puerto->nombre}}</option>
                        @endforeach
                    </select>
                </div>
                <span class="help-block error">
                    <strong>{{ $errors->first('puerto_conexion') }}</strong>
                </span>
            </div>

            <div class="col-md-4 form-group">
	            <label class="control-label">Tipo Factura <span class="text-danger">*</span></label>
	            <div class="input-group">
	                <select class="form-control selectpicker" name="facturacion" id="facturacion" required="" title="Seleccione" data-live-search="true" data-size="5">
	                        <option value="1" {{$contrato->facturacion == 1 ? 'selected' : ''}}>Facturación Estándar</option>
	                        <option value="3" {{$contrato->facturacion == 3 ? 'selected' : ''}} >Facturación Electrónica</option>
	                </select>
	            </div>
	            <span class="help-block error">
	                <strong>{{ $errors->first('facturacion') }}</strong>
	            </span>
	        </div>
            <div class="col-md-4 form-group">
                <label class="control-label">Fecha de Suspensión <a><i data-tippy-content="Fecha de suspensión personalizada, distinta a la asociada al grupo de corte" class="icono far fa-question-circle"></i></a></label>
                <input type="number" class="form-control"  id="fecha_suspension" value="{{$contrato->fecha_suspension}}" name="fecha_suspension" min="1" max="30">

                <span class="help-block error">
                    <strong>{{ $errors->first('fecha_suspension') }}</strong>
                </span>
            </div>
            {{-- <div class="col-md-4 form-group d-none">
                <label class="control-label">Días para suspender <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" id="fecha_suspension" name="fecha_suspension" required="" value="{{ $contrato->fecha_suspension }}" title="Seleccione">
                    <option value="0" @if($contrato->fecha_suspension == 0) ? selected : '' @endif>No Suspender</option>
                    <option value="1" @if($contrato->fecha_suspension == 1) ? selected : '' @endif>1 día después de la fecha de corte</option>
                    <option value="2" @if($contrato->fecha_suspension == 2) ? selected : '' @endif>2 días después de la fecha de corte</option>
                    <option value="3" @if($contrato->fecha_suspension == 3) ? selected : '' @endif>3 días después de la fecha de corte</option>
                    <option value="4" @if($contrato->fecha_suspension == 4) ? selected : '' @endif>4 días después de la fecha de corte</option>
                    <option value="5" @if($contrato->fecha_suspension == 5) ? selected : '' @endif>5 días después de la fecha de corte</option>
                    <option value="6" @if($contrato->fecha_suspension == 6) ? selected : '' @endif>6 días después de la fecha de corte</option>
                    <option value="7" @if($contrato->fecha_suspension == 7) ? selected : '' @endif>7 días después de la fecha de corte</option>
                    <option value="8" @if($contrato->fecha_suspension == 8) ? selected : '' @endif>8 días después de la fecha de corte</option>
                    <option value="9" @if($contrato->fecha_suspension == 9) ? selected : '' @endif>9 días después de la fecha de corte</option>
                    <option value="10" @if($contrato->fecha_suspension == 10) ? selected : '' @endif>10 días después de la fecha de corte</option>
                    <option value="11" @if($contrato->fecha_suspension == 11) ? selected : '' @endif>11 días después de la fecha de corte</option>
                    <option value="12" @if($contrato->fecha_suspension == 12) ? selected : '' @endif>12 días después de la fecha de corte</option>
                    <option value="13" @if($contrato->fecha_suspension == 13) ? selected : '' @endif>13 días después de la fecha de corte</option>
                    <option value="14" @if($contrato->fecha_suspension == 14) ? selected : '' @endif>14 días después de la fecha de corte</option>
                    <option value="15" @if($contrato->fecha_suspension == 15) ? selected : '' @endif>15 días después de la fecha de corte</option>
                </select>
                <span class="help-block error">
                    <strong>{{ $errors->first('fecha_suspension') }}</strong>
                </span>
            </div>--}}
            
            <div class="col-md-12 d-none">
                <hr>
            </div>
            
            <div class="col-md-3 form-group d-none">
                <label class="control-label">Marca Router</label>
                <select class="form-control selectpicker" id="marca_router" name="marca_router" required="" value="{{ $contrato->marca_router }}" title="Seleccione">
                    @foreach($marcas as $marca)
                    <option value="{{$marca->id}}" @if($marca->id == $contrato->marca_router)? selected : '' @endif>{{$marca->nombre}}</option>
                    @endforeach
                </select>
                <span class="help-block error">
                    <strong>{{ $errors->first('marca_router') }}</strong>
                </span>
            </div>
            <div class="col-md-3 form-group d-none">
                <label class="control-label">Modelo Router</label>
                <input type="text" class="form-control"  id="modelo_router" name="modelo_router" required="" value="{{ $contrato->modelo_router }}" required="">
                <span class="help-block error">
                    <strong>{{ $errors->first('modelo_router') }}</strong>
                </span>
            </div>
            <div class="col-md-3 form-group d-none">
                <label class="control-label">Marca Antena</label>
                <select class="form-control selectpicker" id="marca_antena" name="marca_antena" required="" value="{{ $contrato->marca_antena }}" title="Seleccione">
                    @foreach($marcas as $marca)
                    <option value="{{$marca->id}}" @if($marca->id == $contrato->marca_antena)? selected : '' @endif>{{$marca->nombre}}</option>
                    @endforeach
                </select>
                <span class="help-block error">
                    <strong>{{ $errors->first('marca_antena') }}</strong>
                </span>
            </div>
            <div class="col-md-3 form-group d-none">
                <label class="control-label">Modelo Antena</label>
                <input type="text" class="form-control"  id="modelo_antena" name="modelo_antena" required="" value="{{ $contrato->modelo_antena }}" required="">
                <span class="help-block error">
                    <strong>{{ $errors->first('modelo_antena') }}</strong>
                </span>
            </div>
        </div>
        
        <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
        <hr>
        
        <div class="row" >
            <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
                <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" class="btn btn-success">Guardar</button>
            </div>
        </div>
    </form>
    
    <div class="modal fade" id="modal-ips" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body px-0">
                    <div class="row" style="text-align: center;">
                        <div class="col-md-12">
                            <h3>DIRECCIONES IP DISPONIBLES</h3>
                            <hr>
                        </div>
                    </div>
                    <div class="row" style="text-align: center;" id="row_ip">
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            $('#mac_address').mask('AA:AA:AA:AA:AA:AA', {
                'translation': {A: {pattern: /[0-9a-fA-F]/}},
            });
            getInterfaces($("#server_configuration_id").val());
        });
    </script>
@endsection