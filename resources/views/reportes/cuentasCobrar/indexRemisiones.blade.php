@extends('layouts.app')
@section('content')
    <input type="hidden" id="primera" value="{{$request->date ? $request->date['primera'] : ''}}">
    <input type="hidden" id="ultima" value="{{$request->date ? $request->date['ultima'] : ''}}">
	@if(Session::has('success'))
		<div class="alert alert-success" >
			{{Session::get('success')}}
		</div>

		<script type="text/javascript">
			setTimeout(function(){
			    $('.active_table').attr('class', ' ');
			}, 5000);
		</script>
	@endif
    <p  class="card-description">Consulta quién te debe, cuánto te debe y la fecha del vencimiento de las facturas de venta.</p>
	<div class="row card-description">
        <form action="" id="form-reporte">
            <div class="row card-description">
                <div class="form-group col-md-2">
                    <label>Numeración</label>
                    <select class="form-control selectpicker" name="nro">
                        <option value="0">Facturas venta</option>
                        <option value="remisiones" selected>REMISIONES</option>
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
                <div class="form-group col-md-4" style="    padding-top: 2%;">
                    <button type="button" id="generar" class="btn btn-outline-secondary">Generar Reporte</button>
                    <button type="button" id="exportar" class="btn btn-outline-secondary">Exportar a Excel</button>
                </div>
            </div>
        </form>


		<div class="col-md-12 table-responsive">
			<table class="table table-striped table-hover " id="table-general">
			<thead class="thead-dark">
				<tr>
	              <th>Número</th>
	              <th>Cliente</th>
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
                        <tr @if($factura->id==Session::get('codigo')) class="active_table" @endif>
                            <td><a href="{{route('remisiones.show',$factura->nro)}}" >{{$factura->nro}}</a></td>
                            <td><div class="elipsis-short" style="width:135px;"><a href="{{route('contactos.show',$factura->clienteId)}}" target="_blanck">{{$factura->clienteNombre}}</a></div></td>
                            <td>{{date('d-m-Y', strtotime($factura->fecha))}}</td>
                            <td class="@if(date('Y-m-d') > $factura->vencimiento && $factura->estatus==1) text-danger @endif">{{date('d-m-Y', strtotime($factura->vencimiento))}}</td>
                            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->total()->total)}}</td>
                            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->pagado())}}</td>
                            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->porpagar())}}</td>
                            <td>
                                <a href="{{route('remisiones.show',$factura->nro)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>
                                <a href="{{route('remisiones.imprimir',['id' => $factura->nro, 'name'=> 'Remision No. '.$factura->nro.'.pdf'])}}" target="_blank" class="btn btn-outline-primary btn-icons"title="Imprimir"><i class="fas fa-print"></i></a>
                                <a  href="{{route('ingresosr.create_id', ['cliente'=>$factura->cliente()->id, 'factura'=>$factura->nro])}}" class="btn btn-outline-primary btn-icons" title="Agregar pago"><i class="fas fa-money-bill"></i></a>
                            </td>
                        </tr>
                    @endif
				@endforeach
			</tbody>
                <tfoot class="thead-dark">
                <td colspan="6"></td>
                <th  class="text-right">Total por cobrar</th>
                <th>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($totalPagar)}}</th>
                </tfoot>
		</table>
	</div>
    <input type="hidden" id="urlgenerar" value="{{route('reportes.cuentasCobrar')}}">
    <input type="hidden" id="urlexportar" value="{{route('exportar.cuentasCobrar')}}">
@endsection
