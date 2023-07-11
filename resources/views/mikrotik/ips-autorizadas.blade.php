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
            select: true,
            select: {
                style: 'multi',
            },
			language: {
				'url': '/vendors/DataTables/es.json'
			},
			order: [
				[0, "desc"]
			],
			"pageLength": {{ Auth::user()->empresa()->pageLength }},
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
		var contratos = [];

        var table = $('#tabla-ips').DataTable();
        var nro = table.rows('.selected').data().length;

        if(nro<=0){
            swal({
                title: 'ERROR',
                html: 'Para ejecutar esta acción, debe al menos seleccionar un contrato',
                type: 'error',
            });
            return false;
        }

        if(nro>25){
            swal({
                title: 'ERROR',
                html: 'Sólo se permite ejecutar esta acción en lotes máximos de 25 contratos y ha seleccionado '+nro,
                type: 'error',
            });
            return false;
        }

        for (i = 0; i < nro; i++) {
            contratos.push(table.rows('.selected').data()[i]['id']);
        }

        swal({
        	title: '¿Está seguro que desea aplicar la regla a '+nro+' contratos?',
            html: 'Al aplicar la regla se creara un address list en {{ $mikrotik->nombre }} tomando en cuentas las ip que estén registradas en la plataforma, de no estar declaradas esas ip quedaran sin servicio<br><br><span style="color:red;">(EL PROCESO PUEDE DEMORAR UNOS MINUTOS)</span></b>',
            type: 'question',
            showCancelButton: true,
            confirmButtonColor: '#00ce68',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Aceptar',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (result.value) {
                cargando(true);

                if (window.location.pathname.split("/")[1] === "software") {
					var url='/software/empresa/mikrotik/'+contratos+'/autorizar-ips';
				}else{
					var url = '/empresa/mikrotik/'+contratos+'/autorizar-ips';
				}

				$.ajax({
					url: url,
					headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
					method: 'GET',
					success: function(data){
						cargando(false);
                        swal({
                            title: 'PROCESO REALIZADO',
                            html: 'Exitosos: <strong>'+data.correctos+' contratos</strong><br>Fallidos: <strong>'+data.fallidos+' contratos</strong>',
                            type: 'success',
                            showConfirmButton: true,
                            confirmButtonColor: '#1A59A1',
                            confirmButtonText: 'ACEPTAR',
                        });
                        getDataTable();
					}
				});
            }
        })
	}
</script>
@endsection
