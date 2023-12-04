@if(Auth::user()->rol==8)
    @if($estatus==1)
        <a href="{{route('ingresos.create_id', ['cliente'=>$cliente, 'factura'=>$id])}}" class="btn btn-outline-primary btn-xl" title="Agregar pago"><i class="fas fa-money-bill"></i></a>
    @endif
    @if(Auth::user()->empresa()->tirilla && $estatus==0)
        <a href="{{route('facturas.tirilla', ['id' => $id, 'name'=> 'Factura No.'.$id.'.pdf'])}}" target="_blank" class="btn btn-outline-warning btn-xl"title="Imprimir tirilla"><i class="fas fa-file-invoice"></i></a>
    @endif
@else
    <a href="{{route('facturas.show',$id)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>
    <a href="{{route('facturas.imprimir',['id' => $id, 'name'=> 'Factura No. '.$codigo.'.pdf'])}}" target="_blank" class="btn btn-outline-primary btn-icons"title="Imprimir"><i class="fas fa-print"></i></a>
    @if($estatus==0)
        <a href="{{route('facturas.tirilla', ['id' => $id, 'name'=> 'Factura No.'.$id.'.pdf'])}}" target="_blank" class="btn btn-outline-warning btn-icons"title="Imprimir tirilla"><i class="fas fa-file-invoice"></i></a>
    @endif
	@if($estatus==1)
		<a href="{{route('ingresos.create_id', ['cliente'=>$cliente, 'factura'=>$id])}}" class="btn btn-outline-primary btn-icons" title="Agregar pago"><i class="fas fa-money-bill"></i></a>
		@if(($correo==0 && $emitida != 1) && isset($_SESSION['permisos']['43']))
        <a href="{{route('facturas.edit',$id)}}"  class="btn btn-outline-primary btn-icons" title="Editar"><i class="fas fa-edit"></i></a>
        @endif
        @if(isset($_SESSION['permisos']['775']))
        @if($promesa_pago==null || $promesa_pago < Carbon\Carbon::now()->format('Y-m-d'))
        <a href="javascript:modificarPromesa('{{$id}}')" class="btn btn-outline-danger btn-icons promesa" idfactura="{{$id}}" title="Promesa de Pago"><i class="fas fa-calendar"></i></a>
        @endif
        @endif
	@endif
	<form action="{{ route('factura.anular',$id) }}" method="POST" class="delete_form" style="display: none;" id="anular-factura{{$id}}">
		{{ csrf_field() }}
	</form>
	@if(isset($_SESSION['permisos']['43']))
		@if($estatus == 1)
			<button class="btn btn-outline-danger  btn-icons" type="button" title="Anular" onclick="confirmar('anular-factura{{$id}}', '¿Está seguro de que desea anular la factura?', ' ');"><i class="fas fa-minus"></i></button>
		@elseif($estatus==2)
	    	<button class="btn btn-outline-success  btn-icons" type="submit" title="Abrir" onclick="confirmar('anular-factura{{$id}}', '¿Está seguro de que desea abrir la factura?', ' ');"><i class="fas fa-unlock-alt"></i></button>
		@endif
	@endif
	@if($emailcliente)
	    @if($estatus==1)
            @if($correo==0)
		        <a href="{{route('facturas.enviar',$id)}}" class="btn btn-outline-success btn-icons" title="Enviar"><i class="far fa-envelope"></i></a>
	        @else
		        <button class="btn btn-danger btn-icons disabled" title="Factura enviada por Correo"><i class="far fa-envelope"></i></button>
	        @endif
	    @endif
	@endif
	@if($celularcliente)
	    @if($estatus==1)
           @if($mensaje==0)
		        <a href="{{route('facturas.mensaje',$id)}}" class="btn btn-outline-success btn-icons" title="Enviar SMS"><i class="fas fa-mobile-alt"></i></a>
            @else
                <a href="#" class="btn btn-danger btn-icons disabled" disabled title="SMS Enviado"><i class="fas fa-mobile-alt"></i></a>
	        @endif
	        <a href="{{route('facturas.whatsapp',$id)}}" class="btn btn-outline-success btn-icons" title="Enviar Vía WhatsApp"><i class="fab fa-whatsapp"></i></a>
	    @endif
	@endif
	@if($tipo == 2 && $emitida == 0)
	<a href="#" class="btn btn-outline-primary btn-icons" title="Emitir Factura" onclick="validateDian({{ $id }}, '{{route('xml.factura',$id)}}', '{{$codigo}}')"><i class="fas fa-sitemap"></i></a>
	@endif
	<a href="{{route('facturas.showmovimiento',$id)}}" class="btn btn-outline-info btn-icons" title="Ver movimientos"><i class="far fa-sticky-note"></i></a>
	@if(($tipo == 1 && isset($opciones_dian) && $opciones_dian == 1) && isset($_SESSION['permisos']['857']))
	<a onclick="convertirElectronica('{{$codigo}}','{{route('facturas.convertirelectronica',$id)}}')" class="btn btn-outline-info btn-icons" title="Convertir a electrónica"><i class="fas fa-exchange-alt"></i></a>
	@endif
@endif

<script>
	function convertirElectronica(codigo,url){
		Swal.fire({
        title: '¿Desea convertir la factura: ' + codigo + ' a electrónica?',
        text: "No podrás retroceder esta acción",
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: 'Cancelar',
        confirmButtonText: 'Si, convertir'
    }).then((result) => {
        if (result.value) {
            window.location.href = url;
        }
    })
	}
</script>
