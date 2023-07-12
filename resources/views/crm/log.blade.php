@extends('layouts.app')

@section('boton')
    <a href="{{ route('crm.show',$crm->id )}}"  class="btn btn-danger btn-sm" title="Regresar"><i class="fas fa-step-backward"></i></i> Regresar</a>
    <a href="javascript:getDataTable()" class="btn btn-success btn-sm my-1"><i class="fas fa-sync-alt"></i> Actualizar</a>
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
			}, 10000);
		</script>
	@endif

	<div class="row card-description">
    	<div class="col-md-12">
    		<div class="table-responsive">
    			<table class="table table-striped table-bordered table-sm info">
    				<tbody>
    					<tr class="text-center">
    						<th class="text-center">CLIENTE</th>
    						<th class="text-center">IDENTIFICACIÓN</th>
    						<th class="text-center">CELULAR</th>
    						<th class="text-center">ESTADO DEL CRM</th>
    					</tr>
    					<tr class="text-center">
    						<td class="text-center">{{$crm->cliente()->nombre}}</td>
    						<td class="text-center">{{$crm->cliente()->tip_iden('true')}} {{$crm->cliente()->nit}}</td>
    						<td class="text-center">{{$crm->cliente()->celular}}</td>
    						<td class="text-center font-weight-bold text-{{$crm->estado('true')}}">{{$crm->estado()}}</td>
    					</tr>
    				</tbody>
    			</table>
    		</div>
    	</div>
    </div>
	
	<div class="row card-description">
    	<div class="col-md-12">
    		<table class="table table-striped table-hover w-100" id="tabla-crm">
    			<thead class="thead-dark">
    				<tr>
    					<th>Ejecutado por</th>
    					<th>Fecha / Hora</th>
    					<th>Acción</th>
    					<th>Información</th>
    				</tr>
    			</thead>
    		</table>
    	</div>
    </div>
@endsection

@section('scripts')
<script>
    var tabla = null;
    window.addEventListener('load',
    function() {
		$('#tabla-crm').DataTable({
			responsive: true,
			serverSide: true,
			processing: true,
			searching: false,
			language: {
				'url': '/vendors/DataTables/es.json'
			},
			order: [
				[1, "desc"]
			],
			"pageLength": {{ Auth::user()->empresa()->pageLength }},
			ajax: '{{url("/logsCRM/$crm->id")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
			columns: [
			    { data: 'created_by' },
			    { data: 'created_at' },
				{ data: 'accion' },
				{ data: 'informacion' },
			]
		});

        tabla = $('#tabla-crm');
    });

	function getDataTable() {
		tabla.DataTable().ajax.reload();
	}
</script>
@endsection
