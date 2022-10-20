@extends('layouts.app')
@section('content')
    @if(Session::has('success'))
        <div class="alert alert-success alerta" style="margin-left: 2%;margin-right: 2%;">
	    {{Session::get('success')}}
        </div>
        <script type="text/javascript">
            setTimeout(function() {
                $('.alerta').hide();
                $('.active_table').attr('class', ' ');
            }, 5000);
        </script>
    @endif

    @if(Session::has('danger'))
        <div class="alert alert-danger" alerta style="margin-left: 2%;margin-right: 2%;">
	    {{Session::get('danger')}}
        </div>
        <script type="text/javascript">
            setTimeout(function() {
                $('.alerta').hide();
                $('.active_table').attr('class', ' ');
            }, 5000);
        </script>
    @endif

    @if(auth()->user()->modo_lectura())
	    <div class="alert alert-warning text-left" role="alert">
	        <h4 class="alert-heading text-uppercase">NetworkSoft: Suscripción Vencida</h4>
	        <p>Si desea seguir disfrutando de nuestros servicios adquiera alguno de nuestros planes.</p>
	    </div>
	@endif

	<form method="POST" action="{{ route('integracion-gmaps.update', $servicio->id) }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-banco">
	    @csrf
	    <input name="_method" type="hidden" value="PATCH">
	    <input name="id" type="hidden" value="{{ $servicio->id }}">
	    <div class="row">
	    	<div class="col-md-8 offset-md-2 form-group">
	    		<h5 class="text-center mb-3">Las coordenadas que se indiquen en este mapa, serán las coordenadas por defecto en el registro y edición de los contratos.</h5>
	    		<div id="us2" style="width: 100%; height: 350px; position: relative; overflow: hidden;"></div>
	    	</div>
	    	<div class="row d-none">
		        <div class="col-md-12 form-group">
		            <label class="control-label">Latitud</label>
		            <input type="text" class="form-control" id="latitude" name="latitude" value="{{$servicio->latitude}}" maxlength="500">
		            <br>
		            <label class="control-label">Longitud</label>
		            <input type="text" class="form-control" id="longitude" name="longitude" value="{{$servicio->longitude}}" maxlength="500">
		        </div>
		        <span class="d-none">
		        	Location: <input type="text" id="us2-address" style="width: 200px"/>
		        	Radius: <input type="text" id="us2-radius"/>
		        </span>
		    </div>

	    </div>
	    <hr>
	    <div class="row" >
	        <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
	            <a href="{{route('configuracion.index')}}" class="btn btn-outline-secondary">Cancelar</a>
	            @if(auth()->user()->modo_lectura())
	            @else
	            <button type="submit" id="submitcheck" onclick="submitLimit(this.id)" class="btn btn-success">Guardar</button>
	            @endif
	        </div>
	    </div>
	</form>
@endsection

@section('scripts')
    <script>
        $('#us2').locationpicker({
            location: {
                latitude: {{ $servicio->latitude }},
                longitude: {{ $servicio->longitude }}
            },
            zoom: 6,
            radius: 300,
            inputBinding: {
                latitudeInput: $('#latitude'),
                longitudeInput: $('#longitude'),
                radiusInput: $('#us2-radius'),
                locationNameInput: $('#us2-address')
            },
            mapTypeId: google.maps.MapTypeId.roadmap,
        });
    </script>
@endsection