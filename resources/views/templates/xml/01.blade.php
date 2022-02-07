@php echo'<?xml version="1.0" encoding="UTF-8" standalone="no"?>'; @endphp
<Invoice xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2" xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2" xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2" xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2" xmlns:sts="dian:gov:co:facturaelectronica:Structures-2-1" xmlns:xades="http://uri.etsi.org/01903/v1.3.2#" xmlns:xades141="http://uri.etsi.org/01903/v1.4.1#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2     http://docs.oasis-open.org/ubl/os-UBL-2.1/xsd/maindoc/UBL-Invoice-2.1.xsd">
    {{-- UBLExtensions --}}
    @include('templates.xml._ubl_extensions')
    {{--<cbc:UBLVersionID>UBL 2.1</cbc:UBLVersionID>--}}
    <cbc:CustomizationID>{{$FacturaVenta->tipo_operacion == 2 ? '09' : '05'}}</cbc:CustomizationID> {{-- TIPO DE OPERACION: GENERICA 05 --}}
    <cbc:ProfileExecutionID>1</cbc:ProfileExecutionID> {{-- EJECUCION EN PRODUCCION 1 : PRUEBAS 2 --}}
    <cbc:ID>{{ $FacturaVenta->codigo }}</cbc:ID> {{--CORRELATIVO DE LA FACTURA--}}
    <cbc:UUID schemeID="1" schemeName="CUFE-SHA384">{{$CUFEvr}}</cbc:UUID> {{-- TIPO DE EJECUCION + CODIFICACION --}}
    <cbc:IssueDate>{{ Carbon\Carbon::parse($FacturaVenta->created_at)->format('Y-m-d') }}{{-- {{$date ?? Carbon\Carbon::now()->format('Y-m-d')}} --}}</cbc:IssueDate>
    <cbc:IssueTime>{{--{{$time ?? Carbon\Carbon::now()->format('H:i:s')}}--}}{{ Carbon\Carbon::parse($FacturaVenta->created_at)->format('H:i:s') }}-05:00</cbc:IssueTime>
    <cbc:InvoiceTypeCode>01</cbc:InvoiceTypeCode> {{-- TIPO DE DOCUMENTO FAC VENTA--}}
    <cbc:Note>{{$FacturaVenta->nota}}</cbc:Note> {{-- NOTA DELA FACTURA--}}
    <cbc:DocumentCurrencyCode>COP</cbc:DocumentCurrencyCode> {{-- MONEDA EN LA QUE SE PAGARA LA FACTURA --}}
    <cbc:LineCountNumeric>{{count($items)}}</cbc:LineCountNumeric> {{-- CANTIDAD DE ITEMS EN LA FACTURA--}}
    
    
    {{-- OrderReference --}}
    @if($FacturaVenta->ordencompra != null)
    @include('templates.xml._order_reference', ['node' => 'OrderReference',    'data' =>  $data['Empresa'],'Empresa' => true])
    @endif
    {{-- AccountingSupplierParty--}}
    @include('templates.xml._accounting', ['node' => 'AccountingSupplierParty', 'data' =>  $data['Empresa'],'Empresa' => true])
    {{-- AccountingCustomerParty --}}
    @include('templates.xml._accounting', ['node' => 'AccountingCustomerParty', 'data' => $data['Cliente'],'Cliente' => true])
    {{-- PaymentMeans --}}
    @include('templates.xml._payment_means')
    {{-- PaymentTerms --}}
    @include('templates.xml._payment_terms')
    {{-- AllowanceCharges --}}
    @if($FacturaVenta->total()->descuento > 0) 
    @include('templates.xml._allowance_charges')
    @endif
    {{-- TaxTotals --}}
    @include('templates.xml._tax_totals')
    {{-- WithholdingTaxTotal --}}
    @if($retenciones->count() > 0)
    @include('templates.xml._with_holding_tax_total')
    @endif
    {{-- LegalMonetaryTotal --}}
    @include('templates.xml._legal_monetary_total', ['node' => 'LegalMonetaryTotal'])
    {{-- InvoiceLines --}}
    @include('templates.xml._invoice_lines')
    <DATA>
        <UBL21>true</UBL21>
        <Partnership>
            <ID>1128464945</ID>
            <TechKey>{{Auth::user()->empresa()->technicalkey}}</TechKey>
            <SetTestID>{{Auth::user()->empresa()->fe_resolucion}}</SetTestID>
            </Partnership>
    </DATA>
</Invoice>
