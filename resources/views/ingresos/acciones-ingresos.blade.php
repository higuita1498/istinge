<a href="{{route('ingresos.show',$id)}}"   class="btn btn-outline-info @if(Auth::user()->rol==47) btn-xl @else btn-icons @endif" title="Ver"><i class="far fa-eye"></i></i></a>
@if($tipo!=3 && $tipo!=4)
    @if(isset($_SESSION['permisos']['48']))
        <a href="{{route('ingresos.edit',$nro)}}"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
    @endif
@endif

    <a href="{{route('ingresos.imprimir.nombre',['id' => $nro, 'name'=> 'Ingreso No. '.$nro.'.pdf'])}}" target="_blanck"  class="btn btn-outline-primary @if(Auth::user()->rol==47) btn-xl @else btn-icons @endif" title="Imprimir"><i class="fas fa-print"></i></a>
@if($tipo!=3)
    @if($tipo!=4)
        <form action="{{ route('ingresos.anular',$nro) }}" method="post" class="delete_form" style="display: none;" id="anular-ingreso{{$id}}">
            {{ csrf_field() }}
        </form>
        @if(isset($_SESSION['permisos']['49']))
            @if($estatus==1)
                <button class="btn btn-outline-danger  btn-icons" type="submit" title="Anular" onclick="confirmar('anular-ingreso{{$id}}', '¿Está seguro de que desea anular el ingreso?', ' ');"><i class="fas fa-minus"></i></button>
            @else
                <button class="btn btn-outline-success  btn-icons" type="submit" title="Abrir" onclick="confirmar('anular-ingreso{{$id}}', '¿Está seguro de que desea abrir el ingreso?', ' ');"><i class="fas fa-unlock-alt"></i></button>
            @endif
        @endif
    @endif
    <form action="{{ route('ingresos.destroy',$nro) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-ingreso{{$id}}">
    	{{ csrf_field() }}
        <input name="_method" type="hidden" value="DELETE">
    </form>
    @if(Auth::user()->rol==45)
        <button class="btn btn-outline-danger  btn-icons negative_paging" type="submit" title="Eliminar" onclick="confirmar('eliminar-ingreso{{$id}}', '¿Está seguro que desea eliminar el ingreso?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
    @endif
    <a href="{{route('ingresos.showmovimiento',$id)}}" class="btn btn-outline-info btn-icons" title="Ver movimientos"><i class="far fa-sticky-note"></i></a>
@endif