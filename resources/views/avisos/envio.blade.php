@extends('layouts.app')
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
	<form method="POST" action="{{ route('avisos.envio_aviso') }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-retencion">
	    @csrf
	    <input type="hidden" value="{{$opcion}}" name="type">
	    <div class="row">
	        <div class="col-md-3 form-group">
	            <label class="control-label">Plantilla <span class="text-danger">*</span></label>
        	    <select name="plantilla" id="plantilla" class="form-control selectpicker " title="Seleccione" data-live-search="true" data-size="5" required>
        	        @foreach($plantillas as $plantilla)
        	        <option {{old('plantilla')==$plantilla->id?'selected':''}} value="{{$plantilla->id}}">{{$plantilla->title}}</option>
        	        @endforeach
        	    </select>
        	    <span class="help-block error">
        	        <strong>{{ $errors->first('plantilla') }}</strong>
        	    </span>
        	</div>

			@if(isset($servidores))
			<div class="col-md-3 form-group">
	            <label class="control-label">Servidor<span class="text-danger"></span></label>
        	    <select name="servidor" id="servidor" class="form-control selectpicker " onchange="refreshClient()" title="Seleccione" data-live-search="true" data-size="5">
        	        @foreach($servidores as $servidor)
        	        <option {{old('servidor')==$servidor->id?'selected':''}} value="{{$servidor->id}}">{{$servidor->nombre}}</option>
        	        @endforeach
        	    </select>
        	    <span class="help-block error">
        	        <strong>{{ $errors->first('servidor') }}</strong>
        	    </span>
        	</div>
			@endif

			@if(isset($gruposCorte))
			<div class="col-md-3 form-group">
	            <label class="control-label">Grupo corte<span class="text-danger"></span></label>
        	    <select name="corte" id="corte" class="form-control selectpicker" onchange="refreshClient()" title="Seleccione" data-live-search="true" data-size="5">
        	        @foreach($gruposCorte as $corte)
        	        <option {{old('corte')==$corte->id?'selected':''}} value="{{$corte->id}}">{{$corte->nombre}}</option>
        	        @endforeach
        	    </select>
        	    <span class="help-block error">
        	        <strong>{{ $errors->first('corte') }}</strong>
        	    </span>
        	</div>
			@endif

        	<div class="col-md-3 form-group">
	            <label class="control-label">Barrio</label>
        	    <input class="form-control" type="text" name="barrio" id="barrio">
        	    <span class="help-block error">
        	        <strong>{{ $errors->first('barrio') }}</strong>
        	    </span>
        	</div>
        	
        	<div class="col-md-5 form-group {{ $id ? 'd-none':'' }}">
        	    <label class="control-label">Clientes <span class="text-danger">*</span></label>
        	    <div class="btn-group btn-group-toggle" data-toggle="buttons">
        	        <label class="btn btn-success">
        	            <input type="radio" name="options" id="radio_1" onchange="chequeo();"> Habilitados
        	        </label>
        	        <label class="btn btn-danger">
        	            <input type="radio" name="options" id="radio_2" onchange="chequeo();"> Deshabilitados
        	        </label>
        	        <label class="btn btn-secondary">
        	            <input type="radio" name="options" id="radio_3" onchange="chequeo();"> Manual
        	        </label>
        	    </div>
        	</div>
        	
        	<div class="col-md-3 form-group" id="seleccion_manual">
	            <label class="control-label">Selección manual de clientes</label>
        	    <select name="contrato[]" id="contrato_sms" class="form-control selectpicker" title="Seleccione" data-live-search="true" data-size="5" required multiple data-actions-box="true" data-select-all-text="Todos" data-deselect-all-text="Ninguno">
        	        @php $estados=\App\Contrato::tipos();@endphp
        	        @foreach($estados as $estado)
        	        <optgroup label="{{$estado['nombre']}}">
        	            @foreach($contratos as $contrato)
        	                @if($contrato->state==$estado['state'])
        	                    <option class="{{$contrato->state}} grupo-{{ $contrato->grupo_corte()->id ?? 'no' }} servidor-{{ $contrato->servidor()->id ?? 'no' }}" value="{{$contrato->id}}" {{$contrato->client_id==$id?'selected':''}}>{{$contrato->c_nombre}} {{ $contrato->c_apellido1 }} {{ $contrato->c_apellido2 }} - {{$contrato->c_nit}} (contrato: {{ $contrato->nro }})</option>
        	                @endif
        	            @endforeach
        	        </optgroup>
        	        @endforeach
        	    </select>
        	    <span class="help-block error">
        	        <strong>{{ $errors->first('cliente') }}</strong>
        	    </span>
        	</div>

			<div class="col-md-3">
				<div class="form-check form-check-inline d-flex p-3">
					<input class="form-check-input" type="checkbox" id="isAbierta" name="isAbierta" value="true">
					<label class="form-check-label" for="isAbierta"  style="font-weight:bold">Solo facturas abiertas</label>
				</div>
			</div>

       </div>
	    
	   <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>
	   
	   <hr>
	   
	   <div class="row" >
	       <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
	           <a href="{{route('avisos.index')}}" class="btn btn-outline-secondary">Cancelar</a>
	           <button type="submit" id="submitcheck" onclick="submitLimit(this.id); alert_swal();" class="btn btn-success">Guardar</button>
	       </div>
	   </div>
    </form>
