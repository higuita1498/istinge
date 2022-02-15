@extends('layouts.app')

@section('boton')
    <form action="{{route('mikrotik.autorizar-ips',$mikrotik->id)}}" method="get" class="delete_form" style="margin:  0;display: inline-block;" id="ip-autorizadas-{{$mikrotik->id}}">
    	@csrf
    </form>

    <a href="{{ route('mikrotik.index')}}"  class="btn btn-danger btn-sm" title="Regresar"><i class="fas fa-step-backward"></i></i> Regresar</a>
    <a href="javascript:getDataTable()" class="btn btn-success btn-sm my-1"><i class="fas fa-sync-alt"></i> Actualizar</a>
    <button class="btn btn-warning btn-sm" type="button" title="IP's Autorizadas" onclick="confirmar('ip-autorizadas-{{$mikrotik->id}}', '¿Está seguro que desea aplicar la regla?', 'Al aplicar la regla se creara un address list en {{ $mikrotik->nombre }} tomando en cuentas las ip que esten registradas en la plataforma, de no estar declaradas esas ip quedaran sin servicio');"><i class="fas fa-project-diagram"></i> Aplicar Regla</button>
@endsection

@section('style')
<style>
	#tabla-ips > tbody > tr > td:nth-child(1) > span{
		display: none;
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
			}, 10000);
		</script>
	@endif
	
	<div class="row card-description">
    	<div class="col-md-12">
    		<table class="table table-striped table-hover w-100" id="tabla-ips">
    			<thead class="thead-dark">
    				<tr>
    					<th width="90px">Contrato</th>
    					<th>Cliente</th>
    					<th>IP</th>
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

		$('#tabla-ips').DataTable({
			responsive: true,
			serverSide: false,
			processing: true,
			searching: true,
			language: {
				'url': '/vendors/DataTables/es.json'
			},
			order: [
				[0, "desc"]
			],
			"pageLength": 25,
			ajax: '{{url("contratos/m-$mikrotik->id")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
			columns: [
			    { data: 'nro' },
			    { data: 'client_id' },
			    { data: 'ip' },
			]
		});

        tabla = $('#tabla-ips');
    });

	function getDataTable() {
		tabla.DataTable().ajax.reload();
	}
</script>
@endsection
