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
	        <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
	       <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
<p>Medios de pago Nequi: 3026003360 Cuenta de ahorros Bancolombia 42081411021 CC 1001912928 Ximena Herrera representante legal. Adjunte su pago para reactivar su membresía</p>
	    </div>
	@else
	@if(isset($_SESSION['permisos']['706']))

        <div class="row">
            <div class="col-12 pl-5 pr-5">
                <p>Construya el mensaje automático y utilice las variables disponibles {factura}, {empresa}, {valor}, {pagado}, {cliente}, {vencimiento} haciendo uso de las llaves.</p>
                <div class="row">
                    <div class="col-6">
                        <p>Ejemplo:</p>
                        <p>El pago fue recibido correctamente, {empresa} le agradece por su permanencia.</p>
                    </div>
                    <div class="col-6">
                        <p>Salida:</p>
                        <p>El pago fue recibido correctamente, {{ auth()->user()->empresa()->nombre }} le agradece por su permanencia.</p>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('store.avisos.automaticos') }}" method="POST">
        {{ csrf_field() }}
            <div class="row">
                <div class="col-6 p-5">
                    <label for="">Generación de factura y Pago oportuno</label>
                    <textarea class="w-100" rows="8" name="sms_factura_generada">{{ $empresa->sms_factura_generada }}</textarea>
                </div>  
                <div class="col-6 p-5">
                    <label for="">Pago recibido correctamente</label>
                    <textarea class="w-100" rows="8" name="sms_pago">{{ $empresa->sms_pago }}</textarea>
                </div> 
            </div>

            <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success float-right mt-3 mb-3 mr-5">Guardar</button>
        </form>

	@endif
	@endif
@endsection

@section('scripts')

@endsection
