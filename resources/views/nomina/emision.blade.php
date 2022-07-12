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
        background-color: rgb(229, 229, 229) !important;
    }

    .color:hover {
        border: solid 1px #d08f50;
    }

    .w-77 {
        width: 77% !important;
    }

    .notice {
        padding: 15px;
        background-color: #fff;
        /*border-left: 6px solid #7f7f84;*/
        margin-bottom: 10px;
        -webkit-box-shadow: 0 3px 6px 0 rgb(0 0 0 / 15%);
        -moz-box-shadow: 0 3px 6px 0 rgb(0 0 0 / 15%);
        box-shadow: 0 3px 6px 0 rgb(0 0 0 / 15%);
        min-height: 135px;
    }

    .notice-sm {
        padding: 10px;
        font-size: 80%;
    }

    .notice-lg {
        padding: 35px;
        font-size: large;
    }


    .notice-success>strong {
        color: #80D651;
    }

    .notice-info>strong {
        color: #45ABCD;
    }



    .notice-warning>strong {
        color: #FEAF20;
    }



    .notice-danger>strong {
        color: #d73814;
    }

    .disabled {
        cursor: default;
    }

    .disabled>i {
        color: #d5cdcd !important;
    }

    .enabled {
        pointer-events: default;
        cursor: pointer;
    }
</style>
@endsection

@section('content')

