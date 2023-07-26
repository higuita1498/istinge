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
    @if($ingreso->tipo==1)
	     @if($ingreso->ingresofactura()->factura()->estatus == 0)
	          <a href="{{route('facturas.tirilla', ['id' => $ingreso->ingresofactura()->factura()->id, 'name' => "Factura No. ".$ingreso->ingresofactura()->factura()->id.".pdf"])}}" class="btn btn-outline-warning @if(Auth::user()->rol==47) btn-xl @else btn-xs @endif" title="Tirilla" target="_blank"><i class="fas fa-print"></i>Imprimir tirilla</a>
	     @endif
	@endif
	@if($ingreso->tipo!=3)
		@if($ingreso->tipo!=4)
		    @if(isset($_SESSION['permisos']['48']))
			    <a href="{{route('ingresos.edit',$ingreso->nro)}}" class="btn btn-outline-primary btn-xs"><i class="fas fa-edit"></i>Editar</a>
			@endif
		@endif
		@if(isset($_SESSION['permisos']['49']))
		<form action="{{ route('ingresos.destroy',$ingreso->nro) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-ingreso{{$ingreso->id}}">
    						{{ csrf_field() }}
			<input name="_method" type="hidden" value="DELETE">
		</form>
		<button class="btn btn-outline-danger btn-xs" type="submit" title="Eliminar" onclick="confirmar('eliminar-ingreso{{$ingreso->id}}', '¿Estas seguro que deseas eliminar el ingreso?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i> Eliminar</button>
		@endif
	@endif
    @if($ingreso->tipo!=3 && $ingreso->tipo!=4)
	    @if(isset($_SESSION['permisos']['49']))
		<form action="{{ route('ingresos.anular',$ingreso->nro) }}" method="post" class="delete_form" style="display: none;" id="anular-ingreso{{$ingreso->id}}">
			{{ csrf_field() }}
		</form>
		@if($ingreso->estatus==1)
		<button class="btn btn-outline-info btn-xs"  type="button" title="Anular" onclick="confirmar('anular-ingreso{{$ingreso->id}}', '¿Está seguro de que desea anular el ingreso?', ' ');"><i class="fas fa-minus"></i>Anular</button>
		@else
		<button  class="btn btn-outline-info btn-xs" type="button" title="Abrir" onclick="confirmar('anular-ingreso{{$ingreso->id}}', '¿Está seguro de que desea abrir el ingreso?', ' ');">
			<i class="fas fa-unlock-alt"> Convertir a Abierta</i>
		</button>
		@endif
		@endif
	@endif
@endif
@endsection

@section('content')
	<div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
				<button type="button" class="close" data-dismiss="alert">×</button>
				<strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
			</div>

<div class="row card-description">
	<div class="col-md-12">

	  @if(Session::has('success') || Session::has('error'))
	    @if(Session::has('success'))
	    <div class="alert alert-success alert-view-show">
	      {{Session::get('success')}}
	    </div>
	    @endif

	    @if(Session::has('error'))
	    <div class="alert alert-danger alert-view-show">
	      {{Session::get('error')}}
	    </div>
	    @endif
	    <script type="text/javascript">
	      setTimeout(function(){ 
	          $('.alert').hide();
	          $('.active_table').attr('class', ' ');
	      }, 5000);
	    </script>


	  @endif

		<div class="table-responsive">
			<table class="table table-striped table-bordered table-sm info">
				<tbody>
					<tr>
						<th width="15%">Recibo de Caja</th>
						<td>{{$ingreso->nro}}</td>
					</tr>
					<tr>
						<th>Estado</th>
						<td>{{$ingreso->estatus()}}</td>
					</tr>
					<tr>
						<th>Beneficiario</th>
						<td>@if($ingreso->cliente()) <a href="{{route('contactos.show',$ingreso->cliente()->id)}}" target="_blank">{{$ingreso->cliente()->nombre}} {{$ingreso->cliente()->apellidos()}}</a >@else {{auth()->user()->empresa()->nombre}} @endif</td>
					</tr>
					<tr>
						<th>Fecha</th>
						<td>{{date('d-m-Y', strtotime($ingreso->fecha))}}</td> 
					</tr>
					<tr>
						<th>Cuenta</th>
						<td><a href="{{route('bancos.show',$ingreso->cuenta()->nro)}}" target="_blank">{{$ingreso->cuenta()->nombre}}</a></td>
					</tr>
					<tr>
						<th>Observaciones</th>
						<td>{{$ingreso->observaciones}}</td>
					</tr>
					<tr>
						<th>Notas</th>
						<td>{{$ingreso->notas}}</td>
					</tr>
					<tr>
						<th>Método de pago</th>
						<td>{{$ingreso->metodo_pago()}}</td>
					</tr>
					<tr>
						<th>Total</th>
						<td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($ingreso->pago())}}</td>
					</tr>
					@if($ingreso->created_by)
					<tr>
						<th><strong>Realizado por</strong></th>
						<td>{{$ingreso->created_by()->nombres}}</td>
					</tr>
					@endif
					@if($ingreso->updated_by)
					<tr>
						<th><strong>Actualizado por</strong></th>
						<td>{{$ingreso->updated_by()->nombres}}</td>
					</tr>
					@endif
				</tbody>				
			</table>
		</div>
	</div>
</div>

<div class="row card-description">
    <div class="col-md-12">
        <h4>Detalle de movimiento</h4>
		<div class="table-responsive">
			<table class="table table-striped table-bordered table-sm pagos">
				<thead>
					<tr>
						<th>Nro</th>
						<th width="20%">Cuenta contable</th>
						<th width="20%">tecrero</th>
						<th width="20%">Detalle</th>
						<th width="20%">Descripción</th>
						<th width="10%">Débito</th>
						<th width="10%">Crédito</th>
					</tr>
				</thead>
				<tbody>
					@php $i = 1 @endphp
				  @foreach($movimientos as $mov)
					  <tr>
						  <td>{{$i}}</td>
						  <td>{{$mov->codigo_cuenta}} - {{$mov->cuenta()->nombre}}</td>
						  <td>{{$mov->cliente->nombre}}</td>
						  <td>{{$mov->asociadoA()}}</td>
						  <td>{{$mov->descripcion}}</td>
						  <td>{{$mov->debito}}</td>
						  <td>{{$mov->credito}}</td>
					  </tr>
				  @php $i++; @endphp
				  @endforeach
				  <tr>
					  <td style="border:none;"></td>
					  <td style="border:none;"></td>
					  <td style="border:none;"></td>
					  <td style="border:none;"></td>
					  <td style="border:none;">Total:</td>
					  <td>{{isset($mov) ? $mov->totalDebito()->total : ''}}</td>
					  <td>{{isset($mov) ? $mov->totalCredito()->total : ''}}</td>
				  </tr>
				</tbody>
							
			</table>
		</div>
	</div> 
</div>	
@endsection