@extends('layouts.app')

@section('content')
    <style>
        table td{
            font-size:smaller !important;
        }

        .my-table-css{
            color: inherit;
            width: calc(185.2px);
            box-sizing: border-box;
            font-size: inherit;
            font-family: inherit;
            font-weight: inherit;
        }
        .tab-content {
            margin-top: 0;
        }
        .nav-tabs .nav-link.active, .nav-tabs .nav-item.show .nav-link {
            color: #333;
            background: #e9ecef;
        }
        .nav-link {
            padding: 0.75rem 1rem;
        }
    </style>

    <div class="row pl-4">
        <div class="col-12">
            <a href="{{ route('nomina.liquidar', ['periodo' => $periodo, 'year'=> $year, 'editar'=>1,'tipo' => $tipo]) }}"> <i class="fas fa-chevron-left"></i> Regresar a liquidar nomina </a>
        </div>
        <div class="col-12">
            <br>
            <h5 style="font-weight: bold; color: #45ABCD;">Nómina del periodo: {{ $mensajePeriodo }}</h5>
        </div>
    </div>

    <div class="row">
        <div class="col-9">

        </div>
        <div class="col-3" style="">
            <a href="{{ route('nomina.exportar', ['periodo' => $periodo, 'year'=> $year, 'tipo' => $tipo]) }}" target="_blank"> <i class="fas fa-arrow-down"></i> Descargar reporte </a>
        </div>
    </div>

    <div class="row p-4 mt-2">
        <div class="col-12">
            <ul class="nav nav-tabs" id="myTab" role="tablist" style="font-size: 12px">
                <li class="nav-item ">
                    <a class="nav-link active" id="vac-inc-lic-tab" data-toggle="tab" href="#vac-inc-lic" role="tab" aria-controls="Vacac. Incap. y Licen" aria-selected="true">Vacac. Incap. y Licen.</a>
                </li>
                <li class="nav-item ">
                    <a class="nav-link" id="ext-rec-tab" data-toggle="tab" href="#ext-rec" role="tab" aria-controls="Extras y Recargos" aria-selected="false">Extras y Recargos</a>
                </li>
                <li class="nav-item ">
                    <a class="nav-link" id="ingresos-c-tab" data-toggle="tab" href="#ingresos-c" role="tab" aria-controls="Ingresos Constitutivos" aria-selected="false">Ingresos Constitutivos</a>
                </li>
                <li class="nav-item ">
                    <a class="nav-link" id="ingresos-no-c-tab" data-toggle="tab" href="#ingresos-no-c" role="tab" aria-controls="Ingresos No Constitutivos" aria-selected="false">Ingresos No Constitutivos</a>
                </li>
                <li class="nav-item ">
                    <a class="nav-link" id="deducciones-tab" data-toggle="tab" href="#deducciones" role="tab" aria-controls="Deducciones" aria-selected="false">Deducciones</a>
                </li>
            </ul>
            <div class="tab-content fact-table mt-3" id="myTabContent">
                <div class="tab-pane fade show active" id="vac-inc-lic" role="tabpanel" aria-labelledby="vac-inc-lic-tab">
                    <input type="hidden" id="url-show-vac-inc-lic-tab" value="#">
                    <div class="table-responsive">
                        <table class="table table-light table-striped table-hover" id="table-show-vac-inc-lic-tab" style="width: 100%; border: 1px solid #e9ecef;">
                            <thead class="thead-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Tipo</th>
                                    <th>Inicio (fecha)</th>
                                    <th>Fin (fecha)</th>
                                    <th>Cantidad</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($seccion_vacaciones as $vacaciones)
                                    <tr>
                                        <td><a href="{{route('personas.show', $vacaciones->idpersona)}}">{{ $vacaciones->nombrePersona }} {{ $vacaciones->apellido }}</a></td>
                                        <td>{{ $vacaciones->nombre }}</td>
                                        <td>{{date('d-m-Y', strtotime($vacaciones->fecha_inicio))}}</td>
                                        <td>{{date('d-m-Y', strtotime($vacaciones->fecha_fin))}}</td>
                                        <td>{{$vacaciones->num_dias}}</td>
                                        <td>{{ Auth::user()->empresaObj->moneda}} {{ App\Funcion::Parsear($vacaciones->valor_categoria) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tab-pane fade" id="ext-rec" role="tabpanel" aria-labelledby="ext-rec-tab">
                    <input type="hidden" id="url-show-ext-rec" value="#">

                    <table class="table table-light table-striped table-hover" id="table-show-ext-rec" style="width: 100%; border: 1px solid #e9ecef;">
                        <thead class="thead-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Cantidad</th>
                                <th>Fecha</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody class="MuiTableBody-root">
                            @foreach ($seccion_extrasRecargos as $extrasRecargos)
                            <tr class="MuiTableRow-root" index="0" level="0" path="0" style="transition: all 300ms ease 0s;">
                                <td><a href="{{route('personas.show', $extrasRecargos->idpersona)}}">{{ $extrasRecargos->nombrePersona }} {{ $extrasRecargos->apellido }}</a></td>
                                <td class="my-table-css">{{$extrasRecargos->nombre}}</td>
                                <td class="my-table-css" >{{$extrasRecargos->numero_horas}}</td>
                                <td class="my-table-css">{{date('d-m-Y', strtotime($extrasRecargos->fecha_registro))}}</td>
                                <td class="my-table-css">{{ Auth::user()->empresaObj->moneda}} {{ App\Funcion::Parsear($extrasRecargos->valor_categoria) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane fade" id="ingresos-c" role="tabpanel" aria-labelledby="ext-rec-tab">
                    <input type="hidden" id="url-show-ingresos-c" value="#">

                    <table class="table table-light table-striped table-hover" id="table-show-ingresos-c" style="width: 100%; border: 1px solid #e9ecef;">
                        <thead class="thead-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Fecha</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($seccion_constitutivos as $constitutivos)
                            <tr class="MuiTableRow-root" index="0" level="0" path="0" style="transition: all 300ms ease 0s;">
                                <td><a href="{{route('personas.show', $constitutivos->idpersona)}}">{{ $constitutivos->nombrePersona }} {{ $constitutivos->apellido }}</a></td>
                                <td class="my-table-css">{{$constitutivos->nombre}}</td>
                                <td class="my-table-css">{{date('d-m-Y', strtotime($constitutivos->fecha_registro))}}</td>
                                <td class="my-table-css">{{ Auth::user()->empresaObj->moneda}} {{ App\Funcion::Parsear($constitutivos->valor_categoria) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane fade" id="ingresos-no-c" role="tabpanel" aria-labelledby="ext-rec-tab">
                    <input type="hidden" id="url-show-ingresos-no-c" value="#">

                    <table class="table table-light table-striped table-hover" id="table-show-ingresos-no-c" style="width: 100%; border: 1px solid #e9ecef;">
                        <thead class="thead-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Fecha</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($seccion_noConstitutivos as $noConstitutivos)
                            <tr class="MuiTableRow-root" index="0" level="0" path="0" style="transition: all 300ms ease 0s;">
                                <td><a href="{{route('personas.show', $noConstitutivos->idpersona)}}">{{ $noConstitutivos->nombrePersona }} {{ $noConstitutivos->apellido }}</a></td>
                                <td class="my-table-css">{{$noConstitutivos->nombre}}</td>
                                <td class="my-table-css">{{date('d-m-Y', strtotime($noConstitutivos->fecha_registro))}}</td>
                                <td class="my-table-css">{{ Auth::user()->empresaObj->moneda}} {{ App\Funcion::Parsear($noConstitutivos->valor_categoria) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="tab-pane fade" id="deducciones" role="tabpanel" aria-labelledby="deducciones">
                    <input type="hidden" id="url-show-deducciones" value="#">

                    <table class="table table-light table-striped table-hover" id="table-deducciones" style="width: 100%; border: 1px solid #e9ecef;">
                        <thead class="thead-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Fecha</th>
                                <th>Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($seccion_deducciones as $deducciones)
                            <tr class="MuiTableRow-root" index="0" level="0" path="0" style="transition: all 300ms ease 0s;">
                                <td><a href="{{route('personas.show', $deducciones->idpersona)}}">{{ $deducciones->nombrePersona }} {{ $deducciones->apellido }}</a></td>
                                <td class="my-table-css">{{$deducciones->nombre}}</td>
                                <td class="my-table-css">{{date('d-m-Y', strtotime($deducciones->fecha_registro))}}</td>
                                <td class="my-table-css">{{ Auth::user()->empresaObj->moneda}} {{ App\Funcion::Parsear($deducciones->valor_categoria) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

@section('scripts')
    <script>
        $(document).ready(function (){
            $('#table-show-vac-inc-lic-tab,#table-show-ext-rec,#table-show-ingresos-c,#table-show-ingresos-no-c,#table-deducciones').DataTable({
                "language": {
                    "zeroRecords": "Disculpe, No existen registros",
                    "info": "",
                    "infoEmpty": " ",
                    "infoFiltered": "(Filtrado de _MAX_ total entradas)",
                    "infoPostFix": "",
                    "decimal": ",",
                    "thousands": ".",
                    "lengthMenu": "",
                    "loadingRecords": "Cargando...",
                    "processing": "Procesando...",
                    "search": "Buscar:",
                    "zeroRecords": "Sin resultados encontrados",
                },
                "paging":false,
                "searching": true,
                "order": [[ 0, "desc" ]],
            });
        });
    </script>
@endsection

@endsection
