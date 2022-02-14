@extends('layouts.app')

@section('boton') 
    <a href="{{route('pagosrecurrentes.edit',$gasto->nro)}}" class="btn btn-outline-primary btn-sm " title="Editar" target="_blank"><i class="fas fa-edit"></i> Editar</a>
    <a  href="{{route('pagosR.ingreso', $gasto->id)}}" class="btn btn-outline-primary btn-sm" title="Agregar pago"><i class="fas fa-money-bill"></i> Agregar pago</a>
@endsection

@section('content')
    <div class="row card-description">
    	<div class="col-md-6">
    		<div class="table-responsive">
    			<table class="table table-striped table-bordered table-sm info">
    				<tbody>
    					<tr>
    						<th colspan="2" class="text-center">Datos del Pago</th>
    					</tr>
    					<tr>
    						<th width="20%">Código</th>
    						<td>{{$gasto->nro}}</td>
    					</tr>
    					<tr>
    						<th>Cuenta</th>
    						<td>{{$gasto->cuenta()->nombre}}</td>
    					</tr>
    					<tr>
    						<th>Método de pago</th>
    						<td>{{$gasto->metodo_pago()}}</td>
    					</tr>
    					<tr>
    						<th>Monto</th>
    						<td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($gasto->total()->total)}}</td>
    					</tr>
    					<tr>
    						<th>Estado</th>
    						<td class="text-{{$gasto->estado('true')}}">{{ $gasto->estado() }}</td>
    					</tr>
    					<tr>
    						<th>Fecha de Pago</th>
    						<td>{{date('d-m-Y', strtotime($gasto->fecha))}}</td>
    					</tr>
    					<tr>
    						<th>Proxima emisión</th>
    						<td>{{date('d-m-Y', strtotime($gasto->proxima))}}</td>
    					</tr>
    					<tr>
    						<th>Frecuencia (meses)</th>
    						<td>{{$gasto->frecuencia}}</td>
    					</tr>
    					<tr>
    						<th>Observaciones</th>
    						<td>{{$gasto->observaciones}}</td>
    					</tr>
    				</tfoot>
    			</table>
    		</div>
    	</div>

    	@if($gasto->tipo())
    	<div class="col-md-6">
    		<div class="table-responsive">
    			<table class="table table-striped table-bordered table-sm info">
    				<tbody>
    					<tr>
    						<th colspan="2" class="text-center">Datos del Beneficiario (Tipo de Gasto)</th>
    					</tr>
    					<tr>
    						<th width="20%">Nombre</th>
    						<td><a href="{{route('tipos-gastos.show',$gasto->tipo()->id)}}" target="_blanck">{{$gasto->tipo()->nombre}}</a ></td>
    					</tr>
    					<tr>
    						<th>Descripción</th>
    						<td>{{$gasto->tipo()->descripcion}}</td>
    					</tr>
    				</tfoot>
    			</table>
    		</div>
    	</div>
    	@endif
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
    						<th>Cuenta</th>
    						<th>Valor</th>
    						<th>Impuesto</th>
    						<th>Cantidad</th>
    						<th>Observaciones</th>
    						<th>Total</th>
    					</tr>
    				</thead>
    				<tbody>
    					@foreach($items as $item)
						<tr>
							<td>{{$item->categoria()}}</td>
							<td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($item->valor)}}</td>
							<td>{{$item->impuesto()}}</td>
							<td>{{round($item->cant,0)}}</td>
							<td>{{$item->descripcion}}</td>
							<td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($item->valor)}}</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div> 
</div>	

<div class="row card-description">
        <div class="col-md-4 offset-md-8">
          <table style="text-align: right;  width: 100%;" id="totales">
            <tr>
              <td width="40%">Subtotal</td>
              <td>{{Auth::user()->empresa()->moneda}} <span id="subtotal_categoria"> {{App\Funcion::Parsear($gasto->total()->subtotal)}}</span></td>
            </tr>
            @php $cont=0; @endphp
            @if($gasto->total()->imp)
            @foreach($gasto->total()->imp as $imp)
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
              @if($gasto->total()->reten)
              @foreach($gasto->total()->reten as $reten)
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
              <td>{{Auth::user()->empresa()->moneda}} <span id="total_categoria">{{App\Funcion::Parsear($gasto->total()->total)}} </span></td>
            </tr>
          </table>
        </div>
    </div> 
@endsection
