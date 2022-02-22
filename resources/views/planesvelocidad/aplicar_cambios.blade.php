@extends('layouts.app')

@section('boton')
    <form action="{{route('planes-velocidad.aplicando-cambios',$plan->id)}}" method="get" class="delete_form" style="margin:  0;display: inline-block;" id="aplicar_cambios-{{$plan->id}}">
    	@csrf
    </form>

    <a href="{{ route('planes-velocidad.index')}}"  class="btn btn-danger btn-sm" title="Regresar"><i class="fas fa-times"></i></i> Cancelar</a>
    <button class="btn btn-success btn-sm disabled" disabled type="button" title="Aplicar Cambios" onclick="confirmar('aplicar_cambios-{{$plan->id}}', '¿Está seguro que desea aplicar los cambios a los contratos en la Mikrotik?', '');"><i class="fas fa-check"></i> Aplicar Cambios</button>
@endsection

@section('style')
<style>
	#tabla-contratos > tbody > tr > td:nth-child(1) > span{
		display: none;
	}
</style>
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
			}, 10000);
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
			}, 10000);
		</script>
	@endif
	
	<div class="row card-description">
    	<div class="col-md-12">
    		<table class="table table-striped table-hover w-100" id="tabla-contratos">
    			<thead class="thead-dark">
    				<tr>
    					<th width="90px">Contrato</th>
    					<th>Cliente</th>
    					<th>Plan</th>
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

		$('#tabla-contratos').DataTable({
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
			ajax: '{{url("contratos/p-$plan->id")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
			columns: [
			    { data: 'nro' },
			    { data: 'client_id' },
			    { data: 'plan' },
			    { data: 'ip' },
			]
		});

        tabla = $('#tabla-contratos');
    });

	function getDataTable() {
		tabla.DataTable().ajax.reload();
	}
</script>
@endsection
