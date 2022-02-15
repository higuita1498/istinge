@extends('layouts.app')
@section('boton')
    <a href="{{route('mikrotik.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nueva Mikrotik</a>
@endsection

@section('content')
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

	<div class="row card-description">
		<div class="col-md-12">
			<table class="table table-striped table-hover" id="example">
			<thead class="thead-dark">
				<tr>
				    <th>Nombre</th>
				    <th class="text-center">IP</th>
				    <th class="text-center">Puerto WEB</th>
				    <th class="text-center">Puerto API</th>
				    <th class="text-center">Estado</th>
				    <th class="text-center">Acciones</th>
	          </tr>                              
			</thead>
			<tbody>
				@foreach($mikrotiks as $mikrotik)
					<tr @if($mikrotik->id==Session::get('mikrotik_id')) class="active" @endif>
						<td><a href="{{route('mikrotik.show',$mikrotik->id)}}">{{$mikrotik->nombre}}</a></td>
						<td class="text-center">{{$mikrotik->ip}}</td>
						<td class="text-center">{{$mikrotik->puerto_web}}</td>
						<td class="text-center">{{$mikrotik->puerto_api}}</td>
						<td class="font-weight-bold text-center text-{{$mikrotik->status('true')}}">{{$mikrotik->status()}}</td>
						<td class="text-center">
			                @if($mikrotik->status == 0)
			                <form action="{{route('mikrotik.conectar',$mikrotik->id)}}" method="get" class="delete_form" style="margin:  0;display: inline-block;" id="conectar-{{$mikrotik->id}}">
			                    @csrf
			                </form>
			                <form action="{{ route('mikrotik.destroy',$mikrotik->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-mikrotik">
			                    @csrf
			                    <input name="_method" type="hidden" value="DELETE">
			                </form>
			                @endif
						    @if($mikrotik->status == 1)
						    <form action="{{route('mikrotik.reglas',$mikrotik->id)}}" method="get" class="delete_form" style="margin:  0;display: inline-block;" id="regla-{{$mikrotik->id}}">
			                    @csrf
			                </form>
			                <form action="{{route('mikrotik.importar',$mikrotik->id)}}" method="get" class="delete_form" style="margin:  0;display: inline-block;" id="importar-{{$mikrotik->id}}">
			                    @csrf
			                </form>
			                @endif
			                
						    <a title="Editar" href="{{route('mikrotik.edit',$mikrotik->id)}}" class="btn btn-outline-primary btn-icons"><i class="fas fa-edit"></i></a>
						    <a title="Ver detalles" href="{{route('mikrotik.show',$mikrotik->id)}}" class="btn btn-outline-info btn-icons"><i class="fas fa-eye"></i></a>
						    
						    @if($mikrotik->status == 0)
						        <button title="Conectar Mikrotik" class="btn btn-outline-success btn-icons" type="submit" onclick="confirmar('conectar-{{$mikrotik->id}}', '¿Está seguro que desea conectar la Mikrotik?', '');"><i class="fas fa-plug"></i></button>
						    @endif
						    @if($mikrotik->status == 1)
						    <a href="{{ route('mikrotik.grafica',$mikrotik->id )}}" class="btn btn-outline-danger btn-icons" title="Gráfica de Consumo"><i class="fas fa-chart-area"></i></a>
						    <button title="Aplicar Reglas" class="btn btn-outline-dark btn-icons" type="submit" onclick="confirmar('regla-{{$mikrotik->id}}', '¿Está seguro que desea aplicar las reglas a esta Mikrotik?', '');"><i class="fas fa-plus"></i></button>
						    <button title="Importar Contratos" class="btn btn-outline-info btn-icons d-none" type="submit" onclick="confirmar('importar-{{$mikrotik->id}}', '¿Está seguro que desea importar todos los contratos desde {{$mikrotik->nombre}}?', '');"><i class="fas fa-sync"></i></button>
						    @endif
						    @if($mikrotik->status == 0)
						        <button class="btn btn-outline-danger btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-mikrotik', '¿Está seguro que deseas eliminar el Mikrotik?', 'Se borrará de forma permanente');"><i class="fas fa-times"></i></button>
						    @endif
						    <a title="IP's Autorizadas" href="{{ route('mikrotik.ips-autorizadas',$mikrotik->id )}}" class="btn btn-outline-warning btn-icons"><i class="fas fa-project-diagram"></i></a>
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
		</div>
	</div>
@endsection