@extends('layouts.app')
<style>

</style>
@section('styles')
<style>
</style>
@endsection
@section('boton')
@if(auth()->user()->modo_lectura())
<div class="alert alert-warning alert-dismissible fade show" role="alert">
	<a>Estas en modo lectura, si deseas seguir disfrutando de nuestros servicios adquiere alguno de nuestros planes <a class="text-black" href="{{route('PlanesPagina.index')}}"> <b>Click Aquí.</b></a></a>
	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
		<span aria-hidden="true">&times;</span>
	</button>
</div>
@else
<a href="javascript:abrirFiltrador()" class="btn btn-primary btn-sm my-1"><i class="fas fa-search"></i>Filtrar</a>
<!--<a href="{{route('contactos.pdfmariano')}}" target="_blank" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Pdf Mariano</a>-->
@endif
@endsection
@section('content')
@if(Session::has('success'))
<div class="alert alert-success" style="margin-left: 2%;margin-right: 2%;">
	{{Session::get('success')}}
</div>
<script type="text/javascript">
	setTimeout(function() {
		$('.alert').hide();
		$('.active_table').attr('class', ' ');
	}, 5000);
</script>
@endif

<div class="container-fluid d-none" id="form-filter">
	<div class="card shadow-sm border-0">
		<div class="card-body py-0">
			<div class="row">
				<div class="col-md-2 pl-1 pt-1">
					<input type="text" placeholder="Nombre" id="nombre" class="form-control rounded">
				</div>
				<div class="col-md-2 pl-1 pt-1">
					<input type="number" placeholder="Identificación" id="identificacion" class="form-control rounded">
				</div>
				<div class="col-md-2 pl-1 pt-1">
					<input type="number" placeholder="Teléfono" id="telefono" class="form-control rounded">
				</div>
				<div class="col-md-2 pl-1 pt-1">
					<select class="form-control rounded selectpicker" id="tipo_empresa"  title="Tipo Empresa" data-size="5" data-live-search="true">
						@foreach ($tipos_empresa as $tipo)
						<option value="{{ $tipo->id }}">{{ $tipo->nombre }}</option>
						@endforeach
					</select>
				</div>

				@if ($tipo_usuario == 2)
				<div class="col-md-1 pl-1 pt-1">
					<select class="form-control rounded selectpicker" id="contacto"  title="Contacto" data-size="5" data-live-search="true">
						<option value="0">Cliente</option>
						<option value="1">Proveedor</option>
						<option value="2">Cliente / Proveedor</option>
					</select>
				</div>
				@endif

				<div class="col-md-2 pl-1 pt-1">
					<select title="Vendedor" class="form-control rounded selectpicker" id="vendedor" data-size="5" data-live-search="true">
						@foreach ($vendedores as $vendedor)
						<option value="{{ $vendedor->id }}">{{ $vendedor->nombre }}</option>
						@endforeach
					</select>
				</div>
				<div class="{{$tipo_usuario != 2 ? 'col-md-2 pl-1 pt-1' : 'col-md-1 pl-1 pt-1'}}">
					<a href="javascript:cerrarFiltrador()" class="btn  rounded btn-sm p-1 float-right"><i class="fas fa-times"></i></a>
					<a href="javascript:void(0)" id="filtrar" class="btn rounded btn-sm p-1 float-right"><i class="fas fa-search"></i></a>
				</div>
			</div>
		</div>
	</div>
</div>


<div class="row card-description">
	<div class="col-md-12">

		<table class="table table-striped table-hover w-100" id="tabla-contactos">
			<thead class="thead-dark">
				<tr>
					<th>nro</th>
					<th>Emisor</th>
					<th>Código</th>
					<th>tipo</th>
					<th>Fecha</th>
					<th>Pdf</th>
					<th>Eventos</th>
					<th>Estado Dian</th>
					<th>Acciones</th>
				</tr>
			</thead>

		</table>
	</div>
</div>

<!-- Modal Acuse Recibo -->
<div class="modal fade bd-example-modal-lg" id="modalDocumentos" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">

</div>

<!-- Modal Acuse Recibo -->
<div class="modal fade bd-example-modal-lg" id="modalRechazo" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">

</div>
@endsection

