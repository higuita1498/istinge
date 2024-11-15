@extends('layouts.app')

@section('style')

<style>
    .estado-liquidado {
        background-color: rgba(255, 175, 0, 0.1) !important;
    }
</style>

@endsection


@section('content')

@if(Session::has('error'))
<div class="alert alert-danger">
    {{Session::get('error')}}
</div>

<script type="text/javascript">
    setTimeout(function() {
        $('.alert').hide();
        $('.active_table').attr('class', ' ');
    }, 9000);
</script>
@endif

<script type="text/javascript">
            setTimeout(function() {
                $('.alert').hide();
                $('.active_table').attr('class', ' ');
            }, 9000);
</script>


<div class="container-fluid">

    <div class="row">
        <div class="col-12">
        @if($request->ajuste)
        <a href="{{ route('nomina.ajustar', ['periodo' => $request->periodo, 'year' => $request->year, 'persona' => $request->persona, 'tipo' => 1, 'editNomina' => $request->editNomina]) }}" style="margin-left:10px"> <i class="fas fa-chevron-left"></i> Regresar a editar nomina </a>
        @else
        <a href="{{ route('nomina.liquidar', ['periodo' => $periodo, 'year' => $year]) }}" style="margin-left:10px"> <i class="fas fa-chevron-left"></i> Regresar a editar nomina </a>
        @endif
            @include('nomina.includes.periodo', ['mensajePeriodo' => str_replace('de', ' de ', str_replace('-', ' - ', $rango))])
        </div>
        <div class="alert alert-info">
            Observe y edite las prestaciones sociales de las nominas que no han sido emitidas
        </div>
    </div>

    <div class="row mt-4">

        <div class="col text-center px-0">
            @if(!$request->persona)
            <a href="#" style="margin-left:30px; text-decoration: underline;" data-toggle="modal" data-target="#modal-select-persona"> Liquidar una persona </a>
            @else
            <a href="{{ str_replace('persona', 'backPersona', $request->fullUrl()) }}" style="margin-left:30px; text-decoration: underline;"> Todas las personas </a>
            @endif
        </div>

        <div class="col text-center px-0">
            <a href="{{route('nomina.prestacion-social.descargar', ['title' => 'primas', 'tipo' => 'prima', 'year' => $year, 'periodo' => $periodo, 'desde' => $desde, 'hasta' => $hasta])}}" style="text-decoration: underline;"> Descargar resumen </a>
        </div>

        <div class="col text-center px-0">
            <a href="javascript:liquidarValorPagar()" style="text-decoration: underline;"> Liquidar valor a pagar </a>
        </div>

    </div>

    @include('nomina.prestacion-social.modal-select-persona')

    <div class="row mt-4">
        <div class="col-md-12">
            <form method="POST" action="{{ route('nomina.prestacion.social.store') }}" role="form">
                @csrf

                <div class="table-responsive">
                    <table class="table table-light table-striped table-hover w-100 border-1 bg-light" id="table-show-empleados">
                        <thead class="thead-light">
                            <tr>
                                <th class="align-middle">Nombre</th>
                                <th class="align-middle">Base prima de servicios</th>
                                <th>Dias trabajados</th>
                                <th>Valor prima</th>
                                <th>Saldo pendiente</th>
                                <th>Estado</th>
                                <th>Valor a pagar</th>
                                <th class="align-middle text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody style="text-align:left">
                            @if(count($personas)>0)
                            @foreach ($personas as $persona)
                            @if(!$persona->nominaSeleccionada)
                            @php continue; @endphp
                            @endif
                            @php $prima = $persona->nominaSeleccionada->prima @endphp
                            <tr title="{{ !$prima ? 'Los valores de esta fila no están guardados' : '' }}" class="{{ !$prima ? 'estado-liquidado' : '' }}">
                                <td><input type="hidden" name="id_nominas[]" value="{{$persona->nominaSeleccionada->id}}"> <input name="nombres[]" type="hidden" value="prima">{{$persona->nombre()}}
                                    <span class="help-block error">
                                        <strong>{{ $errors->first('id_nominas.'.$loop->index) }}</strong>
                                    </span>
                                </td>
                                <td><input name="bases[]" type="number" step="any" value="{{$prima ?  intval(round($prima->base)) :  intval(round($persona->totalidades['salarioSubsidio']))}}" onchange="actualizarPrima('{{$persona->id }}');" class="form-control" id="salario-{{$persona->id }}" min="0">
                                    <span class="help-block error">
                                        <strong>{{ $errors->first('bases.'.$loop->index) }}</strong>
                                    </span>
                                </td>
                                <td><input name="diasTrabajados[]" type="number" value="{{$prima ? $prima->dias_trabajados : $persona->totalidades['diasTrabajados'] }}" onchange="actualizarPrima('{{$persona->id }}');" class="form-control" id="dias-trabajados-{{$persona->id}}" min="0" max="365">
                                    <span class="help-block error">
                                        <strong>{{ $errors->first('diasTrabajados.'.$loop->index) }}</strong>
                                    </span>
                                </td>
                                <td><span id="total-prima-{{$persona->id }}">${{ $persona->nominaSeleccionada->parsear($prima ? ($valorPrima = $prima->valor) : ($valorPrima = round($persona->totalidades['valorTotal']))) }}</span> <input id="total-input-prima-{{$persona->id}}" type="hidden" name="valores[]" value="{{$valorPrima}}">
                                    <span class="help-block error">
                                        <strong>{{ $errors->first('valores.'.$loop->index) }}</strong>
                                    </span>
                                </td>
                                <td id="deuda-{{$persona->id }}">$ {{ $persona->nominaSeleccionada->parsear($prima ? ($prima->valor - $prima->valor_pagar) : '0') }}</td>

                                <td><strong class="{{ !$prima ? 'text-muted' : '' }}">{{ !$prima ? 'Sin liquidar' : 'Liquidado' }}</strong></td>

                                <td><input name="valoresPagar[]" type="number" value="{{ round($prima ? $prima->valor_pagar : 0) }}" onchange="actualizarPrima('{{$persona->id }}');" class="form-control" id="valor-pagar-{{$persona->id }}" min="0" persona="{{$persona->id}}">
                                    <span class="help-block error">
                                        <strong>{{ $errors->first('valoresPagar.'.$loop->index) }}</strong>
                                    </span>
                                </td>

                                <td>
                                    <a href="{{ route('nomina.prestacion-social.refrescar', ($prima ? $prima->id : '')) }}" title="Refrescar formulario" class="btn btn-outline-secondary btn-icons" onclick="" id="">
                                        <i class="fas fa-sync"></i>
                                    </a>
                                    <a class="btn btn-outline-secondary btn-icons ml-1" onclick="" id="" title="Ver resumen del cálculo de la prima de servicios" data-toggle="modal" data-target="#modal-ver-calculo-{{$persona->id}}">
                                        <i class="far fa-eye"></i>
                                    </a>
                                </td>

                                <div class="modal fade" id="modal-ver-calculo-{{$persona->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">RESUMEN</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">

                                                <div class="row">
                                                    <div class="col-12">
                                                        <i class="fas fa-check"></i> <b>Cálculo de Salario base</b>
                                                    </div>
                                                </div>

                                                <div class="row mt-2 body-oscuro">

                                                    <div class="col-12">
                                                        <div class="row">
                                                            <div class="col-4 p-0" style="text-align:right">
                                                                <span style="top: 11px; position: relative;"> Salario base = </span>
                                                            </div>
                                                            <div class="col-5 p-0" style="text-align:center">
                                                                <div class="row">
                                                                    <div class="col-12 p-0 pt-1" style="border-bottom: 1px solid">
                                                                        $ {{ $persona->nominaSeleccionada->parsear($persona->totalidades['totalSalarioPeriodo']) }} * 30 días
                                                                    </div>
                                                                    <div class="col-12 p-0">
                                                                        {{ $persona->nominaSeleccionada->parsear($persona->totalidades['diasTrabajadosFijos']) }} días
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-3 p-0" style="text-align:left">
                                                                <span style="top: 11px; position: relative;"> = $ {{ $persona->nominaSeleccionada->parsear($persona->totalidades['salarioBase']) }} </span>
                                                            </div>
                                                        </div>
                                                        <div class="row mt-2 pl-3">
                                                            <div class="col-12">
                                                                Salario base = $ {{ $persona->nominaSeleccionada->parsear($persona->totalidades['salarioBase']) }} + $ {{ $persona->nominaSeleccionada->parsear($persona->totalidades['subsidioTransporte']) }} (transporte)
                                                            </div>

                                                            <div class="col-12">
                                                                <span style="font-weight: 500">Salario base = $ {{ $persona->nominaSeleccionada->parsear($persona->totalidades['salarioSubsidio']) }}</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>


                                                <div class="row mt-4">
                                                    <div class="col-12">
                                                        <i class="fas fa-check"></i> <b>Cálculo de Prima de servicios</b>
                                                    </div>
                                                </div>

                                                <div class="row mt-2 body-oscuro">

                                                    <div class="col-12">
                                                        <div class="row">
                                                            <div class="col-4 p-0" style="text-align:right">
                                                                <span style="top: 11px; position: relative;"> Prima = </span>
                                                            </div>
                                                            <div class="col-5 p-0" style="text-align:center">
                                                                <div class="row">
                                                                    <div class="col-12 p-0 pt-1" style="border-bottom: 1px solid">
                                                                        $ {{ $persona->nominaSeleccionada->parsear($persona->totalidades['salarioSubsidio']) }} * {{$persona->totalidades['diasTrabajados']}} días
                                                                    </div>
                                                                    <div class="col-12 p-0">
                                                                        360 días
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col-3 p-0" style="text-align:left">
                                                                <span style="top: 11px; position: relative;"> = $ {{ $persona->nominaSeleccionada->parsear($persona->totalidades['valorTotal']) }} </span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>

                                                <div class="row mt-3">
                                                    <div class="col-12">
                                                        <i class="fas fa-equals"></i> <b>Prima de servicios = </b> $ {{$persona->nominaSeleccionada->parsear($persona->totalidades['valorTotal'])}}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">

                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </tr>
                            @endforeach
                            @else

                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="col-12 mb-5 text-center mt-4">
                    <button type="submit" class="btn btn-success float-right">Guardar & continuar</button>
                </div>

            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')

