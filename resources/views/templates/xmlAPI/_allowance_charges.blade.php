@if(!isset($discountxproduct))
{{-- @foreach ($allowanceCharges as $key => $allowanceCharge) --}}
<cac:AllowanceCharge>
<cbc:ID>1</cbc:ID>
        {{--<cbc:ChargeIndicator>false</cbc:ChargeIndicator>
        @if (($allowanceCharge->charge_indicator === 'false') && ($allowanceCharge->discount))
            <cbc:AllowanceChargeReasonCode>{{$allowanceCharge->discount->code}}</cbc:AllowanceChargeReasonCode>
            @endif--}}
            <cbc:AllowanceChargeReasonCode>2</cbc:AllowanceChargeReasonCode>
            <cbc:AllowanceChargeReason>Descuento a factura por buen cliente</cbc:AllowanceChargeReason>
            <cbc:MultiplierFactorNumeric>{{($FacturaVenta->totalAPI($FacturaVenta->empresa)->descuento * 100) / $FacturaVenta->totalAPI($FacturaVenta->empresa)->subtotal}}</cbc:MultiplierFactorNumeric>
            <cbc:Amount currencyID="COP">{{number_format($FacturaVenta->totalAPI($FacturaVenta->empresa)->descuento, 2, '.', '')}}</cbc:Amount>
            {{-- @if ($allowanceCharge->base_amount)--}}
            <cbc:BaseAmount currencyID="COP">{{number_format($FacturaVenta->totalAPI($FacturaVenta->empresa)->subtotal, 2, '.', '')}}</cbc:BaseAmount>
            {{-- @endif --}}
        </cac:AllowanceCharge>
        {{-- @endforeach --}}
        @elseif(isset($discountxproduct))
        <cac:AllowanceCharge>
        <cbc:ID>1</cbc:ID>
        <cbc:AllowanceChargeReasonCode>2</cbc:AllowanceChargeReasonCode>
        <cbc:AllowanceChargeReason>Descuento a producto por buen cliente</cbc:AllowanceChargeReason>
        <cbc:MultiplierFactorNumeric>{{$item->desc}}</cbc:MultiplierFactorNumeric>
        <cbc:Amount currencyID="COP">{{number_format(($item->precio * $item->cant) - $item->total(), 2, '.', '')}}</cbc:Amount>
        <cbc:BaseAmount currencyID="COP">{{number_format($item->precio * $item->cant, 2, '.', '')}}</cbc:BaseAmount>
    </cac:AllowanceCharge>
    @endif
