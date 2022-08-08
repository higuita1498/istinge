@extends('layouts.app')
@section('boton')
    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
	        <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
	    </div>
	@else
	@if(isset($_SESSION['permisos']['283']))
	    <a href="{{route('bancos.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Banco</a>
	@endif
	<a href="javascript:abrirFiltrador()" class="btn btn-info btn-sm my-1" id="boton-filtrar"><i class="fas fa-search"></i>Filtrar</a>
	@endif
@endsection
@section('content')
@if(isset($_SESSION['permisos']['281']))
	@if(Session::has('success'))
		<div class="alert alert-success" >
			{{Session::get('success')}}
		</div>

		<script type="text/javascript">
			setTimeout(function(){
			    $('.alert').hide();
			    $('.active_table').attr('class', ' ');
			}, 50000);
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
			}, 50000);
		</script>
	@endif

	<div class="container-fluid d-none" id="form-filter">
		<fieldset>
			<legend>Filtro de Búsqueda</legend>
			<div class="card shadow-sm border-0">
				<div class="card-body pb-3 pt-2" style="background: #f9f9f9;">
					<div class="row">
						<div class="col-md-3 pl-1 pt-1 offset-md-1">
							<input type="text" placeholder="Nombre" id="nombre" class="form-control rounded">
						</div>
						<div class="col-md-3 pl-1 pt-1">
							<input type="number" placeholder="Nro Cuenta" id="nro_cta" class="form-control rounded">
						</div>
						<div class="col-md-3 pl-1 pt-1">
						    <select title="Tipo de Cuenta" class="form-control rounded selectpicker" id="tipo_cta">
						        <option value="1">Banco</option>
								<option value="2">Tarjeta de crédito</option>
								<option value="3">Efectivo</option>
								<option value="4">Punto de Venta</option>
							</select>
						</div>
						<div class="col-md-2 pl-1 pt-2 text-center">
							<a href="javascript:cerrarFiltrador()" class="btn btn-icons ml-1 btn-outline-danger rounded btn-sm p-1" title="Limpiar parámetros de busqueda"><i class="fas fa-times"></i></a>
							<a href="javascript:void(0)" id="filtrar" class="btn btn-icons btn-outline-info rounded btn-sm p-1" title="Iniciar busqueda avanzada"><i class="fas fa-search"></i></a>
						</div>
					</div>
				</div>
			</div>
		</fieldset>
	</div>

	<div class="container-fluid">
    	<div class="row card-description" style="padding: 1% 1%; margin-bottom: 0;">
    		<div class="col-md-12 text-right">

    	</div>
    </div>

	<div class="row card-description">
		@if(isset($_SESSION['permisos']['839']))
			<div class="col-md-12">
	    		<div class="container-filtercolumn form-inline">
	                @if(auth()->user()->modo_lectura())
	                @else
	                    @if(isset($_SESSION['permisos']['750']))
	                    <a href="{{route('campos.organizar', 16)}}" class="btn btn-warning mr-1"><i class="fas fa-table"></i> Organizar Tabla</a>
	                    @endif
	                    <div class="dropdown mr-1">
	                    	<button class="btn btn-warning dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	                    		Acciones en Lote
	                    	</button>
	                    	<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
	                    		<a class="dropdown-item" href="javascript:void(0)" id="btn_destroy"><i class="fas fa-fw fa-times" style="margin-left:4px; "></i> Eliminar Bancos</a>
	                    	</div>
	                    </div>
	                @endif
				</div>
			</div>
		@endif
		<div class="col-md-12">
			<table class="table table-striped table-hover w-100" id="tabla-bancos">
				<thead class="thead-dark">
					<tr>
					    @foreach($tabla as $campo)
    					    <th>{{$campo->nombre}}</th>
    					@endforeach
						<th>Acciones</th>
					</tr>
				</thead>
			</table>
		</div>
	</div>
@endif
@endsection

