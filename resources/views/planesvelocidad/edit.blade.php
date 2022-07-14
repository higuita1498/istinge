@extends('layouts.app')
@section('content')
    <style>
    	.input-group-prepend .input-group-text {
    		background: #f9f9f9;
    		border-color: #dee4e6;
    		font-size: 0.9rem;
    	}
    	.nav-tabs .nav-link {
    		font-size: 1em;
    	}
    	.nav-tabs .nav-link.active, .nav-tabs .nav-item.show .nav-link {
    		background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
    		color: #fff!important;
    	}
    	.nav-pills .nav-link.active, .nav-pills .show > .nav-link {
    		color: #fff!important;
    		background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}!important;
    	}
    	.nav-pills .nav-link {
    		font-weight: 700!important;
    	}
    	.nav-pills .nav-link{
    		color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}!important;
    		background-color: #f9f9f9!important;
    		margin: 2px;
    		border: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
    		transition: 0.4s;
    	}
    	.nav-pills .nav-link:hover {
    		color: #fff!important;
    		background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}!important;
    	}
    	.select-group input.form-control{ width: 65%}
    	.select-group select.input-group-addon { width: 35%; }
    	.input-group-addon{
    		background: #f9f9f9;
    		border-color: #dee4e6;
    		font-size: .9em;
    		font-weight: bold;
    		color: #495057;
    		padding: 0 1% 0 2%;
    	}
    </style>

	<form method="POST" action="{{ route('planes-velocidad.update', $plan->id) }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-retencion" >
	    {{ csrf_field() }}
	    <input name="_method" type="hidden" value="PATCH">
	    
	    <ul class="nav nav-pills mb-5" id="pills-tab" role="tablist">
	        <li class="nav-item">
	            <a class="nav-link active" id="pills-basica-tab" data-toggle="pill" href="#pills-basica" role="tab" aria-controls="pills-basica" aria-selected="true">Configuración Básica</a>
	        </li>
	        <li class="nav-item">
	            <a class="nav-link" id="pills-avanzado-tab" data-toggle="pill" href="#pills-avanzado" role="tab" aria-controls="pills-avanzado" aria-selected="false">Configuración Avanzada</a>
	        </li>
			<li class="nav-item">
	            <a class="nav-link" id="pills-contabilidad-tab" data-toggle="pill" href="#pills-contabilidad" role="tab" aria-controls="pills-contabilidad" aria-selected="false">Contabilidad</a>
	        </li>
	    </ul>
	    
	    <div class="tab-content" id="pills-tabContent">
	        <div class="tab-pane fade show active" id="pills-basica" role="tabpanel" aria-labelledby="pills-basica-tab">
        	    <div class="row">
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Mikrotik Asociada <span class="text-danger">*</span></label>
        	            <select name="mikrotik" id="mikrotik" class="form-control selectpicker " title="Seleccione" data-live-search="true" data-size="5" required>
                        @foreach($mikrotiks as $mikrotik)
                            <option {{$plan->mikrotik==$mikrotik->id?'selected':''}} value="{{$mikrotik->id}}">{{$mikrotik->nombre}}</option>
                        @endforeach
                        </select>
        	            <span class="help-block error">
        	                <strong>{{ $errors->first('mikrotik') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Nombre <span class="text-danger">*</span></label>
        	            <input type="text" class="form-control"  id="name" name="name"  required="" value="{{$plan->name}}" maxlength="200">
        	            <span class="help-block error">
        	                <strong>{{ $errors->first('name') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Precio <span class="text-danger">*</span></label>
        	            <div class="input-group mb-2">
        	            	<input type="number" class="form-control"  id="price" name="price"  required="" value="{{$plan->price}}" maxlength="200" onkeypress="return event.charCode >= 48 && event.charCode <=57" min="0">
        	            	<div class="input-group-prepend">
        	            		<div class="input-group-text font-weight-bold">{{ Auth::user()->empresa()->moneda }}</div>
        	            	</div>
        	            </div>
        	            <span class="help-block error">
        	                <strong>{{ $errors->first('price') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Vel. de Descarga <span class="text-danger">*</span></label>
        	            <div class="input-group mb-2">
        	            	<input type="number" class="form-control"  id="download" name="download"  required="" value="{{substr($plan->download, 0, -1)}}" maxlength="200" min="0" onkeypress="return event.charCode >= 48 && event.charCode <=57">
        	            	<select class="input-group-addon" name="inicial_download">
        	            		<option value="k" {{ substr($plan->download, -1)=='k'?'selected':'' }}>Kbps</option>
        	            		<option value="M" {{ substr($plan->download, -1)=='M'?'selected':'' }}>Mbps</option>
        	            	</select>
        	            </div>
        	            <span class="help-block error">
        	                <strong>{{ $errors->first('download') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Vel. de Subida <span class="text-danger">*</span></label>
        	            <div class="input-group mb-2">
        	            	<input type="number" class="form-control"  id="upload" name="upload"  required="" value="{{substr($plan->upload, 0, -1)}}" maxlength="200" min="0" onkeypress="return event.charCode >= 48 && event.charCode <=57">
        	            	<select class="input-group-addon" name="inicial_upload">
        	            		<option value="k" {{ substr($plan->upload, -1)=='k'?'selected':'' }}>Kbps</option>
        	            		<option value="M" {{ substr($plan->upload, -1)=='M'?'selected':'' }}>Mbps</option>
        	            	</select>
        	            </div>
        	            <span class="help-block error">
        	                <strong>{{ $errors->first('upload') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Tipo <span class="text-danger">*</span></label>
        	            <select class="form-control selectpicker" name="type" id="type" required="" title="Seleccione" onchange="typeChange();">
        	                <option {{$plan->type==0?'selected':''}} value="0">Queue Simple</option>
        	                <option {{$plan->type==1?'selected':''}} value="1">PCQ</option>
          			</select>
        	            <span class="help-block error">
        	                <strong>{{ $errors->first('type') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group d-none" id="div_address">
        	            <label class="control-label">Address List <span class="text-danger">*</span></label>
        	            <div class="input-group">
        	                <input type="text" class="form-control" name="address_list" id="address_list">
        	            </div>
        	            <span class="help-block error">
        	                <strong>{{ $errors->first('address_list') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Servidor DHCP</label>
        	            <div class="input-group">
        	                <input type="text" class="form-control" name="dhcp_server" id="dhcp_server" value="{{ $plan->dhcp_server }}">
        	            </div>
        	            <span class="help-block error">
        	                <strong>{{ $errors->first('dhcp_server') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Tipo de Plan <span class="text-danger">*</span></label>
        	            <select class="form-control selectpicker" name="tipo_plan" id="tipo_plan" required="" title="Seleccione">
        	                <option {{$plan->tipo_plan==1?'selected':''}} value="1">Residencial</option>
        	                <option {{$plan->tipo_plan==2?'selected':''}} value="2">Corportativo</option>
          			    </select>
        	            <span class="help-block error">
        	                <strong>{{ $errors->first('tipo_plan') }}</strong>
        	            </span>
        	        </div>
        	   </div>
	        </div>
	        <div class="tab-pane fade" id="pills-avanzado" role="tabpanel" aria-labelledby="pills-avanzado-tab">
	            <div class="row">
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Burst limit subida</label>
        	            <div class="input-group mb-2">
        	            	<input type="number" class="form-control"  id="burst_limit_subida" name="burst_limit_subida"  value="{{substr($plan->burst_limit_subida, 0, -1)}}" maxlength="200" min="0" onkeypress="return event.charCode >= 48 && event.charCode <=57">
        	            	<select class="input-group-addon" name="inicial_burst_limit_subida">
								<option value="k" {{ substr($plan->burst_limit_subida, -1)=='k'?'selected':'' }}>Kbps</option>
        	            		<option value="M" {{ substr($plan->burst_limit_subida, -1)=='M'?'selected':'' }}>Mbps</option>
							</select>
        	            </div>

        	            <span class="help-block error">
        	                <strong>{{ $errors->first('burst_limit_subida') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Burst limit bajada</label>
        	            <div class="input-group mb-2">
        	            	<input type="number" class="form-control"  id="burst_limit_bajada" name="burst_limit_bajada"  value="{{substr($plan->burst_limit_bajada, 0, -1)}}" maxlength="200" min="0" onkeypress="return event.charCode >= 48 && event.charCode <=57">
        	            	<select class="input-group-addon" name="inicial_burst_limit_bajada">
								<option value="k" {{ substr($plan->burst_limit_bajada, -1)=='k'?'selected':'' }}>Kbps</option>
        	            		<option value="M" {{ substr($plan->burst_limit_bajada, -1)=='M'?'selected':'' }}>Mbps</option>
							</select>
        	            </div>

        	            <span class="help-block error">
        	                <strong>{{ $errors->first('burst_limit_bajada') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Burst threshold subida</label>
        	            <div class="input-group mb-2">
        	            	<input type="number" class="form-control"  id="burst_threshold_subida" name="burst_threshold_subida"  value="{{substr($plan->burst_threshold_subida, 0, -1)}}" maxlength="200" min="0" onkeypress="return event.charCode >= 48 && event.charCode <=57">
        	            	<select class="input-group-addon" name="inicial_burst_threshold_subida">
								<option value="k" {{ substr($plan->burst_threshold_subida, -1)=='k'?'selected':'' }}>Kbps</option>
        	            		<option value="M" {{ substr($plan->burst_threshold_subida, -1)=='M'?'selected':'' }}>Mbps</option>
							</select>
        	            </div>

        	            <span class="help-block error">
        	                <strong>{{ $errors->first('burst_threshold_subida') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Burst threshold bajada</label>
        	            <div class="input-group mb-2">
        	            	<input type="number" class="form-control"  id="burst_threshold_bajada" name="burst_threshold_bajada"  value="{{substr($plan->burst_threshold_bajada, 0, -1)}}" maxlength="200" min="0" onkeypress="return event.charCode >= 48 && event.charCode <=57">
        	            	<select class="input-group-addon" name="inicial_burst_threshold_bajada">
								<option value="k" {{ substr($plan->burst_threshold_bajada, -1)=='k'?'selected':'' }}>Kbps</option>
        	            		<option value="M" {{ substr($plan->burst_threshold_bajada, -1)=='M'?'selected':'' }}>Mbps</option>
							</select>
        	            </div>

        	            <span class="help-block error">
        	                <strong>{{ $errors->first('burst_threshold_bajada') }}</strong>
        	            </span>
        	        </div>

        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Limit at subida</label>
        	            <div class="input-group mb-2">
        	            	<input type="number" class="form-control"  id="limit_at_subida" name="limit_at_subida"  value="{{substr($plan->limit_at_subida, 0, -1)}}" maxlength="200" min="0" onkeypress="return event.charCode >= 48 && event.charCode <=57">
        	            	<select class="input-group-addon" name="inicial_limit_at_subida">
								<option value="k" {{ substr($plan->limit_at_subida, -1)=='k'?'selected':'' }}>Kbps</option>
        	            		<option value="M" {{ substr($plan->limit_at_subida, -1)=='M'?'selected':'' }}>Mbps</option>
							</select>
        	            </div>

        	            <span class="help-block error">
        	                <strong>{{ $errors->first('limit_at_subida') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Limit at bajada</label>
        	            <div class="input-group mb-2">
        	            	<input type="number" class="form-control"  id="limit_at_bajada" name="limit_at_bajada"  value="{{substr($plan->limit_at_bajada, 0, -1)}}" maxlength="200" min="0" onkeypress="return event.charCode >= 48 && event.charCode <=57">
        	            	<select class="input-group-addon" name="inicial_limit_at_bajada">
								<option value="k" {{ substr($plan->limit_at_bajada, -1)=='k'?'selected':'' }}>Kbps</option>
        	            		<option value="M" {{ substr($plan->limit_at_bajada, -1)=='M'?'selected':'' }}>Mbps</option>
							</select>
        	            </div>

        	            <span class="help-block error">
        	                <strong>{{ $errors->first('limit_at_bajada') }}</strong>
        	            </span>
        	        </div>

        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Burst time subida</label>
        	            <div class="input-group mb-2">
        	            	<input type="number" class="form-control"  id="burst_time_subida" name="burst_time_subida"  value="{{$plan->burst_time_subida}}" maxlength="200" min="0" onkeypress="return event.charCode >= 48 && event.charCode <=57">
        	            	<div class="input-group-prepend">
        	            		<div class="input-group-text font-weight-bold">Seg</div>
        	            	</div>
        	            </div>

        	            <span class="help-block error">
        	                <strong>{{ $errors->first('burst_time_subida') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Burst time bajada</label>
        	            <div class="input-group mb-2">
        	            	<input type="number" class="form-control"  id="burst_time_bajada" name="burst_time_bajada"  value="{{$plan->burst_time_bajada}}" maxlength="200" min="0" onkeypress="return event.charCode >= 48 && event.charCode <=57">
        	            	<div class="input-group-prepend">
        	            		<div class="input-group-text font-weight-bold">Seg</div>
        	            	</div>
        	            </div>

        	            <span class="help-block error">
        	                <strong>{{ $errors->first('burst_time_bajada') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Queue Type de subida</label>
        	            <input type="text" class="form-control"  id="queue_type_subida" name="queue_type_subida"  value="{{$plan->queue_type_subida}}" maxlength="200">

        	            <span class="help-block error">
        	                <strong>{{ $errors->first('queue_type_subida') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Queue Type de bajada</label>
        	            <input type="text" class="form-control"  id="queue_type_bajada" name="queue_type_bajada"  value="{{$plan->queue_type_bajada}}" maxlength="200">

        	            <span class="help-block error">
        	                <strong>{{ $errors->first('queue_type_bajada') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Parent</label>
        	            <input type="text" class="form-control"  id="parenta" name="parenta"  value="{{$plan->parenta}}" maxlength="200">
        	            <span class="help-block error">
        	                <strong>{{ $errors->first('parenta') }}</strong>
        	            </span>
        	        </div>
        	        <div class="col-md-3 form-group">
        	            <label class="control-label">Prioridad</label>
        	            <input type="number" class="form-control"  id="prioridad" name="prioridad"  value="{{$plan->prioridad}}" min="1" max="8" onkeypress="return event.charCode >= 48 && event.charCode <=57">
        	            <span class="help-block error">
        	                <strong>{{ $errors->first('prioridad') }}</strong>
        	            </span>
        	        </div>
        	   </div>
	        </div>
			<div class="tab-pane fade" id="pills-contabilidad" role="tabpanel" aria-labelledby="pills-contabilidad-tab">
				<div class="row">
					<div class="form-group col-md-3">
						<label class="control-label">Inventario <span class="text-danger">*</span></label>
						<select class="form-control selectpicker" data-live-search="true" data-size="5" name="inventario" id="inventario"  title="Seleccione">
							@foreach($cuentas as $cuenta)
								<option @foreach($cuentasInventario  as $ci) @if($ci->cuenta_id == $cuenta->id && $ci->tipo == 1){{'selected'}}@endif @endforeach 
									value="{{$cuenta->id}}">{{$cuenta->nombre}} - {{$cuenta->codigo}}
								</option>
							@endforeach
						</select>
					<span class="help-block error">
						<strong>{{ $errors->first('inventario') }}</strong>
					</span>
					</div>
					<div class="form-group col-md-3">
						<label class="control-label">Costo <span class="text-danger">*</span></label>
						<select class="form-control selectpicker" data-live-search="true" data-size="5" name="costo" id="costo"  title="Seleccione">
							@foreach($cuentas as $cuenta)
							<option @foreach($cuentasInventario  as $ci) @if($ci->cuenta_id == $cuenta->id && $ci->tipo == 2){{'selected'}}@endif @endforeach 
								value="{{$cuenta->id}}">{{$cuenta->nombre}} - {{$cuenta->codigo}}
							</option>				
							@endforeach
					</select>
					<span class="help-block error">
						<strong>{{ $errors->first('costo') }}</strong>
					</span>
					</div>
					<div class="form-group col-md-3">
						<label class="control-label">Venta <span class="text-danger">*</span></label>
						<select class="form-control selectpicker" data-live-search="true" data-size="5" name="venta" id="venta"  title="Seleccione">
							@foreach($cuentas as $cuenta)
							<option @foreach($cuentasInventario  as $ci) @if($ci->cuenta_id == $cuenta->id && $ci->tipo == 3){{'selected'}}@endif @endforeach 
								value="{{$cuenta->id}}">{{$cuenta->nombre}} - {{$cuenta->codigo}}
							</option>
							@endforeach
					</select>
					<span class="help-block error">
						<strong>{{ $errors->first('venta') }}</strong>
					</span>
					</div>
					<div class="form-group col-md-3">
						<label class="control-label">Devolución <span class="text-danger">*</span></label>
						<select class="form-control selectpicker" data-live-search="true" data-size="5" name="devolucion" id="devolucion"  title="Seleccione">
							@foreach($cuentas as $cuenta)
							<option @foreach($cuentasInventario  as $ci) @if($ci->cuenta_id == $cuenta->id && $ci->tipo == 4){{'selected'}}@endif @endforeach 
								value="{{$cuenta->id}}">{{$cuenta->nombre}} - {{$cuenta->codigo}}
							</option>
							@endforeach
					</select>
					<span class="help-block error">
						<strong>{{ $errors->first('devolucion') }}</strong>
					</span>
					</div>
				</div>
				<div class="row">
					<div class="form-group col-md-5">
						
					</div>
					<div class="form-group col-md-7 ">
						<div class="row">
							<div class="col-md-6 monetario">
							</div>
							<div class="col-md-6" style="padding-top: 1%;padding-left: 0;"><button type="button" class="btn btn-link " style="padding-left: 0;" onclick="agregar_cuenta();" @if(json_encode($cuentas)=='[]') title="Usted no tiene cuentas registradas" @endif><i class="fas fa-plus"></i> Agregar otras cuentas contables</button></div>
						</div>
						<div class="row" id="lista_cuentas">
							<div class="col-md-12">
								<table id="table_cuentas">
									<tbody>
										@php $key2= 0; @endphp
										@foreach($cuentasInventario as $cuenta)
										@if($cuenta->tipo == null)
										<tr id="tr_cuenta_{{($key2+1)}}">
											<td width="20%"><label class="control-label">Cuenta contable <span class="text-danger">* {{$cuenta->nombreProductoServicio()}}</span></label></td>
											<td width="30%">
												<select class="form-control form-control-sm selectpicker no-padding"  title="Seleccione" name="cuentacontable[]" id="cuentacontable{{($key2+1)}}" required="">							        		
													@foreach($cuentas as $c) 
													<option value="{{$c->id}}" {{$cuenta->cuenta_id ==$c->id ? 'selected':''}}  >{{$c->nombre}} - {{$c->codigo}}</option>
													@endforeach
												</select>
												<input type="hidden" name="idcuenta{{($key2)}}" value="{{$cuenta->id}}">
											</td>
											<td width="5%"><button type="button" class="btn btn-link" onclick="eliminarCuenta('tr_cuenta_{{($key2+1)}}');">X</button></td>
										</tr>
										@endif
										@php $key2++; @endphp
										@endforeach

									</tbody>
								</table>
							</div>
						</div>

					</div>
				</div>

				<div class="row">
					<div class="form-group col-md-4">
						<label class="control-label">¿Producto con auto retención?</label>
					  <div class="row">
						  <div class="col-sm-6">
						  <div class="form-radio">
							  <label class="form-check-label">
							  <input type="radio" class="form-check-input" name="tipo_autoretencion" id="tipo_autoretencion1" @if($inventario->type_autoretencion == 1) checked="" @endif value="1" > No
							  <i class="input-helper"></i></label>
						  </div>
					  </div>
					  <div class="col-sm-6">
						  <div class="form-radio">
							  <label class="form-check-label">
							  <input type="radio" class="form-check-input" name="tipo_autoretencion" id="tipo_autoretencion2" value="2" @if($inventario->type_autoretencion == 2) checked="" @endif> Si
							  <i class="input-helper"></i></label>
						  </div>
					  </div>
					  </div>
					  <span class="help-block error">
						  <strong>{{ $errors->first('tipo_autoretencion') }}</strong>
					  </span>
				  </div>
				  <div class="form-group col-md-3">
					<div class="cls-autoretencion @if($inventario->type_autoretencion == 1) d-none @endif">
						<label class="control-label">Cuenta de auto retención <span class="text-danger">*</span></label>
						<select class="form-control selectpicker" data-live-search="true" data-size="5" name="autoretencion" id="autoretencion"  title="Seleccione" required="">
							@foreach($autoRetenciones as $cuenta)
								<option @foreach($cuentasInventario  as $ci) @if($ci->cuenta_id == $cuenta->id && $ci->tipo == 5){{'selected'}}@endif @endforeach 
								value="{{$cuenta->id}}">{{$cuenta->nombre}}</option>
							@endforeach
					</select>
					<span class="help-block error">
						<strong>{{ $errors->first('autoretencion') }}</strong>
					</span>
					</div>
				  </div>
				  <div class="form-group col-md-5">
				  </div>
				</div>
	        </div>
	    </div>
	   <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
	   <hr>
	   <div class="row" >
	       <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
	           <a href="{{route('planes-velocidad.index')}}" class="btn btn-outline-secondary">Cancelar</a>
	           <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
	       </div>
	   </div>
    </form>
@endsection

@section('scripts')
<script type="text/javascript" src="{{asset('lowerScripts/inventario/inventario.js')}}"></script>
<script type="text/javascript">
    function typeChange(){
        if(document.getElementById("type").value == 1){
            document.getElementById("div_address").classList.remove('d-none');
            document.getElementById("address_list").setAttribute('required', true);
        }else{
            document.getElementById("div_address").classList.add('d-none');
            document.getElementById("address_list").removeAttribute('required');
        }
        document.getElementById("address_list").value = '';
    }
</script>
@endsection