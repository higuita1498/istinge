@if(auth()->user()->modo_lectura())
@else
    <form action="{{route('oficinas.status',$id)}}" method="get" class="delete_form" style="margin:  0;display: inline-block;" id="status-{{$id}}">
        @csrf
    </form>
    <form action="{{ route('oficinas.destroy',$id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-{{$id}}">
        @csrf
        <input name="_method" type="hidden" value="DELETE">
    </form>

    @if(isset($session['809']))
        <a title="Ver detalles" href="{{route('oficinas.show',$id)}}" class="btn btn-outline-info btn-icons"><i class="fas fa-eye"></i></a>
    @endif
    @if(isset($session['811']))
        <a title="Editar" href="{{route('oficinas.edit',$id)}}" class="btn btn-outline-primary btn-icons"><i class="fas fa-edit"></i></a>
    @endif
    @if(isset($session['813']))
        @if($status == 0)
            <button title="Habilitar oficina" class="btn btn-outline-success btn-icons" type="submit" onclick="confirmar('status-{{$id}}', '¿Está seguro que desea habilitar la oficina {{$nombre}}?', '');"><i class="fas fa-power-off"></i></button>
        @else
            <button title="Deshabilitar oficina" class="btn btn-outline-danger btn-icons" type="submit" onclick="confirmar('status-{{$id}}', '¿Está seguro que desea deshabilitar la oficina {{$nombre}}?', '');"><i class="fas fa-power-off"></i></button>
        @endif
    @endif
    @if(isset($session['812']))
        @if($uso == 0)
            <button class="btn btn-outline-danger btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-{{$id}}', '¿Está seguro que deseas eliminar la oficina {{$nombre}}?', 'Se borrará de forma permanente');"><i class="fas fa-times"></i></button>
        @endif
    @endif
@endif


