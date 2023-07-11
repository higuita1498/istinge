@if ($factura->etiqueta)
<div class="dropdown">
    <a class="btn btn-sm dropdown-toggle text-white font-weight-bold" style="background-color: {{$factura->etiqueta->color->codigo ?? ''}};" href="#" role="button" id="etiqueta-{{$factura->id}}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        {{ $factura->etiqueta->nombre}}
    </a>
    <div class="dropdown-menu" aria-labelledby="etiqueta-{{$factura->id}}">
        @foreach($etiquetas as $etiqueta)
        <a class="dropdown-item" style="cursor: pointer;" onclick="cambiarEtiqueta('{{ $factura->id }}', '{{ $etiqueta->id }}')">{{ $etiqueta->nombre}}</a>
        @endforeach
    </div>
</div>
@endif