@extends('layouts.app')
@section('content')
    <input type="hidden" id="primera" value="{{$request->date ? $request->date['primera'] : ''}}">
    <input type="hidden" id="ultima" value="{{$request->date ? $request->date['ultima'] : ''}}">
    <input type="hidden" id="valuefecha" value="{{$request->fechas}}">
    <div class="row card-description">
        <div class="col-md-12 ">
            <h2><i class="fas fa-shopping-cart"></i> Totales Pagos de Facturas y Remisiones</h2>

        </div>
    </div>
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
            <div class="form-group col-md-4" style="    padding-top: 2%;">
                <button type="button" id="generar" class="btn btn-outline-secondary">Generar Reporte</button>
                <button type="button" id="exportar" class="btn btn-outline-secondary">Exportar a Excel</button>
            </div>
        </div>
        <div class="row card-description">
            <div class="col-md-12 table-responsive">
                <table class="table table-striped table-hover " id="table-general">
                    <thead class="thead-dark">
                    <tr>

                        <th colspan="3"></th>
                        <th>Remisiones</th>
                        <th>Facturas</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th colspan="3"></th>
                            <td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($totales['totalRemision'])}}</td>
                            <td>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($totales['totalFactura'])}}</td>
                        </tr>

                    </tbody>
                    <tfoot class="thead-dark">
                  {{--  <td colspan="3"></td>
                    <th>Total Retencion: </th>
                    <th>Total IVA: </th>--}}
                    </tfoot>

                </table>
                <div class="text-right">

                </div>

            </div>
        </div>
    </form>
    <input type="hidden" id="urlgenerar" value="">
    <input type="hidden" id="urlexportar" value="">
@endsection
