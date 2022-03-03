<center>
    <a href="{{route('crm.show',$id)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="fas fa-eye"></i></i></a>
    {{-- @if($estado == 0 || $estado == 2 || $estado == 3 || $estado == 4 || $estado == 5 || $estado == 6) --}}
    @if (isset($session['746']))
        <a href="javascript:gestionar({{$c_id}});" class="btn btn-outline-success btn-icons" title="Llamar"><i class="fas fa-phone"></i></i></a>
    @endif
    {{-- @endif --}}
    @if($estado == 4)
        @if (isset($session['747']))
            <a href="javascript:cambiarRetiroTotal('{{$id}}');" title="" class="btn btn-outline-danger btn-icons"><i class="fas fa-times"></i></a>
        @endif
    @endif
</center>