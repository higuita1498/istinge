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

	.text-center-c{
		width: 100%;
		margin: .5em;
		padding: .5em;
		/*display: flex;*/
		align-items: center;
		white-space: normal;
		text-align: initial;
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
	    <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
	        <div class="btn-group" role="group">
	            <button id="btnGroupDrop1" type="button" class="btn btn-dark dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	                Acciones del Contacto {{ $contacto->contrato }}
	            </button>
	            <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
	            	@if(count($contratos)>0)
	            	{{-- <form action="{{ route('contratos.state',$contrato->id) }}" method="post" class="delete_form" style="margin:0;display: inline-block;" id="cambiar-state{{$contrato->id}}">
	            		{{ csrf_field() }}
	            	</form> --}}

	            	<form action="{{ route('contactos.desasociar', $id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="desasociar-contacto{{$contacto->id}}">
	            		{{ csrf_field() }}
	                </form>
	                @endif

	                <form action="{{ route('contactos.destroy', $id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-contacto{{$contacto->id}}">
	                	{{ csrf_field() }}
	                	<input name="_method" type="hidden" value="DELETE">
	                </form>

	            	@if($contacto->status==1)
	            	    @if(isset($_SESSION['permisos']['6']))
	            	        <a href="{{route('contactos.edit',$id)}}" class="dropdown-item"><i class="fas fa-edit"></i> Editar {{$contacto->tipo_contacto==0?'cliente':'proveedor'}}</a>
	            	    @endif
	            	    {{-- @if(!$contrato && $contacto->tipo_contacto !=1) --}}
	            	    <a href="{{route('contratos.create_cliente',$id)}}" class="dropdown-item"><i class="fas fa-file-contract"></i> Crear Contrato</a>
	            	    {{-- @endif --}}
	            	    @if(isset($_SESSION['permisos']['201']) && $contacto->tipo_contacto !=1)
	            	        <a href="{{route('radicados.create_cliente', $id)}}" class="dropdown-item"><i class="far fa-life-ring"></i> Crear Radicado</a>
	            	    @endif

	            	    @if(count($contratos)>0)
	            	        {{-- @if(isset($_SESSION['permisos']['407']))
	            	            <button @if($contrato->state == 'enabled') class="dropdown-item" title="Deshabilitar" @else class="btn btn-outline-success" title="Habilitar" @endif type="submit" onclick="confirmar('cambiar-state{{$contrato->id}}', '¿Estas seguro que deseas cambiar el estatus del contrato?', '');"><i class="fas fa-file-signature"></i>@if($contrato->state == 'enabled') Deshabilitar Contrato @else Habilitar Contrato @endif</button>
	            	        @endif --}}
	            	        @if($user_app && isset($_SESSION['permisos']['730']))
	            	            <button class="dropdown-item" type="submit" title="Desasociar de APP" onclick="confirmar('desasociar-contacto{{$contacto->id}}', '¿Está seguro que desea desasociar el cliente de la APP?', 'Se borrara de forma permanente');"><i class="fas fa-mobile-alt"></i> Desasociar de APP</button>
	            	        @endif
	            	    @endif

	            	    @if(isset($_SESSION['permisos']['7']))
	            	        <button class="dropdown-item" type="submit" title="Eliminar" onclick="confirmar('eliminar-contacto{{$contacto->id}}', '¿Está seguro que desea eliminar el cliente?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i> Eliminar {{$contacto->tipo_contacto==0?'cliente':'proveedor'}}</button>
	            	    @endif
	            	@endif
	            	@if ($contacto->email && $contacto->contract != 'N/A')
	            	<a href="{{route('avisos.envio.email.cliente',$id)}}" target="_blank" class="dropdown-item"><i class="fas fa-envelope-open-text"></i> Enviar Notificación por EMAIL</a>
	            	@endif
	            </div>
	        </div>
	    </div>
	@endif
	<div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
		<button type="button" class="close" data-dismiss="alert">×</button>
		<strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
	</div>
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

	<div class="row card-description">
		<div class="col-md-12">
			<div class="table-responsive">
				<table class="table table-striped table-bordered table-sm info">
					<tbody>
						<tr>
							<th class="bg-th text-center" colspan="2" style="font-size: 1em;"><strong>DATOS GENERALES {{$contacto->tipo_contacto==0?'DEL CLIENTE':'DEL PROVEEDOR'}}</strong></th>
						</tr>
						@if($contacto->serial_onu)
						<tr>
							<th width="20%">Serial ONU</th>
							<td>{{$contacto->serial_onu}}</td>
						</tr>
						@endif
						<tr>
							<th width="20%">Nombre</th>
							<td>{{$contacto->nombre}} {{$contacto->apellidos()}}</td>
						</tr>
						<tr>
							<th width="20%">Tipo de Identificación</th>
							<td>{{ $contacto->identificacion }}</td>
						</tr>
						<tr>
							<th width="20%">Identificación</th>
							<td>{{$contacto->nit}}</td>
						</tr>
						@if($contacto->tip_iden == 6)
						<tr>
							<th width="20%">DV</th>
							<td>{{$contacto->dv}}</td>
						</tr>
						@endif
						@if($contacto->telefono1)
						<tr>
							<th width="20%">Teléfono</th>
							<td>{{$contacto->telefono1}}</td>
						</tr>
						@endif
						@if($contacto->telefono2)
						<tr>
							<th width="20%">Teléfono 2</th>
							<td>{{$contacto->telefono2}}</td>
						</tr>
						@endif
						@if($contacto->fax)
						<tr>
							<th width="20%">Fax</th>
							<td>{{$contacto->fax}}</td>
						</tr>
						@endif
						@if($contacto->celular)
						<tr>
							<th width="20%">Celular</th>
							<td>{{$contacto->celular}}</td>
						</tr>
						@endif
						@if($contacto->estrato)
						<tr>
							<th width="20%">Estrato</th>
							<td>{{$contacto->estrato}}</td>
						</tr>
						@endif
						@if($contacto->fk_iddepartamento)
						<tr>
							<th width="20%">Departamento</th>
							<td>{{$contacto->getDepartamentoNameAttribute()}}</td>
						</tr>
						@endif
						@if($contacto->fk_idmunicipio)
						<tr>
							<th width="20%">Municipio</th>
							<td>{{$contacto->getMunicipioNameAttribute()}}</td>
						</tr>
						@endif
						@if($contacto->direccion)
						<tr>
							<th width="20%">Dirección</th>
							<td>{{$contacto->direccion}}</td>
						</tr>
						@endif
						@if($contacto->vereda)
						<tr>
							<th width="20%">Corregimiento/Vereda</th>
							<td>{{$contacto->vereda}}</td>
						</tr>
						@endif
						@if($contacto->barrio)
						<tr>
							<th width="20%">Barrio</th>
							<td>{{$contacto->barrio}}</td>
						</tr>
						@endif
						@if($contacto->email)
						<tr>
							<th width="20%">Correo Electrónico</th>
							<td>{{$contacto->email}}</td>
						</tr>
						@endif
                        {{-- nuevos campos agregados  --}}
                        @if($contacto->email)
						<tr>
							<th width="20%">Monitoreo</th>
							<td>{{$contacto->monitoreo}}</td>
						</tr>
						@endif

                        @if($contacto->refiere)
						<tr>
							<th width="20%">Refiere</th>
							<td>{{$contacto->refiere}}</td>
						</tr>
						@endif

                        @if($contacto->combo_int_tv)
						<tr>
							<th width="20%">Combo INT y TV</th>
							<td>{{$contacto->combo_int_tv}}</td>
						</tr>
						@endif

                        @if($contacto->referencia_1)
						<tr>
							<th width="20%">Referencia 1</th>
							<td>{{$contacto->referencia_1}}</td>
						</tr>
						@endif

                        @if($contacto->referencia_2)
						<tr>
							<th width="20%">Referencia 2</th>
							<td>{{$contacto->referencia_2}}</td>
						</tr>
						@endif

                        @if($contacto->cierra_venta)
						<tr>
							<th width="20%">Cierra Venta</th>
							<td>{{$contacto->cierra_venta}}</td>
						</tr>
						@endif
                        {{-- fin de campos agregados --}}
						@if($contacto->oficina)
						<tr>
							<th width="20%">Oficina Asociada</th>
							<td>{{$contacto->oficina()->nombre}}</td>
						</tr>
						@endif
						@if($contacto->firma_isp)
						<tr>
							<th width="20%">Fecha de la firma del Contrato</th>
							<td>
							<a href="javascript:editFechaIsp()"> <span id="fecha-isp-date">{{date('d-m-Y', strtotime($contacto->fecha_isp))}}</span> <i class="fas fa-edit"></i></a>
							</td>
						</tr>
						<tr>
							<th width="20%">Asignación de Contrato Digital</th>
							<td><a href="{{ route('asignaciones.imprimir',$id)}}" target="_blank"><strong>Ver Documento</strong></a></td>
						</tr>
						<div class="modal" tabindex="-1" role="dialog" id="modal-fecha-isp">
							<div class="modal-dialog modal-sm" role="document">
								<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title">Actualizar fecha</h5>
									<button type="button" class="close" data-dismiss="modal" aria-label="Close">
									<span aria-hidden="true">&times;</span>
									</button>
								</div>
								<div class="modal-body">
									<form action="{{ route('contactos.cambiar.fechaIsp', $contacto->id) }}" id="form-fechaIsp">
								<input type="text" class="form-control datepicker" id="fecha_isp" value="{{ date('d-m-Y', strtotime($contacto->fecha_isp)) }}">
									</form>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-primary" onclick="updateFechaIsp()">Guardar</button>
								</div>
								</div>
							</div>
						</div>
						@endif
						@if($contacto->router)
						<tr>
							<th width="20%">¿El router fue regresado?</th>
							<td>{{$contacto->router}}</td>
						</tr>
						@endif
						@if($contacto->observaciones)
						<tr>
							<th width="20%">Observaciones</th>
							<td>{{$contacto->observaciones}}</td>
						</tr>
						@endif
					</tbody>
				</table>
			</div>

			<div class="row">
				@foreach($contratos as $contrato)
					<div class="col-md-{{count($contratos)>1?'6':'12'}}">
						<div class="table-responsive">
						<table class="table table-striped table-bordered table-sm info mt-4">
							<tbody>
								<tr>
									<th class="bg-th text-center" colspan="2" style="font-size: 1em;"><strong>CONTRATO ASOCIADO</strong></th>
								</tr>
								@if($contrato->nro)
								<tr>
									<th width="20%">N° Contrato</th>
									<td><a href="{{ route('contratos.show',$contrato->id )}}"><strong>{{ $contrato->nro }}</strong></a></td>
								</tr>
								@endif
								@if($contrato->grupo_corte)
								<tr>
									<th width="20%">Grupo de Corte</th>
									<td><a href="{{ route('grupos-corte.show',$contrato->grupo_corte()->id )}}" target="_blank"><strong>{{ $contrato->grupo_corte()->nombre }}</strong></a> (CORTE {{ $contrato->grupo_corte()->fecha_corte }} - SUSPENSIÓN {{ $contrato->grupo_corte()->fecha_suspension }})</td>
								</tr>
								@endif
								@if($contrato->state)
								<tr>
									<th width="20%">Estado del Contrato</th>
									<td>
									    <strong class="text-{{$contrato->status('true')}}">{{$contrato->status()}}</strong>
									</td>
								</tr>
								@endif
								@if($contrato->ip)
								<tr>
									<th width="20%">Dirección IP</th>
									<td>
										<a href="http://{{ $contrato->ip }}{{ $contrato->puerto ? ':'.$contrato->puerto->nombre : '' }}" target="_blank">{{ $contrato->ip }}{{ $contrato->puerto ? ':'.$contrato->puerto->nombre : '' }} <i class="fas fa-external-link-alt"></i></a>
									</td>
								</tr>
								@endif
								@if($contrato->plan_id)
								<tr>
									<th width="20%">Plan Internet Contratado</th>
									<td>{{$contrato->plan()->name}}</td>
								</tr>
								@endif
								@if($contrato->servicio_tv)
								<tr>
									<th width="20%">Plan TV Contratado</th>
									<td>{{$contrato->plan('true')->producto}}</td>
								</tr>
								@endif
								@if($contrato->factura_individual)
								<tr>
									<th>Facturación Individual</th>
									<td>{{ $contrato->factura_individual == 1 ?'Si':'No' }}</td>
								</tr>
								@endif
								<tr>
									<th>Acciones</th>
									<td>
									@if(isset($_SESSION['permisos']['402']))
									<a href="{{route('asignaciones.create', ['contrato' => $contrato->id, 'nro-contrato' => $contrato->nro])}}" class="btn btn-outline-info btn-sm"><i class="fas fa-plus"></i>Firmar asignación</a>
									@endif
									@if(isset($_SESSION['permisos']['817']))
									<a href="{{route('asignaciones.imprimir', [$contacto->id, 'idContrato' => $contrato->id, 'nro-contrato' => $contrato->nro])}}" class="btn btn-outline-info btn-sm"><i class="fas fa-print"></i>Imprimir</a>
									@endif
									@if(isset($_SESSION['permisos']['818']))
									<a href="{{route('asignaciones.enviar', [$contacto->id, 'idContrato' => $contrato->id, 'nro-contrato' => $contrato->nro])}}" class="btn btn-outline-info btn-sm"><i class="fas fa-envelope"></i>Enviar</a>
									@endif
									<a href="{{route('contratos.crm.forzar', $contacto->id)}}" class="btn btn-outline-info btn-sm">+ CRM</a>
									</td>
								</tr>
							</tbody>
						</table>
					    </div>
					</div>
				@endforeach
		    </div>

			<div class="table-responsive mt-4">
				<table class="table table-striped table-bordered table-sm info">
					<tbody>
						<tr>
							<th class="bg-th text-center" colspan="2" style="font-size: 1em;"><strong>SALDOS {{$contacto->tipo_contacto==0?'DEL CLIENTE':'DEL PROVEEDOR'}}</strong></th>
						</tr>
						<tr>
							<th width="20%">Saldo a favor Pagos / Ingresos</th>
							<td>
								<div style="display:inline-flex;float;left;">
									<p id="textsaldofavor">{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($contacto->saldo_favor)}}</p>
									@if(isset($_SESSION['permisos']['856']))
									<a href="javascript:modificarSaldo({{$contacto->id}})" style="font-size: 0.8em;margin-left: 10px;">
										<i class="fas fa-pencil-alt"></i></a>
									{{-- <a href="javascript:historialSaldos({{$contacto->id}})" style="font-size: 0.8em;margin-left: 10px;">
										<i class="far fa-eye"></i>
									</a> --}}
									@endif
								</div>
							</td>
						</tr>
						<tr>
							<th width="20%">Saldo a favor Pagos / Egresos </th>
							<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($contacto->saldo_favor2)}}</td>
						</tr>
					</tbody>
				</table>
			</div>

		</div>
	</div>

	<div class="row card-description">
		<div class="col-md-12">
			<ul class="nav nav-pills" id="myTab" role="tablist">
				@if($contacto->usado()>0)
				<li class="nav-item">
					<a class="nav-link active" id="profile-tab" data-toggle="tab" href="#facturas_venta" role="tab" aria-controls="facturas_venta" aria-selected="false">Facturas Generadas</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" id="remi-tab" data-toggle="tab" href="#remi" role="tab" aria-controls="remi" aria-selected="false">Remisiones</a>
				</li>
				@if($contacto->tipo_contacto>0)
				<li class="nav-item">
					<a class="nav-link" id="facturas_compra-tab" data-toggle="tab" href="#facturas_compra" role="tab" aria-controls="facturas_compra" aria-selected="false">Facturas de Compra</a>
				</li>
				@endif
				<li class="nav-item">
					<a class="nav-link" id="transacciones-tab" data-toggle="tab" href="#transacciones" role="tab" aria-controls="transacciones" aria-selected="true">Movimientos</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" id="promesas-pago-tab" data-toggle="tab" href="#promesas-pago" role="tab" aria-controls="promesas-pago" aria-selected="false">Promesas de Pago</a>
				</li>
				@if($contacto->nit)
				<li class="nav-item">
					<a class="nav-link" id="radicad-tab" data-toggle="tab" href="#radicad" role="tab" aria-controls="radicad" aria-selected="false">Radicados</a>
				</li>
				<li class="nav-item">
					<a class="nav-link" id="crm-history-tab" data-toggle="tab" href="#crm-history" role="tab" aria-controls="crm-history" aria-selected="false">CRM</a>
				</li>
				@endif
				@endif
				<li class="nav-item">
					<a class="nav-link {{ $contacto->usado()==0?'active':'' }}" id="arcadj-tab" data-toggle="tab" href="#arcadj" role="tab" aria-controls="arcadj" aria-selected="false">Archivos Adjuntos</a>
				</li>
			</ul>
			<hr style="border-top: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}; margin: .5rem 0rem;">
			<div class="tab-content fact-table" id="myTabContent">
				@if($contacto->usado()>0)
					<div class="tab-pane fade" id="transacciones" role="tabpanel" aria-labelledby="transacciones-tab">
						<input type="hidden" id="url-show-movimientos" value="{{route('bancos.cliente.movimientos.cuenta', $contacto->id)}}">
						<div class="table-responsive mt-3">
							<table class="text-center table table-light table-striped table-hover" id="table-show-movimientos" style="width: 100%; border: 1px solid #e9ecef;">
								<thead class="thead-light">
									<th>Fecha</th>
									<th>Banco</th>
									<th>Detalle del Pago</th>
									<th>Salidas</th>
									<th>Entradas</th>
								</thead>
								<tbody>

								</tbody>
							</table>
						</div>
					</div>
					<div class="tab-pane fade show active" id="facturas_venta" role="tabpanel" aria-labelledby="facturas_venta-tab">
						<input type="hidden" id="url-show-facturas" value="{{route('factura.datatable.cliente', $contacto->id)}}">
						<div class="table-responsive mt-3">
		    				<table class="text-center table table-light table-striped table-hover" id="table-show-facturas" style="width: 100%; border: 1px solid #e9ecef;">
		    					<thead class="thead-light">
		    						<tr>
		    							<th>Factura</th>
		    							<th>Cliente</th>
		    							<th>Contrato(s)</th>
		    							<th>Creación</th>
		    							<th>Vencimiento</th>
		    							<th>Total</th>
		    							<th>Pagado</th>
		    							<th>Por Pagar</th>
		    							<th>Estado</th>
		    							<th>Acciones</th>
		    						</tr>
		    					</thead>
		    					<tbody></tbody>
		    				</table>
						</div>
					</div>
					  {{-- Remisiones  --}}
					  <div class="tab-pane fade" id="remi" role="tabpanel" aria-labelledby="remi-tab">
						<input type="hidden" id="url-show-remisiones"
							value="{{ route('remisiones.datatable.cliente', $contacto->id) }}">
						<table class="table table-light table-striped table-hover " id="table-show-remisiones"
							style="width: 100%; border: 1px solid #e9ecef;">
							<thead class="thead-light">
								<tr>
									<th>Código</th>
									<th>Cliente</th>
									<th>Creación</th>
									<th>Vencimiento</th>
									<th>Estado</th>
									<th>Total</th>
									<th>Pagado</th>
									<th>Por pagar</th>
									<th>Acciones</th>
								</tr>

							</thead>
						</table>
					</div>
					<div class="tab-pane fade" id="facturas_compra" role="tabpanel" aria-labelledby="facturas_compra-tab">
						<input type="hidden" id="url-show-facturas-compras" value="{{route('facturap.datatable.cliente', $contacto->id)}}">
						<table class="table table-light table-striped table-hover  " id="table-show-facturas-compras" style="width: 100%; border: 1px solid #e9ecef;">
							<thead class="thead-light">
								<tr>
									<th>Factura</th>
									<th>Proveedor</th>
									<th>Creación</th>
									<th>Vencimiento</th>
									<th>Total</th>
									<th>Pagado</th>
									<th>Por Pagar</th>
									<th>Acciones</th>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>
					<div class="tab-pane fade" id="promesas-pago" role="tabpanel" aria-labelledby="promesas-pago-tab">
						<input type="hidden" id="url-show-promesas" value="{{route('promesas.json', $contacto->id)}}">
						<div class="table-responsive mt-3">
		    				<table class="text-center table table-light table-striped table-hover" id="table-show-promesas" style="width: 100%; border: 1px solid #e9ecef;">
		    					<thead class="thead-light">
		    						<tr>
		    							<th>Nro</th>
		    							<th>Factura</th>
		    							<th>Fecha Pago</th>
		    							<th>Fecha Promesa</th>
		    							<th>Creado por</th>
		    							<th>Acciones</th>
		    						</tr>
		    					</thead>
		    					<tbody></tbody>
		    				</table>
						</div>
					</div>
					@if($contacto->nit)
						<div class="tab-pane fade" id="radicad" role="tabpanel" aria-labelledby="radicad-tab">
							<input type="hidden" id="url-show-radicados" value="{{route('radicados.datatable.cliente', $contacto->nit)}}">
							<div class="table-responsive mt-3">
								<table class="text-center table table-light table-striped table-hover" id="table-show-radicados" style="width: 100%; border: 1px solid #e9ecef;">
									<thead class="thead-light">
										<th>Radicado</th>
										<th>Fecha</th>
										<th>Servicio</th>
										<th>Estatus</th>
										<th>Adjuntos</th>
									</thead>
									<tbody>

									</tbody>
								</table>
							</div>
						</div>
						<div class="tab-pane fade" id="crm-history" role="tabpanel" aria-labelledby="crm-history-tab">
							<input type="hidden" id="url-show-crm-history" value="{{route('cartera.crm.contacto', $contacto->id)}}">
							<div class="table-responsive mt-3">
								<table class="text-center table table-light table-striped table-hover" id="table-show-crm-history" style="width: 100%; border: 1px solid #e9ecef;">
									<thead class="thead-light">
										<th>Codigo</th>
										<th>Fecha</th>
										<th>Estado</th>
										<th>Estatus</th>
										<th>Información</th>
									</thead>
									<tbody>

									</tbody>
								</table>
							</div>
						</div>
					@endif
				@endif
				<div class="tab-pane fade {{ $contacto->usado()==0?'show active':'' }}" id="arcadj" role="tabpanel" aria-labelledby="arcadj-tab">
					<div class="row mt-3">
	                    @if($contacto->contract('true') != 'N/A')
							@if(!$contrato->adjunto_a || !$contrato->adjunto_b || !$contrato->adjunto_c || !$contrato->adjunto_d)
							<div class="col-md-2 mb-2 text-center">
								<div class="card card-adj">
								    <div class="card-body" style="border: 1px solid #28A745;border-radius: 0.25rem;padding: 1.88rem 0.88rem;">
								    	<h3 class="card-title text-success font-weight-bold">Agregar Adjunto</h3>
								    	<a href="{{ route('asignaciones.edit',$contacto->id )}}" class="btn btn-success btn-sm btn-icons"><i class="fas fa-plus"></i></a>
								    </div>
								</div>
							</div>
							@endif
						@endif

						@if($contacto->firma_isp)
							<div class="col-md-2 mb-2 text-center">
								<div class="card card-adj">
								    <div class="card-body" style="border: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};border-radius: 0.25rem;padding: 1.88rem 0.88rem;">
								    	<h3 class="card-title">Contrato Digital</h3>
								    	<a href="{{ route('asignaciones.imprimir',$id)}}" target="_blank" class="btn btn-outline-success btn-sm btn-icons"><i class="fas fa-eye"></i></a>
								    </div>
								</div>
							</div>
						@endif
						@if($contacto->contract('true') != 'N/A')
							@if($contrato->adjunto_a)
							<div class="col-md-2 mb-2 text-center" id="div_adjunto_a">
								<div class="card card-adj">
								    <div class="card-body" style="border: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};border-radius: 0.25rem;">
								    	<h3 class="card-title">{{ $contrato->referencia_a }}</h3>
								    	<a href="{{asset('/adjuntos/documentos/'.$contrato->adjunto_a)}}" target="_blank" class="btn btn-outline-success btn-sm btn-icons"><i class="fas fa-eye"></i></a>
								    	<a href="javascript:eliminar('contratos','adjunto_a','{{$contrato->referencia_a}}','{{$contrato->id}}')" class="btn btn-outline-danger btn-sm btn-icons"><i class="fas fa-times"></i></a>
								    </div>
								</div>
							</div>
							@endif
							@if($contrato->adjunto_b)
							<div class="col-md-2 mb-2 text-center" id="div_adjunto_b">
								<div class="card card-adj">
								    <div class="card-body" style="border: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};border-radius: 0.25rem;">
								    	<h3 class="card-title">{{ $contrato->referencia_b }}</h3>
								    	<a href="{{asset('/adjuntos/documentos/'.$contrato->adjunto_b)}}" target="_blank" class="btn btn-outline-success btn-sm btn-icons"><i class="fas fa-eye"></i></a>
								    	<a href="javascript:eliminar('contratos','adjunto_b','{{$contrato->referencia_b}}','{{$contrato->id}}')" class="btn btn-outline-danger btn-sm btn-icons"><i class="fas fa-times"></i></a>
								    </div>
								</div>
							</div>
							@endif
							@if($contrato->adjunto_c)
							<div class="col-md-2 mb-2 text-center" id="div_adjunto_c">
								<div class="card card-adj">
								    <div class="card-body" style="border: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};border-radius: 0.25rem;">
								    	<h3 class="card-title">{{ $contrato->referencia_c }}</h3>
								    	<a href="{{asset('/adjuntos/documentos/'.$contrato->adjunto_c)}}" target="_blank" class="btn btn-outline-success btn-sm btn-icons"><i class="fas fa-eye"></i></a>
								    	<a href="javascript:eliminar('contratos','adjunto_c','{{$contrato->referencia_c}}','{{$contrato->id}}')" class="btn btn-outline-danger btn-sm btn-icons"><i class="fas fa-times"></i></a>
								    </div>
								</div>
							</div>
							@endif
							@if($contrato->adjunto_d)
							<div class="col-md-2 mb-2 text-center" id="div_adjunto_d">
								<div class="card card-adj">
								    <div class="card-body" style="border: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};border-radius: 0.25rem;">
								    	<h3 class="card-title">{{ $contrato->referencia_d }}</h3>
								    	<a href="{{asset('/adjuntos/documentos/'.$contrato->adjunto_d)}}" target="_blank" class="btn btn-outline-success btn-sm btn-icons"><i class="fas fa-eye"></i></a>
								    	<a href="javascript:eliminar('contratos','adjunto_d','{{$contrato->referencia_d}}','{{$contrato->id}}')" class="btn btn-outline-danger btn-sm btn-icons"><i class="fas fa-times"></i></a>
								    </div>
								</div>
							</div>
							@endif
						@endif
						@if($contacto->documento)
						<div class="col-md-2 mb-2 text-center">
							<div class="card card-adj">
							    <div class="card-body" style="border: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};border-radius: 0.25rem;">
							    	<h3 class="card-title">Documento Asignación</h3>
							    	<a href="{{asset('/adjuntos/documentos/'.$contacto->documento)}}" target="_blank" class="btn btn-outline-success btn-sm btn-icons"><i class="fas fa-eye"></i></a>
							    </div>
							</div>
						</div>
						@endif
						@if($contacto->imgA)
						<div class="col-md-2 mb-2 text-center" id="div_imgA">
							<div class="card card-adj">
							    <div class="card-body" style="border: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};border-radius: 0.25rem;">
							    	<h3 class="card-title">{{ auth()->user()->empresa()->campo_a }}</h3>
							    	<a href="{{asset('/adjuntos/documentos/'.$contacto->imgA)}}" target="_blank" class="btn btn-outline-success btn-sm btn-icons"><i class="fas fa-eye"></i></a>
							    	<a href="javascript:eliminar('contactos','imgA','{{auth()->user()->empresa()->campo_a}}','{{$id}}')" class="btn btn-outline-danger btn-sm btn-icons"><i class="fas fa-times"></i></a>
							    </div>
							</div>
						</div>
						@endif
						@if($contacto->imgB)
						<div class="col-md-2 mb-2 text-center" id="div_imgB">
							<div class="card card-adj">
							    <div class="card-body" style="border: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};border-radius: 0.25rem;">
							    	<h3 class="card-title">{{ auth()->user()->empresa()->campo_b }}</h3>
							    	<a href="{{asset('/adjuntos/documentos/'.$contacto->imgB)}}" target="_blank" class="btn btn-outline-success btn-sm btn-icons"><i class="fas fa-eye"></i></a>
							    	<a href="javascript:eliminar('contactos','imgB','{{auth()->user()->empresa()->campo_b}}','{{$id}}')" class="btn btn-outline-danger btn-sm btn-icons"><i class="fas fa-times"></i></a>
							    </div>
							</div>
						</div>
						@endif
						@if($contacto->imgC)
						<div class="col-md-2 mb-2 text-center" id="div_imgC">
							<div class="card card-adj">
							    <div class="card-body" style="border: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};border-radius: 0.25rem;">
							    	<h3 class="card-title">{{ auth()->user()->empresa()->campo_c }}</h3>
							    	<a href="{{asset('/adjuntos/documentos/'.$contacto->imgC)}}" target="_blank" class="btn btn-outline-success btn-sm btn-icons"><i class="fas fa-eye"></i></a>
							    	<a href="javascript:eliminar('contactos','imgC','{{auth()->user()->empresa()->campo_c}}','{{$id}}')" class="btn btn-outline-danger btn-sm btn-icons"><i class="fas fa-times"></i></a>
							    </div>
							</div>
						</div>
						@endif
						@if($contacto->imgD)
						<div class="col-md-2 mb-2 text-center" id="div_imgD">
							<div class="card card-adj">
							    <div class="card-body" style="border: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};border-radius: 0.25rem;">
							    	<h3 class="card-title">{{ auth()->user()->empresa()->campo_d }}</h3>
							    	<a href="{{asset('/adjuntos/documentos/'.$contacto->imgD)}}" target="_blank" class="btn btn-outline-success btn-sm btn-icons"><i class="fas fa-eye"></i></a>
							    	<a href="javascript:eliminar('contactos','imgD','{{auth()->user()->empresa()->campo_d}}','{{$id}}')" class="btn btn-outline-danger btn-sm btn-icons"><i class="fas fa-times"></i></a>
							    </div>
							</div>
						</div>
						@endif
						@if($contacto->imgE)
						<div class="col-md-2 mb-2 text-center" id="div_imgE">
							<div class="card card-adj">
							    <div class="card-body" style="border: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};border-radius: 0.25rem;">
							    	<h3 class="card-title">{{ auth()->user()->empresa()->campo_e }}</h3>
							    	<a href="{{asset('/adjuntos/documentos/'.$contacto->imgE)}}" target="_blank" class="btn btn-outline-success btn-sm btn-icons"><i class="fas fa-eye"></i></a>
							    	<a href="javascript:eliminar('contactos','imgE','{{auth()->user()->empresa()->campo_e}}','{{$id}}')" class="btn btn-outline-danger btn-sm btn-icons"><i class="fas fa-times"></i></a>
							    </div>
							</div>
						</div>
						@endif
						@if($contacto->imgF)
						<div class="col-md-2 mb-2 text-center" id="div_imgF">
							<div class="card card-adj">
							    <div class="card-body" style="border: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};border-radius: 0.25rem;">
							    	<h3 class="card-title">{{ auth()->user()->empresa()->campo_f }}</h3>
							    	<a href="{{asset('/adjuntos/documentos/'.$contacto->imgF)}}" target="_blank" class="btn btn-outline-success btn-sm btn-icons"><i class="fas fa-eye"></i></a>
							    	<a href="javascript:eliminar('contactos','imgF','{{auth()->user()->empresa()->campo_f}}','{{$id}}')" class="btn btn-outline-danger btn-sm btn-icons"><i class="fas fa-times"></i></a>
							    </div>
							</div>
						</div>
						@endif
						@if($contacto->imgG)
						<div class="col-md-2 mb-2 text-center" id="div_imgG">
							<div class="card card-adj">
							    <div class="card-body" style="border: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};border-radius: 0.25rem;">
							    	<h3 class="card-title">{{ auth()->user()->empresa()->campo_g }}</h3>
							    	<a href="{{asset('/adjuntos/documentos/'.$contacto->imgG)}}" target="_blank" class="btn btn-outline-success btn-sm btn-icons"><i class="fas fa-eye"></i></a>
							    	<a href="javascript:eliminar('contactos','imgG','{{auth()->user()->empresa()->campo_g}}','{{$id}}')" class="btn btn-outline-danger btn-sm btn-icons"><i class="fas fa-times"></i></a>
							    </div>
							</div>
						</div>
						@endif
						@if($contacto->imgH)
						<div class="col-md-2 mb-2 text-center" id="div_imgH">
							<div class="card card-adj">
							    <div class="card-body" style="border: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};border-radius: 0.25rem;">
							    	<h3 class="card-title">{{ auth()->user()->empresa()->campo_h }}</h3>
							    	<a href="{{asset('/adjuntos/documentos/'.$contacto->imgH)}}" target="_blank" class="btn btn-outline-success btn-sm btn-icons"><i class="fas fa-eye"></i></a>
							    	<a href="javascript:eliminar('contactos','imgH','{{auth()->user()->empresa()->campo_h}}','{{$id}}')" class="btn btn-outline-danger btn-sm btn-icons"><i class="fas fa-times"></i></a>
							    </div>
							</div>
						</div>
						@endif
                        @if($contacto->adjunto_audio)
							<div class="col-md-2 mb-2 text-center" id="div_adjunto">
								<div class="card card-adj">
								    <div class="card-body" style="border: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};border-radius: 0.25rem;">
								    	<h3 class="card-title">Audio</h3>
								    	<a href="{{asset('/adjuntos/documentos/'.$contacto->adjunto_audio)}}" target="_blank" class="btn btn-outline-success btn-sm btn-icons"><i class="fas fa-eye"></i></a>
								    	{{-- <a href="javascript:eliminar('contratos','adjunto_c','{{$contrato->referencia_c}}','{{$contrato->id}}')" class="btn btn-outline-danger btn-sm btn-icons"><i class="fas fa-times"></i></a> --}}
								    </div>
								</div>
							</div>
							@endif
					</div>
				</div>
			</div>
		</div>
	</div>

    <div class="modal fade" id="promesaPago" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">GENERAR PROMESA DE PAGO</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div id="div_promesa"></div>
            </div>
        </div>
    </div>

	@if($contacto->contract('true') != 'N/A')
	    <div class="modal fade" id="modalAdjunto" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	    	<div class="modal-dialog modal-dialog-centered">
	    		<div class="modal-content">
	    			<div class="modal-header">
	    				<h4 class="modal-title">ADJUNTOS RELACIONADOS AL CONTRATO</h4>
	    				<button type="button" class="close" data-dismiss="modal">&times;</button>
	    			</div>
	    			<div class="modal-body">
	    				<form method="post" action="{{ route('contratos.carga_adjuntos', $contrato->id ) }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-contrato" enctype="multipart/form-data">
					            @csrf
					        <input name="contacto_id" type="hidden" value="{{ $contacto->id }}">
					        <div class="row">
					        	@if(!$contrato->adjunto_a)
					            <div class="col-md-6 form-group">
					                <label class="control-label">Referencia A</label>
					                <input type="text" class="form-control" id="referencia_a" name="referencia_a" value="{{$contrato->referencia_a}}">
					                <span style="color: red;">
					                    <strong>{{ $errors->first('referencia_a') }}</strong>
					                </span>
					            </div>
					            <div class="col-md-6 form-group">
					                <label class="control-label">Adjunto A</label>
					                <input type="file" class="form-control"  id="adjunto_a" name="adjunto_a" value="{{$contrato->adjunto_a}}" accept=".jpg, .jpeg, .png, .pdf, .JPG, .JPEG, .PNG, .PDF">
					                <span style="color: red;">
					                    <strong>{{ $errors->first('adjunto_a') }}</strong>
					                </span>
					            </div>
					            @endif

					            @if(!$contrato->adjunto_b)
					            <div class="col-md-6 form-group">
					                <label class="control-label">Referencia B</label>
					                <input type="text" class="form-control" id="referencia_b" name="referencia_b" value="{{$contrato->referencia_b}}">
					                <span style="color: red;">
					                    <strong>{{ $errors->first('referencia_b') }}</strong>
					                </span>
					            </div>
					            <div class="col-md-6 form-group">
					                <label class="control-label">Adjunto B</label>
					                <input type="file" class="form-control"  id="adjunto_b" name="adjunto_b" value="{{$contrato->adjunto_b}}" accept=".jpg, .jpeg, .png, .pdf, .JPG, .JPEG, .PNG, .PDF">
					                <span style="color: red;">
					                    <strong>{{ $errors->first('adjunto_b') }}</strong>
					                </span>
					            </div>
					            @endif

					            @if(!$contrato->adjunto_c)
					            <div class="col-md-6 form-group">
					                <label class="control-label">Referencia C</label>
					                <input type="text" class="form-control" id="referencia_c" name="referencia_c" value="{{$contrato->referencia_c}}">
					                <span style="color: red;">
					                    <strong>{{ $errors->first('referencia_c') }}</strong>
					                </span>
					            </div>
					            <div class="col-md-6 form-group">
					                <label class="control-label">Adjunto C</label>
					                <input type="file" class="form-control"  id="adjunto_c" name="adjunto_c" value="{{$contrato->adjunto_c}}" accept=".jpg, .jpeg, .png, .pdf, .JPG, .JPEG, .PNG, .PDF">
					                <span style="color: red;">
					                    <strong>{{ $errors->first('adjunto_c') }}</strong>
					                </span>
					            </div>
					            @endif

					            @if(!$contrato->adjunto_d)
					            <div class="col-md-6 form-group">
					                <label class="control-label">Referencia D</label>
					                <input type="text" class="form-control" id="referencia_d" name="referencia_d" value="{{$contrato->referencia_d}}">
					                <span style="color: red;">
					                    <strong>{{ $errors->first('referencia_d') }}</strong>
					                </span>
					            </div>
					            <div class="col-md-6 form-group">
					                <label class="control-label">Adjunto D</label>
					                <input type="file" class="form-control"  id="adjunto_d" name="adjunto_d" value="{{$contrato->adjunto_d}}" accept=".jpg, .jpeg, .png, .pdf, .JPG, .JPEG, .PNG, .PDF">
					                <span style="color: red;">
					                    <strong>{{ $errors->first('adjunto_d') }}</strong>
					                </span>
					            </div>
					            @endif
					        </div>

					        <hr>

					        <div class="row">
					            <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
					                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cancelar</button>
					                <button type="submit" class="btn btn-success">Subir Adjuntos</button>
					            </div>
					        </div>
					    </form>
	    			</div>
	    		</div>
	    	</div>
	    </div>
	@endif

    <input type="hidden" value="{{$contacto->id}}" id="idContacto">

	<!-- Modal notas -->
	<div class="modal fade" id="modalSaldo" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"></div>
	<!-- Modal historial saldos -->
	<div class="modal fade" id="modalHistorial" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true"></div>
