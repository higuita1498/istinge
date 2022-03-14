@extends('layouts.app')
@section('content')
	@if(Session::has('success'))
      <div class="alert alert-success" >
        {{Session::get('success')}}
      </div>

      <script type="text/javascript">
        setTimeout(function(){
        }, 5000);
      </script>
    @endif


    @if(Session::has('modalFe'))
        @if(Session::get('modalFe') == 1)
            <script type="text/javascript">
            setTimeout(function(){
                $('#modalFe').modal('show');
            }, 7000);
            </script>
        @endif

    @endif

	<form method="POST" action="{{ route('configuracion.empresa.store') }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-empresa" enctype="multipart/form-data">
  		{{ csrf_field() }}

		<div class="row">
			<div class="form-group col-md-4">
	  			<label class="control-label">Tipo de Identificación <span class="text-danger">*</span></label>
	  			<select class="form-control selectpicker" onchange="searchDV(this.value)" name="tip_iden" id="tip_iden" required="" title="Seleccione">
	  				@foreach($identificaciones as $identificacion)
                  		<option {{$empresa->tip_iden==$identificacion->id?'selected':''}} value="{{$identificacion->id}}">{{$identificacion->identificacion}}</option>
	  				@endforeach
                </select>
				<span class="help-block error">
		        	<strong>{{ $errors->first('tip_iden') }}</strong>
		        </span>
			</div>

			<div class="form-group col-md-3">
	  			<label class="control-label">Identificación <span class="text-danger">*</span></label>
				<input type="text" class="form-control" name="nit" id="nit" required="" maxlength="20" value="{{$empresa->nit}}">
				<span class="help-block error">
					<strong>{{ $errors->first('nit') }}</strong>
				</span>
			</div>
			<div class="form-group col-md-1" style="display: none;" id="dvnit">
			<label class="control-label">DV <span class="text-danger">*</span></label>
			<input type="text" class="form-control" name="dv" id="dv" disabled required="" maxlength="20" value="">
			<input type="hidden" name="dvoriginal" id="dvoriginal" value="">
			<span class="help-block error">
				<strong>{{ $errors->first('dv') }}</strong>
			</span>
		</div>
			<div class="form-group col-md-4">
				<label class="control-labe">Tipo de Persona <span class="text-danger">*</span></label>
				<div class="row">
					<div class="col-sm-6">
					<div class="form-radio">
						<label class="form-check-label">
						<input type="radio" class="form-check-input" name="tipo_persona" id="tipo_persona1" value="n" @if($empresa->tipo_persona=='n') checked="" @else checked @endif> Natural
						<i class="input-helper"></i></label>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="form-radio">
						<label class="form-check-label">
						<input type="radio" class="form-check-input" name="tipo_persona" id="tipo_persona" value="j"  @if($empresa->tipo_persona=='j') checked="" @endif> Jurídica
						<i class="input-helper"></i></label>
					</div>
				</div>
				</div>
			</div>
		</div>

		<div class="row">

			<div class="form-group col-md-7">
	  			<label class="control-label">Nombre <span class="text-danger">*</span></label>
				<input type="text" class="form-control" name="nombre" id="nombre" required="" maxlength="200"  value="{{$empresa->nombre}}">
				<span class="help-block error">
		        	<strong>{{ $errors->first('nombre') }}</strong>
		        </span>
			</div>
  			<div class="form-group col-md-5">
	  			<label class="control-label">Sitio web</label>
				<input type="text" class="form-control" id="web" name="web" maxlength="200" value="{{$empresa->web}}">
				<span class="help-block error">
		        	<strong>{{ $errors->first('web') }}</strong>
		        </span>
			</div>
            {{-- <div class="form-group col-md-5 offset-7">
                <label class="control-label">Campo de texto para el formato de impresión.</label>
                <a><i data-tippy-content="<img src='https://gestordepartes.net/images/tipofactura.png'>" class="icono far fa-question-circle"></i></a>
                <input type="text" class="form-control" name="tipofactura" maxlength="40" value="{{$empresa->tipo_fac}}">
            </div> --}}
		</div>

		<div class="row">

		<div class="form-group col-md-3">
			<label class="control-label">País</label>
			<select class="form-control selectpicker" name="pais" id="pais" required="" title="Seleccione" data-live-search="true" data-size="5" onchange="validateCountry(this.value)">
				@foreach($paises as $pais)
				<option value="{{$pais->codigo}}" {{ $empresa->fk_idpais == $pais->codigo ? 'selected' : $pais->codigo == 'CO' ? 'selected' : '' }}>{{$pais->nombre}}</option>
				@endforeach
			</select>
		</div>

		<div class="form-group col-md-3" id="validatec1">
			<label class="control-label">Departamento</label>
			<select class="form-control selectpicker" name="departamento" id="departamento" required title="Seleccione" data-live-search="true" data-size="5" onchange="searchMunicipality(this.value)">
				@foreach($departamentos as $departamento)
				<option value="{{ $departamento->id }}"
					{{ $empresa->fk_iddepartamento == $departamento->id ? 'selected' : '' }}
					>{{ $departamento->nombre }}</option>
				@endforeach
			</select>
		</div>

		<div class="form-group col-md-3" id="validatec2">
			<label class="control-label">Municipio</label>
			<select class="form-control selectpicker" name="municipio" id="municipio" required="" title="Seleccione" data-live-search="true" data-size="5" required>
				<option selected value="{{ $empresa->fk_idmunicipio }}">  {{ $empresa->municipio()->nombre }}</option>
			</select>
		</div>

		<div class="form-group col-md-3" id="validatec3">
			<label class="control-label">Código Postal</label>
			<a><i data-tippy-content="Si desconoces tu código postal <a target='_blank' href='http://visor.codigopostal.gov.co/472/visor/'>haz click aquí</a>" class="icono far fa-question-circle"></i></a>
			<input type="text" class="form-control" id="cod_postal" name="cod_postal" maxlength="200"  value="{{$empresa->cod_postal}}" required>
		</div>
	</div>

  		<div class="row">

			<div class="form-group col-md-5">
	  			<label class="control-label">Dirección <span class="text-danger">*</span></label>
	  			<input type="text" class="form-control" id="direccion" name="direccion" required="" value="{{$empresa->direccion}}">
				<span class="help-block error">
					<strong>{{ $errors->first('direccion') }}</strong>
				</span>
			</div>
  			<div class="form-group col-md-3">
	  			<label class="control-label">Teléfono <span class="text-danger">*</span></label>
	  			<div class="row">
	  				<div class="col-md-4 nopadding ">
	  					<select class="form-control selectpicker prefijo" name="pref" id="pref" required="" title="Cod" data-size="5" data-live-search="true">
			  				@foreach($prefijos as $prefijo)
		                  		<option @if($empresa->telefono) {{'+'.$prefijo->phone_code==$empresa->telef('pref')?'selected':''}}  @endif

		                  		 	data-icon="flag-icon flag-icon-{{strtolower($prefijo->iso2)}}"

		                  		 value="+{{$prefijo->phone_code}}" title="+{{$prefijo->phone_code}}" data-subtext="+{{$prefijo->phone_code}}">{{$prefijo->nombre}}</option>
			  				@endforeach
		                </select>
	  				</div>
	  				<div class="col-md-8" style="padding-left:0;">
	  					<input type="text" class="form-control" id="telefono" name="telefono" required="" maxlength="15" value="{{$empresa->telef()}}">
	  				</div>
	  			</div>
				<span class="help-block error">
		        	<strong>{{ $errors->first('telefono') }}</strong>
		        </span>
			</div>


			<div class="form-group col-md-4">
	  			<label class="control-label" for="email">Correo Electrónico <span class="text-danger">*</span></label>
				<input type="email" class="form-control" id="email" name="email" required="" data-error="Dirección de correo electrónico invalida" maxlength="100"  value="{{$empresa->email}}">
				<div class="help-block error with-errors"></div>
				<span class="help-block error">
					<strong>{{ $errors->first('email') }}</strong>
				</span>
			</div>
			
			<div class="form-group col-md-4">
	  			<label class="control-label">API Token <a href="https://smsgateway.me/login" target="_blank">(SMS Gateway) <i class="fas fa-external-link-alt"></i></a></label>
				<input type="text" class="form-control" id="sms_gateway" name="sms_gateway" value="{{$empresa->sms_gateway}}">
				<div class="help-block error with-errors"></div>
				<span class="help-block error">
					<strong>{{ $errors->first('sms_gateway') }}</strong>
				</span>
			</div>
			<div class="form-group col-md-4">
	  			<label class="control-label">ID Device <a href="https://smsgateway.me/login" target="_blank">(SMS Gateway) <i class="fas fa-external-link-alt"></i></a></label>
				<input type="text" class="form-control" id="device_id" name="device_id" value="{{$empresa->device_id}}">
				<div class="help-block error with-errors"></div>
				<span class="help-block error">
					<strong>{{ $errors->first('device_id') }}</strong>
				</span>
			</div>
  		</div>

		  <div class="card-separator">
			<div class="row">
				<div class="form-check form-check-flat">
					<label class="form-check-label">
						<input type="checkbox" class="form-check-input checks casec" name="facturacionFe" id="facturacion" value="" {{ $empresa->form_fe == 1 ? 'checked' : '' }}>Activar Facturacion Electronica
						<i class="input-helper"></i></label>
				</div>
			</div>
	
			<div id="form-facturacion" style="{{$empresa->form_fe == 0 ? 'display: none' : '' }};">
	
				<div class="row">
					<div class="form-group col-md-6">
						<label class="control-label">Selecciona la versión de facturación electrónica que usarás para emitir tus comprobantes a la DIAN. <span class="text-danger">*</span></label>
						<select class="form-control selectpicker" name="optfacturacion" id="optfacturacion" required="">
							<option value="2">Facturación con validación previa</option>
						</select>
						<span class="help-block error">
							<strong>{{ $errors->first('tip_iden') }}</strong>
						</span>
					</div>
				</div>
				<div class="row">
					<div class="col-md-6">
						<div class="row">
							<table id="tableRes">
								<thead>
									<th>
										<label class="control-label">Agregar Responsabilidades <span class="text-danger">*</span>
											<button type="button" id="addRes" style="font-size: 10px;" onclick="setResponsabilidad();">
												<i class="fas fa-plus"></i> Agregar Responsabilidad</button>
										</label>
									</th>
								</thead>
								<tbody>
									@if(count($empresa_resp) >= 1)
									@php $cont = 0; @endphp
									@foreach($empresa_resp as $resp)
	
									<tr id="{{$cont = $cont + 1}}">
										<td>
											<div class="form-group col-lg-12">
	
												<select class="form-control selectpicker" name="tip_responsabilidad[]" id="responsabilidad{{$cont}}" required="" title="Seleccione" data-size="5" data-live-search="true" onchange="noduplicar(this.value);">
													@foreach($responsabilidades as $responsabilidad)
													<option value="{{$responsabilidad->id}}" {{ $resp->id_responsabilidad == $responsabilidad->id ? 'selected' : '' }}>{{$responsabilidad->responsabilidad}}- ({{$responsabilidad->codigo}})</option>
													@endforeach
												</select>
												<span class="help-block error">
													<strong>{{ $errors->first('tip_responsabilidades') }}</strong>
												</span>
											</div>
										</td>
										<td>
											<button type="button" class="btn btn-outline-secondary btn-icons" onclick="eliminarTr({{$cont}});">X</button>
										</td>
									</tr>
	
									@endforeach
									@else
									<tr id="1">
										<td>
											<div class="form-group col-lg-12">
	
												<select class="form-control selectpicker noduplicar" name="tip_responsabilidad[]" id="responsabilidad1" required="" title="Seleccione" data-size="5" data-live-search="true" onchange="noduplicar(this.value);">
													@foreach($responsabilidades as $responsabilidad)
													<option value="{{$responsabilidad->id}}">{{$responsabilidad->responsabilidad}}- ({{$responsabilidad->codigo}})</option>
													@endforeach
												</select>
												<span class="help-block error">
													<strong>{{ $errors->first('tip_responsabilidades') }}</strong>
												</span>
											</div>
										</td>
										<td>
											<button type="button" class="btn btn-outline-secondary btn-icons" onclick="eliminarTr(1);">X</button>
										</td>
									</tr>
									@endif
								</tbody>
							</table>
						</div>
					</div>
					<div class="col-md-6">
						<div class="row">
							<div class="form-group col-md-12">
								<label class="control-label" for="test_resolucion">Configura tu resolución de pruebas para habilitación <span class="text-danger">*</span></label>
								<input type="text" class="form-control" id="test_resolucion" {{ $empresa->estado_dian==0 ? '' : 'readonly'}} name="test_resolucion" required="" data-error="estructura no valida" maxlength="100" value="{{$empresa->fe_resolucion}}">
								<div class="help-block error with-errors"></div>
								<span class="help-block error">
									<strong id='msj_test_r'>{{ $errors->first('test_resolucion') }}</strong>
								</span>
								<p>Estado de envío de set de pruebas: <span class="text-primary">{{ $empresa->estado_dian==0 ? 'No autorizado' : 'Autorizado'}}</span></p>
							</div>
							@if($empresa->estado_dian==1 && $empresa->technicalkey == null)
							<div class="alert alert-success" role="alert">
								Has sido habilitado ahora debes de ingresar la resolución de facturación electronica vease este tutorial
								<b><a href="">Click aca</a></b>
								<br>
								<br>
	
								<a href="/empresa/configuracion/numeraciones" target="_black">Configurar resolución de facturación</a>
							</div>
							@elseif($empresa->estado_dian==1 && $empresa->technicalkey != null)
							<div class="alert alert-success col-md-12" role="alert">
								<center>
									<h4>Activo con facturación electrónica</h4>
									<img style="width: 130px;" class="logo_dian" src="{{asset('images/PagInicio/logo_dian.png')}}">
								</center>
							</div>
							@endif
						</div>
					</div>
				</div>
			</div>
		</div>

  		<div class="row">
  			<small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small></p>
  		</div>
  		<hr>
  		<div class="row" style="text-align: right;">
  			<div class="col-md-12">
				<a href="{{route('configuracion.index')}}" class="btn btn-outline-light" >Cancelar</a>
  				<button type="submit" class="btn btn-success">Guardar</button>
  			</div>
  		</div>

  	</form>
	<input type="hidden" id="getRes" value="{{json_encode($responsabilidades)}}">


	<div class="modal fade" id="modalFact"  tabindex="-1" role="dialog">
		<div class="modal-dialog modal-md-12">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="modal-title">Has activado la facturacion electronica en tu cuenta</h4>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>
				<div class="modal-body" id="modal-body">
					Para emitir documentos validos ante la DIAN asegúrate haber realizado el proceo para ser facturador
					electrónico en <a href="https://muisca.dian.gov.co" target="_blank">muisca.dian.gov.co</a>. Para saber más, haz <a href="/documentaciones/dian/procesodian.pdf" target="_black">Click aquí</a>
				</div>

			</div>
		</div>
	</div>

	<div class="modal" tabindex="-1" role="dialog" id="modalFe">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Facturación electrónica en proceso</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Estamos procesando su solicitud se le <b>notificará</b>,
                     por correo electrónico cuando esta sea <u>aprobada</u>
                    <u> y habilitada</u> para el siguiente paso.
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')

