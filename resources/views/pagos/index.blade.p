@extends('layouts.app')
@section('boton')
	@if(Auth::user()->modo_lectura()->fec_vencimiento < date('Y-m-d'))
		<div class="alert alert-warning alert-dismissible fade show" role="alert">
			<a>Esta en Modo Lectura si desea seguir disfrutando de Nuestros Servicios Cancelar Alguno de Nuestros Planes <a class="text-black" href="{{route('PlanesPagina.index')}}"> <b>Click Aqui.</b></a></a>
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
	@else
	<a href="{{route('pagos.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Pago</a>
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
		<div class="col-md-12 mb-5">
			<table class="table table-striped table-hover" id="table-ingresos">
			<thead class="thead-dark">
				<tr>
	              <th>Número</th>
	              <th>Cliente</th>
	              <th>Detalle</th>
	              <th>Fecha</th>
	              <th>Cuenta</th>
	              <th>Estatus</th>
	              <th>Monto</th>
	              <th>Acciones</th>
	          </tr>

			</thead>
			<tbody>
				@foreach($gastos as $gasto)
					<tr @if($gasto->id==Session::get('gasto_id')) class="active_table" @endif>
						<td><a href="{{route('pagos.show',$gasto->id)}}">{{$gasto->nro}}</a> </td>
						<td><div class="elipsis-short" style="width:135px;">@if($gasto->beneficiario()) <a href="{{route('contactos.show',$gasto->beneficiario()->id)}}" title="{{$gasto->beneficiario()->nombre}}" target="_blanck">{{$gasto->beneficiario()->nombre}}</a>@endif</div></td>
						<td>{{$gasto->detalle()}} </td>
						<td>{{date('d-m-Y', strtotime($gasto->fecha))}}</td>
						<td>{{$gasto->cuenta()->nombre}} </td>
						<td class="text-{{$gasto->estatus(true)}}">{{$gasto->estatus()}} </td>
						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($gasto->pago())}} </td>
						<td>
							<a  href="{{route('pagos.show',$gasto->nro)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>
							@if(Auth::user()->modo_lectura()->fec_vencimiento < date('Y-m-d'))

							@else
								<a   href="{{route('pagos.imprimir',$gasto->id)}}" target="_black" class="btn btn-outline-primary btn-icons" title="Imprimir"><i class="fas fa-print"></i></a>
								@if($gasto->tipo!=3)
									@if($gasto->tipo!=4)
										<a href="{{route('pagos.edit',$gasto->id)}}"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
										<form action="{{ route('pagos.anular',$gasto->nro) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="anular-gasto{{$gasto->id}}">
										{{ csrf_field() }}
										</form>
										@if($gasto->estatus==1)
										<button class="btn btn-outline-danger  btn-icons negative_paging" type="submit" title="Anular" onclick="confirmar('anular-gasto{{$gasto->id}}', '¿Está seguro de que desea anular el gasto?', ' ');"><i class="fas fa-minus"></i></button>
										@else
										<button class="btn btn-outline-success  btn-icons negative_paging" type="submit" title="Abrir" onclick="confirmar('anular-gasto{{$gasto->id}}', '¿Está seguro de que desea abrir el gasto?', ' ');"><i class="fas fa-unlock-alt"></i></button>
									@endif
								@endif
							@endif
							

							<form action="{{ route('pagos.destroy',$gasto->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-gasto{{$gasto->id}}">
    						{{ csrf_field() }}
							<input name="_method" type="hidden" value="DELETE">
							</form>
							<button class="btn btn-outline-danger  btn-icons negative_paging" type="submit" title="Eliminar" onclick="confirmar('eliminar-gasto{{$gasto->id}}', '¿Estas seguro que deseas eliminar el gasto?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
							@endif 

						</td>  
					</tr> 
				@endforeach			
			</tbody>
		</table>
		<div class="text-right">
					{{$gastos->links()}}
				</div>
		</div><!--
        <div class="col-md-12 mt-5">
            <h1>- Pagos recurrentes</h1>
            <table class="table table-striped table-hover" id="table-pagos-recurrentes">
                <thead class="thead-dark">
                <tr>
                    <th>Número</th>
                    <th>Cliente</th>
                    <th>Detalle</th>
                    <th>Fecha</th>
                    <th>Cuenta</th>
                    <th>Monto</th>
                    <th>Acciones</th>
                </tr>

                </thead>
                <tbody>
                @foreach($gastosR as $gastoR)
                    <tr @if($gastoR->id==Session::get('gasto_id')) class="active_table" @endif>
                        <td><a href="{{route('pagosrecurrentes.show',$gastoR->nro)}}">{{$gastoR->nro}}</a> </td>
                        <td>@if($gastoR->beneficiario())<a href="{{route('contactos.show',$gastoR->beneficiario()->id)}}" target="_blanck">{{$gastoR->beneficiario()->nombre}}@endif</a></td>
                        <td>{{$gastoR->detalle()}} </td>
                        <td>{{date('d-m-Y', strtotime($gastoR->fecha))}}</td>
                        <td>{{$gastoR->cuenta()->nombre}} </td>
                        <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($gastoR->total()->total)}} </td>
                        <td>
                            <a href="{{route('pagosrecurrentes.show',$gastoR->nro)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></i></a>
                            <a href="{{route('pagosR.imprimir',$gastoR->id)}}" target="_black" class="btn btn-outline-primary btn-icons" title="Imprimir"><i class="fas fa-print"></i></a>
                            <form action="{{ route('pagosR.anular',$gastoR->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="anular-gastoR{{$gastoR->id}}">
                                {{ csrf_field() }}
                            </form>
                            @if($gastoR->estatus())
                                <button class="btn btn-outline-danger  btn-icons negative_paging" type="submit" title="Anular" onclick="confirmar('anular-gastoR{{$gastoR->id}}', '¿Está seguro de que desea anular el gasto?', ' ');"><i class="fas fa-minus"></i></button>
                            @else
                                <button class="btn btn-outline-success  btn-icons negative_paging" type="submit" title="Abrir" onclick="confirmar('anular-gastoR{{$gastoR->id}}', '¿Está seguro de que desea abrir el gasto?', ' ');"><i class="fas fa-unlock-alt"></i></button>
                            @endif
                            <form action="{{ route('pagosR.destroyP',$gastoR->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-gastoR{{$gastoR->id}}">
                                {{ csrf_field() }}
                            </form>
                            <button class="btn btn-outline-danger  btn-icons negative_paging" type="submit" title="Eliminar" onclick="confirmar('eliminar-gastoR{{$gastoR->id}}', '¿Estas seguro que deseas eliminar el gasto?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>


                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>-->
	</div>
@endsection
