@extends('layouts.app')
@section('boton')
    <a href="{{route('planes-velocidad.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Plan</a>
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
				    <th class="text-center">Precio</th>
				    <th class="text-center">Download</th>
				    <th class="text-center">Upload</th>
				    <th class="text-center">Tipo</th>
				    <th class="text-center">Mikrotik</th>
				    <th class="text-center">Estado</th>
				    <th class="text-center">Acciones</th>
	          </tr>                              
			</thead>
			<tbody>
				@foreach($planes as $plan)
					<tr @if($plan->id==Session::get('plan_id')) class="active" @endif>
						<td><a href="{{route('planes-velocidad.show',$plan->id)}}">{{$plan->name}}</a></td>
						<td class="text-center">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($plan->price)}}</td>
						<td class="text-center">{{$plan->download}}</td>
						<td class="text-center">{{$plan->upload}}</td>
						<td class="font-weight-bold text-center text-{{$plan->type('true')}}">{{$plan->type()}}</td>
						<td class="font-weight-bold text-center">{{$plan->mikrotik()->nombre}}</td>
						<td class="font-weight-bold text-center text-{{$plan->status('true')}}">{{$plan->status()}}</td>
						<td class="text-center">
						    <form action="{{ route('planes-velocidad.destroy',$plan->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-plan{{$plan->id}}">
						        @csrf
						        <input name="_method" type="hidden" value="DELETE">
						    </form>
						    <form action="{{ route('planes-velocidad.status',$plan->id) }}" method="get" class="delete_form" style="margin:0;display: inline-block;" id="cambiar-state{{$plan->id}}">
						        @csrf
						    </form>
						    <form action="{{route('planes-velocidad.reglas',$plan->id)}}" method="get" class="delete_form" style="margin:  0;display: inline-block;" id="regla-{{$plan->id}}">
			                    @csrf
			                </form>
						    <a href="{{route('planes-velocidad.edit',$plan->id)}}" class="btn btn-outline-primary btn-icons"><i class="fas fa-edit"></i></a>
						    <a href="{{route('planes-velocidad.show',$plan->id)}}" class="btn btn-outline-info btn-icons"><i class="fas fa-eye"></i></a>
						    @if($plan->type == 1)
						    <button title="Aplicar Reglas" class="btn btn-outline-dark btn-icons" type="submit" onclick="confirmar('regla-{{$plan->id}}', '¿Está seguro que desea aplicar las reglas de este plan a la Mikrotik?', '');"><i class="fas fa-plus"></i></button>
						    @endif
						    <button @if($plan->status == 1) class="btn btn-outline-danger btn-icons" title="Deshabilitar" @else class="btn btn-outline-success btn-icons" title="Habilitar" @endif type="submit" onclick="confirmar('cambiar-state{{$plan->id}}', '¿Está seguro que desea @if($plan->status == 1) deshabilitar @else habilitar @endif el plan?', '');"><i class="fas fa-power-off"></i></button>
						    <a href="{{route('planes-velocidad.aplicar-cambios',$plan->id)}}" class="btn btn-outline-success btn-icons" title="Aplicar Cambios"><i class="fas fa-check"></i></a>
						    @if($plan->uso()==0)
						    <button class="btn btn-outline-danger btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-plan{{$plan->id}}', '¿Está seguro que desea eliminar el Plan?', 'Se borrará de forma permanente');"><i class="fas fa-times"></i></button>
						   @endif
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
		</div>
	</div>
@endsection