<script>
    function actualizarPrima(idNomina) {
        let salario = $('#salario-' + idNomina).val();
        let diasTrabajados = $('#dias-trabajados-' + idNomina).val();

        if (diasTrabajados > 365) {
            diasTrabajados = 365;
            $('#dias-trabajados-' + idNomina).val(diasTrabajados);
        }

        let valor_pagar = $('#valor-pagar-' + idNomina);

        let subtotal = (salario * diasTrabajados) / 360;

        subtotal = Math.round(subtotal);
        //  valor_pagar.val(subtotal);
        $('#total-input-prima-' + idNomina).val(subtotal);

        let deuda = subtotal - valor_pagar.val();

        if (deuda < 0) {
            deuda = 0;
            valor_pagar.val(subtotal);
        }

        let formatPago = (subtotal).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,').slice(0, -3);
        (parseFloat(subtotal)).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,').slice(0, -3);
        let formatDeuda = (deuda).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,').slice(0, -3);
        (parseFloat(deuda)).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,').slice(0, -3);
        $('#total-prima-' + idNomina).html('$' + formatPago);
        $('#deuda-' + idNomina).html('$' + formatDeuda);
    }


    function liquidarValorPagar() {
        $('input[name="valoresPagar[]"]').each(function(i) {
            let input = $(this);
            let valorTotal = $('#total-input-prima-' + (input.attr('persona'))).val();
            input.val(valorTotal);
            input.trigger('change');
        });
    }
</script>


@endsection
