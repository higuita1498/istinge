@extends('layouts.app')
@section('content')
    <input type="hidden" id="valuefecha" value="{{$request->fechas}}">
    <input type="hidden" id="primera" value="{{$request->date['primera']}}">
    <input type="hidden" id="ultima" value="{{$request->date['ultima']}}">
    <form id="form-reporte">


        <div class="row card-description">
            <div class="form-group col-md-2 offset-md-1">
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
            <div class="form-group col-md-4" style="    padding-top: 24px;">
                <button type="button" id="generar" class="btn btn-outline-primary">Generar Reporte</button>
                <button type="button" id="exportar" class="btn btn-outline-success">Exportar a Excel</button>
            </div>
        </div>
        <div class="row card-description">
            <div class="col-md-12 table-responsive">
                <table class="table" id="table-facturas">
                <thead class="thead-dark">
                <tr>
                    <th>Fecha</th>
                    <th>Comprobante</th>
                    <th>Cuenta</th>
                    <th>Categoría</th>
                    <th>Estado</th>
                    <th>Salida</th>
                    <th>Entrada</th>
                </tr>
                </thead>
                <tbody>

                @foreach($movimientos as $movimiento)
                    <tr>
                        <td><a href="{{$movimiento->show_url()}}">{{date('d-m-Y', strtotime($movimiento->fecha))}}</a></td>
                        <td>
                            <a href="{{$movimiento->show_url()}}">
                                {{$movimiento->id}}
                            </a>
                        </td>
                        <td>
                            {{$movimiento->banco()->nombre}}
                        </td>
                        <td>
                            {{$movimiento->categoria()}}
                        </td>
                        <td>
                            <spam class="text-{{$movimiento->estatus(true)}}">
                                {{$movimiento->estatus()}}
                            </spam>
                        </td>
                        <td>
                            {{$movimiento->tipo==2?Auth::user()->empresa()->moneda.\App\Funcion::Parsear($movimiento->saldo):''}}
                        </td>
                        <td>
                            {{$movimiento->tipo==1?Auth::user()->empresa()->moneda.\App\Funcion::Parsear($movimiento->saldo):''}}
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot class="thead-dark">
                    <td colspan="5"></td>
                    <th>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($totales['salida'])}}</th>
                    <th>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($totales['entrada'])}}</th>
                </tfoot>
                </table>
                <div class="text-right">
                    {{$movimientos->links()}}
                </div>

            </div>
        </div>
    </form>
    <input type="hidden" id="urlgenerar" value="{{route('reportes.transacciones')}}">
    <input type="hidden" id="urlexportar" value="{{route('exportar.transacciones')}}">
@endsection
