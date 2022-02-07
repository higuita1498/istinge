@extends('layouts.app')
@section('boton')
	<a href="{{route('contactos.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Cliente</a>
@endsection
@section('content')
	@if(Session::has('success'))
		<div class="alert alert-success" style="margin-left: 2%;margin-right: 2%;">
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
			<div class="row card-description" id="detalles" @if($busqueda) style="display: none;" @endif>
				<p><h4>Total Contactos: {{$totalContactos}}</h4></p>
			</div>

			<div class="col-md-12 mx-0 px-0">
				<form id="form-table-facturas">
					<input type="hidden" name="orderby"id="order_by"  value="1">
					<input type="hidden" name="order" id="order" value="0">
					<input type="hidden" id="form" value="form-table-facturas">
					<div id="filtro_tabla" @if(!$busqueda) style="display: none;" @endif>
						<table class="table table-striped table-hover filtro thresp bg-white">
							<tr class="form-group">
								<th><input type="text" class="form-control" name="name_1" id="name_1" placeholder="Nombre Cliente"  value="{{$request->name_1}}"></th>
								<th><input type="text" class="form-control" name="name_2" id="name_2" placeholder="Identificación Cliente" value="{{$request->name_2}}"></th>
								<th><input type="text" class="form-control" name="name_3" id="name_3" placeholder="Teléfono Cliente" value="{{$request->name_3}}"></th>
							</tr>
						</table>
						<center><button class="my-3 btn btn-outline-primary btn-sm">Filtrar</button><a href="#" class="ml-1 my-3 btn btn-outline-warning btn-sm" onclick="resetForm();">Limpiar</a>
						@if(!$busqueda)
							<button type="button" class="my-3 btn btn-outline-danger btn-sm" onclick="hidediv('filtro_tabla'); showdiv('detalles'); showdiv('boto_filtrar'); vaciar_fitro();">Cerrar</button>
						@else
							<a href="{{route('contactos.index')}}" class="my-3 btn btn-outline-danger btn-sm" >Cerrar</a>
						@endif</center>
					</div>

					<div class="row">
						<div class="col-md-12">
							<button type="button" @if($busqueda) style="display: none;" @endif class="btn btn-outline-primary btn-sm float-right ml-2" id="boto_filtrar" onclick="showdiv('filtro_tabla'); hidediv('detalles'); hidediv('boto_filtrar');"><i class="fas fa-search"></i> Filtrar</button>
						</div>
					</div>
				</form>

				<table class="table table-striped table-hover" id="table-facturas">
					<thead class="thead-dark">
						<tr>
							<th>Nombre <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==1?'':'no_order'}}" campo="1" order="@if($request->orderby==1){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==1){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
							<th>Identificación <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==2?'':'no_order'}}" campo="2" order="@if($request->orderby==2){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==2){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
							<th>Teléfono <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==3?'':'no_order'}}" campo="3" order="@if($request->orderby==3){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==3){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
							<th>Email <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==4?'':'no_order'}}" campo="4" order="@if($request->orderby==4){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==4){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
							<th class="text-center">Acciones</th>
			            </tr>
					</thead>
					<tbody>
						@foreach($contactos as $contacto)
							<tr @if($contacto->id==Session::get('contacto_id')) class="active_table" @endif>
								<td><a href="{{route('contactos.show',$contacto->id)}}">{{$contacto->nombre}}</a></td>
								<td><spam title="{{$contacto->tip_iden()}}">({{$contacto->tip_iden('mini')}})</spam> {{$contacto->nit}}</td>
								<td>@if($contacto->celular) {{$contacto->celular}} @else {{ $contacto->telefono1 }} @endif</td>
								<td>{{$contacto->email}}</td>
								<td class="text-center">
								    @if(isset($_SESSION['permisos']['7']))
										<form action="{{ route('contactos.destroy',$contacto->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-contacto{{$contacto->id}}">
											{{ csrf_field() }}
											<input name="_method" type="hidden" value="DELETE">
										</form>
									@endif
									<a href="{{route('contactos.show',$contacto->id)}}" class="btn btn-outline-info btn-icons"><i class="far fa-eye"></i></i></a>
									@if(isset($_SESSION['permisos']['6']))
									    <a href="{{route('contactos.edit',$contacto->id)}}" class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
									@endif
									
									@if(isset($_SESSION['permisos']['7']))
									    <button class="btn btn-outline-danger btn-icons mr-1" type="submit" title="Eliminar" onclick="confirmar('eliminar-contacto{{$contacto->id}}', '¿Está seguro que deseas eliminar el cliente?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
									@endif
								</td>
							</tr>
						@endforeach
					</tbody>
				</table>

				<div class="text-right">
					{!!$contactos->render()!!}
			    </div>
			</div>
		</div>
	</div>
	<script>
	    function resetForm(){
	    	$("#name_1,#name_2,#name_3").val('');
	    }
    </script>
@endsection
