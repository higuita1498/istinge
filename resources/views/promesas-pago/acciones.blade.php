@if(isset($_SESSION['permisos']['776']))
<a href="{{route('promesas.imprimir', ['id' => $id, 'name'=> 'Promesa No. '.$nro.'.pdf'])}}" target="_blank" class="btn btn-outline-danger btn-icons"title="Imprimir"><i class="fas fa-print"></i></a>
@endif