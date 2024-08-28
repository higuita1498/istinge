@extends('layouts.app')


@section('boton')
    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">Integra Colombia: Suscripción Vencida</h4>
	       <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
	    </div>
	@else
        <a href="javascript:abrirFiltrador()" class="btn btn-info btn-sm my-1" id="boton-filtrar"><i class="fas fa-search"></i>Filtrar</a>
        <a href="{{route('asignacionmaterial.create')}}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nueva Asignacion de Material</a>
    @endif
@endsection

@section('content')
    @if(Session::has('success'))
        <div class="alert alert-success">
        	{{Session::get('success')}}
        </div>
        <script type="text/javascript">
        	setTimeout(function() {
        		$('.alert').hide();
        		$('.active_table').attr('class', ' ');
        	}, 5000);
        </script>
    @endif

	@if(Session::has('error'))
		<div class="alert alert-danger" >
			{{Session::get('error')}}
		</div>

		<script type="text/javascript">
			setTimeout(function(){
			    $('.alert').hide();
			    $('.active_table').attr('class', ' ');
			}, 8000);
		</script>
	@endif

    @if(Session::has('danger'))
        <div class="alert alert-danger">
        	{{Session::get('danger')}}
        </div>
        <script type="text/javascript">
        	setTimeout(function() {
        		$('.alert').hide();
        		$('.active_table').attr('class', ' ');
        	}, 5000);
        </script>
    @endif

    @if(Session::has('message_denied'))
	    <div class="alert alert-danger" role="alert">
	    	{{Session::get('message_denied')}}
	    	@if(Session::get('errorReason'))<br> <strong>Razon(es): <br></strong>
	    	    @if(count(Session::get('errorReason')) > 1)
	    	        @php $cont = 0 @endphp
	    	        @foreach(Session::get('errorReason') as $error)
	    	            @php $cont = $cont + 1; @endphp
	    	            {{$cont}} - {{$error}} <br>
	    	        @endforeach
	    	    @endif
	    	@endif
	    	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
	    		<span aria-hidden="true">&times;</span>
	    	</button>
	    </div>
	@endif

	@if(Session::has('message_success'))
	    <div class="alert alert-success" role="alert">
	    	{{Session::get('message_success')}}
	    	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
	    		<span aria-hidden="true">&times;</span>
	    	</button>
	    </div>
	@endif

	<div class="container-fluid d-none" id="form-filter">
		<fieldset>
            <legend>Filtro de Búsqueda</legend>
			<div class="card shadow-sm border-0">
        		<div class="card-body pt-1 pb-3" style="background: #f9f9f9;">
					<div class="row">
						<div class="col-md-2 pl-1 pt-1">

						</div>
					</div>
					<div class="row">
						<div class="col-md-12 pl-1 pt-1 text-center">
							<a href="javascript:limpiarFiltrador()" class="btn btn-icons ml-1 btn-outline-danger rounded btn-sm p-1" title="Limpiar parámetros de busqueda"><i class="fas fa-times"></i></a>
							<a href="javascript:void(0)" id="filtrar" class="btn btn-icons btn-outline-info rounded btn-sm p-1" title="Iniciar busqueda avanzada"><i class="fas fa-search"></i></a>
							<a href="javascript:exportar()" class="btn btn-icons mr-1 btn-outline-success rounded btn-sm p-1" title="Exportar"><i class="fas fa-file-excel"></i></a>
						</div>
					</div>
				</div>
			</div>
		</fieldset>
	</div>

	<div class="row card-description">
		<div class="col-md-12">
    		<div class="container-filtercolumn form-inline">

			</div>
		</div>
		<div class="col-md-12">
            <table class="table table-striped table-hover" id="table-general">
				<thead class="thead-dark">
					<tr>
                        <th></th>
                        <th>Referencia</th>
                        <th>Nombre de Tecnico</th>
                        <th>Email de Tecnico</th>
                        <th>Fecha</th>
						<th>Acciones</th>
					</tr>
				</thead>
                <tbody>
                    @foreach ($materiales as $material)
                    <tr>
                        <td></td>
                        <td><a href="#">{{ $material->referencia }}</a></td>
                        <td>{{$material->tecnico->nombres}}</td>
                        <td>{{$material->tecnico->email}}</td>
                        <td>{{$material->fecha}}</td>
                        <td>
                            <a href="{{ route('asignacionmaterial.edit', $material->id) }}"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
                            <form action="{{ route('asignacionmaterial.delete',$material->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-asignacion{{$material->referencia}}">
                                {{ csrf_field() }}
                                <input name="_method" type="hidden" value="DELETE">
                            </form>
                            <button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-asignacion{{$material->referencia}}', '¿Estas seguro que deseas eliminar la asignación?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
			</table>
		</div>
	</div>
@endsection
@section('scripts')
<script>
</script>
@endsection
