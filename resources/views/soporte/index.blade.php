@extends('layouts.app')
@section('boton')	
		<a href="{{route('soporte.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Ticket</a>
	
	
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
	              <th>Titulo</th>
	              <th>ID Caso</th>
	              <th>Creado en</th>
	              <th>Categoría</th>
	              <th>Estatus</th>
	          </tr>
			</thead>
			<tbody>
				@foreach($tickets as $ticket)
					<tr>
						<td><a href="{{route('soporte.show', $ticket->id)}}">{{$ticket->titulo}}</a></td>
						<td>{{$ticket->id}}</td>
						<td>{{date('d-m-Y h:m a', strtotime($ticket->created_at))}}</td>
						<td>{{$ticket->modulo()}}</td>
						@if($ticket->estatus==1)
						<td class="text-warning">{{$ticket->estatus()}}</td>
						@elseif($ticket->estatus==2)
						<td class="text-success">{{$ticket->estatus()}}</td>
						@else
						<td class="text-danger">{{$ticket->estatus()}}</td>
						@endif
					</tr>
				@endforeach
			</tbody>
		</table>
		</div>
	</div>
@endsection