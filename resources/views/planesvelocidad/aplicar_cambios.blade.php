@extends('layouts.app')

@section('boton')
    <form action="{{route('planes-velocidad.aplicando-cambios',$plan->id)}}" method="get" class="delete_form" style="margin:  0;display: inline-block;" id="aplicar_cambios-{{$plan->id}}">
    	@csrf
    </form>

    <a href="{{ route('planes-velocidad.index')}}"  class="btn btn-danger btn-sm" title="Regresar"><i class="fas fa-times"></i></i> Cancelar</a>
    <a href="javascript:aplicar_cambios()" class="btn btn-success btn-sm" title="Aplicar Cambios"><i class="fas fa-check"></i></i> Aplicar Cambios</a>
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

    <input type="hidden" value="0" id="contador_s">
    <input type="hidden" value="0" id="contador_f">
    <input type="hidden" value="0" id="contador_t">
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

	function aplicar_cambios() {
		swal({
			title: '¿Desea aplicar los cambios a todos los contratos en la mikrotik que poseen este plan?',
			text: 'ESTE PROCESO PUEDE DEMORAR UNOS MINUTOS',
			type: 'question',
			showCancelButton: true,
			confirmButtonColor: '#00ce68',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Aceptar',
			cancelButtonText: 'Cancelar',
		}).then((result) => {
			if (result.value) {
				cargando(true);
				var contador_t = $("#contador_t").val($('#tabla-contratos').DataTable().data().count());

				$('#tabla-contratos tbody tr td.sorting_1 a strong').each(function() {
					var nro = $(this).text();

					if (window.location.pathname.split("/")[1] === "software") {
						var url='/software/empresa/planes-velocidad/'+nro+'/aplicando-cambios';
					}else{
						var url = '/empresa/planes-velocidad/'+nro+'/aplicando-cambios';
					}

					$.ajax({
	                    url: url,
	                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
	                    method: 'get',
	                    success: function(data){
	                        if(data.success){
	                        	var opt = parseInt($("#contador_s").val())+parseInt(1);
	                        	$("#contador_s").val(opt);
	                        }else{
	                        	var opt = parseInt($("#contador_f").val())+parseInt(1);
	                        	$("#contador_f").val(opt);
	                        }
	                        verificar();
	                    },
	                    error: function(data){

	                    }
	                });
				});
			}
		})
	}

	function verificar() {
		var total = parseInt($("#contador_s").val()) + parseInt($("#contador_f").val());
		var opt   = parseInt($("#contador_t").val());
		if(total == opt){
			Swal.fire({
				type: 'success',
				title: 'PROCESO DE ACTUALIZACIÓN FINALIZADO',
				showConfirmButton: false
			});
			cargando(false);
		}
	}
</script>
@endsection
