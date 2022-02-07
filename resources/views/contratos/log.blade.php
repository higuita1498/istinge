@extends('layouts.app')

@section('boton')
    <a href="{{ route('contratos.show',$contrato->id )}}"  class="btn btn-danger btn-sm" title="Regresar"><i class="fas fa-step-backward"></i></i> Regresar</a>
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
    		<table class="table table-striped table-hover w-100" id="tabla-contratos">
    			<thead class="thead-dark">
    				<tr>
    					<th>Ejecutado por</th>
    					<th>Fecha / Hora</th>
    					<th>Descripción</th>
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
			serverSide: true,
			processing: true,
			searching: false,
			language: {
				'url': '/vendors/DataTables/es.json'
			},
			order: [
				[1, "desc"]
			],
			"pageLength": 25,
			ajax: '{{url("/logs/$contrato->id")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
			columns: [
			    { data: 'created_by' },
			    { data: 'created_at' },
				{ data: 'descripcion' }
			]
		});

        tabla = $('#tabla-contratos');
    });

	function getDataTable() {
		tabla.DataTable().ajax.reload();
	}
</script>
@endsection
