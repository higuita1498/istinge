$(document).ready(function(){
	//validateTechnicalKeyDian();
	if (window.location.pathname.split("/")[1] === "software") {
        if (window.location.pathname.split("/")[3] === "radicados" || window.location.pathname.split("/")[3] === "asignaciones") {
			return false;
	    }else{
	        notificacion();
	    }
    }else{
        if (window.location.pathname.split("/")[2] === "radicados") {
			return false;
	    }else{
	        notificacion();
	    }
    }
    
    if (window.location.pathname.split("/")[1] === "software") {
        if (window.location.pathname.split("/")[3] === "wifi") {
			return false;
	    }else{
	        notificacionWifi();
	    }
    }else{
        if (window.location.pathname.split("/")[2] === "wifi") {
			return false;
	    }else{
	        notificacionWifi();
	    }
    }
    
    if (window.location.pathname.split("/")[1] === "software") {
        if (window.location.pathname.split("/")[3]) {
			return false;
	    }else{
	        notificacionPing();
	    }
    }else{
        if (window.location.pathname.split("/")[2]) {
			return false;
	    }else{
	        notificacionPing();
	    }
    }
    $('.precio').mask('000.000.000.000.000', {reverse: true});
})

function validateTechnicalKeyDian()
{
	$.ajax({
		url: '/validatetechnicalkeydian',
		headers:{'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
		method: 'post',
		dataType: 'json',

		success: function(data)
		{
			if (data == 1) {
				Swal.fire(
					'Estas listo para facturar electrónicamente!',
					'Ahora puedes emitir facturas de venta, notas crédito y notas débito!',
					'success'
					);
			}
		}
	});
}