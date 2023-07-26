<cac:BillingReference>
    <cac:InvoiceDocumentReference>
        <cbc:ID>{{$FacturaRelacionada->codigo}}</cbc:ID>
        <cbc:UUID schemeName="CUFE-SHA384">{{$CufeFactRelacionada}}</cbc:UUID>
        <cbc:IssueDate>{{ Carbon\Carbon::parse($FacturaRelacionada->created_at)->format('Y-m-d') }}</cbc:IssueDate>
    </cac:InvoiceDocumentReference>
</cac:BillingReference>
