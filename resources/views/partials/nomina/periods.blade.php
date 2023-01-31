<div class="row">
    <div class="col-6 p-3">
        @include('nomina.includes.periodo', ['mensajePeriodo' => $mensajePeriodo])
    </div>
    <div class="col-6 pr-3">
        <form method="get" action="" style="padding: 2% 3%;" role="form" class="forms-sample"
              id="form-buscarnomina">
            {{-- @csrf --}}
            <div class="notice">
                @if($variosPeriodos->count() == 2)
                    <strong>1- Elige la quincena que deseas editar</strong>
                    <hr>
                    <div class="d-flex">
                        @php $periodoSeleccionado = null; @endphp
                        <select class="form-control selectpicker" name="periodo_quincenal" id="periodo_quincenal"
                                required="" title="Seleccione" data-size="5">
                            @foreach($variosPeriodos as $periodos)
                                <option
                                        @if($tipo == null && $periodos->periodo == 1 || $tipo == 1 && $periodos->periodo == 1)
                                        {{"selected"}}
                                        @php $periodoSeleccionado = $periodos; @endphp
                                        @elseif($tipo == 2 && $periodos->periodo == 2)
                                        {{"selected"}}
                                        @php $periodoSeleccionado = $periodos; @endphp
                                        @endif
                                        value={{$periodos->periodo}}
                                >
                                    {{Carbon\Carbon::parse($periodos->fecha_desde)->format('d')}}
                                    - {{Carbon\Carbon::parse($periodos->fecha_hasta)->format('d')}} {{"de"}} {{Carbon\Carbon::parse($periodos->fecha_hasta)->monthName}} {{Carbon\Carbon::parse($periodos->fecha_desde)->format('Y')}}
                                </option>
                            @endforeach

                            <input type="hidden" id="f_ini"
                                   value="{{ $periodoSeleccionado ? Carbon\Carbon::parse($periodoSeleccionado->fecha_desde)->format('Y-m-d') : ' ' }}">
                            <input type="hidden" id="f_fin"
                                   value="{{ $periodoSeleccionado ? Carbon\Carbon::parse($periodoSeleccionado->fecha_hasta)->format('Y-m-d') : ' ' }}">

                        </select>
                        <a href="javascript:buscarPeriodo()" class="btn btn-success ml-2">Elegir</a>
                    </div>
                @else
                    <strong>Este mes el pago de tu empresa es mensual.</strong>
                    <hr>
                    <select class="form-control selectpicker" name="periodo_completo" id="periodo_completo"
                            required="" title="Seleccione" data-size="5">
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
