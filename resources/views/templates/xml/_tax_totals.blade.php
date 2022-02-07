
@if(!isset($impuestoItem))
@if($FacturaVenta->total()->imp)
@foreach ($FacturaVenta->total()->imp as $key => $imp)
@if(isset($imp->total))
<cac:TaxTotal>
<cbc:TaxAmount currencyID="COP">{{number_format($imp->total, 2, '.', '')}}</cbc:TaxAmount>
<cac:TaxSubtotal>
<cbc:TaxableAmount currencyID="COP">{{number_format($imp->totalprod, 2, '.', '')}}</cbc:TaxableAmount>
<cbc:TaxAmount currencyID="COP">{{number_format($imp->total, 2, '.', '')}}</cbc:TaxAmount>
                    {{--@if ($taxTotal->is_fixed_value)
                        <cbc:BaseUnitMeasure unitCode="{{$taxTotal->unit_measure->code}}">{{number_format($taxTotal->base_unit_measure, 6, '.', '')}}</cbc:BaseUnitMeasure>
                        <cbc:PerUnitAmount currencyID="{{$company->type_currency->code}}">{{number_format($taxTotal->per_unit_amount, 2, '.', '')}}</cbc:PerUnitAmount>
                        @endif--}}
                        <cac:TaxCategory>
                        {{--@if (!$taxTotal->is_fixed_value)--}}
                        <cbc:Percent>{{number_format($imp->porcentaje, 2, '.', '')}}</cbc:Percent>
                        {{--@endif--}}
                        <cac:TaxScheme>
                        <cbc:ID>01</cbc:ID>
                        <cbc:Name>{{$imp->nombre}}</cbc:Name>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        </cac:TaxTotal>
        @endif
        @endforeach
        @endif
        @endif

@isset($impuestoItem)
    <cac:TaxTotal>
        <cbc:TaxAmount currencyID="COP">{{number_format($item->itemImpDescuento() , 2, '.', '')}}</cbc:TaxAmount>
        <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID="COP">{{number_format($item->total(), 2, '.', '')}}</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID="COP">{{number_format($item->itemImpDescuento(), 2, '.', '')}}</cbc:TaxAmount>
            <cac:TaxCategory>
                <cbc:Percent>{{number_format(($item->itemImpuesto() * 100) / $item->precio, 2, '.', '')}}</cbc:Percent>
                 <cac:TaxScheme>
                    <cbc:ID>01</cbc:ID>
                    <cbc:Name>{{$item->impuesto()}}</cbc:Name>
                </cac:TaxScheme>
            </cac:TaxCategory>
        </cac:TaxSubtotal>
    </cac:TaxTotal>
    @endisset
