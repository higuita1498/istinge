<a href="{{ route('descuentos.show', $id) }}" class="btn btn-outline-info btn-icons" title="Ver detalles"><i class="fas fa-eye"></i></a>
@if (isset($session['736']))
    @if($estado==2)
        <a href="javascript:aprobarDescuento({{$id}})" class="btn btn-outline-success btn-icons" title="Aprobar"><i class="fas fa-check"></i></a>
    @endif
@endif
@if (isset($session['849']))
    @if($estado==2)
        <a href="javascript:noaprobarDescuento({{$id}})" class="btn btn-outline-danger btn-icons" title="No Aprobar49"><i class="fas fa-times"></i></a>
    @endif
@endif