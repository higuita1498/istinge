@extends('layouts.app')
@section('content')
    <input type="hidden" id="valuefecha" value="{{$request->fechas}}">
    <input type="hidden" id="primera" value="{{$request->date ? $request->date['primera'] : ''}}">
    <input type="hidden" id="ultima" value="{{$request->date ? $request->date['ultima'] : ''}}">

	<form id="form-reporte">


	<div class="row card-description">
		<div class="form-group col-md-2 d-none">
		    <label>Numeración</label>
		    <select class="form-control selectpicker" name="nro">
		    	@foreach($numeraciones as $nro)
		    		<option value="{{$nro->id}}" {{$nro->id==$request->nro?'selected':''}}>{{$nro->nombre}}</option>
		    	@endforeach
		    </select>
	  	</div>

		<div class="form-group col-md-3">
		    <label>Tipo</label>
		    <select class="form-control selectpicker" name="tipo" id="tipo" placeholder="tipo">
		    	<option value="1" selected>Plan</option>
		    	<option value="2">Material</option>
		    	<option value="3">Tv</option>
		    	<option value="4">Todos</option>
		    </select>
	  	</div>
	  	<div class="form-group col-md-3">
		    <label>Trimestre</label>
		    <select class="form-control selectpicker" name="trimestre" id="trimestre" placeholder="Trimestre">
		    	<option value="1">1</option>
		    	<option value="2">2</option>
		    	<option value="3">3</option>
		    	<option value="4">4</option>
		    </select>
	  	</div>
	  	<div class="form-group col-md-6" style=" padding-top: 24px;">
        	<button type="button" id="generar" class="btn btn-outline-primary">Generar Reporte</button>
        	<button type="button" id="exportar" class="btn btn-outline-success">Exportar a Excel</button>
	  	</div>
	</div>

    <input type="hidden" name="orderby"id="order_by"  value="2">
    <input type="hidden" name="order" id="order" value="1">
    <input type="hidden" id="form" value="form-reporte">

	<div class="row card-description">
		<div class="col-md-12 table-responsive">
			<table class="table table-striped table-hover " id="table-facturas">
			<thead class="thead-dark">
				<tr>
                    <th>Año</th> 
                    <th>Trimestre</th> 
                    <th>Municipio</th> 
                    <th>Segmento</th> 
                    <th>Paquete</th> 
                    <th>Vel. DownStream</th> 
                    <th>Vel. Upstream</th> 
                    <th>Tecnologia Acc.</th> 
                    <th>Estado</th> 
                    <th>Cant</th> 
                    <th>Valor</th> 
	            </tr>
			</thead>
			<tbody>
					<tr>
                        <td>2022</td>
                        <td>1</td>
                        <td>8001</td>
                        <td>101</td>
                        <td>1</td>
                        <td>10</td>
                        <td>5</td>
                        <td>108</td>
                        <td>1</td>
                        <td>105</td>
                        <td>108.500</td>
					</tr>
			</tbody>
			<tfoot class="thead-dark">
				<td colspan="9"></td>
				<th  class="text-right">Total</th>
				<th>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($total)}}</th>
			</tfoot>

		</table>
            {!! $facturas->render() !!}
	</div>
</div>
</form>
<input type="hidden" id="urlgenerar" value="{{route('reportes.ventas')}}">
<input type="hidden" id="urlexportar" value="{{route('exportar.ventas')}}">
@endsection
