<a href="{{ route('descuentos.show', $id) }}" class="btn btn-outline-info btn-icons" title="Ver detalles"><i class="fas fa-eye"></i></button
@if (isset($session['736']))
    @if($estado==2)
        <a href="javascript:aprobarDescuento({{$id}})" class="btn btn-outline-success btn-icons" title="Aprobar"><i class="fas fa-check"></i></button
    @endif
@endif