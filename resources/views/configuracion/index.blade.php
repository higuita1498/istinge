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
			<span class="text-primary">Correo Electrónico:</span>  {{Auth::user()->empresa()->email}}<br>
			@if(!Auth::user()->empresa()->suscripcion()->ilimitado)
			<span class="text-primary">Suscripción NetworkSoft:</span> {{date('d-m-Y', strtotime(Auth::user()->empresa()->suscripcion()->fec_corte))}}</p>
			@endif
		</div>
		<div class="col-sm-8 offset-md-2 {{$empresa->nomina ? 'd-none' : ''}}" id="alerta_nomina">
			<div class="alert alert-info" role="alert" style="color: #d08f50;background-color: #d08f5026;border-color: #d08f50;">
				<h4 class="alert-heading font-weight-bold">SUGERENCIA</h4>
				<p>Para hacer uso del módulo de <strong>Nómina</strong>, primero debe habilitarlo en la opción <strong>Nómina &gt; Habilitar nómina</strong>.</p>
			</div>
		</div>
	</div>

	@if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
	        <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
	    </div>
	@endif

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
			@if(isset($_SESSION['permisos']['769']))
			<a href="{{route('canales.index')}}">Canales de Venta</a><br>
			@endif
			<a href="#" data-toggle="modal" data-target="#periodo_factura">Periodo de Facturación</a><br>
			<a href="javascript:facturacionAutomatica()">{{ Auth::user()->empresa()->factura_auto == 0 ? 'Habilitar':'Deshabilitar' }} Facturación Automática</a><br>
			<input type="hidden" id="facturaAuto" value="{{Auth::user()->empresa()->factura_auto}}">
			<a href="javascript:prorrateo()">{{ Auth::user()->empresa()->prorrateo == 0 ? 'Habilitar':'Deshabilitar' }} Prorrateo</a><br>
			<input type="hidden" id="prorrateoid" value="{{Auth::user()->empresa()->prorrateo}}">
			<a href="javascript:actDescEfecty()">{{ Auth::user()->empresa()->efecty == 0 ? 'Habilitar':'Deshabilitar' }} Efecty</a><br>
			<input type="hidden" id="efectyid" value="{{Auth::user()->empresa()->efecty}}">
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
			{{-- <a href="{{route('categorias.index')}}">Gestionar Categorias</a> <br> --}}
			<a href="{{route('puc.index')}}">Gestionar PUC</a> <br>
			<a href="{{route('formapago.index')}}">Formas de Pago</a> <br>
			<a href="{{route('anticipo.index')}}">Anticipos</a> <br>
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
			<hr class="nomina">
			<a href="#" data-toggle="modal" data-target="#nro_registro">Configurar Nro registros a mostrar</a><br>
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
			<h4 class="card-title">Nómina</h4>
			<p>Gestione la nómina electrónicamente de los empleados que trabajan en su empresa.</p>
			<input type="hidden" name="estado_nomina" id="estado_nomina" value="{{$empresa->nomina}}">
			<a id="texto_nomina" href="javascript:habilitarNomina()">{{$empresa->nomina ? 'Deshabilitar' : 'Habilitar'}} nómina</a> <br>
			<a id="preferencia_pago" href="{{route('nomina.preferecia-pago')}}" class="{{$empresa->nomina ? '' : 'd-none'}}">Preferencias de pago</a> <br>
			<a id="nomina_numeracion" href="{{ route('numeraciones_nomina.index') }}" class="{{$empresa->nomina ? '' : 'd-none'}}">Numeraciones</a> <br>
			<a id="nomina_calculos" href="{{ route('configuraicon.calculosnomina') }}" class="{{$empresa->nomina ? '' : 'd-none'}}">Cálculos fijos</a> <br>
			{{-- <a href="#" onclick="nominaDIAN()" id="div_nominaDIAN"  class="{{$empresa->nomina ? '' : 'd-none'}}">{{ Auth::user()->empresaObj->nomina_dian == 0 ? 'Activar' : 'Desactivar' }} Nómina Electrónica por la DIAN</a><br> --}}
			<input type="hidden" id="nominaDIAN" value="{{Auth::user()->empresaObj->nomina_dian}}">
			<a id="nomina_asistentes" href="{{ route('nomina-dian.asistente') }}" class="{{$empresa->nomina ? '' : 'd-none'}}">Asistente de habilitación DIAN</a> <br>
			<hr class="nomina {{$empresa->nomina ? '' : 'd-none'}}">
			<a id="planes_nomina" href="{{route('nomina.suscripciones')}}" class="{{$empresa->nomina ? '' : 'd-none'}}">Planes de Suscripción</a> <br>
		</div>

		@if(isset($_SESSION['permisos']['759']))
		<div class="col-sm-3">
			<h4 class="card-title">Administración OLT</h4>
			<p>Completa la información de la OLT de tu empresa.</p>
			<a href="#" data-toggle="modal" data-target="#config_olt">Configurar OLT</a><br>
		</div>
		@endif

		@if(isset($_SESSION['permisos']['762']) || isset($_SESSION['permisos']['763']) || isset($_SESSION['permisos']['764']))
		<div class="col-sm-3">
			<h4 class="card-title">Integraciones de Servicios</h4>
			<p>Configure cada uno de los servicios disponibles para darle uso en NetworkSoft</p>
			@if(isset($_SESSION['permisos']['762']))
			<a href="{{ route('integracion-sms.index') }}">Mensajería</a><br>
			@endif
			@if(isset($_SESSION['permisos']['763']))
			<a href="{{ route('integracion-pasarelas.index') }}">Pasarelas de Pago</a><br>
			@endif
			@if(isset($_SESSION['permisos']['777']))
			<a href="{{ route('integracion-whatsapp.index') }}">WhatsApp (CallMEBot)</a><br>
			@endif
			@if(isset($_SESSION['permisos']['764']) && Auth::user()->nombres == 'Desarrollo')
			<a href="#">Troncal SIP</a><br>
			@endif
		</div>
		@endif

		<div class="col-sm-3">
			<h4 class="card-title">Limpieza del Sistema</h4>
			<p>Limpia los archivos temporales y caché del sistema.</p>
			<a href="javascript:limpiarCache()">Limpiar caché</a><br>
		</div>

		@if(!Auth::user()->empresa()->suscripcion()->ilimitado)
		<div class="col-sm-3">
			<h4 class="card-title">Planes</h4>
			<p>Elige el plan que quieres tener y configura cómo quieres pagarlo.</p>
			<a href="{{route('listadoPagos.index')}}">Pagos de Suscripcion</a> <br>
            {{-- @if($personalPlan)
                <a href="{{route('planes.personalizado')}}">Plan personalizado</a> <br>
            @endif
			<a href="{{route('PlanesPagina.index')}}">Planes</a> <br>
			<a href="{{route('PlanesPagina.index')}}">Metodos de pago</a> <br> --}}
		</div>
		@endif
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

	{{-- CANT REGISTRO --}}
	<div class="modal fade show" id="nro_registro" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Configurar Nro registros a mostrar</h4>
				</div>
				<div class="modal-body">
					<p>Indique la cantidad de registro que quiere cargar por página en cada uno de los listados <a><i data-tippy-content="Por defecto aparecerán 25 registros por página." class="icono far fa-question-circle"></i></a></p>
					<div class="col-sm-6 offset-sm-3">
						<select class="form-control selectpicker" name="pageLength" id="val_pageLength" required="" title="Seleccione" data-live-search="true" data-size="5">
							<option value="10" {{ Auth::user()->empresa()->pageLength == 10 ? 'selected' : '' }}>10 registros P/P</option>
							<option value="25" {{ Auth::user()->empresa()->pageLength == 25 ? 'selected' : '' }}>25 registros P/P</option>
							<option value="50" {{ Auth::user()->empresa()->pageLength == 50 ? 'selected' : '' }}>50 registros P/P</option>
							<option value="100" {{ Auth::user()->empresa()->pageLength == 100 ? 'selected' : '' }}>100 registros P/P</option>
						</select>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cerrar</button>
					<button type="button" class="btn btn-success" onclick="storePageLength()">Guardar</button>
				</div>
			</div>
		</div>
	</div>
	{{-- /CANT REGISTRO --}}

	{{-- PERIODO FACTURACIÓN --}}
	<div class="modal fade show" id="periodo_factura" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Configurar Periodo de Facturación</h4>
				</div>
				<div class="modal-body">
					<p>Indique el periodo de su facturación</p>
					<div class="col-sm-6 offset-sm-3">
						<select class="form-control selectpicker" name="periodo_facturacion" id="val_periodo_facturacion" required="" title="Seleccione" data-live-search="true" data-size="5">
							<option value="1" {{ Auth::user()->empresa()->periodo_facturacion == 1 ? 'selected' : '' }}>Mes Anticipado</option>
							<option value="2" {{ Auth::user()->empresa()->periodo_facturacion == 2 ? 'selected' : '' }}>Mes Vencido</option>
						</select>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cerrar</button>
					<button type="button" class="btn btn-success" onclick="storePeriodoFacturacion()">Guardar</button>
				</div>
			</div>
		</div>
	</div>
	{{-- /PERIODO FACTURACIÓN --}}
