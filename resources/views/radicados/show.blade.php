@extends('layouts.app')

@section('style')
    <style>
        body > div.container-scroller > div > div > div.content-wrapper > div > div > div > div.row.card-description > div > div > table > tbody > tr:nth-child(10) > td > img{
            width: 547px;
            height: 297px;
            border-radius: 0%;
        }
    </style>
@endsection

@section('boton')
    @if(auth()->user()->modo_lectura())
        <div class="alert alert-warning text-left" role="alert">
            <h4 class="alert-heading text-uppercase">Integra Colombia: Suscripción Vencida</h4>
           <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
        </div>
    @else
        <a href="javascript:abrirAcciones()" class="btn btn-dark btn-sm my-1" id="boton-acciones">Acciones del Radicado&nbsp;&nbsp;<i class="fas fa-caret-down"></i></a>
    @endif
@endsection

@section('content')
    @if(Session::has('success'))
		<div class="alert alert-success" >
			{{Session::get('success')}}
		</div>

		<script type="text/javascript">
			setTimeout(function(){
			    $('.alert').hide();
			    $('.active_table').attr('class', ' ');
			}, 5000);
		</script>
	@endif

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

    <div class="container-fluid d-none" id="form-acciones">
        <fieldset>
            <legend>Acciones del Radicado</legend>
            <div class="card shadow-sm border-0">
                <div class="card-body pb-3 pt-2" style="background: #f9f9f9;">
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <form action="{{ route('radicados.escalar',$radicado->id) }}" method="POST" class="delete_form" style="display: none;" id="escalar{{$radicado->id}}">
                                @csrf
                            </form>

                            <form action="{{ route('radicados.solventar',$radicado->id) }}" method="POST" class="delete_form" style="display: none;" id="solventar{{$radicado->id}}">
                                @csrf
                            </form>

                            <form action="{{ route('radicados.proceder',$radicado->id) }}" method="POST" class="delete_form" style="display: none;" id="proceder{{$radicado->id}}">
                                @csrf
                            </form>

                            <form action="{{ route('radicados.destroy',$radicado->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-{{$radicado->id}}">
                                @csrf
                                <input name="_method" type="hidden" value="DELETE">
                            </form>

                            @if($radicado->estatus == 1 || $radicado->estatus == 3)
                                @if(isset($_SESSION['permisos']['805']))
                                    <form action="{{ route('radicados.reabrir',$radicado->id) }}" method="POST" class="delete_form" style="display: none;" id="reabrir-{{$radicado->id}}">
                                        @csrf
                                    </form>
                                @endif
                            @endif

                            @if($radicado->estatus==0 || $radicado->estatus==2)
                                <a href="#" onclick="confirmar('proceder{{$radicado->id}}', '¿Está seguro de que desea @if($radicado->tiempo_ini == null) iniciar @else finalizar @endif  el radicado?');" class="btn btn-outline-success btn-sm "title="@if($radicado->tiempo_ini == null) Iniciar @else Finalizar @endif Radicado"><i class="fas fa-stopwatch"></i> @if($radicado->tiempo_ini == null) Iniciar @else Finalizar @endif Radicado</a>
                                @if(isset($_SESSION['permisos']['203']))
                                    <a href="{{route('radicados.edit',$radicado->id)}}" class="btn btn-outline-primary btn-sm" title="Editar"><i class="fas fa-edit"></i> Editar Caso</a>
                                @endif
                            @endif

                            @if($radicado->estatus==2 && !$radicado->firma)
                                @if(isset($_SESSION['permisos']['209']))
                                    <a href="{{route('radicados.firmar', $radicado->id)}}" class="btn btn-outline-warning btn-sm" title="Firmar"><i class="fas fa-file-signature"></i> Firmar Radicado</a>
                                @endif
                            @endif

                            @if($radicado->estatus == 1 || $radicado->estatus == 3)
                                @if(isset($_SESSION['permisos']['805']))
                                    <a href="#" onclick="confirmar('reabrir-{{$radicado->id}}', '¿Está seguro de que desea reabrir el radicado?');" class="btn btn-outline-success btn-sm" title="Reabrir Radicado"><i class="fas fa-lock-open"></i> Reabrir Radicado</a>
                                @endif
                            @else
                                @if($radicado->firma || $radicado->estatus==0)
                                    @if(isset($_SESSION['permisos']['207']))
                                        <a href="#" onclick="confirmar('solventar{{$radicado->id}}', '¿Está seguro de que desea solventar el caso?');" class="btn btn-outline-success btn-sm "title="Solventar Caso"><i class="fas fa-check-double"></i> Solventar Caso</a>
                                    @endif
                                @endif
                            @endif

                            @if($radicado->estatus==0)
                                @if(isset($_SESSION['permisos']['204']))
                                <button class="btn btn-outline-danger btn-sm" type="submit" title="Eliminar" onclick="confirmar('eliminar-{{$radicado->id}}', '¿Estas seguro que deseas eliminar el radicado?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i> Eliminar Caso</button>
                                @endif
                            @endif

                            @if (!$radicado->adjunto)
                                <a href="javascript:void" data-toggle="modal" data-target="#modalAdjunto" class="btn btn-outline-info btn-sm" id="btn_adjunto"><i class="fas fa-file-upload"></i> Adjuntar Archivo 1</a>
                                {{-- <a href="javascript:void" data-toggle="modal" data-target="#modalAdjunto" class="btn btn-outline-info btn-sm {{ $radicado->adjunto ? 'd-none' : '' }}" id="btn_adjunto"><i class="fas fa-file-upload"></i> Adjuntar Archivo</a> --}}

                            @endif

                            @if (!$radicado->adjunto_1)
                            <a href="javascript:void" data-toggle="modal" data-target="#modalAdjunto1" class="btn btn-outline-info btn-sm" id="btn_adjunto"><i class="fas fa-file-upload"></i> Adjuntar Archivo 2</a>
                            {{-- <a href="javascript:void" data-toggle="modal" data-target="#modalAdjunto" class="btn btn-outline-info btn-sm {{ $radicado->adjunto ? 'd-none' : '' }}" id="btn_adjunto"><i class="fas fa-file-upload"></i> Adjuntar Archivo</a> --}}

                            @endif

                            @if (!$radicado->adjunto_2)
                            <a href="javascript:void" data-toggle="modal" data-target="#modalAdjunto2" class="btn btn-outline-info btn-sm" id="btn_adjunto"><i class="fas fa-file-upload"></i> Adjuntar Archivo 3</a>
                            {{-- <a href="javascript:void" data-toggle="modal" data-target="#modalAdjunto" class="btn btn-outline-info btn-sm {{ $radicado->adjunto ? 'd-none' : '' }}" id="btn_adjunto"><i class="fas fa-file-upload"></i> Adjuntar Archivo</a> --}}

                            @endif

                            @if (!$radicado->adjunto_3)
                            <a href="javascript:void" data-toggle="modal" data-target="#modalAdjunto3" class="btn btn-outline-info btn-sm" id="btn_adjunto"><i class="fas fa-file-upload"></i> Adjuntar Archivo 4</a>
                            {{-- <a href="javascript:void" data-toggle="modal" data-target="#modalAdjunto" class="btn btn-outline-info btn-sm {{ $radicado->adjunto ? 'd-none' : '' }}" id="btn_adjunto"><i class="fas fa-file-upload"></i> Adjuntar Archivo</a> --}}

                            @endif

                            @if (!$radicado->adjunto_4)
                            <a href="javascript:void" data-toggle="modal" data-target="#modalAdjunto4" class="btn btn-outline-info btn-sm" id="btn_adjunto"><i class="fas fa-file-upload"></i> Adjuntar Archivo 5</a>
                            {{-- <a href="javascript:void" data-toggle="modal" data-target="#modalAdjunto" class="btn btn-outline-info btn-sm {{ $radicado->adjunto ? 'd-none' : '' }}" id="btn_adjunto"><i class="fas fa-file-upload"></i> Adjuntar Archivo</a> --}}

                            @endif

                            @if (!$radicado->adjunto_audio)
                            <a href="javascript:void" data-toggle="modal" data-target="#modalaudio" class="btn btn-outline-info btn-sm" id="btn_adjunto"><i class="fas fa-file-upload"></i> Adjuntar audio</a>
                            {{-- <a href="javascript:void" data-toggle="modal" data-target="#modalAdjunto" class="btn btn-outline-info btn-sm {{ $radicado->adjunto ? 'd-none' : '' }}" id="btn_adjunto"><i class="fas fa-file-upload"></i> Adjuntar Archivo</a> --}}

                            @endif

                            @if(isset($_SESSION['permisos']['843']))
                                <a href="{{ route('radicados.log',$radicado->id )}}" class="btn btn-outline-primary btn-sm" title="Log del Radicado"><i class="fas fa-clipboard-list"></i> Log del Radicado</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
    </div>

    <div class="row card-description">
    	<div class="col-md-12">
    		<div class="table-responsive">
    			<table class="table table-striped table-bordered table-sm info">
    				<tbody>
    					<tr>
    						<th width="15%">DATOS GENERALES</th>
    						<th></th>
    					</tr>
    					<tr>
    						<th>N° Radicado</th>
    						<td>{{$radicado->codigo}}</td>
    					</tr>
                        <tr>
                            <th>Fecha</th>
                            <td>{{date('d-m-Y g:i:s A', strtotime($radicado->created_at))}}</td>
                        </tr>
    					<tr>
    						<th>Cliente</th>
    						<td><a href="{{ route('contactos.show', $radicado->cliente)}}">{{$radicado->nombre}}</a></td>
    					</tr>
    					<tr>
    						<th>N° Telefónico</th>
    						<td>{{$radicado->telefono}}</td>
    					</tr>
    					<tr>
    						<th>Correo</th>
    						<td>{{$radicado->correo}}</td>
    					</tr>
                        @if($radicado->barrio)
    					<tr>
                            <th>Barrio</th>
                            <td>{{$radicado->barrio}}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>Dirección</th>
                            <td>{{$radicado->direccion}}</td>
                        </tr>
    					@if($radicado->creado)
    					<tr>
    						<th>Creado desde</th>
    						<td>{{$radicado->creado()}}</td>
    					</tr>
    					@endif
    					<tr>
    						<th>Tipo de Servicio</th>
    						<td>{{$radicado->servicio()->nombre}}</td>
    					</tr>

                        @if ($radicado->prioridad)
                        <tr>
                            <th>Prioridad</th>
                            <td class="font-weight-bold text-{{ $radicado->prioridad('true') }}">{{ $radicado->prioridad() }}</td>
                        </tr>
                        @endif
                        @if ($radicado->tiempo_est)
                        <tr>
                            <th>Tiempo Estimado</th>
                            <td>{{ $radicado->tiempo_est }} minuto(s)</td>
                        </tr>
                        @endif
                        @if ($radicado->tiempo_ini)
                        <tr>
                            <th>Inicio</th>
                            <td>{{date('d-m-Y g:i:s A', strtotime($radicado->tiempo_ini))}}</td>
                        </tr>
                        @endif
                        @if ($radicado->tiempo_fin)
                        <tr>
                            <th>Final</th>
                            <td>{{date('d-m-Y g:i:s A', strtotime($radicado->tiempo_fin))}}</td>
                        </tr>
                        <tr>
                            <th>Duración</th>
                            <td>{{ $duracion }} minuto(s)</td>
                        </tr>
                        @endif
                        @if($radicado->contrato)
                        <tr>
                            <th>Contrato</th>
                            <td>{{$radicado->contrato}}</td>
                        </tr>
                        @endif
                        @if ($radicado->ip)
                        <tr>
                            <th>Dirección IP</th>
                            <td>
                                <a href="http://{{ $radicado->ip }}" target="_blank">{{ $radicado->ip }} <i class="fas fa-external-link-alt"></i></a>
                            </td>
                        </tr>
                        @endif
                        @if ($radicado->mac_address)
                        <tr>
                            <th>Dirección MAC</th>
                            <td>{{ $radicado->mac_address }}</td>
                        </tr>
                        @endif
    					@if ($radicado->valor)
    					<tr>
    						<th>Valor de la Instalación</th>
    						<td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($radicado->valor)}}</td>
    					</tr>
    					@endif
                        @if ($radicado->tecnico() != NULL)
                        <tr>
                            <th>Técnico Asociado</th>
                            <td>{{$radicado->tecnico()->nombres}}</td>
                        </tr>
                        @endif
                        @if ($radicado->oficina)
                        <tr>
                            <th>Oficina Asociada</th>
                            <td>{{$radicado->oficina()->nombre}}</td>
                        </tr>
                        @endif
    					@if ($radicado->responsable() != NULL)
    					<tr>
    						<th>Creado por</th>
    						<td>{{$radicado->responsable()->nombres}}</td>
    					</tr>
    					@endif
    					<tr>
    						<th>Observaciones del Radicado</th>
    						<td>@php echo($radicado->desconocido); @endphp</td>
    					</tr>
    					<tr>
    						<th>Estatus</th>
    						<td>
    							@if ($radicado->estatus == 0)
    							    <span class="text-danger font-weight-bold">Pendiente</span>
    							@endif
    							@if ($radicado->estatus == 1)
    							    <span class="text-success font-weight-bold">Resuelto</span>
    							@endif
    							@if ($radicado->estatus == 2)
    							    <span class="text-danger font-weight-bold">Escalado / Pendiente</span>
    							@endif
    							@if ($radicado->estatus == 3)
    							    <span class="text-success font-weight-bold">Escalado / Resuelto</span>
    							@endif
                            </td>
    					</tr>
                        @if($radicado->solventado)
                        <tr>
                            <th>Solventado el</th>
                            <td>{{date('d-m-Y g:i:s A', strtotime($radicado->solventado))}}</td>
                        </tr>
                        @endif
    					@if ($radicado->reporte)
    						<tr>
    							<th>Reporte del Técnico</th>
                                <td>@php echo($radicado->reporte); @endphp</td>
    						</tr>
    					@endif
    					@if ($radicado->firma)
    						<tr>
    							<th>Firma Cliente</th>
    							<td>
    								<img src="data:image/png;base64,{{substr($radicado->firma,1)}}" class="img-fluid" style="width: 100%;height: auto;">
    	                        </td>
    						</tr>
    					@endif
                        @if ($radicado->adjunto_audio)
    						<tr>
    							<th>Evidencia en audio</th>
    							<td>
    								<a href="{{asset('adjuntos/documentos/'.$radicado->adjunto_audio)}}" target="_blank" class="btn btn-outline-success btn-sm btn-icons" style="border-radius: 50%;" title="Escuchar audio"><i class="fas fa-eye"></i>
    	                        </td>
    						</tr>
    					@endif
    					@if(($radicado->adjunto) || ($radicado->adjunto_1) || ($radicado->adjunto_2) || ($radicado->adjunto_3) || ($radicado->adjunto_4))
    					    <tr id="tr_adjunto">
    							<th>Archivos Adjunto</th>
    							<td>
                                    @if ($radicado->adjunto)
                                        {{-- Archivo 1.. --}}
    								    <a href="{{asset('adjuntos/documentos/'.$radicado->adjunto)}}" target="_blank" class="btn btn-outline-success btn-sm btn-icons" style="border-radius: 50%;" title="Ver Adjunto"><i class="fas fa-eye"></i>
                                        <a href="javascript:eliminar('{{$radicado->id}},{{1}}')" class="btn btn-outline-danger btn-sm btn-icons ml-1" style="border-radius: 50%;" title="Eliminar Adjunto"><i class="fas fa-times"></i></a>
                                    @endif
                                    @if ($radicado->adjunto_1)
                                    {{-- Archivo 2 --}}
                                        <a href="{{asset('adjuntos/documentos/'.$radicado->adjunto_1)}}" target="_blank" class="btn btn-outline-success btn-sm btn-icons" style="border-radius: 50%;" title="Ver Adjunto 1"><i class="fas fa-eye"></i>
                                        <a href="javascript:eliminar('{{$radicado->id}},{{2}}')" class="btn btn-outline-danger btn-sm btn-icons ml-1" style="border-radius: 50%;" title="Eliminar Adjunto 1"><i class="fas fa-times"></i></a>
                                    {{-- Archivo 3 --}}
                                    @endif
                                    @if ($radicado->adjunto_2)
                                        <a href="{{asset('adjuntos/documentos/'.$radicado->adjunto_2)}}" target="_blank" class="btn btn-outline-success btn-sm btn-icons" style="border-radius: 50%;" title="Ver Adjunto 2"><i class="fas fa-eye"></i>
                                        <a href="javascript:eliminar('{{$radicado->id}},{{3}}')" class="btn btn-outline-danger btn-sm btn-icons ml-1" style="border-radius: 50%;" title="Eliminar Adjunto 2"><i class="fas fa-times"></i></a>
                                    {{-- Archivo 4 --}}
                                    @endif
                                    @if ($radicado->adjunto_3)
                                        <a href="{{asset('adjuntos/documentos/'.$radicado->adjunto_3)}}" target="_blank" class="btn btn-outline-success btn-sm btn-icons" style="border-radius: 50%;" title="Ver Adjunto 3"><i class="fas fa-eye"></i>
                                        <a href="javascript:eliminar('{{$radicado->id}},{{4}}')" class="btn btn-outline-danger btn-sm btn-icons ml-1" style="border-radius: 50%;" title="Eliminar Adjunto 3"><i class="fas fa-times"></i></a>
                                    @endif
                                    {{-- Archivo 5 --}}
                                    @if ($radicado->adjunto_4)
                                        <a href="{{asset('adjuntos/documentos/'.$radicado->adjunto_4)}}" target="_blank" class="btn btn-outline-success btn-sm btn-icons" style="border-radius: 50%;" title="Ver Adjunto 4"><i class="fas fa-eye"></i>
                                        <a href="javascript:eliminar('{{$radicado->id}},{{5}}')" class="btn btn-outline-danger btn-sm btn-icons ml-1" style="border-radius: 50%;" title="Eliminar Adjunto 4"><i class="fas fa-times"></i></a>
                                    @endif
                                    {{-- @if ($radicado->adjunto_audio)
                                        <a href="{{asset('adjuntos/documentos/'.$radicado->adjunto_audio)}}" target="_blank" class="btn btn-outline-success btn-sm btn-icons" style="border-radius: 50%;" title="Ver Adjunto 4"><i class="fas fa-eye"></i>
                                        <a href="javascript:eliminar('{{$radicado->id}},{{6}}')" class="btn btn-outline-danger btn-sm btn-icons ml-1" style="border-radius: 50%;" title="Eliminar Adjunto 4"><i class="fas fa-times"></i></a>
                                    @endif --}}
                                </td>

    						</tr>
    					@endif
    				</tbody>
    			</table>
    		</div>
    		@if($radicado->reporte=='' && $radicado->estatus > 1)
    			@if(isset($_SESSION['permisos']['210']))
    				<form method="POST" action="{{ route('radicados.update', $radicado->id ) }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-radicado" >
    					@csrf
    					<input name="_method" type="hidden" value="PATCH">
    					<div class="col-md-12 form-group">
    						<label class="control-label">Observaciones del Técnico</label>
    						<textarea  class="form-control form-control-sm min_max_100" id="reporte" required="" name="reporte"></textarea>
    						<span class="help-block error">
    							<strong>{{ $errors->first('desconocido') }}</strong>
    						</span>
    					</div>

    					<div class="col-sm-12" style="text-align: center;">
    						<a href="{{route('radicados.index')}}" class="btn btn-outline-secondary">Cancelar</a>
    						<button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
    					</div>
    				</form>
    			@endif
    		@endif
    	</div>
    </div>

    <div class="modal fade" id="modalAdjunto" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    	<div class="modal-dialog modal-dialog-centered">
    		<div class="modal-content">
    			<div class="modal-header">
    				<h4 class="modal-title">ADJUNTAR ARCHIVO AL RADICADO</h4>
    				<button type="button" class="close" data-dismiss="modal">&times;</button>
    			</div>
    			<form method="post" action="{{ route('radicados.update', $radicado->id ) }}" style="padding: 0;" role="form" class="forms-sample"  id="form_radicado" enctype="multipart/form-data">@csrf
    			<div class="modal-body">
    				<input name="_method" type="hidden" value="PATCH">
    				<input name="id" type="hidden" value="{{ $radicado->id }}">
    				<div class="row">
    					<div class="col-md-12 form-group">
    						<label class="control-label"></label>
    						<input type="file" class="form-control"  id="adjunto" name="adjunto" value="{{$radicado->adjunto}}" accept=".jpg, .jpeg, .png, .pdf, .JPG, .JPEG, .PNG, .PDF" required>
    						<span style="color: red;">
    							<strong>{{ $errors->first('adjunto') }}</strong>
    						</span>
    					</div>
    				</div>
    			</div>
    			<div class="modal-footer">
    				<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
    				<button type="submit" class="btn btn-success">Subir Adjuntos</button>
    			</div>
    			</form>
    		</div>
    	</div>
    </div>
    {{-- modal archivo 1 --}}
    <div class="modal fade" id="modalAdjunto1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    	<div class="modal-dialog modal-dialog-centered">
    		<div class="modal-content">
    			<div class="modal-header">
    				<h4 class="modal-title">ADJUNTAR ARCHIVO AL RADICADO</h4>
    				<button type="button" class="close" data-dismiss="modal">&times;</button>
    			</div>
    			<form method="post" action="{{ route('radicados.update', $radicado->id ) }}" style="padding: 0;" role="form" class="forms-sample"  id="form_radicado" enctype="multipart/form-data">@csrf
    			<div class="modal-body">
    				<input name="_method" type="hidden" value="PATCH">
    				<input name="id" type="hidden" value="{{ $radicado->id }}">
    				<div class="row">
    					<div class="col-md-12 form-group">
    						<label class="control-label"></label>
    						<input type="file" class="form-control"  id="adjunto" name="adjunto1" value="{{$radicado->adjunto}}" accept=".jpg, .jpeg, .png, .pdf, .JPG, .JPEG, .PNG, .PDF" required>
    						<span style="color: red;">
    							<strong>{{ $errors->first('adjunto') }}</strong>
    						</span>
    					</div>
    				</div>
    			</div>
    			<div class="modal-footer">
    				<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
    				<button type="submit" class="btn btn-success">Subir Adjunto</button>
    			</div>
    			</form>
    		</div>
    	</div>
    </div>

    {{-- moda archivo 2 --}}
    <div class="modal fade" id="modalAdjunto2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    	<div class="modal-dialog modal-dialog-centered">
    		<div class="modal-content">
    			<div class="modal-header">
    				<h4 class="modal-title">ADJUNTAR ARCHIVO AL RADICADO</h4>
    				<button type="button" class="close" data-dismiss="modal">&times;</button>
    			</div>
    			<form method="post" action="{{ route('radicados.update', $radicado->id ) }}" style="padding: 0;" role="form" class="forms-sample"  id="form_radicado" enctype="multipart/form-data">@csrf
    			<div class="modal-body">
    				<input name="_method" type="hidden" value="PATCH">
    				<input name="id" type="hidden" value="{{ $radicado->id }}">
    				<div class="row">
    					<div class="col-md-12 form-group">
    						<label class="control-label"></label>
    						<input type="file" class="form-control"  id="adjunto" name="adjunto2" value="{{$radicado->adjunto}}" accept=".jpg, .jpeg, .png, .pdf, .JPG, .JPEG, .PNG, .PDF" required>
    						<span style="color: red;">
    							<strong>{{ $errors->first('adjunto') }}</strong>
    						</span>
    					</div>
    				</div>
    			</div>
    			<div class="modal-footer">
    				<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
    				<button type="submit" class="btn btn-success">Subir Adjunto</button>
    			</div>
    			</form>
    		</div>
    	</div>
    </div>

    {{-- modal archivo 3 --}}
    <div class="modal fade" id="modalAdjunto3" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    	<div class="modal-dialog modal-dialog-centered">
    		<div class="modal-content">
    			<div class="modal-header">
    				<h4 class="modal-title">ADJUNTAR ARCHIVO AL RADICADO</h4>
    				<button type="button" class="close" data-dismiss="modal">&times;</button>
    			</div>
    			<form method="post" action="{{ route('radicados.update', $radicado->id ) }}" style="padding: 0;" role="form" class="forms-sample"  id="form_radicado" enctype="multipart/form-data">@csrf
    			<div class="modal-body">
    				<input name="_method" type="hidden" value="PATCH">
    				<input name="id" type="hidden" value="{{ $radicado->id }}">
    				<div class="row">
    					<div class="col-md-12 form-group">
    						<label class="control-label"></label>
    						<input type="file" class="form-control"  id="adjunto" name="adjunto3" value="{{$radicado->adjunto}}" accept=".jpg, .jpeg, .png, .pdf, .JPG, .JPEG, .PNG, .PDF" required>
    						<span style="color: red;">
    							<strong>{{ $errors->first('adjunto') }}</strong>
    						</span>
    					</div>
    				</div>
    			</div>
    			<div class="modal-footer">
    				<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
    				<button type="submit" class="btn btn-success">Subir Adjunto</button>
    			</div>
    			</form>
    		</div>
    	</div>
    </div>

    {{-- modal archivo 4 --}}
    <div class="modal fade" id="modalAdjunto4" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    	<div class="modal-dialog modal-dialog-centered">
    		<div class="modal-content">
    			<div class="modal-header">
    				<h4 class="modal-title">ADJUNTAR ARCHIVO AL RADICADO</h4>
    				<button type="button" class="close" data-dismiss="modal">&times;</button>
    			</div>
    			<form method="post" action="{{ route('radicados.update', $radicado->id ) }}" style="padding: 0;" role="form" class="forms-sample"  id="form_radicado" enctype="multipart/form-data">@csrf
    			<div class="modal-body">
    				<input name="_method" type="hidden" value="PATCH">
    				<input name="id" type="hidden" value="{{ $radicado->id }}">
    				<div class="row">
    					<div class="col-md-12 form-group">
    						<label class="control-label"></label>
    						<input type="file" class="form-control"  id="adjunto" name="adjunto4" value="{{$radicado->adjunto}}" accept=".jpg, .jpeg, .png, .pdf, .JPG, .JPEG, .PNG, .PDF" required>
    						<span style="color: red;">
    							<strong>{{ $errors->first('adjunto') }}</strong>
    						</span>
    					</div>
    				</div>
    			</div>
    			<div class="modal-footer">
    				<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
    				<button type="submit" class="btn btn-success">Subir Adjunto</button>
    			</div>
    			</form>
    		</div>
    	</div>
    </div>

    {{-- modal audio --}}
    <div class="modal fade" id="modalaudio" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    	<div class="modal-dialog modal-dialog-centered">
    		<div class="modal-content">
    			<div class="modal-header">
    				<h4 class="modal-title">ADJUNTAR AUDIO AL RADICADO</h4>
    				<button type="button" class="close" data-dismiss="modal">&times;</button>
    			</div>
    			<form method="post" action="{{ route('radicados.update', $radicado->id ) }}" style="padding: 0;" role="form" class="forms-sample"  id="form_radicado" enctype="multipart/form-data">@csrf
    			<div class="modal-body">
    				<input name="_method" type="hidden" value="PATCH">
    				<input name="id" type="hidden" value="{{ $radicado->id }}">
    				<div class="row">
    					<div class="col-md-12 form-group">
    						<label class="control-label"></label>
                            <input type="file" name="archivo_de_audio" accept="audio/*" required id="adjunto">
    						{{-- <input type="file" class="form-control"  id="adjunto" name="adjunto4" value="{{$radicado->adjunto}}" accept=".jpg, .jpeg, .png, .pdf, .JPG, .JPEG, .PNG, .PDF" required> --}}
    						<span style="color: red;">
    							<strong>{{ $errors->first('adjunto') }}</strong>
    						</span>
    					</div>
    				</div>
    			</div>
    			<div class="modal-footer">
    				<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
    				<button type="submit" class="btn btn-success">Subir Adjunto</button>
    			</div>
    			</form>
    		</div>
    	</div>
    </div>

