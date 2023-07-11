@extends('layouts.app')

@section('boton')
    <a href="{{ route('contratos.show',$contrato->id )}}"  class="btn btn-primary" title="Regresar al Detalle"><i class="fas fa-step-backward"></i></i> Regresar al Detalle</a>
@endsection

@section('style')
    <style>
        .bg-th{
            text-align: center;
        }
        .info th {
            text-align: center;
        }
    </style>
@endsection

@section('content')
    <div class="row card-description">
    	<div class="col-md-12 mb-4">
    		<div class="table-responsive">
    			<table class="table table-striped table-bordered table-sm info">
    				<tbody>
    					<tr>
    						<th class="bg-th">CONTRATO</th>
    						<th class="bg-th">CLIENTE</th>
    						<th class="bg-th">DIRECCIÓN IP</th>
    						<th class="bg-th">INTERFAZ</th>
    						<th class="bg-th">SERVIDOR ASOCIADO</th>
    						<th class="bg-th">CONEXIÓN</th>
    						{{--<th class="bg-th">PLAN</th>--}}
    					</tr>
    					<tr class="text-center">
    						<td>{{ $contrato->nro }}</td>
    						<td>{{ $contrato->cliente()->nombre }} {{ $contrato->cliente()->apellido1 }} {{ $contrato->cliente()->apellido2 }}</td>
    						<td>{{ $contrato->ip }}</td>
    						<td>{{ $contrato->interfaz }}</td>
    						<td>{{ $contrato->servidor()->nombre }}</td>
    						<td>{{ $contrato->conexion() }}</td>
    						{{--<td>{{ $contrato->plan()->name }}</td>--}}
    					</tr>
    				</tbody>
    			</table>
    		</div>
    	</div>
    	
        <div class="col-md-3 text-center">
            <a href="http://{{$url}}/daily.gif" target="_blank" class="btn btn-system mb-4">
                <h5 class="pb-0 mb-0 font-weight-bold">GRÁFIO DIARIO</h5><p class="mb-0">(promedio de 5 minutos)</p>
                <div class="mb-4 d-none">
                    <img src="http://{{$url}}/daily.gif" class="d-none img-gafica">
                </div>
            </a>
        </div>
        <div class="col-md-3 text-center">
            <a href="http://{{$url}}/weekly.gif" target="_blank" class="btn btn-system mb-4">
                <h5 class="pb-0 mb-0 font-weight-bold">GRÁFIO SEMANAL</h5><p class="mb-0">(promedio de 30 minutos)</p>
                <div class="mb-4 d-none">
                    <img src="http://{{$url}}/weekly.gif" class="d-none img-gafica">
                </div>
            </a>
        </div>
        <div class="col-md-3 text-center">
            <a href="http://{{$url}}/monthly.gif" target="_blank" class="btn btn-system">
                <h5 class="pb-0 mb-0 font-weight-bold">GRÁFIO MENSUAL</h5><p class="mb-0">(promedio de 2 horas)</p>
                <div class="mb-4 d-none">
                    <img src="http://{{$url}}/monthly.gif" class="d-none img-gafica">
                </div>
            </a>
        </div>
        <div class="col-md-3 text-center">
            <a href="http://{{$url}}/yearly.gif" target="_blank" class="btn btn-system">
                <h5 class="pb-0 mb-0 font-weight-bold">GRÁFIO ANUAL</h5><p class="mb-0">(promedio de 1 día)</p>
                <div class="mb-4 d-none">
                    <img src="http://{{$url}}/yearly.gif" class="d-none img-gafica">
                </div>
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 text-center">
            <a href="{{ route('contratos.grafica', $contrato->id) }}" target="_blank" class="btn btn-system mb-4">
                <h5 class="pb-0 mb-0 font-weight-bold">GRÁFIO TIEMPO REAL</h5><p class="mb-0">(descarga y carga)</p>
                <div class="mb-4 d-none">
                    <img src="http://{{$url}}/daily.gif" class="d-none img-gafica">
                </div>
            </a>
        </div>
    </div>

@endsection

@section('scripts')
<script> 
	
</script>
@endsection
