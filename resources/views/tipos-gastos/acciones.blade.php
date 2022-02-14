@if($uso==0)
    <form action="{{ route('tipos-gastos.destroy', $id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar{{$id}}">
        @csrf
        <input name="_method" type="hidden" value="DELETE">
    </form>
@endif

<form action="{{ route('tipos-gastos.act_des',$id) }}" method="GET" class="delete_form" style="display: none;" id="act_des{{$id}}">
    @csrf
</form>


@if (isset($session['719']))
    <a href="{{route('tipos-gastos.show', $id)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>
@endif
@if (isset($session['720']))
    <a href="{{route('tipos-gastos.edit', $id)}}" class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
@endif
@if (isset($session['722']))
    <button class="btn {{ ($estado==0) ? 'btn-outline-success' : 'btn-outline-danger' }} btn-icons" type="button" title="{{ ($estado==0) ? 'Habilitar' : 'Deshabilitar' }}" onclick="confirmar('act_des{{$id}}', '¿Está seguro de que desea {{ ($estado==0) ? 'Habilitar' : 'Deshabilitar' }} el tipo de gasto?', ' ');"><i class="fas fa-power-off"></i></button>
@endif
@if (isset($session['721']))
    @if($uso==0)
        <button type="button" class="btn btn-outline-danger btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar{{$id}}', '¿Está seguro que desear eliminar el tipo de gasto?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button
    @endif
@endif