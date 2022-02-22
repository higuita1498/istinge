@if(isset($discountxproduct))
    <cac:AllowanceCharge>
        <cbc:ID>{{($key + 1)}}</cbc:ID>
        <cbc:ChargeIndicator>false</cbc:ChargeIndicator>
        <cbc:AllowanceChargeReasonCode>2</cbc:AllowanceChargeReasonCode>
        <cbc:AllowanceChargeReason>Descuento a producto por buen cliente</cbc:AllowanceChargeReason>
        <cbc:MultiplierFactorNumeric>{{$item->desc}}</cbc:MultiplierFactorNumeric>
        <cbc:Amount currencyID="COP">{{number_format(($item->precio * $item->cant) - $item->total(), 2, '.', '')}}</cbc:Amount>
        <cbc:BaseAmount currencyID="COP">{{number_format($item->precio * $item->cant, 2, '.', '')}}</cbc:BaseAmount>
    </cac:AllowanceCharge>
@endif
