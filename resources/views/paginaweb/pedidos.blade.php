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
					<th>codigo</th>
					<th>valor total</th>
					<th>tipo moneda</th>
					<th>estado</th>
					<th>descripcion</th>
					<th>medio de pago</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				@foreach ($pedidos as $pedido)
				<tr>
					<td>{{$pedido->id}}</td>
					<td>{{$pedido->referencecode}}</td>
					<td>{{$pedido->amount}}</td>
					<td>{{$pedido->type_currency}}</td>
					<td>@if ($pedido->transactionState == 4)
						<strong style="color:#4dc326;">Finalizado</strong>
						@elseif($pedido->transactionState == 7)
						<strong style="color:#ffc800;">En proceso</strong>
						@elseif($pedido->transactionState == 6)
						<strong style="color:#ff0000;">Rechazado</strong>
						@elseif($pedido->transactionState == 104)
						<strong style="color:#ff0000;">Error</strong>
						@elseif($pedido->transactionState == 5)
						<strong style="color:#057fd5;">Expirado</strong>
						@endif
					</td>
					<td>{{$pedido->description}}</td>
					<td>{{$pedido->lapPayMentMethod}}</td>
					<td><a href="{{route('PaginaWeb.detallepedido',$pedido->id)}}" type="button" class="btn btn-success">Detalle</a></td>
				</tr>
				@endforeach

			</tbody>
		</table>
	</div>
</div>
@endsection