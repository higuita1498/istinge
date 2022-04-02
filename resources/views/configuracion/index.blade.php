@extends('layouts.app')

@section('content')

<div class="row card-description">
	<div class="col-sm-4" style="text-align: center;">
		<img class="img-responsive" src="{{asset('images/Empresas/Empresa'.Auth::user()->empresa()->id.'/'.Auth::user()->empresa()->logo)}}" alt="" style="width: 100%">
	</div>
	<div class="col-sm-8">
		<p  class="card-title"> <span class="text-primary">Empresa:</span> {{Auth::user()->empresa()->nombre}} <br>
			<span class="text-primary">{{Auth::user()->empresa()->tip_iden()}}:</span>  {{Auth::user()->empresa()->nit}} @if(Auth::user()->empresa()->dv)-{{Auth::user()->empresa()->dv}}@endif<br>
			<span class="text-primary">Tipo de Persona:</span>  {{Auth::user()->empresa()->tipo_persona()}}<br>
			<span class="text-primary">Teléfono:</span>  {{Auth::user()->empresa()->telefono}}<br>
			<span class="text-primary">Dirección:</span>  {{Auth::user()->empresa()->direccion}}<br>
			<span class="text-primary">Correo Electrónico:</span>  {{Auth::user()->empresa()->email}}</p>
		</div>
	</div>
	<div class="row card-description configuracion">
		<div class="col-sm-3">
			<h4 class="card-title">Empresa</h4>
			<p>Completa la información de tu empresa.</p>
			<a href="{{route('configuracion.create')}}">Empresa</a> <br>
			<a href="{{route('usuarios.index')}}">Usuarios</a><br>
			<a href="{{route('roles.index')}}">Tipos de Usuario</a><br>
			<a href="{{route('configuracion.servicios')}}">Servicios</a> <br>
			<a href="{{route('miusuario')}}">Mi perfil</a><br>
			<a href="#" data-toggle="modal" data-target="#seguridad">Seguridad</a><br>
		</div>

		@if(isset($_SESSION['permisos']['40']) || isset($_SESSION['permisos']['258']))
		<div class="col-sm-3">
			<h4 class="card-title">Facturación</h4>
			<p>Configura la información que se mostrará en tus facturas de venta.</p>
			<a href="{{route('configuracion.terminos')}}">Términos de pago</a> <br>
			<a href="{{route('configuracion.numeraciones')}}">Numeraciones</a><br>
			<a href="{{route('configuracion.numeraciones_dian')}}">Numeraciones DIAN</a><br>
			<a href="{{route('configuracion.datos')}}">Datos generales</a><br>
			<a href="{{route('vendedores.index')}}">Vendedores</a><br>
			<a href="javascript:facturacionAutomatica()">{{ Auth::user()->empresa()->factura_auto == 0 ? 'Habilitar':'Deshabilitar' }} Facturación Automática</a><br>
			<input type="hidden" id="facturaAuto" value="{{Auth::user()->empresa()->factura_auto}}">
			<a href="javascript:prorrateo()">{{ Auth::user()->empresa()->prorrateo == 0 ? 'Habilitar':'Deshabilitar' }} Prorrateo</a><br>
			<input type="hidden" id="prorrateoid" value="{{Auth::user()->empresa()->prorrateo}}">
		</div>

		<div class="col-sm-3">
			<h4 class="card-title">Impuestos</h4>
			<p>Define aquí los tipos de impuestos y retenciones que aplicas a tus facturas de venta.</p>
			<a href="{{route('impuestos.index')}}">Impuestos</a> <br>
			<a href="{{route('retenciones.index')}}">Retenciones</a><br>
		</div>

		<div class="col-sm-3">
			<h4 class="card-title">Contactos</h4>
			<p>Registra aqui referencias para tus contactos.</p>
			<a href="{{route('tiposempresa.index')}}">Tipos de Contactos</a> <br>
		</div>

		<div class="col-sm-3">
			<h4 class="card-title">Categorias</h4>
			<p>Organice a su medida el plan único de cuentas.</p>
			<a href="{{route('categorias.index')}}">Gestionar Categorias</a> <br>
			<a href="{{route('puc.index')}}">Gestionar PUC</a> <br>
			<a href="{{route('formapago.index')}}">Formas de Pago</a> <br>
			<a href="{{route('productoservicio.index')}}">Productos y Servicios</a> <br>
		</div>
		@endif

		@if(isset($_SESSION['permisos']['737']))
		<div class="col-sm-3">
			<h4 class="card-title">Tipos de Gastos</h4>
			<p>Organice los tipos de gastos que utilizará su empresa.</p>
			@if(isset($_SESSION['permisos']['737']))
			<a href="{{route('tipos-gastos.index')}}">Tipos de Gastos</a> <br>
			@endif
		</div>
		@endif

		@if(isset($_SESSION['permisos']['750']))
		<div class="col-sm-3">
			<h4 class="card-title">Organización de Tablas</h4>
			<p>Configura y organiza los campos de las tablas.</p>
			<a href="{{route('campos.organizar', 1)}}">Contactos</a><br>
			<a href="{{route('campos.organizar', 2)}}">Contratos</a><br>
			{{-- <a href="{{route('campos.organizar', 3)}}">Inventario</a><br> --}}
			<a href="{{route('campos.organizar', 4)}}">Factura de Venta</a><br>
			<a href="{{route('campos.organizar', 5)}}">Pagos / Ingresos</a><br>
			<a href="{{route('campos.organizar', 9)}}">Descuentos</a><br>
			{{-- <a href="{{route('campos.organizar', 6)}}">Factura de Proveedores</a><br>
			<a href="{{route('campos.organizar', 7)}}">Pagos / Egresos</a><br>
			<a href="{{route('campos.organizar', 8)}}">Pagos Recurrentes</a><br> --}}
			<a href="{{route('campos.organizar', 10)}}">Planes de Velocidad</a><br>
			<a href="{{route('campos.organizar', 11)}}">Promesas de Pago</a><br>
			<a href="{{route('campos.organizar', 12)}}">Radicados</a><br>
			<a href="{{route('campos.organizar', 13)}}">Monitor Blacklist</a><br>
		</div>
		@endif

		<div class="col-sm-3">
			<h4 class="card-title">Gestión de Puertos</h4>
			<p>Configura y organiza los puertos de conexión.</p>
			<a href="{{route('puertos-conexion.index')}}">Puertos de Conexión</a><br>
		</div>

		@if(isset($_SESSION['permisos']['752']))
		<div class="col-sm-3">
			<h4 class="card-title">Gestión Servidor de Correo</h4>
			<p>Configura y organiza el servidor de correo externo para el envío de email y notificaciones.</p>
			<a href="{{route('servidor-correo.index')}}">Servidor de Correo</a><br>
		</div>
		@endif

		<div class="col-sm-3">
			<h4 class="card-title">Administración OLT</h4>
			<p>Completa la información de la OLT de tu empresa.</p>
			<a href="#" data-toggle="modal" data-target="#config_olt">Configurar OLT</a><br>
		</div>

		<div class="col-sm-3">
			<h4 class="card-title">Limpieza del Sistema</h4>
			<p>Limpia los archivos temporales y caché del sistema.</p>
			<a href="javascript:limpiarCache()">Limpiar caché</a><br>
		</div>
	</div>
	{{-- <div class="row card-description configuracion">
		<div class="col-sm-3">
			<h4 class="card-title">Campos Extras Inventario</h4>
			<p>Configura las campos adicionales para el módulo de inventario.</p>
			<a href="{{route('personalizar_inventario.index')}}">Campos</a> <br>
		</div>
		<div class="col-sm-3">
			<h4 class="card-title">Planes</h4>
			<p>Elige el plan que quieres tener y configura cómo quieres pagarlo.</p>
			<a href="{{route('listadoPagos.index')}}">Pagos de Suscripcion</a> <br>
            @if($personalPlan)
                <a href="{{route('planes.personalizado')}}">Plan personalizado</a> <br>
            @endif
			<a href="{{route('PlanesPagina.index')}}">Planes</a> <br>
			<a href="{{route('PlanesPagina.index')}}">Metodos de pago</a> <br>
		</div>
				<div class="col-sm-3">
			<h4 class="card-title">Categorias</h4>
			<p>Organice a su medida el plan único de cuentas.</p>
			<a href="{{route('categorias.index')}}">Gestionar Categorias</a> <br>
		</div>
	</div> --}}

	{{-- SEGURIDAD --}}
	<div class="modal fade" id="seguridad" role="dialog">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title"></h4>
				</div>
				<div class="modal-body">
					<p>Si deseas cerrar sesión en todos los dispositivos que has iniciado sesión haz click en el enlace</p><br>
					<a href="{{route('home.closeallsession')}}">Cerrar sesión en todos los dispositivos</a>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
				</div>
			</div>
		</div>
	</div>
	{{-- /SEGURIDAD --}}

	{{-- CONFIGURACION OLT --}}
	<div class="modal fade" id="config_olt" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title"></h4>
				</div>
				<div class="modal-body">
					<form method="POST" action="{{ route('servicio.store') }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form" >
						{{ csrf_field() }}
						<div class="row">
							<div class="col-md-12 form-group">
								<label class="control-label">Smart OLT</label>
								<input type="text" class="form-control"  id="smartOLT" name="smartOLT"  required="" value="{{Auth::user()->empresa()->smartOLT}}" maxlength="200">
								<span class="help-block error">
									<strong>{{ $errors->first('smartOLT') }}</strong>
								</span>
							</div>
							<div class="col-md-12 form-group">
								<label class="control-label">Admin OLT</label>
								<input type="text" class="form-control"  id="adminOLT" name="adminOLT" value="{{Auth::user()->empresa()->adminOLT}}"  maxlength="200">
								<span class="help-block error">
									<strong>{{ $errors->first('adminOLT') }}</strong>
								</span>
							</div>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
					<a href="javascript:configuracionOLT()" class="btn btn-success">Guardar</A>
				</div>
			</div>
		</div>
	</div>
	{{-- /CONFIGURACION OLT --}}
	@endsection

	@section('scripts')
	    <script>
			function facturacionAutomatica() {
				if (window.location.pathname.split("/")[1] === "software") {
					var url='/software/configuracion_facturacionAutomatica';
				}else{
					var url = '/configuracion_facturacionAutomatica';
				}

			    if ($("#facturaAuto").val() == 0) {
			        $titleswal = "¿Desea habilitar la facturación automática de los contratos?";
			    }

			    if ($("#facturaAuto").val() == 1) {
			        $titleswal = "¿Desea deshabilitar la facturación automática de los contratos?";
			    }

			    Swal.fire({
			        title: $titleswal,
			        type: 'warning',
			        showCancelButton: true,
			        confirmButtonColor: '#3085d6',
			        cancelButtonColor: '#d33',
			        cancelButtonText: 'Cancelar',
			        confirmButtonText: 'Aceptar',
			    }).then((result) => {
			        if (result.value) {
			            $.ajax({
			                url: url,
			                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
			                method: 'post',
			                data: { status: $("#facturaAuto").val() },
			                success: function (data) {
			                    console.log(data);
			                    if (data == 1) {
			                        Swal.fire({
			                            type: 'success',
			                            title: 'Factuación automática para los contratos habilitada',
			                            showConfirmButton: false,
			                            timer: 5000
			                        })
			                        $("#facturaAuto").val(1);
			                    } else {
			                        Swal.fire({
			                            type: 'success',
			                            title: 'Factuación automática para los contratos deshabilitada',
			                            showConfirmButton: false,
			                            timer: 5000
			                        })
			                        $("#facturaAuto").val(0);
			                    }
			                    setTimeout(function(){
			                    	var a = document.createElement("a");
			                    	a.href = window.location.pathname;
			                    	a.click();
			                    }, 1000);
			                }
			            });

			        }
			    })
			}

			function limpiarCache() {
				if (window.location.pathname.split("/")[1] === "software") {
					var url='/software/configuracion_limpiarCache';
				}else{
					var url = '/configuracion_limpiarCache';
				}

				var empresa = {{ Auth::user()->empresa()->id }};
				var href = '{{route('home')}}';

			    Swal.fire({
			        title: '¿Desea limpiar los archivos temporales y la caché del sistema?',
			        type: 'warning',
			        showCancelButton: true,
			        confirmButtonColor: '#3085d6',
			        cancelButtonColor: '#d33',
			        cancelButtonText: 'Cancelar',
			        confirmButtonText: 'Aceptar',
			    }).then((result) => {
			        if (result.value) {
			        	cargando(true);
			            $.ajax({
			                url: url,
			                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
			                method: 'post',
			                data: { empresa: empresa },
			                success: function (data) {
			                	cargando(false);
			                    Swal.fire({
			                    	type: 'success',
			                    	title: 'Limpieza realizada con éxito',
			                    	showConfirmButton: false,
			                    	timer: 5000
			                    });
			                    setTimeout(function(){
			                    	var a = document.createElement("a");
			                    	a.href = href;
			                    	a.click();
			                    }, 1000);
			                }
			            });

			        }
			    })
			}

			function configuracionOLT() {
				if (window.location.pathname.split("/")[1] === "software") {
					var url='/software/configuracion_olt';
				}else{
					var url = '/configuracion_olt';
				}

	            $.ajax({
	                url: url,
	                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
	                method: 'post',
	                data: {
	                	smartOLT: $("#smartOLT").val(),
	                	adminOLT: $("#adminOLT").val()
	                },
	                success: function (data) {
	                	$("#config_olt").modal('hide');
	                	Swal.fire({
	                		type: 'success',
	                		title: 'La configuración de la OLT ha sido registrada con éxito',
	                		text: 'Recargando la página',
	                		showConfirmButton: false,
	                		timer: 5000
	                	})
	                    setTimeout(function(){
	                    	var a = document.createElement("a");
	                    	a.href = window.location.pathname;
	                    	a.click();
	                    }, 2000);
	                }
	            });
			}

			function prorrateo(){

				if (window.location.pathname.split("/")[1] === "software") {
					var url='/software/prorrateo';
				}else{
					var url = '/prorrateo';
				}

			    if ($("#prorrateoid").val() == 0) {
			        $titleswal = "¿Desea habilitar el prorrateo de las facturas?";
					text = "La primer factura de los clientes se cobrará según los días de uso de los servicios.";
			    }

			    if ($("#prorrateoid").val() == 1) {
			        $titleswal = "¿Desea deshabilitar el prorrateo de las facturas?";
					text = "";
			    }

			    Swal.fire({
			        title: $titleswal,
			        type: 'warning',
			        showCancelButton: true,
			        confirmButtonColor: '#3085d6',
			        cancelButtonColor: '#d33',
			        cancelButtonText: 'Cancelar',
			        confirmButtonText: 'Aceptar',
					text: text
			    }).then((result) => {
			        if (result.value) {
			            $.ajax({
			                url: url,
			                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
			                method: 'post',
			                data: { prorrateo: $("#prorrateoid").val() },
			                success: function (data) {

			                    if (data == 1) {
			                        Swal.fire({
			                            type: 'success',
			                            title: 'Prorrateo para facturas ha sido habilitado.',
			                            showConfirmButton: false,
			                            timer: 5000
			                        })
			                        $("#prorrateoid").val(1);
			                    } else {
			                        Swal.fire({
			                            type: 'success',
			                            title: 'Prorrateo para facturas ha sido deshabilitado',
			                            showConfirmButton: false,
			                            timer: 5000
			                        })
			                        $("#prorrateoid").val(0);
			                    }
								location.reload();
							}
			            });
			        }
			    });
			}
	    </script>
	@endsection
