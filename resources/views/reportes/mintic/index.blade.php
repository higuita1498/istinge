@extends('layouts.app')
@section('content')
    {{-- <input type="hidden" id="valuefecha" value="{{$request->fechas}}">
    <input type="hidden" id="primera" value="{{$request->date ? $request->date['primera'] : ''}}">
    <input type="hidden" id="ultima" value="{{$request->date ? $request->date['ultima'] : ''}}"> --}}
    <div>
    <form id="form-filtrar" method="POST" action="{{ route('reportes.generar.mostrar') }}">
        @csrf
        <div class="row card-description">
            <div class="form-group col-md-4">
                <div class="row">
                    <label>Seleccione el Año<span class="text-danger">*</span></label>
                    <select class="form-control" id="anio" name="anio" required="">
                        @php
                            $anioActual = date('Y');
                            $cantidadAnios = 20; // Cantidad de años hacia atrás desde el año actual
                        @endphp
                        @for ($i = $anioActual; $i >= $anioActual - $cantidadAnios; $i--)
                        <option value="{{$i}}" {{$request->anio == $i ? 'selected' : ''}}>{{$i}}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="form-group col-md-4 ml-5">
                <div class="row">
                    <label>Seleccione el Trimestre <span class="text-danger">*</span></label>
                    <select class="form-control" id="trimestre" name="trimestre" required="">
                        <option value="1" {{$request->trimestre == 1 ? 'selected' : ''}}>Trimestre 1</option>
                        <option value="2" {{$request->trimestre == 2 ? 'selected' : ''}}>Trimestre 2</option>
                        <option value="3" {{$request->trimestre == 3 ? 'selected' : ''}}>Trimestre 3</option>
                        <option value="4" {{$request->trimestre == 4 ? 'selected' : ''}}>Trimestre 4</option>
                    </select>
                </div>
            </div>
            <div class="form-group col-md-4">
                <!-- Otros campos de filtrado si es necesario -->
            </div>
            <div class="form-group col-md-4 text-center offset-md-4">
                <center>
                    <button type="submit" class="btn btn-outline-primary">Filtrar</button>
                    <a href="{{ route('exportar.reportes.contratos.excel') }}?anio={{ $anioActual }}&trimestre={{ $trimestre }}" class="btn btn-outline-success">Exportar a Excel</a>
                </center>
            </div>
        </div>
    </form>
</div>
        {{-- <div class="form-group col-md-4 text-center offset-md-4">
            <center>
                <button type="button" id="filtrar" class="btn btn-outline-primary">Filtrar</button>
                <button type="button" id="exportar" class="btn btn-outline-success">Exportar a Excel</button>
            </center>
        </div> --}}

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
                    @forelse($contratos as $contrato)
                    <tr>
                        <td>{{ $contrato->created_at ?? '0' }}</td>
                        <td>{{ $trimestre ?? '0' }}</td>
                        <td>{{ $contrato->fk_idmunicipio?? '0' }}</td>
                        <td>{{ $contrato->id_segmento_planes ?? '0' }}</td>
                        <td>{{ $contrato->cantidad_suscriptores ?? '0' }}</td>
                        <td>{{ $contrato->name ?? '0' }}</td>
                        <td>{{ $contrato->iva_factura ? (0.19 * $contrato->price) : 0 }}</td>
                        <td>{{ $contrato->price?? '0' }}</td>
                        <td>{{ $contrato->id_modalidad_plan ?? '0' }}</td>
                        <td>{{ $contrato->created_at ?? '0' }}</td>
                        <td>{{ $contrato->created_at ?? '0' }}</td>
                        <td>{{ $contrato->type ?? '0' }}</td>
                        <td>{{ $contrato->tiene_telefonia_fija ?? '0' }}</td>
                        <td>{{ $contrato->tarifa_telefonia_fija ?? '0' }}</td>
                        <td>{{ $contrato->cantidad_minutos ?? '0' }}</td>
                        <td>{{ $contrato->valor_minuto_incluido_telefonia ?? '0' }}</td>
                        <td>{{ $contrato->valor_minuto_adicional_telefonia ?? '0' }}</td>
                        <td>{{ $contrato->tiene_internet_fijo ?? '0' }}</td>
                        <td>{{ $contrato->nombre_plan_int_fi ?? '0' }}</td>
                        <td>{{ $contrato->tarifa_mensual_internet ?? '0' }}</td>
                        <td>{{ $contrato->burst_limit_bajada ?? '0' }}</td>
                        <td>{{ $contrato->burst_limit_subida ?? '0' }}</td>
                        <td>{{ $contrato->id_tecnologia ?? '0' }}</td>
                        <td>{{ $contrato->canales_premium_tv ?? '0' }}</td>
                        <td>{{ $contrato->canales_hd_tv ?? '0' }}</td>
                        <td>{{ $contrato->video_demanda ?? '0' }}</td>
                        <td>{{ $contrato->costo_deco_adicion ?? '0' }}</td>
                        <td>{{ $contrato->otras_caracteristicas ?? '0' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="28">No se encontraron contratos.</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot class="thead-dark">
                    <td colspan="28"></td>
                </tfoot>
            </table>
            <div class="text-right">
                {{ $contratos->links() }}
            </div>
        </div>
    </div>
    <input type="hidden" id="urlgenerar" value="{{route('reportes.generar.mostrar')}}">
    <input type="hidden" id="urlexportar" value="{{route('exportar.cajas')}}">
@endsection
