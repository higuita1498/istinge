@php echo'<?xml version="1.0" encoding="UTF-8" standalone="no"?>'; @endphp
<DebitNote
    xmlns="urn:oasis:names:specification:ubl:schema:xsd:DebitNote-2"
    xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
    xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
    xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
    xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2"
    xmlns:sts="http://www.dian.gov.co/contratos/facturaelectronica/v1/Structures"
    xmlns:xades="http://uri.etsi.org/01903/v1.3.2#"
    xmlns:xades141="http://uri.etsi.org/01903/v1.4.1#"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="urn:oasis:names:specification:ubl:schema:xsd:DebitNote-2    http://docs.oasis-open.org/ubl/os-UBL-2.1/xsd/maindoc/UBL-DebitNote-2.1.xsd">


    <cbc:CustomizationID>10</cbc:CustomizationID>
    <cbc:ProfileExecutionID>1</cbc:ProfileExecutionID>
    <cbc:ID>{{ $notaDebito->nro }}</cbc:ID>
    <cbc:UUID schemeID="1" schemeName="CUDE-SHA384">{{$CUDEvr}}</cbc:UUID>
    <cbc:IssueDate>{{ Carbon\Carbon::parse($notaDebito->created_at)->format('Y-m-d') }}</cbc:IssueDate>
    <cbc:IssueTime>{{ Carbon\Carbon::parse($notaDebito->created_at)->format('H:i:s') }}-05:00</cbc:IssueTime>
    <cbc:CreditNoteTypeCode>95</cbc:CreditNoteTypeCode>
    <cbc:Note>{{ $notaDebito->observaciones }}</cbc:Note>
    <cbc:DocumentCurrencyCode>COP</cbc:DocumentCurrencyCode>
    <cbc:LineCountNumeric>{{count($items)}}</cbc:LineCountNumeric>

    @php $FacturaVenta = $notaDebito; @endphp
    {{-- BillingReference --}}
    @include('templates.xml._billing_reference')
    {{-- AccountingSupplierParty --}}
    @include('templates.xml._accounting', ['node' => 'AccountingSupplierParty', 'data' =>  $data['Empresa'],'Empresa' => true])
    {{-- AccountingCustomerParty --}}
    @include('templates.xml._accounting', ['node' => 'AccountingCustomerParty', 'data' => $data['Cliente'],'Cliente' => true])
    {{-- PaymentMeans --}}
    {{-- @include('templates.xml._payment_means')--}}
    {{-- AllowanceCharges
    @include('templates.xml._allowance_charges')--}}


      {{-- Como se utiliza el mismo metodo para varias cosas, entonces le damos el nombre que recibe ese metodo, sabiendo que es una notacredito --}}

    {{-- TaxTotals--}}
    @include('templates.xml._tax_totals')
    {{-- WithholdingTaxTotal --}}
    @if($retenciones->count() > 0)
    @include('templates.xml._with_holding_tax_total')
    @endif
    {{-- RequestedMonetaryTotal --}}
    @include('templates.xml._legal_monetary_total', ['node' => 'RequestedMonetaryTotal'])
    {{-- DebitNoteLine --}}
    @include('templates.xml._debit_note_lines')
    <DATA>
        <UBL21>true</UBL21>
        <Partnership>
            <ID>1128464945</ID>
            <TechKey>@if($FacturaRelacionada->technicalkey == null){{Auth::user()->empresaObj->technicalkey}}@else{{$FacturaRelacionada->technicalkey}}@endif</TechKey>
            <SetTestID>{{Auth::user()->empresaObj->fe_resolucion}}</SetTestID>
        </Partnership>
    </DATA>
</DebitNote>
