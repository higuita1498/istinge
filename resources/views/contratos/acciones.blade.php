<form action="{{ route('contratos.destroy',$id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-contrato{{$id}}">
    {{ csrf_field() }}
    <input name="_method" type="hidden" value="DELETE">
</form>
    
<form action="{{ route('contratos.state',$id) }}" method="post" class="delete_form" style="margin:0;display: inline-block;" id="cambiar-state{{$id}}">
    {{ csrf_field() }}
</form>

<a href="{{ route('contratos.show',$id )}}"  class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>
@if($state == 'enabled')
    <a href="{{ route('contratos.grafica',$id )}}" class="btn btn-outline-dark btn-icons" title="Gráfica de Conexión"><i class="fas fa-chart-area"></i></a>
    <a href="{{ route('contratos.grafica_consumo',$id )}}" class="btn btn-outline-info btn-icons" title="Gráfica de Consumo"><i class="fas fa-chart-line"></i></a>
    <a href="{{ route('contratos.conexion',$id )}}" class="btn btn-outline-success btn-icons" title="Ping de Conexión"><i class="fas fa-plug"></i></a>
    <a href="{{ route('contratos.log',$id )}}" class="btn btn-outline-info btn-icons" title="Log de Contrato"><i class="fas fa-clipboard-list"></i></a>
@endif
<a href="{{ route('contratos.edit',$id )}}" class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
@if($latitude && $longitude)
    <a href='https://www.google.com/maps/search/{{$latitude}},{{$longitude}}?hl=es' class="btn btn-outline-success btn-icons" title="Ver Ubicación Google Maps" target="_blank"><i class="fas fa-map-marked-alt"></i></a>
@endif
<button @if($state == 'enabled') class="btn btn-outline-danger btn-icons" title="Deshabilitar" @else class="btn btn-outline-success btn-icons" title="Habilitar" @endif type="submit" onclick="confirmar('cambiar-state{{$id}}', '¿Estas seguro que deseas cambiar el estatus del contrato?', '');"><i class="fas fa-file-signature"></i></button>
<button class="btn btn-outline-danger btn-icons mr-1" type="submit" title="Eliminar" onclick="confirmar('eliminar-contrato{{$id}}', '¿Está seguro que desea eliminar el contrato?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>

@if($firma_isp)
    <a href="{{ route('asignaciones.imprimir',$id )}}"  class="btn btn-outline-danger btn-icons" title="Imprimir Contrato Digital" target="_blank"><i class="fas fa-print"></i></i></a>
@endif

@if(Auth::user()->rol == 3 && $mk == 0)
<form action="{{ route('contratos.enviar_mk',$id) }}" method="post" class="delete_form" style="margin:0;display: inline-block;" id="enviar-mk-{{$id}}">
    {{ csrf_field() }}
</form>
<button class="btn btn-outline-warning btn-icons" title="Enviar a MK" type="submit" onclick="confirmar('enviar-mk-{{$id}}', '¿Está seguro que desea registrar este contrato en la mikrotik?', '');"><i class="fas fa-server"></i></button>
@endif