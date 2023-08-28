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
	  	<div class="form-group col-md-2">
		    <label>Caja</label>
		    <select class="form-control selectpicker" name="caja" id="caja" title="Seleccione" data-live-search="true" data-size="6">
		    	@foreach($cajas as $caja)
		    		<option value="{{$caja->id}}" {{$caja->id==$request->caja?'selected':''}}>{{$caja->nombre}}</option>
		    	@endforeach
		    </select>
	  	</div>
		@if(isset($gruposCorte))
		<div class="form-group col-md-2">
			<label>Grupos corte</label>
			<select class="form-control selectpicker" name="grupo" id="grupo">
				@foreach($gruposCorte as $gp)
					<option value="{{$gp->id}}" {{$gp->id == $request->grupo ? 'selected' : ''}}> {{$gp->nombre}} </option>
				@endforeach
				<option {{ !$request->grupo ? 'selected' : ''}} value="">TODOS</option>
			</select>
		</div>
		@endif

		<div class="form-group col-md-2">
			<label>Formas de pago</label>
			<select class="form-control selectpicker" name="formapago" id="formapago">
				@foreach($formasPago as $fp)
					<option value="{{$fp->id}}" {{$fp->id == $request->formapago ? 'selected' : ''}}> {{$fp->nombre}} </option>
				@endforeach
				<option {{ !$request->formapago ? 'selected' : ''}} value="">TODOS</option>
			</select>
		</div>

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
                    <th>Nro. Factura</th>
                    <th>Cliente <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==1?'':'no_order'}}" campo="1" order="@if($request->orderby==1){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==1){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button> </th>
                    {{-- <th>Creación <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==2?'':'no_order'}}" campo="2" order="@if($request->orderby==2){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==2){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
                    <th>Vencimiento <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==3?'':'no_order'}}" campo="3" order="@if($request->orderby==3){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==3){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th> --}}
                    <th>Pagada <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==4?'':'no_order'}}" campo="4" order="@if($request->orderby==4){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==4){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
                    <th>Caja</th>
                    <th>Total <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==7?'':'no_order'}}" campo="7" order="@if($request->orderby==7){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==7){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button> </th>
	          </tr>
			</thead>
			<tbody>

				@foreach($facturas as $factura)
					<tr>
                        <td><a href="{{route('facturas.show',$factura->id)}}" target="_blank">{{$factura->codigo}}</a> </td>
                        <td><a href="{{route('contactos.show',$factura->cliente()->id)}}" target="_blank">{{$factura->cliente()->nombre}} {{$factura->cliente()->apellidos()}}</a></td>
                        {{-- <td>{{date('d-m-Y', strtotime($factura->fecha))}}</td>
                        <td>{{date('d-m-Y', strtotime($factura->vencimiento))}}</td> --}}
                        <td>{{date('d-m-Y', strtotime($factura->pagada))}}</td>
                        <td>{{$factura->banco()->nombre}}</td>
                        <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->total()->total - $factura->devoluciones())}}</td>
					</tr>
				@endforeach
			</tbody>
			<tfoot class="thead-dark">
				<td colspan="3"></td>
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
