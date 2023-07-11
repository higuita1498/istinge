@extends('layouts.app')

@section('content')

<div class="row card-description">
	<div class="col-sm-4" style="text-align: center;">
		<img class="img-responsive" src="{{asset('images/Empresas/Empresa'.Auth::user()->empresa()->id.'/'.Auth::user()->empresa()->logo)}}" alt="" style="width: 100%">
	</div>
	<div class="col-sm-8">
		<p  class="card-title"> <span class="text-primary">Empresa:</span> {{Auth::user()->empresa()->nombre}} <br>
			<span class="text-primary">{{Auth::user()->empresa()->tip_iden()}}:</span>  {{Auth::user()->empresa()->nit}}<br>
			<span class="text-primary">Tipo de Persona:</span>  {{Auth::user()->empresa()->tipo_persona()}}<br>
			<span class="text-primary">Teléfno:</span>  {{Auth::user()->empresa()->telefono}}<br>
			<span class="text-primary">Dirección:</span>  {{Auth::user()->empresa()->direccion}}<br>
			<span class="text-primary">Correo Electrónico:</span>  {{Auth::user()->empresa()->email}}</p>
		</div>
	</div>
	<div class="row card-description configuracion">
		<div class="col-sm-3">
			<h4 class="card-title">Pedidos</h4>
			<p>Mira los pedidos que se han realizado desde la pagina web.</p>
			<a href="{{route('PaginaWeb.pedidos')}}">Ver Pedidos</a> <br>
		</div>
		<div class="col-sm-3">
			<h4 class="card-title">Comentarios</h4>
			<p>Mira los comentarios a los productos que se han realizado desde la pagina web.</p>
			<a href="{{route('PaginaWeb.comentarios')}}">Ver Comentarios</a> <br>
		</div>
		<div class="col-sm-3">
			<h4 class="card-title">Usuarios Registrados</h4>
			<p>Mira la información básica de las personas que se registran en tu web.</p>
			<a href="{{route('PaginaWeb.personas')}}">Ver usuarios</a> <br>
		</div>
		<div class="col-sm-3">
			<h4 class="card-title">Google Analytics</h4>
			<p>Mira las estadisticas de tu web.</p>
			<a href="{{route('Google.index')}}">Ver estadisticas</a> <br>
		</div>
		<div class="col-sm-3">
			<h4 class="card-title">Complementos</h4>
			<p>¡Tenemos diferetes complementos que te pueden servir!.</p>
			<a target="_blank" href="https://landing.gestordepartes.com/login">Gestor Architect</a> <br>
		</div>
	</div>
	@endsection