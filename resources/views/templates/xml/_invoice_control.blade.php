<sts:InvoiceControl>
    <sts:InvoiceAuthorization>{{$ResolucionNumeracion->nroresolucion}}</sts:InvoiceAuthorization>
    <sts:AuthorizationPeriod>
        <cbc:StartDate>{{$ResolucionNumeracion->desde}}</cbc:StartDate>
        <cbc:EndDate>{{$ResolucionNumeracion->hasta}}</cbc:EndDate>
    </sts:AuthorizationPeriod>
    <sts:AuthorizedInvoices>
        @if ($ResolucionNumeracion->prefijo)
            <sts:Prefix>{{$ResolucionNumeracion->prefijo}}</sts:Prefix>
        @endif
        <sts:From>{{$ResolucionNumeracion->inicioverdadero}}</sts:From>
        <sts:To>{{$ResolucionNumeracion->final}}</sts:To>
    </sts:AuthorizedInvoices>
</sts:InvoiceControl>
