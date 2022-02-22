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
			{{-- <a href="#"  data-toggle="modal" data-target="#seguridad">Seguridad</a><br> --}}
		</div>

		@if(isset($_SESSION['permisos']['40']) || isset($_SESSION['permisos']['258']))
		<div class="col-sm-3">
			<h4 class="card-title">Facturación</h4>
			<p>Configura la información que se mostrará en tus facturas de venta.</p>
			<a href="{{route('configuracion.terminos')}}">Términos de pago</a> <br>
			<a href="{{route('configuracion.numeraciones')}}">Numeraciones</a><br>
			<a href="{{route('configuracion.datos')}}">Datos generales</a><br>
			<a href="{{route('vendedores.index')}}">Vendedores</a><br>
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
		</div>
		@endif
		
		@if(isset($_SESSION['permisos']['429']) || isset($_SESSION['permisos']['433']))
		<div class="col-sm-3">
			<h4 class="card-title">Mikrotik</h4>
			<p>Organice a su medida su conexión Mikrotik.</p>
			@if(isset($_SESSION['permisos']['429']))
			<a href="{{route('mikrotik.index')}}">Gestionar Mikrotik</a> <br>
			@endif
			@if(isset($_SESSION['permisos']['433']))
			<a href="{{route('planes-velocidad.index')}}">Gestionar Planes</a> <br>
			@endif
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

	{{-- Modal contacto nuevo --}}
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
							<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						</div>
					</div>
				</div>
			</div>
			{{-- /Modal contacto nuevo --}}
	@endsection
