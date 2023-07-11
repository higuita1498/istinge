@if($tipo == 1)
    @if(isset($session['821']))
        <a href="{{route('productos.show_asignacion', $id)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>
    @endif
@else
    @if(isset($session['826']))
        <a href="{{route('productos.show_devolucion', $id)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>
    @endif
@endif