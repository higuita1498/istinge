@extends('layouts.app')

@section('content')

<div class="section3">
	<center>
		<div class="enlamitad">
			<div class="barra-princi-pagos">
				<h1>
					<i class="fas fa-shopping-cart"></i> !Bien Hecho!, has escogido el {{$tipo}}
					{{Auth()->user()->email}}
				</h1>
				<input type="hidden" name="plan" id="plan" value="{{$plan}}">
                <input type="hidden" id="personalPlan" value="{{$personalizado}}">
                <input type="hidden" name="meses" id="p_meses" value="0">
			</div>
			<div id="ocult-planfre" style="display: none;">
				<form method="POST" action="">
					{{ csrf_field() }}
					<div class="sections-in-gral-payment">
						<div class="sct-1forpayment fact-table">
							<table class="tableforpayment"  style="min-width: 517px;">
								<tr class="theadforpayment">
									<th>Frecuancia de cobro</th>
									<th>Período</th>
									<th>Precio</th>
								</tr>
								<tr class="trforpayment">
									<td>
										<p><input class="rd-left" type="radio" name="optradio" id="optradio1" value="{{$valor}}"  checked>Pago Mensual</p>
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
											<p><input class="rd-left" type="radio" name="optradio" id="optradio2" value="{{($valor*6)-($valor*6)*0.05}}" >Pago Semestral</p>
										</td>
										<td>
											<p>6 meses <br> <small><strong>5% Descuento</strong></small></p>
										</td>
										<td>
											<p>COP ${{\App\Funcion::Parsear(($valor*6)-($valor*6)*0.05)}} <br> <small><strong>Ahorras Cop ${{\App\Funcion::Parsear(($valor*6)*0.05)}}</strong></small> </p>
										</td>
									</tr>
									<tr class="trforpayment">
										<td>
											<p><input class="rd-left" type="radio" name="optradio" id="optradio3" value="{{($valor*12)-($valor*12)*0.10}}" >Pago Anual</p>
										</td>
										<td>
											<p>12 meses <br> <small><strong>10% Descuento</strong></small></p>
										</td>
										<td>
											<p>COP ${{\App\Funcion::Parsear(($valor*12)-($valor*12)*0.10)}} <br> <small><strong>Ahorras Cop ${{\App\Funcion::Parsear(($valor*12)*0.10)}}</strong></small> </p>
										</td>
									</tr>
								</table>
							</div>

							<div class="sct-2forpayment">
								<div class="sct2-all">
									<div class="sct2-ttl">
										<h2>Total a Pagar</h2>
									</div>
									<div class="sct2-value">
										<h4 id="totalvalue"></h4>
									</div>
								</div>
								<div class="sct2-btn">
									{{--	<button type="submit" class="btn-obtener-pln btn-alarged">Pagar</button>--}}
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
