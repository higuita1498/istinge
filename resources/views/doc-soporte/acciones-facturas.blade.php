<a href="{{route('facturasp.showid',$id)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></i></a>
@php
$sw = 0;
$empresa = Auth::user()->empresa;
@endphp

@if(env('APP_URL') == "https://gestordepartes.net" && $empresa == 128)
<a href="{{route('facturasp.edit', $id)}}" class="btn btn-outline-secondary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
@php $sw = 1; @endphp
@endif

@if($tipo == 1 && $estatus == 1 ||
$estatus == 5 && $tipo == 1 ||
env('APP_URL') == "https://gestordepartes.net" &&
$empresa == 128)

@if ($modo == 2)
<a href="{{route('pagos-remisiones-proveedores.create', ['cliente'=>$proveedor, 'factura'=>$id])}}" class="btn btn-outline-primary btn-icons" title="Agregar pago"><i class="fas fa-money-bill"></i></a>
@else
<a href="{{route('pagos.create_id', ['cliente'=>$proveedor, 'factura'=>$id])}}" class="btn btn-outline-primary btn-icons" title="Agregar pago"><i class="fas fa-money-bill"></i></a>
@endif

@if($pagos_anulados == true && $estatus == 1 && $sw == 0)
<a href="{{route('facturasp.edit', $id)}}" class="btn btn-outline-secondary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
@endif

<a href="{{route('facturasp.imprimir.nombre', ['id' => $id, 'name'=> 'Factura Proveedor No. '.$nro.'.pdf'])}}" class="btn btn-outline-primary btn-icons d-none d-lg-inline-flex" title="Imprimir" target="_blank"><i class="fas fa-print"></i></a>
<a href="{{route('downloadFacturasPApp', $id)}}" class="btn btn-outline-primary btn-icons d-lg-none" title="Descargar"><i class="fas fa-download"></i></a>

@if(count($gastos) == 0 && $estatus == 1)
<button class="btn btn-outline-danger  btn-icons" type="submit" title="Eliminar" onclick="confirmar('eliminar-factura{{$id}}', '¿Estas seguro que deseas eliminar la factura de compra?', 'Se borrara de forma permanente');"><i class="fas fa-times"></i></button>
@endif

<form action="{{ route('facturasp.destroy',$id) }}" method="post" class="delete_form" style="margin:  0;display: inline-block;" id="eliminar-factura{{$id}}">
    @csrf
    <input name="_method" type="hidden" value="DELETE">
</form>
@else
<a href="{{route('facturasp.imprimir.nombre', ['id' => $id, 'name'=> 'Factura Proveedor No. '.$nro.'.pdf'])}}" class="btn btn-outline-primary btn-icons" title="Imprimir"><i class="fas fa-print"></i></a>
@endif

@if(Auth::user()->empresaObj->form_fe == 1 && $emitida == 0 && Auth::user()->empresaObj->estado_dian == 1 && Auth::user()->empresaObj->technicalkey != null && $codigo_dian != null)
    <a href="#" class="btn btn-outline-primary btn-icons" title="Emitir Factura" onclick="validateDian({{ $id }}, '{{route('xml.facturaproveedor',$id)}}', '{{$codigo_dian}}', {{0}}, {{1}})"><i class="fas fa-sitemap"></i></a>
@endif