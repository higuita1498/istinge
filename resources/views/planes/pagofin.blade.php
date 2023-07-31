@extends('layouts.app')

@section('content')

<div class="section3">
	<center>
		<div class="enlamitad">
			<div class="barra-princi-pagos">
				<h1>
					<i class="fas fa-shopping-cart"></i> Pasarela de Pago
				</h1>
			</div>
			<div class="sections-in-gral-payment">
				<h1>{{$precio}}</h1>
					<fieldset>
						<div class="row">
							<div class="col-md-6">
								<label for="email">Email</label>
								<input id="email" class="form-control" name="email" value="test_user_19653727@testuser.com" type="email" placeholder="your email"/>
							</div>
							<div class="col-md-6">
								<label for="cardNumber">Credit card number:</label>
								<input type="text" class="form-control" id="cardNumber" data-checkout="cardNumber" placeholder="4509 9535 6623 3704" onselectstart="return false" onpaste="return false" onCopy="return false" onCut="return false" onDrag="return false" onDrop="return false" autocomplete=off />
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label for="securityCode">Security code:</label>
								<input type="text" class="form-control" id="securityCode" data-checkout="securityCode" placeholder="123" onselectstart="return false" onpaste="return false" onCopy="return false" onCut="return false" onDrag="return false" onDrop="return false" autocomplete=off />
							</div>
							<div class="col-md-6">
								<label for="cardExpirationMonth">Expiration month:</label>
								<input type="text" class="form-control" id="cardExpirationMonth" data-checkout="cardExpirationMonth" placeholder="12" onselectstart="return false" onpaste="return false" onCopy="return false" onCut="return false" onDrag="return false" onDrop="return false" autocomplete=off />
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label for="cardExpirationYear">Expiration year:</label>
								<input type="text" class="form-control" id="cardExpirationYear" data-checkout="cardExpirationYear" placeholder="2015" onselectstart="return false" onpaste="return false" onCopy="return false" onCut="return false" onDrag="return false" onDrop="return false" autocomplete=off />
							</div>
							<div class="col-md-6">
								<label for="cardholderName">Card holder name:</label>
								<input type="text" class="form-control" id="cardholderName" data-checkout="cardholderName" placeholder="APRO" />
							</div>
						</div>
						<div class="row">
							<div class="col-md-6">
								<label for="docType">Document type:</label>
								<select id="docType" class="form-control" data-checkout="docType"></select>
							</div>
							<div class="col-md-6">
								<label for="docNumber">Document number:</label>
								<input type="text" class="form-control" id="docNumber" data-checkout="docNumber" placeholder="12345678" />
							</div>
						</div>
						<div class="row" style="margin-top:20px;">
							<div class="col-md-12">
								<center>
									<input type="hidden" class="form-control" name="paymentMethodId" />
									{{--<a  id="pay" class="btn btn-primary" href="" value="Pay!" />--}}
								</div>
							</center>
						</div>	
					</fieldset>
				<img src="https://www.winkhosting.com/assets/images/formaspago.png" style="margin-bottom: 50px; margin-top: 20px; width: 100%;">
			</div>
		</center>
	</div>
@endsection