@foreach ($items as $key => $item)
    <cac:InvoiceLine>
        <cbc:ID>{{($key + 1)}}</cbc:ID>
        @if($item->descripcion != null || $item->productoIva() == 1)
        <cbc:Note>@if($item->productoIva() == 1){{'Bienes Cubiertos'}} @else @if($FacturaVenta->tipo_operacion == 2){{"Contrato de servicios AIU por concepto de: "}}@endif {{$item->descripcion}} @endif</cbc:Note>
        @endif

        <cbc:InvoicedQuantity unitCode="94">{{number_format($item->cant, 2, '.', '')}}</cbc:InvoicedQuantity>
        <cbc:LineExtensionAmount currencyID="COP">{{number_format($item->total(), 2, '.', '')}}</cbc:LineExtensionAmount>

        @if(isset($facturaP->codigo_dian))
        <cac:InvoicePeriod>
			<cbc:StartDate>{{$facturaP->fecha_factura}}</cbc:StartDate>
			<cbc:DescriptionCode>1</cbc:DescriptionCode> 
			<cbc:Description>Por operación</cbc:Description> 
		</cac:InvoicePeriod>
		@endif
        {{-- AllowanceCharges line  --}}
        @if($item->desc > 0)
        @include('templates.xml._allowance_charges', ['discountxproduct' => true])
        @endif
        {{-- TaxTotals line --}}
        @include('templates.xml._tax_totals', ['impuestoItem' => true])
        <cac:Item>
            <cbc:Description>{{$item->producto()}}</cbc:Description>
              @if(isset($facturaP->codigo_dian))
            <cac:StandardItemIdentification>
                <cbc:ID schemeID="999" schemeName="Estandar de adopcion del contribuyente"  schemeAgencyID="">@if($item->ref){{$item->ref}}@elseif($item->producto(true)->codigo){{$item->producto(true)->codigo}}@endif</cbc:ID>
            </cac:StandardItemIdentification>
            @endif
        </cac:Item>
        <cac:Price>
            <cbc:PriceAmount currencyID="COP">{{number_format($item->precio, 2, '.', '')}}</cbc:PriceAmount>
            <cbc:BaseQuantity unitCode="94">{{number_format($item->cant, 2, '.', '')}}</cbc:BaseQuantity>
        </cac:Price>
    </cac:InvoiceLine>
@endforeach
