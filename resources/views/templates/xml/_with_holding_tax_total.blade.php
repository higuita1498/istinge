@foreach($retenciones as $retencion)
<cac:WithholdingTaxTotal>
<cbc:TaxAmount currencyID="COP">{{ number_format($retencion->valor, 2, '.','')  }}</cbc:TaxAmount>
{{--<cbc:TaxEvidenceIndicator>true</cbc:TaxEvidenceIndicator>--}}
<cac:TaxSubtotal>
<cbc:TaxableAmount currencyID="COP">@php 
//-- Si existe una retencion al iva (tipo = 1) debo de obtener el total del impuesto asociado a ese iva para el xml --//
if ($retencion->retencion()->tipo == 1) {

	$total_impuesto_asociado_a_retencion = ""; 

	foreach ($FacturaVenta->total()->reten as $key => $reten) {
		if (isset($reten->total) && $reten->tipo == 1) {
			foreach ($FacturaVenta->total()->imp as $imp) {
				if (isset($imp->total) && $imp->tipo == 1) {
					$total_impuesto_asociado_a_retencion = $imp->total;
				}
			}
			echo number_format($total_impuesto_asociado_a_retencion,2,'.','');
		}
	}
}
//-- Si existe una retencion en la fuente (tipo = 2) debo de obtener el total del subtotal asociado a esa retefuente para el xml --//
else if($retencion->retencion()->tipo == 2 || $retencion->retencion()->tipo == 3 || $retencion->retencion()->tipo == 4)
{
	if ($FacturaVenta->total()->descuento > 0) {
		echo number_format($FacturaVenta->total()->subtotal - $FacturaVenta->total()->descuento, 2, '.','');
	}else
	{
		echo number_format($FacturaVenta->total()->subtotal,2,'.','');
	}
}
@endphp</cbc:TaxableAmount>
<cbc:TaxAmount currencyID="COP">{{ number_format($retencion->valor, 2, '.','') }}</cbc:TaxAmount>
<cac:TaxCategory>
<cbc:Percent>{{ $retencion->retencion }}</cbc:Percent>
<cac:TaxScheme>
<cbc:ID>@if($retencion->retencion()->tipo == 1){{"05"}}@elseif($retencion->retencion()->tipo == 2){{"06"}}@else{{"ZZ"}}@endif</cbc:ID>
@if(isset($facturap))
<cbc:Name>@if($retencion->retencion()->tipo == 2){{"ReteRenta"}}@elseif($retencion->retencion()->tipo == 1){{"ReteIVA"}}@endif</cbc:Name>
@else
<cbc:Name>{{ $retencion->retencion()->nombre }}</cbc:Name>
@endif
</cac:TaxScheme>
</cac:TaxCategory>
</cac:TaxSubtotal>
</cac:WithholdingTaxTotal>
@endforeach