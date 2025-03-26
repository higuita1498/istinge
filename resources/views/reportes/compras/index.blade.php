@extends('layouts.app')
@section('content')
    <input type="hidden" id="primera" value="{{$request->date ? $request->date['primera'] : ''}}">
    <input type="hidden" id="ultima" value="{{$request->date ? $request->date['ultima'] : ''}}">
	@if(Session::has('success'))
		<div class="alert alert-success" style="margin-left: 2%;margin-right: 2%;">
			{{Session::get('success')}}
		</div>
	@endif
    <p  class="card-description">Consulta el detalle de las facturas de proveedor en un período de tiempo .</p>
	<form id="form-table-facturasp">
		<input type="hidden" name="orderby"id="order_by"  value="1">
		<input type="hidden" name="order" id="order" value="0">
		<input type="hidden" id="form" value="form-table-facturasp">
	</form>
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
                <div class="form-group col-md-4" style=" padding-top: 24px;">
                    <button type="button" id="generar" class="btn btn-outline-primary">Generar Reporte</button>
                    <button type="button" id="exportar" class="btn btn-outline-success">Exportar a Excel</button>
                </div>
        </div>
        </form>
		<div class="col-md-12 table-responsive">
			<table class="table table-striped table-hover" id="table-compras2">
			<thead class="thead-dark">
				<tr>
                    <th>Nro </th>
                    <th>Factura </th>
                    <th>Proveedor </th>
                    <th>Creación </th>
                    <th>Vencimiento </th>
                    <th>Subtotal </th>
                    <th>IVA </th>
                    <th>Retencion  </th>
                    <th>Total</th>
                    <th>Acciones</th>
	          </tr>
			</thead>
			<tbody >
				@foreach($facturas as $factura)
					<tr @if($factura->id==Session::get('factura_id')) class="active" @endif>
                        <td>{{$factura->nro}}</td>
                        <td><a href="{{route('facturasp.showid',$factura->id)}}" target="_blanck">{{$factura->codigo}}</a></td>
                        <td><a href="{{route('contactos.show',$factura->proveedor()->id)}}" target="_blanck">{{$factura->proveedor()->nombre}}</a></td>
                        <td>{{date('d-m-Y', strtotime($factura->fecha_factura))}}</td>
                        <td>{{date('d-m-Y', strtotime($factura->vencimiento))}}</td>
                        <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->total()->subsub)}}</td>
                        <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->impuestos_totales())}}</td>
                        <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->retenido())}}</td>
                        <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->total()->total)}}</td>
                        <td>
                            <a   href="{{route('facturasp.show',$factura->nro)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></i></a>
                            @if($factura->tipo ==1 && $factura->estatus==1)
                                <a  href="{{route('pagos.create_id', ['cliente'=>$factura->proveedor, 'factura'=>$factura->nro])}}" class="btn btn-outline-primary btn-icons" title="Agregar pago"><i class="fas fa-money-bill"></i></a>

                            @endif
                        </td>
					</tr>
				@endforeach
			</tbody>
            <tfoot class="thead-dark">
                <td colspan="7"></td>
                <th  class="text-right">Total</th>
                <th>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($totalPagar)}}</th>
            </tfoot>
		</table>
		</div>
    <input type="hidden" id="urlgenerar" value="{{route('reportes.compras')}}">
    <input type="hidden" id="urlexportar" value="{{route('exportar.compras')}}">
@endsection

@section('scripts')
    <script !src="">
        $('#table-compras2').DataTable();
    </script>
@endsection
