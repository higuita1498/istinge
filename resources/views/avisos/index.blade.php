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
	
	@if(isset($_SESSION['permisos']['706']))
	<div class="row card-description mb-5">
		<div class="col-md-4 offset-md-2">
		    <a href="{{route('avisos.envio.sms')}}">
		        <div class="card text-center card-notificacion">
		            <div class="card-body">
		                <h5 class="card-title">NOTIFICACIONES POR SMS<br><i class="fas fa-mobile-alt fa-5x mt-4"></i></h5>
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
	</div>
	@endif
@endsection