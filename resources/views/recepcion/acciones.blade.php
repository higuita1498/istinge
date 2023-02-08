{{-- <a href="#" class="btn btn-outline-info btn-icons" title="Registrar comprobante"><i class="fas fa-solid fa-id-badge"></i></a> --}}

<!-- Acuse de recibo de documentos electrónicos -->
@if ($documento->acusado == 1)
<a href="#" title="Acuse de recibo de documentos electrónicos" 
class="btn btn-outline-info btn-icons " style="background-color: #777 !important;">
    <i class="fas fa-regular fa-folder-open"></i>
</a>
@else
<a href="#" title="Acuse de recibo de documentos electrónicos" 
class="btn btn-outline-info btn-icons"
 onclick="modalDocumentos('{{$documento->uuid}}','{{$documento->documentId}}',1)">
    <i class="fas fa-regular fa-folder-open"></i>
</a>
@endif

<!-- Confirma la recepción del bien o servicio -->
@if ($documento->confirma_recepcion == 1)
<a href="#" title="Confirma la recepción del bien o servicio"
 class="btn btn-outline-info btn-icons " style="background-color: #777 !important;">
    <i class="fas fa-exchange-alt"></i></a>
@else
<a href="#" title="Confirma la recepción del bien o servicio"
 class="btn btn-outline-info btn-icons" 
 onclick="modalDocumentos('{{$documento->uuid}}','{{$documento->documentId}}',2)">
    <i class="fas fa-exchange-alt"></i></a>
    
@endif

<!-- Aceptar el documento -->
@if ($documento->acusado != 1 || $documento->confirma_recepcion != 1 || $documento->aceptado == 1 || $documento->rechazado == 1)
<a href="#" title="Aceptar el documento"
 class="btn btn-outline-info btn-icons " style="background-color: #777 !important;" >
<i class="fas fa-check"></i></a>
@else
<a href="#" title="Aceptar el documento"
 class="btn btn-outline-info btn-icons" 
onclick="modalAceptoRechazo('{{$documento->uuid}}','{{$documento->documentId}}',3)">
<i class="fas fa-check"></i></a>
@endif

<!-- Rechazar el documento -->
@if ($documento->acusado != 1 || $documento->confirma_recepcion != 1 || $documento->aceptado == 1 || $documento->rechazado == 1)
<a href="#" title="Rechazar el documento" 
class="btn btn-outline-info btn-icons " style="background-color: #777 !important;">
 <i class="fas fa-times"></i></a>
@else
<a href="#" title="Rechazar el documento" 
class="btn btn-outline-info btn-icons"
 onclick="modalAceptoRechazo('{{$documento->uuid}}','{{$documento->documentId}}',4)">
 <i class="fas fa-times"></i></a>
@endif



{{-- <a href="{{route('home')}}" class="btn btn-outline-info btn-icons" title="Ver correo original"><i class="far fa-envelope"></i></a> --}}
{{-- <a href="{{route('home')}}" class="btn btn-outline-info btn-icons" title="Descargar archivos asociados"><i class="fas fa-download"></i></a> --}}
{{-- <a href="#" class="btn btn-outline-info btn-icons" title="Eliminar"><i class="fas fa-trash-alt"></i></a> --}}