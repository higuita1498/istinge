<a href="{{route('pagos.show',$id)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>
<a href="{{route('pagos.imprimir.nombre',['id' => $id, 'name'=> 'Pago No. '.$nro.'.pdf'])}}" target="_blank" class="btn btn-outline-primary btn-icons" title="Imprimir"><i class="fas fa-print"></i></a>
@if($tipo!=3)
    @if($tipo!=4)
        @if(isset($_SESSION['permisos']['254']))
        <a href="{{route('pagos.edit',$id)}}"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
        <form action="{{ route('pagos.anular',$id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="anular-gasto{{$id}}">
        	@csrf
        </form>
        @if($estatus==1)
            <button class="btn btn-outline-danger  btn-icons negative_paging" type="submit" title="Anular" onclick="confirmar('anular-gasto{{$id}}', '¿Está seguro de que desea anular el gasto?', ' ');"><i class="fas fa-minus"></i></button>
        @else
            <button class="btn btn-outline-success  btn-icons negative_paging" type="submit" title="Abrir" onclick="confirmar('anular-gasto{{$id}}', '¿Está seguro de que desea abrir el gasto?', ' ');"><i class="fas fa-unlock-alt"></i></button>
        @endif
        @endif
    @endif
@endif
@if(isset($_SESSION['permisos']['255']))
<form action="{{ route('pagos.destroy',$id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-gasto{{$id}}">
	@csrf
	<input name="_method" type="hidden" value="DELETE">
</form>
<button class="btn btn-outline-danger  btn-icons negative_paging" type="submit" title="Eliminar" onclick="confirmar('eliminar-gasto{{$id}}', '¿Está seguro que desea eliminar el gasto?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
<a href="{{route('pagos.showmovimiento',$id)}}" class="btn btn-outline-info btn-icons" title="Ver movimientos"><i class="far fa-sticky-note"></i></a>
@endif