@endsection

@section('scripts')
    <script>
        function abrirAcciones() {
            if ($('#form-acciones').hasClass('d-none')) {
                $('#boton-acciones').html('Acciones del Radicado&nbsp;&nbsp;<i class="fas fa-caret-up"></i>');
                $('#form-acciones').removeClass('d-none');
            } else {
                $('#boton-acciones').html('Acciones del Radicado&nbsp;&nbsp;<i class="fas fa-caret-down"></i>');
                cerrarFiltrador();
            }
        }

        function cerrarFiltrador() {
            $('#form-acciones').addClass('d-none');
            $('#boton-acciones').html('Acciones del Radicado&nbsp;&nbsp;<i class="fas fa-caret-down"></i>');
        }

        function eliminar(id,num){
        	swal({
        		title: '¿Está seguro de eliminar el archivo adjunto del sistema?',
        		text: 'Se borrara de forma permanente',
        		type: 'question',
        		showCancelButton: true,
        		confirmButtonColor: '#00ce68',
        		cancelButtonColor: '#d33',
        		confirmButtonText: 'Aceptar',
        		cancelButtonText: 'Cancelar',
        	}).then((result) => {
        		if (result.value) {
        			if (window.location.pathname.split("/")[1] === "software") {
        				var url = '/software/empresa/radicados/'+id+'/eliminarAdjunto';
        			}else{
        				var url = '/empresa/radicados/'+id+'/eliminarAdjunto';
        			}

        			$.ajax({
        				url: url,
        				beforeSend: function(){
        					cargando(true);
        				},
        				success: function(data){
        				    Swal.fire({
        						type:  data.type,
        						title: data.title,
        						text:  data.text,
        						showConfirmButton: false,
        						timer: 5000
        					});
        					if(data.success == true){
        						$("#tr_adjunto").remove();
        						$("#btn_adjunto").removeClass('d-none');
        						// setTimeout(function(){
        						// 	location.reload();
        						// }, 1000);
        					}
        					cargando(false);
        				},
        				error: function(data){
        					cargando(false);
        					Swal.fire({
        						type:  'error',
        						title: 'Disculpe, estamos presentando problemas al tratar de enviar el formulario.',
        						text:  'intentelo mas tarde',
        						showConfirmButton: false,
        						timer: 2500
        					})
        				}
        			});
        		}
        	})
        }
    </script>
@endsection
