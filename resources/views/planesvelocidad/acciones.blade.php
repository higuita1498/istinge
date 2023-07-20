<form action="{{ route('planes-velocidad.destroy',$id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-plan{{$id}}">
    @csrf
    <input name="_method" type="hidden" value="DELETE">
</form>

<form action="{{ route('planes-velocidad.status',$id) }}" method="get" class="delete_form" style="margin:0;display: inline-block;" id="cambiar-state{{$id}}">
    @csrf
</form>

<form action="{{route('planes-velocidad.reglas',$id)}}" method="get" class="delete_form" style="margin:  0;display: inline-block;" id="regla-{{$id}}">
    @csrf
</form>

<a href="{{route('planes-velocidad.edit',$id)}}" class="btn btn-outline-primary btn-icons"><i class="fas fa-edit"></i></a>
<a href="{{route('planes-velocidad.show',$id)}}" class="btn btn-outline-info btn-icons"><i class="fas fa-eye"></i></a>
@if($type == 1)
<button title="Aplicar Reglas" class="btn btn-outline-dark btn-icons" type="submit" onclick="confirmar('regla-{{$id}}', '¿Está seguro que desea aplicar las reglas de este plan a la Mikrotik?', '');"><i class="fas fa-plus"></i></button>
@endif
<button @if($status == 1) class="btn btn-outline-danger btn-icons" title="Deshabilitar" @else class="btn btn-outline-success btn-icons" title="Habilitar" @endif type="submit" onclick="confirmar('cambiar-state{{$id}}', '¿Está seguro que desea @if($status == 1) deshabilitar @else habilitar @endif el plan?', '');"><i class="fas fa-power-off"></i></button>
<a href="{{route('planes-velocidad.aplicar-cambios',$id)}}" class="btn btn-outline-success btn-icons" title="Aplicar Cambios"><i class="fas fa-check"></i></a>
@if($uso==0)
<button class="btn btn-outline-danger btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-plan{{$id}}', '¿Está seguro que desea eliminar el Plan?', 'Se borrará de forma permanente');"><i class="fas fa-times"></i></button>
@endif