@extends('layouts.app')
@section('content')
    <input type="hidden" id="valuefecha" value="{{$request->fechas}}">
        <input type="hidden" id="primera" value="{{$example->date['primera']}}">
    <input type="hidden" id="ultima" value="{{$example->date['ultima']}}">

    @if(count($clienteFacturas) == 0)
        <div class="card-description">
            <p>No se han obtenido resultados, verifique que el cliente tenga facturas en su cuenta o modifique el periodo de consulta</p>
            
        </div>
    @endif

    <form action="" id="form-reporte">
        <input type="hidden" value="{{$request->client}}" name="client">
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
    <div class="row card-description">
        <div class="col-md-12">
            <table class="table table-striped table-hover " id="table-general">
                <thead class="thead-dark">
                <tr>
                    <th>Número</th>
                    <th>Tipo de documen</th>
                    <th>Creación</th>
                    <th>Vencimiento</th>
                    <th>Días vencidos</th>
                    <th>Estado</th>
                    <th>Total</th>
                    <th>Pagado</th>
                    <th>Por pagar</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                    @foreach($clienteFacturas as $clienteFactura)
                        <tr>
                            <td>{{$clienteFactura->codigo}}</td>
                            <td>
                                {{$clienteFactura->typeName}}
                            </td>
                            <td>{{$clienteFactura->fecha}}</td>
                            <td>{{$clienteFactura->vencimiento}}</td>
                            
                            @php
                                $time = \App\Funcion::diffDates(date('Y-m-d'), $clienteFactura->vencimiento);
                                $pay = $clienteFactura->porpagar() - $clienteFactura->pagado() > 0  ? "Por pagar" : "Pagado";
                            @endphp

                            <td @if($time!=0) class="text-danger" @endif>{{$time}}</td>
                            <td @if($pay == 'Por pagar') class="text-danger" @endif>{{$pay}}</td>
                            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($clienteFactura->total()->total)}}</td>
                            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($clienteFactura->pagado())}}</td>
                            <td>{{Auth::user()->empresa()->moneda}}{{App\Funcion::Parsear($clienteFactura->porpagar())}}</td>
                            <td>
                                <a href="{{route('facturas.show',$clienteFactura->nro)}}" class="btn btn-outline-info btn-icons" title="Ver"><i class="far fa-eye"></i></a>
                                <a href="{{route('facturas.imprimir',['id' => $clienteFactura->nro, 'name'=> 'Factura No. '.$clienteFactura->codigo.'.pdf'])}}" target="_blank" class="btn btn-outline-primary btn-icons"title="Imprimir"><i class="fas fa-print"></i></a>
                                @if($clienteFactura->estatus==1)
                                    <a  href="{{route('ingresos.create_id', ['cliente'=>$clienteFactura->cliente, 'factura'=>$clienteFactura->nro])}}" class="btn btn-outline-primary btn-icons" title="Agregar pago"><i class="fas fa-money-bill"></i></a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="thead-dark">
                    <td colspan="5"></td>
                    <th  class="text-right">Total</th>
                    <th>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($totales['total'])}}</th>
                    <th>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($totales['pagado'])}}</th>
                    <th>{{Auth::user()->empresa()->moneda}} {{App\Funcion::Parsear($totales['porPagar'])}}</th>
                </tfoot>
            </table>
            <div class="text-right">
            </div>

        </div>
    </div>
    <input type="hidden" id="urlgenerar" value="{{route('reportes.estadoClienteShow')}}">
    <input type="hidden" id="urlexportar" value="{{route('exportar.estadoCliente')}}">
@endsection
