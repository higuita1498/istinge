
@php $noIva = 0; $contNoIva = 0; $swIva=0; @endphp
@if(!isset($impuestoItem))
@if($FacturaVenta->total()->imp && $isImpuesto)
@foreach ($FacturaVenta->total()->imp as $key => $imp)
    @if(isset($imp->total) && $imp->tipo == 1)
    @php $swIva = 1; @endphp
<cac:TaxTotal>
        <cbc:TaxAmount currencyID="COP">{{number_format($impTotal, 2, '.', '')}}</cbc:TaxAmount>
        <cbc:RoundingAmount currencyID="COP">0</cbc:RoundingAmount>
        <cac:TaxSubtotal>
        <cbc:TaxableAmount currencyID="COP">{{number_format($imp->totalprod, 2, '.', '')}}</cbc:TaxableAmount>
        <cbc:TaxAmount currencyID="COP">{{number_format(round($imp->total), 2, '.', '')}}</cbc:TaxAmount>
        <cac:TaxCategory>
        <cbc:Percent>{{number_format($imp->porcentaje, 2, '.', '')}}</cbc:Percent>
        <cac:TaxScheme>
        <cbc:ID>01</cbc:ID>
        <cbc:Name>{{$imp->nombre}}</cbc:Name>
        </cac:TaxScheme>
        </cac:TaxCategory>
        </cac:TaxSubtotal>
        @if($noIva == 1 || $FacturaVenta->isItemSinIva() === true)
        <cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID="COP">{{number_format($FacturaVenta->total()->total, 2, '.', '')}}</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID="COP">0.00</cbc:TaxAmount>
            <cac:TaxCategory>
            <cbc:Percent>0.00</cbc:Percent>
            <cac:TaxScheme>
            <cbc:ID>01</cbc:ID>
            <cbc:Name>IVA</cbc:Name>
            </cac:TaxScheme>
            </cac:TaxCategory>
        </cac:TaxSubtotal>
        @endif
</cac:TaxTotal>
    @elseif(!isset($imp->total))
    @php $contNoIva++ @endphp
    @endif
@endforeach
{{-- Comparamos si la cantidad imp que no tienen total es igual a la cantidad del array --}}
@if($contNoIva == count($FacturaVenta->total()->imp))
    @php $noIva=1 @endphp
@endif
@if(($noIva == 1 || $FacturaVenta->isItemSinIva() === true) && $swIva == 0)
<cac:TaxTotal>
    <cbc:TaxAmount currencyID="COP">0.00</cbc:TaxAmount>
    <cbc:RoundingAmount currencyID="COP">0</cbc:RoundingAmount>
    <cac:TaxSubtotal>
    <cbc:TaxableAmount currencyID="COP">{{number_format($FacturaVenta->total()->total, 2, '.', '')}}</cbc:TaxableAmount>
    <cbc:TaxAmount currencyID="COP">0.00</cbc:TaxAmount>
    <cac:TaxCategory>
    <cbc:Percent>0.00</cbc:Percent>
    <cac:TaxScheme>
    <cbc:ID>01</cbc:ID>
    <cbc:Name>IVA</cbc:Name>
    </cac:TaxScheme>
    </cac:TaxCategory>
    </cac:TaxSubtotal>
</cac:TaxTotal>
@endif

{{-- Otros impuestos como el inc tiene que ir en otro <cac:ax:total> --}}
@foreach ($FacturaVenta->total()->imp as $key => $imp)
    @if(isset($imp->total) && $imp->tipo == 3)
<cac:TaxTotal>
        <cbc:TaxAmount currencyID="COP">{{number_format($impTotal, 2, '.', '')}}</cbc:TaxAmount>
        <cbc:RoundingAmount currencyID="COP">0</cbc:RoundingAmount>
        <cac:TaxSubtotal>
        <cbc:TaxableAmount currencyID="COP">{{number_format($imp->totalprod, 2, '.', '')}}</cbc:TaxableAmount>
        <cbc:TaxAmount currencyID="COP">{{number_format(round($imp->total), 2, '.', '')}}</cbc:TaxAmount>
        <cac:TaxCategory>
        <cbc:Percent>{{number_format($imp->porcentaje, 2, '.', '')}}</cbc:Percent>
        <cac:TaxScheme>
        <cbc:ID>04</cbc:ID>
        <cbc:Name>{{$imp->nombre}}</cbc:Name>
        </cac:TaxScheme>
        </cac:TaxCategory>
        </cac:TaxSubtotal>
