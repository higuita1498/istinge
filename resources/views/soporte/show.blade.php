@extends('layouts.app')
@section('content')

<div class="row card-description">
	<div class="col-md-9">
		<div class="table-responsive">
			<table class="table table-striped table-bordered table-sm">
				<tbody>
					<tr>
						<td>Título</td>
						<td>{{$soporte->titulo}}</td>
						<td>Estatus</td>
						<td>{{$soporte->estatus()}}</td>
					</tr>
					<tr>
						<td>ID Caso</td>
						<td>{{$soporte->id}}</td>
						<td>Categoría</td>
						<td>{{$soporte->modulo()}}</td>
					</tr>
					<tr>
						<td>De</td>
						<td>{{$soporte->empresa()->nombre}}</td>
						<td>Usuario</td>
						<td>{{$soporte->usuario()}}</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
	<div class="col-md-3" style="text-align: center;">
		@if($soporte->imagen)
			<img class="img-responsive" src="{{asset('images/Empresas/Empresa'.$soporte->empresa.'/soporte/'.$soporte->id.'/'.$soporte->imagen)}}" alt="" style="    width: 100%;">
      	@endif
		
	</div>
</div> 

<div class="row card-description">
	<div class="col-md-12">
		@foreach($tickets as $ticket)
			<div class="row" style="margin-bottom: 1%;">
				<div class="col-md-3" style="text-align: right;">
					<p><b>{{$ticket->usuario()}}</b> <br>
					{{date('d-m-Y h:m a', strtotime($ticket->created_at))}}</p>

				</div>
				<div class="col-md-8 mensaje">
					<p>{{$ticket->error}}</p>
					@if($ticket->imagen)
					<p><a href="{{asset('images/Empresas/Empresa'.$soporte->empresa.'/soporte/'.$soporte->id.'/'.$ticket->imagen)}}" target="_blanck"> Ver Imagen</a></p>
				@endif
				</div>
				
			</div>
		@endforeach
		<div class="row">
			<div class="col-md-3" style="text-align: right;">
				<p><b>{{$soporte->usuario()}}</b> <br>
				{{date('d-m-Y h:m a', strtotime($soporte->created_at))}}</p>

			</div>
			<div class="col-md-8 mensaje">
				<p>{{$soporte->error}}</p>

				@if($soporte->imagen)
					<p><a href="{{asset('images/Empresas/Empresa'.$soporte->empresa.'/soporte/'.$soporte->id.'/'.$soporte->imagen)}}" target="_blanck"> Ver Imagen</a></p>
				@endif
			</div>
			
		</div>
	</div>
</div> 
<div class="row card-description">
	@if(Auth::user()->rol==1 && $soporte->estatus==1)
		<div class="col-md-12 mensaje">
			<h4>Responder</h4>
			<form method="POST" action="{{ route('atencionsoporte.update',$soporte->id ) }}" role="form" class="forms-sample" novalidate id="form-soporte" enctype="multipart/form-data">
      <input name="_method" type="hidden" value="PATCH">
		   			{{ csrf_field() }} 
				<div class="row">
			    <div class="col-md-12 form-group">
			      <label class="control-label">Descripción <span class="text-danger">*</span></label>
			      <textarea  class="form-control form-control-sm min_max_100" name="error" required="">{{old('error')}}</textarea>
			      <span class="help-block error">
			        <strong>{{ $errors->first('error') }}</strong>
			      </span>
			    </div>
			  </div>
			  <div class="row">
		    <div class="form-group col-md-5">
		      <label class="control-label">Imagen</label>
		      <input type="file" class="form-control " name="imagen" value="{{old('imagen')}}">
		      <span class="help-block error">
		        <strong>{{ $errors->first('imagen') }}</strong>
		      </span>
		    </div>
		  </div>

		  <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>

			<div class="row" >
		    <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
		      <a href="{{route('atencionsoporte.index')}}" class="btn btn-outline-secondary">Cancelar</a>
		      <button type="submit" class="btn btn-success">Guardar</button>
		    </div>
			</div>
		</form>
		</div>
	@elseif($soporte->estatus==2)
		<div class="col-md-12 mensaje">
			<h4>Responder</h4>
			<form method="POST" action="{{ route('soporte.update',$soporte->id ) }}" role="form" class="forms-sample" novalidate id="form-soporte" enctype="multipart/form-data">
      			<input name="_method" type="hidden" value="PATCH">
		   			{{ csrf_field() }} 
				<div class="row">
			    <div class="col-md-12 form-group">
			      <label class="control-label">Descripción <span class="text-danger">*</span></label>
			      <textarea  class="form-control form-control-sm min_max_100" name="error" required="">{{old('error')}}</textarea>
			      <span class="help-block error">
			        <strong>{{ $errors->first('error') }}</strong>
			      </span>
			    </div>
			  </div>
			  <div class="row">
		    <div class="form-group col-md-5">
		      <label class="control-label">Imagen</label>
		      <input type="file" class="form-control " name="imagen" value="{{old('imagen')}}">
		      <span class="help-block error">
		        <strong>{{ $errors->first('imagen') }}</strong>
		      </span>
		    </div>
		  </div>

		  <small>Los campos marcados con <span class="text-danger">*</span> son obligatorios</small>

			<div class="row" >
		    <div class="col-sm-12" style="text-align: right;  padding-top: 1%;">
		      <a href="{{route('soporte.index')}}" class="btn btn-outline-secondary">Cancelar</a>
		      <button type="submit" class="btn btn-success">Guardar</button>
		    </div>
			</div>
		</form>
	</div>
	@endif
</div>

@endsection