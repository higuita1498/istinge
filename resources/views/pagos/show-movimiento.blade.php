@extends('layouts.app')

@section('boton')
@if(auth()->user()->modo_lectura())
<div class="alert alert-warning text-left" role="alert">
	<h4 class="alert-heading text-uppercase">Integra Colombia: Suscripción Vencida</h4>
	<p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
</div>
@else
@if($gasto->tipo!=4)
<a href="{{route('pagos.imprimir.nombre',['id' => $gasto->id, 'name'=> 'Pago No. '.$gasto->nro.'.pdf'])}}" class="btn btn-outline-primary btn-sm "title="Imprimir" target="_blank"><i class="fas fa-print"></i> Imprimir</a>
@endif
@if(isset($_SESSION['permisos']['254']))
@if($gasto->nro && $gasto->estatus>0 && $gasto->tipo!=3)
	<a href="{{route('pagos.edit',$gasto->id)}}" class="btn btn-outline-info btn-sm "title="Editar"><i class="fas fa-edit"></i> Editar</a>
@endif
@endif
@if($gasto->beneficiario())
	<a href="{{route('pagos.enviar',$gasto->id)}}" class="btn btn-outline-primary btn-sm "title="Enviar"><i class="far fa-envelope"></i> Enviar por Correo Al Cliente</a>
@endif
@if($gasto->tipo!=3)
	@if(isset($_SESSION['permisos']['255']))
	<form action="{{ route('pagos.destroy',$gasto->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-gasto{{$gasto->id}}">
	{{ csrf_field() }}
	<input name="_method" type="hidden" value="DELETE">
	</form>
	<button class="btn btn-outline-danger btn-sm" type="submit" title="Eliminar" onclick="confirmar('eliminar-gasto{{$gasto->id}}', '¿Estas seguro que deseas eliminar el gasto?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i> Eliminar</button>
	@endif
	@if(isset($_SESSION['permisos']['254']))
	<form action="{{ route('pagos.anular',$gasto->id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="anular-gasto{{$gasto->id}}">
	{{ csrf_field() }}
	</form>
	@if($gasto->estatus==1)
	<button class="btn btn-outline-danger btn-sm" type="submit" title="Anular" onclick="confirmar('anular-gasto{{$gasto->id}}', '¿Está seguro de que desea anular el gasto?', ' ');"><i class="fas fa-minus"></i> Anular</button>
	@else
	<button class="btn btn-outline-success btn-sm" type="submit" title="Abrir" onclick="confirmar('anular-gasto{{$gasto->id}}', '¿Está seguro de que desea abrir el gasto?', ' ');"><i class="fas fa-unlock-alt"></i> Abrir</button>
	@endif
	@endif
@endif
@endif

<div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
<button type="button" class="close" data-dismiss="alert">×</button>
<strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
</div>
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
						<td>{{$gasto->nro}}</td>
					</tr>
					<tr>
						<th>Estado</th>
						<td>{{$gasto->estatus()}}</td>
					</tr>
					<tr>
						<th>Beneficiario</th>
						<td>@if($gasto->beneficiario()) <a href="{{route('contactos.show',$gasto->beneficiario()->id)}}" target="_blank">{{$gasto->beneficiario()->nombre}} {{$gasto->beneficiario()->apellidos()}}</a >@else {{auth()->user()->empresa()->nombre}} @endif</td>
					</tr>
					<tr>
						<th>Fecha</th>
						<td>{{date('d-m-Y', strtotime($gasto->fecha))}}</td> 
					</tr>
					<tr>
						<th>Cuenta</th>
						<td><a href="{{route('bancos.show',$gasto->cuenta()->nro)}}" target="_blank">{{$gasto->cuenta()->nombre}}</a></td>
					</tr>
					<tr>
						<th>Observaciones</th>
						<td>{{$gasto->observaciones}}</td>
					</tr>
					<tr>
						<th>Notas</th>
						<td>{{$gasto->notas}}</td>
					</tr>
					<tr>
						<th>Método de pago</th>
						<td>{{$gasto->metodo_pago()}}</td>
					</tr>
					<tr>
						<th>Total</th>
						<td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($gasto->pago())}}</td>
					</tr>
					@if($gasto->created_by)
					<tr>
						<th><strong>Realizado por</strong></th>
						<td>{{$gasto->created_by()->nombres}}</td>
					</tr>
					@endif
					@if($gasto->updated_by)
					<tr>
						<th><strong>Actualizado por</strong></th>
						<td>{{$gasto->updated_by()->nombres}}</td>
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