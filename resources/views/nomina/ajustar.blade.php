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

        input[type="date" i] {
            color: rgb(0, 0, 0) !important;
            font-size: 11px;
            font-weight: 400;
        }

        div.dataTables_filter input {
            padding: 4px !important;
        }

        .nav-tabs .nav-link.active, .nav-tabs .nav-item.show .nav-link {
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

    {{-- @include('nomina.tips.serie-base', ['pasos' => \collect([2,3,4])->diff($guiasVistas->keyBy('nro_tip')->keys())->all()]) --}}

    @include('partials.nomina.periods')

    @include('partials.nomina.pago-persona-costo-empresa',["person" => "persona"])


    <div class="row">
        <div class="col-12 px-5 py-3">
            <div class="row card-description">
                <div class="col-md-12">
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="empleados-tab" data-toggle="tab" href="#empleados" role="tab"
                               aria-controls="empleados" aria-selected="true">{{ $tipoContrato }}</a>
                        </li>
                    </ul>
                </div>

                <div class="col-md-12 mt-5">
                    <div class="table-responsive">
                        <table class="table table-light table-striped table-hover" id="table-show-empleados"
                               style="width: 100%; border: 1px solid #e9ecef;">
                            <thead class="thead-light">
                            <tr>
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

                            @foreach($nomina->nominaperiodos as $nominaPeriodo)
                                @php $personaValor = $moneda . App\Funcion::Parsear($nominaPeriodo->pago_empleado ? $nominaPeriodo->pago_empleado : $nominaPeriodo->valor_total); @endphp
                                <tr>
                                    <td>{{$personaValor}}
                                        <input type="hidden"
                                               id="salario-promedio-nomina-{{$nominaPeriodo->id}}"
                                               value="{{$nomina->persona->valor}}">
                                        <input type="hidden"
                                               id="base-periodo-nomina-{{$nominaPeriodo->id}}"
                                               value="{{$nominaPeriodo->pago_empleado ? $nominaPeriodo->pago_empleado : $nominaPeriodo->valor_total}}">
                                    </td>
                                    <td>
                                        <span id="extras{{ $nominaPeriodo->id }}">{{ $nominaPeriodo->extras() }}</span>
                                        <i class="far fa-edit color" onclick="editHoras({{ $nominaPeriodo->id }});"></i>
                                    </td>
                                    <td>
                                        <span id="vacaciones{{ $nominaPeriodo->id }}">{{ $nominaPeriodo->vacaciones() }}</span>
                                        <i class="far fa-edit color"
                                           onclick="editVacaciones({{ $nominaPeriodo->id }});"></i>
                                        @if($nomina->persona->subsidio)
                                            <input type="hidden"
                                                   name="dias_trabajados"
                                                   id="dias-trabajados-{{$nominaPeriodo->id}}"
                                                   value="{{$nominaPeriodo->diasTrabajados()}}">
                                        @endif
                                        <input type="hidden" id="dias-pagos" name="dias_pagos"
                                               value="{{$nominaPeriodo->diasTrabajados()}}">
                                    </td>
                                    <td>{{Auth::user()->empresaObj->moneda}}
                                        <span id="ingresos{{ $nominaPeriodo->id }}">{{ App\Funcion::Parsear($nominaPeriodo->ingresos()) }}</span>
                                        <i class="far fa-edit color"
                                           onclick="editAdicionales({{ $nominaPeriodo->id }});"></i></td>
                                    <td>{{Auth::user()->empresaObj->moneda}}
                                        <span id="deducciones{{ $nominaPeriodo->id }}">{{ App\Funcion::Parsear($nominaPeriodo->deducciones()) }}</span>
                                        <i class="far fa-edit color"
                                           onclick="editDeducciones({{ $nominaPeriodo->id }});"></i></td>
                                    <td>
                                        <span id="pago-nomina-format-{{$nominaPeriodo->id}}">
                                            {{Auth::user()->empresaObj->moneda}} {{ App\Funcion::Parsear($nominaPeriodo->valor_total ? $nominaPeriodo->valor_total : 0) }}
                                        </span>
                                        <input type="hidden"
                                               id="pago-nomina-{{$nomina->id}}"
                                               value="{{$nominaPeriodo->pago_empleado ? $nominaPeriodo->pago_empleado : $nominaPeriodo->valor_total}}">
                                    </td>
                                    <td><i class="far fa-folder-open color"
                                           data-toggle="modal"
                                           data-target="#comentario-1"></i>
                                        <a href="{{route('nomina.calculos',[$nominaPeriodo->id, 'periodo' => $mensajePeriodo])}}">
                                            <i class="far fa-eye color"></i></a>
                                        @if($nomina->prestacionesSociales->count() == 0)
                                            <a href="{{ route('nomina.pdf', $nominaPeriodo->id)}}"
                                               target="_blank"><i class="far fa-file color"></i>
                                            </a>
                                        @else
                                            <a href="#"
                                               data-toggle="modal"
                                               data-target="#modal-imprimir-{{ $nominaPeriodo->id }}">
                                                <i class="far fa-file color"></i>
                                            </a>

                                            <div class="modal"
                                                 id="modal-imprimir-{{ $nominaPeriodo->id }}"
                                                 tabindex="-1"
                                                 role="dialog">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Opciones de impresión</h5>
                                                            <button type="button" class="close"
                                                                    data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row w-100">
                                                                <div class="col-6 pl-3">
                                                                    <div class="card" style="width: 100%;">
                                                                        <ul class="list-group list-group-flush">
                                                                            @foreach($nomina->prestacionesSociales as $prestacion)
                                                                                <a href="{{ route('prestacion-social.imprimir', $prestacion->id) }}"
                                                                                   target="_blank">
                                                                                    <li class="list-group-item">{{str_replace('_', ' ', $prestacion->nombre)}}</li>
                                                                                </a>
                                                                            @endforeach
                                                                        </ul>
                                                                    </div>
                                                                </div>
                                                                <div class="col-6 pl-3">
                                                                    <div class="card" style="width: 100%;">
                                                                        <ul class="list-group list-group-flush">
                                                                            <a href="{{ route('nomina.pdf', $nominaPeriodo->id)}}"
                                                                               target="_blank">
                                                                                <li class="list-group-item">
                                                                                    Nomina
                                                                                </li>
                                                                            </a>
                                                                            @foreach($nomina->prestacionesSociales as $prestacion)
                                                                                <a href="{{ route('nomina.pdf', [$nominaPeriodo->id, 'prestacion_social' => $prestacion->id])}}"
                                                                                   target="_blank">
                                                                                    <li class="list-group-item">
                                                                                        Nomina
                                                                                        mas {{ strtoupper(str_replace('_', ' ', $prestacion->nombre)) }}</li>
                                                                                </a>
                                                                            @endforeach

                                                                            @if($nomina->prestacionesSociales->count() >= 2)
                                                                                <a href="{{ route('nomina.pdf', [$nominaPeriodo->id, 'prestacion_social' => 'todas'])}}"
                                                                                    target="_blank">
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
                                        <i class="far fa-paper-plane color"></i>
                                    </td>
                                    @include('nomina.modals.extras-y-recargos')
                                    @if($loop->iteration == 1)
                                        @include('nomina.modals.comentario')
                                    @endif
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-3"></div>

        <div class="col-3">
            <b style="font-size: 13px">CONFIRMA LOS VALORES</b>
            <p>Asegurate que los valores esten 100% correctos. <i class="far fa-check-circle"
                                                                  style="font-size:15px"></i></p>
        </div>

        <div class="col-3">
            <b style="font-size:12px">TOTAL PAGO A PERSONA</b><br>
            <span>$ <span id="total-pago-personas">{{ $costoPeriodo->pagoEmpleados }}</span> </span>
        </div>

        <div class="col-3" id="btn-confirmar-nomina">
            <a href="{{ route('estado-nominas.ajustar', [
                                'year' => $year,
                                'periodo' => $periodo,
                                "persona" => $persona,
                                "nomina" => $nomina->id
                                ]) }}"
               role="button"
               class="btn btn-success">Guardar y volver</a>
        </div>
    </div>




    {{-- MODAL VACACIONES, INCAPACIDADES Y LICENCIAS --}}
    @include('nomina.modals.vacaciones-incap')

    {{-- MODAL INGRESOS --}}
    @include('nomina.modals.ingresos-adicionales')
    {{-- MODAL INGRESOS --}}

    {{-- MODAL DEDUCCIONES --}}
    @include('nomina.modals.deducciones-prest-retefuente')
    {{-- MODAL DEDUCCIONES --}}