@endsection

@section('scripts')
<script type="text/javascript">
	window.addEventListener('load', function() {
		$('#barrio').on('keyup',function(e) {
        	if(e.which > 32 || e.which == 8) {
        		if($('#barrio').val().length > 3){
        			if (window.location.pathname.split("/")[1] === "software") {
        				var url = '/software/getContractsBarrio/'+$('#barrio').val();
        			}else{
        				var url = '/getContractsBarrio/'+$('#barrio').val();
        			}

        			cargando(true);

        			$.ajax({
        				url: url,
        				headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
        				method: 'get',
        				success: function (data) {
        					console.log(data);
        					cargando(false);

        					var $select = $('#contrato_sms');
        					$select.empty();
        					$.each(data.data,function(key, value){
        						var apellidos = '';
        						if(value.apellido1){
        							apellidos += ' '+value.apellido1;
        						}
        						if(value.apellido2){
        							apellidos += ' '+value.apellido2;
        						}
        						$select.append('<option value='+value.id+' class="'+value.state+'">'+value.nombre+' '+apellidos+' - '+value.nit+'</option>');
        					});
        					$select.selectpicker('refresh');
							refreshClient();
        				},
        				error: function(data){
        					cargando(false);
        				}
        			});
        		}
        		return false;
        	}
        });
    });

    function chequeo(){
        if($("#radio_1").is(":checked")){
            $(".enabled").attr('selected','selected');
            $(".disabled").removeAttr("selected");
            //$("#seleccion_manual").addClass('d-none');
			refreshClient('enabled');
        }else if($("#radio_2").is(":checked")){
            $(".disabled").attr('selected','selected');
            $(".enabled").removeAttr("selected");
            //$("#seleccion_manual").addClass('d-none');
			refreshClient('disabled');
        }else if($("#radio_3").is(":checked")){
            //$("#seleccion_manual").removeClass('d-none');
        }
        $("#contrato").selectpicker('refresh');
    }

    function alert_swal(){
    	Swal.fire({
    		type: 'info',
    		title: 'ENVIANDO NOTIFICACIONES',
    		text: 'Este proceso puede demorar varios minutos',
    		showConfirmButton: false,
    	})
    }

	function refreshClient(estadoCliente = null){

		let grupoCorte = $('#corte').val();
		let servidor = $('#servidor').val();


		if(estadoCliente){

			if(grupoCorte && servidor){
				options = $(`.servidor-${servidor}.grupo-${grupoCorte}.${estadoCliente}`);
			}else{
				if(servidor){
					options = $(`.servidor-${servidor}.${estadoCliente}`);
				}
				if(grupoCorte){
					options = $(`.grupo-${servidor}.${estadoCliente}`);
				}
			}

		}else{
			if(grupoCorte && servidor){
				options = $(`.servidor-${servidor}.grupo-${grupoCorte}`);
			}else{
				if(servidor){
					options = $(`#contrato_sms option[class*="servidor-${servidor}"]`);
				}
				if(grupoCorte){
					 options = $(`#contrato_sms option[class*="grupo-${grupoCorte}"]`);
				}
			}
		}
		
		$("#contrato_sms option:selected").prop("selected", false);
		$("#contrato_sms option:selected").removeAttr("selected");

		options.attr('selected', true);
		options.prop('selected', true);

		$('#contrato_sms').selectpicker('refresh');

	}

</script>
@endsection