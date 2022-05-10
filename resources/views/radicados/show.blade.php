@extends('layouts.app')

@section('boton')
    <form action="{{ route('radicados.escalar',$radicado->id) }}" method="POST" class="delete_form" style="display: none;" id="escalar{{$radicado->id}}">
      	{{ csrf_field() }}
    </form>

    <form action="{{ route('radicados.solventar',$radicado->id) }}" method="POST" class="delete_form" style="display: none;" id="solventar{{$radicado->id}}">
      	{{ csrf_field() }}
    </form>
    
    <form action="{{ route('radicados.proceder',$radicado->id) }}" method="POST" class="delete_form" style="display: none;" id="proceder{{$radicado->id}}">
      	{{ csrf_field() }}
    </form>
    
    @if($radicado->estatus==0 || $radicado->estatus==2)
        <a href="#" onclick="confirmar('proceder{{$radicado->id}}', '¿Está seguro de que desea @if($radicado->tiempo_ini == null) iniciar @else finalizar @endif  el radicado?');" class="btn btn-outline-success btn-sm "title="@if($radicado->tiempo_ini == null) Iniciar @else Finalizar @endif Radicado">@if($radicado->tiempo_ini == null) Iniciar @else Finalizar @endif Radicado</a>
        @if(isset($_SESSION['permisos']['203']))
            <a href="{{route('radicados.edit',$radicado->id)}}" class="btn btn-outline-primary btn-sm" title="Editar">Editar Caso</a>
        @endif
    @endif

    {{-- @if($radicado->estatus==0)
        @if(isset($_SESSION['permisos']['205']))
            <a href="#" onclick="confirmar('escalar{{$radicado->id}}', '¿Está seguro de que desea escalar el caso?');" class="btn btn-outline-warning btn-sm "title="Escalar Caso">Escalar Caso</a>
        @endif
	@endif --}}

    @if($radicado->estatus == 1 || $radicado->estatus == 3)

    @else
        @if($radicado->firma || $radicado->estatus==0)
            @if(isset($_SESSION['permisos']['207']))
                <a href="#" onclick="confirmar('solventar{{$radicado->id}}', '¿Está seguro de que desea solventar el caso?');" class="btn btn-outline-success btn-sm "title="Solventar Caso">Solventar Caso</a>
            @endif
        @endif
	@endif
@endsection

