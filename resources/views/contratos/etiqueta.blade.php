<div class="dropdown w-100">
    <button style="background-color: {{ $contrato->etiqueta ? $contrato->etiqueta->color : '#e5e5e5' }} !important"
            class="btn btn-secondary dropdown-toggle w-100"
            type="button"
            id="etiqueta-drop-{{$contrato->id}}"
            data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false">
        {{ $contrato->etiqueta ? $contrato->etiqueta->nombre : 'etiquetar' }}
    </button>
    <div class="dropdown-menu w-100" aria-labelledby="etiqueta-drop-{{$contrato->id}}" style="max-height:200px; overflow: auto">
        <a class="dropdown-item" href="javascript:cambiarEtiqueta(0, {{ $contrato->id }})">Eliminar etiqueta</a>
        @foreach($etiquetas as $etiqueta)
            <a class="dropdown-item" href="javascript:cambiarEtiqueta({{ $etiqueta->id }}, {{ $contrato->id }})">{{ $etiqueta->nombre }}</a>
        @endforeach
    </div>
</div>

<script>
    function cambiarEtiqueta(etiqueta, contrato){
        $.get('{{URL::to('/')}}/empresa/contratos/cambiar-etiqueta/'+etiqueta+'/'+contrato, function(response){
            let button = $('#etiqueta-drop-'+contrato);

            if (response.nombre && response.color) {
                button.html(response.nombre);
                button.css('background-color', response.color);
            } else {
                button.html('etiquetar');
                button.css('background-color', '#e5e5e5');
            }
        });
    }
</script>