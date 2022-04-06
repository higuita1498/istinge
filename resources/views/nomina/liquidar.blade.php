@extends('layouts.app')


@section('style')
<style>
    .form-control.form-control-sm {
        padding: 0px;
    }

    .color {
        color: #d08f50;
        background: #e9ecef;
        font-weight: bold;
        padding: 5px;
        border-radius: 5px;
        border: solid 1px #dbdbdb;
    }

    .color:hover {
        border: solid 1px #d08f50;
    }

    .w-77 {
        width: 77% !important;
    }

    input[type="date"i] {
        color: rgb(0, 0, 0) !important;
        font-size: 11px;
        font-weight: 400;
    }

    div.dataTables_filter input {
        padding: 4px !important;
    }

    .nav-tabs .nav-link.active,
    .nav-tabs .nav-item.show .nav-link {
        color: #495057 !important;
        background-color: #e9ecef !important;
        border-color: #dee2e6 #dee2e6 #dee2e6 !important;
    }

    .nav-tabs .nav-link {
        font-size: 0.95em;
    }

    /*
        input[type="date" i]::before {
            content: '¿';
            color:rgb(53, 50, 50);
            font-size: '13px';
        }
        */
</style>
@endsection

@section('content')

<div class="container-fluid">
    @if ($modoLectura->success)
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <a>{{ $modoLectura->message }}, si deseas seguir disfrutando de nuestros servicios adquiere alguno de nuestros planes <a class="text-black" href="{{route('nomina.planes')}}"> <b>Click Aquí.</b></a></a>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    {{-- @include('nomina.tips.serie-base', ['pasos' => \collect([2,3,4, 10, 11, 12, 13, 14, 15])->diff($guiasVistas->keyBy('nro_tip')->keys())->all()]) --}}

    @if (session()->has('success'))
    <div class="row">
        <div class="col-12">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session()->pull('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-12 col-md-6 px-3">
            @include('nomina.includes.periodo', ['mensajePeriodo' => $mensajePeriodo])
        </div>
        <div class="col-12 col-md-6 px-3">
            <form method="get" action="" role="form" class="forms-sample" id="form-buscarnomina">
                {{-- @csrf --}}
                <div class="notice">

                    @if($variosPeriodos->count() == 2)
                    <strong>1- Elige la quincena que deseas editar</strong>
                    <hr>
                    <div class="d-flex">
                        @php $periodoSeleccionado = null; @endphp
                        <select class="form-control selectpicker" name="periodo_quincenal" id="periodo_quincenal" required="" title="Seleccione" data-size="5">
                            @foreach($variosPeriodos as $periodos)
                            <option @if($tipo==null && $periodos->periodo == 1 || $tipo == 1 && $periodos->periodo == 1)
                                {{"selected"}}
                                @php $periodoSeleccionado = $periodos; @endphp
                                @elseif($tipo == 2 && $periodos->periodo == 2)
                                {{"selected"}}
                                @php $periodoSeleccionado = $periodos; @endphp
                                @endif
                                value={{$periodos->periodo}}
                                >
                                {{Carbon\Carbon::parse($periodos->fecha_desde)->format('d')}}
                                - {{Carbon\Carbon::parse($periodos->fecha_hasta)->format('d')}} {{"de"}} {{Carbon\Carbon::parse($periodos->fecha_hasta)->format('F')}} {{Carbon\Carbon::parse($periodos->fecha_desde)->format('Y')}}
                            </option>
                            @endforeach

                            <input type="hidden" id="f_ini" value="{{ $periodoSeleccionado ? Carbon\Carbon::parse($periodoSeleccionado->fecha_desde)->format('Y-m-d') : ' ' }}">
                            <input type="hidden" id="f_fin" value="{{ $periodoSeleccionado ? Carbon\Carbon::parse($periodoSeleccionado->fecha_hasta)->format('Y-m-d') : ' ' }}">

                        </select>
                        <a href="javascript:buscarPeriodo()" class="btn btn-success ml-2">Elegir</a>
                    </div>
                    @else
                    <strong>Este mes el pago de tu empresa es mensual.</strong>
                    <hr>
                    <select class="form-control selectpicker" name="periodo_completo" id="periodo_completo" required="" title="Seleccione" data-size="5">
                        @foreach($variosPeriodos as $periodos)
                        <option {{$periodos->periodo == 0 ? 'selected readonly' : ''}} value={{$periodos->periodo}}>
                            {{Carbon\Carbon::parse($periodos->fecha_desde)->format('d')}} -
                            {{Carbon\Carbon::parse($periodos->fecha_hasta)->format('d')}} {{"de"}}
                            {{Carbon\Carbon::parse($periodos->fecha_hasta)->monthName}}
                            {{Carbon\Carbon::parse($periodos->fecha_desde)->format('Y')}}
                        </option>
                        @endforeach
                    </select>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <hr>

    <div class="row">
        <input id="actualizar-costo-url" type="hidden" value="{{route('nomina.costoPeriodo', ['tipo' => $tipo, 'year' => $year, 'periodo' => $periodo])}}">
        <div class="col-6 px-0">
            <div class="card w-100 h-100 shadow-sm bg-light">
                <div class="card-body px-1 text-center">
                    <div class="row">
                        <div class="col-2">
                            <i class="fas fa-users" style="font-size: 23px"></i>
                        </div>
                        <div class="col-10">
                            <div class="row">
                                <div class="col-12">
                                    <h5> pago a personas </h5>
                                </div>
                                <div class="col-12">
                                    <span style="text-align: right">$<span id="pago-empleados">{{ $costoPeriodo->pagoEmpleados }}</span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 px-0">
            <div class="card w-100 h-100 shadow-sm bg-light">
                <div class="card-body px-1 text-center">
                    <div class="row">
                        <div class="col-2">
                            <i class="fas fa-building" style="font-size: 23px"></i>
                        </div>
                        <div class="col-10">
                            <div class="row">
                                <div class="col-12">
                                    <h5> costo empresa </h5>
                                </div>
                                <div class="col-12">
                                    <span style="text-align: right">$<span id="costo-empresa">{{ $costoPeriodo->costoEmpresa }}</span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="row text-center pt-2">
        <div class="col-4">
            <a href="{{ route('nomina.resumenExcel', ['periodo' => $periodo, 'year'=> $year, 'tipo' => $tipo]) }}" style="text-decoration: underline;">Resumen nómina</a>
        </div>
        <div class="col-4">
            <a target="_blank" href="{{ route('nomina.novedades', ['periodo' => $periodo, 'year'=> $year, 'tipo' => $tipo]) }}" style="text-decoration: underline;">Reporte novedades</a>
        </div>
        {{--
        <div class="col-3 d-none">
            <a href="#" style="text-decoration: underline;">Cargar novedades</a>
        </div>
        --}}
        <div class="col-4">
            <div class="btn-group dropdown">
                <a style="text-decoration:underline" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="btn-prestaciones-sociales">
                    Prestaciones sociales
                </a>
                <div class="dropdown-menu px-3">
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('nomina.prestacion-social', ['year' => $year, 'periodo' => $periodo, 'rango' => str_replace(' ', '', $mensajePeriodo), 'tipo' => $tipo]) }}" style="text-decoration: underline;">- Prima de servicios</a>
                    <br>
                    <a href="{{ route('nomina.prestacion-social.cesantias', ['year' => $year, 'periodo' => $periodo, 'rango' => str_replace(' ', '', $mensajePeriodo), 'tipo' => $tipo]) }}" style="text-decoration: underline;">- Cesantias</a>
                    <br>
                    <a href="{{ route('nomina.prestacion-social.intereses-cesantias', ['year' => $year, 'periodo' => $periodo, 'rango' => str_replace(' ', '', $mensajePeriodo), 'tipo' => $tipo]) }}" style="text-decoration: underline;">- Intereses a las Cesantias</a>
                </div>
            </div>
        </div>
    </div>


    <div class="row mt-3">
        <div class="col-12">
            <div class="row card-description">
                <div class="col-12 col-md-12">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="empleados-tab" data-toggle="tab" href="#empleados" role="tab" aria-controls="empleados" aria-selected="true">Empleados ({{ count($empleados) }})</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="contratados-tab" data-toggle="tab" href="#contratados" role="tab" aria-controls="contratados" aria-selected="true">Contratados ({{ count($contratados) }}
                                )</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="estudiantes-tab" data-toggle="tab" href="#estudiantes" role="tab" aria-controls="estudiantes" aria-selected="false">Estudiantes ({{ count($estudiantes) }}
                                )</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="aprendices-tab" data-toggle="tab" href="#aprendices" role="tab" aria-controls="aprendices" aria-selected="false">Aprendices ({{ count($aprendices) }}
                                )</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="pensionados-tab" data-toggle="tab" href="#pensionados" role="tab" aria-controls="pensionados" aria-selected="false">Pensionados ({{ count($pensionados) }}
                                )</a>
                        </li>
                    </ul>

                    <div class="tab-content fact-table" id="myTabContent">
                        <div class="tab-pane fade show active" id="empleados" role="tabpanel" aria-labelledby="empleados-tab">
                            <input type="hidden" id="url-show-empleados" value="{{route('bancos.cliente.movimientos.cuenta', 1)}}">
                            <div class="table-responsive">
                                <table class="table table-light table-striped table-hover" id="table-show-empleados" style="width: 100%; border: 1px solid #e9ecef;">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="align-middle">Personas</th>
                                            <th class="align-middle">Salario base</th>
                                            <th id="th-extras-r">Horas extras y<br>recargos</th>
                                            <th id="th-vacaciones-i">Vacaciones,<br>Incap y Lic</th>
                                            <th id="th-ingresos">Ingresos<br>adicionales</th>
                                            <th id="th-deducciones">Deducc, prést y<br>ReteFuen</th>
                                            <th>Pago<br>empleado</th>
                                            {{-- @if(isset($_SESSION['permisos']['160']) || isset($_SESSION['permisos']['161']))--}}
                                            <th id="th-acciones" class="align-middle text-center">Acciones</th>
                                            {{-- @endif--}}
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($empleados as $nomina)
                                        @foreach($nomina->nominaperiodos as $nominaPeriodo)

                                        @php $personaValor = $moneda . App\Funcion::Parsear($nominaPeriodo->pago_empleado ? $nominaPeriodo->pago_empleado : $nominaPeriodo->valor_total); @endphp
                                        <tr>
                                            <td>
                                                <a href="{{route('personas.show', $nomina->persona)}}" target="_blank">
                                                    {{$nomina->persona->nombre()}}
                                                </a>
                                            </td>
                                            <td>{{$personaValor}} <input type="hidden" id="salario-promedio-nomina-{{$nominaPeriodo->id}}" value="{{$nomina->persona->valor}}"> <input type="hidden" id="base-periodo-nomina-{{$nominaPeriodo->id}}" value="{{$nominaPeriodo->pago_empleado ? $nominaPeriodo->pago_empleado : $nominaPeriodo->valor_total}}">
                                            </td>
                                            <td>
                                                <span id="extras{{ $nominaPeriodo->id }}">{{ $nominaPeriodo->extras() }}</span>
                                                @if($nomina->persona_liquidada == false && !$modoLectura->success)
                                                <i class="far fa-edit color" onclick="editHoras('{{ $nominaPeriodo->id }}');"></i>
                                                @endif
                                            </td>
                                            <td>
                                                <span id="vacaciones{{ $nominaPeriodo->id }}">{{ $nominaPeriodo->vacaciones() }}</span>
                                                @if($nomina->persona_liquidada == false && !$modoLectura->success)
                                                <i class="far fa-edit color" onclick="editVacaciones('{{ $nominaPeriodo->id }}');"></i> @if($nomina->persona->subsidio)
                                                <input type="hidden" name="dias_trabajados" id="dias-trabajados-{{$nominaPeriodo->id}}" value="{{$nominaPeriodo->diasTrabajados()}}"> @endif
                                                <input type="hidden" id="dias-pagos" name="dias_pagos" value="{{$nominaPeriodo->diasTrabajados()}}">
                                                @endif
                                            </td>
                                            <td>{{Auth::user()->empresaObj->moneda}} <span id="ingresos{{ $nominaPeriodo->id }}">{{ App\Funcion::Parsear($nominaPeriodo->ingresos()) }}</span>
                                                @if($nomina->persona_liquidada == false && !$modoLectura->success)
                                                <i class="far fa-edit color" onclick="editAdicionales('{{ $nominaPeriodo->id }}');"></i>
                                                @endif
                                            </td>
                                            <td>{{Auth::user()->empresaObj->moneda}} <span id="deducciones{{ $nominaPeriodo->id }}">{{ App\Funcion::Parsear($nominaPeriodo->deducciones()) }}</span>
                                                @if($nomina->persona_liquidada == false && !$modoLectura->success)
                                                @if (!$modoLectura->success)
                                                <i class="far fa-edit color" onclick="editDeducciones('{{ $nominaPeriodo->id }}');"></i>
                                                @endif
                                                @endif
                                            </td>

                                            <td>
                                                <span id="pago-nomina-format-{{$nominaPeriodo->id}}">{{Auth::user()->empresaObj->moneda}} {{ App\Funcion::Parsear($nominaPeriodo->valor_total ? $nominaPeriodo->valor_total : 0) }} </span><input type="hidden" id="pago-nomina-{{$nomina->id}}" value="{{$nominaPeriodo->pago_empleado ? $nominaPeriodo->pago_empleado : $nominaPeriodo->valor_total}}">
                                            </td>
                                            <td>
                                      
                                                @if (!$modoLectura->success)
                                                <a id="a-ob-nomina-{{$nominaPeriodo->id}}" href="javascript:modificarNota('{{ $nominaPeriodo->observaciones }}', '{{ $nominaPeriodo->id}}', 'a-ob-nomina-{{$nominaPeriodo->id}}')">
                                                    <i class="far fa-folder-open color"></i>
                                                </a>
                                                @endif
                                                                              
                                                <a href="{{route('nomina.calculos',[$nominaPeriodo->id, 'periodo' => $mensajePeriodo])}}" title="Ver calculos"><i class="far fa-eye color"></i></a>
                                           
                                                @if($nomina->prestacionesSociales->count() == 0)
                                              
                                                <a href="{{ route('nomina.pdf', $nominaPeriodo->id)}}" target="_blank" title="Ver colilla de pago"><i class="far fa-file color"></i></a>
                                            
                                                @else              
                                                <a href="#" data-toggle="modal" data-target="#modal-imprimir-{{ $nominaPeriodo->id }}"><i class="far fa-file color"></i></a>

                                                <div class="modal" id="modal-imprimir-{{ $nominaPeriodo->id }}" tabindex="-1" role="dialog">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Opciones de
                                                                    impresión</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row w-100">
                                                                    <div class="col-6 pl-3">
                                                                        <div class="card" style="width: 100%;">
                                                                            <ul class="list-group list-group-flush">
                                                                                @foreach($nomina->prestacionesSociales as $prestacion)
                                                                                <a href="{{ route('prestacion-social.imprimir', $prestacion->id) }}" target="_blank">
                                                                                    <li class="list-group-item">{{str_replace('_', ' ', $prestacion->nombre)}}</li>
                                                                                </a>
                                                                                @endforeach
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-6 pl-3">
                                                                        <div class="card" style="width: 100%;">
                                                                            <ul class="list-group list-group-flush">
                                                                                <a href="{{ route('nomina.pdf', $nominaPeriodo->id)}}" target="_blank">
                                                                                    <li class="list-group-item">
                                                                                        Nomina
                                                                                    </li>
                                                                                </a>
                                                                                @foreach($nomina->prestacionesSociales as $prestacion)
                                                                                <a href="{{ route('nomina.pdf', [$nominaPeriodo->id, 'prestacion_social' => $prestacion->id])}}" target="_blank">
                                                                                    <li class="list-group-item">
                                                                                        Nomina
                                                                                        mas {{ strtoupper(str_replace('_', ' ', $prestacion->nombre)) }}</li>
                                                                                </a>
                                                                                @endforeach



                                                                                @if($nomina->prestacionesSociales->count() >= 2)
                                                                                <a href="{{ route('nomina.pdf', [$nominaPeriodo->id, 'prestacion_social' => 'todas'])}}" target="_blank">
                                                                                    <li class="list-group-item">Nomina con todo</li>
                                                                                </a>
                                                                                @endif
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                @endif

                                                <a href="{{ route('nomina.liquidacion', ['nomina' => $nomina, 'periodo' => $nominaPeriodo]) }}" title="Enviar nómina al correo">
                                                    <i class="fas fa-envelope-open-text color"></i>
                                                </a>
                                            </td>
                                            @include('nomina.modals.extras-y-recargos')
                                            @if($loop->iteration == 1)
                                            @include('nomina.modals.comentario')
                                            @endif
                                        </tr>
                                        @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="contratados" role="tabpanel" aria-labelledby="contratados-tab">
                            <input type="hidden" id="url-show-contratados" value="{{route('bancos.cliente.movimientos.cuenta', 1)}}">
                            <div class="table-responsive">
                                <table class="table table-light table-striped table-hover" id="table-show-contratados" style="width: 100%; border: 1px solid #e9ecef;">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="align-middle">Personas</th>
                                            <th class="align-middle">Salario base</th>
                                            <th>Horas extras y<br>recargos</th>
                                            <th>Vacaciones,<br>Incap y Lic</th>
                                            <th>Ingresos<br>adicionales</th>
                                            <th>Deducc, prést y<br>ReteFuen</th>
                                            <th>Pago<br>empleado</th>
                                            <th class="align-middle text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($contratados as $nomina)
                                        @foreach($nomina->nominaperiodos as $nominaPeriodo)
                                        @php $personaValor = $moneda . App\Funcion::Parsear($nominaPeriodo->pago_empleado ? $nominaPeriodo->pago_empleado : $nominaPeriodo->valor_total); @endphp
                                        <tr>
                                            <td>
                                                <a href="{{route('personas.show', $nomina->persona)}}" target="_blank">
                                                    {{$nomina->persona->nombre()}}
                                                </a>
                                            </td>
                                            <td>{{$personaValor}} <input type="hidden" id="salario-promedio-nomina-{{$nominaPeriodo->id}}" value="{{$nomina->persona->valor}}"> <input type="hidden" id="base-periodo-nomina-{{$nominaPeriodo->id}}" value="{{$nominaPeriodo->pago_empleado ? $nominaPeriodo->pago_empleado : $nominaPeriodo->valor_total}}">
                                            </td>
                                            <td>
                                                <span id="extras{{ $nominaPeriodo->id }}">{{ $nominaPeriodo->extras() }}</span>
                                                @if($nomina->persona_liquidada == false && !$modoLectura->success)
                                                <i class="far fa-edit color" onclick="editHoras('{{ $nominaPeriodo->id }}');"></i>
                                                @endif
                                            </td>

                                            <td>
                                                <span id="vacaciones{{ $nominaPeriodo->id }}">{{ $nominaPeriodo->vacaciones() }}</span>
                                                @if($nomina->persona_liquidada == false && !$modoLectura->success)
                                                <i class="far fa-edit color" onclick="editVacaciones('{{ $nominaPeriodo->id }}');"></i> @if($nomina->persona->subsidio)
                                                <input type="hidden" name="dias_trabajados" id="dias-trabajados-{{$nominaPeriodo->id}}" value="{{$nominaPeriodo->diasTrabajados()}}"> @endif
                                                <input type="hidden" id="dias-pagos" name="dias_pagos" value="{{$nominaPeriodo->diasTrabajados()}}">
                                                @endif
                                            </td>
                                            <td>{{Auth::user()->empresaObj->moneda}} <span id="ingresos{{ $nominaPeriodo->id }}">{{ App\Funcion::Parsear($nominaPeriodo->ingresos()) }}</span>
                                                @if($nomina->persona_liquidada == false && !$modoLectura->success)
                                                <i class="far fa-edit color" onclick="editAdicionales('{{ $nominaPeriodo->id }}');"></i>
                                                @endif
                                            </td>
                                            <td>{{Auth::user()->empresaObj->moneda}} <span id="deducciones{{ $nominaPeriodo->id }}">{{ App\Funcion::Parsear($nominaPeriodo->deducciones()) }}</span>
                                                @if($nomina->persona_liquidada == false && !$modoLectura->success)
                                                <i class="far fa-edit color" onclick="editDeducciones('{{ $nominaPeriodo->id }}');"></i>
                                                @endif
                                            </td>
                                            <td>
                                                <span id="pago-nomina-format-{{$nominaPeriodo->id}}">{{Auth::user()->empresaObj->moneda}} {{ App\Funcion::Parsear($nominaPeriodo->valor_total ? $nominaPeriodo->valor_total : 0) }} </span><input type="hidden" id="pago-nomina-{{$nomina->id}}" value="{{$nominaPeriodo->pago_empleado ? $nominaPeriodo->pago_empleado : $nominaPeriodo->valor_total}}">
                                            </td>
                                            <td>
                                                @if($nomina->persona_liquidada == false && !$modoLectura->success)
                                                <a id="a-ob-nomina-{{$nominaPeriodo->id}}" href="javascript:modificarNota('{{ $nominaPeriodo->observaciones }}', '{{ $nominaPeriodo->id}}', 'a-ob-nomina-{{$nominaPeriodo->id}}')">
                                                    <i class="far fa-folder-open color"></i>
                                                </a>
                                                @endif
                                         
                                                <a href="{{route('nomina.calculos',[$nominaPeriodo->id, 'periodo' => $mensajePeriodo])}}">
                                                    <i class="far fa-eye color"></i>
                                                </a>
                                        
                                                @if($nomina->prestacionesSociales->count() == 0)
                                                <a href="{{ route('nomina.pdf', $nominaPeriodo->id)}}" target="_blank"><i class="far fa-file color"></i></a>
                                                @else
                                                <a href="#" data-toggle="modal" data-target="#modal-imprimir-{{ $nominaPeriodo->id }}">
                                                    <i class="far fa-file color"></i>
                                                </a>

                                                <div class="modal" id="modal-imprimir-{{ $nominaPeriodo->id }}" tabindex="-1" role="dialog">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Opciones de
                                                                    impresión</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row w-100">
                                                                    <div class="col-6 pl-3">
                                                                        <div class="card" style="width: 100%;">
                                                                            <ul class="list-group list-group-flush">
                                                                                @foreach($nomina->prestacionesSociales as $prestacion)
                                                                                <a href="{{ route('prestacion-social.imprimir', $prestacion->id) }}" target="_blank">
                                                                                    <li class="list-group-item">{{str_replace('_', ' ', $prestacion->nombre)}}</li>
                                                                                </a>
                                                                                @endforeach
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-6 pl-3">
                                                                        <div class="card" style="width: 100%;">
                                                                            <ul class="list-group list-group-flush">
                                                                                <a href="{{ route('nomina.pdf', $nominaPeriodo->id)}}" target="_blank">
                                                                                    <li class="list-group-item">
                                                                                        Nomina
                                                                                    </li>
                                                                                </a>
                                                                                @foreach($nomina->prestacionesSociales as $prestacion)
                                                                                <a href="{{ route('nomina.pdf', [$nominaPeriodo->id, 'prestacion_social' => $prestacion->id])}}" target="_blank">
                                                                                    <li class="list-group-item">
                                                                                        Nomina
                                                                                        mas {{ strtoupper(str_replace('_', ' ', $prestacion->nombre)) }}</li>
                                                                                </a>
                                                                                @endforeach


                                                                                @if($nomina->prestacionesSociales->count() >= 2)
                                                                                <a href="{{ route('nomina.pdf', [$nominaPeriodo->id, 'prestacion_social' => 'todas'])}}" target="_blank">
                                                                                    <li class="list-group-item">Nomina con todo</li>
                                                                                </a>
                                                                                @endif
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                @endif
                                                
                                                {{-- <i class="far fa-paper-plane color"></i>--}}
                                            </td>
                                            @include('nomina.modals.extras-y-recargos')
                                            @if($loop->iteration == 1)
                                            @include('nomina.modals.comentario')
                                            @endif
                                        </tr>
                                        @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="estudiantes" role="tabpanel" aria-labelledby="estudiantes-tab">
                            <input type="hidden" id="url-show-estudiantes" value="{{route('bancos.cliente.movimientos.cuenta', 1)}}">
                            <div class="table-responsive">
                                <table class="table table-light table-striped table-hover" id="table-show-estudiantes" style="width: 100%; border: 1px solid #e9ecef;">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="align-middle">Personas</th>
                                            <th class="align-middle">Salario base</th>
                                            <th>Horas extras y<br>recargos</th>
                                            <th>Vacaciones,<br>Incap y Lic</th>
                                            <th>Ingresos<br>adicionales</th>
                                            <th>Deducc, prést y<br>ReteFuen</th>
                                            <th>Pago<br>empleado</th>
                                            <th class="align-middle text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($estudiantes as $nomina)
                                        @foreach($nomina->nominaperiodos as $nominaPeriodo)
                                        @php $personaValor = $moneda . App\Funcion::Parsear($nominaPeriodo->pago_empleado ? $nominaPeriodo->pago_empleado : $nominaPeriodo->valor_total); @endphp
                                        <tr>
                                            <td>
                                                <a href="{{route('personas.show', $nomina->persona)}}" target="_blank">
                                                    {{$nomina->persona->nombre()}}
                                                </a>
                                            </td>
                                            <td>{{$personaValor}} <input type="hidden" id="salario-promedio-nomina-{{$nominaPeriodo->id}}" value="{{$nomina->persona->valor}}"> <input type="hidden" id="base-periodo-nomina-{{$nominaPeriodo->id}}" value="{{$nominaPeriodo->pago_empleado ? $nominaPeriodo->pago_empleado : $nominaPeriodo->valor_total}}">
                                            </td>
                                            <td>
                                                <span id="extras{{ $nominaPeriodo->id }}">{{ $nominaPeriodo->extras() }}</span>
                                                @if($nomina->persona_liquidada == false && !$modoLectura->success)
                                                <i class="far fa-edit color" onclick="editHoras('{{ $nominaPeriodo->id }}');"></i>
                                                @endif
                                            </td>

                                            <td>
                                                <span id="vacaciones{{ $nominaPeriodo->id }}">{{ $nominaPeriodo->vacaciones() }}</span>
                                                @if($nomina->persona_liquidada == false && !$modoLectura->success)
                                                <i class="far fa-edit color" onclick="editVacaciones('{{ $nominaPeriodo->id }}');"></i> @if($nomina->persona->subsidio)
                                                <input type="hidden" name="dias_trabajados" id="dias-trabajados-{{$nominaPeriodo->id}}" value="{{$nominaPeriodo->diasTrabajados()}}"> @endif
                                                <input type="hidden" id="dias-pagos" name="dias_pagos" value="{{$nominaPeriodo->diasTrabajados()}}">
                                                @endif
                                            </td>

                                            <td>{{Auth::user()->empresaObj->moneda}} <span id="ingresos{{ $nominaPeriodo->id }}">{{ App\Funcion::Parsear($nominaPeriodo->ingresos()) }}</span>
                                                @if($nomina->persona_liquidada == false && !$modoLectura->success)
                                                <i class="far fa-edit color" onclick="editAdicionales('{{ $nominaPeriodo->id }}');"></i>
                                                @endif
                                            </td>
                                            <td>{{Auth::user()->empresaObj->moneda}} <span id="deducciones{{ $nominaPeriodo->id }}">{{ App\Funcion::Parsear($nominaPeriodo->deducciones()) }}</span>
                                                @if($nomina->persona_liquidada == false && !$modoLectura->success)
                                                <i class="far fa-edit color" onclick="editDeducciones('{{ $nominaPeriodo->id }}');"></i>
                                                @endif
                                            </td>
                                            <td>
                                                <span id="pago-nomina-format-{{$nominaPeriodo->id}}">{{Auth::user()->empresaObj->moneda}} {{ App\Funcion::Parsear($nominaPeriodo->valor_total ? $nominaPeriodo->valor_total : 0) }} </span><input type="hidden" id="pago-nomina-{{$nomina->id}}" value="{{$nominaPeriodo->pago_empleado ? $nominaPeriodo->pago_empleado : $nominaPeriodo->valor_total}}">
                                            </td>
                                            <td>
                                                <a id="a-ob-nomina-{{$nominaPeriodo->id}}" href="javascript:modificarNota('{{ $nominaPeriodo->observaciones }}', '{{ $nominaPeriodo->id}}', 'a-ob-nomina-{{$nominaPeriodo->id}}')">
                                                    <i class="far fa-folder-open color"></i>
                                                </a>
                                                <a href="{{route('nomina.calculos',[$nominaPeriodo->id, 'periodo' => $mensajePeriodo])}}"><i class="far fa-eye color"></i></a>
                                                @if($nomina->prestacionesSociales->count() == 0)
                                                <a href="{{ route('nomina.pdf', $nominaPeriodo->id)}}" target="_blank"><i class="far fa-file color"></i></a> @else
                                                <a href="#" data-toggle="modal" data-target="#modal-imprimir-{{ $nominaPeriodo->id }}"><i class="far fa-file color"></i></a>

                                                <div class="modal" id="modal-imprimir-{{ $nominaPeriodo->id }}" tabindex="-1" role="dialog">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Opciones de
                                                                    impresión</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row w-100">
                                                                    <div class="col-6 pl-3">
                                                                        <div class="card" style="width: 100%;">
                                                                            <ul class="list-group list-group-flush">
                                                                                @foreach($nomina->prestacionesSociales as $prestacion)
                                                                                <a href="{{ route('prestacion-social.imprimir', $prestacion->id) }}" target="_blank">
                                                                                    <li class="list-group-item">{{str_replace('_', ' ', $prestacion->nombre)}}</li>
                                                                                </a>
                                                                                @endforeach
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-6 pl-3">
                                                                        <div class="card" style="width: 100%;">
                                                                            <ul class="list-group list-group-flush">
                                                                                <a href="{{ route('nomina.pdf', $nominaPeriodo->id)}}" target="_blank">
                                                                                    <li class="list-group-item">
                                                                                        Nomina
                                                                                    </li>
                                                                                </a>
                                                                                @foreach($nomina->prestacionesSociales as $prestacion)
                                                                                <a href="{{ route('nomina.pdf', [$nominaPeriodo->id, 'prestacion_social' => $prestacion->id])}}" target="_blank">
                                                                                    <li class="list-group-item">
                                                                                        Nomina
                                                                                        mas {{ strtoupper(str_replace('_', ' ', $prestacion->nombre)) }}</li>
                                                                                </a>
                                                                                @endforeach


                                                                                @if($nomina->prestacionesSociales->count() >= 2)
                                                                                <a href="{{ route('nomina.pdf', [$nominaPeriodo->id, 'prestacion_social' => 'todas'])}}" target="_blank">
                                                                                    <li class="list-group-item">Nomina con todo</li>
                                                                                </a>
                                                                                @endif
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                                {{-- <i class="far fa-paper-plane color"></i>--}}
                                            </td>
                                            @include('nomina.modals.extras-y-recargos')
                                            @if($loop->iteration == 1)
                                            @include('nomina.modals.comentario')
                                            @endif
                                        </tr>
                                        @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="aprendices" role="tabpanel" aria-labelledby="aprendices-tab">
                            <input type="hidden" id="url-show-aprendices" value="{{route('bancos.cliente.movimientos.cuenta', 1)}}">
                            <div class="table-responsive">
                                <table class="table table-light table-striped table-hover" id="table-show-aprendices" style="width: 100%; border: 1px solid #e9ecef;">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="align-middle">Personas</th>
                                            <th class="align-middle">Salario base</th>
                                            <th>Horas extras y<br>recargos</th>
                                            <th>Vacaciones,<br>Incap y Lic</th>
                                            <th>Ingresos<br>adicionales</th>
                                            <th>Deducc, prést y<br>ReteFuen</th>
                                            <th>Pago<br>empleado</th>
                                            <th class="align-middle text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($aprendices as $nomina)
                                        @foreach($nomina->nominaperiodos as $nominaPeriodo)
                                        @php $personaValor = $moneda . App\Funcion::Parsear($nominaPeriodo->pago_empleado ? $nominaPeriodo->pago_empleado : $nominaPeriodo->valor_total); @endphp
                                        <tr>
                                            <td>
                                                <a href="{{route('personas.show', $nomina->persona)}}" target="_blank">
                                                    {{$nomina->persona->nombre()}}
                                                </a>
                                            </td>
                                            <td>{{$personaValor}} <input type="hidden" id="salario-promedio-nomina-{{$nominaPeriodo->id}}" value="{{$nomina->persona->valor}}"> <input type="hidden" id="base-periodo-nomina-{{$nominaPeriodo->id}}" value="{{$nominaPeriodo->pago_empleado ? $nominaPeriodo->pago_empleado : $nominaPeriodo->valor_total}}">
                                            </td>
                                            <td>
                                                <span id="extras{{ $nominaPeriodo->id }}">{{ $nominaPeriodo->extras() }}</span>
                                                @if($nomina->persona_liquidada == false && !$modoLectura->success)
                                                <i class="far fa-edit color" onclick="editHoras('{{ $nominaPeriodo->id }}');" />
                                                @endif
                                            </td>
                                            <td>
                                                <span id="vacaciones{{ $nominaPeriodo->id }}">{{ $nominaPeriodo->vacaciones() }}</span>
                                                @if($nomina->persona_liquidada == false && !$modoLectura->success)
                                                <i class="far fa-edit color" onclick="editVacaciones('{{ $nominaPeriodo->id }}');">
                                                </i>
                                                @endif
                                                @if($nomina->persona->subsidio)
                                                <input type="hidden" name="dias_trabajados" id="dias-trabajados-{{$nominaPeriodo->id}}" value="{{$nominaPeriodo->diasTrabajados()}}">
                                                @endif
                                                <input type="hidden" id="dias-pagos" name="dias_pagos" value="{{$nominaPeriodo->diasTrabajados()}}">
                                            </td>
                                            <td>
                                                {{Auth::user()->empresaObj->moneda}}
                                                <span id="ingresos{{ $nominaPeriodo->id }}">{{ App\Funcion::Parsear($nominaPeriodo->ingresos()) }}</span>
                                                @if($nomina->persona_liquidada == false && !$modoLectura->success)
                                                <i class="far fa-edit color" onclick="editAdicionales('{{ $nominaPeriodo->id }}');"></i>
                                                @endif
                                            </td>
                                            <td>{{Auth::user()->empresaObj->moneda}} <span id="deducciones{{ $nominaPeriodo->id }}">{{ App\Funcion::Parsear($nominaPeriodo->deducciones()) }}</span>
                                                @if($nomina->persona_liquidada == false && !$modoLectura->success)
                                                <i class="far fa-edit color" onclick="editDeducciones('{{ $nominaPeriodo->id }}');"></i>
                                                @endif
                                            </td>
                                            <td>
                                                <span id="pago-nomina-format-{{$nominaPeriodo->id}}">{{Auth::user()->empresaObj->moneda}} {{ App\Funcion::Parsear($nominaPeriodo->valor_total ? $nominaPeriodo->valor_total : 0) }} </span><input type="hidden" id="pago-nomina-{{$nomina->id}}" value="{{$nominaPeriodo->pago_empleado ? $nominaPeriodo->pago_empleado : $nominaPeriodo->valor_total}}">
                                            </td>
                                            <td>
                                                @if(!$modoLectura->success)
                                                <a id="a-ob-nomina-{{$nominaPeriodo->id}}" href="javascript:modificarNota('{{ $nominaPeriodo->observaciones }}', '{{ $nominaPeriodo->id}}', 'a-ob-nomina-{{$nominaPeriodo->id}}')">
                                                    <i class="far fa-folder-open color"></i>
                                                </a>
                                                @endif
                                     
                                                <a href="{{route('nomina.calculos',[$nominaPeriodo->id, 'periodo' => $mensajePeriodo])}}"><i class="far fa-eye color"></i>
                                                </a>
                                                
                                                @if($nomina->prestacionesSociales->count() == 0)
                                                <a href="{{ route('nomina.pdf', $nominaPeriodo->id)}}" target="_blank"><i class="far fa-file color"></i></a> @else
                                                <a href="#" data-toggle="modal" data-target="#modal-imprimir-{{ $nominaPeriodo->id }}"><i class="far fa-file color"></i></a>

                                                <div class="modal" id="modal-imprimir-{{ $nominaPeriodo->id }}" tabindex="-1" role="dialog">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Opciones de
                                                                    impresión</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row w-100">
                                                                    <div class="col-6 pl-3">
                                                                        <div class="card" style="width: 100%;">
                                                                            <ul class="list-group list-group-flush">
                                                                                @foreach($nomina->prestacionesSociales as $prestacion)
                                                                                <a href="{{ route('prestacion-social.imprimir', $prestacion->id) }}" target="_blank">
                                                                                    <li class="list-group-item">{{str_replace('_', ' ', $prestacion->nombre)}}</li>
                                                                                </a>
                                                                                @endforeach
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-6 pl-3">
                                                                        <div class="card" style="width: 100%;">
                                                                            <ul class="list-group list-group-flush">
                                                                                <a href="{{ route('nomina.pdf', $nominaPeriodo->id)}}" target="_blank">
                                                                                    <li class="list-group-item">
                                                                                        Nomina
                                                                                    </li>
                                                                                </a>
                                                                                @foreach($nomina->prestacionesSociales as $prestacion)
                                                                                <a href="{{ route('nomina.pdf', [$nominaPeriodo->id, 'prestacion_social' => $prestacion->id])}}" target="_blank">
                                                                                    <li class="list-group-item">
                                                                                        Nomina
                                                                                        mas {{ strtoupper(str_replace('_', ' ', $prestacion->nombre)) }}</li>
                                                                                </a>
                                                                                @endforeach


                                                                                @if($nomina->prestacionesSociales->count() >= 2)
                                                                                <a href="{{ route('nomina.pdf', [$nominaPeriodo->id, 'prestacion_social' => 'todas'])}}" target="_blank">
                                                                                    <li class="list-group-item">Nomina con todo</li>
                                                                                </a>
                                                                                @endif
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                @endif
                                                {{-- <i class="far fa-paper-plane color"></i>--}}
                                            </td>
                                            @include('nomina.modals.extras-y-recargos')
                                            @if($loop->iteration == 1)
                                            @include('nomina.modals.comentario')
                                            @endif
                                        </tr>
                                        @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="pensionados" role="tabpanel" aria-labelledby="pensionados-tab">
                            <input type="hidden" id="url-show-pensionados" value="{{route('bancos.cliente.movimientos.cuenta', 1)}}">
                            <div class="table-responsive">
                                <table class="table table-light table-striped table-hover" id="table-show-pensionados" style="width: 100%; border: 1px solid #e9ecef;">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="align-middle">Personas</th>
                                            <th class="align-middle">Salario base</th>
                                            <th>Horas extras y<br>recargos</th>
                                            <th>Vacaciones,<br>Incap y Lic</th>
                                            <th>Ingresos<br>adicionales</th>
                                            <th>Deducc, prést y<br>ReteFuen</th>
                                            <th>Pago<br>empleado</th>
                                            <th class="align-middle text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pensionados as $nomina)
                                        @foreach($nomina->nominaperiodos as $nominaPeriodo)
                                        @php $personaValor = $moneda . App\Funcion::Parsear($nominaPeriodo->pago_empleado ? $nominaPeriodo->pago_empleado : $nominaPeriodo->valor_total); @endphp
                                        <tr>
                                            <td>
                                                <a href="{{route('personas.show', $nomina->persona)}}" target="_blank">
                                                    {{$nomina->persona->nombre()}}
                                                </a>
                                            </td>
                                            <td>{{$personaValor}} <input type="hidden" id="salario-promedio-nomina-{{$nominaPeriodo->id}}" value="{{$nomina->persona->valor}}"> <input type="hidden" id="base-periodo-nomina-{{$nominaPeriodo->id}}" value="{{$nominaPeriodo->pago_empleado ? $nominaPeriodo->pago_empleado : $nominaPeriodo->valor_total}}">
                                            </td>
                                            <td>
                                                <span id="extras{{ $nominaPeriodo->id }}">{{ $nominaPeriodo->extras() }}</span>
                                                @if($nomina->persona_liquidada == false && !$modoLectura->success)
                                                <i class="far fa-edit color" onclick="editHoras('{{ $nominaPeriodo->id }}');"></i>
                                                @endif
                                            </td>
                                            <td>
                                                <span id="vacaciones{{ $nominaPeriodo->id }}">{{ $nominaPeriodo->vacaciones() }}</span>
                                                @if($nomina->persona_liquidada == false && !$modoLectura->success)
                                                <i class="far fa-edit color" onclick="editVacaciones('{{ $nominaPeriodo->id }}');"></i>
                                                @endif

                                                @if($nomina->persona->subsidio)
                                                <input type="hidden" name="dias_trabajados" id="dias-trabajados-{{$nominaPeriodo->id}}" value="{{$nominaPeriodo->diasTrabajados()}}"> @endif
                                                <input type="hidden" id="dias-pagos" name="dias_pagos" value="{{$nominaPeriodo->diasTrabajados()}}">
                                            </td>
                                            <td>{{Auth::user()->empresaObj->moneda}} <span id="ingresos{{ $nominaPeriodo->id }}">{{ App\Funcion::Parsear($nominaPeriodo->ingresos()) }}</span>
                                                @if($nomina->persona_liquidada == false && !$modoLectura->success)
                                                <i class="far fa-edit color" onclick="editAdicionales('{{ $nominaPeriodo->id }}');"></i>
                                                @endif
                                            </td>
                                            <td>{{Auth::user()->empresaObj->moneda}} <span id="deducciones{{ $nominaPeriodo->id }}">{{ App\Funcion::Parsear($nominaPeriodo->deducciones()) }}</span>
                                                @if($nomina->persona_liquidada == false && !$modoLectura->success)
                                                <i class="far fa-edit color" onclick="editDeducciones('{{ $nominaPeriodo->id }}');"></i>
                                                @endif
                                            </td>
                                            <td>
                                                <span id="pago-nomina-format-{{$nominaPeriodo->id}}">{{Auth::user()->empresaObj->moneda}} {{ App\Funcion::Parsear($nominaPeriodo->valor_total ? $nominaPeriodo->valor_total : 0) }} </span><input type="hidden" id="pago-nomina-{{$nomina->id}}" value="{{$nominaPeriodo->pago_empleado ? $nominaPeriodo->pago_empleado : $nominaPeriodo->valor_total}}">
                                            </td>
                                            <td>
                                                @if(!$modoLectura->success)
                                                <a id="a-ob-nomina-{{$nominaPeriodo->id}}" href="javascript:modificarNota('{{ $nominaPeriodo->observaciones }}', '{{ $nominaPeriodo->id}}', 'a-ob-nomina-{{$nominaPeriodo->id}}')">
                                                    <i class="far fa-folder-open color"></i>
                                                </a>
                                                @endif
                                                <a href="{{route('nomina.calculos',[$nominaPeriodo->id, 'periodo' => $mensajePeriodo])}}"><i class="far fa-eye color"></i></a>
                                           
                                                @if($nomina->prestacionesSociales->count() == 0)
                                                <a href="{{ route('nomina.pdf', $nominaPeriodo->id)}}" target="_blank"><i class="far fa-file color"></i></a> @else
                                                <a href="#" data-toggle="modal" data-target="#modal-imprimir-{{ $nominaPeriodo->id }}"><i class="far fa-file color"></i></a>

                                                <div class="modal" id="modal-imprimir-{{ $nominaPeriodo->id }}" tabindex="-1" role="dialog">
                                                    <div class="modal-dialog" role="document">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Opciones de
                                                                    impresión</h5>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row w-100">
                                                                    <div class="col-6 pl-3">
                                                                        <div class="card" style="width: 100%;">
                                                                            <ul class="list-group list-group-flush">
                                                                                @foreach($nomina->prestacionesSociales as $prestacion)
                                                                                <a href="{{ route('prestacion-social.imprimir', $prestacion->id) }}" target="_blank">
                                                                                    <li class="list-group-item">{{str_replace('_', ' ', $prestacion->nombre)}}</li>
                                                                                </a>
                                                                                @endforeach
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-6 pl-3">
                                                                        <div class="card" style="width: 100%;">
                                                                            <ul class="list-group list-group-flush">
                                                                                <a href="{{ route('nomina.pdf', $nominaPeriodo->id)}}" target="_blank">
                                                                                    <li class="list-group-item">
                                                                                        Nomina
                                                                                    </li>
                                                                                </a>
                                                                                @foreach($nomina->prestacionesSociales as $prestacion)
                                                                                <a href="{{ route('nomina.pdf', [$nominaPeriodo->id, 'prestacion_social' => $prestacion->id])}}" target="_blank">
                                                                                    <li class="list-group-item">
                                                                                        Nomina
                                                                                        mas {{ strtoupper(str_replace('_', ' ', $prestacion->nombre)) }}</li>
                                                                                </a>
                                                                                @endforeach



                                                                                @if($nomina->prestacionesSociales->count() >= 2)
                                                                                <a href="{{ route('nomina.pdf', [$nominaPeriodo->id, 'prestacion_social' => 'todas'])}}" target="_blank">
                                                                                    <li class="list-group-item">Nomina con todo</li>
                                                                                </a>
                                                                                @endif
                                                                            </ul>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                @endif
                                            
                                                {{-- <i class="far fa-paper-plane color"></i>--}}
                                            </td>
                                            @include('nomina.modals.extras-y-recargos')
                                            @if($loop->iteration == 1)
                                            {{-- @include('nomina.modals.comentario') --}}
                                            @endif
                                        </tr>
                                        @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">

        <div class="col-6 col-md-3">
            <b style="font-size: 13px">CONFIRMA LOS VALORES</b>
            <p>Asegurate que los valores esten 100% correctos. <i class="far fa-check-circle" style="font-size:15px"></i></p>
        </div>

        <div class="col-6 col-md-3">
            <b style="font-size:12px">TOTAL PAGO A PERSONAS</b><br>
            <span>$ <span id="total-pago-personas">{{ $costoPeriodo->pagoEmpleados }}</span> </span>
        </div>

        <div class="col-6 col-md-3" id="btn-confirmar-nomina">
            @if (!$modoLectura->success)
            <a href="{{ route('nomina.confirmar', ['year' => $year, 'periodo' => $periodo, 'periodo_quincenal' => request()->periodo_quincenal]) }}" role="button" class="btn btn-success">Liquidar nomina</a>
            @else
            <a href="#" role="button" class="btn btn-success disabled">Liquidar nomina</a>
            @endif
        </div>

        {{-- <div class="col-6 col-md-3" id="btn-confirmar-nomina">
            @if (!$modoLectura->success)
            <a href="{{ route('nomina-dian.emitir', ['periodo' => $periodo, 'year' => $year]) }}" role="button" class="btn btn-primary">Módulo de emitir nomina</a>
            @else
            <a href="#" role="button" class="btn btn-primary disabled">Módulo de emitir nomina</a>
            @endif
        </div> --}}
    </div>

    {{-- MODAL VACACIONES, INCAPACIDADES Y LICENCIAS --}}
    @include('nomina.modals.vacaciones-incap')

    {{-- MODAL INGRESOS --}}
    @include('nomina.modals.ingresos-adicionales')
    {{-- MODAL INGRESOS --}}

    {{-- MODAL DEDUCCIONES --}}
    @include('nomina.modals.deducciones-prest-retefuente')
    {{-- MODAL DEDUCCIONES --}}
