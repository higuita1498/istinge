@if($uso==0)
    <form action="{{ route('puertos-conexion.destroy', $id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar{{$id}}">
        @csrf
        <input name="_method" type="hidden" value="DELETE">
    </form>
@endif
<a href="{{route('puertos-conexion.edit', $id)}}" class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
@if($uso==0)
    <button type="button" class="btn btn-outline-danger btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar{{$id}}', '¿Está seguro que desear eliminar el puerto?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button
@endif