@extends('layouts.app')
@section('content')
    <input type="hidden" id="primera" value="{{$request->date ? $request->date['primera'] : ''}}">
    <input type="hidden" id="ultima" value="{{$request->date ? $request->date['ultima'] : ''}}">
	@if(Session::has('success'))
		<div class="alert alert-success" style="margin-left: 2%;margin-right: 2%;">
			{{Session::get('success')}}
		</div>
	@endif
	<form id="form-table-facturasp">
		<input type="hidden" name="orderby"id="order_by"  value="1">
		<input type="hidden" name="order" id="order" value="0">
		<input type="hidden" id="form" value="form-table-facturasp">
	</form>
	<div class="row card-description">
        <form action="" id="form-reporte">
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
                <div class="form-group col-md-4" style="    padding-top: 2%;">
                    <button type="button" id="generar" class="btn btn-outline-secondary">Generar Reporte</button>
                    <button type="button" id="exportar" class="btn btn-outline-secondary">Exportar a Excel</button>
                </div>
            </div>
        </form>
		<div class="col-md-12">
			<table class="table table-striped table-hover " id="table-general">
			<thead class="thead-dark">
				<tr>
	              <th class="resp-cxpagar">Nro</th>
	              <th class="resp-cxpagar">Factura</th>
	              <th>Proveedor</th>
	              <th>Creación</th>
	              <th>Vencimiento</th>
	              <th>Total</th>
	              <th>Pagado</th>
	              <th>Por Pagar</th>
	              <th>Acciones</th>
	          </tr>
			</thead>
			<tbody >
				@foreach($facturas as $factura)

                    @if(App\Funcion::Parsear($factura->porpagar()) > 0 )
                        <tr @if($factura->id==Session::get('factura_id')) class="active" @endif>
                            <td><div class="elipsis-short-number"><a href="{{route('facturasp.show',$factura->nro)}}" target="_blanck">{{$factura->nro}}</a></div></td>
                            <td><a href="{{route('facturasp.show',$factura->nro)}}" target="_blanck">{{$factura->codigo}}</a></td>
                            <td><div class="elipsis-short"><a href="{{route('contactos.show',$factura->proveedor()->id)}}" target="_blanck">{{$factura->proveedor()->nombre}}</a></div></td>
                            <td>{{date('d-m-Y', strtotime($factura->fecha_factura))}}</td>
                            <td>{{date('d-m-Y', strtotime($factura->vencimiento_factura))}}</td>
                            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->total()->total)}}</td>
                            <td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($factura->pagado())}}</td>
                            <td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($factura->porpagar())}}</td>
                            <td>
                                <a   href="{{route('facturasp.show',$factura->nro)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></i></a>
                                @if($factura->tipo ==1 && $factura->estatus==1)
                                    <a  href="{{route('pagos.create_id', ['cliente'=>$factura->proveedor, 'factura'=>$factura->nro])}}" class="btn btn-outline-primary btn-icons" title="Agregar pago"><i class="fas fa-money-bill"></i></a>
                                @endif
                            </td>
                        </tr>
                    @endif
				@endforeach
			</tbody>
                <tfoot class="thead-dark">
                <td colspan="7"></td>
                <th  class="text-right">Total a pagar: </th>
                <th>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($totalPagar)}}</th>
                </tfoot>
		</table>
		</div>
	</div>
    <input type="hidden" id="urlgenerar" value="{{route('reportes.cuentasPagar')}}">
    <input type="hidden" id="urlexportar" value="{{route('exportar.cuentasPagar')}}">
@endsection