@endsection

@section('scripts')
    <script>
    	function storePeriodoFacturacion() {
    		cargando(true);
    		if (window.location.pathname.split("/")[1] === "software") {
    			var url = `/software/empresa/configuracion/storePeriodoFacturacion`;
    		}else{
    			var url = `/empresa/configuracion/storePeriodoFacturacion`;
    		}
    		$.ajax({
    			url: url,
    			method: 'POST',
    			headers: {
    				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    			},
    			data: {
    				periodo_facturacion: $('#val_periodo_facturacion').val()
    			},
    			success: function(response) {
    				cargando(false);
    				swal({
    					title: response.title,
    					text: response.message,
    					type: response.type,
    					showConfirmButton: true,
    					confirmButtonColor: '#1A59A1',
    					confirmButtonText: 'ACEPTAR',
    				});
    				if (response.success == true) {
    					$("#periodo_factura").modal('hide');
    				}
    			}
    		});
    	}

    	function storePageLength() {
    		cargando(true);
    		if (window.location.pathname.split("/")[1] === "software") {
    			var url = `/software/empresa/configuracion/storePageLength`;
    		}else{
    			var url = `/empresa/configuracion/storePageLength`;
    		}
    		$.ajax({
    			url: url,
    			method: 'POST',
    			headers: {
    				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    			},
    			data: {
    				pageLength: $('#val_pageLength').val()
    			},
    			success: function(response) {
    				cargando(false);
    				swal({
    					title: 'NRO DE REGISTROS A MOSTRAR',
    					text: response.message,
    					type: response.type,
    					showConfirmButton: true,
    					confirmButtonColor: '#1A59A1',
    					confirmButtonText: 'ACEPTAR',
    				});
    				if (response.success == true) {
    					$("#nro_registro").modal('hide');
    					setTimeout(function(){
    						location.reload();
    					}, 1000);
    				}
    			}
    		});
    	}

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

		function actDescEfecty() {
			if (window.location.pathname.split("/")[1] === "software") {
				var url='/software/efecty';
			}else{
				var url = '/efecty';
			}

		    if ($("#efectyid").val() == 0) {
		        $titleswal = "¿Desea habilitar la plataforma Efecty?";
		    }

		    if ($("#efectyid").val() == 1) {
		        $titleswal = "¿Desea deshabilitar la plataforma Efecty?";
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
		                data: { efecty: $("#efectyid").val() },
		                success: function (data) {
		                    console.log(data);
		                    if (data == 1) {
		                        Swal.fire({
		                            type: 'success',
		                            title: 'Plataforma Efecty habilitada',
		                            showConfirmButton: false,
		                            timer: 5000
		                        })
		                        $("#efectyid").val(1);
		                    } else {
		                        Swal.fire({
		                            type: 'success',
		                            title: 'Plataforma Efecty deshabilitada',
		                            showConfirmButton: false,
		                            timer: 5000
		                        })
		                        $("#efectyid").val(0);
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

		function habilitarNomina() {
			var estadoNomina = parseInt($('#estado_nomina').val());
			if (window.location.pathname.split("/")[1] === "software") {
				var url='/software/empresa';
			}else{
				var url = '/empresa';
			}
			Swal.fire({
				title: `¿${estadoNomina == 1 ? 'Deshabilitar' : 'Habilitar'} nómina?`,
				text: `${estadoNomina == 1 ? 'La nómina de su empresa será deshabilitada' : 'Recuerde que la nomina electrónica estará abilitada por 15 días de manera gratuita'} `,
				type: 'question',
				showCancelButton: true,
				confirmButtonColor: '#3085d6',
				cancelButtonColor: '#d33',
				cancelButtonText: 'Cancelar',
				confirmButtonText: `${estadoNomina == 1 ? 'Deshabilitar' : 'Habilitar'}`,
			}).then((result) => {
				if (result.value) {
					$.ajax({
						url: url + '/configuracion/estado/nomina',
						headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
						method: 'post',
						success: function (response) {
							console.log(response);
							if (response.success) {
								Swal.fire({
									position: 'top-center',
									type: 'success',
									text: response.text,
									title: response.message,
									showConfirmButton: false,
									timer: 5000
								})
								$("#estado_nomina").val(response.nomina);
								$("#texto_nomina").text(response.nomina == 1 ? 'Deshabilitar nómina' : 'Habilitar nómina');
								if (response.nomina == 1) {
									$("#preferencia_pago").removeClass('d-none');
									$("#nomina_numeracion").removeClass('d-none');
									$("#nomina_calculos").removeClass('d-none');
									$("#nomina").removeClass('d-none');
									$('#div_nominaDIAN').removeClass('d-none');
									$("#nomina").addClass('nav-item');
									$("#nomina_asistente").removeClass('d-none');
									$(".nomina").removeClass('d-none');
									$("#planes_nomina").removeClass('d-none');
									$("#nomina_asistentes").removeClass('d-none');
									$("#alerta_nomina").addClass('d-none');
								} else {
									$("#preferencia_pago").addClass('d-none');
									$("#nomina_numeracion").addClass('d-none');
									$("#nomina_calculos").addClass('d-none');
									$("#nomina").addClass('d-none');
									$('#div_nominaDIAN').addClass('d-none');
									$("#nomina_asistente").addClass('d-none');
									$(".nomina").addClass('d-none');
									$("#planes_nomina").addClass('d-none');
									$("#nomina_asistentes").addClass('d-none');
									$("#alerta_nomina").removeClass('d-none');
								}
							}
							location.reload()
						}
					});
				}
			})
		}
    </script>
@endsection
