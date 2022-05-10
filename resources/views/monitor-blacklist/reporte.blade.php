@extends('layouts.app')

@section('boton')
    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
	        <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
	    </div>
	@else
    <a href="{{ route('monitor-blacklist.index')}}"  class="btn btn-danger" title="Regresar"><i class="fas fa-step-backward"></i></i> Regresar al Listado</a>
    @endif
@endsection

@section('content')
	<style>
		body > div.container-scroller > div > div > div.content-wrapper > div > div > div > div.row.card-description > div > div > table > tbody > tr:nth-child(10) > td > img{
			width: 547px;
			height: 297px;
			border-radius: 0%;
		}
		.bg-th{
	        background: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}} !important;
	        border-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}} !important;
	        color: #fff !important;
	        text-align: center;
	    }
	    .info th {
	        text-align: center;
	    }
	    .badge-danger, .badge-success {
	    	border: 5px solid #e65251;
	    }
	</style>

	<div class="row card-description">
		<div class="col-md-12">
			<div class="table-responsive">
				<table class="table table-striped table-bordered table-sm info">
					<tbody>
    					<tr>
    						<th class="bg-th">NOMBRE</th>
    						<th class="bg-th">IP</th>
    						<th class="bg-th">LISTADO EN</th>
    						<th class="bg-th">ESTADO</th>
    					</tr>
    					<tr class="text-center">
    						<td>{{ $blacklist->nombre }}</td>
    						<td>{{ $blacklist->ip }}</td>
    						<td>{{ $blacklist->blacklisted_count }} sitio(s)</td>
    						<td><span class="badge badge-{{ $blacklist->estado('true') }}">{{ $blacklist->estado() }}</span></td>
    					</tr>
    				</tbody>
				</table>
			</div>
		</div>
		<div class="col-md-12 mt-4">
    		<div class="table-responsive">
    			<table class="table table-striped table-bordered table-sm info">
    				<tbody>
    					<tr>
    						<th class="bg-th" width="10%">RBL</th>
    						<th class="bg-th" width="20%">DELIST</th>
    					</tr>
    					@php $i = 0; @endphp
    					@foreach($response as $item)
    					    <tr>
    						    <td class="text-center">{{ $response[$i]['RBL'] }}</td>
    						    <td class="text-center"><a href="{{ $response[$i]['Delist'] }}" target="_blank">{{ $response[$i]['Delist'] }}</a></td>
    						</tr>
    					@php $i++; @endphp
    					@endforeach
    				</tbody>
    			</table>
    		</div>
    	</div>
    </div>

@endsection
