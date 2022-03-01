@extends('layouts.app')

@section('boton')
    <form action="{{route('mikrotik.autorizar-ips',$mikrotik->id)}}" method="get" class="delete_form" style="margin:  0;display: inline-block;" id="ip-autorizadas-{{$mikrotik->id}}">
    	@csrf
    </form>

    <a href="javascript:getDataTable()" class="btn btn-success btn-sm my-1"><i class="fas fa-sync-alt"></i> Actualizar</a>
    <a href="{{ route('mikrotik.index')}}"  class="btn btn-danger btn-sm" title="Regresar" id="btn_salir"><i class="fas fa-step-backward"></i></i> Regresar</a>
    <a href="javascript:aplicar_cambios()" class="btn btn-warning btn-sm" title="Aplicar Cambios" id="btn_cambios"><i class="fas fa-check"></i> Aplicar Cambios</a>
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

    <input type="hidden" value="0" id="contador_s">
    <input type="hidden" value="0" id="contador_f">
    <input type="hidden" value="0" id="contador_t">
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
	function aplicar_cambios() {
		swal({
			title: '¿Está seguro que desea aplicar la regla?',
			text: 'Al aplicar la regla se creara un address list en {{ $mikrotik->nombre }} tomando en cuentas las ip que esten registradas en la plataforma, de no estar declaradas esas ip quedaran sin servicio',
			type: 'question',
			showCancelButton: true,
			confirmButtonColor: '#00ce68',
			cancelButtonColor: '#d33',
			confirmButtonText: 'Aceptar',
			cancelButtonText: 'Cancelar',
		}).then((result) => {
			if (result.value) {
				cargando(true);
				var contador_t = $("#contador_t").val($('#tabla-ips').DataTable().data().count());

				$('#tabla-ips tbody tr td.sorting_1 a strong').each(function() {
					var nro = $(this).text();

					if (window.location.pathname.split("/")[1] === "software") {
						var url='/software/empresa/mikrotik/'+nro+'/autorizar-ips';
					}else{
						var url = '/empresa/mikrotik/'+nro+'/autorizar-ips';
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
			$("#btn_cambios").addClass('d-none');
			$("#btn_salir").text('Volver al listado');
			tabla.DataTable().ajax.reload();
		}
	}
</script>
@endsection
