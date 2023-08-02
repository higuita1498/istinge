@extends('layouts.app')
@section('boton')
	@if(Auth::user()->modo_lectura())
		<div class="alert alert-warning alert-dismissible fade show" role="alert">
			<a>Esta en Modo Lectura si desea seguir disfrutando de Nuestros Servicios Cancelar Alguno de Nuestros Planes <a class="text-black" href="{{route('PlanesPagina.index')}}"> <b>Click Aqui.</b></a></a>
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
	@else
	    @if(isset($_SESSION['permisos']['47']))
		    <a href="{{route('ingresos.create')}}" class="btn btn-primary @if(Auth::user()->rol==47) btn-xl @else btn-sm @endif" ><i class="fas fa-plus"></i> Nuevo Ingreso</a>
		@endif
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
			}, 7000);
		</script>
	@endif
	
	@if(Session::has('danger'))
        <div class="alert alert-danger" >
            {{Session::get('danger')}}
        </div>
    @endif
    
    @if(Session::has('alerta'))
		<script type="text/javascript">
			setTimeout(function(){
			    Swal.fire({
                    position: 'top-center',
                    type: 'error',
                    title: 'EL PAGO NO HA SIDO REGISTRADO, INTENTELO NUEVAMENTE',
                    showConfirmButton: false,
                    timer: 30000
                });
			}, 500);
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
			}, 7000);
		</script>
	@endif	
		
		<div class="row card-description">
			<div class="col-md-12">
				<form id="form-table-ingresos">
					<input type="hidden" name="orderby"id="order_by"  value="1">
					<input type="hidden" name="order" id="order" value="0">
					<input type="hidden" id="form" value="form-table-ingresos">
					<div id="filtro_tabla" @if(!$busqueda) style="display: none;" @endif>
						<table class="table table-striped table-hover filtro">				
							<tr class="form-group">
								<th><input type="text" class="form-control @if(Auth::user()->rol==47) @else form-control-sm @endif" name="name_1" placeholder="Número" value="{{$request->name_1}}"></th>
								<th><input type="text" class="form-control @if(Auth::user()->rol==47) @else form-control-sm @endif" name="name_2" placeholder="Cliente"  value="{{$request->name_2}}"></th>
								<th></th>
								<th class="calendar_small"><input type="text" class="form-control @if(Auth::user()->rol==47) @else form-control-sm @endif datepicker" name="name_3" placeholder="Fecha" value="{{$request->name_3}}"></th>
								<th>
									<select name="name_4" class="@if(Auth::user()->rol==47) @else form-control-sm @endif selectpicker" title="Cuenta"  data-width="150px">	  
										@php $tipos_cuentas=\App\Banco::tipos();@endphp
							              @foreach($tipos_cuentas as $tipo_cuenta)
							                <optgroup label="{{$tipo_cuenta['nombre']}}">
							                  @foreach($bancos as $cuenta)
							                    @if($cuenta->tipo_cta==$tipo_cuenta['nro'])
							                      <option value="{{$cuenta->id}}" {{$request->name_4==$cuenta->id?'selected':''}}>{{$cuenta->nombre}}</option>
							                    @endif
							                  @endforeach
							                </optgroup>
							             @endforeach   
				  					</select>
								</th>
								<th>
								    <select name="name_5" class="form-control-sm selectpicker" title="Método de Pago" data-width="150px">
								        @foreach($metodos_pago as $metodo)
								        <option value="{{$metodo->id}}" {{$request->name_5==$metodo->id?'selected':''}}>{{$metodo->metodo}}</option>
								        @endforeach
								    </select>
								</th>
							</tr>
						</table>
						<center>
						    <button class="my-3 btn btn-outline-primary btn-sm">Filtrar</button>
						    @if(!$busqueda) 
							    <button type="button" class="my-3 btn btn-outline-danger btn-sm"  onclick="hidediv('filtro_tabla'); showdiv('boto_filtrar'); vaciar_fitro();">Cerrar</button>
						    @else
							    <a href="{{route('ingresos.index')}}" class="my-3 btn btn-outline-danger btn-sm" >Cerrar</a>
						    @endif
						</center>
					</div>
					<div class="row">
						<div class="col-md-12">
							<center><button type="button" @if($busqueda) style="display: none;" @endif class="btn btn-outline-primary float-right ml-2 btn-sm" id="boto_filtrar" onclick="showdiv('filtro_tabla'); hidediv('boto_filtrar');"><i class="fas fa-search"></i> Filtrar</button></center>
						</div>
					</div>
				</form>
				<table class="table table-striped table-hover" id="table-ingresos">
					<thead class="thead-dark"> 
						<tr>
			              <th>Número <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==1?'':'no_order'}}" campo="1" order="@if($request->orderby==1){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==1){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
			              <th>Cliente <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==2?'':'no_order'}}" campo="2" order="@if($request->orderby==2){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==2){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
			              <th>Detalle <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==3?'':'no_order'}}" campo="3" order="@if($request->orderby==3){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==3){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
			              <th>Fecha <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==4?'':'no_order'}}" campo="4" order="@if($request->orderby==4){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==4){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
			              <th>Cuenta <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==5?'':'no_order'}}" campo="5" order="@if($request->orderby==5){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==5){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
			              {{-- <th>Estado <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==6?'':'no_order'}}" campo="6" order="@if($request->orderby==6){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==6){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th> --}}
			              <th>Monto <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==7?'':'no_order'}}" campo="7" order="@if($request->orderby==7){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==7){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
			              <th>Acciones</th> 
			          </tr>
					</thead>
					<tbody>
        				@foreach($ingresos as $ingreso)
        					<tr @if($ingreso->id==Session::get('ingreso_id')) class="active_table" @endif>
        						<td><a href="{{route('ingresos.show',$ingreso->id)}}">{{$ingreso->nro}}</a> </td>
        						<td><div class="elipsis-short">@if($ingreso->cliente())<a href="{{route('contactos.show',$ingreso->cliente()->id)}}" target="_blanck">{{$ingreso->cliente()->nombre}}@endif</a></div></td>
        						<td>{{$ingreso->detalle()}} </td>
        						<td>{{date('d-m-Y', strtotime($ingreso->fecha))}}</td>
        						<td>{{$ingreso->cuenta()->nombre}} </td>
        						{{-- <td class="text-{{$ingreso->estatus(true)}}">{{$ingreso->estatus()}} </td> --}}
        						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($ingreso->pago())}}</td>
        						<td>
        						    <a href="{{route('ingresos.show',$ingreso->id)}}"   class="btn btn-outline-info @if(Auth::user()->rol==47) btn-xl @else btn-icons @endif" title="Ver"><i class="far fa-eye"></i></i></a>
        						    @if(Auth::user()->modo_lectura())
        							@else
        								@if($ingreso->tipo!=3 && $ingreso->tipo!=4)
        								    @if(isset($_SESSION['permisos']['48']))
        								        <a href="{{route('ingresos.edit',$ingreso->nro)}}"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
        								    @endif
        								@endif
        								@if($ingreso->tipo==2)
        								<a href="{{route('ingresos.imprimir.nombre',['id' => $ingreso->nro, 'name'=> 'Ingreso No. '.$ingreso->nro.'.pdf'])}}" target="_blanck"  class="btn btn-outline-primary @if(Auth::user()->rol==47) btn-xl @else btn-icons @endif" title="Imprimir"><i class="fas fa-print"></i></a>
        								@endif
        								{{-- @if($ingreso->tipo==1)
        								    @if($ingreso->tirilla()->estatus == 0)
        								    <a href="{{route('facturas.tirilla', ['id' => $ingreso->tirilla()->nro, 'name' => "Factura No. ".$ingreso->tirilla()->nro.".pdf"])}}" class="btn btn-outline-warning @if(Auth::user()->rol==47) btn-xl @else btn-icons @endif" title="Tirilla" target="_blank"><i class="fas fa-print"></i></a>
        								    @endif
	                                    @endif --}}
        								@if($ingreso->tipo!=3)
        									@if($ingreso->tipo!=4)
        									<form action="{{ route('ingresos.anular',$ingreso->nro) }}" method="post" class="delete_form" style="display: none;" id="anular-ingreso{{$ingreso->id}}">
        									{{ csrf_field() }}
        									</form>
        									@if(isset($_SESSION['permisos']['49']))
        									@if($ingreso->estatus==1)
        									<button class="btn btn-outline-danger  btn-icons" type="submit" title="Anular" onclick="confirmar('anular-ingreso{{$ingreso->id}}', '¿Está seguro de que desea anular el ingreso?', ' ');"><i class="fas fa-minus"></i></button>
        									@else
        									<button class="btn btn-outline-success  btn-icons" type="submit" title="Abrir" onclick="confirmar('anular-ingreso{{$ingreso->id}}', '¿Está seguro de que desea abrir el ingreso?', ' ');"><i class="fas fa-unlock-alt"></i></button>
        									@endif
        									@endif
        								@endif
        								<form action="{{ route('ingresos.destroy',$ingreso->nro) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-ingreso{{$ingreso->id}}">
        								{{ csrf_field() }}
        								<input name="_method" type="hidden" value="DELETE">
        								</form>
        								@if(isset($_SESSION['permisos']['49']))
        								<button class="btn btn-outline-danger  btn-icons negative_paging" type="submit" title="Eliminar" onclick="confirmar('eliminar-ingreso{{$ingreso->id}}', '¿Estas seguro que deseas eliminar el ingreso?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
        								@endif
        								@endif
        							@endif
        						</td> 
        					</tr> 
        				@endforeach
					</tbody>
				</table>
				<div class="text-right">
					{{$ingresos->links()}}
				</div>
			</div>
		</div>
@endsection