<div class="container-fluid">
    
     @if (session()->has('error'))
    <div class="row">
        <div class="col-12">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session()->pull('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    </div>
    @endif
    

    @if($modoLectura->success)
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <a>{{ $modoLectura->message }}, si deseas seguir disfrutando de nuestros servicios adquiere alguno de nuestros planes <a class="text-black" href="{{route('nomina.planes')}}"> <b>Click Aquí.</b></a></a>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    @include('nomina.tips.serie-base', ['pasos' => \collect([2,3,4])->diff($guiasVistas->keyBy('nro_tip')->keys())->all()])

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

    @if (isset($errors) && $errors->any())
    <div class="row">
        <div class="col-12">
            <ul class="list-unstyled">
                @foreach ($errors->all() as $error)
                <li>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ $error }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-12 col-md-6">
            <div class="notice pt-5 pl-4">
                <strong>Proceso de la emisión</strong>
                <br>
                Se han emitido <span id="cantidad-emitida">{{ $emitidas }}</span> de <span id="empleados">{{ $personas }}</span> nóminas
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="notice pt-4">
                <strong>Mes <span class="float-right">{{ ucfirst($date->monthName).' '.$date->format('Y') }}</span></strong>
                <hr>
                @if($isFinalizado)
                <strong>Estado <span class="float-right text-success">Finalizado</span></strong>
                @else
                <strong>Estado <span class="float-right text-danger">En Proceso</span></strong>
                @endif
            </div>
        </div>
    </div>

    <div class="row text-center w-100" style="font-size: 0.9em;">
        <div class="col notice">
            Aceptadas
            <br>
            <span style="font-size: 1.4em;" class="text-success font-weight-bold">{{$estadosNomina->aceptadas}}</span>
        </div>
        <div class="col notice">
            Rechazadas
            <br>
            <span style="font-size: 1.4em;" class="text-danger font-weight-bold">{{$estadosNomina->rechazadas}}</span>
        </div>
        <div class="col notice">
            Ingresos
            <br>
            <span style="font-size: 1.4em;" class="font-weight-bold">{{Auth::user()->empresaObj->moneda}} {{ App\Funcion::Parsear($devengadosTotal) }}</span>
        </div>
        <div class="col notice">
            Deducciones
            <br>
            <span style="font-size: 1.4em;" class="font-weight-bold">{{Auth::user()->empresaObj->moneda}} {{ App\Funcion::Parsear($deduccionesTotal) }}</span>
        </div>
        <div class="col notice">
            Total Pago
            <br>
            <span style="font-size: 1.4em;" class="font-weight-bold">{{Auth::user()->empresaObj->moneda}} {{ App\Funcion::Parsear($ingresosTotal) }}</span>
        </div>
    </div>

    <div class="row">


        <div class="col-12 p-4">
            <div class="row card-description">
                <div class="col-md-12">

                    <div class="tab-content fact-table" id="myTabContent">
                        <div class="tab-pane fade show active" id="empleados" role="tabpanel" aria-labelledby="empleados-tab">
                            <input type="hidden" id="url-show-empleados" value="{{route('bancos.cliente.movimientos.cuenta', 1)}}">

                            @if(Session::has('success'))
                            <div class="alert alert-success" style="margin-left: 2%;margin-right: 2%;">
                                {{Session::get('success')}}
                            </div>
                            <script type="text/javascript">
                                setTimeout(function() {
                                    $('.alert').hide();
                                    $('.active_table').attr('class', ' ');
                                }, 5000);
                            </script>
                            @endif


                            <div class="table-responsive">
                                <table class="table table-light table-striped table-hover" id="table-show-empleados" style="width: 100%; border: 1px solid #e9ecef;">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="font-weight-bold align-middle">CODIGO</th>
                                            <th class="font-weight-bold align-middle">NOMBRE</th>
                                            <th class="font-weight-bold align-middle">IDENTIFICACIÓN</th>
                                            <th class="font-weight-bold align-middle">VALOR PAGADO</th>
                                            <th class="font-weight-bold align-middle">ESTADO EMISIÓN</th>
                                            <th class="font-weight-bold align-middle text-center">ACCIONES</th>
                                        </tr>
                                    </thead>
                                    <tbody class="paul">
                                        @php $i = 0; @endphp
                                        @foreach ($detalles as $detalle)
                                        <tr style="{{$detalle['emitida'] == 6 ? 'pointer-events:none;background-color:#cccccc7a;' : ''}}">
                                            <td>
                                                <a href="{{route('personas.show', $detalles[$i]['idpersona'])}}">
                                                    {{ $detalles[$i]['codigo_dian_eliminado'] ? $detalles[$i]['codigo_dian_eliminado'] : $detalles[$i]['codigo_dian'] }}
                                                </a>
                                            </td>
                                            <td><a href="{{route('personas.show', $detalles[$i]['idpersona'])}}">{{ $detalles[$i]['persona'] }}</a></td>
                                            <td>{{ $detalles[$i]['identificacion'] }}</td>
                                            <td>{{ Auth::user()->empresaObj->moneda}} {{ App\Funcion::Parsear($detalles[$i]['total']) }}</td>
                                            <td>
                                                <span class="text-{{ $detalles[$i]['text'] }}">{{$detalles[$i]['emitida'] == 4 ? 'Ajuste de nómina ' : ''}} {{ $detalles[$i]['estado'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if(isset($_SESSION['permisos']['166']))
                                                <a href="{{route('nomina.calculosCompleto',$detalles[$i]['idnomina'])}}"><i class="far fa-eye color"></i>
                                                </a>
                                                @endif
                                                {{--<a href="{{ route('nomina.liquidar', ['periodo' => $detalles[$i]['periodo'], 'year'=> $detalles[$i]['year']]) }}" title="Editar Nómina"><i class="far fa-edit color"></i></a>--}}

                                                @if(isset($_SESSION['permisos']['169']) && !$modoLectura->success)
                                                @if($detalles[$i]['estado'] != 'Emitida')
                                                <a class="disabled" title="Solo se puede editar nominas que ya hayan sido emitidas"><i class="far fa-edit color"></i></a>
                                                @else
                                                <a href="#" onclick="confirmarAjusteNomina(`{{ route('nomina.ajustar', ['periodo' => $detalles[$i]['periodo'], 'year'=> $detalles[$i]['year'], $detalles[$i]['idpersona']]) }}`)" title="Ajustar nomina"><i class="far fa-edit color"></i></a>
                                                @endif
                                                @endif

                                                @if(isset($_SESSION['permisos']['166']))
                                                <a title="Imprimir nomina" href="{{ route('nominaCompleta.pdf', $detalles[$i]['idnomina']) }}" target="_blank"><i class="far fa-file-pdf color"></i></a>
                                                @endif


                                                @if(isset($_SESSION['permisos']['167']) && !$modoLectura->success)
                                                @if (!$empresa->nomina_dian)
                                                <a href="#" title="Emitir {{$detalles[$i]['emitida'] == 4 ? 'Ajuste de' : ''}} Nomina" onclick="validateDianNomina({{ $detalles[$i]['idnomina'] }}, '{{route('nomina-dian.emitir', [$periodo,$year])}}', '{{$codigo = ''}}')">
                                                    <i class="far fa-paper-plane color"></i>
                                                </a>
                                                @elseif($detalles[$i]['estado'] == 'No emitida' || $detalles[$i]['estado'] == 'Ajuste sin emitir' || $detalles[$i]['estado'] == 'Rechazada')
                                                <a href="#" title="Emitir {{$detalles[$i]['emitida'] == 4 ? 'Ajuste de' : ''}} Nomina" onclick="validateDianNomina({{ $detalles[$i]['idnomina'] }}, '{{route('nomina-dian.emitir', [$periodo,$year])}}', '{{$codigo = ''}}')">
                                                    <i class="far fa-paper-plane color"></i>
                                                </a>
                                                @elseif($detalle['emitida'] == 1)
                                                <a href="{{ route('nomina.xml', $detalle['idnomina']) }}" title="Descargar XML de la nómina">
                                                    <i class="far fa-file-code color"></i>
                                                </a>
                                                @else
                                                <a class="disabled" title="Solo se puede editar nominas que ya hayan sido emitidas">
                                                    <i class="far fa-paper-plane color"></i>
                                                </a>
                                                @endif
                                                @endif

                                                @if(isset($_SESSION['permisos']['169']) && !$modoLectura->success)
                                                <a class="btn-comentario" href="#" title="Agregar observación" data-route="{{ route('nomina.traer.observacion') }}" data-nomina="{{$detalles[$i]['idnomina']}}">
                                                    <i class="far fa-comment color"></i>
                                                </a>
                                                @endif

                                                <a href="{{ route('emitir-nomina.email', $detalles[$i]['idnomina']) }}" title="Enviar nómina al correo">
                                                    <i class="fas fa-envelope-open-text color"></i>
                                                </a>

                                                @if(isset($_SESSION['permisos']['159']))
                                                    @if($detalles[$i]['estado'] == 'Emitida')
                                                
                                                        <a title="Eliminar nomina dian"
                                                        onclick="validateDianNomina({{ $detalles[$i]['idnomina'] }}, '{{route('nomina-dian.emitir', [$periodo,$year])}}', '{{$codigo = ''}}')">
                                                            <i class="far fa-times color"></i>
                                                        </a>
                                                    @else
                                                        <a class="disabled" title="Eliminar">
                                                            <i class="far fa-times color"></i>
                                                        </a>
                                                    @endif
                                                @endif
                                            </td>
                                        </tr>

                                        @php $i++; @endphp
                                        @endforeach
                                    </tbody>
                                </table>

                                @include('nomina.modals.observacion-nomina')


                            </div>
                            <div class="text-right mt-5">
                                @if (!$modoLectura->success)
                                <a href="{{ route('nomina.liquidar', ['periodo' => $periodo, 'year' => $year]) }}" role="button" class="btn btn-success">Editar nominas</a>
                                @else
                                <a href="#" role="button" class="btn btn-success disabled">Editar nominas</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="{{asset('lowerScripts/nomina/nomina.js')}}"></script>
<script>
    $(document).ready(function() {


        $('.btn-comentario').on('click', function(e) {
            cargando(true);
            e.preventDefault();
            const element = $(this);
            const nomina = element.data('nomina');
            const route = $(this).data('route');
            traerObservacion(nomina, route);
            $('#btn-guardar-obser-nomina').attr('data-nomina', nomina);

        })

        function traerObservacion(nomina, route) {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            jQuery.ajax({
                method: "GET",
                data: {
                    'nomina': nomina
                },
                url: route,
                success: function(data) {
                    cargando(false);
                    $('#textarea').val(data);
                    $('#modal-agreg-observacion-nomina').modal('show');

                }
            })
        }

        $('#table-show-empleados').DataTable({
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
            "searching": false,
            "order": [
                [0, "desc"]
            ],
        });
    });

    function confirmarAjusteNomina(params) {
        Swal.fire({
            title: '¿Generar un ajuste de nómina?',
            text: "tenga en cuenta que esto implica que se bloquee esta nómina emitida y se genere una nueva nómina al empleado seleccionado",
            type: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Si, generar',
            cancelButtonText: 'Cancelar',
        }).then((result) => {
            if (result.value) {
                window.location = params;
            }
        })
    }
</script>
@endsection