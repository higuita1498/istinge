@extends('layouts.app')

@section('style')
<style>

</style>
@endsection

<a href="{{route('radicados.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Crear nueva</a>

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
					          </tr>
							</thead>
							<tbody>
								@foreach($etiquetas as $etiqueta)
										<tr>
											<td>{{$etiqueta->nombre}}</a></td>
											<td>{{$etiqueta->color}}</td>
											<td>{{$etiqueta->created_at->format('d-m-Y')}}</td>
                                        </tr>
								@endforeach
							</tbody>
						</table>
					</div>
			
		</div>
	</div>
@endsection