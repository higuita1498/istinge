<cac:PaymentMeans>
<cbc:ID>{{ $FacturaVenta->forma_pago() }}</cbc:ID> {{--TIPO DE PAGO CREDITO O CONTADO--}}
@if($FacturaVenta->forma_pago() == 1) <cbc:PaymentMeansCode>10</cbc:PaymentMeansCode> @endif {{--METODO DE PAGO EFECTIVO--}}
@if($FacturaVenta->forma_pago() == 2)
<cbc:PaymentMeansCode>1</cbc:PaymentMeansCode>
<cbc:PaymentDueDate>{{ $FacturaVenta->vencimiento }}</cbc:PaymentDueDate>@endif{{-- Se pinta si forma de pago es 2 (Crédito) Fecha vencimiento fact --}}

<cbc:PaymentID>{{ $FacturaVenta->plazo() }}</cbc:PaymentID>
</cac:PaymentMeans>
