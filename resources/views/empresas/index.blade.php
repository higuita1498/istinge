@extends('layouts.app')
@section('boton')	
	@if(!isset($inactivas))
		<a href="{{route('empresas.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nueva Empresa</a>
		<a href="{{route('empresas.inactivas')}}" class="btn btn-outline-light btn-sm" >Empresas Inactivas</a>
	@endif
	
@endsection		
@section('content')
	@if(Session::has('success'))
		<div class="alert alert-success" style="margin-left: 2%;margin-right: 2%;">
			{{Session::get('success')}}
		</div>
	@endif
	<div class="row card-description">
		<div class="col-md-12">
			<table class="table table-striped table-hover" id="example">
			<thead class="thead-dark">
				<tr>
				  <th>ID</th>
	              <th>Logo</th>
	              <th>Tipo de identificación</th>
	              <th>Identificación</th>
				  <th>Nombre</th>
				  <th>Dian</th>
				  <th>Emitidas</th>
	              <th>Tipo de Persona</th>
	              <th>Telefono</th>
	              <th>Correo Electrónico</th>
	              <th>Usuario</th>
	              <th>Fecha Creación</th>
	              <th>Acciones</th>
	          </tr>                              
			</thead>
			<tbody>
				@foreach($empresas as $empresa)
					<tr @if($empresa->id==Session::get('empresa_id')) class="active" @endif>
						<td>{{$empresa->id}}</td>
						<td>@if($empresa->logo)							
	                        <div class="project-wrapper" style=" width: 50%;">
	                          <div class="project">
	                            <div class="photo-wrapper">
	                                <div class="photo" style="background: #fff; padding-left: 20%;"><img class="img-contenida" src="{{asset('images/Empresas/Empresa'.$empresa->id.'/'.$empresa->logo)}}" alt="">
	                                </div>
	                                <div class="overlay"></div>
	                            </div>
	                          </div>
	                      </div>
	                      @else
	                      Sin Logo
	                      @endif
						</td>
						<td>{{$empresa->tip_iden()}}</td>
						<td>{{$empresa->nit}}</td>
						<td>{{$empresa->nombre}}</td>
						<td>@if($empresa->estado_dian == 1) <strong class="text-success">Activo</strong> @else <strong class="text-danger">Innactivo</strong> @endif</td>
						<td>{{$empresa->totalEmissions()}}</td>
                        <td>{{$empresa->tipo_persona()}}</td>
                        <td>{{$empresa->telefono}}</td>
                        <td>{{$empresa->email}}</td>
                        <td>{{$empresa->usuario()?$empresa->usuario()->username:'- - -'}}</td>
						<td>{{date('d-m-Y h:m a', strtotime($empresa->created_at))}}</td>
						<td><a href="{{route('empresas.edit',$empresa->id)}}" class="btn btn-outline-primary btn-icons"><i class="fas fa-edit"></i></a>
							@if($empresa->status==1) 
								<form action="{{ route('empresas.desactivar',$empresa->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="desactivar-empresa{{$empresa->id}}">
            						{{ csrf_field() }}
        						</form>	
            					<button class="btn btn-outline-danger  btn-icons" type="button" title="Desactivar" onclick="confirmar('desactivar-empresa{{$empresa->id}}', '¿Estas seguro que deseas desactivar la empresa?', 'Se enviara a empresas inactivas');"><i class="fas fa-power-off"></i></button>
    						@else
    							<form action="{{ route('empresas.activar',$empresa->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="activar-empresa{{$empresa->id}}">
            						{{ csrf_field() }}
            						
        						</form>
        						<button class="btn btn-outline-success  btn-icons" type="submit" title="Activar" onclick="confirmar('activar-empresa{{$empresa->id}}', '¿Estas seguro que deseas activar la empresa?');"><i class="fas fa-power-off"></i></button>
    						@endif
                            <a title="Ingresar" class="btn btn-outline-success  btn-icons" href="{{route('empresas.ingresar', $empresa->email)}}"><i class="fas fa-sign-in-alt"></i></a>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
		</div>
	</div>
@endsection