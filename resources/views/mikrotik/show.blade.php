@extends('layouts.app')

@section('content')
<div class="row card-description">
	<div class="col-md-12 text-center">
	    @if($mikrotik->status == 0)
        <form action="{{route('mikrotik.conectar',$mikrotik->id)}}" method="get" class="delete_form" style="margin:  0;display: inline-block;" id="conectar-{{$mikrotik->id}}">
            @csrf
        </form>
        <form action="{{ route('mikrotik.destroy',$mikrotik->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-mikrotik">
            @csrf
            <input name="_method" type="hidden" value="DELETE">
        </form>
        @endif
	    @if($mikrotik->status == 1)
	    <form action="{{route('mikrotik.reglas',$mikrotik->id)}}" method="get" class="delete_form" style="margin:  0;display: inline-block;" id="regla-{{$mikrotik->id}}">
            @csrf
        </form>
        <form action="{{route('mikrotik.importar',$mikrotik->id)}}" method="get" class="delete_form" style="margin:  0;display: inline-block;" id="importar-{{$mikrotik->id}}">
            @csrf
        </form>
        <form action="{{route('mikrotik.reiniciar',$mikrotik->id)}}" method="get" class="delete_form" style="margin:  0;display: inline-block;" id="reiniciar-{{$mikrotik->id}}">
            @csrf
        </form>
        @endif
        
	    <a title="Editar" href="{{route('mikrotik.edit',$mikrotik->id)}}" class="btn btn-outline-primary"><i class="fas fa-edit"></i> Editar Mikrotik</a>
	    @if($mikrotik->status == 1)
	    <a title="Log" href="{{route('mikrotik.log',$mikrotik->id)}}" class="btn btn-outline-success"><i class="fas fa-clipboard-check"></i> Ver Log</a>
	    <a title="Gráfica de Consumo" href="{{route('mikrotik.grafica',$mikrotik->id)}}" class="btn btn-outline-info"><i class="fas fa-chart-area"></i> Ver Gráfica de Consumo</a>
	    @endif
	    @if($mikrotik->status == 0)
	    <button title="Conectar Mikrotik" class="btn btn-outline-success" type="submit" onclick="confirmar('conectar-{{$mikrotik->id}}', '¿Está seguro que desea conectar la Mikrotik?', '');"><i class="fas fa-plug"></i> Conectar Mikrotik</button>
	    @endif
	    @if($mikrotik->status == 1)
	    <button title="Aplicar Reglas" class="btn btn-outline-dark" type="submit" onclick="confirmar('regla-{{$mikrotik->id}}', '¿Está seguro que desea aplicar las reglas a esta Mikrotik?', '');"><i class="fas fa-plus"></i> Aplicar Reglas</button>
	    {{--<button title="Importar Contratos" class="btn btn-outline-info" type="submit" onclick="confirmar('importar-{{$mikrotik->id}}', '¿Está seguro que desea importar todos los contratos desde {{$mikrotik->nombre}}?', '');"><i class="fas fa-sync"></i> Importar Contratos</button>--}}
	    <button title="Reiniciar" class="btn btn-outline-danger" type="submit" onclick="confirmar('reiniciar-{{$mikrotik->id}}', '¿Está seguro que desea reiniciar el mikrotik {{$mikrotik->nombre}}?', '');"><i class="fas fa-power-off"></i> Reiniciar Mikrotik</button>
	    @endif
	    @if($mikrotik->status == 0)
	    <button title="Eliminar" class="btn btn-outline-danger" type="submit" onclick="confirmar('eliminar-mikrotik', '¿Está seguro que deseas eliminar el Mikrotik?', 'Se borrará de forma permanente');"><i class="fas fa-times"></i> Eliminar Mikrotik</button>
	    @endif
	</div>
</div>


<div class="row card-description">
	<div class="col-md-12">
		<div class="table-responsive">
			<table class="table table-striped table-bordered table-sm info">
				<tbody>
					<tr>
						<th width="15%">DATOS GENERALES</th>
						<th></th>
					</tr>
					<tr>
						<th>Nombre</th>
						<td>{{ $mikrotik->nombre }}</td>
					</tr>
					<tr>
						<th>IP</th>
						<td>{{ $mikrotik->ip }}</td>
					</tr>
					<tr>
						<th>Puerto API</th>
						<td>{{ $mikrotik->puerto_api }}</td>
					</tr>
					<tr>
						<th>Puerto WEB</th>
						<td>{{ $mikrotik->puerto_web }}</td>
					</tr>
					<tr>
						<th>Interfaz WAN</th>
						<td>{{ $mikrotik->interfaz }}</td>
					</tr>
					<tr>
						<th>Interfaz LAN</th>
						<td>{{ $mikrotik->interfaz_lan }}</td>
					</tr>
					<tr>
						<th>Estado</th>
						<td><span class="font-weight-bold text-{{$mikrotik->status('true')}}">{{ $mikrotik->status() }}</span></td>
					</tr>
					<tr>
						<th>Segmentos de IP</th>
						<td>
						    @foreach($segmentos as $segmento)
						        {{$segmento->segmento}}<br>
						    @endforeach
						</td>
					</tr>
					@if($mikrotik->status == 1)
					<tr>
						<th>Uptime</th>
						<td>{{ $mikrotik->uptime }}</td>
					</tr>
					<tr>
						<th>Versión</th>
						<td>{{ $mikrotik->version }}</td>
					</tr>
					<tr>
						<th>Build Time</th>
						<td>{{ $mikrotik->buildtime }}</td>
					</tr>
					<tr>
						<th>Free Memory</th>
						<td>{{ $mikrotik->freememory }}</td>
					</tr>
					<tr>
						<th>Total Memory</th>
						<td>{{ $mikrotik->totalmemory }}</td>
					</tr>
					<tr>
						<th>CPU</th>
						<td>{{ $mikrotik->cpu }}</td>
					</tr>
					<tr>
						<th>CPU Count</th>
						<td>{{ $mikrotik->cpucount }}</td>
					</tr>
					<tr>
						<th>CPU Frequency</th>
						<td>{{ $mikrotik->cpufrequency }}</td>
					</tr>
					<tr>
						<th>CPU Load</th>
						<td>{{ $mikrotik->cpuload }}</td>
					</tr>
					<tr>
						<th>Free HDD Space</th>
						<td>{{ $mikrotik->freehddspace }}</td>
					</tr>
					<tr>
						<th>Total HDD Space</th>
						<td>{{ $mikrotik->totalhddspace }}</td>
					</tr>
					<tr>
						<th>Architecture Name</th>
						<td>{{ $mikrotik->architecturename }}</td>
					</tr>
					<tr>
						<th>Board</th>
						<td>{{ $mikrotik->board }}</td>
					</tr>
					<tr>
						<th>Platform</th>
						<td>{{ $mikrotik->platform }}</td>
					</tr>
					@endif
					
					@if($mikrotik->created_by)
					<tr>
						<th>Registrado por</th>
						<td>{{ $mikrotik->created_by()->nombres }}</td>
					</tr>
					@endif
					
					@if($mikrotik->updated_by)
					<tr>
						<th>Actualizado por</th>
						<td>{{ $mikrotik->updated_by()->nombres }}</td>
					</tr>
					@endif
				</tbody>
			</table>
		</div>
	</div>
</div>

@endsection
