@extends('layouts.app')
@section('content')
    <input type="hidden" id="valuefecha" value="{{$request->fechas}}">
    <div class="row card-description">
        <div class="col-md-12 ">
            <p  class="card-description">Consulta el total de facturación y cantidad vendida por cada uno de tus items en un período de tiempo, sin importar si la factura de venta ya se pagó o no </p>
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

            <div class="form-group col-md-2">
                <label>Item</label>
                <select class="form-control selectpicker" name="item" id="item" data-size="5" data-live-search="true">
                    <option value="0">Ninguno</option>
                    @foreach($productos as $pr)
                        <option value="{{$pr->id}}" {{$pr->id==$request->item?'selected':''}}>{{$pr->producto}}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-4" style=" padding-top: 24px;">
                <button type="button" id="generar" class="btn btn-outline-primary">Generar Reporte</button>
                <button type="button" id="exportar" class="btn btn-outline-success">Exportar a Excel</button>
              </div>
        </div>
        <div class="row card-description">
            <div class="col-md-12 table-responsive">
                <table class="table table-striped table-hover " id="table-ventas-item">
                    <thead class="thead-dark">
                    <tr>
                        <th>Ítem</th>
                        <th>Referencia</th>
                        <th>Factura Relacionada</th>
                        <th>Valor Item</th>
                        <th>Antes de Impuestos</th>
                        <th>Después de Impuestos</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($items as $item)
                        <tr>
                            <td><a href="{{route('inventario.show',$item->id)}}" target="_blanck">{{$item->producto}}</a> </td>
                            <td><a href="{{route('inventario.show',$item->id)}}" target="_blanck">{{$item->ref}}</a></td>
                            <td>{{ $item->codigo }}</td>
                            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($item->precio)}}</td>
                            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($item->cant * $item->precio)}}</td>
                            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($item->despuesIva)}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                    <tfoot class="thead-dark">
                    <td colspan="5"></td>
                    <th>Total: {{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($totalFacturado)}}</th>
                    </tfoot>

                </table>
                <div class="text-right">
                    {{$items->links()}}
                </div>

            </div>
        </div>
    </form>
    <input type="hidden" id="urlgenerar" value="{{route('reportes.ventasItem')}}">
    <input type="hidden" id="urlexportar" value="{{route('exportar.ventasItem')}}">
@endsection