<script>
        var sw = 0;
		$(document).ready(function () {

		    var url = '/software/empresa/configuracion/check_FE';
            var _token =   $('meta[name="csrf-token"]').attr('content');


            $('#facturacion').change(function(){

                $.post(url,{ _token : _token},function(resul){
                    if(resul['status']=='OK'){
                        //$('#cancelar').click();
                         $('#modalFact').modal("show");
                         sw = 1;
                    }

                    console.log(resul);
                },'json');

                if(!$(this).prop('checked')){
                    $('#form-facturacion').hide();
                    sw = 0;
                }else{
                    $('#form-facturacion').show();
                    sw = 1;
                }
            });

            var option = document.getElementById('tip_iden').value;

            if (option == 6) {
                searchDV($("#tip_iden").val());
            }

            $("form").submit(function(){

            });
        });

</script>

<script>

window.addEventListener('load', function() {
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.getElementsByClassName('forms-sample');
    // Loop over them and prevent submission
    var validation = Array.prototype.filter.call(forms, function(form) {
      form.addEventListener('submit', function(event) {
		  //console.log(form);
		 if(sw == 1)
		 {
        if (form.checkValidity() === false) {
          event.preventDefault();
          event.stopPropagation();
		}else{
			const Toast = Swal.mixin({
		toast: true,
		position: 'top-center',
		showConfirmButton: false,
		timer: 1000000000,
		timerProgressBar: true,
		onOpen: (toast) => {
			toast.addEventListener('mouseenter', Swal.stopTimer)
			toast.addEventListener('mouseleave', Swal.resumeTimer)
		}
	})

	Toast.fire({
		type: 'success',
		title: 'Enviando Formulario...',
	})
		}
	}else{
		const Toast = Swal.mixin({
		toast: true,
		position: 'top-center',
		showConfirmButton: false,
		timer: 1000000000,
		timerProgressBar: true,
		onOpen: (toast) => {
			toast.addEventListener('mouseenter', Swal.stopTimer)
			toast.addEventListener('mouseleave', Swal.resumeTimer)
		}
	})

	Toast.fire({
		type: 'success',
		title: 'Enviando Formulario...',
	})
	}

        //form.classList.add('was-validated');
	  }, false);

    });
  }, false);

