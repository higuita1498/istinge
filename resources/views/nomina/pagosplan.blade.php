@extends('layouts.app')

@section('content')

<div class="section3">
	<center>
		<div class="enlamitad">
			<div class="barra-princi-pagos mx-4" style="width: 90%;">
				<h1>
					<i class="fas fa-shopping-cart"></i> !Bien Hecho!, {{Auth()->user()->email}}<br>has escogido el {{$tipo}}
				</h1>
				<input type="hidden" name="plan" id="plan" value="{{$plan}}">
                <input type="hidden" id="personalPlan" value="{{$personalizado}}">
                <input type="hidden" name="meses" id="p_meses" value="0">
                <input type="hidden" id="tipo" value="{{$tipo}}">
			</div>
			<div id="ocult-planfre" style="display: none;">
				<form method="POST" action="">
					@csrf
					<div class="sections-in-gral-payment">
						<div class="sct-1forpayment fact-table pb-5 pt-3">
							<table class="tableforpayment"  style="min-width: 517px;">
								<tr class="theadforpayment">
									<th>Frecuancia de cobro</th>
									<th>Período</th>
									<th>Precio</th>
								</tr>
								<tr class="trforpayment">
									<td>
										<p><input class="rd-left" type="radio" name="optradio" id="optradio1" value="{{$valor}}">Pago Mensual</p>
									</td>
									<td>
										<p>1 mes</p>
									</td>
									<td>
										<p>COP ${{\App\Funcion::Parsear($valor)}} <br> <small><strong></p>
									</td>
								</tr>
                                <tr class="trforpayment">
									<td>
										<p><input class="rd-left" type="radio" name="optradio" id="optradio2" value="@if($valor!=15000){{($valor*6)-($valor*6)*0.05}}@else{{($valor*6)}}@endif" >Pago Semestral</p>
									</td>
									<td>
										<p>6 meses @if($valor!=15000)<br> <small><strong>5% Descuento</strong></small>@endif</p>
									</td>
									<td>
										<p>@if($valor!=15000) COP ${{\App\Funcion::Parsear(($valor*6)-($valor*6)*0.05)}} <br> <small><strong>Ahorras Cop ${{\App\Funcion::Parsear(($valor*6)*0.05)}}</strong></small> @else COP ${{\App\Funcion::Parsear($valor*6)}}@endif</p>
									</td>
								</tr>
								<tr class="trforpayment">
									<td>
										<p><input class="rd-left" type="radio" name="optradio" id="optradio3" value="@if($valor!=15000){{($valor*12)-($valor*12)*0.10}}@else{{($valor*12)}}@endif">Pago Anual</p>
									</td>
									<td>
										<p>12 meses @if($valor!=15000)<br> <small><strong>10% Descuento</strong></small>@endif</p>
									</td>
									<td>
										<p>@if($valor!=15000) COP ${{\App\Funcion::Parsear(($valor*12)-($valor*12)*0.10)}} <br> <small><strong>Ahorras Cop ${{\App\Funcion::Parsear(($valor*12)*0.10)}}</strong></small> @else COP ${{\App\Funcion::Parsear($valor*12)}}@endif</p>
									</td>
								</tr>
							</table>
						</div>
						<div class="sct-2forpayment pb-5 pt-3">
							<div class="sct2-all">
								<div class="sct2-ttl">
									<h2>Total a Pagar</h2>
								</div>
								<div class="sct2-value">
									<h4 id="totalvalue"></h4>
								</div>
							</div>
							<div class="sct2-btn">
								<div id="idPayuButtonContainer">
								</div>
							</div>
						</div>
					</div>
				</form>
			</div>
		</div>
	</center>
</div>
@endsection
