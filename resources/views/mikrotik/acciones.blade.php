@if(auth()->user()->modo_lectura())
@else
    <form action="{{route('mikrotik.conectar',$id)}}" method="get" class="delete_form" style="margin:  0;display: inline-block;" id="conectar-{{$id}}">
        @csrf
    </form>
    @if($status == 0)
        <form action="{{ route('mikrotik.destroy',$id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-mikrotik-{{$id}}">
            @csrf
            <input name="_method" type="hidden" value="DELETE">
        </form>
    @endif
    @if($status == 1 && $reglas == 0)
        <form action="{{route('mikrotik.reglas',$id)}}" method="get" class="delete_form" style="margin:  0;display: inline-block;" id="regla-{{$id}}">
            @csrf
        </form>
        <form action="{{route('mikrotik.importar',$id)}}" method="get" class="delete_form" style="margin:  0;display: inline-block;" id="importar-{{$id}}">
            @csrf
        </form>
    @endif

    @if(isset($session['431']))
        <a title="Editar" href="{{route('mikrotik.edit',$id)}}" class="btn btn-outline-primary btn-icons"><i class="fas fa-edit"></i></a>
    @endif
    @if(isset($session['430']))
        <a title="Ver detalles" href="{{route('mikrotik.show',$id)}}" class="btn btn-outline-info btn-icons"><i class="fas fa-eye"></i></a>
    @endif

    @if(isset($session['431']))
        @if($status == 0)
            <button title="Conectar Mikrotik" class="btn btn-outline-success btn-icons" type="submit" onclick="confirmar('conectar-{{$id}}', '¿Está seguro que desea conectar la Mikrotik {{$nombre}}?', '');"><i class="fas fa-plug"></i></button>
        @else
            <button title="Desconectar Mikrotik" class="btn btn-outline-danger btn-icons" type="submit" onclick="confirmar('conectar-{{$id}}', '¿Está seguro que desea desconectar la Mikrotik {{$nombre}}?', '');"><i class="fas fa-plug"></i></button>
        @endif
    @endif

    @if($status == 1)
        <a href="{{ route('mikrotik.grafica',$id )}}" class="btn btn-outline-danger btn-icons" title="Gráfica de Consumo"><i class="fas fa-chart-area"></i></a>
        @if($reglas == 0)
            @if(isset($session['806']))
                <button title="Aplicar Reglas de Corte" class="btn btn-outline-dark btn-icons" type="submit" onclick="confirmar('regla-{{$id}}', '¿Está seguro que desea aplicar las reglas de corte a esta Mikrotik {{$nombre}}?', '');"><i class="fas fa-plus"></i></button>
            @endif
        @endif
    @endif

    @if(isset($session['433']))
        @if($status == 0 && $uso == 0)
            <button class="btn btn-outline-danger btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-mikrotik-{{$id}}', '¿Está seguro que deseas eliminar el Mikrotik {{$nombre}}?', 'Se borrará de forma permanente');"><i class="fas fa-times"></i></button>
        @endif
    @endif

    @if(isset($session['807']))
        <a title="IP's Autorizadas" href="{{ route('mikrotik.ips-autorizadas',$id )}}" class="btn btn-outline-warning btn-icons"><i class="fas fa-project-diagram"></i></a>
    @endif
@endif


