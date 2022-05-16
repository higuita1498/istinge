$(document).ready(function(){
	//validateTechnicalKeyDian();

	if (window.location.pathname === "/empresa") {
		notificacionRadicado();
		notificacionPing();
		notificacionWifi();
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