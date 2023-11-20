@extends('layouts.app')

@section('boton')

@endsection

@section('content')
	@if(Session::has('success'))
		<div class="alert alert-success" >
			{{Session::get('success')}}
		</div>

		<script type="text/javascript">
			setTimeout(function(){
			    $('.alert').hide();
			    $('.active_table').attr('class', ' ');
			}, 5000);
		</script>
	@endif
	@if(Session::has('danger'))
		<div class="alert alert-danger" >
			{{Session::get('danger')}}
		</div>

		<script type="text/javascript">
			setTimeout(function(){
			    $('.alert').hide();
			    $('.active_table').attr('class', ' ');
			}, 10000);
		</script>
	@endif

	<style>
	    .card-notificacion .card-title:hover {
	        color: #fff;
	    }
	</style>

	@if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">Integra Colombia: Suscripción Vencida</h4>
	       <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
	    </div>
	@else
	@if(isset($_SESSION['permisos']['706']))
	<div class="row card-description mb-5">
		<div class="col-md-4">
		    <a href="{{route('avisos.envio.sms')}}">
		        <div class="card text-center card-notificacion">
		            <div class="card-body">
		                <h5 class="card-title">NOTIFICACIONES POR SMS<br><i class="fas fa-mobile fa-5x mt-4"></i></h5>
		            </div>
		        </div>
		    </a>
		</div>
		<div class="col-md-4 text-center">
		    <a href="{{route('avisos.envio.email')}}">
		        <div class="card text-center card-notificacion">
		            <div class="card-body">
		                <h5 class="card-title">NOTIFICACIONES POR EMAIL<br><i class="fas fa-at fa-5x mt-4"></i></h5>
		            </div>
		        </div>
		    </a>
		</div>
		<div class="col-md-4">
		    <a href="javascript:void(0)" data-toggle="modal" data-target="#modalSMS" onclick="limpiar('form_sms'); dnone('div_contenido'); dnone('div_footer');">
		        <div class="card text-center card-notificacion">
		            <div class="card-body">
		                <h5 class="card-title">NOTIFICACIÓN SMS PERSONALIZADA<br><i class="fas fa-mobile-alt fa-5x mt-4"></i></h5>
		            </div>
		        </div>
		    </a>
		</div>
	</div>

	<div class="modal fade" id="modalSMS" tabindex="-1" role="dialog" aria-labelledby="modalSMSLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalSMSLabel">Enviar Notificación SMS</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="form_sms" method="POST" action="{{ route('avisos.envio_personalizado') }}" role="form" onsubmit="event.preventDefault();">
                    	@csrf
	                    <p class="text-center">Seleccione el cliente al cual desea enviar la notificación SMS</p>
	                    <div class="col-md-12">
	                        <select title="Clientes" class="form-control selectpicker" id="cliente_sms" name="cliente_sms" data-size="5" data-live-search="true">
	                            @foreach ($clientes as $cliente)
	                            <option value="{{ $cliente->id }}">{{ $cliente->nombre }} {{ $cliente->apellidoS() }} - {{ $cliente->nit }}</option>
	                            @endforeach
	                        </select>
	                    </div>
	                    <span class="d-none" id="div_contenido">
	                        <div class="col-md-12 mt-3" id="div_contenido_user">

	                        </div>
	                        <div class="col-md-12 mt-3">
	                            <textarea class="form-control" maxlength="140" required rows="5" id="text_sms" name="text_sms" onkeyup="contarCaracteres(this.value);"></textarea>
	                            <input type="hidden" name="numero_sms" id="numero_sms">
	                            <p id="charNum">0/140</p>
	                        </div>
	                    </span>
                    </form>
                </div>
                <div class="modal-footer d-none" id="div_footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                    <a href="javascript:envio_personalizado()" class="btn btn-success">Enviar SMS</a>
                </div>
            </div>
        </div>
    </div>
	@endif
	@endif
@endsection

@section('scripts')
<script>
    $("#form_sms").submit(function () {
        return false;
    });

    $(document).ready(function() {
        $('#cliente_sms').on('change',function() {
            getClienteSMS(this.value);
        });
    });

    function envio_personalizado(){
    	if($('#cliente_sms').val().length > 0 && $('#text_sms').val().length > 0 && $('#numero_sms').val().length > 0){
    		$.post($("#form_sms").attr('action'), $("#form_sms").serialize(), function(data) {
    			if (data.success == true) {
    				$('#modalSMS').modal('hide');
    				$('#form_sms').trigger("reset");
    			}
    			swal(data.title, data.message, data.type);
    		}, 'json');
    	}else{
    		swal('ERROR', 'Información Incompleta', 'error');
    		return false;
    	}
    }
</script>
@endsection
