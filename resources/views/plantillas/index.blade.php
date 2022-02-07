@extends('layouts.app')

@section('boton')
    @if(isset($_SESSION['permisos']['706']))
        <a href="{{route('plantillas.envio')}}" class="btn btn-success btn-sm" ><i class="fas fa-paper-plane"></i> Enviar Aviso</a>
    @endif
    @if(isset($_SESSION['permisos']['701']))
        <a href="{{route('plantillas.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nueva Plantilla</a>
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
			<table class="table table-striped table-hover" id="table-facturas">
    			<thead class="thead-dark">
    				<tr>
    	                <th class="text-center">Nro</th>
    	                <th class="text-center">Título</th>
    	                <th class="text-center">Tipo</th>
    	                <th class="text-center">Clasificación</th>
    	                <th class="text-center">Estado</th>
    	                <th class="text-center">Acciones</th>
    	            </tr>
    			</thead>
    			<tbody>
    			    @foreach($plantillas as $plantilla)
    				<tr @if($plantilla->id==Session::get('plantilla_id')) class="active_table" @endif>
    	                <td class="text-center">{{ $plantilla->nro }}</td>
    	                <td class="text-center">{{ $plantilla->title }}</td>
    	                <td class="text-center">{{ $plantilla->tipo() }}</td>
    	                <td class="text-center">{{ $plantilla->clasificacion() }}</td>
    	                <td class="text-center text-{{$plantilla->status('true')}}">{{ $plantilla->status() }}</td>
    	                <td class="text-center">
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
    	                    
    	                    @if(isset($_SESSION['permisos']['704']))
    	                        <a href="{{route('plantillas.show',$plantilla->id)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>
    	                    @endif
    	                    @if(isset($_SESSION['permisos']['702']))
    	                        <a href="{{route('plantillas.edit',$plantilla->id)}}" class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
    						@endif
    						@if(isset($_SESSION['permisos']['703']))
    						    <button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-plantilla-{{$plantilla->id}}', '¿Está seguro que desea eliminar la plantilla?', 'Se borrará de forma permanente');"><i class="fas fa-times"></i></button>
    						@endif
    						@if(isset($_SESSION['permisos']['705']))
    						    <button class="btn @if($plantilla->status == 0) btn-outline-success @else btn-outline-danger @endif btn-icons" type="submit" title="@if($plantilla->status == 0) Activar @else Desactivar @endif" onclick="confirmar('act-desc-{{$plantilla->id}}', '¿Está seguro que desea @if($plantilla->status == 0) activar @else desactivar @endif la plantilla?', '');"><i class="fas fa-power-off"></i></button>
    						@endif
    	                </td>
    	            </tr>
    	            @endforeach
    			</tbody>
    		</table>
		</div>
	</div>
@endsection