</script>

<script>
    function setResponsabilidad() {

        var nro = $('#tableRes tbody tr').length + 1;
        var i;
        if ($('#' + nro).length > 0) {
            for (i = 1; i <= nro; i++) {
                if ($('#' + i).length == 0) {
                    nro = i;
                    break;
                }
            }
        }
        $('#tableRes tbody').append(
				'<tr id="'+nro+'">'+
					'<td>'+
						'<div class="form-group col-md-12">'+
						'<select class="form-control selectpicker noduplicar" name="tip_responsabilidad[]" id="responsabilidades'+nro+'" required="" title="Seleccione" data-size="5" data-live-search="true" onchange="noduplicar(this.value);"> </select>'+
						'</div>'+
						'<td>'+
						    '<button type="button" class="btn btn-outline-secondary btn-icons" onclick="eliminarTr('+nro+');">X</button>'+
						'</td>'+
					'</td>'+
				'</tr>');


        data = $('#getRes').val();
        data = JSON.parse(data);
        var $select = $('#responsabilidades' + nro);
        $.each(data, function (key, value) {
            $select.append('<option value=' + value.id + '>' + value.responsabilidad + '- ('+value.codigo +')</option>');
        });
        $('#responsabilidades' + nro).selectpicker();
    }

    function eliminarTr(id){
        $("#" + id).remove();
    }

    function noduplicar(id){

        $(".noduplicar").each(function(index) {
              console.log( $(this).val());
         });

    }
    $('#test_resolucion').blur(function() {
        $('.help-block #msj_test_r').html();
        $(this).removeClass('border-succes')
        $(this).removeClass('border-danger')
        if ($(this).val().match(/^([0-9a-zA-Z]{8}\-[0-9a-zA-Z]{4}\-[0-9a-zA-Z]{4}\-[0-9a-zA-Z]{4}\-[0-9a-zA-Z]{12})$/)){
            $(this).addClass('border-success')
            $('.help-block #msj_test_r').html("");
        }else{
            $(this).addClass('border-danger');
            $('.help-block #msj_test_r').html($(this).data('error'));
        }
    });


</script>

@endsection
