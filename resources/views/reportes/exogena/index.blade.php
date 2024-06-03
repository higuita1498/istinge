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
                    <th>Concepto</th>
                    <th>tipo Doc. <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==1?'':'no_order'}}" campo="1" order="@if($request->orderby==1){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==1){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button> </th>
                    <th>N. Id<button type="button" class="btn btn-link no-padding orderby {{$request->orderby==2?'':'no_order'}}" campo="2" order="@if($request->orderby==2){{$request->order==2?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==2){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
                    <th>Primer Apellido</th>
                    <th>Segundo Apellido <button type="button" class="btn btn-link no-padding orderby {{$request->orderby==7?'':'no_order'}}" campo="7" order="@if($request->orderby==7){{$request->order==1?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==7){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button> </th>
                    <th>Primer Nombre<button type="button" class="btn btn-link no-padding orderby {{$request->orderby==2?'':'no_order'}}" campo="2" order="@if($request->orderby==2){{$request->order==2?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==2){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
                    <th>Otros Nombres<button type="button" class="btn btn-link no-padding orderby {{$request->orderby==2?'':'no_order'}}" campo="2" order="@if($request->orderby==2){{$request->order==2?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==2){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
                    <th>Razon Social<button type="button" class="btn btn-link no-padding orderby {{$request->orderby==2?'':'no_order'}}" campo="2" order="@if($request->orderby==2){{$request->order==2?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==2){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
                    <th>Pais Residencia<button type="button" class="btn btn-link no-padding orderby {{$request->orderby==2?'':'no_order'}}" campo="2" order="@if($request->orderby==2){{$request->order==2?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==2){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
                    <th>Ingresos Brutos<button type="button" class="btn btn-link no-padding orderby {{$request->orderby==2?'':'no_order'}}" campo="2" order="@if($request->orderby==2){{$request->order==2?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==2){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
                    <th>Dev, Reb, Desc<button type="button" class="btn btn-link no-padding orderby {{$request->orderby==2?'':'no_order'}}" campo="2" order="@if($request->orderby==2){{$request->order==2?'0':'1'}}@else 0 @endif" ><i class="fas fa-arrow-@if($request->orderby==2){{$request->order==0?'up':'down'}}@else{{'down'}} @endif"></i></button></th>
	          </tr>
			</thead>
			<tbody>

				@foreach($contactos as $cont)
                    @php $swIngresos = $cont->ingresosBrutos >= 500000 ? 1 : 0; @endphp
					<tr>
                        <td></td>
                        <td>{{$swIngresos == 1 ? 13 : 31}}</td>
                        <td>{{$swIngresos == 1 ? $cont->nit : '222222222'}}</td>
                        <td>{{$swIngresos == 1 ? $cont->apellido1 : ''}}</td>
                        <td>{{$swIngresos == 1 ? $cont->apellido2 : ''}}</td>
                        <td>{{$swIngresos == 1 ? $cont->nombre : ''}}</td>
                        <td></td>
                        <td>{{$swIngresos == 0 ? $empresa->nombre : '' }}</td>
                        <td>169</td>
                        <td>{{App\Funcion::Parsear($cont->ingresosBrutos)}}</td>
                        <td>0</td>
					</tr>
				@endforeach
			</tbody>
			<tfoot class="thead-dark">
				{{-- <td colspan="3"></td>
				<th  class="text-right">Total</th>
				<th>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($total)}}</th> --}}
			</tfoot>

		</table>
            {!! $contactos->render() !!}
	</div>
</div>
</form>
<input type="hidden" id="urlgenerar" value="{{route('reportes.exogena')}}">
<input type="hidden" id="urlexportar" value="{{route('exportar.exogena')}}">
@endsection
