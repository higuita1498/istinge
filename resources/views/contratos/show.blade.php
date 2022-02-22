@extends('layouts.app')

@section('boton')
    @if($contrato->cs_status==1)
        <form action="{{ route('contratos.state',$contrato->id) }}" method="post" class="delete_form" style="margin:0;display: inline-block;" id="cambiar-state{{$contrato->id}}">
	       @csrf
	    </form>
	    <form action="{{ route('contratos.destroy',$contrato->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-contrato{{$contrato->id}}">
	        @csrf
	        <input name="_method" type="hidden" value="DELETE">
	    </form>
	    <form action="{{ route('contratos.destroy_to_mk',$contrato->id) }}" method="post" class="delete_form" style="margin:0;display: inline-block;" id="eliminar-contrato-mk{{$contrato->id}}">
	       @csrf
	    </form>
        <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
            <div class="btn-group" role="group">
                <button id="btnGroupDrop1" type="button" class="btn btn-dark dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Acciones del Contrato
                </button>
                <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                    @if(isset($_SESSION['permisos']['406']))
                    <a href="{{ route('contratos.edit',$contrato->id )}}"  class="dropdown-item" title="Editar Contrato"><i class="fas fa-edit"></i></i> Editar Contrato</a>
                    @endif
                    @if(isset($_SESSION['permisos']['407']))
                    <button @if($contrato->state == 'enabled') class="dropdown-item" title="Deshabilitar Contrato" @else  class="dropdown-item" title="Habilitar Contrato" @endif type="submit" onclick="confirmar('cambiar-state{{$contrato->id}}', '¿Está seguro que desea @if($contrato->state == 'enabled') deshabilitar @else habilitar @endif contrato?', '');"><i class="fas fa-file-signature"></i>@if($contrato->state == 'enabled') Deshabilitar Contrato @else Habilitar Contrato @endif</button>
                    @endif
                    <a href="{{ route('contratos.grafica',$contrato->id )}}" class="dropdown-item" title="Gráfica de Conexión" onclick="cargando('true');"><i class="fas fa-chart-area"></i> Gráfica de Conexión</a>
                    <a href="{{ route('contratos.grafica_consumo',$contrato->id )}}" class="dropdown-item" title="Gráfica de Consumo" onclick="cargando('true');"><i class="fas fa-chart-line"></i> Gráfica de Consumo</a>
                    <a href="{{ route('contratos.conexion',$contrato->id )}}" class="dropdown-item" title="Ping de Conexión" onclick="cargando('true');"><i class="fas fa-plug"></i> Ping de Conexión</a>
                    <a href="{{ route('contratos.log',$contrato->id )}}" class="dropdown-item" title="Log del Contrato" onclick="cargando('true');"><i class="fas fa-clipboard-list"></i> Log del Contrato</a>
                    @if(isset($_SESSION['permisos']['439']))
                    <button class="dropdown-item" type="submit" title="Eliminar" onclick="confirmar('eliminar-contrato{{$contrato->id}}', '¿Está seguro que desea eliminar el contrato?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i> Eliminar Contrato</button>
                    @endif
                    @if(isset($_SESSION['permisos']['440']))
                    <button class="dropdown-item d-none" type="submit" title="Eliminar del Mikrotik" onclick="confirmar('eliminar-contrato-mk{{$contrato->id}}', '¿Está seguro que desea eliminar el contrato del Mikrotik?', 'Se borrara de forma permanente');"><i class="fas fa-times-circle"></i> Eliminar Contrato del Mikrotik</button>
                    @endif
                </div>
            </div>
        </div>
    @endif
@endsection

@section('content')
<style>
	body > div.container-scroller > div > div > div.content-wrapper > div > div > div > div.row.card-description > div > div > table > tbody > tr:nth-child(10) > td > img{
		width: 547px;
		height: 297px;
		border-radius: 0%;
	}
	.bg-th{
        background: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}} !important;
        border-color: {{Auth::user()->rol > 1 ? Auth::user()->empresa()->color:''}} !important;
        color: #fff !important;
    }
</style>

    @if(Session::has('success'))
		<div class="alert alert-success" >
			{{Session::get('success')}}
		</div>

		<script type="text/javascript">
			setTimeout(function(){
			    $('.alert').hide();
			    $('.active_table').attr('class', ' ');
			}, 10000);
		</script>
	@endif
	
	@if(Session::has('danger'))
		<div class="alert alert-danger" >
			{{Session::get('danger')}}
		</div>

		<script type="text/javascript">
			setTimeout(function(){
			    $('.alert').hide();
			    $('.active_table').attr('class', ' ');
			}, 5000);
		</script>
	@endif
	
	

