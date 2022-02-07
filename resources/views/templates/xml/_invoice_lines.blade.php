@foreach ($items as $key => $item)
    <cac:InvoiceLine>
        <cbc:ID>{{($key + 1)}}</cbc:ID>
        @if($item->descripcion != null)
        <cbc:Note>@if($FacturaVenta->tipo_operacion == 2){{"Contrato de servicios AIU por concepto de: "}}@endif {{$item->descripcion}}</cbc:Note>@endif

        <cbc:InvoicedQuantity unitCode="94">{{number_format($item->cant, 2, '.', '')}}</cbc:InvoicedQuantity>
        <cbc:LineExtensionAmount currencyID="COP">{{number_format($item->total(), 2, '.', '')}}</cbc:LineExtensionAmount>
        {{--<cbc:FreeOfChargeIndicator></cbc:FreeOfChargeIndicator>--}}
        {{--@if ($invoiceLine->free_of_charge_indicator === 'true')
            <cac:PricingReference>
                <cac:AlternativeConditionPrice>
                    <cbc:PriceAmount currencyID="{{$company->type_currency->code}}">{{number_format($invoiceLine->price_amount, 2, '.', '')}}</cbc:PriceAmount>
                    <cbc:PriceTypeCode>{{$invoiceLine->reference_price->code}}</cbc:PriceTypeCode>
                </cac:AlternativeConditionPrice>
            </cac:PricingReference>
        @endif--}}
        {{-- AllowanceCharges line  --}}
        @if($item->desc > 0)
        @include('templates.xml._allowance_charges', ['discountxproduct' => true])
        @endif
        {{-- TaxTotals line --}}
        @include('templates.xml._tax_totals', ['impuestoItem' => true])
        <cac:Item>
            <cbc:Description>{{$item->producto()}}</cbc:Description>
            <cac:StandardItemIdentification>
                <cbc:ID schemeID="999" schemeName="" schemeAgencyID="">{{$item->ref}}</cbc:ID>
            </cac:StandardItemIdentification>
        </cac:Item>
        <cac:Price>
            <cbc:PriceAmount currencyID="COP">{{number_format($item->precio, 2, '.', '')}}</cbc:PriceAmount>
            <cbc:BaseQuantity unitCode="94">{{number_format($item->cant, 2, '.', '')}}</cbc:BaseQuantity>
        </cac:Price>
    </cac:InvoiceLine>
@endforeach
