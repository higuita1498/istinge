@extends('layouts.app')
@section('boton')
	@if(Auth::user()->modo_lectura())
		<div class="alert alert-warning alert-dismissible fade show" role="alert">
			<a>Esta en Modo Lectura si desea seguir disfrutando de Nuestros Servicios Cancelar Alguno de Nuestros Planes <a class="text-black" href="{{route('PlanesPagina.index')}}"> <b>Click Aqui.</b></a></a>
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
	@else
	<a href="{{route('notasdebito.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nueva Nota de Débito</a>
	@endif
@endsection
@section('content')
	@if(Session::has('success'))
		<div class="alert alert-success" style="margin-left: 2%;margin-right: 2%;">
			{{Session::get('success')}}
		</div>
	@endif
@if(Session::has('message_denied'))
<div class="alert alert-danger" role="alert">
	{{Session::get('message_denied')}} 
	@if(Session::get('errorReason'))<br> <strong>Razon(es): <br></strong>
	@if(count(Session::get('errorReason')) > 0)
	@php $cont = 0 @endphp
	@foreach(Session::get('errorReason') as $error)
	@php $cont = $cont + 1; @endphp
	{{$cont}} - {{$error}} <br>
	@endforeach
	@else
	{{ Session::get('errorReason') }}
	@endif
	@endif
	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
		<span aria-hidden="true">&times;</span>
	</button>
</div>
@endif
	<div class="row card-description">
		<div class="col-md-12">
			<table class="table table-striped table-hover" id="example">
			<thead class="thead-dark">
				<tr>
	              <th>Código</th>
	              <th>Proveedor</th>
	              <th>Creación</th>
	              <th>Total</th>
	              <th>Por aplicar</th>
	              <th>Acciones</th>
	          </tr>                              
			</thead>
			<tbody>
				@foreach($notas as $nota)
					<tr @if($nota->id==Session::get('nota_id')) class="active" @endif>

						<td><a href="{{route('notasdebito.show',$nota->id)}}" >{{$nota->nro}}</a> </td>
						<td><a href="{{route('contactos.show',$nota->proveedor()->id)}}" target="_blanck">{{$nota->proveedor()->nombre}}
						</a></td> 
						<td>{{date('d-m-Y', strtotime($nota->fecha))}}</td>
						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($nota->total()->total)}}</td>
						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($nota->por_aplicar())}}</td>
						<td>
							<a href="{{route('notasdebito.show',$nota->id)}}"  class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></i></a>
							@if(Auth::user()->modo_lectura())

							@else
                                <a href="{{route('notasdebito.imprimir.nombre',['id' => $nota->id, 'name'=> 'Nota Debito No. '.$nota->nro.'.pdf'])}}" target="_blanck" class="btn btn-outline-primary btn-icons" title="Imprimir"><i class="fas fa-print"></i></a>
                                @if(Auth::user()->empresa()->form_fe == 1 && $nota->emitida == 0 && Auth::user()->empresa == 1)
								<a onclick="confirmSendDian('{{route('xml.notadebito',$nota->id)}}','{{$nota->nro}}')" href="#"  class="btn btn-outline-primary btn-icons"title="Emitir Nota Debito"><i class="fas fa-sitemap"></i></a>
								@endif
								<a href="{{route('notasdebito.edit',$nota->id)}}"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
								<form action="{{ route('notasdebito.destroy',$nota->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-notasdebito{{$nota->id}}">
								{{ csrf_field() }}
								<input name="_method" type="hidden" value="DELETE">
								</form>
								<button class="btn btn-outline-danger  btn-icons negative_paging" type="submit" title="Eliminar" onclick="confirmar('eliminar-notasdebito{{$nota->id}}', '¿Estas seguro que deseas eliminar nota de débito?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
							@endif
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
		</div>
	</div>
@endsection