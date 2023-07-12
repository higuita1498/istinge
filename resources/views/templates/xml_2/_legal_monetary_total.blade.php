<cac:{{$node}}>
    <cbc:LineExtensionAmount currencyID="COP">{{number_format($FacturaVenta->total()->subtotal - $FacturaVenta->total()->descuento, 2, '.', '')}}</cbc:LineExtensionAmount>
    @if(isset($nc))
        <cbc:TaxExclusiveAmount currencyID="COP">{{number_format($FacturaVenta->total()->subtotal - $FacturaVenta->total()->descuento, 2, '.', '')}}</cbc:TaxExclusiveAmount>
    @else
        <cbc:TaxExclusiveAmount currencyID="COP">{{number_format($FacturaVenta->total()->TaxExclusiveAmount, 2, '.', '')}}</cbc:TaxExclusiveAmount>
    @endif
    <cbc:TaxInclusiveAmount currencyID="COP">{{number_format($FacturaVenta->total()->subtotal + $FacturaVenta->impuestos_totales() - $FacturaVenta->total()->descuento, 2, '.', '')}}</cbc:TaxInclusiveAmount>

    @if($FacturaVenta->total()->descuento > 0) 
        <cbc:AllowanceTotalAmount currencyID="COP">{{--{{number_format($FacturaVenta->total()->descuento, 2, '.', '')}}--}}0.00</cbc:AllowanceTotalAmount>
    @endif
    {{-- 
    @if ($legalMonetaryTotals->charge_total_amount)
        <cbc:ChargeTotalAmount currencyID="{{$company->type_currency->code}}">{{number_format($legalMonetaryTotals->charge_total_amount, 2, '.', '')}}</cbc:ChargeTotalAmount>
    @endif
    @if ($legalMonetaryTotals->pre_paid_amount)
        <cbc:PrePaidAmount currencyID="{{$company->type_currency->code}}">{{number_format($legalMonetaryTotals->pre_paid_amount, 2, '.', '')}}</cbc:PrePaidAmount>
    @endif--}}
    <cbc:PayableAmount currencyID="COP">{{number_format($FacturaVenta->total()->subtotal + $FacturaVenta->impuestos_totales() - $FacturaVenta->total()->descuento, 2, '.', '')}}</cbc:PayableAmount>
</cac:{{$node}}>
