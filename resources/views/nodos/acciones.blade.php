@if($uso==0)
    <form action="{{ route('nodos.destroy', $id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar{{$id}}">
        @csrf
        <input name="_method" type="hidden" value="DELETE">
    </form>
@endif

<form action="{{ route('nodos.act_des',$id) }}" method="GET" class="delete_form" style="display: none;" id="act_des{{$id}}">
    @csrf
</form>

<?php if (isset($session['713'])) { ?>
    <a href="{{route('nodos.show', $id)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>
<?php } ?>
<?php if (isset($session['714'])) { ?>
    <a href="{{route('nodos.edit', $id)}}" class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
<?php } ?>
<?php if (isset($session['716'])) { ?>
    <button class="btn {{ ($status==0) ? 'btn-outline-success' : 'btn-outline-danger' }} btn-icons" type="button" title="{{ ($status==0) ? 'Habilitar' : 'Deshabilitar' }}" onclick="confirmar('act_des{{$id}}', '¿Está seguro de que desea {{ ($status==0) ? 'Habilitar' : 'Deshabilitar' }} el nodo?', ' ');"><i class="fas fa-power-off"></i></button>
<?php } ?>
<?php if (isset($session['715'])) { ?>
    @if($uso==0)
        <button type="button" class="btn btn-outline-danger btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar{{$id}}', '¿Está seguro que desear eliminar el nodo?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button
    @endif
<?php } ?>