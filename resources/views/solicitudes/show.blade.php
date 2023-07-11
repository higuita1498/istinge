@extends('layouts.app')

@section('boton')
    {{-- @if($solicitud->status==1)
        <form action="{{ route('solicitudes.status',$solicitud->id) }}" method="POST" class="delete_form" style="display: none;" id="status-{{$solicitud->id}}">
            {{ csrf_field() }}
        </form>
        <a href="#" onclick="confirmar('status{{$solicitud->id}}', '¿Está seguro de que desea darle respuesta positiva a la solicitud de servicio?');" class="btn btn-outline-success btn-sm "title="Solventar Caso">Solventar Solicitud</a>
    @endif --}}
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
						<th width="25%">DATOS GENERALES</th>
						<th></th>
					</tr>
					<tr>
						<th>Nombre Cliente</th>
						<td>{{ $solicitud->nombre }}</td>
					</tr>
					<tr>
						<th>Cédula Cliente</th>
						<td>{{ $solicitud->cedula }}</td>
					</tr>
					<tr>
						<th>Nro Contacto</th>
						<td>{{ $solicitud->nrouno }} @if($solicitud->nrodos) | {{ $solicitud->nrodos }} @endif</td>
					</tr>
					<tr>
						<th>Correo Electrónico</th>
						<td>{{ $solicitud->email }}</td>
					</tr>
					<tr>
						<th>Dirección</th>
						<td>{{ $solicitud->direccion }}</td>
					</tr>
					<tr>
						<th>Plan Solicitado</th>
						<td>{{ $solicitud->plan }}</td>
					</tr>
					<tr>
						<th>Fecha Agendada</th>
						<td>{{date('d-m-Y', strtotime($solicitud->fecha))}}</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>

<div class="row">

</div>
@endsection
