@extends('layouts.app')


@section('style')
<style>
    .notice {
        padding: 15px;
        background-color: #fafafa;
        border-left: 6px solid #7f7f84;
        margin-bottom: 10px;
        -webkit-box-shadow: 0 5px 8px -6px rgba(0, 0, 0, .2);
        -moz-box-shadow: 0 5px 8px -6px rgba(0, 0, 0, .2);
        box-shadow: 0 5px 8px -6px rgba(0, 0, 0, .2);
    }

    .notice-sm {
        padding: 10px;
        font-size: 80%;
    }

    .notice-lg {
        padding: 35px;
        font-size: large;
    }

    .notice-success {
        border-color: #80D651;
    }

    .notice-success>strong {
        color: #80D651;
    }

    .notice-info {
        border-color: #45ABCD;
    }

    .notice-info>strong {
        color: #45ABCD;
    }

    .notice-warning {
        border-color: #FEAF20;
    }

    .notice-warning>strong {
        color: #FEAF20;
    }

    .notice-danger {
        border-color: #d73814;
    }

    .notice-danger>strong {
        color: #d73814;
    }

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
</style>
@endsection

@section('boton')
@if($modoLectura->success)
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <a>{{ $modoLectura->message }}, si deseas seguir disfrutando de nuestros servicios adquiere alguno de nuestros planes <a class="text-black" href="{{route('nomina.planes')}}"> <b>Click Aquí.</b></a></a>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@else
{{--@if(isset($_SESSION['permisos']['159']) || auth()->user()->username == 'gestordepartes')--}}
<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#new-nomina" id="btn-generar-nomina">
    <i class="fas fa-plus"></i> Generar Nueva Nómina
</button>
{{--@endif--}}
@endif
@endsection

@section('content')

@if(Session::has('error'))
<div class="alert alert-danger">
    {{Session::get('error')}}
</div>
@endif

@if(Session::has('success'))
<div class="alert alert-success">
    {{Session::get('success')}}
</div>
@endif

<script type="text/javascript">
    setTimeout(function() {
        $('.alert-success, .alert-danger').hide();
    }, 5000);
</script>

{{-- @include('nomina.tips.serie-base', ['pasos' => \collect([9])->diff(auth()->user()->guiasVistas()->keyBy('nro_tip')->keys())->all()]) --}}

