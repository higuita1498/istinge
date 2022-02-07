@extends('layouts.app')
@section('boton')
	<a href="{{route('roles.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Tipo Usuario</a>


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
	<div class="row card-description">
		<div class="col-md-12">
			<table class="table table-striped table-hover" id="example">
				<thead class="thead-dark">
				<tr>
					<th>Nro</th>
					<th>Tipo Usuario</th>
					<th>Acciones</th>
				</tr>
				</thead>
				<tbody>
				@foreach($roles as $rol)
					<tr>
						<td>{{$rol->nro}}</td>
						<td>{{$rol->rol}}</td>
						<td>
							<a href="{{route('roles.edit',$rol->id)}}" class="btn btn-outline-primary btn-icons"><i class="fas fa-edit"></i></a>

							<button class="btn btn-outline-danger  btn-icons eliminar" idRol="{{$rol->id}}" title="Eliminar" titulo="Estas Seguro?"
							mensaje="Esta seguro de que desea eliminar este tipo de usuario?!" boton="Si,Eliminar"><i class="fas fa-times"></i></button>
						
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

$(document).ready(function(){
    
    $('#example tbody').on('click','.eliminar',function(){
        swal({
           title: $(this).attr('titulo'),
           text: $(this).attr('mensaje'),
           type: "warning",
           showCancelButton: true,
           confirmButtonColor: "#DD6B55",
           confirmButtonText: $(this).attr('boton'),
        }).then((value)=>{
            var url = '/empresa/configuracion/roles/eliminar';
            $.post(url,{ idRol:  $(this).attr('idRol') ,_token: $('meta[name="csrf-token"]').attr('content')},function(dato){
               if(dato['status'] == 'ok'){
                   swal("Registro Eliminado","El tipo de usuario fue eliminado satisfactoriamente","success")
                   .then((value) => {
                          window.location.href = '/empresa/configuracion/roles';
                    });
               }
            },'json');
        });
       
        
    });
});

</script>
@endsection