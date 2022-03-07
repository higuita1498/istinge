@if ($usado == 0)
<form action="{{ route('contactos.destroy',$id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-contacto{{$id}}">
	{{ csrf_field() }}
	<input name="_method" type="hidden" value="DELETE">
</form>
@endif
<a href="{{route('contactos.show',$id)}}" class="btn btn-outline-info btn-icons"><i class="far fa-eye"></i></i></a>
<a href="{{route('contactos.edit',$id)}}" class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
@if ($email && $contract != 'N/A')
<a href="{{route('avisos.envio.email.cliente',$id)}}" target="_blank" class="btn btn-outline-success btn-icons" title="Enviar Notificación por EMAIL"><i class="fas fa-envelope-open-text"></i></a>
@endif

{{-- @if ($celular && $contract != 'N/A')
<a href="{{route('avisos.envio.sms.cliente',$id)}}" target="_blank" class="btn btn-outline-success btn-icons" title="Enviar Notificación por SMS"><i class="fas fa-mobile-alt"></i></a>
@endif --}}

@if ($usado == 0)
<button class="btn btn-outline-danger btn-icons mr-1" type="submit" title="Eliminar" onclick="confirmar('eliminar-contacto{{$id}}', '¿Está seguro que deseas eliminar el cliente?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
@endif