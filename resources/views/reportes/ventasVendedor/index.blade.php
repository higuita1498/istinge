@extends('layouts.app')
@section('content')

    <input type="hidden" id="valuefecha" value="{{$request->fechas}}">
        <input type="hidden" id="primera" value="{{$example->date['primera']}}">
    <input type="hidden" id="ultima" value="{{$example->date['ultima']}}">
    <form id="form-reporte">

        <div class="row card-description">
            <p>Consulta las facturas de venta asociadas a tus vendedores en el periodo de tiempo que elijas </p>
        </div>
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
        <div class="row card-description">
            <div class="col-md-12 table-responsive">
                <table class="table table-striped table-hover " id="table-facturas">
                    <thead class="thead-dark">
                    <tr>
                        <th>Vendedor</th>
                        <th>Número de facturas</th>
                        <th>Pagado</th>
                        <th>Antes de impuestos</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($vendedores as $vendedor)
                        <tr>
                            <td>{{$vendedor->nombre}}</td>
                            <td>{{$vendedor->nroFacturas($dates['inicio'], $dates['fin'])}}</td>
                            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($vendedor->pagosFecha($dates['inicio'], $dates['fin']))}}</td>
                            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($vendedor->montoTotal($dates['inicio'], $dates['fin'])['subtotal'])}}</td>
                            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($vendedor->montoTotal($dates['inicio'], $dates['fin'])['total'])}}</td>
                            <td></td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot class="thead-dark">
                        <td colspan="2"></td>
                        <th>Pagado: {{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($totales['pagado'])}}</th>
                        <th>Subtotal: {{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($totales['subtotal'])}}</th>
                        <th>Total: {{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($totales['total'])}}</th>
                        <th></th>
                    </tfoot>
                </table>
                <div class="text-right">
                </div>

            </div>
        </div>
        
<!--Modificado 12/06/2019-->        
        <div class="row card-description">
            <div class="col-md-12 table-responsive">
                <table class="table table-striped table-hover " id="table-facturas">
                    <thead class="thead-dark">
                    <tr>
                        <th>Vendedor</th>
                        <th>Número de remisiones</th>
                        <th>Pagado</th>
                        <th>Antes de Impuesto</th>
                        <th>Total</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($vendedores as $vendedor)
                        <tr>
                            <td>{{$vendedor->nombre}}</td>
                            <td>{{$vendedor->nroRemisiones($dates['inicio'], $dates['fin'])}}</td>
                            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($vendedor->pagosFechaR($dates['inicio'], $dates['fin']))}}</td>
                            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($vendedor->montoTotalR($dates['inicio'], $dates['fin'])['subtotalR'])}}</td>
                            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($vendedor->montoTotalR($dates['inicio'], $dates['fin'])['totalR'])}}</td>
                            <td></td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot class="thead-dark">
                    <td colspan="2"></td>
                    <th>Pagado: {{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($totales['pagadoR'])}}</th>
                    <th>Total: {{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($totales['subtotalR'])}}</th>
                    <th>Total: {{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($totales['totalR'])}}</th>
                    <th></th>
                    </tfoot>
                </table>
                <div class="text-right">
                </div>

            </div>
        </div>
        
        <div class="row card-description">
            <div class="col-md-12 table-responsive">
                <table class="table table-striped table-hover " id="table-facturas">
                    <thead class="thead-dark">
                    <tr>
                        <th>Vendedor</th>
                        <th>Total Ventas</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($vendedores as $vendedor)
                        <tr>
                            <td>{{$vendedor->nombre}}</td>
                            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($vendedor->montoTotalR($dates['inicio'], $dates['fin'])['totalR']+$vendedor->montoTotal($dates['inicio'], $dates['fin'])['total'])}}</td>
                            
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot class="thead-dark">
                    <th></th>
                    <th></th>
                    </tfoot>
                </table>
                <div class="text-right">
                </div>

            </div>
        </div>
        
    </form>
    <input type="hidden" id="urlgenerar" value="{{route('reportes.ventasVendedor')}}">
    <input type="hidden" id="urlexportar" value="{{route('exportar.ventasVendedor')}}">
@endsection
