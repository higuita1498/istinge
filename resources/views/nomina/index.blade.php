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
        color: #022454;
        background: #e9ecef;
        font-weight: bold;
        padding: 5px;
        border-radius: 5px;
        border: solid 1px #dbdbdb;
    }

    .color:hover {
        border: solid 1px #022454;
    }

    .w-77 {
        width: 77% !important;
    }
</style>
@endsection

@section('boton')
@if(auth()->user()->modo_lecturaNomina())
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <a>Estas en modo lectura, si deseas seguir disfrutando de nuestros servicios adquiere alguno de nuestros planes
        <a class="text-black" href="{{ route('clientplans.index') }}"> <b>Click Aquí.</b></a></a>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
@else
{{-- @if(isset($_SESSION['permisos']['159']) || auth()->user()->username == 'gestordepartes') --}}
<x-modules-header
    titleModule='Nómina Electrónica'
    description='Haz los pagos de tu equipo fácil y rápido, creando periodos de salario sin complicaciones'
    hideActions='true'>
    <x-slot name="icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="var(--gestoru-secundario)" title="Nomina User">
            <path d="M7.75 7.5C7.75 5.15279 9.65279 3.25 12 3.25C14.3472 3.25 16.25 5.15279 16.25 7.5C16.25 9.84721 14.3472 11.75 12 11.75C9.65279 11.75 7.75 9.84721 7.75 7.5Z" fill="#141B34"/>
            <path d="M4.25 20C4.25 16.1678 7.83242 13.25 12 13.25C16.1676 13.25 19.75 16.1678 19.75 20V20.75H4.25V20Z" fill="currentColor"/>
            <path d="M7.23113 4.75809C6.76605 5.56524 6.5 6.50155 6.5 7.5C6.5 8.89211 7.0172 10.1634 7.86994 11.1323C7.59306 11.209 7.30133 11.25 7.00002 11.25C5.20511 11.25 3.74998 9.79494 3.74998 8C3.74998 6.20506 5.20511 4.75 7.00002 4.75C7.07772 4.75 7.15479 4.75273 7.23113 4.75809Z" fill="#141B34"/>
            <path d="M8.786 12.5328C5.91139 13.5128 3.61372 15.7763 3.10517 18.75H1.25V18C1.25 14.8244 3.82436 12.25 7 12.25C7.62349 12.25 8.2238 12.3492 8.786 12.5328Z" fill="#141B34"/>
            <path d="M20.8947 18.75H22.7499V18C22.7499 14.8244 20.1755 12.25 16.9999 12.25C16.3764 12.25 15.7761 12.3492 15.2139 12.5328C18.0885 13.5128 20.3861 15.7763 20.8947 18.75Z" fill="#141B34"/>
            <path d="M16.1299 11.1323C16.4068 11.209 16.6986 11.25 16.9999 11.25C18.7948 11.25 20.2499 9.79493 20.2499 8C20.2499 6.20507 18.7948 4.75 16.9999 4.75C16.9222 4.75 16.8451 4.75273 16.7687 4.75809C17.2338 5.56524 17.4999 6.50155 17.4999 7.5C17.4999 8.89211 16.9827 10.1634 16.1299 11.1323Z" fill="#141B34"/>
        </svg>
    </x-slot>
    <x-slot name="buttonAditional">
        <button class="btn-actions create" data-toggle="modal" data-target="#new-nomina"
            id="btn-generar-nomina">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="15" viewBox="0 0 16 15"
                fill="none">
                <path fill-rule="evenodd" clip-rule="evenodd"
                    d="M3.47299 0.230384C6.48178 -0.0767946 9.51822 -0.0767946 12.527 0.230384C14.1929 0.402364 15.5369 1.6107 15.7324 3.15046C16.0892 5.96093 16.0892 8.80013 15.7324 11.6106C15.6335 12.3588 15.2631 13.0543 14.6813 13.5843C14.0995 14.1143 13.3404 14.4478 12.527 14.5307C9.51827 14.8388 6.48172 14.8388 3.47299 14.5307C2.65964 14.4478 1.90052 14.1143 1.31872 13.5843C0.736911 13.0543 0.366526 12.3588 0.267611 11.6106C-0.0892037 8.80043 -0.0892037 5.96153 0.267611 3.15135C0.366484 2.4033 0.736728 1.70793 1.31834 1.17792C1.89995 0.647922 2.65884 0.314353 3.47202 0.231279L3.47299 0.230384ZM8 2.90861C8.19344 2.90861 8.37896 2.97939 8.51575 3.10537C8.65253 3.23136 8.72938 3.40224 8.72938 3.58041V6.70918H12.1263C12.3198 6.70918 12.5053 6.77996 12.6421 6.90595C12.7789 7.03194 12.8557 7.20281 12.8557 7.38098C12.8557 7.55915 12.7789 7.73003 12.6421 7.85601C12.5053 7.982 12.3198 8.05278 12.1263 8.05278H8.72938V11.1816C8.72938 11.3597 8.65253 11.5306 8.51575 11.6566C8.37896 11.7826 8.19344 11.8534 8 11.8534C7.80655 11.8534 7.62104 11.7826 7.48425 11.6566C7.34747 11.5306 7.27062 11.3597 7.27062 11.1816V8.05278H3.87366C3.68022 8.05278 3.4947 7.982 3.35791 7.85601C3.22113 7.73003 3.14428 7.55915 3.14428 7.38098C3.14428 7.20281 3.22113 7.03194 3.35791 6.90595C3.4947 6.77996 3.68022 6.70918 3.87366 6.70918H7.27062V3.58041C7.27062 3.40224 7.34747 3.23136 7.48425 3.10537C7.62104 2.97939 7.80655 2.90861 8 2.90861Z"
                    fill="#63ECBC" />
            </svg>
            Generar nueva nómina
        </button>
    </x-slot>
