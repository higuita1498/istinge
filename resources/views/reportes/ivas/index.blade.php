@extends('layouts.app')
@section('content')
    <input type="hidden" id="valuefecha" value="{{$request->fechas}}">
    <input type="hidden" id="primera" value="{{$request->date ? $request->date['primera'] : ''}}">
    <input type="hidden" id="ultima" value="{{$request->date ? $request->date['ultima'] : ''}}">
    <form id="form-reporte">
        <div class="row card-description">
            <div class="form-group col-md-4">
                <label>Documento</label>
                <select class="form-control selectpicker" name="documento" id="documento" title="Seleccione" data-live-search="true" data-size="6">
                    <option value="1" {{1==$request->documento?'selected':''}}>Facturas</option>
                    <option value="2" {{2==$request->documento?'selected':''}}>Notas Crédito</option>
                </select>
            </div>
            <div class="form-group col-md-4">
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
            <div class="form-group col-md-4 text-center offset-md-4">
                <center><button type="button" id="generar" class="btn btn-outline-primary">Generar Reporte</button>
                <button type="button" id="exportar" class="btn btn-outline-success">Exportar a Excel</button></center>
            </div>
        </div>
        <div class="row card-description">
            <div class="col-md-12 table-responsive">
                <table class="table" id="table-facturas">
                <thead class="thead-dark">
                <tr>
                    <th>Nro.</th>
                    <th>Cliente</th>
                    <th>Fecha</th>
                    <th>Iva</th>
                </tr>
                </thead>
                <tbody>

                @foreach($documentos as $documento)
                    <tr>
                        <td><a href="">{{$documento->nro}}</a></td>
                        <td><a href="">{{$documento->cliente()->nombre}}</a></td>
                        <td>{{date('d-m-Y', strtotime($documento->fecha))}}</td>
                        <td>{{$documento->impuestos_totales()}}</td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot class="thead-dark">
                    <td colspan="3"></td>
                    <th>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($totalIva)}}</th>
                </tfoot>
                </table>
                <div class="text-right">
                    {{$documentos->links()}}
                </div>
            </div>
        </div>
    </form>
    <input type="hidden" id="urlgenerar" value="{{route('reportes.ivas')}}">
    <input type="hidden" id="urlexportar" value="{{route('exportar.ivas')}}">
@endsection
