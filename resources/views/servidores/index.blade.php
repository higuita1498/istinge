@extends('layouts.app')
@section('boton')
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
				  <th>ID</th>
	              <th>Nombre</th>
	              <th class="text-center">Estado</th>
	              <th class="text-center">Tipo</th>
	              {{-- <th class="text-center">Contratos</th> --}}
	              <th class="text-center">Acciones</th>
	          </tr>                              
			</thead>
			<tbody>
				@foreach($servidores as $servidor)
					<tr @if($servidor->id==Session::get('public_id')) class="active" @endif>
						<td>{{$servidor->public_id}}</td>
						<td>{{$servidor->name}}</td>
						<td class="text-center">{{$servidor->state()}}</td>
						<td class="text-center">{{$servidor->type}}</td>
						{{-- <td class="text-center">
						    <span class="badge badge-success">{{$servidor->enabled()}}</span>
						    <span class="badge badge-danger">{{$servidor->disabled()}}</span>
						</td> --}}
						<td class="text-center">
						    @if(isset($_SESSION['permisos']['409']))
								<form action="{{ route('servidores.aplicar_cambios',$servidor->public_id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="desactivar-empresa{{$servidor->id}}">
            						{{ csrf_field() }}
        						</form>	
            					<button class="btn btn-outline-success btn-xs" type="button" title="Aplicar Cambios" onclick="confirmar('desactivar-empresa{{$servidor->id}}', '¿Estas seguro que deseas aplicar los cambios al servidor?');"><i class="fas fa-check"></i> Aplicar Cambios</button>
    						@else
    							- - -
    						@endif
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
		</div>
	</div>
@endsection