@section('scripts')
    <script>
        $(document).ready(function () {
            $('.datepicker').each(function () {
                $(this).datepicker('destroy');
                $(this).attr('type', 'date');
                $(this).css({'width': '100%', 'font-size': '9px'});
            });

            firstTip = $('.tour-tips').first().attr('nro_tip');

            if (firstTip) {
                //nuevoTip(firstTip);
            }

            // $('#table-show-empleados, #table-show-contratados, #table-show-pensionados, #table-show-aprendices, #table-show-estudiantes').DataTable({
            //     "language": {
            //         "zeroRecords": "Disculpe, No existen registros",
            //         "info": "",
            //         "infoEmpty": " ",
            //         "infoFiltered": "(Filtrado de _MAX_ total entradas)",
            //         "infoPostFix": "",
            //         "decimal": ",",
            //         "thousands": ".",
            //         "lengthMenu": "",
            //         "loadingRecords": "Cargando...",
            //         "processing": "Procesando...",
            //         "search": "Buscar:",
            //         "zeroRecords": "Sin resultados encontrados",
            //     },
            //     "paging": false,
            //     "searching": true,
            //     "order": [[0, "desc"]],
            // });
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

            const year = {{$year}};
            const periodo = {{$periodo}};
            const type = $("#periodo_quincenal").val();
            const persona = {{$persona}};
            // alert(periodo + " - " + periodo + " - " + type);
            const url = '/empresa/nomina/ajustar-nomina/' + periodo + '/' + year + '/' + persona + '/' + type;
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
            $.get($('#actualizar-costo-url').val(), function (response) {
                let costo = response.costo;

                $('#costo-empresa').html(costo.costoEmpresa);
                $('#pago-empleados').html(costo.pagoEmpleados);
                $('#total-pago-personas').html(costo.pagoEmpleados);
            });
        }
    </script>
@endsection


@endsection
