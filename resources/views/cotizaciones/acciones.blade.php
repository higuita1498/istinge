@if(isset($session['56']))
<a href="{{route('cotizaciones.show',$cot_nro)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>
@endif
@if(isset($session['58']))
<a href="{{route('cotizaciones.edit',$cot_nro)}}"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
@endif
@if($estatus!=2)
	<a href="{{route('cotizaciones.imprimir.nombre',['id' => $cot_nro, 'name'=> 'Cotizacion No. '.$cot_nro.'.pdf'])}}" target="_blank"  class="btn btn-outline-primary btn-icons" title="Imprimir"><i class="fas fa-print"></i></a>
	@if(isset($session['59']))
	<form action="{{ route('cotizaciones.destroy',$cot_nro) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-factura{{$id}}">
		{{ csrf_field() }}
		<input name="_method" type="hidden" value="DELETE">
	</form>
	<button class="btn btn-outline-danger  btn-icons negative_paging" type="submit" title="Eliminar" onclick="confirmar('eliminar-factura{{$id}}', '¿Estas seguro que deseas eliminar la cotización?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
	@endif
@endif