@extends('layouts.app')

@section('boton')
    @if(isset($_SESSION['permisos']['770']))
	<a href="{{route('canales.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Canal</a>
	@endif
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
			}, 5000);
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
			}, 5000);
		</script>
	@endif

	<div class="row card-description">
		<div class="col-md-12">
			<table class="table table-striped table-hover" id="example">
			<thead class="thead-dark">
				<tr>
	              <th>Nombre</th>
	              <th>Observaciones</th>
	              <th>Status</th>
	              <th>Acciones</th>
	          </tr>
			</thead>
			<tbody>
				@foreach($canales as $canal)
					<tr @if($canal->id==Session::get('canal_id')) class="active_table" @endif>
						<td>{{$canal->nombre}}</td>
						<td>{{$canal->observaciones}}</td>
						<td class="font-weight-bold text-{{$canal->status('true')}}">{{$canal->status()}}</td>
						<td>
							<form action="{{ route('canales.act_desc',$canal->id) }}" method="POST" class="delete_form" style="margin:  0;display: inline-block;" id="act_desc-canal">
			                    @csrf
			                </form>
			                <form action="{{ route('canales.destroy',$canal->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-canal">
        						@csrf
								<input name="_method" type="hidden" value="DELETE">
    						</form>

    						@if(isset($_SESSION['permisos']['771']))
    						<a href="{{route('canales.edit',$canal->id)}}" class="btn btn-outline-primary btn-icons"><i class="fas fa-edit"></i></a>
    						@endif

    						@if(isset($_SESSION['permisos']['773']))
							@if($canal->usado()==0)
							    <button class="btn btn-outline-danger btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-canal', '¿Está seguro que desea eliminar el canal de venta?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
							@endif
							@endif

							@if(isset($_SESSION['permisos']['772']))
							@if($canal->status==1)
							    <button class="btn btn-outline-danger btn-icons" type="submit" title="Deshabilitar" onclick="confirmar('act_desc-canal', '¿Está seguro que desea deshabilitar este canal de venta?', 'No aparecera para seleccionar en los contratos');"><i class="fas fa-power-off"></i></button>
			                @else
			                    <button class="btn btn-outline-success btn-icons" type="submit" title="Habilitar" onclick="confirmar('act_desc-canal', '¿Estas seguro que deseas habilitar este canal de venta?', 'Aparecera para seleccionar en los contratos');"><i class="fas fa-power-off"></i></button>
			                @endif
			                @endif
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
		</div>
	</div>
@endsection