@section('content')
    <style>
    	body > div.container-scroller > div > div > div.content-wrapper > div > div > div > div.row.card-description > div > div > table > tbody > tr:nth-child(10) > td > img{
    		width: 547px;
    		height: 297px;
    		border-radius: 0%;
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
			}, 5000);
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
    						<th width="15%">DATOS GENERALES</th>
    						<th></th>
    					</tr>
    					<tr>
    						<th>N° Radicado</th>
    						<td>{{$radicado->codigo}}</td>
    					</tr>
    					<tr>
    						<th>Fecha</th>
    						<td>{{date('d-m-Y', strtotime($radicado->fecha))}}</td>
    					</tr>
    					@if ($radicado->tiempo_ini)
    					<tr>
    						<th>Inicio</th>
    						<td>{{ $radicado->tiempo_ini }}</td>
    					</tr>
    					@endif
    					@if ($radicado->tiempo_fin)
    					<tr>
    						<th>Final</th>
    						<td>{{ $radicado->tiempo_fin }}</td>
    					</tr>
    					<tr>
    						<th>Duración</th>
    						<td>{{ $duracion }} minuto(s)</td>
    					</tr>
    					@endif
    					<tr>
    						<th>Contrato</th>
    						<td>{{$radicado->contrato}}</td>
    					</tr>
    					@if ($radicado->ip)
    					<tr>
    						<th>Dirección IP</th>
    						<td>{{ $radicado->ip }}</td>
    					</tr>
    					@endif
    					@if ($radicado->mac_address)
    					<tr>
    						<th>Dirección MAC</th>
    						<td>{{ $radicado->mac_address }}</td>
    					</tr>
    					@endif
    					<tr>
    						<th>Cliente</th>
    						<td>{{$radicado->nombre}}</td>
    					</tr>
    					<tr>
    						<th>N° Telefónico</th>
    						<td>{{$radicado->telefono}}</td>
    					</tr>
    					<tr>
    						<th>Correo</th>
    						<td>{{$radicado->correo}}</td>
    					</tr>
    					<tr>
    						<th>Dirección</th>
    						<td>{{$radicado->direccion}}</td>
    					</tr>
    					@if($radicado->creado)
    					<tr>
    						<th>Creado desde</th>
    						<td>{{$radicado->creado()}}</td>
    					</tr>
    					@endif
    					<tr>
    						<th>Tipo de Servicio</th>
    						<td>{{$radicado->servicio()->nombre}}</td>
    					</tr>
    					@if ($radicado->valor)
    					<tr>
    						<th>Valor de la Instalación</th>
    						<td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($radicado->valor)}}</td>
    					</tr>
    					@endif
    					@if ($radicado->tecnico() != NULL)
    					<tr>
    						<th>Técnico Asociado</th>
    						<td>{{$radicado->tecnico()->nombres}}</td>
    					</tr>
    					@endif
    					@if ($radicado->responsable() != NULL)
    					<tr>
    						<th>Responsable</th>
    						<td>{{$radicado->responsable()->nombres}}</td>
    					</tr>
    					@endif
    					<tr>
    						<th>Observaciones del Radicado</th>
    						<td>@php echo $radicado->desconocido @endphp</td>
    					</tr>
    					<tr>
    						<th>Estatus</th>
    						<td>
    							@if ($radicado->estatus == 0)
    							    <span class="text-danger font-weight-bold">Pendiente</span>
    							@endif
    							@if ($radicado->estatus == 1)
    							    <span class="text-success font-weight-bold">Resuelto</span>
    							@endif
    							@if ($radicado->estatus == 2)
    							    <span class="text-danger font-weight-bold">Escalado / Pendiente</span>
    							@endif
    							@if ($radicado->estatus == 3)
    							    <span class="text-success font-weight-bold">Escalado / Resuelto</span>
    							@endif
                            </td>
    					</tr>
    					@if ($radicado->reporte)
    						<tr>
    							<th>Reporte del Técnico</th>
    							<td>{{$radicado->reporte}}</td>
    						</tr>
    					@endif
    					@if ($radicado->firma)
    						<tr>
    							<th>Firma Cliente</th>
    							<td>
    								<img src="data:image/png;base64,{{substr($radicado->firma,1)}}" class="img-fluid" style="width: 100%;height: auto;">
    	                        </td>
    						</tr>
    					@endif
    				</tbody>
    			</table>
    		</div>
    		@if($radicado->reporte=='' && $radicado->estatus > 1)
    			@if(isset($_SESSION['permisos']['210']))
    				<form method="POST" action="{{ route('radicados.update', $radicado->id ) }}" style="padding: 2% 3%;    " role="form" class="forms-sample" novalidate id="form-radicado" >
    					{{ csrf_field() }}
    					<input name="_method" type="hidden" value="PATCH">
    					<div class="col-md-12 form-group">
    						<label class="control-label">Observaciones del Técnico</label>
    						<textarea  class="form-control form-control-sm min_max_100" id="reporte" required="" name="reporte"></textarea>
    						<span class="help-block error">
    							<strong>{{ $errors->first('desconocido') }}</strong>
    						</span>
    					</div>
    
    					<div class="col-sm-12" style="text-align: center;">
    						<a href="{{route('radicados.index')}}" class="btn btn-outline-secondary">Cancelar</a>
    						<button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
    					</div>
    				</form>
    			@endif
    		@endif
    	</div>
    </div>
@endsection
