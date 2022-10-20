@extends('layouts.app')

@section('boton')
    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
	        <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
	    </div>
	@else
    @if(isset($_SESSION['permisos']['703']))
        <form action="{{ route('plantillas.destroy',$plantilla->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-plantilla-{{$plantilla->id}}">
            @csrf
            <input name="_method" type="hidden" value="DELETE">
        </form>
    @endif
    @if(isset($_SESSION['permisos']['705']))
        <form action="{{ route('plantillas.act_desc',$plantilla->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="act-desc-{{$plantilla->id}}">
            @csrf
        </form>
    @endif
    
    <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
        <div class="btn-group" role="group">
            <button id="btnGroupDrop1" type="button" class="btn btn-dark dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                Acciones de la Plantilla
            </button>
            <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                @if(isset($_SESSION['permisos']['702']))
                    <a href="{{route('plantillas.edit',$plantilla->id)}}" class="dropdown-item" title="Editar"><i class="fas fa-edit"></i> Editar</a>
                @endif
                @if(isset($_SESSION['permisos']['705']))
                    <button class="dropdown-item" type="submit" title="@if($plantilla->status == 0) Activar @else Desactivar @endif" onclick="confirmar('act-desc-{{$plantilla->id}}', '¿Está seguro que desea @if($plantilla->status == 0) activar @else desactivar @endif la plantilla?', '');"><i class="fas fa-power-off"></i> @if($plantilla->status == 0) Activar @else Desactivar @endif</button>
                @endif
                @if(isset($_SESSION['permisos']['703']))
                    <button class="dropdown-item" type="submit" title="Eliminar" onclick="confirmar('eliminar-plantilla-{{$plantilla->id}}', '¿Está seguro que desea eliminar la plantilla?', 'Se borrará de forma permanente');"><i class="fas fa-times"></i> Eliminar</button>
                @endif
            </div>
        </div>
    </div>
    @endif
@endsection

@section('content')
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
    						<th width="25%">DATOS GENERALES</th>
    						<th></th>
    					</tr>
    					<tr>
    						<th>Titulo</th>
    						<td><strong>{{ $plantilla->title }}</strong></td>
    					</tr>
    					<tr>
    						<th>Tipo</th>
    						<td>{{ $plantilla->tipo() }}</td>
    					</tr>
    					<tr>
    						<th>Clasificación</th>
    						<td>{{ $plantilla->clasificacion}}</td>
    					</tr>
    					<tr>
    						<th>Creado por</th>
    						<td>{{ $plantilla->created_by()->nombres }}</td>
    					</tr>
    					<tr>
    					<tr>
    						<th>Estado</th>
    						<td><span class="text-{{$plantilla->status('true')}}">{{ $plantilla->status() }}</span></td>
    					</tr>
    				</tbody>
    			</table>
    		</div>
    		
    		<div class="mt-3 p-3" style="border: 1px solid rgba(0, 0, 0, 0.125);border-radius: 0.25rem;">
    		    @php echo($plantilla->contenido); @endphp
    		</div>
    	</div>
    </div>
@endsection