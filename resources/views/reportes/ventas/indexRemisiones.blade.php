@extends('layouts.app')
@section('content')
    <input type="hidden" id="valuefecha" value="{{$request->fechas}}">
    <input type="hidden" id="primera" value="{{$request->date ? $request->date['primera'] : ''}}">
    <input type="hidden" id="ultima" value="{{$request->date ? $request->date['ultima'] : ''}}">
    <div class="row card-description">
        <div class="col-md-12 ">
            <h2><i class="fas fa-shopping-cart"></i> Ventas - Remisiones</h2>
            <p  class="card-description">Consulta el detalle de las facturas de venta en un período de tiempo.</p>
        </div>
    </div>
    <form id="form-reporte">


        <div class="row card-description">
            <div class="form-group col-md-2">
                <label>Numeración</label>
                <select class="form-control selectpicker" name="nro">
                    <option value="0">Todas</option>
                    @foreach($numeraciones as $nro)
                        <option value="{{$nro->id}}" {{$nro->id==$request->nro?'selected':''}}>{{$nro->nombre}}</option>
                    @endforeach
                    <option value="remisiones" selected="selected">REMISIONES</option>
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
        <div class="row card-description">
            <div class="col-md-12 table-responsive">
                <table class="table table-striped table-hover " id="table-facturas">
                    <thead class="thead-dark">
                    <tr>
                        <th>Número</th>
                        <th>Cliente</th>
                        <th>Creación</th>
                        <th>Antes de Impuestos</th>
                        <th>Después de Impuestos</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($facturas as $factura)
                        <tr>
                            <td><a href="{{route('facturas.show',$factura->nro)}}" target="_blanck">{{$factura->nro}}</a> </td>
                            <td><a href="{{route('contactos.show',$factura->cliente()->id)}}" target="_blanck">{{$factura->cliente()->nombre}}</a></td>
                            <td>{{date('d-m-Y', strtotime($factura->fecha))}}</td>
                            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->total()->subsub)}}</td>
                            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($factura->total()->total)}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot class="thead-dark">
                    <td colspan="2"></td>
                    <th  class="text-right">Total</th>
                    <th>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($subtotal)}}</th>
                    <th>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($total)}}</th>
                    </tfoot>

                </table>
                <div class="text-right">
                    {{$facturas->links()}}
                </div>

            </div>
        </div>
    </form>
    <input type="hidden" id="urlgenerar" value="{{route('reportes.ventas')}}">
    <input type="hidden" id="urlexportar" value="{{route('exportar.ventas')}}">
@endsection
