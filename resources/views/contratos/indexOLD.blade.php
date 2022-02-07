@extends('layouts.app')

@section('boton')
	<a href="{{route('contratos.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Contrato</a>
@endsection

@section('content')
	@if(Session::has('success'))
		<div class="alert alert-success" style="margin-left: 2%;margin-right: 2%;">
			{{Session::get('success')}}
		</div>
	@endif
@if(Session::has('message_denied'))
<div class="alert alert-danger" role="alert">
	{{Session::get('message_denied')}}
	@if(Session::get('errorReason'))<br> <strong>Razon(es): <br></strong>
	@if(count(Session::get('errorReason')) > 0)
	@php $cont = 0 @endphp
	@foreach(Session::get('errorReason') as $error)
	@php $cont = $cont + 1; @endphp
	{{$cont}} - {{$error}} <br>
	@endforeach
	@else
	{{ Session::get('errorReason') }}
	@endif
	@endif
	<button type="button" class="close" data-dismiss="alert" aria-label="Close">
		<span aria-hidden="true">&times;</span>
	</button>
</div>
@endif

	<div class="row card-description" id="detalles" @if($busqueda) style="display: none;" @endif>
		<div class="col-md-2 offset-md-1">
			<div class="alert alert-primary text-center" role="alert">
				<h5 class="alert-heading" style="font-size: 0.8rem;">Registrados</h5>
				<h5 class="alert-heading">{{ $numcontratos }}</h5>
			</div>
		</div>
		<div class="col-md-2">
			<div class="alert alert-success text-center" role="alert">
				<h5 class="alert-heading" style="font-size: 0.8rem;">Habilitados</h5>
				<h5 class="alert-heading">{{ $habilitados }}</h5>
			</div>
		</div>
		<div class="col-md-2">
			<div class="alert alert-danger text-center" role="alert">
				<h5 class="alert-heading" style="font-size: 0.8rem;">Deshabilitados</h5>
				<h5 class="alert-heading">{{ $deshabilitados }}</h5>
			</div>
		</div>
		<div class="col-md-2">
			<div class="alert alert-dark text-center" role="alert">
				<h5 class="alert-heading" style="font-size: 0.8rem;">Corte del 15</h5>
				<h5 class="alert-heading">{{ $corte15 }}</h5>
			</div>
		</div>
		<div class="col-md-2">
			<div class="alert alert-dark text-center" role="alert">
				<h5 class="alert-heading" style="font-size: 0.8rem;">Corte del 30</h5>
				<h5 class="alert-heading">{{ $corte30 }}</h5>
			</div>
		</div>
	</div>

	<div class="row card-description">
		<div class="col-md-12">
			<form id="form-table-facturas">
				<input type="hidden" name="orderby"id="order_by"  value="1">
				<input type="hidden" name="order" id="order" value="0">
				<input type="hidden" id="form" value="form-table-facturas">
				<div id="filtro_tabla" @if(!$busqueda) style="display: none;" @endif>
					<table class="table table-striped table-hover filtro thresp bg-white">
						<tr class="form-group">
							<th><input type="text" class="form-control" name="name_1" id="name_1" placeholder="Número Contrato" value="{{$request->name_1}}"></th>
							<th><input type="text" class="form-control" name="name_2" id="name_2" placeholder="Nombre Cliente"  value="{{$request->name_2}}"></th>
							<th><input type="text" class="form-control" name="name_3" id="name_3" placeholder="Cédula Cliente" value="{{$request->name_3}}"></th>
						</tr>
						<tr class="form-group">
							<th>
								<select name="name_4" id="name_4" class="form-control selectpicker" title="Plan" data-live-search="true" data-size="5">
									@foreach($planes as $plan)
                                        <option value="{{$plan->id}}" @if($request->name_4 == $plan->id) selected="" @endif>{{$plan->name}}</option>
									@endforeach
				  				</select>
				  			</th>
				  			<th>
								<select name="name_5" id="name_5" class="form-control selectpicker" title="Fecha Corte">
								    @if($request->name_5)
									    <option value="15" @if($request->name_5 == 15) selected="" @endif >Día 15</option>
									    <option value="30" @if($request->name_5 == 30) selected="" @endif >Día 30</option>
									@else
									    <option value="15" >Día 15</option>
									    <option value="30" >Día 30</option>
									@endif
				  				</select>
				  			</th>
							<th>
								<select name="name_6" id="name_6" class="form-control selectpicker" title="Estado">
									@if($request->name_6)
										<option value="enabled" @if($request->name_6 == "enabled") selected="" @endif >Habilitado</option>
										<option value="disabled" @if($request->name_6 == "disabled") selected="" @endif >Deshabilitado</option>
									@else
										<option value="enabled" >Habilitado</option>
										<option value="disabled" >Deshabilitado</option>
									@endif
				  				</select>
				  			</th>
						</tr>
					</table>
					<center><button class="my-3 btn btn-outline-primary btn-sm">Filtrar</button><a href="#" class="ml-1 my-3 btn btn-outline-warning btn-sm" onclick="resetForm();">Limpiar</a>
					@if(!$busqueda)
						<button type="button" class="my-3 btn btn-outline-danger btn-sm" onclick="hidediv('filtro_tabla'); showdiv('detalles'); showdiv('boto_filtrar'); vaciar_fitro();">Cerrar</button>
					@else
						<a href="{{route('contratos.index')}}" class="my-3 btn btn-outline-danger btn-sm" >Cerrar</a>
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
						<th>Nro. <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==1?'':'no_order'}}" campo="1" order="@if($request->orderby==1){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==1){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
						<th>Cliente <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==2?'':'no_order'}}" campo="2" order="@if($request->orderby==2){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==2){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
						<th>Cédula <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==3?'':'no_order'}}" campo="3" order="@if($request->orderby==3){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==3){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
						<th>Plan <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==4?'':'no_order'}}" campo="4" order="@if($request->orderby==4){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==4){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
						<th>Fecha Corte <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==5?'':'no_order'}}" campo="5" order="@if($request->orderby==5){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==5){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
						<th class="text-center">Estado <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==6?'':'no_order'}}" campo="6" order="@if($request->orderby==6){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==6){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
						<th class="text-center">Acciones</th>
		            </tr>
				</thead>
				<tbody>
					@foreach($contratos as $contrato)
						<tr>
							<td><a href="{{ route('contratos.show',$contrato->public_id )}}"  title="Ver">{{ $contrato->public_id }}</a></td>
							<td>{{ $contrato->nombre }}</td>
							<td>{{ $contrato->nit }}</td>
							<td>{{ $contrato->plan }}</td>
							<td>@if($contrato->fecha_corte) {{ $contrato->fecha_corte }} de cada mes @else No Asignada @endif</td>
							<td>
								@if($contrato->status() == 'Habilitado')
								   <span class="text-success font-weight-bold">{{ $contrato->status() }}</span>
								@else
							       <span class="text-danger font-weight-bold">{{ $contrato->status() }}</span>
							    @endif
							</td>
							<td>
								<a href="{{ route('contratos.show',$contrato->public_id )}}"  class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></i></a>
								<?php if(isset($_SESSION['permisos']['406'])){ ?>
								<a href="{{ route('contratos.edit',$contrato->public_id )}}"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></i></a>
								<?php } ?>
								<?php if(isset($_SESSION['permisos']['407'])){ ?>
								    <form action="{{ route('contratos.state',$contrato->public_id) }}" method="post" class="delete_form" style="margin:0;display: inline-block;" id="cambiar-state{{$contrato->public_id}}">
								    	{{ csrf_field() }}
									</form>
									<button @if($contrato->state == 'enabled') class="btn btn-outline-danger btn-icons" title="Desabilitar" @else  class="btn btn-outline-success btn-icons" title="Habilitar" @endif type="submit" onclick="confirmar('cambiar-state{{$contrato->public_id}}', '¿Estas seguro que deseas cambiar el estatus del contrato?', '');"><i class="fas fa-file-signature"></i></button>
								<?php } ?>
							</td>
						</tr>
					@endforeach
				</tbody>
			</table>

			<div class="text-right">
				{!!$contratos->render()!!}
		    </div>
		</div>
	</div>

	<script>
	    function resetForm(){
	    	$("#name_1,#name_2,#name_3,#name_4,#name_5,#name_6").val('').selectpicker('refresh');
	    }
    </script>


@endsection