@endsection

@section('scripts')
    <script type="text/javascript">
        $(document).ready(function() {

        });

        function eliminar(tipo, archivo, referencia, id){
        	swal({
        		title: '¿Está seguro de eliminar el archivo '+referencia+' del sistema?',
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
        				var url = '/software/empresa/'+tipo+'/'+id+'/'+archivo+'/eliminar';
        			}else{
        				var url = '/empresa/'+tipo+'/'+id+'/'+archivo+'/eliminar';
        			}

        			$.ajax({
        				url: url,
        				beforeSend: function(){
        					cargando(true);
        				},
        				success: function(data){
        					//data=JSON.parse(data);
        					if(data.success == true){
        						$("#div_"+archivo).remove();
        					}
        					Swal.fire({
        						type:  data.type,
        						title: data.title,
        						text:  data.text,
        						showConfirmButton: false,
        						timer: 5000
        					})
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

		function editFechaIsp(){
			$('#modal-fecha-isp').modal('show');
		}

		function updateFechaIsp(){
			var token = $('meta[name="csrf-token"]').attr('content');

			$.post($('#form-fechaIsp').attr('action'), {'_token': token, 'fecha_isp': $('#fecha_isp').val() }, function(response){
				$('#fecha-isp-date').html(response.fecha_isp);
				$('#modal-fecha-isp').modal('hide');
			});
		}

		function modificarSaldo(contactoId){
			var valor;
	  		var tipo;

			if (window.location.pathname.split("/")[1] === "software") {
			var url='/software/empresa';
            }else{
            var url = '/empresa';
            }

		  $.ajax({
			  url: url+`/contactos/editsaldo/${contactoId}`,
			  method: 'GET',
			  headers: {
				  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			  },
			  success: function(response) {
				  if (response) {
					  contacto = response;

					  $('#modalSaldo').html('');
					  $('#modalSaldo').append(`<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
							  <div class="modal-content">
								  <div class="modal-header">
									  <h5 class="modal-title" id="exampleModalLabel">Editar saldo a favor</h5>
									  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
										  <span aria-hidden="true">&times;</span>
									  </button>
								  </div>
								  <div class="modal-body">
									<p>
                                        Ingrese el saldo a favor sin puntos,
                                        recuerde que al modificar le saldo a favor de esta manera no afectará ningun otro modulo.
                                    </p>
									  <div class="form-group">
										<label for="ancho">saldo</label>
					  					<input type="text" name="saldo_favor" id="saldo_favor" class="form-control" value="${contacto.saldo_favor}">
									  </div>

									  <div id="custom-target"></div>
								  </div>
								  <div class="modal-footer">
									  <a  class="btn btn-secondary" data-dismiss="modal">Cerrar</a>
									  <a  class="btn btn-primary text-white" onclick="guardarSaldo(${contactoId})">Guardar</a>
								  </div>
							  </div>
							  </div>`);
				$('#modalSaldo').modal('show');
				}
			  }
		  });
		}

		function guardarSaldo(contactoId){
			var saldo_favor = $('#saldo_favor').val();
		  	var contactoId = contactoId;

			if (window.location.pathname.split("/")[1] === "software") {
			var url='/software/empresa';
            }else{
            var url = '/empresa';
            }

		  $.ajax({
			url: url+`/contactos/storesaldo`,
			headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
			method: 'POST',
			beforeSend: function() {
			  cargando(true);
			},
			headers: {
			  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			data: {
			  saldo_favor: saldo_favor,
			  contactoId: contactoId,
			},
			success: function(response) {
			  cargando(false);
			  if (response) {
				document.getElementById("textsaldofavor").innerHTML = "$" + response.saldo_favor;
				Swal.fire({
					position: 'top-center',
					type: 'success',
					title: 'Saldo a favor actualizado',
					showConfirmButton: false,
					timer: 2500
                })
			  }
			  $('#modalSaldo').modal('hide');
			}
		  });
		}

		function historialSaldos(contactoId){
			if (window.location.pathname.split("/")[1] === "software") {
			var url='/software/empresa';
            }else{
            var url = '/empresa';
            }


			$.ajax({
			  url: url+`/contactos/historialsaldo/${contactoId}`,
			  method: 'GET',
			  headers: {
				  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			  },
			  success: function(response) {
				  if (response) {
					  contacto = response;
						let html = "";
					  for (var i = 0, len = response.length; i < len; i++) {
						html+=`<li>
							Fecha: ${response[i].fecha} el usuario ${response[i].nombre} ${response[i].accion}
							</li>`;
					  }

					  $('#modalHistorial').html('');
					  $('#modalHistorial').append(`<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
							  <div class="modal-content">
								  <div class="modal-header">
									  <h5 class="modal-title" id="exampleModalLabel">Historial de cambios saldo a favor</h5>
									  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
										  <span aria-hidden="true">&times;</span>
									  </button>
								  </div>
								  	<div class="modal-body">
										<ul>
											${html}
										</ul>
									</div>

									  <div id="custom-target"></div>
									  <div class="modal-footer">
										  <a  class="btn btn-secondary" data-dismiss="modal">Cerrar</a>
									  </div>
								  </div>
							  </div>
							  </div>`);
				$('#modalHistorial').modal('show');
				}
			  }
		  });

		}
    </script>
@endsection
