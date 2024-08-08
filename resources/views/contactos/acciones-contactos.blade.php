{{-- @if ($usado == 0)
<form action="{{ route('contactos.destroy',$id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-contacto{{$id}}">
	{{ csrf_field() }}
	<input name="_method" type="hidden" value="DELETE">
</form>
@endif
<a href="{{route('contactos.show',$id)}}" class="btn btn-outline-info btn-icons"><i class="far fa-eye"></i></i></a>
<a href="{{route('contactos.edit',$id)}}" class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>

@if ($email && $contract != 'N/A')
<a href="{{route('avisos.envio.email.cliente',$id)}}" target="_blank" class="btn btn-outline-success btn-icons" title="Enviar Notificación por EMAIL"><i class="fas fa-envelope-open-text"></i></a>
@endif --}}

{{-- @if ($celular && $contract != 'N/A')
<a href="{{route('avisos.envio.sms.cliente',$id)}}" target="_blank" class="btn btn-outline-success btn-icons" title="Enviar Notificación por SMS"><i class="fas fa-mobile-alt"></i></a>
@endif --}}

{{-- @if($contract != 'N/A')
    <a href="{{ route('contratos.show',$details['id'] )}}" target="_blank" class="btn btn-outline-info btn-icons" title="Ver Contrato"><i class="fas fa-file-contract"></i></a>
    @if($details['state'] == 'enabled')
        <a href="{{ route('contratos.grafica',$details['id'] )}}" target="_blank" class="btn btn-outline-dark btn-icons" title="Ver Gráfica de Conexión"><i class="fas fa-chart-area"></i></a>
        <a href="{{ route('contratos.grafica_consumo',$details['id'] )}}" target="_blank" class="btn btn-outline-info btn-icons" title="Ver Gráfica de Consumo"><i class="fas fa-chart-line"></i></a>
        <a href="{{ route('contratos.conexion',$details['id'] )}}" target="_blank" class="btn btn-outline-success btn-icons" title="Ping de Conexión"><i class="fas fa-plug"></i></a>
        <a href="{{ route('contratos.log',$details['id'] )}}" target="_blank" class="btn btn-outline-info btn-icons" title="Log de Contrato"><i class="fas fa-clipboard-list"></i></a>
    @endif
@endif

@if ($usado == 0)
<button class="btn btn-outline-danger btn-icons mr-1" type="submit" title="Eliminar" onclick="confirmar('eliminar-contacto{{$id}}', '¿Está seguro que deseas eliminar el cliente?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
@endif

<a href="{{route('factura.create.cliente', $id)}}" class="btn btn-outline-warning btn-icons" title="Crear una factura" target="_blank"><i class="fas fa-file-invoice-dollar"></i></a>
<a href="{{route('cliente.cambiares',$id)}}" class="btn btn-outline-primary btn-icons" title="redireccionar a CRM"><i class="fas fa-file-contract"></i></a> --}}
