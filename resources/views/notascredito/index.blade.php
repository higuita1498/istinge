@extends('layouts.app')
@section('boton')
	@if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
	        <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
	    </div>
	@else
	<a href="{{route('notascredito.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nueva Nota de Crédito</a>
	@endif
@endsection
@section('content')
	@if(Session::has('success'))
		<div class="alert alert-success" style="margin-left: 2%;margin-right: 2%;">
			{{Session::get('success')}}
		</div>
	@endif
	
	@if(Session::has('message_success'))
		<div class="alert alert-success" style="margin-left: 2%;margin-right: 2%;">
			{{Session::get('message_success')}}
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
	<div class="row card-description"> 
		<div class="col-md-12">
			<form id="form-table-notascredito">
				<input type="hidden" name="orderby"id="order_by"  value="1">
				<input type="hidden" name="order" id="order" value="0">
				<input type="hidden" id="form" value="form-table-notascredito">
				<div id="filtro_tabla" @if(!$busqueda) style="display: none;" @endif>
					<table class="table table-striped table-hover filtro">				
						<tr class="form-group">
							<th><input type="text" class="form-control form-control-sm" name="name_1" placeholder="Código" value="{{$request->name_1}}"></th>
							<th><input type="text" class="form-control form-control-sm" name="name_2" placeholder="Cliente"  value="{{$request->name_2}}"></th>
							<th></th>
							<th class="calendar_small"><input type="text" class="form-control form-control-sm datepicker" name="name_3" placeholder="Creación " value="{{$request->name_3}}"></th>
							<th class="monetario">
								<select name="name_4_simb">
									<option value="=" {{$request->name_4_simb=='='?'selected':''}}>=</option>
									<option value=">" {{$request->name_4_simb=='>'?'selected':''}}>></option>
									<option value="<" {{$request->name_4_simb=='<'?'selected':''}}><</option>
								</select>
								<input type="text" class="form-control form-control-sm" name="name_4" placeholder="Total" value="{{$request->name_4}}">
							</th>	
						</tr>
					</table>
					<button class="btn btn-link no-padding">Filtrar</button>
					@if(!$busqueda) 
						<button type="button" class="btn btn-link no-padding"  onclick="hidediv('filtro_tabla'); showdiv('boto_filtrar'); vaciar_fitro();">Cerrar</button>

					@else
						<a href="{{route('cotizaciones.index')}}" class="btn btn-link no-padding" >Cerrar</a> 
					@endif				
				</div>
				<div class="row">
					<div class="col-md-12">
						<button type="button" @if($busqueda) style="display: none;" @endif class="btn btn-link float-right" id="boto_filtrar" onclick="showdiv('filtro_tabla'); hidediv('boto_filtrar');"><i class="fas fa-search"></i> Filtrar</button>
					</div>
				</div>
			</form>

			<table class="table table-striped table-hover" id="table-cotizacion">
			<thead class="thead-dark">
				<tr>
	              <th>Código <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==1?'':'no_order'}}" campo="1" order="@if($request->orderby==1){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==1){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Cliente <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==2?'':'no_order'}}" campo="2" order="@if($request->orderby==2){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==2){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Creación <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==3?'':'no_order'}}" campo="3" order="@if($request->orderby==3){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==3){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Total <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==4?'':'no_order'}}" campo="4" order="@if($request->orderby==4){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==4){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Saldo Restante <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==5?'':'no_order'}}" campo="5" order="@if($request->orderby==5){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==5){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	               <th>Estado <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==8?'':'no_order'}}" campo="8" order="@if($request->orderby==8){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==8){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Acciones</th>
	          </tr>                              
			</thead>
			<tbody>
				@foreach($facturas as $factura) 
					<tr @if($factura->id==Session::get('nota_id')) class="active_table" @endif>
						<td><a href="{{route('notascredito.show',$factura->nro)}}" >{{$factura->nro}}</a> </td>
						<td><a href="{{route('contactos.show',$factura->cliente()->id)}}" target="_blanck">{{$factura->cliente()->nombre}}</a></td>
						<td>{{date('d-m-Y', strtotime($factura->fecha))}}</td>
						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->total()->total)}} </td>
						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->por_aplicar())}} </td> 
						<td class="text">
						    @if(Auth::user()->empresa()->estado_dian == 1)
						    @if($factura->emitida == 1)
						    <strong style="color:green">Emitida</strong>
						    @else
						    <strong style="color:red">No Emitida</strong>
						    @endif
						@endif
						    </td>
						<td>
							@if(auth()->user()->modo_lectura())
							@else
							<a href="{{route('notascredito.show',$factura->nro)}}"  class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></i></a>
							@if(Auth::user()->modo_lectura())

							@else
							<a href="{{route('notascredito.imprimir.nombre',['id' => $factura->nro, 'name'=> 'Nota Credito No. '.$factura->nro.'.pdf'])}}" target="_blanck" class="btn btn-outline-primary btn-icons" title="Imprimir"><i class="fas fa-print"></i></a>
							@if(Auth::user()->empresa()->form_fe == 1 && $factura->emitida == 0 && Auth::user()->empresa()->estado_dian == 1 && Auth::user()->empresa()->technicalkey != null)
							<a onclick="confirmSendDian('{{route('xml.notacredito',$factura->id)}}','{{$factura->nro}}')" href="#"  class="btn btn-outline-primary btn-icons"title="Emitir Nota crédito"><i class="fas fa-sitemap"></i></a>
							@endif
							@if($factura->emitida !=1)
							<a href="{{route('notascredito.edit',$factura->nro)}}"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
							@endif
							<form action="{{ route('notascredito.destroy',$factura->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-notascredito{{$factura->id}}">
    						{{ csrf_field() }}
							<input name="_method" type="hidden" value="DELETE">
							</form>
							@if($factura->emitida !=1)
							<button class="btn btn-outline-danger  btn-icons negative_paging" type="submit" title="Eliminar" onclick="confirmar('eliminar-notascredito{{$factura->id}}', '¿Estas seguro que deseas eliminar nota de crédito?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
							@endif
							@endif
							@endif
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
		<div class="text-right">
			{{$facturas->links()}}
		</div>
		</div>
	</div>
@endsection 


@section('scripts')
<script>
	$(document).ready(function () {
	
		$.ajax({
		url: 'notascredito/validatetime/emicion',
		headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
		method: 'POST',

		success: function(factura){

			if(factura.length > 0){

				var text = "";

					$.each(factura,function(index,value){	
						 text = text + `${value.nro} <br>`;
					})

					console.log(text);

				Swal.fire({
				type: 'warning',
				title: 'DIAN',
				html: `Tienes Notas Credito realizadas hace 24h sin emitir <br>` + text,
				})

			}
		}
		});
	
	})
</script>
@endsection