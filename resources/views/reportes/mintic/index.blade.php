@extends('layouts.app')
@section('content')
    <input type="hidden" id="valuefecha" value="{{$request->fechas}}">
    <input type="hidden" id="primera" value="{{$request->date ? $request->date['primera'] : ''}}">
    <input type="hidden" id="ultima" value="{{$request->date ? $request->date['ultima'] : ''}}">
    <form id="form-reporte">
        <div class="row card-description">
            <div class="form-group col-md-2 offset-md-3">
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
                    <th>Fecha</th>
                    <th>Trimestre</th>
                    <th>Id municipio</th>
                    <th>Id segemento planes</th>
                    <th>Cantidad de suscriptores</th>
                    <th>Nombre del plan</th>
                    <th>Valor plan iva</th>
                    <th>Valor plan</th>
                    <th>Id modalidad plan</th>
                    <th>Fecha inicio</th>
                    <th>Fecha fin</th>
                    <th>Id tipo plan</th>
                    <th>Tiene telefonia fija</th>
                    <th>Tarifa telefonia fija</th>
                    <th>Cantidad minutos</th>
                    <th>Valor minuto inlcuido telefonia</th>
                    <th>Valor minuto adicional telefonia</th>
                    <th>Tiene internet fijo</th>
                    <th>Nombre Plan Int FI</th>
                    <th>Tarifa Mensual Internet</th>
                    <th>Velocidad Ofrecida Bajada</th>
                    <th>Velocidad Ofrecida Subida</th>
                    <th>Id Tecnologia</th>
                    <th>Canales Premium TV</th>
                    <th>Canales HD TV</th>
                    <th>Video Demanda</th>
                    <th>Costo Deco Adición</th>
                    <th>Otras Caracteristicas</th>


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
                            {{$movimiento->tipo==1?Auth::user()->empresa()->moneda.\App\Funcion::Parsear($movimiento->saldo):''}}
                        </td>
                    </tr>
                @endforeach
                </tbody>
                <tfoot class="thead-dark">
                    <td colspan="5"></td>
                    <th></th>
                </tfoot>
                </table>
                <div class="text-right">
                    {{$movimientos->links()}}
                </div>

            </div>
        </div>
    </form>
    <input type="hidden" id="urlgenerar" value="{{route('reportes.instalacion')}}">
    <input type="hidden" id="urlexportar" value="{{route('exportar.cajas')}}">
@endsection
