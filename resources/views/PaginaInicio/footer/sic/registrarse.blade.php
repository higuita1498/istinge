@extends('layouts.includes.inicio') 
@section('content2') 
<!--<div class="full" style="background-image: url('../images/PagInicio/qq.png')">-->
	<div class="full" style="background-image: url('../images/PagInicio/qq.png')">    
		<div class="enlamitad-2">
			<center>
				<div class="card-paginicio card-text-principal">
					<h2 class="card-title card-title-module">¡REGISTRATE AHORA!</h2>{{--  <img src="/images/favicon.png" style="margin-left: 15px; margin-bottom: -15px;"> --}}
				</div>
				@if(Session::has('success'))
				<div class="alert alert-success" style="text-align: center;">
					<button type="button" class="close" data-dismiss="alert">X</button>
					<strong>{{Session::get('success')}}</strong>
				</div>
				@endif
				<div class="card-paginicio">
					<center>
						<div class="enlamitad">
							<div class="formulario-empresa">
								<form method="post" action="{{route('social.registronormal')}}"  class="form-control formu-empresa" style="float:none;">
									{{ csrf_field() }}
									<center><h1 style="margin-bottom: 10px;"><img src="/images/favicon.png" style="margin-right: 5px;margin-bottom: -15px;">CREAR CUENTA<img src="/images/favicon.png" style="margin-left: 5px; margin-bottom: -15px;"></h1></center>
									<!--<label for="txtcorreo">Correo Electronico</label> -->
									<input type="text" name="email" class="{{ $errors->has('email') ? ' is-invalid' : '' }}" placeholder="Correo Electronico" value="{{old('email')}}" required>

									@if ($errors->has('email'))
									<span class="invalid-feedback" style="display:block;" role="alert">
										<strong>{{ $errors->first('email') }}</strong>
									</span>
									@endif
									
									<input type="text" name="username" class="{{ $errors->has('username') ? ' is-invalid' : '' }}" placeholder="Usuario" value="{{old('username')}}" required>

									@if ($errors->has('username'))
									<span class="invalid-feedback" style="display:block;" role="alert">
										<strong>{{ $errors->first('username') }}</strong>
									</span>
									@endif

									<!--	<label for="txtcontrasena">Contraseña</label> -->
									<input type="password" name="password" class="" placeholder="Contraseña" required>

									@if ($errors->has('password'))
									<span class="invalid-feedback" style="display:block;" role="alert">
										<strong>{{ $errors->first('password') }}</strong>
									</span>
									@endif

									<!--	<label for="txtempresa">Nombre de la Empresa</label> -->
									<input type="text" name="empresa" class="" placeholder="Nombre Empresa" required value="{{old('empresa')}}">

									@if ($errors->has('empresa'))
									<span class="invalid-feedback" style="display:block;" role="alert">
										<strong>{{ $errors->first('empresa') }}</strong>
									</span>
									@endif

									<select name="tip_iden" class="form-control" style="width:100%;margin-top:15px;" required value="{{old('tip_iden')}}">
										<option value="">Seleccione una opción</option>
										<option value="1">Registro Civil (RC)</option>
										<option value="2">Tarjeta de Identidad (TI)</option>
										<option value="3">Cedula de ciudadania (CC)</option>
										<option value="4">Tarjeta de Extranjeria (TE)</option>
										<option value="5">Cedula de Extranjeria</option>
										<option value="6">Número de identificación tributaria (NIT)</option>
										<option value="7">Pasaporte (PP)</option>
										<option value="8">Documento de identificación extrajero (DIE)</option>
									</select>

									<!--<label for="txtcedula">Cedula o NIT:</label>-->
									<input type="text" name="documento" class="" placeholder="Documento" required value="{{old('documento')}}">

									@if ($errors->has('documento'))
									<span class="invalid-feedback" style="display:block;" role="alert">
										<strong>{{ $errors->first('documento') }}</strong>
									</span>
									@endif

									<div class="row">
										<div class="col-md-4">
											<select name="prefijo" class="form-control" style="width:100%;margin-top:15px; height: 48px;" required value="{{old('prefijo')}}">
												@foreach($prefijos as $prefijo)
												<option @if(old('prefijo')) {{old('prefijo')==$prefijo->phone_code?'selected':''}} @else
													{{ $prefijo->phone_code ==  '+57' ? 'selected' : ''}}
													@endif

													data-icon="flag-icon flag-icon-{{strtolower($prefijo->iso2)}}"

													value="+{{$prefijo->phone_code}}" title="+{{$prefijo->phone_code}}" data-subtext="+{{$prefijo->phone_code}}">{{$prefijo->nombre}}</option>
													@endforeach
												</select>
											</div>

											<div class="col-md-8">
												
												<input type="text" name="telefono" class="{{ $errors->has('celular') ? ' is-invalid' : '' }}" placeholder="Celular" value="{{old('celular')}}" required>

												@if ($errors->has('celular'))
												<span class="invalid-feedback" style="display:block;" role="alert">
													<strong>{{ $errors->first('celular') }}</strong>
												</span>
												@endif
											</div>
										</div>




										<P style="color:#fff;">¡Pruebalo totalmente gratis durante 1 mes!</P>

										<input type="submit" name="txtaceptar" value="CREAR CUENTA">
									</form>
								</div>
							{{--<div class="form-group">
								<div class="col-md-12">

									<a class="a-clsgoogle" href="{{route('google.logueo',"google")}}"><div class="btn-google" data-ga="[&quot;sign up&quot;,&quot;Sign Up Started - Google&quot;,&quot;New Post&quot;,null,null]">
										<svg aria-hidden="true" class="svg-icon native iconGoogle" width="18" height="18" viewBox="0 0 18 18"><path d="M16.51 8H8.98v3h4.3c-.18 1-.74 1.48-1.6 2.04v2.01h2.6a7.8 7.8 0 0 0 2.38-5.88c0-.57-.05-.66-.15-1.18z" fill="#4285F4"></path><path d="M8.98 17c2.16 0 3.97-.72 5.3-1.94l-2.6-2a4.8 4.8 0 0 1-7.18-2.54H1.83v2.07A8 8 0 0 0 8.98 17z" fill="#34A853"></path><path d="M4.5 10.52a4.8 4.8 0 0 1 0-3.04V5.41H1.83a8 8 0 0 0 0 7.18l2.67-2.07z" fill="#FBBC05"></path><path d="M8.98 4.18c1.17 0 2.23.4 3.06 1.2l2.3-2.3A8 8 0 0 0 1.83 5.4L4.5 7.49a4.77 4.77 0 0 1 4.48-3.3z" fill="#EA4335"></path></svg><font style="vertical-align: inherit;"><font style="vertical-align: inherit;"> Acceder usando Google
										</font></font></div></a>
									</div>
								</div>--}}
							</div>
						</center>
					</div>
				</center>
			</div>
			@endsection