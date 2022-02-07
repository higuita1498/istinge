@extends('layouts.app')
@section('content')
	@if(Session::has('error'))
	    <div class="alert alert-danger">
	      {{Session::get('error')}}
	    </div>
	  @endif
	<form method="POST" action="{{ route('empresas.update',$empresa->id) }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-empresa" enctype="multipart/form-data">
  		<input name="_method" type="hidden" value="PATCH">
  		{{ csrf_field() }}
  		@if($empresa->logo)
			<div class="row">
				<div class="form-group col-md-5">
  					<label class="control-label">Logo Actual</label>
                    <div class="project-wrapper" style=" width: 50%;">
                      <div class="project">
                        <div class="photo-wrapper">
                            <div class="photo" style="background: #fff; padding-left: 20%;">
                              <img class="img-responsive" src="{{asset('images/Empresas/Empresa'.$empresa->id.'/'.$empresa->logo)}}" alt="">
                            </div>
                            <div class="overlay"></div>
                        </div>
                      </div>
                  </div>
				</div>
			</div>
		@endif

  		<div class="row">
  			<div class="form-group col-md-4">
	  			<label class="control-label">Logo de la Empresa (Opcional)</label>
				<input type="file" class="form-control " name="logo" value="{{old('logo')}}">
				<span class="help-block error">
		        	<strong>{{ $errors->first('logo') }}</strong>
		        </span>
			</div>
			<div class="form-group col-md-4">
	  			<label class="control-label">Nombre</label>
				<input type="text" class="form-control" name="nombre" id="nombre" required="" maxlength="200"  value="{{$empresa->nombre}}">
				<span class="help-block error">
		        	<strong>{{ $errors->first('nombre') }}</strong>
		        </span>
			</div>
			<div class="form-group col-md-4">
                <label class="control-label">Plan personalizado</label>
                <select name="p_personalizado" class="selectpicker">
                    <option value="0" {{($empresa->p_personalizado == 0) ? 'selected' : ''}}>No</option>
                    @foreach($planes as $plan)
                        <option value="{{$plan->id}}" {{($empresa->p_personalizado == $plan->id) ? 'selected' : ''}}>{{$plan->nombre}}</option>
                    @endforeach
                </select>
            </div>
  		</div>
  		<div class="row">


			<div class="form-group col-md-4">
	  			<label class="control-label">Tipo de Identificación</label>
	  			<select class="form-control selectpicker" name="tip_iden" id="tip_iden" required="" title="Seleccione">
	  				@foreach($identificaciones as $identificacion)
                  		<option {{$empresa->tip_iden==$identificacion->id?'selected':''}} value="{{$identificacion->id}}">{{$identificacion->identificacion}}</option>
	  				@endforeach
                </select>
				<span class="help-block error">
		        	<strong>{{ $errors->first('tip_iden') }}</strong>
		        </span>
			</div>
			<div class="form-group col-md-4">
	  			<label class="control-label">Identificación</label>
				<input type="text" class="form-control" name="nit" id="nit" required="" maxlength="20" value="{{$empresa->nit}}">
				<span class="help-block error">
					<strong>{{ $errors->first('nit') }}</strong>
				</span>
			</div>
			<div class="form-group col-md-4">
				<label class="control-labe">Tipo de Persona</label>
				<div class="row">
					<div class="col-sm-6">
					<div class="form-radio">
						<label class="form-check-label">
						<input type="radio" class="form-check-input" name="tipo_persona" id="tipo_persona1" value="n" @if($empresa->tipo_persona=='n') checked="" @endif> Natural
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
  			<div class="form-group col-md-3">
	  			<label class="control-label">Teléfono</label>
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

			<div class="form-group col-md-5">
	  			<label class="control-label">Dirección</label>
				<input type="text" class="form-control" name="direccion" required=""  value="{{$empresa->direccion}}" >
				<span class="help-block error">
					<strong>{{ $errors->first('direccion') }}</strong>
				</span>
			</div>
			<div class="form-group col-md-4">
	  			<label class="control-label" for="email">Correo Electrónico</label>
				<input type="email" class="form-control" id="email" name="email" required="" data-error="Dirección de correo electrónico invalida" maxlength="100"  value="{{$empresa->email}}">
				<div class="help-block error with-errors"></div>
				<span class="help-block error">
					<strong>{{ $errors->first('email') }}</strong>
				</span>
			</div>
  		</div>

  		<div class="row">

			<div class="form-group col-md-3">
				<label class="control-labe">¿Dispondra de carrito?</label>
				<div class="row">
					<div class="col-sm-6">
					<div class="form-radio">
						<label class="form-check-label">
						<input type="radio" class="form-check-input" name="carrito" id="carrito1" value="1" @if($empresa->carrito==1) checked="" @endif> Si
						<i class="input-helper"></i></label>
					</div>
				</div>
				<div class="col-sm-6">
					<div class="form-radio">
						<label class="form-check-label">
						<input type="radio" class="form-check-input" name="carrito" id="carrito" value="0" @if($empresa->carrito==0) checked="" @endif> No
						<i class="input-helper"></i></label>
					</div>
				</div>
				</div>
			</div>
			<div class="form-group col-md-4">
	  			<label class="control-label">Sitio Web (opcional)</label>
				<input type="text" class="form-control" name="web" id="web" maxlength="255" value="{{$empresa->web}}">
				<span class="help-block error">
					<strong>{{ $errors->first('web') }}</strong>
				</span>
			</div>

		 </div>
  		<div class="row">
			<div class="form-group col-md-4">
	  			<label class="control-label">Usuario</label>
				<input type="text" class="form-control" name="username" id="username" required="" maxlength="100"
				    autocomplete="off"
				    value="{{$empresa->usuario()->username}}">
				<span class="help-block error">
					<strong>{{ $errors->first('username') }}</strong>
				</span>
			</div>

			<div class="form-group col-md-2">
	  			<label class="control-label">Cambiar contraseña</label>

                  <div class="form-check form-check-flat mt-0" >
                    <label class="form-check-label">
                      <input type="checkbox" class="form-check-input" name="changepass" id="changepass" value="1"> Cambiar
                    </label>
                  </div>
			</div>
			<input type="hidden" id="cambiar" value="0">

			<div id="pass" class=" col-md-6" style="display: none;">
				<div class="row">
					<div class="form-group col-md-6">
		  			<label for="inputPassword" class="control-label">Contraseña </label>
					<input type="password" class="form-control" name="password" id="inputPassword" required>
				</div>
				<div class="form-group col-md-6">
		  			<label class="control-label">Confirmar Contraseña </label>
					<input type="password" class="form-control"  id="inputPasswordConfirm" name="inputPasswordConfirm" required >
					<div class="help-block error with-errors"></div>
				</div>
				</div>
	  		</div>


  		</div>
        <hr>
        <div class="row">
            <table class='table table-striped table-hover'>
                <thead class="thead-dark" >
                    <th>Plan</th>
                    <th>Fecha de inicio</th>
                    <th>Fecha de corte</th>
                    <th>Acción</th>
                </thead>
                <tbody>
                    @foreach($subscriptions as $subscription)
                        @if($subscription->valid || $subscription->estado==10)
                            <tr>
                                <td>{{$subscription->plan()}}</td>
                                <td>{{date('d-m-Y', strtotime($subscription->created_at))}}</td>
                                <td>{{date('d-m-Y', strtotime($subscription->expiration))}}</td>
                                <td>
                                    @if($subscription->estado!=1)
                                        <a href="{{route('suscripciones.activar', $subscription->id)}}" class="btn btn-sm btn-primary">
                                            Activar
                                        </a>
                                    @else
                                        <a href="{{route('suscripciones.anular', $subscription->id)}}" class="btn btn-sm btn-danger">
                                            Anular
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        </div>
  		<hr>
  		<div class="row" style="text-align: right;">
  			<div class="col-md-12">
				<a href="{{route('empresas.index')}}" class="btn btn-outline-light" >Cancelar</a>
  				<button type="submit" class="btn btn-success">Guardar</button>
  			</div>
  		</div>

  	</form>

@endsection
