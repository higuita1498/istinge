<cac:{{$node}}>
    <cbc:ID>{{$FacturaVenta->ordencompra}}</cbc:ID>
       {{-- <cbc:IssueDate>@if(isset($FacturaRelacionada)){{Carbon\Carbon::parse($FacturaRelacionada->created_at)->format('Y-m-d')}}@else{{Carbon\Carbon::parse($FacturaVenta->created_at)->format('Y-m-d')}}@endif</cbc:IssueDate> --}}</cac:{{$node}}>
