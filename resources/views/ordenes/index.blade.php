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
	<button type="button" @if($request->busqueda) style="display: none;" @endif class="btn btn-info btn-sm " id="boto_filtrar" onclick="showdiv('filtro_tabla'); hidediv('boto_filtrar');"><i class="fas fa-search"></i> Filtrar</button>
	<a href="{{route('ordenes.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nueva Orden de Compra</a>
	@endif
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
	<form id="form-table-ordenes">
		<input type="hidden" name="orderby"id="order_by"  value="1">
		<input type="hidden" name="order" id="order" value="0">
		<input type="hidden" id="form" value="form-table-ordenes">
	</form>
	<div class="row card-description">
		<div class="col-md-12">
		    <form action="{{route('ordenes.index')}}">
                <div id="filtro_tabla" @if(!$request->busqueda) style="display: none;" @endif class="mb-5">
                    <table class="table table-striped table-hover filtro thresp">
                        <tr class="form-group">
                            <th>
                                <input  type="text" class="form-control form-control-sm mb-1" name="search_code" placeholder="Codigo" value="{{$request->search_code}}">
                            </th>
                            <th>
                                <input type="text" class="form-control form-control-sm" name="search_client" placeholder="Proveedor"  value="{{$request->search_client}}">
                            </th>
                            <th>
                                <input type="text" class="form-control-sm mb-1 datepicker" name="search_date" placeholder="Fecha" value="{{$request->search_date}}">
                            </th>

                            <th width="10px"><select name="search_status[]" class="form-control-sm selectpicker mb-1" title="Estado"  data-width="100px" multiple>
                                    @if(is_array($request->search_status))
                                        <option value="1" @if(in_array("1", $request->search_status)) selected="" @endif >Consolidado</option>
                                        <option value="0" @if(in_array("0", $request->search_status)) selected="" @endif >Cerrada</option>
                                    @else
                                        <option value="1" >Abierta</option>
                                        <option value="0" >Cerrada</option>
                                    @endif
                                </select>
                            </th>
                            <th></th>
                        </tr>
                    </table>
                    <button class="btn btn-info btn-sm float-right"> <i class="fas fa-search"></i>Filtrar</button>
                    @if(!$request->busqueda)
                        <button type="button" class="btn btn-secondary btn-sm float-right mr-1"  onclick="hidediv('filtro_tabla'); showdiv('boto_filtrar'); vaciar_fitro();">
                            <i class="mdi mdi-close"></i>Cerrar</button>

                    @else
                        <a href="{{route('ordenes.index')}}" class="btn btn-secondary btn-sm mr-1 float-right" ><i class="mdi mdi-close"></i>Cerrar</a>
                    @endif
                </div>
            </form>
			<table class="table table-striped table-hover " id="notable-general">
			<thead class="thead-dark">
				<tr>
	              <th>Código <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==1?'':'no_order'}}" campo="1" order="@if($request->orderby==1){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==1){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Proveedor <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==2?'':'no_order'}}" campo="2" order="@if($request->orderby==2){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==2){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Fecha <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==3?'':'no_order'}}" campo="3" order="@if($request->orderby==3){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==3){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Fecha Entrega <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==4?'':'no_order'}}" campo="4" order="@if($request->orderby==4){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==4){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Estado <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==5?'':'no_order'}}" campo="5" order="@if($request->orderby==5){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==5){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	              <th>Total <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==6?'':'no_order'}}" campo="6" order="@if($request->orderby==6){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==6){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>

	              <th>Acciones</th>
	          </tr>                              
			</thead>
			<tbody > 
				@foreach($ordenes as $orden)
					<tr @if($orden->id==Session::get('codigo')) class="active" @endif>
						<td><a href="{{route('ordenes.show',$orden->orden_nro)}}" >{{$orden->orden_nro}}</a> </td>
						<td>
							<a href="{{route('contactos.show',$orden->proveedor()->id)}}" target="_blanck">{{$orden->proveedor()->nombre}}</a>
						</td> 
						<td>{{date('d-m-Y', strtotime($orden->fecha))}}</td>
						<td>{{date('d-m-Y', strtotime($orden->vencimiento))}}</td>
						<td class="text-{{$orden->estatus(true)}}">{{$orden->estatus()}}</td>
						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($orden->total()->total)}}</td>
						<td>
							<a  href="{{route('ordenes.show',$orden->orden_nro)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></i></a>
							@if(Auth::user()->modo_lectura())

							@else
							<a   href="{{route('ordenes.imprimir.nombre',['id' => $orden->orden_nro, 'name'=> 'Orden Compra No. '.$orden->orden_nro.'.pdf'])}}" target="_black" class="btn btn-outline-primary btn-icons" title="Imprimir"><i class="fas fa-print"></i></a>
								@if($orden->tipo ==2 && empty($orden->nro) && $orden->estatus>0)
									<a href="{{route('ordenes.edit', $orden->orden_nro)}}"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>

									<form action="{{ route('ordenes.anular',$orden->id) }}" method="post" class="delete_form" style="display: none;" id="anular-orden{{$orden->id}}">
									{{ csrf_field() }}
									</form>
									@if($orden->estatus==1)

									<button class="btn btn-outline-danger  btn-icons " type="submit" title="Anular" onclick="confirmar('anular-orden{{$orden->id}}', '¿Está seguro de que desea anular la orden de compra?', ' ');"><i class="fas fa-minus"></i></button>
									@else
									<button class="btn btn-outline-success  btn-icons" type="submit" title="Abrir" onclick="confirmar('anular-orden{{$orden->id}}', '¿Está seguro de que desea abrir  orden de compra?', ' ');"><i class="fas fa-unlock-alt"></i></button>
									@endif


									<form action="{{ route('ordenes.destroy',$orden->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-orden{{$orden->id}}">
									{{ csrf_field() }}
									<input name="_method" type="hidden" value="DELETE">
									</form>
									<button class="btn btn-outline-danger  btn-icons negative_paging" type="submit" title="Eliminar" onclick="confirmar('eliminar-orden{{$orden->id}}', '¿Estas seguro que deseas eliminar la orden de compra?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
								@endif
							@endif
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
		<div class="text-right">
			{{$ordenes->links()}}
		</div>
		</div>
	</div>
@endsection