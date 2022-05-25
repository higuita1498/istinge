<form action="{{ route('ventas-externas.aprobar', $id) }}" method="get" class="delete_form" style="margin:0;display: inline-block;" id="aprobar-{{$id}}">
    @csrf
</form>

@if(isset($session['802']))
    <a href="{{route('ventas-externas.edit', $id)}}" class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
@endif
@if(isset($session['803']))
    <button class="btn btn-outline-success btn-icons" title="Aprobar Venta Externa" type="submit" onclick="confirmar('aprobar-{{$id}}', '¿Está seguro que desea aprobar esta venta externa?', 'Los cambios realizados no se pueden revertir');"><i class="fas fa-check"></i></button>
@endif