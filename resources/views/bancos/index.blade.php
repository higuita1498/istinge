@extends('layouts.app')
@section('boton')
    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
	        <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
	    </div>
	@else
	@if(isset($_SESSION['permisos']['283']))
	    <a href="{{route('bancos.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Banco</a>
	@endif
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
	<div class="row card-description">
		<div class="col-md-12">
			<table class="table table-striped table-hover" id="example">
			<thead class="thead-dark">
				<tr>
	              <th>Nombre de la Cuenta</th>
	              <th>Número de la Cuenta</th>
	              <th>Descripción</th>
	              <th>Tipo</th>
	              <th>Saldo</th>
	              <th>Acciones</th>
	          </tr>
			</thead>
			<tbody>
				@foreach($bancos as $banco)
					<tr @if($banco->id==Session::get('banco_id')) class="active_table" @endif> 
						<td><a href="{{route('bancos.show',$banco->nro)}}">{{$banco->nombre}}</a></td>
						<td>{{$banco->nro_cta}}</td>
						<td>{{$banco->descripcion}} </td>
						<td>{{$banco->tipo()}} </td>
						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($banco->saldo())}}</td>
						<td>
						    <form action="{{ route('bancos.destroy',$banco->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-banco-{{$banco->id}}">
								{{ csrf_field() }}
								<input name="_method" type="hidden" value="DELETE">
							</form>
							<a href="{{route('bancos.show',$banco->nro)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>
							@if(Auth::user()->modo_lectura())
							@else
							    @if(isset($_SESSION['permisos']['284']) && $banco->lectura==0)
								<a href="{{route('bancos.edit',$banco->nro)}}" class="btn btn-outline-primary btn-icons"><i class="fas fa-edit"></i></a>
								@endif
								@if(!$banco->uso())
								@if($banco->lectura==0)
								<button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-banco-{{$banco->id}}', '¿Está seguro que desea eliminar el banco?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
								@endif
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