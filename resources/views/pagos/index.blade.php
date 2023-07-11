@extends('layouts.app')
@section('boton')
	@if(auth()->user()->modo_lectura())
        <div class="alert alert-warning text-left" role="alert">
            <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
           <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
        </div>
    @else
	<button type="button" @if($busqueda) style="display: none;" @endif class="btn btn-info btn-sm " id="boto_filtrar" onclick="showdiv('filtro_tabla'); hidediv('boto_filtrar');"><i class="fas fa-search"></i> Filtrar</button>
	<a href="{{route('pagos.create')}}" class="btn btn-primary btn-sm" ><i class="fas fa-plus"></i> Nuevo Pago</a>
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
	<div class="row card-description">
		<div class="col-md-12 mb-5 table-responsive">
		    <form action="{{route('pagos.index')}}">
                <div id="filtro_tabla" @if(!$busqueda) style="display: none;" @endif class="mb-5">
                    <table class="table table-striped table-hover filtro thresp">
                        <tr class="form-group">
                            <th>
                                <input  type="text" class="form-control form-control-sm mb-1" name="search_code" placeholder="Número" value="{{$request->search_code}}">
                            </th>
                            <th>
                                <input type="text" class="form-control form-control-sm" name="search_client" placeholder="Cliente"  value="{{$request->search_client}}">
                            </th>
                            <th>
                                <input type="text" class="form-control-sm datepicker mb-1" name="search_date" placeholder="Fecha" value="{{$request->search_date}}">
                            </th>

                            <th width="10px"><select name="search_status[]" class="form-control-sm selectpicker mb-1" title="Estado"  data-width="100px" multiple>
                                    @if(is_array($request->search_status))
                                        <option value="1" @if(in_array("1", $request->search_status)) selected="" @endif >Consolidado</option>
                                        <option value="2" @if(in_array("2", $request->search_status)) selected="" @endif >Anulada</option>
                                    @else
                                        <option value="1" >Abierta</option>
                                        <option value="2" >Anulada</option>
                                    @endif
                                </select>
                            </th>
                            <th></th>
                        </tr>
                    </table>
                    <button class="btn btn-info btn-sm float-right"> <i class="fas fa-search"></i>Filtrar</button>
                    @if(!$busqueda)
                        <button type="button" class="btn btn-secondary btn-sm float-right mr-1"  onclick="hidediv('filtro_tabla'); showdiv('boto_filtrar'); vaciar_fitro();">
                            <i class="mdi mdi-close"></i>Cerrar
                        </button>
                    @else
                        <a href="{{route('pagos.index')}}" class="btn btn-secondary btn-sm mr-1 float-right" ><i class="mdi mdi-close"></i>Cerrar</a>
                    @endif
                </div>
            </form>
            
            <table class="table table-striped table-hover nowrap" id="table-facturas">
                <thead class="thead-dark">
                    <tr>
                        <th>Número</th>
                        <th>Cliente</th>
                        <th>Detalle</th>
                        <th>Fecha</th>
                        <th>Cuenta</th>
                        <th>Estatus</th>
                        <th>Monto</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
    				@foreach($gastos as $gasto)
    					<tr @if($gasto->id==Session::get('gasto_id')) class="active_table" @endif>
    						<td><a href="{{route('pagos.show',$gasto->id)}}">{{$gasto->nro}}</a> </td>
    						<td><div class="elipsis-short" style="width:135px;">@if($gasto->beneficiario()) <a href="{{route('contactos.show',$gasto->beneficiario()->id)}}" title="{{$gasto->beneficiario()->nombre}}" target="_blank">{{$gasto->beneficiario()->nombre}}</a>@else {{Auth::user()->empresa()->nombre}} @endif</div></td>
    						<td>{{$gasto->detalle()}} </td>
    						<td>{{date('d-m-Y', strtotime($gasto->fecha))}}</td>
    						<td>{{$gasto->cuenta()->nombre}} </td>
    						<td class="text-{{$gasto->estatus(true)}}">{{$gasto->estatus()}} </td>
    						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($gasto->pago())}} </td>
    						<td>
    							<a  href="{{route('pagos.show',$gasto->id)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>
    							@if(Auth::user()->modo_lectura())
    
    							@else
    								<a   href="{{route('pagos.imprimir.nombre',['id' => $gasto->id, 'name'=> 'Pago No. '.$gasto->nro.'.pdf'])}}" target="_black" class="btn btn-outline-primary btn-icons" title="Imprimir"><i class="fas fa-print"></i></a>
    								@if($gasto->tipo!=3)
    									@if($gasto->tipo!=4)
    										<a href="{{route('pagos.edit',$gasto->id)}}"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
    										<form action="{{ route('pagos.anular',$gasto->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="anular-gasto{{$gasto->id}}">
    										{{ csrf_field() }}
    										</form>
    										@if($gasto->estatus==1)
    										<button class="btn btn-outline-danger  btn-icons negative_paging" type="submit" title="Anular" onclick="confirmar('anular-gasto{{$gasto->id}}', '¿Está seguro de que desea anular el gasto?', ' ');"><i class="fas fa-minus"></i></button>
    										@else
    										<button class="btn btn-outline-success  btn-icons negative_paging" type="submit" title="Abrir" onclick="confirmar('anular-gasto{{$gasto->id}}', '¿Está seguro de que desea abrir el gasto?', ' ');"><i class="fas fa-unlock-alt"></i></button>
    									@endif
    								@endif
    							@endif
    							
    
    							<form action="{{ route('pagos.destroy',$gasto->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-gasto{{$gasto->id}}">
        						{{ csrf_field() }}
    							<input name="_method" type="hidden" value="DELETE">
    							</form>
    							<button class="btn btn-outline-danger  btn-icons negative_paging" type="submit" title="Eliminar" onclick="confirmar('eliminar-gasto{{$gasto->id}}', '¿Estas seguro que deseas eliminar el gasto?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
    							@endif 
    
    						</td>  
    					</tr> 
    				@endforeach			
    			</tbody>
    		</table>
    		{{$gastos->render()}}
    	</div>
    </div>
@endsection
