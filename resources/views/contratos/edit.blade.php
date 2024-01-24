@extends('layouts.app')

@section('style')
    <style>
        .bg-th{
            background: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}} !important;
            border-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}} !important;
            color: #fff !important;
        }
        .table .thead-light th {
            color: #fff!important;
            background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}!important;
            border-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}!important;
        }
        .nav-tabs .nav-link {
            font-size: 1em;
        }
        .nav-tabs .nav-link.active, .nav-tabs .nav-item.show .nav-link {
            background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
            color: #fff!important;
            box-shadow: 2px 2px 10px #797979;
        }
        .nav-pills .nav-link.active, .nav-pills .show > .nav-link {
            color: #fff!important;
            background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}!important;
            box-shadow: 2px 2px 10px #797979;
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
            box-shadow: 2px 2px 10px #797979;
        }
        table.dataTable td.dataTables_empty, table.dataTable th.dataTables_empty {
            text-align: center;
            color: red;
            font-weight: 900;
        }
        .card-adj:hover{
            box-shadow: 2px 2px 10px #797979;
        }
        .btn.btn-icons {
            border-radius: 50%;
        }
        .readonly{ border: 0 !important; background-color: #f9f9f9 !important; }
        .dropdown-header > span{ font-weight: 500; }
        .input-group-prepend .input-group-text {
            background: #f9f9f9;
            border-color: #dee4e6;
            font-size: 0.9rem;
        }
    </style>
@endsection

@section('content')
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

    <form method="POST" action="{{ route('contratos.update', $contrato->id ) }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-contrato" enctype="multipart/form-data">
        @csrf
        <input name="_method" type="hidden" value="PATCH">
        <input name="opciones_dian" id="opciones_dian" type="hidden" value="{{$contrato->opciones_dian}}">
        <input name="contrato_id" id="contrato_id" type="hidden" value="{{$contrato->id}}">

        <div class="row card-description p-0">
            <div class="col-md-4 form-group">
                <label class="control-label font-weight-bold">Nombre del Cliente</label>
                <input type="text" class="form-control readonly"  id="nombre" name="nombre"  required="" value="{{$contrato->nombre}} {{$contrato->apellido1}} {{$contrato->apellido2}}" maxlength="200" readonly="">
                <span class="help-block error">
                    <strong>{{ $errors->first('nombre') }}</strong>
                </span>
            </div>
            <div class="col-md-4 form-group">
                <label class="control-label font-weight-bold">Identificación</label>
                <input type="text" class="form-control readonly" id="ident" name="ident" readonly="" value="{{$contrato->nit}}" maxlength="20">
                <span class="help-block error">
                    <strong>{{ $errors->first('identificacion') }}</strong>
                </span>
            </div>
            <div class="col-md-4 form-group">
                <label class="control-label font-weight-bold">Nro.Contrato</label>
                <input type="text" class="form-control" id="nro" name="nro"  value="{{$contrato->nro}}" maxlength="200" onkeypress="return event.charCode >= 48 && event.charCode <=57">
                <span class="help-block error">
                    <strong>{{ $errors->first('nro') }}</strong>
                </span>
            </div>

            <div class="col-md-12">
                <ul class="nav nav-pills" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info" role="tab" aria-controls="info" aria-selected="false">INFORMACIÓN PRINCIPAL</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="internet-tab" data-toggle="tab" href="#internet" role="tab" aria-controls="internet" aria-selected="false">SERVICIO DE INTERNET</a>
                    </li>
                    @if(count($servicios)>0)
                    <li class="nav-item">
                        <a class="nav-link" id="television-tab" data-toggle="tab" href="#television" role="tab" aria-controls="television" aria-selected="false">SERVICIO DE TELEVISIÓN</a>
                    </li>
                    @endif
                    <li class="nav-item">
                        <a class="nav-link" id="television-tab" data-toggle="tab" href="#otrositems" role="tab" aria-controls="television" aria-selected="false">OTROS ITEMS A FACTURAR</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="adicionales-tab" data-toggle="tab" href="#adicionales" role="tab" aria-controls="adicionales" aria-selected="false">OPCIONES ADICIONALES</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="adjuntos-tab" data-toggle="tab" href="#adjuntos" role="tab" aria-controls="adjuntos" aria-selected="false">ARCHIVOS ADJUNTOS</a>
                    </li>
                </ul>
                <hr style="border-top: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}; margin: .5rem 0rem 2rem;">
                <div class="tab-content fact-table" id="myTabContent">
                    <div class="tab-pane fade show active" id="info" role="tabpanel" aria-labelledby="info-tab">
                        <div class="row">
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
                                <label class="control-label">Tipo Factura <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select class="form-control selectpicker" name="facturacion" id="facturacion" required="" title="Seleccione" data-live-search="true" data-size="5" onchange="validarTipo()">
                                        <option value="1" {{$contrato->facturacion == 1 ? 'selected' : ''}}>Facturación Estándar</option>
                                        <option value="3" {{$contrato->facturacion == 3 ? 'selected' : ''}} >Facturación Electrónica</option>
                                    </select>
                                </div>
                                <span class="help-block error">
                                    <strong>{{ $errors->first('facturacion') }}</strong>
                                </span>
                            </div>
                            <div class="col-md-4 form-group" id="div_facturacion">
                                <label class="control-label">Facturación Individual <span class="text-danger">*</span> <a><i data-tippy-content="Indicar si desea crear una factura general con los otros contratos o crear individualmente" class="icono far fa-question-circle"></i></a></label>
                                <div class="input-group">
                                    <select class="form-control selectpicker" name="factura_individual" id="factura_individual" required="" title="Seleccione" data-live-search="true" data-size="5">
                                        <option value="1" {{$contrato->factura_individual == 1 ? 'selected' : ''}}>Si</option>
                                        <option value="0" {{$contrato->factura_individual == 0 ? 'selected' : ''}}>No</option>
                                    </select>
                                </div>
                                <span class="help-block error">
                                    <strong>{{ $errors->first('factura_individual') }}</strong>
                                </span>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="control-label">Fecha de Suspensión <a><i data-tippy-content="Fecha de suspensión personalizada, distinta a la asociada al grupo de corte" class="icono far fa-question-circle"></i></a></label>
                                <input type="number" class="form-control"  id="fecha_suspension" value="{{$contrato->fecha_suspension}}" name="fecha_suspension" min="1" max="30">

                                <span class="help-block error">
                                    <strong>{{ $errors->first('fecha_suspension') }}</strong>
                                </span>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="control-label">¿Aplicar contrato de permanencia? <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker" id="contrato_permanencia" name="contrato_permanencia"  required="" title="Seleccione" data-live-search="true" data-size="5">
                                    <option value="1" {{$contrato->contrato_permanencia == '1' ? 'selected':''}}>SI</option>
                                    <option value="0" {{$contrato->contrato_permanencia == '0' ? 'selected':''}}>NO</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group {{old('contrato_permanencia')==1 || $contrato->contrato_permanencia==1?'':'d-none'}}" id="div_meses">
                                <label class="control-label">Meses del contrato de permanencia <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <select class="form-control selectpicker" id="contrato_permanencia_meses" name="contrato_permanencia_meses"  required="" title="Seleccione" data-live-search="true" data-size="5">
                                        <option value="3" {{$contrato->contrato_permanencia_meses==3?'selected':''}}>3 meses</option>
                                        <option value="6" {{$contrato->contrato_permanencia_meses==6?'selected':''}}>6 meses</option>
                                        <option value="9" {{$contrato->contrato_permanencia_meses==9?'selected':''}}>9 meses</option>
                                        <option value="12" {{$contrato->contrato_permanencia_meses==12?'selected':''}}>12 meses</option>
                                    </select>
                                </div>
                                <span class="help-block error">
                                    <strong>{{ $errors->first('contrato_permanencia_meses') }}</strong>
                                </span>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="control-label">Coordenadas GPS <a><i data-tippy-content="Arrastre el pin para indicar las coordenadas deseadas." class="icono far fa-question-circle"></i></a></label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="us2-lat" name="latitude" value="{{ $contrato->latitude }}">
                                    <input type="text" class="form-control" id="us2-lon" name="longitude" value="{{ $contrato->longitude }}">
                                    <div class="input-group-prepend">
                                        <button class="btn btn-outline-success btn-sm" type="button" data-toggle="modal" data-target="#modal-gps" style="border-radius: 0 5px 5px 0;">
                                            <i class="fas fa-map-marked-alt" style="margin: 2px;"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4 form-group" id="div_direccion">
                                <label class="control-label">Dirección de Instalación</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" name="address_street" id="address_street" value="{{ $contrato->address_street }}">
                                    <span class="help-block error">
                                        <strong>{{ $errors->first('address_street') }}</strong>
                                    </span>
                                </div>
                            </div>

                            @if(Auth::user()->empresa()->oficina)
                            <div class="form-group col-md-4">
                                <label class="control-label">Oficina Asociada <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker" name="oficina" id="oficina" required="" title="Seleccione" data-live-search="true" data-size="5">
                                    @foreach($oficinas as $oficina)
                                    <option value="{{$oficina->id}}" {{ $oficina->id == auth()->user()->oficina ? 'selected' : '' }}>{{$oficina->nombre}}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif

                            <div class="col-md-4 form-group d-none" id="div_opciones">
                                <label class="control-label">{{$contrato->opciones_dian == 0 ? 'Habilitar' : 'Deshabilitar'}} opciones de facturacion electórnica?</label>
                                <div class="input-group">
                                    <a href="#" onclick="hab_desha()" class="btn btn-outline-primary" style="width:100%;">{{$contrato->opciones_dian == 0 ? 'Habilitar' : 'Deshabilitar'}}</a>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="tab-pane fade" id="internet" role="tabpanel" aria-labelledby="internet-tab">
                        <div class="row">
                            <input type="hidden" id="interfaz_user" value="{{$contrato->interfaz}}">
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
                                <input type="hidden" name="amarre_mac" id="amarre_mac">
                            </div>
                            <div class="col-md-4 form-group {{$contrato->conexion==2?'':'d-none'}}" id="div_dhcp">
                                <label class="control-label">Simple Queue <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker" id="simple_queue" name="simple_queue"  required="" title="Seleccione" data-live-search="true" data-size="5">
                                    <option value="dinamica" {{$contrato->simple_queue == 'dinamica' ? 'selected':''}}>Dinámica</option>
                                    <option value="estatica" {{$contrato->simple_queue == 'estatica' ? 'selected':''}}>Estática</option>
                                </select>
                            </div>
                            <div class="col-md-4 form-group {{$contrato->conexion==3?'':'d-none'}}" id="div_interfaz">
                                <label class="control-label">Interfaz de Conexión <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="hidden" id="interfaz_bd" value="{{ $contrato->interfaz }}">
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
                                    <input type="hidden" id="segmento_bd" value="{{ $contrato->local_address }}">
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
                                        <button class="btn btn-outline-success btn-sm" type="button" id="searchIP" style="border-radius: 0 5px 5px 0;"><i class="fa fa-search" style="margin: 2px;"></i></button>
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

                            <div class="col-md-4 form-group  {{$contrato->conexion==1?'':'d-none'}}" id="local_adress" {{$contrato->local_adress_pppoe ? '': 'd-none'}}>
                                <label class="control-label">Dirección IP (Local Address)</label>
                                <input type="text" class="form-control" name="direccion_local_address" id="local_address" value="{{$contrato->local_adress_pppoe}}" onkeypress="return event.charCode >= 48 && event.charCode <=57 || event.charCode==46 || event.charCode==47">
                            </div>

                            <div class="col-md-4 form-group  {{$contrato->conexion==1?'':'d-none'}}" id="div_profile" {{$contrato->profile ? '':'d-none'}}>
                                <label class="control-label">Profile</label>
                                <input type="text" class="form-control" name="profile" id="profile" value="{{ $contrato->profile}}">
                            </div>


                            <div class="col-md-4 form-group {{$contrato->conexion==1 ? '' : 'd-none'}}" id="div_usuario" >
                                <label class="control-label">Usuario <span class="text-danger">*</span></label>
                                  <div class="input-group">
                                    <input type="text" class="form-control" name="usuario" id="usuario" value="{{ $contrato->usuario}}">
                                    <span class="help-block error">
                                        <strong>{{ $errors->first('usuario') }}</strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4 form-group {{$contrato->conexion==1 ? '' : 'd-none'}}" id="div_password">
                                <label class="control-label">Contraseña <span class="text-danger">*</span></label>
                                  <div class="input-group">
                                    <input type="text" class="form-control" name="password" id="password" value="{{ $contrato->password}}">
                                    <span class="help-block error">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-4 form-group">
                                <label class="control-label">Tipo de Tecnología <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker" id="tecnologia" name="tecnologia" required="" title="Seleccione">
                                    <option value="1" {{$contrato->tecnologia==1?'selected':''}}>Fibra</option>
                                    <option value="2" {{$contrato->tecnologia==2?'selected':''}}>Inalámbrico</option>
                                </select>
                                <span class="help-block error">
                                    <strong>{{ $errors->first('tecnologia') }}</strong>
                                </span>
                            </div>

                            <div class="col-md-4 form-group {{$contrato->tecnologia==2?'':'d-none'}}" id="div_ap">
                                <label class="control-label">Access Point Asociado <span class="text-danger">*</span></label>
                                <select class="form-control selectpicker" id="ap" name="ap" title="Seleccione" data-live-search="true" data-size="5">
                                    @foreach($nodos as $nodo)
                                    <optgroup label="NODO {{$nodo->nombre}}">
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
                                <label class="control-label">Dirección MAC</label>
                                <div class="input-group">
                                    <input type="text" class="form-control mac_address" name="mac_address" id="mac_address" value="{{ $contrato->mac_address }}">
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-success btn-sm" type="button" id="searchMAC" style="border-radius: 0 5px 5px 0;"><i class="fa fa-search" style="margin: 2px;"></i></button>
                                    </div>
                                    <span class="help-block error">
                                        <strong>{{ $errors->first('mac_address') }}</strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="control-label">Puerto de Conexión</label>
                                <div class="input-group">
                                    <select class="form-control selectpicker" name="puerto_conexion" id="puerto_conexion" required="" title="Seleccione" data-live-search="true" data-size="5">
                                        <option value="">Ninguno</option>
                                        @foreach($puertos as $puerto)
                                            <option value="{{$puerto->id}}" {{$puerto->id == $contrato->puerto_conexion? 'selected':''}}>{{$puerto->nombre}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <span class="help-block error">
                                    <strong>{{ $errors->first('puerto_conexion') }}</strong>
                                </span>
                            </div>
                            <div class="col-md-3 form-group d-none">
                                <label class="control-label">Marca Router</label>
                                <select class="form-control selectpicker" id="marca_router" name="marca_router" required="" value="{{ $contrato->marca_router }}" title="Seleccione">
                                    @foreach($marcas as $marca)
                                    <option value="{{$marca->id}}" {{ $marca->id == $contrato->marca_router ? 'selected' : '' }}>{{$marca->nombre}}</option>
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
                            <div class="col-md-4 form-group">
                                <label class="control-label">Serial ONU</label>
                                  <div class="input-group">
                                    <input type="text" class="form-control" name="serial_onu" id="serial_onu" value="{{$contrato->serial_onu}}">
                                    <span class="help-block error">
                                        <strong>{{ $errors->first('serial_onu') }}</strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="control-label">Línea</label>
                                  <div class="input-group">
                                    <input type="text" class="form-control" name="linea" id="linea" value="{{$contrato->linea}}">
                                    <span class="help-block error">
                                        <strong>{{ $errors->first('linea') }}</strong>
                                    </span>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <label class="control-label">¿Agregar iva al servicio de internet?  <a><i
                                    data-tippy-content="Decida si la factura que genere este contrato llevará iva"
                                    class="icono far fa-question-circle"></i></a></label>
                              <div class="row">
                                  <div class="col-sm-6">
                                  <div class="form-radio">
                                      <label class="form-check-label">
                                      <input type="radio" class="form-check-input" name="iva_factura" id="iva_factura1" value="1" {{$contrato->iva_factura == 1 ? 'checked' : ''}}> Si
                                      <i class="input-helper"></i><i class="input-helper"></i></label>
                                  </div>
                              </div>
                              <div class="col-sm-6">
                                  <div class="form-radio">
                                      <label class="form-check-label">
                                      <input type="radio" class="form-check-input" name="iva_factura" id="iva_factura2" value="0" {{$contrato->iva_factura == 0 ? 'checked' : ''}}> No
                                      <i class="input-helper"></i><i class="input-helper"></i></label>
                                  </div>
                              </div>
                              </div>
                              <span class="help-block error">
                                  <strong></strong>
                              </span>
                          </div>
                        </div>
                    </div>
                    @if(count($servicios)>0)
                    <div class="tab-pane fade" id="television" role="tabpanel" aria-labelledby="television-tab">
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label class="control-label">Servicio de Televisión</label>
                                <select class="form-control selectpicker" name="servicio_tv" id="servicio_tv" title="Seleccione" data-live-search="true" data-size="5">
                                    <option value="">NINGUNO</option>
                                    @foreach($servicios as $servicio)
                                        <option value="{{$servicio->id}}" {{ $servicio->id==$contrato->servicio_tv?'selected':'' }}>{{$servicio->producto}} - ({{ Auth::user()->empresa()->moneda }} {{ App\Funcion::Parsear($servicio->precio)}})</option>
                                    @endforeach
                                </select>
                                <span style="color: red;">
                                    <strong>{{ $errors->first('servicio_tv') }}</strong>
                                </span>
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="tab-pane fade" id="otrositems" role="tabpanel" aria-labelledby="otrositems-tab">
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label class="control-label">Otros ítems</label>
                                <select class="form-control selectpicker" name="servicio_otro" id="servicio_otro" title="Seleccione" data-live-search="true" data-size="5">
                                    <option value="">NINGUNO</option>
                                    @foreach($serviciosOtros as $servicioOtro)
                                        <option value="{{$servicioOtro->id}}" {{ $servicioOtro->id==$contrato->servicio_otro?'selected':'' }}>{{$servicioOtro->producto}} - ({{ Auth::user()->empresa()->moneda }} {{ App\Funcion::Parsear($servicioOtro->precio)}})</option>
                                    @endforeach
                                </select>
                                <span style="color: red;">
                                    <strong>{{ $errors->first('servicio_otro') }}</strong>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="adjuntos" role="tabpanel" aria-labelledby="adjuntos-tab">
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label class="control-label">Referencia A</label>
                                <input type="text" class="form-control" id="referencia_a" name="referencia_a" value="{{$contrato->referencia_a}}">
                                <span style="color: red;">
                                    <strong>{{ $errors->first('referencia_a') }}</strong>
                                </span>
                            </div>
                            <div class="col-md-3 form-group">
                                <label class="control-label">Referencia B</label>
                                <input type="text" class="form-control" id="referencia_b" name="referencia_b" value="{{$contrato->referencia_b}}">
                                <span style="color: red;">
                                    <strong>{{ $errors->first('referencia_b') }}</strong>
                                </span>
                            </div>
                            <div class="col-md-3 form-group">
                                <label class="control-label">Referencia C</label>
                                <input type="text" class="form-control" id="referencia_c" name="referencia_c" value="{{$contrato->referencia_c}}">
                                <span style="color: red;">
                                    <strong>{{ $errors->first('referencia_c') }}</strong>
                                </span>
                            </div>
                            <div class="col-md-3 form-group">
                                <label class="control-label">Referencia D</label>
                                <input type="text" class="form-control" id="referencia_d" name="referencia_d" value="{{$contrato->referencia_d}}">
                                <span style="color: red;">
                                    <strong>{{ $errors->first('referencia_d') }}</strong>
                                </span>
                            </div>

                            <div class="col-md-3 form-group">
                                <label class="control-label">Adjunto A</label>
                                <input type="file" class="form-control"  id="adjunto_a" name="adjunto_a" value="{{$contrato->adjunto_a}}" accept=".jpg, .jpeg, .png, .pdf, .JPG, .JPEG, .PNG, .PDF">
                                <span style="color: red;">
                                    <strong>{{ $errors->first('adjunto_a') }}</strong>
                                </span>
                            </div>
                            <div class="col-md-3 form-group">
                                <label class="control-label">Adjunto B</label>
                                <input type="file" class="form-control"  id="adjunto_b" name="adjunto_b" value="{{$contrato->adjunto_b}}" accept=".jpg, .jpeg, .png, .pdf, .JPG, .JPEG, .PNG, .PDF">
                                <span style="color: red;">
                                    <strong>{{ $errors->first('adjunto_b') }}</strong>
                                </span>
                            </div>
                            <div class="col-md-3 form-group">
                                <label class="control-label">Adjunto C</label>
                                <input type="file" class="form-control"  id="adjunto_c" name="adjunto_c" value="{{$contrato->adjunto_c}}" accept=".jpg, .jpeg, .png, .pdf, .JPG, .JPEG, .PNG, .PDF">
                                <span style="color: red;">
                                    <strong>{{ $errors->first('adjunto_c') }}</strong>
                                </span>
                            </div>
                            <div class="col-md-3 form-group">
                                <label class="control-label">Adjunto D</label>
                                <input type="file" class="form-control"  id="adjunto_d" name="adjunto_d" value="{{$contrato->adjunto_d}}" accept=".jpg, .jpeg, .png, .pdf, .JPG, .JPEG, .PNG, .PDF">
                                <span style="color: red;">
                                    <strong>{{ $errors->first('adjunto_d') }}</strong>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="adicionales" role="tabpanel" aria-labelledby="adicionales-tab">
                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label class="control-label">Descuento <a><i data-tippy-content="El descuento que se indique, se reflejará en la facturación recurrente del contrato" class="icono far fa-question-circle"></i></a></label>
                                <div class="input-group mb-2">
                                    <input type="number" class="form-control"  id="descuento" name="descuento"  required="" value="{{$contrato->descuento}}" onkeypress="return event.charCode >= 48 && event.charCode <=57" min="0" max="100">
                                    <div class="input-group-prepend">
                                        <div class="input-group-text font-weight-bold"><i class="fas fa-percentage"></i></div>
                                    </div>
                                </div>
                                <span style="color: red;">
                                    <strong>{{ $errors->first('descuento') }}</strong>
                                </span>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="control-label">Vendedor <a><i data-tippy-content="Seleccione el vendedor del contrato" class="icono far fa-question-circle"></i></a></label>
                                <div class="input-group mb-2">
                                    <select class="form-control selectpicker" name="vendedor" id="vendedor" title="Seleccione" data-live-search="true" data-size="5">
                                        @foreach($vendedores as $vendedor)
                                        <option value="{{$vendedor->id}}" {{$contrato->vendedor==$vendedor->id?'selected':''}}>{{$vendedor->nombre}}</option>
                                        @endforeach
                                    </select>
                                    <span style="color: red;">
                                        <strong>{{ $errors->first('vendedor') }}</strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="control-label">Canal de Venta <a><i data-tippy-content="Seleccione el canal de venta del contrato" class="icono far fa-question-circle"></i></a></label>
                                <div class="input-group mb-2">
                                    <select class="form-control selectpicker" name="canal" id="canal" title="Seleccione" data-live-search="true" data-size="5" required>
                                        @foreach($canales as $canal)
                                        <option value="{{$canal->id}}" {{$contrato->canal==$canal->id?'selected':''}}>{{$canal->nombre}}</option>
                                        @endforeach
                                    </select>
                                    <span style="color: red;">
                                        <strong>{{ $errors->first('canal') }}</strong>
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-4 form-group">
                                <label class="control-label">¿Cobro de Reconexión?</label>
                                <div class="input-group mb-2">
                                    <select class="form-control selectpicker" name="reconexion" id="reconexion" title="Seleccione">
                                        <option value="1" {{ $contrato->costo_reconexion > 0 ? 'selected':'' }}>Si</option>
                                        <option value="0" {{ $contrato->costo_reconexion == 0 ? 'selected':'' }}>No</option>
                                    </select>
                                    <span style="color: red;">
                                        <strong>{{ $errors->first('reconexion') }}</strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4 form-group {{ $contrato->costo_reconexion > 0 ? '':'d-none' }}" id="div_reconexion">
                                <label class="control-label">Monto de Reconexión</label>
                                <div class="input-group mb-2">
                                    <input type="number" class="form-control" id="costo_reconexion" name="costo_reconexion" onkeypress="return event.charCode >= 48 && event.charCode <=57" min="0" value="{{ $contrato->costo_reconexion }}">
                                    <span style="color: red;">
                                        <strong>{{ $errors->first('costo_reconexion') }}</strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="control-label">Tipo de Contrato</label>
                                <div class="input-group mb-2">
                                    <select class="form-control selectpicker" name="tipo_contrato" id="tipo_contrato" title="Seleccione">
                                        <option value="instalacion" {{ $contrato->tipo_contrato == 'instalacion' || $contrato->tipo_contrato == null ? 'selected':'' }}>Instalación</option>
                                        <option value="reconexion" {{ $contrato->tipo_contrato == 'reconexion' ? 'selected':'' }}>Reconexión</option>
                                        <option value="cambio titular del servicio" {{ $contrato->tipo_contrato == 'cambio titular del servicio' ? 'selected':'' }}>Cambio titular del servicio</option>
                                    </select>
                                    <span style="color: red;">
                                        <strong>{{ $errors->first('tipo_contrato') }}</strong>
                                    </span>
                                </div>
                            </div>

                            <div class="form-group col-md-4">
                                <label class="control-label">¿Agregar fechas de No suspensión?  <a><i data-tippy-content="Decida si este cnotrato tendrá un rango de fechas donde si tiene facturas abiertas no lo suspenderá (esto solo pasará en el rango escogido)" class="icono far fa-question-circle"></i></a></label>
                              <div class="row">
                                  <div class="col-sm-6">
                                  <div class="form-radio">
                                      <label class="form-check-label">
                                      <input type="radio" class="form-check-input" name="tipo_suspension_no" id="tipo_suspension_no1" value="1" {{$contrato->tipo_nosuspension == 1 ? 'checked' : ''}}> Si
                                      <i class="input-helper"></i><i class="input-helper"></i></label>
                                  </div>
                              </div>
                              <div class="col-sm-6">
                                  <div class="form-radio">
                                      <label class="form-check-label">
                                      <input type="radio" class="form-check-input" name="tipo_suspension_no" id="tipo_suspension_no2" value="0" {{$contrato->tipo_nosuspension == 0 ? 'checked' : ''}}> No
                                      <i class="input-helper"></i><i class="input-helper"></i></label>
                                  </div>
                              </div>
                              </div>
                              <span class="help-block error">
                                  <strong></strong>
                              </span>
                          </div>



                            <div class="col-md-4 form-group">
                                <div @if($contrato->tipo_nosuspension == 0) class="cls-nosuspension d-none" @endif>
                                <label class="control-label">Fecha desde no suspensión</label>
                                        <input type="date" class="form-control"  id="fecha_desde_nosuspension" value="{{$contrato->fecha_desde_nosuspension}}" name="fecha_desde_nosuspension" >
                                </div>
                            </div>

                            <div class="col-md-4 form-group">
                                <div @if($contrato->tipo_nosuspension == 0) class="cls-nosuspension d-none" @endif>
                                    <label class="control-label">Fecha hasta no suspensión</label>
                                    <input type="date" class="form-control"  id="fecha_hasta_nosuspension" value="{{$contrato->fecha_hasta_nosuspension}}" name="fecha_hasta_nosuspension">
                                </div>
                            </div>

                            <div class="form-group col-md-12">
                                <label class="control-label">Observaciones</label>
                                <textarea class="form-control" name="observaciones" >{{ $contrato->observaciones }}</textarea>
                                <span class="help-block error">
                                    <strong>{{ $errors->first('observaciones') }}</strong>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
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

    <div class="modal fade" id="modal-gps" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body px-0">
                    <div class="row" style="text-align: center;">
                        <div class="col-md-12">
                            <p><span class="font-weight-bold text-uppercase">Arrastre el pin para indicar las coordenadas deseadas.</span></p>
                        </div>
                    </div>
                    <div class="row" style="text-align: center;">
                        <span class="d-none">
                            Location: <input type="text" id="us2-address" style="width: 200px"/>
                            Radius: <input type="text" id="us2-radius"/>
                        </span>
                        <center>
                            <div id="us2" style="width: 465px; height: 400px; position: relative; overflow: hidden; margin: 0 30px;"></div>
                        </center>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $('#us2').locationpicker({
            location: {
                latitude: {{ $contrato->latitude ? $contrato->latitude : $gmaps->latitude }},
                longitude: {{ $contrato->longitude ? $contrato->longitude : $gmaps->longitude }}
            },
            zoom: 6,
            radius: 300,
            inputBinding: {
                latitudeInput: $('#us2-lat'),
                longitudeInput: $('#us2-lon'),
                radiusInput: $('#us2-radius'),
                locationNameInput: $('#us2-address')
            },
            mapTypeId: google.maps.MapTypeId.roadmap,
        });

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
            getInterfaces($("#server_configuration_id").val());
            $('#contrato_permanencia').change(function(){
                if($('#contrato_permanencia').val() == 1){
                    $("#div_meses").removeClass('d-none');
                    $("#contrato_permanencia_meses").val('').selectpicker('refresh');
                }else{
                    $("#contrato_permanencia_meses").val('').selectpicker('refresh');
                    $("#div_meses").addClass('d-none');
                }
            });
            $('#reconexion').change(function(){
                if($('#reconexion').val() == 1){
                    $("#div_reconexion").removeClass('d-none');
                    $("#costo_reconexion").val('');
                }else{
                    $("#div_reconexion").addClass('d-none');
                    $("#costo_reconexion").val('');
                }
            });
        });

        function validarTipo(){
                let type = $("#facturacion").val();
                if(type == 3){
                    $("#div_opciones").addClass('d-none');
                }
                else{
                    $("#div_opciones").removeClass('d-none');
                }
        }

            function hab_desha(){
                let opcion = $("#facturacion").val();
                let estadoDian = $("#opciones_dian").val();

                if (window.location.pathname.split("/")[1] === "software") {
    			var url = `/software/empresa/contratos/opciones_dian`;
                }else{
                    var url = `/empresa/contratos/opciones_dian`;
                }

                Swal.fire({
				title: `¿${estadoDian == 1 ? 'Deshabilitar' : 'Habilitar'} opciones de facturación electrónica?`,
				text: `${estadoDian == 1 ? 'Las opciones de facturacion electrónica para este contrato y esta persona se deshabilitarán' : 'Las opciones de facturacion electrónica para este contrato y esta persona se habilitarán'} `,
				type: 'question',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				cancelButtonText: 'Cancelar',
				confirmButtonText: `${estadoDian == 1 ? 'Deshabilitar' : 'Habilitar'}`,
			}).then((result) => {
				if (result.value) {
					$.ajax({
						url: url,
						headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        data: {
                            contratoId : $("#contrato_id").val(),
                            opcionDian : estadoDian
                        },
						method: 'post',
						success: function (response) {

							if (response) {
								Swal.fire({
									position: 'top-center',
									type: 'success',
									text: response.text,
									title: response.message,
									showConfirmButton: false,
									timer: 5000
								})
                                location.reload();
							}
						}
					});
				}
			})
            }

        $('#tipo_suspension_no1').change(function (e) {
            if ($('#tipo_suspension_no1').val() == 1) {
                $('.cls-nosuspension').removeClass('d-none');
            } else {
                $('.cls-nosuspension').addClass('d-none');
            }
        });


    $('#tipo_suspension_no2').change(function (e) {
        if ($('#tipo_suspension_no2').val() == 0) {
            $('.cls-nosuspension').addClass('d-none');
        } else {
            $('.cls-nosuspension').removeClass('d-none');
        }
    });

    </script>
@endsection
