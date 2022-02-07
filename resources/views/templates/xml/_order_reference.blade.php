<cac:{{$node}}>
        <cbc:ID>{{$FacturaVenta->ordencompra}}</cbc:ID>
        <cbc:IssueDate>{{ Carbon\Carbon::parse($FacturaVenta->created_at)->format('Y-m-d') }}</cbc:IssueDate>
</cac:{{$node}}>
