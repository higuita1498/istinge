@extends('layouts.app')

@section('style')
<style>
    .bg-th{
        background: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}} !important;
        border-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}} !important;
        color: #fff !important;
    }
    .nav-tabs .nav-link {
        font-size: 1em;
    }
    .nav-tabs .nav-link.active, .nav-tabs .nav-item.show .nav-link {
        background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
        color: #fff!important;
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
    table.dataTable td.dataTables_empty, table.dataTable th.dataTables_empty {
        text-align: center;
        color: red;
        font-weight: 900;
    }
</style>
@endsection

@section('boton')
    <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
        <div class="btn-group" role="group">
            <button id="btnGroupDrop1" type="button" class="btn btn-dark dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Acciones del Contacto {{ $contacto->contrato }}
            </button>
            <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
            	@if($contrato)
            	<form action="{{ route('contratos.state',$contrato->id) }}" method="post" class="delete_form" style="margin:0;display: inline-block;" id="cambiar-state{{$contrato->id}}">
            		{{ csrf_field() }}
            	</form>

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
            	    @if(!$contrato && $contacto->tipo_contacto !=1)
            	    <a href="{{route('contratos.create_cliente',$id)}}" class="dropdown-item"><i class="fas fa-file-contract"></i> Crear Contrato</a>
            	    @endif
            	    @if(isset($_SESSION['permisos']['201']) && $contacto->tipo_contacto !=1)
            	        <a href="{{route('radicados.create_cliente', $id)}}" class="dropdown-item"><i class="far fa-life-ring"></i> Crear Radicado</a>
            	    @endif

            	    @if($contrato)
            	        @if(isset($_SESSION['permisos']['407']))
            	            <button @if($contrato->state == 'enabled') class="dropdown-item" title="Deshabilitar" @else class="btn btn-outline-success" title="Habilitar" @endif type="submit" onclick="confirmar('cambiar-state{{$contrato->id}}', '¿Estas seguro que deseas cambiar el estatus del contrato?', '');"><i class="fas fa-file-signature"></i>@if($contrato->state == 'enabled') Deshabilitar Contrato @else Habilitar Contrato @endif</button>
            	        @endif
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
							<th class="bg-th text-center" width="20%">DATOS GENERALES {{$contacto->tipo_contacto==0?'DEL CLIENTE':'DEL PROVEEDOR'}}</th>
							<th class="bg-th text-center"></th></th>
						</tr>
						@if($contacto->serial_onu)
						<tr>
							<th>Serial ONU</th>
							<td>{{$contacto->serial_onu}}</td>
						</tr>
						@endif
						<tr>
							<th>Nombre</th>
							<td>{{$contacto->nombre}}</td>
						</tr>
						<tr>
							<th>Tipo de Identificación</th>
							<td>{{ $contacto->identificacion }}</td>
						</tr>
						<tr>
							<th>Identificación</th>
							<td>{{$contacto->nit}}</td>
						</tr>
						@if($contacto->tip_iden == 6)
						<tr>
							<th>DV</th>
							<td>{{$contacto->dv}}</td>
						</tr>
						@endif
						@if($contacto->telefono1)
						<tr>
							<th>Teléfono</th>
							<td>{{$contacto->telefono1}}</td>
						</tr>
						@endif
						@if($contacto->telefono2)
						<tr>
							<th>Teléfono 2</th>
							<td>{{$contacto->telefono2}}</td>
						</tr>
						@endif
						@if($contacto->fax)
						<tr>
							<th>Fax</th>
							<td>{{$contacto->fax}}</td>
						</tr>
						@endif
						@if($contacto->celular)
						<tr>
							<th>Celular</th>
							<td>{{$contacto->celular}}</td>
						</tr>
						@endif
						@if($contacto->direccion)
						<tr>
							<th>Dirección</th>
							<td>{{$contacto->direccion}}</td>
						</tr>
						@endif
						@if($contacto->barrio)
						<tr>
							<th>Barrio</th>
							<td>{{$contacto->barrio}}</td>
						</tr>
						@endif
						@if($contacto->email)
						<tr>
							<th>Correo Electrónico</th>
							<td>{{$contacto->email}}</td>
						</tr>
						@endif
						@if($contacto->firma_isp)
						<tr>
							<th>Fecha de la firma del Contrato</th>
							<td>{{date('d-m-Y', strtotime($contacto->fecha_isp))}}</strong></a></td>
						</tr>
						<tr>
							<th>Asignación de Contrato Digital</th>
							<td><a href="{{ route('asignaciones.imprimir',$id)}}" target="_blank"><strong>Ver Documento</strong></a></td>
						</tr>
						@if($contacto->imgA || $contacto->imgB || $contacto->imgC || $contacto->imgD)
						<tr>
							<th>Archivos Adjuntos</th>
							<td>
								@if($contacto->imgA)
								<a href="{{asset('../adjuntos/documentos/'.$contacto->imgA)}}" target="_blank"><strong>Ver Adjunto A</strong></a>
								@endif
								@if($contacto->imgB)
								| <a href="{{asset('../adjuntos/documentos/'.$contacto->imgB)}}" target="_blank"><strong>Ver Adjunto B</strong></a>
								@endif
								@if($contacto->imgC)
								| <a href="{{asset('../adjuntos/documentos/'.$contacto->imgC)}}" target="_blank"><strong>Ver Adjunto C</strong></a>
								@endif
								@if($contacto->imgD)
								| <a href="{{asset('../adjuntos/documentos/'.$contacto->imgD)}}" target="_blank"><strong>Ver Adjunto D</strong></a>
								@endif
							</td>
						</tr>
						@endif




						@endif
						</tbody>
				</table>

				@if($contrato)
				<table class="table table-striped table-bordered table-sm info mt-4">
					<tbody>
						<tr>
							<th class="bg-th" width="20%"><strong>CONTRATO ASOCIADO</strong></th>
							<th class="bg-th"></th>
						</tr>
						@if($contrato->nro)
						<tr>
							<th>N° Contrato</th>
							<td><a href="{{ route('contratos.show',$contrato->id )}}"><strong>{{ $contrato->nro }}</strong></a></td>
						</tr>
						@endif
						@if($contrato->grupo_corte)
						<tr>
							<th>Grupo de Corte</th>
							<td><a href="{{ route('grupos-corte.show',$contrato->grupo_corte()->id )}}" target="_blank"><strong>{{ $contrato->grupo_corte()->nombre }}</strong></a> (CORTE {{ $contrato->grupo_corte()->fecha_corte }} - SUSPENSIÓN {{ $contrato->grupo_corte()->fecha_suspension }})</td>
						</tr>
						@endif
						@if($contrato->state)
						<tr>
							<th>Estado del Contrato</th>
							<td>
							    <strong class="text-{{$contrato->status('true')}}">{{$contrato->status()}}</strong>
							</td>
						</tr>
						@endif
						@if($contrato->ip)
						<tr>
							<th>Dirección IP</th>
							<td>
							    {{$contrato->ip}}
							</td>
						</tr>
						@endif
						@if($contrato->plan()->name)
						<tr>
							<th>Plan Contratado</th>
							<td>{{$contrato->plan()->name}}</td>
						</tr>
						@endif
					</tbody>
				</table>
				@endif
			</div>
		</div>
	</div>

	@if($contacto->usado()>0)
	<div class="row card-description">
		<div class="col-md-12">
			<ul class="nav nav-pills" id="myTab" role="tablist">
				<li class="nav-item">
					<a class="nav-link active" id="profile-tab" data-toggle="tab" href="#facturas_venta" role="tab" aria-controls="facturas_venta" aria-selected="false">Facturas Generadas</a>
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
				<li class="nav-item">
					<a class="nav-link" id="radicad-tab" data-toggle="tab" href="#radicad" role="tab" aria-controls="radicad" aria-selected="false">Radicados</a>
				</li>
			</ul>
			<hr style="border-top: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}; margin: .5rem 0rem;">
			<div class="tab-content fact-table" id="myTabContent">
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
				<div class="tab-pane fade" id="radicad" role="tabpanel" aria-labelledby="radicad-tab">
					<input type="hidden" id="url-show-radicados" value="{{route('radicados.datatable.cliente', $contacto->nit)}}">
					<div class="table-responsive mt-3">
						<table class="text-center table table-light table-striped table-hover" id="table-show-radicados" style="width: 100%; border: 1px solid #e9ecef;">
							<thead class="thead-light">
								<th>Radicado</th>
								<th>Fecha</th>
								<th>Servicio</th>
								<th>Estatus</th>
							</thead>
							<tbody>

							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
    @endif

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

    <input type="hidden" value="{{$contacto->id}}" id="idContacto">
@endsection
