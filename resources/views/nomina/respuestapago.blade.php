@extends('layouts.app')

@section('content')

<style>
    body > div.container-scroller > div > div > div.content-wrapper > div > div > div > div.row{
        display: none;
    }
</style>

<div class="section3">
	<center>
		<div class="enlamitad" style="margin: 2%;">
			<div class="barra-princi-pagos">
				<h1>
					Respuesta de Pago WOMPI
				</h1>
			</div>
			<div class="sections-in-gral-payment">
				<div class="row">
					<div class="col-md-8 offset-md-2 my-5">
					    <div class="table-responsive">
					        <table class="table table-bordered">
					            <tbody>
                                    <tr>
                                        <th class="text-uppercase text-center barra-princi-pagos" colspan="2" style="border-radius: 0;">Detalle de la Transacción</th>
                                    </tr>
                                    <tr>
                                        <th class="bold w-50">Transacción #</th>
                                        <td class="w-50" id="referencia"></td>
                                    </tr>
                                    <tr>
                                        <th class="bold w-50">Monto</th>
                                        <td class="w-50" id="monto"></td>
                                    </tr>
                                    <tr>
                                        <th class="bold w-50">Referencia</th>
                                        <td class="w-50" id="reference"></td>
                                    </tr>
                                    <tr>
                                        <th class="bold w-50">Estado</th>
                                        <td class="w-50" class="" id="status"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
				</div>
			</div>
		</div>
	</center>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
<script>
    $(document).ready(function(){
        cargando(true);
        wompi = 'https://production.wompi.co/v1/transactions/<?=$_GET['id'];?>';
        $.ajax({
            url: wompi,
            dataType: 'json',
            success: function(data) {
                console.log(data);
                $("#referencia").text(data.data.id);
                $("#monto").text(data.data.amount_in_cents/100+' COP');
                $("#reference").text(data.data.reference);
                $("#status").text(data.data.status);

                if(data.data.status == "APPROVED"){
                	var _token = $('meta[name="csrf-token"]').attr('content');
                    $.ajax({
                        type : 'POST',
                        url  : 'store_respuesta_wompi',
                        data : {_token: _token,reference:data.data.reference, amount:(data.data.amount_in_cents/100), status:data.data.status, transactionId:data.data.id,tipo_pago:data.data.payment_method_type},
                        success : function(data){
                            cargando(false);
                            Swal.fire({
                                title: data.mensaje,
                                type: data.type,
                                showCancelButton: false,
                                showConfirmButton: false,
                                cancelButtonColor: '#d33',
                                cancelButtonText: 'Aceptar',
                                timer: 10000
                            });
                        }
                    });
                }else if(data.data.status == "VOIDED"){
                    Swal.fire({
                        title: 'Transacción Wompi Anulada',
                        type: 'error',
                        showCancelButton: false,
                        showConfirmButton: false,
                        cancelButtonColor: '#d33',
                        cancelButtonText: 'Aceptar',
                        timer: 10000
                    });
                }else if(data.data.status == "DECLINED"){
                    Swal.fire({
                        title: 'Transacción Wompi Rechazada',
                        type: 'error',
                        showCancelButton: false,
                        showConfirmButton: false,
                        cancelButtonColor: '#d33',
                        cancelButtonText: 'Aceptar',
                        timer: 10000
                    });
                }else if(data.data.status == "PENDING"){
                    Swal.fire({
                        title: 'Transacción Wompi Pendiente',
                        type: 'error',
                        showCancelButton: false,
                        showConfirmButton: false,
                        cancelButtonColor: '#d33',
                        cancelButtonText: 'Aceptar',
                        timer: 10000
                    });
                }else{
                    Swal.fire({
                        title: 'Error Desconocido',
                        type: 'error',
                        showCancelButton: false,
                        showConfirmButton: false,
                        cancelButtonColor: '#d33',
                        cancelButtonText: 'Aceptar',
                        timer: 10000
                    });
                }
                cargando(false);
            },
            error: function(data){
            	cargando(false);
            	Swal.fire({
            		title: data.responseJSON.error.reason,
            		type: 'error',
            		showCancelButton: false,
            		showConfirmButton: true,
            		cancelButtonColor: '#d33',
            		cancelButtonText: 'Aceptar',
            		timer: 10000
            	});
            }
        });
        return false;
    });
</script>
@endsection