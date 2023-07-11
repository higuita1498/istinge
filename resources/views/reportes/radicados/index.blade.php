@extends('layouts.app')
@section('content')
    <input type="hidden" id="valuefecha" value="{{$request->fechas}}">
    <input type="hidden" id="primera" value="{{$request->date ? $request->date['primera'] : ''}}">
    <input type="hidden" id="ultima" value="{{$request->date ? $request->date['ultima'] : ''}}">
    <form id="form-reporte">
        <div class="row card-description mb-0 pb-0">
            <div class="form-group col-md-2">
                <label>Técnico</label>
                <select class="form-control selectpicker" name="tecnico" id="tecnico" title="Seleccione" data-live-search="true" data-size="6">
                    @foreach($tecnicos as $tecnico)
                        <option value="{{$tecnico->id}}" {{$request->tecnico==$tecnico->id?'selected':''}}>{{$tecnico->nombres}}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                <label>Servicio</label>
                <select class="form-control selectpicker" name="servicio" id="servicio" title="Seleccione" data-live-search="true" data-size="6">
                    @foreach($servicios as $servicio)
                        <option value="{{$servicio->id}}" {{$request->servicio==$servicio->id?'selected':''}}>{{$servicio->nombre}}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-2">
                <label>Estatus</label>
                <select class="form-control selectpicker" name="estatus" title="Seleccione" data-size="4">
                    <option value="0" >Pendientes</option>
                    <option value="1" {{$request->estatus==1?'selected':''}}>Solventados</option>
                    <option value="2" {{$request->estatus==2?'selected':''}}>Escalados / Pendientes</option>
                    <option value="3" {{$request->estatus==3?'selected':''}}>Escalados / Solventados</option>
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

            <div class="form-group col-md-4 text-center offset-md-4 mb-0">
                <center><button type="button" id="generar" class="btn btn-outline-primary">Generar Reporte</button>
                <button type="button" id="exportar" class="btn btn-outline-success">Exportar a Excel</button></center>
            </div>

            <div class="form-group col-md-12 text-left mb-0">
                <p class="font-weight-bold mt-2 mb-0" style="font-size: 1em;">Número de Radicados {{ count($movimientosTodos) }}</p>
            </div>
        </div>
        <div class="row card-description">
            <div class="col-md-12 table-responsive">
                <table class="table" id="table-facturas">
                <thead class="thead-dark">
                <tr>
                    <th class="text-center">N° Radicado</th>
                    <th class="text-center">Fecha</th>
                    <th>Cliente</th>
                    <th class="text-center">Servicio</th>
                    <th class="text-center">Técnico</th>
                    <th class="text-center">Nro Radicados</th>
                    <th class="text-center">Estatus</th>
                </tr>
                </thead>
                <tbody>

                @foreach($movimientos as $movimiento)
                    <tr>
                        <td class="text-center"><a href="{{route('radicados.show',$movimiento->id)}}" target="_blank">{{ $movimiento->codigo }}</a></td>
                        <td class="text-center">{{date('d-m-Y', strtotime($movimiento->fecha))}}</td>
                        <td>{{ $movimiento->nombre }}</td>
                        <td class="text-center">{{ $movimiento->servicio()->nombre }}</td>
                        <td class="text-center">{{ $movimiento->tecnico_reporte() }}</td>
                        <td class="text-center">{{ $movimiento->nro_radicados() }}</td>
                        <td class="text-center">
                            @if ($movimiento->estatus == 0)
							    <span class="text-danger font-weight-bold">Pendiente</span>
							@endif
							@if ($movimiento->estatus == 1)
							    <span class="text-success font-weight-bold">Solventado</span>
							@endif
							@if ($movimiento->estatus == 2)
							    <span class="text-danger font-weight-bold">Escalado / Pendiente</span>
							@endif
							@if ($movimiento->estatus == 3)
							    <span class="text-success font-weight-bold">Escalado / Solventado</span>
							@endif
						</td>
                    </tr>
                @endforeach
                </tbody>
                </table>
                <div class="text-right">
                    {{$movimientos->links()}}
                </div>

            </div>
        </div>
    </form>
    <input type="hidden" id="urlgenerar" value="{{route('reportes.radicados')}}">
    <input type="hidden" id="urlexportar" value="{{route('exportar.radicados')}}">
@endsection
