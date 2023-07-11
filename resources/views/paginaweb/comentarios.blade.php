@extends('layouts.app')
@section('boton')	
{{--<a href="{{route('logistica.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Envio</a>--}}

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
					<th>nro</th>
					<th>producto</th>
					<th>url Producto</th>
					<th>Comentarios</th>
					<th>Calificacion Promedio</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				@foreach ($comentarios as $comentario)
				<tr>
					<td>{{$comentario->id}}</td>
					<td>{{$comentario->nombre_producto}}</td>
					<td><div class="elipsis-short"><a target="_blank" href="{{$comentario->url_producto}}">{{$comentario->url_producto}}</a></div></td>
					<td>{{$comentario->cantidadproductos}}</td>
					<td>   
						<div class="stars-incomment col-md-4">
							<div class="form-stars no-color-star">
								<div class="noclick"></div>
								<p class="clasificacion">

            <input id="{{$comentario->id}}-1" type="radio" name="estrellacoment1-{{$comentario->id}}" value="5" {{round($comentario->sumcalificacion/$comentario->cantidadproductos) == 5 ? 'checked' : ''}} disabled><!--
            --><label for="{{$comentario->id}}-1">★</label><!--
            --><input id="{{$comentario->id}}-2" type="radio" name="estrellacoment2-{{$comentario->id}}" value="4" {{round($comentario->sumcalificacion/$comentario->cantidadproductos) == 4 ? 'checked' : ''}} disabled><!--
            --><label for="{{$comentario->id}}-2">★</label><!--
            --><input id="{{$comentario->id}}-3" type="radio" name="estrellacoment3-{{$comentario->id}}" value="3" {{round($comentario->sumcalificacion/$comentario->cantidadproductos) == 3 ? 'checked' : ''}} disabled><!--
            --><label for="{{$comentario->id}}-3">★</label><!--
            --><input id="{{$comentario->id}}-4" type="radio" name="estrellacoment4-{{$comentario->id}}" value="2" {{round($comentario->sumcalificacion/$comentario->cantidadproductos) == 2 ? 'checked' : ''}} disabled><!--
            --><label for="{{$comentario->id}}-4">★</label><!--
            --><input id="{{$comentario->id}}-5" type="radio" name="estrellacoment5-{{$comentario->id}}" value="1" {{round($comentario->sumcalificacion/$comentario->cantidadproductos) == 1 ? 'checked' : ''}} disabled><!--
        --><label for="{{$comentario->id}}-5">★</label>
    </p>
</div>
</div>
</td>
<td><a href="{{route('PaginaWeb.detallecomentarios',$comentario->id_producto)}}" type="button" class="btn btn-success">Ver comentarios</a></td>
</tr>
@endforeach

</tbody>
</table>
</div>
</div>
@endsection