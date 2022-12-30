@extends('layouts.app')
@section('content')
<input type="hidden" id="valuefecha" value="{{$request->fechas}}">
<input type="hidden" id="primera" value="{{$request->date ? $request->date['primera'] : ''}}">
<input type="hidden" id="ultima" value="{{$request->date ? $request->date['ultima'] : ''}}">

	<form id="form-reporte">


	<div class="row card-description">
	
		<div class="form-group col-md-2">
		    <label></label>
		    <select class="form-control selectpicker" name="fechas" id="fechas">
		    	<optgroup label="Presente">
				    <option value="0">Hoy</option>
				    <option value="1">Este Mes</option>
				    <option value="2">Este Año</option>
			  	</optgroup>
		    	<optgroup label="Anterior">
				    <option value="3">Ayer</option>
				    <option value="4">Semana Pasada</option>
				    <option value="5">Mes Anterior</option>
				    <option value="6">Año Anterior</option>
			  	</optgroup>
		    	<optgroup label="Manual">
				    <option value="7">Manual</option>
			  	</optgroup>
               <optgroup label="Todas">
                    <option value="8">Todas</option>
                </optgroup>
		    </select>
	  	</div>
          <div class="form-group col-md-2">
		    {{-- <label>Grupo corte</label>
		    <select class="form-control selectpicker" name="tipo">
                @foreach($grupos_cosrte as $grupo)
		    	<option value="{{$grupo->id}}" {{1==$request->tipo?'selected':''}}>{{$grupo->nombre}}</option>
                @endforeach
		    	<option value="0" {{0==$request->tipo?'selected':''}}>Todos</option>
		    </select> --}}
	  	</div>
	  	<div class="form-group col-md-4">
			<div class="row">
				<div class="col-md-6">
					<label>Desde <span class="text-danger">*</span></label>
					<input type="text" class="form-control"  id="desde" value="{{$request->fecha}}" name="fecha" required="" >
				</div>
				<div class="col-md-6">
					<label >Hasta <span class="text-danger">*</span></label>
	  				<input type="text" class="form-control" id="hasta" value="{{$request->hasta}}" name="hasta" required="">
				</div>

			</div>
	  	</div>
	  	<div class="form-group col-md-4" style=" padding-top: 24px;">
			{{-- <label>Incluir facturas</label>
			<i data-tippy-content="Incluir las facturas generadas el 30 del mes pasado. (el mes pasado se toma con la opción 'desde' escogida.)" class="icono far fa-question-circle" tabindex="0"></i>
		    <select class="form-control selectpicker" name="incluir">
		    	<option value="1" {{1==$request->tipo?'selected':''}}>Incluir facturas del 30 del mes pasado</option>
		    	<option value="0" {{2==$request->tipo?'selected':''}}>No Incluir</option>
		    </select> --}}
			<button type="button" id="generar" class="btn btn-outline-primary">Generar Reporte</button>
        	<button type="button" id="exportar" class="btn btn-outline-success">Exportar a Excel</button>
	  	</div>
	</div>

	{{-- <div class="row card-description">
		<div class="form-group col-md-4">
        	<button type="button" id="generar" class="btn btn-outline-primary">Generar Reporte</button>
        	<button type="button" id="exportar" class="btn btn-outline-success">Exportar a Excel</button>
	  	</div>
	</div> --}}

    <input type="hidden" name="orderby"id="order_by"  value="2">
    <input type="hidden" name="order" id="order" value="1">
    <input type="hidden" id="form" value="form-reporte">

	<div class="row card-description">
		<div class="col-md-12 table-responsive">
			<table class="table table-striped table-hover " id="table-facturas">
			<thead class="thead-dark">
				<tr>
                    <th>Nro. Factura</th>
                    <th>Cliente <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==1?'':'no_order'}}" campo="1" order="@if($request->orderby==1){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==1){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button> </th>
                    <th>Cédula<button type="button" class="btn btn-link no-padding orderby {{$request->orderby==2?'':'no_order'}}" campo="1" order="@if($request->orderby==1){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==1){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button> </th>
                    <th>Estrato<button type="button" class="btn btn-link no-padding orderby {{$request->orderby==3?'':'no_order'}}" campo="1" order="@if($request->orderby==1){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==1){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button> </th>
                    <th>Municipio<button type="button" class="btn btn-link no-padding orderby {{$request->orderby==4?'':'no_order'}}" campo="1" order="@if($request->orderby==1){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==1){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button> </th>
                    <th>Creación <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==5?'':'no_order'}}" campo="2" order="@if($request->orderby==2){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==2){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
                    <th>Vencimiento <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==6?'':'no_order'}}" campo="3" order="@if($request->orderby==3){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==3){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
					<th>Estado <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==8?'':'no_order'}}" campo="3" order="@if($request->orderby==3){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==3){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
					<th>Subtotal<button type="button" class="btn btn-link no-padding orderby {{$request->orderby==9?'':'no_order'}}" campo="3" order="@if($request->orderby==3){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==3){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
                    <th>Iva<button type="button" class="btn btn-link no-padding orderby {{$request->orderby==10?'':'no_order'}}" campo="3" order="@if($request->orderby==3){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==3){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
                    <th>Total <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==11?'':'no_order'}}" campo="7" order="@if($request->orderby==7){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==7){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button> </th>
	          </tr>
			</thead>
			<tbody>

				@foreach($facturas as $factura)
					<tr>
                        <td><a href="{{route('facturas.show',$factura->id)}}" target="_blank">{{$factura->codigo}}</a> </td>
                        <td><a href="{{route('contactos.show',$factura->cliente()->id)}}" target="_blank">{{$factura->cliente()->nombre}}  {{$factura->cliente()->apellidos()}} @if($factura->cliente()->celular) | {{$factura->cliente()->celular}}@endif</a></td>
                        <td><a href="{{route('contactos.show',$factura->cliente()->id)}}" target="_blank">{{$factura->cliente()->nit}}</a></td>
                        <td>{{$factura->cliente()->estrato}}</td>
                        <td>{{$factura->cliente()->municipio()->nombre}}</td>
                        <td>{{date('d-m-Y', strtotime($factura->fecha))}}</td>
                        <td>{{date('d-m-Y', strtotime($factura->vencimiento))}}</td>
                        <td>{{$factura->estatus()}}</td>
                        <td>{{$factura->total()->subtotal}}</td>
                        <td>{{$factura->total()->valImpuesto}}</td>
                        <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->total()->total - $factura->devoluciones())}}</td>
					</tr>
				@endforeach
			</tbody>
			<tfoot class="thead-dark">
				<td colspan="10"></td>
				<th  class="text-right">Total</th>
				<th>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($total)}}</th>
			</tfoot>

		</table>
            {!! $facturas->render() !!}
	</div>
</div>
</form>
<input type="hidden" id="urlgenerar" value="{{route('reportes.facturasEstandar')}}">
<input type="hidden" id="urlexportar" value="{{route('exportar.facturasEstandar')}}">
@endsection
