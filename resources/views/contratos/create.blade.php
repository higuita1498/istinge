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
			}, 100000);
		</script>
	@endif
	
	<form method="POST" action="{{ route('contratos.store') }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-contrato" enctype="multipart/form-data">
	    @csrf
	    <div class="row">
	        <div class="col-md-4 form-group">
	            <label class="control-label">Cliente <span class="text-danger">*</span></label>
	            <div class="input-group">
	                <select class="form-control selectpicker" name="client_id" id="client_id" required="" title="Seleccione" data-live-search="true" data-size="5" onchange="getContracts(this.value)">
	                    @foreach($clientes as $client)
	                        <option value="{{$client->id}}" {{$cliente== $client->id?'selected':''}} >{{$client->nombre}} - {{$client->nit}}</option>
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
	                        <option value="{{$servidor->id}}">{{$servidor->nombre}} - {{$servidor->ip}}</option>
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
                    <option value="2">DHCP</option>
                    <option value="3">IP Estática</option>
                    <option value="4">VLAN</option>
                </select>
                <input type="hidden" name="amarre_mac" id="amarre_mac">
            </div>

            <div class="col-md-4 form-group d-none" id="div_dhcp">
                <label class="control-label">Simple Queue <span class="text-danger">*</span></label>
                <select class="form-control selectpicker" id="simple_queue" name="simple_queue"  required="" title="Seleccione" data-live-search="true" data-size="5">
                    <option value="dinamica" {{old('simple_queue') == 'dinamica' ? 'selected':''}}>Dinámica</option>
                    <option value="estatica" {{old('simple_queue') == 'estatica' ? 'selected':''}}>Estática</option>
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
              
            <div class="col-md-4 form-group">
                <label class="control-label" id="div_local_address">Segmento de IP <span class="text-danger">*</span></label>
                  <div class="input-group">
                    {{--<input type="text" class="form-control" name="local_address" id="local_address" onkeypress="return event.charCode >= 48 && event.charCode <=57 || event.charCode==46 || event.charCode==47">--}}
                    <input type="hidden" id="segmento_bd">
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

            <div class="col-md-4 form-group" id="div_mac">
                <label class="control-label">Dirección MAC</label>
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
                            <option value="{{$grupo->id}}">{{$grupo->nombre}} (Corte {{ $grupo->fecha_corte }} - Suspensión {{ $grupo->fecha_suspension }})</option>
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
                            <option value="{{$puerto->id}}">{{$puerto->nombre}}</option>
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
	                        <option value="1">Facturación Estándar</option>
	                        <option value="3">Facturación Electrónica</option>
	                </select>
	            </div>
	            <span class="help-block error">
	                <strong>{{ $errors->first('facturacion') }}</strong>
	            </span>
	        </div>

            <div class="col-md-4 form-group d-none" id="div_facturacion">
                <label class="control-label">Facturación Individual <span class="text-danger">*</span> <a><i data-tippy-content="Indicar si desea crear una factura general con los otros contratos o crear individualmente" class="icono far fa-question-circle"></i></a></label>
                <div class="input-group">
                    <select class="form-control selectpicker" name="factura_individual" id="factura_individual" required="" title="Seleccione" data-live-search="true" data-size="5">
                            <option value="1">Si</option>
                            <option value="0">No</option>
                    </select>
                </div>
                <span class="help-block error">
                    <strong>{{ $errors->first('grupo_corte') }}</strong>
                </span>
            </div>

            <div class="col-md-12 text-center">
                <hr>
                <h4>ADJUNTOS RELACIONADOS AL CONTRATO</h4>
            </div>

            <div class="col-md-3 form-group">
                <label class="control-label">Referencia A</label>
                <input type="text" class="form-control" id="referencia_a" name="referencia_a" value="{{old('referencia_a')}}">
                <span style="color: red;">
                    <strong>{{ $errors->first('referencia_a') }}</strong>
                </span>
            </div>
            <div class="col-md-3 form-group">
                <label class="control-label">Referencia B</label>
                <input type="text" class="form-control" id="referencia_b" name="referencia_b" value="{{old('referencia_b')}}">
                <span style="color: red;">
                    <strong>{{ $errors->first('referencia_b') }}</strong>
                </span>
            </div>
            <div class="col-md-3 form-group">
                <label class="control-label">Referencia C</label>
                <input type="text" class="form-control" id="referencia_c" name="referencia_c" value="{{old('referencia_c')}}">
                <span style="color: red;">
                    <strong>{{ $errors->first('referencia_c') }}</strong>
                </span>
            </div>
            <div class="col-md-3 form-group">
                <label class="control-label">Referencia D</label>
                <input type="text" class="form-control" id="referencia_d" name="referencia_d" value="{{old('referencia_d')}}">
                <span style="color: red;">
                    <strong>{{ $errors->first('referencia_d') }}</strong>
                </span>
            </div>

            <div class="col-md-3 form-group">
                <label class="control-label">Adjunto A</label>
                <input type="file" class="form-control"  id="adjunto_a" name="adjunto_a" value="{{old('adjunto_a')}}" accept=".jpg, .jpeg, .png, .pdf, .JPG, .JPEG, .PNG, .PDF">
                <span style="color: red;">
                    <strong>{{ $errors->first('adjunto_a') }}</strong>
                </span>
            </div>
            <div class="col-md-3 form-group">
                <label class="control-label">Adjunto B</label>
                <input type="file" class="form-control"  id="adjunto_b" name="adjunto_b" value="{{old('adjunto_b')}}" accept=".jpg, .jpeg, .png, .pdf, .JPG, .JPEG, .PNG, .PDF">
                <span style="color: red;">
                    <strong>{{ $errors->first('adjunto_b') }}</strong>
                </span>
            </div>
            <div class="col-md-3 form-group">
                <label class="control-label">Adjunto C</label>
                <input type="file" class="form-control"  id="adjunto_c" name="adjunto_c" value="{{old('adjunto_c')}}" accept=".jpg, .jpeg, .png, .pdf, .JPG, .JPEG, .PNG, .PDF">
                <span style="color: red;">
                    <strong>{{ $errors->first('adjunto_c') }}</strong>
                </span>
            </div>
            <div class="col-md-3 form-group">
                <label class="control-label">Adjunto D</label>
                <input type="file" class="form-control"  id="adjunto_d" name="adjunto_d" value="{{old('adjunto_d')}}" accept=".jpg, .jpeg, .png, .pdf, .JPG, .JPEG, .PNG, .PDF">
                <span style="color: red;">
                    <strong>{{ $errors->first('adjunto_d') }}</strong>
                </span>
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
        $(document).on('change','input[type="file"]',function(){
            var fileName = this.files[0].name;
            var fileSize = this.files[0].size;

            if(fileSize > 512000){
                this.value = '';
                Swal.fire({
                    title: 'La documentación adjuntada no puede exceder 512kb',
                    text: 'Intente nuevamente',
                    type: 'error',
                    showCancelButton: false,
                    showConfirmButton: false,
                    cancelButtonColor: '#d33',
                    cancelButtonText: 'Cancelar',
                    timer: 10000
                });
            }else{
                var ext = fileName.split('.').pop();
                switch (ext) {
                    case 'jpg':
                    case 'png':
                    case 'pdf':
                    case 'JPG':
                    case 'PNG':
                    case 'PDF':
                        break;
                    default:
                        this.value = '';
                        Swal.fire({
                            title: 'La documentación adjuntada debe poseer una extensión apropiada. Sólo se aceptan archivos jpg, png o pdf',
                            text: 'Intente nuevamente',
                            type: 'error',
                            showCancelButton: false,
                            showConfirmButton: false,
                            cancelButtonColor: '#d33',
                            cancelButtonText: 'Cancelar',
                            timer: 10000
                        });
                }
            }
        });
        $(document).ready(function () {
            $('#mac_address').mask('AA:AA:AA:AA:AA:AA', {
                'translation': {A: {pattern: /[0-9a-fA-F]/}},
            });
        });
    </script>
@endsection