</div>

<!-- Modal -->
<div class="modal fade" id="modalNotas" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">

</div>

@section('scripts')
<script>
    $(document).ready(function() {
        $('.datepicker').each(function() {
            $(this).datepicker('destroy');
            $(this).attr('type', 'date');
            $(this).css({
                'width': '100%',
                'font-size': '9px'
            });
        });

        firstTip = $('.tour-tips').first().attr('nro_tip');

        if (firstTip) {
            //nuevoTip(firstTip);
        }

        $('#table-show-empleados, #table-show-contratados, #table-show-pensionados, #table-show-aprendices, #table-show-estudiantes').DataTable({
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
            "paging": false,
            "searching": true,
            "order": [
                [0, "desc"]
            ],
        });

        firstTip = $('.tour-tips').first().attr('nro_tip');

        if (firstTip) {
            nuevoTip(firstTip);
        }

    });

    function datesV(id) {
        var ini = $("#v_desde_" + id).val();
        var fin = $("#v_hasta_" + id).val();
        if (fin) {
            if (fin < ini) {
                $("#v_hasta_" + id).val('');
                swal("ERROR EN VALIDACIÓN", "La fecha final debe ser mayor a la fecha inicial", "error");
            }
        }
    }

    function datesI(id) {
        var ini = $("#i_desde_" + id).val();
        var fin = $("#i_hasta_" + id).val();
        if (fin) {
            if (fin < ini) {
                $("#i_hasta_" + id).val('');
                swal("ERROR EN VALIDACIÓN", "La fecha final debe ser mayor a la fecha inicial", "error");
            }
        }
    }

    function datesL(id) {
        var ini = $("#l_desde_" + id).val();
        var fin = $("#l_hasta_" + id).val();
        if (fin) {
            if (fin < ini) {
                $("#l_hasta_" + id).val('');
                swal("ERROR EN VALIDACIÓN", "La fecha final debe ser mayor a la fecha inicial", "error");
            }
        }
    }

    function buscarPeriodo() {

        var year = '{{$year}}';
        var periodo = '{{$periodo}}';
        var type = $("#periodo_quincenal").val();
        // alert(periodo + " - " + periodo + " - " + type);
        var url = '/empresa/nomina/liquidar-nomina/' + periodo + '/' + year + '/' + true + '/' + type;
        $('#form-buscarnomina').attr('action', url);
        $('#form-buscarnomina').submit();
    }

    function formatPago(value, idNomina, formated = null) {

        let pagoFormat = '';

        if (!formated) {
            pagoFormat = (value).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,').slice(0, -3);
            (parseFloat(value)).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,').slice(0, -3);
        } else {
            pagoFormat = formated;
        }

        $('#pago-nomina-format-' + idNomina).text('$' + pagoFormat);
        return pagoFormat;
    }

    function refrescarCosto() {
        $.get($('#actualizar-costo-url').val(), function(response) {
            let costo = response.costo;

            $('#costo-empresa').html(costo.costoEmpresa);
            $('#pago-empleados').html(costo.pagoEmpleados);
            $('#total-pago-personas').html(costo.pagoEmpleados);
        });
    }

    function modificarNota(nota, id, anclor) {

        if (nota) {
            nota = String(nota);
        } else {
            nota = '';
        }

        id = parseInt(id);

        $('#modalNotas').html('');

        $('#modalNotas').append(`<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel">Observacion de nómina</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<div class="form-group">
						<label for="notas">Ingrese las observaciones de nómina</label>
						<textarea name="notas" id="notas-${id}" cols="30" rows="10" class="form-control">${nota}</textarea>
					</div>
					<div id="custom-target"></div>

				</div>
				<div class="modal-footer">
					<a  class="btn btn-secondary" data-dismiss="modal">Cerrar</a>
					<a  class="btn btn-primary text-white" onclick="guardarNotas(${id}, '${anclor}')">Guardar</a>
				</div>
			</div>
		</div>`);

        $('#modalNotas').modal('show');
    }

    function guardarNotas(id, anclor) {


        if (window.location.pathname.split("/")[1] === "software") {
				var url='/software/empresa';
		}else{
				var url = '/empresa';
        }
        $.ajax({
            url: url + `/nomina/agregar-observacion-periodo`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            data: {
                id: id,
                observacion: $('#notas-' + id).val()
            },
            success: function(response) {
                if (response.ok) {
                    $(`#nota-parrafo-${id}`).empty();
                    $('#modalNotas').modal('hide');
                    let observacion = response.observacion;
                    if (observacion) {
                        $(`#nota-parrafo-${id}`).append(observacion.substring(0, 24));
                        $(`#a-ob-nomina-${id}`).attr("href", "javascript:modificarNota('" + observacion + "', '" + id + "', 'a-ob-nomina-" + id + "')");
                    } else {
                        $(`#a-ob-nomina-${id}`).attr("href", "javascript:modificarNota('', '" + id + "', 'a-ob-nomina-" + id + "')");
                    }
                    $(`#nota-parrafo-${id}`).attr('title', observacion);
                    $('#' + anclor).attr('title', observacion);
                }
            }
        });
    }
</script>
@endsection

@endsection