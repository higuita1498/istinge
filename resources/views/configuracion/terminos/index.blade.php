 @extends('layouts.app')
@section('boton')	
		<a href="{{route('termino.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Término de Pago</a>
	
	
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
	<div class="row card-description">
		<div class="col-md-12">
			<table class="table table-striped table-hover" id="example">
			<thead class="thead-dark">
				<tr>
	              <th>Nombre</th>
	              <th>Días</th>
	              <th>Acciones</th>
	          </tr>
			</thead>
			<tbody>
				@foreach($terminos as $termino)
					<tr @if($termino->id==Session::get('termino_id')) class="active_table" @endif>
						<td>{{$termino->nombre}}</td>
						<td>{{$termino->dias}}</td>
						<td>

							<a href="{{route('termino.edit',$termino->id)}}" class="btn btn-outline-primary btn-icons"><i class="fas fa-edit"></i></a>
							@if($termino->usado()==0)
								<form action="{{ route('termino.destroy',$termino->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-termino">
        						{{ csrf_field() }}
								<input name="_method" type="hidden" value="DELETE">
    						</form>
    						<button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-termino', '¿Estas seguro que deseas eliminar el término de pago?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
							@endif

						</td>
					</tr>
				@endforeach

				<tr>
					<td>Vencimiento manual</td>
					<td>Vencimiento manual</td>
					<td></td>
				</tr>
			</tbody>
		</table>
		</div>
	</div>
@endsection