<div class="row card-description">
	<div class="col-md-12">
		<div class="table-responsive">
			<table class="table table-striped table-bordered table-sm info">
				<tbody>
					<tr>
						<th class="bg-th" width="20%">DATOS GENERALES</th>
						<th class="bg-th"></th>
					</tr>
					<tr>
						<th>Nro. Contrato</th>
						<td>{{ $contrato->nro }}</td>
					</tr>
					<tr>
						<th>Nombre Servicio</th>
						<td>{{ $contrato->servicio }}</td>
					</tr>
					@if($contrato->conexion)
					<tr>
						<th>Tipo de Conexión</th>
						<td><strong>{{ $contrato->conexion() }}</strong></td>
					</tr>
					@endif
					@if($contrato->grupo_corte)
					<tr>
						<th>Grupo de Corte</th>
						<td><a href="{{ route('grupos-corte.show',$contrato->grupo_corte()->id )}}" target="_blank"><strong>{{ $contrato->grupo_corte()->nombre }}</strong></a> (CORTE {{ $contrato->grupo_corte()->fecha_corte }} - SUSPENSIÓN {{ $contrato->grupo_corte()->fecha_suspension }})</td>
					</tr>
					@endif
					<tr>
						<th>Estado Contrato</th>
						<td>
						    <strong class="text-{{$contrato->status('true')}}">{{$contrato->status()}}</strong>
						</td>
					</tr>
					<tr>
						<th>Dirección IP</th>
						<td>{{ $contrato->ip }}</td>
					</tr>
					@if($contrato->ip_new)
					<tr>
						<th>Dirección IP</th>
						<td>{{ $contrato->ip_new }}</td>
					</tr>
					@endif
					@if($contrato->mac_address)
					<tr>
						<th>Dirección MAC</th>
						<td>{{ $contrato->mac_address }}</td>
					</tr>
					@endif
					@if($contrato->interfaz)
					<tr>
						<th>Interfaz</th>
						<td>{{ $contrato->interfaz }}</td>
					</tr>
					@endif
					@if($contrato->marca_antena)
					<tr>
						<th>Antena</th>
						<td>{{ $contrato->marca_antena()->nombre }} @if($contrato->modelo_antena) - {{$contrato->modelo_antena}} @endif</td>
					</tr>
					@endif
					@if($contrato->marca_router)
					<tr>
						<th>Router</th>
						<td>{{ $contrato->marca_router()->nombre }} @if($contrato->modelo_router) - {{$contrato->modelo_router}} @endif</td>
					</tr>
					@endif
					@if($contrato->nodo)
					<tr>
						<th>Nodo Asociado</th>
						<td><a href="{{ route('nodos.show',$contrato->nodo()->id )}}" target="_blank"><strong>{{ $contrato->nodo()->nombre }}</strong></a></td>
					</tr>
					@endif
					@if($contrato->ap)
					<tr>
						<th>Access Point Asociado</th>
						<td><a href="{{ route('access-point.show',$contrato->ap()->id )}}" target="_blank"><strong>{{ $contrato->ap()->nombre }}</strong></a></td>
					</tr>
					@endif
					@if($contrato->server_configuration_id)
					<tr>
						<th>Servidor Asociado</th>
						<td>{{ $contrato->servidor()->nombre }}</td>
					</tr>
					@endif
					<tr>
						<th>Plan Contratado</th>
						<td><a href="{{route('planes-velocidad.show',$contrato->plan_id)}}" target="_blank"><strong>{{ $contrato->plan }}</strong></a></td>
					</tr>
					<tr>
						<th>Precio Plan</th>
						<td>{{ Auth::user()->empresa()->moneda }} {{ App\Funcion::Parsear($contrato->price) }}</td>
					</tr>
					@if($contrato->creador)
					<tr>
						<th>Contrato Registrado por</th>
						<td>{{ $contrato->creador }}</td>
					</tr>
					@endif
				</tbody>
			</table>
			
			<table class="table table-striped table-bordered table-sm info mt-4">
				<tbody>
					<tr>
						<th class="bg-th" width="20%">CLIENTE ASOCIADO</th>
						<th class="bg-th"></th>
					</tr>
					<tr>
						<th>Nombre Cliente</th>
						<td><a href="{{ route('contactos.show',$contrato->id_cliente )}}" target="_blank"><strong>{{ $contrato->nombre }}</strong></a></td></td>
					</tr>
					@if($contrato->nit)
					<tr>
						<th>Cédula Cliente</th>
						<td>{{ $contrato->nit }}</td>
					</tr>
					@endif
					@if($contrato->celular || $contrato->telefono1)
					<tr>
						<th>Nro Teléfono</th>
						<td>@if($contrato->celular) {{ $contrato->celular }} @else {{ $contrato->telefono1 }} @endif</td>
					</tr>
					@endif
					@if($contrato->email)
					<tr>
						<th>Correo Electrónico</th>
						<td>{{ $contrato->email }}</td>
					</tr>
					@endif
					@if($contrato->barrio)
					<tr>
						<th>Barrio</th>
						<td>{{ $contrato->barrio }}</td>
					</tr>
					@endif
					@if($contrato->direccion)
					<tr>
						<th>Dirección</th>
						<td>{{ $contrato->direccion }}</td>
					</tr>
					@endif
				</tbody>
			</table>
		</div>
	</div>
</div>

<div class="row">

</div>
@endsection
