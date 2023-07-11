@extends('layouts.app')

@section('style')
<style>
    .bg-th {
        background: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}!important;
        color: #fff!important;
    }
    .table th{
        font-weight: bold;
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
</style>
@endsection

@section('boton')
    @if(isset($_SESSION['permisos']['847']))
        <a href="{{route('crm.log', $crm->id)}}" class="btn btn-warning btn-sm"><i class="fas fa-clipboard-list"></i> Ver Log del CRM</a>
    @endif
@endsection

@section('content')

    <div class="row card-description">
    	<div class="col-md-12">
    		<div class="table-responsive">
    			<table class="table table-striped table-bordered table-sm info">
    				<tbody>
    					<tr>
    						<th class="bg-th" width="17.5%">DATOS DEL CLIENTE</th>
    						<th class="bg-th"></th>
    					</tr>
    					<tr>
    						<th>CLIENTE</th>
    						<td>{{$crm->cliente()->nombre}} {{$crm->cliente()->apellidos()}}</td>
    					</tr>
    					<tr>
    						<th>IDENTIFICACIÓN</th>
    						<td>{{$crm->cliente()->tip_iden('true')}} {{$crm->cliente()->nit}}</td>
    					</tr>
    					<tr>
    						<th>CELULAR</th>
    						<td>{{$crm->cliente()->celular}}</td>
    					</tr>
    				</tbody>
    			</table>
    		</div>
    	</div>
    </div>
    
    <div class="row card-description mt-3">
    	<div class="col-md-12">
    		<div class="table-responsive">
    			<table class="table table-striped table-bordered table-sm info">
    				<tbody>
    					<tr>
    						<th class="bg-th" width="17.5%">DATOS DEL CRM</th>
    						<th class="bg-th"></th>
    					</tr>
    					<tr>
    						<th>NRO</th>
    						<td>{{$crm->id}}</td>
    					</tr>
    					<tr>
    						<th>ESTADO</th>
    						<td class="font-weight-bold text-{{$crm->estado('true')}}">{{$crm->estado()}}</td>
    					</tr>
    					@if($crm->created_by)
    					<tr>
    						<th>¿ATENDIÓ LA LLAMADA?</th>
    						<td>{{$crm->llamada == 0 ? 'No':'Si'}}</td>
    					</tr>
    					<tr>
    						<th>COMPROMISO DE PAGO</th>
    						<td>{{$crm->promesa_pago == 0 ? 'No':'Si'}}</td>
    					</tr>
                        <tr>
                            <th>FACTURA</th>
                            <td><a href="{{route('facturas.show', $crm->factura_detalle()->id)}}" target="_blank">{{$crm->factura_detalle()->codigo}}</a></td>
                        </tr>
    					@if($crm->fecha_pago)
    					<tr>
    						<th>FECHA DE PAGO</th>
    						<td>{{$crm->fecha_pago}}</td>
    					</tr>
    					@endif
    					<tr>
    						<th>INFORMACIÓN</th>
    						<td style="white-space: break-spaces;text-align: justify;">@php echo $crm->informacion; @endphp</td>
    					</tr>
    					<tr>
    						<th>TIEMPO DE GESTIÓN</th>
    						<td>{{$crm->tiempo}}</td>
    					</tr>
    					<tr>
    						<th>GESTIONADO POR</th>
    						<td>{{$crm->created_by()}}</td>
    					</tr>
    					@if($crm->estado > 0)
    					<tr>
    						<th>GESTIONADO EL</th>
    						<td>{{$crm->updated_at()}}</td>
    					</tr>
    					@endif
    					@endif
    				</tbody>
    			</table>
    		</div>
    	</div>
    </div>
    
    <div class="row card-description mt-3">
	<div class="col-md-12">
		<ul class="nav nav-pills" id="myTab" role="tablist">
			<li class="nav-item">
				<a class="nav-link active" id="facturas_venta-tab" data-toggle="tab" href="#facturas_venta" role="tab" aria-controls="facturas_venta" aria-selected="true">Facturas Generadas</a>
			</li>
		</ul>
		<hr style="border-top: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}; margin: .5rem 0rem;">
		<div class="tab-content fact-table" id="myTabContent">
			<div class="tab-pane fade show active" id="facturas_venta" role="tabpanel" aria-labelledby="facturas_venta-tab">
				<input type="hidden" id="url-show-facturas" value="{{route('factura.datatable.cliente', $crm->cliente)}}">
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
		</div>
	</div>
</div>

@endsection

@section('scripts')

@endsection