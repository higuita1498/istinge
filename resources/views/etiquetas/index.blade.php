@extends('layouts.app')

@section('style')
<link rel="stylesheet" href="{{asset('css/jquery.minicolors.css')}}">
<style>

</style>
@endsection

@include('etiquetas.modals.create')
@include('etiquetas.modals.edit')

@section('boton')
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-etiqueta">
    Crear nueva
</button>
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

	<style>
		
	</style>

	<div class="row card-description">
		<div class="col-md-12">
			<hr style="border-top: 1px solid #b00606; margin: .5rem 0rem;">

					<div class="table-responsive">
						<table class="table table-striped table-hover" id="example0" style="width: 100%; border: 1px solid #e9ecef;">
							<thead class="thead-dark">
								<tr>
					              <th>Nombre</th>
					              <th>Color</th>
					              <th>Creacion</th>
								  <th>Acciones</th>
					          </tr>
							</thead>
							<tbody id="data-etiquetas">
								@foreach($etiquetas as $etiqueta)
										<tr id="rw-{{ $etiqueta->id }}">
											<td>{{$etiqueta->nombre}}</a></td>
											<td>{{$etiqueta->color}}</td>
											<td>{{$etiqueta->created_at->format('d-m-Y')}}</td>
											<td>@include('etiquetas.acciones', $etiqueta)</td>
                                        </tr>
								@endforeach
							</tbody>
						</table>
					</div>
			
		</div>
	</div>
@endsection

 @section('scripts')
<script src="{{ asset('js/jquery.minicolors.js') }}"></script>

<script>
$(function(){
		$('#color').minicolors();
		$('#edit-color').minicolors();
});
</script>

@endsection

