<cac:PaymentMeans>
    <cbc:ID>1</cbc:ID> {{--TIPO DE PAGO CREDITO O CONTADO--}}
    <cbc:PaymentMeansCode>10</cbc:PaymentMeansCode> {{--METODO DE PAGO EFECTIVO--}}
    {{--@if ($ == '2')
        <cbc:PaymentDueDate>{{FECHA EN LA Q SE EFECTUARA EL PAGO}}</cbc:PaymentDueDate>
    @endif--}} {{--SI EL METODO DE PAGO ES A CREDITO SE HABILITA ESTA CONDIFCION--}}
    <cbc:PaymentID>Efectivo</cbc:PaymentID>
</cac:PaymentMeans>
