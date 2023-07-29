@extends('layouts.app')	
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
	              <th>Título</th>
	              <th>ID Caso</th>
	              <th>Creado en</th>
	              <th>Categoría</th>
	              <th>Empresa</th>
	              <th>Estatus</th>
	          </tr>
			</thead>
			<tbody>
				@foreach($tickets as $ticket)
					<tr>
						<td><a href="{{route('atencionsoporte.show', $ticket->id)}}">{{$ticket->titulo}}</a></td>
						<td>{{$ticket->id}}</td>
						<td>{{date('d-m-Y h:m a', strtotime($ticket->created_at))}}</td>
						<td>{{$ticket->modulo()}}</td>
						<td>{{$ticket->empresa()->nombre}}</td>
						<td>{{$ticket->estatus()}}</td>
					</tr>
				@endforeach
			</tbody>
		</table>
		</div>
	</div>
@endsection