<form action="{{ route('monitor-blacklist.destroy', $id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar{{$id}}">
    @csrf
    <input name="_method" type="hidden" value="DELETE">
</form>

<form action="{{ route('monitor-blacklist.verificar', $id) }}" method="get" class="delete_form" style="margin:  0;display: inline-block;" id="verificar{{$id}}">
    @csrf
</form>

@if(isset($session['755']))
    <a href="{{route('monitor-blacklist.edit', $id)}}" class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
@endif
@if(isset($session['758']))
    <button type="button" class="btn btn-outline-success btn-icons" type="submit" title="Eliminar" onclick="confirmar('verificar{{$id}}', '¿Está seguro que desea verificar el blacklist?', 'Esto puede demorar unos minutos');"><i class="fas fa-check-double"></i></button>

@endif
@if(isset($session['756']))
    <button type="button" class="btn btn-outline-danger btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar{{$id}}', '¿Está seguro que desea eliminar el monitor blacklist?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
@endif