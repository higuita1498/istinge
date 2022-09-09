<div class="dropdown w-100">
  <button class="btn btn-secondary dropdown-toggle" type="button" id="etiqueta-drop-{{$crm->id}}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    {{ $crm->etiqueta ? $crm->etiqueta->nombre : 'etiquetar' }}
  </button>
  <div class="dropdown-menu" aria-labelledby="etiqueta-drop-{{$crm->id}}">
    @foreach($etiquetas as $etiqueta)
    <a class="dropdown-item" href="javascript:cambiarEtiqueta({{ $etiqueta->id }}, {{ $crm->id }})">{{ $etiqueta->nombre }}</a>
    @endforeach
  </div>
</div>

<script>
    function cambiarEtiqueta(etiqueta, crm){
        alert(etiqueta, crm);
    }
</script>


