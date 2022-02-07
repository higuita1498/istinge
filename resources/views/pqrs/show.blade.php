@extends('layouts.app')

@section('boton')

@endsection

@section('content')
<style>
	body > div.container-scroller > div > div > div.content-wrapper > div > div > div > div.row.card-description > div > div > table > tbody > tr:nth-child(10) > td > img{
		width: 547px;
		height: 297px;
		border-radius: 0%;
	}
</style>

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

<div class="row card-description">
	<div class="col-md-12">
		<div class="table-responsive">
			<table class="table table-striped table-bordered table-sm info">
				<tbody>
					<tr>
						<th width="15%">DATOS GENERALES</th>
						<th></th>
					</tr>
					<tr>
						<th>Tipo Solicitud</th>
						<td>{{$pqrs->solicitud}}</td>
					</tr>
					<tr>
						<th>Fecha</th>
						<td>{{date('d-m-Y', strtotime($pqrs->fecha))}}</td>
					</tr>
					<tr>
						<th>Estatus</th>
						<td>
						    <span class="text-{{$pqrs->estatus('true')}} font-weight-bold">{{$pqrs->estatus()}}</span>
                        </td>
					</tr>
					<tr>
						<th>N° PQRS</th>
						<td>{{$pqrs->id}}</td>
					</tr>
					<tr>
						<th>Cliente</th>
						<td>{{$pqrs->nombres}}</td>
					</tr>
					<tr>
						<th>Email</th>
						<td>{{$pqrs->email}}</td>
					</tr>
					<tr>
						<th>Teléfono</th>
						<td>{{$pqrs->telefono}}</td>
					</tr>
					<tr>
						<th>Dirección</th>
						<td>{{$pqrs->direccion}}</td>
					</tr>
					<tr>
						<th>Solicitud PQRS</th>
						<td>{{$pqrs->mensaje}}</td>
					</tr>
					@if ($pqrs->updated_by() != NULL)
					<tr>
						<th>Responsable</th>
						<td>{{$pqrs->updated_by()->nombres}}</td>
					</tr>
					@endif
					@if ($pqrs->respuesta)
					<tr>
						<th>Respuesta PQRS</th>
						<td>{{$pqrs->respuesta}}</td>
					</tr>
					@endif
				</tbody>
			</table>
		</div>
	</div>
	
	@if($pqrs->respuesta == NULL)
	<div class="col-md-12">
	    <form method="POST" action="{{ route('pqrs.update', $pqrs->id ) }}" style="padding: 2% 3%;" role="form" class="forms-sample" novalidate id="form-pqrs">
	        {{ csrf_field() }}
	        <input name="_method" type="hidden" value="PATCH">
	        
	        <div class="col-md-12 form-group">
	            <label class="control-label">Respuesta PQRS <span class="text-danger">*</span></label>
	            <textarea  class="form-control form-control-sm min_max_100" id="respuesta" required="" name="respuesta"></textarea>
	            <span class="help-block error">
	                <strong>{{ $errors->first('respuesta') }}</strong>
	            </span>
	        </div>
	        
	        <div class="col-sm-12" style="text-align: center;  padding-top: 1%;">
	            <button type="submit" class="btn btn-success">Guardar</button>
	        </div>
	    </form>
	</div>
	@endif
</div>
@endsection
