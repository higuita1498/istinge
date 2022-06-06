@if(Auth::user()->modo_lectura())
@else
    <a href="{{route('notascredito.show',$nro)}}"  class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></i></a>
    <a href="{{route('notascredito.imprimir.nombre',['id' => $nro, 'name'=> 'Nota Credito No. '.$nro.'.pdf'])}}" target="_blank" class="btn btn-outline-primary btn-icons" title="Imprimir"><i class="fas fa-print"></i></a>
    @if(Auth::user()->empresa()->form_fe == 1 && $emitida == 0 && Auth::user()->empresa()->estado_dian == 1 && Auth::user()->empresa()->technicalkey != null)
        <a onclick="confirmSendDian('{{route('xml.notacredito',$id)}}','{{$nro}}')" href="#"  class="btn btn-outline-primary btn-icons"title="Emitir Nota crédito"><i class="fas fa-sitemap"></i></a>
    @endif
    @if($emitida !=1)
        <a href="{{route('notascredito.edit',$nro)}}"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
    @endif
    <form action="{{ route('notascredito.destroy',$id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-notascredito{{$id}}">
    	{{ csrf_field() }}
    	<input name="_method" type="hidden" value="DELETE">
    </form>
    @if($emitida !=1)
        <button class="btn btn-outline-danger  btn-icons negative_paging" type="submit" title="Eliminar" onclick="confirmar('eliminar-notascredito{{$id}}', '¿Estas seguro que deseas eliminar nota de crédito?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
    @endif
@endif