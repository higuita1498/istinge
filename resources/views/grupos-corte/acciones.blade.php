@if($uso==0)
    <form action="{{ route('grupos-corte.destroy', $id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar{{$id}}">
        @csrf
        <input name="_method" type="hidden" value="DELETE">
    </form>
@endif

<form action="{{ route('grupos-corte.act_des',$id) }}" method="GET" class="delete_form" style="display: none;" id="act_des{{$id}}">
    @csrf
</form>

@if(isset($session['726']))
    <a href="{{route('grupos-corte.show', $id)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>
@endif
@if(isset($session['726']))
    <a href="{{route('grupos-corte.estados', $id)}}" class="btn btn-outline-danger btn-icons" title="Ver"><i class="fas fa-file-signature"></i></a>
@endif
@if(isset($session['727']))
    <a href="{{route('grupos-corte.edit', $id)}}" class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
@endif
@if(isset($session['729']))
    <button class="btn {{ ($status==0) ? 'btn-outline-success' : 'btn-outline-danger' }} btn-icons" type="button" title="{{ ($status==0) ? 'Habilitar' : 'Deshabilitar' }}" onclick="confirmar('act_des{{$id}}', '¿Está seguro de que desea {{ ($status==0) ? 'Habilitar' : 'Deshabilitar' }} el grupo de corte?', ' ');"><i class="fas fa-power-off"></i></button>
@endif
@if(isset($session['728']))
    @if($uso==0)
        <button type="button" class="btn btn-outline-danger btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar{{$id}}', '¿Está seguro que desear eliminar el grupo de corte?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button
    @endif
@endif