@section('scripts')
<script>
    var tabla = null;
    window.addEventListener('load',
    function() {

		$('#tabla-contactos').DataTable({
			responsive: true,
			serverSide: true,
			processing: true,
			language: {
				'url': '/vendors/DataTables/es.json'
			},
			order: [
				[0, "desc"]
			],
			"pageLength": 25,
			ajax: '{{url("empresa/recepcion/documentos")}}',
			headers: {
				'X-CSRF-TOKEN': '{{csrf_token()}}'
			},
			columns: [
			    {
					data: 'nro'
				},
			    {
					data: 'supplierNit'
				},
				{
					data: 'documentId'
				},
				{
				    data: 'documentTypeCode'
				},
				{
					data: 'created_at'
				},
				{
					data: 'pdfUrl'
				},
				{
					data: 'acusado'
				},
				{
					data: 'estado_dian'
				},
				{
					data: 'acciones'
				},
			]
		});


        tabla = $('#tabla-contactos');

        tabla.on('preXhr.dt', function(e, settings, data) {
            data.nombre = $('#nombre').val();
            data.identificacion = $('#identificacion').val();
            data.telefono1 = $('#telefono').val();
            data.tipo_empresa = $('#tipo_empresa').val();
            data.contacto = $('#contacto').val();
            data.vendedor = $('#vendedor').val();
            data.filtro = true;
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

    });

	function getDataTable() {
		tabla.DataTable().ajax.reload();
	}

	function abrirFiltrador() {
		if ($('#form-filter').hasClass('d-none')) {
			$('#form-filter').removeClass('d-none');
		} else cerrarFiltrador();
	}

	function cerrarFiltrador() {
		$('#nombre').val('');
		$('#identificacion').val('');
		$('#telefono').val('');
		$('#tipo_empresa').val('');
		$('#contacto').val('');
		$('#vendedor').val('');
		$('#form-filter').addClass('d-none');
		getDataTable();
	}
</script>

<script>
	//tipo [1 = Acuse de recibo, 2= Confirma Recepcion del bien]
	function modalDocumentos(uuid,codigo,tipo) {
		
		let url = "";
		let titulo = "";
		let texto = "";
		let buttonName = "";
		if(tipo == 1){
			url = `/empresa/recepcion/modificaracuserecibo/${uuid}`;
			titulo = "Acuse de Recibo";
			texto = `Estás generando la confirmación de recepción de la Factura Electrónica <strong>${codigo}</strong> 
			Notificaremos tu respuesta al proveedor.`;
			buttonName = "Generar Acuse";
		}else if(tipo == 2){
			url = `/empresa/recepcion/modificarrecepcionbien/${uuid}`;
			titulo = "Confirmación de Recepción del Documento";
			texto = `Estás genernado la confirmación de recibo de mercancia y/o prestación del servicio de la Factura
			Electrónica <strong>${codigo}</strong> Notificaremos tu respuesta al proveedor.`;
			buttonName = "Confirmar Recepción";
		}

		$.ajax({
			url: url,
			method: 'GET',
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			success: function(response) {
				if (response) {
                    // console.log(response);
					let identificaciones = response.identificaciones;
					let formulario = [];
					let arrayform = {
						'tip_iden':"",
						'nit':"",
						'dv':"",
						'primer_nombre':"",
						'segundo_nombre':"",
						'apellidos':"",
						'rol':"",
						'area':""
					}
					
					if(response.document.status == 200 && response.document.formulario != false){
					    formulario = response.document.formulario;
					    
					    if(formulario.segundo_nombre != null){arrayform.segundo_nombre = formulario.segundo_nombre;}
					 
					 	arrayform.tip_iden = formulario.tip_iden;
						arrayform.nit = formulario.identificacion;
						arrayform.dv = formulario.dv;
						arrayform.primer_nombre = formulario.primer_nombre;
						arrayform.apellidos = formulario.apellidos;
						arrayform.rol = formulario.cargo;
						arrayform.area = formulario.area;
					}


					$('#modalDocumentos').html('');
					$('#modalDocumentos').append(`<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:616px !important;">
						<form method="post" action="" id="formacuserecibo">	
							<input type="hidden" value="${uuid}" name="uuid" id="uuid">
							<input type="hidden" value="${tipo}" name="tipo" id="tipo">
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title" id="exampleModalLabel">${titulo}</h5>
									<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
										</button>
										</div>
										<div class="modal-body">
										<p>
										${texto}
										</p>
								<div class="form-group">
									<div class="row">
										<div class="form-group col-md-5">
											<label class="control-label">Tipo de Identificación <span class="text-danger">*</span></label>
											<select class="form-control selectpicker" name="tip_iden" id="tip_iden" required="" onchange="searchDV(this.value)" title="Seleccione">
											</select>
										</div>
										<div class="form-group col-md-5">
											<label class="control-label">Identificación <span class="text-danger">*</span><a><i data-tippy-content="Identificación de la persona" class="icono far fa-question-circle"></i></a></label>
											<input type="text" class="form-control" name="nit" id="nit" required="" maxlength="10" value="${arrayform.nit}" onkeypress="return event.charCode >= 48 && event.charCode <=57">
										</div>
										<div class="form-group col-md-2" style="display: none;" id="dvnit">
											<label class="control-label">DV <span class="text-danger">*</span></label>
											<input type="text" class="form-control" name="dv" id="dv" disabled required="" maxlength="20" value="${arrayform.dv}">
											<input type="hidden" name="dvoriginal" id="dvoriginal" value="${arrayform.dv}">
										</div>
									</div>

									<div class="row">
										<div class="form-group col-md-4">
											<label class="control-label">Primer Nombre<span class="text-danger">*</span></label>
											<input type="text" class="form-control" name="primer_nombre" id="primer_nombre" required="" value="${arrayform.primer_nombre}">
										</div>
										<div class="form-group col-md-4">
											<label class="control-label">Segundo Nombre</label>
											<input type="text" class="form-control" name="segundo_nombre" id="segundo_nombre"  value="${arrayform.segundo_nombre}">
										</div>
										<div class="form-group col-md-4">
											<label class="control-label">Apellidos<span class="text-danger">*</span></label>
											<input type="text" class="form-control" name="apellidos" id="apellidos" required="" value="${arrayform.apellidos}">
										</div>
									</div>

									<div class="row">
										<div class="form-group col-md-6">
											<label class="control-label">Rol/Cargo<span class="text-danger">*</span></label>
											<input type="text" class="form-control" name="rol" id="rol" required="" value="${arrayform.rol}">
										</div>
										<div class="form-group col-md-6">
											<label class="control-label">Área<span class="text-danger">*</span></label>
											<input type="text" class="form-control" name="area" id="area" required="" value="${arrayform.area}">
										</div>
									</div>
								</div>
								<div class="modal-footer">
									<a  class="btn btn-secondary" data-dismiss="modal">Cancelar</a>
									<a  class="btn btn-primary text-white" onclick="guardarAcuseRecibo()">${buttonName}</a>
								</div>
							</div>
						</form>
						</div>`);

						$.each( identificaciones, function( key, value ){

						$('#tip_iden').append($('<option>',
							{
								value: value.id,
								text : value.identificacion,
								selected: value.id == arrayform.tip_iden ? true : false,
							}));
					});

					if(arrayform.tip_iden != ""){
						$("#tip_iden option[value=" + `${arrayform.tip_iden}` + "]").attr("selected",true);
					}

					if(arrayform.tip_iden == 6){
						document.getElementById("dvnit").style.display = "block";
					}

					$('#tip_iden').selectpicker('refresh');
					$('#modalDocumentos').modal('show');
				}
			}
		});
	}

	function guardarAcuseRecibo(){
		
		let urlpost = "/empresa/recepcion/modificaracuserecibostore";
		let formId = $("#formacuserecibo").serialize();

		//validacion de que ningun campo vaya vacio
		arrv = {
			'tip_iden':  $("#tip_iden").val(),
			'nit':  $("#nit").val(),
			'dv':  $("#dv").val(),
			'primer_nombre':  $("#primer_nombre").val(),
			'segundo_nombre':  $("#segundo_nombre").val(),
			'apellidos':  $("#apellidos").val(),
			'rol':  $("#rol").val(),
			'area':  $("#area").val()
		}
		if(!arrv.tip_iden || !arrv.nit || !arrv.primer_nombre || !arrv.apellidos || !arrv.rol || !arrv.area){
			alert("Debe llenar todos los campos, son obligatorios para la DIAN.")
			return false;
		}
	
		$(".loader").show();
		$.ajax({
			url: urlpost,
			method: 'POST',
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			data: formId,
			success: function(response) {

				if(response[0]){
					response = response[0];
				}else{
					if(response.status == 400){
						mensajeSwal('error al actualizar, no se encontró el documento, intente nuevamente porfavor.',response.messageError,'error');
					}
				}
				if(response.status == 200){
					mensajeSwal('Evento emitido correctamente','','success');
					getDataTable();
				}else{
					mensajeSwal('error al actualizar, no se encontró el documento, intente nuevamente porfavor.','','error');
				}

				$(".loader").hide();
				$('#modalDocumentos').modal('hide');
			}
		});
	}

	//tipo [3 = Aceptacion, 4= Rechazo]
	function modalAceptoRechazo(uuid,codigo,tipo){
	
		let = titleswal = '';
		let textswal = '';
		let button = '';

		if(tipo == 3){
			titulo = '¿Desea Aceptar el documento?';
			texto = 'Al aceptar confirmarás que estas de acuerdo con la información de la factura electrónica ' + codigo;
			button = 'Aceptar';
		}else if(tipo == 4){
			titulo = '¿Desea Rechazar el documento?';
			texto = 'Al rechazar confirmarás que NO estas de acuerdo con la información de la factura electrónica ' + codigo;
			button = 'Rechazar';
		}

			$('#modalRechazo').html('');
			$('#modalRechazo').append(`<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:616px !important;">
				<form method="post" action="" id="formaceptorechazo">	
					<input type="hidden" value="${uuid}" name="uuid" id="uuid">
					<input type="hidden" value="${tipo}" name="tipo" id="tipo">
					<div class="modal-content">
						<div class="modal-header">
							<h5 class="modal-title" id="exampleModalLabel">${titulo}</h5>
							<button type="button" class="close" data-dismiss="modal" aria-label="Close">
								<span aria-hidden="true">&times;</span>
								</button>
								</div>
								<div class="modal-body">
								<p>
								${texto}
								</p>
						<div class="form-group">
							<div class="row">
								<div class="form-group col-md-12">
									<label class="control-label">Nombre Usuario<span class="text-danger">*</span></label>
									<input type="text" class="form-control" name="primer_nombre" id="primer_nombre" required="" value="">
								</div>
							</div>
							<div class="row d-none" id="classrechazo">
								<div class="form-group col-md-12">
									<label class="control-label">Motivo de rechazo<span class="text-danger">*</span></label>
									<select class="form-control selectpicker" name="claim_code" id="claim_code" required="" title="Seleccione">
									</select>								
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<a  class="btn btn-secondary" data-dismiss="modal">Cancelar</a>
							<a  class="btn btn-primary text-white" onclick="guardarAceptoRechazo(${tipo})">${button}</a>
						</div>
					</div>
				</form>
			</div>`);

			//LLenamos el select para rechazos
			if(tipo == 4){
				
				$("#classrechazo").removeClass("d-none");

				let arrayClaim = [
					{"nombre":"Documento con  inconsistencias", "tipo" : "01"},
					{"nombre" : "Mercancía no entregada totalmente", "tipo" : "02"},
					{"nombre" : "Mercancía no entregada parcialmente", "tipo" : "03"},
					{"nombre" : "Servicio no prestado", "tipo" : "04"}
			];

			$.each( arrayClaim, function( key, value ){
				$('#claim_code').append($('<option>',
					{
						value: value.tipo,
						text : value.nombre,
					}));
				});
				$("#claim_code option[value=" + `01` + "]").attr("selected",true);
				$("#claim_code").selectpicker('refresh');
			}

			$('#modalRechazo').modal('show');
	}

	function guardarAceptoRechazo(tipo){

		let formId = $("#formaceptorechazo").serialize();
		let urlpost = "/empresa/recepcion/aceptorechazodocumento";
		//validacion de que ningun campo vaya vacio
		arrv = {
			'primer_nombre':  $("#primer_nombre").val(),
		}

		if(!arrv.primer_nombre){
			alert("Debe llenar todos los campos, son obligatorios para la DIAN.")
			return false;
		}

		$(".loader").show();
		$.ajax({
			url: urlpost,
			headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
			method: 'POST',
			data: formId,
			success: function(response) {
				
				let respuestaSwal = '';
				if(tipo == 3){
					respuestaSwal = 'Documento aceptado correctamente';
				}else if(tipo == 4){
					respuestaSwal = 'Documento rechazado correctamente';
				}

				if(response[0]){
					response = response[0];
				}else{
					if(response.status == 400){
						mensajeSwal('error al actualizar, no se encontró el documento, intente nuevamente porfavor.',response.messageError,'error');
					}
				}
				if(response.status == 200){
					mensajeSwal(respuestaSwal,'','success');
					getDataTable();
				}else{
					mensajeSwal('error al actualizar, no se encontró el documento, intente nuevamente porfavor.','','error');
				}
				$('#modalRechazo').modal('hide');
				$(".loader").hide();
			}

		})
	}

	function mensajeSwal(title,text,type){
		Swal.fire({
			title: title,
			text: text,
			type: type,
			showCancelButton: false,
			showConfirmButton: true,
			cancelButtonColor: '#d33',
			cancelButtonText: 'Cancelar',
// 			timer: 9000
		});
	}
</script>

@endsection
