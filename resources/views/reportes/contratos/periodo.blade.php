@extends('layouts.app')
@section('content')
    <input type="hidden" id="valuefecha" value="{{$request->fechas}}">
    <form id="form-reporte">


        <div class="row card-description">
            <div class="form-group col-md-2">
                <label></label>
                <select class="form-control selectpicker" name="month" id="month" placeholder="Seleccione mes">
                <option value='0' disabled selected>Seleccione mes</option>
                <option value='1' {{ isset($request->month) && $request->month == 1 ? 'selected' : '' }}>Enero</option>
                <option value='2' {{ isset($request->month) && $request->month == 2 ? 'selected' : '' }}>Febrero</option>
                <option value='3' {{ isset($request->month) && $request->month == 3 ? 'selected' : '' }}>Marzo</option>
                <option value='4' {{ isset($request->month) && $request->month == 4 ? 'selected' : '' }}>Abril</option>
                <option value='5' {{ isset($request->month) && $request->month == 5 ? 'selected' : '' }}>Mayo</option>
                <option value='6' {{ isset($request->month) && $request->month == 6 ? 'selected' : '' }}>Junio</option>
                <option value='7' {{ isset($request->month) && $request->month == 7 ? 'selected' : '' }}>Julio</option>
                <option value='8' {{ isset($request->month) && $request->month == 8 ? 'selected' : '' }}>Agosto</option>
                <option value='9' {{ isset($request->month) && $request->month == 9 ? 'selected' : '' }}>Septiembre</option>
                <option value='10' {{ isset($request->month) && $request->month == 10 ? 'selected' : '' }}>Octubre</option>
                <option value='11' {{ isset($request->month) && $request->month == 11 ? 'selected' : '' }}>Noviembre</option>
                <option value='12' {{ isset($request->month) && $request->month == 12 ? 'selected' : '' }}>Diciembre</option>
                </select>
            </div>

            <div class="form-group col-md-2">
                <label></label>
                <select class="form-control selectpicker" name="year" id="year" placeholder="Seleccione Año">
                <option value='0' disabled selected>Seleccione año</option>
                <option value='2022' {{ isset($request->year) && $request->year == 2022 ? 'selected' : '' }}>2022</option>
                <option value='2023' {{ isset($request->year) && $request->year == 2023 ? 'selected' : '' }}>2023</option>
                <option value='2024' {{ isset($request->year) && $request->year == 2024 ? 'selected' : '' }}>2024</option>
                <option value='2025' {{ isset($request->year) && $request->year == 2025 ? 'selected' : '' }}>2025</option>
                <option value='2026' {{ isset($request->year) && $request->year == 2026 ? 'selected' : '' }}>2026</option>
                <option value='2027' {{ isset($request->year) && $request->year == 2027 ? 'selected' : '' }}>2027</option>
                <option value='2028' {{ isset($request->year) && $request->year == 2028 ? 'selected' : '' }}>2028</option>
                <option value='2029' {{ isset($request->year) && $request->year == 2029 ? 'selected' : '' }}>2029</option>
                <option value='2030' {{ isset($request->year) && $request->year == 2030 ? 'selected' : '' }}>2030</option>
                </select>
            </div>


            <div class="form-group col-md-4" style="    padding-top: 2%;">
                <button type="button" id="generar" class="btn btn-outline-secondary">Generar Reporte</button>
                <button type="button" id="exportar" class="btn btn-outline-secondary">Exportar a Excel</button>
            </div>
        </div>
        <div class="row card-description">
            <div class="col-md-12 table-responsive">
                <table class="table table-striped table-hover " id="example">
                    <thead class="thead-dark">
                    <tr>
                        <th>Nro contrato</th>
                        <th>Cliente</th>
                        <th>Consumo</th>
                        <th>Periodo</th>
                        <th>Deuda</th>
                        <th>Periodo</th>
                        <th>Deuda</th>
                    </tr>
                    </thead>
                    <tbody>

                    @foreach($contratos as $contrato)
                    @php $deudaFacturas =  $contrato->deudaFacturas(); @endphp
                        <tr>
                            <td><a href="{{route('contratos.show', $contrato->id)}}" target="_blanck">{{ $contrato->nro }}</a> </td>
                            <td><a href="{{ route('contactos.show', $contrato->client_id) }}">
                                {{ App\Http\Controllers\Controller::caracteres($contrato->servicio) }}
                            </a></td>
                            <td>Consumo {{ $contrato->mes_factura }}</td>
                            <td>{{ $contrato->fecha_concatenada }}</td>
                            <td>{{ $deudaFacturas }}</td>
                            <td>{{ $contrato->fecha_concatenada }}</td>
                            <td>{{ $deudaFacturas }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <div class="text-right">
                   {{$contratos->links()}}

                </div>
            </div>
        </div>
    </form>
    <input type="hidden" id="urlgenerar" value="{{route('reportes.contratoperiodo')}}">
    <input type="hidden" id="urlexportar" value="{{route('exportar.contratoperiodo')}}">
@endsection
