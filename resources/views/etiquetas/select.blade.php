<div class="dropdown w-100">
  <button style="background-color: {{ $crm->etiqueta ? $crm->etiqueta->color : '' }}" class="btn btn-secondary dropdown-toggle w-100" type="button" id="etiqueta-drop-{{$crm->id}}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    {{ $crm->etiqueta ? $crm->etiqueta->nombre : 'etiquetar' }}
  </button>
  <div class="dropdown-menu w-100" aria-labelledby="etiqueta-drop-{{$crm->id}}">
    @foreach($etiquetas as $etiqueta)
    <a class="dropdown-item" href="javascript:cambiarEtiqueta({{ $etiqueta->id }}, {{ $crm->id }})">{{ $etiqueta->nombre }}</a>
    @endforeach
  </div>
</div>

<script>
    function cambiarEtiqueta(etiqueta, crm){
       $.get('{{URL::to('/')}}/empresa/crm/cambiar-etiqueta/'+etiqueta+'/'+crm, function(response){
            $('#etiqueta-drop-'+crm).html(response.nombre);
            $('#etiqueta-drop-'+crm).css('background-color', response.color);
       });
    }
</script>


