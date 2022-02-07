@extends('layouts.app')
@section('content')
	<style>
        .readonly{ border: 0 !important; }
        .dropdown-header > span{ font-weight: 500; }
    </style>

    @if(Session::has('danger'))
		<div class="alert alert-danger" >
			{{Session::get('danger')}}
		</div>

		<script type="text/javascript">
			setTimeout(function(){
			    $('.alert').hide();
			    $('.active_table').attr('class', ' ');
			}, 10000);
		</script>
	@endif
	
	<form method="POST" action="{{ route('contratos.store') }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-contrato">
	    @csrf
	    <div class="row">
	        <div class="col-md-4 form-group">
	            <label class="control-label">Cliente <span class="text-danger">*</span></label>
	            <div class="input-group">
	                <select class="form-control selectpicker" name="client_id" id="client_id" required="" title="Seleccione" data-live-search="true" data-size="5">
	                    @foreach($clientes as $cliente)
	                        <option value="{{$cliente->id}}">{{$cliente->nombre}} - {{$cliente->nit}}</option>
	                    @endforeach
	                </select>
	                <div class="input-group-append">
	                    <a href="#" data-toggle="modal" data-target="#contactoModal" class="btn btn-outline-success btn-sm">
	                        <i class="fas fa-plus" style="margin: 2px;"></i>
	                    </a>
	                </div>
	            </div>
	            <span class="help-block error">
	                <strong>{{ $errors->first('client_id') }}</strong>
	            </span>
	        </div>
	        
	        <div class="col-md-4 form-group">
	            <label class="control-label">Servidor <span class="text-danger">*</span></label>
	            <div class="input-group">
	                <select class="form-control selectpicker" name="server_configuration_id" id="server_configuration_id" required="" title="Seleccione" data-live-search="true" data-size="5" onchange="getPlanes(this.value);">
	                    @foreach($servidores as $servidor)
	                        <option value="{{$servidor->id}}" selected>{{$servidor->nombre}} - {{$servidor->ip}}</option>
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
	                    
	                </select>
	            </div>
	            <span class="help-block error">
	                <strong>{{ $errors->first('plan_id') }}</strong>
	            </span>
	        </div>
	        
	        <div class="col-md-4 form-group">
	            <label class="control-label">Tipo Conexión <span class="text-danger">*</span></label>
	            <select class="form-control selectpicker" id="conexion" name="conexion"  required="" title="Seleccione" data-live-search="true" data-size="5" onchange="interfazChange();">
	                <option value="1">PPPOE</option>
	                <option value="2" disabled>DHCP</option>
	                <option value="3">IP Estática</option>
	                <option value="4">VLAN</option>
	            </select>
	        </div>
	        
	        <div class="col-md-4 form-group d-none" id="div_name_vlan">
	            <label class="control-label">Nombre VLAN <span class="text-danger">*</span></label>
	            <div class="input-group">
	                <input type="text" class="form-control" name="name_vlan" id="name_vlan">
	                <span class="help-block error">
	                    <strong>{{ $errors->first('name_vlan') }}</strong>
	                </span>
	            </div>
	        </div>
      
            <div class="col-md-4 form-group d-none" id="div_id_vlan">
                <label class="control-label">ID VLAN <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="number" class="form-control" name="id_vlan" id="id_vlan" min="1" max="4095">
                    <span class="help-block error">
                        <strong>{{ $errors->first('id_vlan') }}</strong>
                    </span>
                </div>
            </div>
              
            <div class="col-md-4 form-group d-none" id="div_interfaz">
                <label class="control-label">Interfaz de Conexión <span class="text-danger">*</span></label>
                <div class="input-group">
	                <select class="form-control selectpicker" name="interfaz" id="interfaz" required="" title="Seleccione" data-live-search="true" data-size="5">
	                    
	                </select>
                    <span class="help-block error">
                        <strong>{{ $errors->first('interfaz') }}</strong>
                    </span>
                </div>
            </div>
              
            <div class="col-md-4 form-group d-none" id="div_mac">
                <label class="control-label">Dirección MAC de Conexión</label>
                <div class="input-group">
                    <input type="text" class="form-control mac_address" name="mac_address" id="mac_address">
                    <span class="help-block error">
                        <strong>{{ $errors->first('mac_address') }}</strong>
                    </span>
                </div>
            </div>
              
            <div class="col-md-4 form-group">
                <label class="control-label" id="div_local_address">Segmento de IP <span class="text-danger">*</span></label>
                  <div class="input-group">
                    {{--<input type="text" class="form-control" name="local_address" id="local_address" onkeypress="return event.charCode >= 48 && event.charCode <=57 || event.charCode==46 || event.charCode==47">--}}
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
                    <input type="text" class="form-control" name="ip" id="ip" required="" readonly onkeypress="return event.charCode >= 48 && event.charCode <=57 || event.charCode==46 || event.charCode==47">
                    <div class="input-group-append">
                        <button class="btn btn-outline-success btn-sm" type="button" id="searchIP"><i class="fa fa-search" style="margin: 2px;"></i></button>
                    </div>
                    <span class="help-block error">
                        <strong>{{ $errors->first('ip') }}</strong>
                    </span>
                </div>
            </div>
            
            <div class="col-md-4 form-group d-none" id="new_segmento">
                <label class="control-label">Segmento de IP</label>
                <input type="text" class="form-control" name="local_address_new" id="local_address_new" onkeypress="return event.charCode >= 48 && event.charCode <=57 || event.charCode==46 || event.charCode==47">
            </div>
              
            <div class="col-md-4 form-group d-none" id="new_ip">
                <label class="control-label" id="div_ip">Dirección IP (Remote Address) <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input type="text" class="form-control" name="ip_new" id="ip_new" readonly required="" onkeypress="return event.charCode >= 48 && event.charCode <=57 || event.charCode==46">
                    <div class="input-group-append">
                        <button class="btn btn-outline-success btn-sm" type="button" id="searchIP2"><i class="fa fa-search" style="margin: 2px;"></i></button>
                    </div>
                    <span class="help-block error">
                        <strong>{{ $errors->first('ip_new') }}</strong>
                    </span>
                </div>
            </div>
              
            <div class="col-md-4 form-group" id="div_usuario">
                <label class="control-label">Usuario <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input type="text" class="form-control" name="usuario" id="usuario">
                    <span class="help-block error">
                        <strong>{{ $errors->first('usuario') }}</strong>
                    </span>
                </div>
            </div>
              
            <div class="col-md-4 form-group" id="div_password">
                <label class="control-label">Contraseña <span class="text-danger">*</span></label>
                  <div class="input-group">
                    <input type="text" class="form-control" name="password" id="password">
                    <span class="help-block error">
                        <strong>{{ $errors->first('password') }}</strong>
                    </span>
                </div>
            </div>
              
            <div class="col-md-4 form-group d-none" id="div_ap">
                <label class="control-label">Access Point Asociado <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" id="ap" name="ap" required="" title="Seleccione" data-live-search="true" data-size="5">
                    @foreach($nodos as $nodo)
                    <optgroup label="{{$nodo->nombre}}">
                        @foreach($aps as $ap)
                            @if($ap->nodo==$nodo->id)
                                <option id="{{$ap->id}}" value="{{$ap->id}}">{{$ap->nombre}}</option>
                            @endif
                        @endforeach
                    </optgroup>
                    @endforeach
                </select>
                <span class="help-block error">
                    <strong>{{ $errors->first('ap') }}</strong>
                </span>
            </div>
            
            <div class="col-md-4 form-group d-none">
                <label class="control-label">¿Aplicar reuso? <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" id="rehuso" name="rehuso" required="" title="Seleccione">
                    <option value="SI">Si</option>
                    <option value="NO">No</option>
                </select>
                <span class="help-block error">
                    <strong>{{ $errors->first('rehuso') }}</strong>
                </span>
            </div>
            
            <div class="col-md-4 form-group d-none" id="div_rehuso_aplicar">
                <label class="control-label">Reuso a aplicar<span class="text-danger">*</span></label>
                <div class="input-group mb-2">
                    <div class="input-group-prepend">
                        <div class="input-group-text" style="background: #f3f5f6;border-color: #dee4e6;">1:</div>
                    </div>
                    <input type="text" class="form-control" name="rehuso_aplicar" id="rehuso_aplicar">
                </div>
                <span class="help-block error">
                    <strong>{{ $errors->first('rehuso_aplicar') }}</strong>
                </span>
            </div>
            
            <div class="col-md-4 form-group">
                <label class="">Dirección MAC</label>
                  <div class="input-group">
                    <input type="text" class="form-control mac_address" name="mac_address" id="mac_address">
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
	                        <option value="{{$grupo->id}}">{{$grupo->nombre}}</option>
	                    @endforeach
	                </select>
	            </div>
	            <span class="help-block error">
	                <strong>{{ $errors->first('grupo_corte') }}</strong>
	            </span>
	        </div>
              
            <div class="col-md-12 d-none">
                <hr>
            </div>
            
            <div class="col-md-3 form-group d-none">
                <label class="control-label">Marca Router</label>
                <select class="form-control selectpicker" id="marca_router" name="marca_router" title="Seleccione">
                    @foreach($marcas as $marca)
                    <option value="{{$marca->id}}">{{$marca->nombre}}</option>
                    @endforeach
                </select>
                <span class="help-block error">
                    <strong>{{ $errors->first('marca_router') }}</strong>
                </span>
            </div>
            
            <div class="col-md-3 form-group d-none">
                <label class="control-label">Modelo Router</label>
                <input type="text" class="form-control"  id="modelo_router" name="modelo_router">
                <span class="help-block error">
                    <strong>{{ $errors->first('modelo_router') }}</strong>
                </span>
            </div>
            
            <div class="col-md-3 form-group d-none">
                <label class="control-label">Marca Antena</label>
                <select class="form-control selectpicker" id="marca_antena" name="marca_antena" title="Seleccione">
                    @foreach($marcas as $marca)
                    <option value="{{$marca->id}}">{{$marca->nombre}}</option>
                    @endforeach
                </select>
                <span class="help-block error">
                    <strong>{{ $errors->first('marca_antena') }}</strong>
                </span>
            </div>
            
            <div class="col-md-3 form-group d-none">
                <label class="control-label">Modelo Antena</label>
                <input type="text" class="form-control"  id="modelo_antena" name="modelo_antena">
                <span class="help-block error">
                    <strong>{{ $errors->first('modelo_antena') }}</strong>
                </span>
            </div>
        </div>
        
        <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
        <hr>
        
        <div class="row" >
            <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
                <a href="{{route('contratos.index')}}" class="btn btn-outline-secondary">Cancelar</a>
                <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
            </div>
        </div>
    </form>

    <div class="modal fade" id="contactoModal" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body px-0">
                    @include('contactos.modal.modal')
                </div>
            </div>
        </div>
    </div>
    
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
        });
    </script>
@endsection
