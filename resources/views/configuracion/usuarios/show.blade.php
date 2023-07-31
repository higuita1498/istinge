@extends('layouts.app')

@section('content')
<div class="row card-description">
	<div class="col-md-10">
		<div class="table-responsive">
			<table class="table table-striped table-bordered table-sm">
				<tbody>
					<tr>
						<td width="20%">Código</td>
						<td>{{$usuario->nro}}</td>
					</tr>
					<tr>
						<td>Nombre y Apellido</td>
						<td>{{$usuario->nombres}}</td>
					</tr>
					<tr>
						<td>Correo Electrónico</td>
						<td>{{$usuario->email}}</td>
					</tr>
					<tr>
						<td>Nombre de Usuario</td>
						<td>{{$usuario->username}}</td>
					</tr>
					<tr>
						<td>Rol de Usuario</td>
						<td>{{$rol->rol}}</td>
					</tr>
					<tr>
						<td>Oficina Asociada</td>
						<td>{{$usuario->oficina ? $usuario->oficina()->nombre : 'N/A'}}</td>
					</tr>
					<tr>
						<td>Acciones</td>
						<td><a href="{{route('usuarios.edit',$usuario->id)}}" class="btn btn-outline-primary btn-icons" title="Editar Usuario"><i class="fas fa-edit"></i></a></td>
					</tr>
					
				</tbody>

				
			</table>
		</div>
		
	</div>
	<div class="col-md-2" style="text-align: center;">
		@if($usuario->image)
			<img class="img-responsive" src="{{asset('images/Empresas/Empresa'.$usuario->empresa.'/usuarios/'.$usuario->image)}}" alt="" style="    width: 100%;">
		@else
			<img class="img-responsive" src="{{asset('images/no-user-image.png')}}" alt="" style="    width: 100%;">
      	@endif
		
	</div>
</div>
@endsection