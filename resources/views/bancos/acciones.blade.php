@if(auth()->user()->modo_lectura())
@else
    <form action="{{ route('bancos.destroy',$id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-banco-{{$id}}">
        @csrf
        <input name="_method" type="hidden" value="DELETE">
    </form>

    <form action="{{ route('bancos.ocultar',$id) }}" method="GET" class="delete_form" style="display: none;" id="ocultar-{{$id}}">
        @csrf
    </form>

    <a href="{{route('bancos.show',$nro)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>
    @if(isset($session['284']) && $lectura==0)
        <a href="{{route('bancos.edit',$nro)}}" class="btn btn-outline-primary btn-icons"><i class="fas fa-edit"></i></a>
    @endif

    @if(!$uso)
        @if($lectura==0)
            <button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-banco-{{$id}}', '¿Está seguro que desea eliminar el banco?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
        @endif
    @endif

    <button class="btn {{ ($oculto==0) ? 'btn-outline-success' : 'btn-outline-danger' }} btn-icons" type="button" title="{{ ($oculto==0) ? 'Mover a Ocultos' : 'Mover a Disponibles' }}" onclick="confirmar('ocultar-{{$id}}', '¿Está seguro de que desea {{ ($oculto==0) ? 'mover a ocultos' : 'mover a disponibles' }} el banco?', ' ');"><i class="fas fa-power-off"></i></button>
@endif
