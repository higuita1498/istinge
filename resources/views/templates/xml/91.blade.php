@php echo'<?xml version="1.0" encoding="UTF-8" standalone="no"?>'; @endphp
<CreditNote xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns="urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:sts="dian:gov:co:facturaelectronica:Structures-2-1" xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" xmlns:xades141="http://uri.etsi.org/01903/v1.4.1#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2     http://docs.oasis-open.org/ubl/os-UBL-2.1/xsd/maindoc/UBL-CreditNote-2.1.xsd">
    {{-- UBLExtensions
    @include('templates.xml._ubl_extensions') --}}
    <cbc:CustomizationID>{{$NotaCredito->tipo_operacion == 2 ? '20' : '20'}}</cbc:CustomizationID>
    <cbc:ProfileExecutionID>1</cbc:ProfileExecutionID>
    <cbc:ID>{{ $NotaCredito->nro }}</cbc:ID>
    <cbc:UUID schemeID="1" schemeName="CUDE-SHA384">{{$CUDEvr}}</cbc:UUID>
    <cbc:IssueDate>{{ Carbon\Carbon::parse($NotaCredito->fecha)->format('Y-m-d') }}</cbc:IssueDate>
    @if($NotaCredito->tiempo_creacion)
    <cbc:IssueTime>{{ Carbon\Carbon::parse($NotaCredito->tiempo_creacion)->format('H:i:s') }}-05:00</cbc:IssueTime>
    @else
    <cbc:IssueTime>{{ Carbon\Carbon::parse($NotaCredito->created_at)->format('H:i:s') }}-05:00</cbc:IssueTime>
    @endif
    <cbc:CreditNoteTypeCode>91</cbc:CreditNoteTypeCode>{{-- tipo de nota de credito --}}
    <cbc:DocumentCurrencyCode>COP</cbc:DocumentCurrencyCode>
    <cbc:LineCountNumeric>{{count($items)}}</cbc:LineCountNumeric>

    <cac:DiscrepancyResponse>
        <cbc:ReferenceID>Seccion de la factura la cual se le aplica la correcion</cbc:ReferenceID>
        <cbc:ResponseCode>{{ $NotaCredito->tipo }}</cbc:ResponseCode> {{-- codigo para correccion --}}
        <cbc:Description>{{ $NotaCredito->tipo() }}</cbc:Description>{{-- Concepto de Correccion para Notas credito --}}
    </cac:DiscrepancyResponse>
    {{-- Como se utiliza el mismo metodo para varias cosas, entonces le damos el nombre que recibe ese metodo, sabiendo que es una notacredito --}}
    @php $FacturaVenta = $NotaCredito; @endphp
    {{-- OrderReference --}}
    @if($FacturaVenta->ordencompra != null)
    @include('templates.xml._order_reference', ['node' => 'OrderReference',    'data' =>  $data['Empresa'],'Empresa' => true])
    @endif
    {{-- BillingReference --}}
    @include('templates.xml._billing_reference')
    {{-- AccountingSupplierParty --}}
    @include('templates.xml._accounting', ['node' => 'AccountingSupplierParty', 'data' =>  $data['Empresa'],'Empresa' => true,'nc'=>1])
    {{-- AccountingCustomerParty --}}
    @include('templates.xml._accounting', ['node' => 'AccountingCustomerParty', 'data' => $data['Cliente'],'Cliente' => true,'nc'=>1])
    {{-- PaymentMeans --}}
    @include('templates.xml._payment_means', ['isNotaCredito' => 1])
    {{-- AllowanceCharges --}}
    {{--@include('xml._allowance_charges')--}}
    {{-- TaxTotals --}}
    @include('templates.xml._tax_totals')
    {{-- WithholdingTaxTotal --}}
    {{--@if($retenciones->count() > 0)
    @include('templates.xml._with_holding_tax_total')
    @endif--}}{{-- LegalMonetaryTotal --}}
    @include('templates.xml._legal_monetary_total', ['node' => 'LegalMonetaryTotal', 'nc'=>1])
    {{-- CreditNoteLine --}}
    @include('templates.xml._credit_note_lines')
    <DATA>
        <UBL21>true</UBL21>
        <Partnership>
            <ID>1128464945</ID>
            <TechKey>@if($FacturaRelacionada->technicalkey == null){{Auth::user()->empresaObj->technicalkey}}@else{{$FacturaRelacionada->technicalkey}}@endif</TechKey>
            <SetTestID>{{Auth::user()->empresaObj->fe_resolucion}}</SetTestID>
        </Partnership>
    </DATA>
</CreditNote>
