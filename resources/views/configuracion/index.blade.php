@extends('layouts.app')

@section('content')

<style>
    .enlaces a {
    margin-bottom: 1px; /* Espacio entre cada enlace */
    border-bottom: 1px solid rgba(0, 0, 0, 0.1); /* Línea ligeramente transparente */
    padding-bottom: 1px; /* Espacio entre el enlace y la línea */
}
</style>
<div class="row card-description">
	<div class="col-sm-4" style="text-align: center;">
		<img class="img-responsive" src="{{asset('images/Empresas/Empresa'.Auth::user()->empresa()->id.'/'.Auth::user()->empresa()->logo)}}" alt="" style="max-width: 100%; max-width: 200px;">
	</div>
	<div class="col-sm-8">
		<p  class="card-title"> <span class="text-primary">Empresa:</span> {{Auth::user()->empresa()->nombre}} <br>
			<span class="text-primary">{{Auth::user()->empresa()->tip_iden()}}:</span> {{Auth::user()->empresa()->nit}}@if(Auth::user()->empresa()->dv)-{{Auth::user()->empresa()->dv}}@endif<br>
			<span class="text-primary">Tipo de Persona:</span>  {{Auth::user()->empresa()->tipo_persona()}}<br>
			<span class="text-primary">Teléfono:</span>  {{Auth::user()->empresa()->telefono}}<br>
			@if(Auth::user()->empresa()->whatsapp)<span class="text-primary">Whatsapp:</span>  {{Auth::user()->empresa()->whatsapp}}<br>@endif
			@if(Auth::user()->empresa()->soporte)<span class="text-primary">Soporte:</span>  {{Auth::user()->empresa()->soporte}}<br>@endif
			@if(Auth::user()->empresa()->ventas)<span class="text-primary">Ventas:</span>  {{Auth::user()->empresa()->ventas}}<br>@endif
			@if(Auth::user()->empresa()->finanzas)<span class="text-primary">Finanzas:</span>  {{Auth::user()->empresa()->finanzas}}<br>@endif

			<span class="text-primary">Dirección:</span>  {{Auth::user()->empresa()->direccion}}<br>
			<span class="text-primary">Correo Electrónico:</span>  {{Auth::user()->empresa()->email}}<br>
			@if(!Auth::user()->empresa()->suscripcion()->ilimitado)
			<span class="text-primary">Suscripción Integra Colombia:</span> {{date('d-m-Y', strtotime(Auth::user()->empresa()->suscripcion()->fec_corte))}}</p>
			@endif
		</div>
		<div class="col-sm-8 offset-md-2 {{$empresa->nomina ? 'd-none' : ''}}" id="alerta_nomina">
			<div class="alert alert-info" role="alert" style="color: #d08f50;background-color: #d08f5026;border-color: #d08f50;">
				<h4 class="alert-heading font-weight-bold">SUGERENCIA</h4>
				<p class="mb-0">Para hacer uso del módulo de <strong>Nómina</strong>, primero debe habilitarlo en la opción <strong>Nómina &gt; Habilitar nómina</strong>.</p>
			</div>
		</div>
	</div>

	@if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">Integra Colombia: Suscripción Vencida</h4>
	       <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
	    </div>
	@endif

	<div class="row card-description configuracion">
		<div class="col-sm-3 enlaces">
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
		<div class="col-sm-3 enlaces">
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
			<a href="#" data-toggle="modal" data-target="#formato_impresion">Formato de Impresión</a><br>
			<a href="javascript:facturacionAutomatica()">{{ Auth::user()->empresa()->factura_auto == 0 ? 'Habilitar':'Deshabilitar' }} Facturación Automática</a><br>
			<input type="hidden" id="facturaAuto" value="{{Auth::user()->empresa()->factura_auto}}">
            <a href="javascript:saldoFavorAutomatico()">{{ Auth::user()->empresa()->aplicar_saldofavor == 0 ? 'Habilitar':'Deshabilitar' }} aplicación de saldos a favor automático</a><br>
			<input type="hidden" id="saldofavAuto" value="{{Auth::user()->empresa()->aplicar_saldofavor}}">
            <a href="javascript:prorrateo()">{{ Auth::user()->empresa()->prorrateo == 0 ? 'Habilitar':'Deshabilitar' }} Prorrateo</a><br>
			<input type="hidden" id="prorrateoid" value="{{Auth::user()->empresa()->prorrateo}}">
			<a href="javascript:actDescEfecty()">{{ Auth::user()->empresa()->efecty == 0 ? 'Habilitar':'Deshabilitar' }} Efecty</a><br>
			<input type="hidden" id="efectyid" value="{{Auth::user()->empresa()->efecty}}">
			<a href="javascript:facturacionSmsAutomatica()">{{ Auth::user()->empresa()->factura_sms_auto == 0 ? 'Habilitar':'Deshabilitar' }} SMS automaticos</a><br>
			<input type="hidden" id="facturaSmsAuto" value="{{Auth::user()->empresa()->factura_sms_auto}}">
		</div>

		<div class="col-sm-3 enlaces">
			<h4 class="card-title">Impuestos</h4>
			<p>Define aquí los tipos de impuestos y retenciones que aplicas a tus facturas de venta.</p>
			<a href="{{route('impuestos.index')}}">Impuestos</a> <br>
			<a href="{{route('retenciones.index')}}">Retenciones</a><br>
			<a href="{{route('autoretenciones.index')}}">Autoretenciones</a><br>
		</div>

		<div class="col-sm-3 enlaces">
			<h4 class="card-title">Contactos</h4>
			<p>Registra aqui referencias para tus contactos.</p>
			<a href="{{route('tiposempresa.index')}}">Tipos de Contactos</a> <br>
		</div>

        {{-- Agregando campos adicionales a contactos --}}
        <div class="col-sm-3 enlaces">
			<h4 class="card-title">campos adicionales a Contactos</h4>
			<p>Añade aqui campos adicionales para el registro de tus contactos.</p>
			<a href="{{route('contact.new')}}">Añadir campos</a> <br>
		</div>
        {{-- fin del codigo --}}

		@if(isset($_SESSION['permisos']['845']))
		<div class="col-sm-3 enlaces">
			<h4 class="card-title">Contratos</h4>
			<p>Gestiona y organiza las configuraciones de contratos.</p>
			<a href="#" data-toggle="modal" data-target="#config_clausula">Definir Monto de Clausula de Permanencia</a><br>
			@if(isset($_SESSION['permisos']['751']))
			<a href="javascript:parametrosContratoDigital();">Parámetros Contrato Digital</a><br>
			<a href="javascript:facturacionCronAbiertas()">{{ Auth::user()->empresa()->cron_fact_abiertas == 0 ? 'Habilitar':'Deshabilitar' }} facturacion automatica fact. abiertas</a><br>
			<input type="hidden" id="cronAbierta" value="{{Auth::user()->empresa()->cron_fact_abiertas}}">

            {{-- Valor de reconexion generico --}}
            <a href="javascript:reconexionGenerica()">{{ Auth::user()->empresa()->reconexion_generica == 0 ? 'Habilitar':'Deshabilitar' }} Valor de reconexión genérico</a><br>
			<input type="hidden" id="reconexionGenerica" value="{{Auth::user()->empresa()->reconexion_generica}}">
            @if(Auth::user()->empresa()->reconexion_generica == 1)
            <a href="#" data-toggle="modal" data-target="#config_reconexion">Configurar reconexion genérica</a><br>
            @endif
            @endif
		</div>
		@endif

		<div class="col-sm-3 enlaces">
			<h4 class="card-title">Documentos Soporte</h4>
			<p>Configura la información de los documentos soporte por las compras que realices a sujetos no obligados a expedir factura.</p>
			@if($empresa->equivalente == 0)
			<a href="#" onclick="docEquivalente()">Habilitar documentos soporte</a><br>
			<input type="hidden" id="docEquivalente" value="{{$empresa->equivalente}}">
			@else
			<a href="#" onclick="docEquivalente()">Deshabilitar documentos soporte</a><br>
			<input type="hidden" id="docEquivalente" value="{{$empresa->equivalente}}">
			<a class="doc-equivalente-class" href="{{route('configuracion.numeraciones_equivalentes')}}">Numeraciones</a><br>
			@endif
		</div>

		<div class="col-sm-3 enlaces">
			<h4 class="card-title">Categorias</h4>
			<p>Organice a su medida el plan único de cuentas.</p>
			{{-- <a href="{{route('categorias.index')}}">Gestionar Categorias</a> <br> --}}
			<a href="{{route('puc.index')}}">Gestionar PUC</a> <br>
			<a href="{{route('formapago.index')}}">Formas de Pago</a> <br>
			<a href="{{route('anticipo.index')}}">Anticipos</a> <br>
			{{-- <a href="{{route('productoservicio.index')}}">Productos y Servicios</a> <br> --}}
			<a href="{{route('saldoinicial.index')}}">Comprobantes contables</a> <br>
		</div>
		@endif

		@if(isset($_SESSION['permisos']['737']))
		<div class="col-sm-3 enlaces">
			<h4 class="card-title">Tipos de Gastos</h4>
			<p>Organice los tipos de gastos que utilizará su empresa.</p>
			@if(isset($_SESSION['permisos']['737']))
			<a href="{{route('tipos-gastos.index')}}">Tipos de Gastos</a> <br>
			@endif
		</div>
		@endif

		<div class="col-sm-3 enlaces">
			<h4 class="card-title">Gestión de Puertos</h4>
			<p>Configura y organiza los puertos de conexión.</p>
			<a href="{{route('puertos-conexion.index')}}">Puertos de Conexión</a><br>
		</div>

		@if(isset($_SESSION['permisos']['752']))
		<div class="col-sm-3 enlaces">
			<h4 class="card-title">Gestión Servidor de Correo</h4>
			<p>Configura y organiza el servidor de correo externo para el envío de email y notificaciones.</p>
			<a href="{{route('servidor-correo.index')}}">Servidor de Correo</a><br>
		</div>
		@endif

		<div class="col-sm-3 enlaces">
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
		<div class="col-sm-3 enlaces">
			<h4 class="card-title">Administración OLT</h4>
			<p>Completa la información de la OLT de tu empresa.</p>
			<a href="#" data-toggle="modal" data-target="#config_olt">Configurar OLT</a><br>
		</div>
		@endif

		@if(isset($_SESSION['permisos']['762']) || isset($_SESSION['permisos']['763']) || isset($_SESSION['permisos']['764']))
		<div class="col-sm-3 enlaces">
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
			@if(isset($_SESSION['permisos']['798']))
			<a href="{{ route('integracion-gmaps.index') }}">Google Maps</a><br>
			@endif
			@if(isset($_SESSION['permisos']['764']) && Auth::user()->nombres == 'Desarrollo')
			<a href="#">Troncal SIP</a><br>
			@endif
		</div>
		@endif

		<div class="col-sm-3 enlaces">
			<h4 class="card-title">Oficinas</h4>
			<p>Configura la información relacionada a las oficinas de tu empresa.</p>
			<a href="javascript:actDescOficina()">{{ Auth::user()->empresa()->oficina == 0 ? 'Habilitar':'Deshabilitar' }} uso de oficinas en NetworkSoft</a><br>
			<input type="hidden" id="oficinaid" value="{{Auth::user()->empresa()->oficina}}">
		</div>

		@if(!Auth::user()->empresa()->suscripcion()->ilimitado)
		<div class="col-sm-3 enlaces">
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

		@if(isset($_SESSION['permisos']['750']))
		<div class="col-sm-3 enlaces">
			<h4 class="card-title">Organización de Tablas</h4>
			<p>Configura y organiza los campos de las tablas.</p>
			<a href="#" data-toggle="modal" data-target="#config_modulos">Organización de Tablas</a><br>
			{{-- <a href="{{route('campos.organizar', 3)}}">Inventario</a><br> --}}
			{{-- <a href="{{route('campos.organizar', 8)}}">Pagos Recurrentes</a><br> --}}
			<hr class="nomina">
			<a href="#" data-toggle="modal" data-target="#nro_registro">Configurar Nro registros a mostrar</a><br>
		</div>
		@endif

		<div class="col-sm-3 enlaces">
			<h4 class="card-title">Documentación</h4>
			<p>Documentos y guías de uso NetworkSoft.</p>
			<a href="https://networksoft.online/software/images/Empresas/Empresa1/contabilidad.pdf" target="_blank">Contabilidad</a> <br>
			<a href="{{asset('images/Empresas/Empresa1/Gestión Servidor De Correo.pdf')}}" target="_blank">Servidor De Correo</a> <br>
		</div>

		<div class="col-sm-3 enlaces">
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

	{{-- MÓDULOS --}}
	<div class="modal fade" id="config_modulos" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
                    <h4 class="modal-title">Organización de Tablas</h4>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12 text-center">
							<p>Seleccione el módulo a donde requiere hacer la configuración de la tabla</p>
						</div>
						<div class="col-md-6">
							<a href="{{route('campos.organizar', 1)}}">Contactos</a><br>
							<a href="{{route('campos.organizar', 2)}}">Contratos</a><br>
							<a href="{{route('campos.organizar', 4)}}">Factura de Venta</a><br>
							<a href="{{route('campos.organizar', 5)}}">Pagos / Ingresos</a><br>
							<a href="{{route('campos.organizar', 9)}}">Descuentos</a><br>
							<a href="{{route('campos.organizar', 6)}}">Factura de Proveedores</a><br>
							<a href="{{route('campos.organizar', 7)}}">Pagos / Egresos</a><br>
							<a href="{{route('campos.organizar', 18)}}">Notas de Crédito</a><br>
							<a href="{{route('campos.organizar', 19)}}">Cotizaciones</a><br>
							<a href="{{route('campos.organizar', 20)}}">Remisiones</a><br>
						</div>
						<div class="col-md-6">
							<a href="{{route('campos.organizar', 10)}}">Planes de Velocidad</a><br>
							<a href="{{route('campos.organizar', 11)}}">Promesas de Pago</a><br>
							<a href="{{route('campos.organizar', 12)}}">Radicados</a><br>
							<a href="{{route('campos.organizar', 13)}}">Monitor Blacklist</a><br>
							<a href="{{route('campos.organizar', 14)}}">Ventas Externas</a><br>
							<a href="{{route('campos.organizar', 15)}}">Mikrotik</a><br>
							<a href="{{route('campos.organizar', 16)}}">Bancos</a><br>
							<a href="{{route('campos.organizar', 17)}}">Oficinas</a><br>
							<a href="{{route('campos.organizar', 21)}}">Produtos</a><br>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
				</div>
			</div>
		</div>
	</div>
	{{-- /MÓDULOS --}}

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

    	{{-- CONFIGURACION RECONEXION GENERICA --}}
        <div class="modal fade" id="config_reconexion" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title"></h4>
                    </div>
                    <div class="modal-body">
                        <form method="POST" action="{{ route('configuracion.updatereconexiongenerica') }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form" >
                            {{ csrf_field() }}
                            <div class="row">
                                <div class="col-md-12 form-group">
                                    <label class="control-label">Días para cobro adicional</label>
                                    <input type="number" class="form-control"  id="dias_reconexion_generica" name="dias_reconexion_generica" value="{{Auth::user()->empresa()->dias_reconexion_generica}}"  maxlength="200">
                                    <span class="help-block error">
                                        <strong>{{ $errors->first('dias_reconexion_generica') }}</strong>
                                    </span>
                                </div>
                                <div class="col-md-12 form-group">
                                    <label class="control-label">Precio del cobro adicional</label>
                                    <input type="text" class="form-control"  id="precio_reconexion_generica" name="precio_reconexion_generica"  required="" value="{{Auth::user()->empresa()->precio_reconexion_generica}}" maxlength="200">
                                    <span class="help-block error">
                                        <strong>{{ $errors->first('precio_reconexion_generica') }}</strong>
                                    </span>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                        <a href="javascript:updateReconexionGenerica()" class="btn btn-success">Guardar</A>
                    </div>
                </div>
            </div>
        </div>
        {{-- CONFIGURACION RECONEXION GENERICA --}}

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
								<label class="control-label">URL (ejemplo: https://dominio.smartolt.com)</label>
								<input type="text" class="form-control"  id="adminOLT" name="adminOLT" value="{{Auth::user()->empresa()->adminOLT}}"  maxlength="200">
								<span class="help-block error">
									<strong>{{ $errors->first('adminOLT') }}</strong>
								</span>
							</div>
							<div class="col-md-12 form-group">
								<label class="control-label">ApiKey Smart OLT</label>
								<input type="text" class="form-control"  id="smartOLT" name="smartOLT"  required="" value="{{Auth::user()->empresa()->smartOLT}}" maxlength="200">
								<span class="help-block error">
									<strong>{{ $errors->first('smartOLT') }}</strong>
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
							<option value="3" {{ Auth::user()->empresa()->periodo_facturacion == 3 ? 'selected' : '' }}>Mes Actual</option>
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

	{{-- FORMATO IMPRESION --}}
	<div class="modal fade show" id="formato_impresion" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title">Configurar formato de impresión</h4>
				</div>
				<div class="modal-body">
					<p>Indique el formato de impresión</p>
					<div class="col-sm-6 offset-sm-3">
						<select class="form-control selectpicker" name="formato_impresion" id="val_formato_impresion" required="" title="Seleccione" data-live-search="true" data-size="5">
							<option value="1" {{ Auth::user()->empresa()->formato_impresion == 1 ? 'selected' : '' }}>Formato CRC</option>
							<option value="2" {{ Auth::user()->empresa()->formato_impresion == 2 ? 'selected' : '' }}>Formato Estándar</option>
						</select>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Cerrar</button>
					<button type="button" class="btn btn-success" onclick="storeFormatoImpresion()">Guardar</button>
				</div>
			</div>
		</div>
	</div>
	{{-- /FORMATO IMPRESION --}}

	{{-- CONFIGURACION CLAUSULAS --}}
	<div class="modal fade" id="config_clausula" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal">&times;</button>
					<h4 class="modal-title"></h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-md-12 form-group">
							<label class="control-label">Indique el monto a establecer de Clausula de Permanencia</label>
							<input type="number" class="form-control" id="clausula_permanencia" name="clausula_permanencia" value="{{Auth::user()->empresa()->clausula_permanencia}}" min="0">
							<span class="help-block error">
								<strong>{{ $errors->first('clausula_permanencia') }}</strong>
							</span>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
					<a href="javascript:configurarClausula()" class="btn btn-success">Guardar</A>
				</div>
			</div>
		</div>
	</div>
	{{-- /CONFIGURACION CLAUSULAS --}}

	{{-- /CONFIGURACION CONTRATO DIGITAL --}}
	<div class="modal fade" id="modal_parametrosContratoDigital"  tabindex="-1" role="dialog">
        <div class="modal-dialog" style="max-width: 50%;">
            <div class="modal-content">
                <div class="modal-body">
                	<form method="POST" action="{{ route('asignaciones.campos_asignacion') }}" role="form" class="forms-sample" id="form_contrato" >
                        @csrf
	                	<ul class="nav nav-pills" id="pills-tab" role="tablist">
	                		<li class="nav-item">
	                			<a class="nav-link active" id="pills-asignacion-tab" data-toggle="pill" href="#pills-asignacion" role="tab" aria-controls="pills-asignacion" aria-selected="true">Asignación de Contrato</a>
	                		</li>
	                		<li class="nav-item">
	                			<a class="nav-link" id="pills-contrato-tab" data-toggle="pill" href="#pills-contrato" role="tab" aria-controls="pills-contrato" aria-selected="false">Contrato Digital</a>
	                		</li>
	                	</ul>

	                	<hr style="border-top: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}; margin: .5rem 0rem;">

					    <div class="tab-content mt-4" id="pills-tabContent">
					        <div class="tab-pane fade show active" id="pills-asignacion" role="tabpanel" aria-labelledby="pills-asignacion-tab">
					            <div class="row">
		                            <div class="form-group col-md-6 offset-md-3">
		                                <label class="control-label">Campo Principal</label>
		                                <input type="text" class="form-control" name="campo_1" id="campo_1">
		                                <span class="help-block error">
		                                    <strong>{{ $errors->first('campo_1') }}</strong>
		                                </span>
		                            </div>
		                        </div>
		                        <div class="row">
		                            <div class="form-group col-md-3">
		                                <label class="control-label">Campo A</label>
		                                <input type="text" class="form-control" name="campo_a" id="campo_a">
		                                <span class="help-block error">
		                                    <strong>{{ $errors->first('campo_a') }}</strong>
		                                </span>
		                            </div>
		                            <div class="form-group col-md-3">
		                                <label class="control-label">Campo B</label>
		                                <input type="text" class="form-control" name="campo_b" id="campo_b">
		                                <span class="help-block error">
		                                    <strong>{{ $errors->first('campo_b') }}</strong>
		                                </span>
		                            </div>
		                            <div class="form-group col-md-3">
		                                <label class="control-label">Campo C</label>
		                                <input type="text" class="form-control" name="campo_c" id="campo_c">
		                                <span class="help-block error">
		                                    <strong>{{ $errors->first('campo_c') }}</strong>
		                                </span>
		                            </div>
		                            <div class="form-group col-md-3">
		                                <label class="control-label">Campo D</label>
		                                <input type="text" class="form-control" name="campo_d" id="campo_d">
		                                <span class="help-block error">
		                                    <strong>{{ $errors->first('campo_d') }}</strong>
		                                </span>
		                            </div>

		                            <div class="form-group col-md-3">
		                                <label class="control-label">Campo E</label>
		                                <input type="text" class="form-control" name="campo_e" id="campo_e">
		                                <span class="help-block error">
		                                    <strong>{{ $errors->first('campo_e') }}</strong>
		                                </span>
		                            </div>
		                            <div class="form-group col-md-3">
		                                <label class="control-label">Campo F</label>
		                                <input type="text" class="form-control" name="campo_f" id="campo_f">
		                                <span class="help-block error">
		                                    <strong>{{ $errors->first('campo_f') }}</strong>
		                                </span>
		                            </div>
		                            <div class="form-group col-md-3">
		                                <label class="control-label">Campo G</label>
		                                <input type="text" class="form-control" name="campo_g" id="campo_g">
		                                <span class="help-block error">
		                                    <strong>{{ $errors->first('campo_g') }}</strong>
		                                </span>
		                            </div>
		                            <div class="form-group col-md-3">
		                                <label class="control-label">Campo H</label>
		                                <input type="text" class="form-control" name="campo_h" id="campo_h">
		                                <span class="help-block error">
		                                    <strong>{{ $errors->first('campo_h') }}</strong>
		                                </span>
		                            </div>
		                        </div>
					        </div>
					        <div class="tab-pane fade" id="pills-contrato" role="tabpanel" aria-labelledby="pills-contrato-tab">
					        	<div class="row">
					        		<div class="form-group col-md-12">
		                                <label class="control-label">Contrato Digital</label>
		                                <textarea class="form-control" name="contrato_digital" id="contrato_digital" rows="6"></textarea>
		                            </div>
		                            <div class="form-group col-md-12 d-none">
		                                <label class="control-label">ANEXO 1</label>
		                                <textarea class="form-control" name="anexo_1" id="anexo_1" rows="6"></textarea>
		                            </div>
		                            <div class="form-group col-md-12 d-none">
		                                <label class="control-label">ANEXO 2</label>
		                                <textarea class="form-control" name="anexo_2" id="anexo_2" rows="6"></textarea>
		                            </div>
		                            <div class="form-group col-md-12 d-none">
		                                <label class="control-label">ANEXO 3</label>
		                                <textarea class="form-control" name="anexo_3" id="anexo_3" rows="6"></textarea>
		                            </div>
		                            <div class="form-group col-md-12 d-none">
		                                <label class="control-label">ANEXO 4</label>
		                                <textarea class="form-control" name="anexo_4" id="anexo_4" rows="6"></textarea>
		                            </div>
				        	   </div>
					        </div>
					    </div>

					    <hr>

					    <div class="row">
					    	<div class="col-sm-12" style="text-align: right;">
					    		<button type="button" class="btn btn-outline-secondary" data-dismiss="modal" id="cancelar">Cancelar</button>
					    		<a href="javascript:void(0);" class="btn btn-success" id="guardar">Guardar</a>
					    	</div>
					    </div>
				    </form>
                </div>
            </div>
        </div>
    </div>
    {{-- /CONFIGURACION CONTRATO DIGITAL --}}
@endsection

@section('scripts')
    <script>

	function docEquivalente() {
		if ($("#docEquivalente").val() == 1) {
			$titleswal = "¿Deshabilitar documentos soporte?";
			$textswal = "Ya no podrá crear documentos soporte desde las facturas de proveedores";
			$confirmswal = "Si, Deshabilitar";
		} else {
			$titleswal = "¿Habilitar documentos soporte?";
			$textswal = "Tendrá la opcion de escoger el tipo de documento equivalente desde crear facturas de proveedores.";
			$confirmswal = "Si, Habilitar";
		}

		if (window.location.pathname.split("/")[1] === "software") {
			var url='/software/empresa';
            }else{
            var url = '/empresa';
            }

		Swal.fire({
			title: $titleswal,
			text: $textswal,
			type: 'warning',
			showCancelButton: true,
			confirmButtonColor: '#3085d6',
			cancelButtonColor: '#d33',
			cancelButtonText: 'Cancelar',
			confirmButtonText: $confirmswal,
		}).then((result) => {
			if (result.value) {

				$.ajax({
					url: url+'/configuracion/configuracion_actdesc_equivalentes',
					headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
					method: 'post',
					data: { status: $("#docEquivalente").val() },
					success: function(data) {

						if (data == 1) {
							Swal.fire({
								position: 'top-center',
								type: 'success',
								title: 'Documentos Soporte habilitados',
								showConfirmButton: false,
								timer: 2500
							})
							$("#docEquivalente").val(1);

						} else {
							Swal.fire({
								position: 'top-center',
								type: 'success',
								title: 'Documentos Soporte Deshabilitados',
								showConfirmButton: false,
								timer: 2500
							})
							$("#docEquivalente").val(0);
						}

						setTimeout(function() {
							location.reload();
						}, 2500);

					}
				});

			}
		})
	}

    	function storePeriodoFacturacion() {
    		cargando(true);
    		if (window.location.pathname.split("/")[1] === "software") {
    			var url = `/software/empresa/configuracion/storePeriodoFacturacion`;
    		}
			else if(window.location.pathname.split("/")[1] === "portal"){
				var url = `/portal/empresa/configuracion/storePeriodoFacturacion`;
			}
			else{
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

		function storeFormatoImpresion(){
			cargando(true);
    		if (window.location.pathname.split("/")[1] === "software") {
    			var url = `/software/empresa/configuracion/storeFormatoImpresion`;
    		}else{
    			var url = `/empresa/configuracion/storeFormatoImpresion`;
    		}
    		$.ajax({
    			url: url,
    			method: 'GET',
    			headers: {
    				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    			},
    			data: {
    				formato_impresion: $('#val_formato_impresion').val()
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
    					$("#formato_impresion").modal('hide');
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

        function reconexionGenerica(){
            if (window.location.pathname.split("/")[1] === "software") {
				var url='/software/configuracion_reconexiongenerica';
			}else{
				var url = '/configuracion_reconexiongenerica';
			}

		    if ($("#reconexionGenerica").val() == 0) {
		        $titleswal = "¿Desea habilitar la reconexión genérica?";
		    }

		    if ($("#reconexionGenerica").val() == 1) {
		        $titleswal = "¿Desea deshabilitar la reconexión genérica?";
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
		                data: { status: $("#reconexionGenerica").val() },
		                success: function (data) {
		                    console.log(data);
		                    if (data == 1) {
		                        Swal.fire({
		                            type: 'success',
		                            title: 'Reconexión Genérica para los contratos habilitada',
		                            showConfirmButton: false,
		                            timer: 5000
		                        })
		                        $("#reconexionGenerica").val(1);
		                    } else {
		                        Swal.fire({
		                            type: 'success',
		                            title: 'Reconexión Genérica para los contratos deshabilitada',
		                            showConfirmButton: false,
		                            timer: 5000
		                        })
		                        $("#reconexionGenerica").val(0);
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

        function updateReconexionGenerica() {
			if (window.location.pathname.split("/")[1] === "software") {
				var url='/software/updatereconexiongenerica';
			}else{
				var url = '/updatereconexiongenerica';
			}

            $.ajax({
                url: url,
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                method: 'post',
                data: {
                	dias_reconexion_generica: $("#dias_reconexion_generica").val(),
                	precio_reconexion_generica: $("#precio_reconexion_generica").val()
                },
                success: function (data) {
                	$("#config_reconexion").modal('hide');
					if(data == 1){
						Swal.fire({
                		type: 'success',
                		title: 'La configuración de la reconexión genérica ha sido registrada.',
                		text: 'Recargando la página',
                		showConfirmButton: false,
                		timer: 5000
                		})
					}else{
						Swal.fire({
                		type: 'error',
                		title: 'Error al actualizar la reconexión genérica',
                		text: 'Recargando la página',
                		showConfirmButton: false,
                		timer: 5000
                		})
					}

                    setTimeout(function(){
                    	var a = document.createElement("a");
                    	a.href = window.location.pathname;
                    	a.click();
                    }, 2000);
                }
            });
		}

        function saldoFavorAutomatico() {
			if (window.location.pathname.split("/")[1] === "software") {
				var url='/software/configuracion_aplicacionsaldosfavor';
			}else{
				var url = '/configuracion_aplicacionsaldosfavor';
			}

		    if ($("#saldofavAuto").val() == 0) {
		        $titleswal = "¿Desea habilitar la aplicacion de saldos a favor automaticamente?";
		    }

		    if ($("#saldofavAuto").val() == 1) {
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
		                data: { status: $("#saldofavAuto").val() },
		                success: function (data) {
		                    console.log(data);
		                    if (data == 1) {
		                        Swal.fire({
		                            type: 'success',
		                            title: 'Aplicación de saldos a favor automáticamente habilitada',
		                            showConfirmButton: false,
		                            timer: 5000
		                        })
		                        $("#saldofavAuto").val(1);
		                    } else {
		                        Swal.fire({
		                            type: 'success',
		                            title: 'Aplicación de saldos a favor automáticamente deshabilitada',
		                            showConfirmButton: false,
		                            timer: 5000
		                        })
		                        $("#saldofavAuto").val(0);
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

        function facturacionCronAbiertas() {
			if (window.location.pathname.split("/")[1] === "software") {
				var url='/software/configuracion_factcronabiertas';
			}else{
				var url = '/configuracion_factcronabiertas';
			}

		    if ($("#cronAbierta").val() == 0) {
		        $titleswal = "¿Desea habilitar la creación de facturas así la última factura esté abierta?";
		    }

		    if ($("#cronAbierta").val() == 1) {
		        $titleswal = "¿Desea Deshabilitar la creación de facturas así la última factura esté abierta?";
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
		                data: { status: $("#cronAbierta").val() },
		                success: function (data) {
		                    console.log(data);
		                    if (data == 1) {
		                        Swal.fire({
		                            type: 'success',
		                            title: 'Creacion de factruas actualizada correctamente.',
		                            showConfirmButton: false,
		                            timer: 5000
		                        })
		                        $("#cronAbierta").val(1);
		                    } else {
		                        Swal.fire({
		                            type: 'success',
		                            title: 'Creacion de facturas actualizada correctamente.',
		                            showConfirmButton: false,
		                            timer: 5000
		                        })
		                        $("#cronAbierta").val(0);
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

		function facturacionSmsAutomatica() {
			if (window.location.pathname.split("/")[1] === "software") {
				var url='/software/configuracion_facturacionSmsAutomatica';
			}else{
				var url = '/configuracion_facturacionSmsAutomatica';
			}

		    if ($("#facturaSmsAuto").val() == 0) {
		        $titleswal = "¿Desea habilitar el envio de SMS automaticos?";
		    }

		    if ($("#facturaSmsAuto").val() == 1) {
		        $titleswal = "¿Desea deshabilitar el envio de SMS automaticos?";
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
		                data: { status: $("#facturaSmsAuto").val() },
		                success: function (data) {
		                    console.log(data);
		                    if (data == 1) {
		                        Swal.fire({
		                            type: 'success',
		                            title: 'Envio de sms automaticos habilitado',
		                            showConfirmButton: false,
		                            timer: 5000
		                        })
		                        $("#facturaAuto").val(1);
		                    } else {
		                        Swal.fire({
		                            type: 'success',
		                            title: 'Envio de sms automaticos deshabilitada',
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
                method: 'get',
                data: {
                	smartOLT: $("#smartOLT").val(),
                	adminOLT: $("#adminOLT").val()
                },
                success: function (data) {
                	$("#config_olt").modal('hide');
					if(data == 1){
						Swal.fire({
                		type: 'success',
                		title: 'La configuración de la OLT ha sido registrada con éxito',
                		text: 'Recargando la página',
                		showConfirmButton: false,
                		timer: 5000
                		})
					}else{
						Swal.fire({
                		type: 'error',
                		title: 'Error en la conexión, revise la ApiKey',
                		text: 'Recargando la página',
                		showConfirmButton: false,
                		timer: 5000
                		})
					}

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

		function actDescOficina() {
			if (window.location.pathname.split("/")[1] === "software") {
				var url='/software/oficina';
			}else{
				var url = '/oficina';
			}

		    if ($("#oficinaid").val() == 0) {
		        $titleswal = "¿Desea habilitar el uso de oficinas?";
		    }

		    if ($("#oficinaid").val() == 1) {
		        $titleswal = "¿Desea deshabilitar el uso de oficinas?";
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
		                data: { oficina: $("#oficinaid").val() },
		                success: function (data) {
		                    console.log(data);
		                    if (data == 1) {
		                        Swal.fire({
		                            type: 'success',
		                            title: 'Uso de oficinas en NetworkSoft habilitada',
		                            showConfirmButton: false,
		                            timer: 5000
		                        })
		                        $("#oficinaid").val(1);
		                    } else {
		                        Swal.fire({
		                            type: 'success',
		                            title: 'Uso de oficinas en NetworkSoft deshabilitada',
		                            showConfirmButton: false,
		                            timer: 5000
		                        })
		                        $("#oficinaid").val(0);
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

		function configurarClausula() {
    		cargando(true);
    		if (window.location.pathname.split("/")[1] === "software") {
    			var url = `/software/clausula_permanencia`;
    		}else{
    			var url = `/clausula_permanencia`;
    		}
    		$.ajax({
    			url: url,
    			method: 'POST',
    			headers: {
    				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    			},
    			data: {
    				clausula_permanencia: $('#clausula_permanencia').val()
    			},
    			success: function(response) {
    				cargando(false);
    				swal({
    					title: response.message,
    					text: response.text,
    					type: response.type,
    					showConfirmButton: true,
    					confirmButtonColor: '#1A59A1',
    					confirmButtonText: 'ACEPTAR',
    				});
    				if (response.success == true) {
    					$("#config_clausula").modal('hide');
    					setTimeout(function(){
    						location.reload();
    					}, 1000);
    				}
    			}
    		});
    	}

    	function parametrosContratoDigital(){
    		cargando(true);
            var url = 'asignaciones/config_campos_asignacion';
            $.get(url,function(data){
                data = JSON. parse(data);
                $("#campo_a").val(data.campo_a);
                $("#campo_b").val(data.campo_b);
                $("#campo_c").val(data.campo_c);
                $("#campo_d").val(data.campo_d);
                $("#campo_e").val(data.campo_e);
                $("#campo_f").val(data.campo_f);
                $("#campo_g").val(data.campo_g);
                $("#campo_h").val(data.campo_h);
                $("#campo_1").val(data.campo_1);
                $("#contrato_digital").val(data.contrato_digital);
                $("#anexo_1").val(data.anexo_1);
                $("#anexo_2").val(data.anexo_2);
                $("#anexo_3").val(data.anexo_3);
                $("#anexo_4").val(data.anexo_4);
            });
    		cargando(false);
            $('#modal_parametrosContratoDigital').modal("show");
        }

    	$(document).ready(function () {
            $("#guardar").click(function (form) {
                $.post($("#form_contrato").attr('action'), $("#form_contrato").serialize(), function (data) {
                    console.log(data);
                    if(data.success == true){
                        $('#cancelar').click();
                        $('#form_contrato').trigger("reset");
                        swal("Configuración Almacenada", "", "success");
                    } else {
                        swal('ERROR', 'Intente nuevamente', "error");
                    }
                }, 'json');
            });
        });
    </script>
@endsection

@section('style')
    <style>
    	.nav-tabs .nav-link {
    		font-size: 1em;
    	}
    	.nav-tabs .nav-link.active, .nav-tabs .nav-item.show .nav-link {
    		background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
    		color: #fff!important;
    	}
    	.nav-pills .nav-link.active, .nav-pills .show > .nav-link {
    		color: #fff!important;
    		background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}!important;
    	}
    	.nav-pills .nav-link {
    		font-weight: 700!important;
    	}
    	.nav-pills .nav-link{
    		color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}!important;
    		background-color: #f9f9f9!important;
    		margin: 2px;
    		border: 1px solid {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}};
    		transition: 0.4s;
    	}
    	.nav-pills .nav-link:hover {
    		color: #fff!important;
    		background-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}}!important;
    	}
    </style>
@endsection