</x-modules-header>
{{-- @endif --}}
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


<div class="alert alert-primary" role="alert">
    Señor usuario, recuerde actualizar los <a href="{{ route('configuraicon.calculosnomina') }}">valores fijos (aux transporte, retenciones)</a> y las <a href="{{route('nomina.preferecia-pago')}}">preferencias de pago</a> para este año {{ now()->year }}.
</div>

<script type="text/javascript">
    setTimeout(function() {
        $('.alert-success, .alert-danger').hide();
    }, 5000);
</script>

<div class="row card-description">
    <div class="col-md-12">
        <div class="table-responsive">
            <table class="table table-light table-striped table-hover @if(env('APP_ENTORNO')==2 && auth()->user()->empresa == 77777) mytable @endif" id="table-nominas" style="width: 100%; border: 1px solid #e9ecef;">
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
                    @php $estadosNomina = $periodo->estadosNomina();  @endphp
                    <tr>
                        <td>{{ $periodo->periodo() }}</td>
                        <td>{{ $periodo->empleados() }}</td>
                        <td>{{ $estadosNomina->aceptadas}}</td>
                        <td>{{ $estadosNomina->enEspera}}</td>
                        <td>{{ $estadosNomina->rechazadas}}</td>
                        <td>{{ $estadosNomina->estado }}</td>
                        <td>
                            {{-- @if(isset($_SESSION['permisos']['158']) || auth()->user()->username == 'gestordepartes') --}}
                            <a href="{{ route('nomina.liquidar', ['periodo' => $periodo->periodo, 'year'=> $periodo->year]) }}" title="Detalle de Nómina"><i class="far fa-eye color"></i></a>
                            <a href="#" data-toggle="modal" data-target="#reiniciar-{{$periodo->id}}" ><i data-tippy-content="Reinicia el periodo con los datos calculados" class="icono fas fa-redo color"></i></a>

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
                                        <p>¡Solo se verán afectadas las nominas de personas habilitadas <br> y las nominas que aun no se han emitido!</p>
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

                            {{-- @endif --}}
                            {{-- @if(isset($_SESSION['permisos']['157']) || auth()->user()->username == 'gestordepartes') --}}
                            <a href="{{ route('nomina-dian.emitir', ['periodo' => $periodo->periodo, 'year'=> $periodo->year]) }}" title="Emitir Nómina"><i class="fas fa-sitemap color"></i></a>
                            {{-- @endif --}}
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
                                    @for($i = 0; $i < 4; $i++)
                                        <option value="{{ date('Y') - $i }}">{{ date('Y') - $i }}</option>
                                    @endfor

                                </select>
                            </div>
                        </div>
                        <div class="col-6" style="font-size:13px">
                            <div class="form-group">
                                <label>Mes</label>
                                @php $mesActual = now()->format('m') + 1; @endphp
                                <select name="periodo" id="periodo" class="form-control selectpicker " title="Seleccione" data-live-search="true" data-size="5" required>
                                    <option value="1" {{ 1 <= $mesActual ? '' : '' }}>Enero</option>
                                    <option value="2" {{ 2 <= $mesActual ? '' : '' }}>Febrero</option>
                                    <option value="3" {{ 3 <= $mesActual ? '' : '' }}>Marzo</option>
                                    <option value="4" {{ 4 <= $mesActual ? '' : '' }}>Abril</option>
                                    <option value="5" {{ 5 <= $mesActual ? '' : '' }}>Mayo</option>
                                    <option value="6" {{ 6 <= $mesActual ? '' : '' }}>Junio</option>
                                    <option value="7" {{ 7 <= $mesActual ? '' : '' }}>Julio</option>
                                    <option value="8" {{ 8 <= $mesActual ? '' : '' }}>Agosto</option>
                                    <option value="9" {{ 9 <= $mesActual ? '' : '' }}>Septiembre</option>
                                    <option value="10" {{ 10 <= $mesActual ? '' : '' }}>Octubre</option>
                                    <option value="11" {{ 11 <= $mesActual ? '' : '' }}>Noviembre</option>
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

        firstTip = $('.tour-tips').first().attr('nro_tip');

        if (firstTip) {
            nuevoTip(firstTip);
        }

    });
</script>
@endsection
