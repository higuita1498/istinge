<a href="javascript:editEtiqueta({{ $etiqueta->id}}, '{{ $etiqueta->nombre }}', '{{ $etiqueta->color}}')">editar</a>
 | 
<a href="javascript:destroyEtiqueta({{ $etiqueta->id}}, {{$etiqueta->radicados->count()}}, false)">eliminar</a>