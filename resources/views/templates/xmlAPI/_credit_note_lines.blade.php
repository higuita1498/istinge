@foreach ($items as $key => $item)
    <cac:CreditNoteLine>
        <cbc:ID>{{($key + 1)}}</cbc:ID>
        <cbc:CreditedQuantity unitCode="">{{number_format($item->cant, 2, '.', '')}}</cbc:CreditedQuantity>
        <cbc:LineExtensionAmount currencyID="COP">{{number_format($item->total(), 2, '.', '')}}</cbc:LineExtensionAmount>
        {{--<cbc:FreeOfChargeIndicator>{{$creditNoteLine->free_of_charge_indicator}}</cbc:FreeOfChargeIndicator>
        @if ($creditNoteLine->free_of_charge_indicator === 'true')
            <cac:PricingReference>
                <cac:AlternativeConditionPrice>
                    <cbc:PriceAmount currencyID="{{$company->type_currency->code}}">{{number_format($creditNoteLine->price_amount, 2, '.', '')}}</cbc:PriceAmount>
                    <cbc:PriceTypeCode>{{$creditNoteLine->reference_price->code}}</cbc:PriceTypeCode>
                </cac:AlternativeConditionPrice>
            </cac:PricingReference>
        @endif--}}
        
        {{-- TaxTotals line --}}
        @include('templates.xml._tax_totals')
        {{-- AllowanceCharges line  
        @include('xml._allowance_charges', ['allowanceCharges' => $creditNoteLine->allowance_charges])--}}
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
    </cac:CreditNoteLine>
@endforeach
