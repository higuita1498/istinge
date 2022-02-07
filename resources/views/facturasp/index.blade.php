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
		<a href="{{route('facturasp.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nueva Factura de Compra</a>
	@endif
@endsection
@section('content')
	@if(Session::has('success'))
		<div class="alert alert-success" style="margin-left: 2%;margin-right: 2%;">
			{{Session::get('success')}}
		</div>
	@endif
    <div class="col-md-12 table-responsive">
        <form id="form-table-facturas">
            <input type="hidden" name="orderby"id="order_by"  value="1">
            <input type="hidden" name="order" id="order" value="0">
            <input type="hidden" id="form" value="form-table-facturas">
            <div id="filtro_tabla" @if(!$busqueda) style="display: none;" @endif>
                <table class="table table-striped table-hover filtro thresp">
                    <tr class="form-group">
                        <th><input type="text" class="form-control form-control-sm" name="name_11" placeholder="Código"  value="{{$request->name_11}}"></th>
                        <th><input type="text" class="form-control form-control-sm" name="name_1" placeholder="Factura" value="{{$request->name_1}}"></th>
                        <th><input type="text" class="form-control form-control-sm" name="name_2" placeholder="Proveedor"  value="{{$request->name_2}}"></th>
                        <th class="calendar_small"><input type="text" class="form-control form-control-sm  datepicker" name="name_3" placeholder="Creación" value="{{$request->name_3}}"></th>
                        <th class="calendar_small"><input type="text" class="form-control form-control-sm datepickerinput" name="name_4" placeholder="Vencimiento" value="{{$request->name_4}}"></th>
                        <th>
                            <select class="form-control form-control-sm mb-1" name="name_5_simb" style="width: 70px">
                                <option value="=" {{$request->name_5_simb=='>'?'selected':''}}>=</option>
                                <option value=">" {{$request->name_5_simb=='<'?'selected':''}}>></option>
                                <option value="<" {{$request->name_5_simb=='='?'selected':''}}><</option>
                            </select>
                        </th>
                        <th>
                            <input type="text" class="form-control form-control-sm" name="name_5" placeholder="Total" value="{{$request->name_5}}">
                        </th>
                    </tr>
                </table>
                <button class="btn btn-link no-padding">Filtrar</button>
                @if(!$busqueda)
                    <button type="button" class="btn btn-link no-padding"  onclick="hidediv('filtro_tabla'); showdiv('boto_filtrar'); vaciar_fitro();">Cerrar</button>
                @else
                    <a href="{{route('facturasp.index')}}" class="btn btn-link no-padding" >Cerrar</a>
                @endif
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button type="button" @if($busqueda) style="display: none;" @endif class="btn btn-link float-right" id="boto_filtrar" onclick="showdiv('filtro_tabla'); hidediv('boto_filtrar');"><i class="fas fa-search"></i> Filtrar</button>
                </div>
            </div>
        </form>
    	<form id="form-table-facturasp">
    		<input type="hidden" name="orderby"id="order_by"  value="1">
    		<input type="hidden" name="order" id="order" value="0">
    		<input type="hidden" id="form" value="form-table-facturasp">
    	</form>
    	<div class="row card-description">
    		<div class="col-md-12">
    			<table class="table table-striped table-hover " id="notable-general">
        			<thead class="thead-dark">
        				<tr>
        				    <th>Nro <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==1?'':'no_order'}}" campo="1" order="@if($request->orderby==1){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==1){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
        				    <th>Factura <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==2?'':'no_order'}}" campo="2" order="@if($request->orderby==2){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==2){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
        				    <th>Proveedor <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==3?'':'no_order'}}" campo="3" order="@if($request->orderby==3){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==3){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
        				    <th>Creación <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==4?'':'no_order'}}" campo="4" order="@if($request->orderby==4){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==4){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
        				    <th>Vencimiento <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==5?'':'no_order'}}" campo="5" order="@if($request->orderby==5){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==5){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
        				    <th>Total <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==6?'':'no_order'}}" campo="6" order="@if($request->orderby==6){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==6){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
        				    <th>Por Pagar <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==7?'':'no_order'}}" campo="7" order="@if($request->orderby==7){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==7){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
        				    <th>Pagado <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==8?'':'no_order'}}" campo="8" order="@if($request->orderby==8){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==8){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
        				    <th>Acciones</th>
        				</tr>                              
        			</thead>
        			<tbody>
        				@foreach($facturas as $factura)
        					<tr @if($factura->id==Session::get('factura_id')) class="active" @endif>
        						<td><a href="{{route('facturasp.showid',$factura->id)}}" target="_blanck">{{$factura->nro}}</a></td>
        						<td><a href="{{route('facturasp.showid',$factura->id)}}" target="_blanck">{{$factura->codigo}}</a></td>
        						<td class="disp-show"><div class="elipsis-short"><a href="{{route('contactos.show',$factura->proveedor()->id)}}" target="_blanck">{{$factura->proveedor()->nombre}}</a></div></td>
        						<td>{{date('d-m-Y', strtotime($factura->fecha_factura))}}</td>
        						<td>{{date('d-m-Y', strtotime($factura->vencimiento_factura))}}</td>
        						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->total()->total)}}</td>
        					    <td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($factura->porpagar())}}</td>
        						<td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($factura->pagado())}}</td>
        						<td>
        							<a   href="{{route('facturasp.showid',$factura->id)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></i></a>
        							@if(Auth::user()->modo_lectura())
        
        							@else
        								@if($factura->tipo ==1 && $factura->estatus==1)
        									<a  href="{{route('pagos.create_id', ['cliente'=>$factura->proveedor, 'factura'=>$factura->id])}}" class="btn btn-outline-primary btn-icons" title="Agregar pago"><i class="fas fa-money-bill"></i></a>
        									
        									<a href="{{route('facturasp.edit', $factura->id)}}"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
        									<a href="{{route('facturasp.imprimir.nombre', ['id' => $factura->id, 'name'=> 'Factura Proveedor No. '.$factura->nro.'.pdf'])}}"  class="btn btn-outline-primary btn-icons" title="Imprimir"><i class="fas fa-print"></i></a>
        									<button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-factura{{$factura->id}}', '¿Estas seguro que deseas eliminar la factura de compra?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
        									<form action="{{ route('facturasp.destroy',$factura->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-factura{{$factura->id}}">
        										{{ csrf_field() }}
        										<input name="_method" type="hidden" value="DELETE">
        									</form>
        									@else
        									<a href="{{route('facturasp.imprimir.nombre', ['id' => $factura->id, 'name'=> 'Factura Proveedor No. '.$factura->nro.'.pdf'])}}"  class="btn btn-outline-primary btn-icons" title="Imprimir"><i class="fas fa-print"></i></a>
        								@endif
        							@endif
        						</td>
        					</tr>
        				@endforeach
        			</tbody>
        		</table>
        		<div class="text-right">
        			{!!$facturas->render()!!}
                    @if($facturas->lastPage() != 1)
                        @include('layouts.includes.goTo')
                    @endif
        		</div>
    		</div>
    	</div>
    </div>
@endsection
