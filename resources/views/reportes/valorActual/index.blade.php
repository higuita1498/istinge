@extends('layouts.app')
@section('content')
    <input type="hidden" id="valuefecha" value="{{$request->fechas}}">
    <div class="row card-description">
        <div class="col-md-12 ">
            <p  class="card-description">Consulta el valor del inventario actual, la cantidad de items inventariables que tienes y su costo promedio.</p>
        </div>
    </div>
    <form id="form-reporte">


        <div class="row card-description">
            <div class="form-group col-md-4">
                <label>Bodega: </label>
                <select class="form-control" name="bodega">
                    <option value="all" @if($actualBodega == 'all') selected @endif>Todas</option>
                    @foreach($bodegas as $bodega)
                        <option @if($actualBodega->id == $bodega->id) selected @endif value="{{$bodega->id}}">{{$bodega->bodega}}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4" style="    padding-top: 2%;">
                <button type="button" id="generar" class="btn btn-outline-secondary">Generar Reporte</button>
                <button type="button" id="exportar" class="btn btn-outline-secondary">Exportar a Excel</button>
            </div>
        </div>
        <div class="row card-description">
            <div class="col-md-12 ">
                <table class="table table-responsive table-striped table-hover " id="table-general">
                    <thead class="thead-dark">
                    <tr>
                        <th>Ítem</th>
                        <th>Referencia</th>
                        <th>Descripción</th>
                        <th>Cantidad</th>
                        <th>Unidad</th>
                        <th>Estado</th>
                        <th width="10%">Costo</th>
                        <th width="10%">Total</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($productos as $producto)
                        <tr>
                            <td><a href="{{route('inventario.show',$producto->id)}}" target="_blanck">{{$producto->producto}}</a> </td>
                            <td><a href="{{route('inventario.show',$producto->id)}}" target="_blanck">{{$producto->ref}}</a></td>
                            <td>{{$producto->descripcion}}</td>
                            <td>{{$producto->inventario}}</td>
                            <td>{{$producto->unidad()}}</td>
                            <td>{{$producto->status()}}</td>
                            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($producto->costo_unidad)}}</td>
                            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($producto->total)}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                    <div class="card text-right">
                        <div class="card-body">
                            <h5 class="card-text">
                                <strong>Valor inventario:</strong> <br>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($total)}}
                            </h5>

                        </div>
                    </div>


            </div>
        </div>
    </form>
    <input type="hidden" id="urlgenerar" value="{{route('reportes.valorActual')}}">
    <input type="hidden" id="urlexportar" value="{{route('exportar.valorActual')}}">
@endsection