@section('scripts')
<script>
    var tabla = null;
    window.addEventListener('load',
    function() {
		tabla = $('#tabla-bancos').DataTable({
			responsive: true,
			serverSide: true,
			processing: true,
			searching: false,
			language: {
				'url': '/vendors/DataTables/es.json'
			},
			order: [
				[0, "desc"]
			],
			"pageLength": {{ Auth::user()->empresa()->pageLength }},
			ajax: '{{url("lbanco")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
			columns: [
			    @foreach($tabla as $campo)
                    {data: '{{$campo->campo}}'},
                @endforeach
				{data: 'acciones'},
			],
			@if(isset($_SESSION['permisos']['839']))
			select: true,
            select: {
                style: 'multi',
            },
			dom: 'Blfrtip',
            buttons: [{
            	text: '<i class="fas fa-check"></i> Seleccionar todos',
            	action: function() {
            		tabla.rows({
            			page: 'current'
            		}).select();
            	}
            },
            {
            	text: '<i class="fas fa-times"></i> Deseleccionar todos',
            	action: function() {
            		tabla.rows({
            			page: 'current'
            		}).deselect();
            	}
            }]
            @endif
		});

        tabla.on('preXhr.dt', function(e, settings, data) {
			data.nombre  = $('#nombre').val();
			data.nro_cta = $('#nro_cta').val();
			data.tipo_cta    = $('#tipo_cta').val();
			data.filtro  = true;
        });

        $('#filtrar').on('click', function(e) {
            getDataTable();
            return false;
        });

        $('#form-filter').on('keypress',function(e) {
            if(e.which == 13) {
                getDataTable();
                return false;
            }
        });

        $('#nombre, #nro_cta').on('keyup',function(e) {
        	if(e.which > 32 || e.which == 8) {
        		getDataTable();
        		return false;
        	}
        });

        $('#tipo_cta').on('change',function() {
        	getDataTable();
        	return false;
        });

        $('#btn_destroy').click( function () {
            destroy();
        });
    });

	function getDataTable() {
		tabla.ajax.reload();
	}

	function abrirFiltrador() {
		if ($('#form-filter').hasClass('d-none')) {
			$('#boton-filtrar').html('<i class="fas fa-times"></i> Cerrar');
			$('#form-filter').removeClass('d-none');
		} else {
			$('#boton-filtrar').html('<i class="fas fa-search"></i> Filtrar');
			cerrarFiltrador();
		}
	}

	function cerrarFiltrador() {
		$('#nombre').val('');
		$('#nro_cta').val('');
		$('#tipo_cta').val('').selectpicker('refresh');
		$('#form-filter').addClass('d-none');
		$('#boton-filtrar').html('<i class="fas fa-search"></i> Filtrar');
		getDataTable();
	}

	function destroy(){
        var bancos = [];

        var table = $('#tabla-bancos').DataTable();
        var nro = table.rows('.selected').data().length;

        if(nro<=1){
            swal({
                title: 'ERROR',
                html: 'Para ejecutar esta acción, debe al menos seleccionar dos bancos',
                type: 'error',
            });
            return false;
        }

        for (i = 0; i < nro; i++) {
            bancos.push(table.rows('.selected').data()[i]['id']);
        }

        swal({
            title: '¿Desea eliminar '+nro+' bancos en lote?',
            text: 'Al Aceptar, no podrá cancelar el proceso',
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
                    var url = `/software/empresa/bancos/`+bancos+`/destroy_lote`;
                }else{
                    var url = `/empresa/bancos/`+bancos+`/destroy_lote`;
                }

                $.ajax({
                    url: url,
                    method: 'GET',
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function(data) {
                        cargando(false);
                        swal({
                            title: 'PROCESO REALIZADO',
                            html: '<strong>'+data.correctos+' bancos '+data.state+'</strong><br><strong>'+data.fallidos+' bancos no '+data.state+' por estar en uso</strong>',
                            type: 'success',
                            showConfirmButton: true,
                            confirmButtonColor: '#1A59A1',
                            confirmButtonText: 'ACEPTAR',
                        });
                        getDataTable();
                    }
                })
            }
        })
    }
</script>
@endsection