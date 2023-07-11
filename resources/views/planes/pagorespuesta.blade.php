@extends('layouts.app')

@section('content')

<div class="section3">
	<center>
		<div class="enlamitad">
			<div class="barra-princi-pagos">
				<h1>
					<i class="fas fa-shopping-cart"></i> Pago Exitoso
				</h1>
			</div>
			<div class="sections-in-gral-payment">
				<div class="alert alert-success" style="text-align: center;">
					<button type="button" class="close" data-dismiss="alert">X</button>
					<strong>PAGO EXITOSO SEÑOR: {{auth()->user()->email}}<br>
						Api key: {{$ApiKey}}<br>
						Merchant Id: {{$pago_respuesta->merchant_id}}<br>
						referenceCode: {{$pago_respuesta->referenceCode}}<br>
						TX_VALUE: {{$pago_respuesta->TX_VALUE}}<br>
						currency: {{$pago_respuesta->currency}}<br>
						{{--currency: {{$pago_respuesta->New_value}}}<br>--}}
						transactionState: {{$pago_respuesta->transactionState}}<br>
						{{--firma_cadena: {{$pago_respuesta->transactionState}}}<br>--}}
						{{--firmacreada: {{$pago_respuesta->firma_cadena}}}<br>
						firma: {{$pago_respuesta->firmacreada}}}<br>--}}
						firma: {{$pago_respuesta->firma}}<br>
						reference_pol: {{$pago_respuesta->reference_pol}}<br>
						cus: {{$pago_respuesta->cus}}<br>
						extra1: {{$pago_respuesta->extra1}}<br>
						pseBank: {{$pago_respuesta->pseBank}}<br>
						lapPaymentMethod: {{$pago_respuesta->lapPaymentMethod}}<br>
						transactionId: {{$pago_respuesta->transactionId}}<br></strong>

					</div>
					<img src="https://www.winkhosting.com/assets/images/formaspago.png" style="margin-bottom: 50px; margin-top: 20px; width: 100%;">
				</div>
			</center>
		</div>
		@endsection