<div class="row card-description">
    <div class="col-md-12">
        <div class="table-responsive">
            <table class="table table-light table-striped table-hover" id="table-nominas" style="width: 100%; border: 1px solid #e9ecef;">
                <thead class="thead-light">
                    <tr>
                        <th>Mes</th>
                        <th>Empleados</th>
                        <th>Emitidos</th>
                        <th>Por emitir</th>
                        <th>Rechazados</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($periodos as $periodo)
                    <tr>
                        <td>{{ $periodo->periodo() }}</td>
                        <td>{{ $periodo->empleados() }}</td>
                        <td>{{$periodo->estadosNomina()->aceptadas}}</td>
                        <td>{{$periodo->estadosNomina()->enEspera}}</td>
                        <td>{{$periodo->estadosNomina()->rechazadas}}</td>
                        <td>{{ $periodo->estadosNomina()->aceptadas == $periodo->empleados() ? 'Finalizado' : 'En Proceso' }}</td>
                        <td>
                            <a href="{{ route('nomina.liquidar', ['periodo' => $periodo->periodo, 'year'=> $periodo->year]) }}" title="Detalle de Nómina"><i class="far fa-eye color"></i></a>
                            <a href="#" data-toggle="modal" data-target="#reiniciar-{{$periodo->id}}" ><i data-tippy-content="Reinicia el periodo con los datos calculados y refresca la lista de personas ingresadas en el periodo" class="icono fas fa-redo color"></i></a>

                            <div class="modal" tabindex="-1" role="dialog" id="reiniciar-{{$periodo->id}}">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Reiniciar periodo - {{$periodo->periodo()}}</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                    <div class="row">

                                        <div class="col-12 p-1">
                                        <p syle="white-space:normal; font-size:15px;">Si usted reinicia el periodo, se refrescaran todos los valores calculados <br> y se utilizara la configuración vigente. <br> Recuerde las personas serán ingresadas en este periodo teniendo <br> encuenta la fecha de contratación</p>
                                        <p>¡Solo se verán afectadas las nominas de personas activas o habilitadas!</p>
                                        </div>

                                    </div>
                                    </div>
                                    <div class="modal-footer">
                                        <a href="{{ route('nomina.periodo.verificar.personas', [$periodo->periodo, $periodo->year]) }}" role="button" class="btn btn-primary">Reiniciar</a>
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                    </div>
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('nomina-dian.emitir', ['periodo' => $periodo->periodo, 'year'=> $periodo->year]) }}" title="Emitir Nómina"><i class="fas fa-sitemap color"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade bd-example-modal-lg" id="new-nomina" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Nómina Electrónica</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('nomina.periodo') }}" role="form" class="forms-sample p-0" id="periodoForm" onsubmit="event.preventDefault();">
                    @csrf
                    <div class="row">
                        <div class="col-12" style="font-size:13px">
                            <p class="text-justify">Para generar una nueva nómina electrónica, debe establecer el
                                periodo, indicando el año y el mes requerido.</p>
                        </div>
                        <div class="col-6" style="font-size:13px">
                            <div class="form-group">
                                <label>Año</label>
                                <select name="year" id="year" class="form-control selectpicker " title="Seleccione" data-live-search="true" data-size="5" required>
                                    <option value="{{ 2021 }}" selected="">{{ 2021 }}</option>
                                    <option value="{{ date('Y') }}" selected="">{{ date('Y') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6" style="font-size:13px">
                            <div class="form-group">
                                <label>Mes</label>
                                @php $mesActual = now()->format('m') + 1; @endphp
                                <select name="periodo" id="periodo" class="form-control selectpicker " title="Seleccione" data-live-search="true" data-size="5" required>
                                    <option value="1" {{ 1 <= $mesActual ? '' : 'disabled' }}>Enero</option>
                                    <option value="2" {{ 2 <= $mesActual ? '' : 'disabled' }}>Febrero</option>
                                    <option value="3" {{ 3 <= $mesActual ? '' : 'disabled' }}>Marzo</option>
                                    <option value="4" {{ 4 <= $mesActual ? '' : 'disabled' }}>Abril</option>
                                    <option value="5" {{ 5 <= $mesActual ? '' : 'disabled' }}>Mayo</option>
                                    <option value="6" {{ 6 <= $mesActual ? '' : 'disabled' }}>Junio</option>
                                    <option value="7" {{ 7 <= $mesActual ? '' : 'disabled' }}>Julio</option>
                                    <option value="8" {{ 8 <= $mesActual ? '' : 'disabled' }}>Agosto</option>
                                    <option value="9" {{ 9 <= $mesActual ? '' : 'disabled' }}>Septiembre</option>
                                    <option value="10" {{ 10 <= $mesActual ? '' : 'disabled' }}>Octubre</option>
                                    <option value="11" {{ 11 <= $mesActual ? '' : 'disabled' }}>Noviembre</option>
                                    <option value="12" {{ 12 <= $mesActual ? '' : '' }}>Diciembre</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-light" data-dismiss="modal" id="modal_periodo">Cerrar
                </button>
                <a href="javascript:periodoForm()" class="btn btn-success">Guardar</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function periodoForm() {
        if ($("#periodo").val().length > 0) {
            cargando(true);
            $.post($("#periodoForm").attr('action'), $("#periodoForm").serialize(), function(dato) {
                if (dato['success'] == true) {
                    $('#modal_periodo').click();
                    $('#periodoForm').trigger("reset");
                    cargando(false);
                    swal("Registro Almacenado", "Nómina electrónica generada satisfactoriamente", "success");
                    $('#table-nominas tbody').append(
                        `<tr>
                                <td>` + dato['nomina'] + `</td>
                                <td>` + dato['empleados'] + `</td>
                                <td>0</td>
                                <td>0</td>
                                <td>0</td>
                                <td>En Proceso</td>
                                <td>
                                    <a href="liquidar-nomina/` + dato['periodo'] + `/` + dato['year'] + `"><i class="far fa-eye color"></i></a>
                                    <a href="emitir-nomina/` + dato['periodo'] + `/` + dato['year'] + `"><i class="fas fa-sitemap color"></i></a>
                                </td>
                            </tr>`
                    );

                    window.location.href = dato['url'];

                } else {
                    swal('ERROR', dato['message_error'], "error");
                    cargando(false);
                }
            }, 'json');
        } else {
            swal('ERROR', 'Debe seleccionar un mes válido para crear la nómina electrónica', "error");
        }
    }
</script>


<script>
    $(document).ready(function() {

        // firstTip = $('.tour-tips').first().attr('nro_tip');

        // if (firstTip) {
        //     nuevoTip(firstTip);
        // }

    });
</script>
@endsection
