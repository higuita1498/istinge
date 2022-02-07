<cac:{{$node}}>
    <cbc:LineExtensionAmount currencyID="COP">{{number_format($FacturaVenta->totalAPI($FacturaVenta->empresa)->subtotal, 2, '.', '')}}</cbc:LineExtensionAmount>
    <cbc:TaxExclusiveAmount currencyID="COP">{{number_format($FacturaVenta->totalAPI($FacturaVenta->empresa)->subtotal, 2, '.', '')}}</cbc:TaxExclusiveAmount>

    <cbc:TaxInclusiveAmount currencyID="COP">{{number_format($FacturaVenta->impuestos_totalesFe(), 2, '.', '')}}</cbc:TaxInclusiveAmount>

    @if($FacturaVenta->totalAPI($FacturaVenta->empresa)->descuento > 0)
        <cbc:AllowanceTotalAmount currencyID="COP">{{number_format($FacturaVenta->totalAPI($FacturaVenta->empresa)->descuento, 2, '.', '')}}</cbc:AllowanceTotalAmount>
    @endif
    {{--
    @if ($legalMonetaryTotals->charge_total_amount)
        <cbc:ChargeTotalAmount currencyID="{{$company->type_currency->code}}">{{number_format($legalMonetaryTotals->charge_total_amount, 2, '.', '')}}</cbc:ChargeTotalAmount>
    @endif
    @if ($legalMonetaryTotals->pre_paid_amount)
        <cbc:PrePaidAmount currencyID="{{$company->type_currency->code}}">{{number_format($legalMonetaryTotals->pre_paid_amount, 2, '.', '')}}</cbc:PrePaidAmount>
    @endif--}}
    <cbc:PayableAmount currencyID="COP">{{-- {{number_format($FacturaVenta->totalAPI($FacturaVenta->empresa)->subtotal + $FacturaVenta->impuestos_totales(), 2, '.', '')}} --}}{{  number_format($FacturaVenta->totalAPI($FacturaVenta->empresa)->total, 2, '.', '') }}</cbc:PayableAmount>
</cac:{{$node}}>
