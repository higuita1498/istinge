@if($estatus==0)
    @if(isset($session['204']))
        <form action="{{ route('radicados.destroy',$id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-{{$id}}">
            {{ csrf_field() }}
            <input name="_method" type="hidden" value="DELETE">
        </form>
    @endif
@endif

@if($estatus == 1 || $estatus == 3)
    @if(isset($session['805']))
        <form action="{{ route('radicados.reabrir',$id) }}" method="POST" class="delete_form" style="display: none;" id="reabrir-{{$id}}">
            {{ csrf_field() }}
        </form>
    @endif
@endif

<a href="{{route('radicados.show',$id)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>

@if($estatus==0 || $estatus==2)
    @if(isset($session['203']))
        <a href="{{route('radicados.edit',$id)}}" class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
    @endif
@endif

@if($firma || $estatus==0)
    @if(isset($session['207']))
        <form action="{{ route('radicados.solventar',$id) }}" method="POST" class="delete_form" style="display: none;" id="solventar-{{$id}}">
        	{{ csrf_field() }}
        </form>
        <a href="#" onclick="confirmar('solventar-{{$id}}', '¿Está seguro de que desea solventar el caso?');" class="btn btn-outline-success btn-icons" title="Solventar"><i class="fas fa-check-double"></i></a>
    @endif
@endif

<a href="{{route('radicados.imprimir', ['id' => $id, 'name'=> 'Caso Radicado No. '.$codigo.'.pdf'])}}"  class="btn btn-outline-primary btn-icons" title="Imprimir" target="_blank"><i class="fas fa-print"></i></a>

@if($estatus==2 && !$firma)
    @if(isset($session['209']))
        <a href="{{route('radicados.firmar', $id)}}"  class="btn btn-outline-success btn-icons" title="Firmar" target="_blank"><i class="fas fa-file-signature"></i></a>
    @endif
@endif

@if($estatus==0)
    @if(isset($session['204']))
        <button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-{{$id}}', '¿Estas seguro que deseas eliminar el radicado?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
    @endif
@endif

@if($estatus == 1 || $estatus == 3)
    @if(isset($session['805']))
        <a href="#" onclick="confirmar('reabrir-{{$id}}', '¿Está seguro de que desea reabrir el radicado?');" class="btn btn-outline-success btn-icons" title="Reabrir Radicado"><i class="fas fa-lock-open"></i></a>
    @endif
@endif


{{-- @if($tiempo_ini)
    <form action="{{ route('radicados.proceder',$id) }}" method="POST" class="delete_form" style="display: none;" id="proceder{{$id}}">
    	{{ csrf_field() }}
    </form>
    <a href="#" onclick="confirmar('proceder{{$id}}', '¿Está seguro de que desea @if($tiempo_ini == null) iniciar @else finalizar @endif  el radicado?');" class="btn btn-outline-success btn-icons" title="@if($tiempo_ini == null) Iniciar @else Finalizar @endif Radicado"><i class="fas fa-check"></i></a>
@endif --}}