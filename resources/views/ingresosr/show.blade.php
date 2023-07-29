@extends('layouts.app')

@section('boton')
@if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
	       <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
	    </div>
	@else
	<a href="{{route('ingresosr.imprimir.nombre',['id' => $ingreso->nro, 'name'=> 'IngresoR No. '.$ingreso->nro.'.pdf'])}}" target="_blank"class="btn btn-outline-primary btn-xs"><i class="fas fa-print"></i> Imprimir</a>
	<a href="{{route('ingresosr.edit',$ingreso->nro)}}" class="btn btn-outline-primary btn-xs"><i class="fas fa-edit"></i>Editar</a>
	<form action="{{ route('ingresosr.destroy',$ingreso->nro) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-ingreso{{$ingreso->id}}">
			{{ csrf_field() }}
	<input name="_method" type="hidden" value="DELETE">
	</form>
	<button class="btn btn-outline-primary btn-xs" type="submit" title="Eliminar" onclick="confirmar('eliminar-ingreso{{$ingreso->id}}', '¿Estas seguro que deseas eliminar el ingreso?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i> Eliminar</button>
	<a href="{{route('ingresosr.enviar',$ingreso->nro)}}" class="btn btn-outline-primary btn-xs"><i class="far fa-envelope"></i> Enviar por Correo</a>
	<form action="{{ route('ingresosr.anular',$ingreso->nro) }}" method="post" class="delete_form" style="display: none;" id="anular-ingreso{{$ingreso->id}}">
		{{ csrf_field() }}
	</form>
	@if($ingreso->estatus==1)
	<button class="btn btn-outline-primary btn-xs"  type="button" title="Anular" onclick="confirmar('anular-ingreso{{$ingreso->id}}', '¿Está seguro de que desea anular el ingreso?', ' ');"><i class="fas fa-minus"></i>Anular</button>
	@else
	<button  class="btn btn-outline-primary btn-xs" type="button" title="Abrir" onclick="confirmar('anular-ingreso{{$ingreso->id}}', '¿Está seguro de que desea abrir el ingreso?', ' ');">
		<i class="fas fa-unlock-alt"> Convertir a Abierta</i>
	</button>
	@endif
@endif
			<div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
			<button type="button" class="close" data-dismiss="alert">×</button>
			<strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
		</div>
@endsection	
@section('content')

@if(Session::has('success') || Session::has('error'))
@if(Session::has('success'))
<div class="alert alert-success">
  {{Session::get('success')}}
</div>
@endif

@if(Session::has('error'))
<div class="alert alert-danger">
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

<div class="row card-description">
	<div class="col-md-12">
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
						<td>@if($ingreso->cliente()) <a href="{{route('contactos.show',$ingreso->cliente()->id)}}" target="_blank">{{$ingreso->cliente()->nombre}} {{$ingreso->cliente()->apellidos()}}</a >@endif</td>
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
						<td>{{number_format($ingreso->pago(), 2)}}</td>
					</tr>
				</tbody>				
			</table>
		</div>
	</div>
</div>
	<div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
			<button type="button" class="close" data-dismiss="alert">×</button>
			<strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
		</div>
<div class="row card-description">
	<div class="col-md-12">
		<div class="table-responsive">
			<table class="table table-striped table-bordered table-sm pagos">
				<thead>
					<tr>
						<th>Número</th>
						<th>Fecha</th>
						<th>Vencimiento</th>
						<th>Total</th>
						<th>Pagado</th>
						<th>Por Pagar</th>
						<th>Monto</th>
						<th>Total</th>
					</tr>
				</thead>
				<tbody>
					@foreach($items as $item)
						<tr>
							<td><a href="{{route('remisiones.show', $item->remision)}}" target="_blank">{{$item->remision()->nro}}</a></td>
							<td>{{date('d-m-Y', strtotime($item->remision()->fecha))}}</td>
							<td>{{date('d-m-Y', strtotime($item->remision()->vencimiento))}}</td>
							<td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($item->remision()->total()->total)}}</td>
							<td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($item->pagado)}}</td>
							<td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear(($item->remision()->total()->total - $item->pago))}}</td>
							<td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($item->pago)}}</td>
							
							<td>
								{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($item->pago())}}
							</td>
						</tr>
					@endforeach
				</tbody>
							
			</table>
		</div>
	</div>
</div>


<div class="row card-description" >
        <div class="col-md-4 offset-md-8">
          <table style="text-align: right;  width: 100%;" id="totales">
            <tr>
              <td width="40%">Subtotal</td>
              <td>{{Auth::user()->empresa()->moneda}} <span id="subtotal_categoria"> {{App\Funcion::Parsear($ingreso->total()->subtotal)}}</span></td>
            </tr>
            @php $cont=0; @endphp
            @if($ingreso->total()->imp)
            @foreach($ingreso->total()->imp as $imp)
                @if(isset($imp->total))                
                  @php $cont+=1; @endphp
                  <tr id="imp{{$cont}}">
                    <td>{{$imp->nombre}} ({{$imp->porcentaje}}%)</td>
                    <td id="totalimp{{$cont}}">{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($imp->total)}}</td>
                  </tr>
                @endif
            @endforeach
            @endif
          </table>
          <table style="text-align: right; width: 100%;" id="totalesreten">
            <tbody>
              
              @php $cont=0; @endphp
              @if($ingreso->total()->reten)
              @foreach($ingreso->total()->reten as $reten)
                  @if(isset($reten->total))  
                     <tr id="retentotal{{$cont}}"><td width="40%" style="font-size: 0.8em;">{{$reten->nombre}} ({{$reten->porcentaje}}%)</td><td id="retentotalvalue{{$cont}}">-{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($reten->total)}} </td></tr>               
                    @php $cont+=1; @endphp
                  @endif
              @endforeach
              @endif
             

            </tbody>
          </table>
          <hr>
          <table style="text-align: right; font-size: 24px !important; width: 100%;">
            <tr>
              <td width="40%">TOTAL</td>
              <td>{{Auth::user()->empresa()->moneda}} <span id="total_categoria">{{App\Funcion::Parsear($ingreso->total()->total)}} </span></td>
            </tr>
          </table>
        </div>
    </div> 
@endsection