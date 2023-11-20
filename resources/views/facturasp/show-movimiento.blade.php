@extends('layouts.app')

@section('boton')

@endsection   

@section('content')
    <style>
        .paper:before {
            top: 0px;
            right: 0px;
            border-color: #f9fafd #f9f9f9 #eaedf7 #eaedf7;
        }

    .desgloce.mov tbody > tr > td {
    padding: 1.5% 3px !important;
    border: 1px solid #ccc;
    }
    </style>
    
    @if(auth()->user()->modo_lectura())
        <div class="alert alert-warning text-left" role="alert">
            <h4 class="alert-heading text-uppercase">Integra Colombia: Suscripción Vencida</h4>
           <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
        </div>
    @else
        @if(auth()->user()->rol <> 8)
        <div class="table-responsive">
			<table class="table table-striped table-bordered table-sm info">
				<tbody> 
					<tr>
						<th width="20%">Número</th>
						<td>{{$factura->codigo}}</td>
					</tr>
					<tr>
						<th>Proveedor</th> 
						<td><a href="{{route('contactos.show',$factura->proveedor()->id)}}" target="_blanck">{{$factura->proveedor()->nombre}} {{ $factura->proveedor()->apellidos() }}</a></td>
					</tr>
					<tr>
						<th>Bodega</th>
						<td>{{$factura->bodega()}}</td>
					</tr>
					<tr>
						<th>Fecha</th>
						<td>{{date('d-m-Y', strtotime($factura->fecha_factura))}}</td>
					</tr>
					<tr>
						<th>Vencimiento</th>
						<td>{{date('d-m-Y', strtotime($factura->vencimiento_factura))}}</td>
					</tr>
          <tr>
						<th>Observaciones</th>
						<td>{{$factura->observaciones_factura}}</td>
					</tr>
          <tr>
						<th>Total</th>
						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->total()->total)}}</td>
					</tr>
          <tr>
            <th>Por pagar</th>
            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->porpagar())}}</td>
          </tr>
          <tr>
            <td colspan="2"></td>
          </tr>
          <tr>
            <th>Valor Retenido</th>
            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->total()->totalreten)}}</td>
          </tr>
          <tr>
            <th>Valor pagado</th>
            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->pagado())}}</td>
          </tr>
				</tbody>
			</table>
		    </div>
        @endif
    @endif

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

    <div class="mx-3">
    
        <div class="alert alert-warning nopadding onlymovil" style="text-align: center;">
			<button type="button" class="close" data-dismiss="alert">×</button>
			<strong><small><i class="fas fa-angle-double-left"></i> Deslice <i class="fas fa-angle-double-right"></i></small></strong>
		</div>
		
        <!-- Desgloce -->
        <div class="row" style="padding: 2% 6%;">
            <div class="col-md-12 fact-table">
                <table class="table table-striped table-sm desgloce mov"  width="100%">
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
                <td style="border:none;"></td>
                <td>{{isset($mov) ? $mov->totalDebito()->total : ''}}</td>
                <td>{{isset($mov) ? $mov->totalCredito()->total : ''}}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection
