@foreach ($items as $key => $item)
    <cac:DebitNoteLine>
        <cbc:ID>{{($key + 1)}}</cbc:ID>
        <cbc:DebitedQuantity unitCode="">{{number_format($item->cant, 2, '.', '')}}</cbc:DebitedQuantity>
        <cbc:LineExtensionAmount currencyID="COP">{{number_format($item->total(), 2, '.', '')}}</cbc:LineExtensionAmount>
        {{-- @if ($debitNoteLine->free_of_charge_indicator === 'true')
            <cac:PricingReference>
                <cac:AlternativeConditionPrice>
                    <cbc:PriceAmount currencyID="{{$company->type_currency->code}}">{{number_format($debitNoteLine->price_amount, 2, '.', '')}}</cbc:PriceAmount>
                    <cbc:PriceTypeCode>{{$debitNoteLine->reference_price->code}}</cbc:PriceTypeCode>
                </cac:AlternativeConditionPrice>
            </cac:PricingReference>
        @endif --}}
        {{-- TaxTotals line --}}
        @include('templates.xml._tax_totals')
        {{-- AllowanceCharges line 
        @include('xml._allowance_charges', ['allowanceCharges' => $debitNoteLine->allowance_charges]) --}}
        <cac:Item>
            <cbc:Description>{{$item->producto()}}</cbc:Description>
            <cac:StandardItemIdentification>
                <cbc:ID schemeID="" schemeName="EAN13" schemeAgencyID="">{{$item->ref}}</cbc:ID>
            </cac:StandardItemIdentification>
        </cac:Item>
        <cac:Price>
            <cbc:PriceAmount currencyID="COP">{{number_format($item->precio, 2, '.', '')}}</cbc:PriceAmount>
            <cbc:BaseQuantity unitCode="EA">{{number_format($item->cant, 2, '.', '')}}</cbc:BaseQuantity>
        </cac:Price>
    </cac:DebitNoteLine>
@endforeach
