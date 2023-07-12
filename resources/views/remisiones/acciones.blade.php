@if(isset($session['66']))
<a href="{{route('remisiones.show',$id)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>
@endif
<a href="{{route('remisiones.imprimir',['id' => $nro, 'name'=> 'Remision No. '.$nro.'.pdf'])}}" target="_black"  class="btn btn-outline-primary btn-icons" title="Imprimir"><i class="fas fa-print"></i></a>
@if($estatus==1)
    @if(isset($session['68']))
    <a href="{{route('remisiones.edit',$nro)}}"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
    <form action="{{ route('remisiones.anular',$nro) }}" method="POST" class="delete_form" style="display: none;" id="anular-factura{{$id}}">
    	{{ csrf_field() }}
    </form>
    <button class="btn btn-outline-danger  btn-icons" type="button" title="Anular" onclick="confirmar('anular-factura{{$id}}', '¿Está seguro de que desea anular la remisión?', ' ');"><i class="fas fa-minus"></i></button>
    @endif
@elseif($estatus==2)
    @if(isset($session['68']))
    <button class="btn btn-outline-success  btn-icons" type="submit" title="Abrir" onclick="confirmar('anular-factura{{$id}}', '¿Está seguro de que desea abrir la remisión?', ' ');"><i class="fas fa-unlock-alt"></i></button>
    @endif
@endif