</cac:TaxTotal>
    @endif
        @if(isset($imp->total) && $imp->tipo == 2)
<cac:TaxTotal>
        <cbc:TaxAmount currencyID="COP">{{number_format($impTotal, 2, '.', '')}}</cbc:TaxAmount>
        <cbc:RoundingAmount currencyID="COP">0</cbc:RoundingAmount>
        <cac:TaxSubtotal>
        <cbc:TaxableAmount currencyID="COP">{{number_format($imp->totalprod, 2, '.', '')}}</cbc:TaxableAmount>
        <cbc:TaxAmount currencyID="COP">{{number_format(round($imp->total), 2, '.', '')}}</cbc:TaxAmount>
        <cac:TaxCategory>
        <cbc:Percent>{{number_format($imp->porcentaje, 2, '.', '')}}</cbc:Percent>
        <cac:TaxScheme>
        <cbc:ID>ZZ</cbc:ID>
        <cbc:Name>No aplica</cbc:Name>
        </cac:TaxScheme>
        </cac:TaxCategory>
        </cac:TaxSubtotal>
</cac:TaxTotal>
    @endif
@endforeach
{{-- FIN Otros impuestos como el inc tiene que ir en otro <cac:ax:total> FIN --}}
@endif
@endif
@php $sw = 0; @endphp
@isset($impuestoItem)
    <cac:TaxTotal>
        <cbc:TaxAmount currencyID="COP">{{number_format(round($item->itemImpDescuento()), 2, '.', '')}}</cbc:TaxAmount>
        @for($i = 0; $i < 7; $i++) @if(isset($item->totalImpSingular()[$i]["imp".$i]))<cac:TaxSubtotal>
            @php $sw = 1; @endphp
            <cbc:TaxableAmount currencyID="COP">{{number_format($item->total(), 2, '.', '')}}</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID="COP">{{number_format(round($item->totalImpSingular()[$i]["imp".$i]), 2, '.', '')}}</cbc:TaxAmount>
            <cac:TaxCategory>
                <cbc:Percent>{{number_format($item->itemImpuestoSingular()[$i]["imp".$i], 2, '.', '')}}</cbc:Percent>
                 <cac:TaxScheme>
                    <cbc:ID>@if($item->impuestoSingularNombre()[$i]["imp".$i] == "INC" ){{"04"}}@elseif($item->impuestoSingularNombre()[$i]["imp".$i] == "ICO" ){{"ZZ"}}@else{{"01"}}@endif</cbc:ID>
                    <cbc:Name>@if($item->impuestoSingularNombre()[$i]["imp".$i] == "ICO"){{"No aplica"}}@else{{$item->impuestoSingularNombre()[$i]["imp".$i]}}@endif</cbc:Name>
                </cac:TaxScheme>
            </cac:TaxCategory>
        </cac:TaxSubtotal>
        @endif
        @endfor @if($sw == 0)<cac:TaxSubtotal>
            <cbc:TaxableAmount currencyID="COP">{{number_format($item->total(), 2, '.', '')}}</cbc:TaxableAmount>
            <cbc:TaxAmount currencyID="COP">{{number_format($FacturaVenta->redondeo($item->itemImpDescuento()), 2, '.', '')}}</cbc:TaxAmount>
            <cac:TaxCategory>
                <cbc:Percent>{{number_format(($item->itemImpuesto() * 100) / $item->precio, 2, '.', '')}}</cbc:Percent>
                 <cac:TaxScheme>
                    <cbc:ID>01</cbc:ID>
                    <cbc:Name>{{$item->impuestoNombre()}}</cbc:Name>
                </cac:TaxScheme>
            </cac:TaxCategory>
        </cac:TaxSubtotal>
    @endif
    </cac:TaxTotal>
    @endisset
