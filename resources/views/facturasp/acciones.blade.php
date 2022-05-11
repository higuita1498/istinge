@if(Auth::user()->modo_lectura())

@else
    <a href="{{route('facturasp.showid', $id)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></i></a>
    @if($tipo ==1 && $estatus==1)
        <a  href="{{route('pagos.create_id', ['cliente'=> $proveedor, 'factura'=> $id])}}" class="btn btn-outline-primary btn-icons" title="Agregar pago"><i class="fas fa-money-bill"></i></a>
        <a href="{{route('facturasp.edit', $id)}}"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
        <a href="{{route('facturasp.imprimir.nombre', ['id' => $id, 'name'=> 'Factura Proveedor No. '.$nro.'.pdf'])}}"  class="btn btn-outline-primary btn-icons" title="Imprimir"><i class="fas fa-print"></i></a>
        <button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-factura{{$id}}', '¿Estas seguro que deseas eliminar la factura de compra?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
        <form action="{{ route('facturasp.destroy', $id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-factura{{$id}}">
        	{{ csrf_field() }}
        	<input name="_method" type="hidden" value="DELETE">
        </form>
    @else
        <a href="{{route('facturasp.imprimir.nombre', ['id' => $id, 'name'=> 'Factura Proveedor No. '.$nro.'.pdf'])}}"  class="btn btn-outline-primary btn-icons" title="Imprimir"><i class="fas fa-print"></i></a>
    @endif
    <a href="{{route('facturasp.showmovimiento', $id)}}" class="btn btn-outline-info btn-icons" title="Ver movimientos"><i class="far fa-sticky-note"></i></a>
@endif